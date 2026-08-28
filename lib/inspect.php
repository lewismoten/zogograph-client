<?php
declare(strict_types=1);

/*
 * Everything that can honestly be said about one path.
 *
 * The treemap answers "where is the space" and then stops at a coloured
 * rectangle. This is the other half: you have found the rectangle, and now you
 * want to know what it is, how long it has been getting bigger, whether it is
 * safe to remove and whether you can look inside it before you decide.
 *
 * Three sources, and they are kept apart on purpose:
 *
 *   the filesystem   stat, and for a directory a bounded walk. Live, exact,
 *                    and the only thing that knows about right now.
 *   the database     this path's recorded sizes, if it was ever interesting
 *                    enough to record. Historical, sparse, and absent for most
 *                    paths - which is reported rather than filled in with a
 *                    flat line.
 *   the config       whether it may be deleted or read, which is policy and
 *                    not a property of the file at all.
 *
 * Nothing here deletes anything or reads a file's contents. Both are separate
 * actions with their own checks; this only reports whether they would be
 * allowed, so the interface can offer the button rather than guess.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/collect.php';
require_once __DIR__ . '/logs.php';
require_once __DIR__ . '/treemap.php';

/** How long a directory inspection may spend measuring before it reports a bound. */
const INSPECT_WALK_BUDGET = 3.0;

/** The largest file this will read the head of to identify an image. */
const INSPECT_IMAGE_MAX = 64 * 1024 * 1024;

/** How many recorded sizes to hand back for the history chart. */
const INSPECT_HISTORY_ROWS = 400;

/**
 * The full report on one path.
 *
 * @return array<string, mixed>
 */
function inspectPath(array $config, PDO $pdo, string $requested): array
{
    $real = resolveInspectable($config, $requested);
    $root = realpath((string)$config['scan_root']) ?: '';

    $isLink = is_link($real);
    $isDir = !$isLink && is_dir($real);

    $facts = statFacts($real, $isLink);
    $what = classifyFile($real);

    $out = [
        'path' => $real,
        'name' => basename($real) ?: $real,
        'parent' => dirname($real),
        'kind' => $isLink ? 'link' : ($isDir ? 'dir' : 'file'),
        'inside_account' => $root !== '' && str_starts_with($real . '/', rtrim($root, '/') . '/'),
        'facts' => $facts,
        'type' => $isDir ? null : $what['type'],
        'extension' => $isDir ? null : $what['extension'],
        'category' => categoryOf($real . ($isDir ? '/x' : ''), $config['scan'], $root),
    ];

    /*
     * Blocks rather than apparent size, alongside it.
     *
     * A sparse file reports a size df has never heard of, and a small file
     * still occupies a whole block. Showing both is the only way the number
     * here can be reconciled with the number on the disk.
     */
    $stat = $isLink ? @lstat($real) : @stat($real);
    $out['allocated_bytes'] = $stat === false ? null : (int)($stat['blocks'] ?? 0) * 512;
    $out['inode'] = $stat === false ? null : (int)($stat['ino'] ?? 0);
    $out['device'] = $stat === false ? null : (int)($stat['dev'] ?? 0);

    if ($isLink) {
        $target = @readlink($real);
        $out['link_target'] = $target === false ? null : $target;
        $out['link_resolves'] = $target !== false && file_exists($real);
    }

    if ($isDir) {
        $out['contents'] = directoryContents($config, $real);
        $out['site'] = siteFacts($config, $real);
    } else {
        $out['image'] = imageFacts($real);
        $out['text'] = !$isLink && looksLikeText($real);
    }

    $out['history'] = pathHistoryRows($pdo, $real);
    $out['permissions'] = permissionFacts($config, $pdo, $real, $isDir, $isLink);

    return $out;
}

/**
 * The path, if this account has any business looking at it.
 *
 * Wider than the delete and read rules - describing a file is not the same act
 * as removing it or printing it - but not unbounded, which is where this
 * started. Reporting the size, owner and permissions of any path on the machine
 * makes this a general-purpose stat oracle, and nothing in the interface needs
 * one: every path it can offer comes from the account's own tree or from the
 * host logs the administrator named. So those are the two places it will look.
 *
 * A symlink is described as itself rather than as its target, and the boundary
 * is tested against the *resolved* parent, so a link cannot be used to nominate
 * a file outside the account and have it described under an innocuous name.
 */
