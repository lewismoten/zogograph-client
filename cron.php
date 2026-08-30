#!/usr/bin/env php
<?php
declare(strict_types=1);

/*
 * The collector. Run from cron; it does nothing over HTTP and .htaccess denies
 * it there.
 *
 * Three jobs on three clocks:
 *
 *   --vitals   disk, inodes, load, memory, cpu. Costs milliseconds. Every
 *              minute is reasonable; every five is plenty.
 *   --scan     walk the tree and size everything. Costs minutes of IO on a big
 *              home directory. Hourly at most.
 *   --alerts   compare the latest readings against the rules and email if
 *              anything has tripped. Costs nothing, so it runs after both.
 *
 * With no flags it does all three, which is right for a daily job and wrong for
 * a per-minute one - see the README for the crontab lines.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("cron.php is a command line program.\n");
}

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    fwrite(
        STDERR,
        "No lib/config.php. Copy lib/config.sample.php and edit it.\n",
    );
    exit(1);
}

require_once __DIR__ . '/lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
date_default_timezone_set($config['timezone'] ?? 'UTC');

require_once __DIR__ . '/lib/scans.php';
require_once __DIR__ . '/lib/rdns.php';
require_once __DIR__ . '/lib/boot.php';
define('DISKMON_LOG', dirname((string) $config['database']) . '/error.log');

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/encryption.php';
require_once __DIR__ . '/lib/collect.php';
require_once __DIR__ . '/lib/alerts.php';
require_once __DIR__ . '/lib/incident.php';
require_once __DIR__ . '/lib/retention.php';
require_once __DIR__ . '/lib/processes.php';
// Outbound URL checks, which need no privilege and so ride the account's cron.
require_once __DIR__ . '/lib/urlwatch.php';
// Domain expiry, which is the one outage no amount of server monitoring sees.
require_once __DIR__ . '/lib/domains.php';
require_once __DIR__ . '/lib/hostdb.php';
require_once __DIR__ . '/lib/peertransport.php';
// Update-result alerts are measured by the root collector and read here.
require_once __DIR__ . '/lib/updates.php';

$flags = array_slice($argv, 1);
$quiet = in_array('--quiet', $flags, true);
$jobs = array_values(
    array_filter($flags, fn(string $f): bool => $f !== '--quiet'),
);
$wants = fn(string $flag): bool => $jobs === [] || in_array($flag, $jobs, true);
$runStarted = microtime(true);
$runPdo = null;

/** Records the cost of the whole cron invocation, even when one job fails. */
$recordRun = function (?PDO $pdo, bool $ok, ?string $error = null) use (
    $runStarted,
    $jobs,
): void {
    if ($pdo === null) {
        return;
    }
    try {
        $pdo->prepare(
            'INSERT INTO collector_runs (started_at, kind, jobs, seconds, ok, error)
             VALUES (?, ?, ?, ?, ?, ?)',
        )->execute([
            (int) $runStarted,
            'account',
            $jobs === [] ? 'normal' : implode(' ', $jobs),
            round(microtime(true) - $runStarted, 3),
            $ok ? 1 : 0,
            $error,
        ]);
    } catch (Throwable) {
        // Observability must never turn a successful collection into a failure.
    }
};

$say = function (string $line) use ($quiet): void {
    if (!$quiet) {
        echo $line, "\n";
    }
};

if (in_array('--help', $jobs, true) || in_array('-h', $jobs, true)) {
    echo <<<TEXT
    Zogograph collector

      php cron.php --vitals     take one cheap reading (disk, memory, cpu)
      php cron.php --scan       walk the tree and record what is where
      php cron.php --urls       run the URL checks that are due (also part of --vitals)
      php cron.php --rdns       resolve one waiting address (also part of --vitals)
      php cron.php --domains    look up domain expiry for any that are due
      php cron.php --alerts     evaluate the rules, email if anything tripped
      php cron.php --prune      thin readings older than the retention window
      php cron.php --seal       create opted-in encrypted SQLite copies
      php cron.php --verify-sealed  re-authenticate retained encrypted copies
      php cron.php --recovery-drill  record a non-destructive selected-store recovery proof
      php cron.php              all of the above
      php cron.php --quiet      say nothing unless something goes wrong

    TEXT;
    exit(0);
}

