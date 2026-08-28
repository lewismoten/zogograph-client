<?php
declare(strict_types=1);

/*
 * Where the space actually went, as a picture.
 *
 * A table of the twenty-five biggest paths answers "what is large" and is
 * hopeless at "where is it". Space hides in aggregate: forty thousand session
 * files of 8 KB each are 320 MB and not one of them appears in any top-N list,
 * because individually they are nothing. A treemap shows the directory that
 * holds them as a large rectangle, which is the only view in which that problem
 * is visible at all.
 *
 * The storage decision, which is the interesting one:
 *
 * Nothing is stored for this. Not one row.
 *
 * The tempting design is a nightly job that records the whole tree so the map
 * can be drawn instantly - and that is a table with one row per directory,
 * growing with the disk it watches, on a tool whose entire purpose is to stop
 * a disk filling up. The scanner already keeps only the biggest few hundred
 * paths for exactly this reason.
 *
 * A treemap does not need the whole tree. It shows one directory at a time and
 * you click into a child to see the next; each view is the immediate children
 * of one path. So this reads the filesystem live, one level per request, and
 * stores nothing. The stored scan is used where it happens to have the answer
 * already, and ignored otherwise.
 *
 * That leaves one real cost: the recursive size of a child directory needs a
 * walk, and walking /home on every click would be its own denial of service.
 * Hence the deadline. A walk that runs out of time returns what it has and
 * says so - the child is drawn with a "measured so far" marker rather than a
 * confident wrong number. See the note on walkSize().
 */

require_once __DIR__ . '/collect.php';
// siteFacts, so a document root can carry its domains onto the map.
require_once __DIR__ . '/inspect.php';
require_once __DIR__ . '/actions.php';

/** Longest a single treemap request may spend reading the filesystem. */
const TREEMAP_BUDGET_SECONDS = 4.0;

/** Children returned for one directory. Beyond this the small ones are pooled. */
const TREEMAP_MAX_CHILDREN = 400;

/** Directories deeper than this are not descended when measuring a subtree. */
const TREEMAP_MAX_DEPTH = 24;

/**
 * Resolves a requested path to something safe to read.
 *
 * The same shape as resolveDeletable() and deliberately not the same function:
 * that one additionally refuses anything outside a deletable category, which is
 * right for deleting and wrong for looking - the whole point here is to be able
 * to look anywhere inside the watched directory, including the parts that must
 * never be deleted from a browser.
 */
function resolveTreemapPath(array $config, string $requested): string
{
    $root = realpath((string)$config['scan_root']);
    if ($root === false) {
        throw new ActionRefused('the watched directory does not resolve');
    }

    if ($requested === '' || $requested === '/') {
        return $root;
    }

    if (str_contains($requested, "\0")) {
        throw new ActionRefused('malformed path');
    }

    $real = realpath($requested);
    if ($real === false) {
        throw new ActionRefused('path does not exist');
    }

    // The trailing separator matters: without it "/home/regaldra-old" passes a
    // prefix test against "/home/regaldra".
    if ($real !== $root && !str_starts_with($real . '/', rtrim($root, '/') . '/')) {
        throw new ActionRefused('outside the watched directory');
    }

    if (!is_dir($real)) {
        throw new ActionRefused('not a directory');
    }

    /*
     * Never through a link.
     *
     * realpath() has already resolved any link in the middle of the path, and
     * the prefix test above was applied to the resolved answer - so a link
     * pointing outside is caught. This refuses the last component as well, so
     * that a link *inside* the root pointing at another place inside the root
     * cannot be used to make one directory appear in two places in the map.
     */
    if (is_link($requested)) {
        throw new ActionRefused('that is a symlink');
    }

    return $real;
}

/**
 * One level of the map: the immediate children of a directory.
 *
 * @return array{
 *   path: string, parent: ?string, root: string, children: list<array>,
 *   bytes: int, files: int, complete: bool, seconds: float, pooled: int
 * }
 */
