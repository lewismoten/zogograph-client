<?php
declare(strict_types=1);

/*
 * What is using the machine.
 *
 * Two halves, and the second is the one that matters. A live list is `top` with
 * extra steps - useful, but you have to be watching. The recorded half answers
 * the question this whole project exists for: something pinned the CPU at four
 * in the morning, what was it.
 *
 * Recording every process every minute would be a table growing faster than the
 * disk it is watching, so it only writes when the machine is actually busy.
 * When nothing is wrong there is nothing worth keeping.
 *
 * Everything here is signed-in only. A command line is not a size: it carries
 * paths, arguments, and often enough a password somebody put on a command line
 * despite being told not to. Those get redacted too, because they end up in a
 * database and on a screen.
 */

require_once __DIR__ . '/collect.php';

/** Processes with the same name are one row: forty php-fpm workers are one thing. */
const PROCESS_KEEP_DEFAULT = 8;

/**
 * A snapshot of what is running, biggest first.
 *
 * `ps -eo` rather than anything cleverer because it is the one invocation that
 * behaves the same on Linux and BSD - `--sort` is procps only, so the sorting
 * happens here instead.
 *
 * @return array{available: bool, reason: ?string, rows: list<array>}
 */
function listProcesses(
    string $by = 'cpu',
    int $limit = 20,
    bool $group = true,
): array {
    // `etime` rather than `etimes`: the seconds form is procps only, and the
    // clock form is understood by both. It has no spaces, so it stays a field.
    // ppid is here so the collector can identify its own tree and leave itself
    // out of the record - see excludeOwnTree().
    $out = shellRead(
        'ps -eo pid,ppid,user,pcpu,pmem,rss,etime,args 2>/dev/null',
    );
    if ($out === null) {
        return [
            'available' => false,
            'reason' => 'This host does not allow running ps from PHP.',
            'rows' => [],
        ];
    }

    $lines = preg_split('/\R/', trim($out)) ?: [];
    array_shift($lines);

    $rows = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        // Eight fields, and the last one keeps its spaces because it is the
        // command line.
        $parts = preg_split('/\s+/', $line, 8);
        if (!$parts || count($parts) < 8) {
            continue;
        }

        [$pid, $ppid, $user, $cpu, $mem, $rss, $etime, $args] = $parts;
        if (!ctype_digit($pid)) {
            continue;
        }

        $rows[] = [
            'pid' => (int) $pid,
            'ppid' => (int) $ppid,
            'user' => $user,
            'cpu_percent' => (float) $cpu,
            'mem_percent' => (float) $mem,
            // ps reports RSS in kilobytes on both platforms.
            'rss_bytes' => (int) $rss * 1024,
            'elapsed_seconds' => parseElapsed($etime),
            'name' => processName($args),
            'command' => redactSecrets($args),
        ];
    }

    /*
     * Grouped by name, because that is the answer to the question.
     *
     * Fifteen Chrome helpers using two gigabytes between them is one fact, and
     * shown a row apiece it is fifteen rows none of which look alarming. The
     * heaviest process keeps its pid and its command line so there is still
     * something to go and look at.
     */
    if ($group) {
        $rows = groupByName($rows);
    }

    $key = $by === 'memory' ? 'rss_bytes' : 'cpu_percent';
    usort($rows, fn(array $a, array $b): int => $b[$key] <=> $a[$key]);

    return [
        'available' => true,
        'reason' => null,
        'rows' => array_slice($rows, 0, $limit),
    ];
}

/**
 * How long a process has been running, in seconds.
 *
 * `ps` writes elapsed time as `[[DD-]HH:]MM:SS`, which is readable and useless
 * for arithmetic. Null rather than zero when it cannot be read: zero would mean
 * "just started", which is the opposite of "not known" and exactly the sort of
 * thing somebody would act on.
 */
function parseElapsed(string $etime): ?int
{
    if (
        !preg_match('/^(?:(\d+)-)?(?:(\d+):)?(\d+):(\d+)$/', trim($etime), $m)
    ) {
        return null;
    }

    return ((int) ($m[1] ?: 0)) * 86400 +
        ((int) ($m[2] ?: 0)) * 3600 +
        ((int) $m[3]) * 60 +
        (int) $m[4];
}

/**
 * The short name a process should be grouped under.
 *
 * The first word of the command line, without its path - so every worker of the
 * same daemon lands in one row. Interpreters are the exception worth handling:
 * a hundred `php` processes tell you nothing, and `php cron.php` tells you
 * everything, so the script name comes along.
 *
 * Taken from `args` rather than from `comm`, which would be tidier on Linux and
 * unparseable on BSD: `comm` there is a full path that can contain spaces, and
 * a field with spaces in the middle of a space-separated line cannot be split
 * on reliably. The cost is that an executable whose *path* contains a space
 * shows only the part before it - vanishingly rare on a server, common in macOS
 * application bundles, so it looks worse on a laptop than where it will run.
 */
