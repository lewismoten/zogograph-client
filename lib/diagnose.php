<?php
declare(strict_types=1);

/*
 * One page that says what is wrong, and where to look next.
 *
 * There are now forty pages here, each answering one question well. That is
 * useful when you already know which question to ask, and useless at three in
 * the morning when the only thing you know is that the site is slow. This looks
 * at everything at once and produces a short ranked list of things that are
 * actually true, each one carrying the measurement it came from and a link to
 * the page that shows it in full.
 *
 * Three rules, and they are what separate this from a dashboard that cries
 * wolf:
 *
 *   Every finding is a measurement. Nothing here infers a cause from a
 *   correlation, and nothing says "probably" without saying what it is reading.
 *
 *   Absence of data is a finding of its own, never a clean bill of health. A
 *   machine with no host collector is not a machine with no problems, and
 *   reporting silence as "all well" is the single most dangerous thing a
 *   monitor can do.
 *
 *   Nothing appears twice. A disk at 96% is one finding, not one from the
 *   threshold rule, one from the trend and one from the alert it raised.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/hostdb.php';
require_once __DIR__ . '/alerts.php';
// Domains and dated events, which are findings no measurement can produce.
require_once __DIR__ . '/events.php';

/** How bad a thing is, and therefore where it sorts. */
const DIAGNOSIS_RANK = ['critical' => 0, 'warning' => 1, 'watch' => 2, 'unknown' => 3, 'ok' => 4];

/**
 * Everything worth saying about this machine right now.
 *
 * @return array{findings: list<array>, checked: int, worst: string, at: int}
 */
function diagnose(array $config, PDO $pdo): array
{
    $findings = [];
    $ignored = [];
    $now = time();

    $vitals = $pdo->query(
        'SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1'
    )->fetch() ?: null;

    $host = null;
    $hostPdo = null;
    try {
        $hostPdo = hostDb($config, false);
        if ($hostPdo !== null) {
            $host = $hostPdo->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')->fetch() ?: null;
        }
    } catch (Throwable) {
        $hostPdo = null;
    }

    foreach ([
        diagnoseCollection($vitals, $host, $now),
        diagnoseDisk($config, $pdo, $vitals),
        diagnoseInodes($vitals),
        diagnoseCpu($pdo, $vitals, $host),
        diagnoseMemory($vitals, $host),
        diagnoseSwap($host),
        diagnoseLoad($vitals, $host),
        diagnoseNetwork($hostPdo),
        diagnosePorts($hostPdo),
        diagnoseUnits($hostPdo),
        diagnoseAlerts($pdo, $now),
        diagnoseDates($config, $pdo, $now),
        diagnosePathHygiene($config, $pdo),
    ] as $group) {
        foreach ($group as $finding) {
            if (!diagnosisIgnored($config, (string)$finding['key'])) {
                $findings[] = $finding;
            } else {
                $ignored[] = [
                    'key' => (string)$finding['key'],
                    'title' => (string)$finding['title'],
                ];
            }
        }
    }

    usort($findings, fn(array $a, array $b): int =>
        (DIAGNOSIS_RANK[$a['level']] <=> DIAGNOSIS_RANK[$b['level']])
        ?: strcmp($a['title'], $b['title']));

    $worst = 'ok';
    foreach ($findings as $finding) {
        if (DIAGNOSIS_RANK[$finding['level']] < DIAGNOSIS_RANK[$worst]) {
            $worst = $finding['level'];
        }
    }

    return [
        'findings' => $findings,
        'ignored' => $ignored,
        'checked' => count($findings),
        'worst' => $worst,
        'at' => $now,
    ];
}

function diagnosePathHygiene(array $config, PDO $pdo): array
{
    if (!function_exists('pathHygieneEnabled') || !pathHygieneEnabled($config, $pdo)) return [];
    $last = pathHygieneLast($pdo);
    if ($last === null) return [finding('unknown', 'Path-name hygiene has not run',
        'Enabled, but no completed disk scan has recorded it yet.',
        'The check runs during the normal account disk walk and does not send notifications.',
        'Run or review disk scans', 'storage')];
    if ($last['total'] === 0) return [];
    $sample = $last['findings'][0] ?? ['path' => '(not retained)', 'reason' => 'has an unsafe name'];
    return [finding('watch', 'Some paths have shell-confusing names',
        $last['total'] . ' found in the last disk scan' . ($last['truncated'] ? ' (list truncated)' : '') . '.',
        (string)$sample['path'] . ' ' . (string)$sample['reason']
            . '. This is advisory only; Zogograph does not email this finding.',
        'Review the exact paths and rename only files you own', 'storage')];
}

