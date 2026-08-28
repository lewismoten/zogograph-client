<?php
declare(strict_types=1);

/*
 * Where the space went, when the account cannot see it.
 *
 * The situation this exists for: df says 72 GB is used and everything the
 * account can reach adds up to 4. The missing 68 GB is not hiding - it is
 * somewhere the cPanel user is not allowed to look, and no amount of scanning
 * from inside the account will ever find it.
 *
 * There are three genuinely different answers and they need three different
 * questions. Getting this wrong means walking a disk for twenty minutes to
 * confirm something a one-line reading would have told you at the start.
 *
 *   Which filesystem?
 *       The first question, and the cheapest. /home may be 4 GB used out of 5
 *       while the 72 GB lives on / or on a separate /backup. Half of these
 *       investigations end here, before anything has been walked at all.
 *
 *   Which directories?
 *       A du by another name, bounded by a clock, keeping only the biggest few
 *       hundred directories. That is enough to navigate: the answer is always a
 *       chain of large directories from the root down, and a directory too
 *       small to make the list is too small to be the answer.
 *
 *   Files that are deleted and still open.
 *       The one that defeats everybody. A process holding a deleted file keeps
 *       its blocks allocated until it closes them, so the space is gone from df
 *       and invisible to du, ls, find and every treemap ever written - the
 *       directory entry is gone and the data is not. A truncated-then-rotated
 *       log that Apache still has open is exactly this, and it is the classic
 *       cause of "72 used, 4 accounted for".
 *
 *       Only root can enumerate it, by reading /proc/<pid>/fd and noticing
 *       which links point at something marked deleted. That is why this file
 *       exists at all.
 */

require_once __DIR__ . '/collect.php';

/** How long the directory walk may run. */
const HOSTDISK_WALK_SECONDS = 45.0;

/** How many directories are kept. */
const HOSTDISK_KEEP_DIRS = 400;

/** How deep the walk records directories. Deeper ones roll up into these. */
const HOSTDISK_MAX_DEPTH = 6;

/*
 * How deep the time budget is shared out between siblings.
 *
 * Sharing at every level sounds fair and is useless: each level divides again,
 * so at depth six a child receives the budget divided by the branching factor
 * six times over - microseconds - and the walk gives up immediately. A real run
 * of this machine finished in 9 of its 45 seconds having measured almost
 * nothing.
 *
 * Breadth only matters near the top. Two levels is what the reported case
 * needs: / is depth 0 and /backup is depth 1, so sharing at both means
 * /backup's two destinations each get a slice. A third level fragments the
 * budget further for no benefit - it left 30 of 45 seconds unused on a real
 * run, because an early sibling could not spend what a later one did not need.
 */
const HOSTDISK_FAIR_DEPTH = 2;

/*
 * How many unopened directories are worth recording.
 *
 * They exist so that a missed subtree is visible rather than silently absent.
 * Keeping all of them is worse than keeping none: the same real walk produced
 * ten thousand, which filled the entire row budget and pushed out every
 * directory that had actually been measured.
 */
const HOSTDISK_KEEP_UNMEASURED = 60;

/** Filesystem types that are not storage and would only add noise. */
function pseudoFilesystems(): array
{
    return [
        'proc', 'sysfs', 'devtmpfs', 'devpts', 'securityfs', 'cgroup', 'cgroup2',
        'pstore', 'bpf', 'autofs', 'mqueue', 'hugetlbfs', 'debugfs', 'tracefs',
        'fusectl', 'configfs', 'binfmt_misc', 'rpc_pipefs', 'nfsd', 'selinuxfs',
        'squashfs', 'ramfs', 'efivarfs',
    ];
}

/**
 * Every real filesystem, with how full it is.
 *
 * The question to ask first. An account that has 4 GB of files on a partition
 * showing 72 GB used is not a mystery about missing files - it is two different
 * filesystems, and the answer is which one.
 *
 * @return list<array>
 */
