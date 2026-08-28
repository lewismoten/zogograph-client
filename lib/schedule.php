<?php
declare(strict_types=1);

/*
 * Editing the crontab from a web page.
 *
 * A crontab line is a command the machine will run as you, on a timer. Letting
 * a browser put text into one is remote code execution with extra steps, so
 * this file never does. There is no field anywhere that becomes part of a
 * command.
 *
 * Instead: the schedule is chosen from a fixed list of presets, the PHP binary
 * is chosen from a list of binaries found on disk, and the command itself is
 * built here from constants. The only thing crossing from the browser is which
 * option was picked, and an option that is not on the list is refused.
 *
 * The other rule is that the rest of your crontab is none of our business. Our
 * lines live between two marker comments and everything outside them is copied
 * through untouched, in order, byte for byte.
 */

require_once __DIR__ . '/actions.php';
// For shellRead(), which knows how to give up when shell_exec is disabled.
require_once __DIR__ . '/collect.php';

const CRON_BEGIN = '# BEGIN diskmon — managed by the dashboard, edited between these markers';
const CRON_END = '# END diskmon';

/**
 * The schedules on offer, described as steps rather than as cron text.
 *
 * `minute_step` and `hour_step` are in minutes and hours. A step of 1 means
 * every one of them, so the field becomes `*`. A step equal to the whole cycle
 * (60 minutes, 24 hours) means it happens once per cycle, so the field is a
 * single number. Anything between is a list.
 *
 * Written out as a list, and not as `4/6`.
 *
 * The `start/step` form is a Vixie extension. It works in cronie, and it is
 * rejected by cPanel's cron editor and by several other panels and minimal
 * crons - so a schedule written that way installs cleanly over ssh and is
 * refused through the interface most people on shared hosting actually use.
 * `0,4,8,12,16,20` is understood by every cron ever written, and has the
 * further merit of being readable without knowing any syntax at all.
 */
function cronPresets(): array
{
    return [
        'vitals' => [
            'off' => ['label' => 'Off', 'minute_step' => null],
            'every-minute' => ['label' => 'Every minute', 'minute_step' => 1],
            'every-2m' => ['label' => 'Every 2 minutes', 'minute_step' => 2],
            'every-5m' => ['label' => 'Every 5 minutes', 'minute_step' => 5],
            'every-10m' => ['label' => 'Every 10 minutes', 'minute_step' => 10],
            'every-15m' => ['label' => 'Every 15 minutes', 'minute_step' => 15],
            'every-30m' => ['label' => 'Every 30 minutes', 'minute_step' => 30],
            'hourly' => ['label' => 'Hourly', 'minute_step' => 60, 'hour_step' => 1],
        ],
        'scan' => [
            'off' => ['label' => 'Off', 'minute_step' => null],
            'hourly' => ['label' => 'Every hour', 'minute_step' => 60, 'hour_step' => 1],
            'every-2h' => ['label' => 'Every 2 hours', 'minute_step' => 60, 'hour_step' => 2],
            'every-3h' => ['label' => 'Every 3 hours', 'minute_step' => 60, 'hour_step' => 3],
            'every-4h' => ['label' => 'Every 4 hours', 'minute_step' => 60, 'hour_step' => 4],
            'every-6h' => ['label' => 'Every 6 hours', 'minute_step' => 60, 'hour_step' => 6],
            'every-8h' => ['label' => 'Every 8 hours', 'minute_step' => 60, 'hour_step' => 8],
            'every-12h' => ['label' => 'Every 12 hours', 'minute_step' => 60, 'hour_step' => 12],
            'daily' => ['label' => 'Once a day', 'minute_step' => 60, 'hour_step' => 24],
        ],
    ];
}

/**
 * One cron field: every value, a single value, or the list a step produces.
 *
 * `$start` is where the cycle begins, so a four-hour step starting at 4 gives
 * 4, 8, 12, 16, 20 and then 0 - written back in order, because a sorted list is
 * what somebody reading their crontab later expects to see.
 *
 * Any start works, because the set repeats with the step: asking for every four
 * hours from 16 gives the same six hours as asking from 4, and both of them
 * contain the hour that was asked for. An earlier version clamped the start to
 * one turn of the cycle instead, which quietly turned "every 4 hours from 4am"
 * into a job that ran at 3am - the exact kind of silent rewrite this form is
 * supposed to prevent.
 */
function cronField(int $start, int $step, int $cycle): string
{
    if ($step <= 1) {
        return '*';
    }
    if ($step >= $cycle) {
        return (string)($start % $cycle);
    }

    $values = [];
    for ($k = 0; $k * $step < $cycle; $k++) {
        $values[] = ($start + $k * $step) % $cycle;
    }

    $values = array_values(array_unique($values));
    sort($values);

    return implode(',', $values);
}

