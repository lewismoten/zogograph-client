<?php
declare(strict_types=1);

/*
 * What is installed, and when it last changed.
 *
 * Almost every "it was working yesterday" turns out to be something changing
 * underneath - a kernel that came with a package update, an OpenSSL bump that
 * moved a default, a PHP point release that deprecated a function. None of it
 * is in any chart here, and the machine does not keep a note of it either: the
 * package manager's log is root's, is not always present, and says what was
 * installed rather than what is running now.
 *
 * So the versions are read and remembered, and the moment one moves is
 * recorded. Not what was *installed* - what is actually answering, which is a
 * different thing after an update that needs a restart.
 *
 * Where a version cannot be had, a fingerprint stands in. Plenty of binaries
 * have no --version and plenty more print something unparseable, and a change
 * still matters: "the file is different from the one that was here on Tuesday"
 * is a smaller fact than "it went from 3.0.11 to 3.0.13" but it is the same
 * kind of fact, and it is the one that answers "did anything change".
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';

/**
 * How much of a file goes into its fingerprint.
 *
 * The head, not the whole thing. A binary can be a hundred megabytes and this
 * runs on a shared box; the first chunk plus the size and modification time
 * distinguishes builds without reading gigabytes to do it. It is not a
 * security check - nothing here defends against a deliberately crafted file -
 * it is a way to notice that something moved.
 */
const FINGERPRINT_BYTES = 65536;

/**
 * A short, stable marker for the contents of a file.
 *
 * Returns null for anything unreadable, which is a normal answer on a shared
 * host rather than an error: plenty of the interesting binaries are root's.
 */
function fileFingerprint(string $path): ?string
{
    if ($path === '' || !is_file($path) || !is_readable($path)) {
        return null;
    }

    $size = @filesize($path);
    $mtime = @filemtime($path);
    if ($size === false || $mtime === false) {
        return null;
    }

    $handle = @fopen($path, 'rb');
    if ($handle === false) {
        return null;
    }

    $head = (string)@fread($handle, FINGERPRINT_BYTES);
    fclose($handle);

    /*
     * Size and mtime go in as well as the bytes.
     *
     * Two builds of the same program often share their first 64 KB - the ELF
     * header and the start of the text section barely move between point
     * releases - and the size almost never matches. Including it turns a weak
     * check into a reliable one at no extra cost.
     */
    return substr(hash('sha256', $size . ':' . $mtime . ':' . $head), 0, 16);
}

/**
 * The versions of the things most likely to break something when they move.
 *
 * Read from files wherever possible rather than by running programs: this is
 * called on every capability probe, and shelling out a dozen times to learn
 * things that /proc already knows would be a waste on a machine that is
 * probably already busy.
 *
 * @return list<array{name: string, label: string, value: ?string, note: string}>
 */
