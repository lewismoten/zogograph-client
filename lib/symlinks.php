<?php
declare(strict_types=1);

/*
 * Symlinks that eat things.
 *
 * The scanner steps over every symlink it meets, deliberately - following them
 * double counts at best and walks out of the scan root at worst. The
 * consequence is that a bad link is invisible to this tool while still being
 * the thing that ruins everything else: a link pointing at one of its own
 * ancestors is an infinitely deep directory to anything that *does* follow
 * links, and backup jobs, `du`, `tar` and web crawlers all do. That is how a
 * disk fills up with one file copied ten thousand times.
 *
 * Four things are worth reporting:
 *
 *   loop      the chain of links comes back to a link it already passed
 *   ancestor  it resolves to a directory containing the link itself, so
 *             anything that follows it recurses until it runs out of something
 *   broken    it points at nothing, which is untidy rather than dangerous
 *   outside   it leaves the watched directory, so sizes measured here are not
 *             the sizes something following it would see
 *
 * The walk never follows a link. It reads where each one points and reasons
 * about the path, which is the only safe way to inspect a loop.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/collect.php';

/** Longest chain of links followed before calling it a loop regardless. */
const SYMLINK_MAX_HOPS = 40;

/** Most problems collected in one walk. A backstop, not a page size. */
const SYMLINK_FIND_MAX = 20000;

/** Most problems drawn on the page at once. Display only - nothing acts on this. */
const SYMLINK_SHOW_MAX = 300;

/**
 * Walks the tree looking for symlinks that will hurt something.
 *
 * Bounded by wall clock rather than by entries, because the thing that varies
 * between accounts is how slow the disk is, not how many files there are. It
 * says when it stopped early: a check that quietly gave up and reported
 * "nothing wrong" would be worse than not having one.
 *
 * Everything found is returned, not a page of it.
 *
 * An earlier version cut the list to 200 after sorting worst-first, which was
 * fine for reading and wrong for everything else. On an account whose `.cpanel`
 * directory holds a couple of hundred links pointing at the system PHP - all
 * `outside`, all sorted ahead of `broken` - those two hundred slots were gone
 * before a single broken link got one. Deleting the handful that showed through
 * worked, and then the next check showed the same handful again, because the
 * number that fits in the gap does not change. It looked exactly like a delete
 * that silently did nothing. The page slices the list for display; nothing
 * *decides* anything on a slice.
 *
 * @return array{
 *   problems: list<array>, links: int, dirs: int, kinds: array<string,int>,
 *   complete: bool, full: bool, seconds: float
 * }
 */
function findSymlinkProblems(array $config, float $budgetSeconds = 10.0): array
{
    $root = realpath((string) $config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('the watched directory does not resolve');
    }

    $excluded = array_map(
        'normalisePath',
        $config['scan']['exclude_paths'] ?? [],
    );
    $deadline = microtime(true) + $budgetSeconds;
    $started = microtime(true);

    $state = [
        'links' => 0,
        'dirs' => 0,
        'problems' => [],
        'complete' => true,
        'full' => true,
    ];
    walkForLinks($root, $root, $excluded, $deadline, $state);

    // Worst first: a loop will break a backup tonight, a broken link never will.
    $rank = ['loop' => 0, 'ancestor' => 1, 'outside' => 2, 'broken' => 3];
    usort(
        $state['problems'],
        fn(array $a, array $b): int => ($rank[$a['kind']] ?? 9) <=>
            ($rank[$b['kind']] ?? 9),
    );

    $kinds = ['loop' => 0, 'ancestor' => 0, 'outside' => 0, 'broken' => 0];
    foreach ($state['problems'] as $problem) {
        $kinds[$problem['kind']] = ($kinds[$problem['kind']] ?? 0) + 1;
    }

    return [
        'problems' => $state['problems'],
        'links' => $state['links'],
        'dirs' => $state['dirs'],
        'kinds' => $kinds,
        'complete' => $state['complete'],
        // False when the ceiling below stopped it collecting, which is a
        // different thing from running out of time and is said differently.
        'full' => $state['full'],
        'seconds' => round(microtime(true) - $started, 2),
    ];
}

