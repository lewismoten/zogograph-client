<?php
declare(strict_types=1);

/*
 * Deleting things.
 *
 * This is the only part of the monitor that cannot be undone, so it is the part
 * written most defensively. A request has to clear all of these before anything
 * is unlinked:
 *
 *   - a signed-in session
 *   - POST, with a CSRF token matching the session
 *   - a path that resolves, via realpath, to somewhere inside scan_root
 *   - a path in a category the config marks deletable
 *   - not a symlink, at any point along the way
 *   - not the monitor's own data directory
 *
 * The order matters: everything is decided against the *resolved* path, because
 * "/home/x/trash/../../../etc/passwd" is inside scan_root right up until the
 * moment you resolve it.
 *
 * Every attempt is written to the actions table, including the refusals. A run
 * of refusals is what an attempt to escape scan_root looks like, and it is the
 * only place it would ever show up.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/collect.php';
// humanBytes, for the audit note when a link survives a delete.
require_once __DIR__ . '/alerts.php';

final class ActionRefused extends RuntimeException
{
}

/**
 * Resolves a requested path and refuses it if it is not somewhere we may touch.
 *
 * Returns the real path. Throws ActionRefused with a reason otherwise - the
 * reason is logged, but the caller should not hand every distinction back to an
 * unauthenticated client.
 */
function resolveDeletable(array $config, string $requested): string
{
    if ($requested === '' || str_contains($requested, "\0")) {
        throw new ActionRefused('empty or malformed path');
    }

    $real = realpath($requested);
    if ($real === false) {
        throw new ActionRefused('path does not exist');
    }

    $root = realpath((string)$config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('scan root does not resolve');
    }

    // The trailing separator matters: without it "/home/account-old" passes a
    // prefix test against "/home/account".
    if ($real !== $root && !str_starts_with($real . '/', rtrim($root, '/') . '/')) {
        throw new ActionRefused('outside the scan root');
    }
    if ($real === $root) {
        throw new ActionRefused('refusing to delete the scan root itself');
    }

    // Never act through a link, and never act on one: both are ways to reach
    // somewhere the checks above just approved a different path for.
    if (is_link($requested) || is_link($real)) {
        throw new ActionRefused('symlinks are not deleted');
    }

    $dataDir = realpath(dirname((string)$config['database']));
    if ($dataDir !== false && ($real === $dataDir || str_starts_with($real . '/', $dataDir . '/'))) {
        throw new ActionRefused('refusing to delete the monitor\'s own data');
    }

    /*
     * How far deletion reaches, which is a policy and not a property of a file.
     *
     * `categories` is the cautious rule this started with: only trash and temp,
     * so a mis-click can only ever remove something already declared disposable.
     * `account` allows anything inside the watched directory, which is what an
     * account owner looking at their own disk generally means by "delete this".
     *
     * Everything else above still applies in both modes - never the scan root
     * itself, never outside it, never a symlink, never the monitor's own data.
     * Widening the categories does not widen those.
     */
    $scope = (string)($config['deletable_scope'] ?? 'account');

    if ($scope !== 'account') {
        $category = categoryOf($real . (is_dir($real) ? '/x' : ''), $config['scan'], $root);
        $allowed = $config['deletable_categories'] ?? [];
        if ($category === null || !in_array($category, $allowed, true)) {
            throw new ActionRefused(sprintf(
                'this install only allows deleting from %s. Change deletable_scope to '
                . "'account' in lib/config.php to allow anything inside the watched "
                . 'directory',
                $allowed === [] ? 'nothing' : implode(' and ', $allowed)
            ));
        }
    }

    return $real;
}

/**
 * Deletes one file.
 *
 * Files only. Handing a recursive delete to a single path parameter is how a
 * one-character mistake removes a home directory; emptying a whole category is
 * a separate, named action below.
 */
