<?php
declare(strict_types=1);

/*
 * The assistant's own database, deliberately apart from the other two.
 *
 * Three reasons it is its own file rather than more tables in the account
 * database.
 *
 * It is a transcript of conversations with an outside service, including
 * whatever the model was told about this machine. That is a different kind of
 * thing from a disk reading, it has different retention needs, and somebody who
 * wants it gone should be able to delete one file rather than pick tables apart.
 *
 * It is written on a completely different rhythm - a burst of rows every few
 * seconds while somebody is chatting, nothing for days afterwards - and mixing
 * that with the collector's steady append pattern makes both slower.
 *
 * And the polling that drives the whole conversation reads this file several
 * times a second. Keeping that traffic off the database the collectors are
 * writing to is worth a file on its own.
 *
 * Nothing here is required for the rest of the dashboard to work. If this file
 * cannot be created, the assistant is unavailable and everything else carries
 * on, which is the correct failure for a feature that is an extra.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/storekeep.php';
require_once __DIR__ . '/db.php';

/** Where it lives: beside the other two, which .htaccess already denies. */
function aiDatabasePath(array $config): string
{
    $configured = (string) ($config['ai']['database'] ?? '');
    if ($configured !== '') {
        return $configured;
    }
    return dirname((string) $config['database']) . '/ai.db';
}

/**
 * Opens it, creating and migrating if needed.
 *
 * Returns null rather than throwing when it cannot be opened: the assistant is
 * an extra, and a dashboard that refused to load because a chat log could not
 * be written would be a poor trade.
 */
function aiDb(array $config, bool $forWriting = true): ?PDO
{
    static $handle = null;
    if ($handle !== null) {
        return $handle;
    }

    $path = aiDatabasePath($config);

    if (!$forWriting && !is_file($path)) {
        return null;
    }

    try {
        $directory = dirname($path);
        if (!is_dir($directory) && !@mkdir($directory, 0700, true)) {
            return null;
        }

        $fresh = !is_file($path);
        $previousUmask = umask(0077);
        try {
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA foreign_keys = ON');
            /*
             * A short busy timeout, because this is polled hard.
             *
             * Several requests a second read this file while a conversation is
             * running. Without a timeout a reader that meets a writer fails
             * immediately, and the chat window shows an error for something that
             * would have succeeded a millisecond later.
             */
            $pdo->exec('PRAGMA busy_timeout = 3000');

            aiMigrate($pdo, $fresh);
            aiPrivateSqliteFiles($path);

            $handle = $pdo;
            return $handle;
        } finally {
            umask($previousUmask);
        }
    } catch (Throwable) {
        return null;
    }
}

/** Assistant transcripts and journals remain private to the web account. */
function aiPrivateSqliteFiles(string $path): void
{
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
        if (is_file($file) && !is_link($file)) {
            @chmod($file, 0600);
        }
    }
}

