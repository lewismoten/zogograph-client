<?php
declare(strict_types=1);

/* OpenID Connect provider primitives for explicitly registered trusted peers.
 * The provider never advertises itself until its issuer and signing key are
 * valid, and the endpoint enforces authorization code, PKCE, and signed ID
 * tokens rather than presenting a discovery document for an imaginary login. */

/** Returns an unambiguous HTTPS issuer URL, or null when it is unsafe. */
function openIdIssuerUrl(string $value): ?string
{
    $issuer = rtrim(trim($value), '/');
    $parts = parse_url($issuer);
    if (
        $issuer === '' ||
        !is_array($parts) ||
        ($parts['scheme'] ?? '') !== 'https' ||
        !isset($parts['host']) ||
        isset($parts['user']) ||
        isset($parts['pass']) ||
        isset($parts['query']) ||
        isset($parts['fragment'])
    ) {
        return null;
    }
    return filter_var($issuer, FILTER_VALIDATE_URL) ? $issuer : null;
}

function openIdIssuer(array $config): ?string
{
    if (empty($config['openid']['enabled'])) {
        return null;
    }
    return openIdIssuerUrl((string) ($config['openid']['issuer'] ?? ''));
}

function openIdDiscovery(array $config): ?array
{
    $issuer = openIdIssuer($config);
    if ($issuer === null) {
        return null;
    }
    $jwk = openIdJwk($config);
    if ($jwk === null) {
        return null;
    }
    return [
        'issuer' => $issuer,
        'authorization_endpoint' => $issuer . '/openid.php?action=authorize',
        'token_endpoint' => $issuer . '/openid.php?action=token',
        'jwks_uri' => $issuer . '/openid.php?action=jwks',
        'response_types_supported' => ['code'],
        'grant_types_supported' => ['authorization_code'],
        'subject_types_supported' => ['public'],
        // The token endpoint accepts the confidential client values in the
        // form body and intentionally does not implement HTTP Basic auth.
        'token_endpoint_auth_methods_supported' => ['client_secret_post'],
        'code_challenge_methods_supported' => ['S256'],
        // The application only manages locally named groups, so this is the
        // claim a peer will eventually map after signature validation.
        'scopes_supported' => ['openid', 'profile', 'groups'],
        'id_token_signing_alg_values_supported' => ['RS256'],
    ];
}

function openIdBase64Url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

/** Keeps echoed authorization values bounded and free of header-breaking controls. */
function openIdOpaqueAuthorizeValueValid(
    string $value,
    int $maximumLength,
): bool {
    return $value !== '' &&
        strlen($value) <= $maximumLength &&
        preg_match('/^[\x20-\x7E]+$/D', $value) === 1;
}

/** @param list<string> $scopes */
function openIdScopesValid(string $raw, array $scopes): bool
{
    if (strlen($raw) > 512 || $scopes === [] || count($scopes) > 10) {
        return false;
    }
    foreach ($scopes as $scope) {
        if (
            !is_string($scope) ||
            !preg_match('/^[A-Za-z][A-Za-z0-9._-]{0,63}$/D', $scope)
        ) {
            return false;
        }
    }
    return true;
}

/** Creates a dedicated provider key; callers decide where its private PEM lives. */
function openIdGenerateSigningKey(): string
{
    if (
        !function_exists('openssl_pkey_new') ||
        !function_exists('openssl_pkey_export')
    ) {
        throw new ActionRefused(
            'OpenSSL is not available to generate an OpenID signing key',
        );
    }
    $key = openssl_pkey_new([
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 3072,
    ]);
    if ($key === false || !openssl_pkey_export($key, $private)) {
        throw new ActionRefused(
            'OpenSSL could not generate an OpenID signing key',
        );
    }
    $details = openssl_pkey_get_details($key);
    if (
        !is_array($details) ||
        ($details['type'] ?? null) !== OPENSSL_KEYTYPE_RSA ||
        (int) ($details['bits'] ?? 0) < 3072
    ) {
        throw new ActionRefused(
            'OpenSSL returned an unsuitable OpenID signing key',
        );
    }
    return $private;
}

function openIdDecodeBase64Url(string $value): string|false
{
    $pad = strlen($value) % 4;
    return base64_decode(
        strtr($value . ($pad ? str_repeat('=', 4 - $pad) : ''), '-_', '+/'),
        true,
    );
}