function resolveInspectable(array $config, string $requested): string
{
    if ($requested === '' || str_contains($requested, "\0")) {
        throw new ActionRefused('empty or malformed path');
    }

    /*
     * A symlink is described as itself, not as what it points at.
     *
     * realpath would silently substitute the target - so a link into /etc
     * would be reported under its innocuous name, with the target's size and
     * the target's owner, and nothing on screen would say a link was involved.
     */
    $parent = realpath(dirname($requested));
    if ($parent === false) {
        throw new ActionRefused('no such path');
    }
    $real = rtrim($parent, '/') . '/' . basename($requested);

    if (!file_exists($real) && !is_link($real)) {
        throw new ActionRefused('no such path');
    }

    $dataDir = realpath(dirname((string)$config['database']));
    if ($dataDir !== false && str_starts_with($real . '/', $dataDir . '/')) {
        throw new ActionRefused('that is the monitor\'s own data');
    }

    $root = realpath((string)$config['scan_root']);
    if ($root !== false && str_starts_with($real . '/', rtrim($root, '/') . '/')) {
        return $real;
    }

    $hostLogs = array_values(array_filter(
        array_map('realpath', $config['host_log_paths'] ?? []),
        fn(mixed $p): bool => is_string($p)
    ));
    if (in_array($real, $hostLogs, true)) {
        return $real;
    }

    throw new ActionRefused(
        'that path is outside this account and is not one of the host logs named in '
        . 'host_log_paths. Files that only root can see are reported by the host '
        . 'collector, which records them; this account cannot examine them directly.'
    );
}

/**
 * What is directly inside a directory, and how big the whole of it is.
 *
 * The recursive total is bounded by the clock and says when it gave up, for the
 * same reason the treemap does: a lower bound presented as a measurement is how
 * the thing being hunted gets dismissed.
 */
function directoryContents(array $config, string $dir): array
{
    $excluded = array_map('normalisePath', $config['scan']['exclude_paths'] ?? []);
    $total = walkSize($dir, $excluded, microtime(true) + INSPECT_WALK_BUDGET);

    $entries = ['dirs' => 0, 'files' => 0, 'links' => 0];
    $biggest = [];
    $types = [];

    $handle = @opendir($dir);
    if ($handle !== false) {
        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = $dir . '/' . $entry;

            if (is_link($full)) {
                $entries['links']++;
                continue;
            }
            if (is_dir($full)) {
                $entries['dirs']++;
                continue;
            }

            $entries['files']++;
            $size = (int)(@filesize($full) ?: 0);
            $biggest[$entry] = $size;
        }
        closedir($handle);
    }

    arsort($biggest);

    /*
     * Types from the recursive walk, not from this one directory.
     *
     * "What is in here" means the whole subtree - a directory whose immediate
     * children are all directories would otherwise report no types at all,
     * which is the case where the question is most worth asking.
     */
    $types = $total['types'];
    arsort($types);

    return [
        'bytes' => $total['bytes'],
        'files' => $total['files'],
        'complete' => $total['complete'],
        'entries' => $entries,
        'biggest' => array_map(
            fn(string $name, int $bytes): array => ['name' => $name, 'bytes' => $bytes],
            array_keys(array_slice($biggest, 0, 8, true)),
            array_values(array_slice($biggest, 0, 8, true))
        ),
        'types' => array_map(
            fn(string $type, int $bytes): array => ['type' => $type, 'bytes' => $bytes],
            array_keys($types),
            array_values($types)
        ),
    ];
}

/*
 * Which domains, if any, are served out of a directory.
 *
 * On cPanel this is knowable without guessing. `~/.cpanel/userdata/main` lists
 * every domain on the account and `~/.cpanel/userdata/<domain>` gives its
 * document root, so a directory can be matched against the real configuration
 * rather than against a naming convention. That file belongs to the account, so
 * the dashboard can read it without needing root.
 *
 * Everything else is a guess and is marked as one. A directory named after a
 * domain and containing an index file is *probably* a site; a directory called
 * `public_html` almost certainly is. Neither is evidence, so they are reported
 * with `confident => false` and the interface says "looks like" rather than
 * "is".
 *
 * @return array{is_site: bool, domains: list<string>, confident: bool, why: string}
 */
