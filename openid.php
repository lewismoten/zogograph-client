<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('json');
require_once __DIR__ . '/lib/setupstore.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/actions.php';
require_once __DIR__ . '/lib/openid.php';

$file = __DIR__ . '/lib/config.php';
if (!is_file($file)) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'not configured']);
    exit;
}
$config = loadConfiguredSettings($file);
date_default_timezone_set($config['timezone'] ?? 'UTC');
$action = (string)($_GET['action'] ?? 'discovery');
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
$json = static function (array $body, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit;
};
$discovery = openIdDiscovery($config);
if ($discovery === null) {
    $json(['error' => 'OpenID provider is not enabled or has no valid signing key'], 404);
}

if ($action === 'discovery') {
    $json($discovery);
}
if ($action === 'jwks') {
    $json(['keys' => [openIdJwk($config)]], 200);
}
if ($action === 'authorize') {
    startSession($config);
    if (!isSignedIn()) {
        $json(['error' => 'sign in to Zogograph before authorizing a trusted client'], 401);
    }
    $clientId = (string)($_GET['client_id'] ?? '');
    $redirect = (string)($_GET['redirect_uri'] ?? '');
    $challenge = (string)($_GET['code_challenge'] ?? '');
    $method = (string)($_GET['code_challenge_method'] ?? '');
    $state = (string)($_GET['state'] ?? '');
    $scopeRaw = (string)($_GET['scope'] ?? '');
    $scope = preg_split('/\s+/', trim($scopeRaw)) ?: [];
    $nonce = isset($_GET['nonce']) ? (string)$_GET['nonce'] : null;
    $client = openIdClient($config, $clientId);
    if (($_GET['response_type'] ?? '') !== 'code' || $client === null || !openIdRedirectAllowed($client, $redirect)
        || !openIdOpaqueAuthorizeValueValid($state, 2048) || !openIdScopesValid($scopeRaw, $scope)
        || !in_array('openid', $scope, true) || $method !== 'S256'
        || ($nonce !== null && !openIdOpaqueAuthorizeValueValid($nonce, 255))
        || !preg_match('/^[A-Za-z0-9_-]{43,128}$/', $challenge)) {
        $json(['error' => 'invalid authorization request'], 400);
    }
    $identity = ['username' => (string)($_SESSION['username'] ?? 'administrator'),
        'groups' => array_values(array_filter((array)($_SESSION['groups'] ?? ['Administrators']), 'is_string'))];
    $pdo = db($config);
    $code = openIdCreateCode($pdo, $clientId, $redirect, $challenge, $identity['username'],
        $identity['groups'], $nonce);
    logAction($pdo, 'openid_authorize', $clientId, 'ok', 0, 0, 'authorization code issued');
    header('Cache-Control: no-store');
    header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?')
        . http_build_query(['code' => $code, 'state' => $state], '', '&', PHP_QUERY_RFC3986), true, 302);
    exit;
}
if ($action === 'token') {
    if (!$post || ($_POST['grant_type'] ?? '') !== 'authorization_code') {
        $json(['error' => 'invalid_request'], 400);
    }
    $clientId = (string)($_POST['client_id'] ?? '');
    $client = openIdClient($config, $clientId);
    if ($client === null || (string)($client['client_secret'] ?? '') === ''
        || !hash_equals((string)$client['client_secret'], (string)($_POST['client_secret'] ?? ''))) {
        $json(['error' => 'invalid_client'], 401);
    }
    $verifier = (string)($_POST['code_verifier'] ?? '');
    $pdo = db($config);
    $code = openIdConsumeCode($pdo, (string)($_POST['code'] ?? ''), $verifier);
    if ($code === null || !hash_equals($clientId, (string)$code['client_id'])
        || !hash_equals((string)($_POST['redirect_uri'] ?? ''), (string)$code['redirect_uri'])) {
        $json(['error' => 'invalid_grant'], 400);
    }
    logAction($pdo, 'openid_token', $clientId, 'ok', 0, 0, 'authorization code exchanged');
    $json(['token_type' => 'Bearer', 'expires_in' => 600, 'id_token' => openIdIdToken($config, $code)]);
}
$json(['error' => 'not_found'], 404);