function processName(string $args): string
{
    $parts = preg_split('/\s+/', trim($args)) ?: [''];
    $name = basename($parts[0] ?? '');

    if (preg_match('/^(php|python[\d.]*|perl|ruby|node|bash|sh)$/i', $name)) {
        foreach (array_slice($parts, 1) as $part) {
            // The first thing that is not a flag is the script.
            if ($part !== '' && $part[0] !== '-') {
                return $name . ' ' . basename($part);
            }
        }
    }

    return $name !== '' ? $name : '(unknown)';
}

/**
 * Removes the collector and everything it started from a process list.
 *
 * The record of a busy moment kept filling up with `ps`, `sh -c ps`,
 * `php cron.php` and the shell between them - the observer, its own
 * measurement, and nothing else. On an account where a single page render can
 * cross the threshold, that left snapshots where every visible process read
 * 0.0% and the thing that actually caused it had already exited.
 *
 * Both directions have to go. Upwards is the collector itself and whatever
 * launched it: cron, the shell, the PHP binary. Downwards is the `ps` this very
 * function is reading the output of, which cannot help but appear in it.
 *
 * @param list<array> $rows Ungrouped rows carrying pid and ppid.
 * @return list<array>
 */
function excludeOwnTree(array $rows): array
{
    $me = getmypid();
    if ($me === false) {
        return $rows;
    }

    $parentOf = [];
    $childrenOf = [];
    foreach ($rows as $row) {
        $parentOf[$row['pid']] = $row['ppid'];
        $childrenOf[$row['ppid']][] = $row['pid'];
    }

    $drop = [];

    // Up: this process and everything that launched it.
    $at = $me;
    $hops = 0;
    while ($at > 1 && $hops++ < 40) {
        $drop[$at] = true;
        $next = $parentOf[$at] ?? 0;
        if ($next < 2 || isset($drop[$next])) {
            break;
        }
        $at = $next;
    }

    // Down: the shell and the `ps` it ran, and anything else we spawned.
    $queue = [$me];
    while ($queue) {
        $pid = array_pop($queue);
        foreach ($childrenOf[$pid] ?? [] as $child) {
            if (!isset($drop[$child])) {
                $drop[$child] = true;
                $queue[] = $child;
            }
        }
    }

    return array_values(
        array_filter($rows, fn(array $row): bool => !isset($drop[$row['pid']])),
    );
}

/**
 * Blanks out anything on a command line that looks like a secret.
 *
 * People put passwords in arguments. `ps` shows them to anyone on the box, and
 * without this they would be written into the database and rendered in a
 * browser as well - turning a monitoring tool into a second place the password
 * leaks from. It is a blunt filter and it does not need to be clever: erring
 * towards hiding an innocent argument costs nothing here.
 */
function redactSecrets(string $args): string
{
    $patterns = [
        // -pSECRET, as mysql and friends take it.
        '/(^|\s)(-p)(?!\s)\S+/i' => '$1$2[hidden]',
        // --password=SECRET, --token SECRET, api_key=SECRET, and the like.
        '/(--?(?:password|passwd|pwd|token|secret|api[-_]?key|auth)[=\s])\S+/i' =>
            '$1[hidden]',
        '/((?:password|passwd|pwd|token|secret|auth|api[-_]?key|access[-_]?key)=)\S+/i' =>
            '$1[hidden]',
    ];

    $clean = $args;
    foreach ($patterns as $pattern => $replacement) {
        $clean = (string) preg_replace($pattern, $replacement, $clean);
    }

    return mb_substr($clean, 0, 400);
}

/**
 * Writes down what was running, but only when something was worth writing down.
 *
 * Called after each vitals reading. The thresholds are the whole design: a
 * table of every process every minute grows faster than anything it could warn
 * you about, and a table of what was running during the three minutes the CPU
 * was pinned is small and answers the question.
 */
