<?php
declare(strict_types=1);

/*
 * What the account cannot see.
 *
 * Inside CageFS a cPanel account gets its own /proc containing only its own
 * processes, and its own view of the filesystem. That is the point of it, and
 * it is also why the account collector can watch memory disappear and have
 * nothing to blame: the process holding two gigabytes belongs to another user,
 * so from inside the cage it does not exist. Neither do the kernel's slab
 * caches, another account's swap, or a 763 MB Apache log under /etc.
 *
 * So this half runs as root, out of WHM's crontab, and writes to its own
 * database. Everything here reads /proc directly rather than shelling out to
 * ps, free or slabtop:
 *
 *   - those tools are not all installed everywhere, and their output format
 *     differs between procps and BSD userland in ways that are hard to notice
 *   - /proc/<pid>/smaps_rollup carries Pss, which no ps can report and which is
 *     the only per-process memory figure that adds up
 *   - a file can be read from a fixture directory, so the parsers in here are
 *     testable off a Linux box
 *
 * That last one matters more than it looks. Every function below takes the
 * root of the tree it reads as an argument.
 */

require_once __DIR__ . '/hostdb.php';
require_once __DIR__ . '/daemons.php';
// shellRead lives in collect.php and redactSecrets in processes.php. Required
// explicitly rather than left to whatever the caller happened to load first.
require_once __DIR__ . '/collect.php';
require_once __DIR__ . '/processes.php';
// halfRate and median, shared with the disk projection. Required, not assumed.
require_once __DIR__ . '/alerts.php';
// bootHistory, for the shutdown reasons only root can read.
require_once __DIR__ . '/uptime.php';
// Ports, interfaces, peers and service units, all read as part of a reading.
require_once __DIR__ . '/network.php';

/** Top slab caches, processes and files kept per reading. */
const HOST_KEEP_SLAB = 20;
const HOST_KEEP_PROCS = 25;

/*
 * How many processes are kept for their disk I/O regardless of their size.
 *
 * A separate ranking rather than a bigger HOST_KEEP_PROCS: raising the memory
 * cut-off to reach a 20 MiB process would store most of the process table to
 * catch one of them.
 */
const HOST_KEEP_IO_PROCS = 15;

/*
 * And how many are kept for their processor use, on the same argument.
 *
 * The top of the memory list and the top of the CPU list overlap far less than
 * people expect: the process holding two gigabytes is usually asleep, and the
 * one pinning a core is often a few megabytes of shell script in a loop. A CPU
 * page fed from the memory ranking would show the busiest of the fattest, which
 * is not a question anybody asks.
 */
const HOST_KEEP_CPU_PROCS = 15;

/**
 * Services tracked in the long series, and the floor for being one.
 *
 * More than the process list keeps, because these rows are tiny and a service
 * that matters at 3am may be nowhere near the top at noon. The floor stops the
 * table filling with one-off shell commands.
 */
const HOST_KEEP_SERVICES = 40;
const HOST_SERVICE_MIN_BYTES = 8388608;

/** Processes whose Pss is worth the extra read, biggest by RSS first. */
const HOST_PSS_LIMIT = 60;

/**
 * Everything in /proc/meminfo, in bytes.
 *
 * Kept whole rather than picked over, because which line matters depends on
 * what is wrong, and the file is thirty short lines. Values are kB in the file
 * and bytes everywhere in this program - mixing the two is the classic way to
 * be wrong by a factor of 1024 in a number nobody checks.
 *
 * @return array<string, int>
 */
function hostMeminfo(string $procRoot = '/proc'): array
{
    $raw = @file_get_contents($procRoot . '/meminfo');
    if ($raw === false) {
        return [];
    }

    $out = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        if (preg_match('/^(\w+):\s+(\d+)(\s+kB)?/', $line, $m)) {
            $out[$m[1]] = (int)$m[2] * (isset($m[3]) && $m[3] !== '' ? 1024 : 1);
        }
    }

    return $out;
}

/**
 * Where the memory actually went.
 *
 * The question this whole file exists to answer, and the reason it is not
 * simply "look at the biggest process".
 *
 * `free` calls memory used when it is neither free nor reclaimable cache:
 *
 *     used = total - free - buffers - cached - reclaimable slab
 *
 * The trap is that adding up RSS across processes gives a much bigger number
 * than that - on the machine this was written for, 4.96 GiB of RSS against
 * 3.4 GiB genuinely in use. Nothing is wrong: RSS counts every shared page once
 * per process, so a hundred Apache workers each "own" the same copy of libc.
 * Chasing the difference is chasing an artefact, and it is exactly what
 * somebody staring at `ps --sort=-rss` will do. Pss - each page divided by the
 * number of processes sharing it - is the figure that adds up, so it is
 * collected too and shown beside RSS.
 *
 * The `unaccounted` column - shown as *other kernel/system memory*, because the
 * internal name reads like an accusation - is what is left after everything
 * nameable is subtracted: process anonymous memory, page cache, tmpfs, slab,
 * page tables and kernel stacks.
 *
 * A few hundred megabytes there is normal and not a fault. Linux does not
 * reconcile exactly from a handful of summary lines, and never claimed to:
 * vmalloc regions, DMA and driver buffers, hugepages and per-cpu areas are real
 * allocations that those lines do not break out, and the counters are not even
 * all sampled at the same instant. The figure is worth having because it is
 * bounded - if it *grows* across days while nothing else does, the memory has
 * gone somewhere no per-process view will ever show, and knowing that is worth
 * more than another process list. A large but steady value is just this
 * machine's kernel.
 *
 * @return array<string, int|null>
 */
function hostMemoryAccounting(array $mem, int $rssSum, int $pssSum): array
{
    $get = fn(string $key): int => (int)($mem[$key] ?? 0);

    $total = $get('MemTotal');
    if ($total <= 0) {
        return ['used_bytes' => null, 'cache_bytes' => null, 'unaccounted' => null];
    }

    $cache = $get('Buffers') + $get('Cached') + $get('SReclaimable');
    $used = $total - $get('MemFree') - $cache;

    /*
     * Shmem is counted inside Cached, so it is already in $cache. What is not:
     * anonymous pages, pages held in RAM that also have a copy in swap,
     * unreclaimable slab, page tables, kernel stacks and per-cpu areas. Those
     * are what "used" is made of on a machine behaving normally.
     *
     * VmallocUsed is deliberately left out. On older kernels it reported
     * address space rather than memory and could exceed the size of RAM, and
     * subtracting it would turn the remainder into a large negative number that
     * looked like a bug in this function rather than in the source.
     */
    $named = $get('AnonPages')
        + $get('SwapCached')
        + $get('SUnreclaim')
        + $get('PageTables')
        + $get('KernelStack')
        + $get('Percpu');

    return [
        'used_bytes' => $used,
        'cache_bytes' => $cache,
        'unaccounted' => $used - $named,
        'rss_sum_bytes' => $rssSum,
        'pss_sum_bytes' => $pssSum,
    ];
}

/**
 * Every process on the machine, read out of /proc.
 *
 * Pss comes from smaps_rollup, which the kernel builds by walking the page
 * tables - cheap per process but not free, and there can be a thousand
 * processes. So it is read for the biggest few by RSS, which are the only ones
 * anybody will look at, and the rest carry a null Pss rather than a wrong one.
 *
 * @return array{procs: list<array>, rss_sum: int, pss_sum: int, swap_sum: int,
 *               threads: int, available: bool}
 */
function hostProcesses(
    string $procRoot = '/proc',
    int $pssLimit = HOST_PSS_LIMIT,
    string $passwd = '/etc/passwd'
): array {
    $entries = @scandir($procRoot);
    if ($entries === false) {
        // Every key the success path returns, so a caller reading one of them
        // does not have to know which branch it came from.
        return ['procs' => [], 'rss_sum' => 0, 'pss_sum' => 0, 'pss_procs' => 0,
                'swap_sum' => 0, 'threads' => 0, 'available' => false];
    }

    $users = hostUserNames($passwd);
    $boot = bootTime($procRoot);
    $ticks = hostClockTicks();

    $procs = [];
    $rssSum = $swapSum = $threads = 0;

    foreach ($entries as $entry) {
        if (!ctype_digit($entry)) {
            continue;
        }

        $status = @file_get_contents("$procRoot/$entry/status");
        if ($status === false) {
            // Exited between the scandir and now, which is normal and not
            // worth a word: a process list is a photograph of a moving thing.
            continue;
        }

        $fields = [];
        foreach (preg_split('/\R/', $status) ?: [] as $line) {
            if (preg_match('/^([A-Za-z_]+):\s*(.+)$/', $line, $m)) {
                $fields[$m[1]] = trim($m[2]);
            }
        }

        $kb = fn(string $key): int =>
            preg_match('/^(\d+)/', (string)($fields[$key] ?? ''), $m) ? (int)$m[1] * 1024 : 0;

        $uid = preg_match('/^(\d+)/', (string)($fields['Uid'] ?? ''), $m) ? (int)$m[1] : -1;
        $rss = $kb('VmRSS');
        $swap = $kb('VmSwap');
        $thr = (int)($fields['Threads'] ?? 1);

        $rssSum += $rss;
        $swapSum += $swap;
        $threads += $thr;

        $stat = hostProcStat($procRoot, (int)$entry);

        $procs[] = [
            'pid' => (int)$entry,
            'user' => $users[$uid] ?? (string)$uid,
            'uid' => $uid,
            'name' => (string)($fields['Name'] ?? '?'),
            'rss_bytes' => $rss,
            'pss_bytes' => null,
            'swap_bytes' => $swap,
            'vsz_bytes' => $kb('VmSize'),
            'threads' => $thr,
            'elapsed_seconds' => hostElapsed($stat['started'] ?? null, $boot, $ticks),
            'state' => $stat['state'] ?? null,
            'ppid' => $stat['ppid'] ?? null,
            // Processor time since it started, in jiffies. A counter, so it
            // says nothing until it is differenced - see hostCpuRates.
            'cpu_ticks' => $stat === null ? null : $stat['utime'] + $stat['stime'],
            'started_ticks' => $stat['started'] ?? null,
            /*
             * The share of its own lifetime this process has spent on a core.
             *
             * What `ps` calls %CPU, and not what `top` calls %CPU. A backup
             * that pinned a core for its first two minutes and has been idle
             * for six hours still reports a high figure here, because the
             * average includes the two minutes forever. Useful precisely
             * because of that - it is the column that says "this has always
             * been expensive" rather than "this is expensive now".
             */
            'cpu_lifetime_percent' => null,
            // Block-layer bytes, so a service re-reading its own cache all day
            // does not look like a heavy reader. Readable for every process
            // when running as root, which is the point of this collector.
            'io' => hostProcessIo($procRoot, (int)$entry),
            'command' => hostCommandLine($procRoot, (int)$entry, (string)($fields['Name'] ?? '')),
        ];

        $life = end($procs);
        if ($life['cpu_ticks'] !== null && $ticks > 0 && (int)$life['elapsed_seconds'] > 0) {
            $procs[count($procs) - 1]['cpu_lifetime_percent'] =
                round(($life['cpu_ticks'] / $ticks) / $life['elapsed_seconds'] * 100, 2);
        }
    }

    // Biggest first, then Pss for the head of the list.
    usort($procs, fn(array $a, array $b): int => $b['rss_bytes'] <=> $a['rss_bytes']);

    $pssSum = 0;
    $pssCount = 0;
    foreach ($procs as $i => $proc) {
        if ($i >= $pssLimit) {
            break;
        }
        $pss = hostPss($procRoot, $proc['pid']);
        $procs[$i]['pss_bytes'] = $pss;
        if ($pss !== null) {
            $pssSum += $pss;
            $pssCount++;
        }
    }

    return [
        'procs' => $procs,
        'rss_sum' => $rssSum,
        'pss_sum' => $pssSum,
        // How many processes that total actually covers. Without it the sum
        // reads as a whole-machine figure when it is the biggest few.
        'pss_procs' => $pssCount,
        'swap_sum' => $swapSum,
        'threads' => $threads,
        'available' => true,
    ];
}

