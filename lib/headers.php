<?php
declare(strict_types=1);

/*
 * What an HTTP header actually says.
 *
 * A response head is dense, abbreviated and full of numbers with no units. Some
 * of it is standard and some is whatever the CDN in front of the site decided
 * to call things, and almost none of it is readable to somebody who does not
 * already know it. `max-age=31536000` is a year. `dur=596.0` is six tenths of a
 * second. `br` is Brotli. None of that is guessable.
 *
 * Three jobs here:
 *
 *   explain     what a header is for, in a sentence. A short catalogue of the
 *               ones people actually meet, extensible by the operator - a house
 *               header nobody else has documented is exactly the one worth
 *               annotating.
 *   split       several headers carry structured values in their own private
 *               syntax. Set-Cookie is a list of attributes; Link is a list of
 *               URLs with parameters; Server-Timing is a list of measurements.
 *               Read as one long string they are unreadable.
 *   humanise    a duration is a duration. Turning 31536000 into "a year" is the
 *               difference between reading a header and decoding one.
 *
 * Nothing here changes what was received. The raw value is always kept beside
 * the interpretation, because an interpretation that hid its source would make
 * a parsing mistake indistinguishable from a server misconfiguration.
 */

require_once __DIR__ . '/db.php';

/**
 * What each header means, as far as anyone can say generally.
 *
 * Deliberately a short list of the ones that turn up constantly rather than an
 * attempt at every registered header. A note that says "this is the standard
 * caching header" earns its place; one that restates the header's own name does
 * not.
 *
 * Positional - a title and a note - because written as a keyed array the
 * catalogue is twice the length and half as readable. headerNotes() gives them
 * their names.
 *
 * @return array<string, array{0: string, 1: string}>
 */
function headerCatalogue(): array
{
    return [
        'cache-control' => [
            'Caching rules',
            'How long this may be reused, and by whom. `public` allows shared caches, ' .
            '`private` allows only the browser, `no-store` forbids keeping it at all.',
        ],
        'age' => [
            'Time in cache',
            'How long the copy you received has been sitting in a cache. A large age with a ' .
            'small max-age means you are being served something close to stale.',
        ],
        'expires' => [
            'Expiry date',
            'The older way of saying the same thing as max-age. When both are present, ' .
            'Cache-Control wins.',
        ],
        'etag' => [
            'Version tag',
            'An opaque marker for this exact version. Send it back as If-None-Match and an ' .
            'unchanged page answers 304 with no body.',
        ],
        'last-modified' => [
            'When it last changed',
            'The other way of asking "has this changed", using a date rather than a tag.',
        ],
        'content-encoding' => [
            'Compression',
            'How the body was compressed on the wire. It is decompressed before you see it, ' .
            'so this does not change the content.',
        ],
        'content-type' => [
            'What kind of thing this is',
            'The media type, and for text the character set. Browsers take this seriously; ' .
            'getting it wrong is why a page sometimes downloads instead of displaying.',
        ],
        'content-length' => [
            'Size in bytes',
            'The length of the body as sent, after any ' . 'compression.',
        ],
        'set-cookie' => [
            'Sets a cookie',
            'One cookie per header, with its attributes. `HttpOnly` hides it from JavaScript, ' .
            '`Secure` sends it only over HTTPS, and `SameSite` decides whether it travels ' .
            'with requests from other sites.',
        ],
        'link' => [
            'Related URLs',
            'Relationships to other addresses - the API root, alternate formats, a canonical ' .
            'URL - carried in the headers rather than in the page.',
        ],
        'server-timing' => [
            'How long the server took',
            'Measurements the server chose to publish about its own work. Names are the ' .
            'server\'s own; the durations are in milliseconds.',
        ],
        'strict-transport-security' => [
            'HTTPS only, from now on',
            'Tells the browser to refuse plain HTTP for this host for the given period. ' .
            'Powerful and hard to undo: a long max-age set by accident is a long problem.',
        ],
        'content-security-policy' => [
            'What the page may load',
            'Restricts where scripts, styles and images may come from. The main defence ' .
            'against injected script.',
        ],
        'x-frame-options' => [
            'Framing rules',
            'Whether other sites may embed this page. Largely superseded by ' .
            'frame-ancestors in the Content-Security-Policy.',
        ],
        'location' => ['Where it is redirecting to', ''],
        'server' => [
            'What is serving it',
            'Often deliberately vague, and sometimes a lie.',
        ],
        'vary' => [
            'What the cache must key on',
            'Which request headers change the response. A cache that ignores this serves the ' .
            'wrong variant - the compressed copy to a client that cannot decompress it.',
        ],
        'x-ac' => [
            'Cache and routing (Automattic)',
            'A house header from Automattic\'s edge. It names the data centre that served ' .
            'you and whether the cache had a copy - HIT means it did, MISS means it had to ' .
            'ask the origin.',
        ],
        'x-cache' => [
            'Cache result',
            'Whether the edge had a copy already. HIT is served from cache; MISS went to the ' .
            'origin.',
        ],
        'x-served-by' => ['Which edge server answered', ''],
        'cf-cache-status' => [
            'Cache result (Cloudflare)',
            'HIT, MISS, EXPIRED, BYPASS and so on - whether Cloudflare had a usable copy.',
        ],
        'x-powered-by' => [
            'What built the response',
            'Usually worth turning off: it advertises versions to anybody scanning.',
        ],
        'x-robots-tag' => [
            'Indexing rules',
            'Whether search engines may index this.',
        ],
        'retry-after' => [
            'When to try again',
            'Sent with 429 and 503. Either a number of seconds or a date.',
        ],
        'accept-ranges' => [
            'Partial requests',
            'Whether the server supports resuming a ' . 'download part-way.',
        ],
        'alt-svc' => [
            'Other ways to reach this',
            'Advertises an alternative protocol, usually HTTP/3, for future requests.',
        ],
    ];
}