function siteFacts(array $config, string $path): array
{
    $none = ['is_site' => false, 'domains' => [], 'confident' => false, 'why' => ''];

    if (!is_dir($path)) {
        return $none;
    }

    $known = cpanelDocumentRoots($config);
    $real = realpath($path);

    if ($real !== false && isset($known[$real])) {
        return [
            'is_site' => true,
            'domains' => $known[$real],
            'confident' => true,
            'why' => 'This is the document root cPanel has recorded for '
                . (count($known[$real]) === 1 ? 'this domain' : 'these domains') . '.',
        ];
    }

    /*
     * No cPanel data, so fall back to what the directory looks like.
     *
     * A document root has an index file in it. Without one, a directory named
     * `example.com` is just as likely to be a backup of a site as a site, and
     * calling it a site would put a globe on the wrong rectangle.
     */
    $name = basename($path);
    $looksLikeDomain = (bool)preg_match(
        '/^(?:www\.)?[a-z0-9]([a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i',
        $name
    );

    $indexes = ['index.php', 'index.html', 'index.htm', 'index.cgi'];
    $hasIndex = false;
    foreach ($indexes as $index) {
        if (is_file($path . '/' . $index)) {
            $hasIndex = true;
            break;
        }
    }

    /*
     * Why the cPanel record did not answer, which is two different things.
     *
     * Either it could not be read at all - no cPanel, or a home directory this
     * is not - or it was read and does not list this directory, which means
     * cPanel serves nothing from here and whatever is in it is something else.
     * Saying "not readable" in the second case would be a confident falsehood.
     */
    $why = $known === []
        ? 'cPanel\'s own record of document roots could not be read, so this is a guess '
            . 'from the name and contents.'
        : 'cPanel does not serve anything from here, so this is a guess from the name '
            . 'and contents — quite possibly a copy of a site rather than a live one.';

    if ($name === 'public_html' && $hasIndex) {
        return [
            'is_site' => true, 'domains' => [], 'confident' => false,
            'why' => 'A public_html with an index file in it. ' . $why,
        ];
    }

    if ($looksLikeDomain && $hasIndex) {
        return [
            'is_site' => true, 'domains' => [strtolower($name)], 'confident' => false,
            'why' => 'Named like a domain and contains an index file. ' . $why,
        ];
    }

    return $none;
}

/**
 * Document root to domains, from the account's own cPanel data.
 *
 * Read once per request and cached: the treemap asks about every child of a
 * directory, and re-reading and re-parsing the userdata files for each one
 * would be dozens of opens to answer the same question.
 *
 * Parsed with a deliberately small YAML reader rather than a real one. These
 * files are flat `key: value` and nothing else, no library is guaranteed to be
 * present on a shared host, and only one key is wanted.
 *
 * @return array<string, list<string>>
 */
function cpanelDocumentRoots(array $config): array
{
    static $roots = null;
    if ($roots !== null) {
        return $roots;
    }

    $roots = [];
    $home = (string)($config['scan_root'] ?? '');
    $dir = rtrim($home, '/') . '/.cpanel/userdata';

    if (!is_dir($dir) || !is_readable($dir)) {
        return $roots;
    }

    $handle = @opendir($dir);
    if ($handle === false) {
        return $roots;
    }

    while (($entry = readdir($handle)) !== false) {
        // main and *.cache are indexes, not per-domain files.
        if ($entry === '.' || $entry === '..' || $entry === 'main'
            || str_ends_with($entry, '.cache')) {
            continue;
        }

        $file = $dir . '/' . $entry;
        if (!is_file($file) || !is_readable($file)) {
            continue;
        }

        $documentRoot = null;
        $serverName = null;
        $aliases = [];

        foreach (preg_split('/\R/', (string)@file_get_contents($file)) ?: [] as $line) {
            if (preg_match('/^documentroot:\s*(.+)$/i', $line, $m)) {
                $documentRoot = trim($m[1], " \t'\"");
            } elseif (preg_match('/^servername:\s*(.+)$/i', $line, $m)) {
                $serverName = trim($m[1], " \t'\"");
            } elseif (preg_match('/^serveralias:\s*(.+)$/i', $line, $m)) {
                $aliases = preg_split('/\s+/', trim($m[1], " \t'\"")) ?: [];
            }
        }

        if ($documentRoot === null) {
            continue;
        }

        $resolved = realpath($documentRoot);
        if ($resolved === false) {
            continue;
        }

        $names = array_values(array_unique(array_filter(
            array_merge([$serverName ?? $entry], $aliases),
            fn(string $n): bool => $n !== ''
        )));

        $roots[$resolved] = array_values(array_unique(
            array_merge($roots[$resolved] ?? [], $names)
        ));
    }

    closedir($handle);
    return $roots;
}

/**
 * Whether this is an image, decided by its first bytes rather than its name.
 *
 * A `.png` is a claim made by whoever named the file. The signature is a fact
 * about its contents, and it is the only safe basis for deciding to show
 * something as a picture: rendering a file as an image because of its extension
 * is how a mislabelled thing gets handed to a decoder that was not expecting it.
 *
 * Dimensions come from getimagesize, which parses only the header.
 *
 * @return array{is_image: bool, format: ?string, width: ?int, height: ?int, reason: string}
 */
