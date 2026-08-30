<?php
declare(strict_types=1);

/*
 * Streams one consistent account-database export, then removes the temporary
 * file. Copying the .db file directly is unsafe under WAL: recent committed
 * rows may exist only in its -wal companion, so this always uses VACUUM INTO.
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
require_once __DIR__ . '/lib/notes.php';

startSession($config);
if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !isSignedIn() ||
    !accessAllows($config, 'write') ||
    !csrfValid($_POST['csrf'] ?? null)
) {
    http_response_code(403);
    exit('A current administrator session and form token are required.');
}

$pdo = db($config);
$temporary =
    snapshotDirectory($config) .
    '/download-account-' .
    gmdate('Ymd\THis\Z') .
    '-' .
    bin2hex(random_bytes(8)) .
    '.db';

try {
    $copy = snapshotDatabase($pdo, $temporary);
    logAction(
        $pdo,
        'database_export',
        'account',
        'ok',
        $copy['bytes'],
        0,
        'one-time SQLite export',
    );

    header('Content-Type: application/vnd.sqlite3');
    header(
        'Content-Disposition: attachment; filename="zogograph-account-' .
            gmdate('Ymd-His') .
            '.db"',
    );
    header('Content-Length: ' . $copy['bytes']);
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($temporary);
} catch (ActionRefused $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
} finally {
    @unlink($temporary);
}