function treemapLevel(array $config, string $requested, ?float $budget = null): array
{
    $root = realpath((string)$config['scan_root']);
    $path = resolveTreemapPath($config, $requested);
    $deadline = microtime(true) + ($budget ?? TREEMAP_BUDGET_SECONDS);
    $started = microtime(true);

    $excluded = array_map('normalisePath', $config['scan']['exclude_paths'] ?? []);

    $handle = @opendir($path);
    if ($handle === false) {
        throw new ActionRefused('that directory cannot be read');
    }

    $children = [];
    $complete = true;

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $full = $path . DIRECTORY_SEPARATOR . $entry;

        /*
         * Symlinks are listed but never followed or measured.
         *
         * Following one double counts at best - the same bytes appearing in two
         * rectangles - and at worst walks out of the watched directory or into
         * a loop. Shown with a size of zero and a kind of its own, so the map
         * says the entry exists without claiming it occupies anything.
         */
        if (is_link($full)) {
            $children[] = [
                'name' => $entry,
                'path' => $full,
                'kind' => 'link',
                'bytes' => 0,
                'files' => 0,
                'complete' => true,
            ] + statFacts($full, true);
            continue;
        }

        if (isExcludedPath($full, $excluded)) {
            continue;
        }

        if (is_dir($full)) {
            $measured = walkSize($full, $excluded, $deadline);
            $children[] = [
                'name' => $entry,
                'path' => $full,
                'kind' => 'dir',
                'bytes' => $measured['bytes'],
                'type_bytes' => $measured['types'],
                /*
                 * The domains served out of it, where that is knowable.
                 *
                 * A globe on a rectangle answers "which of these forty
                 * directories is the live site" without opening any of them,
                 * which is the question somebody looking at a home directory
                 * usually has. cpanelDocumentRoots caches, so this is a lookup
                 * per child rather than a re-read.
                 */
                'site' => siteFacts($config, $full),
                'files' => $measured['files'],
                'complete' => $measured['complete'],
            ] + statFacts($full, false);

            if (!$measured['complete']) {
                $complete = false;
            }
            continue;
        }

        $facts = statFacts($full, false);
        $children[] = [
            'name' => $entry,
            'path' => $full,
            'kind' => 'file',
            'bytes' => $facts['size_bytes'],
            'files' => 1,
            'complete' => true,
            /*
             * Whether it runs, which the browser cannot work out for itself.
             *
             * Most Unix executables have no extension, so a map coloured by
             * type in the browser would put every compiled helper in `other`.
             * One bit, and it comes from the stat that has already happened.
             */
            'executable' => (bool)(((int)octdec((string)($facts['mode'] ?? '0'))) & 0o111),
        ] + $facts;
    }

    closedir($handle);

    usort($children, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);

    /*
     * Everything past the cap becomes one rectangle.
     *
     * A directory with 40,000 entries cannot be drawn as 40,000 rectangles -
     * they would be a fraction of a pixel each and the browser would spend a
     * second on shapes nobody can see. Pooling them keeps the *area* honest,
     * which is what a treemap is for: the pool is as big as its contents and
     * can be clicked into.
     */
    $pooled = 0;
    if (count($children) > TREEMAP_MAX_CHILDREN) {
        $rest = array_slice($children, TREEMAP_MAX_CHILDREN);
        $children = array_slice($children, 0, TREEMAP_MAX_CHILDREN);

        $pooled = count($rest);
        $children[] = [
            'name' => sprintf('%s smaller entries', number_format($pooled)),
            'path' => $path,
            'kind' => 'pool',
            'bytes' => array_sum(array_column($rest, 'bytes')),
            'files' => array_sum(array_column($rest, 'files')),
            'complete' => true,
            'size_bytes' => 0,
            'modified_at' => null,
            'changed_at' => null,
            'accessed_at' => null,
            'mode' => null,
            'owner' => null,
            'links' => 1,
        ];
    }

    $parent = $path === $root ? null : dirname($path);
    if ($parent !== null && !str_starts_with($parent . '/', rtrim((string)$root, '/') . '/')
        && $parent !== $root) {
        $parent = null;
    }

    return [
        'path' => $path,
        'parent' => $parent,
        'root' => (string)$root,
        'children' => $children,
        'bytes' => array_sum(array_column($children, 'bytes')),
        'files' => array_sum(array_column($children, 'files')),
        'complete' => $complete,
        'pooled' => $pooled,
        'seconds' => round(microtime(true) - $started, 3),
    ];
}

/**
 * Total size of a subtree, or as much of it as the deadline allowed.
 *
 * The honesty problem this function exists to solve: a size that stopped early
 * is a lower bound, not a measurement, and the two must not look the same. A
 * directory reported as 400 MB when the walk gave up halfway will be dismissed
 * as small, and the 12 GB underneath it never found - which is precisely the
 * failure this whole feature is meant to prevent.
 *
 * So `complete` travels with every number, and the browser draws an unfinished
 * one differently.
 *
 * Per-type totals travel with the size, so the map can be filtered to one kind
 * of file without a second walk. The alternative - filtering in the browser -
 * cannot work: a directory's rectangle has to shrink to the audio *inside* it,
 * and only something that walked the tree knows how much that is.
 *
 * @return array{bytes: int, files: int, complete: bool, types: array<string,int>}
 */
