<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/alerts.php';

function muteExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$now = time();
$pdo->exec("CREATE TABLE alerts (id INTEGER PRIMARY KEY, notification_disposition TEXT NOT NULL DEFAULT 'immediate', notification_reason TEXT, notification_muted_until INTEGER)");
$pdo->exec('CREATE TABLE alert_mutes (id INTEGER PRIMARY KEY, rule TEXT, created_at INTEGER, expires_at INTEGER, reason TEXT, created_by TEXT)');
$pdo->exec("INSERT INTO alerts (id) VALUES (1), (2)");
$pdo->prepare("INSERT INTO alert_mutes VALUES (1, 'disk_free', ?, ?, 'planned maintenance', 'administrator')")
    ->execute([$now, $now + 3600]);

muteExpect(alertActiveMute($pdo, 'disk_free', 'warning', $now) !== null,
    'active warning mute was not found');
muteExpect(alertActiveMute($pdo, 'disk_free', 'critical', $now) === null,
    'critical alert was eligible for an operator mute');
$raised = applyAlertMutes($pdo, [
    ['id' => 1, 'rule' => 'disk_free', 'severity' => 'warning'],
    ['id' => 2, 'rule' => 'disk_free', 'severity' => 'critical'],
]);
muteExpect(($raised[0]['notification_disposition'] ?? '') === 'suppressed'
    && !isset($raised[1]['notification_disposition']), 'mute disposition did not preserve the critical bypass');
$row = $pdo->query('SELECT notification_disposition, notification_reason FROM alerts WHERE id = 1')->fetch();
muteExpect($row['notification_disposition'] === 'suppressed'
    && str_contains((string)$row['notification_reason'], 'planned maintenance'), 'mute evidence was not stored on alert');
muteExpect(alertUnmute($pdo, 1) !== null && alertMutes($pdo) === [],
    'ending a mute did not expire it');

echo "alert mute checks passed\n";
