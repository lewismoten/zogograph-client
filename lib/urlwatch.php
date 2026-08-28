<?php
declare(strict_types=1);

/*
 * Asking a URL whether it is still working, on a schedule.
 *
 * Everything else here watches the machine from the inside, which cannot answer
 * the only question that matters to whoever is visiting: does the site respond?
 * A server can be healthy by every internal measure while the site returns 500
 * because a config file is wrong, or return 200 with an empty page because a
 * database is down. Nothing in /proc knows.
 *
 * Three things are recorded per check, and each catches a different failure:
 *
 *   the status code   the blunt one - 500, 502, 404, or nothing at all
 *   the response time a site that answers in nine seconds is failing even
 *                     though every request technically succeeds
 *   a value from the body   the subtle one. A page that returns 200 with the
 *                     wrong content is the failure mode nothing else sees, and
 *                     extracting a number from it turns "is it up" into "is it
 *                     right" - a queue depth, a record count, a version.
 *
 * This runs from the account's cron, not root's: it is an outbound HTTP request
 * and needs no privilege. That also means it is checking from where this
 * machine sits, which is worth remembering - it cannot tell you the site is
 * unreachable from elsewhere.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/outbound.php';

/** How long to wait for a response before calling it a failure. */
const URLWATCH_TIMEOUT = 15;

/** The largest body this will read, so a huge download cannot stall the cron. */
const URLWATCH_MAX_BYTES = 2 * 1024 * 1024;

/** How many checks one install may have. */
const URLWATCH_MAX = 60;

/**
 * The intervals on offer.
 *
 * A fixed list because each one is a promise about how often the cron can
 * actually deliver: a one-minute check on an account whose cron runs every five
 * is a lie, and the interface says so rather than silently under-delivering.
 *
 * @return array<int, string>
 */
function urlWatchIntervals(): array
{
    return [
        60 => 'every minute',
        300 => 'every 5 minutes',
        600 => 'every 10 minutes',
        1800 => 'every 30 minutes',
        3600 => 'hourly',
        14400 => 'every 4 hours',
        86400 => 'daily',
        604800 => 'weekly',
    ];
}

/**
 * The ways a check can look at a response body.
 *
 * `none` is the common case and is listed rather than implied, because "just
 * check it answers" is a legitimate choice and should not look like a setting
 * somebody forgot to fill in.
 *
 * @return array<string, array{label: string, hint: string, needs_value: bool, extracts: bool}>
 */
function urlWatchChecks(): array
{
    return [
        'none' => [
            'label' => 'Nothing — just the status code',
            'hint' => 'Whether it answers, and how quickly.',
            'needs_value' => false,
            'extracts' => false,
        ],
        'contains' => [
            'label' => 'The body contains this text',
            'hint' => 'The simplest guard against a page that returns 200 and is empty, or '
                . 'has been replaced by a holding page.',
            'needs_value' => true,
            'extracts' => false,
        ],
        'missing' => [
            'label' => 'The body does NOT contain this text',
            'hint' => 'For catching something that should never appear — a stack trace, a '
                . '"database error", the word "suspended".',
            'needs_value' => true,
            'extracts' => false,
        ],
        'regex' => [
            'label' => 'A regular expression matches',
            'hint' => 'Records what one bracketed group matches, every run. The text is '
                . 'compared against last time so a change can be noticed, and charted as a '
                . 'number where it is one. Try it against real text first — a pattern '
                . 'that captures the wrong group draws a chart of a constant, and nothing '
                . 'about that looks like a mistake.',
            'needs_value' => true,
            'extracts' => true,
        ],
        'json' => [
            'label' => 'A value from JSON',
            'hint' => 'A dotted path such as <code>data.queue.depth</code>. Array positions '
                . 'are numbers: <code>items.0.count</code>.',
            'needs_value' => true,
            'extracts' => true,
        ],
        'meta' => [
            'label' => 'An HTML meta tag',
            'hint' => 'The name of a meta tag; its content is recorded. Useful because a page '
                . 'can publish a build number or a record count without showing it.',
            'needs_value' => true,
            'extracts' => true,
        ],
        'xpath' => [
            'label' => 'An XPath expression',
            'hint' => 'For XML and well-formed HTML. The first matching node\'s text is '
                . 'recorded.',
            'needs_value' => true,
            'extracts' => true,
        ],
    ];
}

/**
 * Every check, with when each is next due.
 *
 * @return list<array>
 */