function walkSize(string $dir, array $excluded, float $deadline, int $depth = 0): array
{
    $bytes = 0;
    $files = 0;
    $complete = true;
    $types = [];

    if ($depth > TREEMAP_MAX_DEPTH || microtime(true) > $deadline) {
        return ['bytes' => 0, 'files' => 0, 'complete' => false, 'types' => []];
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        // Unreadable is not empty. Saying so lets the map show a directory that
        // exists and cannot be measured, rather than one that looks empty.
        return ['bytes' => 0, 'files' => 0, 'complete' => false, 'types' => []];
    }

    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (microtime(true) > $deadline) {
            $complete = false;
            break;
        }

        $full = $dir . DIRECTORY_SEPARATOR . $entry;

        // Never followed, for the same reason as above: a link's target is
        // counted where it actually lives, or not at all.
        if (is_link($full) || isExcludedPath($full, $excluded)) {
            continue;
        }

        if (is_dir($full)) {
            $below = walkSize($full, $excluded, $deadline, $depth + 1);
            $bytes += $below['bytes'];
            $files += $below['files'];
            foreach ($below['types'] as $type => $size) {
                $types[$type] = ($types[$type] ?? 0) + $size;
            }
            if (!$below['complete']) {
                $complete = false;
            }
            continue;
        }

        $stat = @stat($full);
        if ($stat === false) {
            continue;
        }

        /*
         * A hard-linked file is counted once per name here, deliberately.
         *
         * The scanner deduplicates by inode because it is producing a total for
         * the disk. This is producing an area for a rectangle, and a file that
         * appears under two names in two different directories occupies space
         * in both as far as anyone reading the map is concerned. The tooltip
         * reports the link count so the difference is visible where it matters.
         */
        $bytes += (int)$stat['size'];
        $files++;

        $type = fileTypeOf($full);
        // A file with no extension is classified by its execute bit, and the
        // stat is already in hand - no second syscall for the same question.
        if ($type === 'other' && (((int)$stat['mode']) & 0o111)) {
            $type = 'executables';
        }
        $types[$type] = ($types[$type] ?? 0) + (int)$stat['size'];
    }

    closedir($handle);

    return ['bytes' => $bytes, 'files' => $files, 'complete' => $complete, 'types' => $types];
}

/**
 * The facts a tooltip needs, from one stat.
 *
 * Gathered while walking rather than fetched when something is hovered: a
 * tooltip that has to make a request before it can say anything is a tooltip
 * that appears after the pointer has moved on.
 */
function statFacts(string $path, bool $isLink): array
{
    $stat = $isLink ? @lstat($path) : @stat($path);
    if ($stat === false) {
        return [
            'size_bytes' => 0, 'modified_at' => null, 'changed_at' => null,
            'accessed_at' => null, 'mode' => null, 'owner' => null, 'links' => 1,
        ];
    }

    return [
        'size_bytes' => (int)$stat['size'],
        'modified_at' => (int)$stat['mtime'],
        /*
         * ctime is when the inode last changed, not when the file was created.
         *
         * Unix does not record a creation time on most filesystems, and
         * labelling ctime "created" is a lie that gets repeated in every file
         * manager. It is still useful - a permissions change or a rename moves
         * it - so it is reported under its own name.
         */
        'changed_at' => (int)$stat['ctime'],
        'accessed_at' => (int)$stat['atime'],
        'mode' => substr(sprintf('%o', $stat['mode']), -4),
        'owner' => ownerName((int)$stat['uid']),
        'links' => (int)$stat['nlink'],
    ];
}

/** A username where the host will say, the uid where it will not. */
function ownerName(int $uid): string
{
    static $cache = [];
    if (isset($cache[$uid])) {
        return $cache[$uid];
    }

    if (function_exists('posix_getpwuid')) {
        $entry = @posix_getpwuid($uid);
        if (is_array($entry) && isset($entry['name'])) {
            return $cache[$uid] = (string)$entry['name'];
        }
    }

    return $cache[$uid] = (string)$uid;
}

