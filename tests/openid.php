<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/openid.php';

function openIdExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

if (!function_exists('openssl_pkey_new')) {
    echo "OpenID checks skipped: OpenSSL is unavailable\n";
    exit(0);
}

$key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
openIdExpect($key !== false && openssl_pkey_export($key, $private), 'could not generate an OpenID test key');
$generated = openIdGenerateSigningKey();
$generatedDetails = openssl_pkey_get_details(openssl_pkey_get_private($generated));
openIdExpect(is_array($generatedDetails) && ($generatedDetails['type'] ?? null) === OPENSSL_KEYTYPE_RSA
    && (int)($generatedDetails['bits'] ?? 0) >= 3072, 'generated OpenID signing key is not a 3072-bit RSA key');
$config = ['openid' => ['enabled' => true, 'issuer' => 'https://hostscope.example',
    'signing_private_key' => $private, 'clients' => ['peer' => [
        'client_id' => 'peer-client', 'client_secret' => 'test-secret',
        'redirect_uris' => ['https://peer.example/callback'],
    ]]]];
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE openid_codes (code_hash TEXT PRIMARY KEY, client_id TEXT NOT NULL, redirect_uri TEXT NOT NULL, code_challenge TEXT NOT NULL, username TEXT NOT NULL, groups_json TEXT NOT NULL, nonce TEXT, auth_time INTEGER NOT NULL, expires_at INTEGER NOT NULL, used_at INTEGER)');
$verifier = str_repeat('v', 43);
$challenge = openIdBase64Url(hash('sha256', $verifier, true));
$code = openIdCreateCode($pdo, 'peer-client', 'https://peer.example/callback', $challenge,
    'operator', ['Operator'], 'nonce-value');
$grant = openIdConsumeCode($pdo, $code, $verifier);
openIdExpect(is_array($grant) && $grant['username'] === 'operator', 'valid PKCE authorization code was refused');
openIdExpect(openIdConsumeCode($pdo, $code, $verifier) === null, 'authorization code was accepted twice');
$claims = openIdVerifyOwnToken($config, openIdIdToken($config, $grant));
openIdExpect(is_array($claims) && $claims['sub'] === 'operator' && $claims['nonce'] === 'nonce-value',
    'issued OpenID ID token did not verify with its registered audience and nonce');
$claims['iat'] = time() + 120;
$header = openIdBase64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']) ?: '{}');
$body = openIdBase64Url(json_encode($claims, JSON_UNESCAPED_SLASHES) ?: '{}');
openssl_sign($header . '.' . $body, $signature, $private, OPENSSL_ALGO_SHA256);
$future = $header . '.' . $body . '.' . openIdBase64Url($signature);
openIdExpect(openIdVerifyOwnToken($config, $future) === null,
    'a locally signed token issued too far in the future was accepted');
$bad = openIdCreateCode($pdo, 'peer-client', 'https://peer.example/callback', $challenge,
    'operator', ['Operator'], null);
openIdExpect(openIdConsumeCode($pdo, $bad, str_repeat('x', 43)) === null,
    'an authorization code accepted an incorrect PKCE verifier');
openIdExpect(openIdDiscovery($config)['code_challenge_methods_supported'] === ['S256'],
    'OpenID discovery did not advertise its enforced PKCE method');

echo "OpenID checks passed\n";
