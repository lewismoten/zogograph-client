<?php
declare(strict_types=1);

/*
 * Every record a domain publishes, and what changed since last time.
 *
 * The gap this fills: a name resolving is not the same as a zone being right.
 * Mail stops arriving because an MX was dropped or an SPF record edited; a
 * subdomain starts pointing at an address that is not yours; a TTL is cut to
 * sixty seconds during a migration and never put back. None of that shows up
 * anywhere else on this site, and all of it is visible in the zone.
 *
 * Two problems, solved differently:
 *
 *   which records   asking for one type at a time. `ANY` is not an answer -
 *                   most resolvers now return a minimal or refused response to
 *                   it, and a zone that answers it does so inconsistently. So
 *                   the types worth having are asked for individually.
 *   which names     a zone transfer if the nameserver allows one from here,
 *                   which gives the authoritative list; cPanel's own record of
 *                   the account's domains, which is exact for this account; and
 *                   the names already known from previous runs. Not a wordlist:
 *                   guessing at names is slow, noisy, and produces a list of
 *                   things that do not exist.
 *
 * Every record is stored with when it was first and last seen, so "what
 * changed" is a query rather than a diff of two snapshots. A record that stops
 * appearing is marked gone rather than deleted - the interesting question is
 * usually about something that used to be there.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/headers.php';
require_once __DIR__ . '/network.php';
require_once __DIR__ . '/inspect.php';

/** Types asked for on the apex of a zone. */
const DNS_APEX_TYPES = ['A', 'AAAA', 'NS', 'MX', 'TXT', 'SOA', 'CAA'];

/** Types asked for on a subdomain, where SOA and NS rarely apply. */
const DNS_NAME_TYPES = ['A', 'AAAA', 'CNAME', 'MX', 'TXT'];

/** How many names one sweep will look up, so a large zone cannot run for ever. */
const DNS_MAX_NAMES = 120;

/*
 * How long a whole sweep may take.
 *
 * This runs while somebody waits for the page, so it is bounded by the clock
 * rather than by a count: a zone of twenty names on a fast resolver finishes in
 * a second, and one name pointing at a nameserver that never answers would
 * otherwise hold the request open until PHP gave up.
 */
const DNS_SWEEP_BUDGET = 20.0;

/** Per-query patience. Two seconds is generous for a resolver that is working. */
const DNS_QUERY_TIME = 2;

/** And a hard cap on a zone transfer, which is TCP and can hang indefinitely. */
const DNS_TRANSFER_TIMEOUT = 5;

/*
 * How many nameservers to try a transfer against.
 *
 * They all hold the same zone, so a second opinion only guards against the
 * first being the misconfigured one. Trying all five of a large provider's
 * servers costs five timeouts to learn the same refusal.
 */
const DNS_TRANSFER_SERVERS = 2;

/**
 * Looks a whole domain up and records what it publishes.
 *
 * @return array{ok: bool, reason: string, names: int, records: int, changes: list<array>}
 */
function dnsSweep(
    array $config,
    PDO $pdo,
    string $domain,
    ?callable $runner = null,
): array {
    $domain = strtolower(trim($domain, " \t."));

    if (
        !preg_match(
            '/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/',
            $domain,
        )
    ) {
        return [
            'ok' => false,
            'reason' => 'that is not a domain name',
            'names' => 0,
            'records' => 0,
            'changes' => [],
        ];
    }

    $dig = $runner === null ? whichBinary('dig') : 'dig';
    if ($dig === null) {
        return [
            'ok' => false,
            'reason' =>
                'dig is not installed here. On Red Hat it is in bind-utils; on ' .
                'Debian, dnsutils. The host collector can be asked to run it instead.',
            'names' => 0,
            'records' => 0,
            'changes' => [],
        ];
    }

    $names = dnsNamesToCheck($config, $pdo, $domain, $dig, $runner);
    $at = time();
    $seen = [];
    $asked = 0;
    $skipped = 0;
    $deadline = microtime(true) + DNS_SWEEP_BUDGET;

    foreach (array_slice($names, 0, DNS_MAX_NAMES) as $name) {
        if (microtime(true) > $deadline) {
            break;
        }

        /*
         * Does this name exist at all, before asking five questions about it.
         *
         * The common-name list is mostly names that do not exist on any given
         * zone, and asking each of them for five record types meant forty
         * lookups that all had to time out. One question first - and a name
         * that does not exist is skipped entirely.
         *
         * The apex always exists by definition, so it goes straight through.
         */
        if ($name !== $domain) {
            $exists = dnsNameExists($dig, $name, $runner);
            $asked++;
            if ($exists === false) {
                $skipped++;
                continue;
            }
        }

        $types = $name === $domain ? DNS_APEX_TYPES : DNS_NAME_TYPES;

        foreach ($types as $type) {
            if (microtime(true) > $deadline) {
                break 2;
            }
            $asked++;
            foreach (dnsQueryRecords($dig, $name, $type, $runner) as $record) {
                $seen[] = $record;
            }
        }
    }

    $ranOut = microtime(true) > $deadline;

    /*
     * An incomplete sweep never marks anything gone.
     *
     * dnsStore decides "gone" by absence, and a sweep that ran out of time is
     * full of absences that mean nothing. Reporting those as records that
     * disappeared would be inventing changes on the one page whose entire job
     * is to report real ones.
     */
    $changes = dnsStore($pdo, $domain, $seen, $at, !$ranOut);

    return [
        'ok' => true,
        'reason' => '',
        'names' => min(count($names), DNS_MAX_NAMES),
        'records' => count($seen),
        'lookups' => $asked,
        'skipped' => $skipped,
        'changes' => $changes,
        'truncated' => count($names) > DNS_MAX_NAMES,
        /*
         * Said out loud, because a partial sweep looks exactly like a small zone.
         *
         * Records that were not asked about would otherwise be marked gone by
         * dnsStore - reporting a change that did not happen, which is the worst
         * possible failure for a page whose job is to report changes.
         */
        'incomplete' => $ranOut,
    ];
}