/** The five-field expression a preset produces at a given minute and hour. */
function cronExpressionFor(array $preset, int $minute, int $hour): ?string
{
    $minuteStep = $preset['minute_step'] ?? null;
    if ($minuteStep === null) {
        return null;
    }

    $hourStep = $preset['hour_step'] ?? 1;

    return sprintf(
        '%s %s * * *',
        cronField($minute, (int)$minuteStep, 60),
        cronField($hour, (int)$hourStep, 24)
    );
}

/**
 * Whether the minute and hour fields do anything, given the chosen presets.
 *
 * Only whether, not how far. The full 0-59 and 0-23 are always accepted: a
 * start outside one turn of the cycle is not an error, it names the same set
 * from a different point, and the set always contains the time that was asked
 * for. Restricting the range was worse than useless - it rewrote the answer.
 *
 * @return array{minute_used: bool, hour_used: bool}
 */
function cronBounds(array $choices): array
{
    $presets = cronPresets();
    $used = ['minute_used' => false, 'hour_used' => false];

    foreach (['vitals', 'scan'] as $job) {
        $preset = $presets[$job][(string)($choices[$job] ?? 'off')] ?? null;
        $minuteStep = $preset['minute_step'] ?? null;
        if ($preset === null || $minuteStep === null) {
            continue;
        }
        if ($minuteStep > 1) {
            $used['minute_used'] = true;
        }
        if ((int)($preset['hour_step'] ?? 1) > 1) {
            $used['hour_used'] = true;
        }
    }

    return $used;
}

/**
 * The timezone cron will actually use.
 *
 * Not PHP's. Three different clocks are in play and people reasonably assume
 * they are one: the browser's, PHP's `date.timezone` (which sets what the
 * dashboard prints), and the system zone that the cron daemon reads times in.
 * On the machine this was written on PHP said UTC while the system said
 * America/New_York, so a crontab written for "2am" would fire four hours from
 * when the config implied.
 *
 * A `CRON_TZ` or `TZ` line in the crontab beats the system setting, so that is
 * checked too - it is the one thing that would make everything else wrong.
 *
 * @return array{name: ?string, abbrev: ?string, offset: ?int, source: string, cron_tz: ?string}
 */
function systemTimezone(string $crontab = ''): array
{
    $name = null;
    $source = 'unknown';

    // The symlink is the most useful: it names the zone, so the browser can
    // convert with its own database rather than guessing from an offset that
    // changes twice a year.
    $link = @readlink('/etc/localtime');
    if (is_string($link) && preg_match('#zoneinfo/(.+)$#', $link, $m)) {
        $name = $m[1];
        $source = '/etc/localtime';
    }

    if ($name === null && is_readable('/etc/timezone')) {
        $candidate = trim((string)@file_get_contents('/etc/timezone'));
        if ($candidate !== '') {
            $name = $candidate;
            $source = '/etc/timezone';
        }
    }

    if ($name === null) {
        $env = getenv('TZ');
        if (is_string($env) && $env !== '') {
            $name = $env;
            $source = 'TZ';
        }
    }

    $abbrev = null;
    $offset = null;
    $stamp = shellRead('date +%Z,%z 2>/dev/null');
    if ($stamp !== null && preg_match('/^([A-Za-z+\-0-9]+),([+\-]\d{4})/', trim($stamp), $m)) {
        $abbrev = $m[1];
        $sign = $m[2][0] === '-' ? -1 : 1;
        $offset = $sign * ((int)substr($m[2], 1, 2) * 3600 + (int)substr($m[2], 3, 2) * 60);
        if ($source === 'unknown') {
            $source = 'date';
        }
    }

    /*
     * A TZ set inside the crontab wins over all of it.
     *
     * cron reads `CRON_TZ=` (Vixie, cronie) or `TZ=` before the schedule lines
     * and interprets everything after it in that zone. Missing it would make
     * every time this dashboard shows quietly wrong.
     */
    $cronTz = null;
    foreach (preg_split('/\R/', $crontab) ?: [] as $line) {
        if (preg_match('/^\s*(?:CRON_TZ|TZ)\s*=\s*"?([A-Za-z0-9_+\-\/]+)"?/', $line, $m)) {
            $cronTz = $m[1];
        }
    }

    return [
        'name' => $cronTz ?? $name,
        'abbrev' => $abbrev,
        'offset' => $offset,
        'source' => $cronTz !== null ? 'CRON_TZ in the crontab' : $source,
        'cron_tz' => $cronTz,
    ];
}

/** The flags each job runs with. Constants, not input. */
function cronJobs(): array
{
    return [
        'vitals' => ['--vitals', '--alerts', '--quiet'],
        'scan' => ['--scan', '--alerts', '--prune', '--quiet'],
    ];
}