/**
 * The catalogue plus whatever the operator has written.
 *
 * Their notes win. A house header nobody else has documented is exactly the one
 * worth annotating, and if somebody has written down what their own `x-thing`
 * means, that is better information than any general catalogue.
 *
 * @return array<string, array{title: string, note: string, mine: bool}>
 */
function headerNotes(PDO $pdo): array
{
    $out = [];
    foreach (headerCatalogue() as $name => [$title, $note]) {
        $out[$name] = ['title' => $title, 'note' => $note, 'mine' => false];
    }

    try {
        $rows = $pdo
            ->query(
                "SELECT match, label, note FROM labels WHERE kind = 'header'",
            )
            ->fetchAll();
    } catch (Throwable) {
        return $out;
    }

    foreach ($rows as $row) {
        $out[strtolower((string) $row['match'])] = [
            'title' => (string) $row['label'],
            'note' => (string) ($row['note'] ?? ''),
            'mine' => true,
        ];
    }

    return $out;
}

/**
 * One header, taken apart as far as it usefully can be.
 *
 * @return array<string, mixed>
 */
function explainHeader(string $name, string $value, array $notes): array
{
    $key = strtolower(trim($name));
    $note = $notes[$key] ?? null;

    $out = [
        'name' => $name,
        'value' => $value,
        'title' => $note['title'] ?? null,
        'note' => $note['note'] ?? null,
        'mine' => (bool) ($note['mine'] ?? false),
        'parts' => [],
        'kind' => 'plain',
    ];

    switch ($key) {
        case 'set-cookie':
            $out['kind'] = 'cookie';
            $out['parts'] = explainCookie($value);
            break;

        case 'link':
            $out['kind'] = 'links';
            $out['parts'] = explainLinkHeader($value);
            break;

        case 'server-timing':
            $out['kind'] = 'timing';
            $out['parts'] = explainServerTiming($value);
            break;

        case 'cache-control':
            $out['kind'] = 'directives';
            $out['parts'] = explainDirectives($value);
            break;

        case 'strict-transport-security':
            $out['kind'] = 'directives';
            $out['parts'] = explainDirectives($value);
            break;

        case 'content-encoding':
            $out['kind'] = 'encoding';
            $out['parts'] = explainEncoding($value);
            break;

        case 'x-ac':
            $out['kind'] = 'routing';
            $out['parts'] = explainXac($value);
            break;

        case 'expires':
        case 'date':
        case 'last-modified':
            $at = strtotime($value);
            if ($at !== false) {
                $out['kind'] = 'date';
                $out['parts'] = [
                    [
                        'name' => '',
                        'value' => $value,
                        'note' => headerWhen($at),
                    ],
                ];
            }
            break;

        case 'age':
        case 'retry-after':
            if (ctype_digit(trim($value))) {
                $out['kind'] = 'duration';
                $out['parts'] = [
                    [
                        'name' => '',
                        'value' => $value,
                        'note' => headerDuration((int) $value),
                    ],
                ];
            }
            break;
    }

    return $out;
}