/**
 * Which names to ask about, from every source that knows one.
 *
 * @return list<string>
 */
function dnsNamesToCheck(
    array $config,
    PDO $pdo,
    string $domain,
    string $dig,
    ?callable $runner = null,
): array {
    $names = [$domain => true];

    /*
     * A zone transfer, if the nameserver will give one.
     *
     * On somebody else's zone this is refused, which is correct and expected.
     * On your own nameserver it is the only complete answer there is - every
     * other method is inference - so it is worth one attempt against each
     * authoritative server before falling back.
     */
    $tried = 0;
    foreach (dnsAuthoritative($dig, $domain, $runner) as $server) {
        if ($tried++ >= DNS_TRANSFER_SERVERS) {
            break;
        }
        foreach (dnsZoneTransfer($dig, $domain, $server, $runner) as $name) {
            $names[$name] = true;
        }
        if (count($names) > 1) {
            // One server answering is enough; the rest hold the same zone.
            break;
        }
    }

    /*
     * cPanel's own list, which is exact for this account.
     *
     * Reuses the same reader the treemap uses to put a globe on a document
     * root - the file says which domains this account serves, which is a fact
     * rather than a guess.
     */
    foreach (array_keys(cpanelDocumentRoots($config)) as $root) {
        foreach (cpanelDocumentRoots($config)[$root] as $served) {
            $served = strtolower(trim($served, '.'));
            if ($served === $domain || str_ends_with($served, '.' . $domain)) {
                $names[$served] = true;
            }
        }
    }

    // Whatever was found before, so a name that stops resolving is still asked
    // about - that is precisely the change worth reporting.
    try {
        $stmt = $pdo->prepare(
            'SELECT DISTINCT name FROM dns_records WHERE domain = ?',
        );
        $stmt->execute([$domain]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $known) {
            $names[strtolower((string) $known)] = true;
        }
    } catch (Throwable) {
        // No table yet.
    }

    /*
     * A short list of names that exist on almost every zone.
     *
     * Not a brute-force wordlist - eight names, all of them things a mail or web
     * host creates by default. Anything larger would be scanning somebody's
     * zone, which is slow, rude and mostly returns nothing.
     */
    foreach (
        [
            'www',
            'mail',
            'ftp',
            'webmail',
            'cpanel',
            'autodiscover',
            'ns1',
            'ns2',
        ]
        as $common
    ) {
        $names[$common . '.' . $domain] = true;
    }

    $out = array_keys($names);
    sort($out);
    return $out;
}

/** The nameservers a domain says are authoritative for it. */
function dnsAuthoritative(
    string $dig,
    string $domain,
    ?callable $runner = null,
): array {
    $out = [];
    foreach (dnsQueryRecords($dig, $domain, 'NS', $runner) as $record) {
        $server = strtolower(trim((string) $record['value'], '.'));
        if ($server !== '') {
            $out[] = $server;
        }
    }
    return $out;
}

/**
 * A zone transfer, which is refused far more often than it succeeds.
 *
 * A refusal is the correct configuration for a public nameserver and is not
 * reported as an error - it simply means this method contributed nothing and
 * the others carry the load.
 *
 * @return list<string>
 */
function dnsZoneTransfer(
    string $dig,
    string $domain,
    string $server,
    ?callable $runner = null,
): array {
    if (!preg_match('/^[a-z0-9.-]{1,253}$/i', $server)) {
        return [];
    }

    /*
     * Wrapped in `timeout`, because dig's own +time does not bound this.
     *
     * A zone transfer is TCP, and +time governs the UDP query timeout. Against
     * a server that accepts the connection and then says nothing - which is
     * what a nameserver set to ignore rather than refuse transfers does - dig
     * simply sits there, and it hung the entire sweep.
     */
    $limit = whichBinary('timeout');
    $command = sprintf(
        '%s%s +noall +answer +tries=1 +time=' .
            DNS_QUERY_TIME .
            ' @%s %s AXFR 2>&1',
        $limit === null
            ? ''
            : escapeshellarg($limit) . ' ' . DNS_TRANSFER_TIMEOUT . ' ',
        escapeshellarg($dig),
        escapeshellarg($server),
        escapeshellarg($domain),
    );

    $out = $runner === null ? shellRead($command) : $runner($command);
    if (
        $out === null ||
        stripos($out, 'transfer failed') !== false ||
        stripos($out, 'communications error') !== false
    ) {
        return [];
    }

    $names = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 4) {
            continue;
        }
        $name = strtolower(rtrim($parts[0], '.'));
        if (
            $name !== '' &&
            ($name === $domain || str_ends_with($name, '.' . $domain))
        ) {
            $names[$name] = true;
        }
    }

    return array_keys($names);
}

