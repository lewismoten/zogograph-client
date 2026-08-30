<?php
declare(strict_types=1);

/*
 * Turning an address into a name.
 *
 * A capture or an access log is a wall of numbers, and the same number means
 * nothing on Monday and something on Friday once somebody has looked it up.
 * `78.174.88.104` is a fact; `host-88-104.tur.example.net` is a sentence.
 *
 * This is deliberately not "your own names". That table is what *you* decided
 * an address means - "Lewis: home", "the build agent" - and it is small,
 * hand-written and permanent. This one is what the internet says an address is:
 * automatic, large, occasionally wrong, and stale within months. Mixing them
 * would mean a machine-generated guess sitting in a list somebody curated, and
 * a curated note being silently replaced by a PTR record.
 *
 * The lookup itself goes out to somebody else's resolver, which is why it is a
 * queue rather than a function call:
 *
 *   - a page showing forty addresses would otherwise make forty DNS queries
 *     while somebody waits, and a resolver that is slow or down turns a page
 *     into a timeout
 *   - a reverse lookup can take seconds, and there is no way to know which
 *     before asking
 *   - it is an external service being used on somebody else's behalf, which is
 *     a thing to do sparingly and visibly
 *
 * So: anything can ask, one is drained per collector run, oldest first, and a
 * request can be withdrawn before it is taken.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/spans.php';

/** How long a resolved name is believed before it is worth asking again. */
const RDNS_FRESH_FOR = 30 * SPAN_DAY;

/** How long a failure is remembered, so a dead address is not re-asked hourly. */
const RDNS_RETRY_AFTER = 7 * SPAN_DAY;

/** As many as the queue will hold. Beyond this, asking is refused rather than lost. */
const RDNS_QUEUE_MAX = 500;

/**
 * Asks for an address to be resolved, unless it already is.
 *
 * Returns what happened, because all four outcomes are worth different words
 * on screen: it is already known, it is already queued, it was just queued, or
 * it was asked about too recently to be worth asking again.
 *
 * @return array{state: string, name: ?string, at: ?int}
 */
function rdnsRequest(PDO $pdo, string $address, string $who = 'account'): array
{
    $address = trim($address);

    if (@inet_pton($address) === false) {
        throw new ActionRefused('that is not an address');
    }

    /*
     * Addresses that no public resolver can answer for.
     *
     * A private range has no PTR record anywhere outside the network it
     * belongs to, so asking is a guaranteed timeout - and it would consume the
     * one lookup a collector run is allowed to make.
     */
    if (isPrivateAddress($address)) {
        return ['state' => 'private', 'name' => null, 'at' => null];
    }

    $known = rdnsLookup($pdo, $address);

    if ($known !== null) {
        if (
            $known['name'] !== null &&
            $known['checked_at'] > time() - RDNS_FRESH_FOR
        ) {
            return [
                'state' => 'known',
                'name' => $known['name'],
                'at' => $known['checked_at'],
            ];
        }
        if (
            $known['name'] === null &&
            $known['checked_at'] > time() - RDNS_RETRY_AFTER
        ) {
            /*
             * A recent failure counts as an answer.
             *
             * Plenty of addresses simply have no PTR record, and re-asking
             * every time one appears in a capture would spend the whole budget
             * on the addresses guaranteed not to answer.
             */
            return [
                'state' => 'no name',
                'name' => null,
                'at' => $known['checked_at'],
            ];
        }
    }

    $waiting = (int) $pdo
        ->query('SELECT COUNT(*) FROM rdns_queue WHERE taken_at IS NULL')
        ->fetchColumn();

    $already = $pdo->prepare(
        'SELECT asked_at FROM rdns_queue WHERE address = ? AND taken_at IS NULL',
    );
    $already->execute([$address]);
    $when = $already->fetchColumn();

    if ($when !== false) {
        return ['state' => 'waiting', 'name' => null, 'at' => (int) $when];
    }

    if ($waiting >= RDNS_QUEUE_MAX) {
        throw new ActionRefused(
            sprintf(
                'there are already %d addresses waiting, which at one per collector run is ' .
                    'more than a week of work. Let it catch up first.',
                $waiting,
            ),
        );
    }

    $pdo->prepare(
        'INSERT INTO rdns_queue (address, asked_at, asked_by) VALUES (?, ?, ?)',
    )->execute([$address, time(), $who]);

    return ['state' => 'queued', 'name' => null, 'at' => time()];
}