function urlWatchList(PDO $pdo): array
{
    try {
        $rows = $pdo->query('SELECT * FROM url_checks ORDER BY label, url')->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $now = time();
    foreach ($rows as &$row) {
        $row['enabled'] = (bool)$row['enabled'];
        $row['interval_label'] = urlWatchIntervals()[(int)$row['interval_seconds']]
            ?? sprintf('every %d seconds', (int)$row['interval_seconds']);

        $last = (int)($row['last_at'] ?? 0);
        $row['due_at'] = $last === 0 ? $now : $last + (int)$row['interval_seconds'];
        $row['overdue'] = $row['enabled'] && $row['due_at'] < $now - 60;
    }
    unset($row);

    return $rows;
}

/**
 * Runs every check that is due, and returns what happened.
 *
 * Due-ness is decided from the last recorded run rather than from a schedule
 * this process keeps: the cron may not have run for an hour, and when it does
 * the right thing is to catch up once, not to pretend the missed runs happened.
 *
 * @return array{ran: int, failed: int, rows: list<array>}
 */
function urlWatchRunDue(
    PDO $pdo,
    ?int $only = null,
    ?callable $fetcher = null,
    array $config = []
): array {
    $now = time();
    $ran = 0;
    $failed = 0;
    $rows = [];

    foreach (urlWatchList($pdo) as $check) {
        if ($only !== null) {
            if ((int)$check['id'] !== $only) {
                continue;
            }
        } elseif (!$check['enabled'] || $check['due_at'] > $now) {
            continue;
        }

        $result = urlWatchRunOne($check, $fetcher, $config);

        /*
         * Noticed before it is stored, because storing overwrites last_text.
         *
         * The comparison is against what the check remembered, and the store
         * is what moves that memory forward - so the order here is the whole
         * mechanism rather than a matter of taste.
         */
        urlWatchNoteChange($pdo, $config, $check, $result);
        urlWatchStore($pdo, (int)$check['id'], $result);

        $ran++;
        if (!$result['ok']) {
            $failed++;
        }
        $rows[] = ['id' => (int)$check['id'], 'label' => $check['label']] + $result;
    }

    return ['ran' => $ran, 'failed' => $failed, 'rows' => $rows];
}

/**
 * One request, and everything measured about it.
 *
 * @return array<string, mixed>
 */
function urlWatchRunOne(array $check, ?callable $fetcher = null, array $config = []): array
{
    $started = microtime(true);
    $fetched = $fetcher === null
        ? urlWatchFetch((string)$check['url'], [
            'follow_redirects' => (bool)($check['follow_redirects'] ?? true),
            'method' => (string)($check['method'] ?? 'GET'),
            // Only when asked, and only with something to send: a conditional
            // request with no ETag is just an ordinary one with extra words.
            'etag' => !empty($check['send_etag']) ? ($check['last_etag'] ?? null) : null,
        ], $config)
        : $fetcher((string)$check['url']);

    $result = [
        'at' => time(),
        'status' => $fetched['status'],
        'ms' => (int)round((microtime(true) - $started) * 1000),
        'bytes' => strlen((string)$fetched['body']),
        'value' => null,
        'matched' => null,
        'ok' => false,
        'error' => $fetched['error'],
        'hops' => (int)($fetched['hops'] ?? 0),
        'landed' => $fetched['landed'] ?? null,
        'etag' => $fetched['etag'] ?? null,
        'headers' => $fetched['headers'] ?? '',
    ];

    if ($fetched['error'] !== null) {
        return $result;
    }

    /*
     * The expected status, which is not always 200.
     *
     * A URL that should redirect, or an endpoint that correctly answers 401 to
     * an unauthenticated request, is working exactly as intended - and a monitor
     * that insisted on 200 would report it as broken for ever, which is how a
     * monitor gets ignored.
     */
    $expect = (int)($check['expect_status'] ?? 200);
    $statusOk = $expect === 0
        ? $fetched['status'] >= 200 && $fetched['status'] < 400
        : $fetched['status'] === $expect;

    /*
     * A 304 has no body, so a body rule cannot be applied to it.
     *
     * This is not a failure: a conditional request answered "unchanged" is the
     * server behaving correctly and is often the thing being tested. Running
     * the content rule against an empty body would report it as broken.
     */
    if ($fetched['status'] === 304) {
        $result['ok'] = $statusOk || $expect === 200;
        $result['matched'] = null;
        $result['error'] = $result['ok']
            ? null
            : sprintf('answered 304, expected %d', $expect);
        return $result;
    }

    $inspection = urlWatchInspect(
        (string)($check['check_kind'] ?? 'none'),
        (string)($check['check_value'] ?? ''),
        (string)$fetched['body'],
        max(0, (int)($check['capture_group'] ?? 1))
    );

    $result['matched'] = $inspection['matched'];
    $result['value'] = $inspection['value'];
    $result['text'] = $inspection['text'] ?? null;
    $result['ok'] = $statusOk && $inspection['matched'] !== false;

    /*
     * Whether what was captured is different from last time.
     *
     * Compared against what was stored rather than against the previous row,
     * so a check that failed for an hour and came back does not report every
     * intervening blank as a change.
     */
    $was = $check['last_text'] ?? null;
    $result['changed'] = $result['text'] !== null && $was !== null && $result['text'] !== $was;
    $result['was'] = $was;

    if (!$statusOk) {
        $result['error'] = sprintf(
            'answered %d, expected %s',
            $fetched['status'],
            $expect === 0 ? 'any success' : (string)$expect
        );
    } elseif ($inspection['matched'] === false) {
        $result['error'] = $inspection['why'];
    }

    return $result;
}

/**
 * Looks at a body according to one rule.
 *
 * `matched` is null when the rule is "none" - nothing was asked, so nothing
 * passed or failed. That is deliberately distinct from false: a check with no
 * body rule must not be reported as having satisfied one.
 *
 * `$group` is which capturing group a regex should take. Usually 1, because a
 * pattern nearly always needs context around the part that matters - matching
 * `index\.css\?v=(\d+\.\d+\.\d+)` only works because of the bits that are not
 * the version. Zero means the whole match.
 *
 * @return array{matched: ?bool, value: ?float, text: ?string, why: string}
 */
function urlWatchInspect(string $kind, string $needle, string $body, int $group = 1): array
{
    $none = ['matched' => null, 'value' => null, 'text' => null, 'why' => ''];

    if ($kind === 'none' || $needle === '') {
        return $none;
    }

    switch ($kind) {
        case 'contains':
            $found = str_contains($body, $needle);
            return ['matched' => $found,
                    'why' => $found ? '' : 'the body does not contain that text'] + $none;

        case 'missing':
            $found = str_contains($body, $needle);
            return ['matched' => !$found,
                    'why' => $found ? 'the body contains text it should not' : ''] + $none;

        case 'regex':
            /*
             * Delimited here rather than by whoever typed it.
             *
             * A pattern from a form arriving with its own delimiters could
             * carry modifiers - `/e` is gone but `/x` and friends still change
             * meaning - and letting the input choose them is letting the input
             * choose the language. The `u` modifier is ours: bodies are text.
             */
            $pattern = '/' . str_replace('/', '\\/', $needle) . '/u';
            $found = @preg_match($pattern, $body, $m);

            if ($found === false) {
                return ['matched' => false,
                        'why' => 'that is not a valid regular expression'] + $none;
            }
            if ($found === 0) {
                return ['matched' => false, 'why' => 'the pattern did not match'] + $none;
            }

            if (!array_key_exists($group, $m)) {
                return [
                    'matched' => false,
                    'why' => sprintf(
                        'the pattern matched, but it has no group %d - it has %d',
                        $group,
                        count($m) - 1
                    ),
                ] + $none;
            }

            /*
             * The text as well as the number.
             *
             * A version string is the thing people most want to watch and it
             * is not a number - `2.14.1` parses to 2.14 and loses the part
             * that changed. So the text is what is compared for changes and
             * the number is only for the chart, where it is often nothing.
             */
            $captured = (string)$m[$group];
            return [
                'matched' => true,
                'value' => urlWatchNumber($captured),
                'text' => $captured,
                'why' => '',
            ];

        case 'json':
            $data = json_decode($body, true);
            if (!is_array($data)) {
                return ['matched' => false,
                        'why' => 'the response is not JSON'] + $none;
            }
            $found = urlWatchDig($data, $needle);
            if ($found === null) {
                return ['matched' => false,
                        'why' => 'there is nothing at that path'] + $none;
            }
            return [
                'matched' => true,
                'value' => urlWatchNumber($found),
                'text' => is_scalar($found) ? (string)$found : json_encode($found),
                'why' => '',
            ];

        case 'meta':
            $quoted = preg_quote($needle, '/');
            if (preg_match(
                '/<meta[^>]+name\s*=\s*["\']' . $quoted . '["\'][^>]*content\s*=\s*["\']([^"\']*)/i',
                $body,
                $m
            ) || preg_match(
                '/<meta[^>]+content\s*=\s*["\']([^"\']*)["\'][^>]*name\s*=\s*["\']' . $quoted . '["\']/i',
                $body,
                $m
            )) {
                return [
                    'matched' => true,
                    'value' => urlWatchNumber($m[1]),
                    'text' => (string)$m[1],
                    'why' => '',
                ];
            }
            return ['matched' => false, 'why' => 'no meta tag by that name'] + $none;

        case 'xpath':
            return urlWatchXpath($body, $needle);
    }

    return $none;
}

/**
 * An XPath lookup, with the parser's complaints kept to itself.
 *
 * Real HTML is full of things libxml objects to, and a monitor that reported
 * every unclosed tag as a failure would report every page as failing. Errors
 * are collected and discarded; only whether a node was found decides the result.
 */
function urlWatchXpath(string $body, string $expression): array
{
    // Its own defaults: this is a separate function, and $none belongs to the
    // caller's scope.
    $none = ['matched' => null, 'value' => null, 'text' => null, 'why' => ''];

    if (!class_exists('DOMDocument')) {
        return ['matched' => false,
                'why' => 'this PHP has no DOM extension, so XPath cannot be used'] + $none;
    }

    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument();

    $loaded = str_contains(ltrim($body), '<?xml')
        ? @$document->loadXML($body)
        : @$document->loadHTML($body);

    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return ['matched' => false, 'why' => 'the response could not be parsed'] + $none;
    }

    $xpath = new DOMXPath($document);
    $nodes = @$xpath->query($expression);

    if ($nodes === false) {
        return ['matched' => false, 'why' => 'that is not a valid XPath expression'] + $none;
    }
    if ($nodes->length === 0) {
        return ['matched' => false, 'why' => 'nothing matched that expression'] + $none;
    }

    $text = trim((string)$nodes->item(0)?->textContent);
    return ['matched' => true, 'value' => urlWatchNumber($text), 'text' => $text, 'why' => ''];
}

