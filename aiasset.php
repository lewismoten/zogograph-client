<?php
declare(strict_types=1);

/*
 * A picture the assistant generated, served from here rather than from theirs.
 *
 * The reason this file exists is that a generator's URL expires. A picture that
 * was paid for and is a 404 a fortnight later is a record of nothing, and
 * "review them later in full size" cannot be done from a dead link. The bytes
 * are fetched once, when the picture is made, and kept.
 *
 * Only generated images. An animation found by a search is somebody else's file
 * on their service; copying it here would be storing their content rather than
 * ours, and a link is enough for something nobody paid for.
 *
 * Same three safeguards as thumb.php: the session check, a signature check on
 * the stored bytes, and an explicit content type with nosniff. The signature is
 * re-checked on the way out rather than trusted from when it went in - a
 * database on disk is a thing other processes can write to, and "it was an
 * image when we saved it" is a weaker statement than it sounds.
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
require_once __DIR__ . '/lib/spans.php';
require_once __DIR__ . '/lib/storekeep.php';
require_once __DIR__ . '/lib/aidb.php';

startSession($config);

if (!isSignedIn() || !accessAllows($config, 'ai')) {
    http_response_code(403);
    exit('Your group cannot see assistant assets.');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('No picture asked for.');
}

$ai = aiDb($config, false);
if ($ai === null) {
    http_response_code(404);
    exit('There is no store here.');
}

$stmt = $ai->prepare('SELECT mime, body FROM ai_assets WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row === false) {
    http_response_code(404);
    exit('No such picture.');
}

$bytes = (string) $row['body'];

/*
 * The type comes from the bytes, not from the column.
 *
 * getimagesizefromstring reads the actual header. Serving whatever the mime
 * column happens to say would mean a row somebody edited could choose the
 * content type of a response, which is the whole shape of a stored-XSS bug.
 */
$size = @getimagesizefromstring($bytes);
$allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

if (
    $size === false ||
    !in_array((string) ($size['mime'] ?? ''), $allowed, true)
) {
    http_response_code(422);
    exit('That is not a picture.');
}

header('Content-Type: ' . $size['mime']);
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . strlen($bytes));
// A generated picture never changes, so re-fetching it is pure waste. Private,
// because it is behind a sign-in and must not sit in a shared proxy.
header('Cache-Control: private, max-age=86400, immutable');

echo $bytes;
