<?php
declare(strict_types=1);

/*
 * The newest lines from every log at once, and what keeps repeating in them.
 *
 * Two problems this solves that the single-file viewer cannot.
 *
 * The first is that a machine misbehaving does not tell you which log to open.
 * There are a dozen candidates - the account's error logs, each domain's, the
 * host's if it can be read - and the useful view is all of them merged, newest
 * first, because the line that explains it is the most recent one *somewhere*.
 *
 * The second is repetition. A log filling a disk is almost never full of
 * interesting distinct events; it is one event ten thousand times. Reading the
 * last hundred lines shows the same line a hundred times and no more. What is
 * needed is the *shape* of the line with its variable parts removed, counted -
 * "this happened 41,000 times" is a finding, and the same information as forty
 * one thousand lines nobody can read.
 *
 * Ordering across files is done on the timestamps in the lines where they can
 * be parsed, and by file modification time where they cannot. Which of the two
 * was used is reported, because a merged view sorted by the wrong clock is
 * confidently wrong about the sequence of events, and sequence is the whole
 * point of merging.
 */

require_once __DIR__ . '/logs.php';

/** How many lines to read from the end of each file before merging. */
const STREAM_PER_FILE = 60;

/** How much of each line to keep. Log lines can be enormous. */
const STREAM_MAX_LINE = 400;

/**
 * The newest lines across several files, merged.
 *
 * @param list<array{path: string, label: string}> $files
 * @return array{rows: list<array>, files: list<array>, ordering: string, note: string}
 */
function logStream(array $files, int $want = 20): array
{
    $want = max(1, min(200, $want));

    $rows = [];
    $read = [];
    $timed = 0;
    $untimed = 0;

    foreach (array_slice($files, 0, 40) as $file) {
        $path = (string)$file['path'];
        $label = (string)($file['label'] ?? basename($path));

        if (!is_file($path) || !is_readable($path)) {
            $read[] = ['path' => $path, 'label' => $label, 'ok' => false,
                       'reason' => 'not readable'];
            continue;
        }

        if (compressedExtension($path) !== null) {
            $read[] = ['path' => $path, 'label' => $label, 'ok' => false,
                       'reason' => 'compressed'];
            continue;
        }

        try {
            $tail = tailFile($path, STREAM_PER_FILE, 0, '');
        } catch (Throwable $e) {
            $read[] = ['path' => $path, 'label' => $label, 'ok' => false,
                       'reason' => $e->getMessage()];
            continue;
        }

        $mtime = (int)(@filemtime($path) ?: 0);
        $read[] = [
            'path' => $path, 'label' => $label, 'ok' => true, 'reason' => '',
            'lines' => count($tail['lines']), 'bytes' => $tail['size'], 'modified_at' => $mtime,
        ];

        /*
         * Folded here, per file, before anything is merged or sorted.
         *
         * A stack frame belongs to the line above it *in the file*. That is a
         * fact about the file, not about the merged sequence - and the frames
         * carry no timestamp, so once everything is interleaved they sort by
         * their file's mtime and end up nowhere near the error they belong to.
         * Folding after the merge attached them to whatever happened to be
         * adjacent, which was usually the wrong entry.
         */
        foreach (foldFileLines($tail['lines']) as $line) {
            $text = (string)$line['text'];
            $at = logLineTime($text);

            if ($at === null) {
                $untimed++;
            } else {
                $timed++;
            }

            $rows[] = [
                'parsed' => $line['parsed'],
                'trace' => $line['trace'],
                'path' => $path,
                'label' => $label,
                'n' => (int)$line['n'],
                'text' => mb_substr($text, 0, STREAM_MAX_LINE),
                'at' => $at,
                /*
                 * The fallback clock, and it is a poor one.
                 *
                 * A file's mtime is when it was last written, which is the time
                 * of its *newest* line - so using it for every line from that
                 * file bunches them all at one moment. Good enough to decide
                 * which file is currently active, not good enough to interleave
                 * lines with. Hence saying which was used.
                 */
                'at_guess' => $mtime,
                'timed' => $at !== null,
            ];
        }
    }

    /*
     * Sorted newest first, on whichever clock each line has.
     *
     * A line with a real timestamp sorts on it. A line without sorts on its
     * file's mtime, which puts it near the other lines from the same file - the
     * least wrong place for it.
     */
    usort($rows, fn(array $a, array $b): int =>
        (($b['at'] ?? $b['at_guess']) <=> ($a['at'] ?? $a['at_guess']))
        ?: ($b['n'] <=> $a['n']));

    $ordering = $untimed === 0 ? 'timestamps'
        : ($timed === 0 ? 'file times' : 'mixed');

    return [
        'rows' => array_slice($rows, 0, $want),
        'files' => $read,
        'ordering' => $ordering,
        'note' => match ($ordering) {
            'timestamps' => '',
            'file times' => 'None of these lines carry a timestamp this recognises, so they '
                . 'are ordered by when each file was last written. Lines from one file keep '
                . 'their order; lines from different files may be interleaved wrongly.',
            default => sprintf(
                '%d of these lines carry a timestamp and %d do not. The ones without are '
                . 'placed by when their file was last written, which is only approximately '
                . 'where they belong.',
                $timed,
                $untimed
            ),
        },
    ];
}

