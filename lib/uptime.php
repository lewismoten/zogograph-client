<?php
declare(strict_types=1);

/*
 * Was it down, or were we not looking?
 *
 * Those are different facts and they are easy to confuse, because from the
 * database they look identical: a stretch of time with no readings in it. One
 * means the machine was off; the other means the collector was. Reporting the
 * second as the first would put a server outage on the chart every time a cron
 * job failed, and would make an uptime figure worthless - and worthless in the
 * direction that starts a needless investigation.
 *
 * The kernel's boot time tells them apart. It is a constant for as long as a
 * machine stays up, so:
 *
 *   - a gap with the *same* boot time either side means the machine ran right
 *     through it and the collector stopped. A monitoring gap.
 *   - a gap with a *later* boot time after it means the machine restarted
 *     inside it. A real outage.
 *
 * What that cannot do is say exactly how long the outage was. If the last
 * reading was at 02:00 and the machine came back up at 06:00, it was up for
 * some unknown part of those four hours before going down. The boot time is the
 * moment it *returned*, so the outage is at most that gap and probably less.
 * Everything here reports it as an upper bound and says so, because quietly
 * treating the bound as the measurement would overstate every outage by however
 * long the machine sat there working with nobody watching.
 */

/** A gap longer than this many times the usual interval is worth explaining. */
const UPTIME_GAP_FACTOR = 3.0;

/** Never treat anything under this as a gap, however short the interval. */
const UPTIME_MIN_GAP = 180;

/**
 * Reads a series of readings and works out where the holes are.
 *
 * @param list<array> $rows  Ordered by taken_at, each with taken_at and boot_at.
 * @return array{
 *   outages: list<array>, gaps: list<array>, boots: int,
 *   covered: int, window: int, expected_interval: int,
 *   downtime: int, unmonitored: int, uptime_percent: ?float, coverage_percent: ?float
 * }
 */
