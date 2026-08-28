<?php
declare(strict_types=1);

/*
 * Domain names, and the date nobody remembers until it has passed.
 *
 * A domain expiring is the one outage on this whole site that no amount of
 * server monitoring can see coming. Disk, memory, CPU and HTTP checks all stay
 * green right up to the moment the name stops resolving, and then everything
 * fails at once for a reason that is nowhere in any log. It is also the only
 * failure here that is trivially preventable and permanently expensive to get
 * wrong.
 *
 * The source is whois, which is a protocol with no schema. Every registry
 * answers in its own layout, in its own words, with its own date format, and
 * several of them rate-limit. So the parsing is deliberately forgiving and
 * deliberately honest: a date it cannot read is reported as unread rather than
 * guessed at, because a wrong expiry date is worse than none - it is a promise
 * that the thing is fine.
 *
 * Checked weekly by default. A registration does not change between Tuesdays,
 * and whois servers throttle anybody who asks more often than that.
 */

require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/network.php';
// The outbound budget: whois registries defend themselves, so this drains.
require_once __DIR__ . '/external.php';
// domainStatusPlain lives with the other DNS wording.
require_once __DIR__ . '/dnsrecords.php';

/** How long to let a whois lookup run before giving up on it. */
const WHOIS_TIMEOUT = 20;

/** How much of a whois answer to keep, for showing verbatim. */
const WHOIS_MAX_BYTES = 20000;

/** How many domains one install may watch. */
const DOMAIN_MAX = 200;

/** Default: start warning this many days before expiry. */
const DOMAIN_WARN_DAYS = 45;

/**
 * Every watched domain, with how long each has left.
 *
 * @return list<array>
 */
