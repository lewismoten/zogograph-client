<?php
declare(strict_types=1);
require_once __DIR__ . '/encryption.php';

/*
 * Durable setup configuration, separate from the measurement database.
 *
 * config.php remains the boot strap: it tells PHP where data lives before any
 * database can be opened. Once setup.db exists, everything else is loaded from
 * it. Existing installs keep working because a full legacy config is also a
 * valid bootstrap until setup migrates it.
 */

function setupStorePath(array $bootstrap): string
{
    return dirname((string)($bootstrap['database'] ?? __DIR__ . '/../data/stats.db')) . '/setup.db';
}

function setupStoreOpen(array $bootstrap, bool $create = false): ?PDO
{
    $path = setupStorePath($bootstrap);
    if (!str_starts_with($path, '/') || is_link($path)) throw new RuntimeException('setup database path must be absolute and must not be a symbolic link');
    if (!$create && !is_file($path)) return null;
    $dir = dirname($path);
    if (is_link($dir)) throw new RuntimeException('setup database directory must not be a symbolic link');
    if ($create && !is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('could not create the setup data directory');
    }
    $real = realpath($dir);
    if ($real === false || !is_dir($real) || is_link($dir)) throw new RuntimeException('setup database directory is unavailable or unsafe');
    $path = $real . '/' . basename($path);
    if (is_link($path)) throw new RuntimeException('setup database path must not be a symbolic link');
    $previousUmask = umask(0077);
    try {
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    if ($create) {
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('CREATE TABLE IF NOT EXISTS setup_values (
            name TEXT PRIMARY KEY, value TEXT NOT NULL, updated_at INTEGER NOT NULL
        )');
        setupStorePrivateSqliteFiles($path);
    }
    return $pdo;
    } finally { umask($previousUmask); }
}

/** setup.db and its SQLite journals carry credentials and policy. */
function setupStorePrivateSqliteFiles(string $path): void
{
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) if (is_file($file) && !is_link($file)) @chmod($file, 0600);
}

/** @return array{state:string,detail:string} Safe at-rest status for setup UI. */
function setupStoreConfigurationProtection(string $configFile): array
{
    if (!is_file($configFile)) return ['state' => 'warning',
        'detail' => 'No saved configuration yet. Configure a local key to encrypt setup settings after the first save.'];
    try {
        $bootstrap = require $configFile;
        if (!is_array($bootstrap)) throw new RuntimeException('bootstrap config is invalid');
        $store = setupStoreOpen($bootstrap, false);
        if ($store === null) return ['state' => 'warning',
            'detail' => 'The setup database has not been created yet; no full configuration is stored.'];
        $sealed = $store->query("SELECT value FROM setup_values WHERE name = 'config_encrypted'")->fetchColumn();
        if (is_string($sealed) && $sealed !== '') {
            setupStoreOpenConfig($sealed, (string)($bootstrap['setup_key_file'] ?? ''));
            return ['state' => 'ok',
                'detail' => 'The full setup configuration is encrypted with the configured external local key.'];
        }
        $plain = $store->query("SELECT value FROM setup_values WHERE name = 'config'")->fetchColumn();
        return ['state' => is_string($plain) ? 'warning' : 'warning', 'detail' => is_string($plain)
            ? 'The full setup configuration is permission-protected but not encrypted at rest. Configure a local key and save setup to migrate it.'
            : 'No full setup configuration record was found.'];
    } catch (Throwable) {
        return ['state' => 'error',
            'detail' => 'Zogograph could not verify the encrypted setup configuration. Check the private key path and restore only a verified backup.'];
    }
}