/**
 * The resource footprint of this installation's collectors and everything
 * they launched.
 *
 * A command name is not enough: `php`, `sh`, `timeout`, and the utility the
 * collector invokes are all ordinary programs.  Start from the two known
 * entry-point paths, then walk parent PID links, so a collector's children
 * remain attributed even after exec has replaced the shell command.  The
 * current host collector PID is included as a belt-and-braces root: procfs can
 * truncate a command line but it must not make the collector invisible.
 *
 * Load average cannot be split exactly.  Linux stores an exponentially
 * decayed count of runnable *and uninterruptible* tasks, not per-process
 * contributions.  `runnable` is therefore reported beside load as the
 * monitor's instantaneous queue presence, never subtracted from load1.
 *
 * @param list<array<string, mixed>> $procs
 * @return array{processes: int, runnable: int, cpu_percent: ?float, rss_bytes: int, pss_bytes: ?int}
 */
function hostMonitoringImpact(array $procs, string $installRoot, string $procRoot = '/proc'): array
{
    $byPid = [];
    $children = [];
    $roots = [];
    $entries = [rtrim($installRoot, '/') . '/cron.php', rtrim($installRoot, '/') . '/host-cron.php'];

    foreach ($procs as $proc) {
        $pid = (int)($proc['pid'] ?? 0);
        if ($pid <= 0) continue;
        $byPid[$pid] = $proc;
        $parent = (int)($proc['ppid'] ?? 0);
        if ($parent > 0) $children[$parent][] = $pid;
        $command = (string)($proc['command'] ?? '');
        foreach ($entries as $entry) {
            if ($command !== '' && str_contains($command, $entry)) {
                $roots[$pid] = true;
                break;
            }
        }
    }
    if (isset($byPid[getmypid()])) $roots[getmypid()] = true;

    $seen = [];
    $queue = array_keys($roots);
    while ($queue !== []) {
        $pid = array_pop($queue);
        if (isset($seen[$pid]) || !isset($byPid[$pid])) continue;
        $seen[$pid] = true;
        foreach ($children[$pid] ?? [] as $child) $queue[] = $child;
    }

    $runnable = $rss = $pss = 0;
    $cpu = 0.0;
    $hasCpu = $hasPss = false;
    foreach (array_keys($seen) as $pid) {
        $proc = $byPid[$pid];
        $rss += max(0, (int)($proc['rss_bytes'] ?? 0));
        if (in_array((string)($proc['state'] ?? ''), ['R', 'D'], true)) $runnable++;
        if (($proc['cpu_percent'] ?? null) !== null) {
            $cpu += max(0.0, (float)$proc['cpu_percent']);
            $hasCpu = true;
        }
        // Get PSS for this small, explicitly selected tree even if it fell
        // outside the normal "largest processes" sampling budget.
        $part = hostPss($procRoot, $pid);
        if ($part !== null) {
            $pss += $part;
            $hasPss = true;
        }
    }

    return [
        'processes' => count($seen),
        'runnable' => $runnable,
        'cpu_percent' => $hasCpu ? round($cpu, 2) : null,
        'rss_bytes' => $rss,
        'pss_bytes' => $hasPss ? $pss : null,
    ];
}

/**
 * What one process has actually read from and written to a disk.
 *
 * read_bytes/write_bytes rather than rchar/wchar: those count every byte handed
 * to read() and write() including the ones served from page cache, so a process
 * looping over a cached file reports gigabytes of I/O while the disk stays
 * idle. cancelled_write_bytes is subtracted from writes - it is the accounting
 * for dirty pages discarded before flush, typically a file written and then
 * deleted, which never reached the platter.
 *
 * @return array{read: int, write: int}
 */
function hostProcessIo(string $procRoot, int $pid): array
{
    $raw = @file_get_contents("$procRoot/$pid/io");
    if ($raw === false) {
        /*
         * Not readable is not the same as no I/O, and this used to say zero for
         * both.
         *
         * /proc/<pid>/io is readable by root and by the process's own owner,
         * and by nobody else - so the account collector sees zero for every
         * process it does not own, and a kernel thread has no io file at all.
         * Reporting that as "this process did no disk I/O" is a confident wrong
         * answer, and it is wrong in the direction that hides the culprit.
         */
        return ['read' => 0, 'write' => 0, 'readable' => false];
    }

    $get = function (string $key) use ($raw): int {
        return preg_match('/^' . $key . ':\s+(\d+)/m', $raw, $m) ? (int)$m[1] : 0;
    };

    return [
        'read' => $get('read_bytes'),
        'write' => max(0, $get('write_bytes') - $get('cancelled_write_bytes')),
        'readable' => true,
    ];
}

/**
 * Which processes are worth writing down.
 *
 * Sorting by memory and taking the top twenty-five is the obvious rule and it
 * loses exactly the process this feature exists to find. Webalizer holds about
 * twenty megabytes while it works; on a machine also running MariaDB, Forgejo
 * and JetBackup it is nowhere near the top of a list ordered by RSS, so its
 * I/O was never recorded and no amount of querying afterwards could recover it.
 *
 * So the kept set is the union of three rankings: the heaviest on memory, the
 * heaviest on disk, and the heaviest on the processor. A process qualifies by
 * being remarkable in any one dimension rather than having to be remarkable in
 * the one that happens to be the sort key. The union is typically well under
 * the sum, because on a busy machine the same few processes tend to top more
 * than one - but not reliably: the process holding two gigabytes is usually
 * asleep, and the one pinning a core is often a few megabytes of shell script.
 *
 * @param list<array> $procs sorted by rss_bytes, descending
 * @return list<array>
 */
function hostKeepProcesses(array $procs): array
{
    $keep = [];
    foreach (array_slice($procs, 0, HOST_KEEP_PROCS) as $row) {
        $keep[(int)$row['pid']] = $row;
    }

    $byIo = array_filter($procs, fn(array $r): bool => ($r['io']['readable'] ?? false));
    usort($byIo, function (array $a, array $b): int {
        $total = fn(array $r): int => (int)$r['io']['read'] + (int)$r['io']['write'];
        return $total($b) <=> $total($a);
    });

    foreach (array_slice($byIo, 0, HOST_KEEP_IO_PROCS) as $row) {
        // A process that has never touched a disk is not an I/O finding, and
        // including it would just pad the table with zeroes.
        if ((int)$row['io']['read'] + (int)$row['io']['write'] <= 0) {
            break;
        }
        $keep[(int)$row['pid']] = $row;
    }

    /*
     * And again by processor use, which overlaps the other two hardly at all.
     *
     * Ranked on the recent figure where there is one and on the lifetime figure
     * where there is not, so a process taking a core on its first reading -
     * which is exactly when somebody is looking - is not invisible until the
     * minute after.
     */
    $byCpu = array_filter(
        $procs,
        fn(array $r): bool => ($r['cpu_percent'] ?? $r['cpu_lifetime_percent'] ?? null) !== null
    );
    usort($byCpu, function (array $a, array $b): int {
        $of = fn(array $r): float => (float)($r['cpu_percent'] ?? $r['cpu_lifetime_percent'] ?? 0);
        return $of($b) <=> $of($a);
    });

    foreach (array_slice($byCpu, 0, HOST_KEEP_CPU_PROCS) as $row) {
        // An idle process is not a CPU finding, and a table padded with zeroes
        // is how a CPU page stops being read.
        if ((float)($row['cpu_percent'] ?? $row['cpu_lifetime_percent'] ?? 0) <= 0) {
            break;
        }
        $keep[(int)$row['pid']] = $row;
    }

    return array_values($keep);
}

/**
 * What each process has done with the processor since the last reading.
 *
 * The figure `top` shows, and the one people mean by "CPU%": jiffies burned
 * between two readings, divided by the wall-clock seconds between them. Not
 * capped at 100 - a process with eight threads on eight cores genuinely used
 * 800% of one core's worth of time, and clamping it would hide the single most
 * useful fact about it.
 *
 * The previous counts come from host_proc_ticks, which carries every live
 * process rather than the kept few, because ranking by CPU out of a table
 * selected by memory finds the busiest of the fattest.
 *
 * Three things are refused rather than guessed:
 *
 *   - a pid with no previous row. Nothing to difference; a brand-new process
 *     gets its lifetime figure and no recent one, which is honest.
 *   - a pid whose start time has changed. The number was reused and the
 *     counter belongs to a process that has since died; differencing across
 *     that produces a spectacular figure for an innocent process.
 *   - a negative delta, which means the same thing and is worth catching
 *     separately in case a kernel ever reports start times this cannot see.
 *
 * @return array<int, float> pid to percent of one core
 */
function hostCpuRates(PDO $pdo, array $procs, int $ticks, int $now): array
{
    if ($ticks <= 0) {
        return [];
    }

    $previous = [];
    try {
        foreach ($pdo->query('SELECT pid, started, ticks, at FROM host_proc_ticks') as $row) {
            $previous[(int)$row['pid']] = $row;
        }
    } catch (Throwable) {
        // The table arrives with a migration, and a collector running against a
        // database from before it should still take its reading.
        return [];
    }

    $rates = [];
    foreach ($procs as $proc) {
        $pid = (int)$proc['pid'];
        $ours = $proc['cpu_ticks'] ?? null;
        $was = $previous[$pid] ?? null;
        if ($ours === null || $was === null) {
            continue;
        }

        $started = $proc['started_ticks'] ?? null;
        if ($started !== null && $was['started'] !== null && (int)$was['started'] !== (int)$started) {
            continue;
        }

        $seconds = $now - (int)$was['at'];
        $delta = $ours - (int)$was['ticks'];
        if ($seconds <= 0 || $delta < 0) {
            continue;
        }

        $rates[$pid] = round(($delta / $ticks) / $seconds * 100, 2);
    }

    return $rates;
}

/**
 * Replaces the scratchpad of per-process tick counts.
 *
 * Whole, every reading, inside the caller's transaction. Deleting first rather
 * than upserting is what keeps dead processes out: a pid that has exited leaves
 * a row that would otherwise sit there until its number came round again, at
 * which point it would be differenced against a stranger.
 */
function hostRememberCpuTicks(PDO $pdo, array $procs, int $now): void
{
    $pdo->exec('DELETE FROM host_proc_ticks');

    $write = $pdo->prepare(
        'INSERT OR REPLACE INTO host_proc_ticks (pid, started, ticks, at) VALUES (?, ?, ?, ?)'
    );
    foreach ($procs as $proc) {
        if (($proc['cpu_ticks'] ?? null) === null) {
            continue;
        }
        $write->execute([
            (int)$proc['pid'], $proc['started_ticks'] ?? null, (int)$proc['cpu_ticks'], $now,
        ]);
    }
}

