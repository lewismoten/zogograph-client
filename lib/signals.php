<?php
declare(strict_types=1);

/*
 * Stopping processes.
 *
 * Deleting a file is undoable-in-principle from a backup. This is not: a
 * process killed mid-write leaves whatever it was writing half written, and
 * SIGKILL does not even give it the chance to notice. So this file is written
 * the same way lib/actions.php is - a list of things that must all be true, and
 * a log entry for every attempt including the refusals.
 *
 * The danger here is different from the one there, though, and worth naming.
 * A path is a stable identifier: /home/x/big.log means the same file in a
 * minute's time. **A pid is a recycled number.** The dashboard shows one it read
 * some seconds ago; between the reading and the click that process can exit and
 * the kernel can hand its number to something else. Signalling a pid because of
 * what a stale table says is sitting on it is how a monitoring tool kills a
 * database. Every guard below exists, but that one is the reason this is a
 * separate file with its own tests rather than three lines in the API.
 *
 * Two defences against it:
 *
 *   - Stopping *one* process carries what the caller believed it was - the name
 *     and how long it had been running - and the server re-reads the process
 *     and refuses if either has changed. A recycled pid always fails the age
 *     test, because the new occupant is younger than the reading.
 *
 *   - Stopping a *group* sends no pids at all. The caller names the group; the
 *     server resolves it to pids at the moment it signals them. There is no
 *     window to go stale in.
 *
 * And three things are never signalled, whatever is asked:
 *
 *   - pid 1, which is the machine
 *   - anything in this process's own ancestry, which is the web server, the
 *     shell above it and everything in between: killing one of those means the
 *     request answering the click dies with it
 *   - anything owned by another user, and anything matching the protect list
 */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/processes.php';
// For humanDuration, used in the refusal messages. Required explicitly rather
// than relied on: it only happened to be loaded because api.php gets to
// alerts.php first, which is not a dependency, it is a coincidence.
require_once __DIR__ . '/alerts.php';

/** Ask nicely, and do not ask at all. */
const SIGNAL_TERM = 15;
const SIGNAL_KILL = 9;

/** How long to wait for a signalled process to actually go away. */
const SIGNAL_SETTLE_TRIES = 8;
const SIGNAL_SETTLE_USLEEP = 200000;

/** Most processes one group stop will signal before it refuses. */
const SIGNAL_GROUP_MAX = 40;

/**
 * Names never signalled, regardless of who asks.
 *
 * The account's own plumbing. Killing `sshd` from a web page locks you out of
 * the machine you are trying to fix, and killing the shell you are sitting in
 * is a support ticket. Everything here is overridable in the config, because
 * the one process somebody genuinely needs to stop is always the one somebody
 * else thought was infrastructure.
 */
const PROTECTED_NAMES_DEFAULT = [
    'init', 'systemd', 'launchd', 'kthreadd',
    'sshd', 'bash', 'sh', 'zsh', 'dash', 'login', 'tmux', 'screen',
    'cron', 'crond', 'cpsrvd', 'cpanel',
    'httpd', 'nginx', 'apache2', 'php-fpm', 'lsphp', 'litespeed',
    'mysqld', 'mariadbd', 'exim', 'dovecot', 'named',
];

/**
 * Whether this host will let PHP signal anything at all, and why not.
 *
 * Asked before the buttons are drawn. A dashboard offering a Stop button that
 * cannot possibly work is worse than one that explains why it is missing.
 *
 * @return array{available: bool, reason: ?string, how: ?string}
 */
function signalsAvailable(array $config): array
{
    if (!($config['processes']['may_stop'] ?? true)) {
        return [
            'available' => false,
            'reason' => 'Stopping processes is turned off in the config.',
            'how' => null,
        ];
    }
    if (function_exists('posix_kill')) {
        return ['available' => true, 'reason' => null, 'how' => 'posix'];
    }
    if (shellRead('echo ok') !== null) {
        return ['available' => true, 'reason' => null, 'how' => 'shell'];
    }

    return [
        'available' => false,
        'reason' => 'This host allows neither the posix extension nor shell commands from PHP.',
        'how' => null,
    ];
}

