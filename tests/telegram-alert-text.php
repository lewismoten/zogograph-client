<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/alerts.php';

function telegramExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$config = ['alerts' => ['site_url' => 'https://status.example/']];
$link = dashboardLink($config);
$rows = [['severity' => 'info', 'subject' => 'Later'], ['severity' => 'critical', 'subject' => 'Now']];
$text = telegramAlertText($config, $rows);
telegramExpect(str_starts_with($text, 'Zogograph CRITICAL'), 'Telegram summary did not lead with worst severity');
telegramExpect(strpos($text, 'CRITICAL — Now') < strpos($text, 'INFO — Later'), 'Telegram alerts were not severity ordered');
telegramExpect(str_ends_with($text, $link), 'Telegram summary lost its dashboard link');

$many = array_fill(0, 30, ['severity' => 'warning', 'subject' => str_repeat('x', 30)]);
$short = telegramAlertText($config, $many);
telegramExpect(mb_strlen($short) <= TELEGRAM_ALERT_TEXT_LIMIT, 'Telegram summary exceeded its safe limit');
telegramExpect(str_contains($short, 'plus 10 more alerts.'), 'Telegram summary did not disclose omitted alerts');
telegramExpect(str_ends_with($short, $link), 'Telegram truncation lost dashboard link');

echo "Telegram alert text checks passed\n";