/**
 * A dotted path into decoded JSON.
 *
 * Array positions are plain numbers, so `items.0.count` works - which is what
 * anybody writing one of these expects, and avoids inventing a syntax.
 */
function urlWatchDig(array $data, string $path): mixed
{
    $cursor = $data;
    foreach (explode('.', $path) as $part) {
        if (!is_array($cursor) || !array_key_exists($part, $cursor)) {
            return null;
        }
        $cursor = $cursor[$part];
    }
    return is_scalar($cursor) ? $cursor : null;
}

/**
 * Whatever was extracted, as a number if it can be one.
 *
 * The point of extracting a value is to chart it, and a chart needs numbers.
 * Something that is not one is not an error - a version string is a perfectly
 * good thing to watch for *changes* in - so it is kept as text alongside and
 * only the numeric form is charted.
 */
function urlWatchNumber(mixed $value): ?float
{
    if ($value === null) {
        return null;
    }

    $text = trim((string)$value);
    // Digit grouping and a trailing unit are common in the things worth
    // watching: "1,240 records", "98%".
    $text = str_replace(',', '', $text);

    return preg_match('/-?\d+(?:\.\d+)?/', $text, $m) ? (float)$m[0] : null;
}

/**
 * Fetches a URL, without following redirects into somewhere else entirely.
 *
 * @return array{status: int, body: string, error: ?string}
 */
