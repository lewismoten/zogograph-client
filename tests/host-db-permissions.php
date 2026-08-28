<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/hostdb.php';

function hostPermissionExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }

$root = sys_get_temp_dir() . '/hostscope-host-db-' . bin2hex(random_bytes(4));
mkdir($root, 0700, true);
try {
    $config = ['database' => $root . '/account.db', 'host' => ['database' => $root . '/host.db']];
    hostDb($config, true);
    foreach ([$root . '/host.db', $root . '/host.db-wal', $root . '/host.db-shm'] as $privateFile) if (is_file($privateFile)) {
        hostPermissionExpect((fileperms($privateFile) & 0007) === 0, 'host SQLite file is world-readable or writable');
        hostPermissionExpect(in_array(fileperms($privateFile) & 0070, [0000, 0040], true), 'host SQLite file grants group write or execute access');
    }
    echo "host database permission checks passed\n";
} finally {
    foreach (['host.db', 'host.db-wal', 'host.db-shm'] as $file) @unlink($root . '/' . $file);
    @rmdir($root);
}