/** A deliberate exception is configuration, not an acknowledged alert. */
function diagnosisIgnored(array $config, string $key): bool
{
    return in_array($key, $config['diagnostic_ignores'] ?? [], true);
}

/**
 * One finding.
 *
 * `measured` is the number it is based on and is never optional - a finding
 * without one is an opinion. `next` is where to go, because "memory is at 94%"
 * without "and here is what is holding it" leaves somebody exactly where they
 * started.
 */
function finding(
    string $level,
    string $title,
    string $measured,
    string $means,
    string $next = '',
    string $view = ''
): array {
    return [
        // Titles are fixed, human-readable rule identities. Hashing keeps the
        // config compact and avoids turning prose into a public API key.
        'key' => sha1($title),
        'level' => $level,
        'title' => $title,
        'measured' => $measured,
        'means' => $means,
        'next' => $next,
        'view' => $view,
    ];
}

/**
 * Whether anything is still being collected.
 *
 * First, because every other check below reads data that this one says whether
 * to trust. A disk figure from four days ago is not a disk figure.
 */
function diagnoseCollection(?array $vitals, ?array $host, int $now): array
{
    $out = [];

    if ($vitals === null) {
        return [finding(
            'unknown',
            'Nothing has been collected',
            'No readings in the database at all.',
            'Every page here is empty because nothing has run, not because there is nothing '
                . 'wrong. Until the cron job runs, this cannot tell you anything.',
            'Set up the schedule',
            'schedule'
        )];
    }

    $age = $now - (int)$vitals['taken_at'];
    if ($age > 1800) {
        $out[] = finding(
            $age > 21600 ? 'critical' : 'warning',
            'The account collector has stopped',
            sprintf('The newest reading is %s old.', humanDuration($age)),
            'Everything on this site describing this account is that old. A chart that stops '
                . 'looks like a machine that went quiet, and this is the difference.',
            'Check the schedule',
            'schedule'
        );
    }

    if ($host === null) {
        $out[] = finding(
            'unknown',
            'Nothing can be seen past this account',
            'No host readings.',
            'Inside CageFS this account sees its own processes, its own CPU allowance and its '
                . 'own memory limit presented as though they were the machine\'s. Without the '
                . 'host collector, every figure here is the cage\'s answer - and a machine '
                . 'being ruined by another tenant looks completely healthy from in here.',
            'Set up the host collector',
            'schedule'
        );
    } else {
        $hostAge = $now - (int)$host['taken_at'];
        if ($hostAge > 1800) {
            $out[] = finding(
                $hostAge > 21600 ? 'warning' : 'watch',
                'The host collector has stopped',
                sprintf('The newest host reading is %s old.', humanDuration($hostAge)),
                'Memory by user, the machine\'s disks, its network and its services are all '
                    . 'that old.',
                'Check the schedule',
                'schedule'
            );
        }
    }

    return $out;
}