/**
 * Whether an address can only be answered for from inside its own network.
 *
 * Checked here rather than left to fail, because a timeout costs the whole of
 * one collector run's budget and a private address will always time out.
 */
function isPrivateAddress(string $address): bool
{
    $packed = @inet_pton($address);
    if ($packed === false) {
        return true;
    }

    if (strlen($packed) === 4) {
        // RFC 1918, loopback, link-local, CGNAT and this-network.
        return (bool) !filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE |
                FILTER_FLAG_NO_RES_RANGE,
        );
    }

    return (bool) !filter_var(
        $address,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
    );
}

/** What is known about one address, or null. */
function rdnsLookup(PDO $pdo, string $address): ?array
{
    try {
        $stmt = $pdo->prepare('SELECT * FROM rdns WHERE address = ?');
        $stmt->execute([$address]);
        $row = $stmt->fetch();
    } catch (Throwable) {
        return null;
    }

    if ($row === false) {
        return null;
    }

    return [
        'address' => (string) $row['address'],
        'name' =>
            $row['name'] === null || $row['name'] === ''
                ? null
                : (string) $row['name'],
        'checked_at' => (int) $row['checked_at'],
        'first_seen' => (int) $row['first_seen'],
        'by' => (string) $row['resolved_by'],
        'ms' => (int) $row['ms'],
    ];
}

/**
 * Every name known, keyed by address, for decorating a table.
 *
 * One query for a whole page rather than one per row: a capture report can
 * mention two hundred addresses.
 *
 * @return array<string, string>
 */
function rdnsNames(PDO $pdo, array $addresses = []): array
{
    try {
        if ($addresses === []) {
            $rows = $pdo
                ->query(
                    "SELECT address, name FROM rdns WHERE name IS NOT NULL AND name <> ''",
                )
                ->fetchAll();
        } else {
            $in = implode(',', array_fill(0, count($addresses), '?'));
            $stmt = $pdo->prepare(
                "SELECT address, name FROM rdns
                  WHERE name IS NOT NULL AND name <> '' AND address IN ($in)",
            );
            $stmt->execute(array_values($addresses));
            $rows = $stmt->fetchAll();
        }
    } catch (Throwable) {
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        $out[(string) $row['address']] = (string) $row['name'];
    }

    return $out;
}

/**
 * Takes the oldest waiting request and answers it.
 *
 * One per call, which is the whole point: this uses somebody else's resolver,
 * and a collector run that resolved four hundred addresses would be a burst of
 * traffic to a service that never agreed to serve it. Oldest first, so a
 * request cannot be starved by newer ones arriving.
 *
 * Returns null when there is nothing to do, which is the common case.
 *
 * @return array{address: string, name: ?string, ms: int, waited: int}|null
 */