function hostMounts(string $procRoot = '/proc'): array
{
    $raw = @file_get_contents($procRoot . '/mounts');
    if ($raw === false) {
        return [];
    }

    $pseudo = pseudoFilesystems();
    $seen = [];
    $out = [];

    foreach (preg_split('/\R/', trim($raw)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line));
        if (!$parts || count($parts) < 3) {
            continue;
        }

        [$device, $mount, $type] = $parts;
        if (in_array($type, $pseudo, true)) {
            continue;
        }

        // Mount points are escaped in /proc/mounts: a space is \040.
        $mount = str_replace(['\040', '\011', '\012', '\134'], [' ', "\t", "\n", '\\'], $mount);

        $total = @disk_total_space($mount);
        $free = @disk_free_space($mount);
        if ($total === false || $free === false || $total <= 0) {
            continue;
        }

        /*
         * One row per filesystem, not per mount point.
         *
         * Bind mounts and containerised hosts show the same device mounted in
         * several places, and adding them up would report a machine with three
         * times the disk it has. Keyed on the device and the size together,
         * because two different devices can legitimately share a name.
         */
        $key = $device . ':' . $total;
        if (isset($seen[$key])) {
            $out[$seen[$key]]['also_at'][] = $mount;
            continue;
        }
        $seen[$key] = count($out);

        $out[] = [
            'device' => $device,
            'mount' => $mount,
            'type' => $type,
            'total_bytes' => (int)$total,
            'free_bytes' => (int)$free,
            'used_bytes' => (int)($total - $free),
            'used_percent' => round(($total - $free) / $total * 100, 2),
            'also_at' => [],
        ];
    }

    usort($out, fn(array $a, array $b): int => $b['used_bytes'] <=> $a['used_bytes']);
    return $out;
}

/**
 * The biggest directories on the machine, as far as time allowed.
 *
 * A du that knows when to stop. Three things keep it affordable:
 *
 * It does not cross filesystem boundaries. Walking into a network mount or a
 * backup volume attached to the same tree turns a one-minute job into an
 * unbounded one, and those are separate questions with their own rows in
 * hostMounts().
 *
 * It records directories only to a fixed depth. Everything below rolls up into
 * its nearest recorded ancestor, so the total stays right while the row count
 * does not grow with the tree.
 *
 * It stops at a deadline and says where it got to. A partial answer that knows
 * it is partial is useful; one that does not is a trap.
 *
 * @return array{dirs: list<array>, complete: bool, seconds: float, scanned: int}
 */