function domainList(PDO $pdo): array
{
    try {
        $rows = $pdo->query('SELECT * FROM domains ORDER BY expires_at IS NULL, expires_at, name')
            ->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $now = time();
    foreach ($rows as &$row) {
        $row['days_left'] = $row['expires_at'] === null
            ? null
            : (int)floor(((int)$row['expires_at'] - $now) / 86400);

        // Seconds as well as days, so the browser can say "9y 4m" rather than
        // "3411 days" without doing the arithmetic back the other way.
        $row['seconds_left'] = $row['expires_at'] === null
            ? null
            : (int)$row['expires_at'] - $now;

        $row['state'] = domainState($row);
        $row['status_plain'] = statusList((string)($row['status'] ?? ''));
        $row['due_at'] = (int)($row['last_checked_at'] ?? 0) + (int)$row['interval_seconds'];
        $row['auto_renew'] = $row['auto_renew'] === null ? null : (bool)$row['auto_renew'];
    }
    unset($row);

    return $rows;
}

/**
 * One word for where a domain stands.
 *
 * `unknown` is a state of its own and sorts as a problem rather than as fine.
 * A domain whose expiry could not be read is not a domain that is safe; it is a
 * domain nobody has checked, and treating those as green is exactly how one
 * gets missed.
 */
function domainState(array $row): string
{
    if ($row['expires_at'] === null) {
        return ($row['last_error'] ?? '') !== '' ? 'error' : 'unknown';
    }

    $days = (int)floor(((int)$row['expires_at'] - time()) / 86400);
    $warn = (int)($row['warn_days'] ?? DOMAIN_WARN_DAYS);

    if ($days < 0) {
        return 'expired';
    }
    if ($days <= 7) {
        return 'urgent';
    }
    if ($days <= $warn) {
        return 'soon';
    }
    return 'ok';
}

function domainStateNote(string $state): string
{
    return match ($state) {
        'expired' => 'The registration has lapsed. Depending on the registry there is usually '
            . 'a grace period, then a redemption period that costs several times the renewal '
            . 'fee, and then it is gone.',
        'urgent' => 'Within a week. Renew it now rather than remembering to.',
        'soon' => 'Inside the warning window. Nothing is wrong yet.',
        'ok' => 'Registered well beyond the warning window.',
        'unknown' => 'The expiry date could not be read from the whois answer. That is not '
            . 'the same as being fine — nobody has confirmed this one.',
        'error' => 'The lookup itself failed, so nothing is known about this domain.',
        default => '',
    };
}

/**
 * Looks one domain up and stores what came back.
 *
 * @return array<string, mixed>
 */
function domainCheck(PDO $pdo, array $domain, ?callable $runner = null, array $config = []): array
{
    $name = (string)$domain['name'];
    $answer = whoisLookup($name, $runner);
    // A caller supplying a WHOIS runner is a deterministic test or import,
    // not a request to open an unrelated live HTTPS connection.
    $certificate = $runner === null
        ? domainCertificate($name)
        : ['expires_at' => null, 'error' => null];

    /*
     * When whois is missing here, use whatever root's collector found.
     *
     * A cage frequently has no whois binary while the host does, and reporting
     * "not installed" for every domain for ever is not a monitoring outcome.
     * The request is left by the caller - see domainCheckDue - because leaving
     * one per domain from in here would queue a hundred tasks on a first run.
     */
    if (!$answer['ok'] && $config !== []
        && str_contains((string)$answer['error'], 'not installed')) {
        $fromHost = domainHostAnswer($config, $name);
        if ($fromHost !== null && $fromHost['ok']) {
            $answer = ['ok' => true, 'text' => (string)$fromHost['text'], 'error' => null];
        }
    }

    $parsed = $answer['ok']
        ? parseWhois($answer['text'])
        : ['expires_at' => null, 'registrar' => null, 'status' => null,
           'nameservers' => null, 'created_at' => null, 'updated_at' => null];

    $pdo->prepare(
        'UPDATE domains SET last_checked_at = ?, expires_at = ?, registrar = ?, status = ?,
                nameservers = ?, created_at = ?, updated_at = ?, auto_renew = ?,
                last_error = ?, whois = ?, certificate_expires_at = COALESCE(?, certificate_expires_at), certificate_checked_at = ?,
                certificate_error = ?
          WHERE id = ?'
    )->execute([
        time(),
        $parsed['expires_at'],
        $parsed['registrar'],
        $parsed['status'],
        $parsed['nameservers'],
        $parsed['created_at'],
        $parsed['updated_at'],
        $parsed['auto_renew'] ?? null,
        $answer['ok'] ? null : $answer['error'],
        $answer['ok'] ? substr($answer['text'], 0, WHOIS_MAX_BYTES) : null,
        $certificate['expires_at'],
        time(),
        $certificate['error'],
        (int)$domain['id'],
    ]);

    return $parsed + [
        'ok' => $answer['ok'],
        'error' => $answer['error'],
        'name' => $name,
    ];
}

/**
 * Reads a leaf HTTPS certificate's expiry alongside the weekly domain check.
 *
 * This is inventory, not HTTPS health verification: the URL monitor remains
 * strict. Accepting an invalid chain here lets an already-expired certificate
 * report the date the calendar needs to show.
 *
 * @return array{expires_at: ?int, error: ?string}
 */
function domainCertificate(string $name): array
{
    if (!function_exists('openssl_x509_parse')) {
        return ['expires_at' => null, 'error' => 'the PHP OpenSSL extension is unavailable'];
    }

    $context = stream_context_create(['ssl' => [
        'capture_peer_cert' => true,
        'peer_name' => $name,
        'SNI_enabled' => true,
        'SNI_server_name' => $name,
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]]);
    $errno = 0;
    $error = '';
    $socket = @stream_socket_client('ssl://' . $name . ':443', $errno, $error, 8,
        STREAM_CLIENT_CONNECT, $context);
    if ($socket === false) {
        return ['expires_at' => null, 'error' => $error !== '' ? $error : 'HTTPS did not answer'];
    }

    $params = stream_context_get_params($socket);
    fclose($socket);
    $cert = $params['options']['ssl']['peer_certificate'] ?? null;
    $parsed = $cert === null ? false : @openssl_x509_parse($cert);
    $expires = $parsed['validTo_time_t'] ?? null;
    if (!is_int($expires) && !is_numeric($expires)) {
        return ['expires_at' => null, 'error' => 'the certificate did not contain an expiry date'];
    }

    return ['expires_at' => (int)$expires, 'error' => null];
}

/**
 * Runs every domain check that is due.
 *
 * One at a time with a pause between, because whois servers rate-limit and a
 * burst of twenty lookups is how an install gets temporarily blocked - which
 * would then be reported as twenty domains in an unknown state.
 *
 * @return array{ran: int, failed: int, rows: list<array>}
 */
function domainCheckDue(PDO $pdo, ?int $only = null, ?callable $runner = null, array $config = []): array
{
    $now = time();
    $ran = 0;
    $failed = 0;
    $rows = [];

    /*
     * A budget per run, and the oldest request first.
     *
     * whois registries rate-limit, and a blocked install reports every domain
     * as unknown - one self-inflicted problem that looks like twenty real ones.
     * Draining oldest-first also means one impatient person cannot starve the
     * queue by asking about the same domain repeatedly.
     *
     * A check asked for by hand bypasses the budget: it is one lookup, somebody
     * is waiting for it, and the queue is for the unattended runs.
     */
    $budget = $only === null ? externalBudget($config, 'whois') : PHP_INT_MAX;

    $due = array_values(array_filter(
        domainList($pdo),
        function (array $domain) use ($only, $now): bool {
            if ($only !== null) {
                return (int)$domain['id'] === $only;
            }
            return $domain['due_at'] <= $now;
        }
    ));

    usort($due, fn(array $a, array $b): int =>
        (int)($a['last_checked_at'] ?? 0) <=> (int)($b['last_checked_at'] ?? 0));

    $waiting = max(0, count($due) - $budget);
    $askedHost = 0;

    foreach (array_slice($due, 0, $budget) as $domain) {

        if ($ran > 0 && $runner === null) {
            // Politeness, and self-preservation: a burst gets an install
            // blocked, and a blocked install reports every domain as unknown.
            sleep(2);
        }

        $result = domainCheck($pdo, $domain, $runner, $config);

        if (!$result['ok'] && $config !== [] && $askedHost < 3
            && str_contains((string)$result['error'], 'not installed')) {
            domainAskHost($config, $pdo, (string)$domain['name']);
            $askedHost++;
        }

        $ran++;
        if (!$result['ok']) {
            $failed++;
        }
        $rows[] = $result;
    }

    return [
        'ran' => $ran,
        'failed' => $failed,
        'rows' => $rows,
        // Said, so a run that answered one of twelve does not read as a run
        // that found only one thing to do.
        'waiting' => $waiting,
        'budget' => $budget === PHP_INT_MAX ? null : $budget,
    ];
}

/** The host collector's stored whois answer for a name, if it has one. */
function domainHostAnswer(array $config, string $name): ?array
{
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        return null;
    }
    return $host === null ? null : hostAnswer($host, 'whois', $name);
}

