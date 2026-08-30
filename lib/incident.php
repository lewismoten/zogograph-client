<?php
declare(strict_types=1);

/*
 * Why did this alert fire?
 *
 * An alert reports that something crossed a line. It cannot report what was
 * doing it, and that is the half anybody actually needs: "disk is at 91%" is
 * not a thing you can act on, and "disk is at 91%, and this log file put 4 GiB
 * on it in the last hour" is.
 *
 * The difficulty is timing. All of this evidence is perishable. A runaway
 * process is gone the moment it is reaped, a log is gone the moment it rotates,
 * and the memory a leak was holding is back in the pool as soon as the service
 * restarts. By the time the mail is read - six hours later, on a Monday - there
 * is nothing left to look at but the subject line. So the state is written down
 * at the instant the rule fires, while the thing that caused it is still there.
 *
 * It is deliberately more than a process list, because half the interesting
 * cases are invisible in one. Eleven identical processes are not remarkable
 * until you can see they all descend from the same cron entry; disk growth is
 * not attributable until you can see which file moved; and memory pressure is
 * a story about swap and MemAvailable together rather than about any single
 * process. The snapshot is meant to answer the follow-up questions, not just
 * the first one.
 *
 * Two things it does that a naive snapshot would not:
 *
 * It records what it could not see, and why. On CloudLinux this account is
 * inside CageFS and /proc is virtualised: /proc/diskstats is not there, and CPU
 * percentages are a share of this account's LVE allowance rather than of the
 * machine. An incident record that quietly omitted the I/O section would read
 * as "there was no I/O", which is a different and wrong claim. Every gap is
 * named.
 *
 * And it leaves the collector out of its own evidence. The reason the existing
 * busy-moment records were so disappointing is that the top of the list was
 * `ps`, `sh` and `php cron.php` - the act of looking, crowding out the thing
 * being looked for. excludeOwnTree() removes this process, its ancestry and its
 * children before anything is ranked.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/collect.php';
require_once __DIR__ . '/processes.php';
require_once __DIR__ . '/hostdb.php';

/** How many processes are kept in each ranking. */
const INCIDENT_KEEP_PROCS = 15;

/** How many recently changed files are kept. */
const INCIDENT_KEEP_FILES = 15;

/** Nodes in the process tree, as a backstop against a pathological fork bomb. */
const INCIDENT_MAX_TREE = 120;

/**
 * Writes down the state of the machine, and returns the incident id.
 *
 * Called once per evaluation with every alert that survived its cooldown. Null
 * when there is nothing to attach it to, which is the ordinary case - most runs
 * raise nothing, and a snapshot of a machine that is behaving costs a `ps` and
 * tells nobody anything.
 *
 * @param list<array> $alerts
 */
