<?php
declare(strict_types=1);

/*
 * Root-side operating-system updates.
 *
 * The browser never gets a package-manager endpoint.  It chooses the stages
 * in config, and host-cron performs them on its next scheduled run.  Keeping
 * that boundary makes an accidental web-session compromise very different
 * from a root shell compromise.
 */

const UPDATE_STAGES = ['detect', 'notify', 'download', 'auto-update'];

/** @return list<string> */
function updateStages(array $config): array
{
    $picked = $config['updates']['modes'] ?? [];
    if (!is_array($picked)) return [];
    return array_values(array_intersect(UPDATE_STAGES, array_unique(array_map('strval', $picked))));
}

function updateInterval(array $config): int
{
    return max(3600, min(31 * 86400, (int)($config['updates']['interval_seconds'] ?? 86400)));
}

function updateManager(array $config): ?string
{
    $asked = (string)($config['external']['package_manager'] ?? 'auto');
    $known = ['apt', 'dnf', 'yum'];
    if ($asked !== 'auto' && in_array($asked, $known, true) && updateBinary($asked) !== null) {
        return $asked;
    }
    foreach ($known as $manager) {
        if (updateBinary($manager) !== null) return $manager;
    }
    return null;
}

function updateBinary(string $manager): ?string
{
    $binary = $manager === 'apt' ? 'apt-get' : $manager;
    foreach (['/usr/bin/', '/usr/sbin/', '/bin/', '/sbin/'] as $dir) {
        $path = $dir . $binary;
        if (is_executable($path)) return $path;
    }
    return null;
}

/** @return array{ok:bool,updates:?int,output:string,error:?string,seconds:float} */
function runUpdateStage(string $manager, string $stage): array
{
    $binary = updateBinary($manager);
    if ($binary === null) {
        return ['ok' => false, 'updates' => null, 'output' => '',
            'error' => "{$manager} is no longer available", 'seconds' => 0.0];
    }

    $args = match ($stage) {
        'detect' => $manager === 'apt' ? [$binary, '-s', 'upgrade'] : [$binary, 'check-update'],
        'download' => $manager === 'apt' ? [$binary, '-y', '-d', 'upgrade']
            : [$binary, '-y', '--downloadonly', 'upgrade'],
        'auto-update' => [$binary, '-y', 'upgrade'],
        default => throw new InvalidArgumentException('unknown update stage'),
    };
    $started = microtime(true);
    $pipes = [];
    $process = @proc_open($args, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        return ['ok' => false, 'updates' => null, 'output' => '',
            'error' => "could not start {$manager}", 'seconds' => round(microtime(true) - $started, 3)];
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    $output = trim(substr($stdout . ($stderr === '' ? '' : "\n" . $stderr), 0, 4000));
    // dnf/yum use 100 to mean updates are available, not that checking failed.
    $ok = $exit === 0 || ($stage === 'detect' && in_array($manager, ['dnf', 'yum'], true) && $exit === 100);
    $updates = null;
    if ($stage === 'detect') {
        if ($manager === 'apt' && preg_match('/(\d+) upgraded,/', $output, $match)) $updates = (int)$match[1];
        if (in_array($manager, ['dnf', 'yum'], true)) {
            $updates = $exit === 100 ? max(1, preg_match_all('/^[^\s]+\s+[^\s]+\s+[^\s]+$/m', $output)) : 0;
        }
    }
    return ['ok' => $ok, 'updates' => $updates, 'output' => $output,
        'error' => $ok ? null : ($output !== '' ? $output : "{$manager} exited {$exit}"),
        'seconds' => round(microtime(true) - $started, 3)];
}

/** Runs configured stages once per interval, returning rows fit for the ledger. */
function runSystemUpdates(array $config, PDO $pdo, bool $forced = false): array
{
    $stages = updateStages($config);
    if ($stages === []) return [];
    $last = (int)$pdo->query('SELECT COALESCE(MAX(started_at), 0) FROM host_update_runs')->fetchColumn();
    if (!$forced && $last > time() - updateInterval($config)) return [];
    $manager = updateManager($config);
    if ($manager === null) {
        $row = ['stage' => 'detect', 'manager' => 'none', 'updates' => null, 'ok' => false,
            'error' => 'No supported package manager found', 'output' => '', 'seconds' => 0.0];
        recordUpdateRun($pdo, $row);
        return [$row];
    }

    $rows = [];
    // Every useful stage needs a current package list.  Asking for Download or
    // Auto-Update therefore records Detect too, even when it was not checked.
    $wanted = $stages;
    if (!in_array('detect', $wanted, true)) array_unshift($wanted, 'detect');
    foreach ($wanted as $stage) {
        if ($stage === 'notify') continue; // notify is surfaced by alert evaluation below.
        $result = runUpdateStage($manager, $stage);
        $row = ['stage' => $stage, 'manager' => $manager] + $result;
        recordUpdateRun($pdo, $row);
        $rows[] = $row;
        if (!$result['ok']) break; // downloading after a failed refresh is misleading.
    }
    return $rows;
}

function recordUpdateRun(PDO $pdo, array $row): void
{
    $pdo->prepare('INSERT INTO host_update_runs
        (started_at, stage, manager, updates, seconds, ok, error, output)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([
        time(), $row['stage'], $row['manager'], $row['updates'], $row['seconds'],
        $row['ok'] ? 1 : 0, $row['error'], $row['output'],
    ]);
}