/** Leaves a request for the collector to look this name up. */
function domainAskHost(array $config, PDO $pdo, string $name): void
{
    try {
        requestHostTask($pdo, hostAskTask('whois', $name), 'domain check');
    } catch (Throwable) {
        // The request is a convenience; failing to leave one is not a reason to
        // fail the check that was already going to report "not installed".
    }
}

/**
 * The raw whois answer for a name.
 *
 * @return array{ok: bool, text: string, error: ?string}
 */
function whoisLookup(string $name, ?callable $runner = null): array
{
    if (!preg_match('/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.(?!-)[A-Za-z0-9-]{1,63}(?<!-))+$/', $name)) {
        return ['ok' => false, 'text' => '', 'error' => 'that is not a domain name'];
    }

    if ($runner !== null) {
        $text = (string)$runner($name);
        return ['ok' => trim($text) !== '', 'text' => $text, 'error' => null];
    }

    $whois = whichBinary('whois');
    if ($whois === null) {
        return [
            'ok' => false,
            'text' => '',
            'error' => 'the whois command is not installed here. On Red Hat it is in the '
                . '"whois" package; on Debian, "whois". Without it this cannot ask a registry '
                . 'anything.',
        ];
    }

    $timeout = whichBinary('timeout');
    $command = sprintf(
        '%s%s %s 2>&1',
        $timeout === null ? '' : escapeshellarg($timeout) . ' ' . WHOIS_TIMEOUT . ' ',
        escapeshellarg($whois),
        escapeshellarg($name)
    );

    $text = shellRead($command);
    if ($text === null || trim($text) === '') {
        return ['ok' => false, 'text' => '', 'error' => 'the whois server did not answer'];
    }

    /*
     * Rate limiting looks like an answer, and must not be stored as one.
     *
     * Several registries reply with a perfectly well-formed record whose only
     * content is a complaint about how often you have asked. Parsed naively
     * that produces a domain with no expiry date, which then reads as "could
     * not be determined" rather than "ask again in an hour".
     */
    foreach (['rate limit', 'exceeded', 'try again later', 'too many requests',
              'quota exceeded'] as $needle) {
        if (stripos($text, $needle) !== false && strlen($text) < 600) {
            return [
                'ok' => false,
                'text' => $text,
                'error' => 'the whois server is rate-limiting this address. It will be tried '
                    . 'again on the next run.',
            ];
        }
    }

    if (preg_match('/^\s*(no match|not found|no data found|no entries found)/im', $text)) {
        return [
            'ok' => true,
            'text' => $text,
            'error' => null,
        ];
    }

    return ['ok' => true, 'text' => $text, 'error' => null];
}

