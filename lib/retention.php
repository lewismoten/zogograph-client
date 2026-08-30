<?php
declare(strict_types=1);

/*
 * Keeping the history without keeping every reading.
 *
 * A reading a minute is half a million rows a year, and a monitor that fills
 * the disk it is watching has failed in the most embarrassing way available to
 * it. So old readings are compacted: many rows become one, covering a longer
 * span, and the further back you look the coarser it gets.
 *
 *   the last 7 days      every reading, exactly as collected
 *   7 to 30 days         one row per 5 minutes
 *   30 days to a year    one row per hour
 *   beyond a year        one row per day, kept indefinitely
 *
 * The interesting question is what a compacted row should *say*, and the
 * obvious answer is wrong. The previous rule here kept the first reading of
 * each hour and threw away the other fifty-nine - so a load spike at twenty
 * past, the exact thing somebody looks back through a year of history to find,
 * survived one time in sixty. Averaging is no better: a minute at 100% CPU
 * inside an idle hour averages to 1.7% and disappears just as completely.
 *
 * So a compacted row records the *worst* moment in its bucket. High is bad for
 * most things, which means MAX; low is bad for the free-space columns, which
 * means MIN for those three. A year-old point saying "load 8" means load
 * reached 8 at some point in that hour, and the panel says so - a peak that
 * might be misread is worth far more than an average that is certainly useless.
 *
 * Cumulative counters come out right by accident and it is worth writing down
 * why, because it looks like a bug. cpu_total_ticks and the rest only ever
 * increase, so the maximum in a bucket *is* the last value in it, and
 * differencing consecutive compacted rows still yields the correct total for
 * the span. Better still, when a bucket contains a reboot the counter resets
 * mid-bucket and MAX holds the pre-reboot peak - so the next difference is
 * negative and the reset is reported as a gap, exactly as it would have been
 * without compaction. Taking the true final value would have quietly smoothed
 * a reboot into ordinary traffic.
 *
 * And nothing near an alert is ever touched. See protectedWindow(): the whole
 * point of keeping history is the hour around the thing that went wrong, and it
 * would be a poor joke to compact away the evidence for the incident record
 * that sits next to it.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/incident.php';
// humanBytes() lives here. Required explicitly rather than relied on: this file
// is loaded by cron.php, which happens to pull in alerts.php first, and that
// coincidence is not a dependency.
require_once __DIR__ . '/alerts.php';

/**
 * The tiers, and how long each lasts.
 *
 * Zero anywhere means "for ever" - a retention rule that silently deleted
 * everything when somebody wrote 0 meaning "no limit" would be the worst
 * possible reading of an ambiguous number.
 */
function retentionRules(array $config): array
{
    $set = $config['retention'] ?? [];

    return $set + [
        'raw_days' => 7,
        'five_minute_days' => 30,
        'hourly_days' => 365,
        // Daily rows are tiny - a few hundred a year - and are what makes
        // "was this normal last summer?" answerable at all.
        'daily_days' => 0,

        /*
         * Incidents outlive everything, because they are the evidence.
         *
         * One per alerting evaluation rather than one per reading, so even a
         * bad year is a few hundred rows at roughly 26 KB each. Bounding that
         * to save a handful of megabytes would trade the only permanent record
         * of what actually happened for nothing worth having.
         */
        'incident_days' => 0,

        // How much untouched history to keep either side of an alert.
        'protect_hours' => 2,

        // The audit log and the raised alerts themselves, which are small and
        // are the index into the incidents.
        'alert_days' => 0,
        'action_days' => 0,

        'scan_days' => 400,
        'process_days' => 400,
        // Remote payloads are already summarized vitals, not the local raw
        // series. Keep their fleet context for a comparable year by default.
        'remote_vital_days' => 400,
        // Nonces need only outlive the replay window; two days also leaves
        // useful security evidence without making a permanent nonce ledger.
        'remote_receipt_days' => 2,
    ];
}

