<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

function throttleExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE actions (at INTEGER, action TEXT, outcome TEXT, detail TEXT, remote_addr TEXT)');
$at = 1700000000;
$insert = $pdo->prepare('INSERT INTO actions VALUES (?, ?, ?, ?, ?)');
for ($i = 0; $i < SIGNIN_THROTTLE_LIMIT - 1; $i++) {
    $insert->execute([$at - 1, 'sign_in', 'refused', 'wrong password', '203.0.113.5']);
}
throttleExpect(!signInRateLimited($pdo, '203.0.113.5', $at), 'login limited before threshold');
$insert->execute([$at - 1, 'sign_in', 'refused', 'wrong password', '203.0.113.5']);
throttleExpect(signInRateLimited($pdo, '203.0.113.5', $at), 'login was not limited at threshold');
$insert->execute([$at - 1, 'sign_in_limited', 'refused', 'rate limited', '203.0.113.5']);
throttleExpect(signInRateLimited($pdo, '203.0.113.5', $at), 'rate-limit audit row changed threshold accounting');
throttleExpect(!signInRateLimited($pdo, '203.0.113.5', $at + SIGNIN_THROTTLE_WINDOW + 1),
    'login remained limited after throttle window');
echo "authentication throttle checks passed\n";
