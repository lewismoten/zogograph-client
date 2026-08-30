<?php
declare(strict_types=1);

/*
 * One image, served as an image.
 *
 * Separate from api.php because everything there is JSON, and a picture in JSON
 * is a base64 string thirty percent larger than the file, held in memory twice
 * and impossible for the browser to cache. An <img src> pointing here is what
 * the browser is already good at.
 *
 * Three things make this safe to have at all:
 *
 *   signed in       the same session check as everything else that names a path
 *   a signature     the file's first bytes have to say it is an image, so a
 *                   .png that is really a shell script is refused rather than
 *                   sent with an image content type
 *   nosniff         with an explicit type from that signature, so the browser
 *                   cannot be talked into treating the response as anything else
 *
 * It is deliberately not a general file server. There is no way to ask it for
 * something that is not a recognised bitmap, and it will not resize: scaling
 * happens in the page, because a real thumbnailer needs an image library that
 * shared hosts do not reliably have, and pretending otherwise would produce a
 * feature that works on the developer's machine and nowhere else.
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
define('DISKMON_LOG', dirname((string) $config['database']) . '/error.log');

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/actions.php';
require_once __DIR__ . '/lib/inspect.php';

startSession($config);

header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'; sandbox');
header('Cache-Control: private, max-age=60');

try {
    if (!isSignedIn() || !accessAllows($config, 'pages', 'storage-files')) {
        throw new ActionRefused('your group cannot see inspected images');
    }

    $path = resolveInspectable($config, (string) ($_GET['path'] ?? ''));

    if (is_link($path) || !is_file($path)) {
        throw new ActionRefused('not a regular file');
    }

    $facts = imageFacts($path);
    if (!$facts['is_image']) {
        throw new ActionRefused(
            'that file is not an image: ' . $facts['reason'],
        );
    }

    $size = (int) (@filesize($path) ?: 0);
    if ($size <= 0 || $size > INSPECT_IMAGE_MAX) {
        throw new ActionRefused('too large to send');
    }

    header('Content-Type: ' . imageMime((string) $facts['format']));
    header('Content-Length: ' . $size);
    // Never inline: the browser downloads or renders it in an <img>, and this
    // header stops a crafted file being opened as a top-level document.
    header('Content-Disposition: inline; filename="preview"');

    // Streamed rather than read into a string: a 40 MB photograph should not
    // become 40 MB of PHP memory on a shared host to be handed straight out.
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new ActionRefused('not readable');
    }
    fpassthru($handle);
    fclose($handle);
} catch (ActionRefused $e) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
}
