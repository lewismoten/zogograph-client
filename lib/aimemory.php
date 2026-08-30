<?php
declare(strict_types=1);

/*
 * What the assistant is allowed to remember between conversations.
 *
 * A conversation ends and everything it worked out goes with it. That is fine
 * for "why is the disk full today" and useless for the things that are true
 * every time: which of the four PHP versions this account actually runs, that
 * the nightly backup is what makes I/O spike at 03:10, that the operator has
 * already been told about the .cpanel symlinks and does not want to hear it
 * again. Re-deriving those every session costs tokens and, worse, costs the
 * operator the same conversation twice.
 *
 * Four rules, and they are the whole design:
 *
 *   one subject, one memory   writing the same subject twice replaces it, so
 *                             the store cannot fill with sixteen slightly
 *                             different versions of one fact
 *   every memory is visible   there is a page listing all of them, and anything
 *                             on it can be edited or deleted by a person
 *   every memory has a source the session that wrote it is recorded, because a
 *                             remembered claim whose origin cannot be found is
 *                             a rumour
 *   nothing is remembered for it cannot remember a password, a key, or
 *   the operator             anything else it was shown in confidence; see
 *                             aiMemoryRefuses below
 *
 * The store is small on purpose. A model given three hundred remembered facts
 * spends its context on them instead of on the problem, and the useful ones are
 * drowned by the ones that were true in March.
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';

/** Most memories kept. Past this the least recently useful are dropped. */
const AI_MEMORY_MAX = 200;

/** Most put in front of the model at the start of a conversation. */
const AI_MEMORY_BRIEF = 40;

/** Longest one memory can be. A memory is a fact, not a transcript. */
const AI_MEMORY_BODY = 1200;

/**
 * The kinds, and what each is for.
 *
 * Named for what the memory does rather than where it came from, because the
 * question at recall time is always "is this the sort of thing that helps here".
 */
function aiMemoryKinds(): array
{
    return [
        'fact' => [
            'label' => 'About this machine',
            'note' =>
                'Something true of this install that is not obvious from the ' .
                'readings — which PHP the site actually runs, what the 03:10 I/O spike ' .
                'is, which mount is the real one.',
        ],
        'preference' => [
            'label' => 'How you want things',
            'note' =>
                'Something the operator has said about how they want to be helped. ' .
                'The one kind worth keeping even when it is not about the machine at all.',
        ],
        'finding' => [
            'label' => 'Something worked out',
            'note' =>
                'A diagnosis that held up, with what it turned on. Saves re-deriving ' .
                'the same conclusion from the same evidence next month.',
        ],
        'todo' => [
            'label' => 'Left unfinished',
            'note' =>
                'Something identified and not yet done. The kind of thing that is ' .
                'otherwise remembered only by whoever happened to be reading.',
        ],
    ];
}

/**
 * Things that are never written down, whatever it was asked to remember.
 *
 * Not a security control - a model that wants to leak a secret has a hundred
 * better routes than this table, and anything typed into the chat has already
 * left the machine. It is an honesty control. A store that quietly accumulated
 * credentials would be a store nobody could safely show to a colleague, and the
 * whole value of this one is that it can be read.
 *
 * @return string|null why it is refused, or null if it is fine
 */
function aiMemoryRefuses(string $subject, string $body): ?string
{
    $both = $subject . ' ' . $body;

    if (preg_match('/-----BEGIN [A-Z ]*PRIVATE KEY-----/', $both)) {
        return 'that is a private key';
    }

    /*
     * Long random-looking strings next to a word that means "secret".
     *
     * Both halves are needed. The word alone catches "the API key is set up
     * correctly", which is a perfectly good thing to remember; the entropy
     * alone catches every path hash and mask pseudonym on the dashboard.
     */
    $named =
        '/\b(password|passwd|secret|api[_ -]?key|token|bearer|credential)\b/i';
    $looksRandom = '/\b[A-Za-z0-9_\-]{24,}\b/';

    if (preg_match($named, $both) && preg_match($looksRandom, $both)) {
        return 'that looks like a credential, and this store is meant to be readable';
    }

    return null;
}

/**
 * Writes one memory, replacing any with the same subject.
 *
 * The subject is the key. "the backup window" written twice is one memory that
 * changed its mind, not two memories disagreeing - and a store that keeps both
 * makes the model choose between them with no way to tell which is current.
 *
 * @return array{id: int, replaced: bool}
 */
function aiMemoryWrite(
    PDO $ai,
    ?int $session,
    string $kind,
    string $subject,
    string $body,
    string $who = 'assistant',
): array {
    $subject = trim($subject);
    $body = trim($body);

    if ($subject === '') {
        throw new ActionRefused('a memory needs a subject');
    }
    if ($body === '') {
        throw new ActionRefused('a memory needs something in it');
    }
    if (!isset(aiMemoryKinds()[$kind])) {
        throw new ActionRefused('that is not one of the kinds of memory');
    }
    if (mb_strlen($subject) > 120) {
        throw new ActionRefused('that subject is too long to be a subject');
    }
    if (mb_strlen($body) > AI_MEMORY_BODY) {
        throw new ActionRefused(
            sprintf(
                'a memory holds %d characters. This is %d. Write the conclusion, not the ' .
                    'working.',
                AI_MEMORY_BODY,
                mb_strlen($body),
            ),
        );
    }

    $refused = aiMemoryRefuses($subject, $body);
    if ($refused !== null) {
        throw new ActionRefused('not remembering that: ' . $refused);
    }

    $now = time();

    $stmt = $ai->prepare('SELECT id FROM ai_memories WHERE subject = ?');
    $stmt->execute([$subject]);
    $existing = $stmt->fetchColumn();

    if ($existing !== false) {
        $ai->prepare(
            'UPDATE ai_memories
                SET body = ?, kind = ?, updated_at = ?, source_session = ?, written_by = ?
              WHERE id = ?',
        )->execute([$body, $kind, $now, $session, $who, (int) $existing]);

        return ['id' => (int) $existing, 'replaced' => true];
    }

    $ai->prepare(
        'INSERT INTO ai_memories (at, updated_at, source_session, kind, subject, body, written_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
    )->execute([$now, $now, $session, $kind, $subject, $body, $who]);

    aiMemoryTrim($ai);

    return ['id' => (int) $ai->lastInsertId(), 'replaced' => false];
}