function rdnsDrainOne(
    PDO $pdo,
    string $by = 'host',
    ?callable $resolver = null,
): ?array {
    try {
        $next = $pdo
            ->query(
                'SELECT id, address, asked_at FROM rdns_queue
              WHERE taken_at IS NULL ORDER BY asked_at, id LIMIT 1',
            )
            ->fetch();
    } catch (Throwable) {
        return null;
    }

    if ($next === false) {
        return null;
    }

    /*
     * Marked as taken before the lookup, not after.
     *
     * A lookup that hangs and a process that is killed while waiting would
     * otherwise leave the request at the head of the queue for ever, and every
     * subsequent run would pick the same one and hang on it too.
     */
    $pdo->prepare(
        'UPDATE rdns_queue SET taken_at = ?, taken_by = ? WHERE id = ?',
    )->execute([time(), $by, (int) $next['id']]);

    $address = (string) $next['address'];
    $started = microtime(true);

    $name = $resolver !== null ? $resolver($address) : rdnsResolve($address);

    $ms = (int) round((microtime(true) - $started) * 1000);

    rdnsStore($pdo, $address, $name, $by, $ms);

    $pdo->prepare('DELETE FROM rdns_queue WHERE id = ?')->execute([
        (int) $next['id'],
    ]);

    return [
        'address' => $address,
        'name' => $name,
        'ms' => $ms,
        'waited' => time() - (int) $next['asked_at'],
    ];
}

/**
 * The actual reverse lookup.
 *
 * gethostbyaddr returns the address unchanged when there is no PTR record,
 * which is a genuinely awful interface - "the answer is the question" means
 * "no answer" - so that case is turned into null here rather than being
 * stored as a name that is not one.
 */
function rdnsResolve(string $address): ?string
{
    $name = @gethostbyaddr($address);

    if ($name === false || $name === '' || $name === $address) {
        return null;
    }

    // A trailing dot is correct in DNS and noise in a table.
    return rtrim($name, '.');
}

/** Records an answer, including the answer "there is no name". */
function rdnsStore(
    PDO $pdo,
    string $address,
    ?string $name,
    string $by,
    int $ms,
): void {
    $now = time();

    $pdo->prepare(
        'INSERT INTO rdns (address, name, checked_at, first_seen, resolved_by, ms)
         VALUES (:address, :name, :now, :now, :by, :ms)
         ON CONFLICT(address) DO UPDATE SET
            name = :name, checked_at = :now, resolved_by = :by, ms = :ms,
            /*
             * first_seen keeps the moment this name first appeared, not the
             * moment the row did - so "this address has been called that since
             * March" is answerable, and a name that changes resets it.
             */
            first_seen = CASE WHEN rdns.name IS :name THEN rdns.first_seen ELSE :now END',
    )->execute([
        ':address' => $address,
        ':name' => $name,
        ':now' => $now,
        ':by' => $by,
        ':ms' => $ms,
    ]);
}

/**
 * What is waiting, and what has been answered.
 *
 * @return array<string, mixed>
 */
function rdnsState(PDO $pdo, int $limit = 200): array
{
    $waiting = [];
    $known = [];

    try {
        $waiting = $pdo
            ->query(
                'SELECT id, address, asked_at, asked_by, taken_at, taken_by
               FROM rdns_queue ORDER BY asked_at, id LIMIT ' . max(1, $limit),
            )
            ->fetchAll();

        $known = $pdo
            ->query(
                'SELECT * FROM rdns ORDER BY checked_at DESC LIMIT ' .
                    max(1, $limit),
            )
            ->fetchAll();
    } catch (Throwable) {
        return ['waiting' => [], 'known' => [], 'queue_max' => RDNS_QUEUE_MAX];
    }

    $now = time();
    foreach ($waiting as &$row) {
        $row['waited'] = $now - (int) $row['asked_at'];
        $row['taken'] = $row['taken_at'] !== null;
    }
    unset($row);

    foreach ($known as &$row) {
        $row['name'] = $row['name'] === '' ? null : $row['name'];
        $row['age'] = $now - (int) $row['checked_at'];
        $row['stale'] = $row['age'] > RDNS_FRESH_FOR;
    }
    unset($row);

    return [
        'waiting' => $waiting,
        'known' => $known,
        'queue_max' => RDNS_QUEUE_MAX,
        'fresh_for' => RDNS_FRESH_FOR,
    ];
}

/**
 * Withdraws a request that has not been taken yet.
 *
 * Only an untaken one: once a collector has picked it up the lookup is either
 * running or done, and pretending it can be recalled would be a lie about what
 * this can control.
 */