function hostDirectoryUsage(
    string $root = '/',
    ?float $budget = null,
    array $skip = [],
    ?int $device = null,
    array $keepChildrenOf = []
): array {
    $started = microtime(true);
    $deadline = $started + ($budget ?? HOSTDISK_WALK_SECONDS);

    $real = realpath($root);
    if ($real === false || !is_dir($real)) {
        return ['dirs' => [], 'complete' => false, 'seconds' => 0.0, 'scanned' => 0];
    }

    if ($device === null) {
        $stat = @stat($real);
        $device = $stat === false ? null : (int)$stat['dev'];
    }

    /*
     * Bind mounts are skipped, because they are the same blocks twice.
     *
     * The device check below stops the walk crossing onto another filesystem.
     * It does nothing about a subtree of *this* filesystem mounted somewhere
     * else on it, which is exactly what CageFS does seventy times over - and
     * those files have one inode, one set of blocks, and two paths. The result
     * was /usr/local/cpanel counted at 3.51 GiB and again at 3.55 GiB under the
     * skeleton, and a root total that was not a total of anything.
     *
     * Deduplicating by inode would also work and costs a hash entry per file -
     * tens of megabytes on a real server. Refusing to descend into the view in
     * the first place is cheaper and more honest: the bytes are counted where
     * they actually live.
     */
    foreach (bindMountPoints() as $point) {
        $skip[] = $point;
    }

    /*
     * Skip paths are resolved before they are compared.
     *
     * The walk works in resolved paths - realpath() is applied to the root and
     * every child is built from that - so a configured skip of "/tmp/cache"
     * never matches a walk that is seeing "/private/tmp/cache", and the
     * directory is quietly walked anyway. A skip that silently does nothing is
     * worse than no skip: the whole reason to configure one is a directory
     * that must not be entered.
     *
     * Trailing slashes go too, for the same reason from the other direction.
     */
    $resolve = static function (array $paths): array {
        return array_values(array_filter(array_map(
            static function (string $path): ?string {
                $resolved = realpath($path);
                return $resolved === false ? (rtrim($path, '/') ?: null) : $resolved;
            },
            $paths
        )));
    };

    $skip = $resolve($skip);

    /*
     * Resolved too, for the same reason and it was missed the first time.
     *
     * The walk works in resolved paths, so a protected root of "/tmp/backup"
     * never matches a row under "/private/tmp/backup" and the protection
     * silently does nothing - which is exactly the failure this protection
     * exists to prevent, arriving by a different route.
     */
    $keepChildrenOf = $resolve($keepChildrenOf);

    $sizes = [];
    $counts = [];
    $state = ['complete' => true, 'scanned' => 0, 'inodes' => [], 'unmeasured' => []];

    hostWalkDir($real, $real, 0, $device, $skip, $deadline, $sizes, $counts, $state);

    $dirs = [];
    foreach ($sizes as $path => $bytes) {
        $dirs[] = [
            'path' => $path,
            'depth' => $path === '/' ? 0 : substr_count(rtrim($path, '/'), '/'),
            'bytes' => $bytes,
            'files' => $counts[$path] ?? 0,
            // A size of zero means one of two very different things, and this
            // is which: measured and empty, or never opened.
            'measured' => !isset($state['unmeasured'][$path]),
        ];
    }

    /*
     * Unmeasured directories are kept regardless of the size cut.
     *
     * They report zero bytes, so sorting by size buries them at the bottom and
     * the top-400 cut throws them away - which is exactly how the second
     * backup destination disappeared. A directory nobody looked at is more
     * worth reporting than the four-hundredth largest one somebody did.
     */
    $unmeasured = array_values(array_filter($dirs, fn(array $d): bool => !$d['measured']));
    $measured = array_values(array_filter($dirs, fn(array $d): bool => $d['measured']));

    /*
     * The shallowest unopened directories, and only a handful.
     *
     * A missed /backup/something matters; a missed directory six levels inside
     * one is noise, and there are thousands of those. Sorted by depth so the
     * ones worth naming survive, then capped so they cannot displace the
     * measurements this walk exists to produce.
     */
    usort($unmeasured, function (array $a, array $b): int {
        return [$a['depth'], -$a['bytes']] <=> [$b['depth'], -$b['bytes']];
    });
    $unmeasured = array_slice($unmeasured, 0, HOSTDISK_KEEP_UNMEASURED);

    /*
     * Some directories are kept whatever their size.
     *
     * The row budget is a top-400-by-size cut, which is right for "what is
     * using the disk" and quietly wrong for anything that has a section of its
     * own. A second JetBackup destination holding a few hundred megabytes is
     * not in the top four hundred, so it vanished from the backup panel -
     * which then showed one destination and looked like the whole answer.
     *
     * Only the immediate children of the named paths, so this stays a handful
     * of rows rather than a second walk.
     */
    $always = [];
    $rest = [];
    foreach ($measured as $row) {
        $parent = dirname((string)$row['path']);
        if (in_array($parent, $keepChildrenOf, true) || in_array((string)$row['path'], $keepChildrenOf, true)) {
            $always[] = $row;
        } else {
            $rest[] = $row;
        }
    }

    usort($rest, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);

    $kept = array_merge(
        $always,
        array_slice($rest, 0, max(0, HOSTDISK_KEEP_DIRS - count($unmeasured) - count($always)))
    );

    return [
        'dirs' => array_merge($kept, $unmeasured),
        'unmeasured' => count($unmeasured),
        'unmeasured_total' => count(array_filter($dirs, fn(array $d): bool => !$d['measured'])),
        'complete' => $state['complete'],
        'scanned' => $state['scanned'],
        'seconds' => round(microtime(true) - $started, 2),
    ];
}

