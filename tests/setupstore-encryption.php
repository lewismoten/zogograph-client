<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/encryption.php';
require_once __DIR__ . '/../lib/setupstore.php';

function setupVaultExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
if (!encryptionAvailable()) { echo "setup-store encryption checks skipped: encryption is unavailable\n"; exit(0); }
$root = sys_get_temp_dir() . '/zogograph-setup-vault-' . bin2hex(random_bytes(4));
mkdir($root . '/data', 0700, true); mkdir($root . '/web', 0700, true); mkdir($root . '/keys', 0700, true);
try {
    $configFile = $root . '/web/config.php'; $key = $root . '/keys/setup.key';
    encryptionCreateLocalKey($key, $root . '/web');
    $settings = ['database' => $root . '/data/stats.db', 'scan_root' => $root, 'timezone' => 'UTC',
        'password_hash' => 'sensitive-hash', 'encryption' => ['local_key_file' => $key]];
    saveConfiguredSettings($settings, $configFile, $root . '/web/lib');
    $bootstrap = require $configFile;
    setupVaultExpect(($bootstrap['setup_key_file'] ?? null) === $key, 'bootstrap did not retain the private setup-key locator');
    $store = setupStoreOpen($bootstrap, false);
    setupVaultExpect($store instanceof PDO && $store->query("SELECT value FROM setup_values WHERE name = 'config'")->fetchColumn() === false,
        'plaintext configuration remained in setup.db');
    $sealed = $store->query("SELECT value FROM setup_values WHERE name = 'config_encrypted'")->fetchColumn();
    setupVaultExpect(is_string($sealed) && !str_contains($sealed, 'sensitive-hash'), 'configuration was not encrypted in setup.db');
    setupVaultExpect(loadConfiguredSettings($configFile)['password_hash'] === 'sensitive-hash', 'encrypted configuration could not be loaded');
    $protection = setupStoreConfigurationProtection($configFile);
    setupVaultExpect($protection['state'] === 'ok' && str_contains($protection['detail'], 'encrypted'),
        'encrypted setup configuration was not reported as protected');
    setupVaultExpect(setupStoreConfigurationProtectionForSettings($settings, $root . '/web')['state'] === 'ok',
        'dashboard settings could not report encrypted setup configuration');
    $badBootstrap = $bootstrap; $badBootstrap['setup_key_file'] = $root . '/keys/missing.key';
    $badPath = $root . '/web/bad.php'; file_put_contents($badPath, "<?php return " . var_export($badBootstrap, true) . ";\n");
    try { loadConfiguredSettings($badPath); throw new RuntimeException('encrypted configuration fell back without its key'); }
    catch (RuntimeException $error) { setupVaultExpect($error->getMessage() !== 'encrypted configuration fell back without its key', $error->getMessage()); }
    echo "setup-store encryption checks passed\n";
} finally {
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($items as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    @rmdir($root);
}