/**
 * How each column survives being merged with its neighbours.
 *
 * Everything takes the worst value in the bucket. For most columns worse means
 * larger; for the three that measure what is left, worse means smaller. Listing
 * the exceptions rather than the rule keeps this honest when a column is added:
 * a new one is treated as MAX, which is the safe direction for a monitor.
 */
function retentionMinColumns(): array
{
    return ['disk_free_bytes', 'inodes_free', 'mem_available_bytes'];
}

/** Columns that are never aggregated: the key, and the timestamp we rebuild. */
function retentionSkipColumns(): array
{
    return ['id', 'taken_at', 'samples'];
}

/**
 * Compacts everything old enough, tier by tier.
 *
 * Each tier is compacted from the outside in - daily first, then hourly, then
 * five-minute - so that a row is only ever merged once per run rather than
 * being merged into a five-minute bucket and then immediately again into an
 * hourly one.
 *
 * @return array{compacted: int, removed: int, protected: int, tiers: array}
 */
function compactVitals(PDO $pdo, array $rules, ?int $now = null): array
{
    $now ??= time();
    $pad = max(0, (int) $rules['protect_hours']) * 3600;

    $columns = vitalsColumns($pdo);
    $tiers = [];
    $compacted = 0;
    $removed = 0;

    $bands = [
        // [bucket seconds, oldest edge of the band, newest edge of the band]
        'daily' => [
            86400,
            null,
            $rules['hourly_days'] > 0
                ? $now - (int) $rules['hourly_days'] * 86400
                : null,
        ],
        'hourly' => [
            3600,
            $rules['hourly_days'] > 0
                ? $now - (int) $rules['hourly_days'] * 86400
                : null,
            $rules['five_minute_days'] > 0
                ? $now - (int) $rules['five_minute_days'] * 86400
                : null,
        ],
        'five_minute' => [
            300,
            $rules['five_minute_days'] > 0
                ? $now - (int) $rules['five_minute_days'] * 86400
                : null,
            $rules['raw_days'] > 0
                ? $now - (int) $rules['raw_days'] * 86400
                : null,
        ],
    ];

    foreach ($bands as $name => [$bucket, $from, $to]) {
        if ($to === null) {
            // The tier below is set to keep everything for ever, so nothing can
            // reach this one.
            $tiers[$name] = 0;
            continue;
        }

        $done = compactBand($pdo, $columns, $bucket, $from, $to, $pad);
        $tiers[$name] = $done['buckets'];
        $compacted += $done['buckets'];
        $removed += $done['removed'];
    }

    // Anything past the last tier, when that tier is not "for ever".
    if ((int) $rules['daily_days'] > 0) {
        $stmt = $pdo->prepare(
            'DELETE FROM vitals WHERE taken_at < :cutoff' .
                protectionClause('vitals'),
        );
        $stmt->execute([
            ':cutoff' => $now - (int) $rules['daily_days'] * 86400,
            ':pad' => $pad,
        ]);
        $removed += $stmt->rowCount();
    }

    /*
     * The record of what happened, on its own much longer clock.
     *
     * Both were configurable and neither was applied - the settings existed and
     * did nothing, so the honest answer to "does this ever get cleared" was no,
     * for ever, whatever the config said. They still default to keeping
     * everything, because these are the two tables somebody goes looking for
     * months later ("when did I delete that", "did it warn me first") and each
     * row is a few dozen bytes. But the setting now means what it says.
     */
    $kept = [];
    foreach (
        ['alerts' => 'alert_days', 'actions' => 'action_days']
        as $table => $key
    ) {
        $days = (int) ($rules[$key] ?? 0);
        $kept[$table] = $days;
        if ($days <= 0) {
            continue;
        }
        $column = $table === 'alerts' ? 'raised_at' : 'at';
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$column} < ?");
        $stmt->execute([$now - $days * 86400]);
        $removed += $stmt->rowCount();
    }

    foreach (
        [
            'remote_peer_vitals' => ['remote_vital_days', 'taken_at'],
            'remote_peer_receipts' => ['remote_receipt_days', 'received_at'],
        ]
        as $table => [$key, $column]
    ) {
        // A replay ledger must always expire; zero otherwise means forever in
        // retention settings, which is unsuitable for request nonces.
        $days =
            $key === 'remote_receipt_days'
                ? max(1, (int) $rules[$key])
                : (int) $rules[$key];
        $kept[$table] = $days;
        if ($days <= 0) {
            continue;
        }
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$column} < ?");
        $stmt->execute([$now - $days * 86400]);
        $removed += $stmt->rowCount();
    }

    return [
        'compacted' => $compacted,
        'removed' => $removed,
        'tiers' => $tiers,
        'kept_days' => $kept,
    ];
}