/**
 * The time a log line claims for itself, or null.
 *
 * Four formats, which between them cover almost everything a web host writes:
 * Apache's bracketed form, the ISO-ish one most modern software uses, syslog's
 * monthless-year form, and the Common Log Format found in access logs.
 *
 * Returns null rather than guessing. A wrong timestamp is worse than none in a
 * merged view: it silently moves a line to the wrong place in the sequence, and
 * the sequence is the only reason to merge.
 */
function logLineTime(string $line): ?int
{
    $head = substr($line, 0, 120);

    // [Mon Aug 24 09:20:00 2026] or [Mon Aug 24 09:20:00.123456 2026]
    if (preg_match('/^\[([A-Z][a-z]{2} [A-Z][a-z]{2} +\d+ \d{2}:\d{2}:\d{2})(?:\.\d+)? (\d{4})\]/', $head, $m)) {
        $at = strtotime($m[1] . ' ' . $m[2]);
        return $at === false ? null : $at;
    }

    /*
     * PHP's own: [25-Aug-2026 11:40:20 America/New_York]
     *
     * Which is the format of the file this tool most often reads and was the
     * one format missing - so every line of every PHP error log fell through
     * to "no timestamp" and got placed by its file's mtime instead. In a view
     * whose whole purpose is ordering lines from several files together, that
     * was the ordering being wrong for the majority of them.
     *
     * The zone is a name rather than an offset, so it is applied rather than
     * parsed: strtotime does not accept "11:40:20 America/New_York".
     */
    if (preg_match('/^\[(\d{1,2}-[A-Z][a-z]{2}-\d{4} \d{2}:\d{2}:\d{2})(?:\s+([A-Za-z_\/+-]{2,40}))?\]/', $head, $m)) {
        $zone = null;
        if (isset($m[2]) && $m[2] !== '') {
            try {
                $zone = new DateTimeZone($m[2]);
            } catch (Throwable) {
                // An unrecognised zone is better ignored than fatal: the local
                // one is at worst a few hours out, and null would lose the line.
                $zone = null;
            }
        }

        try {
            $when = new DateTimeImmutable($m[1], $zone);
            return $when->getTimestamp();
        } catch (Throwable) {
            return null;
        }
    }

    // 2026-08-24 09:20:00 or 2026-08-24T09:20:00Z or with an offset
    if (preg_match('/(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:[.,]\d+)?(?:Z|[+-]\d{2}:?\d{2})?)/', $head, $m)) {
        $at = strtotime(str_replace(',', '.', $m[1]));
        return $at === false ? null : $at;
    }

    // Common Log Format: [24/Aug/2026:09:20:00 +0000]
    if (preg_match('#\[(\d{2}/[A-Z][a-z]{2}/\d{4}):(\d{2}:\d{2}:\d{2}) ([+-]\d{4})\]#', $head, $m)) {
        $at = strtotime(str_replace('/', '-', $m[1]) . ' ' . $m[2] . ' ' . $m[3]);
        return $at === false ? null : $at;
    }

    /*
     * syslog: "Aug 24 09:20:00", with no year at all.
     *
     * The year is inferred as the current one, and rolled back when that would
     * put the line in the future - which is what happens across New Year. A
     * line from last December read on the 2nd of January is otherwise stamped
     * eleven months ahead of everything around it.
     */
    if (preg_match('/^([A-Z][a-z]{2} +\d+ \d{2}:\d{2}:\d{2})/', $head, $m)) {
        $year = (int)date('Y');
        $at = strtotime($m[1] . ' ' . $year);
        if ($at === false) {
            return null;
        }
        if ($at > time() + 86400) {
            $at = strtotime($m[1] . ' ' . ($year - 1));
        }
        return $at === false ? null : $at;
    }

    return null;
}