function diagnoseDisk(array $config, PDO $pdo, ?array $vitals): array
{
    if ($vitals === null || !$vitals['disk_total_bytes']) {
        return [];
    }

    $used = (float)$vitals['disk_used_percent'];
    $free = (int)$vitals['disk_free_bytes'];
    $out = [];

    if ($used >= 90) {
        $out[] = finding(
            $used >= 97 ? 'critical' : 'warning',
            'The disk is nearly full',
            sprintf('%.1f%% used, %s free.', $used, humanBytes($free)),
            $used >= 97
                ? 'At this point writes start failing. Databases corrupt, sessions vanish and '
                    . 'mail bounces - and all of it looks like unrelated application bugs.'
                : 'There is room now and the margin is thin. Worth acting on before it is '
                    . 'urgent rather than after.',
            'See what is taking the space',
            'storage'
        );
    }

    /*
     * The trend, only when it is not already the headline.
     *
     * "Full in six hours" is the useful form of "83% used", and reporting both
     * as separate findings is how one problem becomes three lines.
     */
    /*
     * seriesTrend takes rows, not a query. Fetching a day of them here rather
     * than passing a window keeps the trend measured over exactly the readings
     * it is shown against.
     */
    $rows = $pdo->prepare(
        'SELECT taken_at, disk_free_bytes FROM vitals
          WHERE taken_at >= ? AND disk_free_bytes IS NOT NULL ORDER BY taken_at'
    );
    $rows->execute([time() - 86400]);
    $trend = seriesTrend($rows->fetchAll(), 'disk_free_bytes');
    if ($trend !== null && ($trend['sustained'] ?? false) && ($trend['seconds_left'] ?? null) !== null) {
        $left = (int)$trend['seconds_left'];
        if ($left < 7 * 86400 && $used < 90) {
            $out[] = finding(
                $left < 86400 ? 'critical' : 'warning',
                'On course to fill up',
                sprintf(
                    'Losing %s an hour; at that rate the disk is full in %s.',
                    humanBytes(-$trend['rate'] * 3600),
                    humanDuration($left)
                ),
                'This is the one that arrives before the disk is full rather than after. The '
                    . 'rate is measured over a day, so a single large delete will not produce it.',
                'Find what is growing',
                'storage-files'
            );
        }
    }

    return $out;
}

function diagnoseInodes(?array $vitals): array
{
    if ($vitals === null || !$vitals['inodes_total']) {
        return [];
    }

    $used = (float)$vitals['inodes_used_percent'];
    if ($used < 85) {
        return [];
    }

    return [finding(
        $used >= 95 ? 'critical' : 'warning',
        'Running out of inodes',
        sprintf('%.1f%% of inodes used, %s free.', $used, humanCountPlain((int)$vitals['inodes_free'])),
        'A filesystem with free gigabytes and no inodes refuses to create a file, and the '
            . 'error it gives is "no space left on device" - which sends everybody to look at '
            . 'a disk that has plenty of room. It is caused by very many small files, not by '
            . 'large ones.',
        'Find the directories with the most files',
        'storage-files'
    )];
}

function diagnoseCpu(PDO $pdo, ?array $vitals, ?array $host): array
{
    $out = [];

    if ($vitals !== null && $vitals['cpu_percent'] !== null) {
        $cpu = (float)$vitals['cpu_percent'];

        /*
         * Sustained, not instantaneous.
         *
         * One reading at 100% is a backup starting or a page rendering, and is
         * over before anybody could read about it. The question is whether it
         * is *staying* there.
         */
        $recent = $pdo->query(
            'SELECT cpu_percent FROM vitals WHERE cpu_percent IS NOT NULL
             ORDER BY taken_at DESC LIMIT 15'
        )->fetchAll(PDO::FETCH_COLUMN);

        $high = count(array_filter($recent, fn($v): bool => (float)$v >= 85));
        if (count($recent) >= 5 && $high >= count($recent) * 0.7) {
            $out[] = finding(
                'warning',
                'CPU has been at the ceiling',
                sprintf('%d of the last %d readings at or above 85%%; the newest is %.0f%%.',
                    $high, count($recent), $cpu),
                'Inside CageFS this is a percentage of what this account is allowed, not of '
                    . 'the machine - so this can be pinned while the server is idle. That is '
                    . 'not a false alarm: it means the account is being throttled, which is '
                    . 'felt as a slow site.',
                'See what was running',
                'busy'
            );
        }
    }

    if ($host !== null && $host['cpu_percent'] !== null && (float)$host['cpu_percent'] >= 85) {
        $monitoring = ($host['monitoring_cpu_percent'] ?? null) === null
            ? ''
            : sprintf(' The collector tree accounted for %.1f%% of that interval; the rest is workload outside Zogograph.',
                (float)$host['monitoring_cpu_percent']);
        $out[] = finding(
            'warning',
            'The whole machine is busy',
            sprintf('%.0f%% of every core on the host is in use.', (float)$host['cpu_percent']),
            'This is the server itself, not this account\'s allowance. Every account on it is '
                . 'affected, and the cause may well not be yours.' . $monitoring,
            // The CPU pages rather than the memory ones: "busy" is a processor
            // finding, and the biggest process by memory is very often asleep.
            'See what is burning the processor',
            'cpu-procs'
        );
    }

    return $out;
}