/**
 * What a pid is right now, read fresh.
 *
 * Deliberately not taken from the listing the browser was shown. That listing
 * is the thing being checked.
 *
 * @return array{pid:int, ppid:int, user:string, elapsed:?int, state:string, name:string, command:string}|null
 */
function processFacts(int $pid): ?array
{
    if ($pid < 1) {
        return null;
    }

    // `state` is spelled the same on procps and BSD, unlike most of `ps`.
    $out = shellRead(sprintf('ps -p %d -o pid=,ppid=,user=,etime=,state=,args= 2>/dev/null', $pid));
    if ($out === null || trim($out) === '') {
        return null;
    }

    $line = trim((string)strtok(trim($out), "\n"));
    $parts = preg_split('/\s+/', $line, 6);
    if (!$parts || count($parts) < 6) {
        return null;
    }

    [$got, $ppid, $user, $etime, $state, $args] = $parts;
    if ((int)$got !== $pid) {
        return null;
    }

    return [
        'pid' => $pid,
        'ppid' => (int)$ppid,
        'user' => $user,
        'elapsed' => parseElapsed($etime),
        'state' => $state,
        'name' => processName($args),
        'command' => redactSecrets($args),
    ];
}

/**
 * This process, its parent, its parent's parent, all the way up.
 *
 * The web server is in here, and so is whatever started it. None of them may be
 * signalled: the request handling the click is running inside them, and killing
 * one takes down the answer along with the thing being answered. Walking the
 * chain rather than listing names catches it whatever the server is called.
 *
 * @return array<int, true>
 */
function ownAncestry(): array
{
    $chain = [];
    $pid = getmypid();
    if ($pid === false) {
        return $chain;
    }

    $hops = 0;
    while ($pid > 1 && $hops++ < 40) {
        $chain[$pid] = true;
        $facts = processFacts($pid);
        if ($facts === null || $facts['ppid'] < 1 || isset($chain[$facts['ppid']])) {
            break;
        }
        $pid = $facts['ppid'];
    }

    return $chain;
}

/** The user PHP is running as, for refusing other people's processes politely. */
function currentUser(): ?string
{
    if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
        $info = @posix_getpwuid(posix_geteuid());
        if (is_array($info) && isset($info['name'])) {
            return (string)$info['name'];
        }
    }

    $me = getmypid();
    $facts = $me === false ? null : processFacts($me);
    return $facts['user'] ?? null;
}

/**
 * Everything that must be true before a pid is signalled.
 *
 * Throws ActionRefused with a reason written for the person who will read it.
 * The order runs cheapest and most absolute first, so that "you may not signal
 * pid 1" never depends on being able to read a process table.
 */
function guardSignal(array $config, array $facts, array $ancestry, ?string $me): void
{
    // Only pid, name and user are read, so a partial row from a process listing
    // is as good as a full one from processFacts().
    $pid = (int)$facts['pid'];

    if ($pid <= 1) {
        throw new ActionRefused('pid 1 is the machine itself.');
    }
    if (isset($ancestry[$pid])) {
        throw new ActionRefused(
            'that process is this dashboard, or something it is running inside. '
            . 'Stopping it would kill the request answering you.'
        );
    }

    $protected = $config['processes']['protect'] ?? PROTECTED_NAMES_DEFAULT;
    // The bare executable, so that `php cron.php` is matched by `php` only if
    // somebody actually protected `php` - and `sshd: user@pts/0` by `sshd`.
    $bare = strtolower((string)preg_split('/[\s:]/', $facts['name'])[0]);
    foreach ($protected as $name) {
        if ($bare === strtolower(trim((string)$name))) {
            throw new ActionRefused(
                sprintf('“%s” is on the protected list in the config.', $facts['name'])
            );
        }
    }

    if ($me !== null && $facts['user'] !== $me) {
        throw new ActionRefused(
            sprintf('that process belongs to %s, and this runs as %s.', $facts['user'], $me)
        );
    }
}