/**
 * Accumulates one directory into the totals, recursively.
 *
 * Returns the bytes below it so a parent can add them without walking twice.
 */
function hostWalkDir(
    string $dir,
    string $root,
    int $depth,
    ?int $device,
    array $skip,
    float $deadline,
    array &$sizes,
    array &$counts,
    array &$state
): array {
    if (microtime(true) > $deadline) {
        $state['complete'] = false;
        return ['bytes' => 0, 'files' => 0, 'measured' => false];
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        return ['bytes' => 0, 'files' => 0, 'measured' => false];
    }

    $state['scanned']++;
    $bytes = 0;
    $files = 0;
    $measured = true;

    /*
     * Files first, subdirectories collected for a second pass.
     *
     * The reason is the whole point of this rewrite. Walking depth-first with
     * one shared deadline means the first subdirectory can consume the entire
     * budget, and every sibling after it is never opened - so /backup reported
     * one destination and silently omitted the other. The panel then showed
     * that absence as though it were the answer.
     *
     * Collecting the children first makes it possible to give each of them a
     * share of whatever time is left, so breadth survives running out of it.
     */
    $subdirs = [];

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (microtime(true) > $deadline) {
            $state['complete'] = false;
            $measured = false;
            break;
        }

        $full = ($dir === '/' ? '' : $dir) . '/' . $entry;

        if (in_array($full, $skip, true) || is_link($full)) {
            continue;
        }

        $stat = @lstat($full);
        if ($stat === false) {
            continue;
        }

        if (($stat['mode'] & 0040000) !== 0) {
            /*
             * Never off this filesystem.
             *
             * A mount point looks exactly like a directory until you stat it
             * and find a different device. Descending into one turns a walk of
             * the root filesystem into a walk of every disk, network share and
             * backup volume attached to the machine - and each of those is
             * already its own row in hostMounts().
             */
            if ($device !== null && (int)$stat['dev'] !== $device) {
                continue;
            }

            $subdirs[] = $full;
            continue;
        }

        /*
         * A hard-linked file is counted once across the whole walk.
         *
         * This is producing a total for a disk, so the same blocks must not be
         * added twice because two names point at them. The account-side
         * treemap makes the opposite choice deliberately, for a different
         * reason - see the note in lib/treemap.php.
         */
        if ((int)($stat['nlink'] ?? 1) > 1) {
            $key = $stat['dev'] . ':' . $stat['ino'];
            if (isset($state['inodes'][$key])) {
                continue;
            }
            $state['inodes'][$key] = true;
        }

        /*
         * Blocks, not the apparent size.
         *
         * A sparse file reports a size far larger than it occupies, and a small
         * file still consumes a whole block. df counts blocks, so a walk meant
         * to explain df has to count blocks too - otherwise the two disagree
         * and the disagreement looks like the missing space.
         */
        $bytes += isset($stat['blocks']) && $stat['blocks'] >= 0
            ? (int)$stat['blocks'] * 512
            : (int)$stat['size'];
        $files++;
    }

    closedir($handle);

    /*
     * Each child gets a share of the time that is left when its turn comes.
     *
     * Recomputed per child rather than divided once, so a subdirectory that
     * finishes early hands the rest of its slice to its siblings instead of
     * wasting it. A directory with two children gets half the remaining budget
     * each; one with two hundred gets a sliver each, which is the right answer
     * - better a shallow figure for all of them than a complete one for the
     * first and nothing at all for the rest.
     */
    $remaining = $subdirs;
    $share = $depth < HOSTDISK_FAIR_DEPTH;

    while ($remaining !== []) {
        $left = $deadline - microtime(true);
        if ($left <= 0) {
            $state['complete'] = false;
            $measured = false;
            // The ones never entered are still recorded, marked unmeasured, so
            // they appear as "not measured" rather than as though absent.
            foreach ($remaining as $missed) {
                if ($depth + 1 <= HOSTDISK_MAX_DEPTH && !isset($sizes[$missed])) {
                    $sizes[$missed] = 0;
                    $counts[$missed] = 0;
                    $state['unmeasured'][$missed] = true;
                }
            }
            break;
        }

        $child = array_shift($remaining);

        /*
         * Near the top, each child gets an equal slice of what is left so a
         * huge first sibling cannot starve the rest. Deeper down it inherits
         * the parent's deadline, because dividing again at every level shrinks
         * the budget to nothing long before the leaves.
         */
        $childDeadline = $share
            ? min($deadline, microtime(true) + $left / (count($remaining) + 1))
            : $deadline;

        $below = hostWalkDir(
            $child, $root, $depth + 1, $device, $skip, $childDeadline,
            $sizes, $counts, $state
        );

        $bytes += $below['bytes'];
        $files += $below['files'];
        if (!($below['measured'] ?? true)) {
            $measured = false;
        }
    }

    if ($depth <= HOSTDISK_MAX_DEPTH) {
        $sizes[$dir] = $bytes;
        $counts[$dir] = $files;
        if (!$measured) {
            $state['unmeasured'][$dir] = true;
        }
    }

    return ['bytes' => $bytes, 'files' => $files, 'measured' => $measured];
}

