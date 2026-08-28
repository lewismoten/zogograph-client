<?php
declare(strict_types=1);

/*
 * A picture kept with a note, served as a picture.
 *
 * Separate from api.php for the same reason thumb.php is: everything there is
 * JSON, and PNG bytes are not valid UTF-8 - they cannot go in a JSON string at
 * all without base64, which is a third larger, held in memory twice, and
 * useless to the browser's image cache.
 *
 * Only rows this tool wrote itself are reachable, and only ones stored as the
 * `image` kind. The bytes were checked for a PNG signature before they were
 * stored, and are checked again here before the content type is promised: a
 * blob served as an image has to be an image, and "it was an image when we
 * saved it" is a weaker statement than it sounds once a database is on disk
 * where other things can write to it.
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
require_once __DIR__ . '/lib/actions.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/notes.php';

startSession($config);

if (!isSignedIn() || !accessAllows($config, 'notes')) {
    http_response_code(403);
    exit('Sign in to see this.');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('No picture asked for.');
}

$pdo = db($config);
if (noteForSnapshot($pdo, $config, $id) === null) {
    http_response_code(403);
    exit('That picture is not available to your group.');
}
$stmt = $pdo->prepare("SELECT body, at FROM note_snapshots WHERE id = ? AND kind = 'image'");
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row === false) {
    http_response_code(404);
    exit('No such picture.');
}

$bytes = (string)$row['body'];

if (!str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
    http_response_code(422);
    exit('That is not a picture.');
}

header('Content-Type: image/png');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . strlen($bytes));
/*
 * Cacheable, privately and for a long time.
 *
 * A picture kept with a note never changes - it is a moment that already
 * happened - so re-fetching it is pure waste. Private, because it is behind a
 * sign-in and must not sit in a shared proxy.
 */
header('Cache-Control: private, max-age=86400, immutable');

echo $bytes;