/**
 * Confirms the pid still holds the process the caller was looking at.
 *
 * The recycling guard, and the only one that needs the caller to say what it
 * believed. A reused pid is a *different* process with a *younger* age, and
 * elapsed time cannot go backwards for a process that has not changed - so an
 * age lower than the one on screen is proof the number was handed on, even when
 * the name happens to match.
 */
function guardIdentity(array $facts, string $expectName, ?int $expectElapsed): void
{
    if ($expectName !== '' && $facts['name'] !== $expectName) {
        throw new ActionRefused(sprintf(
            'pid %d is now “%s”, not “%s”. The number was reused; refresh and look again.',
            $facts['pid'],
            $facts['name'],
            $expectName
        ));
    }

    if ($expectElapsed !== null && $facts['elapsed'] !== null && $facts['elapsed'] < $expectElapsed) {
        throw new ActionRefused(sprintf(
            'pid %d has only been running %s, less than the %s on screen. '
            . 'The number was reused; refresh and look again.',
            $facts['pid'],
            humanDuration($facts['elapsed']),
            humanDuration($expectElapsed)
        ));
    }
}

/** Sends the signal, by whichever route this host allows. */
function sendSignal(int $pid, int $signal): bool
{
    if ($pid < 2) {
        return false;
    }

    if (function_exists('posix_kill')) {
        return @posix_kill($pid, $signal);
    }

    // Both numbers are ours, not the caller's, but they are formatted as
    // integers regardless: this is a shell command line.
    return shellRead(sprintf('kill -%d %d 2>&1', $signal, $pid)) !== null;
}

/**
 * Whether a pid is still there, treating a zombie as gone.
 *
 * A process that has exited but not yet been reaped by its parent still has a
 * pid and still answers signal 0. It is dead - reporting "still running" for it
 * would send somebody looking for a SIGKILL they do not need.
 */
function stillRunning(int $pid): bool
{
    $facts = processFacts($pid);
    if ($facts === null) {
        return false;
    }
    return !str_starts_with(strtoupper($facts['state']), 'Z');
}

/** Waits a moment for a signalled process to go, because it is never instant. */
function settled(int $pid): bool
{
    for ($try = 0; $try < SIGNAL_SETTLE_TRIES; $try++) {
        if (!stillRunning($pid)) {
            return true;
        }
        usleep(SIGNAL_SETTLE_USLEEP);
    }
    return !stillRunning($pid);
}

/**
 * Stops one process.
 *
 * $expectName and $expectElapsed are what the caller had on screen. They are
 * not decoration: without them this function would signal whatever happens to
 * hold that number at the moment it runs.
 *
 * @return array{pid:int, name:string, signal:string, stopped:bool, note:string}
 */
function stopProcess(
    array $config,
    PDO $pdo,
    int $pid,
    string $expectName = '',
    ?int $expectElapsed = null,
    bool $force = false
): array {
    $signal = $force ? SIGNAL_KILL : SIGNAL_TERM;
    $label = $force ? 'force' : 'graceful';
    $target = $expectName !== '' ? sprintf('%s (pid %d)', $expectName, $pid) : "pid $pid";

    $ready = signalsAvailable($config);
    if (!$ready['available']) {
        logAction($pdo, 'stop_process', $target, 'refused', 0, 0, $ready['reason']);
        throw new ActionRefused((string)$ready['reason']);
    }

    try {
        $facts = processFacts($pid);
        if ($facts === null) {
            throw new ActionRefused('that process is not running any more.');
        }
        guardIdentity($facts, $expectName, $expectElapsed);
        guardSignal($config, $facts, ownAncestry(), currentUser());
    } catch (ActionRefused $e) {
        logAction($pdo, 'stop_process', $target, 'refused', 0, 0, $label . ': ' . $e->getMessage());
        throw $e;
    }

    if (!sendSignal($pid, $signal)) {
        logAction($pdo, 'stop_process', $target, 'failed', 0, 0, $label . ': signal rejected');
        throw new ActionRefused('the system refused the signal.');
    }

    $stopped = settled($pid);
    logAction(
        $pdo,
        'stop_process',
        $target,
        $stopped ? 'ok' : 'partial',
        0,
        $stopped ? 1 : 0,
        $label . ($stopped ? ': stopped' : ': signalled, still running')
    );

    return [
        'pid' => $pid,
        'name' => $facts['name'],
        'signal' => $label,
        'stopped' => $stopped,
        'note' => $stopped
            ? 'Stopped.'
            : ($force
                ? 'Signalled, but it is still there. It is probably stuck in the kernel — '
                    . 'a process blocked on disk or network IO cannot be killed until it unblocks.'
                : 'Asked it to exit, but it is still running. It may be finishing up, '
                    . 'or it may be ignoring the request — force it if it does not go.'),
    ];
}