/**
 * Merges one band of readings into buckets of the given size.
 *
 * @return array{buckets: int, removed: int}
 */
function compactBand(
    PDO $pdo,
    array $columns,
    int $bucket,
    ?int $from,
    int $to,
    int $pad,
): array {
    $min = retentionMinColumns();
    $skip = retentionSkipColumns();

    $select = [
        'MAX(taken_at) AS taken_at',
        'SUM(COALESCE(samples, 1)) AS samples',
    ];
    $into = ['taken_at', 'samples'];

    foreach ($columns as $column) {
        if (in_array($column, $skip, true)) {
            continue;
        }
        $into[] = $column;
        $select[] =
            (in_array($column, $min, true) ? 'MIN' : 'MAX') .
            "($column) AS $column";
    }

    $where = ['taken_at < :to'];
    $params = [':to' => $to, ':bucket' => $bucket, ':pad' => $pad];
    if ($from !== null) {
        $where[] = 'taken_at >= :from';
        $params[':from'] = $from;
    }

    $guard = protectionClause('vitals');
    $sql =
        'SELECT ' .
        implode(', ', $select) .
        ', taken_at / :bucket AS bucket_key
         FROM vitals
         WHERE ' .
        implode(' AND ', $where) .
        $guard .
        '
         GROUP BY taken_at / :bucket
         HAVING COUNT(*) > 1';

    $read = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $read->bindValue($key, $value, PDO::PARAM_INT);
    }
    $read->execute();
    $buckets = $read->fetchAll(PDO::FETCH_ASSOC);

    if ($buckets === []) {
        return ['buckets' => 0, 'removed' => 0];
    }

    $insert = $pdo->prepare(
        'INSERT INTO vitals (' .
            implode(', ', $into) .
            ')
         VALUES (' .
            implode(', ', array_fill(0, count($into), '?')) .
            ')',
    );

    /*
     * The originals go before the replacement arrives.
     *
     * Deleting by bucket rather than by id, and doing it first, so that a
     * bucket cannot end up holding both its inputs and its output - which
     * would double every figure in it and be very hard to notice a year later.
     */
    $clear = $pdo->prepare(
        'DELETE FROM vitals WHERE taken_at / :bucket = :key AND taken_at < :to' .
            ($from !== null ? ' AND taken_at >= :from' : '') .
            protectionClause('vitals'),
    );

    $removed = 0;
    foreach ($buckets as $row) {
        $key = (int) $row['bucket_key'];
        unset($row['bucket_key']);

        $clear->bindValue(':bucket', $bucket, PDO::PARAM_INT);
        $clear->bindValue(':key', $key, PDO::PARAM_INT);
        $clear->bindValue(':to', $to, PDO::PARAM_INT);
        $clear->bindValue(':pad', $pad, PDO::PARAM_INT);
        if ($from !== null) {
            $clear->bindValue(':from', $from, PDO::PARAM_INT);
        }
        $clear->execute();
        $removed += $clear->rowCount();

        $values = [];
        foreach ($into as $column) {
            $values[] = $row[$column] ?? null;
        }
        $insert->execute($values);
    }

    return ['buckets' => count($buckets), 'removed' => $removed];
}

