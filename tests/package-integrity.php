<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/package-integrity.php';
require_once __DIR__ . '/../lib/package-submission.php';
require_once __DIR__ .
    '/../plugins/zogograph-key-custody/src/KeyCustodyHttpClient.php';
require_once __DIR__ .
    '/../plugins/zogograph-key-custody/src/KeyCustodyProvider.php';
require_once __DIR__ .
    '/../plugins/zogograph-replica-relay/src/ReplicaRelayHttpClient.php';
require_once __DIR__ .
    '/../plugins/sqlcipher-encrypted-sqlite/src/SqlcipherStorageProvider.php';
function packageExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
if (!function_exists('sodium_crypto_sign_keypair')) {
    echo "package integrity checks skipped: sodium is unavailable\n";
    exit(0);
}
$root = sys_get_temp_dir() . '/zogograph-package-' . bin2hex(random_bytes(4));
mkdir($root . '/src', 0700, true);
file_put_contents($root . '/src/provider.php', "<?php return 'safe';\n");
$manifest = [
    'id' => 'test-package',
    'version' => '1.0.0',
    'name' => 'Test package',
];
try {
    $sqlcipher = new \HostScope\Plugin\SqlcipherEncryptedSqlite\SqlcipherStorageProvider();
    $sqlcipherCapability = $sqlcipher->capability();
    packageExpect(
        $sqlcipherCapability['id'] === 'sqlcipher-encrypted-sqlite',
        'SQLCipher provider identity is invalid',
    );
    if ($sqlcipherCapability['state'] === 'unavailable') {
        packageExpect(
            str_contains($sqlcipherCapability['reason'], 'plaintext SQLite'),
            'SQLCipher provider does not fail closed on a normal SQLite driver',
        );
    }
    try {
        $sqlcipher->open($root . '/sqlcipher.db', 'short');
        throw new RuntimeException(
            'SQLCipher provider accepted an invalid key',
        );
    } catch (InvalidArgumentException | RuntimeException $error) {
        packageExpect(
            $error->getMessage() !==
                'SQLCipher provider accepted an invalid key',
            $error->getMessage(),
        );
    }
    $sqlcipherSource = file_get_contents(
        __DIR__ .
            '/../plugins/sqlcipher-encrypted-sqlite/src/SqlcipherStorageProvider.php',
    );
    packageExpect(
        $sqlcipherSource !== false &&
            str_contains(
                $sqlcipherSource,
                'SELECT COUNT(*) FROM sqlite_master',
            ) &&
            str_contains($sqlcipherSource, 'PRAGMA cipher_integrity_check'),
        'SQLCipher provider does not prove the opened database and supplied key',
    );
    $custodyMetadata = \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::requestMetadata();
    packageExpect(
        ctype_digit($custodyMetadata['timestamp']) &&
            preg_match('/^[A-Za-z0-9_-]{16,}$/', $custodyMetadata['nonce']) ===
                1,
        'custody client request metadata is not replay-safe',
    );
    $custodyPair = sodium_crypto_sign_keypair();
    $custodySecret = sodium_crypto_sign_secretkey($custodyPair);
    $custodyPayload = \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::requestPayload(
        'POST',
        '/v1/keys/example/envelopes',
        $custodyMetadata['timestamp'],
        $custodyMetadata['nonce'],
        '{}',
    );
    $custodySignature = sodium_crypto_sign_detached(
        $custodyPayload,
        $custodySecret,
    );
    packageExpect(
        sodium_crypto_sign_verify_detached(
            $custodySignature,
            $custodyPayload,
            sodium_crypto_sign_publickey($custodyPair),
        ),
        'custody request signature does not verify',
    );
    $wrappingPair = sodium_crypto_box_keypair();
    $artifactDataKey = random_bytes(32);
    $sealedArtifactKey = \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::sealDataKey(
        $artifactDataKey,
        sodium_crypto_box_publickey($wrappingPair),
    );
    packageExpect(
        hash_equals(
            $artifactDataKey,
            (string) sodium_crypto_box_seal_open(
                $sealedArtifactKey,
                $wrappingPair,
            ),
        ),
        'custody adapter public-key envelope does not open with the matching custody keypair',
    );
    try {
        new \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient(
            'https://user@example.test',
            'test-token',
            base64_encode($custodySecret),
        );
        throw new RuntimeException(
            'custody client accepted an ambiguous endpoint',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'custody client accepted an ambiguous endpoint',
            $error->getMessage(),
        );
    }
    try {
        new \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient(
            'https://example.test',
            'tok_' . str_repeat('a', 20) . ".bad\r\nvalue",
            base64_encode($custodySecret),
        );
        throw new RuntimeException(
            'custody client accepted a malformed bearer credential',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'custody client accepted a malformed bearer credential',
            $error->getMessage(),
        );
    }
    try {
        new \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient(
            'https://example.test',
            'tok_' . str_repeat('a', 20) . '.good',
            base64_encode($custodySecret),
            'not-a-pin',
        );
        throw new RuntimeException(
            'custody client accepted a malformed TLS pin',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'custody client accepted a malformed TLS pin',
            $error->getMessage(),
        );
    }
    try {
        \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::healthEndpoint(
            'http://example.test',
        );
        throw new RuntimeException(
            'custody health probe accepted an insecure endpoint',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'custody health probe accepted an insecure endpoint',
            $error->getMessage(),
        );
    }
    $custodyApiUrl = new ReflectionMethod(
        \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::class,
        'apiUrl',
    );
    packageExpect(
        $custodyApiUrl->invoke(
            null,
            'https://custody.example/services/key-custody',
            '/health',
        ) === 'https://custody.example/services/key-custody/v1/health' &&
            $custodyApiUrl->invoke(
                null,
                'https://custody.example/services/key-custody/v1',
                '/health',
            ) === 'https://custody.example/services/key-custody/v1/health',
        'custody service root and API-root URLs did not normalize to one endpoint',
    );
    $custodySource = file_get_contents(
        __DIR__ .
            '/../plugins/zogograph-key-custody/src/KeyCustodyHttpClient.php',
    );
    packageExpect(
        $custodySource !== false &&
            str_contains($custodySource, 'int $requestedTtl = 0') &&
            str_contains(
                $custodySource,
                "'requested_ttl_seconds' => \$requestedTtl",
            ) &&
            str_contains($custodySource, 'CURLOPT_SSL_VERIFYPEER => true') &&
            str_contains($custodySource, 'CURLOPT_SSL_VERIFYHOST => 2') &&
            str_contains($custodySource, 'CURLOPT_PINNEDPUBLICKEY') &&
            str_contains($custodySource, 'function apiUrl') &&
            str_contains($custodySource, "'/v1'") &&
            str_contains($custodySource, 'KeyCustodyRetryException') &&
            str_contains($custodySource, 'retry_after_seconds') &&
            method_exists(
                \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::class,
                'health',
            ) &&
            method_exists(
                \HostScope\Plugin\KeyCustody\KeyCustodyHttpClient::class,
                'healthEndpoint',
            ),
        'custody client does not enforce its secure transport defaults',
    );
    $custodyProviderSource = file_get_contents(
        __DIR__ .
            '/../plugins/zogograph-key-custody/src/KeyCustodyProvider.php',
    );
    packageExpect(
        $custodyProviderSource !== false &&
            str_contains(
                $custodyProviderSource,
                '$client = new KeyCustodyHttpClient(',
            ) &&
            str_contains(
                $custodyProviderSource,
                '$entitlement = $client->envelopeEntitlement();',
            ) &&
            str_contains(
                $custodyProviderSource,
                '$key = $client->unwrap($keyId, $artifactId, 0);',
            ) &&
            preg_match(
                '/new\\s+KeyCustodyHttpClient\\s*\\([\\s\\S]*?\\)\\s*->/',
                $custodyProviderSource,
            ) !== 1,
        'key custody provider uses constructor method chaining that PHP 8.1 cannot parse',
    );
    $custodyDirectory = __DIR__ . '/../plugins/zogograph-key-custody';
    $custodyManifest = json_decode(
        (string) file_get_contents($custodyDirectory . '/manifest.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $custodyLocale = json_decode(
        (string) file_get_contents($custodyDirectory . '/locales/en.json'),
        true,
        128,
        JSON_THROW_ON_ERROR,
    );
    packageExpect(
        is_array($custodyManifest) &&
            ($custodyManifest['runtime']['operations'] ?? null) === [
                'capability',
                'custody_endpoint_probe',
                'custody_enrollment_check',
                'custody_store_envelope',
                'custody_unwrap_artifact',
            ] &&
            method_exists(
                \HostScope\Plugin\KeyCustody\KeyCustodyProvider::class,
                'preflight',
            ) &&
            method_exists(
                \HostScope\Plugin\KeyCustody\KeyCustodyProvider::class,
                'storeEnvelope',
            ) &&
            method_exists(
                \HostScope\Plugin\KeyCustody\KeyCustodyProvider::class,
                'unwrapArtifact',
            ) &&
            packageVerifyIntegrity($custodyManifest, $custodyDirectory)['ok'],
        'key custody package manifest does not cover its installed files',
    );
    packageExpect(
        ($custodyManifest['locales']['en'] ?? null) === 'locales/en.json' &&
            is_array($custodyLocale) &&
            isset($custodyLocale['zogograph-key-custody.settings_title']) &&
            isset($custodyLocale['zogograph-key-custody.save_action']) &&
            isset(
                $custodyLocale['zogograph-key-custody.service_url_placeholder'],
            ) &&
            isset(
                $custodyLocale[
                    'zogograph-key-custody.credential_reference_placeholder'
                ],
            ),
        'key custody package does not own its declared localization catalog',
    );
    $custodyLocaleKeys = array_keys($custodyLocale);
    foreach ((array) ($custodyManifest['locales'] ?? []) as $localeFile) {
        $catalog = json_decode(
            (string) file_get_contents($custodyDirectory . '/' . $localeFile),
            true,
            128,
            JSON_THROW_ON_ERROR,
        );
        packageExpect(
            is_array($catalog) && array_keys($catalog) === $custodyLocaleKeys,
            'key custody package locales do not share one complete catalog',
        );
    }
    $dashboardSource = file_get_contents(__DIR__ . '/../index.js');
    packageExpect(
        $dashboardSource !== false &&
            str_contains(
                $dashboardSource,
                'zogograph-key-custody.remote_envelope_dialog',
            ) &&
            str_contains(
                $dashboardSource,
                'zogograph-key-custody.remote_recovery_dialog',
            ) &&
            str_contains(
                $dashboardSource,
                'zogograph-key-custody.service_url_placeholder',
            ) &&
            str_contains(
                $dashboardSource,
                'zogograph-key-custody.credential_reference_placeholder',
            ) &&
            isset(
                $custodyLocale['zogograph-key-custody.store_envelope_confirm'],
            ) &&
            isset(
                $custodyLocale[
                    'zogograph-key-custody.run_remote_drill_confirm'
                ],
            ),
        'key custody recovery controls do not use the package localization catalog',
    );
    $relayPair = sodium_crypto_sign_keypair();
    $relaySecret = sodium_crypto_sign_secretkey($relayPair);
    try {
        new \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient(
            'https://user@example.test',
            'test-token',
            base64_encode($relaySecret),
        );
        throw new RuntimeException(
            'relay client accepted an ambiguous endpoint',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'relay client accepted an ambiguous endpoint',
            $error->getMessage(),
        );
    }
    try {
        new \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient(
            'https://example.test',
            'tok_' . str_repeat('a', 20) . ".bad\r\nvalue",
            base64_encode($relaySecret),
        );
        throw new RuntimeException(
            'relay client accepted a malformed bearer credential',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'relay client accepted a malformed bearer credential',
            $error->getMessage(),
        );
    }
    try {
        new \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient(
            'https://example.test',
            'tok_' . str_repeat('a', 20) . '.good',
            base64_encode($relaySecret),
            'not-a-pin',
        );
        throw new RuntimeException('relay client accepted a malformed TLS pin');
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'relay client accepted a malformed TLS pin',
            $error->getMessage(),
        );
    }
    try {
        \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient::healthEndpoint(
            'http://example.test',
        );
        throw new RuntimeException(
            'relay health probe accepted an insecure endpoint',
        );
    } catch (InvalidArgumentException $error) {
        packageExpect(
            $error->getMessage() !==
                'relay health probe accepted an insecure endpoint',
            $error->getMessage(),
        );
    }
    $relaySource = file_get_contents(
        __DIR__ .
            '/../plugins/zogograph-replica-relay/src/ReplicaRelayHttpClient.php',
    );
    packageExpect(
        $relaySource !== false &&
            str_contains($relaySource, 'CURLOPT_UPLOAD => true') &&
            str_contains($relaySource, 'CURLOPT_SSL_VERIFYPEER => true') &&
            str_contains($relaySource, 'CURLOPT_SSL_VERIFYHOST => 2') &&
            str_contains($relaySource, 'CURLOPT_PINNEDPUBLICKEY') &&
            str_contains($relaySource, "'/v1/health'") &&
            method_exists(
                \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient::class,
                'health',
            ) &&
            method_exists(
                \HostScope\Plugin\ReplicaRelay\ReplicaRelayHttpClient::class,
                'healthEndpoint',
            ),
        'relay client does not stream ciphertext with secure transport defaults',
    );
    $relayDirectory = __DIR__ . '/../plugins/zogograph-replica-relay';
    $relayManifest = json_decode(
        (string) file_get_contents($relayDirectory . '/manifest.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    packageExpect(
        is_array($relayManifest) &&
            in_array(
                'ciphertext_replica',
                (array) ($relayManifest['capabilities'] ?? []),
                true,
            ) &&
            packageVerifyIntegrity($relayManifest, $relayDirectory)['ok'],
        'replica relay package manifest does not cover its installed files',
    );
    $pair = sodium_crypto_sign_keypair();
    $signed = packageSignManifest(
        $manifest,
        $root,
        'test-publisher',
        sodium_crypto_sign_secretkey($pair),
    );
    packageExpect(
        packageVerifyIntegrity($signed, $root)['ok'],
        'fresh package integrity did not verify',
    );
    packageExpect(
        packageVerifySignature($signed, [
            'test-publisher' => [
                'public_key' => base64_encode(
                    sodium_crypto_sign_publickey($pair),
                ),
            ],
        ])['ok'],
        'fresh package signature did not verify',
    );
    file_put_contents(
        $root . '/manifest.json',
        json_encode($signed, JSON_THROW_ON_ERROR),
    );
    if (class_exists('ZipArchive')) {
        $bundle = packageCreateReviewBundle(
            $root,
            $root . '/review.zip',
            base64_encode(sodium_crypto_sign_publickey($pair)),
        );
        packageExpect(
            $bundle['files'] === 1 && is_file($root . '/review.zip'),
            'review bundle was not created',
        );
        @unlink($root . '/review.zip');
    }
    file_put_contents($root . '/src/provider.php', "<?php return 'changed';\n");
    packageExpect(
        !packageVerifyIntegrity($signed, $root)['ok'],
        'changed package file was accepted',
    );
    echo "package integrity checks passed\n";
} finally {
    @unlink($root . '/manifest.json');
    @unlink($root . '/src/provider.php');
    @rmdir($root . '/src');
    @rmdir($root);
}