/**
 * Pulls what can be pulled out of a whois answer.
 *
 * whois has no schema. Every registry uses its own field names, its own date
 * format and its own idea of what to include, so this matches a list of the
 * labels actually used in the wild rather than pretending there is a standard.
 *
 * Anything not recognised is left null. A wrong expiry date is worse than no
 * date at all: no date says "nobody has checked this", and a wrong one says
 * "this is fine" about a domain that is not.
 *
 * @return array<string, mixed>
 */
function parseWhois(string $text): array
{
    $out = [
        'expires_at' => null, 'created_at' => null, 'updated_at' => null,
        'registrar' => null, 'status' => null, 'nameservers' => null,
        'auto_renew' => null, 'available' => false,
    ];

    if (preg_match('/^\s*(no match|not found|no data found|no entries found)/im', $text)) {
        // The registry has never heard of it, which for somebody checking
        // availability is the answer they wanted rather than a failure.
        $out['available'] = true;
        return $out;
    }

    $field = function (array $labels) use ($text): ?string {
        foreach ($labels as $label) {
            $pattern = '/^\s*' . preg_quote($label, '/') . '\s*:\s*(.+?)\s*$/im';
            if (preg_match($pattern, $text, $m) && trim($m[1]) !== '') {
                return trim($m[1]);
            }
        }
        return null;
    };

    $out['expires_at'] = whoisDate($field([
        'Registry Expiry Date', 'Registrar Registration Expiration Date',
        'Expiration Date', 'Expiry Date', 'Expiry date', 'expires',
        'Domain Expiration Date', 'paid-till', 'renewal date', 'Expires On',
        'Expiration Time', 'validity',
    ]));

    $out['created_at'] = whoisDate($field([
        'Creation Date', 'Created On', 'created', 'Domain Registration Date',
        'Registered on', 'registered',
    ]));

    $out['updated_at'] = whoisDate($field([
        'Updated Date', 'Last Modified', 'last-update', 'changed', 'Last Updated On',
    ]));

    $out['registrar'] = $field(['Registrar', 'Sponsoring Registrar', 'registrar name']);

    /*
     * Every status line, not just the first.
     *
     * The interesting ones are the locks - clientTransferProhibited is normal
     * and reassuring, pendingDelete is an emergency - and a domain routinely
     * carries several at once.
     */
    if (preg_match_all('/^\s*(?:Domain )?Status\s*:\s*(.+?)\s*$/im', $text, $m)) {
        $statuses = array_values(array_unique(array_map(
            // The EPP URL after each status is documentation, not information.
            fn(string $s): string => trim(preg_replace('#https?://\S+#', '', $s) ?? $s),
            $m[1]
        )));
        $out['status'] = implode(', ', array_filter($statuses));
    }

    if (preg_match_all('/^\s*(?:Name Server|nserver|Nameservers?)\s*:\s*(.+?)\s*$/im', $text, $m)) {
        $servers = array_values(array_unique(array_map(
            fn(string $s): string => strtolower(trim(explode(' ', trim($s))[0])),
            $m[1]
        )));
        sort($servers);
        $out['nameservers'] = implode(' ', $servers);
    }

    if (preg_match('/auto[- ]?renew\s*:?\s*(yes|no|true|false|enabled|disabled)/i', $text, $m)) {
        $out['auto_renew'] = in_array(strtolower($m[1]), ['yes', 'true', 'enabled'], true) ? 1 : 0;
    }

    return $out;
}

/**
 * A whois date in whatever shape the registry felt like.
 *
 * The formats in the wild include ISO with and without a zone, `dd-MMM-yyyy`,
 * `yyyy.mm.dd`, and several with a trailing timezone name strtotime does not
 * know. Anything that will not parse returns null - see the note above about
 * why a guess would be worse.
 */
function whoisDate(?string $value): ?int
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    // Trailing timezone names strtotime chokes on, and trailing notes.
    $value = (string)preg_replace('/\s*\((.*?)\)\s*$/', '', $value);
    $value = (string)preg_replace('/\s+(CLST|CLT|MSK|JST|EET|EEST)$/i', '', $value);

    // yyyy.mm.dd, used by a few European registries.
    if (preg_match('/^(\d{4})\.(\d{2})\.(\d{2})/', $value, $m)) {
        $value = "{$m[1]}-{$m[2]}-{$m[3]}";
    }

    $at = strtotime($value);
    if ($at === false) {
        return null;
    }

    /*
     * A sanity window, because a misparse is silent.
     *
     * `strtotime` will happily turn a registrar's phone number into a date. A
     * registration expiring before the web existed or a century from now is a
     * parsing accident, not a fact, and passing it on would put an alarming or
     * a reassuring lie on the page.
     */
    if ($at < strtotime('1990-01-01') || $at > time() + 100 * 365 * 86400) {
        return null;
    }

    return $at;
}

