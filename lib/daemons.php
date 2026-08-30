<?php
declare(strict_types=1);

/*
 * Whether the services are actually alive.
 *
 * Everything else on the host panel measures how much of the machine something
 * is using. None of it can tell you that MariaDB died at four this morning,
 * because a process that is not there uses no memory and no CPU and appears in
 * no ranking. The most important fact about a daemon is the one every other
 * panel is structurally incapable of reporting.
 *
 * Asked two ways, because neither works everywhere:
 *
 * systemd is the authority where it exists. It knows the difference between a
 * service that is stopped and one that was never installed, it counts restarts,
 * and it knows when the current run began - none of which can be recovered by
 * looking at a process list. Two shell calls answer for every service at once,
 * so the cost does not grow with the list.
 *
 * A process match is the fallback. It needs nothing but the process table the
 * collector has already read, so it costs nothing extra and works on a host
 * with no systemd at all. What it cannot do is tell "stopped" from "not
 * installed", and it says so rather than guessing.
 *
 * That distinction is the whole difficulty of this file. A service that is not
 * installed on this machine must never be reported as down: it would be a red
 * row that is red for ever, and a permanent alarm is one nobody reads. So
 * "absent" is a state of its own, separate from "stopped", and only a
 * transition out of "running" is ever worth waking somebody for.
 */

require_once __DIR__ . '/collect.php';

/**
 * The daemons worth watching, and what they are called.
 *
 * Several names each, because the same service answers to different ones
 * depending on the distribution: Apache is httpd on anything Red Hat and
 * apache2 on anything Debian, and a monitor that only knew one would report the
 * other as missing on half the machines it ran on.
 *
 * `units` are systemd unit names, matched exactly or by glob. `processes` are
 * executable names for the fallback. A definition matching nothing at all is
 * reported as absent, which on most machines is the right answer for most of
 * this list.
 */
function defaultDaemons(): array
{
    return [
        [
            'name' => 'httpd',
            'label' => 'Apache (httpd)',
            'units' => ['httpd', 'apache2'],
            'processes' => ['httpd', 'apache2'],
        ],

        [
            'name' => 'mariadb',
            'label' => 'MariaDB / MySQL',
            'units' => ['mariadb', 'mysql', 'mysqld'],
            'processes' => ['mariadbd', 'mysqld'],
        ],

        [
            'name' => 'forgejo',
            'label' => 'Forgejo',
            'units' => ['forgejo', 'gitea'],
            'processes' => ['forgejo', 'gitea'],
        ],

        [
            'name' => 'mongod',
            'label' => 'MongoDB',
            'units' => ['mongod'],
            'processes' => ['mongod'],
        ],

        [
            'name' => 'redis',
            'label' => 'Redis',
            'units' => ['redis', 'redis-server'],
            'processes' => ['redis-server', 'redis'],
        ],

        [
            'name' => 'icecast',
            'label' => 'Icecast',
            'units' => ['icecast', 'icecast2'],
            'processes' => ['icecast', 'icecast2'],
        ],

        /*
         * PHP-FPM is several services on cPanel - one per installed PHP
         * version - so this one expects to match many units and counts them.
         * "Running" here means at least one pool is up; the count is what tells
         * you a version disappeared.
         */
        [
            'name' => 'php-fpm',
            'label' => 'PHP-FPM',
            'units' => ['ea-php*-php-fpm', 'php*-fpm', 'php-fpm'],
            'processes' => ['php-fpm'],
            'many' => true,
        ],

        [
            'name' => 'exim',
            'label' => 'Mail — outgoing (Exim)',
            'units' => ['exim', 'postfix'],
            'processes' => ['exim', 'master'],
        ],

        [
            'name' => 'dovecot',
            'label' => 'Mail — mailboxes (Dovecot)',
            'units' => ['dovecot'],
            'processes' => ['dovecot'],
        ],

        [
            'name' => 'dns',
            'label' => 'DNS (BIND / PowerDNS)',
            'units' => ['named', 'bind9', 'pdns'],
            'processes' => ['named', 'pdns_server'],
        ],
    ];
}

