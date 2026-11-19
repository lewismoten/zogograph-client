#!/usr/bin/env php
<?php
declare(strict_types=1);

/*
 * The retention sweep, on its own.
 *
 * Used to live inside cron.php's --prune flag, running as one long
 * transaction: a year of vitals compacted, half a dozen tables swept, then a
 * VACUUM-shaped reclaim, all between a single BEGIN and COMMIT. host-cron.php
 * already knew better - it takes a non-blocking lock and *skips* a tick
 * rather than queue behind a slow one. cron.php took no lock at all, so
 * whatever took five minutes once blocked five more --vitals ticks behind it,
 * and each of those found the database busy and died in under a second -
 * which is exactly the "12 processes, Kill:0, Run Time 0-2s" lfd reported for
 * one install (stats.lewismoten.com) running this codebase.
 *
 * Two changes fix that instead of one:
 *
 *   1. A lock of its own, same shape as host-cron.php's: a run already in
 *      progress means this tick skips rather than piling a second one on top.
 *   2. Nothing here runs in one long transaction any more. Compaction asks
 *      for PRUNE_BATCH buckets at a time and commits after each batch; the
 *      flat deletes take PRUNE_BATCH rows at a time the same way. A --vitals
 *      tick that fires mid-sweep now finds the database busy for a batch's
 *      width, not a whole sweep's.
 *
 * Crontab line (replaces --prune on the old --scan line):
 *
 *   7 * * * * /usr/local/bin/php /path/to/prune.php --quiet
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("prune.php is a command line program.\n");
}

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "No lib/config.php. Copy lib/config.sample.php and edit it.\n");
    exit(1);
}

require_once __DIR__ . '/lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
date_default_timezone_set($config['timezone'] ?? 'UTC');
define('DISKMON_LOG', dirname((string) $config['database']) . '/error.log');

require_once __DIR__ . '/lib/boot.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/alerts.php';
require_once __DIR__ . '/lib/retention.php';
require_once __DIR__ . '/lib/incident.php';
require_once __DIR__ . '/lib/encryption.php';

// Buckets per compaction commit, rows per flat-delete commit. Small enough
// that a --vitals tick never waits long; large enough that a database with a
// year of backlog still finishes in a reasonable number of commits rather
// than thousands of them.
const PRUNE_BATCH = 500;

$args = array_slice($argv, 1);
$quiet = in_array('--quiet', $args, true);
$say = function (string $line) use ($quiet): void {
    if (!$quiet) {
        echo $line, "\n";
    }
};

/*
 * Same shape as host-cron.php's lock: held for the process's lifetime, and
 * released the instant it exits for any reason because the kernel owns it,
 * not a PID file that believes whatever it last wrote. A tick that finds the
 * previous sweep still going yields to it rather than queuing - queuing is
 * the exact behaviour being removed here.
 */
$lockFile = dirname((string) $config['database']) . '/prune.lock';
$lockHandle = @fopen($lockFile, 'c');
if ($lockHandle === false) {
    fwrite(STDERR, "Unable to open $lockFile for the run lock.\n");
    exit(1);
}
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $say('Skipped: a previous prune.php run is still in progress.');
    exit(0);
}
@chmod($lockFile, 0600);
register_shutdown_function(static function () use ($lockHandle): void {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
});

/**
 * Deletes in batches of $limit, committing after each one, until a pass
 * finds nothing left. Equivalent to the old unbounded DELETE, just spread
 * over however many short transactions it takes instead of one long one.
 */
function batchedDelete(
    PDO $pdo,
    string $table,
    string $column,
    int $cutoff,
    int $limit,
): int {
    // rowid rather than id: every ordinary table has one, including the two
    // composite-key peer tables that have no id column of their own. Tables
    // declared WITHOUT ROWID would break this, and none here are.
    $removed = 0;
    $stmt = $pdo->prepare(
        "DELETE FROM {$table} WHERE rowid IN (
            SELECT rowid FROM {$table} WHERE {$column} < :cutoff LIMIT :limit
        )",
    );
    while (true) {
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $pdo->beginTransaction();
        try {
            $stmt->execute();
            $hit = $stmt->rowCount();
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        $removed += $hit;
        if ($hit < $limit) {
            break;
        }
    }
    return $removed;
}

