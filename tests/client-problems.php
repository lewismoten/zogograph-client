<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/client-problems.php';

function clientProblemExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec(
    'CREATE TABLE client_problem_rate_limits (reporter_hash TEXT PRIMARY KEY, reported_at INTEGER NOT NULL)',
);
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
$config = ['mask_secret' => 'test secret', 'database' => '/private/test.db'];
$now = 1700000000;
clientProblemExpect(
    anonymousClientProblemAllowed($pdo, $config, $now),
    'first anonymous browser problem was not accepted',
);
clientProblemExpect(
    !anonymousClientProblemAllowed($pdo, $config, $now + 1),
    'anonymous browser problem was not limited for one hour',
);
clientProblemExpect(
    anonymousClientProblemAllowed(
        $pdo,
        $config,
        $now + ANONYMOUS_CLIENT_PROBLEM_WINDOW + 1,
    ),
    'anonymous browser problem remained limited after one hour',
);
$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
clientProblemExpect(
    anonymousClientProblemAllowed($pdo, $config, $now + 2),
    'a different anonymous browser was incorrectly limited',
);
clientProblemExpect(
    clientProblemReporterHash($config) !== '203.0.113.10',
    'anonymous browser report stored the raw source address',
);

echo "client problem reporting checks passed\n";
