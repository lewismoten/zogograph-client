#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Creates a local sealed-copy KEK without putting it in the web tree or config. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../lib/encryption.php';

$args = array_slice($argv, 1);
$command = array_shift($args);
$root = dirname(__DIR__);
if ($command === 'create' && count($args) === 1) {
    encryptionCreateLocalKey($args[0], $root);
    echo "Created local encryption key at {$args[0]} (mode 0600). Store a recovery copy offline.\n";
    exit(0);
}
if ($command === 'decrypt' && count($args) === 3) {
    encryptionOpenFile($args[1], $args[2], encryptionReadLocalKey($args[0], $root));
    echo "Decrypted copy written to {$args[2]}. Keep it private and remove it when finished.\n";
    exit(0);
}
if ($command === 'rotate' && count($args) === 5) {
    [$oldPath, $newPath, $source, $destination, $artifactId] = $args;
    encryptionRotateSealedFile($source, $destination, encryptionReadLocalKey($oldPath, $root),
        encryptionReadLocalKey($newPath, $root), $artifactId);
    echo "New sealed copy written to {$destination}. Verify recovery before retiring the old copy.\n";
    exit(0);
}
fwrite(STDERR, "Usage:\n"
    . "  php scripts/encryption-key.php create /absolute/private/hostscope.key\n"
    . "  php scripts/encryption-key.php decrypt /private/key source.sealed output.db\n"
    . "  php scripts/encryption-key.php rotate /old/key /new/key source.sealed replacement.sealed artifact-id\n");
fwrite(STDERR, "For a configured installation, use rotate-sealed-copies.php to rotate every retained artifact before switching keys.\n");
exit(2);