/**
 * Whether a name exists at all.
 *
 * NXDOMAIN is a definite no from the zone's own nameserver and is the answer
 * for most of the common names on most zones. NOERROR with no records means the
 * name exists but has nothing of that type - which is a yes, because another
 * type may well be there.
 *
 * Returns null when nothing came back, and null is treated as "ask anyway":
 * a resolver that timed out has told us nothing, and skipping a name on that
 * basis would silently drop records that are really there.
 */
function dnsNameExists(
    string $dig,
    string $name,
    ?callable $runner = null,
): ?bool {
    if (!preg_match('/^[a-z0-9._-]{1,253}$/i', $name)) {
        return false;
    }

    $command = sprintf(
        '%s +noall +comments +tries=1 +time=' . DNS_QUERY_TIME . ' %s A 2>&1',
        escapeshellarg($dig),
        escapeshellarg($name),
    );

    $out = $runner === null ? shellRead($command) : $runner($command);
    if ($out === null || trim($out) === '') {
        return null;
    }

    if (preg_match('/status:\s*NXDOMAIN/i', $out)) {
        return false;
    }
    if (preg_match('/status:\s*NOERROR/i', $out)) {
        return true;
    }

    return null;
}

/**
 * One name and type, as records.
 *
 * @return list<array{name: string, type: string, value: string, ttl: int}>
 */
function dnsQueryRecords(
    string $dig,
    string $name,
    string $type,
    ?callable $runner = null,
): array {
    if (
        !preg_match('/^[a-z0-9._-]{1,253}$/i', $name) ||
        !preg_match('/^[A-Z]{1,10}$/', $type)
    ) {
        return [];
    }

    $command = sprintf(
        '%s +noall +answer +tries=1 +time=' . DNS_QUERY_TIME . ' %s %s 2>&1',
        escapeshellarg($dig),
        escapeshellarg($name),
        escapeshellarg($type),
    );

    $out = $runner === null ? shellRead($command) : $runner($command);
    if ($out === null) {
        return [];
    }

    $records = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        if ($line === '' || str_starts_with($line, ';')) {
            continue;
        }
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 5) {
            continue;
        }

        [$answerName, $ttl, , $answerType] = $parts;
        $value = implode(' ', array_slice($parts, 4));

        /*
         * A CNAME answer to an A query is reported as the CNAME it is.
         *
         * dig follows the chain and returns both, and recording the final A
         * under the alias would claim the name has an address record when what
         * it actually has is a pointer somewhere else - a distinction that
         * matters the day the target moves.
         */
        $records[] = [
            'name' => strtolower(rtrim($answerName, '.')),
            'type' => strtoupper($answerType),
            'value' => trim($value),
            'ttl' => (int) $ttl,
        ];
    }

    return $records;
}

/**
 * Stores what was seen, and returns what changed.
 *
 * The change list is the point of the whole exercise. A record appearing, a
 * value changing and a record vanishing are three different events and each is
 * reported as itself.
 *
 * @return list<array{change: string, name: string, type: string, value: string, was: ?string}>
 */