/**
 * Stops every process sharing a name.
 *
 * Takes a name, never a list of pids, and resolves it here. That is the whole
 * design: pids from the browser are seconds old and a group of forty is forty
 * chances to signal the wrong one, whereas a name resolved at this instant
 * cannot be stale.
 *
 * Oldest first, because in a pool of workers the oldest is usually the one that
 * spawned the rest - stopping it first means the workers are not being replaced
 * while the rest of the loop is stopping them.
 *
 * @return array{name:string, signal:string, stopped:int, failed:int, refused:int,
 *               remaining:int, results:list<array>}
 */
function stopGroup(array $config, PDO $pdo, string $name, bool $force = false): array
{
    $signal = $force ? SIGNAL_KILL : SIGNAL_TERM;
    $label = $force ? 'force' : 'graceful';

    $ready = signalsAvailable($config);
    if (!$ready['available']) {
        logAction($pdo, 'stop_group', $name, 'refused', 0, 0, $ready['reason']);
        throw new ActionRefused((string)$ready['reason']);
    }
    if (trim($name) === '') {
        throw new ActionRefused('no process name was given.');
    }

    $snapshot = listProcesses('cpu', 5000, false);
    if (!$snapshot['available']) {
        throw new ActionRefused('the process list cannot be read here.');
    }

    $members = array_values(array_filter(
        $snapshot['rows'],
        fn(array $row): bool => $row['name'] === $name
    ));
    if (!$members) {
        logAction($pdo, 'stop_group', $name, 'refused', 0, 0, $label . ': nothing by that name');
        throw new ActionRefused(sprintf('nothing called “%s” is running any more.', $name));
    }

    $max = max(1, (int)($config['processes']['max_group'] ?? SIGNAL_GROUP_MAX));
    if (count($members) > $max) {
        logAction($pdo, 'stop_group', $name, 'refused', 0, 0, $label . ': group too large');
        throw new ActionRefused(sprintf(
            'that is %d processes and the limit is %d. Raise processes.max_group '
            . 'in the config if you mean it.',
            count($members),
            $max
        ));
    }

    usort(
        $members,
        fn(array $a, array $b): int => ($b['elapsed_seconds'] ?? 0) <=> ($a['elapsed_seconds'] ?? 0)
    );

    $ancestry = ownAncestry();
    $me = currentUser();
    $results = [];
    $stopped = $failed = $refused = 0;

    foreach ($members as $member) {
        $pid = (int)$member['pid'];

        try {
            // Re-read rather than trust the snapshot: signalling the earlier
            // members of a group takes time, and the list aged while it ran.
            $facts = processFacts($pid);
            if ($facts === null) {
                $results[] = ['pid' => $pid, 'outcome' => 'gone', 'note' => 'Already exited.'];
                $stopped++;
                continue;
            }
            guardIdentity($facts, $name, $member['elapsed_seconds']);
            guardSignal($config, $facts, $ancestry, $me);
        } catch (ActionRefused $e) {
            $results[] = ['pid' => $pid, 'outcome' => 'refused', 'note' => $e->getMessage()];
            $refused++;
            continue;
        }

        if (!sendSignal($pid, $signal)) {
            $results[] = ['pid' => $pid, 'outcome' => 'failed', 'note' => 'The system refused the signal.'];
            $failed++;
            continue;
        }

        $results[] = ['pid' => $pid, 'outcome' => 'signalled', 'note' => ''];
    }

    /*
     * Counted after signalling them all, not one at a time.
     *
     * Waiting a second for each in turn would take a minute over a group of
     * forty, and would hold the whole group half-signalled while it did.
     */
    foreach ($results as &$result) {
        if ($result['outcome'] !== 'signalled') {
            continue;
        }
        if (settled($result['pid'])) {
            $result['outcome'] = 'stopped';
            $result['note'] = 'Stopped.';
            $stopped++;
        } else {
            $result['outcome'] = 'running';
            $result['note'] = $force ? 'Still there — probably stuck in IO.' : 'Ignored the request.';
        }
    }
    unset($result);

    $remaining = count(array_filter($results, fn(array $r): bool => $r['outcome'] === 'running'));

    logAction(
        $pdo,
        'stop_group',
        $name,
        $remaining === 0 && $failed === 0 && $refused === 0 ? 'ok' : 'partial',
        0,
        $stopped,
        sprintf(
            '%s: %d stopped, %d still running, %d refused, %d failed',
            $label,
            $stopped,
            $remaining,
            $refused,
            $failed
        )
    );

    return [
        'name' => $name,
        'signal' => $label,
        'stopped' => $stopped,
        'failed' => $failed,
        'refused' => $refused,
        'remaining' => $remaining,
        'results' => $results,
    ];
}