/**
 * The configured list, or the defaults.
 *
 * Replacing rather than merging: somebody who writes out a list means that
 * list, and quietly adding ten services they left out would be the monitor
 * arguing with them.
 */
function daemonDefinitions(array $config): array
{
    $configured = $config['host']['daemons'] ?? null;
    if (!is_array($configured) || $configured === []) {
        return defaultDaemons();
    }

    $out = [];
    foreach ($configured as $row) {
        if (!is_array($row) || !isset($row['name'])) {
            continue;
        }
        $out[] = $row + [
            'label' => (string) $row['name'],
            'units' => [],
            'processes' => [],
        ];
    }

    return $out ?: defaultDaemons();
}

/* ------------------------------------------------------------------ *
 * Which of them to watch
 * ------------------------------------------------------------------ */

/**
 * The on/off choices, as name => bool.
 *
 * Only the choices, not the whole list: a service nobody has touched has no row
 * at all, and the caller treats a missing entry as watched. That way a fresh
 * install monitors everything without needing ten rows written first, and a
 * service added to the defaults in a later version is watched immediately
 * rather than being silently off because nothing turned it on.
 */
function daemonWatch(PDO $pdo): array
{
    try {
        $rows = $pdo
            ->query('SELECT name, watched FROM daemon_watch')
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Throwable) {
        // The table arrives in a migration; an older database simply has no
        // choices recorded, which means everything is watched.
        return [];
    }

    $out = [];
    foreach ($rows as $name => $watched) {
        $out[(string) $name] = (bool) $watched;
    }

    return $out;
}

/** Records a choice. */
function setDaemonWatch(PDO $pdo, string $name, bool $watched): void
{
    $pdo->prepare(
        'INSERT INTO daemon_watch (name, watched, changed_at) VALUES (:name, :watched, :at)
         ON CONFLICT(name) DO UPDATE SET watched = :watched, changed_at = :at',
    )->execute([
        'name' => $name,
        'watched' => $watched ? 1 : 0,
        'at' => time(),
    ]);
}

/**
 * The choices, read by the root collector out of the account's database.
 *
 * Opened by hand rather than through db(), which would migrate it - and a
 * migration run by root leaves root-owned journal files in a directory the
 * account has to be able to write. `query_only` makes that impossible rather
 * than merely unlikely: this process has no business writing to the account's
 * database at all, and the pragma is a cheaper guarantee than remembering not
 * to.
 *
 * Every failure means "watch everything". Monitoring a service nobody asked
 * about is untidy; silently monitoring nothing because a file was unreadable
 * is the failure that gets noticed weeks later.
 */
function daemonWatchFromConfig(array $config): array
{
    $file = (string) ($config['database'] ?? '');
    if ($file === '' || !is_file($file) || !is_readable($file)) {
        return [];
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA query_only = 1');
        return daemonWatch($pdo);
    } catch (Throwable) {
        return [];
    }
}

/**
 * Drops the definitions that have been switched off.
 *
 * "Not monitored" has to mean not collected, or the word is a lie - the rows
 * would keep being written and the only difference would be that nobody sees
 * them. The panel still lists a switched-off service, from the definitions
 * rather than from the readings, which is what makes it possible to switch one
 * back on.
 */
function watchedDaemons(array $definitions, array $watch): array
{
    return array_values(
        array_filter(
            $definitions,
            fn(array $d): bool => $watch[(string) $d['name']] ?? true,
        ),
    );
}

/* ------------------------------------------------------------------ *
 * Asking systemd
 * ------------------------------------------------------------------ */

/**
 * Every service unit systemd knows about, loaded or not.
 *
 * `--all` matters: without it a stopped unit is simply absent from the output,
 * which would collapse the one distinction this file exists to make.
 *
 * @return array<string, array{active: string, sub: string, loaded: string}>|null
 */