/**
 * Which lines keep coming back, with the variable parts taken out.
 *
 * The normalisation is the whole idea. Ten thousand lines differing only in a
 * PID, an IP and a request path are one event, and counting them as one event
 * with a count of ten thousand is the only presentation of that fact anybody
 * can act on.
 *
 * What is replaced is deliberately conservative: numbers, addresses, quoted
 * strings, hex blobs and bracketed fields. Replacing more would fold together
 * lines that are genuinely different - "permission denied" and "no such file"
 * must not become one row - and the failure mode of folding too much is that
 * the finding disappears into a generic bucket.
 *
 * @return array{groups: list<array>, lines: int, distinct: int}
 */
function repeatedLines(array $rows, int $keep = 15): array
{
    $groups = [];

    foreach ($rows as $row) {
        $text = (string)($row['text'] ?? '');
        if (trim($text) === '') {
            continue;
        }

        $shape = normaliseLogLine($text);
        if (!isset($groups[$shape])) {
            /*
             * The message, not the raw line.
             *
             * Every one of these begins with a timestamp and a level - forty
             * characters that are identical down the whole column and are
             * exactly what the group has already factored out. Showing the raw
             * line meant the cell was full before it reached the part that
             * differs, so the column read as a list of dates. The raw line is
             * still one click away.
             */
            $parsed = $row['parsed'] ?? parseLogLine($text);

            $groups[$shape] = [
                'shape' => $shape,
                'count' => 0,
                'example' => $text,
                'message' => trim((string)($parsed['message'] ?? $text)),
                'kind' => (string)($parsed['kind'] ?? ''),
                'where' => (string)($parsed['where'] ?? ''),
                'files' => [],
                'first_at' => null,
                'last_at' => null,
            ];
        }

        $groups[$shape]['count']++;
        $groups[$shape]['files'][(string)($row['label'] ?? '')] = true;

        $at = $row['at'] ?? null;
        if ($at !== null) {
            $groups[$shape]['first_at'] = $groups[$shape]['first_at'] === null
                ? $at : min($groups[$shape]['first_at'], $at);
            $groups[$shape]['last_at'] = $groups[$shape]['last_at'] === null
                ? $at : max($groups[$shape]['last_at'], $at);
        }
    }

    $out = [];
    foreach ($groups as $group) {
        $group['files'] = array_values(array_filter(array_keys($group['files'])));

        /*
         * A rate, where the timestamps allow one.
         *
         * "41,000 times" is a finding; "41,000 times in the last four minutes"
         * is an emergency, and the difference is not visible in the count.
         */
        $span = ($group['last_at'] ?? 0) - ($group['first_at'] ?? 0);
        $group['per_minute'] = $span > 0 ? round($group['count'] / ($span / 60), 1) : null;
        $group['span_seconds'] = $span > 0 ? $span : null;

        $out[] = $group;
    }

    usort($out, fn(array $a, array $b): int => $b['count'] <=> $a['count']);

    return [
        'groups' => array_slice($out, 0, $keep),
        'lines' => count($rows),
        'distinct' => count($groups),
    ];
}

/**
 * A log line with the parts that vary between occurrences taken out.
 *
 * Order matters here: the longer, more specific patterns run first, so an IPv4
 * address is replaced as an address rather than as four numbers.
 */