function analyseUptime(array $rows, int $from, int $to): array
{
    $window = max(0, $to - $from);
    $empty = [
        'outages' => [], 'gaps' => [], 'boots' => 0,
        'covered' => 0, 'window' => $window, 'expected_interval' => 0,
        'tracking_from' => null,
        'downtime' => 0, 'unmonitored' => 0,
        'uptime_percent' => null, 'coverage_percent' => null,
    ];

    $points = [];
    foreach ($rows as $row) {
        if (($row['taken_at'] ?? null) === null) {
            continue;
        }
        $points[] = [
            'at' => (int)$row['taken_at'],
            // Null where the reading predates boot-time collection. Those
            // stretches can be measured but not explained, and saying which is
            // better than guessing.
            'boot' => isset($row['boot_at']) && $row['boot_at'] !== null
                ? (int)$row['boot_at']
                : null,
        ];
    }

    if (count($points) < 2) {
        return $empty;
    }

    /*
     * The usual interval, taken as the median step rather than the mean.
     *
     * The mean is dragged upwards by exactly the gaps this function exists to
     * find, so using it would raise the threshold every time something went
     * wrong and hide the next one.
     */
    $steps = [];
    foreach (range(1, count($points) - 1) as $i) {
        $step = $points[$i]['at'] - $points[$i - 1]['at'];
        if ($step > 0) {
            $steps[] = $step;
        }
    }
    if (!$steps) {
        return $empty;
    }

    /*
     * When boot times started being recorded at all.
     *
     * Readings older than that column cannot be classified - not because
     * anything is wrong, but because the evidence did not exist yet. Saying so
     * is much better than "no boot time either side to compare", which in a
     * panel about outages reads as though the machine might have been off.
     */
    $trackingFrom = null;
    foreach ($points as $point) {
        if ($point['boot'] !== null) {
            $trackingFrom = $point['at'];
            break;
        }
    }

    sort($steps);
    $interval = (int)$steps[intdiv(count($steps), 2)];
    $threshold = max(UPTIME_MIN_GAP, (int)round($interval * UPTIME_GAP_FACTOR));

    $outages = [];
    $gaps = [];
    $boots = 0;
    $downtime = 0;
    $unmonitored = 0;

    foreach (range(1, count($points) - 1) as $i) {
        $before = $points[$i - 1];
        $after = $points[$i];
        $length = $after['at'] - $before['at'];

        $rebooted = $before['boot'] !== null
            && $after['boot'] !== null
            && $after['boot'] > $before['boot'];

        if ($rebooted) {
            $boots++;
        }

        if ($length <= $threshold && !$rebooted) {
            continue;
        }

        if ($rebooted) {
            /*
             * The machine came back at its new boot time, so the outage ended
             * there. It began somewhere between the last reading and that
             * moment - unknowable from here, which is why this is a bound.
             */
            $ended = $after['boot'];
            $bound = max(0, $ended - $before['at']);
            $downtime += $bound;
            $outages[] = [
                'kind' => 'restart',
                'from' => $before['at'],
                'to' => $ended,
                'seconds_at_most' => $bound,
                'booted_at' => $ended,
            ];
            continue;
        }

        // Same boot time either side: it ran through, and nobody was recording.
        if ($before['boot'] !== null && $after['boot'] !== null) {
            $unmonitored += $length;
            $gaps[] = [
                'kind' => 'not_recorded',
                'from' => $before['at'],
                'to' => $after['at'],
                'seconds' => $length,
            ];
            continue;
        }

        // No boot time to compare. Measurable, not explainable - and the
        // reason it cannot be explained is worth distinguishing.
        $unmonitored += $length;
        $gaps[] = [
            'kind' => $trackingFrom === null || $after['at'] <= $trackingFrom
                ? 'before_tracking'
                : 'unknown',
            'from' => $before['at'],
            'to' => $after['at'],
            'seconds' => $length,
        ];
    }

    $covered = max(0, $points[count($points) - 1]['at'] - $points[0]['at']);

    return [
        'outages' => $outages,
        'gaps' => $gaps,
        'boots' => $boots,
        'covered' => $covered,
        'window' => $window,
        'expected_interval' => $interval,
        'tracking_from' => $trackingFrom,
        'downtime' => $downtime,
        'unmonitored' => $unmonitored,
        /*
         * Two percentages, because they answer two questions.
         *
         * Uptime counts only time the machine is known to have been down, so a
         * fortnight of readings with one reboot in it is 99.9% however often the
         * collector stumbled. Coverage counts the holes we cannot account for,
         * which is a statement about the monitoring rather than the server.
         * Merging them into one number would let a broken cron job masquerade
         * as a fragile machine.
         */
        'uptime_percent' => $covered > 0
            ? round(max(0, 100 - ($downtime / $covered) * 100), 4)
            : null,
        'coverage_percent' => $covered > 0
            ? round(max(0, 100 - ($unmonitored / $covered) * 100), 4)
            : null,
    ];
}

/**
 * Boot and shutdown records from `last`, which only root can read.
 *
 * The point of it is *why* a machine restarted. A boot preceded by a shutdown
 * record was asked to go; a boot with no shutdown before it means the previous
 * session ended without one - a crash, a power cut, a hard reset, an OOM kill
 * of init. The difference matters enormously and nothing else on the machine
 * records it as plainly.
 *
 * Parsed from text rather than read from wtmp directly, because wtmp is a
 * binary struct whose layout differs between libc versions, and `last` is the
 * program whose job is knowing that.
 *
 * @return array{available: bool, reason: ?string, boots: list<array>}
 */