/**
 * Files that have been deleted and are still holding their blocks.
 *
 * The answer nothing else can find. When a process has a file open and the
 * file is unlinked, the directory entry goes but the data stays until the last
 * descriptor closes. df counts those blocks; du, ls, find and every treemap
 * cannot see them, because as far as the filesystem's namespace is concerned
 * there is nothing there.
 *
 * This is why a server can show 72 GB used with 4 GB of visible files, and why
 * the usual advice - "restart the service" - works and seems like magic.
 *
 * Read from /proc/<pid>/fd, where the kernel spells it out: the symlink target
 * of a deleted file ends in " (deleted)". Root sees every process; anyone else
 * sees their own, which is why this belongs to the host collector.
 *
 * @return array{files: list<array>, bytes: int, readable: bool, reason: ?string}
 */
function hostDeletedOpen(string $procRoot = '/proc'): array
{
    $entries = @scandir($procRoot);
    if ($entries === false) {
        return ['files' => [], 'bytes' => 0, 'readable' => false,
                'reason' => 'This host does not expose /proc.'];
    }

    $found = [];
    $seen = [];
    $blocked = 0;

    foreach ($entries as $entry) {
        if (!ctype_digit($entry)) {
            continue;
        }

        $fdDir = "$procRoot/$entry/fd";
        $handles = @scandir($fdDir);
        if ($handles === false) {
            // Somebody else's process, and we are not root.
            $blocked++;
            continue;
        }

        $name = trim((string)@file_get_contents("$procRoot/$entry/comm"));

        foreach ($handles as $fd) {
            if ($fd === '.' || $fd === '..') {
                continue;
            }

            $target = @readlink("$fdDir/$fd");
            if ($target === false || !str_ends_with($target, ' (deleted)')) {
                continue;
            }

            // Only real files. A deleted socket or pipe holds no blocks.
            $stat = @stat("$fdDir/$fd");
            if ($stat === false || ($stat['mode'] & 0100000) === 0) {
                continue;
            }

            $bytes = isset($stat['blocks']) && $stat['blocks'] >= 0
                ? (int)$stat['blocks'] * 512
                : (int)$stat['size'];
            if ($bytes <= 0) {
                continue;
            }

            /*
             * One entry per file, however many processes hold it.
             *
             * A log inherited by forty Apache children is forty descriptors on
             * one inode occupying one file's worth of disk. Counting it once
             * per descriptor would report forty times the space and send
             * somebody hunting for something that is not there.
             */
            $key = $stat['dev'] . ':' . $stat['ino'];
            if (isset($seen[$key])) {
                $found[$seen[$key]]['holders']++;
                continue;
            }
            $seen[$key] = count($found);

            $found[] = [
                'pid' => (int)$entry,
                'process' => $name !== '' ? $name : '?',
                'path' => substr($target, 0, -strlen(' (deleted)')),
                'bytes' => $bytes,
                'holders' => 1,
            ];
        }
    }

    usort($found, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);

    return [
        'files' => array_slice($found, 0, 50),
        'bytes' => array_sum(array_column($found, 'bytes')),
        'readable' => true,
        'reason' => $blocked > 0
            ? sprintf(
                '%d processes could not be inspected. Without root this only covers this '
                . 'account\'s own processes, and the space held by everyone else stays hidden.',
                $blocked
            )
            : null,
    ];
}