/**
 * Marks each row of a process listing with whether it could be stopped.
 *
 * So the buttons can be dead before they are pressed rather than after. Half
 * the rows of a typical listing belong to root or to the account's plumbing,
 * and a live-looking button that always answers with a refusal teaches people
 * to distrust the ones that work. The reason travels with the row and becomes
 * the tooltip, which turns "that did nothing" into "that belongs to root".
 *
 * Advisory only. Every one of these checks runs again, against a freshly read
 * process, at the moment anything is signalled - this is for drawing, not for
 * deciding.
 */
function annotateEligibility(array $config, array $rows): array
{
    $ancestry = ownAncestry();
    $me = currentUser();

    foreach ($rows as &$row) {
        try {
            guardSignal($config, $row, $ancestry, $me);
            $row['eligible'] = true;
            $row['reason'] = null;
        } catch (ActionRefused $e) {
            $row['eligible'] = false;
            $row['reason'] = $e->getMessage();
        }
    }
    unset($row);

    return $rows;
}

/* --------------------------------------------------------------- patterns -- */

/*
 * Stopping by what the command line says, which is `pkill -f`.
 *
 * The name of a process is often not specific enough to aim with. Twelve
 * `webalizer` processes are one name and one of them is the one chewing through
 * a particular backup log; the thing that tells them apart is the arguments.
 *
 * Two deliberate differences from pkill:
 *
 *   - **The pattern never reaches a shell.** It is matched here, in PHP,
 *     against a process list this code read itself. Passing a string from a
 *     browser into `pkill -f '...'` is a shell metacharacter away from being
 *     remote code execution, and no amount of quoting is worth betting the
 *     account on. It costs nothing: matching a few hundred lines against a
 *     regex is what PCRE is for.
 *
 *   - **It previews before it fires.** `pkill -9 -f` has taken down more
 *     servers than it has fixed, always the same way: the pattern was broader
 *     than the person thought and they found out afterwards. Here the match is
 *     a separate, harmless call - you see exactly what would be signalled, then
 *     say go.
 */

/** Too short to be aimed. `pkill -f e` is a way to end a session. */
const SIGNAL_PATTERN_MIN = 3;

/**
 * Compiles a caller's pattern, safely.
 *
 * Delimited with \x01 rather than the usual slash or tilde. A delimiter that
 * can appear in the pattern has to be escaped, escaping it correctly means
 * knowing whether the caller already did, and getting that wrong silently
 * changes what the pattern means - on a function that kills things. A byte that
 * cannot occur in a command line has no such problem.
 */
function compilePattern(string $pattern): string
{
    $pattern = trim($pattern);

    if (strlen($pattern) < SIGNAL_PATTERN_MIN) {
        throw new ActionRefused(sprintf(
            'that pattern is too short to aim with — %d characters at least.',
            SIGNAL_PATTERN_MIN
        ));
    }
    if (str_contains($pattern, "\x01") || str_contains($pattern, "\0")) {
        throw new ActionRefused('that pattern contains characters a command line cannot.');
    }

    $compiled = "\x01" . $pattern . "\x01";
    if (@preg_match($compiled, '') === false) {
        throw new ActionRefused('that is not a valid regular expression.');
    }

    return $compiled;
}