function walkForLinks(
    string $dir,
    string $root,
    array $excluded,
    float $deadline,
    array &$state,
    int $depth = 0,
): void {
    if ($depth > 40 || isExcludedPath($dir, $excluded)) {
        return;
    }
    if (microtime(true) > $deadline) {
        $state['complete'] = false;
        return;
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        return;
    }

    $state['dirs']++;

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $dir . DIRECTORY_SEPARATOR . $entry;

        if (is_link($full)) {
            $state['links']++;
            $problem = inspectLink($full, $root);
            if ($problem !== null) {
                /*
                 * A ceiling, so a tree with a million bad links cannot exhaust
                 * memory. Far above any real account, and said out loud when it
                 * is hit rather than quietly truncating the answer.
                 */
                if (count($state['problems']) >= SYMLINK_FIND_MAX) {
                    $state['full'] = false;
                } else {
                    $state['problems'][] = $problem;
                }
            }
            // Never descend through one. Following the link is the mistake this
            // whole file exists to warn other programs about.
            continue;
        }

        if (is_dir($full)) {
            walkForLinks(
                $full,
                $root,
                $excluded,
                $deadline,
                $state,
                $depth + 1,
            );
            if (!$state['complete']) {
                break;
            }
        }
    }

    closedir($handle);
}

/**
 * What is wrong with one symlink, or null if nothing is.
 *
 * @return array{kind: string, path: string, target: string, hops: int, note: string}|null
 */
function inspectLink(string $link, string $root): ?array
{
    $immediate = @readlink($link);
    if ($immediate === false) {
        return [
            'kind' => 'broken',
            'path' => $link,
            'target' => '',
            'hops' => 0,
            'note' => 'The link cannot be read at all.',
        ];
    }

    $chain = followLink($link);
    $target = $chain['target'];

    if ($chain['loop']) {
        return [
            'kind' => 'loop',
            'path' => $link,
            'target' => $target,
            'hops' => $chain['hops'],
            'note' =>
                $chain['hops'] >= SYMLINK_MAX_HOPS
                    ? 'The chain of links never ends. Anything following it will give up or hang.'
                    : 'The links point at each other in a circle.',
        ];
    }

    /*
     * A link into one of its own ancestors.
     *
     * The dangerous one, and the one that looks perfectly innocent in a
     * listing: `photos/all -> ..` is one line and an infinitely deep tree.
     */
    $inside = rtrim($target, '/') . '/';
    if ($target !== '' && str_starts_with($link . '/', $inside)) {
        return [
            'kind' => 'ancestor',
            'path' => $link,
            'target' => $target,
            'hops' => $chain['hops'],
            'note' =>
                'Points at a directory that contains the link itself, so anything ' .
                'following symlinks will descend for ever.',
        ];
    }

    if (!$chain['exists']) {
        return [
            'kind' => 'broken',
            'path' => $link,
            'target' => $target,
            'hops' => $chain['hops'],
            'note' => 'Points at something that is not there.',
        ];
    }

    if (!str_starts_with($target . '/', rtrim($root, '/') . '/')) {
        return [
            'kind' => 'outside',
            'path' => $link,
            'target' => $target,
            'hops' => $chain['hops'],
            'note' =>
                'Leaves the watched directory. Sizes measured here will not match ' .
                'what a program following this link would see.',
        ];
    }

    return null;
}

/**
 * Follows a chain of links by reading them, never by opening them.
 *
 * `realpath` would do this in one call and returns false for a loop, a broken
 * link and a permissions problem alike - three different answers flattened into
 * one, and this function has to tell them apart. Reading hop by hop also gives
 * the length of the chain, which is what distinguishes a tidy two-link circle
 * from a runaway.
 *
 * @return array{target: string, loop: bool, exists: bool, hops: int}
 */
function followLink(string $link): array
{
    $seen = [];
    $current = $link;
    $hops = 0;

    while ($hops < SYMLINK_MAX_HOPS) {
        if (isset($seen[$current])) {
            return [
                'target' => $current,
                'loop' => true,
                'exists' => false,
                'hops' => $hops,
            ];
        }
        $seen[$current] = true;

        if (!is_link($current)) {
            // The end of the chain: a real file, a real directory, or nothing.
            return [
                'target' => $current,
                'loop' => false,
                'exists' => file_exists($current),
                'hops' => $hops,
            ];
        }

        $next = @readlink($current);
        if ($next === false) {
            return [
                'target' => $current,
                'loop' => false,
                'exists' => false,
                'hops' => $hops,
            ];
        }

        // A relative target is relative to the directory the link sits in.
        $current = $next[0] === '/' ? $next : dirname($current) . '/' . $next;
        $current = tidyPath($current);
        $hops++;
    }

    return [
        'target' => $current,
        'loop' => true,
        'exists' => false,
        'hops' => $hops,
    ];
}