function aiMigrate(PDO $pdo, bool $fresh = false): void
{
    $at = (int) $pdo->query('PRAGMA user_version')->fetchColumn();

    $steps = aiMigrations();
    ksort($steps);
    $pending = array_filter(
        array_keys($steps),
        fn(int $version): bool => $version > $at,
    );
    if (!$fresh && $pending !== []) {
        migrationBackup($pdo, $at, min($pending));
    }

    foreach ($steps as $version => $statements) {
        if ($version <= $at) {
            continue;
        }
        $pdo->beginTransaction();
        try {
            foreach ($statements as $sql) {
                $pdo->exec($sql);
            }
            $pdo->exec('PRAGMA user_version = ' . (int) $version);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

function aiMigrations(): array
{
    return [
        1 => [
            /*
             * One conversation.
             *
             * A session outlives the chat window: closing the panel is not the
             * same as ending the conversation, because somebody following a
             * suggestion to look at another page has not finished talking - and
             * making them start again would lose everything the model had
             * worked out.
             *
             * `spent_cents` is a running total in hundredths of a cent, kept as
             * an integer. Money in floating point accumulates error, and a
             * budget that is enforced against a number drifting by a fraction
             * each turn is a budget that stops working.
             */
            "CREATE TABLE ai_sessions (
                id            INTEGER PRIMARY KEY,
                started_at    INTEGER NOT NULL,
                last_at       INTEGER NOT NULL,
                ended_at      INTEGER,
                title         TEXT,
                state         TEXT NOT NULL,
                model         TEXT NOT NULL,
                stop_reason   TEXT,
                started_by    TEXT,
                opened_from   TEXT,
                turns         INTEGER NOT NULL DEFAULT 0,
                tokens_in     INTEGER NOT NULL DEFAULT 0,
                tokens_out    INTEGER NOT NULL DEFAULT 0,
                tokens_cached INTEGER NOT NULL DEFAULT 0,
                spent_millicents INTEGER NOT NULL DEFAULT 0,
                max_seconds   INTEGER NOT NULL DEFAULT 0,
                max_millicents INTEGER NOT NULL DEFAULT 0,
                max_turns     INTEGER NOT NULL DEFAULT 0
            )",
            'CREATE INDEX ai_sessions_state ON ai_sessions (state, last_at)',

            /*
             * Every message, in the order it happened.
             *
             * Including the tool results, which are messages as far as the model
             * is concerned - and are the part somebody auditing this will care
             * about most, because they are what left the machine.
             */
            "CREATE TABLE ai_messages (
                id         INTEGER PRIMARY KEY,
                session_id INTEGER NOT NULL REFERENCES ai_sessions(id) ON DELETE CASCADE,
                at         INTEGER NOT NULL,
                role       TEXT NOT NULL,
                content    TEXT,
                tool_name  TEXT,
                tool_id    TEXT,
                tool_args  TEXT,
                visible    INTEGER NOT NULL DEFAULT 1
            )",
            'CREATE INDEX ai_messages_session ON ai_messages (session_id, id)',

            /*
             * One request to the model, with what it cost.
             *
             * The whole request and response are kept. They are the only record
             * of what was sent to somebody else's service about this machine,
             * and a summary would be exactly the wrong thing to have when that
             * question is being asked.
             */
            "CREATE TABLE ai_calls (
                id         INTEGER PRIMARY KEY,
                session_id INTEGER NOT NULL REFERENCES ai_sessions(id) ON DELETE CASCADE,
                at         INTEGER NOT NULL,
                ms         INTEGER NOT NULL DEFAULT 0,
                model      TEXT NOT NULL,
                tokens_in  INTEGER NOT NULL DEFAULT 0,
                tokens_out INTEGER NOT NULL DEFAULT 0,
                tokens_cached INTEGER NOT NULL DEFAULT 0,
                millicents INTEGER NOT NULL DEFAULT 0,
                request    TEXT,
                response   TEXT,
                error      TEXT,
                http_status INTEGER
            )",
            'CREATE INDEX ai_calls_session ON ai_calls (session_id, id)',

            /*
             * Every tool the model asked for, and what happened.
             *
             * `state` walks: proposed -> (allowed | denied) -> done | failed.
             * A tool needing permission stops at `proposed` until a person says
             * otherwise, and a tool the browser has to run stops at `allowed`
             * until the browser comes back with an answer.
             */
            "CREATE TABLE ai_tool_runs (
                id         INTEGER PRIMARY KEY,
                session_id INTEGER NOT NULL REFERENCES ai_sessions(id) ON DELETE CASCADE,
                call_id    INTEGER,
                at         INTEGER NOT NULL,
                settled_at INTEGER,
                tool       TEXT NOT NULL,
                tool_id    TEXT NOT NULL,
                arguments  TEXT NOT NULL,
                runs_where TEXT NOT NULL,
                state      TEXT NOT NULL,
                needs_permission INTEGER NOT NULL DEFAULT 0,
                decided_by TEXT,
                result     TEXT,
                error      TEXT,
                ms         INTEGER NOT NULL DEFAULT 0
            )",
            'CREATE INDEX ai_tool_runs_session ON ai_tool_runs (session_id, id)',
            'CREATE INDEX ai_tool_runs_state ON ai_tool_runs (state, session_id)',
        ],

        2 => [
            /*
             * Pictures made, and what they cost in budget.
             *
             * The hourly limit is counted from this table rather than held in
             * memory, so restarting PHP does not reset it - which on a shared
             * host happens constantly.
             *
             * The URL rather than the bytes: the image lives on the provider's
             * CDN and the browser fetches it directly, so this never holds a
             * megabyte of JPEG to pass along. The consequence is that these
             * expire when the provider expires them, which is fine for a
             * throwaway aside and is why the prompt is kept alongside.
             */
            "CREATE TABLE ai_images (
                id         INTEGER PRIMARY KEY,
                session_id INTEGER,
                at         INTEGER NOT NULL,
                kind       TEXT NOT NULL,
                prompt     TEXT NOT NULL,
                style      TEXT,
                url        TEXT,
                error      TEXT
            )",
            'CREATE INDEX ai_images_at ON ai_images (at)',
            'CREATE INDEX ai_images_kind ON ai_images (kind, id)',
        ],

        3 => [
            /*
             * Which page the operator is looking at.
             *
             * Kept on the session and refreshed as they move, so every request
             * can say it. Without it the model is advising somebody whose
             * screen it cannot see, and "check the Disk I/O page" is wasted
             * breath when they are already on it.
             */
            'ALTER TABLE ai_sessions ADD COLUMN viewing TEXT',

            /*
             * How hard to think, per conversation.
             *
             * Settings hold the ceiling; this holds what was chosen for this
             * one. Most questions do not need the expensive setting and the
             * person asking usually knows which theirs is.
             */
            'ALTER TABLE ai_sessions ADD COLUMN effort TEXT',
        ],

        4 => [
            /*
             * What it remembers between conversations.
             *
             * Deliberately its own table rather than a long-lived session: a
             * memory outlives the conversation that produced it, has to be
             * reviewable on its own, and has to be deletable without throwing
             * away the transcript it came from.
             *
             * `source_session` is kept so every memory can be traced back to
             * the conversation that made it. A remembered claim whose origin
             * cannot be found is a rumour.
             */
            "CREATE TABLE ai_memories (
                id             INTEGER PRIMARY KEY,
                at             INTEGER NOT NULL,
                updated_at     INTEGER NOT NULL,
                source_session INTEGER,
                kind           TEXT NOT NULL,
                subject        TEXT NOT NULL,
                body           TEXT NOT NULL,
                pinned         INTEGER NOT NULL DEFAULT 0,
                used_at        INTEGER,
                used           INTEGER NOT NULL DEFAULT 0,
                written_by     TEXT NOT NULL DEFAULT 'assistant'
            )",
            'CREATE UNIQUE INDEX ai_memories_subject ON ai_memories (subject)',
            'CREATE INDEX ai_memories_kind ON ai_memories (kind, updated_at)',

            /*
             * Where a conversation was summarised.
             *
             * Compaction replaces a run of old messages with one summary. The
             * originals are not deleted - they are the record of what was
             * actually sent - so the transcript builder needs to know which
             * ones to skip and what to put in their place.
             */
            'ALTER TABLE ai_sessions ADD COLUMN compacted_to INTEGER NOT NULL DEFAULT 0',
        ],

        5 => [
            /*
             * The generated images themselves, not just a link to them.
             *
             * A provider's URL expires. Keeping only the link means a picture
             * that was paid for is gone in a week and the record of what was
             * made is a 404. The bytes are small next to the transcripts they
             * sit beside, and only images this tool generated are stored -
             * animations fetched from a search are somebody else's file and
             * stay theirs.
             */
            "CREATE TABLE ai_assets (
                id       INTEGER PRIMARY KEY,
                image_id INTEGER NOT NULL,
                at       INTEGER NOT NULL,
                mime     TEXT NOT NULL,
                width    INTEGER NOT NULL DEFAULT 0,
                height   INTEGER NOT NULL DEFAULT 0,
                bytes    INTEGER NOT NULL,
                body     BLOB NOT NULL
            )",
            'CREATE INDEX ai_assets_image ON ai_assets (image_id)',

            // What one image cost, so a session total can include it.
            'ALTER TABLE ai_images ADD COLUMN millicents INTEGER NOT NULL DEFAULT 0',
        ],

        6 => [
            /*
             * Which tier a picture was generated at.
             *
             * The plan sells separate allowances for standard and for the
             * expensive tier, so counting them together runs one allowance out
             * on the other's usage. Everything written before this column
             * existed was standard, which was the only option at the time -
             * hence the default rather than a null.
             *
             * Its own migration, not an edit to the one above. A migration that
             * has already run never runs again, so adding a line to it changes
             * nothing on any install that has already seen it - which is
             * exactly what happened here, and the symptom was a fatal on the
             * first insert.
             */
            "ALTER TABLE ai_images ADD COLUMN quality TEXT NOT NULL DEFAULT 'standard'",
        ],

        7 => [
            /*
             * Who named the conversation.
             *
             * The assistant can title a session so that a list of past ones
             * reads as a list of subjects rather than of timestamps. It must
             * not overwrite a title a person chose: what they called it is what
             * they will look for, and a model deciding it knows better is a
             * model quietly losing somebody's bookmark.
             *
             * A column rather than a convention, because "is this title mine"
             * cannot be answered by looking at the text.
             */
            'ALTER TABLE ai_sessions ADD COLUMN title_by TEXT',
        ],

        8 => [
            /*
             * Conversations somebody wants to keep.
             *
             * Retention deletes by age, which is right for the hundred sessions
             * nobody will read again and wrong for the one that diagnosed the
             * outage. Without a way to say so, the only way to keep that one is
             * to turn retention off for all of them.
             */
            'ALTER TABLE ai_sessions ADD COLUMN pinned INTEGER NOT NULL DEFAULT 0',
            /*
             * Which limit stopped it, so the page can offer to raise that one.
             *
             * Named rather than inferred from the message: the message is prose
             * written for a person, and reading a decision back out of prose is
             * how a feature breaks when somebody improves the wording.
             */
            'ALTER TABLE ai_sessions ADD COLUMN limit_hit TEXT',
        ],

        9 => [
            /* Prices are a property of the call, not of the settings page as it
             * happens to look later. This keeps past billing explainable after
             * a provider changes its rate or an operator corrects one. */
            'ALTER TABLE ai_calls ADD COLUMN price_in REAL NOT NULL DEFAULT 0',
            'ALTER TABLE ai_calls ADD COLUMN price_out REAL NOT NULL DEFAULT 0',
            'ALTER TABLE ai_calls ADD COLUMN price_cached REAL NOT NULL DEFAULT 0',
        ],
    ];
}

