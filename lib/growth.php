<?php
declare(strict_types=1);

/*
 * What grew, what arrived, and what went away.
 *
 * The offenders table answers "what is big", which is the wrong question most
 * of the time. A 40 GB backup directory that has been 40 GB since March is not
 * why the disk filled up this morning; the 900 MB log that was 12 MB yesterday
 * is. Size tells you where the space went, and only change tells you where it
 * is going.
 *
 * Everything here rests on comparing two scans, and two facts about how scans
 * work make that harder than it sounds.
 *
 * Scans are periodic, so a window rarely lines up with one. Asked for "the last
 * hour" on a machine that scans every six, the honest answer is not to invent
 * an hour - it is to compare against the nearest scan and say plainly that the
 * span was six hours. Every view here reports the span it actually measured
 * rather than the one that was asked for.
 *
 * And a scan records only the biggest few hundred paths, because writing every
 * file in the home directory every hour would fill the disk faster than
 * whatever it was watching for. That is fine for "what is big" and treacherous
 * for "what went away": a file missing from the latest scan may have been
 * deleted, or may simply have been pushed off the list by something else
 * growing. Those are completely different events and one of them is not news.
 * So the deleted view does not infer - it looks. The dashboard runs as the
 * account that owns these files and can ask the filesystem directly whether
 * each one is still there, which turns a guess into an observation.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';

/** The views this file can answer. */
const GROWTH_VIEWS = [
    'size',
    'growth',
    'hour',
    'today',
    'week',
    'new',
    'deleted',
];

/** How far back each windowed view reaches. 'today' is handled separately. */
const GROWTH_WINDOWS = ['hour' => 3600, 'week' => 604800];

/**
 * A file has to be at least this big to count as "newly appeared".
 *
 * Otherwise the view fills with the small files that cross the recording
 * threshold in both directions all day, which is churn rather than news.
 */
const GROWTH_NEW_MIN_BYTES = 10485760;

/**
 * Which two scans a view compares, and what span that actually covers.
 *
 * The rule when a window does not line up with a scan, which is most of the
 * time:
 *
 *   Prefer the most recent scan at or before the target, because it covers the
 *   whole window and then some. Report the real span so nobody reads six hours
 *   of growth as one.
 *
 *   If there is no such scan - the window reaches back further than the history
 *   does - use the earliest scan inside it instead. That covers part of the
 *   window rather than all of it, which is the best available answer and is
 *   again reported as the span it really is.
 *
 * @return array{now: ?int, before: ?int, now_at: ?int, before_at: ?int,
 *               requested: ?int, actual: ?int, exact: bool, reason: ?string}
 */
function growthWindow(PDO $pdo, string $view): array
{
    $blank = [
        'now' => null,
        'before' => null,
        'now_at' => null,
        'before_at' => null,
        'requested' => null,
        'actual' => null,
        'exact' => true,
        'reason' => null,
    ];

    $latest = $pdo
        ->query(
            'SELECT id, finished_at FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 1',
        )
        ->fetch();

    if (!$latest) {
        /*
         * Assigned, not merged.
         *
         * `$blank + [...]` looks like it sets the reason and does not: the +
         * operator keeps the left-hand value for any key both sides have, and
         * these arrays already carry a null reason. Every explanation on this
         * path was being silently dropped, which showed up as an empty table
         * with no reason given - on a fresh install, where the reason is the
         * only thing worth saying.
         */
        $blank['reason'] = 'No completed scan yet.';
        return $blank;
    }

    $nowAt = (int) $latest['finished_at'];
    $window = $blank;
    $window['now'] = (int) $latest['id'];
    $window['now_at'] = $nowAt;

    // The two views that compare against the immediately preceding scan.
    if (
        $view === 'size' ||
        $view === 'growth' ||
        $view === 'deleted' ||
        $view === 'new'
    ) {
        $previous = $pdo
            ->query(
                'SELECT id, finished_at FROM scans WHERE finished_at IS NOT NULL
             ORDER BY id DESC LIMIT 1 OFFSET 1',
            )
            ->fetch();

        if (!$previous) {
            $window['reason'] = 'Needs two completed scans to compare.';
            return $window;
        }

        $window['before'] = (int) $previous['id'];
        $window['before_at'] = (int) $previous['finished_at'];
        $window['actual'] = $nowAt - (int) $previous['finished_at'];
        return $window;
    }

    $target =
        $view === 'today' ? startOfDay($nowAt) : $nowAt - GROWTH_WINDOWS[$view];

    $requested = $nowAt - $target;
    $window['requested'] = $requested;

    $before = $pdo->prepare(
        'SELECT id, finished_at FROM scans
         WHERE finished_at IS NOT NULL AND finished_at <= ?
         ORDER BY finished_at DESC LIMIT 1',
    );
    $before->execute([$target]);
    $row = $before->fetch();

    if (!$row) {
        // Nothing old enough. The earliest scan inside the window is the best
        // that can honestly be offered.
        $inside = $pdo->prepare(
            'SELECT id, finished_at FROM scans
             WHERE finished_at IS NOT NULL AND finished_at > ? AND id != ?
             ORDER BY finished_at ASC LIMIT 1',
        );
        $inside->execute([$target, $window['now']]);
        $row = $inside->fetch();
    }

    if (!$row) {
        $window['reason'] =
            'Only one scan falls in this period, so there is nothing ' .
            'to compare it with.';
        return $window;
    }

    $window['before'] = (int) $row['id'];
    $window['before_at'] = (int) $row['finished_at'];
    $window['actual'] = $nowAt - (int) $row['finished_at'];

    // Close enough that the label is not misleading: within a quarter of the
    // window, or within a minute for the short ones.
    $window['exact'] =
        abs($window['actual'] - $requested) <=
        max(60, (int) ($requested * 0.25));

    return $window;
}

