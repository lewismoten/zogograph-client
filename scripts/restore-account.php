#!/usr/bin/env php
<?php
declare(strict_types=1);

/*
 * Deliberate CLI-only account database recovery. A web process or cron writer
 * left running can reopen an old WAL after replacement, so this refuses to run
 * until the operator explicitly acknowledges that every writer is stopped.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This recovery command is CLI-only.\n");
}

if ($argc === 4 && $argv[1] === '--from' && $argv[3] === '--writers-stopped') {
    $sourceArgument = $argv[2];
    $sealedKey = null;
} elseif (
    $argc === 6 &&
    $argv[1] === '--sealed' &&
    $argv[3] === '--key' &&
    $argv[5] === '--writers-stopped'
) {
    $sourceArgument = $argv[2];
    $sealedKey = $argv[4];
} else {
    fwrite(
        STDERR,
        "Usage:\n" .
            "  php scripts/restore-account.php --from /secure/known-good.db --writers-stopped\n" .
            "  php scripts/restore-account.php --sealed /secure/account.sealed --key \$HOME/.private/zogograph.key --writers-stopped\n",
    );
    exit(64);
}

$configFile = __DIR__ . '/../lib/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "No lib/config.php; nothing can be restored safely.\n");
    exit(1);
}

require_once __DIR__ . '/../lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/actions.php';
require_once __DIR__ . '/../lib/spans.php';
require_once __DIR__ . '/../lib/alerts.php';
require_once __DIR__ . '/../lib/retention.php';
require_once __DIR__ . '/../lib/storekeep.php';
require_once __DIR__ . '/../lib/notes.php';
require_once __DIR__ . '/../lib/encryption.php';

$source = realpath($sourceArgument);
$target = (string) ($config['database'] ?? '');
if (
    $source === false ||
    is_link($sourceArgument) ||
    !is_file($source) ||
    $target === '' ||
    !str_starts_with($target, '/') ||
    is_link($target)
) {
    fwrite(
        STDERR,
        "The source must be a readable export/artifact and the account database must be configured.\n",
    );
    exit(1);
}
if ($sealedKey === null && realpath($target) === $source) {
    fwrite(
        STDERR,
        "The source is already the live account database; refusing to replace it with itself.\n",
    );
    exit(1);
}
$targetDirectory = realpath(dirname($target));
if (
    $targetDirectory === false ||
    is_link(dirname($target)) ||
    !is_dir($targetDirectory) ||
    !is_writable($targetDirectory)
) {
    fwrite(
        STDERR,
        "The account database directory is unavailable or unsafe.\n",
    );
    exit(1);
}
$target = $targetDirectory . '/' . basename($target);

$temporary = $target . '.restore-' . bin2hex(random_bytes(8));
$decrypted = null;
$kek = null;
$rollbackPlain = null;
try {
    if ($sealedKey !== null) {
        $decrypted =
            $target . '.sealed-restore-' . bin2hex(random_bytes(8)) . '.db';
        $kek = encryptionReadLocalKey($sealedKey, dirname(__DIR__));
        encryptionOpenFile($source, $decrypted, $kek);
        $source = $decrypted;
    }
    $candidate = new PDO('sqlite:' . $source, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $integrity = databaseFullIntegrity($candidate);
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'the candidate database failed SQLite integrity_check: ' .
                implode('; ', $integrity['messages']),
        );
    }
    $hasVitals = (int) $candidate
        ->query(
            "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'vitals'",
        )
        ->fetchColumn();
    $hasSchema = (int) $candidate
        ->query(
            "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'schema_version'",
        )
        ->fetchColumn();
    if ($hasVitals !== 1 || $hasSchema !== 1) {
        throw new RuntimeException(
            'the candidate is not an account database export',
        );
    }
    $candidate = null;

    if (is_file($target)) {
        $live = new PDO('sqlite:' . $target, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $recovery =
            snapshotDirectory($config) .
            '/before-restore-account-' .
            gmdate('Ymd\THis\Z') .
            '.db';
        $made = snapshotDatabase($live, $recovery);
        $live = null;
        $recovery = $made['path'];
        if ($kek !== null) {
            $rollbackPlain = $recovery;
            $stamp = gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(4));
            $sealedRecovery =
                dirname($recovery) .
                '/before-restore-account-' .
                $stamp .
                '.sealed';
            $record = encryptionSealFile(
                $recovery,
                $sealedRecovery,
                $kek,
                'account:before-restore:' . $stamp,
            );
            encryptionVerifySealedStoreArtifact(
                $sealedRecovery,
                $kek,
                (int) $record['plaintext_bytes'],
                'account',
            );
            @unlink($recovery);
            $rollbackPlain = null;
            $recovery = $sealedRecovery;
        }
        fwrite(STDOUT, 'Saved the current database to ' . $recovery . "\n");
    }

    if (!@copy($source, $temporary)) {
        throw new RuntimeException(
            'could not copy the verified export beside the live database',
        );
    }
    @chmod($temporary, 0600);
    if (!@rename($temporary, $target)) {
        throw new RuntimeException(
            'could not atomically replace the live database',
        );
    }
    // These belong to the replaced database and must never be replayed onto it.
    @unlink($target . '-wal');
    @unlink($target . '-shm');
    fwrite(
        STDOUT,
        "Restored account database. Start the web app and cron only after this command exits.\n",
    );
} catch (Throwable $e) {
    @unlink($temporary);
    // exit does not run finally on every supported PHP runtime.
    if ($decrypted !== null) {
        @unlink($decrypted);
    }
    if ($rollbackPlain !== null) {
        @unlink($rollbackPlain);
    }
    fwrite(STDERR, 'Restore refused: ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($decrypted !== null) {
        @unlink($decrypted);
    }
}

