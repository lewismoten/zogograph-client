<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/alerts.php';

function retryExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE alert_emails (id INTEGER PRIMARY KEY, subject TEXT, body TEXT, recipients TEXT, headers TEXT,
    sent INTEGER, error TEXT, alert_ids TEXT, attempts INTEGER, next_attempt_at INTEGER)');
$pdo->exec('CREATE TABLE alerts (id INTEGER PRIMARY KEY, email_id INTEGER, emailed_at INTEGER, email_error TEXT)');
$pdo->exec("INSERT INTO alert_emails VALUES (1, 'Alert', 'Body', 'one@example.test', '', 0, 'refused', '1', 0, 1)");
$pdo->exec("INSERT INTO alerts VALUES (1, 1, NULL, 'refused')");

$failed = retryAlertEmails($pdo, 3, fn(): bool => false);
$row = $pdo->query('SELECT sent, attempts, next_attempt_at FROM alert_emails WHERE id = 1')->fetch();
retryExpect($failed === ['attempted' => 1, 'sent' => 0, 'failed' => 1] && (int)$row['attempts'] === 1
    && (int)$row['next_attempt_at'] > time(), 'refused mail was not deferred with backoff');
$pdo->exec('UPDATE alert_emails SET next_attempt_at = 1 WHERE id = 1');
$sent = retryAlertEmails($pdo, 3, fn(): bool => true);
$row = $pdo->query('SELECT sent, attempts, next_attempt_at, error FROM alert_emails WHERE id = 1')->fetch();
$alert = $pdo->query('SELECT emailed_at, email_error FROM alerts WHERE id = 1')->fetch();
retryExpect($sent === ['attempted' => 1, 'sent' => 1, 'failed' => 0] && (int)$row['sent'] === 1
    && (int)$row['attempts'] === 2 && $row['next_attempt_at'] === null && $row['error'] === null,
    'accepted retry did not close its delivery record');
retryExpect((int)$alert['emailed_at'] > 0 && $alert['email_error'] === null,
    'accepted retry did not repair alert delivery evidence');
retryExpect(alertEmailRetryAt(99, 10) === 3610, 'retry backoff exceeded one hour');

$pdo->exec("INSERT INTO alert_emails VALUES (2, 'Alert', 'Body', 'one@example.test; two@example.test', '', 0, 'refused', '2', 0, 1)");
$pdo->exec("INSERT INTO alerts VALUES (2, 2, NULL, 'refused')");
$calls = [];
$partial = retryAlertEmails($pdo, 3, function (string $recipient) use (&$calls): bool {
    $calls[] = $recipient;
    return $recipient === 'one@example.test';
});
$row = $pdo->query('SELECT sent, attempts, next_attempt_at, error FROM alert_emails WHERE id = 2')->fetch();
retryExpect($partial === ['attempted' => 1, 'sent' => 1, 'failed' => 0]
    && $calls === ['one@example.test', 'two@example.test'] && (int)$row['sent'] === 1
    && $row['next_attempt_at'] === null && str_contains((string)$row['error'], 'two@example.test'),
    'a partially accepted batch was queued again or lost its refusal evidence');
retryExpect(retryAlertEmails($pdo, 3, fn(): bool => false) === ['attempted' => 0, 'sent' => 0, 'failed' => 0],
    'a partially accepted batch was retried and could duplicate its accepted recipient');

echo "alert email retry checks passed\n";