/**
 * How busy each core was, against the same core in the previous reading.
 *
 * Per core rather than aggregate because the aggregate has already thrown away
 * the only thing worth knowing: whether the work is spread. Idle counts as
 * idle+iowait, exactly as the whole-machine figure does, so a core waiting on a
 * disk does not read as a core doing work.
 *
 * Cores present in this reading and absent from the last - a machine that has
 * been resized, or a first reading - come back with a null percentage rather
 * than a zero, because "not measurable yet" and "idle" look identical in a bar
 * chart and are not the same claim.
 *
 * @return array<int, ?float> cpu index to busy percent
 */
function hostCoreBusy(array $now, array $before): array
{
    $busy = [];
    foreach ($now as $index => $core) {
        $was = $before[$index] ?? null;
        $busy[$index] = null;
        if ($was === null) {
            continue;
        }

        $totalDelta = $core['total'] - (int)$was['total_ticks'];
        // A counter that went backwards is a reboot, not a measurement.
        if ($totalDelta <= 0) {
            continue;
        }

        $idleDelta = ($core['idle'] + $core['iowait'])
            - ((int)$was['idle_ticks']);
        $busy[$index] = round(max(0.0, min(100.0, (1 - $idleDelta / $totalDelta) * 100)), 2);
    }

    return $busy;
}

/** Proportional set size for one process, or null where the kernel will not say. */
function hostPss(string $procRoot, int $pid): ?int
{
    $raw = @file_get_contents("$procRoot/$pid/smaps_rollup");
    if ($raw === false) {
        return null;
    }
    return preg_match('/^Pss:\s+(\d+)\s*kB/m', $raw, $m) ? (int)$m[1] * 1024 : null;
}

/**
 * The command line, with the arguments back in one piece.
 *
 * /proc separates them with NUL bytes, which is the only reason a command line
 * with spaces in an argument can be read back correctly at all - and why this
 * is done here rather than by parsing ps output. A kernel thread has an empty
 * cmdline; it gets its name in brackets, the way ps writes it.
 */
function hostCommandLine(string $procRoot, int $pid, string $name): string
{
    $raw = @file_get_contents("$procRoot/$pid/cmdline");
    if ($raw === false || trim($raw, "\0") === '') {
        return $name === '' ? '' : "[$name]";
    }

    $line = trim(str_replace("\0", ' ', $raw));
    return mb_substr(redactSecrets($line), 0, 400);
}

/**
 * The fixed-width half of /proc/PID/stat, read once and parsed once.
 *
 * State, parent, start time and processor time all live on the same line, and
 * this used to be three functions that opened the same file three times for
 * three of them - once per process, on every reading. One read gets all four
 * and the fourth is the one this file could not previously answer at all.
 *
 * Parsed from the last ')' rather than by splitting on spaces. Field two is the
 * executable name in brackets and it can contain both spaces and brackets -
 * "(Web Content)" is real - so counting fields from the left lands on the wrong
 * one for exactly the processes most worth looking at. Everything after the
 * final ')' is fixed-width and safe to index.
 *
 * The fields taken, and why each is here:
 *
 *   state     R running or runnable      doing work
 *             S sleeping                 waiting for something, normally
 *             D uninterruptible sleep    waiting on I/O and unkillable - a pile
 *                                        of these is a stuck disk or a hung
 *                                        network filesystem, not a busy one
 *             Z zombie                   finished, nobody collected the result
 *             T stopped
 *             Without it "18 copies of git" is a count. With it, eighteen in S
 *             with short ages is a busy Git server and eighteen in D is a
 *             problem.
 *
 *   ppid      Eighteen processes sharing one parent are one job fanning out -
 *             or one job that has stopped collecting its children. Eighteen
 *             with eighteen different parents are eighteen unrelated arrivals,
 *             which for a Git server is simply traffic. Invisible in a count.
 *
 *   utime     Jiffies spent in userland and in the kernel on this process's
 *   stime     behalf. Summed, they are the total processor time it has ever
 *             used - a counter since it started, not a rate, and useless until
 *             differenced against another reading or against its own age.
 *
 *   started   Jiffies after boot at which it started, which is what turns the
 *             counter above into either of the percentages anybody wants: over
 *             the last minute, or over its whole life.
 *
 * @return array{state: ?string, ppid: ?int, utime: int, stime: int, started: ?int}|null
 */
function hostProcStat(string $procRoot, int $pid): ?array
{
    $raw = @file_get_contents("$procRoot/$pid/stat");
    if ($raw === false) {
        return null;
    }

    $close = strrpos($raw, ')');
    if ($close === false) {
        return null;
    }

    $rest = preg_split('/\s+/', trim(substr($raw, $close + 1))) ?: [];
    $at = static fn(int $i): ?int =>
        isset($rest[$i]) && ctype_digit($rest[$i]) ? (int)$rest[$i] : null;

    // Counting from after the name: state is [0], so field N overall is [N-3].
    return [
        'state' => isset($rest[0]) && $rest[0] !== '' ? substr($rest[0], 0, 1) : null,
        'ppid' => $at(1),
        'utime' => $at(11) ?? 0,
        'stime' => $at(12) ?? 0,
        'started' => $at(19),
    ];
}

/** Seconds since a process started, from its start time and the boot clock. */
function hostElapsed(?int $started, int $boot, int $ticks): ?int
{
    if ($started === null || $boot <= 0 || $ticks <= 0) {
        return null;
    }

    return max(0, (int)round(time() - ($boot + $started / $ticks)));
}

/** Kernel clock ticks per second. Practically always 100, but not by standard. */
function hostClockTicks(): int
{
    if (function_exists('posix_sysconf') && defined('POSIX_SC_CLK_TCK')) {
        $ticks = @posix_sysconf(POSIX_SC_CLK_TCK);
        if (is_int($ticks) && $ticks > 0) {
            return $ticks;
        }
    }
    $out = shellRead('getconf CLK_TCK 2>/dev/null');
    return $out !== null && ctype_digit(trim($out)) ? (int)trim($out) : 100;
}

/**
 * uid to name, read once.
 *
 * From posix_getpwuid where the extension is there, and /etc/passwd where it is
 * not. Names matter here in a way they do not in the account collector: "uid
 * 1005 is holding two gigabytes" is a puzzle, and "git is holding two
 * gigabytes" is an answer.
 *
 * @return array<int, string>
 */
function hostUserNames(string $passwd = '/etc/passwd'): array
{
    // Keyed by path, not a bare static: a cache that ignores its argument
    // returns the first caller's answer to everyone, which in a test means the
    // fixture silently reads the real machine and looks like it passed.
    static $cache = [];
    if (isset($cache[$passwd])) {
        return $cache[$passwd];
    }

    $names = [];
    $raw = @file_get_contents($passwd);
    if ($raw !== false) {
        foreach (preg_split('/\R/', $raw) ?: [] as $line) {
            $parts = explode(':', $line);
            if (count($parts) >= 3 && ctype_digit($parts[2])) {
                $names[(int)$parts[2]] = $parts[0];
            }
        }
    }

    return $cache[$passwd] = $names;
}

/**
 * Memory added up per user.
 *
 * Pss where it was measured and RSS otherwise, kept in separate columns rather
 * than blended: a total that silently mixes two different measurements is worse
 * than two honest ones.
 *
 * @return list<array>
 */
function hostByUser(array $procs): array
{
    $users = [];

    foreach ($procs as $proc) {
        $name = $proc['user'];
        $users[$name] ??= [
            'user' => $name, 'procs' => 0, 'threads' => 0,
            'rss_bytes' => 0, 'pss_bytes' => 0, 'swap_bytes' => 0,
        ];

        $users[$name]['procs']++;
        $users[$name]['threads'] += (int)$proc['threads'];
        $users[$name]['rss_bytes'] += (int)$proc['rss_bytes'];
        $users[$name]['pss_bytes'] += (int)($proc['pss_bytes'] ?? 0);
        $users[$name]['swap_bytes'] += (int)$proc['swap_bytes'];
    }

    usort($users, fn(array $a, array $b): int => $b['rss_bytes'] <=> $a['rss_bytes']);
    return array_values($users);
}

/**
 * Memory added up per service, biggest first.
 *
 * Grouped by the executable, so the row survives a restart. A pid does not: the
 * daemon comes back with a new one, and a series keyed on pids resets to zero
 * every time something is upgraded - which is precisely when you most want to
 * compare before and after.
 *
 * **Grouped first, then ranked.** The order matters and is easy to get
 * backwards. Taking the biggest processes and grouping what survives loses
 * every service made of many small workers: a hundred and twenty httpd children
 * at 20 MB each is 2.4 GB that never appears in a top-25-processes list, while
 * one Forgejo at 1.8 GB does. Ranking the groups instead puts them side by side
 * at their real weight.
 *
 * @return list<array>
 */
/**
 * The distinguishing part of a command line.
 *
 * Everything after the subcommand is usually a path, and paths are what make
 * eighteen identical jobs look like eighteen different ones. Two tokens is
 * enough to tell `git upload-pack` from `git gc` while collapsing the repos.
 */
function commandShape(string $command, string $name): string
{
    $command = trim($command);
    if ($command === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $command) ?: [];
    $first = basename($parts[0] ?? $name);
    $second = $parts[1] ?? '';

    // A second token that is a path says nothing; one that is a subcommand
    // says everything.
    if ($second === '' || str_starts_with($second, '/') || str_starts_with($second, '-')) {
        return $first;
    }

    return $first . ' ' . $second;
}

/** "S:16 R:2" - a distribution small enough to store on every row. */
function summariseCounts(array $counts, int $keep): string
{
    $parts = [];
    foreach (array_slice($counts, 0, $keep, true) as $key => $n) {
        $parts[] = $key . ':' . $n;
    }

    return implode(' ', $parts);
}

