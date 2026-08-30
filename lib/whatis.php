<?php
declare(strict_types=1);

/*
 * What a thing on this dashboard actually is.
 *
 * Two lists on this site are full of names that mean something precise to the
 * kernel and nothing at all to the person reading them. `dentry` holding 400 MB
 * and `kworker/3:1H` at the top of the CPU list are both completely normal, and
 * both look alarming to somebody who has not met them before - so the honest
 * reading of the page depends on knowledge that is not on it.
 *
 * The descriptions answer the question people actually have, which is almost
 * never "what is this" but "is this one the problem". So each says what makes
 * that thing grow, because that is what a large number means.
 *
 * Three sources, in order:
 *
 *   the operator's own    written on this machine, about this machine, and
 *                         therefore right where the others are guessing
 *   an exact name         from the built-in list
 *   a prefix             `kmalloc-*` covers kmalloc-512 and the rest of the
 *                         family, which the kernel names by size
 *
 * The built-in list is a data file rather than code, so it can be corrected or
 * extended on an install without touching PHP - and so that being wrong about
 * `clamd` is a one-line fix rather than a deployment.
 */

require_once __DIR__ . '/db.php';

/** How many of their own descriptions one install may keep. */
const WHATIS_MAX = 400;

/**
 * The built-in descriptions, read once.
 *
 * @return array{slab: array<string, array>, process: array<string, array>}
 */
function whatIsBuiltIn(): array
{
    static $loaded = null;

    if ($loaded !== null) {
        return $loaded;
    }

    $file = __DIR__ . '/../data/what-things-are.json';
    $raw = is_file($file) ? file_get_contents($file) : false;
    $found = $raw === false ? null : json_decode($raw, true);

    /*
     * A missing file is not an error worth stopping for.
     *
     * Nothing here is load-bearing: without it the pages show names with no
     * explanation, which is exactly what they showed before this existed.
     */
    $loaded = [
        'slab' => is_array($found['slab'] ?? null) ? $found['slab'] : [],
        'process' => is_array($found['process'] ?? null)
            ? $found['process']
            : [],
    ];

    return $loaded;
}

/**
 * Everything known about one kind of thing, ready to send to the browser.
 *
 * Sent as a whole map rather than looked up one name at a time, because the
 * page has a hundred rows and a hundred round trips to describe them would be
 * absurd. The map is small - a few kilobytes - and the browser holds it for as
 * long as the page is open.
 *
 * @return array<string, array{what: string, link: ?string, mine: bool}>
 */
function whatIsAll(PDO $pdo, string $kind): array
{
    $out = [];

    foreach (whatIsBuiltIn()[$kind] ?? [] as $name => $entry) {
        $out[(string) $name] = [
            'what' => (string) ($entry['what'] ?? ''),
            'link' =>
                ($entry['link'] ?? '') === '' ? null : (string) $entry['link'],
            'mine' => false,
        ];
    }

    foreach (whatIsMine($pdo, $kind) as $row) {
        /*
         * The operator's own wins outright, and says so.
         *
         * Somebody who has written down what a process on their machine does
         * knows something the built-in list cannot: it is guessing from a name
         * that is often just a binary somebody chose. Marked as theirs so the
         * page can show whose sentence it is rather than presenting a local
         * note as documentation.
         */
        $out[(string) $row['name']] = [
            'what' => (string) $row['what'],
            'link' =>
                ($row['link'] ?? '') === '' ? null : (string) $row['link'],
            'mine' => true,
        ];
    }

    return $out;
}

/**
 * The descriptions written on this install.
 *
 * @return list<array>
 */
function whatIsMine(PDO $pdo, ?string $kind = null): array
{
    try {
        if ($kind === null) {
            return $pdo
                ->query('SELECT * FROM descriptions ORDER BY kind, name')
                ->fetchAll();
        }
        $stmt = $pdo->prepare(
            'SELECT * FROM descriptions WHERE kind = ? ORDER BY name',
        );
        $stmt->execute([$kind]);
        return $stmt->fetchAll();
    } catch (Throwable) {
        // An older database without the table. None is a valid answer.
        return [];
    }
}

/**
 * Writes one, or replaces it.
 *
 * A name is stored as typed apart from the trimming, including its case: slab
 * caches are case-sensitive and `TCP` is not `tcp`.
 */
function whatIsSave(
    PDO $pdo,
    string $kind,
    string $name,
    string $what,
    string $link = '',
): void {
    $kind = in_array($kind, ['slab', 'process'], true) ? $kind : 'process';
    $name = trim($name);
    $what = trim($what);
    $link = trim($link);

    if ($name === '') {
        throw new ActionRefused('which one is this about?');
    }
    if (mb_strlen($name) > 80) {
        throw new ActionRefused('that name is longer than any real one');
    }
    /*
     * A name, and a trailing star for a family.
     *
     * The kernel names families by size - kmalloc-512, kmalloc-1k - and writing
     * the same sentence out for each of them is how a list stops being kept up.
     */
    if (!preg_match('~^[A-Za-z0-9_.:/@+-]+\*?$~', $name)) {
        throw new ActionRefused(
            'a name can hold letters, digits and _.:/@+-, and may end in * for a family',
        );
    }

    if ($what === '') {
        throw new ActionRefused(
            'a description with no words in it explains nothing',
        );
    }
    if (mb_strlen($what) > 600) {
        throw new ActionRefused(
            'that is longer than a tooltip can usefully hold',
        );
    }

    if ($link !== '') {
        /*
         * http and https only.
         *
         * This ends up as an href on a page somebody clicks from, and the two
         * schemes worth refusing here - javascript: and data: - are refused by
         * allowing only the two that are ever meant.
         */
        $parts = parse_url($link);
        if (
            $parts === false ||
            !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
        ) {
            throw new ActionRefused(
                'a link has to start with http:// or https://',
            );
        }
        if (mb_strlen($link) > 300) {
            throw new ActionRefused('that link is too long');
        }
    }

    $count = (int) $pdo
        ->query('SELECT COUNT(*) FROM descriptions')
        ->fetchColumn();
    $exists = $pdo->prepare(
        'SELECT COUNT(*) FROM descriptions WHERE kind = ? AND name = ?',
    );
    $exists->execute([$kind, $name]);

    if ($count >= WHATIS_MAX && (int) $exists->fetchColumn() === 0) {
        throw new ActionRefused(
            'that is as many descriptions as this will hold',
        );
    }

    $pdo->prepare(
        'INSERT INTO descriptions (kind, name, what, link, added_at)
         VALUES (:k, :n, :w, :l, :at)
         ON CONFLICT(kind, name) DO UPDATE SET what = :w, link = :l',
    )->execute([
        ':k' => $kind,
        ':n' => $name,
        ':w' => $what,
        ':l' => $link === '' ? null : $link,
        ':at' => time(),
    ]);
}

/** Removes one. Returns whether there was one to remove. */
function whatIsForget(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM descriptions WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;
}
