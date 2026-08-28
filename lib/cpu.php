<?php
declare(strict_types=1);

/*
 * Turning processor counters into the four or five sentences worth reading.
 *
 * The CPU page used to be two dials and a number, which is enough to know that
 * something is wrong and useless for finding out what. "The machine is at 90%"
 * is the same reading for a box compiling something, a box waiting on a failing
 * disk, a box whose network card is drowning it in interrupts, and a box whose
 * hypervisor has taken the cores away - and those four need four completely
 * different responses.
 *
 * Everything here works on differences between two readings, because every
 * figure in /proc/stat is a counter since boot and none of them is a rate. That
 * has one consequence worth stating up front: a single reading can say nothing
 * at all, and the first minute after an install or a reboot is honestly blank
 * rather than confidently zero.
 */

require_once __DIR__ . '/diagnose.php';

/**
 * The states a core can be in, in the order they are stacked and charted.
 *
 * Ordered by what a reader should look at first rather than by /proc's own
 * order: the three that mean "something outside this machine's control" come
 * first, because they are the ones that change what you do next, and they are
 * the ones a plain busy percentage hides completely.
 */
function cpuStates(): array
{
    return [
        'steal' => [
            'label' => 'Stolen by the host',
            'short' => 'steal',
            'what' => 'Time this machine was ready to run and the hypervisor gave the '
                . 'physical core to somebody else. Not work being done here, and not '
                . 'something any change on this machine can reduce. On shared or '
                . 'virtualised hosting this is the figure that explains "it is slow and '
                . 'nothing is running".',
        ],
        'iowait' => [
            'label' => 'Waiting for storage',
            'short' => 'iowait',
            'what' => 'The core had nothing to run and something was blocked on a disk. '
                . 'This is idle time, not CPU being consumed - the processor is the '
                . 'victim here rather than the problem. High iowait means go and look at '
                . 'the disks; adding CPU would change nothing.',
        ],
        'softirq' => [
            'label' => 'Deferred interrupt work',
            'short' => 'softirq',
            'what' => 'The second half of interrupt handling, done outside the interrupt '
                . 'itself. On a server this is mostly the network stack. A whole core of '
                . 'it is a finding: it usually means packet volume rather than anything '
                . 'a process is doing.',
        ],
        'irq' => [
            'label' => 'Hardware interrupts',
            'short' => 'irq',
            'what' => 'Servicing devices. Ordinarily a fraction of a percent; anything '
                . 'visible on this chart points at a device or a driver.',
        ],
        'system' => [
            'label' => 'Kernel, for a process',
            'short' => 'system',
            'what' => 'Time in the kernel on some process\'s behalf: system calls, page '
                . 'faults, memory being mapped. More of this than userland time means '
                . 'the work is syscall-heavy - lots of small reads and writes, lots of '
                . 'process creation - rather than computation.',
        ],
        'user' => [
            'label' => 'Userland',
            'short' => 'user',
            'what' => 'Ordinary work at normal priority: the thing you actually asked the '
                . 'machine to do. A machine busy in userland and nothing else is a '
                . 'machine working, which may be entirely correct.',
        ],
        'nice' => [
            'label' => 'Userland, low priority',
            'short' => 'nice',
            'what' => 'The same, at lowered priority - backups, batch jobs, anything run '
                . 'under nice. It yields to everything else, so a machine that is busy '
                . 'only here is not a machine anybody is waiting on.',
        ],
        'idle' => [
            'label' => 'Idle',
            'short' => 'idle',
            'what' => 'Nothing to run, and nothing waiting on a disk either.',
        ],
    ];
}

