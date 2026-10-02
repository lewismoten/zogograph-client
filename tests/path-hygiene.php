<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/marketplace.php';
require_once __DIR__ . '/../lib/path-hygiene.php';

function pathHygieneExpect(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

/* Account-specific paths and unresolved display segments must never drift back
 * into shipped application source. Tests intentionally carry fixtures, so only
 * inspect deployable source rather than this test directory. */
$sourceRoot = realpath(__DIR__ . '/..');
$forbiddenPathText = ['regaldra', '/lib/../data/'];
$sourceIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
);
foreach ($sourceIterator as $sourceFile) {
    $relative = substr($sourceFile->getPathname(), strlen($sourceRoot) + 1);
    if (
        preg_match(
            '#^(?:\.git|tests|dist|data|scratch)/#',
            str_replace(DIRECTORY_SEPARATOR, '/', $relative),
        ) ||
        !$sourceFile->isFile()
    ) {
        continue;
    }
    $contents = file_get_contents($sourceFile->getPathname());
    foreach ($forbiddenPathText as $needle) {
        pathHygieneExpect(
            !is_string($contents) ||
                !str_contains(strtolower($contents), $needle),
            'shipped source contains forbidden account-specific or unresolved path text: ' .
                $relative,
        );
    }
}

$root =
    sys_get_temp_dir() . '/zogograph-path-hygiene-' . bin2hex(random_bytes(4));
mkdir($root, 0700);
file_put_contents(
    $root . '/zogograph-path-hygiene.json',
    json_encode([
        'id' => 'zogograph-path-hygiene',
        'version' => '1.0.0',
        'name' => 'Path-name hygiene',
        'publisher' => 'Zogograph',
        'summary' => 'Advisory path name review.',
        'license' => 'FSL-1.1-Apache-2.0',
        'capabilities' => ['path_hygiene'],
        'recommendations' => [],
    ]),
);
$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec(
    'CREATE TABLE marketplace_packages (package_id TEXT PRIMARY KEY, version TEXT, enabled_at INTEGER, changed_at INTEGER, changed_by TEXT)',
);
$pdo->exec('CREATE TABLE scans (id INTEGER PRIMARY KEY)');
$pdo->exec(
    'CREATE TABLE path_hygiene_scans (scan_id INTEGER PRIMARY KEY, checked_at INTEGER, findings_json TEXT, findings_total INTEGER, truncated INTEGER)',
);
$config = ['marketplace' => ['directory' => $root]];
pathHygieneExpect(
    !pathHygieneEnabled($config, $pdo),
    'disabled package enabled the scanner',
);
marketplaceSetEnabled(
    $config,
    $pdo,
    'zogograph-path-hygiene',
    true,
    'administrator',
);
pathHygieneExpect(
    pathHygieneEnabled($config, $pdo),
    'enabled package did not activate the fixed scanner',
);
$state = pathHygieneStart(true);
pathHygieneObserve('/tmp/normal name', 'normal name', $state);
pathHygieneObserve('/tmp/leading-space', ' leading', $state);
pathHygieneObserve('/tmp/trailing-space', 'trailing ', $state);
pathHygieneObserve('/tmp/dash', '-dash', $state);
pathHygieneObserve("/tmp/newline\n", "newline\n", $state);
pathHygieneObserve('/tmp/question', 'report?.log', $state);
pathHygieneExpect(
    $state['total'] === 5 && count($state['findings']) === 5,
    'path scanner classified names incorrectly',
);
pathHygieneExpect(
    pathHygieneReason('report?.log') ===
        'contains a shell or wildcard character',
    'question mark was not classified as unsafe',
);
$pdo->exec('INSERT INTO scans (id) VALUES (1)');
pathHygieneStore($pdo, 1, $state);
$stored = pathHygieneLast($pdo);
pathHygieneExpect(
    $stored !== null &&
        $stored['total'] === 5 &&
        count($stored['findings']) === 5,
    'path scan was not stored',
);
$renameRoot = $root . '/watched';
mkdir($renameRoot, 0700);
$oldName = 'rename this ';
$oldPath = $renameRoot . '/' . $oldName;
file_put_contents($oldPath, 'demo');
$renameState = pathHygieneStart(true);
pathHygieneObserve($oldPath, $oldName, $renameState);
$pdo->exec('INSERT INTO scans (id) VALUES (2)');
pathHygieneStore($pdo, 2, $renameState);
$renamed = pathHygieneRename(
    ['scan_root' => $renameRoot],
    $pdo,
    $oldPath,
    'rename-this',
);
pathHygieneExpect(
    $renamed['to'] === $renameRoot . '/rename-this' && is_file($renamed['to']),
    'path hygiene rename did not move the exact finding',
);
pathHygieneExpect(
    pathHygieneLast($pdo)['total'] === 0,
    'renamed path remained an active finding',
);
unlink($renamed['to']);
rmdir($renameRoot);
unlink($root . '/zogograph-path-hygiene.json');
rmdir($root);
echo "path hygiene checks passed\n";