/**
 * How large the assistant's database has grown.
 *
 * Reported on the settings page, because transcripts of conversations that
 * include screenshots are the one thing here that can grow quickly and quietly.
 */
function aiDatabaseFacts(array $config): array
{
    $path = aiDatabasePath($config);

    return [
        'path' => $path,
        'exists' => is_file($path),
        'bytes' => is_file($path) ? (int) filesize($path) : 0,
        'wal_bytes' => is_file($path . '-wal')
            ? (int) filesize($path . '-wal')
            : 0,
        'writable' => is_writable(is_file($path) ? $path : dirname($path)),
    ];
}

/**
 * What the assistant's database is holding, and how old it is.
 *
 * Its own page rather than a line on the settings one, because this is the
 * database that grows without anybody deciding it should. The account database
 * grows at a rate somebody chose - a reading every five minutes - and retention
 * thins it. This one grows by the conversation: every message, every tool
 * result that came back, every picture. One long session with a few screenshots
 * in it can be larger than a week of readings.
 *
 * @return array<string, mixed>
 */
function aiStoreReport(array $config): array
{
    $facts = aiDatabaseFacts($config);
    $out = [
        'file' => $facts,
        'tables' => [],
        'holds' => [],
        'why' => null,
    ];

    if (!$facts['exists']) {
        $out['why'] =
            'Nothing has been asked yet, so there is no database to look at.';
        return $out;
    }

    $pdo = aiDb($config, false);
    if ($pdo === null) {
        $out['why'] = 'The database is there but could not be opened.';
        return $out;
    }

    /*
     * The fuller facts, with `exists` carried over.
     *
     * databaseFacts does not report it - it is only ever called on a database
     * that is open, so the question does not arise there. It arises here,
     * because the page decides between "there is nothing to show" and the
     * whole report on that one key, and losing it made a database with a
     * conversation in it render as "nothing has been asked yet".
     */
    $out['file'] = databaseFacts($pdo, $config, $facts['path']) + [
        'exists' => true,
    ];
    $store = storeTables($pdo);
    $out['tables'] = $store['tables'];
    $out['measured'] = $store['measured'];
    $out['table_why'] = $store['why'];

    /*
     * What each kind of row is, in the words somebody would use to decide
     * whether to delete it - not the table names.
     */
    foreach (aiStoreKinds() as $key => $kind) {
        $row = aiStoreCount($pdo, $kind['table'], $kind['when']);
        $out['holds'][] = [
            'key' => $key,
            'label' => $kind['label'],
            'note' => $kind['note'],
            'rows' => $row['rows'],
            'oldest' => $row['oldest'],
            'newest' => $row['newest'],
        ];
    }

    return $out;
}

