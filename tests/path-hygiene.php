<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/marketplace.php';
require_once __DIR__ . '/../lib/path-hygiene.php';

function pathHygieneExpect(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }

$root = sys_get_temp_dir() . '/hostscope-path-hygiene-' . bin2hex(random_bytes(4));
mkdir($root, 0700);
file_put_contents($root . '/hostscope-path-hygiene.json', json_encode([
    'id' => 'hostscope-path-hygiene', 'version' => '1.0.0', 'name' => 'Path-name hygiene',
    'publisher' => 'HostScope', 'summary' => 'Advisory path name review.', 'license' => 'Proprietary',
    'capabilities' => ['path_hygiene'], 'recommendations' => [],
]));
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE marketplace_packages (package_id TEXT PRIMARY KEY, version TEXT, enabled_at INTEGER, changed_at INTEGER, changed_by TEXT)');
$pdo->exec('CREATE TABLE scans (id INTEGER PRIMARY KEY)');
$pdo->exec('CREATE TABLE path_hygiene_scans (scan_id INTEGER PRIMARY KEY, checked_at INTEGER, findings_json TEXT, findings_total INTEGER, truncated INTEGER)');
$config = ['marketplace' => ['directory' => $root]];
pathHygieneExpect(!pathHygieneEnabled($config, $pdo), 'disabled package enabled the scanner');
marketplaceSetEnabled($config, $pdo, 'hostscope-path-hygiene', true, 'administrator');
pathHygieneExpect(pathHygieneEnabled($config, $pdo), 'enabled package did not activate the fixed scanner');
$state = pathHygieneStart(true);
pathHygieneObserve('/tmp/normal name', 'normal name', $state);
pathHygieneObserve('/tmp/leading-space', ' leading', $state);
pathHygieneObserve('/tmp/trailing-space', 'trailing ', $state);
pathHygieneObserve('/tmp/dash', '-dash', $state);
pathHygieneObserve("/tmp/newline\n", "newline\n", $state);
pathHygieneObserve('/tmp/question', 'report?.log', $state);
pathHygieneExpect($state['total'] === 5 && count($state['findings']) === 5, 'path scanner classified names incorrectly');
pathHygieneExpect(pathHygieneReason('report?.log') === 'contains a shell or wildcard character', 'question mark was not classified as unsafe');
$pdo->exec('INSERT INTO scans (id) VALUES (1)');
pathHygieneStore($pdo, 1, $state);
$stored = pathHygieneLast($pdo);
pathHygieneExpect($stored !== null && $stored['total'] === 5 && count($stored['findings']) === 5, 'path scan was not stored');
$renameRoot = $root . '/watched'; mkdir($renameRoot, 0700);
$oldName = 'rename this '; $oldPath = $renameRoot . '/' . $oldName; file_put_contents($oldPath, 'demo');
$renameState = pathHygieneStart(true); pathHygieneObserve($oldPath, $oldName, $renameState);
$pdo->exec('INSERT INTO scans (id) VALUES (2)'); pathHygieneStore($pdo, 2, $renameState);
$renamed = pathHygieneRename(['scan_root' => $renameRoot], $pdo, $oldPath, 'rename-this');
pathHygieneExpect($renamed['to'] === $renameRoot . '/rename-this' && is_file($renamed['to']), 'path hygiene rename did not move the exact finding');
pathHygieneExpect(pathHygieneLast($pdo)['total'] === 0, 'renamed path remained an active finding');
unlink($renamed['to']); rmdir($renameRoot);
unlink($root . '/hostscope-path-hygiene.json'); rmdir($root);
echo "path hygiene checks passed\n";