/**
 * The same picture for the host, built from what the collector recorded.
 *
 * No live walk here, and there cannot be one: those files belong to root and
 * the dashboard runs as the account. What exists is host_files - the large
 * files root can see and this account cannot - so the map is assembled by
 * grouping those under their directories.
 *
 * It is therefore a map of the recorded files rather than of the whole disk,
 * and says so. That is still the view nobody else can produce: it is where the
 * space is that the account-side map is structurally blind to.
 */
function hostTreemapLevel(PDO $host, string $requested): array
{
    $latest = (int)$host->query('SELECT MAX(id) FROM host_samples')->fetchColumn();
    if ($latest <= 0) {
        return [
            'path' => '/', 'parent' => null, 'root' => '/', 'children' => [],
            'bytes' => 0, 'files' => 0, 'complete' => false, 'pooled' => 0,
            'seconds' => 0.0,
            'reason' => 'The host collector has not recorded any files yet.',
        ];
    }

    $stmt = $host->prepare(
        'SELECT path, bytes, owner, mtime, device, inode, links
         FROM host_files WHERE sample_id = ?'
    );
    $stmt->execute([$latest]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $base = $requested === '' ? '/' : rtrim($requested, '/');
    if ($base === '') {
        $base = '/';
    }
    $prefix = $base === '/' ? '/' : $base . '/';

    $children = [];
    $countedInodes = [];

    foreach ($files as $file) {
        $path = (string)$file['path'];
        if (!str_starts_with($path, $prefix)) {
            continue;
        }

        $rest = substr($path, strlen($prefix));
        $slash = strpos($rest, '/');

        /*
         * The facts the collector already has, rather than a row of nulls.
         *
         * host_files records the owner and the modification time along with the
         * size, so the tooltip on the host map is as full as the one on the
         * account map - which matters more here, since these are the files
         * nothing else on the dashboard can describe at all.
         */
        $facts = [
            'size_bytes' => (int)$file['bytes'],
            'modified_at' => $file['mtime'] === null ? null : (int)$file['mtime'],
            'changed_at' => null,
            'accessed_at' => null,
            'mode' => null,
            'owner' => $file['owner'] === null ? null : (string)$file['owner'],
            'links' => max(1, (int)$file['links']),
        ];

        if ($slash === false) {
            $children[$rest] = [
                'name' => $rest,
                'path' => $path,
                'kind' => 'file',
                'bytes' => (int)$file['bytes'],
                'files' => 1,
                'complete' => true,
            ] + $facts;
            continue;
        }

        $dir = substr($rest, 0, $slash);
        $children[$dir] ??= [
            'name' => $dir,
            'path' => $prefix . $dir,
            'kind' => 'dir',
            'bytes' => 0,
            'files' => 0,
            'complete' => false,
        ] + blankFacts(0);

        /*
         * A hard-linked file is added to a directory total once.
         *
         * The collector records device and inode precisely so that two names
         * for one 217 MB file are not reported as 434 MB, and a directory
         * rollup that re-added them would undo that work one level up. Each
         * name still gets its own rectangle at the level it lives in, where
         * the tooltip's link count explains it.
         */
        $key = $file['device'] === null || $file['inode'] === null
            ? $path
            : $file['device'] . ':' . $file['inode'];

        if (!isset($countedInodes[$key])) {
            $countedInodes[$key] = true;
            $children[$dir]['bytes'] += (int)$file['bytes'];
        }
        $children[$dir]['files']++;
    }

    $children = array_values($children);
    usort($children, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);

    return [
        'path' => $base,
        'parent' => $base === '/' ? null : dirname($base),
        'root' => '/',
        'children' => $children,
        'bytes' => array_sum(array_column($children, 'bytes')),
        'files' => array_sum(array_column($children, 'files')),
        // Never complete, and it matters: this is the recorded files only.
        'complete' => false,
        'pooled' => 0,
        'seconds' => 0.0,
        'reason' => 'Built from the files the host collector recorded, not from a walk of '
            . 'the disk - the dashboard cannot read root\'s directories. Sizes are the sum '
            . 'of the recorded files underneath, so anything below the collector\'s '
            . 'threshold is not counted.',
    ];
}

/** Placeholder facts for rows that came from the database rather than a stat. */
function blankFacts(int $bytes): array
{
    return [
        'size_bytes' => $bytes, 'modified_at' => null, 'changed_at' => null,
        'accessed_at' => null, 'mode' => null, 'owner' => null, 'links' => 1,
    ];
}
