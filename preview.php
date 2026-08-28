<?php
declare(strict_types=1);

/*
 * Serving a fetched page back to a sandboxed frame.
 *
 * This exists for one reason: a frame filled with `srcdoc` inherits the parent
 * document's Content-Security-Policy. Somebody else's page is full of inline
 * styles, and under this site's policy every one of them became a console
 * error attributed to this site - noise that buries the violations that are
 * actually ours.
 *
 * A frame with a real `src` gets the policy of the response it loaded. So the
 * body is served from here, under a policy written for exactly this job:
 *
 *   sandbox            an opaque origin, no scripts, no forms, no navigation.
 *                      Sent as a header so it holds even if the frame's own
 *                      sandbox attribute were ever removed.
 *   default-src 'none' nothing loads from anywhere - no scripts, no fonts, no
 *                      frames, no requests back to the origin site.
 *   style-src          inline styles allowed, because that is the whole point:
 *                      the page should look like itself.
 *   img-src            data: only unless the site's own files were asked for.
 *                      Off by default: fetching them tells the site being
 *                      inspected that somebody is inspecting it.
 *
 * The body is never trusted, never executed, and never reaches this origin -
 * `sandbox` without `allow-same-origin` puts it in an origin of its own, so it
 * cannot read a cookie or touch the page that framed it.
 */

require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('html');

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Not configured.');
}

require_once __DIR__ . '/lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
define('DISKMON_LOG', dirname((string)$config['database']) . '/error.log');

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/access.php';

startSession($config);

if (!isSignedIn() || !accessAllows($config, 'pages', 'net-fetch')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Your group cannot preview fetched pages.');
}

$pdo = db($config);

$token = (string)($_GET['token'] ?? '');

/*
 * Whether the page may fetch its own supporting files.
 *
 * Off by default, for two reasons. Every one of those requests tells the site
 * that somebody is looking at it, from this server's address - and a page you
 * are inspecting because you distrust it is exactly the page that should not
 * be told. And a page stripped of its own styling and pictures is how a reader
 * without them meets it, which is worth being able to see on purpose.
 *
 * Never scripts, in either state. There is no version of this that runs
 * somebody else's code.
 */
$images = ($_GET['images'] ?? '0') === '1';

if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No such preview.';
    exit;
}

$stmt = $pdo->prepare('SELECT url, content_type, body FROM fetch_bodies WHERE token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch();

if ($row === false) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'That preview has expired. Ask for the page again.';
    exit;
}

$body = (string)$row['body'];

/*
 * A base so that relative links resolve against the site they came from.
 *
 * Without it, `/style.css` and `<a href="/about">` resolve against this server,
 * which is both wrong and a way to point a page at our own endpoints - which
 * matters rather more once the site's own files are allowed to load.
 */
$base = '<base href="' . htmlspecialchars((string)$row['url'], ENT_QUOTES) . '">';

if (!$images) {
    /*
     * Pictures replaced rather than blocked.
     *
     * Blocking them leaves ragged holes and hides whether alt text exists at
     * all. Substituting a marked placeholder shows the layout the page intends
     * and puts the alt text where a reader relying on it would meet it - which
     * is the reason to look at a page without its images in the first place.
     */
    $body = previewPlaceholders($body);
}

$policy = $images
    ? "img-src data: https: http:; style-src 'unsafe-inline' https: http:; "
        . "font-src data: https: http:"
    : "img-src data:; style-src 'unsafe-inline'; font-src data:";

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header(
    "Content-Security-Policy: sandbox; default-src 'none'; {$policy}; "
    . "form-action 'none'; base-uri 'none'; frame-ancestors 'self'"
);
// No referrer on anything the page does fetch: the site should not learn the
// address of this dashboard from a stylesheet request.
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

echo $base, $body;

/**
 * Swaps every picture for a labelled box carrying its alt text.
 *
 * A data URI, so nothing is fetched. The alt text is put in the box's own
 * markup as well as left on the element, because an empty alt - which is
 * correct for a decorative image and wrong for a meaningful one - is invisible
 * unless something draws attention to it.
 */
function previewPlaceholders(string $html): string
{
    return (string)preg_replace_callback(
        '#<img\b([^>]*)>#i',
        function (array $m): string {
            $attributes = $m[1];

            $alt = preg_match('/\balt\s*=\s*"([^"]*)"|\balt\s*=\s*\'([^\']*)\'/i', $attributes, $a)
                ? ($a[1] !== '' ? $a[1] : ($a[2] ?? ''))
                : null;

            $label = $alt === null
                ? 'no alt attribute'
                : ($alt === '' ? 'alt is empty' : $alt);

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="12">'
                . '<rect width="16" height="12" fill="#d8d8e0" stroke="#8a8a99"/>'
                . '<path d="M1 11 L6 5 L9 8 L12 5 L15 11 Z" fill="#8a8a99"/></svg>';

            // Width and height are kept if the page declared them, so the
            // layout stays the shape the page intended.
            $size = '';
            foreach (['width', 'height'] as $dimension) {
                if (preg_match('/\b' . $dimension . '\s*=\s*"?(\d+)"?/i', $attributes, $d)) {
                    $size .= ' ' . $dimension . '="' . $d[1] . '"';
                }
            }

            return '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '"'
                . $size . ' alt="' . htmlspecialchars($label, ENT_QUOTES) . '"'
                . ' title="' . htmlspecialchars('Placeholder — ' . $label, ENT_QUOTES) . '">';
        },
        $html
    );
}