/**
 * Every running process whose full command line matches, and what would happen.
 *
 * The preview, and also the first half of the stop - the same function decides
 * both, so what you are shown cannot disagree with what gets signalled.
 *
 * Raw arguments are matched but never returned. The pattern has to be tested
 * against what `ps` actually says, and what `ps` actually says is sometimes a
 * password; the rows going back carry the redacted form only.
 *
 * @return array{available:bool, reason:?string, matched:list<array>, scanned:int}
 */
function matchProcesses(array $config, string $pattern): array
{
    $compiled = compilePattern($pattern);

    $out = shellRead('ps -eo pid,user,pcpu,pmem,rss,etime,args 2>/dev/null');
    if ($out === null) {
        return [
            'available' => false,
            'reason' => 'This host does not allow running ps from PHP.',
            'matched' => [],
            'scanned' => 0,
        ];
    }

    $lines = preg_split('/\R/', trim($out)) ?: [];
    array_shift($lines);

    $ancestry = ownAncestry();
    $me = currentUser();
    $matched = [];
    $scanned = 0;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = preg_split('/\s+/', $line, 7);
        if (!$parts || count($parts) < 7) {
            continue;
        }
        [$pid, $user, $cpu, $mem, $rss, $etime, $args] = $parts;
        if (!ctype_digit($pid)) {
            continue;
        }

        $scanned++;
        if (!preg_match($compiled, $args)) {
            continue;
        }

        $row = [
            'pid' => (int)$pid,
            'user' => $user,
            'name' => processName($args),
            'cpu_percent' => (float)$cpu,
            'mem_percent' => (float)$mem,
            'rss_bytes' => (int)$rss * 1024,
            'elapsed_seconds' => parseElapsed($etime),
            'command' => redactSecrets($args),
            'eligible' => true,
            'reason' => null,
        ];

        /*
         * Why a refusal is shown rather than filtered out.
         *
         * A pattern that matches sshd is a pattern about to be run again with
         * one more character on the end. Hiding the match would hide that.
         */
        try {
            guardSignal(
                $config,
                ['pid' => (int)$pid, 'user' => $user, 'name' => $row['name']],
                $ancestry,
                $me
            );
        } catch (ActionRefused $e) {
            $row['eligible'] = false;
            $row['reason'] = $e->getMessage();
        }

        $matched[] = $row;
    }

    usort(
        $matched,
        fn(array $a, array $b): int => ($b['elapsed_seconds'] ?? 0) <=> ($a['elapsed_seconds'] ?? 0)
    );

    return ['available' => true, 'reason' => null, 'matched' => $matched, 'scanned' => $scanned];
}

/**
 * Stops everything matching a command-line pattern.
 *
 * Re-matches rather than taking the pids the preview showed, for the reason the
 * top of this file exists: those pids are as old as the preview. Matching again
 * costs one `ps` and removes the whole class of mistake.
 *
 * @return array{pattern:string, signal:string, stopped:int, failed:int, refused:int,
 *               remaining:int, scanned:int, results:list<array>}
 */