function hostByService(array $procs): array
{
    $services = [];

    foreach ($procs as $proc) {
        $name = $proc['name'];
        $services[$name] ??= [
            'name' => $name, 'procs' => 0, 'threads' => 0,
            'rss_bytes' => 0, 'pss_bytes' => 0, 'swap_bytes' => 0,
            'read_bytes' => 0, 'write_bytes' => 0,
            // Both ends, because together they say what neither says alone: a
            // group whose oldest is hours and whose newest is seconds is
            // churning, not merely long-lived.
            'oldest_seconds' => null, 'newest_seconds' => null,
            /*
             * The evidence a count on its own cannot supply.
             *
             * Eighteen copies of git is a number. Eighteen sharing one parent,
             * all in D, all four hours old is a diagnosis; eighteen with
             * eighteen parents, all in S, all under a minute is a busy Git
             * server. Collected here because it is nearly free while the
             * process list is already in hand, and impossible afterwards.
             */
            'ages' => [], 'states' => [], 'parents' => [], 'commands' => [],
        ];

        $services[$name]['procs']++;
        $services[$name]['threads'] += (int)$proc['threads'];
        $services[$name]['rss_bytes'] += (int)$proc['rss_bytes'];
        $services[$name]['pss_bytes'] += (int)($proc['pss_bytes'] ?? 0);
        $services[$name]['swap_bytes'] += (int)$proc['swap_bytes'];
        $services[$name]['read_bytes'] += (int)($proc['io']['read'] ?? 0);
        $services[$name]['write_bytes'] += (int)($proc['io']['write'] ?? 0);

        if (($proc['state'] ?? null) !== null) {
            $services[$name]['states'][$proc['state']] =
                ($services[$name]['states'][$proc['state']] ?? 0) + 1;
        }

        if (($proc['ppid'] ?? null) !== null) {
            $services[$name]['parents'][(int)$proc['ppid']] =
                ($services[$name]['parents'][(int)$proc['ppid']] ?? 0) + 1;
        }

        /*
         * Command lines grouped by shape, not counted verbatim.
         *
         * Eighteen `git upload-pack /repos/a.git` through `.../r.git` are
         * eighteen different strings and one kind of work. Keeping the first
         * couple of tokens turns them into one row saying "upload-pack x18",
         * which is the thing worth reading.
         */
        $shape = commandShape((string)($proc['command'] ?? ''), $name);
        if ($shape !== '') {
            $services[$name]['commands'][$shape] =
                ($services[$name]['commands'][$shape] ?? 0) + 1;
        }

        $age = $proc['elapsed_seconds'] ?? null;
        if ($age !== null) {
            $services[$name]['ages'][] = $age;
        }
        if ($age !== null) {
            $services[$name]['oldest_seconds'] = $services[$name]['oldest_seconds'] === null
                ? $age : max($services[$name]['oldest_seconds'], $age);
            $services[$name]['newest_seconds'] = $services[$name]['newest_seconds'] === null
                ? $age : min($services[$name]['newest_seconds'], $age);
        }
    }

    foreach ($services as $name => $row) {
        $ages = $row['ages'];
        sort($ages);
        $services[$name]['median_seconds'] = $ages === []
            ? null
            : (int)$ages[intdiv(count($ages), 2)];

        arsort($services[$name]['states']);
        arsort($services[$name]['parents']);
        arsort($services[$name]['commands']);

        $services[$name]['distinct_parents'] = count($row['parents']);
        $services[$name]['state_summary'] = summariseCounts($services[$name]['states'], 4);
        $services[$name]['command_summary'] = summariseCounts($services[$name]['commands'], 3);

        // The raw arrays have done their job and would bloat every row.
        unset($services[$name]['ages'], $services[$name]['states'],
              $services[$name]['parents'], $services[$name]['commands']);
    }

    $services = array_values(array_filter(
        $services,
        fn(array $row): bool => $row['rss_bytes'] >= HOST_SERVICE_MIN_BYTES
    ));

    usort($services, fn(array $a, array $b): int => $b['rss_bytes'] <=> $a['rss_bytes']);
    return array_slice($services, 0, HOST_KEEP_SERVICES);
}

/**
 * Kernel slab caches, biggest first.
 *
 * The cache size is worked out the way slabtop does it - slabs times pages per
 * slab times the page size - rather than as objects times object size. The two
 * disagree, because a slab is whole pages and the objects in it rarely divide
 * evenly into them; the difference is the memory actually held.
 *
 * @return list<array>
 */
function hostSlab(string $procRoot = '/proc', int $pageSize = 4096): array
{
    $raw = @file_get_contents($procRoot . '/slabinfo');
    if ($raw === false) {
        return [];
    }

    $out = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || str_starts_with($line, 'slabinfo')) {
            continue;
        }

        // name active objs size perslab pagesperslab : tunables ... : slabdata active_slabs num_slabs ...
        $parts = preg_split('/\s+/', $line) ?: [];
        if (count($parts) < 6) {
            continue;
        }

        $numSlabs = null;
        foreach ($parts as $i => $part) {
            if ($part === 'slabdata' && isset($parts[$i + 2])) {
                $numSlabs = (int)$parts[$i + 2];
                break;
            }
        }

        $objs = (int)$parts[2];
        $objSize = (int)$parts[3];
        $perSlab = max(1, (int)$parts[4]);
        $pagesPer = max(1, (int)$parts[5]);

        // Without a slabdata section, fall back to what the object counts imply.
        $slabs = $numSlabs ?? (int)ceil($objs / $perSlab);

        $out[] = [
            'name' => $parts[0],
            'objs' => $objs,
            'obj_bytes' => $objSize,
            'cache_bytes' => $slabs * $pagesPer * $pageSize,
        ];
    }

    usort($out, fn(array $a, array $b): int => $b['cache_bytes'] <=> $a['cache_bytes']);
    return $out;
}

/**
 * Shared memory: System V segments and the tmpfs at /dev/shm.
 *
 * Both are memory that belongs to no process in a way `ps` will show. A segment
 * whose creator has exited still occupies RAM until someone removes it, which
 * is a slow leak with no owner to point at.
 *
 * @return array{segments: int, bytes: int, devshm_total: ?int, devshm_used: ?int}
 */
function hostSharedMemory(): array
{
    $segments = 0;
    $bytes = 0;

    $out = shellRead('ipcs -m 2>/dev/null');
    if ($out !== null) {
        foreach (preg_split('/\R/', $out) ?: [] as $line) {
            // key shmid owner perms bytes nattch status
            $parts = preg_split('/\s+/', trim($line)) ?: [];
            if (count($parts) >= 6 && str_starts_with($parts[0], '0x') && ctype_digit($parts[4])) {
                $segments++;
                $bytes += (int)$parts[4];
            }
        }
    }

    $total = $used = null;
    if (is_dir('/dev/shm')) {
        $free = @disk_free_space('/dev/shm');
        $size = @disk_total_space('/dev/shm');
        if ($size !== false && $free !== false) {
            $total = (int)$size;
            $used = (int)($size - $free);
        }
    }

    return ['segments' => $segments, 'bytes' => $bytes,
            'devshm_total' => $total, 'devshm_used' => $used];
}

/**
 * Sizes of files the account cannot stat.
 *
 * Globs from the config, defaulting to Apache's domain logs - the place a
 * rotated log quietly reaches 763 MB while every tool the account can run
 * reports the home directory as unchanged.
 *
 * @return list<array>
 */
function hostWatchedFiles(array $config, int $limit = 40, string $passwd = '/etc/passwd'): array
{
    $globs = $config['host']['watch'] ?? [];
    $minimum = (int)($config['host']['watch_min_bytes'] ?? 1048576);

    $found = [];
    foreach ($globs as $pattern) {
        foreach (glob((string)$pattern) ?: [] as $path) {
            if (!is_file($path)) {
                continue;
            }
            /*
             * One stat, for the identity as well as the size.
             *
             * The watch list deliberately covers both /etc/apache2/logs/domlogs/*
             * and the per-account directory below it, which is how the same log
             * shows up twice. Without the inode there is no way to tell that
             * apart from two genuinely large files, and the difference is 199 MB
             * against 398 MB.
             */
            $stat = @stat($path);
            if ($stat === false || (int)$stat['size'] < $minimum) {
                continue;
            }

            $uid = $stat['uid'] ?? false;
            $found[$path] = [
                'path' => $path,
                'bytes' => (int)$stat['size'],
                'owner' => $uid === false ? null : (hostUserNames($passwd)[$uid] ?? (string)$uid),
                'mtime' => (int)($stat['mtime'] ?? 0),
                'device' => (int)($stat['dev'] ?? 0),
                'inode' => (int)($stat['ino'] ?? 0),
                'links' => (int)($stat['nlink'] ?? 1),
            ];
        }
    }

    $rows = array_values($found);
    usort($rows, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);
    return array_slice($rows, 0, $limit);
}

/**
 * CloudLinux limits and faults, where CloudLinux is installed.
 *
 * A process killed for exceeding an LVE limit leaves no other trace: it is gone
 * and nothing in the account's logs says why. A rising fault count is the
 * evidence, and it is only visible from the host.
 *
 * @return array{available: bool, reason: ?string, rows: list<array>}
 */
function hostLve(): array
{
    if (!is_file('/usr/sbin/lveinfo') && !is_file('/usr/bin/lveinfo')) {
        return ['available' => false, 'reason' => 'CloudLinux is not installed here.', 'rows' => []];
    }

    $out = shellRead('lveinfo --json --period=1h --by-fault=any 2>/dev/null');
    if ($out === null || trim($out) === '') {
        return ['available' => false, 'reason' => 'lveinfo returned nothing.', 'rows' => []];
    }

    $parsed = json_decode($out, true);
    if (!is_array($parsed)) {
        return ['available' => false, 'reason' => 'lveinfo output was not JSON.', 'rows' => []];
    }

    /*
     * Shape-tolerant on purpose.
     *
     * lveinfo's JSON has moved between CloudLinux releases, and a collector
     * that throws on an unexpected key would take the whole host reading down
     * with it over the least important table here. Anything unrecognised is
     * skipped and the rest is kept.
     */
    $rows = [];
    foreach ($parsed['data'] ?? $parsed as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $user = (string)($entry['user'] ?? $entry['username'] ?? $entry['id'] ?? '');
        if ($user === '') {
            continue;
        }
        $rows[] = [
            'user' => $user,
            'cpu_percent' => isset($entry['cpu']) ? (float)$entry['cpu'] : null,
            'mem_bytes' => isset($entry['mem']) ? (int)$entry['mem'] : null,
            'procs' => isset($entry['nproc']) ? (int)$entry['nproc'] : null,
            'faults_cpu' => (int)($entry['cpuf'] ?? $entry['cpu_faults'] ?? 0),
            'faults_mem' => (int)($entry['memf'] ?? $entry['mem_faults'] ?? 0),
            'faults_procs' => (int)($entry['nprocf'] ?? $entry['nproc_faults'] ?? 0),
        ];
    }

    return ['available' => true, 'reason' => null, 'rows' => $rows];
}

/**
 * Takes one host reading and writes it down.
 *
 * Everything in one transaction, so a reading is either wholly there or not
 * there at all: a sample row whose processes failed to insert would show up as
 * a machine with memory in use and nothing running on it, which is a worse lie
 * than a missing minute.
 *
 * @return array<string, mixed>
 */
