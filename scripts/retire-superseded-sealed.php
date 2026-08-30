#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit();
}
$root = dirname(__DIR__);
$configFile = $root . '/lib/config.php';
require_once $root . '/lib/setupstore.php';
require_once $root . '/lib/db.php';
require_once $root . '/lib/encryption.php';
if (
    count($argv) !== 4 ||
    $argv[1] !== '--old-key' ||
    $argv[3] !== '--confirm'
) {
    fwrite(
        STDERR,
        "Usage: php scripts/retire-superseded-sealed.php --old-key /absolute/private/old.key --confirm\n",
    );
    exit(64);
}
try {
    if (!is_file($configFile)) {
        throw new RuntimeException('Zogograph is not configured');
    }
    $config = loadConfiguredSettings($configFile);
    $retired = encryptionRetireSupersededArtifacts(
        $config,
        db($config),
        $root,
        $argv[2],
    );
    echo "Retired {$retired} superseded sealed artifact(s). Keep independent offline recovery records as required by your policy.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Retirement refused: ' . $error->getMessage() . "\n");
    exit(1);
}