function normaliseLogLine(string $line): string
{
    $shape = $line;

    // Timestamps in the shapes logLineTime knows about.
    $shape = preg_replace('/\[[A-Z][a-z]{2} [A-Z][a-z]{2} +\d+ [\d:.]+ \d{4}\]/', '[TIME]', $shape);
    $shape = preg_replace('#\[\d{2}/[A-Z][a-z]{2}/\d{4}:[\d:]+ [+-]\d{4}\]#', '[TIME]', $shape);
    $shape = preg_replace('/\d{4}-\d{2}-\d{2}[T ][\d:]+(?:[.,]\d+)?(?:Z|[+-][\d:]+)?/', 'TIME', $shape);
    $shape = preg_replace('/^[A-Z][a-z]{2} +\d+ \d{2}:\d{2}:\d{2}/', 'TIME', $shape);

    // Addresses before numbers, or an IPv4 becomes four separate N's.
    $shape = preg_replace('/\b\d{1,3}(?:\.\d{1,3}){3}\b/', 'IP', $shape);
    $shape = preg_replace('/\b(?:[0-9a-f]{1,4}:){2,7}[0-9a-f]{0,4}\b/i', 'IP6', $shape);

    // Quoted runs, which in an access log are the whole request.
    $shape = preg_replace('/"[^"]*"/', '"S"', $shape);
    $shape = preg_replace("/'[^']*'/", "'S'", $shape);

    // Long hex - session ids, hashes, request ids.
    $shape = preg_replace('/\b[0-9a-f]{8,}\b/i', 'HEX', $shape);

    // Paths, which differ per request and are usually the only difference.
    $shape = preg_replace('#(?<![\w.])/[^\s:,()"\']{2,}#', '/PATH', $shape);

    // Anything left that is a number.
    $shape = preg_replace('/\b\d+\b/', 'N', $shape);

    // Whitespace last, so the collapsing does not merge tokens above.
    $shape = trim((string)preg_replace('/\s+/', ' ', (string)$shape));

    return mb_substr($shape, 0, 200);
}

/* ------------------------------------------------------ taking a line apart -- */

/**
 * A log line, split into the parts a person reads.
 *
 * "Every log at once" was showing whole raw lines, which is almost useless for
 * the thing it exists for. A PHP error line is a bracketed timestamp, then a
 * timezone, then the word PHP, then a level, then a colon, then the message,
 * then usually the file and line number - and the message, which is the only
 * part anybody is scanning for, starts a hundred characters in and gets cut off
 * by the column width.
 *
 * So: the timestamp comes out, the level comes out, and what is left is the
 * message. Sorted into fields the eye can skip down.
 *
 * `kind` is one of info, notice, warning, error, critical, or '' when the line
 * announces no level at all. It is taken from what the line says rather than
 * guessed from the words in it: a message that happens to contain "error" is
 * not necessarily one.
 *
 * @return array<string, mixed>
 */
