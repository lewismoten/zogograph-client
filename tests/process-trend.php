<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/processes.php';

function processTrendExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(
    'CREATE TABLE process_samples (taken_at INTEGER, reason TEXT, name TEXT, cpu_percent REAL, rss_bytes INTEGER)',
);
$insert = $pdo->prepare('INSERT INTO process_samples VALUES (?, ?, ?, ?, ?)');
for ($i = 0; $i < 9; $i++) {
    $insert->execute([900 + $i, 'cpu', 'recent-' . $i, 1, 1000]);
}
// It stopped long ago, but it was the actual problem and must not disappear.
$insert->execute([100, 'cpu', 'overnight-backup', 95, 9000]);
// Two reason rows at one moment are one process-group point, not two lines.
$insert->execute([908, 'memory', 'recent-8', 2, 2000]);

$trend = processTrend($pdo, 0, 1000, 'cpu');
processTrendExpect(
    count($trend['names']) === 10,
    'trend did not retain ten process groups',
);
processTrendExpect(
    in_array('overnight-backup', $trend['names'], true),
    'historic peak was lost to recency',
);
processTrendExpect(
    count(
        array_filter(
            $trend['rows'],
            fn(array $row): bool => (int) $row['taken_at'] === 908,
        ),
    ) === 1,
    'same-time rows were not grouped into one chart point',
);
processTrendExpect(
    ($trend['rows'][count($trend['rows']) - 1]['recent-8'] ?? null) === 2.0,
    'CPU chart did not retain the strongest same-time process value',
);
echo "Process trend tests passed\n";