/**
 * PHP binaries that actually exist on this machine.
 *
 * Offered as a list rather than a text box for the obvious reason, and worth
 * offering at all because the binary the web server runs is very often not the
 * CLI one - `PHP_BINARY` under mod_php or FPM points at the wrong thing, and a
 * cron job that runs the wrong PHP fails in a way that is tedious to diagnose.
 */
function phpBinaries(): array
{
    $candidates = [
        PHP_BINDIR . '/php',
        '/usr/local/bin/php',
        '/usr/bin/php',
        '/opt/cpanel/ea-php82/root/usr/bin/php',
        '/opt/cpanel/ea-php83/root/usr/bin/php',
    ];

    if (PHP_SAPI === 'cli') {
        array_unshift($candidates, PHP_BINARY);
    }

    $found = [];
    foreach ($candidates as $path) {
        if (is_file($path) && is_executable($path) && !isset($found[$path])) {
            $found[$path] = true;
        }
    }

    return array_keys($found);
}

/**
 * Reads the current crontab.
 *
 * An empty crontab and no crontab command at all are different things, and the
 * caller has to be able to tell them apart: writing a fresh crontab over
 * somebody's existing one because `crontab -l` was unavailable would be the
 * worst bug in this project.
 *
 * @return array{available: bool, raw: string, reason: ?string}
 */
function readCrontab(): array
{
    if (!function_exists('shell_exec')) {
        return ['available' => false, 'raw' => '', 'reason' => 'shell_exec is disabled here.'];
    }
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled, true)) {
        return ['available' => false, 'raw' => '', 'reason' => 'shell_exec is disabled here.'];
    }

    $output = @shell_exec('crontab -l 2>/dev/null');
    if ($output === null) {
        // Either there is no crontab yet, or there is no crontab command. The
        // second is the dangerous one, so it is checked for separately.
        $which = trim((string)@shell_exec('command -v crontab 2>/dev/null'));
        if ($which === '') {
            return [
                'available' => false,
                'raw' => '',
                'reason' => 'No crontab command on this host. Add the lines through your control panel.',
            ];
        }
        return ['available' => true, 'raw' => '', 'reason' => null];
    }

    return ['available' => true, 'raw' => (string)$output, 'reason' => null];
}

/** The lines this dashboard manages, pulled out of a crontab. */
function managedBlock(string $crontab): array
{
    $lines = preg_split('/\R/', $crontab) ?: [];
    $inside = false;
    $ours = [];

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), CRON_BEGIN)) {
            $inside = true;
            continue;
        }
        if (trim($line) === CRON_END) {
            $inside = false;
            continue;
        }
        if ($inside && trim($line) !== '') {
            $ours[] = $line;
        }
    }

    return $ours;
}

/**
 * Puts a new managed block into a crontab, leaving everything else alone.
 *
 * Everything outside the markers is copied through in order. If there is no
 * block yet, ours goes on the end. If the new block is empty, the markers go
 * too, so removing the schedule leaves no litter behind.
 */
function replaceManagedBlock(string $crontab, array $ourLines): string
{
    $lines = preg_split('/\R/', rtrim($crontab, "\n")) ?: [];
    $kept = [];
    $inside = false;
    $sawBlock = false;

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), CRON_BEGIN)) {
            $inside = true;
            $sawBlock = true;
            continue;
        }
        if (trim($line) === CRON_END) {
            $inside = false;
            continue;
        }
        if (!$inside) {
            $kept[] = $line;
        }
    }

    // A crontab that was only ever our block leaves a run of blank lines.
    while ($kept && trim(end($kept)) === '') {
        array_pop($kept);
    }

    if ($ourLines) {
        if ($kept) {
            $kept[] = '';
        }
        $kept[] = CRON_BEGIN;
        foreach ($ourLines as $line) {
            $kept[] = $line;
        }
        $kept[] = CRON_END;
    }

    unset($sawBlock);

    return $kept ? implode("\n", $kept) . "\n" : '';
}

/**
 * Builds our crontab lines from choices, and refuses anything not on the list.
 *
 * This is the only place a schedule becomes text, and every part of that text
 * is either a constant or an integer that has been clamped. `$choices` is
 * whatever arrived in the request; nothing in it is trusted.
 */