/**
 * Resolves `.` and `..` in a path as text.
 *
 * Textually, because `realpath` needs the path to exist and half the paths
 * reaching this function are the broken ones. It is not a substitute for
 * realpath in general - it cannot know about symlinks in the middle of a path -
 * but each hop here is resolved one at a time, so there are none left to know
 * about.
 */
function tidyPath(string $path): string
{
    $absolute = str_starts_with($path, '/');
    $out = [];

    foreach (explode('/', $path) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($out);
            continue;
        }
        $out[] = $part;
    }

    return ($absolute ? '/' : '') . implode('/', $out);
}

/* ------------------------------------------------------- repairing them -- */

/**
 * The most links one bulk operation will touch.
 *
 * A pattern is easy to write and easy to write too broadly. A limit means a
 * mistake is a hundred links rather than everything under the account, and the
 * page says how many were left so nobody thinks it finished.
 */
const SYMLINK_BULK_MAX = 500;

/**
 * A path a repair may touch, resolved and checked.
 *
 * Everything here rewrites the filesystem, so the rule is the one used for
 * deletions elsewhere: inside the watched directory, no `..` surviving
 * resolution, and never through a symlink in the middle of the path. The last
 * one matters more here than anywhere else in this tool, because the thing
 * being operated on *is* a symlink and following the path to reach it is
 * exactly the mistake.
 */
function resolveLinkPath(array $config, string $path): string
{
    $root = realpath((string) $config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('the watched directory does not resolve');
    }

    $path = tidyPath(trim($path));
    if ($path === '' || $path[0] !== '/') {
        throw new ActionRefused('that is not an absolute path');
    }

    /*
     * The parent is resolved; the last component never is.
     *
     * realpath() on the link itself would follow it - returning the target, or
     * false for a broken or looping link, which is every link this page exists
     * to fix. Resolving the directory it sits in gives the containment check
     * something real to test while leaving the link untouched.
     */
    $parent = realpath(dirname($path));
    if ($parent === false) {
        throw new ActionRefused('the directory that link is in does not exist');
    }

    $inside = rtrim($root, '/') . '/';
    if ($parent . '/' !== $inside && !str_starts_with($parent . '/', $inside)) {
        throw new ActionRefused('that is outside the watched directory');
    }

    return $parent . '/' . basename($path);
}

/**
 * Deletes one symlink.
 *
 * `unlink` on a symlink removes the link and never the target - that is what
 * the call does, and it is the reason no check of the target is needed. The
 * `is_link` test is here to refuse a regular file that happens to be named in
 * a pattern, not to protect the target.
 *
 * @return array{path: string, target: string}
 */
function deleteLink(array $config, string $path): array
{
    $full = resolveLinkPath($config, $path);

    if (!is_link($full)) {
        throw new ActionRefused('that is not a symlink');
    }

    $target = (string) @readlink($full);

    if (!@unlink($full)) {
        throw new ActionRefused(
            'could not remove it - check the directory is writable',
        );
    }

    return ['path' => $full, 'target' => $target];
}

/**
 * Points an existing symlink somewhere else.
 *
 * Written as a new link beside the old one and renamed over it, so there is no
 * moment where the name does not exist. A program that opens this path every
 * second sees the old target or the new one, never nothing.
 *
 * The new target is not required to exist. That sounds wrong and is not: a link
 * into a filesystem that mounts later, or to a file a deploy is about to write,
 * is a normal thing to want, and refusing it would make this unable to create
 * the arrangement it is being used to restore. What it does do is say so.
 *
 * @return array{path: string, was: string, now: string, exists: bool}
 */
