<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/alerts.php';
function hoursExpect(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$window = ['enabled' => true, 'timezone' => 'America/New_York', 'days' => [1,2,3,4,5], 'start' => '09:00', 'end' => '17:00'];
hoursExpect(alertWorkingHoursDecision($window, 'warning', strtotime('2026-08-24 13:00 UTC'))['disposition'] === 'immediate', 'weekday working-hour alert was delayed');
hoursExpect(alertWorkingHoursDecision($window, 'warning', strtotime('2026-08-24 22:00 UTC'))['disposition'] === 'delayed', 'after-hours warning was sent');
hoursExpect(alertWorkingHoursDecision($window, 'critical', strtotime('2026-08-24 22:00 UTC'))['disposition'] === 'immediate', 'critical alert did not bypass working hours');
echo "alert working-hour checks passed\n";
