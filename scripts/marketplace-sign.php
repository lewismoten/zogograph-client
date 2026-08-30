#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Publisher helper: it only reads a named local manifest and writes signed JSON
 * to stdout. Private keys are never stored by Zogograph. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This publisher command is CLI-only.\n");
}

require_once __DIR__ . '/../lib/marketplace.php';

function marketplaceSignUsage(): never
{
    fwrite(
        STDERR,
        "Usage:\n" .
            "  php scripts/marketplace-sign.php keygen --key-id ID --publisher NAME\n" .
            "  php scripts/marketplace-sign.php sign --manifest /secure/package.json --key-id ID [--stdin]\n",
    );
    exit(64);
}

/** Reads a Base64 private signing key without placing it in a process listing. */
function marketplaceSignSecret(bool $stdin): string
{
    if ($stdin) {
        return trim((string) stream_get_contents(STDIN));
    }
    $hidden =
        function_exists('shell_exec') &&
        !in_array(
            'shell_exec',
            array_map(
                'trim',
                explode(',', (string) ini_get('disable_functions')),
            ),
            true,
        ) &&
        stripos(PHP_OS_FAMILY, 'win') !== 0 &&
        stream_isatty(STDIN);
    fwrite(
        STDERR,
        'Base64 Ed25519 private key' .
            ($hidden ? ': ' : ' (input will be visible): '),
    );
    if ($hidden) {
        @shell_exec('stty -echo');
    }
    $line = fgets(STDIN);
    if ($hidden) {
        @shell_exec('stty echo');
        fwrite(STDERR, "\n");
    }
    return trim((string) $line);
}

if (!function_exists('sodium_crypto_sign_keypair')) {
    fwrite(
        STDERR,
        "PHP sodium support is required for marketplace publisher signing.\n",
    );
    exit(1);
}

$command = $argv[1] ?? '';
$options = [];
for ($index = 2; $index < $argc; $index++) {
    $option = $argv[$index];
    if ($option === '--stdin') {
        $options['stdin'] = true;
        continue;
    }
    if (
        !in_array($option, ['--key-id', '--publisher', '--manifest'], true) ||
        ++$index >= $argc ||
        isset($options[$option])
    ) {
        marketplaceSignUsage();
    }
    $options[$option] = $argv[$index];
}

if ($command === 'keygen') {
    $keyId = trim((string) ($options['--key-id'] ?? ''));
    $publisher = trim((string) ($options['--publisher'] ?? ''));
    if (
        isset($options['stdin']) ||
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $keyId) ||
        $publisher === '' ||
        mb_strlen($publisher) > 120
    ) {
        marketplaceSignUsage();
    }
    $pair = sodium_crypto_sign_keypair();
    $public = base64_encode(sodium_crypto_sign_publickey($pair));
    $private = base64_encode(sodium_crypto_sign_secretkey($pair));
    fwrite(
        STDOUT,
        "Add this public key to lib/config.php:\n\n" .
            "'trusted_keys' => [\n    '" .
            $keyId .
            "' => ['publisher' => " .
            var_export($publisher, true) .
            ", 'public_key' => '" .
            $public .
            "'],\n],\n\n" .
            "Private signing key — store it offline; Zogograph will not retain it:\n" .
            $private .
            "\n",
    );
    exit(0);
}

if (
    $command !== 'sign' ||
    !isset($options['--manifest'], $options['--key-id'])
) {
    marketplaceSignUsage();
}
$keyId = trim((string) $options['--key-id']);
$path = realpath((string) $options['--manifest']);
if (
    !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $keyId) ||
    $path === false ||
    !is_file($path) ||
    !is_readable($path) ||
    filesize($path) > MARKETPLACE_MAX_MANIFEST_BYTES
) {
    fwrite(STDERR, "The manifest path or publisher key id is invalid.\n");
    exit(1);
}

try {
    $manifest = marketplaceManifestFromJson((string) file_get_contents($path));
    $secret = base64_decode(
        marketplaceSignSecret(!empty($options['stdin'])),
        true,
    );
    if (
        $secret === false ||
        strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
    ) {
        throw new InvalidArgumentException('private key is invalid');
    }
    $payload = $manifest;
    unset($payload['signature']);
    $manifest['signature'] = [
        'key_id' => $keyId,
        'algorithm' => MARKETPLACE_SIGNATURE_ALGORITHM,
        'value' => base64_encode(
            sodium_crypto_sign_detached(
                marketplaceCanonicalJson($payload),
                $secret,
            ),
        ),
    ];
    fwrite(STDOUT, marketplaceManifestJson($manifest));
} catch (Throwable $error) {
    fwrite(STDERR, 'Signing refused: ' . $error->getMessage() . "\n");
    exit(1);
}