/** The four things this database holds, and what deleting each one costs. */
function aiStoreKinds(): array
{
    return [
        'sessions' => [
            'table' => 'ai_sessions',
            'when' => 'started_at',
            'label' => 'Conversations',
            'note' =>
                'One per conversation, with what it cost. Deleting one takes its ' .
                'messages and tool runs with it.',
        ],
        'messages' => [
            'table' => 'ai_messages',
            'when' => 'at',
            'label' => 'Messages',
            'note' =>
                'Every turn in both directions, including the tool results that ' .
                'went back to the model. This is the record of what left the machine.',
        ],
        'calls' => [
            'table' => 'ai_calls',
            'when' => 'at',
            'label' => 'Requests to the provider',
            'note' =>
                'One per round trip, with the token counts each one was billed ' .
                'for. What the cost figures are added up from.',
        ],
        'tool_runs' => [
            'table' => 'ai_tool_runs',
            'when' => 'at',
            'label' => 'Tool runs',
            'note' =>
                'Every tool the model asked for, what it was given, and what came ' .
                'back. Kept so an answer can be checked against what it was based on.',
        ],
        'images' => [
            'table' => 'ai_images',
            'when' => 'at',
            'label' => 'Pictures',
            'note' =>
                'What was asked for and where the result was put. The pictures ' .
                'themselves live at the provider, not here.',
        ],
    ];
}

