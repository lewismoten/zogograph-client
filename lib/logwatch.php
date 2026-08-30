<?php
declare(strict_types=1);

/*
 * The things logs do that other files do not.
 *
 * Logs caused the incident this whole tool was written after: a 10 GB error_log
 * that nobody saw until the disk was full. The generic rules elsewhere catch
 * some of that - a file growing far faster than it usually does will trip the
 * baseline anomaly whatever kind of file it is - but logs have a life cycle,
 * and most of what goes wrong with them is invisible to a rule that only knows
 * how big something is and how fast it changed.
 *
 * Six things worth knowing, and what makes each of them log-specific:
 *
 *   too big          A log has no natural size. A 2 GB database file is a
 *                    database; a 2 GB log is a log nobody has read.
 *
 *   growing fast     Judged in bytes per hour against a fixed ceiling rather
 *                    than against its own history, because a log that has
 *                    *always* written a gigabyte an hour is not thereby fine -
 *                    the baseline rule elsewhere will have made its peace with
 *                    it, and this one will not.
 *
 *   stale .bkup      Webalizer and friends write a .bkup beside the log they
 *                    are processing and remove it when they finish. One still
 *                    there days later is the signature of a job that died
 *                    halfway - which is exactly what happened here, and it sat
 *                    unnoticed because a small file breaks no threshold.
 *
 *   hard links       Two paths, one file, one set of bytes. Deleting either
 *                    frees nothing, and a report that counts both is wrong
 *                    about the disk by exactly the size of the file.
 *
 *   not rotating     A log that never gets smaller is a log whose rotation is
 *                    broken. Nothing else notices: it grows smoothly, breaks no
 *                    threshold on any single day, and is enormous by March.
 *
 *   accelerating     An error log's *second* derivative. A site that has begun
 *                    to fail writes errors faster and faster, and the useful
 *                    moment to hear about it is while the curve is bending, not
 *                    when it crosses a line.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/alerts.php';

/** How many recent scans of history each rule may look at. */
const LOG_HISTORY_SCANS = 60;

/**
 * The thresholds, all configurable.
 *
 * Chosen to be quiet on a healthy account and to have caught the incident that
 * prompted them. A rule nobody trusts gets turned off, and one that never fires
 * might as well be.
 */
function logRules(array $config): array
{
    $set = $config['logs'] ?? [];

    return $set + [
        // Any single log bigger than this, whatever it is doing.
        'max_bytes' => 1073741824, // 1 GiB

        // Bytes an hour, against a ceiling rather than against its own past.
        'fast_bytes_per_hour' => 104857600, // 100 MiB/h

        // A .bkup left behind by a job that did not finish.
        'backup_suffixes' => ['.bkup', '.bak', '.tmp', '.partial'],
        'backup_max_age_hours' => 24,

        // Rotation: a log this big that has not shrunk once in this many days
        // is not being rotated.
        'rotate_after_days' => 8,
        'rotate_min_bytes' => 104857600, // 100 MiB

        // Error-log acceleration: the recent rate against the earlier one.
        'acceleration_multiple' => 4.0,
        'acceleration_floor_per_hour' => 10485760, // 10 MiB/h
        'error_names' => [
            'error_log',
            'error.log',
            'php_errorlog',
            'php-error.log',
        ],
    ];
}

/**
 * Whether a path is a log at all.
 *
 * Category first, because that is what the scanner already decided and it
 * follows the user's own configuration. The name check is a fallback for a log
 * that lives somewhere unexpected - the original incident's error_log was found
 * by name, not by living in a directory called logs.
 */
function isLogPath(array $row, array $rules): bool
{
    if (in_array($row['category'] ?? null, ['logs', 'errors'], true)) {
        return true;
    }

    $name = strtolower(basename((string) $row['path']));
    if (in_array($name, array_map('strtolower', $rules['error_names']), true)) {
        return true;
    }

    return (bool) preg_match('/\.log($|\.)|_log($|\.)|\blog\b/', $name);
}

/**
 * Candidate logs with enough history to reason about.
 *
 * One query for the paths and one for their sizes rather than a join per rule:
 * six detectors over the same handful of files, and re-reading the history six
 * times would be the most expensive thing on the page.
 *
 * @return list<array{path_id:int, path:string, kind:string, category:?string,
 *                    bytes:int, links:int, series:list<array{at:int, bytes:int}>}>
 */
