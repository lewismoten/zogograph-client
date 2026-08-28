<?php
declare(strict_types=1);

/* Read-only peer surface. It deliberately returns no paths, process command
 * lines, logs, tokens, or configuration. A connected Zogograph starts with the
 * same safe capacity summary that its public Overview can show. */
require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('json');
require_once __DIR__ . '/lib/setupstore.php';
$file = __DIR__ . '/lib/config.php';
if (!is_file($file)) { http_response_code(503); exit; }
$config = loadConfiguredSettings($file);
require_once __DIR__ . '/lib/core-features.php';
if (!coreFeatureEnabled($config, 'peers')) { http_response_code(404); exit; }
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/openid.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'GET required']);
    exit;
}

$bearer = preg_match('/^Bearer\s+(.+)$/i', (string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''), $m)
    ? $m[1] : '';
$claims = $bearer === '' ? null : openIdVerifyOwnToken($config, $bearer);
if ($claims === null || !accessAllowsGroups($config, (array)($claims['groups'] ?? []), 'pages', 'overview')) {
    http_response_code(403);
    echo json_encode(['error' => 'a valid peer token with Overview access is required']);
    exit;
}
$row = db($config)->query('SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1')->fetch() ?: null;
echo json_encode([
    'host' => php_uname('n'), 'subject' => $claims['sub'], 'taken_at' => $row['taken_at'] ?? null,
    'vitals' => $row === null ? null : [
        'disk_total_bytes' => (int)$row['disk_total_bytes'], 'disk_free_bytes' => (int)$row['disk_free_bytes'],
        'inodes_total' => (int)$row['inodes_total'], 'inodes_free' => (int)$row['inodes_free'],
        'cpu_percent' => $row['cpu_percent'] === null ? null : (float)$row['cpu_percent'],
        'mem_used_percent' => $row['mem_used_percent'] === null ? null : (float)$row['mem_used_percent'],
        'load1' => $row['load1'] === null ? null : (float)$row['load1'], 'cpu_cores' => (int)$row['cpu_cores'],
    ],
], JSON_UNESCAPED_SLASHES);
