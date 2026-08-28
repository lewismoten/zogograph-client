<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/setupstore.php';
require_once __DIR__ . '/../lib/setup-preflight.php';

function setupExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = sys_get_temp_dir() . '/hostscope-preflight-' . bin2hex(random_bytes(6));
mkdir($root . '/data', 0700, true);
mkdir($root . '/lib', 0700, true);
$config = $root . '/lib/config.php';
file_put_contents($config, "<?php return ['database' => " . var_export($root . '/data/stats.db', true) . "];\n");

try {
    if (function_exists('symlink') && @symlink($root . '/data', $root . '/linked-data')) {
        try { setupStoreOpen(['database' => $root . '/linked-data/stats.db'], true); throw new RuntimeException('symlinked setup directory was accepted'); }
        catch (RuntimeException $error) { setupExpect($error->getMessage() !== 'symlinked setup directory was accepted', $error->getMessage()); }
        @unlink($root . '/linked-data');
    }
    file_put_contents($root . '/setup-target.db', 'not a database');
    if (function_exists('symlink') && @symlink($root . '/setup-target.db', $root . '/data/setup.db')) {
        try { setupStoreOpen(['database' => $root . '/data/stats.db'], true); throw new RuntimeException('symlinked setup database was accepted'); }
        catch (RuntimeException $error) { setupExpect($error->getMessage() !== 'symlinked setup database was accepted', $error->getMessage()); }
        @unlink($root . '/data/setup.db');
    }
    @unlink($root . '/setup-target.db');
    saveConfiguredSettings(['database' => $root . '/data/stats.db', 'scan_root' => $root, 'timezone' => 'UTC'], $config, $root . '/lib');
    setupExpect((fileperms($config) & 0077) === 0, 'bootstrap configuration has broad permissions');
    $setup = setupStoreOpen(['database' => $root . '/data/stats.db'], true);
    foreach ([$root . '/data/setup.db', $root . '/data/setup.db-wal', $root . '/data/setup.db-shm'] as $privateFile) if (is_file($privateFile)) {
        setupExpect((fileperms($privateFile) & 0077) === 0, 'setup SQLite file has broad permissions');
    }
    unset($setup);
    $checks = setupPreflight($root, $config, [
        'php_version' => '8.0.30', 'sqlite' => false, 'commands' => [], 'https' => false, 'cli' => false, 'encryption' => false, 'sqlcipher' => false,
    ]);
    $byLabel = array_column($checks, null, 'label');
    setupExpect($byLabel['PHP']['state'] === 'error', 'unsupported PHP was not a blocking installer error');
    setupExpect($byLabel['SQLite']['state'] === 'error', 'missing SQLite was not a blocking installer error');
    setupExpect($byLabel['Command capability']['state'] === 'warning'
        && str_contains($byLabel['Command capability']['detail'], 'PHP-only collection still works'),
        'disabled command execution did not preserve the PHP-only install path');
    setupExpect($byLabel['Cron']['state'] === 'warning'
        && str_contains($byLabel['Cron']['detail'], 'host panel'),
        'shared-host cron guidance was not shown without command execution');
    setupExpect($byLabel['HTTPS']['state'] === 'warning', 'plain HTTP was not called out before credential entry');
    setupExpect($byLabel['Encrypted copies']['state'] === 'warning', 'missing encryption capability was not reported');
    setupExpect($byLabel['Live SQLCipher option']['state'] === 'warning' && str_contains($byLabel['Live SQLCipher option']['detail'], 'recommended SQLite sealed-copy'),
        'missing SQLCipher driver was not explained as optional');
    setupExpect($byLabel['Setup configuration at rest']['state'] === 'warning'
        && str_contains($byLabel['Setup configuration at rest']['detail'], 'not encrypted at rest'),
        'plaintext setup configuration was not explained in preflight');

    $live = setupPreflight($root, $config, [
        'php_version' => '8.1.0', 'sqlite' => true, 'commands' => ['proc_open'], 'https' => true, 'cli' => true, 'encryption' => true, 'sqlcipher' => true,
    ]);
    $liveByLabel = array_column($live, null, 'label');
    setupExpect($liveByLabel['PHP']['state'] === 'ok' && $liveByLabel['SQLite']['state'] === 'ok',
        'minimum supported PHP/SQLite installation was rejected');
    setupExpect($liveByLabel['Command capability']['state'] === 'ok' && $liveByLabel['Cron']['state'] === 'ok',
        'available CLI capabilities were not reported as usable');
    setupExpect($liveByLabel['Encrypted copies']['state'] === 'ok', 'available encryption capability was not reported');
    setupExpect($liveByLabel['Live SQLCipher option']['state'] === 'ok', 'available SQLCipher driver was not reported');
} finally {
    @unlink($config);
    foreach (['setup.db', 'setup.db-wal', 'setup.db-shm'] as $file) @unlink($root . '/data/' . $file);
    @rmdir($root . '/lib');
    @rmdir($root . '/data');
    @rmdir($root);
}

echo "setup preflight checks passed\n";