function systemdUnits(): ?array
{
    $out = shellRead(
        'systemctl list-units --type=service --all --no-legend --plain 2>/dev/null',
    );
    if ($out === null) {
        return null;
    }

    $units = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        // UNIT LOAD ACTIVE SUB DESCRIPTION, and the description has spaces.
        $parts = preg_split('/\s+/', trim($line), 5);
        if (!$parts || count($parts) < 4) {
            continue;
        }

        // A unit systemd cannot find is listed with a bullet in the first
        // column; drop it so the name does not arrive with punctuation on it.
        $name = ltrim($parts[0], "\u{25CF}* \t");
        if (!str_ends_with($name, '.service')) {
            continue;
        }

        $units[substr($name, 0, -8)] = [
            'loaded' => $parts[1],
            'active' => $parts[2],
            'sub' => $parts[3],
        ];
    }

    return $units;
}

/**
 * Restart counts and start times for the units that matter.
 *
 * One call for all of them. `systemctl show` accepts many units and prints a
 * block each, so this does not become slower as the watch list grows.
 *
 * @param list<string> $units
 * @return array<string, array>|null
 */
function systemdProperties(array $units): ?array
{
    if ($units === []) {
        return [];
    }

    $safe = [];
    foreach ($units as $unit) {
        // Nothing but a unit name ever reaches a shell.
        if (preg_match('/^[A-Za-z0-9@:._\-]+$/', $unit)) {
            $safe[] = escapeshellarg($unit . '.service');
        }
    }
    if ($safe === []) {
        return [];
    }

    $out = shellRead(
        'systemctl show ' .
            implode(' ', $safe) .
            ' --property=Id,ActiveState,SubState,NRestarts,ExecMainPID' .
            ',ActiveEnterTimestampMonotonic,ActiveEnterTimestamp' .
            ' --timestamp=unix 2>/dev/null',
    );
    if ($out === null) {
        return null;
    }

    $blocks = [];
    $current = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            if (isset($current['Id'])) {
                $blocks[
                    preg_replace('/\.service$/', '', $current['Id'])
                ] = $current;
            }
            $current = [];
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $current[$key] = $value;
    }
    if (isset($current['Id'])) {
        $blocks[preg_replace('/\.service$/', '', $current['Id'])] = $current;
    }

    return $blocks;
}

/**
 * When a unit last became active, as an absolute time.
 *
 * Two ways of asking because systemd answers differently by version, and the
 * one that is available is not knowable in advance:
 *
 * `--timestamp=unix` gives `@1712345678` and is exact, but only on systemd 249
 * and newer.
 *
 * The monotonic value is microseconds since boot and works everywhere, but it
 * is only a wall-clock time once the boot time is added - so it needs one, and
 * returns nothing rather than a wrong answer if there is none.
 */
function daemonStartedAt(array $properties, ?int $bootAt): ?int
{
    $stamp = (string) ($properties['ActiveEnterTimestamp'] ?? '');
    if (preg_match('/^@?(\d{9,})$/', $stamp, $m)) {
        return (int) $m[1];
    }

    $monotonic = (int) ($properties['ActiveEnterTimestampMonotonic'] ?? 0);
    if ($monotonic > 0 && $bootAt !== null) {
        return $bootAt + (int) ($monotonic / 1000000);
    }

    return null;
}

/* ------------------------------------------------------------------ *
 * Deciding
 * ------------------------------------------------------------------ */

/**
 * The state of every watched daemon.
 *
 * Pure, so it can be tested against a machine that is not this one - which is
 * the only way to test it at all, since the interesting cases are a stopped
 * MariaDB and a PHP-FPM with three versions installed, and neither is something
 * to arrange on a development laptop.
 *
 * @param list<array> $definitions
 * @param list<array> $procs process rows carrying at least 'name'
 * @param array<string, array>|null $units null when systemd could not be asked
 * @param array<string, array> $properties
 * @return list<array>
 */
