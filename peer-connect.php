<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('html');
require_once __DIR__ . '/lib/setupstore.php';
$file = __DIR__ . '/lib/config.php';
if (!is_file($file)) { http_response_code(503); exit('Zogograph is not configured.'); }
$config = loadConfiguredSettings($file);
require_once __DIR__ . '/lib/branding.php';
$brand = brandName($config);
require_once __DIR__ . '/lib/core-features.php';
if (!coreFeatureEnabled($config, 'peers')) { http_response_code(404); exit; }
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/openid.php';
require_once __DIR__ . '/lib/peers.php';
startSession($config);
if (!isSignedIn() || !accessAllows($config, 'write')) { http_response_code(403); exit('Administrators only.'); }

try {
    if (isset($_GET['error'])) throw new ActionRefused('remote sign-in was refused: ' . (string)$_GET['error']);
    $state = (string)($_GET['state'] ?? '');
    if ($state === '') {
        peerStartLogin($config, (string)($_GET['peer'] ?? ''));
    }
    $flow = $_SESSION['peer_oauth'][$state] ?? null;
    unset($_SESSION['peer_oauth'][$state]);
    if (!is_array($flow) || (int)($flow['expires_at'] ?? 0) < time()) throw new ActionRefused('that peer sign-in expired; start again');
    $peer = $config['peers'][$flow['name']] ?? null;
    if (!is_array($peer)) throw new ActionRefused('that trusted server no longer exists');
    $token = peerPostFormJson((string)$flow['token_endpoint'], [
        'grant_type' => 'authorization_code', 'code' => (string)($_GET['code'] ?? ''),
        'redirect_uri' => (string)$peer['redirect_uri'], 'client_id' => (string)$peer['client_id'],
        'client_secret' => (string)($peer['client_secret'] ?? ''), 'code_verifier' => (string)$flow['verifier'],
    ], (string)($flow['issuer'] ?? ''));
    $claims = peerVerifyToken((string)($token['id_token'] ?? ''), $flow, $peer);
    $_SESSION['peer_tokens'][$flow['name']] = [
        'id_token' => (string)$token['id_token'], 'subject' => $claims['sub'],
        'groups' => $claims['groups'] ?? [], 'expires_at' => (int)($claims['exp'] ?? 0),
    ];
    header('Location: index.html#/sys-peers?connected=' . rawurlencode((string)$flow['name']), true, 302);
    exit;
} catch (Throwable $e) {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><title>' . htmlspecialchars($brand, ENT_QUOTES) . ' — Peer connection</title><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</p><p><a href="index.html#/sys-peers">Return to trusted servers</a></p>';
}