/**
 * A number of seconds, said the way a person would.
 *
 * The whole reason half this file exists: `max-age=31536000` is a year, and
 * nobody reads it as one.
 */
function headerDuration(int $seconds): string
{
    if ($seconds <= 0) {
        return 'immediately';
    }

    $units = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];

    foreach ($units as [$name, $size]) {
        if ($seconds >= $size) {
            $count = $seconds / $size;
            $rounded = $count >= 10 ? round($count) : round($count, 1);

            /*
             * Trailing zeros only after a decimal point.
             *
             * Trimming them unconditionally turns 10 minutes into 1 minute,
             * which is the kind of wrong that reads as right.
             */
            $shown = (string) $rounded;
            if (str_contains($shown, '.')) {
                $shown = rtrim(rtrim($shown, '0'), '.');
            }

            return $shown . ' ' . $name . ($rounded == 1.0 ? '' : 's');
        }
    }

    return $seconds . ' second' . ($seconds === 1 ? '' : 's');
}

/** A moment, said as how far away it is. */
function headerWhen(int $at): string
{
    $delta = $at - time();
    if (abs($delta) < 60) {
        return 'now';
    }
    return $delta > 0
        ? 'in ' . headerDuration($delta)
        : headerDuration(-$delta) . ' ago';
}

/**
 * A Set-Cookie value as its name, value and attributes.
 *
 * The first pair is the cookie itself and everything after it is an attribute,
 * which is why this cannot just split on semicolons and treat every pair alike.
 *
 * @return list<array{name: string, value: string, note: string}>
 */
function explainCookie(string $value): array
{
    $parts = preg_split('/;\s*/', trim($value)) ?: [];
    $out = [];

    foreach ($parts as $index => $part) {
        if ($part === '') {
            continue;
        }

        $at = strpos($part, '=');
        $name = $at === false ? $part : substr($part, 0, $at);
        $val = $at === false ? '' : substr($part, $at + 1);

        if ($index === 0) {
            $out[] = [
                'name' => $name,
                'value' => $val,
                'note' =>
                    'The cookie itself: its name and the value being stored.',
            ];
            continue;
        }

        $out[] = [
            'name' => $name,
            'value' => $val,
            'note' => cookieAttributeNote($name, $val),
        ];
    }

    return $out;
}

function cookieAttributeNote(string $name, string $value): string
{
    switch (strtolower(trim($name))) {
        case 'expires':
            $at = strtotime($value);
            return $at === false
                ? 'When the browser should discard it.'
                : 'Discarded ' . headerWhen($at) . '.';

        case 'max-age':
            return ctype_digit(trim($value))
                ? 'Kept for ' .
                        headerDuration((int) $value) .
                        ' — this wins over Expires.'
                : 'How long the browser should keep it.';

        case 'path':
            return 'Sent only for requests under this path.';

        case 'domain':
            return 'Sent to this host and its subdomains, rather than only the exact host.';

        case 'secure':
            return 'Sent only over HTTPS.';

        case 'httponly':
            return 'Hidden from JavaScript, which is the main defence against a stolen ' .
                'session cookie.';

        case 'samesite':
            return match (strtolower(trim($value))) {
                'strict'
                    => 'Never sent with requests coming from another site.',
                'lax'
                    => 'Sent when following a link from another site, but not with ' .
                    'background requests. The usual choice.',
                'none'
                    => 'Sent with requests from any site, which requires Secure.',
                default
                    => 'Whether it travels with requests from other sites.',
            };

        case 'partitioned':
            return 'Kept separately per top-level site, so embedded content cannot track ' .
                'across them.';

        default:
            return '';
    }
}

/**
 * A Link header as its individual links.
 *
 * One header can carry several, comma-separated, each with its own parameters -
 * and the URL is in angle brackets precisely because it may contain commas.
 *
 * @return list<array{name: string, value: string, note: string, url: ?string}>
 */