function parseLogLine(string $line): array
{
    $out = [
        'kind' => '',
        'where' => '',
        'message' => trim($line),
        'trace' => false,
        'frame' => null,
        'stamp' => '',
    ];

    $rest = rtrim($line, "\r\n");

    /*
     * A stack frame is recognised first, and never parsed further.
     *
     * `#0 /path/file.php(12): thing()` belongs to the error above it, and
     * treating it as its own entry is what fills the merged view with lines
     * that have no timestamp, no level and no meaning on their own.
     */
    if (preg_match('/^\s*#(\d+)\s+(.*)$/', $rest, $m)) {
        return [
            'kind' => '',
            'where' => '',
            'message' => trim($m[2]),
            'trace' => true,
            'frame' => (int)$m[1],
            'stamp' => '',
        ];
    }

    /*
     * The timestamp comes off before anything else is decided.
     *
     * Including before the trace check: PHP writes `[25-Aug-2026 ...] PHP Stack
     * trace:` with the timestamp on the front, so looking for the trace header
     * in the raw line missed every one that had a date on it - which is all of
     * them, in the file this view reads.
     *
     * Removed rather than kept, because the merged view has a column for it
     * already; repeating it inside the message is the padding this whole
     * function exists to strip.
     */
    $patterns = [
        // Apache and PHP: [25-Aug-2026 11:40:29 America/New_York]
        '/^\[([^\]]{6,60})\]\s*/',
        // ISO-ish: 2026-08-25T11:40:29-04:00, or with a space
        '/^(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:[.,]\d+)?(?:Z|[+-]\d{2}:?\d{2})?)\s*/',
        // syslog: Aug 25 11:40:29
        '/^([A-Z][a-z]{2}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2})\s*/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $rest, $m)) {
            $out['stamp'] = trim($m[1]);
            $rest = substr($rest, strlen($m[0]));
            break;
        }
    }

    /*
     * A leading colon left by the timestamp.
     *
     * suexec writes `[2026-08-25 11:39:00]: uid: ...`, so once the bracket is
     * removed the line starts with a colon and every later rule is thrown off
     * by it.
     */
    $rest = preg_replace('/^\s*:\s*/', '', $rest) ?? $rest;

    /*
     * The other shapes a continuation takes.
     *
     * The header of a trace, the "thrown in" line that closes one, and both
     * numbering styles: PHP's own `PHP   1. {main}()` and the exception
     * form `#0 /path(12): thing()`.
     */
    if (preg_match('/^(PHP\s+)?(Stack trace:|thrown in\b|#\d+\s|\d+\.\s)/i', ltrim($rest))) {
        return [
            'kind' => '', 'where' => '', 'message' => trim($rest),
            'trace' => true, 'frame' => null, 'stamp' => $out['stamp'],
        ];
    }

    /*
     * This tool's own log is tab-separated: time, level, where, message.
     *
     * Recognised explicitly because it is the file somebody reads when the
     * dashboard itself is misbehaving, and leaving its level unparsed would
     * mean the one log guaranteed to be present is the one shown worst.
     */
    if (str_contains($rest, "\t")) {
        $fields = explode("\t", $rest);
        $level = logLevelWord($fields[0]);
        if ($level !== '' && count($fields) >= 2) {
            $out['kind'] = $level;
            array_shift($fields);
            if (count($fields) >= 2) {
                $out['where'] = trim(array_shift($fields));
            }
            $rest = implode(' ', $fields);
        }
    }

    /*
     * A bracketed field is either a level or the source.
     *
     * `[diskmon] [error] something` and `[core:notice] [pid 12] something` are
     * both common. Whichever bracket reads as a level becomes the level; the
     * rest name whatever wrote the line, which is exactly the question a merged
     * view raises.
     */
    while (preg_match('/^\[([^\]]{1,40})\]\s*/', $rest, $m)) {
        $inside = trim($m[1]);
        $rest = substr($rest, strlen($m[0]));

        $level = logLevelWord($inside);
        if ($level !== '' && $out['kind'] === '') {
            $out['kind'] = $level;
        } elseif ($out['where'] === '') {
            $out['where'] = $inside;
        }
    }

    /*
     * `PHP Warning:  message` and friends.
     *
     * The level is the words before the first colon, and only when they are
     * short and read as a level - otherwise a message that happens to contain
     * a colon would have its first clause promoted to a severity.
     */
    if (preg_match('/^(?:PHP\s+)?([A-Za-z][A-Za-z ]{1,24}?):\s*(.*)$/s', $rest, $m)) {
        $level = logLevelWord($m[1]);
        if ($level !== '') {
            $out['kind'] = $level;
            if ($out['where'] === '' && stripos($m[1], 'PHP') === false) {
                // The exact wording is kept: "Fatal error" and "Recoverable
                // fatal error" are different things that map to one kind.
                $out['where'] = trim($m[1]);
            }
            $rest = $m[2];
        }
    }

    /*
     * syslog's `host program[pid]:` prefix.
     *
     * The program is the useful half - "which of the twenty things writing to
     * /var/log/messages said this" - and the hostname is the same on every
     * line of a file read from the machine it describes.
     */
    if ($out['where'] === ''
        && preg_match('/^(\S+)\s+([A-Za-z][\w.\/-]{1,32})(\[\d+\])?:\s+(.*)$/s', $rest, $m)
        /*
         * Not when the second word is a level.
         *
         * `PHP Warning:  something` fits this shape exactly - "PHP" as the
         * host and "Warning" as the program - and matching it here consumed
         * the line before the level branch could see it, so every PHP warning
         * came out with no severity at all.
         */
        && logLevelWord($m[2]) === '') {
        $out['where'] = $m[2] . ($m[3] ?? '');
        $rest = $m[4];
    }

    $out['message'] = trim($rest);

    /*
     * The file and line, pulled off the end where PHP puts it.
     *
     * ` in /home/x/y.php on line 12` is not part of the sentence and is often
     * the longest thing on the line. Held separately so the message reads as a
     * message and the location is still there when it is wanted.
     */
    if (preg_match('/^(.*?)\s+in\s+(\/\S+)\s+on\s+line\s+(\d+)\.?$/s', $out['message'], $m)) {
        $out['message'] = trim($m[1]);
        $out['file'] = $m[2];
        $out['line'] = (int)$m[3];
    } elseif (preg_match('/^(.*?)\s+in\s+(\/\S+):(\d+)$/s', $out['message'], $m)) {
        // The `:12` form, which is what an uncaught exception writes.
        $out['message'] = trim($m[1]);
        $out['file'] = $m[2];
        $out['line'] = (int)$m[3];
    }

    return $out;
}

