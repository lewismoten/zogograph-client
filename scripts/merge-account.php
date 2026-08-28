#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Collector-only account merge. Review first; apply requires an explicit
 * acknowledgement of both known overlap and stopped writers. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("This merge command is CLI-only.\n"); }

if ($argc === 4 && $argv[1] === '--from' && $argv[3] === '--review') {
    [$sourceArgument, $apply] = [$argv[2], false];
} elseif ($argc === 6 && $argv[1] === '--from' && $argv[3] === '--apply' && $argv[4] === '--allow-overlap' && $argv[5] === '--writers-stopped') {
    [$sourceArgument, $apply] = [$argv[2], true];
} else {
    fwrite(STDERR, "Usage:\n"
        . "  php scripts/merge-account.php --from /secure/other-account.db --review\n"
        . "  php scripts/merge-account.php --from /secure/other-account.db --apply --allow-overlap --writers-stopped\n");
    exit(64);
}

$configFile = __DIR__ . '/../lib/config.php';
if (!is_file($configFile)) { fwrite(STDERR, "No lib/config.php; nothing can be merged safely.\n"); exit(1); }
require_once __DIR__ . '/../lib/setupstore.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/account-merge.php';
$config = loadConfiguredSettings($configFile);
$source = realpath($sourceArgument); $target = realpath((string)($config['database'] ?? ''));
if ($source === false || is_link($sourceArgument) || !is_file($source) || $target === false || $source === $target) {
    fwrite(STDERR, "Source must be a readable non-symlinked account export distinct from the live account database.\n"); exit(1);
}

try {
    $live = db($config); $plan = accountMergePlan($live, $source);
    if (!$plan['eligible']) throw new RuntimeException((string)$plan['reason']);
    foreach ($plan['collector'] as $name => $evidence) {
        $sourceRange = $evidence['source']; $targetRange = $evidence['target'];
        fwrite(STDOUT, sprintf("%s: source %d rows (%s–%s); live %d rows (%s–%s)%s\n", $name,
            $sourceRange['rows'], $sourceRange['first'] ?? 'none', $sourceRange['last'] ?? 'none',
            $targetRange['rows'], $targetRange['first'] ?? 'none', $targetRange['last'] ?? 'none',
            $evidence['overlap'] ? '; ranges overlap' : ''));
    }
    if (!$apply) { fwrite(STDOUT, "Review only: no database was changed. Re-run with --apply --allow-overlap --writers-stopped after stopping all web and cron writers.\n"); exit(0); }
    $result = accountMergeApply($live, $source, true);
    fwrite(STDOUT, sprintf("Merged collector data: %d paths, %d vitals, %d scans, %d totals, %d path sizes. Rollback snapshot: %s\n",
        $result['paths'], $result['vitals'], $result['scans'], $result['scan_totals'], $result['path_sizes'], $result['snapshot']));
} catch (Throwable $error) {
    fwrite(STDERR, 'Merge refused: ' . $error->getMessage() . "\n"); exit(1);
}