function imageFacts(string $path): array
{
    $none = ['is_image' => false, 'format' => null, 'width' => null, 'height' => null, 'reason' => ''];

    $size = @filesize($path);
    if ($size === false || $size < 8) {
        return $none + ['reason' => 'too small to carry a signature'];
    }
    if ($size > INSPECT_IMAGE_MAX) {
        return array_merge($none, ['reason' => 'larger than this will read for a preview']);
    }

    $handle = @fopen($path, 'rb');
    if ($handle === false) {
        return array_merge($none, ['reason' => 'not readable by this user']);
    }
    $head = (string)fread($handle, 16);
    fclose($handle);

    $format = imageSignature($head);
    if ($format === null) {
        return array_merge($none, ['reason' => 'the first bytes are not an image signature']);
    }

    $facts = ['is_image' => true, 'format' => $format, 'width' => null, 'height' => null, 'reason' => ''];

    if (function_exists('getimagesize')) {
        $measured = @getimagesize($path);
        if (is_array($measured)) {
            $facts['width'] = (int)$measured[0];
            $facts['height'] = (int)$measured[1];
        }
    }

    return $facts;
}

/**
 * The format named by a file's opening bytes, or null.
 *
 * Only the formats a browser will render without help. A TIFF or a PSD is an
 * image and cannot be shown as one, so recognising it here would produce a
 * broken picture rather than a useful answer.
 */
function imageSignature(string $head): ?string
{
    if (str_starts_with($head, "\x89PNG\r\n\x1a\n")) {
        return 'png';
    }
    if (str_starts_with($head, "GIF87a") || str_starts_with($head, "GIF89a")) {
        return 'gif';
    }
    if (str_starts_with($head, "\xff\xd8\xff")) {
        return 'jpeg';
    }
    if (str_starts_with($head, "BM")) {
        return 'bmp';
    }
    // RIFF....WEBP
    if (str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP') {
        return 'webp';
    }
    // An SVG is text, and text is not identified by a signature. Left out
    // deliberately: it is a document that can carry script, not a bitmap.
    return null;
}

/** The mime type to serve a recognised image as. */
function imageMime(string $format): string
{
    return match ($format) {
        'png' => 'image/png',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        default => 'application/octet-stream',
    };
}

/**
 * This path's recorded sizes, if it has any.
 *
 * Most paths have none - only the interesting ones are written each scan - and
 * that is reported as an absence rather than smoothed into an empty chart.
 */
function pathHistoryRows(PDO $pdo, string $path): array
{
    $stmt = $pdo->prepare('SELECT id, category FROM paths WHERE path = ?');
    $stmt->execute([$path]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['recorded' => false, 'path_id' => null, 'rows' => []];
    }

    $sizes = $pdo->prepare(
        'SELECT s.started_at, ps.bytes, ps.files, ps.links
           FROM path_sizes ps JOIN scans s ON s.id = ps.scan_id
          WHERE ps.path_id = ? AND s.finished_at IS NOT NULL
          ORDER BY s.started_at DESC LIMIT ' . INSPECT_HISTORY_ROWS
    );
    $sizes->execute([(int)$row['id']]);
    $rows = array_reverse($sizes->fetchAll());

    return [
        'recorded' => true,
        'path_id' => (int)$row['id'],
        'category' => $row['category'],
        'rows' => $rows,
    ];
}

/**
 * What the interface may offer for this path, and why not when it may not.
 *
 * The reasons matter more than the flags. "Delete is unavailable" invites the
 * user to go and find another way; "this is outside the categories the config
 * allows deleting from" tells them which line to change if they disagree.
 */
function permissionFacts(
    array $config,
    PDO $pdo,
    string $path,
    bool $isDir,
    bool $isLink
): array {
    $out = [
        'delete' => false, 'delete_reason' => '',
        'view' => false, 'view_reason' => '',
    ];

    if ($isLink) {
        $out['delete_reason'] = 'This is a symlink. Removing it would not free any space, '
            . 'and following it to delete what it points at is not something this will do.';
        $out['view_reason'] = 'This is a symlink.';
        return $out;
    }

    try {
        resolveDeletable($config, $path);
        $out['delete'] = true;
    } catch (ActionRefused $e) {
        $out['delete_reason'] = ucfirst($e->getMessage()) . '.';
    }

    if ($isDir) {
        $out['view_reason'] = 'A directory has no contents to read.';
        return $out;
    }

    try {
        resolveViewable($config, $path);
        $out['view'] = true;
    } catch (ActionRefused $e) {
        $out['view_reason'] = ucfirst($e->getMessage()) . '.';
    }

    return $out;
}