function bootHistory(?string $output = null): array
{
    if ($output === null) {
        /*
         * ISO timestamps first, because they carry their own offset.
         *
         * `last` prints in the *system's* timezone, and this program has
         * already set PHP's default to the one in the config. Where those
         * differ - a server running UTC, a config saying America/New_York -
         * strtotime reads "03:54" as if it were already Eastern and every boot
         * lands four hours late. The card at the top of the dashboard reads
         * btime, a true epoch, so it stayed right while this table drifted,
         * which is a nasty way to be wrong: two figures for the same reboot
         * that disagree, and no clue which to trust.
         *
         * `--time-format iso` gives 2026-08-24T03:54:16-04:00, and an explicit
         * offset makes the default timezone irrelevant.
         */
        $output = shellRead('last -x -F --time-format iso reboot shutdown 2>/dev/null');

        // Older util-linux has no --time-format and writes the usage message to
        // stderr, leaving nothing useful on stdout.
        if ($output === null || !preg_match('/\d{4}-\d{2}-\d{2}T/', $output)) {
            $output = shellRead('last -x -F reboot shutdown 2>/dev/null');
        }
    }

    if ($output === null || trim($output) === '') {
        return [
            'available' => false,
            'reason' => 'last is not available here, or wtmp is unreadable.',
            'boots' => [],
        ];
    }

    $events = [];
    foreach (preg_split('/\R/', $output) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, 'wtmp begins')) {
            continue;
        }

        if (!preg_match('/^(reboot|shutdown)\s+system\s+(boot|down)\s+(\S+)\s+(.*)$/', $line, $m)) {
            continue;
        }

        $kind = $m[1] === 'reboot' ? 'boot' : 'down';
        $kernel = $m[3];
        $rest = $m[4];

        /*
         * The first full date on the line is when it happened. What follows may
         * be "- <date>", "still running", "crash", or a duration in brackets,
         * none of which are needed to place the event.
         */
        $at = eventTime($rest);
        if ($at === null) {
            continue;
        }

        $events[] = [
            'kind' => $kind,
            'at' => $at,
            'kernel' => $kernel,
            'still_running' => str_contains($rest, 'still running'),
            'crash_noted' => str_contains($rest, 'crash'),
        ];
    }

    if (!$events) {
        return [
            'available' => false,
            'reason' => 'last produced no boot or shutdown records.',
            'boots' => [],
        ];
    }

    // Oldest first, so each boot can look behind it.
    usort($events, fn(array $a, array $b): int => $a['at'] <=> $b['at']);

    $boots = [];
    $lastDown = null;
    $oldest = $events[0]['at'];

    foreach ($events as $event) {
        if ($event['kind'] === 'down') {
            $lastDown = $event['at'];
            continue;
        }

        /*
         * Clean if a shutdown was recorded shortly before this boot.
         *
         * "Shortly" matters: a shutdown record from three weeks ago belongs to
         * an earlier session, and pairing it with this boot would call a crash
         * a planned restart. An hour is generous for a reboot and far short of
         * anything that could be a different session.
         */
        $clean = $lastDown !== null && $event['at'] - $lastDown <= 3600;

        $boots[] = [
            'booted_at' => $event['at'],
            'kernel' => $event['kernel'],
            'still_running' => $event['still_running'],
            // The oldest record has nothing before it, so its predecessor
            // cannot be judged - which is not the same as it having crashed.
            'ended' => $clean ? 'shutdown' : ($event['at'] === $oldest ? 'unknown' : 'unclean'),
            'shutdown_at' => $clean ? $lastDown : null,
        ];

        $lastDown = null;
    }

    // Newest first for display.
    usort($boots, fn(array $a, array $b): int => $b['booted_at'] <=> $a['booted_at']);

    return ['available' => true, 'reason' => null, 'boots' => $boots];
}

/**
 * The moment an event happened, from whichever format `last` used.
 *
 * ISO with an offset is taken as-is and needs no assumption about anybody's
 * timezone. The legacy format has no offset, so it is read in the timezone the
 * *system* is in - the one `last` printed it in - and not in whatever the
 * config asked the rest of this program to use.
 *
 * The weekday name in the legacy format is matched but deliberately discarded.
 * strtotime treats a leading weekday as an instruction rather than a label:
 * given "Tue Jul 01 2026", where the 1st is a Wednesday, it moves to the
 * following Tuesday and returns the 7th without complaint.
 */
function eventTime(string $rest): ?int
{
    if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:[+-]\d{2}:?\d{2}|Z))/', $rest, $m)) {
        $at = strtotime($m[1]);
        return $at === false ? null : $at;
    }

    if (!preg_match(
        '/^[A-Z][a-z]{2}\s+([A-Z][a-z]{2}\s+\d+\s+\d+:\d+:\d+\s+\d{4})/',
        $rest,
        $m
    )) {
        return null;
    }

    $was = date_default_timezone_get();
    $system = systemZone();
    if ($system !== null) {
        date_default_timezone_set($system);
    }

    $at = strtotime($m[1]);

    date_default_timezone_set($was);
    return $at === false ? null : $at;
}

/**
 * The timezone the machine itself is in, or null if it cannot be told.
 *
 * Read from the system rather than from the config, because the question here
 * is what `last` meant by the numbers it printed - not what the dashboard
 * would like to display them in.
 */
