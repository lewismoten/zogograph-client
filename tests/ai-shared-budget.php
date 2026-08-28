<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ai.php';

function budgetExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$ai = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$ai->exec('CREATE TABLE ai_calls (at INTEGER NOT NULL, millicents INTEGER NOT NULL)');
$now = gmmktime(12, 0, 0, 8, 27, 2026);
$ai->prepare('INSERT INTO ai_calls VALUES (?, ?)')->execute([$now - 60, 1500]);
$ai->prepare('INSERT INTO ai_calls VALUES (?, ?)')->execute([$now - 40 * 86400, 9000]);
$settings = ['daily_cents' => 1.5, 'monthly_cents' => 2.0];
$budget = aiSharedBudget($ai, $settings, $now);
budgetExpect($budget['daily']['used_millicents'] === 1500 && !$budget['daily']['ok'],
    'daily AI budget did not include today\'s shared spend');
budgetExpect($budget['monthly']['used_millicents'] === 1500 && $budget['monthly']['remaining_millicents'] === 500,
    'monthly AI budget did not use the UTC calendar-month boundary');
budgetExpect(aiSharedBudget($ai, ['daily_cents' => 0, 'monthly_cents' => 0], $now)['daily']['ok'],
    'zero shared budget was not treated as uncapped');

echo "AI shared budget checks passed\n";