/** Midnight local time, in the configured zone - not "twenty-four hours ago". */
function startOfDay(int $at): int
{
    return (int) mktime(
        0,
        0,
        0,
        (int) date('n', $at),
        (int) date('j', $at),
        (int) date('Y', $at),
    );
}

/**
 * Paths that appeared between the two scans.
 *
 * "Appeared" is said carefully. A scan records only the largest paths, so a
 * file can enter this list by being created *or* by growing past the threshold
 * that gets it recorded. first_seen separates them: a path with no history at
 * all before this window is genuinely new to the monitor, and one that has been
 * seen before has merely come back into view. Both are worth showing and they
 * are labelled differently.
 */
function newlyAppeared(
    PDO $pdo,
    array $window,
    array $filters,
    int $limit,
): array {
    $where = ['now.scan_id = :now', 'p.kind = \'file\'', 'now.bytes >= :min'];
    $params = [
        ':now' => $window['now'],
        ':before' => $window['before'],
        ':min' => GROWTH_NEW_MIN_BYTES,
        ':limit' => $limit,
    ];
    applyFilters($filters, $where, $params);

    $sql =
        'SELECT p.id AS path_id, p.path, p.kind, p.category, p.first_seen,
                now.bytes, now.files, now.links,
                now.bytes AS growth, now.files AS growth_files,
                CASE WHEN p.first_seen >= :beforeAt THEN 1 ELSE 0 END AS never_seen_before
         FROM path_sizes now
         JOIN paths p ON p.id = now.path_id
         WHERE ' .
        implode(' AND ', $where) .
        '
           AND NOT EXISTS (
             SELECT 1 FROM path_sizes was
              WHERE was.path_id = now.path_id AND was.scan_id = :before
           )
         ORDER BY now.bytes DESC LIMIT :limit';

    $params[':beforeAt'] = $window['before_at'];

    return runGrowthQuery($pdo, $sql, $params);
}

/**
 * Paths that were in the earlier scan and are not in the later one.
 *
 * Then checked against the filesystem, because the list alone cannot tell a
 * deletion from a demotion - see the note at the top of this file. Each
 * candidate gets one stat, which is a few dozen syscalls against a scan that
 * walked the whole tree, and turns "probably gone" into "gone".
 *
 * Rows the filesystem still finds are kept but marked, because "it dropped out
 * of the largest 25" is a real answer to "where did it go" and hiding it would
 * leave somebody hunting for a file that never moved.
 */
