<?php
declare(strict_types=1);

/*
 * Reading the end of a log file.
 *
 * The whole reason this exists is a ten gigabyte `error_log`, so the one rule
 * that governs everything here is: never read the file. Not into memory, not
 * into a string, not with file(), not with SplFileObject in a loop from the
 * top. The end of a log is at the end, so the reader seeks there and walks
 * backwards in blocks, and the cost of showing the last two hundred lines of a
 * ten gigabyte file is the same as for a ten kilobyte one.
 *
 * Searching cannot be free in the same way - a match might be anywhere - so it
 * is bounded instead: it scans backwards through a fixed budget of bytes and
 * says so when it stops early. A search that quietly gave up would be worse
 * than one that admits it only looked at the last sixty-four megabytes.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/collect.php';

/** Read backwards in blocks of this size. */
const TAIL_BLOCK = 65536;
/** The furthest a search will read before giving up and saying so. */
const TAIL_SEARCH_BUDGET = 64 * 1024 * 1024;
/** Longest single line kept. A log with no newlines is not a log. */
const TAIL_MAX_LINE = 8192;

/**
 * Resolves a path the signed-in user may read.
 *
 * Narrower than the delete rules and separate from them on purpose: being
 * allowed to *look at* a file and being allowed to remove it are different
 * permissions, and the set of things worth reading (logs, error logs) is not
 * the set of things worth deleting (trash, temp).
 */
/**
 * Names that cannot be tailed whatever they are called.
 *
 * A rotated log keeps the word "log" in its name and lands in the same
 * directory, so the category rules put it squarely in `logs` - and then the
 * viewer reads the last few kilobytes of a gzip stream and prints them. On the
 * machine this was found, 81% of the last 200 bytes of a .gz were unprintable.
 *
 * Decompressing to tail is not the fix. Gzip cannot be read backwards, so the
 * only way to see the end is to inflate the whole thing - which on a 700 MB
 * archive is exactly the cost the reverse-block reader exists to avoid. The
 * honest answer is that this viewer does not read compressed files.
 */
const COMPRESSED_EXTENSIONS = [
    'gz',
    'bz2',
    'xz',
    'zst',
    'lz4',
    'lzma',
    'z',
    'zip',
    '7z',
    'rar',
    'tar',
    'tgz',
    'tbz2',
    'txz',
];

/** The compressed extension of a path, or null if it has none. */
function compressedExtension(string $path): ?string
{
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($extension, COMPRESSED_EXTENSIONS, true)
        ? $extension
        : null;
}

/**
 * Whether the end of a file looks like text.
 *
 * The extension list catches what people name sensibly; this catches the rest.
 * Only the tail is sampled, because the tail is what the viewer shows - a text
 * log with a binary blob pasted into the middle is still readable at the end.
 *
 * Bytes above 0x7F are allowed: they are how UTF-8 spells anything that is not
 * ASCII, and rejecting them would refuse a perfectly good log the moment
 * somebody's name appeared in it. What gives binary away is control characters,
 * which text uses only as tab, newline and carriage return.
 */
function looksLikeText(string $path, int $sample = 4096): bool
{
    $handle = @fopen($path, 'rb');
    if ($handle === false) {
        return false;
    }

    $size = @filesize($path);
    if ($size === false) {
        fclose($handle);
        return false;
    }
    if ($size === 0) {
        fclose($handle);
        return true;
    }

    if ($size > $sample) {
        fseek($handle, -$sample, SEEK_END);
    }
    $chunk = (string) fread($handle, $sample);
    fclose($handle);

    if ($chunk === '') {
        return true;
    }
    // A NUL byte is the clearest single signal that this is not text.
    if (str_contains($chunk, "\0")) {
        return false;
    }

    $control = 0;
    $length = strlen($chunk);
    for ($i = 0; $i < $length; $i++) {
        $byte = ord($chunk[$i]);
        if (
            ($byte < 32 && !in_array($byte, [9, 10, 13], true)) ||
            $byte === 127
        ) {
            $control++;
        }
    }

    return $control / $length <= 0.1;
}