function urlWatchFetch(string $url, array $how = [], array $config = []): array
{
    try {
        $endpoint = urlWatchEndpointPolicy($url, $config);
    } catch (ActionRefused $e) {
        return ['status' => 0, 'body' => '', 'headers' => '', 'error' => $e->getMessage(),
            'hops' => 0, 'landed' => null, 'etag' => null, 'method' => 'GET'];
    }
    $parts = parse_url($url);
    if ($parts === false || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
        return ['status' => 0, 'body' => '', 'error' => 'that is not an http or https URL'];
    }

    $follow = (bool)($how['follow_redirects'] ?? true);
    $method = strtoupper((string)($how['method'] ?? 'GET'));
    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        $method = 'GET';
    }

    if (function_exists('curl_init')) {
        $requested = $endpoint['url'];
        $hops = 0;
        $requestMethod = $method;
        while (true) {
            $reply = urlWatchCurlOnce($requested, $endpoint, $requestMethod, $how['etag'] ?? null);
            $location = urlWatchHeader($reply['headers'], 'location');
            $redirect = $follow && in_array($reply['status'], [301, 302, 303, 307, 308], true)
                && $location !== null && $reply['error'] === null;
            if (!$redirect) {
                return $reply + ['hops' => $hops,
                    'landed' => $requested === $url ? null : $requested,
                    'etag' => urlWatchHeader($reply['headers'], 'etag'), 'method' => $requestMethod];
            }
            if ($hops >= 5) {
                return array_replace($reply, ['hops' => $hops, 'landed' => $requested,
                    'etag' => null, 'method' => $requestMethod, 'error' => 'too many redirects']);
            }
            try {
                $requested = urlWatchRedirectUrl($requested, $location);
                $endpoint = urlWatchEndpointPolicy($requested, $config);
            } catch (ActionRefused $e) {
                return array_replace($reply, ['hops' => $hops, 'landed' => $requested,
                    'etag' => null, 'method' => $requestMethod, 'error' => $e->getMessage()]);
            }
            $hops++;
            if ($reply['status'] === 303) {
                $requestMethod = 'GET';
            }
        }
    }

    return ['status' => 0, 'body' => '', 'headers' => '',
        'error' => 'safe URL checks require PHP cURL on this host', 'hops' => 0,
        'landed' => null, 'etag' => null, 'method' => $method];
}

