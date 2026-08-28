<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/account-merge.php';

function accountMergeExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$mergeScript = file_get_contents(__DIR__ . '/../scripts/merge-account.php');
accountMergeExpect($mergeScript !== false && str_contains($mergeScript, '--review') && str_contains($mergeScript, '--allow-overlap')
    && str_contains($mergeScript, '--writers-stopped') && str_contains($mergeScript, 'accountMergeApply'),
    'collector merge command lacks review or explicit apply safeguards');
$setup = file_get_contents(__DIR__ . '/../setup.php');
accountMergeExpect($setup !== false && str_contains($setup, 'review-account-merge') && str_contains($setup, 'apply-account-merge')
    && str_contains($setup, 'writers_stopped') && str_contains($setup, 'allow_overlap') && str_contains($setup, 'move_uploaded_file'),
    'collector merge setup workflow lacks reviewed upload and explicit apply safeguards');
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
    $source->exec('INSERT INTO scans (started_at) VALUES (150)');
    $source->exec("INSERT INTO paths (path, kind, first_seen, last_seen) VALUES ('/source/file', 'file', 150, 150)");
    $source->exec('INSERT INTO scan_totals (scan_id, category, bytes, files) VALUES (1, "other", 12, 1)');
    $source->exec('INSERT INTO path_sizes (scan_id, path_id, bytes, files) VALUES (1, 1, 12, 1)');
    $source = null;
    /* This is a real historical collector shape, not a current database with
     * only its version ledger edited. Its private preparation must exercise
     * every migration required by a first-generation production account DB. */
    $historicalPath = $dir . '/historical-v1.db';
    $historical = new PDO('sqlite:' . $historicalPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    foreach (migrations()[1] as $statement) $historical->exec($statement);
    $historical->exec('CREATE TABLE schema_version (version INTEGER NOT NULL)');
    $historical->exec('INSERT INTO schema_version VALUES (1)');
    $historical->exec('INSERT INTO vitals (taken_at, source) VALUES (50, "historical")');
    $historical->exec('INSERT INTO scans (started_at) VALUES (50)');
    $historical->exec("INSERT INTO paths (path, kind, first_seen, last_seen) VALUES ('/historical/file', 'file', 50, 50)");
    $historical->exec('INSERT INTO scan_totals (scan_id, category, bytes, files) VALUES (1, "other", 4, 1)');
    $historical->exec('INSERT INTO path_sizes (scan_id, path_id, bytes, files) VALUES (1, 1, 4, 1)');
    $historical = null;
    $older = accountMergeInspect($historicalPath);
    accountMergeExpect($older['eligible'] && $older['source_version'] === 1 && $older['source_version'] < $older['current_version'],
        'a genuine historical collector source was not eligible for a private upgrade before merge');
    $prepared = accountMergePreparedSource($historicalPath, $dir . '/prepared-source');
    $preparedPdo = new PDO('sqlite:' . $prepared, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    accountMergeExpect((int)$preparedPdo->query('SELECT MAX(version) FROM schema_version')->fetchColumn() === $older['current_version']
        && accountMergeInspect($historicalPath)['source_version'] === 1,
        'merge preparation did not upgrade only the private source clone');
    $preparedPdo = null; @unlink($prepared); @rmdir($dir . '/prepared-source');

    /* A sanitized operator fixture can prove the read-only merge-review path
     * against a real account store. It is optional in CI and never attached to
     * or changed by this test. */
    $provided = realpath(__DIR__ . '/../scratch/old-stats.db');
    if ($provided !== false && is_file($provided) && !is_link($provided)) {
        $before = hash_file('sha256', $provided);
        $providedInspection = accountMergeInspect($provided);
        accountMergeExpect($providedInspection['eligible']
            && $providedInspection['source_version'] !== null
            && $providedInspection['source_version'] <= $providedInspection['current_version'],
            'supplied historical account fixture is not eligible for a read-only merge review');
        $providedPrepared = accountMergePreparedSource($provided, $dir . '/provided-source');
        $providedCopy = new PDO('sqlite:' . $providedPrepared, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        accountMergeExpect((int)$providedCopy->query('SELECT MAX(version) FROM schema_version')->fetchColumn() === $providedInspection['current_version']
            && hash_file('sha256', $provided) === $before,
            'historical account merge preparation changed its source or did not produce a current private clone');
        $providedCopy = null; @unlink($providedPrepared); @rmdir($dir . '/provided-source');
    }
    $target = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); migrate($target, true);
    $target->exec('INSERT INTO vitals (taken_at, source) VALUES (200, "test"), (300, "test")');
    $target->exec('INSERT INTO scans (started_at) VALUES (400)');
    $plan = accountMergePlan($target, $sourcePath);
    accountMergeExpect($plan['eligible'] && $plan['collector']['vitals']['source']['rows'] === 2
        && $plan['collector']['vitals']['target']['rows'] === 2 && $plan['collector']['vitals']['overlap']
        && !$plan['collector']['scans']['overlap'], 'merge plan does not report collector ranges and overlap');
    $targetPath = $dir . '/target.db'; $fileTarget = new PDO('sqlite:' . $targetPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); migrate($fileTarget, true);
    $fileTarget->exec('INSERT INTO vitals (taken_at, source) VALUES (200, "target"), (300, "target")');
    $fileTarget->exec('INSERT INTO scans (started_at) VALUES (400)');
    $historicalResult = accountMergeApply($fileTarget, $historicalPath, true);
    accountMergeExpect($historicalResult['vitals'] === 1 && $historicalResult['scans'] === 1 && $historicalResult['paths'] === 1
        && $historicalResult['scan_totals'] === 1 && $historicalResult['path_sizes'] === 1,
        'a genuine historical collector source did not upgrade and merge its mapped rows');
    $result = accountMergeApply($fileTarget, $sourcePath, true);
    accountMergeExpect($result['vitals'] === 1 && $result['scans'] === 1 && $result['paths'] === 1
        && $result['scan_totals'] === 1 && $result['path_sizes'] === 1 && is_file($result['snapshot'])
        && (fileperms($result['snapshot']) & 0077) === 0, 'collector merge did not create a private rollback or import mapped rows');
    accountMergeExpect((int)$fileTarget->query('SELECT COUNT(*) FROM vitals')->fetchColumn() === 4
        && (int)$fileTarget->query('SELECT COUNT(*) FROM path_sizes')->fetchColumn() === 2,
        'collector merge duplicated overlap or lost mapped child rows');
    $fileTarget = null;
    $future = $dir . '/future.db'; $pdo = new PDO('sqlite:' . $future); $pdo->exec('CREATE TABLE schema_version (version INTEGER NOT NULL)'); $pdo->exec('INSERT INTO schema_version VALUES (999)'); $pdo = null;
    accountMergeExpect(accountMergeInspect($future)['reason'] === 'source was created by a newer Zogograph version', 'future source was not rejected');
    @unlink($future); @unlink($historicalPath); @unlink($sourcePath); @unlink($targetPath); foreach (glob($dir . '/merge-backups/*') ?: [] as $file) @unlink($file); @rmdir($dir . '/merge-backups'); foreach (glob($dir . '/merge-workspace/*') ?: [] as $file) @unlink($file); @rmdir($dir . '/merge-workspace'); @rmdir($dir);
    echo "account merge checks passed\n";
} catch (Throwable $error) {
    @unlink($historicalPath ?? ''); @unlink($sourcePath); @rmdir($dir); throw $error;
}