function resolveViewable(array $config, string $requested): string
{
    if ($requested === '' || str_contains($requested, "\0")) {
        throw new ActionRefused('empty or malformed path');
    }

    $real = realpath($requested);
    if ($real === false || !is_file($real)) {
        throw new ActionRefused('no such file');
    }

    /*
     * The host's own logs, when the config names them and the host allows it.
     *
     * Checked before the scan-root rule, because these are outside it by
     * definition. Membership of the list is not permission - the open() below
     * decides that, as it should - it only says the administrator considers
     * this file one of the ones worth offering. Compared exactly against the
     * resolved path so a symlink cannot be used to nominate a different file.
     */
    $hostLogs = array_values(
        array_filter(
            array_map('realpath', $config['host_log_paths'] ?? []),
            fn(mixed $p): bool => is_string($p),
        ),
    );
    $isHostLog = in_array($real, $hostLogs, true);

    $root = realpath((string) $config['scan_root']);
    if (
        !$isHostLog &&
        ($root === false ||
            !str_starts_with($real . '/', rtrim($root, '/') . '/'))
    ) {
        throw new ActionRefused(
            'outside the scan root, and not one of the host logs ' .
                'named in host_log_paths',
        );
    }
    if (is_link($requested) || is_link($real)) {
        throw new ActionRefused('symlinks are not read');
    }

    $dataDir = realpath(dirname((string) $config['database']));
    if ($dataDir !== false && str_starts_with($real . '/', $dataDir . '/')) {
        throw new ActionRefused('not the monitor\'s own data');
    }

    // A named host log is already the administrator saying "this one"; the
    // account's category rules describe the account's own tree and have nothing
    // to say about /var/log.
    if (!$isHostLog) {
        $category = categoryOf(
            $real,
            $config['scan'],
            $root === false ? '' : $root,
        );
        $allowed = $config['viewable_categories'] ?? ['logs', 'errors'];
        if ($category === null || !in_array($category, $allowed, true)) {
            throw new ActionRefused('only log files can be read here');
        }
    }

    if (!is_readable($real)) {
        throw new ActionRefused(
            $isHostLog
                ? 'that log belongs to the host and this account cannot read it. On a ' .
                    'shared server that is normal and there is nothing to change here — ' .
                    'it needs a group membership or an ACL granted by root.'
                : 'the file is not readable by this user',
        );
    }

    $compressed = compressedExtension($real);
    if ($compressed !== null) {
        throw new ActionRefused(
            sprintf(
                'that is a .%s archive, not a readable log. Compressed files cannot be ' .
                    'tailed: gzip and friends cannot be read backwards, so showing the end ' .
                    'would mean decompressing the whole file. Uncompress a copy and read that.',
                $compressed,
            ),
        );
    }

    if (!looksLikeText($real)) {
        throw new ActionRefused(
            'that file does not look like text — the end of it is mostly control bytes, ' .
                'so showing it would print binary rather than log lines.',
        );
    }

    return $real;
}

/**
 * The last lines of a file, oldest first, without reading the whole thing.
 *
 * `page` counts backwards from the end: page 0 is the last `lines`, page 1 the
 * `lines` before those. With a `needle`, only matching lines are counted, so
 * paging walks through matches rather than through the file.
 *
 * @return array{
 *   lines: list<array{n:int, text:string}>,
 *   size:int, scanned:int, truncated:bool, reached_start:bool,
 *   total_lines:?int, matched:int
 * }
 */
function tailFile(
    string $path,
    int $lines = 200,
    int $page = 0,
    string $needle = '',
    bool $caseSensitive = false,
): array {
    $lines = max(1, min(2000, $lines));
    $page = max(0, $page);

    $handle = @fopen($path, 'rb');
    if ($handle === false) {
        throw new ActionRefused('could not open the file');
    }

    $size = (int) max(0, @filesize($path) ?: 0);
    $position = $size;
    $remainder = '';
    $collected = [];
    $skip = $page * $lines;
    $matched = 0;
    $scanned = 0;
    $searching = $needle !== '';
    $budget = $searching ? TAIL_SEARCH_BUDGET : PHP_INT_MAX;

    /*
     * Line numbers are counted from the end, negative-style, then flipped at
     * the finish. Counting from the start would mean knowing how many lines the
     * file has, which means reading all of it - the one thing we will not do.
     */
    $fromEnd = 0;

    while ($position > 0 && count($collected) < $lines && $scanned < $budget) {
        $read = (int) min(TAIL_BLOCK, $position);
        $position -= $read;
        fseek($handle, $position);
        $block = (string) fread($handle, $read);
        $scanned += $read;

        $chunk = $block . $remainder;
        $parts = explode("\n", $chunk);
        // The first piece may be half a line: the rest of it is further back.
        $remainder = $position > 0 ? array_shift($parts) : '';

        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $text = rtrim($parts[$i], "\r");
            $fromEnd++;

            if ($text === '' && $i === count($parts) - 1 && $fromEnd === 1) {
                // The trailing newline every well-behaved log ends with.
                $fromEnd--;
                continue;
            }

            if ($searching && !lineMatches($text, $needle, $caseSensitive)) {
                continue;
            }

            $matched++;
            if ($skip > 0) {
                $skip--;
                continue;
            }

            $collected[] = [
                'n' => $fromEnd,
                'text' =>
                    strlen($text) > TAIL_MAX_LINE
                        ? substr($text, 0, TAIL_MAX_LINE) . ' …[line truncated]'
                        : $text,
            ];

            if (count($collected) >= $lines) {
                break;
            }
        }
    }

    fclose($handle);

    // Gathered newest-first because that is the direction it was read; shown
    // oldest-first because that is the direction a log is read.
    $collected = array_reverse($collected);

    return [
        'lines' => $collected,
        'size' => $size,
        'scanned' => $scanned,
        'truncated' => $searching && $scanned >= $budget && $position > 0,
        'reached_start' => $position <= 0,
        'matched' => $matched,
        'searching' => $searching,
    ];
}

function lineMatches(string $line, string $needle, bool $caseSensitive): bool
{
    return $caseSensitive
        ? str_contains($line, $needle)
        : stripos($line, $needle) !== false;
}