/** Returns the usable details for the provider's dedicated RSA signing key. */
function openIdSigningKeyDetails(string $key): ?array
{
    if ($key === '' || !function_exists('openssl_pkey_get_private')) {
        return null;
    }
    $resource = openssl_pkey_get_private($key);
    $details =
        $resource === false ? false : openssl_pkey_get_details($resource);
    if (
        !is_array($details) ||
        ($details['type'] ?? null) !== OPENSSL_KEYTYPE_RSA ||
        (int) ($details['bits'] ?? 0) < 3072 ||
        !isset($details['rsa']['n'], $details['rsa']['e'])
    ) {
        return null;
    }
    return $details;
}

function openIdJwk(array $config): ?array
{
    $key = trim((string) ($config['openid']['signing_private_key'] ?? ''));
    $details = openIdSigningKeyDetails($key);
    if ($details === null) {
        return null;
    }
    $n = openIdBase64Url($details['rsa']['n']);
    $e = openIdBase64Url($details['rsa']['e']);
    return [
        'kty' => 'RSA',
        'use' => 'sig',
        'alg' => 'RS256',
        'kid' => substr(hash('sha256', $n . '.' . $e), 0, 16),
        'n' => $n,
        'e' => $e,
    ];
}

/** Validates the narrow confidential-client record accepted by this provider. */
function openIdClientDefinition(string $name, mixed $value): ?array
{
    if (
        !preg_match('/^[A-Za-z][A-Za-z0-9._-]{2,127}$/', $name) ||
        !is_array($value)
    ) {
        return null;
    }
    $clientId = (string) ($value['client_id'] ?? $name);
    $secret = (string) ($value['client_secret'] ?? '');
    $redirects = $value['redirect_uris'] ?? null;
    if (
        !preg_match('/^[A-Za-z][A-Za-z0-9._-]{2,127}$/', $clientId) ||
        !preg_match('/^[a-f0-9]{64}$/', $secret) ||
        !is_array($redirects) ||
        $redirects === [] ||
        count($redirects) > 20
    ) {
        return null;
    }
    $out = [];
    foreach ($redirects as $redirect) {
        if (!is_string($redirect) || strlen($redirect) > 2048) {
            return null;
        }
        $parts = parse_url($redirect);
        if (
            !is_array($parts) ||
            ($parts['scheme'] ?? '') !== 'https' ||
            !isset($parts['host']) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            isset($parts['fragment'])
        ) {
            return null;
        }
        $out[] = $redirect;
    }
    if (count(array_unique($out, SORT_STRING)) !== count($out)) {
        return null;
    }
    return [
        'name' => $name,
        'client_id' => $clientId,
        'client_secret' => $secret,
        'redirect_uris' => $out,
    ];
}

/** @return array<string, array{name:string,client_id:string,client_secret:string,redirect_uris:list<string>}> */
function openIdClientDefinitions(array $config): array
{
    $out = [];
    foreach ((array) ($config['openid']['clients'] ?? []) as $name => $client) {
        $definition = openIdClientDefinition((string) $name, $client);
        if ($definition !== null && !isset($out[$definition['client_id']])) {
            $out[$definition['client_id']] = $definition;
        }
    }
    return $out;
}

/** Creates a client secret that Setup displays only in the response that made it. */
function openIdCreateClient(string $clientId, string $redirect): array
{
    $definition = openIdClientDefinition($clientId, [
        'client_id' => $clientId,
        'client_secret' => bin2hex(random_bytes(32)),
        'redirect_uris' => [$redirect],
    ]);
    if ($definition === null) {
        throw new InvalidArgumentException(
            'client ID or redirect URL is invalid',
        );
    }
    return $definition;
}

function openIdClient(array $config, string $clientId): ?array
{
    $clients = openIdClientDefinitions($config);
    return $clients[$clientId] ?? null;
}

function openIdRedirectAllowed(array $client, string $redirect): bool
{
    foreach ((array) ($client['redirect_uris'] ?? []) as $known) {
        if (is_string($known) && hash_equals($known, $redirect)) {
            return true;
        }
    }
    return false;
}

