<?php
declare(strict_types=1);

/*
 * Pulling the useful parts out of a page that came back.
 *
 * A fetched response is one long string. Three things in it are worth reading
 * on their own:
 *
 *   the title and meta tags   what the page claims to be, and what it tells
 *                             search engines and social networks to say about it
 *   application/ld+json       structured data, which is where a site publishes
 *                             machine-readable facts about itself
 *   JSON bodies               unreadable as one line, ordinary as formatted text
 *
 * Parsed with DOMDocument rather than regular expressions, because real HTML is
 * full of unquoted attributes, stray `<`, and tags nobody closed. Libxml's HTML
 * parser is built to survive exactly that; a regex meets its first `content='a
 * "b"'` and gets it wrong silently.
 */

/**
 * The title and every meta tag, with a note on the ones that matter.
 *
 * @return array{title: ?string, tags: list<array>, error: ?string}
 */
function pageMeta(string $html): array
{
    $doc = loadHtml($html);
    if ($doc === null) {
        return [
            'title' => null,
            'tags' => [],
            'error' => 'that does not parse as HTML',
        ];
    }

    $title = null;
    $titles = $doc->getElementsByTagName('title');
    if ($titles->length > 0) {
        $title = trim($titles->item(0)->textContent);
    }

    $tags = [];
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        /*
         * Three different attributes name a meta tag.
         *
         * `name` is the standard one, `property` is Open Graph's, and
         * `http-equiv` is a header pretending to be markup. A view that only
         * read `name` would show a third of the tags on a typical page.
         */
        foreach (['name', 'property', 'http-equiv', 'charset'] as $attribute) {
            if (!$meta->hasAttribute($attribute)) {
                continue;
            }

            $key =
                $attribute === 'charset'
                    ? 'charset'
                    : trim($meta->getAttribute($attribute));
            $value =
                $attribute === 'charset'
                    ? $meta->getAttribute('charset')
                    : $meta->getAttribute('content');

            $tags[] = [
                'group' => metaGroup($key),
                'key' => $key,
                'value' => trim($value),
                'via' => $attribute,
                'note' => metaNote($key),
            ];
            break;
        }
    }

    // Grouped so Open Graph does not sit interleaved with the viewport.
    $order = [
        'Page' => 0,
        'Search engines' => 1,
        'Open Graph' => 2,
        'Twitter' => 3,
        'Technical' => 4,
        'Other' => 5,
    ];
    usort(
        $tags,
        fn(array $a, array $b): int => [
            $order[$a['group']] ?? 9,
            $a['key'],
        ] <=> [$order[$b['group']] ?? 9, $b['key']],
    );

    return ['title' => $title, 'tags' => $tags, 'error' => null];
}

function metaGroup(string $key): string
{
    $key = strtolower($key);

    if (
        str_starts_with($key, 'og:') ||
        str_starts_with($key, 'article:') ||
        str_starts_with($key, 'fb:')
    ) {
        return 'Open Graph';
    }
    if (str_starts_with($key, 'twitter:')) {
        return 'Twitter';
    }
    if (
        in_array(
            $key,
            ['description', 'keywords', 'robots', 'googlebot', 'canonical'],
            true,
        )
    ) {
        return 'Search engines';
    }
    if (
        in_array(
            $key,
            [
                'charset',
                'content-type',
                'viewport',
                'refresh',
                'x-ua-compatible',
                'content-security-policy',
            ],
            true,
        )
    ) {
        return 'Technical';
    }
    if (
        in_array(
            $key,
            ['title', 'author', 'generator', 'application-name', 'theme-color'],
            true,
        )
    ) {
        return 'Page';
    }

    return 'Other';
}

function metaNote(string $key): string
{
    return match (strtolower($key)) {
        'description'
            => 'The sentence search engines usually show under the title.',
        'keywords' => 'Ignored by every major search engine since about 2009.',
        'robots'
            => 'Whether search engines may index this page and follow its links.',
        'viewport'
            => 'How the page should scale on a phone. Without it, mobile browsers ' .
            'render at desktop width and zoom out.',
        'charset'
            => 'The character encoding. Wrong here is where mojibake comes from.',
        'generator'
            => 'What built the page. Often names software and version to anyone asking.',
        'theme-color' => 'Tints the browser chrome on mobile.',
        'og:title'
            => 'The headline used when the page is shared on social networks.',
        'og:description' => 'The summary used when shared.',
        'og:image'
            => 'The picture used when shared. Its absence is why some links look bare.',
        'og:url'
            => 'The address a share should point at, regardless of how you arrived.',
        'og:type'
            => 'What kind of thing this page is - article, website, product.',
        'og:site_name' => 'The name of the site as a whole.',
        'twitter:card' => 'Which layout X/Twitter should use for the preview.',
        'refresh'
            => 'Redirects or reloads after a delay. A relic, and bad for accessibility.',
        'x-ua-compatible'
            => 'A message to versions of Internet Explorer that no longer exist.',
        default => '',
    };
}

/**
 * Every application/ld+json block, decoded.
 *
 * A page may carry several, and one block may hold an array or a `@graph` of
 * many things, so this returns a flat list of the entities rather than the
 * blocks - which is how anyone reading it thinks about it.
 *
 * @return array{blocks: list<array>, error: ?string}
 */