/* ------------------------------------------------------------------ *
 * Reading it back
 * ------------------------------------------------------------------ */

/**
 * The last disk walk, in the order the questions should be asked.
 *
 * Filesystems first because half of these investigations end there, then the
 * directories, then the space that has no directory entry at all.
 */
function hostDiskReport(PDO $pdo, int $limit = 40): array
{
    $walk = $pdo->query(
        'SELECT * FROM host_walks ORDER BY taken_at DESC LIMIT 1'
    )->fetch(PDO::FETCH_ASSOC);

    $latestMount = (int)$pdo->query('SELECT MAX(taken_at) FROM host_mounts')->fetchColumn();

    if (!$walk && $latestMount <= 0) {
        return [
            'available' => false,
            'reason' => 'No disk walk has been run yet. As root: php host-cron.php --disk',
            'taken_at' => 0,
            'mounts' => [], 'dirs' => [], 'deleted' => [],
        ];
    }

    $at = (int)($walk['taken_at'] ?? $latestMount);

    $mounts = $pdo->prepare(
        'SELECT * FROM host_mounts WHERE taken_at = ? ORDER BY used_bytes DESC'
    );
    $mounts->execute([$latestMount]);

    $dirs = $pdo->prepare(
        'SELECT * FROM host_dirs WHERE taken_at = ? ORDER BY bytes DESC LIMIT ?'
    );
    $dirs->bindValue(1, $at, PDO::PARAM_INT);
    $dirs->bindValue(2, $limit, PDO::PARAM_INT);
    $dirs->execute();

    $deleted = $pdo->prepare(
        'SELECT * FROM host_deleted WHERE taken_at = ? ORDER BY bytes DESC LIMIT ?'
    );
    $deleted->bindValue(1, $at, PDO::PARAM_INT);
    $deleted->bindValue(2, $limit, PDO::PARAM_INT);
    $deleted->execute();

    $deletedRows = $deleted->fetchAll(PDO::FETCH_ASSOC);

    return [
        'available' => true,
        'reason' => null,
        'taken_at' => $at,
        'age_seconds' => max(0, time() - $at),
        'root' => (string)($walk['root'] ?? '/'),
        'complete' => (bool)($walk['complete'] ?? false),
        'seconds' => (float)($walk['seconds'] ?? 0),
        'dirs_scanned' => (int)($walk['dirs_scanned'] ?? 0),
        'note' => $walk['note'] ?? null,
        'mounts' => $mounts->fetchAll(PDO::FETCH_ASSOC),
        'dirs' => $dirs->fetchAll(PDO::FETCH_ASSOC),
        'deleted' => $deletedRows,
        'deleted_bytes' => (int)($walk['deleted_bytes'] ?? 0),
        'deleted_readable' => (bool)($walk['deleted_readable'] ?? false),
    ];
}

