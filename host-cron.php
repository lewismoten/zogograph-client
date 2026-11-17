<?php
declare(strict_types=1);

/*
 * The host-side collector. Runs as root, out of WHM's crontab.
 *
 *   * * * * * /usr/local/bin/php /home/.../host-cron.php --collect --prune --quiet
 *
 * Command line only: it is refused over HTTP below, and .htaccess denies it as
 * well. A root-privileged endpoint reachable from a browser is not something to
 * leave to one layer of protection.
 *
 * It reads /proc, /etc/passwd, Apache's log directory and - if CloudLinux is
 * installed - lveinfo, then writes to its own database. It never touches the
 * account's database, and the account's collector never touches this one.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit("host-cron.php runs from the command line only.\n");
}

require_once __DIR__ . '/lib/scans.php';
require_once __DIR__ . '/lib/rdns.php';
require_once __DIR__ . '/lib/boot.php';
require_once __DIR__ . '/lib/setupstore.php';

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Not configured: lib/config.php is missing.\n");
    exit(1);
}

$config = loadConfiguredSettings($configFile);
date_default_timezone_set($config['timezone'] ?? 'UTC');
define('DISKMON_LOG', dirname((string) $config['database']) . '/error.log');

require_once __DIR__ . '/lib/db.php';
// For humanBytes, the same way cron.php gets it.
require_once __DIR__ . '/lib/alerts.php';
require_once __DIR__ . '/lib/host.php';
require_once __DIR__ . '/lib/hostdisk.php';
require_once __DIR__ . '/lib/backups.php';
require_once __DIR__ . '/lib/mountinfo.php';
require_once __DIR__ . '/lib/capabilities.php';
// reclaimSpace, so a prune gives the disk back rather than only marking pages free.
require_once __DIR__ . '/lib/retention.php';
// The outbound budget, so registries are not asked twenty things at once.
require_once __DIR__ . '/lib/external.php';
require_once __DIR__ . '/lib/updates.php';

$args = array_slice($argv, 1);
$quiet = in_array('--quiet', $args, true);
$want = fn(string $flag): bool => in_array($flag, $args, true);
$runStarted = microtime(true);
$runPdo = null;
$runJobs = array_values(
    array_filter($args, fn(string $arg): bool => $arg !== '--quiet'),
);

$recordRun = function (?PDO $pdo, bool $ok, ?string $error = null) use (
    $runStarted,
    &$runJobs,
): void {
    if ($pdo === null) {
        return;
    }
    try {
        $pdo->prepare(
            'INSERT INTO host_collector_runs (started_at, jobs, seconds, ok, error)
             VALUES (?, ?, ?, ?, ?)',
        )->execute([
            (int) $runStarted,
            $runJobs === [] ? 'normal' : implode(' · ', array_unique($runJobs)),
            round(microtime(true) - $runStarted, 3),
            $ok ? 1 : 0,
            $error,
        ]);
    } catch (Throwable) {
        // Recording cannot make a root collection fail.
    }
};

// Nothing named means collect, which is what a crontab line usually wants.
$doCollect =
    $want('--collect') ||
    (!$want('--prune') &&
        !$want('--show') &&
        !$want('--disk') &&
        !$want('--capabilities'));

/*
 * The disk walk is its own mode, and deliberately not part of --collect.
 *
 * Collection runs every minute and must stay cheap. Walking the root
 * filesystem takes tens of seconds and answers a question that does not change
 * minute to minute, so it belongs on its own much slower schedule - or on a
 * command somebody types when 72 GB has gone missing and they want to know
 * where. Both are served by giving it a flag of its own.
 */
$doDisk = $want('--disk');
$doCapabilities = $want('--capabilities');
$doUpdates = $want('--updates');

/*
 * Or because somebody asked for one from the dashboard.
 *
 * The dashboard runs as the account and cannot walk root's disk, so its button
 * leaves a request in the account database and this picks it up on the next
 * ordinary collection. Checked before the run rather than after, so a walk that
 * takes forty seconds still counts as answering the request that started it.
 */
$requestedAt = requestedHostWalk($config);
$requestedTasks = requestedHostTasks($config);