function logCandidates(PDO $pdo, array $config): array
{
    $rules = logRules($config);

    $scans = $pdo
        ->query(
            'SELECT id, finished_at FROM scans WHERE finished_at IS NOT NULL
         ORDER BY id DESC LIMIT ' . LOG_HISTORY_SCANS,
        )
        ->fetchAll(PDO::FETCH_ASSOC);

    if (count($scans) < 1) {
        return [];
    }

    $finished = [];
    foreach ($scans as $scan) {
        $finished[(int) $scan['id']] = (int) $scan['finished_at'];
    }
    $latest = (int) $scans[0]['id'];

    $rows = $pdo->prepare(
        'SELECT ps.scan_id, ps.bytes, ps.links, p.id AS path_id, p.path, p.kind, p.category
         FROM path_sizes ps
         JOIN paths p ON p.id = ps.path_id
         WHERE ps.scan_id IN (' .
            implode(',', array_map('intval', array_keys($finished))) .
            ')
           AND p.kind = \'file\'
         ORDER BY ps.scan_id ASC',
    );
    $rows->execute();

    $byPath = [];
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (!isLogPath($row, $rules)) {
            continue;
        }

        $id = (int) $row['path_id'];
        $byPath[$id] ??= [
            'path_id' => $id,
            'path' => (string) $row['path'],
            'kind' => (string) $row['kind'],
            'category' => $row['category'],
            'bytes' => 0,
            'links' => 1,
            'present' => false,
            'series' => [],
        ];

        $byPath[$id]['series'][] = [
            'at' => $finished[(int) $row['scan_id']],
            'bytes' => (int) $row['bytes'],
        ];

        if ((int) $row['scan_id'] === $latest) {
            $byPath[$id]['bytes'] = (int) $row['bytes'];
            $byPath[$id]['links'] = max(1, (int) $row['links']);
            $byPath[$id]['present'] = true;
        }
    }

    return array_values($byPath);
}

/* ------------------------------------------------------------------ *
 * The rules
 * ------------------------------------------------------------------ */

/** A log bigger than any log has business being. */
function oversizeLogs(array $logs, array $rules): array
{
    $limit = (int) $rules['max_bytes'];
    if ($limit <= 0) {
        return [];
    }

    $out = [];
    foreach ($logs as $log) {
        if (!$log['present'] || $log['bytes'] < $limit) {
            continue;
        }

        $out[] = logAlert(
            $log,
            'log_oversize',
            'warning',
            sprintf(
                '%s is %s',
                basename($log['path']),
                humanBytes($log['bytes']),
            ),
            sprintf(
                "%s\n  A single log file has reached %s, past the %s that this account " .
                    "considers reasonable for one.\n\n" .
                    'Nothing reads a log this large. If it matters, rotate and compress it; ' .
                    'if it does not, it is %s of disk doing nothing.',
                $log['path'],
                humanBytes($log['bytes']),
                humanBytes($limit),
                humanBytes($log['bytes']),
            ),
        );
    }

    return $out;
}

/**
 * A log writing faster than any log should, measured against a ceiling.
 *
 * Deliberately not a baseline comparison. The baseline rule in anomalies.php
 * asks "is this unusual for this file", and answers no for a log that has been
 * writing a gigabyte an hour all week - which is the point at which somebody
 * needs to be told, not the point at which it stops being interesting.
 */
function fastGrowingLogs(array $logs, array $rules): array
{
    $ceiling = (float) $rules['fast_bytes_per_hour'];
    if ($ceiling <= 0) {
        return [];
    }

    $out = [];
    foreach ($logs as $log) {
        $rate = recentRate($log['series']);
        if ($rate === null || $rate < $ceiling) {
            continue;
        }

        $out[] = logAlert(
            $log,
            'log_fast',
            'warning',
            sprintf(
                '%s is writing %s an hour',
                basename($log['path']),
                humanBytes((int) $rate),
            ),
            sprintf(
                "%s\n  Growing at %s an hour, above the %s an hour this account treats as " .
                    "too fast for a log.\n\n" .
                    'At that rate it adds %s a day. This is a ceiling rather than a comparison ' .
                    'with its own past: a log that has always written this fast is not thereby ' .
                    'fine.',
                $log['path'],
                humanBytes((int) $rate),
                humanBytes((int) $ceiling),
                humanBytes((int) ($rate * 24)),
            ),
        );
    }

    return $out;
}

/**
 * A .bkup left behind by a job that did not finish.
 *
 * The signature of the original incident. Webalizer writes one of these beside
 * the log it is chewing on and removes it when it is done, so one still sitting
 * there a day later means the run died - and because the file is small, nothing
 * else on this page would ever mention it.
 *
 * The age comes from the filesystem rather than the database. first_seen only
 * says when this monitor first recorded the path, which for a file that existed
 * before the monitor did is a date with no meaning.
 */
