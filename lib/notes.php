<?php
declare(strict_types=1);

/*
 * Writing down what you were thinking at the time.
 *
 * Every chart here answers "what happened". None of them answer "why", and the
 * why is almost never in the data - it is that somebody upgraded PHP on the
 * Tuesday, or that the backup window moved, or that a customer was importing
 * ten years of records. Six months later the chart still shows the step and
 * nobody remembers the reason.
 *
 * So: a note, attached to a moment, optionally pinned to the page or the chart
 * it is about, and drawn on the charts as a mark. Four scopes, because the
 * useful ones differ:
 *
 *   page    "this is about the memory page" - the common case
 *   chart   one specific chart, when a page has several and only one bent
 *   site    something that affected everything, like a kernel upgrade
 *   plain   a note with no marks at all, for a reminder or a decision
 *
 * A note can also carry a snapshot: the data behind a page, or every page, or
 * a copy of both databases. That is for the case where something is wrong now
 * and will have been compacted away by the time anybody looks - a monitor that
 * rolls up its own evidence is not much use during an incident.
 *
 * And a note can be a message to whoever else opens this dashboard, which is
 * the difference between "I know about the disk" and three people finding out
 * separately at two in the morning.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/access.php';

/** How much text one note holds. Generous; this is prose, not a log line. */
const NOTE_MAX_BODY = 8000;

/** How many notes are kept. A limit, not a target. */
const NOTE_MAX = 2000;

/**
 * The largest snapshot this will write.
 *
 * A whole-site JSON snapshot on a busy install is a few megabytes; a database
 * copy can be hundreds. The cap is on what goes into the database - the
 * database copies go to disk beside it and are counted separately.
 */
const NOTE_SNAPSHOT_MAX = 8 * 1024 * 1024;

/** The scopes a note can have, and what each one means on a chart. */
function noteScopes(): array
{
    return [
        'plain' => [
            'label' => 'Just a note',
            'note' => 'Kept and listed, but never drawn on a chart. For a reminder, a '
                . 'decision, or something that has no particular moment.',
        ],
        'page' => [
            'label' => 'Mark it on this page',
            'note' => 'Drawn on every chart on the page you were looking at. The usual '
                . 'choice: "we moved the backup window" belongs on the disk charts and '
                . 'nowhere else.',
        ],
        'chart' => [
            'label' => 'Mark it on specific charts',
            'note' => 'Drawn only on the charts you tick below. Worth it when a page has '
                . 'several and only one or two of them bent.',
        ],
        'site' => [
            'label' => 'Mark it everywhere',
            'note' => 'Drawn on every chart in this dashboard. For something that affected '
                . 'the whole machine - a kernel upgrade, a move to different hardware.',
        ],
    ];
}

/** What a snapshot can capture. */
function snapshotKinds(): array
{
    return [
        'none' => [
            'label' => 'Nothing',
            'note' => 'Just the note.',
        ],
        'page' => [
            'label' => 'The data behind this page',
            'note' => 'What this page fetched to draw itself, exactly as it arrived. Small, '
                . 'and enough to reconstruct what you were looking at.',
        ],
        'site' => [
            'label' => 'The data behind every page',
            'note' => 'Every read-only view fetched in one go. Slower and larger, and the '
                . 'right choice when you do not yet know which page held the answer.',
        ],
        'databases' => [
            'label' => 'A copy of both databases',
            'note' => 'A consistent copy of the account database, and the host one where it '
                . 'can be read, written beside them on disk. For a forensic look later, '
                . 'after retention has rolled up the readings this is about.',
        ],
    ];
}

/**
 * Saves a note.
 *
 * `at` is when the note is *about*, which is not always now - somebody writing
 * up an incident the next morning wants the mark where the incident was.
 *
 * @return array{id: int, at: int}
 */