/**
 * What every registry asks you to agree to before it will answer.
 *
 * Registries publish a version of this with their answers, in legal English at
 * a length nobody reads. The substance is short and worth actually knowing,
 * because both prohibitions are things somebody could do by accident with a
 * tool like this one - which is exactly why the throttle exists.
 *
 * Shown once and remembered per browser: a notice on every lookup is a notice
 * nobody reads, and this is a statement of terms rather than a safety warning
 * that needs repeating.
 */
function whoisTerms(): array
{
    return [
        'summary' => 'Registries give this data out for looking up domain registrations, '
            . 'and nothing else. They do not guarantee it is accurate.',
        'points' => [
            'Do not use it to send bulk or unsolicited mail to the addresses in it.',
            'Do not use it to run high-volume automated queries against a registry. This '
                . 'dashboard looks up a small number per collector run for that reason.',
            'Registries can change these terms, and can refuse to answer anyone who ignores '
                . 'them.',
        ],
    ];
}

function domainSave(PDO $pdo, string $name, int $warnDays, int $interval): int
{
    $name = strtolower(trim($name));
    // A URL pasted instead of a name is the common slip, and is recoverable.
    $name = (string)preg_replace('#^https?://#', '', $name);
    $name = explode('/', $name)[0];

    if (!preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/', $name)) {
        throw new ActionRefused('that is not a domain name');
    }

    if ($warnDays < 1 || $warnDays > 365) {
        throw new ActionRefused('the warning window must be between 1 and 365 days');
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM domains')->fetchColumn();
    $exists = $pdo->prepare('SELECT id FROM domains WHERE name = ?');
    $exists->execute([$name]);
    $id = $exists->fetchColumn();

    if ($id !== false) {
        $pdo->prepare('UPDATE domains SET warn_days = ?, interval_seconds = ? WHERE id = ?')
            ->execute([$warnDays, $interval, (int)$id]);
        return (int)$id;
    }

    if ($count >= DOMAIN_MAX) {
        throw new ActionRefused('that is as many domains as this will watch');
    }

    $pdo->prepare(
        'INSERT INTO domains (name, warn_days, interval_seconds, added_at) VALUES (?, ?, ?, ?)'
    )->execute([$name, $warnDays, $interval, time()]);

    return (int)$pdo->lastInsertId();
}

/**
 * Domains worth raising an alert about.
 *
 * Kept separate from the list so the alerting code and the page cannot disagree
 * about what counts as urgent.
 *
 * @return list<array>
 */
function domainsNeedingAttention(PDO $pdo): array
{
    return array_values(array_filter(
        domainList($pdo),
        fn(array $row): bool => in_array($row['state'], ['expired', 'urgent', 'soon'], true)
    ));
}

/**
 * A whois status field as a list of distinct, readable states.
 *
 * Registries are inconsistent about case, and some list the same code twice
 * with different capitalisation - `clientTransferProhibited` beside
 * `clienttransferprohibited`. Translated separately they became two identical
 * rows saying the same thing, which reads as though the domain is locked
 * against transfer in two different ways.
 *
 * So the list is keyed on the meaning rather than on the spelling. The raw
 * codes are kept, all of them, because "what did the registry actually send"
 * is the question the moment a translation looks wrong.
 *
 * @return list<array{plain: string, note: string, raw: string, seen: list<string>}>
 */
function statusList(string $field): array
{
    $out = [];

    foreach (preg_split('/[,\n]+/', $field) ?: [] as $code) {
        $code = trim($code);
        if ($code === '') {
            continue;
        }

        /*
         * Registries append a URL explaining the code, which is not part of it.
         *
         * `clientTransferProhibited https://icann.org/epp#clientTransferProhibited`
         * arrives as one field from several registries, and left alone it makes
         * every such code unique and untranslatable.
         */
        $code = (string)preg_replace('#\s+https?://\S+$#i', '', $code);

        $said = domainStatusPlain($code);
        $key = strtolower($said['plain']);

        if (isset($out[$key])) {
            // Same meaning, different spelling. Keep the spelling, drop the row.
            if (!in_array($code, $out[$key]['seen'], true)) {
                $out[$key]['seen'][] = $code;
            }
            continue;
        }

        $said['seen'] = [$code];
        $out[$key] = $said;
    }

    return array_values($out);
}