/** @return array{state:string,detail:string} Same status when only loaded settings are available. */
function setupStoreConfigurationProtectionForSettings(array $settings, string $webRoot): array
{
    try {
        $store = setupStoreOpen($settings, false);
        if ($store === null) return ['state' => 'warning', 'detail' => 'The setup database has not been created yet; no full configuration is stored.'];
        $sealed = $store->query("SELECT value FROM setup_values WHERE name = 'config_encrypted'")->fetchColumn();
        if (is_string($sealed) && $sealed !== '') {
            $encryption = (array)($settings['encryption'] ?? []);
            setupStoreOpenConfig($sealed, (string)($encryption['local_key_file'] ?? ''));
            return ['state' => 'ok', 'detail' => 'The full setup configuration is encrypted with the configured external local key.'];
        }
        return ['state' => 'warning', 'detail' => 'The full setup configuration is permission-protected but not encrypted at rest.'];
    } catch (Throwable) {
        return ['state' => 'error', 'detail' => 'Zogograph could not verify the encrypted setup configuration. Check the private key path and restore only a verified backup.'];
    }
}

/** Loads setup.db when present, otherwise a legacy all-in-one config.php. */
function loadConfiguredSettings(string $configFile): array
{
    $bootstrap = require $configFile;
    if (!is_array($bootstrap)) throw new RuntimeException('config.php must return an array');
    try {
        $store = setupStoreOpen($bootstrap, false);
        if ($store === null) return $bootstrap;
        $sealed = $store->query("SELECT value FROM setup_values WHERE name = 'config_encrypted'")->fetchColumn();
        if (is_string($sealed) && $sealed !== '') {
            $saved = setupStoreOpenConfig($sealed, (string)($bootstrap['setup_key_file'] ?? ''));
        } else {
            $value = $store->query("SELECT value FROM setup_values WHERE name = 'config'")->fetchColumn();
            $saved = is_string($value) ? json_decode($value, true) : null;
        }
        if (!is_array($saved)) return $bootstrap;
        // Paths required before setup.db opens always come from the bootstrap.
        foreach (['database', 'scan_root', 'timezone'] as $key) {
            if (array_key_exists($key, $bootstrap)) $saved[$key] = $bootstrap[$key];
        }
        return $saved;
    } catch (Throwable $error) {
        // A damaged optional setup store must not turn a recoverable legacy
        // config into a blank dashboard. An encrypted setup record is
        // different: falling back without its password/access policy would be
        // unsafe, so setup.php must repair it with the configured key.
        if (trim((string)($bootstrap['setup_key_file'] ?? '')) !== '') throw $error;
        return $bootstrap;
    }
}