/** Rows, oldest and newest, for one table. Missing tables report as empty. */
function aiStoreCount(PDO $pdo, string $table, string $column): array
{
    /*
     * The table and column are named by the catalogue above, never by anything
     * that arrived in a request - they cannot be bound as parameters, so the
     * only safe source for them is code.
     */
    try {
        $row = $pdo
            ->query(
                sprintf(
                    'SELECT COUNT(*) AS n, MIN(%s) AS oldest, MAX(%s) AS newest FROM %s',
                    $column,
                    $column,
                    $table,
                ),
            )
            ->fetch();
    } catch (Throwable) {
        return ['rows' => 0, 'oldest' => null, 'newest' => null];
    }

    return [
        'rows' => (int) ($row['n'] ?? 0),
        'oldest' => $row['oldest'] === null ? null : (int) $row['oldest'],
        'newest' => $row['newest'] === null ? null : (int) $row['newest'],
    ];
}

/**
 * Removes conversations that finished before a cut-off.
 *
 * Whole conversations only. Deleting the messages out of a session while
 * leaving the session would produce a record that says a conversation cost
 * four cents and contains nothing, which is worse than either keeping it or
 * removing it - it looks like data loss, because it is.
 *
 * @return array{sessions: int, before: int}
 */
function aiForget(PDO $pdo, int $olderThanDays): array
{
    if ($olderThanDays < 0) {
        throw new ActionRefused('that is not a number of days');
    }

    $before = time() - $olderThanDays * 86400;

    /*
     * Pinned conversations are never swept.
     *
     * The whole point of pinning one is that it outlives the rule, so the rule
     * has to know. A pin that the sweep ignored would be worse than no pin at
     * all: somebody would rely on it and lose the thing anyway.
     */
    $stmt = $pdo->prepare(
        'SELECT id FROM ai_sessions WHERE last_at < ? AND COALESCE(pinned, 0) = 0',
    );
    $stmt->execute([$before]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($ids === []) {
        return ['sessions' => 0, 'before' => $before];
    }

    /*
     * The children are deleted explicitly rather than left to the foreign key.
     *
     * ON DELETE CASCADE only fires when `PRAGMA foreign_keys` is on, and that
     * pragma is per-connection and off by default in SQLite. Relying on it is
     * how orphaned rows accumulate in a database that looks like it cascades.
     */
    $marks = implode(',', array_fill(0, count($ids), '?'));

    foreach (
        ['ai_messages', 'ai_calls', 'ai_tool_runs', 'ai_images']
        as $table
    ) {
        try {
            $pdo->prepare(
                "DELETE FROM {$table} WHERE session_id IN ({$marks})",
            )->execute($ids);
        } catch (Throwable) {
            // A table that is not there yet has nothing to remove from it.
        }
    }

    $pdo->prepare("DELETE FROM ai_sessions WHERE id IN ({$marks})")->execute(
        $ids,
    );

    return ['sessions' => count($ids), 'before' => $before];
}