/**
 * Counters into shares of time, one row per gap between readings.
 *
 * The percentages are of every core added together, so 100% means every core
 * fully occupied rather than one of them - the same convention the whole-machine
 * figure already used, and the opposite of the per-process convention below,
 * where 100% means one core's worth. The two are labelled accordingly on every
 * page that shows them, because mixing them up is the single easiest mistake to
 * make with these numbers.
 *
 * Total is the sum of the eight states charted, not the raw total column, so
 * the stack always adds to exactly 100. `guest` is deliberately left out of it:
 * the kernel counts guest time inside `user` as well, and including both makes
 * the shares add to more than everything.
 *
 * Gaps are skipped rather than smoothed. A counter that went backwards is a
 * reboot, and drawing a line across it would invent a minute of activity that
 * never happened.
 *
 * @param list<array> $rows host_samples rows, oldest first
 * @return list<array<string, float|int|null>>
 */
function cpuStateSeries(array $rows): array
{
    $states = array_keys(cpuStates());
    $out = [];
    $previous = null;

    foreach ($rows as $row) {
        if (($row['cpu_user_ticks'] ?? null) === null) {
            // Before the collector recorded the breakdown. Not a gap in the
            // machine's history, a gap in what was written down about it.
            $previous = null;
            continue;
        }

        if ($previous !== null) {
            $deltas = [];
            $total = 0;
            foreach ($states as $state) {
                $delta = (int)$row["cpu_{$state}_ticks"] - (int)$previous["cpu_{$state}_ticks"];
                $deltas[$state] = max(0, $delta);
                $total += $deltas[$state];
            }

            $seconds = (int)$row['taken_at'] - (int)$previous['taken_at'];
            if ($total > 0 && $seconds > 0) {
                $point = ['taken_at' => (int)$row['taken_at'], 'seconds' => $seconds];
                foreach ($deltas as $state => $delta) {
                    $point[$state] = round($delta / $total * 100, 2);
                }
                // Everything that is not idle, which is the figure the rest of
                // the dashboard calls "CPU" - restated here so a reader can see
                // it is the sum of the parts above and not a separate reading.
                $point['busy'] = round(100 - $point['idle'], 2);
                /*
                 * The collector tree was measured across this same sampling
                 * interval.  Keep both readings: total CPU is the alerting
                 * truth, while workload is the useful investigative view.
                 */
                $point['monitoring_cpu_percent'] = ($row['monitoring_cpu_percent'] ?? null) === null
                    ? null : (float)$row['monitoring_cpu_percent'];
                $point['workload_cpu_percent'] = $point['monitoring_cpu_percent'] === null
                    ? null : round(max(0.0, $point['busy'] - $point['monitoring_cpu_percent']), 2);

                $rate = function (string $key) use ($row, $previous, $seconds): ?float {
                    if (($row[$key] ?? null) === null || ($previous[$key] ?? null) === null) {
                        return null;
                    }
                    $delta = (int)$row[$key] - (int)$previous[$key];
                    return $delta < 0 ? null : round($delta / $seconds, 1);
                };

                $point['ctxt_rate'] = $rate('ctxt_switches');
                $point['intr_rate'] = $rate('interrupts');
                $point['fork_rate'] = $rate('forks');
                // Depths, not counters: taken as read rather than differenced.
                $point['procs_running'] = $row['procs_running'] === null
                    ? null : (int)$row['procs_running'];
                $point['procs_blocked'] = $row['procs_blocked'] === null
                    ? null : (int)$row['procs_blocked'];
                $point['monitoring_runnable'] = ($row['monitoring_runnable'] ?? null) === null
                    ? null : (int)$row['monitoring_runnable'];
                $point['workload_runnable'] = $point['monitoring_runnable'] === null
                    || $point['procs_running'] === null
                    ? null : max(0, $point['procs_running'] - $point['monitoring_runnable']);
                $point['cpu_pressure_some'] = $row['cpu_pressure_some'] === null
                    ? null : (float)$row['cpu_pressure_some'];
                $point['load1'] = $row['load1'] === null ? null : (float)$row['load1'];

                $out[] = $point;
            }
        }

        $previous = $row;
    }

    return $out;
}

