<?php
declare(strict_types=1);

/* Public only to already-paired remote installations; no browser session. */
require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('json');
$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) { http_response_code(503); exit; }
require_once __DIR__ . '/lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
define('DISKMON_LOG', dirname((string)$config['database']) . '/error.log');
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/peertransport.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && (string)($_GET['health'] ?? '') === '1') {
    echo json_encode(['ok' => true, 'service' => 'zogograph-peer-transport', 'protocol_version' => PEER_TRANSPORT_PROTOCOL]);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['error' => 'POST required; use GET ?health=1 for protocol readiness']);
    exit;
}
$declaredLength = filter_var($_SERVER['CONTENT_LENGTH'] ?? null, FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 0]]);
if ($declaredLength !== false && $declaredLength > PEER_TRANSPORT_MAX_BYTES) {
    http_response_code(413);
    echo json_encode(['error' => 'payload too large']);
    exit;
}
try {
    $body = (string)file_get_contents('php://input');
    $pdo = db($config);
    $accepted = peerTransportAccept($pdo, $config, [
        'peer' => $_SERVER['HTTP_X_HOSTSCOPE_PEER'] ?? '',
        'timestamp' => $_SERVER['HTTP_X_HOSTSCOPE_TIMESTAMP'] ?? '',
        'nonce' => $_SERVER['HTTP_X_HOSTSCOPE_NONCE'] ?? '',
        'signature' => $_SERVER['HTTP_X_HOSTSCOPE_SIGNATURE'] ?? '',
        'encoding' => $_SERVER['HTTP_CONTENT_ENCODING'] ?? '',
    ], $body);
    if ($accepted['activate']) {
        try {
            $config['peers'][$accepted['peer']]['transport_pending_until'] = 0;
            $config['peers'][$accepted['peer']]['transport_activated_at'] = time();
            saveConfiguredSettings($config, $configFile, __DIR__ . '/lib');
            logAction($pdo, 'peer_transport_activate', $accepted['peer'], 'ok', 0, 0, 'first signed delivery');
        } catch (Throwable $e) {
            logProblem('warning', 'remote pairing activation: ' . $e->getMessage());
        }
    }
    http_response_code(202);
    echo json_encode(['ok' => true, 'peer' => $accepted['peer'], 'taken_at' => $accepted['taken_at'],
        'activated' => $accepted['activate'], 'protocol_version' => PEER_TRANSPORT_PROTOCOL]);
} catch (ActionRefused $e) {
    http_response_code(403);
    echo json_encode(['error' => $e->getMessage()]);
}
