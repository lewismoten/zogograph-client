<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/host.php';
require_once __DIR__ . '/../lib/cpu.php';

function monitoringImpactExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = sys_get_temp_dir() . '/zogograph-monitoring-' . bin2hex(random_bytes(6));
mkdir($root . '/201', 0700, true);
mkdir($root . '/202', 0700, true);
file_put_contents($root . '/201/smaps_rollup', "Pss:                64 kB\n");
file_put_contents($root . '/202/smaps_rollup', "Pss:                32 kB\n");

try {
    $impact = hostMonitoringImpact([
        ['pid' => 201, 'ppid' => 1, 'command' => '/usr/bin/php /srv/zogograph/host-cron.php --collect',
            'state' => 'R', 'cpu_percent' => 1.25, 'rss_bytes' => 200000],
        ['pid' => 202, 'ppid' => 201, 'command' => 'sqlite3 /srv/private/host.db',
            'state' => 'D', 'cpu_percent' => 2.5, 'rss_bytes' => 100000],
        ['pid' => 203, 'ppid' => 1, 'command' => 'mariadbd',
            'state' => 'R', 'cpu_percent' => 45, 'rss_bytes' => 9000000],
    ], '/srv/zogograph', $root);

    monitoringImpactExpect($impact['processes'] === 2, 'collector child was not attributed exactly');
    monitoringImpactExpect($impact['runnable'] === 2, 'runnable collector-tree tasks were not counted');
    monitoringImpactExpect($impact['cpu_percent'] === 3.75, 'collector CPU did not exclude unrelated work');
    monitoringImpactExpect($impact['rss_bytes'] === 300000, 'collector RSS did not include the child exactly');
    monitoringImpactExpect($impact['pss_bytes'] === 98304, 'collector PSS did not include the selected tree');

    $first = ['taken_at' => 10, 'monitoring_cpu_percent' => 0, 'monitoring_runnable' => 0,
        'procs_running' => 1, 'procs_blocked' => null, 'cpu_pressure_some' => null, 'load1' => null];
    $second = ['taken_at' => 20, 'monitoring_cpu_percent' => 7.5, 'monitoring_runnable' => 1,
        'procs_running' => 3, 'procs_blocked' => null, 'cpu_pressure_some' => null, 'load1' => null];
    foreach (array_keys(cpuStates()) as $state) {
        $first['cpu_' . $state . '_ticks'] = 0;
        $second['cpu_' . $state . '_ticks'] = $state === 'user' ? 30 : ($state === 'idle' ? 70 : 0);
    }
    $series = cpuStateSeries([$first, $second]);
    monitoringImpactExpect(($series[0]['monitoring_cpu_percent'] ?? null) === 7.5,
        'monitoring CPU was not exposed as a series data point');
    monitoringImpactExpect(($series[0]['workload_cpu_percent'] ?? null) === 22.5,
        'workload CPU did not exclude the monitoring contribution');
    monitoringImpactExpect(($series[0]['workload_runnable'] ?? null) === 2,
        'workload runnable count did not exclude monitoring');
    echo "Monitoring impact tests passed\n";
} finally {
    @unlink($root . '/201/smaps_rollup');
    @unlink($root . '/202/smaps_rollup');
    @rmdir($root . '/201');
    @rmdir($root . '/202');
    @rmdir($root);
}