try {
    $pdo = db($config);
    $runStarted = microtime(true);

    $rules = retentionRules($config);
    $now = time();

    // --- Compaction, batch by batch, tier by tier, commit after each. ---
    $compacted = 0;
    $compactedRemoved = 0;
    $tiers = [];
    do {
        $done = compactVitals($pdo, $rules, $now, PRUNE_BATCH);
        $compacted += $done['compacted'];
        $compactedRemoved += $done['removed'];
        foreach ($done['tiers'] as $name => $count) {
            $tiers[$name] = ($tiers[$name] ?? 0) + $count;
        }
    } while (!empty($done['more']));
    $say(sprintf(
        'compacted %d buckets (5m %d, 1h %d, 1d %d) removing %d readings',
        $compacted,
        $tiers['five_minute'] ?? 0,
        $tiers['hourly'] ?? 0,
        $tiers['daily'] ?? 0,
        $compactedRemoved,
    ));

    // --- The flat sweeps that used to ride compactVitals's own transaction. ---
    if ((int) $rules['daily_days'] > 0) {
        $removed = batchedDelete(
            $pdo,
            'vitals',
            'taken_at',
            $now - (int) $rules['daily_days'] * 86400,
            PRUNE_BATCH,
        );
        $say("vitals (past daily tier): removed $removed");
    }

    foreach (
        ['alerts' => ['alert_days', 'raised_at'], 'actions' => ['action_days', 'at']]
        as $table => [$key, $column]
    ) {
        $days = (int) ($rules[$key] ?? 0);
        if ($days <= 0) {
            continue;
        }
        $removed = batchedDelete($pdo, $table, $column, $now - $days * 86400, PRUNE_BATCH);
        if ($removed > 0) {
            $say("$table: removed $removed");
        }
    }

    foreach (
        [
            'remote_peer_vitals' => ['remote_vital_days', 'taken_at'],
            'remote_peer_receipts' => ['remote_receipt_days', 'received_at'],
        ]
        as $table => [$key, $column]
    ) {
        $days = $key === 'remote_receipt_days'
            ? max(1, (int) $rules[$key])
            : (int) $rules[$key];
        if ($days <= 0) {
            continue;
        }
        $removed = batchedDelete($pdo, $table, $column, $now - $days * 86400, PRUNE_BATCH);
        if ($removed > 0) {
            $say("$table: removed $removed");
        }
    }

    $say(
        'kept ' .
            protectedReadings($pdo, max(0, (int) $rules['protect_hours']) * 3600) .
            ' readings around alerts',
    );

    // --- The rest of the old prune(): scans, process samples, incidents, orphan paths. ---
    if ((int) $rules['scan_days'] > 0) {
        $removed = batchedDelete(
            $pdo,
            'scans',
            'started_at',
            $now - (int) $rules['scan_days'] * 86400,
            PRUNE_BATCH,
        );
        $say("scans: removed $removed");
    }

    if ((int) $rules['process_days'] > 0) {
        $removed = batchedDelete(
            $pdo,
            'process_samples',
            'taken_at',
            $now - (int) $rules['process_days'] * 86400,
            PRUNE_BATCH,
        );
        $say("process_samples: removed $removed");
    }

    if ((int) $rules['incident_days'] > 0) {
        $removed = pruneIncidents($pdo, (int) $rules['incident_days']);
        if ($removed > 0) {
            $say("incidents: removed $removed");
        }
    }

    // Orphan paths, same batched shape: PRUNE_BATCH ids at a time until none
    // are left.
    $pathsRemoved = 0;
    $pathStmt = $pdo->prepare(
        'DELETE FROM paths WHERE id IN (
            SELECT id FROM paths
             WHERE id NOT IN (SELECT path_id FROM path_sizes)
               AND id NOT IN (SELECT path_id FROM alerts WHERE path_id IS NOT NULL)
             LIMIT :limit
        )',
    );
    while (true) {
        $pathStmt->bindValue(':limit', PRUNE_BATCH, PDO::PARAM_INT);
        $pdo->beginTransaction();
        try {
            $pathStmt->execute();
            $hit = $pathStmt->rowCount();
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        $pathsRemoved += $hit;
        if ($hit < PRUNE_BATCH) {
            break;
        }
    }
    if ($pathsRemoved > 0) {
        $say("paths: removed $pathsRemoved");
    }

    // Encryption copy pruning is its own small, already-cheap sweep; no
    // batching needed, just moved here with the rest of retention.
    $pruned = encryptionPruneConfiguredCopies($config, $pdo);
    if ($pruned['removed'] > 0) {
        $say('encrypted artifacts: removed ' . $pruned['removed']);
    }
    foreach ($pruned['warnings'] as $warning) {
        $say('encryption retention warning: ' . $warning);
    }

    // Reclaim once, after every batch above is done, same as the old prune().
    $say('space:  ' . reclaimSpace($pdo));

    $pdo
        ->prepare(
            'INSERT INTO collector_runs (started_at, kind, jobs, seconds, ok, error)
             VALUES (?, ?, ?, ?, ?, ?)',
        )
        ->execute([
            (int) $runStarted,
            'prune',
            'prune',
            round(microtime(true) - $runStarted, 3),
            1,
            null,
        ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logProblem('error', 'prune: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
    exit(1);
}