function repointLink(array $config, string $path, string $target): array
{
    $full = resolveLinkPath($config, $path);

    if (!is_link($full)) {
        throw new ActionRefused('that is not a symlink');
    }

    $target = trim($target);
    if ($target === '') {
        throw new ActionRefused('a link needs somewhere to point');
    }
    if (strlen($target) > 4096) {
        throw new ActionRefused('that target is too long');
    }

    $was = (string) @readlink($full);

    /*
     * Refuse to build the problem this page reports.
     *
     * A link pointing at one of its own ancestors is an infinitely deep tree to
     * anything that follows links. Creating one here, from the page whose job
     * is to find them, would be absurd.
     */
    $resolved = $target[0] === '/' ? $target : dirname($full) . '/' . $target;
    $resolved = tidyPath($resolved);
    if (str_starts_with($full . '/', rtrim($resolved, '/') . '/')) {
        throw new ActionRefused(
            'that points at a directory containing the link itself, which is the loop this ' .
                'page exists to find',
        );
    }

    $temporary = $full . '.relink-' . bin2hex(random_bytes(4));
    if (!@symlink($target, $temporary)) {
        throw new ActionRefused(
            'could not create the new link - check the directory is writable',
        );
    }

    if (!@rename($temporary, $full)) {
        @unlink($temporary);
        throw new ActionRefused('could not put the new link in place');
    }

    return [
        'path' => $full,
        'was' => $was,
        'now' => $target,
        'exists' => file_exists($resolved),
    ];
}

/**
 * Replaces a symlink with a real, empty directory.
 *
 * The usual fix for a link that pointed at something now gone, where whatever
 * writes into it only needs somewhere to write. The link goes first and the
 * directory is created in its place; if the second step fails the link is put
 * back, because leaving neither would be worse than leaving the broken one.
 *
 * @return array{path: string, was: string}
 */
function replaceLinkWithDirectory(array $config, string $path): array
{
    $full = resolveLinkPath($config, $path);

    if (!is_link($full)) {
        throw new ActionRefused('that is not a symlink');
    }

    $was = (string) @readlink($full);

    if (!@unlink($full)) {
        throw new ActionRefused(
            'could not remove the link - check the directory is writable',
        );
    }

    if (!@mkdir($full, 0755)) {
        // Put it back rather than leaving the name missing entirely.
        @symlink($was, $full);
        throw new ActionRefused(
            'could not create the directory, so the link was left as it was',
        );
    }

    return ['path' => $full, 'was' => $was];
}

/**
 * Does one path fall under a pattern the operator typed?
 *
 * A pattern names a place, and everything at or under that place matches. That
 * is the whole rule, and it is the rule because the alternative was watched in
 * use: to clear one previews directory somebody had to type
 *
 *     .../previews/*&#47;*&#47;*&#47;*&#47;*&#47;*
 *
 * counting directory levels by hand and adding another star whenever the count
 * came out wrong. Nobody knows how deep a tree of thumbnails goes, and nobody
 * should have to. Typing `.../previews` now means the same thing.
 *
 * A `*` still stops at a slash, but it chooses a *name*, not a depth: it says
 * which entries at that position count, and everything under a chosen entry is
 * included. `previews/*` therefore means the same as `rm -r previews/*` does in
 * a shell, which is the behaviour anybody typing it already expects.
 */
function symlinkPatternMatch(string $pattern, string $path): bool
{
    $pattern = rtrim(trim($pattern), '/');
    if ($pattern === '') {
        return false;
    }

    $regex = '';
    foreach (str_split($pattern) as $character) {
        $regex .= match ($character) {
            '*' => '[^/]*',
            '?' => '[^/]',
            default => preg_quote($character, '#'),
        };
    }

    // The place itself, or anything below it.
    return (bool) preg_match('#^' . $regex . '(/.*)?$#', $path);
}

/**
 * Deletes every broken link matching a pattern.
 *
 * A pattern rather than a list of ticked boxes, because the actual case is
 * "everything under previews/ is dangling" and ticking four hundred boxes is
 * not a workflow. `fnmatch` semantics, so `*` and `?` behave the way they do in
 * a shell.
 *
 * Only ever links that are *broken*, and only ones the check already flagged.
 * A healthy link is not in findSymlinkProblems() at all, so it is never a
 * candidate here regardless of what the pattern says - the destructive half is
 * bounded by a fact about each link rather than by how carefully somebody wrote
 * the pattern. A matching link with a *different* problem, a loop or an escape,
 * is counted and left alone: those want a decision, not a sweep.
 *
 * @return array{
 *   deleted: list<array>, skipped: int, matched: int,
 *   remaining: int, complete: bool
 * }
 */
