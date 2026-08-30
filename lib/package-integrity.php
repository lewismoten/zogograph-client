<?php
declare(strict_types=1);

/* Integrity contract for future executable Zogograph packages. A signature
 * protects the manifest and the manifest protects every immutable package
 * file. Runtime package data is deliberately excluded. */

const PACKAGE_SIGNATURE_ALGORITHM = 'ed25519';
const PACKAGE_MAX_FILES = 1000;
const PACKAGE_MAX_FILE_BYTES = 33554432;

/** @return array<string, string> relative path => lowercase SHA-256 */
function packageFileHashes(string $directory): array
{
    $root = realpath($directory);
    if ($root === false || !is_dir($root) || is_link($directory)) {
        throw new InvalidArgumentException(
            'package directory is unavailable or unsafe',
        );
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if (is_link($path)) {
            throw new InvalidArgumentException(
                'package files must not be symbolic links',
            );
        }
        if (!$file->isFile() || !$file->isReadable()) {
            throw new InvalidArgumentException(
                'package contains an unreadable non-file',
            );
        }
        $relative = substr($path, strlen($root) + 1);
        if (
            $relative === 'manifest.json' ||
            str_starts_with($relative, 'data/')
        ) {
            continue;
        }
        if (
            $file->getSize() > PACKAGE_MAX_FILE_BYTES ||
            count($files) >= PACKAGE_MAX_FILES
        ) {
            throw new InvalidArgumentException(
                'package contains too many or too large files',
            );
        }
        $hash = hash_file('sha256', $path);
        if (!is_string($hash)) {
            throw new RuntimeException('could not hash a package file');
        }
        $files[str_replace(DIRECTORY_SEPARATOR, '/', $relative)] = $hash;
    }
    ksort($files, SORT_STRING);
    return $files;
}

/** Stable bytes: object keys are lexical and arrays retain their order. */
function packageCanonicalJson(mixed $value): string
{
    if (is_array($value)) {
        if (array_is_list($value)) {
            return '[' .
                implode(',', array_map(packageCanonicalJson(...), $value)) .
                ']';
        }
        ksort($value, SORT_STRING);
        $items = [];
        foreach ($value as $key => $item) {
            $items[] =
                json_encode((string) $key, JSON_UNESCAPED_SLASHES) .
                ':' .
                packageCanonicalJson($item);
        }
        return '{' . implode(',', $items) . '}';
    }
    return json_encode(
        $value,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
}

/** @return array<string, mixed> */
function packageWithIntegrity(array $manifest, string $directory): array
{
    $manifest['integrity'] = [
        'algorithm' => 'sha256',
        'files' => packageFileHashes($directory),
    ];
    unset($manifest['signature']);
    return $manifest;
}

/** @return array<string, mixed> */
function packageSignManifest(
    array $manifest,
    string $directory,
    string $keyId,
    string $secret,
): array {
    if (!function_exists('sodium_crypto_sign_detached')) {
        throw new RuntimeException(
            'PHP sodium support is required for package signing',
        );
    }
    if (
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $keyId) ||
        strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
    ) {
        throw new InvalidArgumentException('package signing key is invalid');
    }
    $manifest = packageWithIntegrity($manifest, $directory);
    $manifest['signature'] = [
        'key_id' => $keyId,
        'algorithm' => PACKAGE_SIGNATURE_ALGORITHM,
        'value' => base64_encode(
            sodium_crypto_sign_detached(
                packageCanonicalJson($manifest),
                $secret,
            ),
        ),
    ];
    return $manifest;
}

/** @return array{ok: bool, detail: string} */
function packageVerifyIntegrity(array $manifest, string $directory): array
{
    $integrity = $manifest['integrity'] ?? null;
    if (
        !is_array($integrity) ||
        ($integrity['algorithm'] ?? null) !== 'sha256' ||
        !is_array($integrity['files'] ?? null)
    ) {
        return [
            'ok' => false,
            'detail' => 'package integrity metadata is missing or unsupported',
        ];
    }
    try {
        $actual = packageFileHashes($directory);
    } catch (Throwable $error) {
        return ['ok' => false, 'detail' => $error->getMessage()];
    }
    $declared = $integrity['files'];
    ksort($declared, SORT_STRING);
    if (array_keys($declared) !== array_keys($actual)) {
        return [
            'ok' => false,
            'detail' => 'package files differ from the signed manifest',
        ];
    }
    foreach ($actual as $path => $hash) {
        if (
            !is_string($declared[$path]) ||
            !hash_equals($hash, strtolower($declared[$path]))
        ) {
            return [
                'ok' => false,
                'detail' => 'package file hash differs: ' . $path,
            ];
        }
    }
    return [
        'ok' => true,
        'detail' => 'all package files match the signed manifest',
    ];
}

/** @return array{ok: bool, detail: string, key_id: ?string} */
function packageVerifySignature(array $manifest, array $trustedKeys): array
{
    $signature = $manifest['signature'] ?? null;
    if (
        !is_array($signature) ||
        ($signature['algorithm'] ?? null) !== PACKAGE_SIGNATURE_ALGORITHM
    ) {
        return [
            'ok' => false,
            'detail' => 'package signature is missing or unsupported',
            'key_id' => null,
        ];
    }
    $keyId = (string) ($signature['key_id'] ?? '');
    $public = base64_decode(
        (string) (($trustedKeys[$keyId] ?? [])['public_key'] ?? ''),
        true,
    );
    $value = base64_decode((string) ($signature['value'] ?? ''), true);
    if (
        !function_exists('sodium_crypto_sign_verify_detached') ||
        $public === false ||
        strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ||
        $value === false ||
        strlen($value) !== SODIUM_CRYPTO_SIGN_BYTES
    ) {
        return [
            'ok' => false,
            'detail' =>
                'package key, signature, or sodium support is unavailable',
            'key_id' => $keyId,
        ];
    }
    $payload = $manifest;
    unset($payload['signature']);
    return sodium_crypto_sign_verify_detached(
        $value,
        packageCanonicalJson($payload),
        $public,
    )
        ? [
            'ok' => true,
            'detail' => 'package manifest signature verified',
            'key_id' => $keyId,
        ]
        : [
            'ok' => false,
            'detail' => 'package manifest signature is invalid',
            'key_id' => $keyId,
        ];
}