function deletedSince(
    PDO $pdo,
    array $window,
    array $filters,
    int $limit,
): array {
    $where = ['was.scan_id = :before'];
    $params = [
        ':before' => $window['before'],
        ':now' => $window['now'],
        ':limit' => $limit,
    ];
    applyFilters($filters, $where, $params);

    $sql =
        'SELECT p.id AS path_id, p.path, p.kind, p.category,
                was.bytes, was.files, was.links,
                -was.bytes AS growth, -was.files AS growth_files,
                (SELECT MAX(a.at) FROM actions a
                  WHERE a.outcome = \'ok\'
                    AND ((a.action = \'delete_file\' AND a.target = p.path)
                      OR (a.action = \'empty_category\' AND a.target = p.category))
                ) AS deleted_at
         FROM path_sizes was
         JOIN paths p ON p.id = was.path_id
         WHERE ' .
        implode(' AND ', $where) .
        '
           AND NOT EXISTS (
             SELECT 1 FROM path_sizes now
              WHERE now.path_id = was.path_id AND now.scan_id = :now
           )
         ORDER BY was.bytes DESC LIMIT :limit';

    $rows = runGrowthQuery($pdo, $sql, $params);

    foreach ($rows as $i => $row) {
        $path = (string) $row['path'];

        /*
         * The filesystem is the authority here, not the database.
         *
         * file_exists() rather than is_file(): a path that has become
         * unreadable is not the same as one that is gone, and answering "gone"
         * for a permissions change would send somebody looking for a file that
         * is sitting right there.
         */
        $stillThere = $path !== '' && @file_exists($path);

        $rows[$i]['gone'] = !$stillThere;
        $rows[$i]['reason'] = $stillThere
            ? 'still on disk'
            : (($row['deleted_at'] ?? null) !== null
                ? 'deleted from here'
                : 'removed');
    }

    /*
     * Genuinely gone first.
     *
     * The demoted ones are context rather than news, and a list that opens with
     * five files that never moved buries the one that did.
     */
    usort(
        $rows,
        fn(array $a, array $b): int => [$b['gone'], (int) $b['bytes']] <=> [
            $a['gone'],
            (int) $a['bytes'],
        ],
    );

    return $rows;
}

/** Growth between the two scans of the window, biggest first. */
function grewMost(PDO $pdo, array $window, array $filters, int $limit): array
{
    $where = ['now.scan_id = :now'];
    $params = [
        ':now' => $window['now'],
        ':before' => $window['before'],
        ':limit' => $limit,
    ];
    applyFilters($filters, $where, $params);

    $sql =
        'SELECT p.id AS path_id, p.path, p.kind, p.category,
                now.bytes, now.files, now.links,
                now.bytes - was.bytes AS growth,
                now.files - was.files AS growth_files
         FROM path_sizes now
         JOIN path_sizes was ON was.path_id = now.path_id AND was.scan_id = :before
         JOIN paths p ON p.id = now.path_id
         WHERE ' .
        implode(' AND ', $where) .
        '
           AND now.bytes - was.bytes > 0
         ORDER BY growth DESC LIMIT :limit';

    return runGrowthQuery($pdo, $sql, $params);
}

function applyFilters(array $filters, array &$where, array &$params): void
{
    $category = (string) ($filters['category'] ?? '');
    if ($category !== '') {
        $where[] = 'p.category = :category';
        $params[':category'] = $category;
    }

    $kind = (string) ($filters['kind'] ?? '');
    if ($kind === 'file' || $kind === 'dir') {
        $where[] = 'p.kind = :kind';
        $params[':kind'] = $kind;
    }
}

function runGrowthQuery(PDO $pdo, string $sql, array $params): array
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(
            $key,
            $value,
            is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR,
        );
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * A sentence saying what was actually measured.
 *
 * Written here rather than in the browser because it is the one part of this
 * feature most likely to mislead: the view is called "last hour" and the
 * numbers may cover six. Whoever reads the table has to be told which.
 */
function growthSpanNote(array $window, string $view): string
{
    if ($window['reason'] !== null) {
        return (string) $window['reason'];
    }

    $actual = (int) ($window['actual'] ?? 0);
    $span = $actual > 0 ? humanDuration($actual) : 'no time at all';

    if (
        $view === 'deleted' ||
        $view === 'new' ||
        $view === 'growth' ||
        $view === 'size'
    ) {
        return sprintf('Comparing the last two scans, %s apart.', $span);
    }

    $asked =
        [
            'hour' => 'the last hour',
            'today' => 'today',
            'week' => 'the last 7 days',
        ][$view] ?? 'this period';

    if ($window['exact']) {
        return sprintf(
            'Covering %s — the two scans are %s apart.',
            $asked,
            $span,
        );
    }

    return sprintf(
        'Asked for %s, but scans do not line up with it: this covers %s, between the two ' .
            'nearest scans. The numbers are real, the period is just not the one on the label.',
        $asked,
        $span,
    );
}