function openIdCreateCode(
    PDO $pdo,
    string $clientId,
    string $redirect,
    string $challenge,
    string $username,
    array $groups,
    ?string $nonce,
): string {
    $code = openIdBase64Url(random_bytes(32));
    $pdo->prepare(
        'INSERT INTO openid_codes (code_hash, client_id, redirect_uri, code_challenge, username, groups_json, nonce, auth_time, expires_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    )->execute([
        hash('sha256', $code),
        $clientId,
        $redirect,
        $challenge,
        $username,
        json_encode(array_values($groups), JSON_UNESCAPED_SLASHES),
        $nonce,
        time(),
        time() + 120,
    ]);
    return $code;
}

function openIdConsumeCode(PDO $pdo, string $code, string $verifier): ?array
{
    $hash = hash('sha256', $code);
    $stmt = $pdo->prepare(
        'SELECT * FROM openid_codes WHERE code_hash = ? AND used_at IS NULL AND expires_at >= ?',
    );
    $stmt->execute([$hash, time()]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        return null;
    }
    if (
        !preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $verifier) ||
        !hash_equals(
            (string) $row['code_challenge'],
            openIdBase64Url(hash('sha256', $verifier, true)),
        )
    ) {
        return null;
    }
    $used = $pdo->prepare(
        'UPDATE openid_codes SET used_at = ? WHERE code_hash = ? AND used_at IS NULL',
    );
    $used->execute([time(), $hash]);
    return $used->rowCount() === 1 ? $row : null;
}

function openIdIdToken(array $config, array $code): string
{
    $jwk = openIdJwk($config);
    $key = trim((string) ($config['openid']['signing_private_key'] ?? ''));
    if ($jwk === null || $key === '') {
        throw new ActionRefused('OpenID signing key is not configured');
    }
    $now = time();
    $payload = [
        'iss' => openIdIssuer($config),
        'sub' => (string) $code['username'],
        'aud' => (string) $code['client_id'],
        'iat' => $now,
        'exp' => $now + 600,
        'auth_time' => (int) $code['auth_time'],
        'groups' => json_decode((string) $code['groups_json'], true) ?: [],
    ];
    if ($code['nonce'] !== null && $code['nonce'] !== '') {
        $payload['nonce'] = (string) $code['nonce'];
    }
    $head = openIdBase64Url(
        json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $jwk['kid']]) ?:
        '{}',
    );
    $body = openIdBase64Url(
        json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}',
    );
    if (
        openssl_sign(
            $head . '.' . $body,
            $signature,
            $key,
            OPENSSL_ALGO_SHA256,
        ) !== true
    ) {
        throw new ActionRefused('OpenID signing failed');
    }
    return $head . '.' . $body . '.' . openIdBase64Url($signature);
}

/** Validates a token this Zogograph instance itself signed. */
function openIdVerifyOwnToken(array $config, string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    $header = json_decode(openIdDecodeBase64Url($parts[0]) ?: '', true);
    $claims = json_decode(openIdDecodeBase64Url($parts[1]) ?: '', true);
    $signature = openIdDecodeBase64Url($parts[2]);
    $details = openIdSigningKeyDetails(
        (string) ($config['openid']['signing_private_key'] ?? ''),
    );
    if (
        !is_array($header) ||
        !is_array($claims) ||
        $signature === false ||
        !is_array($details) ||
        ($header['alg'] ?? '') !== 'RS256' ||
        openssl_verify(
            $parts[0] . '.' . $parts[1],
            $signature,
            $details['key'],
            OPENSSL_ALGO_SHA256,
        ) !== 1
    ) {
        return null;
    }
    $aud = $claims['aud'] ?? null;
    $audiences = is_array($aud) ? $aud : [$aud];
    $registered = false;
    foreach ($audiences as $id) {
        if (is_string($id) && openIdClient($config, $id) !== null) {
            $registered = true;
        }
    }
    $now = time();
    return $registered &&
        hash_equals(
            (string) openIdIssuer($config),
            (string) ($claims['iss'] ?? ''),
        ) &&
        (int) ($claims['exp'] ?? 0) >= $now &&
        (int) ($claims['iat'] ?? 0) > 0 &&
        (int) $claims['iat'] <= $now + 60 &&
        (!isset($claims['nbf']) || (int) $claims['nbf'] <= $now + 60) &&
        is_string($claims['sub'] ?? null)
        ? $claims
        : null;
}
