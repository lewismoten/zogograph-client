<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/actions.php';

function actorExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE actions (at INTEGER, action TEXT, target TEXT, outcome TEXT, bytes_freed INTEGER,
    files_removed INTEGER, detail TEXT, remote_addr TEXT, actor TEXT)');
$_SESSION = ['username' => 'operator@example.test'];
$_SERVER['REMOTE_ADDR'] = '203.0.113.8';
logAction($pdo, 'test', null, 'ok');
$row = $pdo->query('SELECT actor, remote_addr FROM actions')->fetch();
actorExpect($row['actor'] === 'operator@example.test' && $row['remote_addr'] === '203.0.113.8',
    'audit record did not retain the authenticated actor and source address separately');

echo "audit actor checks passed\n";