function deleteBrokenMatching(
    array $config,
    string $pattern,
    bool $dryRun = true,
    float $budgetSeconds = 20.0,
): array {
    $root = realpath((string) $config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('the watched directory does not resolve');
    }

    $pattern = trim($pattern);
    if ($pattern === '') {
        throw new ActionRefused('a pattern is needed');
    }

    /*
     * The pattern has to name somewhere inside the watched directory.
     *
     * Checked on the literal prefix before the first wildcard, so `/*` and
     * `/home/*` are refused rather than matching the whole account by accident.
     */
    $literal = substr($pattern, 0, strcspn($pattern, '*?['));

    $inside = rtrim($root, '/') . '/';
    if (
        !str_starts_with($literal, $inside) ||
        strlen($literal) <= strlen($inside)
    ) {
        throw new ActionRefused(
            'the pattern has to start with a real path inside ' .
                $root .
                ', so that a stray wildcard cannot match the whole account',
        );
    }

    $found = findSymlinkProblems($config, $budgetSeconds);

    $deleted = [];
    $matched = 0;
    $broken = 0;
    $skipped = 0;

    /*
     * Tried and could not, kept separately from chose not to.
     *
     * These were one number, and that was wrong in a way that made a real
     * failure indistinguishable from correct behaviour: a read-only directory
     * produced "0 links removed, 5 links left alone", which reads as a decision
     * rather than as five failures. The reason was already known - deleteLink
     * says exactly what went wrong - and was being discarded one line later.
     */
    $failed = [];

    foreach ($found['problems'] as $problem) {
        if (!symlinkPatternMatch($pattern, $problem['path'])) {
            continue;
        }
        $matched++;

        if ($problem['kind'] !== 'broken') {
            // Matched, but not broken. Never deleted on a pattern alone.
            $skipped++;
            continue;
        }

        $broken++;

        if (count($deleted) >= SYMLINK_BULK_MAX) {
            continue;
        }

        if ($dryRun) {
            $deleted[] = [
                'path' => $problem['path'],
                'target' => $problem['target'],
            ];
            continue;
        }

        try {
            $deleted[] = deleteLink($config, $problem['path']);
        } catch (ActionRefused $e) {
            $failed[] = ['path' => $problem['path'], 'why' => $e->getMessage()];
        }
    }

    return [
        'deleted' => $deleted,
        // Matched the path but is not broken, so was never a candidate.
        'skipped' => $skipped,
        // Was a candidate, was attempted, and did not go. With the reason.
        'failed' => $failed,
        'matched' => $matched,
        'broken' => $broken,
        'remaining' => max(0, $broken - count($deleted) - count($failed)),
        // The walk itself may have run out of time, in which case there may be
        // matching links it never reached.
        'complete' => $found['complete'] && $found['full'],
        'dry_run' => $dryRun,
    ];
}

/**
 * Two lines around the `settings` table, so nothing has to know its shape.
 *
 * Kept here rather than in db.php because these are the only two callers and
 * putting a general-purpose key-value API in front of a two-column table
 * invites it to become a place things get hidden.
 */
function rememberSetting(PDO $pdo, string $key, string $value): void
{
    try {
        $pdo->prepare(
            'INSERT INTO settings (key, value) VALUES (?, ?)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value',
        )->execute([$key, $value]);
    } catch (Throwable) {
        // A missing table means an install that has not migrated yet. Losing
        // the summary is untidy; failing the walk over it would not be.
    }
}

function recallSetting(PDO $pdo, string $key): ?string
{
    try {
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
        $stmt->execute([$key]);
        $found = $stmt->fetchColumn();
    } catch (Throwable) {
        return null;
    }

    return $found === false ? null : (string) $found;
}

/**
 * The last completed walk, rebuilt from what was stored.
 *
 * Returns null when there has never been one. Everything here was already being
 * written down for the "what is new since last time" comparison; it simply was
 * not being read back, so the page forgot a fifteen-second walk the moment
 * somebody reloaded.
 *
 * The notes are regenerated rather than stored. A note is a fixed sentence per
 * kind - keeping four copies of it on every row would be a column that could
 * drift out of step with the one in inspectLink.
 */
