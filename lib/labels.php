<?php
declare(strict_types=1);

/*
 * Your own names for the numbers.
 *
 * A capture report full of `165.245.160.135` and `8000` is a report you have to
 * decode before you can read it, and the decoding happens in the operator's
 * head every single time. `165.245.160.135 (this server)` and `8000 (Icecast)`
 * are the same data with the knowledge already applied.
 *
 * Two kinds, kept apart because they match differently:
 *
 *   address   an exact address, or a CIDR range so a whole network can be
 *             named once - "10.0.0.0/8 (office)" beats sixty rows
 *   port      an exact port number, which overrides the built-in catalogue
 *             because the person running the machine knows what it is and the
 *             catalogue is only guessing from convention
 *
 * Nothing here changes what was measured. A label is a presentation of a fact,
 * and the underlying address or port is always still shown beside it - so a
 * mislabelled entry can never hide what actually happened.
 */

require_once __DIR__ . '/db.php';

/** How many labels one install may have, so a runaway import cannot bloat it. */
const LABEL_MAX = 500;

/**
 * Every label, indexed for lookup.
 *
 * @return array{addresses: list<array>, ports: array<int,array>, count: int}
 */
function loadLabels(PDO $pdo): array
{
    try {
        $rows = $pdo->query('SELECT * FROM labels ORDER BY kind, match')->fetchAll();
    } catch (Throwable) {
        // An older database without the table. No labels is a valid state.
        return ['addresses' => [], 'ports' => [], 'count' => 0];
    }

    $addresses = [];
    $ports = [];

    foreach ($rows as $row) {
        if ($row['kind'] === 'port') {
            $ports[(int)$row['match']] = $row;
            continue;
        }
        $addresses[] = $row + parseCidr((string)$row['match']);
    }

    /*
     * Longest prefix first, so a specific host beats the network it is in.
     *
     * "10.0.0.5 (mail server)" and "10.0.0.0/8 (office)" should both exist and
     * the specific one should win, which is how routing tables have always
     * worked and what anybody writing these will expect.
     */
    $specificity = fn(array $entry): int =>
        // An exact address is the most specific thing there is, so it sorts
        // above every range - including a /32, which means the same thing.
        $entry['bits'] === null ? 129 : (int)$entry['bits'];

    usort($addresses, fn(array $a, array $b): int => $specificity($b) <=> $specificity($a));

    return ['addresses' => $addresses, 'ports' => $ports, 'count' => count($rows)];
}

/**
 * A CIDR string as a comparable prefix, or a marker that it is an exact match.
 *
 * Returns bits => null for a plain address, which sorts last above and is
 * compared as a string - exact matches need no arithmetic and IPv6 is far
 * easier to compare textually than to mask by hand.
 */
function parseCidr(string $match): array
{
    if (!str_contains($match, '/')) {
        return ['bits' => null, 'network' => null];
    }

    [$address, $bits] = explode('/', $match, 2);
    $packed = @inet_pton($address);
    if ($packed === false || !ctype_digit($bits)) {
        return ['bits' => null, 'network' => null];
    }

    return ['bits' => (int)$bits, 'network' => $packed];
}

/**
 * The label for one address, or null.
 *
 * Exact matches are tried through the same list as ranges rather than in a
 * separate pass: a `/32` and a bare address mean the same thing, and having two
 * code paths for one idea is how they come to disagree.
 */
function labelForAddress(array $labels, string $address): ?array
{
    $packed = @inet_pton($address);

    foreach ($labels['addresses'] as $entry) {
        if ($entry['bits'] === null) {
            if ($entry['match'] === $address) {
                return $entry;
            }
            continue;
        }

        if ($packed === false || $entry['network'] === null) {
            continue;
        }
        if (strlen($packed) !== strlen($entry['network'])) {
            // A v4 address against a v6 range, or the reverse.
            continue;
        }
        if (inSameNetwork($packed, $entry['network'], (int)$entry['bits'])) {
            return $entry;
        }
    }

    return null;
}