function deleteFile(array $config, PDO $pdo, string $requested): array
{
    try {
        $real = resolveDeletable($config, $requested);
    } catch (ActionRefused $e) {
        /*
         * "Already deleted" rather than "path does not exist".
         *
         * Both describe a missing file, and only one of them tells the reader
         * what happened. A path this tool removed itself is not an error to be
         * investigated - it is a repeated click, or a stale tab - and the audit
         * log is right there to say so.
         */
        if (!file_exists($requested) && alreadyDeleted($pdo, $requested)) {
            logAction($pdo, 'delete_file', $requested, 'refused', 0, 0, 'already deleted');
            throw new ActionRefused(
                'that file was already deleted. It is still listed because the last scan '
                . 'found it; the next scan will drop it.'
            );
        }

        logAction($pdo, 'delete_file', $requested, 'refused', 0, 0, $e->getMessage());
        throw $e;
    }

    if (is_dir($real)) {
        logAction($pdo, 'delete_file', $real, 'refused', 0, 0, 'is a directory');
        throw new ActionRefused('that is a directory; use empty instead');
    }

    /*
     * What removing this actually gives back.
     *
     * Nothing, if another directory entry points at the same inode: a hard link
     * is a name, and the allocation survives until the last name is gone. The
     * audit log is meant to answer "where did the space come from", so
     * recording the file's size as freed when the disk did not move would make
     * every total in it wrong, and wrong in the flattering direction.
     */
    $stat = @stat($real);
    $size = $stat === false ? 0 : (int)$stat['size'];
    $links = $stat === false ? 1 : (int)($stat['nlink'] ?? 1);
    $freed = $links > 1 ? 0 : $size;

    if (!@unlink($real)) {
        logAction($pdo, 'delete_file', $real, 'failed', 0, 0, 'unlink returned false');
        throw new ActionRefused('could not delete the file');
    }

    logAction(
        $pdo,
        'delete_file',
        $real,
        'ok',
        $freed,
        1,
        $links > 1
            ? sprintf('one of %d links; %s stays on disk until the rest go', $links, humanBytes($size))
            : null
    );

    return ['bytes_freed' => $freed, 'files_removed' => 1, 'links' => $links, 'bytes' => $size];
}

/** Whether this tool has a record of successfully deleting that exact path. */
/**
 * Deletes a directory and everything in it.
 *
 * The dangerous one, so it is bounded on every side that can be bounded:
 *
 *   - the same resolveDeletable check as a single file, so it is inside the
 *     account, not the scan root itself, not the monitor's own data, and in a
 *     category the config marks deletable
 *   - never through a symlink, and never *onto* one: a link inside the tree is
 *     unlinked, never followed, so a link to `/` cannot turn this into a
 *     recursive delete of the machine
 *   - never off the device it started on, so a mount point inside the tree
 *     stops it rather than being emptied
 *   - depth-limited, because a loop that survived the link checks would
 *     otherwise recurse until PHP gives up somewhere unpredictable
 *
 * It reports what it removed and what it refused, and a refusal does not abort
 * the rest: a tree with one unreadable file in it should still lose the other
 * nine thousand, and the one that stayed should be named.
 */
function deleteDirectory(array $config, PDO $pdo, string $requested): array
{
    $real = resolveDeletable($config, $requested);

    if (!is_dir($real)) {
        throw new ActionRefused('that is not a directory');
    }

    $device = @stat($real)['dev'] ?? null;
    $result = ['files' => 0, 'dirs' => 0, 'bytes' => 0, 'skipped' => [], 'links' => 0];

    removeTree($real, $device, $result, 0);

    // The directory itself, last and only if it actually emptied.
    $removed = $result['skipped'] === [] && @rmdir($real);
    if ($removed) {
        $result['dirs']++;
    }

    $outcome = $result['skipped'] === [] ? 'ok' : 'partial';
    logAction(
        $pdo,
        'delete_dir',
        $real,
        $outcome,
        $result['bytes'],
        $result['files'],
        $result['skipped'] === []
            ? null
            : count($result['skipped']) . ' left behind: ' . implode(', ', array_slice($result['skipped'], 0, 5))
    );

    return $result + ['path' => $real, 'removed_itself' => $removed, 'outcome' => $outcome];
}

function removeTree(string $dir, ?int $device, array &$result, int $depth): void
{
    if ($depth > 40) {
        $result['skipped'][] = $dir . ' (nested too deep)';
        return;
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        $result['skipped'][] = $dir . ' (cannot be opened)';
        return;
    }

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $dir . DIRECTORY_SEPARATOR . $entry;

        /*
         * A link is unlinked where it stands.
         *
         * Checked before is_dir, which follows links and would otherwise send
         * this walking into whatever the link points at.
         */
        if (is_link($full)) {
            if (@unlink($full)) {
                $result['links']++;
            } else {
                $result['skipped'][] = $full;
            }
            continue;
        }

        $stat = @lstat($full);
        if ($stat === false) {
            $result['skipped'][] = $full;
            continue;
        }

        if ($device !== null && (int)$stat['dev'] !== $device) {
            // A different filesystem is mounted here. Emptying somebody else's
            // mount because it happens to sit inside this directory is not what
            // "delete this directory" means.
            $result['skipped'][] = $full . ' (a different filesystem is mounted here)';
            continue;
        }

        if (is_dir($full)) {
            removeTree($full, $device, $result, $depth + 1);
            if (@rmdir($full)) {
                $result['dirs']++;
            } else {
                $result['skipped'][] = $full;
            }
            continue;
        }

        $bytes = (int)($stat['size'] ?? 0);
        if (@unlink($full)) {
            $result['files']++;
            $result['bytes'] += $bytes;
        } else {
            $result['skipped'][] = $full;
        }
    }

    closedir($handle);
}