function stopMatching(array $config, PDO $pdo, string $pattern, bool $force = false): array
{
    $signal = $force ? SIGNAL_KILL : SIGNAL_TERM;
    $label = $force ? 'force' : 'graceful';
    $target = 'pattern ' . $pattern;

    $ready = signalsAvailable($config);
    if (!$ready['available']) {
        logAction($pdo, 'stop_matching', $target, 'refused', 0, 0, $ready['reason']);
        throw new ActionRefused((string)$ready['reason']);
    }

    try {
        $found = matchProcesses($config, $pattern);
    } catch (ActionRefused $e) {
        logAction($pdo, 'stop_matching', $target, 'refused', 0, 0, $label . ': ' . $e->getMessage());
        throw $e;
    }

    if (!$found['available']) {
        throw new ActionRefused((string)$found['reason']);
    }

    $eligible = array_values(array_filter($found['matched'], fn(array $r): bool => $r['eligible']));
    $blocked = array_values(array_filter($found['matched'], fn(array $r): bool => !$r['eligible']));

    if (!$found['matched']) {
        logAction($pdo, 'stop_matching', $target, 'refused', 0, 0, $label . ': matched nothing');
        throw new ActionRefused('nothing running matches that pattern.');
    }

    $max = max(1, (int)($config['processes']['max_group'] ?? SIGNAL_GROUP_MAX));
    if (count($eligible) > $max) {
        logAction($pdo, 'stop_matching', $target, 'refused', 0, 0, $label . ': too many matches');
        throw new ActionRefused(sprintf(
            'that pattern matches %d processes and the limit is %d. Narrow it, '
            . 'or raise processes.max_group if you mean it.',
            count($eligible),
            $max
        ));
    }

    /*
     * A pattern that matches nearly everything is a mistake, not an instruction.
     *
     * Under the limit only because the machine is quiet: the same pattern at a
     * busy moment is the whole account. The proportion is what gives it away.
     */
    if ($found['scanned'] > 20 && count($found['matched']) > $found['scanned'] * 0.5) {
        logAction($pdo, 'stop_matching', $target, 'refused', 0, 0, $label . ': pattern too broad');
        throw new ActionRefused(sprintf(
            'that pattern matches %d of the %d processes running. That is almost '
            . 'everything — narrow it before running it.',
            count($found['matched']),
            $found['scanned']
        ));
    }

    $ancestry = ownAncestry();
    $me = currentUser();
    $results = [];
    $stopped = $failed = 0;
    $refused = count($blocked);

    foreach ($blocked as $row) {
        $results[] = [
            'pid' => $row['pid'],
            'name' => $row['name'],
            'outcome' => 'refused',
            'note' => (string)$row['reason'],
        ];
    }

    foreach ($eligible as $row) {
        $pid = $row['pid'];

        try {
            $facts = processFacts($pid);
            if ($facts === null) {
                $results[] = [
                    'pid' => $pid, 'name' => $row['name'],
                    'outcome' => 'gone', 'note' => 'Already exited.',
                ];
                $stopped++;
                continue;
            }
            guardIdentity($facts, $row['name'], $row['elapsed_seconds']);
            guardSignal($config, $facts, $ancestry, $me);
        } catch (ActionRefused $e) {
            $results[] = [
                'pid' => $pid, 'name' => $row['name'],
                'outcome' => 'refused', 'note' => $e->getMessage(),
            ];
            $refused++;
            continue;
        }

        if (!sendSignal($pid, $signal)) {
            $results[] = [
                'pid' => $pid, 'name' => $row['name'],
                'outcome' => 'failed', 'note' => 'The system refused the signal.',
            ];
            $failed++;
            continue;
        }

        $results[] = ['pid' => $pid, 'name' => $row['name'], 'outcome' => 'signalled', 'note' => ''];
    }

    foreach ($results as &$result) {
        if ($result['outcome'] !== 'signalled') {
            continue;
        }
        if (settled($result['pid'])) {
            $result['outcome'] = 'stopped';
            $result['note'] = 'Stopped.';
            $stopped++;
        } else {
            $result['outcome'] = 'running';
            $result['note'] = $force ? 'Still there — probably stuck in IO.' : 'Ignored the request.';
        }
    }
    unset($result);

    $remaining = count(array_filter($results, fn(array $r): bool => $r['outcome'] === 'running'));

    logAction(
        $pdo,
        'stop_matching',
        $target,
        $remaining === 0 && $failed === 0 && $refused === 0 ? 'ok' : 'partial',
        0,
        $stopped,
        sprintf(
            '%s: %d stopped, %d still running, %d refused, %d failed',
            $label,
            $stopped,
            $remaining,
            $refused,
            $failed
        )
    );

    return [
        'pattern' => $pattern,
        'signal' => $label,
        'stopped' => $stopped,
        'failed' => $failed,
        'refused' => $refused,
        'remaining' => $remaining,
        'scanned' => $found['scanned'],
        'results' => $results,
    ];
}