function noteSave(PDO $pdo, array $config, array $input, string $who): array
{
    $body = trim((string)($input['body'] ?? ''));
    if ($body === '') {
        throw new ActionRefused('a note needs something in it');
    }
    if (mb_strlen($body) > NOTE_MAX_BODY) {
        throw new ActionRefused('that note is longer than this will hold');
    }

    $scope = (string)($input['scope'] ?? 'page');
    if (!isset(noteScopes()[$scope])) {
        throw new ActionRefused('that is not one of the scopes on offer');
    }

    $view = trim((string)($input['view'] ?? ''));
    $chart = trim((string)($input['chart'] ?? ''));

    if ($scope === 'chart' && $chart === '') {
        throw new ActionRefused('tick at least one chart this is about');
    }
    if (($scope === 'page' || $scope === 'chart') && $view === '') {
        throw new ActionRefused('that scope needs to know which page this is about');
    }

    /*
     * A moment, bounded either side.
     *
     * Far-future dates are almost always a timezone mistake or a typo, and a
     * mark ten years out is invisible on every chart rather than obviously
     * wrong - so it is refused where it can still be seen.
     */
    $at = (int)($input['at'] ?? time());
    if ($at < 946684800 || $at > time() + SPAN_YEAR) {
        throw new ActionRefused('that is not a date this can put on a chart');
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM notes')->fetchColumn();
    if ($count >= NOTE_MAX) {
        throw new ActionRefused('that is as many notes as this will hold');
    }

    $audience = array_values(array_unique(array_filter(
        is_array($input['audience'] ?? null) ? $input['audience'] : [],
        fn(mixed $group): bool => is_string($group) && isset(accessGroups($config)[$group])
    )));
    if (is_array($input['audience'] ?? null) && count($audience) !== count($input['audience'])) {
        throw new ActionRefused('one of the selected note groups does not exist');
    }

    $pdo->prepare(
        'INSERT INTO notes (at, written_at, body, scope, view, chart, link, broadcast, who, audience)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $at,
        time(),
        $body,
        $scope,
        $view === '' ? null : $view,
        $chart === '' ? null : $chart,
        trim((string)($input['link'] ?? '')) ?: null,
        empty($input['broadcast']) ? 0 : 1,
        $who,
        json_encode($audience, JSON_UNESCAPED_SLASHES),
    ]);

    return ['id' => (int)$pdo->lastInsertId(), 'at' => $at];
}

/**
 * Notes to draw on the charts of one view.
 *
 * Returned as `{at, body, chart}` so the browser can hand the ones for a
 * particular chart to that chart and the page-wide ones to all of them.
 *
 * @return list<array<string, mixed>>
 */
function noteVisibleTo(array $row, array $config): bool
{
    $audience = json_decode((string)($row['audience'] ?? '[]'), true);
    if (!is_array($audience) || $audience === []) {
        return true;
    }
    return array_intersect($audience, accessIdentity($config)['groups']) !== [];
}

function noteMarks(PDO $pdo, array $config, string $view, int $from, int $to): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT id, at, body, scope, chart, audience FROM notes
              WHERE at >= ? AND at <= ?
                AND (scope = 'site' OR ((scope = 'page' OR scope = 'chart') AND view = ?))
              ORDER BY at"
        );
        $stmt->execute([$from, $to, $view]);
        $rows = $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }

    return array_map(fn(array $row): array => [
        'id' => (int)$row['id'],
        'at' => (int)$row['at'],
        // Enough to read in a tooltip; the whole thing is on the Notes page.
        'body' => mb_substr((string)$row['body'], 0, 200),
        'scope' => (string)$row['scope'],
        'chart' => $row['chart'],
    ], array_values(array_filter($rows, fn(array $row): bool => noteVisibleTo($row, $config))));
}

/**
 * Notes meant for whoever else is looking, that nobody has cleared.
 *
 * Shown in the alert banner. Separate from alerts proper because an alert is
 * something the machine noticed and this is something a person said, and
 * mixing them would make it impossible to tell which is which.
 *
 * @return list<array<string, mixed>>
 */
function noteBroadcasts(PDO $pdo, array $config): array
{
    try {
        $rows = $pdo->query(
            'SELECT id, at, body, who, link, audience FROM notes
              WHERE broadcast = 1 AND cleared_at IS NULL
              ORDER BY at DESC LIMIT 10'
        )->fetchAll();
        return array_values(array_filter($rows, fn(array $row): bool => noteVisibleTo($row, $config)));
    } catch (Throwable) {
        return [];
    }
}

/**
 * The whole list, newest first, with what each one carries.
 *
 * @return array<string, mixed>
 */