function recordProcesses(array $config, PDO $pdo, array $vitals): array
{
    $rules = $config['processes'] ?? [];
    $cpuAbove = (float) ($rules['record_when_cpu_above'] ?? 70);
    $memoryAbove = (float) ($rules['record_when_memory_above'] ?? 85);

    $cpu = $vitals['cpu_percent'];
    $memory = $vitals['mem_used_percent'];

    $reasons = [];
    if ($cpu !== null && $cpu >= $cpuAbove) {
        $reasons[] = 'cpu';
    }
    if ($memory !== null && $memory >= $memoryAbove) {
        $reasons[] = 'memory';
    }
    if (!$reasons) {
        return ['recorded' => 0, 'reasons' => []];
    }

    // Ungrouped, so the tree can be walked, then grouped afterwards.
    $snapshot = listProcesses('cpu', 5000, false);
    if (!$snapshot['available']) {
        return ['recorded' => 0, 'reasons' => $reasons];
    }

    $grouped = groupByName(excludeOwnTree($snapshot['rows']));
    usort(
        $grouped,
        fn(array $a, array $b): int => $b['cpu_percent'] <=> $a['cpu_percent'],
    );

    $keep = max(1, (int) ($rules['keep'] ?? PROCESS_KEEP_DEFAULT));

    /*
     * What triggered it, alongside what the snapshot can explain.
     *
     * Stored because the two often disagree and the gap is the useful part. A
     * reading of 78% with 3% visible in the process list means whatever did it
     * finished before `ps` ran - which is a real answer, and a different one
     * from "the machine is under sustained load". Without both figures the
     * panel can only show a list of near-idle processes under a heading that
     * says the machine was busy.
     */
    $accounted = 0.0;
    foreach (array_slice($grouped, 0, $keep) as $row) {
        $accounted += (float) $row['cpu_percent'];
    }

    $insert = $pdo->prepare(
        'INSERT INTO process_samples
            (taken_at, reason, name, cpu_percent, mem_percent, rss_bytes, procs,
             oldest_seconds, newest_seconds, trigger_percent, accounted_percent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    );

    $written = 0;
    foreach ($reasons as $reason) {
        $ranked = $grouped;
        usort(
            $ranked,
            $reason === 'memory'
                ? fn(array $a, array $b): int => $b['rss_bytes'] <=>
                    $a['rss_bytes']
                : fn(array $a, array $b): int => $b['cpu_percent'] <=>
                    $a['cpu_percent'],
        );

        foreach (array_slice($ranked, 0, $keep) as $row) {
            $insert->execute([
                (int) $vitals['taken_at'],
                $reason,
                $row['name'],
                round($row['cpu_percent'], 2),
                round($row['mem_percent'], 2),
                $row['rss_bytes'],
                $row['procs'],
                $row['oldest_seconds'],
                $row['newest_seconds'],
                round($reason === 'memory' ? (float) $memory : (float) $cpu, 2),
                round($reason === 'memory' ? 0.0 : $accounted, 2),
            ]);
            $written++;
        }
    }

    return ['recorded' => $written, 'reasons' => $reasons];
}

/**
 * Adds together every process sharing a name.
 *
 * The totals are sums, since that is what "how much is this thing costing me"
 * means. The pid and command kept are the heaviest single process of the group,
 * because those are only useful for going and looking at one of them, and the
 * biggest is the one worth looking at.
 */
function groupByName(array $rows): array
{
    $byName = [];

    foreach ($rows as $row) {
        $name = $row['name'];

        if (!isset($byName[$name])) {
            $byName[$name] = [
                'name' => $name,
                'cpu_percent' => 0.0,
                'mem_percent' => 0.0,
                'rss_bytes' => 0,
                'procs' => 0,
                'user' => $row['user'],
                'pid' => $row['pid'],
                'command' => $row['command'],
                /*
                 * The oldest and the youngest of the group.
                 *
                 * Both, because together they say something neither says
                 * alone: a pool up for forty days whose newest member is nine
                 * seconds old is churning, and that is a different fault from
                 * one that has simply been running a long time.
                 */
                'oldest_seconds' => null,
                'newest_seconds' => null,
                'heaviest' => -1,
            ];
        }

        $into = &$byName[$name];
        $into['cpu_percent'] += $row['cpu_percent'];
        $into['mem_percent'] += $row['mem_percent'];
        $into['rss_bytes'] += $row['rss_bytes'];
        $into['procs']++;

        $elapsed = $row['elapsed_seconds'];
        if ($elapsed !== null) {
            $into['oldest_seconds'] =
                $into['oldest_seconds'] === null
                    ? $elapsed
                    : max($into['oldest_seconds'], $elapsed);
            $into['newest_seconds'] =
                $into['newest_seconds'] === null
                    ? $elapsed
                    : min($into['newest_seconds'], $elapsed);
        }

        if ($row['rss_bytes'] > $into['heaviest']) {
            $into['heaviest'] = $row['rss_bytes'];
            $into['pid'] = $row['pid'];
            $into['command'] = $row['command'];
            $into['user'] = $row['user'];
        }
        unset($into);
    }

    foreach ($byName as &$row) {
        unset($row['heaviest']);
    }

    return array_values($byName);
}

/**
 * The busy moments that were recorded, newest first.
 *
 * Grouped by the instant they were taken, so the answer reads as "at 04:12 the
 * CPU was pinned and these five things were running" rather than as a flat list
 * of rows somebody has to reassemble.
 */
/*
 * How near in time an alert has to be before it is worth mentioning.
 *
 * Defined in a library rather than beside the endpoint that uses it: a
 * top-level `const` runs when execution reaches it, and api.php has answered
 * the request and finished long before it reaches its own bottom half.
 */
const BUSY_ALERT_WINDOW = 120;

function recordedProcesses(PDO $pdo, int $limit = 12): array
{
    $moments = $pdo->prepare(
        'SELECT DISTINCT taken_at, reason FROM process_samples
         ORDER BY taken_at DESC LIMIT :limit',
    );
    $moments->bindValue(':limit', $limit, PDO::PARAM_INT);
    $moments->execute();

    $rows = $pdo->prepare(
        'SELECT name, cpu_percent, mem_percent, rss_bytes, procs,
                oldest_seconds, newest_seconds, trigger_percent, accounted_percent
         FROM process_samples WHERE taken_at = ? AND reason = ?
         ORDER BY ' .
            '(CASE WHEN ? = \'memory\' THEN rss_bytes ELSE cpu_percent END) DESC',
    );

    $out = [];
    foreach ($moments->fetchAll() as $moment) {
        $rows->execute([
            $moment['taken_at'],
            $moment['reason'],
            $moment['reason'],
        ]);
        $out[] = [
            'taken_at' => (int) $moment['taken_at'],
            'reason' => $moment['reason'],
            'rows' => $rows->fetchAll(),
        ];
    }

    return $out;
}

/**
 * The recorded process trend for one requested window.
 *
 * A PID is deliberately absent: short jobs and restarted workers get a new
 * PID before the next busy snapshot. `name` is the same stable grouping used
 * when the snapshot is written. Recent reporters get most of the chart slots,
 * while peak ranking fills the remainder so an overnight culprit is not lost
 * merely because it is quiet now.
 *
 * @return array{metric: string, names: list<string>, rows: list<array<string, int|float|null>>}
 */
function processTrend(
    PDO $pdo,
    int $from,
    int $to,
    string $metric,
    int $limit = 10,
): array {
    $metric = $metric === 'memory' ? 'memory' : 'cpu';
    $limit = max(1, min(10, $limit));
    $peak = $metric === 'memory' ? 'MAX(rss_bytes)' : 'MAX(cpu_percent)';
    $summary = $pdo->prepare(
        'SELECT name, MAX(taken_at) AS last_at, ' .
            $peak .
            ' AS peak
           FROM process_samples
          WHERE taken_at BETWEEN ? AND ?
          GROUP BY name',
    );
    $summary->execute([$from, $to]);
    $all = $summary->fetchAll();

    $recent = $all;
    usort(
        $recent,
        fn(array $a, array $b): int => (int) $b['last_at'] <=>
        (int) $a['last_at'] ?:
        (float) $b['peak'] <=> (float) $a['peak'] ?:
        strcmp((string) $a['name'], (string) $b['name']),
    );
    $heavy = $all;
    usort(
        $heavy,
        fn(array $a, array $b): int => (float) $b['peak'] <=>
        (float) $a['peak'] ?:
        (int) $b['last_at'] <=> (int) $a['last_at'] ?:
        strcmp((string) $a['name'], (string) $b['name']),
    );

    $chosen = [];
    // Seven current/recent answers, then the best historic peaks. The exact
    // split makes recency the default without turning "was it webalizer last
    // night?" into a question the chart cannot answer.
    foreach (array_merge(array_slice($recent, 0, 7), $heavy, $recent) as $row) {
        $name = (string) $row['name'];
        if (!isset($chosen[$name])) {
            $chosen[$name] = true;
        }
        if (count($chosen) >= $limit) {
            break;
        }
    }
    $names = array_keys($chosen);
    if ($names === []) {
        return ['metric' => $metric, 'names' => [], 'rows' => []];
    }

    $holders = implode(', ', array_fill(0, count($names), '?'));
    $value = $metric === 'memory' ? 'MAX(rss_bytes)' : 'MAX(cpu_percent)';
    $rows = $pdo->prepare(
        'SELECT taken_at, name, ' .
            $value .
            ' AS value
           FROM process_samples
          WHERE taken_at BETWEEN ? AND ? AND name IN (' .
            $holders .
            ')
          GROUP BY taken_at, name
          ORDER BY taken_at, name',
    );
    $rows->execute([$from, $to, ...$names]);

    $points = [];
    foreach ($rows->fetchAll() as $row) {
        $at = (int) $row['taken_at'];
        if (!isset($points[$at])) {
            $points[$at] = ['taken_at' => $at];
        }
        $points[$at][(string) $row['name']] =
            $row['value'] === null ? null : (float) $row['value'];
    }

    return [
        'metric' => $metric,
        'names' => $names,
        'rows' => array_values($points),
    ];
}
