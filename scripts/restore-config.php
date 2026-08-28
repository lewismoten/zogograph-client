#!/usr/bin/env php
<?php
declare(strict_types=1);

/* CLI-only, explicit recovery for the sealed bootstrap configuration. It
 * parses recovered PHP without including it, backs up the current bootstrap,
 * then atomically replaces it. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("This recovery command is CLI-only.\n"); }
if ($argc !== 6 || $argv[1] !== '--sealed' || $argv[3] !== '--key' || $argv[5] !== '--confirm') {
    fwrite(STDERR, "Usage: php scripts/restore-config.php --sealed /secure/config.sealed --key \$HOME/.private/zogograph.key --confirm\n");
    exit(64);
}
require_once __DIR__ . '/../lib/encryption.php';
$root = dirname(__DIR__); $target = $root . '/lib/config.php';
try {
    $backup = encryptionRestoreBootstrapConfiguration($argv[2], $argv[4], $target, $root, $root . '/data/recovery');
    fwrite(STDOUT, 'Restored bootstrap configuration' . ($backup === null ? '.' : "; saved a sealed prior copy to {$backup}.") . "\n");
} catch (Throwable $error) {
    fwrite(STDERR, 'Restore refused: ' . $error->getMessage() . "\n"); exit(1);
}
