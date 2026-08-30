#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This publisher command is CLI-only.\n");
}
require_once __DIR__ . '/../lib/package-integrity.php';

function packageSignUsage(): never
{
    fwrite(
        STDERR,
        "Usage:\n" .
            "  php scripts/package-sign.php keygen --key-id ID --publisher NAME\n" .
            "  php scripts/package-sign.php sign --package /secure/package-directory --key-id ID [--stdin] [--write]\n" .
            "  php scripts/package-sign.php verify --package /secure/package-directory --public-key BASE64\n",
    );
    exit(64);
}

function packageSignSecret(bool $stdin): string
{
    if ($stdin) {
        return trim((string) stream_get_contents(STDIN));
    }
    fwrite(STDERR, 'Base64 Ed25519 private key (input will be visible): ');
    return trim((string) fgets(STDIN));
}

if (!function_exists('sodium_crypto_sign_keypair')) {
    fwrite(STDERR, "PHP sodium support is required for package signing.\n");
    exit(1);
}
$command = $argv[1] ?? '';
$options = [];
for ($index = 2; $index < $argc; $index++) {
    $option = $argv[$index];
    if (in_array($option, ['--stdin', '--write'], true)) {
        $options[$option] = true;
        continue;
    }
    if (
        !in_array(
            $option,
            ['--key-id', '--publisher', '--package', '--public-key'],
            true,
        ) ||
        ++$index >= $argc ||
        isset($options[$option])
    ) {
        packageSignUsage();
    }
    $options[$option] = $argv[$index];
}
if ($command === 'keygen') {
    $keyId = trim((string) ($options['--key-id'] ?? ''));
    $publisher = trim((string) ($options['--publisher'] ?? ''));
    if (
        isset($options['--stdin']) ||
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $keyId) ||
        $publisher === ''
    ) {
        packageSignUsage();
    }
    $pair = sodium_crypto_sign_keypair();
    fwrite(
        STDOUT,
        "Configure this publisher key in Zogograph package trust settings:\n" .
            json_encode(
                [
                    'key_id' => $keyId,
                    'publisher' => $publisher,
                    'public_key' => base64_encode(
                        sodium_crypto_sign_publickey($pair),
                    ),
                ],
                JSON_PRETTY_PRINT,
            ) .
            "\n\n" .
            "Private signing key — store offline; Zogograph will not retain it:\n" .
            base64_encode(sodium_crypto_sign_secretkey($pair)) .
            "\n",
    );
    exit(0);
}
if ($command === 'verify') {
    try {
        $directory = realpath((string) ($options['--package'] ?? ''));
        if ($directory === false || !is_file($directory . '/manifest.json')) {
            throw new InvalidArgumentException(
                'package manifest is unavailable',
            );
        }
        $manifest = json_decode(
            (string) file_get_contents($directory . '/manifest.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        if (!is_array($manifest) || !isset($options['--public-key'])) {
            throw new InvalidArgumentException(
                'package or public key is invalid',
            );
        }
        $integrity = packageVerifyIntegrity($manifest, $directory);
        $keyId = (string) (($manifest['signature'] ?? [])['key_id'] ?? '');
        $signature = packageVerifySignature($manifest, [
            $keyId => ['public_key' => (string) $options['--public-key']],
        ]);
        if (!$integrity['ok'] || !$signature['ok']) {
            throw new RuntimeException(
                $integrity['ok'] ? $signature['detail'] : $integrity['detail'],
            );
        }
        fwrite(STDOUT, "Package signature and file integrity verified.\n");
        exit(0);
    } catch (Throwable $error) {
        fwrite(STDERR, 'Verification failed: ' . $error->getMessage() . "\n");
        exit(1);
    }
}
if (
    $command !== 'sign' ||
    !isset($options['--package'], $options['--key-id'])
) {
    packageSignUsage();
}
try {
    $directory = realpath((string) $options['--package']);
    if ($directory === false || !is_file($directory . '/manifest.json')) {
        throw new InvalidArgumentException('package manifest is unavailable');
    }
    $manifest = json_decode(
        (string) file_get_contents($directory . '/manifest.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    if (!is_array($manifest)) {
        throw new InvalidArgumentException('package manifest is not an object');
    }
    $secret = base64_decode(
        packageSignSecret(!empty($options['--stdin'])),
        true,
    );
    if ($secret === false) {
        throw new InvalidArgumentException('private key is invalid');
    }
    $json =
        json_encode(
            packageSignManifest(
                $manifest,
                $directory,
                (string) $options['--key-id'],
                $secret,
            ),
            JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR,
        ) . "\n";
    if (isset($options['--write'])) {
        $temporary = tempnam($directory, '.manifest-');
        if (
            $temporary === false ||
            file_put_contents($temporary, $json, LOCK_EX) !== strlen($json) ||
            !rename($temporary, $directory . '/manifest.json')
        ) {
            @unlink((string) $temporary);
            throw new RuntimeException(
                'could not atomically write the signed manifest',
            );
        }
        fwrite(
            STDOUT,
            "Signed manifest written to {$directory}/manifest.json\n",
        );
    } else {
        fwrite(STDOUT, $json);
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Signing refused: ' . $error->getMessage() . "\n");
    exit(1);
}