function pageLinkedData(string $html): array
{
    $doc = loadHtml($html);
    if ($doc === null) {
        return ['blocks' => [], 'error' => 'that does not parse as HTML'];
    }

    $blocks = [];
    foreach ($doc->getElementsByTagName('script') as $script) {
        $type = strtolower(trim($script->getAttribute('type')));
        if ($type !== 'application/ld+json') {
            continue;
        }

        $raw = trim($script->textContent);
        $data = json_decode($raw, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $blocks[] = [
                'type' => null,
                'name' => null,
                'error' =>
                    'this block is not valid JSON: ' . json_last_error_msg(),
                'json' => $raw,
            ];
            continue;
        }

        foreach (flattenLinkedData($data) as $entity) {
            $blocks[] = [
                'type' => linkedDataType($entity),
                'name' => is_array($entity)
                    ? (is_string($entity['name'] ?? null)
                        ? $entity['name']
                        : null)
                    : null,
                'error' => null,
                'json' => json_encode(
                    $entity,
                    JSON_PRETTY_PRINT |
                        JSON_UNESCAPED_SLASHES |
                        JSON_UNESCAPED_UNICODE,
                ),
            ];
        }
    }

    return ['blocks' => $blocks, 'error' => null];
}

/**
 * One block's worth of JSON-LD as a list of the things it describes.
 *
 * @return list<mixed>
 */
function flattenLinkedData(mixed $data): array
{
    if (!is_array($data)) {
        return [$data];
    }

    // A top-level array is several entities.
    if (array_is_list($data)) {
        $out = [];
        foreach ($data as $item) {
            $out = array_merge($out, flattenLinkedData($item));
        }
        return $out;
    }

    // `@graph` is the conventional way to carry several in one block.
    if (isset($data['@graph']) && is_array($data['@graph'])) {
        $out = [];
        foreach ($data['@graph'] as $item) {
            $out = array_merge($out, flattenLinkedData($item));
        }
        return $out === [] ? [$data] : $out;
    }

    return [$data];
}

function linkedDataType(mixed $entity): ?string
{
    if (!is_array($entity) || !isset($entity['@type'])) {
        return null;
    }
    return is_array($entity['@type'])
        ? implode(', ', array_filter($entity['@type'], 'is_string'))
        : (is_string($entity['@type'])
            ? $entity['@type']
            : null);
}

/**
 * Parses HTML without letting it reach out.
 *
 * `LIBXML_NONET` matters: an HTML document can name an external DTD, and a
 * parser that fetches it turns "show me this page" into "this server will make
 * whatever request the page asks for". Warnings are collected rather than
 * emitted, because real pages produce hundreds and they are not this tool's
 * problem to report.
 */
function loadHtml(string $html): ?DOMDocument
{
    if (trim($html) === '') {
        return null;
    }

    $previous = libxml_use_internal_errors(true);
    $doc = new DOMDocument();

    /*
     * The encoding hint.
     *
     * Without it libxml assumes ISO-8859-1 and every non-ASCII character in the
     * title comes out wrong. The meta prefix is the documented way to say so
     * and is stripped from the tree by the parser itself.
     */
    $ok = $doc->loadHTML(
        '<?xml encoding="UTF-8">' . $html,
        LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING,
    );

    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return $ok ? $doc : null;
}

/**
 * JSON, formatted, with each line tagged by what kind of thing is on it.
 *
 * Colouring in the browser would mean a parser in JavaScript over a string that
 * may be a megabyte; doing it here means the browser receives lines it can
 * render directly. Invalid JSON is returned as-is with the parser's complaint,
 * because "this is not JSON" is the single most useful thing to learn about a
 * response that was supposed to be.
 *
 * @return array{ok: bool, error: ?string, lines: list<array{n: int, text: string, depth: int}>}
 */
function formatJson(string $body, int $maxLines = 4000): array
{
    $data = json_decode($body, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        return [
            'ok' => false,
            'error' => json_last_error_msg(),
            'lines' => [],
        ];
    }

    $pretty = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );

    if ($pretty === false) {
        return [
            'ok' => false,
            'error' => 'could not be re-encoded',
            'lines' => [],
        ];
    }

    $lines = [];
    $all = explode("\n", $pretty);
    $truncated = count($all) > $maxLines;

    foreach (array_slice($all, 0, $maxLines) as $index => $text) {
        $lines[] = [
            'n' => $index + 1,
            'text' => $text,
            'depth' => (int) floor((strlen($text) - strlen(ltrim($text))) / 4),
        ];
    }

    return [
        'ok' => true,
        'error' => null,
        'lines' => $lines,
        'truncated' => $truncated,
        'total' => count($all),
    ];
}

/**
 * An address the way somebody typed it, turned into one that can be fetched.
 *
 * People type `lewismoten.com`. Guessing `https://` is right almost always and
 * is trivially visible when it is wrong, since the resulting request is shown
 * in full. Guessing `http://` would be worse than wrong: it would quietly send
 * a request in the clear.
 */
function tidyUrl(string $typed): string
{
    $typed = trim($typed);
    if ($typed === '') {
        return '';
    }

    // A scheme is letters, digits, +, - and . before a colon.
    if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $typed)) {
        return $typed;
    }

    /*
     * A bare `//host/path` is protocol-relative, which is a real thing that
     * appears in markup and means "whatever scheme the page used".
     */
    if (str_starts_with($typed, '//')) {
        return 'https:' . $typed;
    }

    // `localhost:8000/x` - a colon, but a port rather than a scheme.
    return 'https://' . $typed;
}