function systemZone(): ?string
{
    $link = @readlink('/etc/localtime');
    if (is_string($link) && preg_match('#zoneinfo/(.+)$#', $link, $m)) {
        return $m[1];
    }

    $out = shellRead('timedatectl show --property=Timezone --value 2>/dev/null');
    if (is_string($out) && trim($out) !== '') {
        return trim($out);
    }

    return null;
}

/**
 * How the previous session ended, in words.
 *
 * The wording is deliberately careful about what is actually known. "Crashed"
 * is a strong claim from the absence of a log record, and the absence has other
 * explanations - a wtmp that was rotated, a shutdown so abrupt the record never
 * reached the disk. "Stopped without recording a shutdown" is what the evidence
 * supports.
 */
function bootReason(string $ended): string
{
    return match ($ended) {
        'shutdown' => 'The previous session was shut down cleanly — asked to stop, and it did.',
        'unclean' => 'The previous session stopped without recording a shutdown: a crash, a '
            . 'power loss, a hard reset, or a host-level restart it was never told about.',
        default => 'Nothing recorded before this boot, so how the machine came to be off '
            . 'cannot be told from here.',
    };
}

/**
 * When a collector was actually reporting, as spans rather than points.
 *
 * A list of timestamps is not a track: the useful question is "was it running
 * between these two readings", and the answer depends on how often it is meant
 * to run. Two readings a minute apart on a one-minute collector is continuous
 * coverage; the same two on a five-minute collector still is; two an hour apart
 * on either is a hole.
 *
 * The threshold is the median step times a slack factor, measured rather than
 * configured - this account cannot read root's crontab, and on a shared box it
 * often cannot read its own either.
 *
 * @return array{
 *   spans: list<array{from: int, to: int}>,
 *   gaps: list<array{from: int, to: int}>,
 *   interval: ?int, first: ?int, last: ?int, readings: int
 * }
 */
function reportingTrack(array $times, int $from, int $to, float $slack = 2.5): array
{
    $times = array_values(array_unique(array_map('intval', $times)));
    sort($times);

    $empty = [
        'spans' => [], 'gaps' => [], 'interval' => null,
        'first' => null, 'last' => null, 'readings' => count($times),
    ];

    if (count($times) < 2) {
        /*
         * One reading is a point, not a track.
         *
         * Drawn as a moment rather than as a span, because turning it into one
         * would mean inventing a duration - and "it reported once" is a
         * different fact from "it reported for a while".
         */
        // array_merge, not +: the union operator keeps the LEFT operand's keys,
        // and $empty already has first and last set to null.
        return $times === []
            ? $empty
            : array_merge($empty, ['first' => $times[0], 'last' => $times[0]]);
    }

    $steps = [];
    for ($i = 1, $n = count($times); $i < $n; $i++) {
        $step = $times[$i] - $times[$i - 1];
        if ($step > 0) {
            $steps[] = $step;
        }
    }

    if ($steps === []) {
        return $empty;
    }

    sort($steps);
    $interval = $steps[intdiv(count($steps), 2)];
    $tolerated = max(60, (int)round($interval * $slack));

    $spans = [];
    $gaps = [];
    $spanFrom = $times[0];

    for ($i = 1, $n = count($times); $i < $n; $i++) {
        $step = $times[$i] - $times[$i - 1];
        if ($step <= $tolerated) {
            continue;
        }

        $spans[] = ['from' => $spanFrom, 'to' => $times[$i - 1]];
        $gaps[] = ['from' => $times[$i - 1], 'to' => $times[$i], 'seconds' => $step];
        $spanFrom = $times[$i];
    }

    $spans[] = ['from' => $spanFrom, 'to' => $times[count($times) - 1]];

    /*
     * A silence that runs to the end of the window is a gap too.
     *
     * Without this, a collector that stopped an hour ago shows a track ending
     * an hour early with nothing after it - which looks like the chart running
     * out of room rather than the collector running out of life.
     */
    $last = $times[count($times) - 1];
    if ($to - $last > $tolerated) {
        $gaps[] = ['from' => $last, 'to' => $to, 'seconds' => $to - $last, 'ongoing' => true];
    }

    return [
        'spans' => $spans,
        'gaps' => $gaps,
        'interval' => $interval,
        'first' => $times[0],
        'last' => $last,
        'readings' => count($times),
    ];
}