function rdnsCancel(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        'DELETE FROM rdns_queue WHERE id = ? AND taken_at IS NULL',
    );
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

/** Withdraws everything still waiting. */
function rdnsCancelAll(PDO $pdo): int
{
    $stmt = $pdo->prepare('DELETE FROM rdns_queue WHERE taken_at IS NULL');
    $stmt->execute();
    return $stmt->rowCount();
}

/**
 * Requests whose collector took them and never came back.
 *
 * A run that is killed mid-lookup leaves a row marked taken with no answer.
 * Released rather than deleted: the address was worth asking about and still
 * is, and the alternative is a queue that silently loses entries.
 */
function rdnsReleaseStuck(PDO $pdo, int $after = 900): int
{
    $stmt = $pdo->prepare(
        'UPDATE rdns_queue SET taken_at = NULL, taken_by = NULL WHERE taken_at < ?',
    );
    $stmt->execute([time() - $after]);
    return $stmt->rowCount();
}

/* -------------------------------------------------- the host's half of it -- */

/**
 * Addresses waiting, read from the account database without writing to it.
 *
 * The host collector opens the account database read-only and always has - it
 * runs as root and a file it touched would change owner, after which the
 * account could no longer write its own database. So it reads the queue here
 * and leaves its answer in its own database, where the account picks it up.
 *
 * @return list<array{id: int, address: string, asked_at: int}>
 */
