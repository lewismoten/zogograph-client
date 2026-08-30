<?php
declare(strict_types=1);

/* A support snapshot intentionally contains states and counts, never config values. */
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
require_once __DIR__ . '/lib/entitlement.php';

startSession($config);
if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    !isSignedIn() ||
    !accessAllows($config, 'pages', 'system') ||
    !csrfValid($_POST['csrf'] ?? null)
) {
    http_response_code(403);
    exit('A current session with system access and form token are required.');
}

$pdo = db($config);
$last = static function (string $table, string $column) use ($pdo): ?int {
    try {
        $value = (int) $pdo
            ->query("SELECT MAX({$column}) FROM {$table}")
            ->fetchColumn();
        return $value > 0 ? $value : null;
    } catch (Throwable) {
        return null;
    }
};
$integrity = false;
try {
    $integrity = $pdo->query('PRAGMA quick_check(1)')->fetchColumn() === 'ok';
} catch (Throwable) {
    // The exported false state is actionable without exposing an internal error.
}
$database = (string) $config['database'];
$bytes = 0;
foreach (['', '-wal', '-shm'] as $suffix) {
    $bytes += (int) (@filesize($database . $suffix) ?: 0);
}
$snapshot = [
    'format' => 'zogograph-diagnostic-v1',
    'generated_at' => gmdate('c'),
    'runtime' => [
        'php_version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'os_family' => PHP_OS_FAMILY,
        'architecture' => php_uname('m'),
        'extensions' => [
            'pdo_sqlite' => extension_loaded('pdo_sqlite'),
            'curl' => extension_loaded('curl'),
            'openssl' => extension_loaded('openssl'),
            'zlib' => extension_loaded('zlib'),
        ],
    ],
    'storage' => [
        'account_database_bytes' => $bytes,
        'quick_integrity' => $integrity,
    ],
    'collectors' => [
        'vitals_last_at' => $last('vitals', 'taken_at'),
        'scans_last_at' => $last('scans', 'started_at'),
    ],
    'features' => [
        'host_collector_enabled' => !empty($config['host']['enabled']),
        'remote_peer_count' => count((array) ($config['peers'] ?? [])),
        'assistant_enabled' => !empty($config['ai']['enabled']),
    ],
    'edition' => hostEntitlement($config) + [
        'remote_hosts_configured' => count((array) ($config['peers'] ?? [])),
    ],
];
logAction(
    $pdo,
    'diagnostic_export',
    'sanitized support snapshot',
    'ok',
    0,
    0,
    'no credentials, paths, or hostnames',
);

header('Content-Type: application/json; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="zogograph-diagnostic-' .
        gmdate('Ymd-His') .
        '.json"',
);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo json_encode(
    $snapshot,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
) . "\n";
