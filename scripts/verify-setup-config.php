#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Reports only encryption status, never configuration content or key paths. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This verification command is CLI-only.\n");
}
require_once __DIR__ . '/../lib/setupstore.php';

$args = array_slice($argv, 1);
$config = dirname(__DIR__) . '/lib/config.php';
$expectEncrypted = false;
while ($args !== []) {
    $argument = array_shift($args);
    if ($argument === '--expect-encrypted') {
        $expectEncrypted = true;
        continue;
    }
    if ($argument === '--config' && count($args) > 0) {
        $config = (string) array_shift($args);
        continue;
    }
    fwrite(
        STDERR,
        "Usage: php scripts/verify-setup-config.php [--config /absolute/lib/config.php] [--expect-encrypted]\n",
    );
    exit(64);
}

if ($config === '' || $config[0] !== '/' || !is_file($config)) {
    fwrite(STDERR, "Setup configuration is unavailable.\n");
    exit(1);
}
$status = setupStoreConfigurationProtection($config);
if ($status['state'] === 'ok') {
    fwrite(STDOUT, "Setup configuration encryption is verified.\n");
    exit(0);
}
if ($status['state'] === 'warning' && !$expectEncrypted) {
    fwrite(
        STDOUT,
        'Setup configuration is not encrypted at rest: ' .
            $status['detail'] .
            "\n",
    );
    exit(0);
}
fwrite(
    STDERR,
    'Setup configuration verification failed: ' . $status['detail'] . "\n",
);
exit(1);