function criticalSoftware(): array
{
    $out = [];

    $add = function (string $name, string $label, ?string $value, string $note) use (&$out): void {
        $out[] = [
            'name' => $name,
            'label' => $label,
            'value' => $value === null || trim($value) === '' ? null : trim($value),
            'note' => $note,
        ];
    };

    /*
     * The kernel, from /proc rather than from uname.
     *
     * Same answer, no process. `osrelease` is the version people quote;
     * `version` carries the build stamp, which is what distinguishes two
     * rebuilds of the same release - and a rebuild is exactly the change that
     * turns up as "nothing changed" in every other record.
     */
    $add('kernel.release', 'Kernel', readTrimmed('/proc/sys/kernel/osrelease'),
        'The running kernel. A change here means the machine was rebooted into '
        . 'something else, whatever the package log says.');

    $build = readTrimmed('/proc/version');
    $add('kernel.build', 'Kernel build', $build === null ? null : substr($build, 0, 200),
        'The full build string, including who built it and when. Two rebuilds of the same '
        . 'release differ here and nowhere else.');

    /*
     * The distribution, from os-release.
     *
     * A change here is a major upgrade, which is rarely a surprise but is
     * always worth being able to point at afterwards.
     */
    $add('os.name', 'Operating system', osReleaseField('PRETTY_NAME'),
        'The distribution and its release. A change is a major upgrade.');

    // PHP itself, which is this tool's own floor.
    $add('php.version', 'PHP', PHP_VERSION,
        'What this dashboard is running on. A point release can deprecate a function '
        . 'that half the site depends on.');

    $add('php.extensions', 'PHP extensions', phpExtensionFingerprint(),
        'A fingerprint over the loaded extension list. It moves when one is added, '
        . 'removed or replaced - which is how a working site starts returning 500 after '
        . 'a routine update.');

    $add('php.ini', 'PHP configuration', configFingerprint(php_ini_loaded_file() ?: ''),
        'A fingerprint over the loaded php.ini. Changes here are invisible everywhere '
        . 'else and change how everything behaves.');

    /*
     * OpenSSL, from the extension rather than from the command.
     *
     * The library PHP is linked against is the one that matters for anything
     * this tool does, and it is not always the same as whatever `openssl
     * version` prints - a machine can easily have two.
     */
    $add('openssl.version', 'OpenSSL (as PHP sees it)',
        defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : null,
        'The library PHP is linked against, which is not always the one the openssl '
        . 'command reports - a machine can have two.');

    $add('sqlite.version', 'SQLite', sqliteVersion(),
        'What both databases here are stored by.');

    $add('libc.version', 'C library', libcVersion(),
        'Almost everything on the machine is linked against it, so a change here is a '
        . 'change to almost everything.');

    $add('timezone', 'System timezone', readTrimmed('/etc/timezone') ?: date_default_timezone_get(),
        'A change moves every timestamp on this site relative to every log file on the '
        . 'machine, which is a confusing hour to spend.');

    return $out;
}

function readTrimmed(string $path): ?string
{
    if (!is_file($path) || !is_readable($path)) {
        return null;
    }
    $text = @file_get_contents($path, false, null, 0, 4096);
    return $text === false ? null : trim($text);
}

/** One field out of /etc/os-release, unquoted. */
function osReleaseField(string $field): ?string
{
    $text = readTrimmed('/etc/os-release');
    if ($text === null) {
        return null;
    }

    foreach (explode("\n", $text) as $line) {
        if (!str_starts_with($line, $field . '=')) {
            continue;
        }
        return trim(substr($line, strlen($field) + 1), " \"'");
    }

    return null;
}

/**
 * A marker over the loaded PHP extensions.
 *
 * The list rather than the versions: an extension appearing or disappearing is
 * the change that breaks things, and it is not visible in PHP's own version
 * number at all.
 */
function phpExtensionFingerprint(): ?string
{
    $loaded = get_loaded_extensions();
    if ($loaded === []) {
        return null;
    }

    sort($loaded);
    return count($loaded) . ' loaded · ' . substr(hash('sha256', implode(',', $loaded)), 0, 12);
}

/** A marker over a configuration file, or null if it cannot be read. */
function configFingerprint(string $path): ?string
{
    $mark = fileFingerprint($path);
    return $mark === null ? null : basename($path) . ' · ' . $mark;
}

function sqliteVersion(): ?string
{
    try {
        return (new PDO('sqlite::memory:'))->query('SELECT sqlite_version()')->fetchColumn()
            ?: null;
    } catch (Throwable) {
        return null;
    }
}

/**
 * The C library version, read rather than executed.
 *
 * glibc's shared object is executable and prints its own version when run,
 * which is the documented way and is still running a program. The path is
 * enough to name the version on every distribution that follows the usual
 * convention, and a fingerprint covers the ones that do not.
 */
function libcVersion(): ?string
{
    foreach (['/lib64', '/lib/x86_64-linux-gnu', '/lib/aarch64-linux-gnu', '/lib'] as $directory) {
        foreach (@glob($directory . '/libc-*.so') ?: [] as $path) {
            if (preg_match('/libc-(\d+\.\d+(?:\.\d+)?)\.so$/', $path, $m)) {
                return 'glibc ' . $m[1];
            }
        }

        $plain = $directory . '/libc.so.6';
        if (is_file($plain)) {
            $mark = fileFingerprint($plain);
            return $mark === null ? null : 'libc.so.6 · ' . $mark;
        }
    }

    return null;
}