/**
 * How much of a filesystem the walk managed to account for.
 *
 * The number the whole exercise is for. df knows what is used; the walk knows
 * what it found; the difference is what is hiding, and naming it as a figure
 * turns "some space is missing" into "58 GB is missing", which is a fact
 * somebody can chase.
 */
function hostUnaccounted(array $report): ?array
{
    if (!$report['available'] || $report['mounts'] === [] || $report['dirs'] === []) {
        return null;
    }

    $root = $report['root'];
    $mount = null;
    foreach ($report['mounts'] as $row) {
        // The longest mount point that is a prefix of the walked root owns it.
        $point = (string)$row['mount'];
        if ($root === $point || str_starts_with($root, rtrim($point, '/') . '/') || $point === '/') {
            if ($mount === null || strlen($point) > strlen((string)$mount['mount'])) {
                $mount = $row;
            }
        }
    }
    if ($mount === null) {
        return null;
    }

    $found = 0;
    foreach ($report['dirs'] as $dir) {
        if ((string)$dir['path'] === $root) {
            $found = (int)$dir['bytes'];
            break;
        }
    }
    if ($found <= 0) {
        return null;
    }

    $used = (int)$mount['used_bytes'];

    return [
        'mount' => (string)$mount['mount'],
        'used_bytes' => $used,
        'found_bytes' => $found,
        'deleted_bytes' => (int)$report['deleted_bytes'],
        'missing_bytes' => max(0, $used - $found - (int)$report['deleted_bytes']),
        'complete' => (bool)$report['complete'],
    ];
}

/* ------------------------------------------------------------------ *
 * Asking root to do it
 * ------------------------------------------------------------------ */

/** The one task the dashboard can ask for. */
const HOSTDISK_TASK = 'disk-walk';

/**
 * Records that somebody pressed the button.
 *
 * Overwrites rather than queues: two people asking for a disk walk want one
 * disk walk, and the second press should not mean the collector does it twice.
 */
function requestHostWalk(PDO $account, ?string $who = null): int
{
    return requestHostTask($account, HOSTDISK_TASK, $who);
}

/**
 * Leaves any job for the root collector to pick up.
 *
 * One row per task name, overwritten rather than queued: two people asking for
 * the same sweep want one sweep. The task name is the whole protocol - there is
 * no argument column, so anything parameterised encodes it in the name
 * ("expose:iostat"), and the collector validates it again before acting.
 */
function requestHostTask(PDO $account, string $task, ?string $who = null): int
{
    $at = time();
    $account->prepare(
        'INSERT INTO host_tasks (task, requested_at, requested_by)
         VALUES (:task, :at, :who)
         ON CONFLICT(task) DO UPDATE SET requested_at = :at, requested_by = :who'
    )->execute([':task' => $task, ':at' => $at, ':who' => $who]);

    return $at;
}

/**
 * Every outstanding request, read by root out of the account's database.
 *
 * @return array<string, int> task => requested_at
 */
function requestedHostTasks(array $config): array
{
    $file = (string)($config['database'] ?? '');
    if ($file === '' || !is_file($file) || !is_readable($file)) {
        return [];
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA query_only = 1');

        $rows = $pdo->query('SELECT task, requested_at FROM host_tasks')
            ->fetchAll(PDO::FETCH_KEY_PAIR);

        return array_map('intval', $rows);
    } catch (Throwable) {
        return [];
    }
}

/**
 * What the dashboard should say about outstanding requests.
 *
 * Completion is inferred rather than recorded, because the collector must not
 * write to the account's database: a task counts as done once the host side has
 * something newer than the request.
 */