function staleBackups(array $logs, array $rules): array
{
    $maxAge = max(1, (int) $rules['backup_max_age_hours']) * 3600;
    $suffixes = array_map('strtolower', (array) $rules['backup_suffixes']);
    $now = time();

    $out = [];
    foreach ($logs as $log) {
        if (!$log['present']) {
            continue;
        }

        $name = strtolower(basename($log['path']));
        $matched = null;
        foreach ($suffixes as $suffix) {
            if (
                str_ends_with($name, $suffix) ||
                str_contains($name, $suffix . '.')
            ) {
                $matched = $suffix;
                break;
            }
        }
        if ($matched === null) {
            continue;
        }

        $mtime = @filemtime($log['path']);
        if ($mtime === false) {
            // Gone since the scan, which is the outcome this rule wants anyway.
            continue;
        }

        $age = $now - $mtime;
        if ($age < $maxAge) {
            continue;
        }

        $out[] = logAlert(
            $log,
            'log_stale_backup',
            'warning',
            sprintf(
                '%s has been left behind for %s',
                basename($log['path']),
                humanDuration($age),
            ),
            sprintf(
                "%s\n  Last written %s ago, and it is a %s file - the kind a job creates " .
                    "while it works and removes when it finishes.\n\n" .
                    'One still here after %s means the job that made it did not finish. It is ' .
                    'only %s, so nothing else on this page would ever mention it, and the job ' .
                    'will most likely make another one on its next run.',
                $log['path'],
                humanDuration($age),
                $matched,
                humanDuration($maxAge),
                humanBytes($log['bytes']),
            ),
        );
    }

    return $out;
}

/**
 * Several names for one file.
 *
 * The size is real and it is counted once by the disk, however many directory
 * entries point at it. Worth reporting for two opposite reasons: a report that
 * adds both entries overstates the disk by the size of the file, and somebody
 * deleting one of them to free space will free nothing at all.
 *
 * Grouped by inode, which needs a stat - path_sizes records how many links a
 * file has but not which other paths they are.
 */
function duplicatedLogs(array $logs, array $rules): array
{
    $groups = [];

    foreach ($logs as $log) {
        if (!$log['present']) {
            continue;
        }

        /*
         * Every candidate is stat-ed, not only those the scan recorded as
         * linked. A link made after the last scan leaves path_sizes.links
         * saying 1, and this rule would then miss exactly the case it exists
         * for. Logs are a small set; the filesystem is the authority.
         */
        $stat = @stat($log['path']);
        if ($stat === false || (int) ($stat['nlink'] ?? 1) < 2) {
            continue;
        }

        $groups[$stat['dev'] . ':' . $stat['ino']][] = $log;
    }

    $out = [];
    foreach ($groups as $key => $members) {
        /*
         * Only when this scan actually found more than one of the names.
         *
         * A file can have two links where the other one lives outside the
         * watched directory, or below the size the scan records. Reporting
         * "duplicated" while being able to name only one path would send
         * somebody hunting for a second file this tool cannot see - the link
         * count is mentioned in the detail instead.
         */
        if (count($members) < 2) {
            continue;
        }

        usort(
            $members,
            fn(array $a, array $b): int => strcmp($a['path'], $b['path']),
        );
        $first = $members[0];
        $paths = array_column($members, 'path');

        $out[] = logAlert(
            $first,
            'log_duplicated',
            'warning',
            sprintf(
                '%s is the same file as %s',
                basename($paths[0]),
                count($paths) === 2
                    ? basename($paths[1])
                    : count($paths) - 1 . ' other names',
            ),
            sprintf(
                "%s\n\n  These %d paths are one file of %s, hard-linked:\n%s\n\n" .
                    'The disk holds those bytes once. Deleting any one of these names frees ' .
                    'nothing while another survives, and any total that adds them up is over ' .
                    'by %s.',
                $first['path'],
                count($paths),
                humanBytes($first['bytes']),
                '    ' . implode("\n    ", $paths),
                humanBytes($first['bytes'] * (count($paths) - 1)),
            ),
        );
    }

    return $out;
}

/**
 * A log that never gets smaller.
 *
 * Rotation shows up in the history as a drop: the file is renamed or truncated
 * and starts again near zero. A large log with no drop anywhere in a week of
 * scans is one whose rotation is not running - which nothing else notices,
 * because it grows smoothly, crosses no threshold on any given day, and is
 * enormous by the spring.
 */