function captureIncident(array $config, PDO $pdo, array $alerts): ?int
{
    if ($alerts === []) {
        return null;
    }

    $evidence = gatherEvidence($config, $pdo, $alerts);
    $json = json_encode(
        $evidence,
        JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE,
    );

    /*
     * A snapshot that will not encode must not take the alert down with it.
     *
     * The command lines in here are arbitrary bytes from other people's
     * processes, and JSON is stricter about those than SQLite is. The
     * substitution flag above handles the usual case; this is for the rest.
     */
    if ($json === false) {
        $json = json_encode([
            'at' => time(),
            'incomplete' =>
                'The evidence could not be encoded: ' . json_last_error_msg(),
        ]);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO incidents (captured_at, triggered_by, evidence, bytes) VALUES (?, ?, ?, ?)',
    );
    $stmt->execute([
        time(),
        implode(', ', array_column($alerts, 'rule')),
        $json,
        strlen((string) $json),
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Everything worth writing down, gathered from wherever it can be had.
 *
 * Nothing in here may throw. A snapshot is taken while something is already
 * wrong, which is the worst possible moment to discover that the code meant to
 * explain it has its own opinion about /proc being readable - and an exception
 * here would lose the alert as well as the evidence. Every section is wrapped,
 * and a section that fails records why instead of vanishing.
 */
function gatherEvidence(array $config, PDO $pdo, array $alerts): array
{
    $missing = [];

    $evidence = [
        'at' => time(),
        'triggered_by' => array_map(
            fn(array $a): array => [
                'rule' => (string) $a['rule'],
                'severity' => (string) ($a['severity'] ?? 'warning'),
                'subject' => (string) ($a['subject'] ?? ''),
            ],
            $alerts,
        ),
    ];

    $sections = [
        'vitals' => fn(): array => incidentVitals($pdo),
        'processes' => fn(): array => incidentProcesses(),
        'disk' => fn(): array => incidentDisk($pdo),
        'files' => fn(): array => incidentFiles($pdo),
        'host' => fn(): array => incidentHost($config),
    ];

    foreach ($sections as $name => $gather) {
        try {
            $evidence[$name] = $gather();
        } catch (Throwable $e) {
            $evidence[$name] = null;
            $missing[$name] = 'Could not be read: ' . $e->getMessage();
        }
    }

    /*
     * The process tree is built from the same `ps` the rankings came from
     * rather than by running it again - two calls a second apart would
     * disagree, and a tree whose parents are missing from its own snapshot is
     * worse than no tree.
     */
    if (!empty($evidence['processes']['all'])) {
        $evidence['tree'] = incidentTree($evidence['processes']['all']);
        // The full list has served its purpose; the rankings and the tree are
        // what get read, and keeping all of it would triple the row.
        unset($evidence['processes']['all']);
    }

    foreach ($evidence as $section) {
        if (
            is_array($section) &&
            isset($section['missing']) &&
            is_array($section['missing'])
        ) {
            $missing += $section['missing'];
        }
    }
    $evidence['missing'] = $missing;

    return $evidence;
}

/**
 * Load, memory, swap and CPU, from the reading taken moments ago.
 *
 * From the database rather than from /proc directly: the collector has just
 * written this row, and re-reading the same files would produce slightly
 * different numbers than the ones the rule actually fired on. Evidence that
 * disagrees with the alert it explains is worse than none.
 */
function incidentVitals(PDO $pdo): array
{
    $row = $pdo
        ->query('SELECT * FROM vitals ORDER BY taken_at DESC LIMIT 1')
        ->fetch();
    if (!$row) {
        return [
            'missing' => ['vitals' => 'No readings have been recorded yet.'],
        ];
    }

    $cores = (int) ($row['cpu_cores'] ?? 0);
    $load1 = $row['load1'] === null ? null : (float) $row['load1'];

    return [
        'taken_at' => (int) $row['taken_at'],
        'load' => [
            '1' => $load1,
            '5' => $row['load5'] === null ? null : (float) $row['load5'],
            '15' => $row['load15'] === null ? null : (float) $row['load15'],
            'cores' => $cores ?: null,
            // Load per core is the figure that means the same thing on every
            // machine: above 1.0, work is queuing for a processor.
            'per_core' =>
                $cores > 0 && $load1 !== null
                    ? round($load1 / $cores, 2)
                    : null,
        ],
        'cpu_percent' =>
            $row['cpu_percent'] === null ? null : (float) $row['cpu_percent'],
        'memory' => [
            'total_bytes' => (int) $row['mem_total_bytes'],
            'available_bytes' => (int) $row['mem_available_bytes'],
            'used_percent' =>
                $row['mem_used_percent'] === null
                    ? null
                    : (float) $row['mem_used_percent'],
            'swap_total_bytes' => (int) $row['swap_total_bytes'],
            'swap_used_bytes' => (int) $row['swap_used_bytes'],
        ],
        'io' => [
            'read_rate' =>
                $row['io_read_rate'] === null
                    ? null
                    : (float) $row['io_read_rate'],
            'write_rate' =>
                $row['io_write_rate'] === null
                    ? null
                    : (float) $row['io_write_rate'],
            'util_percent' =>
                $row['io_util_percent'] === null
                    ? null
                    : (float) $row['io_util_percent'],
            'own_read_rate' =>
                $row['own_read_rate'] === null
                    ? null
                    : (float) $row['own_read_rate'],
            'own_write_rate' =>
                $row['own_write_rate'] === null
                    ? null
                    : (float) $row['own_write_rate'],
            'pressure_some' =>
                $row['io_pressure_some'] === null
                    ? null
                    : (float) $row['io_pressure_some'],
        ],
        'source' => (string) ($row['source'] ?? ''),
        /*
         * Asked, rather than assumed.
         *
         * The obvious thing to write here is "CageFS is hiding /proc/diskstats",
         * because on this host it usually is. But an incident record that
         * states a cause it did not check is exactly the sort of confident
         * wrong answer that sends somebody looking in the wrong place -
         * /proc/diskstats can also be missing because /proc is not readable at
         * all. diskstatsDiagnosis() goes and looks.
         */
        'missing' =>
            $row['io_read_rate'] === null
                ? [
                    'disk_io' =>
                        'Whole-disk I/O was not recorded. ' .
                        (diskstatsDiagnosis() ?:
                            'The reading predates I/O collection.'),
                ]
                : [],
    ];
}

/**
 * What was running, ranked two ways, with the collector taken out.
 *
 * Two rankings from one `ps`, because "what is burning the CPU" and "what is
 * holding the memory" are different questions with different answers and an
 * incident usually raises both.
 */
function incidentProcesses(): array
{
    // Ungrouped: the tree needs every pid and its parent, and the rankings can
    // be grouped afterwards from the same data.
    $list = listProcesses('cpu', 100000, false);
    if (!($list['available'] ?? false)) {
        return [
            'available' => false,
            'missing' => [
                'processes' =>
                    (string) ($list['reason'] ?? 'ps could not be run.'),
            ],
        ];
    }

    $rows = excludeOwnTree($list['rows']);

    $byCpu = $rows;
    usort(
        $byCpu,
        fn(array $a, array $b): int => $b['cpu_percent'] <=> $a['cpu_percent'],
    );
    $byMemory = $rows;
    usort(
        $byMemory,
        fn(array $a, array $b): int => $b['rss_bytes'] <=> $a['rss_bytes'],
    );

    /*
     * The oldest processes, which is the ranking that catches a stuck job.
     *
     * A job that has hung is using no CPU and little memory - that is what
     * being stuck means - so it appears nowhere in either list above. It is
     * only unusual in one dimension: it is still there.
     */
    $byAge = array_values(
        array_filter(
            $rows,
            fn(array $r): bool => $r['elapsed_seconds'] !== null,
        ),
    );
    usort(
        $byAge,
        fn(array $a, array $b): int => $b['elapsed_seconds'] <=>
            $a['elapsed_seconds'],
    );

    return [
        'available' => true,
        'count' => count($rows),
        'by_cpu' => array_slice($byCpu, 0, INCIDENT_KEEP_PROCS),
        'by_memory' => array_slice($byMemory, 0, INCIDENT_KEEP_PROCS),
        'by_age' => array_slice($byAge, 0, INCIDENT_KEEP_PROCS),
        'all' => $rows,
    ];
}

/**
 * The processes that matter, with the parents that explain them.
 *
 * A flat list carrying a depth, which is what a tree is once it has to survive
 * being JSON and then being rendered. The chain upwards is the part that turns
 * a list into an explanation: eleven identical processes are a curiosity until
 * their common ancestor turns out to be one cron entry, and then they are a
 * diagnosis.
 *
 * Only the interesting processes and their ancestry are kept. The whole tree of
 * a busy server is hundreds of nodes, nearly all of them idle and irrelevant to
 * whatever fired.
 *
 * @param list<array> $rows every process, ungrouped
 * @return list<array>
 */
function incidentTree(array $rows): array
{
    $byPid = [];
    foreach ($rows as $row) {
        $byPid[(int) $row['pid']] = $row;
    }

    // The ones worth explaining: heaviest on CPU, heaviest on memory, oldest.
    $interesting = [];
    foreach (['cpu_percent', 'rss_bytes', 'elapsed_seconds'] as $key) {
        $sorted = $rows;
        usort(
            $sorted,
            fn(array $a, array $b): int => ($b[$key] ?? 0) <=> ($a[$key] ?? 0),
        );
        foreach (array_slice($sorted, 0, 8) as $row) {
            $interesting[(int) $row['pid']] = true;
        }
    }

    /*
     * Every ancestor of every interesting process.
     *
     * Guarded against a cycle in the pid graph. It should not be possible, but
     * this walks data read from a text command on a machine that is already
     * misbehaving, and an infinite loop inside the code that explains a fault
     * would be a poor way to find out.
     */
    $keep = [];
    foreach (array_keys($interesting) as $pid) {
        $seen = [];
        $walk = (int) $pid;
        while ($walk > 0 && isset($byPid[$walk]) && !isset($seen[$walk])) {
            $seen[$walk] = true;
            $keep[$walk] = true;
            $walk = (int) $byPid[$walk]['ppid'];
        }
    }

    $children = [];
    foreach ($keep as $pid => $_) {
        $children[(int) $byPid[$pid]['ppid']][] = (int) $pid;
    }
    foreach ($children as &$list) {
        sort($list);
    }
    unset($list);

    // Roots are the kept processes whose parent was not itself kept.
    $roots = [];
    foreach (array_keys($keep) as $pid) {
        if (!isset($keep[(int) $byPid[$pid]['ppid']])) {
            $roots[] = (int) $pid;
        }
    }
    sort($roots);

    $out = [];
    $emit = function (int $pid, int $depth) use (
        &$emit,
        &$out,
        $byPid,
        $children,
        $interesting,
    ): void {
        if (count($out) >= INCIDENT_MAX_TREE) {
            return;
        }

        $row = $byPid[$pid];
        $out[] = [
            'pid' => $pid,
            'ppid' => (int) $row['ppid'],
            'depth' => $depth,
            'user' => (string) $row['user'],
            'name' => (string) $row['name'],
            'command' => (string) $row['command'],
            'cpu_percent' => (float) $row['cpu_percent'],
            'rss_bytes' => (int) $row['rss_bytes'],
            'elapsed_seconds' => $row['elapsed_seconds'],
            // Marks the process that got it listed, as against an ancestor
            // that is only here to explain where it came from.
            'notable' => isset($interesting[$pid]),
        ];

        foreach ($children[$pid] ?? [] as $child) {
            $emit($child, $depth + 1);
        }
    };

    foreach ($roots as $root) {
        $emit($root, 0);
    }

    return $out;
}

/**
 * Where the disk stands, and which way it has been moving.
 *
 * The level alone does not distinguish a disk that has been 91% full for a year
 * from one that was 40% full this morning, and those want completely different
 * responses.
 */
function incidentDisk(PDO $pdo): array
{
    $now = $pdo
        ->query(
            'SELECT taken_at, disk_total_bytes, disk_used_bytes, disk_free_bytes,
                disk_used_percent, inodes_used, inodes_free, inodes_used_percent
         FROM vitals ORDER BY taken_at DESC LIMIT 1',
        )
        ->fetch();

    if (!$now) {
        return ['missing' => ['disk' => 'No readings have been recorded yet.']];
    }

    $growth = [];
    foreach (
        ['1 hour' => 3600, '24 hours' => 86400, '7 days' => 604800]
        as $label => $span
    ) {
        $then = $pdo->prepare(
            'SELECT disk_used_bytes, taken_at FROM vitals
             WHERE taken_at <= ? ORDER BY taken_at DESC LIMIT 1',
        );
        $then->execute([(int) $now['taken_at'] - $span]);
        $row = $then->fetch();

        // Absent rather than zero when the history does not reach back that
        // far: zero would read as "it has not grown", which is a claim.
        $growth[$label] = $row
            ? (int) $now['disk_used_bytes'] - (int) $row['disk_used_bytes']
            : null;
    }

    // Which categories moved between the last two completed scans.
    $scans = $pdo
        ->query(
            'SELECT id FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 2',
        )
        ->fetchAll(PDO::FETCH_COLUMN);

    $categories = [];
    if (count($scans) === 2) {
        $stmt = $pdo->prepare(
            'SELECT category, bytes, files FROM scan_totals WHERE scan_id = ?',
        );
        $stmt->execute([$scans[0]]);
        $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->execute([$scans[1]]);
        $before = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $before[(string) $row['category']] = $row;
        }

        foreach ($latest as $row) {
            $was = $before[(string) $row['category']] ?? null;
            $categories[] = [
                'category' => (string) $row['category'],
                'bytes' => (int) $row['bytes'],
                'files' => (int) $row['files'],
                'grew_bytes' => $was
                    ? (int) $row['bytes'] - (int) $was['bytes']
                    : null,
                'grew_files' => $was
                    ? (int) $row['files'] - (int) $was['files']
                    : null,
            ];
        }

        usort(
            $categories,
            fn(array $a, array $b): int => ($b['grew_bytes'] ?? 0) <=>
                ($a['grew_bytes'] ?? 0),
        );
    }

    return [
        'total_bytes' => (int) $now['disk_total_bytes'],
        'used_bytes' => (int) $now['disk_used_bytes'],
        'free_bytes' => (int) $now['disk_free_bytes'],
        'used_percent' => (float) $now['disk_used_percent'],
        'inodes_used' => (int) $now['inodes_used'],
        'inodes_free' => (int) $now['inodes_free'],
        'inodes_used_percent' =>
            $now['inodes_used_percent'] === null
                ? null
                : (float) $now['inodes_used_percent'],
        'growth_bytes' => $growth,
        'categories' => $categories,
    ];
}

/**
 * The files that moved most between the last two scans.
 *
 * Paths are kept in full. This row is only ever shown to somebody signed in -
 * see incidentRead() - and the whole point of the record is being able to go
 * and look at the thing afterwards.
 */
function incidentFiles(PDO $pdo): array
{
    $scans = $pdo
        ->query(
            'SELECT id, finished_at FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 2',
        )
        ->fetchAll(PDO::FETCH_ASSOC);

    if (count($scans) < 2) {
        return [
            'missing' => [
                'files' =>
                    'Two completed scans are needed before growth can be compared.',
            ],
        ];
    }

    $stmt = $pdo->prepare(
        'SELECT p.path, p.kind, p.category, now.bytes, now.files,
                now.bytes - was.bytes AS grew_bytes,
                now.files - was.files AS grew_files
         FROM path_sizes now
         JOIN path_sizes was ON was.path_id = now.path_id AND was.scan_id = :was
         JOIN paths p ON p.id = now.path_id
         WHERE now.scan_id = :now AND now.bytes - was.bytes > 0
         ORDER BY grew_bytes DESC LIMIT :limit',
    );
    $stmt->bindValue(':was', (int) $scans[1]['id'], PDO::PARAM_INT);
    $stmt->bindValue(':now', (int) $scans[0]['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', INCIDENT_KEEP_FILES, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'since' => (int) $scans[1]['finished_at'],
        'until' => (int) $scans[0]['finished_at'],
        'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ];
}

/**
 * What only root can see: real PSS, real disk I/O, the whole machine.
 *
 * Optional throughout. The host collector needs root to install and most of
 * this tool works without it, so its absence is a note rather than a fault -
 * but it is worth *saying*, because "no PSS recorded" and "PSS was zero" look
 * identical in a report and mean opposite things.
 */
function incidentHost(array $config): array
{
    try {
        $host = hostDb($config, false);
    } catch (Throwable $e) {
        return [
            'available' => false,
            'missing' => ['host' => 'Host database: ' . $e->getMessage()],
        ];
    }

    if ($host === null) {
        return [
            'available' => false,
            'missing' => [
                'host' =>
                    'The host collector is not installed, so this record ' .
                    'shows only what the account can see. Proportional memory (PSS), ' .
                    'whole-disk I/O and other accounts\' processes are not included.',
            ],
        ];
    }

    $latest = (int) $host
        ->query('SELECT MAX(taken_at) FROM host_services')
        ->fetchColumn();
    if ($latest <= 0) {
        return [
            'available' => false,
            'missing' => ['host' => 'The host collector has not run yet.'],
        ];
    }

    $services = $host->prepare(
        'SELECT name, procs, threads, rss_bytes, pss_bytes, swap_bytes,
                read_bytes, write_bytes, oldest_seconds
         FROM host_services WHERE taken_at = ? ORDER BY pss_bytes DESC LIMIT ?',
    );
    $services->bindValue(1, $latest, PDO::PARAM_INT);
    $services->bindValue(2, INCIDENT_KEEP_PROCS, PDO::PARAM_INT);
    $services->execute();

    $sample = $host
        ->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')
        ->fetch();

    return [
        'available' => true,
        'taken_at' => $latest,
        // Said plainly, because a host reading from an hour ago described an
        // hour ago, and this record is about a particular minute.
        'age_seconds' => max(0, time() - $latest),
        'services' => $services->fetchAll(PDO::FETCH_ASSOC),
        'sample' => $sample ?: null,
    ];
}

/**
 * Reads one incident back, or null if there is no such thing.
 *
 * Signed-in only, enforced at the endpoint. The record holds full paths and
 * other accounts' command lines - redactSecrets() catches the obvious password
 * arguments and cannot promise to catch every one - so unlike the rest of the
 * dashboard this is not masked for anonymous visitors, it is refused.
 */
function incidentRead(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM incidents WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $evidence = json_decode((string) $row['evidence'], true);

    return [
        'id' => (int) $row['id'],
        'captured_at' => (int) $row['captured_at'],
        'triggered_by' => (string) $row['triggered_by'],
        'bytes' => (int) $row['bytes'],
        'evidence' => is_array($evidence) ? $evidence : null,
        'alerts' => incidentAlerts($pdo, (int) $row['id']),
    ];
}

/** The alerts this snapshot was taken for. */
function incidentAlerts(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT id, raised_at, rule, severity, subject, detail
         FROM alerts WHERE incident_id = ? ORDER BY id',
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Drops old incidents.
 *
 * They are much larger than an alert row and much rarer, so they keep their own
 * clock. Kept longer than the readings they describe: the whole value of an
 * incident is being able to look at a bad week from the far side of it.
 */
function pruneIncidents(PDO $pdo, int $keepDays): int
{
    $stmt = $pdo->prepare('DELETE FROM incidents WHERE captured_at < ?');
    $stmt->execute([time() - max(1, $keepDays) * 86400]);
    return $stmt->rowCount();
}

/**
 * The evidence, compressed to something worth reading in an email.
 *
 * The record itself runs to twenty-odd kilobytes, which is right for a page
 * with a scrollbar and wrong for a message read on a phone at half past six in
 * the morning. This is the part that answers the first question - what was the
 * machine doing - so that the mail is actionable on its own and the dashboard
 * is for the follow-up rather than for the basics.
 *
 * @return list<string>
 */
function incidentSummary(array $evidence): array
{
    $lines = ['At that moment:'];

    $vitals = $evidence['vitals'] ?? null;
    if ($vitals) {
        $load = $vitals['load'];
        if ($load['1'] !== null) {
            $lines[] = sprintf(
                '  Load %.2f%s%s',
                $load['1'],
                $load['cores']
                    ? sprintf(
                        ' on %d core%s',
                        $load['cores'],
                        $load['cores'] === 1 ? '' : 's',
                    )
                    : '',
                $load['per_core'] !== null
                    ? sprintf(' (%.2f per core)', $load['per_core'])
                    : '',
            );
        }
        $lines[] = sprintf(
            '  Memory %s available of %s, swap %s used',
            humanBytes((int) $vitals['memory']['available_bytes']),
            humanBytes((int) $vitals['memory']['total_bytes']),
            humanBytes((int) $vitals['memory']['swap_used_bytes']),
        );
    }

    $disk = $evidence['disk'] ?? null;
    if ($disk) {
        $hour = $disk['growth_bytes']['1 hour'] ?? null;
        $lines[] = sprintf(
            '  Disk %.1f%% used, %s free%s',
            $disk['used_percent'],
            humanBytes((int) $disk['free_bytes']),
            $hour === null
                ? ''
                : sprintf(' (%s in the last hour)', humanBytes((int) $hour)),
        );
    }

    $procs = $evidence['processes'] ?? null;
    if ($procs && ($procs['available'] ?? false)) {
        $top = static function (array $rows, callable $value): string {
            $bits = [];
            foreach (array_slice($rows, 0, 3) as $row) {
                $bits[] = $row['name'] . ' ' . $value($row);
            }
            return implode(', ', $bits);
        };

        $lines[] = '';
        $lines[] =
            '  Most CPU:    ' .
            $top(
                $procs['by_cpu'],
                fn(array $r): string => sprintf('%.1f%%', $r['cpu_percent']),
            );
        $lines[] =
            '  Most memory: ' .
            $top(
                $procs['by_memory'],
                fn(array $r): string => humanBytes((int) $r['rss_bytes']),
            );
        $lines[] =
            '  Longest run: ' .
            $top(
                $procs['by_age'],
                fn(array $r): string => humanDuration(
                    (float) $r['elapsed_seconds'],
                ),
            );
    }

    $files = $evidence['files']['rows'] ?? [];
    if ($files) {
        $lines[] = '';
        $lines[] = '  Files that grew most:';
        foreach (array_slice($files, 0, 5) as $file) {
            $lines[] = sprintf(
                '    %s  +%s',
                $file['path'],
                humanBytes((int) $file['grew_bytes']),
            );
        }
    }

    foreach ($evidence['missing'] ?? [] as $why) {
        $lines[] = '';
        $lines[] = '  Not recorded: ' . $why;
    }

    return $lines;
}