function daemonStates(
    array $definitions,
    array $procs,
    ?array $units,
    array $properties = [],
    ?int $bootAt = null,
): array {
    $running = [];
    foreach ($procs as $proc) {
        $name = (string) ($proc['name'] ?? '');
        if ($name === '') {
            continue;
        }
        $running[$name][] = $proc;
    }

    $out = [];
    foreach ($definitions as $definition) {
        $matched =
            $units === null
                ? []
                : matchUnits($units, (array) ($definition['units'] ?? []));

        $procMatches = [];
        foreach ((array) ($definition['processes'] ?? []) as $pattern) {
            foreach ($running[$pattern] ?? [] as $proc) {
                $procMatches[] = $proc;
            }
        }

        $row = [
            'name' => (string) $definition['name'],
            'label' => (string) ($definition['label'] ?? $definition['name']),
            'procs' => count($procMatches),
            'pid' => $procMatches ? (int) ($procMatches[0]['pid'] ?? 0) : null,
            'unit' => null,
            'units_total' => count($matched),
            'units_active' => 0,
            'active_state' => null,
            'sub_state' => null,
            'restarts' => null,
            'started_at' => null,
            'source' => $units === null ? 'process' : 'systemd',
            'state' => 'absent',
        ];

        if ($matched !== []) {
            $active = [];
            foreach ($matched as $unit => $info) {
                if (($info['active'] ?? '') === 'active') {
                    $active[] = $unit;
                }
            }
            $row['units_active'] = count($active);

            /*
             * The unit that speaks for the service.
             *
             * An active one where there is one, so that a service with three
             * PHP-FPM pools reports the start time and restart count of a pool
             * that is actually up rather than of whichever sorted first.
             */
            $chosen = $active[0] ?? array_key_first($matched);
            $row['unit'] = $chosen;
            $row['active_state'] = $matched[$chosen]['active'] ?? null;
            $row['sub_state'] = $matched[$chosen]['sub'] ?? null;
            $row['state'] = $active !== [] ? 'running' : 'stopped';

            $props = $properties[$chosen] ?? null;
            if ($props) {
                $row['restarts'] =
                    isset($props['NRestarts']) && $props['NRestarts'] !== ''
                        ? (int) $props['NRestarts']
                        : null;
                $row['started_at'] =
                    $row['state'] === 'running'
                        ? daemonStartedAt($props, $bootAt)
                        : null;
                $pid = (int) ($props['ExecMainPID'] ?? 0);
                if ($pid > 0) {
                    $row['pid'] = $pid;
                }
            }
        } elseif ($procMatches !== []) {
            /*
             * Running, but systemd has never heard of it.
             *
             * Perfectly normal: a daemon started from rc.local, a container, or
             * a binary somebody launched by hand. It is alive, which is the
             * question being asked, and the source says where that came from.
             */
            $row['state'] = 'running';
            $row['source'] = 'process';
        } elseif ($units === null) {
            /*
             * No systemd, and no process. This is the one case where the honest
             * answer is that we do not know: a stopped service and one that was
             * never installed look exactly alike from a process list, and
             * calling it either would be a guess presented as a fact.
             */
            $row['state'] = 'unknown';
        }

        // A unit can be active while its processes are gone - "running" from a
        // unit that owns nothing is worth seeing rather than smoothing over.
        if (
            $row['state'] === 'running' &&
            $procMatches === [] &&
            $row['units_active'] === 0
        ) {
            $row['state'] = 'stopped';
        }

        $out[] = $row;
    }

    return $out;
}

/**
 * Units matching a definition, by exact name or by glob.
 *
 * The glob is what makes PHP-FPM work: cPanel installs one unit per PHP
 * version, named ea-php74-php-fpm and so on, and the set changes whenever
 * somebody adds or removes a version.
 *
 * @return array<string, array>
 */
