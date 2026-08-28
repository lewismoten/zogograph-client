<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/account-merge.php';

function accountMergeExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$dir = sys_get_temp_dir() . '/zogograph-account-merge-' . bin2hex(random_bytes(6));
accountMergeExpect(mkdir($dir, 0700), 'could not make account-merge test directory');
$sourcePath = $dir . '/source.db';
try {
    $source = new PDO('sqlite:' . $sourcePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    migrate($source, true); $source = null;
    $inspection = accountMergeInspect($sourcePath);
    accountMergeExpect($inspection['eligible'] && $inspection['source_version'] === $inspection['current_version']
        && $inspection['merge_tables'] === ['paths', 'vitals', 'scans', 'scan_totals', 'path_sizes'], 'current account source is not eligible for merge planning');
    accountMergeExpect(!accountMergeInspect($dir . '/missing.db')['eligible'], 'missing source was accepted');
    $source = new PDO('sqlite:' . $sourcePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $source->exec('INSERT INTO vitals (taken_at, source) VALUES (100, "test"), (200, "test")');
    $source->exec('INSERT INTO scans (started_at) VALUES (150)'); $source = null;
    $target = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); migrate($target, true);
    $target->exec('INSERT INTO vitals (taken_at, source) VALUES (200, "test"), (300, "test")');
    $target->exec('INSERT INTO scans (started_at) VALUES (400)');
    $plan = accountMergePlan($target, $sourcePath);
    accountMergeExpect($plan['eligible'] && $plan['collector']['vitals']['source']['rows'] === 2
        && $plan['collector']['vitals']['target']['rows'] === 2 && $plan['collector']['vitals']['overlap']
        && !$plan['collector']['scans']['overlap'], 'merge plan does not report collector ranges and overlap');
    $future = $dir . '/future.db'; $pdo = new PDO('sqlite:' . $future); $pdo->exec('CREATE TABLE schema_version (version INTEGER NOT NULL)'); $pdo->exec('INSERT INTO schema_version VALUES (999)'); $pdo = null;
    accountMergeExpect(accountMergeInspect($future)['reason'] === 'source was created by a newer Zogograph version', 'future source was not rejected');
    @unlink($future); @unlink($sourcePath); @rmdir($dir);
    echo "account merge checks passed\n";
} catch (Throwable $error) {
    @unlink($sourcePath); @rmdir($dir); throw $error;
}