$say = function (string $line) use ($quiet): void {
    if (!$quiet) {
        echo $line, "\n";
    }
};

/*
 * A warning rather than a refusal.
 *
 * Run as an ordinary user this still works - it just sees what that user can
 * see, which is the situation it exists to escape. Saying so is more useful
 * than exiting, because "I ran it and it collected nothing interesting" is
 * otherwise very hard to explain.
 */
$root = function_exists('posix_geteuid')
    ? posix_geteuid() === 0
    : trim((string) shell_exec('id -u')) === '0';
if (!$root) {
    $say(
        'Warning: not running as root. Other users\' processes, slab detail and',
    );
    $say(
        '         Apache\'s logs will be invisible - which is the whole point of this.',
    );
}

if (!($config['host']['enabled'] ?? true)) {
    fwrite(
        STDERR,
        "Host collection is turned off: set host.enabled to true in lib/config.php.\n",
    );
    exit(1);
}

/*
 * One writer at a time, even though the schema only ever expected one.
 *
 * WAL mode lets readers run beside a writer without blocking, which is why
 * ordinary dashboard traffic never collides with this. What it does not cover
 * is two copies of *this* script: a run that outlasts its own cron interval -
 * a slow disk walk, a package manager update, a host that is simply busy -
 * and the next tick starts a second one before the first exits. Two root
 * writers against one file is a real write-write collision, not a WAL
 * reader/writer non-event, and it is the thing that actually produces
 * "database is locked" here.
 *
 * A flock held for the process's lifetime is the standard fix and is chosen
 * over a PID file on purpose: a PID file believes whatever it last wrote, so
 * a run that is killed without cleaning up leaves a lock nothing will ever
 * clear. flock releases the instant the holding process exits for any reason,
 * including a crash or `kill -9`, because the kernel owns the lock rather
 * than the file's contents.
 *
 * Skipping a tick rather than queuing: a --collect that waited behind a slow
 * --disk walk would write its reading late and under whatever load caused the
 * walk to run long in the first place, which is exactly the wrong moment to
 * get a wrong number. A reading skipped once a conflict actually happens is
 * honest; a reading taken late and mislabeled as now is not.
 */
$lockFile = dirname(hostDatabasePath($config)) . '/host-cron.lock';
$lockHandle = @fopen($lockFile, 'c');
if ($lockHandle === false) {
    fwrite(STDERR, "Unable to open $lockFile for the run lock.\n");
    exit(1);
}
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    // Not an error: the previous run is still going, and this tick yields to it.
    $say('Skipped: a previous host-cron.php run is still in progress.');
    exit(0);
}
@chmod($lockFile, 0600);
register_shutdown_function(static function () use ($lockHandle): void {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
});