function diagnoseMemory(?array $vitals, ?array $host): array
{
    $out = [];

    if ($vitals !== null && $vitals['mem_used_percent'] !== null) {
        $used = (float)$vitals['mem_used_percent'];
        if ($used >= 90) {
            $out[] = finding(
                $used >= 96 ? 'critical' : 'warning',
                'This account is near its memory limit',
                sprintf('%.0f%% used, %s available.', $used, humanBytes((int)$vitals['mem_available_bytes'])),
                'Past the limit the kernel kills whatever asked for the last page, which is '
                    . 'usually not the process that caused the problem. The symptom is a '
                    . 'process disappearing with no error anywhere.',
                'See what is using it',
                'processes'
            );
        }
    }

    if ($host !== null && $host['mem_total']) {
        $available = (int)$host['mem_available'];
        $total = (int)$host['mem_total'];
        $committed = $total - $available;
        $share = ($committed / max(1, $total)) * 100;

        if ($share >= 92) {
            $out[] = finding(
                $share >= 97 ? 'critical' : 'warning',
                'The machine is near its memory limit',
                sprintf('%.0f%% committed, %s available of %s.',
                    $share, humanBytes($available), humanBytes($total)),
                'Measured as total minus MemAvailable, so page cache is not counted - a machine '
                    . 'whose RAM is full of reclaimable cache is working correctly, and this is '
                    . 'the figure that actually runs out.',
                'See where the memory went',
                'host'
            );
        }
    }

    return $out;
}

function diagnoseSwap(?array $host): array
{
    if ($host === null || !$host['swap_total']) {
        return [];
    }

    $used = (int)$host['swap_total'] - (int)$host['swap_free'];
    $share = ($used / max(1, (int)$host['swap_total'])) * 100;

    if ($share < 50) {
        return [];
    }

    return [finding(
        $share >= 85 ? 'warning' : 'watch',
        'Swap is filling',
        sprintf('%s of %s in use.', humanBytes($used), humanBytes((int)$host['swap_total'])),
        'Swap in use is not itself a problem - the kernel pages out things nothing has touched '
            . 'in hours, and that is the system working. Swap *filling* is different: when it '
            . 'runs out the next step is the out-of-memory killer.',
        'See the memory dashboard',
        'host'
    )];
}

function diagnoseLoad(?array $vitals, ?array $host): array
{
    $sample = $host ?? $vitals;
    if ($sample === null || $sample['load1'] === null || empty($sample['cpu_cores'])) {
        return [];
    }

    $perCore = (float)$sample['load1'] / (int)$sample['cpu_cores'];
    if ($perCore < 1.5) {
        return [];
    }

    $monitoring = ($sample['monitoring_runnable'] ?? null) === null
        ? ''
        : ' The collector tree had ' . (int)$sample['monitoring_runnable']
            . ' runnable task' . ((int)$sample['monitoring_runnable'] === 1 ? '' : 's')
            . ' at this instant; that is not an exact share of the averaged load.';
    return [finding(
        $perCore >= 4 ? 'critical' : 'warning',
        'Work is queueing',
        sprintf('Load %.2f over %d cores - %.1f jobs waiting per core.',
            (float)$sample['load1'], (int)$sample['cpu_cores'], $perCore),
        'Load counts work waiting as well as work running, so this can be high while CPU is '
            . 'not - the usual cause of that combination is disk, not processor: everything is '
            . 'blocked waiting for I/O.' . $monitoring,
        'See what is touching the disk',
        'disk-procs'
    )];
}