function alreadyDeleted(PDO $pdo, string $path): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM actions
         WHERE action = 'delete_file' AND outcome = 'ok' AND target = ?"
    );
    $stmt->execute([$path]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Empties a whole category - trash, or temp.
 *
 * Walks the tree and removes the files that count against that category,
 * leaving the directories in place. Leaving them is deliberate: a trash
 * directory that removes itself is a surprise, and directories cost nothing.
 */
function emptyCategory(array $config, PDO $pdo, string $category): array
{
    $allowed = $config['deletable_categories'] ?? [];
    if (!in_array($category, $allowed, true)) {
        logAction($pdo, 'empty_category', $category, 'refused', 0, 0, 'category not deletable');
        throw new ActionRefused('that category cannot be emptied');
    }

    $root = realpath((string)$config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('scan root does not resolve');
    }

    $excluded = array_map('normalisePath', $config['scan']['exclude_paths'] ?? []);
    $freed = 0;
    $removed = 0;
    $failed = 0;
    $linked = 0;

    $files = [];
    collectCategoryFiles($root, $config['scan'], $category, $excluded, $root, $files);

    foreach ($files as $file => $size) {
        // Re-checked one at a time rather than trusted from the walk: the walk
        // and the delete are not the same instant, and this is the last point
        // at which a swapped path can be caught.
        try {
            $real = resolveDeletable($config, $file);
        } catch (ActionRefused) {
            $failed++;
            continue;
        }
        /*
         * Counted only when this was the last name for the file.
         *
         * Checked immediately before the unlink rather than with a set of
         * inodes, and that ordering does the bookkeeping for free: if both
         * links are in this category, the first delete sees two links and
         * counts nothing, the second sees one and counts the whole file. The
         * total comes out right however many names there were and whichever
         * order they were reached in.
         */
        $stat = @stat($real);
        $last = $stat === false || (int)($stat['nlink'] ?? 1) <= 1;

        if (@unlink($real)) {
            if ($last) {
                $freed += $size;
            } else {
                $linked++;
            }
            $removed++;
        } else {
            $failed++;
        }
    }

    logAction(
        $pdo,
        'empty_category',
        $category,
        $failed > 0 ? 'partial' : 'ok',
        $freed,
        $removed,
        implode('; ', array_filter([
            $failed > 0 ? "$failed could not be removed" : null,
            $linked > 0 ? "$linked still had another link, so freed nothing" : null,
        ])) ?: null
    );

    return [
        'bytes_freed' => $freed,
        'files_removed' => $removed,
        'failed' => $failed,
        'still_linked' => $linked,
    ];
}

/** Every file under $dir that counts against $category, with its size. */
function collectCategoryFiles(
    string $dir,
    array $rules,
    string $category,
    array $excluded,
    string $root,
    ?array &$out = null,
    int $depth = 0
): void {
    $out ??= [];
    if ($depth > 40 || isExcludedPath($dir, $excluded)) {
        return;
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        return;
    }

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $dir . DIRECTORY_SEPARATOR . $entry;
        if (is_link($full)) {
            continue;
        }
        if (is_dir($full)) {
            collectCategoryFiles($full, $rules, $category, $excluded, $root, $out, $depth + 1);
            continue;
        }
        if (categoryOf($full, $rules, $root) !== $category) {
            continue;
        }
        $size = @filesize($full);
        if ($size !== false) {
            $out[$full] = (int)$size;
        }
    }

    closedir($handle);
}

function logAction(
    PDO $pdo,
    string $action,
    ?string $target,
    string $outcome,
    int $bytes = 0,
    int $files = 0,
    ?string $detail = null
): void {
    $actor = trim((string)($_SESSION['username'] ?? ''));
    if ($actor === '' && PHP_SAPI === 'cli') {
        $actor = 'collector';
    }
    $pdo->prepare(
        'INSERT INTO actions (at, action, target, outcome, bytes_freed, files_removed, detail, remote_addr, actor)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        time(),
        $action,
        $target,
        $outcome,
        $bytes,
        $files,
        $detail,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $actor !== '' ? mb_substr($actor, 0, 120) : null,
    ]);
}
