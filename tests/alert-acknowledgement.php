<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/alerts.php';

function acknowledgementExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE alerts (id INTEGER PRIMARY KEY, acknowledged_at INTEGER)');
$pdo->exec('INSERT INTO alerts (id, acknowledged_at) VALUES (1, NULL), (2, 1700000000)');

acknowledgementExpect(setAlertsAcknowledged($pdo, [1, 1, 0], true) === 1,
    'an unread alert was not marked read exactly once');
acknowledgementExpect((int)$pdo->query('SELECT acknowledged_at FROM alerts WHERE id = 1')->fetchColumn() > 0,
    'the read timestamp was not recorded');
acknowledgementExpect(setAlertsAcknowledged($pdo, [1], true) === 0,
    'an already-read alert was changed again');
acknowledgementExpect(setAlertsAcknowledged($pdo, [2, 999], false) === 1,
    'a read alert was not restored to unread');
acknowledgementExpect($pdo->query('SELECT acknowledged_at FROM alerts WHERE id = 2')->fetchColumn() === null,
    'the unread restoration did not clear the timestamp');

echo "alert acknowledgement checks passed\n";