function dnsStore(
    PDO $pdo,
    string $domain,
    array $records,
    int $at,
    bool $complete = true,
): array {
    $changes = [];

    $before = [];
    try {
        $stmt = $pdo->prepare(
            'SELECT name, type, value, ttl FROM dns_records WHERE domain = ? AND gone_at IS NULL',
        );
        $stmt->execute([$domain]);
        foreach ($stmt->fetchAll() as $row) {
            $before[
                $row['name'] . '|' . $row['type'] . '|' . $row['value']
            ] = $row;
        }
    } catch (Throwable) {
        return $changes;
    }

    $now = [];
    foreach ($records as $record) {
        $now[
            $record['name'] . '|' . $record['type'] . '|' . $record['value']
        ] = $record;
    }

    $insert = $pdo->prepare(
        'INSERT INTO dns_records (domain, name, type, value, ttl, first_seen, last_seen)
         VALUES (:d, :n, :t, :v, :ttl, :at, :at)
         ON CONFLICT(domain, name, type, value)
         DO UPDATE SET last_seen = :at, ttl = :ttl, gone_at = NULL',
    );

    foreach ($now as $key => $record) {
        $insert->execute([
            ':d' => $domain,
            ':n' => $record['name'],
            ':t' => $record['type'],
            ':v' => $record['value'],
            ':ttl' => $record['ttl'],
            ':at' => $at,
        ]);

        if (!isset($before[$key])) {
            $changes[] = [
                'change' => 'added',
                'name' => $record['name'],
                'type' => $record['type'],
                'value' => $record['value'],
                'was' => null,
                'at' => $at,
            ];
        }
    }

    /*
     * Anything that was there and is not now.
     *
     * Marked gone rather than deleted: "the MX record disappeared last Tuesday"
     * is the question somebody asks a week later, and a row that was removed
     * cannot answer it.
     */
    $gone = $pdo->prepare(
        'UPDATE dns_records SET gone_at = ?
          WHERE domain = ? AND name = ? AND type = ? AND value = ? AND gone_at IS NULL',
    );

    foreach ($complete ? $before : [] as $key => $row) {
        if (isset($now[$key])) {
            continue;
        }
        $gone->execute([
            $at,
            $domain,
            $row['name'],
            $row['type'],
            $row['value'],
        ]);
        $changes[] = [
            'change' => 'gone',
            'name' => $row['name'],
            'type' => $row['type'],
            'value' => $row['value'],
            'was' => null,
            'at' => $at,
        ];
    }

    foreach ($changes as $change) {
        $pdo->prepare(
            'INSERT INTO dns_changes (domain, at, change, name, type, value)
             VALUES (?, ?, ?, ?, ?, ?)',
        )->execute([
            $domain,
            $at,
            $change['change'],
            $change['name'],
            $change['type'],
            $change['value'],
        ]);
    }

    return $changes;
}

/**
 * What a domain currently publishes, grouped by what each group is for.
 *
 * Grouped because a zone read as a flat list of forty rows hides the structure
 * that matters: six records are the mail configuration and belong together, and
 * a missing SPF is only obvious next to the MX records it should accompany.
 *
 * @return array<string, mixed>
 */
function dnsInventory(PDO $pdo, string $domain): array
{
    try {
        $stmt = $pdo->prepare(
            'SELECT * FROM dns_records WHERE domain = ? ORDER BY name, type, value',
        );
        $stmt->execute([$domain]);
        $rows = $stmt->fetchAll();
    } catch (Throwable) {
        return ['groups' => [], 'names' => [], 'rows' => []];
    }

    $groups = [];
    foreach ($rows as $row) {
        $group = dnsRecordGroup((string) $row['type'], (string) $row['value']);
        $row['group'] = $group;
        $row['gone'] = $row['gone_at'] !== null;
        $row['detail'] = recordDetail(
            (string) $row['type'],
            (string) $row['value'],
        );
        $groups[$group][] = $row;
    }

    $names = [];
    foreach ($rows as $row) {
        if ($row['gone_at'] !== null) {
            continue;
        }
        $names[(string) $row['name']] = true;
    }

    return [
        'groups' => $groups,
        'names' => array_keys($names),
        'rows' => $rows,
        'group_notes' => dnsGroupNotes(),
    ];
}

/**
 * Which part of a zone's job a record belongs to.
 *
 * TXT records are split by what they actually are, because a zone's TXT records
 * are a grab bag: SPF and DMARC are mail configuration and belong with the MX
 * records, a domain-verification token is nothing to do with mail, and lumping
 * them together makes the mail group unreadable.
 */
function dnsRecordGroup(string $type, string $value): string
{
    $lower = strtolower($value);

    if (in_array($type, ['MX'], true)) {
        return 'mail';
    }
    if ($type === 'TXT') {
        if (
            str_contains($lower, 'v=spf1') ||
            str_contains($lower, 'v=dmarc1') ||
            str_contains($lower, 'domainkey') ||
            str_contains($lower, 'v=dkim1')
        ) {
            return 'mail';
        }
        if (
            preg_match(
                '/(verification|site-verification|_acme|challenge)/',
                $lower,
            )
        ) {
            return 'proof';
        }
        return 'text';
    }
    if (in_array($type, ['NS', 'SOA'], true)) {
        return 'delegation';
    }
    if ($type === 'CAA') {
        return 'certificates';
    }
    if (in_array($type, ['A', 'AAAA', 'CNAME'], true)) {
        return 'addresses';
    }

    return 'other';
}

/** @return array<string, string> */
function dnsGroupNotes(): array
{
    return [
        'addresses' =>
            'Where the names point. A CNAME is a pointer at another name rather ' .
            'than an address, which matters the day its target moves.',
        'mail' =>
            'Everything that decides whether mail arrives: the servers that accept it, ' .
            'and the SPF, DKIM and DMARC records that decide whether anyone believes it came ' .
            'from you. A missing SPF is only obvious next to the MX records it belongs with.',
        'delegation' =>
            'Which nameservers are in charge, and the serial that says when the ' .
            'zone last changed.',
        'certificates' =>
            'CAA records name the authorities allowed to issue certificates for ' .
            'this domain. An unexpected one here is worth asking about.',
        'proof' =>
            'Verification tokens - proving to somebody that you control this domain. ' .
            'Harmless, and they accumulate for ever unless somebody tidies them.',
        'text' => 'Other TXT records.',
        'other' => 'Everything else.',
    ];
}