function noteList(PDO $pdo, array $config, int $limit = 300): array
{
    try {
        $rows = $pdo->query(
            'SELECT n.*,
                    (SELECT COUNT(*) FROM note_snapshots s WHERE s.note_id = n.id) AS snapshots,
                    (SELECT COUNT(*) FROM note_snapshots s
                      WHERE s.note_id = n.id AND s.kind = \'image\') AS images,
                    (SELECT SUM(bytes) FROM note_snapshots s WHERE s.note_id = n.id) AS snapshot_bytes
               FROM notes n ORDER BY n.at DESC LIMIT ' . max(1, $limit)
        )->fetchAll();
    } catch (Throwable) {
        return ['rows' => [], 'scopes' => noteScopes(), 'kinds' => snapshotKinds()];
    }

    $rows = array_values(array_filter($rows, fn(array $row): bool => noteVisibleTo($row, $config)));
    foreach ($rows as &$row) {
        $row['broadcast'] = (bool)$row['broadcast'];
        $row['snapshots'] = (int)$row['snapshots'];
        $row['snapshot_bytes'] = (int)($row['snapshot_bytes'] ?? 0);
    }
    unset($row);

    return [
        'rows' => $rows,
        'scopes' => noteScopes(),
        'kinds' => snapshotKinds(),
        'files' => databaseSnapshots($config),
    ];
}

/**
 * Everything a note captured, without the payloads.
 *
 * The bodies can be megabytes and are only wanted one at a time, so the list
 * carries what they are and how large rather than the contents.
 *
 * @return list<array<string, mixed>>
 */
/** The note owning a snapshot, but only when the current identity may see it. */
function noteForSnapshot(PDO $pdo, array $config, int $snapshotId): ?array
{
    $visible = $pdo->prepare(
        'SELECT n.* FROM note_snapshots s JOIN notes n ON n.id = s.note_id WHERE s.id = ?'
    );
    $visible->execute([$snapshotId]);
    $note = $visible->fetch();
    return is_array($note) && noteVisibleTo($note, $config) ? $note : null;
}

function noteSnapshotList(PDO $pdo, array $config, int $noteId): array
{
    $visible = $pdo->prepare('SELECT audience FROM notes WHERE id = ?');
    $visible->execute([$noteId]);
    $note = $visible->fetch();
    if (!is_array($note) || !noteVisibleTo($note, $config)) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT id, kind, label, bytes, at FROM note_snapshots WHERE note_id = ? ORDER BY id'
    );
    $stmt->execute([$noteId]);
    return $stmt->fetchAll();
}

/**
 * Stores one captured payload against a note.
 *
 * Refused rather than truncated when it is too large: half a JSON document is
 * not a smaller snapshot, it is a broken one, and finding that out six months
 * later is the worst possible time.
 */
function noteSnapshotSave(
    PDO $pdo,
    int $noteId,
    string $kind,
    string $label,
    string $payload
): int {
    if (strlen($payload) > NOTE_SNAPSHOT_MAX) {
        throw new ActionRefused(sprintf(
            'that snapshot is %s, and the limit is %s. Half a snapshot is not a smaller '
            . 'one, so it is refused rather than cut short.',
            humanBytes(strlen($payload)),
            humanBytes(NOTE_SNAPSHOT_MAX)
        ));
    }

    $pdo->prepare(
        'INSERT INTO note_snapshots (note_id, kind, label, bytes, body, at)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$noteId, $kind, $label, strlen($payload), $payload, time()]);

    return (int)$pdo->lastInsertId();
}

/** Most pictures one note will hold. Enough for a page of charts, not a gallery. */
const NOTE_IMAGE_MAX = 12;

/** Largest single picture. A chart PNG is tens of kilobytes; a page shot, a few hundred. */
const NOTE_IMAGE_BYTES = 4 * 1024 * 1024;

/**
 * Stores pictures of charts alongside a note.
 *
 * The reason this exists rather than "the chart can be redrawn later" is
 * retention. The readings behind a chart get rolled up and thinned as they age,
 * which is the whole point of retention and also means the chart somebody
 * thought was worth writing a note about cannot be drawn again at the
 * resolution they saw. The picture can.
 *
 * Decoded to real bytes here rather than kept as a data URL: base64 in the
 * database is a third larger for no benefit, and the browser can cache an
 * <img src> pointing at a file it already knows how to fetch.
 *
 * A picture that will not decode is skipped and reported, never stored. A blob
 * served with an image content type has to actually be an image.
 *
 * @return array{kept: list<string>, problems: list<string>}
 */
function noteImagesSave(PDO $pdo, int $noteId, string $json): array
{
    $kept = [];
    $problems = [];

    $items = json_decode($json, true);
    if (!is_array($items)) {
        return ['kept' => [], 'problems' => ['the pictures did not arrive in one piece']];
    }

    if (count($items) > NOTE_IMAGE_MAX) {
        $problems[] = sprintf(
            'only the first %d pictures were kept, of %d sent',
            NOTE_IMAGE_MAX,
            count($items)
        );
        $items = array_slice($items, 0, NOTE_IMAGE_MAX);
    }

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $name = trim((string)($item['name'] ?? 'a chart'));
        $bytes = decodeDataImage((string)($item['data'] ?? ''));

        if ($bytes === null) {
            $problems[] = sprintf('%s was not a picture this could read', $name);
            continue;
        }
        if (strlen($bytes) > NOTE_IMAGE_BYTES) {
            $problems[] = sprintf(
                '%s is %s, and the limit for one picture is %s',
                $name,
                humanBytes(strlen($bytes)),
                humanBytes(NOTE_IMAGE_BYTES)
            );
            continue;
        }

        try {
            noteSnapshotSave($pdo, $noteId, 'image', $name, $bytes);
            $kept[] = $name;
        } catch (ActionRefused $e) {
            $problems[] = $e->getMessage();
        }
    }

    return ['kept' => $kept, 'problems' => $problems];
}