/** One pinned HTTP request; redirect policy belongs to urlWatchFetch. */
function urlWatchCurlOnce(string $url, array $endpoint, string $method, ?string $etag): array
{
    $handle = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => URLWATCH_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_RESOLVE => $endpoint['resolve'], CURLOPT_USERAGENT => 'HostScope/1.0 (+uptime check)',
        CURLOPT_ENCODING => '', CURLOPT_HEADER => true, CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    if ($method === 'HEAD') $options[CURLOPT_NOBODY] = true;
    elseif ($method !== 'GET') $options[CURLOPT_CUSTOMREQUEST] = $method;
    if (is_string($etag) && $etag !== '') $options[CURLOPT_HTTPHEADER] = ['If-None-Match: ' . $etag];
    curl_setopt_array($handle, $options);
    $raw = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $size = (int)curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $error = curl_errno($handle) === 0 ? null : curl_error($handle);
    $headers = is_string($raw) ? substr($raw, 0, $size) : '';
    return ['status' => $status, 'body' => is_string($raw) ? substr($raw, $size, URLWATCH_MAX_BYTES) : '',
        'headers' => $headers, 'error' => $error];
}

/** Resolves a Location header without accepting credentials or fragments. */
function urlWatchRedirectUrl(string $from, string $location): string
{
    $location = trim($location);
    if ($location === '') throw new ActionRefused('redirect had no destination');
    $target = parse_url($location);
    if ($target !== false && isset($target['scheme'])) return $location;
    $base = parse_url($from);
    if ($base === false || !isset($base['scheme'], $base['host'])) throw new ActionRefused('redirect source was invalid');
    $origin = $base['scheme'] . '://' . (str_contains((string)$base['host'], ':') ? '[' . $base['host'] . ']' : $base['host'])
        . (isset($base['port']) ? ':' . $base['port'] : '');
    if (str_starts_with($location, '//')) return $base['scheme'] . ':' . $location;
    if (str_starts_with($location, '/')) return $origin . $location;
    if (str_starts_with($location, '?')) return $origin . ($base['path'] ?? '/') . $location;
    $path = rtrim(str_replace('\\', '/', dirname((string)($base['path'] ?? '/'))), '/') . '/' . $location;
    return $origin . '/' . ltrim($path, '/');
}

/**
 * Validates and pins a URL-watch target. Private services require an exact
 * administrator-configured hostname, so a saved public check cannot turn into
 * an SSRF request if its DNS record changes.
 *
 * @return array{url: string, resolve: list<string>}
 */
function urlWatchEndpointPolicy(string $url, array $config = []): array
{
    $url = trim($url);
    $parts = parse_url($url);
    if ($url === '' || $parts === false || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true)
        || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
        throw new ActionRefused('that is not a plain http or https URL');
    }
    $host = strtolower(trim((string)$parts['host'], '[]'));
    $addresses = outboundHostAddresses($host);
    if ($addresses === []) {
        throw new ActionRefused('that URL host did not resolve');
    }
    $allowed = array_map(fn(mixed $value): string => strtolower(trim((string)$value)),
        (array)($config['url_watch']['private_hosts'] ?? []));
    $private = array_filter($addresses, fn(string $address): bool => !outboundPublicAddress($address));
    if ($private !== [] && !in_array($host, $allowed, true)) {
        throw new ActionRefused('private URL targets must be named in url_watch.private_hosts');
    }
    $scheme = strtolower((string)$parts['scheme']);
    $port = (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
    return ['url' => $url, 'resolve' => array_map(
        fn(string $address): string => $host . ':' . $port . ':' . $address, $addresses
    )];
}

