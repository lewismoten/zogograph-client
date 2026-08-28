<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/alerts.php';

function encryptionAlertExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
if (!encryptionAvailable()) { echo "encryption alert checks skipped: OpenSSL AES-256-GCM is unavailable\n"; exit(0); }
$root = sys_get_temp_dir() . '/zogograph-encryption-alerts-' . bin2hex(random_bytes(4)); mkdir($root, 0700, true);
try {
    $key = $root . '/local.key'; encryptionCreateLocalKey($key, dirname(__DIR__));
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE TABLE encryption_artifacts (store TEXT, created_at INTEGER, last_recovery_drill_at INTEGER, state TEXT)');
    $config = ['encryption' => ['mode' => 'sealed_copies_local', 'local_key_file' => $key, 'stores' => ['account'], 'backup_schedule' => 'daily']];
    encryptionAlertExpect(isset(alertCatalogue()['encryption_recovery']), 'encryption recovery rule is not configurable in the alert catalogue');
    $config['database'] = $root . '/stats.db';
    $setup = setupStoreOpen($config, true); $setup->prepare('INSERT INTO setup_values (name, value, updated_at) VALUES (?, ?, ?)')->execute(['config', '{}', time()]);
    $setupAlert = setupConfigurationEncryptionAlerts($config);
    encryptionAlertExpect(count($setupAlert) === 1 && $setupAlert[0]['severity'] === 'warning', 'plaintext setup configuration did not raise an at-rest warning after a local key was configured');
    $setup->prepare('DELETE FROM setup_values WHERE name = ?')->execute(['config']);
    $setup->prepare('INSERT INTO setup_values (name, value, updated_at) VALUES (?, ?, ?)')->execute(['config_encrypted', setupStoreSealConfig('{}', $key, dirname(__DIR__)), time()]);
    encryptionAlertExpect(setupConfigurationEncryptionAlerts($config) === [], 'verified encrypted setup configuration still raised an alert');
    $missing = encryptionRecoveryAlerts($config, $pdo);
    encryptionAlertExpect(count($missing) === 1 && $missing[0]['severity'] === 'warning' && str_contains($missing[0]['detail'], 'No verified encrypted copy'),
        'missing encrypted-copy evidence did not raise a warning');
    $pdo->prepare("INSERT INTO encryption_artifacts VALUES ('account', ?, NULL, 'verified')")->execute([time()]);
    $unrehearsed = encryptionRecoveryAlerts($config, $pdo);
    encryptionAlertExpect(count($unrehearsed) === 1 && str_contains($unrehearsed[0]['detail'], 'No successful non-destructive recovery drill'),
        'missing initial encrypted-copy recovery drill did not raise a warning');
    $pdo->exec('UPDATE encryption_artifacts SET last_recovery_drill_at = ' . time());
    encryptionAlertExpect(encryptionRecoveryAlerts($config, $pdo) === [], 'current drilled encrypted-copy evidence still raised an alert');
    $config['encryption']['recovery_drill_max_age_days'] = 1;
    $pdo->prepare('UPDATE encryption_artifacts SET last_recovery_drill_at = ?')->execute([time() - 2 * 86400]);
    $overdueDrill = encryptionRecoveryAlerts($config, $pdo);
    encryptionAlertExpect(count($overdueDrill) === 1 && str_contains($overdueDrill[0]['detail'], 'older than the selected review period'),
        'overdue encrypted-copy recovery drill did not raise a warning');
    $pdo->exec('UPDATE encryption_artifacts SET last_recovery_drill_at = ' . time());
    $pdo->prepare('UPDATE encryption_artifacts SET created_at = ?')->execute([time() - 3 * 86400]);
    $stale = encryptionRecoveryAlerts($config, $pdo);
    encryptionAlertExpect(count($stale) === 1 && str_contains($stale[0]['detail'], 'older than the selected schedule'),
        'stale encrypted-copy evidence did not raise a warning');
    $pdo->exec("UPDATE encryption_artifacts SET state = 'verification_failed'");
    $failed = encryptionRecoveryAlerts($config, $pdo);
    encryptionAlertExpect(count($failed) === 1 && $failed[0]['severity'] === 'error' && str_contains($failed[0]['detail'], 'failed verification'),
        'failed encrypted-copy evidence did not raise an error');
    $config['encryption']['mode'] = 'operator_managed_volume';
    encryptionAlertExpect(encryptionRecoveryAlerts($config, $pdo) === [], 'unverifiable volume protection raised an encryption recovery alert');
    echo "encryption alert checks passed\n";
} finally { @unlink($root . '/local.key'); @rmdir($root); }
