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
openIdExpect($key !== false && openssl_pkey_export($key, $weakPrivate), 'could not generate an OpenID test key');
$generated = openIdGenerateSigningKey();
$generatedDetails = openssl_pkey_get_details(openssl_pkey_get_private($generated));
openIdExpect(is_array($generatedDetails) && ($generatedDetails['type'] ?? null) === OPENSSL_KEYTYPE_RSA
    && (int)($generatedDetails['bits'] ?? 0) >= 3072, 'generated OpenID signing key is not a 3072-bit RSA key');
$config = ['openid' => ['enabled' => true, 'issuer' => 'https://zogograph.example',
    'signing_private_key' => $generated, 'clients' => ['peer' => [
        'client_id' => 'peer-client', 'client_secret' => str_repeat('a', 64),
        'redirect_uris' => ['https://peer.example/callback'],
    ]]]];
openIdExpect(openIdIssuerUrl('https://zogograph.example/openid') === 'https://zogograph.example/openid'
    && openIdIssuerUrl('https://user:pass@zogograph.example') === null
    && openIdIssuerUrl('https://zogograph.example/?unexpected=1') === null
    && openIdIssuerUrl('https://zogograph.example/#fragment') === null,
    'ambiguous OpenID issuer URL was accepted');
openIdExpect(openIdSigningKeyDetails($weakPrivate) === null
    && openIdJwk(['openid' => ['signing_private_key' => $weakPrivate]]) === null,
    'OpenID accepted a manually supplied RSA signing key below 3072 bits');
openIdExpect(openIdOpaqueAuthorizeValueValid('opaque-state_123', 2048)
    && !openIdOpaqueAuthorizeValueValid("state\r\nLocation: https://attacker.example", 2048)
    && !openIdOpaqueAuthorizeValueValid(str_repeat('x', 2049), 2048)
    && openIdScopesValid('openid profile groups', ['openid', 'profile', 'groups'])
    && !openIdScopesValid(str_repeat('openid ', 100), array_fill(0, 100, 'openid')),
    'unbounded or unsafe OpenID authorization values were accepted');
$createdClient = openIdCreateClient('trusted-app', 'https://trusted.example/callback?source=zogograph');
openIdExpect($createdClient['client_id'] === 'trusted-app' && preg_match('/^[a-f0-9]{64}$/', $createdClient['client_secret']) === 1,
    'trusted OpenID client did not receive a one-time 256-bit secret');
openIdExpect((openIdClientDefinition('bad client', $createdClient) === null)
    && (openIdClientDefinition('trusted-app', array_replace($createdClient, ['redirect_uris' => ['https://user:pass@trusted.example/callback']])) === null),
    'invalid trusted OpenID client identity or credentialed redirect was accepted');
openIdExpect((openIdClientDefinitions($config)['peer-client']['name'] ?? null) === 'peer',
    'registered trusted OpenID client was not normalized');
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
openssl_sign($header . '.' . $body, $signature, $generated, OPENSSL_ALGO_SHA256);
$future = $header . '.' . $body . '.' . openIdBase64Url($signature);
openIdExpect(openIdVerifyOwnToken($config, $future) === null,
    'a locally signed token issued too far in the future was accepted');
$bad = openIdCreateCode($pdo, 'peer-client', 'https://peer.example/callback', $challenge,
    'operator', ['Operator'], null);
openIdExpect(openIdConsumeCode($pdo, $bad, str_repeat('x', 43)) === null,
    'an authorization code accepted an incorrect PKCE verifier');
$discovery = openIdDiscovery($config);
openIdExpect($discovery['code_challenge_methods_supported'] === ['S256']
    && $discovery['subject_types_supported'] === ['public']
    && $discovery['token_endpoint_auth_methods_supported'] === ['client_secret_post'],
    'OpenID discovery did not advertise its enforced token and client capabilities');
$setup = file_get_contents(__DIR__ . '/../setup.php');
openIdExpect($setup !== false && str_contains($setup, 'setupOpenIdClientToken')
    && str_contains($setup, 'create-openid-client') && str_contains($setup, 'remove-openid-client')
    && str_contains($setup, 'I saved the client secret'),
    'protected OpenID client management is not available in Setup');

echo "OpenID checks passed\n";