/** One header out of a raw response head, case-insensitively. */
function urlWatchHeader(string $headers, string $name): ?string
{
    $pattern = '/^' . preg_quote($name, '/') . '\s*:\s*(.+?)\s*$/im';
    return preg_match($pattern, $headers, $m) ? trim($m[1]) : null;
}

function urlWatchStore(PDO $pdo, int $id, array $result): void
{
    $pdo->prepare(
        'INSERT INTO url_results
            (check_id, at, status, ms, bytes, value, text_value, matched, ok, error,
             redirected_to, hops)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $id, $result['at'], $result['status'], $result['ms'], $result['bytes'],
        $result['value'],
        $result['text'] ?? null,
        $result['matched'] === null ? null : ($result['matched'] ? 1 : 0),
        $result['ok'] ? 1 : 0,
        $result['error'],
        $result['landed'] ?? null,
        (int)($result['hops'] ?? 0),
    ]);

    /*
     * The ETag is remembered so the next conditional request has one to send.
     *
     * Only worth keeping when the check asked for it; storing one otherwise
     * would quietly change what the next request looks like.
     */
    /*
     * last_text only moves when something was actually captured.
     *
     * COALESCE rather than a plain assignment: a check that failed to match
     * this once should not forget what it saw last time, or the next
     * successful run reports a change that is really just the gap closing.
     */
    $pdo->prepare(
        'UPDATE url_checks SET last_at = ?, last_ok = ?, last_status = ?, last_error = ?,
                last_etag = COALESCE(?, last_etag),
                last_text = COALESCE(?, last_text)
          WHERE id = ?'
    )->execute([
        $result['at'], $result['ok'] ? 1 : 0, $result['status'], $result['error'],
        $result['etag'] ?? null, $result['text'] ?? null, $id,
    ]);
}

/**
 * Saves a check, validating everything the runner will later rely on.
 *
 * A bad regular expression stored now is a failed check every minute for ever,
 * reported as the site being down - so patterns are compiled here, once, where
 * the person who typed it is still looking at the screen.
 */
function urlWatchSave(PDO $pdo, array $input, array $config = []): int
{
    $url = trim((string)($input['url'] ?? ''));
    $policy = urlWatchEndpointPolicy($url, $config);
    $url = $policy['url'];
    $parts = parse_url($url);

    $interval = (int)($input['interval_seconds'] ?? 300);
    if (!isset(urlWatchIntervals()[$interval])) {
        throw new ActionRefused('that is not one of the intervals on offer');
    }

    $kind = (string)($input['check_kind'] ?? 'none');
    $kinds = urlWatchChecks();
    if (!isset($kinds[$kind])) {
        throw new ActionRefused('that is not one of the checks on offer');
    }

    $value = trim((string)($input['check_value'] ?? ''));
    if ($kinds[$kind]['needs_value'] && $value === '') {
        throw new ActionRefused('that check needs something to look for');
    }

    if ($kind === 'regex' && @preg_match('/' . str_replace('/', '\\/', $value) . '/u', '') === false) {
        throw new ActionRefused('that regular expression will not compile');
    }

    /*
     * Which capturing group to keep.
     *
     * Nearly always 1, because a pattern needs context around the part that
     * matters - `index\.css\?v=(\d+\.\d+\.\d+)` only finds the version
     * because of the bits that are not the version. Zero means the whole match.
     */
    $group = max(0, (int)($input['capture_group'] ?? 1));
    if ($group > 20) {
        throw new ActionRefused('a pattern with more than twenty groups is a pattern to split up');
    }

    $expect = (int)($input['expect_status'] ?? 200);
    if ($expect !== 0 && ($expect < 100 || $expect > 599)) {
        throw new ActionRefused('that is not a status code');
    }

    $id = (int)($input['id'] ?? 0);
    $label = trim((string)($input['label'] ?? '')) ?: ($parts['host'] . ($parts['path'] ?? ''));

    $method = strtoupper((string)($input['method'] ?? 'GET'));
    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        throw new ActionRefused('that is not a method this will send');
    }

    /*
     * A body rule needs a body, and HEAD has none by definition.
     *
     * Refused rather than silently ignored: a check configured to look for text
     * in a response that can never contain any would fail every run, and be
     * reported as the site being broken.
     */
    if ($method === 'HEAD' && $kind !== 'none') {
        throw new ActionRefused(
            'a HEAD request has no body, so there is nothing for a content check to look at. '
            . 'Use GET, or set the check to "just the status code".'
        );
    }

    $follow = empty($input['follow_redirects']) ? 0 : 1;
    $etag = empty($input['send_etag']) ? 0 : 1;

    if ($id > 0) {
        $pdo->prepare(
            'UPDATE url_checks SET url = ?, label = ?, interval_seconds = ?, expect_status = ?,
                    check_kind = ?, check_value = ?, enabled = ?, follow_redirects = ?,
                    method = ?, send_etag = ?, capture_group = ?, notify_change = ?
              WHERE id = ?'
        )->execute([
            $url, $label, $interval, $expect, $kind, $value,
            empty($input['enabled']) ? 0 : 1, $follow, $method, $etag,
            $group, empty($input['notify_change']) ? 0 : 1, $id,
        ]);
        return $id;
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM url_checks')->fetchColumn();
    if ($count >= URLWATCH_MAX) {
        throw new ActionRefused('that is as many checks as this will run');
    }

    $pdo->prepare(
        'INSERT INTO url_checks
            (url, label, interval_seconds, expect_status, check_kind, check_value, enabled,
             follow_redirects, method, send_etag, capture_group, notify_change, added_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $url, $label, $interval, $expect, $kind, $value,
        empty($input['enabled']) ? 0 : 1, $follow, $method, $etag,
        $group, empty($input['notify_change']) ? 0 : 1, time(),
    ]);

    return (int)$pdo->lastInsertId();
}