function lastSymlinkCheck(PDO $pdo): ?array
{
    $raw = recallSetting($pdo, 'symlink_walk');
    if ($raw === null) {
        return null;
    }

    $walk = json_decode($raw, true);
    if (!is_array($walk)) {
        return null;
    }

    try {
        $rows = $pdo
            ->query(
                'SELECT path, kind, target FROM symlink_problems ORDER BY kind, path',
            )
            ->fetchAll();
    } catch (Throwable) {
        return null;
    }

    $rank = ['loop' => 0, 'ancestor' => 1, 'outside' => 2, 'broken' => 3];
    $problems = array_map(
        fn(array $row): array => [
            'kind' => (string) $row['kind'],
            'path' => (string) $row['path'],
            'target' => (string) ($row['target'] ?? ''),
            'hops' => 0,
            'note' => symlinkNoteFor((string) $row['kind']),
        ],
        $rows,
    );

    usort(
        $problems,
        fn(array $a, array $b): int => ($rank[$a['kind']] ?? 9) <=>
            ($rank[$b['kind']] ?? 9),
    );

    return [
        'problems' => $problems,
        'links' => (int) ($walk['links'] ?? 0),
        'dirs' => (int) ($walk['dirs'] ?? 0),
        'kinds' => $walk['kinds'] ?? [],
        'seconds' => (float) ($walk['seconds'] ?? 0),
        'complete' => true,
        'full' => (bool) ($walk['full'] ?? true),
        'at' => (int) ($walk['at'] ?? 0),
    ];
}

/** The fixed sentence for each kind, so it is written once. */
function symlinkNoteFor(string $kind): string
{
    return match ($kind) {
        'loop' => 'The links point at each other in a circle.',
        'ancestor'
            => 'Points at a directory that contains the link itself, so anything ' .
            'following symlinks will descend for ever.',
        'outside'
            => 'Leaves the watched directory, so sizes measured here are not the ' .
            'sizes something following it would see.',
        'broken' => 'Points at something that is not there.',
        default => '',
    };
}

/**
 * Records what the last check found, so a new problem can be noticed.
 *
 * A loop that has been there for two years is a job for a rainy afternoon. One
 * that appeared this morning is a deploy that went wrong, and the difference
 * between the two is the only thing worth an alert - so what matters is the
 * comparison against last time, not the count.
 *
 * @return array{added: list<array>, gone: int, first: bool}
 */
function recordSymlinkState(PDO $pdo, array $found): array
{
    $now = time();
    $seen = [];

    foreach ($found['problems'] as $problem) {
        $seen[$problem['path'] . "\0" . $problem['kind']] = $problem;
    }

    try {
        $before = $pdo
            ->query('SELECT path, kind FROM symlink_problems')
            ->fetchAll();
    } catch (Throwable) {
        return ['added' => [], 'gone' => 0, 'first' => true];
    }

    $first = $before === [];
    $known = [];
    foreach ($before as $row) {
        $known[(string) $row['path'] . "\0" . (string) $row['kind']] = true;
    }

    $added = [];
    foreach ($seen as $key => $problem) {
        if (!isset($known[$key])) {
            $added[] = $problem;
        }
    }

    /*
     * Only rewritten when the walk finished.
     *
     * A walk that ran out of time saw part of the tree. Storing that as the new
     * state would mark everything it did not reach as "gone", and then report
     * all of it as newly appeared on the next complete run.
     */
    /*
     * The walk's own summary, so a reload does not lose it.
     *
     * The last check lived only in the browser's memory, so opening the page
     * again showed "nothing has been checked yet" after a walk that had taken
     * fifteen seconds - and the natural response to that is to run it again.
     * The problems themselves are already in the table below; this is the
     * handful of numbers around them.
     */
    if ($found['complete']) {
        rememberSetting(
            $pdo,
            'symlink_walk',
            json_encode([
                'at' => $now,
                'links' => $found['links'],
                'dirs' => $found['dirs'],
                'seconds' => $found['seconds'],
                'kinds' => $found['kinds'],
                'total' => count($found['problems']),
                'full' => $found['full'],
            ]),
        );
    }

    if ($found['complete']) {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM symlink_problems');
        $insert = $pdo->prepare(
            'INSERT INTO symlink_problems (path, kind, target, first_seen, last_seen)
             VALUES (?, ?, ?, ?, ?)',
        );
        foreach ($seen as $problem) {
            $insert->execute([
                $problem['path'],
                $problem['kind'],
                $problem['target'],
                $now,
                $now,
            ]);
        }
        $pdo->commit();
    }

    return [
        'added' => $added,
        'gone' => max(0, count($known) - count($seen)),
        // Nothing to compare against yet, so nothing counts as new.
        'first' => $first,
    ];
}