/** Whether two packed addresses share the first `bits` bits. */
function inSameNetwork(string $a, string $b, int $bits): bool
{
    $whole = intdiv($bits, 8);
    $rest = $bits % 8;

    if ($whole > strlen($a)) {
        return false;
    }
    if ($whole > 0 && substr($a, 0, $whole) !== substr($b, 0, $whole)) {
        return false;
    }
    if ($rest === 0) {
        return true;
    }

    $mask = 0xFF << (8 - $rest) & 0xFF;
    return (ord($a[$whole]) & $mask) === (ord($b[$whole]) & $mask);
}

function labelForPort(array $labels, int $port): ?array
{
    return $labels['ports'][$port] ?? null;
}

/**
 * Adds or replaces one label.
 *
 * Validated here rather than at the edge, because these are read by every
 * network page and a malformed one would be a rendering problem in six places.
 */
function saveLabel(PDO $pdo, string $kind, string $match, string $label, string $note = ''): void
{
    $kind = in_array($kind, ['port', 'header'], true) ? $kind : 'address';
    $match = $kind === 'header' ? strtolower(trim($match)) : trim($match);
    $label = trim($label);

    if ($label === '') {
        throw new ActionRefused('a label needs some text');
    }
    if (mb_strlen($label) > 60) {
        throw new ActionRefused('that label is too long to fit in a table');
    }

    if ($kind === 'port') {
        if (!ctype_digit($match) || (int)$match < 1 || (int)$match > 65535) {
            throw new ActionRefused('that is not a port number');
        }
    } elseif ($kind === 'header') {
        /*
         * A field name, by the grammar rather than by a list.
         *
         * The whole point of annotating headers is the house ones nobody has
         * documented, so checking against known names would refuse exactly the
         * cases worth writing down.
         */
        if (!preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]{1,64}$/", $match)) {
            throw new ActionRefused('that is not a header name');
        }
    } else {
        $bare = explode('/', $match)[0];
        if (@inet_pton($bare) === false) {
            throw new ActionRefused('that is not an address or a network');
        }
        if (str_contains($match, '/')) {
            $bits = (int)explode('/', $match, 2)[1];
            $max = str_contains($bare, ':') ? 128 : 32;
            if ($bits < 0 || $bits > $max) {
                throw new ActionRefused("a prefix length must be between 0 and {$max}");
            }
        }
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM labels')->fetchColumn();
    $exists = $pdo->prepare('SELECT COUNT(*) FROM labels WHERE kind = ? AND match = ?');
    $exists->execute([$kind, $match]);

    if ($count >= LABEL_MAX && (int)$exists->fetchColumn() === 0) {
        throw new ActionRefused('that is as many labels as this will hold');
    }

    $pdo->prepare(
        'INSERT INTO labels (kind, match, label, note, added_at) VALUES (:k, :m, :l, :n, :at)
         ON CONFLICT(kind, match) DO UPDATE SET label = :l, note = :n'
    )->execute([
        ':k' => $kind, ':m' => $match, ':l' => $label,
        ':n' => $note === '' ? null : mb_substr($note, 0, 200), ':at' => time(),
    ]);
}

function deleteLabel(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM labels WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

/**
 * Applies labels to rows carrying an address or a port.
 *
 * The original value is never replaced, only accompanied. A label is somebody's
 * note about a number, and a note that hides the number it annotates would make
 * a typo in the notes indistinguishable from a change on the network.
 */
function applyLabels(array $labels, array $rows, string $field, string $kind): array
{
    if ($labels['count'] === 0) {
        return $rows;
    }

    foreach ($rows as &$row) {
        $value = $row[$field] ?? null;
        if ($value === null) {
            continue;
        }

        $found = $kind === 'port'
            ? labelForPort($labels, (int)$value)
            : labelForAddress($labels, (string)$value);

        if ($found !== null) {
            /*
             * `own_label`, not `label`.
             *
             * The capture rows already carry a `label` column - it holds the
             * address or port itself - so writing the operator's name there
             * collided with it, and an address with no custom name rendered as
             * itself twice. A distinct key cannot collide with a column name
             * somewhere else.
             */
            $row['own_label'] = $found['label'];
            $row['own_note'] = $found['note'];
            $row['own_match'] = $found['match'];
        }
    }
    unset($row);

    return $rows;
}