function hostTakeReading(
    array $config,
    PDO $pdo,
    string $source = 'cron',
    string $procRoot = '/proc',
    string $passwd = '/etc/passwd'
): array {
    $mem = hostMeminfo($procRoot);
    $processes = hostProcesses($procRoot, HOST_PSS_LIMIT, $passwd);

    /*
     * A reading with no /proc behind it is not a reading.
     *
     * Without this the collector happily stores a row of nulls every minute,
     * and the dashboard draws a machine with no memory and nothing running on
     * it - which looks like a catastrophe rather than like a collector pointed
     * at the wrong place. Refusing says which it is.
     */
    if (!$processes['available'] && !$mem) {
        throw new RuntimeException(
            "nothing readable at $procRoot - this collector needs a Linux host with /proc"
        );
    }
    $accounting = hostMemoryAccounting($mem, $processes['rss_sum'], $processes['pss_sum']);
    $slab = hostSlab($procRoot);
    $shm = hostSharedMemory();
    $files = hostWatchedFiles($config, 40, $passwd);
    $lve = hostLve();
    $load = hostLoad($procRoot);
    $disks = diskStats($procRoot);
    $pressure = pressureIo($procRoot);
    /*
     * The whole of /proc/stat rather than the two figures the busy percentage
     * needs, and the processor's pressure file beside it.
     *
     * Both are a single small read, and between them they are the difference
     * between "the CPU is at 90%" and knowing whether that is work being done,
     * a disk being waited on, or a hypervisor taking the cores away.
     */
    $cpuStat = cpuBreakdown($procRoot);
    $cpuPressure = pressureCpu($procRoot);
    $history = bootHistory();

    $get = fn(string $key): ?int => isset($mem[$key]) ? (int)$mem[$key] : null;

    // Taken once and shared, so a sample and the service rows written beside it
    // carry the same instant even if the insert loop takes a second.
    $takenAt = time();

    $pdo->beginTransaction();
    try {
        /*
         * Column names and values from one array, never two lists.
         *
         * The pair used to be written out separately - a column list and a row
         * of question marks - and adding three columns without adding three
         * placeholders produced "41 values for 44 columns" at the moment a
         * fixture exercised it. Deriving both from the same keys makes that
         * impossible rather than merely unlikely, and it is how takeVitals()
         * has always built its insert.
         */
        /*
         * The host's real CPU use, worked out against the previous host row.
         *
         * A tick counter says nothing on its own - CPU use only exists as a
         * difference between two moments - so the first reading after an
         * install has no percentage, and neither does the first after a reboot,
         * where the counters restart and a difference would be negative. Both
         * are left null rather than guessed at.
         */
        $cpu = $cpuStat === null
            ? cpuTicks()
            : ['total' => $cpuStat['fields']['total'],
               // Idle for this purpose is idle plus iowait: a core waiting on a
               // disk is a core with nothing to run, and counting it as work
               // makes a storage problem read as a processor one.
               'idle' => $cpuStat['fields']['idle'] + $cpuStat['fields']['iowait']];
        $cpuPercent = null;
        $previousId = null;
        if ($cpu !== null) {
            $previous = $pdo->query(
                'SELECT id, cpu_total_ticks, cpu_idle_ticks FROM host_samples
                 WHERE cpu_total_ticks IS NOT NULL ORDER BY taken_at DESC, id DESC LIMIT 1'
            )->fetch();

            if ($previous) {
                $previousId = (int)$previous['id'];
            }
            if ($previous && $cpu['total'] > (int)$previous['cpu_total_ticks']) {
                $totalDelta = $cpu['total'] - (int)$previous['cpu_total_ticks'];
                $idleDelta = $cpu['idle'] - (int)$previous['cpu_idle_ticks'];
                $cpuPercent = round(max(0.0, min(100.0, (1 - $idleDelta / $totalDelta) * 100)), 2);
            }
        }

        /*
         * Per-process processor use, before the process rows are chosen.
         *
         * The ranking that decides what gets written down uses this, so it has
         * to happen first - and it has to happen inside the transaction,
         * because the scratchpad it reads is replaced further down and a reader
         * between the two would see an empty table.
         */
        $clockTicks = hostClockTicks();
        $cpuRates = hostCpuRates($pdo, $processes['procs'], $clockTicks, $takenAt);
        foreach ($processes['procs'] as $i => $proc) {
            $processes['procs'][$i]['cpu_percent'] = $cpuRates[(int)$proc['pid']] ?? null;
        }
        $monitoring = hostMonitoringImpact($processes['procs'], dirname(__DIR__), $procRoot);

        $sample = [
            'taken_at' => $takenAt,
            'mem_total' => $get('MemTotal'),
            'mem_free' => $get('MemFree'),
            'mem_available' => $get('MemAvailable'),
            'buffers' => $get('Buffers'),
            'cached' => $get('Cached'),
            'swap_cached' => $get('SwapCached'),
            'shmem' => $get('Shmem'),
            'anon_pages' => $get('AnonPages'),
            'mapped' => $get('Mapped'),
            'slab' => $get('Slab'),
            'sreclaimable' => $get('SReclaimable'),
            'sunreclaim' => $get('SUnreclaim'),
            'page_tables' => $get('PageTables'),
            'kernel_stack' => $get('KernelStack'),
            'dirty' => $get('Dirty'),
            'swap_total' => $get('SwapTotal'),
            'swap_free' => $get('SwapFree'),
            'used_bytes' => $accounting['used_bytes'],
            'cache_bytes' => $accounting['cache_bytes'],
            'unaccounted' => $accounting['unaccounted'],
            'rss_sum_bytes' => $processes['rss_sum'],
            'pss_sum_bytes' => $processes['pss_sum'],
            'pss_procs' => $processes['pss_procs'],
            'swap_sum_bytes' => $processes['swap_sum'],
            'proc_count' => count($processes['procs']),
            'thread_count' => $processes['threads'],
            'shm_segments' => $shm['segments'],
            'shm_bytes' => $shm['bytes'],
            'devshm_total' => $shm['devshm_total'],
            'devshm_used' => $shm['devshm_used'],
            'load1' => $load[0],
            'load5' => $load[1],
            'load15' => $load[2],
            'cpu_cores' => cpuCores(),
            'cpu_total_ticks' => $cpu['total'] ?? null,
            'cpu_idle_ticks' => $cpu['idle'] ?? null,
            'cpu_percent' => $cpuPercent,
            'monitoring_processes' => $monitoring['processes'],
            'monitoring_runnable' => $monitoring['runnable'],
            'monitoring_cpu_percent' => $monitoring['cpu_percent'],
            'monitoring_rss_bytes' => $monitoring['rss_bytes'],
            'monitoring_pss_bytes' => $monitoring['pss_bytes'],
            /*
             * The columns the single busy percentage above cannot stand in for.
             *
             * Stored as the raw counters they are read as. Differencing them is
             * the reader's job, because a rate worked out here is a rate over
             * whatever gap the cron happened to leave, and a missed minute
             * would halve it permanently in the stored history.
             */
            'cpu_user_ticks' => $cpuStat['fields']['user'] ?? null,
            'cpu_nice_ticks' => $cpuStat['fields']['nice'] ?? null,
            'cpu_system_ticks' => $cpuStat['fields']['system'] ?? null,
            'cpu_iowait_ticks' => $cpuStat['fields']['iowait'] ?? null,
            'cpu_irq_ticks' => $cpuStat['fields']['irq'] ?? null,
            'cpu_softirq_ticks' => $cpuStat['fields']['softirq'] ?? null,
            'cpu_steal_ticks' => $cpuStat['fields']['steal'] ?? null,
            'cpu_guest_ticks' => $cpuStat['fields']['guest'] ?? null,
            'ctxt_switches' => $cpuStat['ctxt'] ?? null,
            'interrupts' => $cpuStat['intr'] ?? null,
            'forks' => $cpuStat['forks'] ?? null,
            // Depths at the instant of the read, not counters: how many tasks
            // were on a core or waiting for one, and how many were blocked in
            // the kernel. The second is the number that says a load average of
            // 40 is a stuck disk rather than a busy processor.
            'procs_running' => $cpuStat['running'] ?? null,
            'procs_blocked' => $cpuStat['blocked'] ?? null,
            'cpu_pressure_some' => $cpuPressure['some10'] ?? null,
            'source' => $source,
            'io_pressure_some' => $pressure['some10'] ?? null,
            'io_pressure_full' => $pressure['full10'] ?? null,
            'io_read_bytes' => $disks['total']['read_bytes'] ?? null,
            'io_write_bytes' => $disks['total']['write_bytes'] ?? null,
            'io_ms' => $disks['total']['io_ms'] ?? null,
            // What this run could actually see, so the dashboard - which runs
            // as the account, inside a filtered /proc - can report the host's
            // capabilities rather than the cage's.
            'kernel' => php_uname('r'),
            'diskstats_available' => $disks === null ? 0 : 1,
            'psi_available' => $pressure === null ? 0 : 1,
            // Empty when it is available; otherwise the reason, read from
            // /boot/config and /proc/cmdline, which only root can see.
            'psi_reason' => pressureDiagnosis($procRoot),
            'boot_at' => bootTime($procRoot) ?: null,
        ];

        $names = implode(', ', array_keys($sample));
        $holders = implode(', ', array_fill(0, count($sample), '?'));
        $pdo->prepare("INSERT INTO host_samples ($names) VALUES ($holders)")
            ->execute(array_values($sample));

        $sampleId = (int)$pdo->lastInsertId();

        $users = $pdo->prepare(
            'INSERT INTO host_users (sample_id, user, procs, threads, rss_bytes, pss_bytes, swap_bytes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach (hostByUser($processes['procs']) as $row) {
            $users->execute([
                $sampleId, $row['user'], $row['procs'], $row['threads'],
                $row['rss_bytes'], $row['pss_bytes'], $row['swap_bytes'],
            ]);
        }

        $procs = $pdo->prepare(
            'INSERT INTO host_processes
                (sample_id, pid, user, name, rss_bytes, pss_bytes, swap_bytes, vsz_bytes,
                 threads, elapsed_seconds, command, read_bytes, write_bytes, state, ppid,
                 cpu_ticks, cpu_percent, cpu_lifetime_percent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach (hostKeepProcesses($processes['procs']) as $row) {
            $procs->execute([
                $sampleId, $row['pid'], $row['user'], $row['name'], $row['rss_bytes'],
                $row['pss_bytes'], $row['swap_bytes'], $row['vsz_bytes'], $row['threads'],
                $row['elapsed_seconds'], $row['command'],
                ($row['io']['readable'] ?? false) ? (int)$row['io']['read'] : null,
                ($row['io']['readable'] ?? false) ? (int)$row['io']['write'] : null,
                $row['state'] ?? null, $row['ppid'] ?? null,
                $row['cpu_ticks'] ?? null, $row['cpu_percent'] ?? null,
                $row['cpu_lifetime_percent'] ?? null,
            ]);
        }

        /*
         * Every live process's tick count, for the next reading to difference.
         *
         * Written after the process rows and before the commit, so the counts
         * and the sample they will be compared against are the same instant.
         */
        hostRememberCpuTicks($pdo, $processes['procs'], $takenAt);

        /*
         * Each core, with how busy it was since the previous reading.
         *
         * The previous row per core is fetched by sample id rather than by
         * "the newest before this one", because this sample has already been
         * inserted by the time we get here and would otherwise be its own
         * predecessor - every core would read as exactly 0% busy forever.
         */
        $before = [];
        if ($previousId !== null) {
            $was = $pdo->prepare('SELECT * FROM host_cpus WHERE sample_id = ?');
            $was->execute([$previousId]);
            foreach ($was->fetchAll() as $row) {
                $before[(int)$row['cpu']] = $row;
            }
        }

        $busy = hostCoreBusy($cpuStat['cpus'] ?? [], $before);
        $cores = $pdo->prepare(
            'INSERT INTO host_cpus
                (sample_id, cpu, total_ticks, idle_ticks, user_ticks, system_ticks,
                 iowait_ticks, steal_ticks, busy_percent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($cpuStat['cpus'] ?? [] as $index => $core) {
            $cores->execute([
                $sampleId, $index, $core['total'],
                // Stored the way the percentage uses it, so a reader
                // differencing this column gets the same answer this did.
                $core['idle'] + $core['iowait'],
                $core['user'], $core['system'], $core['iowait'], $core['steal'],
                $busy[$index] ?? null,
            ]);
        }

        // Its own timestamp, because this table outlives the sample it was
        // taken with - see the migration that creates it.
        $services = $pdo->prepare(
            'INSERT INTO host_services
                (taken_at, name, procs, threads, rss_bytes, pss_bytes, swap_bytes,
                 read_bytes, write_bytes, oldest_seconds, newest_seconds,
                 median_seconds, distinct_parents, state_summary, command_summary)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach (hostByService($processes['procs']) as $row) {
            $services->execute([
                $takenAt, $row['name'], $row['procs'], $row['threads'],
                $row['rss_bytes'], $row['pss_bytes'], $row['swap_bytes'],
                $row['read_bytes'], $row['write_bytes'],
                $row['oldest_seconds'], $row['newest_seconds'],
                $row['median_seconds'] ?? null, $row['distinct_parents'] ?? null,
                $row['state_summary'] ?? null, $row['command_summary'] ?? null,
            ]);
        }

        /*
         * Whether the daemons are alive, which nothing else here can say.
         *
         * Given the process table that was already read rather than reading it
         * again - two answers a second apart would disagree, and a service that
         * appeared to be down in one table and up in another would be worse
         * than either answer alone.
         */
        $daemons = $pdo->prepare(
            'INSERT INTO host_daemons
                (taken_at, name, label, state, unit, units_total, units_active,
                 active_state, sub_state, restarts, started_at, procs, pid, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach (readDaemons($config, $processes['procs'], bootTime($procRoot) ?: null) as $row) {
            $daemons->execute([
                $takenAt, $row['name'], $row['label'], $row['state'], $row['unit'],
                $row['units_total'], $row['units_active'], $row['active_state'],
                $row['sub_state'], $row['restarts'], $row['started_at'],
                $row['procs'], $row['pid'], $row['source'],
            ]);
        }

        /*
         * Written with INSERT OR REPLACE keyed on the boot time.
         *
         * `last` is re-read every collection and mostly says the same thing, so
         * this has to be idempotent. Replacing also lets the current session's
         * row pick up its end time later, once there is one.
         */
        if ($history['available']) {
            $boots = $pdo->prepare(
                'INSERT OR REPLACE INTO host_boots
                    (booted_at, kernel, ended, shutdown_at, still_running, seen_at)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($history['boots'] as $row) {
                $boots->execute([
                    $row['booted_at'], $row['kernel'], $row['ended'],
                    $row['shutdown_at'], $row['still_running'] ? 1 : 0, $takenAt,
                ]);
            }
        }

        $devices = $pdo->prepare(
            'INSERT INTO host_disks
                (sample_id, name, stacked, read_bytes, write_bytes, read_ios, write_ios,
                 io_ms, queue_ms, in_flight)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($disks['devices'] ?? [] as $row) {
            $devices->execute([
                $sampleId, $row['name'], $row['stacked'] ? 1 : 0,
                $row['read_bytes'], $row['write_bytes'], $row['read_ios'],
                $row['write_ios'], $row['io_ms'], $row['queue_ms'], $row['in_flight'],
            ]);
        }

        $slabs = $pdo->prepare(
            'INSERT INTO host_slab (sample_id, name, objs, obj_bytes, cache_bytes)
             VALUES (?, ?, ?, ?, ?)'
        );
        foreach (array_slice($slab, 0, HOST_KEEP_SLAB) as $row) {
            $slabs->execute([$sampleId, $row['name'], $row['objs'], $row['obj_bytes'], $row['cache_bytes']]);
        }

        $watched = $pdo->prepare(
            'INSERT INTO host_files (sample_id, path, bytes, owner, mtime, device, inode, links)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($files as $row) {
            $watched->execute([
                $sampleId, $row['path'], $row['bytes'], $row['owner'], $row['mtime'],
                $row['device'], $row['inode'], $row['links'],
            ]);
        }

        if ($lve['available']) {
            $limits = $pdo->prepare(
                'INSERT INTO host_lve (sample_id, user, cpu_percent, mem_bytes, procs,
                                       faults_cpu, faults_mem, faults_procs)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($lve['rows'] as $row) {
                $limits->execute([
                    $sampleId, $row['user'], $row['cpu_percent'], $row['mem_bytes'],
                    $row['procs'], $row['faults_cpu'], $row['faults_mem'], $row['faults_procs'],
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    /*
     * The network state, recorded beside the memory state.
     *
     * Part of the ordinary reading because it is the same kind of thing: a few
     * files in /proc and one `ss` call, costing milliseconds. What is *not* here
     * is the packet capture, which costs a real measurement on a live interface
     * and only ever runs when somebody asks for it.
     *
     * Failure is contained. A machine without iproute2 should still get its
     * memory reading rather than losing the whole collection to a missing
     * binary, so this cannot take the run down with it.
     */
    $network = ['ports' => 0, 'interfaces' => 0, 'peers' => 0, 'units' => 0];
    try {
        $network = storeNetwork($pdo, $sampleId);
    } catch (Throwable $e) {
        $network['error'] = $e->getMessage();
    }

    return [
        'sample_id' => $sampleId,
        'network' => $network,
        'processes' => count($processes['procs']),
        'users' => count(hostByUser($processes['procs'])),
        'slab' => min(count($slab), HOST_KEEP_SLAB),
        'files' => count($files),
        'services' => count(hostByService($processes['procs'])),
        'disks' => count($disks['devices'] ?? []),
        'cores' => count($cpuStat['cpus'] ?? []),
        'boots' => $history['available'] ? count($history['boots']) : null,
        'psi' => $pressure !== null,
        'psi_reason' => $sample['psi_reason'],
        'lve' => $lve['available'] ? count($lve['rows']) : null,
        'unaccounted' => $accounting['unaccounted'],
    ];
}

/** The three load figures, from /proc rather than sys_getloadavg for testability. */
function hostLoad(string $procRoot = '/proc'): array
{
    $raw = @file_get_contents($procRoot . '/loadavg');
    if ($raw !== false && preg_match('/^([\d.]+)\s+([\d.]+)\s+([\d.]+)/', trim($raw), $m)) {
        return [(float)$m[1], (float)$m[2], (float)$m[3]];
    }

    $load = @sys_getloadavg();
    return is_array($load) ? [$load[0], $load[1], $load[2]] : [null, null, null];
}

/**
 * Whether swap use means anything.
 *
 * It usually does not, and that is the point of this function. A machine
 * reporting 696 MiB of 1 GiB swapped looks alarming and is very often fine:
 * **Linux does not pull pages back out of swap just because RAM became free
 * again.** A page evicted during a busy hour last Tuesday stays evicted until
 * something touches it, so the figure is a high-water mark with no expiry, not
 * a measure of present distress. Alarming on the level alone trains people to
 * ignore the one time it matters.
 *
 * What matters is two things happening together: **swap climbing while
 * available memory falls**. Either alone is ordinary. Swap rising while
 * MemAvailable holds steady is the kernel tidying cold pages away, which is it
 * doing its job. MemAvailable falling while swap holds steady is cache being
 * claimed, which is also its job. Both at once means the machine has run out of
 * cheap options and is paying for memory with disk, and that is worth saying.
 *
 * Uses the same shape of estimate as the disk projection - median of the steps,
 * gated on both halves of the window agreeing - because the failure mode is
 * identical. One burst of swapping during a nightly backup would otherwise read
 * as a trend for as long as it stayed in the window.
 *
 * @return array{state: string, swap_rate: ?float, available_rate: ?float,
 *               samples: int, span_seconds: int}
 */
function swapPressure(array $rows): array
{
    $unknown = ['state' => 'unknown', 'swap_rate' => null, 'available_rate' => null,
                'samples' => count($rows), 'span_seconds' => 0];

    $swap = [];
    $available = [];
    foreach ($rows as $row) {
        $at = (int)($row['taken_at'] ?? 0);
        if (($row['swap_total'] ?? null) !== null && ($row['swap_free'] ?? null) !== null) {
            $swap[] = [$at, (float)$row['swap_total'] - (float)$row['swap_free']];
        }
        if (($row['mem_available'] ?? null) !== null) {
            $available[] = [$at, (float)$row['mem_available']];
        }
    }

    $count = count($swap);
    $span = $count > 1 ? $swap[$count - 1][0] - $swap[0][0] : 0;

    // Nothing swapped is the easy case, and worth answering even without enough
    // history to judge a direction.
    $latest = $count > 0 ? $swap[$count - 1][1] : null;
    if ($latest !== null && $latest <= 0) {
        return ['state' => 'clear', 'swap_rate' => 0.0, 'available_rate' => null,
                'samples' => $count, 'span_seconds' => $span];
    }

    // Same thresholds as the disk trend, for the same reason: below them any
    // answer is noise wearing a confident face.
    if ($count < 8 || $span < 1800 || count($available) < 8) {
        return $latest === null
            ? $unknown
            : ['state' => 'cold', 'swap_rate' => null, 'available_rate' => null,
               'samples' => $count, 'span_seconds' => $span];
    }

    $swapRate = seriesDirection($swap);
    $availableRate = seriesDirection($available);

    /*
     * A floor under "falling", because a direction is not yet a trend.
     *
     * Available memory wanders by tens of megabytes on any busy machine. Left
     * on sign alone, a drift of 27 MiB an hour across 7.5 GiB - four tenths of
     * one percent - counts as falling, and pairs with any swap growth to report
     * pressure that is not there. Half a percent of RAM an hour is the bar:
     * enough to exclude wander, small enough that anything genuinely running
     * out clears it easily.
     */
    $memTotal = (float)($rows[count($rows) - 1]['mem_total'] ?? 0);
    $floor = $memTotal > 0 ? $memTotal * 0.005 / 3600 : 0.0;

    $swapRising = $swapRate !== null && $swapRate['sustained'] && $swapRate['median'] > 0;
    $availableFalling = $availableRate !== null && $availableRate['sustained']
        && $availableRate['median'] < -$floor;

    $state = match (true) {
        $swapRising && $availableFalling => 'pressure',
        $swapRising => 'filling',
        default => 'cold',
    };

    return [
        'state' => $state,
        'swap_rate' => $swapRate['median'] ?? null,
        'available_rate' => $availableRate['median'] ?? null,
        'samples' => $count,
        'span_seconds' => $span,
    ];
}

/**
 * Which way a series is going, and whether it means it.
 *
 * `sustained` is the spike filter: both halves of the window must move the same
 * way as the whole. The median step is the size, because it is the one figure a
 * single outlier cannot drag.
 *
 * @param list<array{0:int,1:float}> $points
 * @return array{median: float, overall: float, sustained: bool}|null
 */
function seriesDirection(array $points): ?array
{
    $n = count($points);
    if ($n < 2) {
        return null;
    }

    $span = $points[$n - 1][0] - $points[0][0];
    if ($span <= 0) {
        return null;
    }

    $overall = ($points[$n - 1][1] - $points[0][1]) / $span;

    $steps = [];
    foreach (range(1, $n - 1) as $i) {
        $dt = $points[$i][0] - $points[$i - 1][0];
        if ($dt > 0) {
            $steps[] = ($points[$i][1] - $points[$i - 1][1]) / $dt;
        }
    }
    if (!$steps) {
        return null;
    }

    $middle = (int)floor($n / 2);
    $first = halfRate(array_slice($points, 0, $middle + 1));
    $second = halfRate(array_slice($points, $middle));

    $sustained = $first !== null && $second !== null
        && (($first > 0 && $second > 0 && $overall > 0)
            || ($first < 0 && $second < 0 && $overall < 0));

    return ['median' => median($steps), 'overall' => $overall, 'sustained' => $sustained];
}

/**
 * Adds watched files up without counting one inode twice.
 *
 * The rule everywhere else in this program too: a file's identity is
 * (device, inode), and a path is only a name for it. Summing the size column of
 * a list that happens to contain two names for one log reports disk that was
 * never allocated.
 *
 * @return array{bytes: int, files: int, paths: int, linked: int}
 */
function hostFileTotals(array $rows): array
{
    $seen = [];
    $bytes = 0;
    $files = 0;
    $linked = 0;

    foreach ($rows as $row) {
        /*
         * Rows written before the inode columns existed carry no identity, so
         * they can only be taken at face value. Better a possible overcount for
         * one transitional reading than a file silently dropped from the total.
         *
         * Counted in `files` as well as in `bytes`: deriving the two from
         * different denominators is how a summary line ends up reading "57 MiB
         * across 1 file" with three of them on screen.
         */
        if ((int)($row['inode'] ?? 0) === 0) {
            $bytes += (int)$row['bytes'];
            $files++;
            continue;
        }

        $key = $row['device'] . ':' . $row['inode'];
        if (isset($seen[$key])) {
            $linked++;
            continue;
        }

        $seen[$key] = true;
        $bytes += (int)$row['bytes'];
        $files++;
    }

    return [
        'bytes' => $bytes,
        'files' => $files,
        'paths' => count($rows),
        'linked' => $linked,
    ];
}

/* ------------------------------------------------------------- services -- */

/**
 * Each service now, a day ago and a week ago, with the change between.
 *
 * The question this whole table exists for. "Forgejo is using 1.8 GiB" cannot
 * be acted on: it might be a leak three days old or a working set that has been
 * 1.8 GiB since it was installed. "1.2 GiB last week, 1.5 GiB yesterday,
 * 1.8 GiB now" is the same fact with the answer attached.
 *
 * Compared on PSS rather than RSS. Summing RSS across a service made of a
 * hundred workers counts one shared copy of the runtime a hundred times, so the
 * total swings with the worker count rather than with the memory - a service
 * that forks ten more children looks like it grew by a gigabyte when nothing
 * was allocated at all.
 *
 * @return list<array>
 */
function hostServiceGrowth(PDO $pdo, int $now, int $limit = 20): array
{
    $latest = (int)$pdo->query('SELECT COALESCE(MAX(taken_at), 0) FROM host_services')->fetchColumn();
    if ($latest === 0) {
        return [];
    }

    $current = $pdo->prepare(
        'SELECT name, procs, threads, rss_bytes, pss_bytes, swap_bytes
         FROM host_services WHERE taken_at = ? ORDER BY pss_bytes DESC, rss_bytes DESC
         LIMIT :limit'
    );
    $current->bindValue(1, $latest, PDO::PARAM_INT);
    $current->bindValue(':limit', $limit, PDO::PARAM_INT);
    $current->execute();

    $rows = $current->fetchAll();
    if (!$rows) {
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        $day = hostServiceAt($pdo, $row['name'], $latest - 86400);
        $week = hostServiceAt($pdo, $row['name'], $latest - 604800);

        $out[] = [
            'name' => $row['name'],
            'procs' => (int)$row['procs'],
            'threads' => (int)$row['threads'],
            'rss_bytes' => (int)$row['rss_bytes'],
            'pss_bytes' => (int)$row['pss_bytes'],
            'swap_bytes' => (int)$row['swap_bytes'],
            'day_ago' => $day,
            'week_ago' => $week,
            // Null rather than zero when there is no reading to compare with.
            // A change of zero and no history at all are different answers, and
            // showing the second as the first is how a brand new install
            // reports every service as perfectly stable.
            'change_day' => $day === null ? null : (int)$row['pss_bytes'] - $day,
            'change_week' => $week === null ? null : (int)$row['pss_bytes'] - $week,
        ];
    }

    return $out;
}

/**
 * What a service was using around a given moment.
 *
 * The nearest reading at or before the target, but only if it is close enough
 * to mean it. A collector that was switched off for three days would otherwise
 * answer "a week ago" with a reading from three weeks back and report the
 * difference as growth.
 */
function hostServiceAt(PDO $pdo, string $name, int $target, int $tolerance = 43200): ?int
{
    $stmt = $pdo->prepare(
        'SELECT pss_bytes, taken_at FROM host_services
         WHERE name = ? AND taken_at <= ? ORDER BY taken_at DESC LIMIT 1'
    );
    $stmt->execute([$name, $target]);
    $row = $stmt->fetch();

    if (!$row || abs($target - (int)$row['taken_at']) > $tolerance) {
        return null;
    }

    return (int)$row['pss_bytes'];
}

/**
 * What each service may be charted on, and how the column behaves.
 *
 * The distinction that matters is level against counter. Memory is a level -
 * whatever it is right now - and plots directly. Bytes read and written are
 * counters that only ever climb, so plotted raw they produce a diagonal line
 * that says nothing; they have to be differentiated into a rate first, exactly
 * as the device figures are.
 */
function hostServiceMetrics(): array
{
    return [
        'pss' => ['column' => 'pss_bytes', 'counter' => false, 'label' => 'Proportional memory'],
        'rss' => ['column' => 'rss_bytes', 'counter' => false, 'label' => 'Resident memory'],
        'swap' => ['column' => 'swap_bytes', 'counter' => false, 'label' => 'Swapped'],
        'read' => ['column' => 'read_bytes', 'counter' => true, 'label' => 'Reading'],
        'write' => ['column' => 'write_bytes', 'counter' => true, 'label' => 'Writing'],
        'procs' => ['column' => 'procs', 'counter' => false, 'label' => 'Processes'],
        'threads' => ['column' => 'threads', 'counter' => false, 'label' => 'Threads'],
    ];
}

/**
 * One metric for several services over time, thinned to something a chart can
 * draw.
 *
 * Every nth reading rather than an average, for the reason the vitals series
 * gives: averaging is the operation that removes the spike you came to look at.
 *
 * @return list<array>
 */
function hostServiceSeries(
    PDO $pdo,
    array $names,
    int $from,
    int $to,
    string $metric = 'pss',
    int $limit = 400
): array {
    if (!$names) {
        return [];
    }

    $metrics = hostServiceMetrics();
    // The column name is interpolated into SQL, so it comes from this list and
    // never from the request - a caller asking for something unknown gets the
    // default rather than a chance to write the query.
    $spec = $metrics[$metric] ?? $metrics['pss'];
    $column = $spec['column'];

    $marks = implode(',', array_fill(0, count($names), '?'));

    $count = $pdo->prepare(
        "SELECT COUNT(DISTINCT taken_at) FROM host_services
         WHERE name IN ($marks) AND taken_at BETWEEN ? AND ?"
    );
    $count->execute([...$names, $from, $to]);
    $step = max(1, (int)ceil(((int)$count->fetchColumn()) / $limit));

    $stmt = $pdo->prepare(
        "SELECT taken_at, name, $column AS value FROM (
             SELECT taken_at, name, $column,
                    DENSE_RANK() OVER (ORDER BY taken_at) AS rn
             FROM host_services
             WHERE name IN ($marks) AND taken_at BETWEEN ? AND ?
         ) WHERE rn % ? = 0 ORDER BY taken_at"
    );
    $stmt->execute([...$names, $from, $to, $step]);

    $rows = $stmt->fetchAll();

    if ($spec['counter']) {
        $rows = hostDifferentiate($rows);
    }

    /*
     * Pivoted into one row per instant with a column per service, because that
     * is the shape a multi-series chart wants: a flat list would leave the
     * browser regrouping it, and a missing service at one instant would shift
     * every later point along by one.
     */
    $byTime = [];
    foreach ($rows as $row) {
        $at = (int)$row['taken_at'];
        $byTime[$at]['taken_at'] = $at;
        if ($row['value'] !== null) {
            $byTime[$at][$row['name']] = $row['value'];
        }
    }

    ksort($byTime);
    return array_values($byTime);
}

/**
 * Turns a cumulative counter into a per-second rate, per service.
 *
 * The first reading of each service has nothing to subtract from and becomes a
 * gap rather than a zero. A counter that went backwards means the service
 * restarted and began counting again - also a gap, because there is no rate
 * across that boundary and the difference would be a large negative number
 * against the one service somebody had just touched.
 *
 * @return list<array>
 */
function hostDifferentiate(array $rows): array
{
    $previous = [];
    $out = [];

    foreach ($rows as $row) {
        $name = $row['name'];
        $at = (int)$row['taken_at'];
        $value = (int)$row['value'];

        $rate = null;
        if (isset($previous[$name])) {
            [$wasAt, $wasValue] = $previous[$name];
            $seconds = $at - $wasAt;
            if ($seconds > 0 && $value >= $wasValue) {
                $rate = round(($value - $wasValue) / $seconds, 2);
            }
        }

        $previous[$name] = [$at, $value];
        $out[] = ['taken_at' => $at, 'name' => $name, 'value' => $rate];
    }

    return $out;
}

/* ------------------------------------------------------------- disk i/o -- */

/**
 * Each device's throughput and how busy it was, between the last two readings.
 *
 * Rates are worked out here rather than stored, because a rate is a difference
 * between two moments and the interval between readings is not fixed - a
 * collector that missed a run should report the honest average over the gap,
 * not a figure computed against an interval that never happened.
 *
 * @return list<array>
 */
function hostDiskRates(PDO $pdo): array
{
    $samples = $pdo->query(
        'SELECT id, taken_at FROM host_samples ORDER BY id DESC LIMIT 2'
    )->fetchAll();

    if (count($samples) < 2) {
        return [];
    }

    [$now, $before] = $samples;
    $seconds = (int)$now['taken_at'] - (int)$before['taken_at'];
    if ($seconds <= 0) {
        return [];
    }

    $rows = $pdo->prepare('SELECT * FROM host_disks WHERE sample_id = ?');
    $rows->execute([$now['id']]);
    $current = $rows->fetchAll();

    $rows->execute([$before['id']]);
    $previous = [];
    foreach ($rows->fetchAll() as $row) {
        $previous[$row['name']] = $row;
    }

    $out = [];
    foreach ($current as $row) {
        $was = $previous[$row['name']] ?? null;
        $readRate = $writeRate = $util = $queue = null;

        if ($was) {
            $readDelta = (int)$row['read_bytes'] - (int)$was['read_bytes'];
            $writeDelta = (int)$row['write_bytes'] - (int)$was['write_bytes'];
            $busyDelta = (int)$row['io_ms'] - (int)$was['io_ms'];
            $queueDelta = (int)$row['queue_ms'] - (int)$was['queue_ms'];

            // Negative means the machine rebooted and the counters restarted.
            // No rate exists across that boundary; an unknown is the honest
            // answer, and dividing anyway would report a spectacular negative.
            if ($readDelta >= 0 && $writeDelta >= 0 && $busyDelta >= 0) {
                $readRate = round($readDelta / $seconds, 2);
                $writeRate = round($writeDelta / $seconds, 2);
                // io_ms is milliseconds busy; over $seconds of wall clock that
                // is a percentage once divided by ten.
                $util = round(min(100.0, $busyDelta / ($seconds * 10)), 2);
                // Average depth of the request queue: total queued milliseconds
                // over elapsed milliseconds. Above one means requests are
                // waiting behind each other rather than being served at once.
                $queue = round($queueDelta / ($seconds * 1000), 2);
            }
        }

        $out[] = [
            'name' => $row['name'],
            'stacked' => (bool)$row['stacked'],
            'read_rate' => $readRate,
            'write_rate' => $writeRate,
            'util_percent' => $util,
            'queue_depth' => $queue,
            'in_flight' => (int)$row['in_flight'],
            'seconds' => $seconds,
        ];
    }

    usort($out, fn(array $a, array $b): int =>
        (($b['read_rate'] ?? 0) + ($b['write_rate'] ?? 0))
        <=> (($a['read_rate'] ?? 0) + ($a['write_rate'] ?? 0)));

    return $out;
}

/**
 * Read and write rates per service, between the last two service readings.
 *
 * A service's counters restart when it does, so a negative difference is a
 * restart rather than a fall and reports as unknown. That matters more here
 * than for devices: daemons are restarted routinely, and treating the reset as
 * a large negative rate would put a nonsense figure against the one service
 * somebody had just touched.
 *
 * @return array<string, array{read: ?float, write: ?float}>
 */
function hostServiceIoRates(PDO $pdo): array
{
    $instants = $pdo->query(
        'SELECT DISTINCT taken_at FROM host_services ORDER BY taken_at DESC LIMIT 2'
    )->fetchAll(PDO::FETCH_COLUMN);

    if (count($instants) < 2) {
        return [];
    }

    $seconds = (int)$instants[0] - (int)$instants[1];
    if ($seconds <= 0) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT name, read_bytes, write_bytes FROM host_services WHERE taken_at = ?'
    );
    $stmt->execute([$instants[0]]);
    $current = $stmt->fetchAll();

    $stmt->execute([$instants[1]]);
    $previous = [];
    foreach ($stmt->fetchAll() as $row) {
        $previous[$row['name']] = $row;
    }

    $out = [];
    foreach ($current as $row) {
        $was = $previous[$row['name']] ?? null;
        if (!$was) {
            $out[$row['name']] = ['read' => null, 'write' => null];
            continue;
        }

        $readDelta = (int)$row['read_bytes'] - (int)$was['read_bytes'];
        $writeDelta = (int)$row['write_bytes'] - (int)$was['write_bytes'];

        $out[$row['name']] = ($readDelta >= 0 && $writeDelta >= 0)
            ? ['read' => round($readDelta / $seconds, 2), 'write' => round($writeDelta / $seconds, 2)]
            : ['read' => null, 'write' => null];
    }

    return $out;
}

/**
 * Which processes are actually touching a disk, and how hard.
 *
 * The whole-host throughput chart says the machine is writing 40 MB/s; the
 * per-service one says MariaDB is. Neither names a pid, and for the cases this
 * exists to catch - eleven webalizer processes, a JetBackup run, a Forgejo
 * package import - the pid is what you need to go and look at, kill, or renice.
 *
 * Two kinds of rate, because processes come and go between samples and only
 * reporting the one that needs two sightings would leave every short-lived job
 * blank - which is most of the interesting ones:
 *
 *   "current"  the process was in both samples, so the counters can be
 *              subtracted and divided by the wall clock between them. This is
 *              a real instantaneous rate and it is what you want.
 *
 *   "average"  the process has only been seen once, so the rate is its total
 *              I/O over its own lifetime. A backup that started thirty seconds
 *              ago and has written 2 GB reports 66 MB/s, which is true and
 *              useful; a daemon up for nine days reports a lifetime average
 *              that is nearly meaningless. Both are labelled, and the caller
 *              is told which it is looking at rather than being left to guess.
 *
 * Matching is on pid *and* command. Pids are recycled, and a recycled one would
 * otherwise subtract a fresh process's counters from a dead one's and report a
 * spectacular negative - or worse, a plausible positive.
 *
 * @return array{available: bool, reason: ?string, seconds: int, rows: list<array>}
 */
function hostProcessIoRates(PDO $pdo): array
{
    $samples = $pdo->query(
        'SELECT id, taken_at FROM host_samples ORDER BY id DESC LIMIT 2'
    )->fetchAll();

    if ($samples === []) {
        return ['available' => false, 'reason' => 'The host collector has not run yet.',
                'seconds' => 0, 'rows' => []];
    }

    $now = $samples[0];
    $before = $samples[1] ?? null;
    $seconds = $before ? (int)$now['taken_at'] - (int)$before['taken_at'] : 0;

    $stmt = $pdo->prepare(
        'SELECT pid, user, name, command, rss_bytes, elapsed_seconds, read_bytes, write_bytes
         FROM host_processes WHERE sample_id = ?'
    );
    $stmt->execute([$now['id']]);
    $current = $stmt->fetchAll();

    if ($current === []) {
        return ['available' => false, 'reason' => 'No processes were recorded in the last reading.',
                'seconds' => $seconds, 'rows' => []];
    }

    /*
     * Whether the collector could read the counters at all.
     *
     * All-null means it ran without root: /proc/<pid>/io is readable by the
     * process's owner and by root, and by nobody else. That is a different
     * situation from "nothing was doing any I/O" and gets a different message.
     */
    $readable = array_filter($current, fn(array $r): bool => $r['read_bytes'] !== null);
    if ($readable === []) {
        return [
            'available' => false,
            'reason' => 'Per-process disk I/O was not readable. /proc/<pid>/io is readable '
                . 'only by root and by each process\'s own owner, so the collector needs to '
                . 'run as root to see this for the whole machine.',
            'seconds' => $seconds,
            'rows' => [],
        ];
    }

    $previous = [];
    if ($before) {
        $stmt->execute([$before['id']]);
        foreach ($stmt->fetchAll() as $row) {
            $previous[$row['pid'] . "\x01" . $row['command']] = $row;
        }
    }

    $out = [];
    foreach ($readable as $row) {
        $read = (int)$row['read_bytes'];
        $write = (int)$row['write_bytes'];
        $was = $previous[$row['pid'] . "\x01" . $row['command']] ?? null;

        $readRate = $writeRate = null;
        $basis = 'unknown';

        if ($was && $seconds > 0 && $was['read_bytes'] !== null) {
            $readDelta = $read - (int)$was['read_bytes'];
            $writeDelta = $write - (int)$was['write_bytes'];

            // Backwards means this is not the process we think it is, whatever
            // the pid and command say. No rate exists across that.
            if ($readDelta >= 0 && $writeDelta >= 0) {
                $readRate = round($readDelta / $seconds, 2);
                $writeRate = round($writeDelta / $seconds, 2);
                $basis = 'current';
            }
        }

        if ($basis === 'unknown') {
            $life = (int)($row['elapsed_seconds'] ?? 0);
            if ($life > 0) {
                $readRate = round($read / $life, 2);
                $writeRate = round($write / $life, 2);
                $basis = 'average';
            }
        }

        $out[] = [
            'pid' => (int)$row['pid'],
            'user' => (string)$row['user'],
            'name' => (string)$row['name'],
            'command' => (string)$row['command'],
            'rss_bytes' => (int)$row['rss_bytes'],
            'elapsed_seconds' => $row['elapsed_seconds'] === null ? null : (int)$row['elapsed_seconds'],
            'read_bytes' => $read,
            'write_bytes' => $write,
            'read_rate' => $readRate,
            'write_rate' => $writeRate,
            // 'current' from two sightings, 'average' over the process's life.
            'basis' => $basis,
        ];
    }

    /*
     * Ranked by rate where there is one, and by total where there is not.
     *
     * Sorting a mixed list on rate alone would bury a process that has written
     * 40 GB since it started under one averaging a few kilobytes a second, and
     * the 40 GB is the thing worth seeing.
     */
    usort($out, function (array $a, array $b): int {
        $rate = fn(array $r): float => (float)($r['read_rate'] ?? 0) + (float)($r['write_rate'] ?? 0);
        $total = fn(array $r): int => $r['read_bytes'] + $r['write_bytes'];
        return [$rate($b), $total($b)] <=> [$rate($a), $total($a)];
    });

    return [
        'available' => true,
        'reason' => null,
        'seconds' => $seconds,
        'rows' => array_slice($out, 0, HOST_KEEP_IO_PROCS),
    ];
}

/**
 * Total disk throughput on the host, over time.
 *
 * The table beside this chart says what the disks are doing now, which answers
 * "is it busy" and not "when did it start" or "is this every night at half past
 * two". A backup that saturates the array for forty minutes is invisible in a
 * snapshot taken at any other moment, and it is the shape over time that tells
 * a runaway from a scheduled job.
 *
 * Totalled across devices, with two rules that are easy to get wrong:
 *
 * Stacked devices are excluded, as everywhere else. A write to dm-0 is remapped
 * onto the disk underneath and advances both counters, so adding them reports
 * twice the traffic that happened.
 *
 * Utilisation is the busiest single device, never the sum. Two disks each 60%
 * busy is not a machine at 120% - it is a machine with a disk at 60%, twice
 * over. Summing a percentage that is already a proportion of wall-clock time
 * produces a number that means nothing and looks alarming.
 *
 * @return list<array{taken_at:int, read_rate:?float, write_rate:?float,
 *                    util_percent:?float, queue_depth:?float}>
 */
function hostDiskSeries(PDO $pdo, int $from, int $to): array
{
    /*
     * One sample before the window, deliberately.
     *
     * These are cumulative counters, so the first point inside the range has
     * nothing to subtract from and would be a gap - leaving every chart to
     * begin with a hole that moves as the range changes.
     */
    $start = $pdo->prepare('SELECT MAX(id) FROM host_samples WHERE taken_at < ?');
    $start->execute([$from]);
    $startId = (int)$start->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT s.id, s.taken_at, d.name, d.stacked, d.read_bytes, d.write_bytes,
                d.io_ms, d.queue_ms
         FROM host_disks d
         JOIN host_samples s ON s.id = d.sample_id
         WHERE s.id >= ? AND s.taken_at <= ?
           AND d.stacked = 0
         ORDER BY s.id'
    );
    $stmt->execute([$startId, $to]);

    $samples = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int)$row['id'];
        $samples[$id]['taken_at'] = (int)$row['taken_at'];
        $samples[$id]['disks'][(string)$row['name']] = $row;
    }

    $out = [];
    $previous = null;

    foreach ($samples as $sample) {
        if ($previous === null) {
            $previous = $sample;
            continue;
        }

        $seconds = $sample['taken_at'] - $previous['taken_at'];
        if ($seconds <= 0) {
            $previous = $sample;
            continue;
        }

        $read = 0;
        $write = 0;
        $util = null;
        $queue = null;
        $reset = false;
        $matched = 0;

        foreach ($sample['disks'] as $name => $now) {
            $was = $previous['disks'][$name] ?? null;
            if ($was === null) {
                // A device that was not in the previous reading cannot be
                // differentiated. Skipping it understates this one point,
                // which is better than inventing its whole counter as traffic.
                continue;
            }

            $readDelta = (int)$now['read_bytes'] - (int)$was['read_bytes'];
            $writeDelta = (int)$now['write_bytes'] - (int)$was['write_bytes'];
            $busyDelta = (int)$now['io_ms'] - (int)$was['io_ms'];

            /*
             * Backwards means the machine rebooted and the counters restarted.
             *
             * The whole point is dropped rather than that one device, because a
             * reboot resets all of them at once - reporting the others as if
             * they were fine would put a plausible number on an interval that
             * did not happen.
             */
            if ($readDelta < 0 || $writeDelta < 0 || $busyDelta < 0) {
                $reset = true;
                break;
            }

            $matched++;
            $read += $readDelta;
            $write += $writeDelta;

            $deviceUtil = min(100.0, $busyDelta / ($seconds * 10));
            $util = $util === null ? $deviceUtil : max($util, $deviceUtil);

            $queueDelta = (int)$now['queue_ms'] - (int)$was['queue_ms'];
            if ($queueDelta >= 0) {
                $deviceQueue = $queueDelta / ($seconds * 1000);
                $queue = $queue === null ? $deviceQueue : max($queue, $deviceQueue);
            }
        }

        $usable = !$reset && $matched > 0;

        $out[] = [
            'taken_at' => $sample['taken_at'],
            'read_rate' => $usable ? round($read / $seconds, 2) : null,
            'write_rate' => $usable ? round($write / $seconds, 2) : null,
            // The busiest device, not the sum. See the note above.
            'util_percent' => $usable && $util !== null ? round($util, 2) : null,
            'queue_depth' => $usable && $queue !== null ? round($queue, 2) : null,
        ];

        $previous = $sample;
    }

    return $out;
}