try {
    $pdo = hostDb($config, true);
    $runPdo = $pdo;
    if ($pdo === null) {
        throw new RuntimeException(
            'the host database could not be opened for writing',
        );
    }

    /*
     * Collection failing must not strand a requested disk walk.
     *
     * They are independent jobs that happen to share a cron line. A reading
     * needs /proc and a walk does not, so a host where collection is broken is
     * exactly the host somebody is trying to investigate - and having the
     * button silently do nothing there would be the worst possible time for it.
     */
    $collectFailure = null;

    /*
     * A per-minute crontab line runs this five times for every reading the
     * schema was designed to keep.
     *
     * hostCronLine() above generates "every five minutes" and says why - a
     * full reading walks every process, every slab cache and every service
     * unit, which is not free on a busy box and does not change meaningfully
     * inside a minute. A crontab edited to run it more often than that (by
     * hand, by a templated WHM task, by copying a line from somewhere that
     * used to want faster vitals) quietly multiplies both the write rate
     * against host.db - more chances for this run to overlap the next one -
     * and its steady-state size, since every one of those tables keeps rows
     * for a number of *days*, not a number of samples.
     *
     * Gated here rather than only documented, because a crontab is easy to
     * get wrong and hard to audit from inside the product that suffers for
     * it. `--force` exists for the person running this by hand who wants a
     * reading right now and does not care that one was taken forty seconds
     * ago.
     */
    $collectIntervalSeconds = (int)($config['host']['collect_interval_seconds'] ?? 300);
    $lastSampleAt = (int)($pdo->query('SELECT COALESCE(MAX(taken_at), 0) FROM host_samples')->fetchColumn());
    $dueForCollect = $want('--force') || (time() - $lastSampleAt) >= $collectIntervalSeconds;

    if ($doCollect && !$dueForCollect) {
        $say(sprintf(
            'Skipped collection: last reading was %ds ago, interval is %ds. Use --force to override.',
            time() - $lastSampleAt,
            $collectIntervalSeconds
        ));
    }

    if ($doCollect && $dueForCollect) {
        try {
            $result = hostTakeReading($config, $pdo, 'cron');
            // Ownership is re-applied every run, not only at creation: WAL and
            // shared-memory files are recreated by SQLite and come back owned by
            // root, and the dashboard cannot read a WAL database without them.
            hostHandOver($config, hostDatabasePath($config));

            $say(
                sprintf(
                    'Recorded sample %d: %d processes, %d users, %d services, %d slab caches, ' .
                        '%d files, %d disks, %d cores%s.',
                    $result['sample_id'],
                    $result['processes'],
                    $result['users'],
                    $result['services'],
                    $result['slab'],
                    $result['files'],
                    $result['disks'],
                    $result['cores'],
                    $result['lve'] === null
                        ? ''
                        : ", {$result['lve']} LVE rows",
                ),
            );

            /*
             * Said out loud when a source is missing, rather than left as a zero in
             * a count nobody reads. Running the collector and seeing nothing appear
             * on the dashboard is the situation this line exists to explain.
             */
            if ($result['disks'] === 0) {
                $say(
                    '  No disk devices: /proc/diskstats could not be read, even as root.',
                );
            }
            /*
             * The per-core table is what tells one pinned core apart from an
             * evenly busy machine, and a kernel without the cpuN lines leaves
             * the whole "Each core" page blank with nothing to explain it.
             */
            if ($result['cores'] === 0) {
                $say(
                    '  No per-core lines in /proc/stat: the "Each core" page will be empty.',
                );
            }
            $say(
                sprintf(
                    '  Kernel %s. Device counters %s, pressure stall info %s.',
                    php_uname('r'),
                    $result['disks'] > 0 ? 'readable' : 'NOT readable',
                    $result['psi'] ? 'available' : 'NOT available',
                ),
            );
            if (!$result['psi'] && $result['psi_reason'] !== '') {
                $say('  ' . $result['psi_reason']);
            }
            if ($result['unaccounted'] !== null) {
                $say(
                    sprintf(
                        'Other kernel/system memory: %s (not in any process, cache or slab).',
                        humanBytes((int) $result['unaccounted']),
                    ),
                );
            }
        } catch (Throwable $e) {
            $collectFailure = $e->getMessage();
        }
    }

    if ($collectFailure !== null) {
        $say('Collection failed: ' . $collectFailure);
    }

    /*
     * A packet capture, if one was asked for.
     *
     * Never on a schedule. It opens a live interface, runs for as long as it was
     * asked to, and is the only thing this collector does that could itself be
     * felt on a busy machine - so it happens once per request and the request is
     * satisfied by a capture recorded after it was made.
     */
    foreach ($requestedTasks as $task => $requested) {
        $capture = parseCaptureTask((string) $task);
        if ($capture === null) {
            continue;
        }

        $done = 0;
        try {
            $stmt = $pdo->prepare(
                'SELECT MAX(taken_at) FROM host_captures WHERE interface = ? AND filter = ?',
            );
            $stmt->execute([$capture['interface'], $capture['filter']]);
            $done = (int) $stmt->fetchColumn();
        } catch (Throwable) {
            $done = 0;
        }

        if ($done >= (int) $requested) {
            continue;
        }

        $seconds = (int) ($config['network']['capture_seconds'] ?? 20);
        $say(
            sprintf(
                'Capturing %ds of %s on %s%s (requested %s ago)...',
                $seconds,
                $capture['filter'],
                $capture['interface'],
                !empty($capture['read_asked'])
                    ? ', reading what was asked for'
                    : '',
                humanDuration(time() - (int) $requested),
            ),
        );

        $result = captureTraffic(
            $capture['interface'],
            $seconds,
            $capture['filter'],
            null,
            (bool) ($capture['read_asked'] ?? false),
        );
        $runJobs[] = 'capture:' . $capture['filter'];
        storeCapture($pdo, $result);
        hostHandOver($config, hostDatabasePath($config));

        $say(
            $result['ok']
                ? sprintf(
                    '  %d packets from %d peers in %ss.',
                    $result['packets'],
                    $result['distinct_peers'],
                    $result['seconds'],
                )
                : '  ' . $result['reason'],
        );
    }

    /*
     * Questions the account could not answer for itself.
     *
     * whois and dig are often missing from inside a cage and present on the
     * host. The account leaves a request naming one of a fixed set of verbs and
     * a domain; this runs it and stores the output verbatim.
     */
    /*
     * One outbound lookup per run, oldest request first.
     *
     * These go to registries and public resolvers, which defend themselves. A
     * burst of twenty gets this address rate-limited, and the result is twenty
     * domains reported as unknown - one self-inflicted problem wearing the
     * costume of twenty real ones. Oldest first, so an impatient person cannot
     * starve the queue by asking repeatedly.
     */
    $asks = [];
    foreach ($requestedTasks as $task => $requested) {
        $parsed = parseAskTask((string) $task);
        if ($parsed !== null) {
            $asks[] = $parsed + [
                'task' => (string) $task,
                'requested' => (int) $requested,
            ];
        }
    }
    usort(
        $asks,
        fn(array $a, array $b): int => $a['requested'] <=> $b['requested'],
    );

    $askBudget = externalBudget($config, 'whois');
    $askedThisRun = 0;

    foreach ($asks as $entry) {
        if ($askedThisRun >= $askBudget) {
            $say(
                sprintf(
                    '%d more question(s) waiting; they run next time.',
                    count($asks) - $askedThisRun,
                ),
            );
            break;
        }

        $ask = ['verb' => $entry['verb'], 'argument' => $entry['argument']];
        $task = $entry['task'];
        $requested = $entry['requested'];

        $done = 0;
        try {
            $stmt = $pdo->prepare(
                'SELECT MAX(answered_at) FROM host_answers WHERE verb = ? AND argument = ?',
            );
            $stmt->execute([$ask['verb'], $ask['argument']]);
            $done = (int) $stmt->fetchColumn();
        } catch (Throwable) {
            $done = 0;
        }

        if ($done >= (int) $requested) {
            continue;
        }

        $say(sprintf('Answering %s for %s...', $ask['verb'], $ask['argument']));
        $answer = runHostAsk(
            $pdo,
            $ask['verb'],
            $ask['argument'],
            (int) $requested,
        );
        $runJobs[] = 'ask:' . $ask['verb'];
        $askedThisRun++;
        hostHandOver($config, hostDatabasePath($config));
        $say(
            '  ' .
                ($answer['ok']
                    ? strlen((string) $answer['text']) . ' bytes'
                    : $answer['error']),
        );
    }

    /*
     * One reverse lookup, if any are waiting.
     *
     * Reverse DNS needs no privileges, so the account resolves these itself
     * most of the time - this is for the case where the account's resolver is
     * blocked and the host's is not, which inside a cage is common enough to
     * be worth covering.
     *
     * One per run, like every other outbound lookup here, and the answer is
     * left in this database for the account to collect. The account's queue is
     * only ever read from here.
     */
    $lookup = rdnsHostDrain($config, $pdo);
    if ($lookup !== null) {
        hostHandOver($config, hostDatabasePath($config));
        $say(
            sprintf(
                'Resolved %s -> %s (%dms).',
                $lookup['address'],
                $lookup['name'] ?? 'no name',
                $lookup['ms'],
            ),
        );
    }

    /*
     * A capability sweep, if one was asked for.
     *
     * Requests to expose a program count as well: whether it worked is only
     * visible by re-probing, so the sweep is the answer to that request too.
     */
    $capabilityRequests = array_filter(
        $requestedTasks,
        fn(string $task): bool => $task === 'capabilities' ||
            str_starts_with($task, 'expose:'),
        ARRAY_FILTER_USE_KEY,
    );

    /*
     * Captured before the sweep writes its own row.
     *
     * Everything below asks "was this requested since the last sweep", and
     * storeHostCapabilities() inserts a run row partway through - so reading
     * this afterwards would compare every request against the sweep that is
     * answering it and conclude that none of them are outstanding.
     */
    $previousRun = 0;
    try {
        $previousRun = (int) $pdo
            ->query('SELECT MAX(ran_at) FROM host_capability_runs')
            ->fetchColumn();
    } catch (Throwable) {
        $previousRun = 0;
    }

    /*
     * Or because it is simply due.
     *
     * The crontab line runs this every few minutes and the expensive scans
     * decide for themselves whether their turn has come - which is what makes
     * "daily" expressible without a second crontab entry per scan.
     */
    /*
     * Both reasons are consulted, always.
     *
     * These used to be an either/or: the schedule was only checked when there
     * were no request rows at all. Request rows are permanent - nothing deletes
     * them but somebody pressing cancel - so a single "expose:whois" from weeks
     * ago meant the scheduled sweep was skipped on that install for ever. The
     * collector ran every five minutes, said nothing, and the capability list
     * silently froze at whatever it said the day that request was made. That is
     * how a machine ends up reporting `ss` as absent months after it was
     * installed.
     */
    if (!$doCapabilities) {
        $when = hostScanDue($config, $pdo, 'host-capabilities');
        if ($when['due']) {
            $say('A capability probe is due.');
            $doCapabilities = true;
        }
    }

    if (!$doCapabilities && $capabilityRequests !== []) {
        if (max($capabilityRequests) >= $previousRun) {
            $doCapabilities = true;
            $say(
                sprintf(
                    '%d capability request(s) outstanding. Running a sweep.',
                    count($capabilityRequests),
                ),
            );
        }
    }

    if ($doCapabilities) {
        $runJobs[] = 'capabilities';
        $user = (string) ($config['host']['owner'] ?? '');
        $cagefs = cagefsStatus($user !== '' ? $user : null);

        $say($cagefs['note']);

        $probe = probeCapabilities($config, null, true, true);

        /*
         * The same list asked again from inside the account's cage.
         *
         * This is the only measurement that distinguishes "installed" from
         * "reachable by the account", and it is the entire reason the host side
         * of this check exists. Without CageFS the two are the same and the
         * second probe is skipped.
         */
        $inCage = [];
        if (
            $cagefs['installed'] &&
            $user !== '' &&
            ($cagefs['enabled'] ?? false)
        ) {
            $inCage = probePaths(array_column($probe['rows'], 'name'), $user);
            $say(
                sprintf(
                    '  %d of %d visible inside %s\'s cage.',
                    count($inCage),
                    count($probe['rows']),
                    $user,
                ),
            );
        }

        storeHostCapabilities(
            $pdo,
            $probe,
            $inCage,
            $user !== '' ? $user : null,
        );

        $onHost = count(
            array_filter(
                $probe['rows'],
                fn(array $r): bool => $r['path'] !== '',
            ),
        );
        $say(
            sprintf(
                'Capabilities: %d of %d present on the host, checked in %ss.',
                $onHost,
                count($probe['rows']),
                $probe['seconds'],
            ),
        );

        // The mount table goes with it: both answer "what can this account see".
        $mounts = readMountInfo();
        if ($mounts !== []) {
            $at = time();
            $pdo->prepare('DELETE FROM host_mountinfo')->execute();
            $insert = $pdo->prepare(
                'INSERT INTO host_mountinfo (taken_at, mount_id, parent_id, device, fs_root,
                    mount_point, fs_type, source, options, propagation, read_only, bind)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            );
            foreach ($mounts as $mount) {
                $insert->execute([
                    $at,
                    $mount['mount_id'],
                    $mount['parent_id'],
                    $mount['device'],
                    $mount['fs_root'],
                    $mount['mount_point'],
                    $mount['fs_type'],
                    $mount['source'],
                    $mount['options'],
                    $mount['propagation'],
                    $mount['read_only'] ? 1 : 0,
                    $mount['bind'] ? 1 : 0,
                ]);
            }

            $cage = cagefsMounts($mounts);
            $say(
                sprintf(
                    'Mount table: %d mounts recorded. %s',
                    count($mounts),
                    $cage['note'],
                ),
            );
        }

        /*
         * Requests to expose a program are reported, not performed.
         *
         * Exposing one means editing /etc/cagefs/conf.d and remounting every
         * caged account, which is a real change to how the machine works. A web
         * button that did that silently would be a privilege escalation with a
         * friendly label, so the commands are printed and run only when the
         * config explicitly allows it.
         */
        foreach ($capabilityRequests as $task => $requestedAtTask) {
            if (!str_starts_with($task, 'expose:')) {
                continue;
            }

            /*
             * Only requests made since the last sweep.
             *
             * The request row stays in the account's database - the collector
             * cannot write there to mark it done - so without this every
             * expose request ever made is reprinted on every run, for ever.
             * A reminder that cannot be dismissed stops being read.
             */
            if ($requestedAtTask < $previousRun) {
                continue;
            }

            $program = substr($task, strlen('expose:'));
            $path = null;
            foreach ($probe['rows'] as $row) {
                if ($row['name'] === $program) {
                    $path = $row['path'];
                    break;
                }
            }

            if ($path === null || $path === '') {
                $say(
                    sprintf(
                        'Cannot expose %s: it is not installed on this host.',
                        $program,
                    ),
                );
                continue;
            }

            /*
             * If it is already visible, say so instead of giving instructions.
             *
             * The sweep re-probes inside the cage, so by the time this runs we
             * know whether the exposure worked - and repeating the commands for
             * something already done is how somebody ends up adding a duplicate
             * line to cagefs conf.d.
             */
            if (isset($inCage[$program])) {
                $say(
                    sprintf(
                        '%s is already visible to %s at %s - nothing to do.',
                        $program,
                        $user ?: 'the account',
                        $inCage[$program],
                    ),
                );
                continue;
            }

            $snippet = cagefsExposeSnippet($program, $path);

            $say(
                sprintf(
                    'Requested: expose %s (%s) to %s.',
                    $program,
                    $path,
                    $user ?: 'the account',
                ),
            );
            $say('  To do it, as root:');
            $say('');
            // Printed flush left: this is meant to be copied and pasted, and an
            // indented heredoc body writes its indentation into the file, where
            // a leading space means a continuation line.
            foreach ($snippet['commands'] as $line) {
                $say($line);
            }
            $say('');
            $say(
                '  These files are INI, so the section header matters - a bare path makes ' .
                    'cagefsctl refuse to run at all.',
            );
            $say(
                '  Not done automatically: it changes what every caged account can run.',
            );
        }
    }

    if (
        !$doDisk &&
        $requestedAt !== null &&
        hostWalkPending($pdo, $requestedAt)
    ) {
        $doDisk = true;
        $say(
            sprintf(
                'A disk walk was requested from the dashboard %s ago. Running it now.',
                humanDuration(max(0, time() - $requestedAt)),
            ),
        );
    }

    if ($doDisk) {
        $runJobs[] = 'disk-walk';
        $at = time();

        $mounts = hostMounts();
        $insert = $pdo->prepare(
            'INSERT INTO host_mounts (taken_at, device, mount, type, total_bytes,
                used_bytes, free_bytes, used_percent, also_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        );
        foreach ($mounts as $row) {
            $insert->execute([
                $at,
                $row['device'],
                $row['mount'],
                $row['type'],
                $row['total_bytes'],
                $row['used_bytes'],
                $row['free_bytes'],
                $row['used_percent'],
                $row['also_at'] ? implode(' ', $row['also_at']) : null,
            ]);
        }
        if ($mounts === []) {
            /*
             * No filesystems is not "the disk is fine", it is "the question
             * could not be asked" - and that is the first question of the
             * three, so saying which it is matters.
             */
            $say(
                'No filesystems could be read. /proc/mounts is where this comes from, so ' .
                    'either this is not Linux or /proc is not mounted.',
            );
        } else {
            $say(
                sprintf(
                    'Recorded %d filesystem(s), fullest first:',
                    count($mounts),
                ),
            );
        }
        foreach (array_slice($mounts, 0, 6) as $row) {
            $say(
                sprintf(
                    '  %-28s %6s used of %-8s (%.0f%%) on %s',
                    $row['mount'],
                    humanBytes($row['used_bytes']),
                    humanBytes($row['total_bytes']),
                    $row['used_percent'],
                    $row['device'],
                ),
            );
        }

        $root = (string) ($config['host']['walk_root'] ?? '/');
        $skip = array_map('strval', $config['host']['walk_skip'] ?? []);
        $budget =
            (float) ($config['host']['walk_seconds'] ?? HOSTDISK_WALK_SECONDS);

        /*
         * Backup roots keep all of their children, however small.
         *
         * They have a panel of their own, and a destination that holds a few
         * hundred megabytes is not in the top four hundred directories on the
         * machine - so without this it is dropped and the panel shows one
         * destination as though it were all of them.
         */
        $keepChildrenOf = backupRoots($config);

        $say(sprintf('Walking %s (up to %.0fs)...', $root, $budget));
        $walk = hostDirectoryUsage(
            $root,
            $budget,
            $skip,
            null,
            $keepChildrenOf,
        );

        $dirs = $pdo->prepare(
            'INSERT INTO host_dirs (taken_at, path, depth, bytes, files, measured)
             VALUES (?, ?, ?, ?, ?, ?)',
        );
        foreach ($walk['dirs'] as $row) {
            $dirs->execute([
                $at,
                $row['path'],
                $row['depth'],
                $row['bytes'],
                $row['files'],
                $row['measured'] ?? true ? 1 : 0,
            ]);
        }
        if (($walk['unmeasured'] ?? 0) > 0) {
            $say(
                sprintf(
                    '  %s directories were not finished before the time ran out; the %d ' .
                        'shallowest are recorded with no size rather than dropped, because an ' .
                        'unopened directory is not an empty one.',
                    number_format(
                        $walk['unmeasured_total'] ?? $walk['unmeasured'],
                    ),
                    $walk['unmeasured'],
                ),
            );
        }

        $say(
            sprintf(
                '  %d directories kept from %s scanned in %ss%s.',
                count($walk['dirs']),
                number_format($walk['scanned']),
                $walk['seconds'],
                $walk['complete']
                    ? ''
                    : ' - STOPPED EARLY, the totals are lower bounds',
            ),
        );
        foreach (array_slice($walk['dirs'], 0, 8) as $row) {
            $say(
                sprintf('  %10s  %s', humanBytes($row['bytes']), $row['path']),
            );
        }

        $deleted = hostDeletedOpen();
        $del = $pdo->prepare(
            'INSERT INTO host_deleted (taken_at, pid, process, path, bytes, holders)
             VALUES (?, ?, ?, ?, ?, ?)',
        );
        foreach ($deleted['files'] as $row) {
            $del->execute([
                $at,
                $row['pid'],
                $row['process'],
                $row['path'],
                $row['bytes'],
                $row['holders'],
            ]);
        }

        if ($deleted['bytes'] > 0) {
            $say(
                sprintf(
                    'Deleted but still open: %s held by %d file(s). This space is gone from df ' .
                        'and invisible to du.',
                    humanBytes($deleted['bytes']),
                    count($deleted['files']),
                ),
            );
            foreach (array_slice($deleted['files'], 0, 8) as $row) {
                $say(
                    sprintf(
                        '  %10s  %s (pid %d, %s)',
                        humanBytes($row['bytes']),
                        $row['path'],
                        $row['pid'],
                        $row['process'],
                    ),
                );
            }
        } else {
            $say('No deleted-but-open files holding space.');
        }
        if ($deleted['reason'] !== null) {
            $say('  ' . $deleted['reason']);
        }

        /*
         * JetBackup's own account of what it holds, alongside the sizes.
         *
         * Asked here rather than on every collection: it shells out to a tool
         * that talks to a database, and the answer changes when a backup runs
         * rather than minute to minute.
         */
        $jet = jetBackupInventory();
        if ($jet['available']) {
            $ins = $pdo->prepare(
                'INSERT INTO host_backups (taken_at, account, created_at, type, bytes,
                    destination, path, backup_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            );
            foreach ($jet['backups'] as $backup) {
                $ins->execute([
                    $at,
                    is_string($backup['account'] ?? null)
                        ? $backup['account']
                        : null,
                    is_numeric($backup['created'] ?? null)
                        ? (int) $backup['created']
                        : null,
                    is_string($backup['type'] ?? null) ? $backup['type'] : null,
                    is_numeric($backup['size'] ?? null)
                        ? (int) $backup['size']
                        : null,
                    is_string($backup['destination'] ?? null)
                        ? $backup['destination']
                        : null,
                    is_string($backup['path'] ?? null) ? $backup['path'] : null,
                    is_string($backup['id'] ?? null) ? $backup['id'] : null,
                ]);
            }
            $say(
                sprintf(
                    'JetBackup: %d backups recorded from its own metadata.',
                    count($jet['backups']),
                ),
            );
        } else {
            $say('JetBackup: ' . $jet['reason']);
        }

        $pdo->prepare(
            'INSERT INTO host_walks (taken_at, root, complete, seconds, dirs_scanned,
                deleted_bytes, deleted_readable, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $at,
            $root,
            $walk['complete'] ? 1 : 0,
            $walk['seconds'],
            $walk['scanned'],
            $deleted['bytes'],
            $deleted['readable'] ? 1 : 0,
            $deleted['reason'],
        ]);
    }

    if ($want('--prune')) {
        $days = (int) ($config['host']['keep_days'] ?? 14);
        $say(
            sprintf(
                'Pruned %d readings older than %d days.',
                hostPrune($pdo, $days),
                $days,
            ),
        );

        // The service series keeps its own, much longer clock: those rows are
        // tiny and their whole value is being older than the detail tables.
        $serviceDays = (int) ($config['host']['keep_service_days'] ?? 90);
        $say(
            sprintf(
                'Pruned %d service readings older than %d days.',
                hostPruneServices($pdo, $serviceDays),
                $serviceDays,
            ),
        );
        $say(
            sprintf(
                'Pruned %d old operational-run records.',
                hostPruneOperationalRuns($pdo),
            ),
        );

        /*
         * And give the space back, which deleting rows does not do.
         *
         * SQLite marks freed pages for reuse and leaves the file the size it
         * was. On a database pruned every night that is fine for a while and
         * then is not: the file settles at the size of the largest it has ever
         * been, which is the wrong number to have on a host somebody is
         * watching for disk space. reclaimSpace declines unless a quarter of
         * the file is free pages, so this is usually a couple of PRAGMAs and
         * no rewrite at all.
         */
        $say('Space:  ' . reclaimSpace($pdo));
    }

    /*
     * Deliberately late in the run.  A package transaction can be slow and
     * should not postpone the reading which tells us whether the host is
     * healthy.  It is interval-gated in updates.php, so the normal five-minute
     * collector line is enough; --updates merely bypasses that interval.
     */
    $updateRows = runSystemUpdates($config, $pdo, $doUpdates);
    foreach ($updateRows as $row) {
        $runJobs[] = 'updates:' . $row['stage'];
        $say(
            sprintf(
                'Updates %s via %s: %s%s.',
                $row['stage'],
                $row['manager'],
                $row['ok'] ? 'ok' : 'FAILED',
                $row['updates'] === null ? '' : ", {$row['updates']} available",
            ),
        );
    }
    if ($updateRows !== []) {
        hostHandOver($config, hostDatabasePath($config));
    }

    if ($want('--show')) {
        $row = $pdo
            ->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')
            ->fetch();
        if (!$row) {
            $say('Nothing recorded yet.');
        } else {
            foreach ($row as $key => $value) {
                printf("  %-16s %s\n", $key, (string) $value);
            }
        }
    }
    $recordRun($runPdo, true);
} catch (Throwable $e) {
    $recordRun($runPdo, false, $e->getMessage());
    // Same shape as cron.php's handler: the log wants a level and a string.
    logProblem(
        'error',
        'host-cron: ' .
            $e->getMessage() .
            ' in ' .
            $e->getFile() .
            ':' .
            $e->getLine(),
    );
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
    exit(1);
}