function matchUnits(array $units, array $patterns): array
{
    $found = [];
    foreach ($patterns as $pattern) {
        $pattern = (string) $pattern;
        foreach ($units as $name => $info) {
            if (
                $name === $pattern ||
                (str_contains($pattern, '*') && fnmatch($pattern, $name))
            ) {
                $found[$name] = $info;
            }
        }
    }

    ksort($found);
    return $found;
}

/**
 * Reads the state of every watched daemon, shelling out where it helps.
 *
 * @param list<array> $procs the process table the collector already has
 */
function readDaemons(array $config, array $procs, ?int $bootAt = null): array
{
    $definitions = watchedDaemons(
        daemonDefinitions($config),
        daemonWatchFromConfig($config),
    );
    if ($definitions === []) {
        return [];
    }

    $units = systemdUnits();

    $wanted = [];
    if ($units !== null) {
        foreach ($definitions as $definition) {
            foreach (
                array_keys(
                    matchUnits($units, (array) ($definition['units'] ?? [])),
                )
                as $unit
            ) {
                $wanted[$unit] = true;
            }
        }
    }

    $properties = $wanted ? systemdProperties(array_keys($wanted)) ?? [] : [];

    return daemonStates($definitions, $procs, $units, $properties, $bootAt);
}

/* ------------------------------------------------------------------ *
 * Reading it back
 * ------------------------------------------------------------------ */

/** How far back the outage summary looks, in days. */
const DAEMON_HISTORY_DAYS = 7;

/**
 * The current state of every watched daemon, with how it got there.
 *
 * "MariaDB is running" is worth much less than "MariaDB is running, and has
 * been for eleven minutes" - the second one says it fell over during breakfast
 * and the first one hides it completely. So each row carries when its current
 * state began, and when it was last not running.
 *
 * Deliberately four cheap indexed queries per daemon rather than a walk over
 * the history. This runs on every dashboard poll, and ninety days of readings
 * is tens of thousands of rows per service - scanning them to notice that
 * nothing has changed since April would be the most expensive query on the page
 * and would say the same thing every time.
 */