/**
 * One check's history, for the charts.
 *
 * Three series from one query, because they are three views of the same request
 * and must describe the same moments - fetching them separately would let the
 * status line and the timing line disagree about when something happened.
 */
function urlWatchHistory(PDO $pdo, int $id, int $from, int $to): array
{
    $stmt = $pdo->prepare(
        'SELECT at, status, ms, value, matched, ok, error FROM url_results
          WHERE check_id = ? AND at BETWEEN ? AND ? ORDER BY at'
    );
    $stmt->execute([$id, $from, $to]);
    $rows = $stmt->fetchAll();

    $ok = 0;
    $slowest = 0;
    $total = 0;
    foreach ($rows as $row) {
        if ((int)$row['ok'] === 1) {
            $ok++;
        }
        $slowest = max($slowest, (int)$row['ms']);
        $total += (int)$row['ms'];
    }

    return [
        'rows' => $rows,
        'checks' => count($rows),
        'ok' => $ok,
        'uptime_percent' => $rows === [] ? null : round(($ok / count($rows)) * 100, 2),
        'average_ms' => $rows === [] ? null : (int)round($total / count($rows)),
        'slowest_ms' => $slowest,
        // Whether the extracted value is worth charting at all.
        'has_value' => (bool)array_filter($rows, fn(array $r): bool => $r['value'] !== null),
    ];
}

/**
 * Raises an alert when a watched value changes.
 *
 * The point of watching a version string is not its value but the moment it
 * moves: "gitea went from 1.22.0 to 1.23.1 at 04:12" is the sentence somebody
 * wants three days later when something has been subtly wrong since Tuesday.
 * A number changing is ordinary; this is only for checks that asked.
 */
function urlWatchNoteChange(PDO $pdo, array $config, array $check, array $result): void
{
    if (!($check['notify_change'] ?? false) || empty($result['changed'])) {
        return;
    }

    recordAlert($pdo, $config['alerts'] ?? [], [
        /*
         * The check's id is part of the rule name, so each has its own cooldown.
         *
         * The cooldown is keyed on rule and path together, and these alerts
         * carry no path - so one rule name would mean a version change on one
         * check silently suppressing a version change on another for the next
         * six hours. Following the same shape as projected_full:<field>.
         */
        'rule' => 'url_value_changed:' . (int)$check['id'],
        'severity' => 'warning',
        'subject' => sprintf('%s changed what it reports', $check['label']),
        'detail' => sprintf(
            'Was %s, now %s. Watched with %s on %s.',
            $result['was'] === null || $result['was'] === '' ? 'blank' : $result['was'],
            $result['text'],
            $check['check_kind'],
            $check['url']
        ),
        'path_id' => null,
    ]);
}