function diagnoseNetwork(?PDO $host): array
{
    if ($host === null) {
        return [];
    }

    $out = [];

    try {
        $latest = (int)$host->query('SELECT MAX(sample_id) FROM host_interfaces')->fetchColumn();
        if ($latest <= 0) {
            return [];
        }

        $rows = $host->prepare('SELECT * FROM host_interfaces WHERE sample_id = ?');
        $rows->execute([$latest]);

        foreach ($rows->fetchAll() as $row) {
            if ($row['name'] === 'lo') {
                continue;
            }

            /*
             * Drops are the finding, not throughput.
             *
             * Throughput has no ceiling to compare against on most machines
             * here - virtual interfaces report no link speed - so "a lot of
             * traffic" cannot be judged. A dropped packet is a fact regardless:
             * the kernel had somewhere to put it and did not.
             */
            $dropped = (int)$row['rx_dropped'] + (int)$row['tx_dropped'];
            $errors = (int)$row['rx_errors'] + (int)$row['tx_errors'];

            if ($errors > 0) {
                $out[] = finding(
                    'warning',
                    sprintf('%s is reporting errors', $row['name']),
                    sprintf('%s errors since boot.', humanCountPlain($errors)),
                    'Interface errors are usually cabling, a duplex mismatch or a failing NIC. '
                        . 'They are not a load problem and will not go away on their own.',
                    'See the interfaces',
                    'network'
                );
            } elseif ($dropped > 1000) {
                $out[] = finding(
                    'watch',
                    sprintf('%s is dropping packets', $row['name']),
                    sprintf('%s dropped since boot.', humanCountPlain($dropped)),
                    'Drops mean packets arrived faster than something could take them - a full '
                        . 'ring buffer, a socket nobody is reading, or a firewall. A steady '
                        . 'small count is normal; a growing one is not.',
                    'See the interfaces',
                    'network'
                );
            }
        }

        /*
         * One address holding an implausible share of the sockets.
         *
         * This is the shape of both a flood and a stuck client, and neither is
         * visible in a total. The threshold is on the share rather than a count
         * because a busy server legitimately has thousands of connections.
         */
        $latestPeers = (int)$host->query('SELECT MAX(sample_id) FROM host_peers')->fetchColumn();
        if ($latestPeers > 0) {
            $peers = $host->prepare(
                'SELECT address, connections, private FROM host_peers
                  WHERE sample_id = ? ORDER BY connections DESC'
            );
            $peers->execute([$latestPeers]);
            $all = $peers->fetchAll();

            $total = array_sum(array_column($all, 'connections'));
            $top = $all[0] ?? null;

            if ($top !== null && $total >= 50 && (int)$top['connections'] >= $total * 0.5) {
                $out[] = finding(
                    'warning',
                    'One address holds most of the connections',
                    sprintf('%s has %s of %s open connections.',
                        $top['address'],
                        humanCountPlain((int)$top['connections']),
                        humanCountPlain((int)$total)),
                    'That is the shape of a flood or of a client that is not closing sockets. '
                        . 'Either way it is one address, which makes it something you can act on.',
                    'See who is connected',
                    'net-peers'
                );
            }
        }
    } catch (Throwable) {
        // An older host database without these tables. Nothing to report, and
        // nothing wrong with the machine.
        return $out;
    }

    return $out;
}

/**
 * Services with no authentication, open to the world.
 *
 * The one check here that is about security rather than capacity, and it is
 * included because it is the same question - "what is this machine doing that
 * it should not be" - and because a public Redis is how a machine becomes
 * somebody else's.
 */
function diagnosePorts(?PDO $host): array
{
    if ($host === null) {
        return [];
    }

    try {
        $latest = (int)$host->query('SELECT MAX(sample_id) FROM host_ports')->fetchColumn();
        if ($latest <= 0) {
            return [];
        }

        $rows = $host->prepare('SELECT * FROM host_ports WHERE sample_id = ? AND public = 1');
        $rows->execute([$latest]);

        $risky = [
            3306 => 'MySQL or MariaDB',
            5432 => 'PostgreSQL',
            6379 => 'Redis',
            9200 => 'Elasticsearch',
            11211 => 'Memcached',
            27017 => 'MongoDB',
        ];

        $found = [];
        foreach ($rows->fetchAll() as $row) {
            $port = (int)$row['port'];
            if (isset($risky[$port])) {
                $found[] = sprintf('%s on %d', $risky[$port], $port);
            }
        }

        if ($found === []) {
            return [];
        }

        return [finding(
            'critical',
            'A database is reachable from off this machine',
            implode(', ', $found) . '.',
            'These have no authentication worth the name in their default configuration. '
                . 'Reachable from outside, that is not a configuration choice - it is how a '
                . 'machine becomes somebody else\'s. Bind it to 127.0.0.1, or firewall it.',
            'See the open ports',
            'net-ports'
        )];
    } catch (Throwable) {
        return [];
    }
}