function daemonStatus(
    PDO $pdo,
    array $definitions = [],
    array $watch = [],
    int $historyDays = DAEMON_HISTORY_DAYS,
): array {
    $latest = (int) $pdo
        ->query('SELECT MAX(taken_at) FROM host_daemons')
        ->fetchColumn();

    $collected = [];
    if ($latest > 0) {
        $stmt = $pdo->prepare('SELECT * FROM host_daemons WHERE taken_at = ?');
        $stmt->execute([$latest]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $collected[(string) $row['name']] = $row;
        }
    }

    /*
     * The list is the definitions, not the readings.
     *
     * A service that has been switched off produces no rows, so a panel built
     * from readings alone would lose it entirely - and with it the only control
     * that could switch it back on. Anything collected under a name no longer
     * in the definitions is kept too, so editing the config does not silently
     * orphan its history.
     */
    if ($definitions === []) {
        foreach ($collected as $name => $row) {
            $definitions[] = [
                'name' => $name,
                'label' => (string) $row['label'],
            ];
        }
    } else {
        $known = array_column($definitions, 'name');
        foreach ($collected as $name => $row) {
            if (!in_array($name, $known, true)) {
                $definitions[] = [
                    'name' => $name,
                    'label' => (string) $row['label'],
                    'retired' => true,
                ];
            }
        }
    }

    $lastDifferent = $pdo->prepare(
        'SELECT MAX(taken_at) FROM host_daemons WHERE name = ? AND state != ?',
    );
    $firstAfter = $pdo->prepare(
        'SELECT MIN(taken_at) FROM host_daemons WHERE name = ? AND state = ? AND taken_at > ?',
    );
    $earliest = $pdo->prepare(
        'SELECT MIN(taken_at) FROM host_daemons WHERE name = ?',
    );
    $down = $pdo->prepare(
        'SELECT COUNT(*) AS readings, MAX(taken_at) AS last_at
         FROM host_daemons
         WHERE name = ? AND state = \'stopped\' AND taken_at >= ?',
    );
    $downSince = $historyDays > 0 ? $latest - $historyDays * 86400 : $latest;

    $rows = [];
    foreach ($definitions as $definition) {
        $name = (string) $definition['name'];
        $watched = $watch[$name] ?? true;
        $row = $collected[$name] ?? null;

        $base = [
            'name' => $name,
            'label' => (string) ($definition['label'] ?? $name),
            'watched' => $watched,
            'retired' => (bool) ($definition['retired'] ?? false),
        ];

        if (!$watched) {
            // Switched off: no reading was taken, so there is nothing to report
            // about it and saying anything else would be inventing a state.
            $rows[] = $base + [
                'state' => 'off',
                'unit' => null,
                'units_total' => 0,
                'units_active' => 0,
                'active_state' => null,
                'sub_state' => null,
                'restarts' => null,
                'started_at' => null,
                'since' => null,
                'since_known' => false,
                'procs' => 0,
                'pid' => null,
                'source' => null,
                'down_readings' => 0,
                'last_down_at' => null,
            ];
            continue;
        }

        if ($row === null) {
            /*
             * Watched, but not in the latest reading.
             *
             * Almost always a service just switched back on: the choice is
             * recorded here and acted on by the collector, which will not run
             * again for a few minutes. "Pending" says that plainly instead of
             * showing it as absent, which would look like a verdict.
             */
            $rows[] = $base + [
                'state' => 'pending',
                'unit' => null,
                'units_total' => 0,
                'units_active' => 0,
                'active_state' => null,
                'sub_state' => null,
                'restarts' => null,
                'started_at' => null,
                'since' => null,
                'since_known' => false,
                'procs' => 0,
                'pid' => null,
                'source' => null,
                'down_readings' => 0,
                'last_down_at' => null,
            ];
            continue;
        }

        $state = (string) $row['state'];

        $lastDifferent->execute([$name, $state]);
        $changed = $lastDifferent->fetchColumn();

        if ($changed === null || $changed === false) {
            $earliest->execute([$name]);
            $since = (int) $earliest->fetchColumn();
            $sinceKnown = false;
        } else {
            $firstAfter->execute([$name, $state, (int) $changed]);
            $since = (int) $firstAfter->fetchColumn();
            $sinceKnown = true;
        }

        $down->execute([$name, $downSince]);
        $outage = $down->fetch(PDO::FETCH_ASSOC) ?: [
            'readings' => 0,
            'last_at' => null,
        ];

        $rows[] = $base + [
            'state' => $state,
            'unit' => $row['unit'],
            'units_total' => (int) $row['units_total'],
            'units_active' => (int) $row['units_active'],
            'active_state' => $row['active_state'],
            'sub_state' => $row['sub_state'],
            'restarts' =>
                $row['restarts'] === null ? null : (int) $row['restarts'],
            /*
             * Two different times, and confusing them would be a real error.
             *
             * started_at is what systemd says about the process running now.
             * since is when this monitor first saw the current state. They
             * disagree whenever the collector was not watching, and systemd's
             * is the better answer because it does not depend on us having
             * been there.
             */
            'started_at' =>
                $row['started_at'] === null ? null : (int) $row['started_at'],
            'since' => $since ?: null,
            'since_known' => $sinceKnown,
            'procs' => (int) $row['procs'],
            'pid' => $row['pid'] === null ? null : (int) $row['pid'],
            'source' => (string) $row['source'],
            'down_readings' => (int) $outage['readings'],
            'last_down_at' =>
                $outage['last_at'] === null ? null : (int) $outage['last_at'],
        ];
    }

    usort(
        $rows,
        fn(array $a, array $b): int => strcasecmp($a['label'], $b['label']),
    );

    return [
        'available' => $latest > 0,
        'reason' =>
            $latest > 0
                ? null
                : 'No daemon states have been recorded yet. This needs the host collector; ' .
                    'upload it and let it run once.',
        'taken_at' => $latest,
        'history_days' => $historyDays,
        'rows' => $rows,
    ];
}