function buildCronLines(array $choices, string $php, string $scriptDir): array
{
    $presets = cronPresets();
    $jobs = cronJobs();

    if (!in_array($php, phpBinaries(), true)) {
        throw new ActionRefused('that is not one of the PHP binaries found on this host');
    }

    $script = realpath($scriptDir . '/cron.php');
    if ($script === false) {
        throw new ActionRefused('cannot find cron.php');
    }

    // Clamped to real clock values and nothing narrower. cronField folds a
    // start into its own cycle, and the resulting set always includes it.
    $minute = max(0, min(59, (int)($choices['minute'] ?? 7)));
    $hour = max(0, min(23, (int)($choices['hour'] ?? 2)));

    $lines = [];
    foreach ($jobs as $job => $flags) {
        $pick = (string)($choices[$job] ?? 'off');
        if (!isset($presets[$job][$pick])) {
            throw new ActionRefused("unknown schedule for $job");
        }

        $expr = cronExpressionFor($presets[$job][$pick], $minute, $hour);
        if ($expr === null) {
            continue;
        }

        /*
         * escapeshellarg on both paths.
         *
         * They are already constrained - one is off a list of binaries that
         * exist, the other is realpath of a file next to this one - but a path
         * with a space in it would otherwise split into two arguments, and
         * quoting the thing that goes into a shell is not a step to skip on the
         * grounds that it is probably fine.
         */
        $lines[] = sprintf(
            '%s %s %s %s',
            $expr,
            escapeshellarg($php),
            escapeshellarg($script),
            implode(' ', $flags)
        );
    }

    return $lines;
}

/**
 * Starts a scan in the background and returns at once.
 *
 * Not run inside the request: a scan is minutes of disk work on a real account,
 * and PHP would hit max_execution_time long before it finished - leaving a
 * half-written scan row and a browser showing a timeout. So it is spawned
 * detached, with its output discarded, and the dashboard watches for the new
 * scan to appear the same way it watches for a cron-driven one.
 *
 * @return array{started: bool, reason: ?string}
 */
function startScan(string $scriptDir, string $php): array
{
    if (!in_array($php, phpBinaries(), true)) {
        throw new ActionRefused('that is not one of the PHP binaries found on this host');
    }

    $script = realpath($scriptDir . '/cron.php');
    if ($script === false) {
        throw new ActionRefused('cannot find cron.php');
    }

    if (shellRead('command -v nohup 2>/dev/null') === null && !function_exists('shell_exec')) {
        return ['started' => false, 'reason' => 'This host does not allow starting background work.'];
    }

    /*
     * Detached, with stdout and stderr thrown away.
     *
     * Without the redirect the shell waits for the pipes to close, which means
     * waiting for the scan - exactly what this is avoiding. Errors still reach
     * data/error.log, because cron.php logs them itself.
     */
    $command = sprintf(
        'nohup %s %s --scan --alerts --quiet > /dev/null 2>&1 &',
        escapeshellarg($php),
        escapeshellarg($script)
    );

    if (shellRead($command . ' echo started') === null) {
        // shell_exec returns null for empty output too, so the echo above is
        // what tells us the shell actually ran rather than being disabled.
        return ['started' => false, 'reason' => 'The command could not be started.'];
    }

    return ['started' => true, 'reason' => null];
}

/**
 * Installs a crontab.
 *
 * Written to a temporary file and loaded with `crontab <file>` rather than
 * piped, because a pipe means building a shell command containing the content,
 * and the content is the one thing that must never be inside a command string.
 */
function writeCrontab(string $content): void
{
    $temporary = tempnam(sys_get_temp_dir(), 'diskmon');
    if ($temporary === false) {
        throw new ActionRefused('could not create a temporary file');
    }

    try {
        if (@file_put_contents($temporary, $content) === false) {
            throw new ActionRefused('could not write the temporary crontab');
        }
        @chmod($temporary, 0600);

        $result = @shell_exec('crontab ' . escapeshellarg($temporary) . ' 2>&1');
        $left = trim((string)$result);
        if ($left !== '') {
            throw new ActionRefused('crontab refused it: ' . $left);
        }
    } finally {
        @unlink($temporary);
    }
}

/**
 * What the dashboard shows about the current schedule.
 *
 * The managed lines are handed back verbatim so somebody can see exactly what
 * is installed, rather than a rendering of what we think we installed.
 */
function scheduleState(string $scriptDir): array
{
    $crontab = readCrontab();

    return [
        'available' => $crontab['available'],
        'reason' => $crontab['reason'],
        'timezone' => systemTimezone($crontab['raw']),
        'php_timezone' => date_default_timezone_get(),
        'lines' => $crontab['available'] ? managedBlock($crontab['raw']) : [],
        'other_lines' => $crontab['available']
            ? max(0, count(array_filter(
                preg_split('/\R/', trim($crontab['raw'])) ?: [],
                fn(string $l): bool => trim($l) !== ''
            )) - count(managedBlock($crontab['raw'])) - 2)
            : 0,
        'binaries' => phpBinaries(),
        'presets' => cronPresets(),
        'script' => realpath($scriptDir . '/cron.php') ?: null,
    ];
}