/**
 * Drops the least useful once there are too many.
 *
 * Least recently *used*, not least recently written: a fact written a year ago
 * and consulted last week is doing its job, and one written yesterday that
 * nothing has ever looked at is not. Pinned memories are never dropped, which
 * is what pinning is for.
 */
function aiMemoryTrim(PDO $ai): int
{
    $over =
        (int) $ai->query('SELECT COUNT(*) FROM ai_memories')->fetchColumn() -
        AI_MEMORY_MAX;
    if ($over <= 0) {
        return 0;
    }

    $ai->prepare(
        'DELETE FROM ai_memories WHERE id IN (
            SELECT id FROM ai_memories
             WHERE pinned = 0
             ORDER BY COALESCE(used_at, updated_at) ASC
             LIMIT ?
         )',
    )->execute([$over]);

    return $over;
}

/**
 * Memories matching a search, newest and most-used first.
 *
 * Substring matching on the subject and body. Not clever, and clever would be
 * worse: an embedding search that silently returns something almost relevant is
 * harder to argue with than a plain match that finds nothing and says so.
 *
 * @return list<array<string, mixed>>
 */
function aiMemoryFind(PDO $ai, string $query = '', int $limit = 20): array
{
    $limit = max(1, min(100, $limit));
    $query = trim($query);

    if ($query === '') {
        $stmt = $ai->prepare(
            'SELECT * FROM ai_memories ORDER BY pinned DESC, updated_at DESC LIMIT ?',
        );
        $stmt->execute([$limit]);
    } else {
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';
        $stmt = $ai->prepare(
            "SELECT * FROM ai_memories
              WHERE subject LIKE ? ESCAPE '\\' OR body LIKE ? ESCAPE '\\'
              ORDER BY pinned DESC, updated_at DESC LIMIT ?",
        );
        $stmt->execute([$like, $like, $limit]);
    }

    $rows = $stmt->fetchAll();

    /*
     * Reading a memory counts as using it.
     *
     * Which is what keeps the trim above honest: the store forgets what nothing
     * ever asks for, rather than what happens to be old.
     */
    if ($rows !== []) {
        $ids = array_map(fn(array $row): int => (int) $row['id'], $rows);
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $ai->prepare(
            "UPDATE ai_memories SET used_at = ?, used = used + 1 WHERE id IN ({$marks})",
        )->execute(array_merge([time()], $ids));
    }

    return array_map('aiMemoryRow', $rows);
}

/** One memory, as everything outside this file wants it. */
function aiMemoryRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'kind' => (string) $row['kind'],
        'subject' => (string) $row['subject'],
        'body' => (string) $row['body'],
        'at' => (int) $row['at'],
        'updated_at' => (int) $row['updated_at'],
        'pinned' => (bool) $row['pinned'],
        'used' => (int) $row['used'],
        'used_at' => $row['used_at'] === null ? null : (int) $row['used_at'],
        'source_session' =>
            $row['source_session'] === null
                ? null
                : (int) $row['source_session'],
        'written_by' => (string) $row['written_by'],
    ];
}

/**
 * The short list put in front of the model at the start of a conversation.
 *
 * Pinned first, then most recently useful. Capped hard: a model reading forty
 * remembered facts before the question has spent its attention on the wrong
 * thing, and the point of a memory is to save work, not to become the work.
 *
 * @return list<array<string, mixed>>
 */
function aiMemoryBrief(PDO $ai, int $limit = AI_MEMORY_BRIEF): array
{
    $stmt = $ai->prepare(
        'SELECT * FROM ai_memories
          ORDER BY pinned DESC, COALESCE(used_at, updated_at) DESC
          LIMIT ?',
    );
    $stmt->execute([max(1, $limit)]);

    return array_map('aiMemoryRow', $stmt->fetchAll());
}

/** Everything, for the page that lists them. */
function aiMemoryAll(PDO $ai): array
{
    $rows = $ai
        ->query(
            'SELECT * FROM ai_memories ORDER BY pinned DESC, updated_at DESC',
        )
        ->fetchAll();

    return array_map('aiMemoryRow', $rows);
}

/** Removes one. */
function aiMemoryForget(PDO $ai, int $id): bool
{
    $stmt = $ai->prepare('DELETE FROM ai_memories WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

/** Removes all of them. */
function aiMemoryClear(PDO $ai): int
{
    $n = (int) $ai->query('SELECT COUNT(*) FROM ai_memories')->fetchColumn();
    $ai->exec('DELETE FROM ai_memories');
    return $n;
}

/** Pins or unpins one, which is what keeps it out of the trim. */
function aiMemoryPin(PDO $ai, int $id, bool $pinned): bool
{
    $stmt = $ai->prepare('UPDATE ai_memories SET pinned = ? WHERE id = ?');
    $stmt->execute([$pinned ? 1 : 0, $id]);
    return $stmt->rowCount() > 0;
}