/**
 * What has changed recently, newest first.
 *
 * @return list<array>
 */
function dnsRecentChanges(PDO $pdo, ?string $domain, int $limit = 100): array
{
    try {
        if ($domain === null) {
            $stmt = $pdo->prepare(
                'SELECT * FROM dns_changes ORDER BY at DESC, id DESC LIMIT ?',
            );
            $stmt->execute([$limit]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT * FROM dns_changes WHERE domain = ? ORDER BY at DESC, id DESC LIMIT ?',
            );
            $stmt->execute([$domain, $limit]);
        }
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/* --------------------------------------------------- explaining a record -- */

/**
 * What a record type is for, in a sentence.
 *
 * Included because a zone read by somebody who does not administer DNS every
 * day is a wall of three-letter names, and the difference between an A and a
 * CNAME is exactly the thing that matters when one of them is wrong.
 *
 * @return array<string, string>
 */
function dnsTypeNotes(): array
{
    return [
        'A' => 'An address. This is where the name actually points, for IPv4.',
        'AAAA' =>
            'The same thing for IPv6. A name can have both, and visitors use whichever ' .
            'their connection supports.',
        'CNAME' =>
            'A pointer at another name rather than at an address. Whatever that name ' .
            'resolves to is what this resolves to, which is convenient until the target ' .
            'moves without anybody remembering this exists.',
        'MX' =>
            'Where mail for this domain should be delivered. The number is a preference: ' .
            'lower is tried first.',
        'NS' =>
            'Which nameservers are authoritative for this zone — the servers that are ' .
            'allowed to answer for it.',
        'SOA' =>
            'The start of authority: who owns the zone, and the serial number that says ' .
            'when it last changed. Secondary servers use the serial to notice updates.',
        'TXT' =>
            'Free text. In practice almost always a machine-readable setting: mail ' .
            'policy, or proof to somebody that you control the domain.',
        'CAA' =>
            'Which certificate authorities are allowed to issue certificates for this ' .
            'domain. Anything unexpected here is worth asking about.',
        'SRV' =>
            'Where a particular service lives, with a port — used by things like chat ' .
            'and directory protocols that need more than an address.',
        'PTR' =>
            'The reverse: an address pointing back at a name. Mail servers check this.',
        'DNSKEY' => 'A DNSSEC signing key.',
        'DS' => 'A pointer to the DNSSEC key, published by the parent zone.',
        'ANY' =>
            'Ask for everything. Most resolvers no longer answer this usefully, which is ' .
            'why this asks for each type in turn instead.',
    ];
}

/**
 * The record types worth offering to look up.
 *
 * @return list<string>
 */
function dnsLookupTypes(): array
{
    return [
        'A',
        'AAAA',
        'CNAME',
        'MX',
        'NS',
        'TXT',
        'SOA',
        'CAA',
        'SRV',
        'PTR',
        'DS',
        'DNSKEY',
    ];
}

/**
 * A TXT record broken into its parts, where it has any.
 *
 * Most TXT records are not text at all: they are a small settings language with
 * no agreed syntax, written as `key=value` pairs separated by semicolons or
 * spaces. Splitting them turns a hundred-character run-on into a list somebody
 * can read, and lets each known key carry a note about what it does.
 *
 * A record that does not look like pairs is returned whole rather than being
 * mangled into halves at the first equals sign it happens to contain.
 *
 * @return array{kind: string, note: string, pairs: list<array{name: string, value: string, note: string}>}
 */
function parseTxtRecord(string $value): array
{
    $text = trim($value, " \t\"");

    // Long records are split across quoted chunks by the protocol; joining them
    // back is what the reader means by "the record".
    $text = (string) preg_replace('/"\s+"/', '', $text);

    $kind = 'text';
    $note = '';

    if (stripos($text, 'v=spf1') === 0) {
        $kind = 'SPF';
        $note =
            'Says which servers are allowed to send mail claiming to be from this domain. ' .
            'The last term decides what happens to everything else: ~all is a soft fail, ' .
            '-all a hard one, ?all no opinion at all.';
    } elseif (stripos($text, 'v=DMARC1') === 0) {
        $kind = 'DMARC';
        $note =
            'Says what a receiving server should do with mail that fails SPF or DKIM, and ' .
            'where to send reports about it.';
    } elseif (stripos($text, 'v=DKIM1') === 0) {
        $kind = 'DKIM';
        $note =
            'A public key used to verify signatures on mail from this domain.';
    } elseif (
        preg_match('/(verification|site-verification|validation)/i', $text)
    ) {
        $kind = 'verification';
        $note =
            'Proof to somebody that you control this domain. Harmless, and they accumulate ' .
            'for ever unless somebody tidies them.';
    }

    /*
     * Split on semicolons first, then on spaces.
     *
     * DMARC and DKIM use semicolons; SPF uses spaces. Trying semicolons first
     * and only falling back keeps an SPF record's `include:` terms intact
     * rather than treating the whole thing as one pair.
     */
    /*
     * Only structured records are taken apart.
     *
     * A genuine free-text record - and they do exist - split on spaces becomes
     * a list of its own words presented as settings, which is confidently
     * wrong. Structure means either a known kind, or something that actually
     * looks like `name=value`.
     */
    $structured =
        $kind !== 'text' ||
        (bool) preg_match('/(^|[\s;])[A-Za-z][A-Za-z0-9_.-]{0,30}=/', $text);

    if (!$structured) {
        return [
            'kind' => $kind,
            'note' => $note,
            'pairs' => [],
            'text' => $text,
        ];
    }

    $parts = str_contains($text, ';')
        ? preg_split('/\s*;\s*/', $text)
        : preg_split('/\s+/', $text);

    $pairs = [];
    foreach ($parts ?: [] as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        /*
         * A pair needs a name-shaped thing before the equals.
         *
         * `v=spf1` is a pair; a base64 key containing an equals as padding is
         * not, and splitting it would produce nonsense with great confidence.
         */
        if (preg_match('/^([A-Za-z][A-Za-z0-9_.-]{0,30})=(.*)$/', $part, $m)) {
            $pairs[] = [
                'name' => $m[1],
                'value' => $m[2],
                'note' => txtKeyNote($kind, $m[1], $m[2]),
            ];
            continue;
        }

        $pairs[] = [
            'name' => '',
            'value' => $part,
            'note' => txtTermNote($kind, $part),
        ];
    }

    return [
        'kind' => $kind,
        'note' => $note,
        'pairs' => $pairs,
        'text' => $text,
    ];
}

/** What one key inside a TXT record means. */
function txtKeyNote(string $kind, string $name, string $value): string
{
    $key = strtolower($name);

    if ($kind === 'DMARC') {
        return match ($key) {
            'v' => 'The version. Always DMARC1.',
            'p' => match (strtolower($value)) {
                'none' => 'Do nothing to failing mail — monitoring only.',
                'quarantine' => 'Failing mail goes to spam.',
                'reject'
                    => 'Failing mail is refused outright. The strictest setting.',
                default => 'What to do with mail that fails.',
            },
            'sp' => 'The same, for subdomains.',
            'rua' => 'Where to send aggregate reports.',
            'ruf' => 'Where to send reports about individual failures.',
            'pct'
                => 'The percentage of failing mail the policy applies to — used to roll ' .
                'a policy out gradually.',
            'adkim'
                => 'How strictly the DKIM domain must match. s is strict, r relaxed.',
            'aspf' => 'The same for SPF.',
            default => '',
        };
    }

    if ($kind === 'DKIM') {
        return match ($key) {
            'v' => 'The version.',
            'k' => 'The key type, usually rsa.',
            'p' => 'The public key itself. An empty one revokes the key.',
            't'
                => 'Flags. y means testing, so failures should not be enforced.',
            default => '',
        };
    }

    if ($kind === 'SPF' && $key === 'v') {
        return 'The version. Always spf1.';
    }

    return '';
}

/** What one bare term inside a TXT record means. */
function txtTermNote(string $kind, string $term): string
{
    if ($kind !== 'SPF') {
        return '';
    }

    if (preg_match('/^([~+?-])?all$/i', $term, $m)) {
        return match ($m[1] ?? '+') {
            '-' => 'Everything not listed above fails. The strict setting.',
            '~'
                => 'Everything not listed above soft-fails — usually treated as suspicious ' .
                'rather than refused.',
            '?'
                => 'No opinion about anything not listed, which is close to having no SPF.',
            default
                => 'Everything is allowed, which defeats the point of the record.',
        };
    }

    if (str_starts_with(strtolower($term), 'include:')) {
        return 'Also allow whatever this domain\'s SPF allows — how a mail provider is ' .
            'authorised.';
    }
    if (
        str_starts_with(strtolower($term), 'ip4:') ||
        str_starts_with(strtolower($term), 'ip6:')
    ) {
        return 'This address may send mail for the domain.';
    }
    /*
     * A qualifier in front changes what a match means, not what it matches.
     *
     * The default is `+`, meaning pass. `-a:mail.example.com` is a mechanism
     * that *denies* - rare, and the sort of thing that gets read straight past
     * because it looks like every other term.
     */
    $bare = $term;
    $qualifier = '';
    if ($term !== '' && str_contains('+-~?', $term[0])) {
        $qualifier = $term[0];
        $bare = substr($term, 1);
    }

    $said = match ($qualifier) {
        '-' => ' Prefixed with -, so a match here fails rather than passes.',
        '~' => ' Prefixed with ~, so a match here soft-fails.',
        '?'
            => ' Prefixed with ?, so a match here is neither a pass nor a fail.',
        default => '',
    };

    $lower = strtolower($bare);

    if ($lower === 'a' || $lower === 'mx') {
        return 'Whatever the domain\'s own ' .
            strtoupper($lower) .
            ' records point at may send ' .
            'mail for it.' .
            $said;
    }
    if (str_starts_with($lower, 'a:')) {
        return 'Whatever ' .
            substr($bare, 2) .
            ' resolves to may send mail for the domain.' .
            $said;
    }
    if (str_starts_with($lower, 'mx:')) {
        return 'Whatever ' .
            substr($bare, 3) .
            '\'s MX records point at may send mail for ' .
            'the domain.' .
            $said;
    }
    if (str_starts_with($lower, 'exists:')) {
        return 'Passes if that name resolves at all. Used for per-sender rules, and rarely ' .
            'by hand.' .
            $said;
    }
    if ($lower === 'ptr' || str_starts_with($lower, 'ptr:')) {
        return 'Checks the sending address\'s reverse name. Deprecated: it is slow, ' .
            'unreliable, and receivers are entitled to ignore it.' .
            $said;
    }
    if (str_starts_with($lower, 'redirect=')) {
        return 'Use another domain\'s SPF record instead of this one. Only takes effect if ' .
            'no term matched, and there must be no `all` term for it to be reached.';
    }
    if (str_starts_with($lower, 'exp=')) {
        return 'Names a TXT record holding the message to return when mail is rejected.';
    }
    if (str_starts_with($lower, 'v=')) {
        return '';
    }

    return $said === '' ? '' : trim($said);
}

/**
 * A domain status word from whois, in plain language.
 *
 * These are EPP status codes and they read like error messages when most of
 * them are the opposite: `clientTransferProhibited` is the registrar lock that
 * stops somebody moving the domain away from you, and seeing it should be
 * reassuring rather than alarming.
 */
function domainStatusPlain(string $status): array
{
    $key = strtolower(trim($status));

    $known = [
        'clienttransferprohibited' => [
            'Locked against transfer',
            'Your registrar will not let the domain be moved elsewhere. This is the normal ' .
            'protective lock and is a good thing to see.',
        ],
        'clientupdateprohibited' => [
            'Locked against changes',
            'Details cannot be edited without unlocking first. Protective.',
        ],
        'clientdeleteprohibited' => [
            'Locked against deletion',
            'The domain cannot be deleted without unlocking first. Protective.',
        ],
        'clientrenewprohibited' => [
            'Renewal blocked by the registrar',
            'Worth asking about — this can prevent renewing.',
        ],
        'clienthold' => [
            'Withdrawn from DNS by the registrar',
            'The domain is registered but will not resolve. Usually a billing or compliance ' .
            'problem, and the site is down while it lasts.',
        ],
        'servertransferprohibited' => [
            'Locked against transfer by the registry',
            'A registry-level lock rather than your registrar’s.',
        ],
        'serverhold' => [
            'Withdrawn from DNS by the registry',
            'The domain will not resolve. This is serious and is not something the registrar ' .
            'set.',
        ],
        'pendingdelete' => [
            'About to be deleted',
            'The registration has lapsed and the registry is releasing it. Act now or lose it.',
        ],
        'redemptionperiod' => [
            'Expired, still recoverable',
            'Recoverable, usually for several times the renewal fee, for a limited time.',
        ],
        'pendingtransfer' => [
            'Being transferred',
            'A transfer to another registrar is in ' . 'progress.',
        ],
        'autorenewperiod' => [
            'Just auto-renewed',
            'The grace period after an automatic renewal.',
        ],
        'inactive' => [
            'No nameservers',
            'The domain has no nameservers set, so it cannot ' . 'resolve.',
        ],
        'ok' => [
            'Nothing unusual',
            'No locks and nothing pending. Note that this also means ' .
            'no transfer lock.',
        ],
        'active' => ['Active', 'Registered and resolving.'],
    ];

    if (isset($known[$key])) {
        return [
            'plain' => $known[$key][0],
            'note' => $known[$key][1],
            'raw' => trim($status),
        ];
    }

    return ['plain' => trim($status), 'note' => '', 'raw' => trim($status)];
}

/**
 * An SOA record as the seven things it actually is.
 *
 * `ns1.example.com. hostmaster.example.com. 2026081501 3600 600 1209600 300`
 * is seven fields with no labels, three of which are durations in seconds and
 * one of which is an email address written in a form that has confused people
 * since 1987: the first dot is an @, because @ could not appear in a DNS name.
 *
 * The timers are worth translating for a reason beyond readability. A large
 * expiry with small refresh is a healthy zone; a minimum of a day means a
 * mistake takes a day to fix everywhere, which is the sort of thing nobody
 * notices until they need it fixed now.
 *
 * @return array{ok: bool, fields: list<array{name: string, value: string, note: string}>}
 */
function parseSoaRecord(string $value): array
{
    $parts = preg_split('/\s+/', trim($value)) ?: [];

    if (count($parts) < 7) {
        return ['ok' => false, 'fields' => []];
    }

    [$primary, $mailbox, $serial, $refresh, $retry, $expire, $minimum] = $parts;

    $fields = [
        [
            'name' => 'Primary nameserver',
            'value' => rtrim($primary, '.'),
            'note' =>
                'The server this zone is edited on. The other nameservers copy from it, ' .
                'so a change made anywhere else is a change that will be overwritten.',
        ],
        [
            'name' => 'Responsible email',
            'value' => soaMailbox($mailbox),
            'note' =>
                'Written as a DNS name, so the first dot stands in for the @ sign. ' .
                'Sent as ' .
                rtrim($mailbox, '.') .
                '.',
        ],
        [
            'name' => 'Serial',
            'value' => $serial,
            'note' => soaSerialNote($serial),
        ],
        [
            'name' => 'Refresh',
            'value' => soaSeconds($refresh),
            'note' =>
                'How often the other nameservers check the primary for a new serial.',
        ],
        [
            'name' => 'Retry',
            'value' => soaSeconds($retry),
            'note' =>
                'How long they wait before trying again after a failed check.',
        ],
        [
            'name' => 'Expire',
            'value' => soaSeconds($expire),
            'note' =>
                'How long a secondary keeps answering when it cannot reach the primary ' .
                'at all. After this it stops answering for the zone, and the domain goes ' .
                'dark rather than serving stale data.',
        ],
        [
            'name' => 'Negative caching',
            'value' => soaSeconds($minimum),
            'note' =>
                'How long other resolvers remember that a name here does not exist. A ' .
                'large value here is why a newly added record can take a while to appear ' .
                'for people who already looked for it.',
        ],
    ];

    return ['ok' => true, 'fields' => $fields];
}

/**
 * The responsible-party mailbox, as an address.
 *
 * The first unescaped dot is the @. A dot that is genuinely part of the local
 * part is written `\.`, which is rare but real - and getting it wrong produces
 * a plausible address that belongs to nobody.
 */
function soaMailbox(string $name): string
{
    $name = rtrim($name, '.');
    $out = '';
    $length = strlen($name);

    for ($i = 0; $i < $length; $i++) {
        if ($name[$i] === '\\' && $i + 1 < $length) {
            $out .= $name[$i + 1];
            $i++;
            continue;
        }
        if ($name[$i] === '.') {
            return $out . '@' . substr($name, $i + 1);
        }
        $out .= $name[$i];
    }

    return $name;
}

/**
 * What a serial number says about itself.
 *
 * The overwhelmingly common convention is YYYYMMDDnn, and when it holds the
 * serial tells you the date of the last edit - which is genuinely useful and
 * invisible as ten digits.
 */
function soaSerialNote(string $serial): string
{
    $plain =
        'The version of the zone. It must increase on every edit, or the other ' .
        'nameservers will not notice the change.';

    if (!preg_match('/^(\d{4})(\d{2})(\d{2})(\d{1,3})$/', $serial, $m)) {
        return $plain;
    }

    [, $year, $month, $day, $revision] = $m;
    if (
        (int) $year < 1990 ||
        (int) $year > (int) date('Y') + 1 ||
        (int) $month < 1 ||
        (int) $month > 12 ||
        (int) $day < 1 ||
        (int) $day > 31
    ) {
        return $plain;
    }

    return sprintf(
        'Follows the usual date convention: last edited %s-%s-%s, revision %d that day. %s',
        $year,
        $month,
        $day,
        (int) $revision,
        $plain,
    );
}

/** A timer field as a number of seconds and as a length of time. */
function soaSeconds(string $raw): string
{
    if (!ctype_digit($raw)) {
        return $raw;
    }

    return $raw . ' (' . headerDuration((int) $raw) . ')';
}

/**
 * The parts a record value breaks into, for the ones that have parts.
 *
 * A table cell can only ever show the beginning of an SPF record, and the
 * interesting half - what happens to mail that does not match, which is the
 * last term - is always the half that gets cut off. So the breakdown travels
 * with the row and the full value is a click away rather than lost.
 *
 * @return array{kind: string, note: string, fields: list<array>}|null
 */
function recordDetail(string $type, string $value): ?array
{
    $type = strtoupper($type);

    if ($type === 'SOA') {
        $soa = parseSoaRecord($value);
        return $soa['ok']
            ? [
                'kind' => 'SOA',
                'note' =>
                    'The zone\'s own settings: who runs it, and how ' .
                    'long everyone else may believe what it says.',
                'fields' => $soa['fields'],
            ]
            : null;
    }

    if ($type !== 'TXT' && $type !== 'SPF') {
        return null;
    }

    $txt = parseTxtRecord($value);
    $fields = [];

    foreach ($txt['pairs'] as $pair) {
        $fields[] = [
            'name' => (string) ($pair['name'] ?? ''),
            'value' => (string) ($pair['value'] ?? ''),
            'note' => (string) ($pair['note'] ?? ''),
        ];
    }

    if ($fields === [] && $txt['note'] === '') {
        return null;
    }

    return [
        'kind' => $txt['kind'],
        'note' => $txt['note'],
        'fields' => $fields,
    ];
}
