<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/encryption.php';
require_once __DIR__ . '/../lib/setupstore.php';

function setupConfigVerifyExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
if (!encryptionAvailable() || !function_exists('proc_open')) {
    echo "setup-config verification checks skipped: required capability is unavailable\n";
    exit(0);
}
$root =
    sys_get_temp_dir() . '/zogograph-setup-verify-' . bin2hex(random_bytes(4));
mkdir($root . '/data', 0700, true);
mkdir($root . '/web', 0700, true);
mkdir($root . '/keys', 0700, true);
try {
    $config = $root . '/web/config.php';
    $key = $root . '/keys/setup.key';
    encryptionCreateLocalKey($key, $root . '/web');
    saveConfiguredSettings(
        [
            'database' => $root . '/data/stats.db',
            'scan_root' => $root,
            'timezone' => 'UTC',
            'password_hash' => 'do-not-print',
            'encryption' => ['local_key_file' => $key],
        ],
        $config,
        $root . '/web/lib',
    );
    $command = [
        PHP_BINARY,
        __DIR__ . '/../scripts/verify-setup-config.php',
        '--config',
        $config,
        '--expect-encrypted',
    ];
    $pipes = [];
    $process = proc_open(
        $command,
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );
    setupConfigVerifyExpect(
        is_resource($process),
        'could not start setup configuration verifier',
    );
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    setupConfigVerifyExpect(
        proc_close($process) === 0 &&
            str_contains($output, 'verified') &&
            !str_contains($output, 'do-not-print'),
        'setup configuration verifier did not verify without disclosing data',
    );
    echo "setup-config verification checks passed\n";
} finally {
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($items as $item) {
        $item->isDir()
            ? @rmdir($item->getPathname())
            : @unlink($item->getPathname());
    }
    @rmdir($root);
}