function pendingHostTasks(array $config, PDO $account, ?PDO $host): array
{
    try {
        $rows = $account->query('SELECT task, requested_at FROM host_tasks')
            ->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return [];
    }

    $capabilityRun = 0;
    $walkRun = 0;
    if ($host !== null) {
        try {
            $capabilityRun = (int)$host->query(
                'SELECT MAX(ran_at) FROM host_capability_runs'
            )->fetchColumn();
        } catch (Throwable) {
            $capabilityRun = 0;
        }
        try {
            $walkRun = (int)$host->query('SELECT MAX(taken_at) FROM host_walks')->fetchColumn();
        } catch (Throwable) {
            $walkRun = 0;
        }
    }

    $out = [];
    foreach ($rows as $row) {
        $task = (string)$row['task'];
        $at = (int)$row['requested_at'];

        // See hostWalkPending(): a same-second tie goes to "still pending".
        $done = match (true) {
            $task === HOSTDISK_TASK => $walkRun > $at,
            $task === 'capabilities', str_starts_with($task, 'expose:') => $capabilityRun > $at,
            default => false,
        };

        $out[] = [
            'task' => $task,
            'requested_at' => $at,
            'pending' => !$done,
        ];
    }

    return $out;
}

/**
 * When a walk was last asked for, read out of the account's database by root.
 *
 * Opened by hand with query_only, exactly as daemonWatchFromConfig() does and
 * for the same reason: the root collector has no business writing to a file the
 * account owns, and a pragma is a cheaper guarantee than remembering not to.
 *
 * Any failure means "nothing requested". A collector that ran an expensive disk
 * walk every five minutes because it could not read a table would be a worse
 * outcome than one that never ran it at all.
 */
function requestedHostWalk(array $config): ?int
{
    $file = (string)($config['database'] ?? '');
    if ($file === '' || !is_file($file) || !is_readable($file)) {
        return null;
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA query_only = 1');

        $stmt = $pdo->prepare('SELECT requested_at FROM host_tasks WHERE task = ?');
        $stmt->execute([HOSTDISK_TASK]);
        $at = $stmt->fetchColumn();

        return $at === false || $at === null ? null : (int)$at;
    } catch (Throwable) {
        return null;
    }
}

/**
 * Whether a requested walk is still outstanding.
 *
 * Satisfied when a walk has been recorded at or after the moment it was asked
 * for. That is the whole completion protocol, and it needs no write back into
 * the account's database - host_walks is written by the walk itself.
 */
function hostWalkPending(PDO $host, ?int $requestedAt): bool
{
    if ($requestedAt === null) {
        return false;
    }

    $last = (int)$host->query('SELECT MAX(taken_at) FROM host_walks')->fetchColumn();

    /*
     * A walk in the same second as the request does not count as answering it.
     *
     * Timestamps here are whole seconds, so when they are equal there is no
     * way to tell whether the walk ran before the button was pressed or after.
     * The two mistakes are not equally bad: guessing "answered" gives a button
     * that visibly does nothing and gets pressed again and again, while
     * guessing "still pending" costs at most one extra walk inside a one-second
     * window. So the tie goes to running it.
     *
     * host_walks records the time the walk *started*, which makes this
     * conservative in the useful direction as well: a forty-second walk already
     * in progress when the button is pressed will not be mistaken for a
     * response to it.
     */
    return $last <= $requestedAt;
}

/**
 * Mount points that are views of a subtree already on this filesystem.
 *
 * From /proc/self/mountinfo, where a root field of anything but "/" means the
 * mount shows part of a filesystem rather than the whole of it. That is the
 * definition of a bind, and it is the only reliable way to spot one - the
 * device is the same, the inodes are the same, and nothing in the directory
 * itself says it is a second window onto somewhere else.
 *
 * @return list<string>
 */
function bindMountPoints(string $procRoot = '/proc'): array
{
    if (!function_exists('readMountInfo')) {
        require_once __DIR__ . '/mountinfo.php';
    }

    $points = [];
    foreach (readMountInfo($procRoot) as $mount) {
        if ($mount['bind'] && $mount['mount_point'] !== '/') {
            $points[] = $mount['mount_point'];
        }
    }

    return $points;
}