function unrotatedLogs(PDO $pdo, array $logs, array $rules): array
{
    $minBytes = (int) $rules['rotate_min_bytes'];
    $needSeconds = max(1, (int) $rules['rotate_after_days']) * 86400;
    if ($minBytes <= 0) {
        return [];
    }

    /*
     * This rule fetches its own history, and has to.
     *
     * logCandidates() reads the last LOG_HISTORY_SCANS scans, which is the
     * right window for "how fast is this growing right now" and useless here:
     * on an hourly schedule sixty scans is two and a half days, so a rule that
     * wants to see eight days without a fall could never once fire. A question
     * about a span of time has to be asked in units of time.
     *
     * Only for logs already big enough to matter, so this is a handful of
     * queries rather than one per log on the disk.
     */
    $history = $pdo->prepare(
        'SELECT ps.bytes, s.finished_at
         FROM path_sizes ps
         JOIN scans s ON s.id = ps.scan_id
         WHERE ps.path_id = :path AND s.finished_at IS NOT NULL AND s.finished_at >= :since
         ORDER BY s.finished_at ASC',
    );

    $out = [];
    $seenInodes = [];

    foreach ($logs as $log) {
        if (!$log['present'] || $log['bytes'] < $minBytes) {
            continue;
        }

        /*
         * One report per file, not one per name.
         *
         * A hard-linked pair is a single file with a single history, and
         * flagging both names is precisely the double-counting the duplicate
         * rule exists to warn about - a poor look for this file to make the
         * mistake it complains about.
         *
         * The filesystem is asked, not the stored link count. path_sizes.links
         * was true when the scan ran, and a second name created since then
         * would leave it saying 1 while the file has two - so trusting it here
         * would make correctness depend on how recently the scan happened.
         * These are only the logs already past the size floor, so it is a
         * handful of stats.
         */
        $stat = @stat($log['path']);
        if ($stat !== false) {
            $inode = $stat['dev'] . ':' . $stat['ino'];
            if (isset($seenInodes[$inode])) {
                continue;
            }
            $seenInodes[$inode] = true;
        }

        $history->execute([
            ':path' => $log['path_id'],
            // A little more than the window, so the span can actually reach it.
            ':since' => time() - $needSeconds - 86400,
        ]);
        $series = $history->fetchAll(PDO::FETCH_ASSOC);

        if (count($series) < 3) {
            continue;
        }

        $span =
            (int) $series[count($series) - 1]['finished_at'] -
            (int) $series[0]['finished_at'];
        if ($span < $needSeconds) {
            continue;
        }

        $shrank = false;
        for ($i = 1; $i < count($series); $i++) {
            if ((int) $series[$i]['bytes'] < (int) $series[$i - 1]['bytes']) {
                $shrank = true;
                break;
            }
        }
        if ($shrank) {
            continue;
        }

        $grew = $log['bytes'] - (int) $series[0]['bytes'];

        /*
         * A log that is not growing has nothing to rotate.
         *
         * Without this the rule fires for ever on every large static file that
         * happens to be named like a log - an archived access_log that has not
         * been written to since March never falls, because nothing is
         * appending to it either. Rotation is only missing if something is
         * still accumulating.
         *
         * Measured against the file's own size so that the test means the same
         * thing for a 100 MiB log as for a 40 GiB one.
         */
        if ($grew < max(1048576, (int) ($log['bytes'] * 0.05))) {
            continue;
        }

        $out[] = logAlert(
            $log,
            'log_unrotated',
            'warning',
            sprintf(
                '%s has not been rotated in %s',
                basename($log['path']),
                humanDuration($span),
            ),
            sprintf(
                "%s\n  Now %s, and it has not got smaller once in %s of scans - it grew by " .
                    "%s over that period without a single drop.\n\n" .
                    'Rotation shows up here as a fall, when the file is renamed or truncated ' .
                    'and starts again. A log this size with no fall at all is one whose ' .
                    'rotation is not running.',
                $log['path'],
                humanBytes($log['bytes']),
                humanDuration($span),
                humanBytes($grew),
            ),
        );
    }

    return $out;
}

/**
 * An error log whose growth is speeding up.
 *
 * The second derivative, and the only rule here that tries to be early rather
 * than accurate. A site that has begun to fail writes errors faster and faster;
 * the useful moment to hear about it is while the curve is bending, which is
 * before any threshold built on size or rate has been crossed.
 *
 * Two halves of the recent history compared against each other. Requiring both
 * a multiple and a floor keeps it quiet: doubling from nothing is still nothing.
 */