try {
    $pdo = db($config);
    $runPdo = $pdo;

    if ($wants('--vitals')) {
        $row = takeVitals($config, $pdo);

        // Only writes anything when the reading was a busy one.
        $processes = recordProcesses($config, $pdo, $row);
        if ($processes['recorded'] > 0) {
            $say(
                sprintf(
                    'busy    recorded %d processes (%s)',
                    $processes['recorded'],
                    implode(' and ', $processes['reasons']),
                ),
            );
        }

        $say(
            sprintf(
                'vitals  disk %s free of %s, load %s, cpu %s',
                humanBytes($row['disk_free_bytes']),
                humanBytes($row['disk_total_bytes']),
                $row['load1'] === null ? 'n/a' : (string) $row['load1'],
                $row['cpu_percent'] === null
                    ? 'n/a'
                    : $row['cpu_percent'] . '%',
            ),
        );
    }

    // Sealing is deliberately explicit. A normal collector invocation must
    // never create encrypted copies merely because a key path exists.
    if (in_array('--seal', $jobs, true)) {
        $sealed = encryptionSealConfiguredCopies($config, $pdo, __DIR__);
        if ($sealed !== []) {
            encryptionRecordArtifacts($pdo, $sealed);
        }
        $pruned = encryptionPruneConfiguredCopies($config, $pdo);
        $say(
            $sealed === []
                ? 'Encryption is disabled; no sealed copies made.'
                : 'Created ' .
                    count($sealed) .
                    ' sealed database cop' .
                    (count($sealed) === 1 ? 'y.' : 'ies.'),
        );
        if ($pruned['removed'] > 0) {
            $say(
                'Pruned ' .
                    $pruned['removed'] .
                    ' expired encrypted artifact' .
                    ($pruned['removed'] === 1 ? '.' : 's.'),
            );
        }
        foreach ($pruned['warnings'] as $warning) {
            $say('Encryption retention warning: ' . $warning);
        }
    }

    if (in_array('--verify-sealed', $jobs, true)) {
        $checked = encryptionVerifyConfiguredCopies($config, $pdo, __DIR__);
        $say(
            'Verified ' .
                $checked['verified'] .
                ' retained encrypted artifact' .
                ($checked['verified'] === 1 ? '.' : 's.'),
        );
        if ($checked['failed'] > 0) {
            $say(
                'Encryption verification failed for ' .
                    $checked['failed'] .
                    ' artifact' .
                    ($checked['failed'] === 1 ? '.' : 's.'),
            );
        }
        foreach ($checked['warnings'] as $warning) {
            $say('Encryption verification warning: ' . $warning);
        }
    }

    if (in_array('--recovery-drill', $jobs, true)) {
        $drill = encryptionRunRecoveryDrill($config, $pdo, __DIR__);
        $say(
            'Recovery drill tested ' .
                $drill['tested'] .
                ' selected encrypted store' .
                ($drill['tested'] === 1 ? '.' : 's.'),
        );
        foreach ($drill['warnings'] as $warning) {
            $say('Encryption recovery-drill warning: ' . $warning);
        }
    }

    if ($wants('--scan')) {
        $result = runScan($config, $pdo);
        $say(
            sprintf(
                'scan    %s files in %s dirs, %s, kept %d paths%s',
                number_format($result['files']),
                number_format($result['dirs']),
                humanBytes($result['bytes']),
                $result['kept'],
                $result['error'] ? ' (partial: ' . $result['error'] . ')' : '',
            ),
        );
    }

    /*
     * The URL checks that are due.
     *
     * Part of --vitals rather than a flag of its own, because they belong to
     * the same minute-by-minute rhythm and asking somebody to add a third cron
     * line is asking for two of the three to be installed. Each check decides
     * its own interval; this only asks which are due.
     *
     * Failures here must not take the vitals run down with them: a URL that
     * hangs is exactly the condition being watched for, and losing the disk
     * reading to it would be the monitor breaking in sympathy.
     */
    /*
     * Whether each of these is due, rather than whether it was asked for.
     *
     * The crontab line runs this every five minutes; what happens inside is
     * decided per scan, which is what makes "daily" and "only when somebody is
     * looking" expressible at all. A flag on the command line still forces one
     * - somebody typing --urls means now.
     */
    $due = function (string $key) use ($config, $pdo, $wants): bool {
        if ($wants('--' . $key)) {
            return true;
        }
        if (!$wants('--vitals')) {
            return false;
        }

        $last = scanLastRunFor($config, $pdo, $key);
        return scanDue($config, $pdo, $key, $last)['due'];
    };

    if ($due('urls')) {
        try {
            $urls = urlWatchRunDue($pdo, null, null, $config);
            if ($urls['ran'] > 0) {
                $say(
                    sprintf(
                        'urls    checked %d, %d failing',
                        $urls['ran'],
                        $urls['failed'],
                    ),
                );
                foreach ($urls['rows'] as $row) {
                    if (!$row['ok']) {
                        $say(
                            sprintf(
                                '        FAIL %s: %s',
                                $row['label'],
                                $row['error'] ?? 'status ' . $row['status'],
                            ),
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            logProblem('warning', 'url checks: ' . $e->getMessage());
            $say('urls    could not run: ' . $e->getMessage());
        }
    }

    /*
     * Domain expiry, on its own much slower clock.
     *
     * Weekly per domain, and only the ones actually due - a registration does
     * not change between Tuesdays, and whois servers throttle anybody who asks
     * more often. Ridden along with --vitals for the same reason the URL checks
     * are: a third cron line is a third thing to be missing.
     */
    /*
     * One reverse lookup, and anything the host answered while we were away.
     *
     * Rides along with --vitals for the same reason the URL checks do: it
     * needs to happen regularly and nobody wants a fourth crontab line. The
     * import is first, so an address the host has already answered is not
     * looked up a second time by this run.
     *
     * The budget is deliberately small. This uses somebody else's resolver on
     * somebody else's behalf, and a queue that drains slowly is the correct
     * behaviour rather than a limitation to work around.
     */
    if ($due('rdns')) {
        try {
            $imported = rdnsImportHostAnswers($pdo, hostDb($config, false));
            if ($imported > 0) {
                $say(
                    sprintf(
                        'names   took %d answer(s) from the host collector',
                        $imported,
                    ),
                );
            }

            $budget = externalBudget($config, 'rdns');
            for ($i = 0; $i < $budget; $i++) {
                $one = rdnsDrainOne($pdo, 'cron');
                if ($one === null) {
                    break;
                }
                $say(
                    sprintf(
                        'names   %s -> %s (%dms, waited %s)',
                        $one['address'],
                        $one['name'] ?? 'no name',
                        $one['ms'],
                        plainSpan($one['waited']),
                    ),
                );
            }

            /*
             * Requests a collector took and never finished.
             *
             * A run killed mid-lookup leaves a row marked taken with no
             * answer; released here so the next run picks it up rather than
             * the queue quietly losing it.
             */
            rdnsReleaseStuck($pdo);
        } catch (Throwable $e) {
            logProblem('warning', 'reverse lookups: ' . $e->getMessage());
            $say('names   could not run: ' . $e->getMessage());
        }
    }

    if ($due('domains')) {
        try {
            $domains = domainCheckDue($pdo, null, null, $config);
            if ($domains['ran'] > 0) {
                $say(
                    sprintf(
                        'domains checked %d, %d could not be read',
                        $domains['ran'],
                        $domains['failed'],
                    ),
                );
            }
        } catch (Throwable $e) {
            logProblem('warning', 'domain checks: ' . $e->getMessage());
            $say('domains could not run: ' . $e->getMessage());
        }
    }

    if ($wants('--alerts')) {
        $raised = evaluateAlerts($config, $pdo);
        foreach ($raised as $alert) {
            $say(
                'ALERT   ' .
                    strtoupper($alert['severity']) .
                    ' ' .
                    $alert['subject'],
            );
        }
        if (!$raised) {
            $say('alerts  nothing tripped');
        }
    }

    if ($wants('--prune')) {
        $say('prune   ' . prune($config, $pdo));
    }

    if ($wants('--vitals')) {
        try {
            $delivery = peerTransportSend($config, $pdo);
            if ($delivery !== null) {
                $say(
                    $delivery['skipped'] && $delivery['delivered'] === 0
                        ? 'remote  next new delivery is rate-limited'
                        : ($delivery['queued']
                            ? 'remote  queued vital reading for ' .
                                $delivery['peer']
                            : 'remote  delivered ' .
                                $delivery['delivered'] .
                                ' vital reading(s) to ' .
                                $delivery['peer']),
                );
            }
        } catch (Throwable $e) {
            // Delivery is an extra path: an offline controller must never make
            // a successful local collection look like missing local data.
            logProblem('warning', 'remote delivery: ' . $e->getMessage());
            $say('remote  could not deliver: ' . $e->getMessage());
        }
    }

    /*
     * The scheduled summaries, on whichever run happens to be first past due.
     *
     * Not on its own cron line: another line is another thing to set up
     * correctly, and this already runs every few minutes. Due-ness is measured
     * from the last report rather than from a clock, so a machine that was off
     * overnight writes one when it comes back rather than skipping the day.
     */
    if ($wants('--collect') || $wants('--report')) {
        $say('report  ' . aiReports($config, $pdo));
    }
    $recordRun($runPdo, true);
} catch (Throwable $e) {
    $recordRun($runPdo, false, $e->getMessage());
    // To the log as well as to stderr: cron's stderr goes to whatever the
    // crontab line redirects to, which is often nowhere.
    logProblem(
        'error',
        'cron: ' .
            $e->getMessage() .
            ' in ' .
            $e->getFile() .
            ':' .
            $e->getLine(),
    );
    fwrite(STDERR, 'error: ' . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Writes and sends whichever summaries are due.
 *
 * Silent about the ones that are switched off, because most installs will have
 * all three off and a line saying so on every cron run is noise in a log
 * somebody redirects to a file.
 */
function aiReports(array $config, PDO $pdo): string
{
    $settings = aiAlertSettings($config);
    $done = [];

    foreach (['daily', 'weekly', 'monthly'] as $period) {
        if (!$settings[$period] || !aiReportDue($pdo, $period)) {
            continue;
        }

        $report = aiWriteReport($config, $pdo, $period);
        if ($report === null) {
            $done[] = $period . ' could not be written';
            continue;
        }

        $sent = sendReportEmail($config, $pdo, $period, $report);
        $done[] = $period . ($sent ? ' written and sent' : ' written');
    }

    return $done === [] ? 'nothing due' : implode('; ', $done);
}

/**
 * Emails one summary.
 *
 * To its own recipients where they are set, and otherwise to whoever gets the
 * alerts - a summary and an alert are different kinds of message and somebody
 * may reasonably want them in different places, but requiring a second address
 * before the feature works at all would be a needless step.
 */
function sendReportEmail(
    array $config,
    PDO $pdo,
    string $period,
    array $report,
): bool {
    $settings = aiAlertSettings($config);
    $to =
        trim($settings['report_to']) !== ''
            ? $settings['report_to']
            : (string) ($config['alerts']['to'] ?? '');

    $recipients = alertRecipients($to);

    if ($recipients === [] || !function_exists('mail')) {
        return false;
    }

    $host = php_uname('n');
    $label = aiReportPeriods()[$period]['label'] ?? ucfirst($period);

    $body = ['Zogograph on ' . $host, '', $report['body'], ''];

    $link = dashboardLink($config, 'sys-reports');
    if ($link !== null) {
        $body[] = 'Every summary: ' . $link;
    }

    $body[] = '';
    $body[] =
        'Written by ' .
        aiPersona($config)['name'] .
        ', which is a language model ' .
        'reading the figures. The figures themselves are on the dashboard.';

    $headers = ['Auto-Submitted: auto-generated'];
    $from = trim((string) ($config['alerts']['from'] ?? ''));
    if ($from !== '') {
        $headers[] = 'From: ' . $from;
    }

    /*
     * One message per recipient, the same as the alert mail does it.
     *
     * A single mail() with several addresses fails as a unit - one bad mailbox
     * and nobody hears - and it also shows everybody the whole list.
     */
    $sent = false;
    foreach ($recipients as $who) {
        $sent =
            @mail(
                $who,
                sprintf('[%s] %s summary', $host, $label),
                implode("\n", $body),
                implode("\r\n", $headers),
            ) || $sent;
    }

    $pdo->prepare(
        'UPDATE ai_reports SET emailed_at = ?, email_error = ? WHERE id = ?',
    )->execute([
        $sent ? time() : null,
        $sent ? null : 'mail() refused it',
        $report['id'],
    ]);

    return (bool) $sent;
}

/**
 * Drops readings that are too old to be interesting.
 *
 * Vitals once a minute is half a million rows a year. SQLite does not care, but
 * a chart does, and so does the phone loading it. The recent past is kept at
 * full resolution; the older past is thinned to one reading an hour rather than
 * deleted, because "when did this start" is a question about months ago.
 */
function prune(array $config, PDO $pdo): string
{
    $rules = retentionRules($config);
    $now = time();

    $pdo->beginTransaction();

    /*
     * Readings are compacted rather than thinned.
     *
     * The old rule kept the first reading of each hour and dropped the other
     * fifty-nine, so a spike at twenty past survived one time in sixty - and a
     * spike is most of the reason to keep a year of history at all. See
     * lib/retention.php: a compacted row now carries the worst moment in its
     * bucket rather than an arbitrary one.
     */
    $vitals = compactVitals($pdo, $rules, $now);

    $ancient = fn(string $key): ?int => (int) $rules[$key] > 0
        ? $now - (int) $rules[$key] * 86400
        : null;

    $scanCount = 0;
    if (($cut = $ancient('scan_days')) !== null) {
        // Scans cascade to their totals and their path sizes.
        $scans = $pdo->prepare('DELETE FROM scans WHERE started_at < ?');
        $scans->execute([$cut]);
        $scanCount = $scans->rowCount();
    }

    $processCount = 0;
    if (($cut = $ancient('process_days')) !== null) {
        $processes = $pdo->prepare(
            'DELETE FROM process_samples WHERE taken_at < ?',
        );
        $processes->execute([$cut]);
        $processCount = $processes->rowCount();
    }

    /*
     * Incidents are kept for ever by default, and separately from everything
     * else, because they are the evidence. One per alerting evaluation rather
     * than one per reading, so even a bad year is a few hundred rows.
     */
    $incidentCount =
        (int) $rules['incident_days'] > 0
            ? pruneIncidents($pdo, (int) $rules['incident_days'])
            : 0;

    // A path nothing refers to any more is history nobody can ask about.
    $paths = $pdo->exec(
        'DELETE FROM paths
         WHERE id NOT IN (SELECT path_id FROM path_sizes)
           AND id NOT IN (SELECT path_id FROM alerts WHERE path_id IS NOT NULL)',
    );

    $pdo->commit();

    // After the commit, never during: SQLite will not VACUUM inside a
    // transaction, and it needs room for a second copy while it runs.
    $reclaimed = reclaimSpace($pdo);

    return sprintf(
        'compacted %d buckets (5m %d, 1h %d, 1d %d) removing %d readings; ' .
            'kept %d around alerts; dropped %d scans, %d paths, %d process rows, %d incidents; %s',
        $vitals['compacted'],
        $vitals['tiers']['five_minute'] ?? 0,
        $vitals['tiers']['hourly'] ?? 0,
        $vitals['tiers']['daily'] ?? 0,
        $vitals['removed'],
        protectedReadings($pdo, max(0, (int) $rules['protect_hours']) * 3600),
        $scanCount,
        (int) $paths,
        $processCount,
        $incidentCount,
        $reclaimed,
    );
}