/**
 * How evenly the work is spread across the cores.
 *
 * The one thing the aggregate figure cannot express, and the difference between
 * a problem you can throw hardware at and one you cannot. Eight cores at 12%
 * and one core at 100% beside seven idle ones are both "12% busy"; the second
 * is a single-threaded bottleneck, and doubling the cores makes it exactly as
 * slow as it was.
 *
 * @param list<array> $cores host_cpus rows
 * @return array{count: int, measured: int, mean: ?float, max: ?float, min: ?float,
 *               busiest: ?int, pinned: int, idle: int, spread: ?float}
 */
function cpuCoreSpread(array $cores): array
{
    $values = [];
    $busiest = null;
    $top = null;

    foreach ($cores as $core) {
        if ($core['busy_percent'] === null) {
            continue;
        }
        $value = (float)$core['busy_percent'];
        $values[] = $value;
        if ($top === null || $value > $top) {
            $top = $value;
            $busiest = (int)$core['cpu'];
        }
    }

    if (!$values) {
        return ['count' => count($cores), 'measured' => 0, 'mean' => null, 'max' => null,
                'min' => null, 'busiest' => null, 'pinned' => 0, 'idle' => 0, 'spread' => null];
    }

    $mean = array_sum($values) / count($values);
    $max = max($values);
    $min = min($values);

    return [
        'count' => count($cores),
        'measured' => count($values),
        'mean' => round($mean, 2),
        'max' => round($max, 2),
        'min' => round($min, 2),
        'busiest' => $busiest,
        // Thresholds chosen so that "pinned" means a core with essentially no
        // headroom left and "idle" means one doing nothing worth mentioning,
        // rather than either being a hair either side of a round number.
        'pinned' => count(array_filter($values, fn(float $v): bool => $v >= 90)),
        'idle' => count(array_filter($values, fn(float $v): bool => $v < 10)),
        'spread' => round($max - $min, 2),
    ];
}

/**
 * What is actually true about this processor right now.
 *
 * The same rules as the diagnosis page and the same shape of answer, kept here
 * rather than there because these read the per-core table and the state
 * breakdown, which that page does not fetch. Every one of them is a
 * measurement; none of them guesses at a cause it cannot see.
 *
 * Ordered by the response they call for, not by size. A machine losing 30% to
 * steal and a machine at 95% in userland are both "busy" and the first is not
 * something anybody here can fix, so it is said first.
 *
 * @param ?array $sample the newest host_samples row
 * @param ?array $now    the newest row of cpuStateSeries
 * @param array  $spread cpuCoreSpread of the newest per-core rows
 * @param list<array> $procs the kept processes, with cpu_percent
 * @return list<array>
 */