function acceleratingErrorLogs(array $logs, array $rules): array
{
    $multiple = (float) $rules['acceleration_multiple'];
    $floor = (float) $rules['acceleration_floor_per_hour'];
    $names = array_map('strtolower', (array) $rules['error_names']);

    $out = [];
    foreach ($logs as $log) {
        if (!$log['present'] || count($log['series']) < 6) {
            continue;
        }

        $name = strtolower(basename($log['path']));
        $isError =
            ($log['category'] ?? null) === 'errors' ||
            in_array($name, $names, true) ||
            str_contains($name, 'error');
        if (!$isError) {
            continue;
        }

        $series = $log['series'];
        $half = intdiv(count($series), 2);
        $earlier = rateOver(array_slice($series, 0, $half + 1));
        $recent = rateOver(array_slice($series, $half));

        if ($earlier === null || $recent === null || $recent < $floor) {
            continue;
        }

        /*
         * A rate of zero earlier is not a division to attempt, and it is the
         * strongest version of this signal rather than the weakest: a log that
         * was doing nothing and is now doing something.
         */
        $times = $earlier > 0 ? $recent / $earlier : INF;
        if ($times < $multiple) {
            continue;
        }

        $out[] = logAlert(
            $log,
            'log_accelerating',
            'critical',
            sprintf('%s is filling faster and faster', basename($log['path'])),
            sprintf(
                "%s\n  Writing %s an hour now, against %s an hour over the earlier half of " .
                    "the same history - %s.\n\n" .
                    'An error log that is speeding up usually means something has started ' .
                    'failing and is retrying. The size is not the problem yet; the shape is.',
                $log['path'],
                humanBytes((int) $recent),
                $earlier > 0 ? humanBytes((int) $earlier) : 'almost nothing',
                is_infinite($times)
                    ? 'it was effectively idle before'
                    : sprintf('%.1f times faster', $times),
            ),
        );
    }

    return $out;
}

/* ------------------------------------------------------------------ *
 * Shared arithmetic
 * ------------------------------------------------------------------ */

/** Bytes an hour between the last two points, or null if it cannot be had. */
function recentRate(array $series): ?float
{
    if (count($series) < 2) {
        return null;
    }

    $last = $series[count($series) - 1];
    $previous = $series[count($series) - 2];
    $hours = ($last['at'] - $previous['at']) / 3600;

    if ($hours <= 0) {
        return null;
    }

    $grew = $last['bytes'] - $previous['bytes'];

    // A rotation is a fall, not a negative rate. Nothing to say about speed
    // across the moment a file started again from zero.
    return $grew < 0 ? null : $grew / $hours;
}

/** Bytes an hour across a run of points, ignoring any rotation inside it. */
function rateOver(array $series): ?float
{
    if (count($series) < 2) {
        return null;
    }

    $grew = 0;
    for ($i = 1; $i < count($series); $i++) {
        $delta = $series[$i]['bytes'] - $series[$i - 1]['bytes'];
        // A drop is a rotation; the bytes before it were still written, so the
        // step contributes what it can rather than a negative.
        $grew += max(0, $delta);
    }

    $hours = ($series[count($series) - 1]['at'] - $series[0]['at']) / 3600;

    return $hours > 0 ? $grew / $hours : null;
}

/** One alert, shaped the way recordAlert and presentAlert both expect. */
function logAlert(
    array $log,
    string $rule,
    string $severity,
    string $subject,
    string $detail,
): array {
    return [
        // Per path, so one noisy log cannot silence the rule for every other.
        'rule' => $rule . ':' . $log['path_id'],
        'severity' => $severity,
        'subject' => $subject,
        'detail' => $detail,
        'path_id' => $log['path_id'],
        'path' => $log['path'],
        'kind' => $log['kind'],
    ];
}

/**
 * Everything the log rules have to say.
 */
function logAlerts(PDO $pdo, array $config): array
{
    $rules = logRules($config);
    if (($rules['enabled'] ?? true) === false) {
        return [];
    }

    $logs = logCandidates($pdo, $config);
    if ($logs === []) {
        return [];
    }

    return [
        ...oversizeLogs($logs, $rules),
        ...fastGrowingLogs($logs, $rules),
        ...staleBackups($logs, $rules),
        ...duplicatedLogs($logs, $rules),
        ...unrotatedLogs($pdo, $logs, $rules),
        ...acceleratingErrorLogs($logs, $rules),
    ];
}