/** Writes full settings to setup.db and reduces config.php to boot essentials. */
function saveConfiguredSettings(array $config, string $configFile, string $libDir): void
{
    $bootstrap = [];
    foreach (['scan_root', 'timezone', 'database'] as $key) {
        if (array_key_exists($key, $config)) $bootstrap[$key] = $config[$key];
    }
    $encryption = (array)($config['encryption'] ?? []);
    $keyPath = trim((string)($encryption['local_key_file'] ?? ''));
    if ($keyPath !== '') {
        /* Keep only a locator in config.php. The key remains outside the web
         * root and is required to open the full setup policy. */
        setupStoreConfigKey($keyPath, dirname($libDir));
        $bootstrap['setup_key_file'] = $keyPath;
    }
    $current = is_file($configFile) ? require $configFile : null;
    $replaceBootstrap = !is_array($current) || $current !== $bootstrap;
    if ($replaceBootstrap && !is_writable(is_file($configFile) ? $configFile : dirname($configFile))) {
        throw new RuntimeException('could not update the bootstrap config');
    }
    $store = setupStoreOpen($bootstrap, true);
    if ($store === null) throw new RuntimeException('could not open setup.db');
    $encoded = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $store->beginTransaction();
    try {
        if ($keyPath !== '') {
            $store->prepare('INSERT INTO setup_values (name, value, updated_at) VALUES (?, ?, ?)
                ON CONFLICT(name) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at')
                ->execute(['config_encrypted', setupStoreSealConfig($encoded, $keyPath, dirname($libDir)), time()]);
            $store->prepare('DELETE FROM setup_values WHERE name = ?')->execute(['config']);
        } else {
            $store->prepare('INSERT INTO setup_values (name, value, updated_at) VALUES (?, ?, ?)
                ON CONFLICT(name) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at')
                ->execute(['config', $encoded, time()]);
            $store->prepare('DELETE FROM setup_values WHERE name = ?')->execute(['config_encrypted']);
        }
        $store->commit();
    } catch (Throwable $error) { $store->rollBack(); throw $error; }

    if (!$replaceBootstrap) return;

    $text = "<?php\ndeclare(strict_types=1);\n\n/* Boot settings. The rest live in ../data/setup.db. */\n\nreturn "
        . setupStoreRender($bootstrap, $libDir) . ";\n";
    $temporary = $configFile . '.new';
    $previousUmask = umask(0077);
    try {
        if (@file_put_contents($temporary, $text, LOCK_EX) === false || !@rename($temporary, $configFile)) {
            @unlink($temporary);
            throw new RuntimeException('could not replace the bootstrap config');
        }
        @chmod($configFile, 0600);
    } finally { umask($previousUmask); }
}

/** Derive a distinct key so configuration encryption cannot be confused with a sealed artifact. */
function setupStoreConfigKey(string $path, string $webRoot): string
{
    if (!encryptionAvailable()) throw new RuntimeException('OpenSSL AES-256-GCM support is required to encrypt setup settings');
    $key = hash_hkdf('sha256', encryptionReadLocalKey($path, $webRoot), 32, 'HostScope setup configuration v1');
    if (!is_string($key) || strlen($key) !== 32) throw new RuntimeException('could not derive the setup configuration key');
    return $key;
}

/** @return string compact JSON envelope, suitable only for the private setup store */
function setupStoreSealConfig(string $plain, string $path, string $webRoot): string
{
    $nonce = random_bytes(12);
    $ciphertext = openssl_encrypt($plain, 'aes-256-gcm', setupStoreConfigKey($path, $webRoot), OPENSSL_RAW_DATA,
        $nonce, $tag, 'HostScope setup configuration v1', 16);
    if (!is_string($ciphertext) || !is_string($tag)) throw new RuntimeException('could not encrypt setup settings');
    return json_encode(['v' => 1, 'n' => base64_encode($nonce), 'c' => base64_encode($ciphertext), 't' => base64_encode($tag)], JSON_THROW_ON_ERROR);
}

/** @return array<string,mixed> */
function setupStoreOpenConfig(string $envelope, string $path): array
{
    $data = json_decode($envelope, true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($data) || ($data['v'] ?? null) !== 1 || trim($path) === '') throw new RuntimeException('encrypted setup settings are unavailable');
    $nonce = base64_decode((string)($data['n'] ?? ''), true);
    $ciphertext = base64_decode((string)($data['c'] ?? ''), true);
    $tag = base64_decode((string)($data['t'] ?? ''), true);
    if (!is_string($nonce) || strlen($nonce) !== 12 || !is_string($ciphertext) || $ciphertext === '' || !is_string($tag) || strlen($tag) !== 16) {
        throw new RuntimeException('encrypted setup settings are invalid');
    }
    $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', setupStoreConfigKey($path, dirname(__DIR__)), OPENSSL_RAW_DATA,
        $nonce, $tag, 'HostScope setup configuration v1');
    $decoded = is_string($plain) ? json_decode($plain, true, 128, JSON_THROW_ON_ERROR) : null;
    if (!is_array($decoded)) throw new RuntimeException('encrypted setup settings could not be opened');
    return $decoded;
}

/** Whether settings can be saved without changing the PHP bootstrap. */
function configuredSettingsWritable(string $configFile): bool
{
    if (!is_file($configFile)) return is_writable(dirname($configFile));
    try {
        $bootstrap = require $configFile;
        if (!is_array($bootstrap)) return false;
        $store = setupStorePath($bootstrap);
        return is_file($store) ? is_writable(dirname($store)) : is_writable($configFile);
    } catch (Throwable) {
        return false;
    }
}

function setupStoreRender(array $bootstrap, string $libDir): string
{
    $parts = [];
    foreach ($bootstrap as $key => $value) {
        $shown = is_string($value) && str_starts_with($value, rtrim($libDir, '/') . '/')
            ? "__DIR__ . " . var_export(substr($value, strlen(rtrim($libDir, '/'))), true)
            : var_export($value, true);
        $parts[] = '    ' . var_export($key, true) . ' => ' . $shown;
    }
    return "[\n" . implode(",\n", $parts) . ",\n]";
}