/**
 * The severity a word announces, or '' if it is not announcing one.
 *
 * Matched against a list rather than by looking for the word anywhere: "an
 * error occurred while warning the user" is not a level, and a merged view
 * that marked it critical would be teaching people to ignore the marks.
 */
function logLevelWord(string $word): string
{
    $word = strtolower(trim($word));

    // `core:error`, `php:warn`, `LogLevel=error` - the level is the last part.
    if (str_contains($word, ':')) {
        $word = trim((string)strrchr($word, ':'), ': ');
    }

    return match (true) {
        $word === 'emerg' || $word === 'emergency' || $word === 'alert'
            || $word === 'crit' || $word === 'critical'
            || $word === 'fatal error' || $word === 'fatal'
            || $word === 'parse error' || $word === 'core error' => 'critical',

        $word === 'err' || $word === 'error' || $word === 'exception'
            || $word === 'recoverable fatal error'
            || $word === 'user error' || $word === 'core warning' => 'error',

        $word === 'warn' || $word === 'warning' || $word === 'user warning'
            || $word === 'deprecated' || $word === 'user deprecated' => 'warning',

        $word === 'notice' || $word === 'user notice' || $word === 'strict standards'
            => 'notice',

        $word === 'info' || $word === 'debug' || $word === 'trace' => 'info',

        default => '',
    };
}

/**
 * Merges stack-trace lines into the entry above them, within one file.
 *
 * A PHP fatal writes one line for the error and then a dozen for the trace, so
 * a view showing the twenty newest lines across every file showed one error and
 * nineteen fragments of it. The trace is not discarded - it is attached to its
 * error, which is where it is useful and where it came from.
 *
 * Lines arrive in file order, oldest first, which is the order this needs: a
 * trace follows its error, so each frame attaches to the most recent entry.
 *
 * @param list<array{n: int, text: string}> $lines
 * @return list<array{n: int, text: string, parsed: array, trace: list<string>}>
 */
function foldFileLines(array $lines): array
{
    $out = [];

    foreach ($lines as $line) {
        $text = (string)$line['text'];
        $parsed = parseLogLine($text);

        if ($parsed['trace'] && $out !== []) {
            $out[count($out) - 1]['trace'][] = $parsed['message'];
            continue;
        }

        /*
         * A frame with nothing above it is kept as an entry of its own.
         *
         * It happens when the tail begins part-way through a trace, and
         * silently dropping it would mean a view promising "the 20 newest
         * lines" showing twelve with no explanation of the rest.
         */
        $out[] = [
            'n' => (int)$line['n'],
            'text' => $text,
            'parsed' => $parsed,
            'trace' => [],
        ];
    }

    return $out;
}