/**
 * The SQL that keeps the readings around an alert out of every delete.
 *
 * One named placeholder used four times, which SQLite allows and which matters
 * here: this clause is appended to statements that already bind by name, and
 * PDO will not mix named and positional styles in one statement.
 *
 * Both tables are consulted. An incident is captured for a set of alerts, so
 * the two nearly always agree - but an alert whose snapshot failed to be
 * written would otherwise have no protection at all, and that is precisely the
 * alert somebody will want the surrounding readings for.
 */
function protectionClause(string $table): string
{
    return "
           AND NOT EXISTS (
             SELECT 1 FROM incidents i
              WHERE $table.taken_at >= i.captured_at - :pad
                AND $table.taken_at <= i.captured_at + :pad
           )
           AND NOT EXISTS (
             SELECT 1 FROM alerts a
              WHERE $table.taken_at >= a.raised_at - :pad
                AND $table.taken_at <= a.raised_at + :pad
           )";
}

/** @return list<string> */
function vitalsColumns(PDO $pdo): array
{
    static $columns = null;
    if ($columns === null) {
        $columns = $pdo
            ->query('PRAGMA table_info(vitals)')
            ->fetchAll(PDO::FETCH_COLUMN, 1);
    }
    return $columns;
}

/**
 * How many readings are being protected from compaction right now.
 *
 * Reported rather than merely done, because "the last year is hourly except for
 * eleven hours that are not" is surprising until you know why, and the surprise
 * is much worse when a chart is what tells you.
 */
function protectedReadings(PDO $pdo, int $pad): int
{
    // Near an incident *or* an alert - not both, which is what inverting the
    // clause above with a search and replace would have asked for.
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM vitals
          WHERE EXISTS (
                  SELECT 1 FROM incidents i
                   WHERE vitals.taken_at BETWEEN i.captured_at - :pad AND i.captured_at + :pad
                )
             OR EXISTS (
                  SELECT 1 FROM alerts a
                   WHERE vitals.taken_at BETWEEN a.raised_at - :pad AND a.raised_at + :pad
                )',
    );
    $stmt->execute([':pad' => $pad]);
    return (int) $stmt->fetchColumn();
}

/**
 * Gives the freed pages back to the filesystem.
 *
 * Deleting rows in SQLite does not shrink the file. The pages go on a free list
 * and are reused by later writes, which is fine for a database that keeps
 * growing into them and useless here: the entire point of compacting a year of
 * readings is to get the space back, and without this the file stays exactly as
 * large as it was on its worst day for ever.
 *
 * VACUUM rewrites the database, so it is not free and it is not run on every
 * prune. Only when the free list is worth reclaiming - a quarter of the file -
 * which after a normal nightly prune it is not, and after the first compaction
 * of a year of history it certainly is.
 *
 * Deliberately outside any transaction: SQLite refuses to VACUUM inside one,
 * and it needs room for a second copy of the database while it works. Both are
 * reasons to call this after the pruning has committed rather than during it.
 */
function reclaimSpace(PDO $pdo, float $minimumWasted = 0.25): string
{
    $free = (int) $pdo->query('PRAGMA freelist_count')->fetchColumn();
    $total = (int) $pdo->query('PRAGMA page_count')->fetchColumn();

    if ($total <= 0) {
        return 'nothing to reclaim';
    }

    $wasted = $free / $total;
    if ($wasted < $minimumWasted) {
        return sprintf('%.0f%% free pages, not worth a rewrite', $wasted * 100);
    }

    $pageSize = (int) $pdo->query('PRAGMA page_size')->fetchColumn();
    $before = $free * $pageSize;

    try {
        $pdo->exec('VACUUM');
    } catch (Throwable $e) {
        /*
         * A failed VACUUM is untidy, not dangerous - the data is all still
         * there and the file is merely larger than it needs to be. Usually it
         * means the disk is too full to hold a second copy, which on a tool
         * that exists to watch for full disks is a situation worth reporting
         * rather than throwing.
         */
        return 'could not reclaim: ' . $e->getMessage();
    }

    return sprintf('reclaimed about %s', humanBytes($before));
}