/**
 * Records what is installed now, and what moved since last time.
 *
 * The timestamp is when the change was *noticed*, not when it happened - this
 * runs on a probe rather than on a schedule, and a weekly check turns a
 * Tuesday upgrade into a Sunday event. The page says so; recording it as
 * though it were the moment of the change would be a quiet lie.
 *
 * @return array{changes: list<array>, checked: int}
 */
function recordSoftware(PDO $pdo, array $items, string $side = 'account'): array
{
    $before = [];
    try {
        $rows = $pdo->prepare('SELECT name, value FROM software WHERE side = ?');
        $rows->execute([$side]);
        foreach ($rows->fetchAll() as $row) {
            $before[(string)$row['name']] = (string)$row['value'];
        }
    } catch (Throwable) {
        return ['changes' => [], 'checked' => 0];
    }

    $hadPrevious = $before !== [];
    $at = time();
    $changes = [];

    $save = $pdo->prepare(
        'INSERT INTO software (side, name, label, value, note, first_seen, last_seen)
         VALUES (:side, :name, :label, :value, :note, :now, :now)
         ON CONFLICT(side, name) DO UPDATE SET
            value = :value, label = :label, note = :note, last_seen = :now,
            first_seen = CASE WHEN software.value = :value THEN software.first_seen ELSE :now END'
    );

    $log = $pdo->prepare(
        'INSERT INTO software_changes (at, side, name, label, old_value, new_value)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $checked = 0;

    foreach ($items as $item) {
        $value = $item['value'];
        if ($value === null) {
            /*
             * Not recorded at all when it cannot be read.
             *
             * Storing "unknown" would make the next successful read look like
             * an upgrade, and on a shared host half of these are unreadable
             * for perfectly ordinary reasons.
             */
            continue;
        }

        $checked++;
        $was = $before[$item['name']] ?? null;

        if ($hadPrevious && $was !== null && $was !== $value) {
            $changes[] = [
                'name' => $item['name'],
                'label' => $item['label'],
                'old_value' => $was,
                'new_value' => $value,
            ];
            $log->execute([$at, $side, $item['name'], $item['label'], $was, $value]);
        }

        $save->execute([
            ':side' => $side,
            ':name' => $item['name'],
            ':label' => $item['label'],
            ':value' => $value,
            ':note' => $item['note'],
            ':now' => $at,
        ]);
    }

    return ['changes' => $changes, 'checked' => $checked];
}

/**
 * Everything currently known, and everything that has moved in a window.
 *
 * The two together, because the useful page is "here is what is installed, and
 * here is when each of them last changed" - two queries away from each other
 * is two pages nobody cross-references.
 *
 * @return array<string, mixed>
 */
function softwareHistory(PDO $pdo, int $from, int $to, string $only = ''): array
{
    $now = [];
    $changes = [];

    try {
        foreach ($pdo->query('SELECT * FROM software ORDER BY side, name')->fetchAll() as $row) {
            $row['age'] = time() - (int)$row['first_seen'];
            $now[] = $row;
        }

        $sql = 'SELECT * FROM software_changes WHERE at BETWEEN ? AND ?';
        $bind = [$from, $to];
        if ($only !== '') {
            $sql .= ' AND name = ?';
            $bind[] = $only;
        }
        $sql .= ' ORDER BY at DESC LIMIT 500';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);
        $changes = $stmt->fetchAll();
    } catch (Throwable) {
        return ['now' => [], 'changes' => [], 'names' => []];
    }

    /*
     * The names offered as a filter come from what has actually changed.
     *
     * A filter listing forty things that have never moved is forty ways to
     * select an empty chart.
     */
    $names = [];
    try {
        foreach ($pdo->query(
            'SELECT name, label, COUNT(*) AS times, MAX(at) AS last
               FROM software_changes GROUP BY name ORDER BY label'
        )->fetchAll() as $row) {
            $names[] = $row;
        }
    } catch (Throwable) {
        $names = [];
    }

    return ['now' => $now, 'changes' => $changes, 'names' => $names];
}