function diagnoseUnits(?PDO $host): array
{
    if ($host === null) {
        return [];
    }

    try {
        $latest = (int)$host->query('SELECT MAX(sample_id) FROM host_units')->fetchColumn();
        if ($latest <= 0) {
            return [];
        }

        $rows = $host->prepare(
            "SELECT name FROM host_units WHERE sample_id = ? AND state = 'failed' ORDER BY name"
        );
        $rows->execute([$latest]);
        $failed = $rows->fetchAll(PDO::FETCH_COLUMN);

        if ($failed === []) {
            return [];
        }

        return [finding(
            'warning',
            count($failed) === 1 ? 'A service has failed' : 'Services have failed',
            implode(', ', array_slice($failed, 0, 6))
                . (count($failed) > 6 ? sprintf(' and %d more.', count($failed) - 6) : '.'),
            'A failed unit uses no memory, no CPU and no disk, so it is absent from every '
                . 'other panel here rather than standing out in one. That is what makes it easy '
                . 'to have been broken for weeks.',
            'See every service',
            'units'
        )];
    } catch (Throwable) {
        return [];
    }
}

/**
 * Dates that are about to matter.
 *
 * The only findings here that come from a calendar rather than a measurement,
 * and the only ones that can be certain: a domain expires on the day it expires
 * whatever the machine is doing. Included because this page is meant to be the
 * one place that answers "what should I be worried about", and "the domain
 * lapses on Friday" belongs in that answer.
 */
function diagnoseDates(array $config, PDO $pdo, int $now): array
{
    try {
        $pressing = pressingEvents($config, $pdo, 21);
    } catch (Throwable) {
        return [];
    }

    $out = [];
    foreach ($pressing as $item) {
        $days = (int)floor(($item['at'] - $now) / 86400);

        $out[] = finding(
            $days <= 3 ? 'critical' : 'warning',
            $item['kind'] === 'domain' ? 'A domain is about to expire' : 'Something is due',
            /*
             * A domain's title already carries its timing.
             *
             * pressingEvents writes "example.com expires in 3 days" for a
             * domain and a bare title for an event, so appending the timing to
             * both produced "expires in 3 days - in 3 days".
             */
            $item['kind'] === 'domain'
                ? $item['title'] . '.'
                : sprintf(
                    '%s %s.',
                    $item['title'],
                    $days < 0
                        ? 'was due ' . humanDuration(abs($days) * 86400) . ' ago'
                        : ($days === 0 ? 'is due today' : 'is due in ' . $days . ' days')
                ),
            $item['kind'] === 'domain'
                ? 'Nothing else on this site can see this coming. Every server measure stays '
                    . 'green until the name stops resolving, and then everything fails at once '
                    . 'for a reason that is in no log.'
                : 'Marked important on the calendar. Mark it dealt with there to stop it '
                    . 'appearing here.',
            $item['kind'] === 'domain' ? 'See the domains' : 'See the calendar',
            $item['kind'] === 'domain' ? 'domains' : 'calendar'
        );
    }

    return $out;
}

function diagnoseAlerts(PDO $pdo, int $now): array
{
    $unread = (int)$pdo->query(
        'SELECT COUNT(*) FROM alerts WHERE acknowledged_at IS NULL'
    )->fetchColumn();

    if ($unread === 0) {
        return [];
    }

    $critical = (int)$pdo->query(
        "SELECT COUNT(*) FROM alerts WHERE acknowledged_at IS NULL AND severity = 'critical'"
    )->fetchColumn();

    $newest = (int)$pdo->query(
        'SELECT MAX(raised_at) FROM alerts WHERE acknowledged_at IS NULL'
    )->fetchColumn();

    return [finding(
        $critical > 0 ? 'warning' : 'watch',
        'Alerts nobody has read',
        sprintf('%d unread%s; the newest is %s old.',
            $unread,
            $critical > 0 ? sprintf(', %d of them critical', $critical) : '',
            humanDuration($now - $newest)),
        'Each one carries the evidence gathered at the moment it fired, which is the only '
            . 'record of what the machine was doing then.',
        'Read them',
        'alerts'
    )];
}

/** A plain count with separators, without pulling in the display layer. */
function humanCountPlain(int $value): string
{
    return number_format($value);
}