/**
 * Every moment a watched value changed, for marking on a chart.
 *
 * Worked out by reading the history in order rather than by storing a flag,
 * so that history imported or repaired after the fact still lines up - and so
 * that turning the notification on does not retroactively invent markers that
 * were never raised.
 *
 * @return array{first: ?array{at: int, value: string},
 *               changes: list<array{at: int, from: ?string, to: string}>}
 */
function urlWatchChanges(PDO $pdo, int $id, int $since, int $limit = 200): array
{
    $stmt = $pdo->prepare(
        'SELECT at, text_value FROM url_results
          WHERE check_id = ? AND at >= ? AND text_value IS NOT NULL
          ORDER BY at'
    );
    $stmt->execute([$id, $since]);

    $out = [];
    $previous = null;
    $first = null;

    foreach ($stmt->fetchAll() as $row) {
        $text = (string)$row['text_value'];

        /*
         * The first value in a window is not a change.
         *
         * It is where the window starts, and marking it on the chart would put
         * a line on every chart at its left edge. It IS worth reporting though
         * - "it has said 1.22.0 since Tuesday and then became 1.23.1" needs
         * both halves - so it comes back separately rather than not at all.
         */
        if ($previous === null) {
            $first = ['at' => (int)$row['at'], 'value' => $text];
        } elseif ($text !== $previous) {
            $out[] = ['at' => (int)$row['at'], 'from' => $previous, 'to' => $text];
        }

        $previous = $text;
    }

    return [
        'first' => $first,
        'changes' => count($out) > $limit ? array_slice($out, -$limit) : $out,
    ];
}

/**
 * Tries a pattern against some text and says exactly what it found.
 *
 * The point is to fail here rather than in production. A pattern that looks
 * right and quietly captures the wrong group produces a chart of a constant,
 * and the only way to know is to have tried it against real text first.
 *
 * Every match is returned, not just the first, because "it matched" and "it
 * matched the one I meant" are different questions when a page mentions four
 * stylesheets.
 *
 * @return array<string, mixed>
 */
function urlWatchTryRegex(string $pattern, string $text, int $limit = 50): array
{
    if (trim($pattern) === '') {
        return ['ok' => false, 'error' => 'no pattern given', 'matches' => []];
    }

    // Delimited here, exactly as the real check does, so what is tested is
    // what will run.
    $delimited = '/' . str_replace('/', '\\/', $pattern) . '/u';

    /*
     * PREG_UNMATCHED_AS_NULL, or an optional group that did not take part
     * simply vanishes from the end of the set.
     *
     * Which is exactly the case somebody is testing for. `(a)(z)?` against "a"
     * would otherwise report one group, and the person reading it would
     * conclude their pattern has one - and then set the capture group to the
     * wrong number.
     */
    $flags = PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL;

    /*
     * The compiler's actual complaint, not preg_last_error_msg().
     *
     * A pattern that fails to compile sets PREG_INTERNAL_ERROR, whose message
     * is the useless "Internal error". The warning PHP emits says where the
     * problem is - "missing closing parenthesis at offset 4" - which is the
     * whole point of a tester.
     */
    $complaint = null;
    set_error_handler(function (int $level, string $message) use (&$complaint): bool {
        $complaint = (string)preg_replace('/^preg_match_all\\(\\)[^:]*:\\s*/', '', $message);
        return true;
    });
    $found = preg_match_all($delimited, $text, $all, $flags);
    restore_error_handler();

    if ($found === false) {
        return [
            'ok' => false,
            'error' => $complaint === null
                ? 'that is not a valid regular expression'
                : 'that is not a valid regular expression: ' . $complaint,
            'matches' => [],
        ];
    }

    $matches = [];
    foreach (array_slice($all, 0, $limit) as $set) {
        $groups = [];
        foreach ($set as $index => $part) {
            // Named groups arrive under both the name and the number; only the
            // numbered ones are what a capture-group setting refers to.
            if (!is_int($index)) {
                continue;
            }
            $groups[] = [
                'n' => $index,
                'text' => $part[0] === null ? null : (string)$part[0],
                'at' => (int)$part[1],
                // -1 means the group is in the pattern but took no part in
                // this match, which is different from matching an empty string.
                'matched' => $part[0] !== null,
            ];
        }
        $matches[] = ['groups' => $groups, 'at' => (int)$set[0][1]];
    }

    return [
        'ok' => true,
        'error' => null,
        'matches' => $matches,
        'total' => $found,
        'truncated' => $found > $limit,
        // How many capturing groups the pattern has, whether or not they took
        // part in any particular match.
        'groups' => $matches === [] ? 0 : count($matches[0]['groups']) - 1,
    ];
}