/**
 * The bytes behind a `data:image/png;base64,...` URL, or null.
 *
 * PNG only, and checked twice: the URL has to claim PNG, and the decoded bytes
 * have to start with the PNG signature. The claim on its own is worth nothing -
 * it is a string the browser wrote and anything could have written - and this
 * blob is later served with an image content type.
 */
function decodeDataImage(string $url): ?string
{
    if (!str_starts_with($url, 'data:image/png;base64,')) {
        return null;
    }

    $bytes = base64_decode(substr($url, strlen('data:image/png;base64,')), true);
    if ($bytes === false || $bytes === '') {
        return null;
    }

    return str_starts_with($bytes, "\x89PNG\r\n\x1a\n") ? $bytes : null;
}

/**
 * Writes a consistent copy of a database beside it.
 *
 * `VACUUM INTO` rather than a file copy. Under WAL, the main file on its own is
 * not the current state - the recent writes are in the -wal - so copying it
 * gives a database missing whatever happened most recently, which on a snapshot
 * taken during an incident is exactly the part that matters. VACUUM INTO
 * produces a complete, already-compacted database from a consistent read.
 *
 * @return array{path: string, bytes: int}
 */
function snapshotDatabase(PDO $pdo, string $into): array
{
    $directory = dirname($into);
    if (!is_dir($directory) && !@mkdir($directory, 0700, true)) {
        throw new ActionRefused('could not make a place to put the copy');
    }

    if (file_exists($into)) {
        throw new ActionRefused('there is already a copy by that name');
    }

    $room = @disk_free_space($directory);
    $size = (int)$pdo->query('PRAGMA page_count')->fetchColumn()
        * (int)$pdo->query('PRAGMA page_size')->fetchColumn();

    if ($room !== false && $room < $size * 1.2) {
        throw new ActionRefused(sprintf(
            'a copy needs about %s and there is %s free',
            humanBytes($size),
            humanBytes((int)$room)
        ));
    }

    try {
        // The path is quoted as a string literal, not interpolated bare: it
        // contains an install-specific directory and this is still SQL.
        $pdo->exec('VACUUM INTO ' . $pdo->quote($into));
    } catch (Throwable $e) {
        throw new ActionRefused('the copy failed: ' . $e->getMessage());
    }

    @chmod($into, 0600);
    clearstatcache(true, $into);

    return ['path' => $into, 'bytes' => is_file($into) ? (int)filesize($into) : 0];
}

/** Where database snapshots are kept. Beside the databases, which .htaccess denies. */
function snapshotDirectory(array $config): string
{
    return dirname((string)$config['database']) . '/snapshots';
}

/**
 * Every database copy on disk, with its age.
 *
 * Listed because they are the one thing here that takes real space and is not
 * covered by any retention rule - a forensic copy is deliberately outside all
 * of that, which means somebody has to be shown they exist.
 *
 * @return list<array<string, mixed>>
 */
function databaseSnapshots(array $config): array
{
    $directory = snapshotDirectory($config);
    $out = [];

    foreach (@glob($directory . '/*.db') ?: [] as $file) {
        $out[] = [
            'name' => basename($file),
            'bytes' => (int)filesize($file),
            'at' => (int)filemtime($file),
        ];
    }

    usort($out, fn(array $a, array $b): int => $b['at'] <=> $a['at']);
    return $out;
}

/** Removes one database copy. */
function snapshotDelete(array $config, string $name): bool
{
    /*
     * The name, and only a name.
     *
     * basename() alone is not enough of a guarantee to build a delete path
     * from, so the shape is checked as well: this is the one function here
     * that takes a filename from a request and unlinks it.
     */
    if (!preg_match('/^[A-Za-z0-9._-]{1,120}\.db$/', $name)) {
        return false;
    }

    $path = snapshotDirectory($config) . '/' . basename($name);
    return is_file($path) && @unlink($path);
}

/** Marks a broadcast as read, so it stops appearing for everyone. */
function noteClear(PDO $pdo, int $id, string $who): bool
{
    $stmt = $pdo->prepare(
        'UPDATE notes SET cleared_at = ?, cleared_by = ? WHERE id = ? AND cleared_at IS NULL'
    );
    $stmt->execute([time(), $who, $id]);
    return $stmt->rowCount() > 0;
}

/** Deletes a note and everything it captured. */
function noteDelete(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
