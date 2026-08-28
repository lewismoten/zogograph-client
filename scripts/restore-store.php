#!/usr/bin/env php
<?php
declare(strict_types=1);

/* CLI-only recovery for the separately sealed SQLite stores. The operator must
 * stop every web, collector, and cron writer before replacing a WAL database. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("This recovery command is CLI-only.\n"); }

if ($argc === 6 && $argv[1] === '--store' && $argv[3] === '--from' && $argv[5] === '--writers-stopped') {
    [$store, $sourceArgument, $sealedKey] = [$argv[2], $argv[4], null];
} elseif ($argc === 8 && $argv[1] === '--store' && $argv[3] === '--sealed' && $argv[5] === '--key' && $argv[7] === '--writers-stopped') {
    [$store, $sourceArgument, $sealedKey] = [$argv[2], $argv[4], $argv[6]];
} else {
    fwrite(STDERR, "Usage:\n"
        . "  php scripts/restore-store.php --store setup|account|host|ai --from /secure/known-good.db --writers-stopped\n"
        . "  php scripts/restore-store.php --store setup|account|host|ai --sealed /secure/store.sealed --key \$HOME/.private/zogograph.key --writers-stopped\n");
    exit(64);
}

if (!in_array($store, ['setup', 'account', 'host', 'ai'], true)) { fwrite(STDERR, "Store must be setup, account, host, or ai.\n"); exit(64); }
$configFile = __DIR__ . '/../lib/config.php';
if (!is_file($configFile)) { fwrite(STDERR, "No lib/config.php; nothing can be restored safely.\n"); exit(1); }

require_once __DIR__ . '/../lib/setupstore.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/encryption.php';
require_once __DIR__ . '/../lib/storekeep.php';
$bootstrap = require $configFile;
if (!is_array($bootstrap)) { fwrite(STDERR, "The bootstrap configuration is invalid.\n"); exit(1); }
/* Restoring setup.db must not first decrypt the damaged setup.db. The small
 * bootstrap intentionally retains its database path and external key locator. */
$config = $store === 'setup' ? $bootstrap : loadConfiguredSettings($configFile);
$base = dirname((string)($config['database'] ?? ''));
$targets = [
    'setup' => setupStorePath($config),
    'account' => (string)($config['database'] ?? ''),
    'host' => (string)($config['host']['database'] ?? '') ?: $base . '/host.db',
    'ai' => (string)($config['ai']['database'] ?? '') ?: $base . '/ai.db',
];
$expected = ['setup' => 'setup_values', 'account' => 'vitals', 'host' => 'host_samples', 'ai' => 'ai_sessions'];
$target = $targets[$store]; $source = realpath($sourceArgument);
if ($source === false || is_link($sourceArgument) || !is_file($source) || $target === '' || !str_starts_with($target, '/') || is_link($target)) {
    fwrite(STDERR, "The source must be readable and the selected store target must be an absolute configured path.\n"); exit(1);
}
if ($sealedKey === null && realpath($target) === $source) { fwrite(STDERR, "The source is already the live store; refusing to replace it with itself.\n"); exit(1); }
$targetDirectory = realpath(dirname($target));
if ($targetDirectory === false || is_link(dirname($target)) || !is_dir($targetDirectory) || !is_writable($targetDirectory)) { fwrite(STDERR, "The selected store directory is unavailable or unsafe.\n"); exit(1); }
$target = $targetDirectory . '/' . basename($target);

$temporary = $target . '.restore-' . bin2hex(random_bytes(8)); $decrypted = null; $kek = null; $rollbackPlain = null;
try {
    if ($sealedKey !== null) {
        $decrypted = $target . '.sealed-restore-' . bin2hex(random_bytes(8)) . '.db';
        $kek = encryptionReadLocalKey($sealedKey, dirname(__DIR__));
        encryptionOpenFile($source, $decrypted, $kek);
        $source = $decrypted;
    }
    $candidate = new PDO('sqlite:' . $source, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $integrity = databaseFullIntegrity($candidate);
    if (!$integrity['ok']) throw new RuntimeException('the candidate database failed SQLite integrity_check: ' . implode('; ', $integrity['messages']));
    $table = $candidate->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = ?"); $table->execute([$expected[$store]]);
    if ((int)$table->fetchColumn() !== 1) throw new RuntimeException('the candidate is not a ' . $store . ' database export');
    $candidate = null;
    if (is_file($target)) {
        $live = new PDO('sqlite:' . $target, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stamp = gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(4));
        $recovery = dirname($target) . '/before-restore-' . $store . '-' . $stamp . '.db';
        $live->exec('VACUUM INTO ' . $live->quote($recovery)); $live = null; @chmod($recovery, 0600);
        if ($kek !== null) {
            $rollbackPlain = $recovery;
            $sealedRecovery = dirname($target) . '/before-restore-' . $store . '-' . $stamp . '.sealed';
            $record = encryptionSealFile($recovery, $sealedRecovery, $kek, $store . ':before-restore:' . $stamp);
            encryptionVerifySealedStoreArtifact($sealedRecovery, $kek, (int)$record['plaintext_bytes'], $store);
            @unlink($recovery); $rollbackPlain = null; $recovery = $sealedRecovery;
        }
        fwrite(STDOUT, 'Saved the current database to ' . $recovery . "\n");
    }
    if (!@copy($source, $temporary)) throw new RuntimeException('could not copy the verified export beside the live database');
    @chmod($temporary, 0600);
    if (!@rename($temporary, $target)) throw new RuntimeException('could not atomically replace the live database');
    @unlink($target . '-wal'); @unlink($target . '-shm');
    fwrite(STDOUT, "Restored {$store} database. Start writers only after this command exits.\n");
} catch (Throwable $error) {
    @unlink($temporary); if ($decrypted !== null) @unlink($decrypted); if ($rollbackPlain !== null) @unlink($rollbackPlain);
    fwrite(STDERR, 'Restore refused: ' . $error->getMessage() . "\n"); exit(1);
} finally { if ($decrypted !== null) @unlink($decrypted); }