function explainLinkHeader(string $value): array
{
    $out = [];

    // Split on commas that are not inside angle brackets.
    foreach (preg_split('/,\s*(?=<)/', trim($value)) ?: [] as $link) {
        if (!preg_match('/^<([^>]*)>\s*(.*)$/', trim($link), $m)) {
            continue;
        }

        $url = $m[1];
        $rest = $m[2];
        $attributes = [];

        foreach (preg_split('/;\s*/', $rest) ?: [] as $pair) {
            if ($pair === '' || !str_contains($pair, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $pair, 2);
            $attributes[strtolower(trim($k))] = trim($v, " \"'");
        }

        $rel = $attributes['rel'] ?? '';
        $out[] = [
            'name' => $rel !== '' ? $rel : 'link',
            'value' => $url,
            'url' => $url,
            'note' => linkRelNote($rel, $attributes),
        ];
    }

    return $out;
}

function linkRelNote(string $rel, array $attributes): string
{
    $extra = [];
    if (isset($attributes['title'])) {
        $extra[] = 'called "' . $attributes['title'] . '"';
    }
    if (isset($attributes['type'])) {
        $extra[] = 'as ' . $attributes['type'];
    }
    $tail = $extra === [] ? '' : ' (' . implode(', ', $extra) . ')';

    $known = [
        'canonical' =>
            'The address this page should be known by, for search engines.',
        'alternate' => 'The same content in another form or language.',
        'shortlink' => 'A short address for the same page.',
        'next' => 'The next page in a sequence.',
        'prev' => 'The previous page in a sequence.',
        'preconnect' => 'A hint to open a connection to this host early.',
        'dns-prefetch' => 'A hint to resolve this host early.',
        'https://api.w.org/' => 'The WordPress REST API root for this site.',
    ];

    foreach ($known as $key => $note) {
        if (strcasecmp($rel, $key) === 0) {
            return $note . $tail;
        }
    }

    return $rel === '' ? trim($tail, ' ()') : 'Relationship: ' . $rel . $tail;
}

/**
 * Server-Timing as named measurements.
 *
 * The names are the server's own and mean whatever it decided; the durations
 * are milliseconds by specification, which is the part worth translating.
 *
 * @return list<array{name: string, value: string, note: string}>
 */
function explainServerTiming(string $value): array
{
    $out = [];

    foreach (preg_split('/,\s*/', trim($value)) ?: [] as $entry) {
        $pieces = preg_split('/;\s*/', trim($entry)) ?: [];
        if ($pieces === []) {
            continue;
        }

        $name = array_shift($pieces);
        $description = null;
        $duration = null;

        foreach ($pieces as $piece) {
            if (str_starts_with($piece, 'desc=')) {
                $description = trim(substr($piece, 5), " \"'");
            } elseif (str_starts_with($piece, 'dur=')) {
                $duration = (float) substr($piece, 4);
            }
        }

        $note = [];
        if ($description !== null) {
            $note[] = $description;
        }
        if ($duration !== null) {
            // Milliseconds by specification, which nobody reads as such at
            // three significant figures.
            $note[] =
                $duration >= 1000
                    ? sprintf('took %.2f seconds', $duration / 1000)
                    : sprintf(
                        'took %s ms',
                        rtrim(rtrim(number_format($duration, 1), '0'), '.'),
                    );
        }

        $out[] = [
            'name' => $name,
            'value' =>
                $description ?? ($duration === null ? '' : (string) $duration),
            'note' => implode(' — ', $note),
        ];
    }

    return $out;
}

/**
 * Comma-separated directives, some with values.
 *
 * @return list<array{name: string, value: string, note: string}>
 */
function explainDirectives(string $value): array
{
    $out = [];

    foreach (preg_split('/[,;]\s*/', trim($value)) ?: [] as $directive) {
        $directive = trim($directive);
        if ($directive === '') {
            continue;
        }

        $at = strpos($directive, '=');
        $name = $at === false ? $directive : substr($directive, 0, $at);
        $val = $at === false ? '' : trim(substr($directive, $at + 1), " \"'");

        $out[] = [
            'name' => $name,
            'value' => $val,
            'note' => directiveNote($name, $val),
        ];
    }

    return $out;
}

function directiveNote(string $name, string $value): string
{
    $key = strtolower(trim($name));

    if (
        in_array(
            $key,
            [
                'max-age',
                's-maxage',
                'stale-while-revalidate',
                'stale-if-error',
                'min-fresh',
            ],
            true,
        ) &&
        is_numeric($value)
    ) {
        $said = headerDuration((int) $value);
        return match ($key) {
            'max-age' => "Reusable for {$said}.",
            's-maxage'
                => "Reusable by shared caches for {$said}, overriding max-age for them.",
            'stale-while-revalidate'
                => "May be served stale for {$said} while a fresh copy " .
                'is fetched in the background.',
            'stale-if-error'
                => "May be served stale for {$said} if the origin is failing.",
            default => $said,
        };
    }

    return match ($key) {
        'public' => 'Any cache may keep it, including shared ones.',
        'private' => 'Only the browser may keep it, not a shared cache.',
        'no-cache'
            => 'May be kept, but must be revalidated before reuse. Not the same as ' .
            'no-store.',
        'no-store' => 'Must not be written to any cache at all.',
        'must-revalidate'
            => 'Once stale, it may not be served without checking.',
        'immutable'
            => 'Promises it will never change, so the browser need not revalidate ' .
            'even on reload.',
        'includesubdomains' => 'Applies to every subdomain as well.',
        'preload'
            => 'Requests inclusion in browsers\' built-in HTTPS-only lists. Very hard ' .
            'to undo.',
        default => '',
    };
}

/** What a content encoding actually is. */
function explainEncoding(string $value): array
{
    $known = [
        'br' => [
            'Brotli',
            'Compression developed at Google. Smaller than gzip for text, and ' .
            'supported by every current browser over HTTPS.',
        ],
        'gzip' => ['gzip', 'The long-standing default. Universally supported.'],
        'deflate' => [
            'deflate',
            'Older and rarely used; implementations disagree about it.',
        ],
        'zstd' => ['Zstandard', 'Newer, fast, increasingly supported.'],
        'identity' => ['no compression', 'Sent as-is.'],
    ];

    $out = [];
    foreach (preg_split('/,\s*/', trim($value)) ?: [] as $encoding) {
        $key = strtolower(trim($encoding));
        $entry = $known[$key] ?? [$encoding, ''];
        $out[] = [
            'name' => $encoding,
            'value' => $entry[0],
            'note' => $entry[1],
        ];
    }

    return $out;
}

/**
 * Automattic's x-ac header, which is routing and cache state.
 *
 * Undocumented publicly and confusing on sight: `1.dca _atomic_dca MISS` is a
 * data centre name and a cache result. Read as a sentence it is "served from
 * the Washington DC edge, which did not have a copy".
 */
function explainXac(string $value): array
{
    $pieces = preg_split('/\s+/', trim($value)) ?: [];
    $out = [];

    $centres = [
        'dca' => 'Washington DC',
        'dfw' => 'Dallas',
        'sjc' => 'San Jose',
        'ams' => 'Amsterdam',
        'lhr' => 'London',
        'nrt' => 'Tokyo',
        'bur' => 'Los Angeles',
        'ord' => 'Chicago',
        'cdg' => 'Paris',
        'sin' => 'Singapore',
        'syd' => 'Sydney',
        'fra' => 'Frankfurt',
    ];

    foreach ($pieces as $piece) {
        $upper = strtoupper($piece);

        if (
            in_array(
                $upper,
                ['HIT', 'MISS', 'BYPASS', 'EXPIRED', 'STALE', 'UPDATING'],
                true,
            )
        ) {
            $out[] = [
                'name' => 'cache',
                'value' => $upper,
                'note' => match ($upper) {
                    'HIT'
                        => 'The edge already had a copy and served it without asking the ' .
                        'origin. Fast, and the origin never saw this request.',
                    'MISS'
                        => 'The edge had no usable copy, so it fetched from the origin. ' .
                        'Normal for a first request or an uncacheable page.',
                    'BYPASS'
                        => 'Caching was deliberately skipped for this request.',
                    'EXPIRED' => 'A copy existed but was too old to use.',
                    'STALE'
                        => 'An old copy was served while a fresh one was fetched.',
                    default => 'The cache was updating this entry.',
                },
            ];
            continue;
        }

        $found = null;
        foreach ($centres as $code => $city) {
            if (str_contains(strtolower($piece), $code)) {
                $found = $city;
                break;
            }
        }

        $out[] = [
            'name' => 'route',
            'value' => $piece,
            'note' =>
                $found === null
                    ? 'Part of the edge routing path.'
                    : 'Served from the ' . $found . ' edge.',
        ];
    }

    return $out;
}