function rdnsQueueFor(array $config, int $limit = 1): array
{
    $file = (string) ($config['database'] ?? '');
    if ($file === '' || !is_file($file) || !is_readable($file)) {
        return [];
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA query_only = 1');

        $stmt = $pdo->prepare(
            'SELECT id, address, asked_at FROM rdns_queue
              WHERE taken_at IS NULL ORDER BY asked_at, id LIMIT ' .
                max(1, $limit),
        );
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Answers what is in the host's own database already.
 *
 * So that a host run does not re-resolve an address the account resolved
 * itself between the two runs - the queue row is the account's to remove, and
 * until it does the host would keep seeing it.
 */
function rdnsAlreadyAnswered(PDO $host, string $address): bool
{
    try {
        $stmt = $host->prepare(
            "SELECT COUNT(*) FROM host_answers
              WHERE verb = 'rdns' AND argument = ? AND answered_at > ?",
        );
        $stmt->execute([$address, time() - RDNS_FRESH_FOR]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Resolves the oldest waiting address and leaves the answer for the account.
 *
 * One per run, which is the whole point: this uses somebody else's resolver.
 *
 * @return array{address: string, name: ?string, ms: int}|null
 */
function rdnsHostDrain(
    array $config,
    PDO $host,
    ?callable $resolver = null,
): ?array {
    foreach (rdnsQueueFor($config, 5) as $row) {
        $address = (string) $row['address'];

        if (rdnsAlreadyAnswered($host, $address)) {
            continue;
        }

        $started = microtime(true);
        $name =
            $resolver !== null ? $resolver($address) : rdnsResolve($address);
        $ms = (int) round((microtime(true) - $started) * 1000);

        try {
            $host
                ->prepare(
                    'INSERT INTO host_answers (verb, argument, asked_at, answered_at, ok, text, error)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                )
                ->execute([
                    'rdns',
                    $address,
                    (int) $row['asked_at'],
                    time(),
                    1,
                    // The empty string is the answer "there is no name", which is
                    // different from the lookup having failed.
                    $name ?? '',
                    null,
                ]);
        } catch (Throwable $e) {
            return [
                'address' => $address,
                'name' => null,
                'ms' => $ms,
                'error' => $e->getMessage(),
            ];
        }

        return ['address' => $address, 'name' => $name, 'ms' => $ms];
    }

    return null;
}

/**
 * Takes any answers the host left and folds them into the account's own store.
 *
 * Run by the account's collector, which is the only process that writes the
 * account database. An answer the host produced and an answer the account
 * produced end up in the same table and are indistinguishable afterwards
 * except by `resolved_by`, which is the point - the reader wants the name, not
 * an account of which process obtained it.
 *
 * @return int how many were taken
 */
function rdnsImportHostAnswers(PDO $pdo, ?PDO $host): int
{
    if ($host === null) {
        return 0;
    }

    try {
        $rows = $host
            ->query(
                "SELECT argument, text, answered_at FROM host_answers
              WHERE verb = 'rdns' ORDER BY answered_at",
            )
            ->fetchAll();
    } catch (Throwable) {
        return 0;
    }

    $taken = 0;

    foreach ($rows as $row) {
        $address = (string) $row['argument'];
        $name = (string) ($row['text'] ?? '');

        $known = rdnsLookup($pdo, $address);
        if (
            $known !== null &&
            $known['checked_at'] >= (int) $row['answered_at']
        ) {
            /*
             * The account already has something at least as new.
             *
             * Which happens when somebody pressed the button while a host run
             * was in flight. The queue row is still cleared below, because the
             * question has been answered either way.
             */
            $pdo->prepare('DELETE FROM rdns_queue WHERE address = ?')->execute([
                $address,
            ]);
            continue;
        }

        rdnsStore($pdo, $address, $name === '' ? null : $name, 'host', 0);
        $pdo->prepare('DELETE FROM rdns_queue WHERE address = ?')->execute([
            $address,
        ]);
        $taken++;
    }

    return $taken;
}

/**
 * Adds the resolved name to rows carrying an address.
 *
 * Alongside `own_label` rather than instead of it: the two answer different
 * questions and both are worth having. "This is 78.174.88.104, which the
 * internet calls host-88-104.tur.example.net, and which you have written down
 * as the crawler that keeps hitting us" is three facts, and the last two are
 * the ones somebody is reading for.
 *
 * `rdns_asked` says the address is already on the queue, so a page can offer to
 * look up the ones that are not without offering to look up the ones that are.
 */
function applyRdns(PDO $pdo, array $rows, string $field = 'address'): array
{
    $addresses = [];
    foreach ($rows as $row) {
        $value = $row[$field] ?? null;
        if (is_string($value) && $value !== '') {
            $addresses[$value] = true;
        }
    }

    if ($addresses === []) {
        return $rows;
    }

    $names = rdnsNames($pdo, array_keys($addresses));

    /*
     * Addresses already answered with "there is no name".
     *
     * A real answer, and a common one - plenty of addresses have no PTR
     * record. Without this the row falls back to offering a lookup, so
     * somebody keeps queueing an address that has already been asked and will
     * keep answering the same way.
     */
    $none = [];
    try {
        $in = implode(',', array_fill(0, count($addresses), '?'));
        $stmt = $pdo->prepare(
            "SELECT address FROM rdns WHERE (name IS NULL OR name = '') AND address IN ($in)",
        );
        $stmt->execute(array_keys($addresses));
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN, 0) as $address) {
            $none[(string) $address] = true;
        }
    } catch (Throwable) {
        $none = [];
    }

    $queued = [];
    try {
        $in = implode(',', array_fill(0, count($addresses), '?'));
        $stmt = $pdo->prepare(
            "SELECT address FROM rdns_queue WHERE address IN ($in)",
        );
        $stmt->execute(array_keys($addresses));
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN, 0) as $address) {
            $queued[(string) $address] = true;
        }
    } catch (Throwable) {
        $queued = [];
    }

    foreach ($rows as &$row) {
        $value = $row[$field] ?? null;
        if (!is_string($value) || $value === '') {
            continue;
        }
        if (isset($names[$value])) {
            $row['rdns_name'] = $names[$value];
        }
        if (isset($queued[$value])) {
            $row['rdns_asked'] = true;
        }
        if (isset($none[$value])) {
            $row['rdns_none'] = true;
        }
    }
    unset($row);

    return $rows;
}
