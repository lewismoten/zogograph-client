#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Rotates every currently verified local sealed artifact before changing the
 * configured key. Old ciphertext is retained as superseded recovery evidence. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root = dirname(__DIR__); $configFile = $root . '/lib/config.php';
require_once $root . '/lib/setupstore.php';
require_once $root . '/lib/db.php';
require_once $root . '/lib/encryption.php';

function rotationUsage(): never
{
    fwrite(STDERR, "Usage: php scripts/rotate-sealed-copies.php --new-key /absolute/private/replacement.key --confirm\n");
    exit(64);
}

$options = []; $arguments = array_slice($argv, 1);
for ($index = 0; $index < count($arguments); $index++) {
    $argument = $arguments[$index];
    if ($argument === '--confirm') { $options['confirm'] = true; continue; }
    if ($argument !== '--new-key' || isset($options['new-key']) || !isset($arguments[++$index])) rotationUsage();
    $options['new-key'] = $arguments[$index];
}
if (empty($options['confirm']) || !isset($options['new-key']) || !is_file($configFile)) rotationUsage();

try {
    $config = loadConfiguredSettings($configFile); $newPath = (string)$options['new-key']; $pdo = db($config);
    $created = encryptionRotateConfiguredArtifacts($config, $pdo, $root, $newPath,
        function (array $updated) use ($configFile, $root): void { saveConfiguredSettings($updated, $configFile, $root . '/lib'); });
    echo 'Rotated ' . count($created) . " sealed artifact(s) and switched the configured local key.\n";
    echo "Old artifacts remain as superseded recovery evidence. Keep the old key until you have run --verify-sealed and completed a restore drill with the replacement key.\n";
} catch (Throwable $error) { fwrite(STDERR, 'Rotation refused: ' . $error->getMessage() . "\n"); exit(1); }