function cpuFindings(?array $sample, ?array $now, array $spread, array $procs): array
{
    $out = [];

    if ($sample === null) {
        return [finding(
            'unknown',
            'No host readings',
            'The host collector has not written a sample.',
            'Everything on these pages comes from /proc read as root. Without that '
                . 'collector there is nothing here to be right or wrong about - which is '
                . 'not the same as the processor being fine.',
            'Set the collector up',
            'schedule'
        )];
    }

    if ($now === null) {
        return [finding(
            'unknown',
            'Only one reading so far',
            'Processor use is a difference between two moments, and there is one.',
            'Every figure in /proc/stat is a counter since boot, so a single reading '
                . 'cannot be turned into a percentage of anything. The next collection '
                . 'gives these pages something to say.',
            '',
            ''
        )];
    }

    $cores = (int)($sample['cpu_cores'] ?? 0);

    /*
     * Steal first, because it is the one finding on this page that nothing done
     * on this machine can improve.
     */
    if ($now['steal'] >= 5) {
        $out[] = finding(
            $now['steal'] >= 20 ? 'critical' : 'warning',
            'The host is taking cores away',
            sprintf('%.1f%% of the last %s was steal time.', $now['steal'],
                cpuGap((int)$now['seconds'])),
            'Steal is time this machine was ready to run and did not get a physical '
                . 'core, because the hypervisor gave it to another tenant. It is not '
                . 'your workload and no change here reduces it - the machine is simply '
                . 'being given less processor than it is nominally sold. Sustained '
                . 'double-digit steal is worth raising with the host.',
            '',
            ''
        );
    }

    if ($now['iowait'] >= 15) {
        $out[] = finding(
            $now['iowait'] >= 35 ? 'warning' : 'watch',
            'Mostly waiting for storage',
            sprintf('%.1f%% iowait against %.1f%% of real work (user and kernel).',
                $now['iowait'], $now['user'] + $now['nice'] + $now['system']),
            'Iowait is idle time with something blocked on a disk, so the processor is '
                . 'the victim rather than the cause. A faster CPU would change nothing '
                . 'here; the disks are where to look.',
            'See how hard the disks are working',
            'storage-io'
        );
    }

    if ($now['softirq'] >= 10) {
        $out[] = finding(
            'watch',
            'A lot of deferred interrupt work',
            sprintf('%.1f%% softirq — about %s of a core.', $now['softirq'],
                $cores > 0 ? sprintf('%.1f', $now['softirq'] * $cores / 100) : 'an unknown share'),
            'On a server this is nearly always the network stack, and it rises with '
                . 'packet volume rather than with anything a process is doing. Worth '
                . 'checking what is on the wire before hunting through the process list.',
            'See who is connected',
            'net-peers'
        );
    }

    if ($now['busy'] >= 25 && $now['system'] > $now['user'] + $now['nice']) {
        $out[] = finding(
            'watch',
            'More time in the kernel than in userland',
            sprintf('%.1f%% system against %.1f%% user.', $now['system'],
                $now['user'] + $now['nice']),
            'The work is syscall-heavy rather than computational: many small reads and '
                . 'writes, many short-lived processes, or a lot of memory being mapped '
                . 'and unmapped. Optimising the program\'s arithmetic will not touch '
                . 'this; reducing the number of things it asks the kernel for will.',
            '',
            ''
        );
    }

    /*
     * The distribution, which the aggregate has already thrown away.
     */
    if ($spread['measured'] > 1 && $spread['pinned'] > 0 && $spread['mean'] !== null
        && $spread['mean'] < 50 && $spread['idle'] >= $spread['measured'] / 2) {
        $out[] = finding(
            'warning',
            'One core is pinned while the rest are idle',
            sprintf('Core %d at %.0f%%, %s below 10%%, %.0f%% on average.',
                $spread['busiest'], $spread['max'],
                cpuMany($spread['idle'], 'core'), $spread['mean']),
            'That shape is a single-threaded bottleneck: one piece of work that cannot '
                . 'be split, holding everything up while the machine looks mostly free. '
                . 'More cores will not help it, and the whole-machine percentage will '
                . 'never look alarming no matter how stuck it gets.',
            'Find the process',
            'cpu-procs'
        );
    } elseif ($spread['measured'] > 1 && $spread['pinned'] === $spread['measured']) {
        $out[] = finding(
            'warning',
            'Every core is pinned',
            sprintf('All %s at or above 90%%.', cpuMany($spread['measured'], 'core')),
            'The machine has no processor headroom at all. Unlike one busy core this '
                . 'does respond to more cores - but first check that the work is work '
                . 'you wanted done.',
            'See what is burning it',
            'cpu-procs'
        );
    }

    /*
     * Pressure, which catches the case utilisation is blind to: a machine with
     * apparent headroom whose tasks are still queueing for a core.
     */
    if (($now['cpu_pressure_some'] ?? null) !== null && $now['cpu_pressure_some'] >= 20) {
        $out[] = finding(
            $now['cpu_pressure_some'] >= 50 ? 'warning' : 'watch',
            'Tasks are queueing for a core',
            sprintf('%.0f%% of the last ten seconds had something runnable and waiting.',
                $now['cpu_pressure_some']),
            'This is the measurement utilisation cannot make. A machine at 100% with no '
                . 'pressure is getting exactly as much done as it can, which is what you '
                . 'want; pressure without high utilisation means something is holding '
                . 'work back - a throttle, a cgroup limit, or a hypervisor.',
            'See what is limiting it',
            'cpu-limits'
        );
    }

    if (($now['procs_blocked'] ?? 0) >= 4) {
        $out[] = finding(
            'watch',
            'Processes stuck in uninterruptible sleep',
            sprintf('%s blocked in the kernel at the last reading.',
                cpuMany((int)$now['procs_blocked'], 'process', 'processes')),
            'These are waiting on I/O and cannot be killed while they wait. They count '
                . 'towards the load average without using any processor, which is how a '
                . 'load of 40 happens on a machine that is 5% busy - the number is a '
                . 'queue length, not a CPU figure.',
            'See how hard the disks are working',
            'storage-io'
        );
    }

    if ($cores > 0 && ($now['procs_running'] ?? null) !== null
        && $now['procs_running'] > $cores * 2) {
        $out[] = finding(
            'watch',
            'More runnable tasks than cores',
            sprintf('%d wanting to run across %s.', $now['procs_running'],
                cpuMany($cores, 'core')),
            'Everything runnable gets a turn, so nothing is stalled - but each turn is '
                . 'shorter, and every one of them takes longer than it would on an idle '
                . 'machine. This is what "the server is slow but nothing is broken" '
                . 'looks like from underneath.',
            '',
            ''
        );
    }

    /*
     * A busy machine with no visible culprit is a finding in itself: it usually
     * means the work is short-lived and the once-a-minute reading keeps missing
     * it, which is worth saying rather than leaving somebody to conclude the
     * table is broken.
     */
    $accounted = 0.0;
    foreach ($procs as $proc) {
        $accounted += (float)($proc['cpu_percent'] ?? 0);
    }
    $accountedShare = $cores > 0 ? $accounted / $cores : null;
    if ($now['busy'] >= 60 && $accountedShare !== null && $accountedShare < $now['busy'] / 3) {
        $out[] = finding(
            'watch',
            'Busy, and no process to show for it',
            sprintf('%.0f%% busy, but the processes recorded account for about %.0f%%.',
                $now['busy'], $accountedShare),
            'Almost always short-lived work: processes that start, burn a core and exit '
                . 'between one reading and the next, so nothing that lived long enough '
                . 'to be written down was responsible. A high fork rate beside this '
                . 'confirms it.',
            'See how fast processes are being created',
            'cpu-queue'
        );
    }

    if (!$out) {
        $out[] = finding(
            'ok',
            'Nothing wrong with the processor',
            sprintf('%.0f%% busy over the last %s, %s.', $now['busy'],
                cpuGap((int)$now['seconds']),
                $spread['mean'] === null
                    ? 'per-core figures not available'
                    : sprintf('cores between %.0f%% and %.0f%%', $spread['min'], $spread['max'])),
            'Every check on this page ran and none of them found anything. That is a '
                . 'different statement from the processor not having been measured.',
            '',
            ''
        );
    }

    return $out;
}

/** A gap between two readings, said the way somebody would say it. */
function cpuGap(int $seconds): string
{
    if ($seconds < 45) {
        return $seconds === 1 ? 'second' : "$seconds seconds";
    }
    $minutes = (int)round($seconds / 60);
    return $minutes === 1 ? 'minute' : "$minutes minutes";
}

/** "3 cores", "1 core" - the plural has to agree or the sentence reads as a bug. */
function cpuMany(int $n, string $singular, ?string $plural = null): string
{
    return $n . ' ' . ($n === 1 ? $singular : ($plural ?? $singular . 's'));
}
