<?php
declare(strict_types=1);

/*
 * Things that are unusual, as opposed to things that are large.
 *
 * The rules in alerts.php are thresholds: disk above 90%, a category up by
 * 100 MB, CPU pegged for a quarter of an hour. They are worth having and they
 * are not enough, for two opposite reasons.
 *
 * They fire when nothing is wrong. A build server whose queue always adds
 * 200 MB a day trips the category rule every single day, and after a week
 * nobody reads the mail. A threshold that is crossed routinely has stopped
 * being information.
 *
 * And they stay silent when something is. Eleven webalizer processes sat on
 * this server for 78 minutes without crossing anything at all: each one was
 * small, the disk was fine, and the CPU reading that did fire pointed at a
 * snapshot containing nothing but `ps` and the collector itself. The evidence
 * was there in plain sight - that job normally takes two minutes - and no
 * threshold in the world encodes "normally takes two minutes", because that
 * number is different on every server and nobody knows it in advance.
 *
 * So these rules compare each thing against its own history rather than
 * against a constant. That is the whole idea, and it brings three obligations
 * that most of the code below is spent honouring.
 *
 * A baseline needs enough history to be one. With three readings, "unusual"
 * means nothing, and a rule that guesses from three readings will cry wolf all
 * of its first day and be turned off before it is ever right. Every detector
 * here refuses to speak until it has `min_readings` behind it, and says
 * nothing rather than something shaky.
 *
 * A ratio needs a floor. Twenty-five percent of a 4 MiB service is 1 MiB, and
 * nobody wants mail about 1 MiB. Every proportional test is paired with an
 * absolute one, and both have to pass.
 *
 * A baseline must not be poisoned by the thing it is meant to catch. The
 * median and the ninetieth percentile are used throughout instead of the mean
 * and the maximum, precisely so that the hour a service spent leaking does not
 * quietly redefine "normal" and immunise the next leak against detection.
 *
 * One further trap, specific to the age rule and worth stating because getting
 * it wrong would make that rule useless: a daemon's age rises for ever. mysqld
 * running for nine days is not an anomaly, but against a baseline built when
 * it was young it looks like an enormous one. What separates a hung batch job
 * from a healthy daemon is not how old it is - it is whether it has ever been
 * young again. So the age rule only judges services whose age has been
 * observed to *reset*. A daemon that has simply been up a long time is never
 * eligible, and needs no special case anywhere else.
 */

require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/hostdb.php';
require_once __DIR__ . '/daemons.php';

/**
 * What each rule counts as normal, and what it takes to disagree.
 *
 * Defaults chosen to be quiet. A rule nobody trusts is worse than no rule, and
 * every one of these can be tightened once its first month of mail has proved
 * it honest.
 */
function anomalyRules(array $config): array
{
    // Nested under 'alerts' because that is what they are: they ride the same
    // email, the same cooldown and the same master switch.
    $set = $config['alerts']['anomalies'] ?? [];

    return $set + [
        'enabled' => true,

        // Fewer readings than this behind a thing and it gets no opinion.
        'min_readings' => 8,

        /*
         * Two windows, because the two kinds of subject have different rhythms.
         *
         * A daemon is present in every reading, so a day of history is hundreds
         * of samples and "recent" genuinely means recent. A batch job is
         * present in three readings a day, so a day of history is three samples
         * and nothing can be concluded from it - those rules look back a week
         * to have anything to compare against at all.
         */
        'baseline_hours' => 24,
        'service_baseline_hours' => 168,

        // A service holding a quarter more memory than it usually does.
        'service_pss_percent' => 25.0,
        'service_pss_floor_bytes' => 64 * 1024 * 1024,

        // A category that jumped, where jumping is not what it usually does.
        'category_floor_bytes' => 100 * 1024 * 1024,
        'category_multiple' => 3.0,

        // A file growing far faster than that same file usually grows.
        'file_floor_bytes' => 50 * 1024 * 1024,
        'file_multiple' => 4.0,
        'file_limit' => 5,

        // Load above one per core, held, not touched.
        'load_readings' => 3,

        // Swap filling while free memory falls - the two together, never apart.
        'swap_floor_bytes' => 32 * 1024 * 1024,

        /*
         * Many more copies of something than there usually are.
         *
         * process_readings is how many consecutive readings must show the pile
         * before it counts as persistent rather than a spike, and
         * process_age_seconds is how old the median has to be before "piling
         * up" is a fair description. Services that fan out by design get a
         * higher multiple than this - see fansOutByDesign().
         */
        'process_multiple' => 3.0,
        'process_floor' => 4,
        'process_readings' => 3,
        'process_age_seconds' => 600,

        // Something still running long after that thing normally finishes.
        'age_multiple' => 3.0,
        'age_floor_seconds' => 900,
    ];
}

/* ------------------------------------------------------------------ *
 * Statistics
 *
 * Median and percentile rather than mean and max, throughout. See the
 * poisoning note in the header: the mean of a series containing the
 * incident is dragged towards the incident, and the max simply *is* the
 * incident, so a rule built on either grows blind to exactly the fault it
 * caught last time.
 * ------------------------------------------------------------------ */

function medianOf(array $values): float
{
    return percentileOf($values, 0.5);
}

/**
 * The value below which the given fraction of the series falls.
 *
 * Interpolated between neighbours, so that short series - which is what the
 * sparse rules have to work with - do not snap to whichever sample happens to
 * land on the index.
 */
function percentileOf(array $values, float $fraction): float
{
    $values = array_values(array_map('floatval', $values));
    if ($values === []) {
        return 0.0;
    }

    sort($values);
    $position = $fraction * (count($values) - 1);
    $low = (int) floor($position);
    $high = (int) ceil($position);

    if ($low === $high) {
        return $values[$low];
    }

    return $values[$low] +
        ($values[$high] - $values[$low]) * ($position - $low);
}

/** Age is whole seconds; a drop larger than this is a different process. */
const AGE_RESET_TOLERANCE = 5.0;

/**
 * Is this series one process getting older, or a succession of them?
 *
 * The question the age rule stands on, and the obvious form of it - "has the
 * age ever gone down?" - is wrong in a way that only shows up on real data. A
 * job that appears in exactly one reading per run reports the same age every
 * time it is seen: 110 seconds, 110 seconds, 110 seconds. Nothing ever falls,
 * yet those are eleven different processes and the series says nothing about
 * any of them lasting.
 *
 * What actually separates the two is whether the age *tracks the clock*. One
 * continuous process is exactly five minutes older in a reading taken five
 * minutes later - that is what being the same process means. A series of
 * short-lived ones is not: its age wanders, repeats, or resets, because each
 * reading is looking at something new.
 *
 * So this compares each step in age against the wall-clock time between the
 * two readings, and calls it continuous only when nearly all of them agree. A
 * tolerance is needed because cron does not fire to the second, and a small
 * absolute floor because the tolerance has to survive short intervals.
 *
 * @param list<array{at: int, age: float}> $samples chronological
 */
function tracksTheClock(array $samples, float $agreement = 0.9): bool
{
    $tracking = 0;
    $pairs = 0;
    $resets = 0;

    for ($i = 1; $i < count($samples); $i++) {
        $clock = (float) $samples[$i]['at'] - (float) $samples[$i - 1]['at'];
        if ($clock <= 0) {
            continue;
        }

        $pairs++;
        $aged = (float) $samples[$i]['age'] - (float) $samples[$i - 1]['age'];
        $slack = max(5.0, $clock * 0.1);

        if (abs($aged - $clock) <= $slack) {
            $tracking++;
        } elseif ($aged < -AGE_RESET_TOLERANCE) {
            /*
             * A fixed tolerance, not the proportional one above.
             *
             * The slack is a share of the interval, which is right for jitter
             * and wrong here: between two runs of a daily job the interval is
             * a day, so a tenth of it is two and a half hours, and a job
             * falling from fifty minutes back to five would be swallowed whole
             * by its own tolerance. Going backwards is going backwards
             * whatever the gap - only the reading noise needs allowing for.
             */
            $resets++;
        }
    }

    /*
     * Repeated resets settle it, whatever the ratio says.
     *
     * The ratio alone quietly misfiles the longer batch jobs. A backup that
     * takes fifty minutes is caught in ten consecutive readings, and within a
     * single run its age does track the clock - nine of those ten steps look
     * exactly like a daemon, so the ratio reaches 90% and the rule would
     * excuse the one job most worth watching.
     *
     * Falling back to its beginning twice is not something one process can do.
     * A daemon restarted once during the week is still one continuous process
     * and stays exempt, which is the intended answer for it.
     */
    if ($resets >= 2) {
        return false;
    }

    // Nothing to judge from is not evidence of continuity. Saying "yes" here
    // would exempt a service from the age rule for want of history, which is
    // the opposite of what too little history should do.
    if ($pairs < 2) {
        return false;
    }

    return $tracking / $pairs >= $agreement;
}

/* ------------------------------------------------------------------ *
 * Detectors
 *
 * Pure: each takes numbers and returns either an alert or null. Nothing
 * here opens a database, which is what makes it possible to test them
 * against a series typed out by hand rather than against whatever the
 * server happened to be doing.
 * ------------------------------------------------------------------ */

/**
 * A service holding materially more memory than it usually holds.
 *
 * PSS rather than RSS, because RSS counts every shared page in full against
 * every process that maps it, and a service that forks ten workers appears to
 * grow tenfold at the moment it forks. PSS divides each shared page among its
 * users, so this compares like with like.
 */
function serviceMemoryAnomaly(
    string $name,
    float $current,
    array $history,
    array $rules,
): ?array {
    if (count($history) < (int) $rules['min_readings']) {
        return null;
    }

    $usual = medianOf($history);
    if ($usual <= 0) {
        return null;
    }

    $grew = $current - $usual;
    $percent = ($grew / $usual) * 100;

    if (
        $grew < (float) $rules['service_pss_floor_bytes'] ||
        $percent < (float) $rules['service_pss_percent']
    ) {
        return null;
    }

    return [
        'rule' => 'anomaly:service_memory:' . $name,
        'severity' => 'warning',
        'subject' => sprintf(
            '%s is holding %s more memory than usual',
            $name,
            humanBytes((int) $grew),
        ),
        'detail' => sprintf(
            '%s is using %s of proportional memory (PSS). Its usual figure is %s, taken as the ' .
                "median of %d readings over the last %d hours, so this is %.0f%% above normal.\n\n" .
                'A service that has grown and stays grown is leaking. One that grows and returns to ' .
                'its usual figure was busy. The services chart will show which of the two this is.',
            $name,
            humanBytes((int) $current),
            humanBytes((int) $usual),
            count($history),
            (int) $rules['baseline_hours'],
            $percent,
        ),
    ];
}

/**
 * Services that fan out by design.
 *
 * A web server, a Git server and an SSH daemon all answer concurrency by
 * spawning, so their process count tracks how busy they are rather than whether
 * anything is wrong. Eighteen copies of git during a clone storm is the system
 * working; eighteen copies of a singleton daemon is never that.
 *
 * The list exists because a rule that treats those two the same has to pick one
 * threshold, and either choice is wrong half the time: low enough to catch a
 * stuck daemon means crying wolf at every busy afternoon.
 */
function fansOutByDesign(string $name): bool
{
    static $known = [
        'git',
        'git-upload-pack',
        'git-receive-pack',
        'git-http-backend',
        'php-cgi',
        'php-fpm',
        'php',
        'httpd',
        'apache2',
        'nginx',
        'lsphp',
        'sshd',
        'dovecot',
        'imap-login',
        'pop3-login',
        'exim',
        'smtpd',
        'postfix',
        'qmgr',
        'proxyexec',
        'cpsrvd',
        'cpanellogd',
    ];

    return in_array($name, $known, true) || str_starts_with($name, 'ea-php');
}

/**
 * Many more copies of something than there normally are - with the evidence.
 *
 * The first version of this reported the count and asserted a cause:
 *
 *   "Processes multiplying rather than finishing is what a job that has stopped
 *    completing looks like from outside."
 *
 * That is a reasonable hypothesis and it was stated as a finding. For git it
 * was very likely wrong: a Git server spawns a process per client operation, so
 * a jump from six to eighteen is a busy morning. The alert was confident about
 * something it had not measured.
 *
 * What actually separates the two is not the count. It is the count *together
 * with* how old those processes are and whether the pile persists:
 *
 *   many, young, many parents, sleeping    a busy server
 *   many, old, one parent, uninterruptible a job that has stopped finishing
 *
 * So the rule is count x age x persistence, the severity depends on all three,
 * and the text says which of them it actually has evidence for.
 *
 * @param array $now     the latest reading for this service
 * @param list<float> $history past counts
 * @param array $context load and core count at this moment
 */
function serviceCountAnomaly(
    string $name,
    array $now,
    array $history,
    array $rules,
    array $context = [],
): ?array {
    if (count($history) < (int) $rules['min_readings']) {
        return null;
    }

    $usual = medianOf($history);
    if ($usual <= 0) {
        return null;
    }

    $count = (int) ($now['procs'] ?? 0);
    $ratio = $count / $usual;

    /*
     * A higher bar for the services that fan out on purpose.
     *
     * Two times a quiet baseline is one extra visitor on a Git server, and an
     * alert that fires for that will be muted within a week - taking the real
     * ones with it.
     */
    $multiple = fansOutByDesign($name)
        ? max(3.0, (float) $rules['process_multiple'])
        : (float) $rules['process_multiple'];

    if (
        $ratio < $multiple ||
        $count - $usual < (float) $rules['process_floor']
    ) {
        return null;
    }

    // How long the pile has been there, from the readings themselves.
    $recent = array_slice(
        $history,
        -max(1, (int) ($rules['process_readings'] ?? 3)),
    );
    $persistent =
        count($recent) >= (int) ($rules['process_readings'] ?? 3) &&
        count(array_filter($recent, fn(float $n): bool => $n >= $usual * 2)) ===
            count($recent);

    $median =
        $now['median_seconds'] === null ? null : (int) $now['median_seconds'];
    $oldMedian =
        $median !== null &&
        $median >= (int) ($rules['process_age_seconds'] ?? 600);

    $cores = (int) ($context['cores'] ?? 0);
    $load = (float) ($context['load'] ?? 0);
    $loaded = $cores > 0 && $load > $cores;

    $parents =
        $now['distinct_parents'] === null
            ? null
            : (int) $now['distinct_parents'];
    $states = (string) ($now['state_summary'] ?? '');
    $commands = (string) ($now['command_summary'] ?? '');

    /*
     * Critical needs all three, exactly as asked for: many, old, and the
     * machine actually suffering. Any one of them alone is a Tuesday.
     */
    $critical = $oldMedian && $loaded;

    $evidence = [];
    if ($median !== null) {
        $evidence[] = sprintf(
            'oldest %s, median %s, newest %s',
            $now['oldest_seconds'] === null
                ? '?'
                : humanDuration((float) $now['oldest_seconds']),
            humanDuration((float) $median),
            $now['newest_seconds'] === null
                ? '?'
                : humanDuration((float) $now['newest_seconds']),
        );
    }
    if ($parents !== null) {
        $evidence[] =
            $parents === 1
                ? 'all of them share one parent process'
                : sprintf('%d distinct parent processes', $parents);
    }
    if ($states !== '') {
        $evidence[] = 'states ' . $states;
    }
    if ($commands !== '') {
        $evidence[] = 'commands ' . $commands;
    }

    /*
     * The reading of the evidence, offered as a reading.
     *
     * Short-lived, many-parented, sleeping processes on a service that fans out
     * is the ordinary shape of being busy, and saying so is more useful than
     * repeating the alarming interpretation.
     */
    $young =
        $median !== null &&
        $median < (int) ($rules['process_age_seconds'] ?? 600);
    $spread = $parents !== null && $parents > 1;
    $stuck = str_contains($states, 'D:') || $parents === 1;

    if (fansOutByDesign($name) && $young && $spread) {
        $reading = sprintf(
            'These are short-lived and started by %d different parents, which is what %s ' .
                'looks like when it is simply busy. Worth a glance rather than an intervention.',
            $parents,
            $name,
        );
    } elseif ($stuck && $oldMedian) {
        $reading =
            'They are old and share a parent, which is what a job that has stopped ' .
            'finishing looks like: each run starts another and none of the old ones leave.';
    } else {
        $reading = sprintf(
            'The count is %.1f times its recent median. That can mean work is piling up ' .
                'unfinished, and on a service that spawns per request it can equally mean a ' .
                'busy period - the ages and parents above are what tell them apart.',
            $ratio,
        );
    }

    return [
        'rule' => 'anomaly:process_count:' . $name,
        'severity' => $critical ? 'critical' : 'warning',
        'subject' => sprintf(
            '%d copies of %s are running%s',
            $count,
            $name,
            $critical ? ', and the machine is struggling' : '',
        ),
        'detail' => sprintf(
            "There are %d %s processes against a usual %s, the median of %d readings.\n\n" .
                "%s\n\n%s%s",
            $count,
            $name,
            rtrim(rtrim(number_format($usual, 1), '0'), '.'),
            count($history),
            $evidence === []
                ? 'No age or parent detail was recorded for this reading.'
                : 'At this reading: ' . implode('; ', $evidence) . '.',
            $reading,
            $critical
                ? sprintf(
                    "\n\nRaised as critical because the pile is old and load is %.2f on %d core%s " .
                        '- the machine is behind, not merely busy.',
                    $load,
                    $cores,
                    $cores === 1 ? '' : 's',
                )
                : ($persistent
                    ? "\n\nIt has been at least double its baseline for the last " .
                        (int) ($rules['process_readings'] ?? 3) .
                        ' readings, so this is not a spike.'
                    : ''),
        ),
    ];
}

/**
 * Something still running long after that thing normally finishes.
 *
 * The rule the webalizer incident asked for. Its whole difficulty is in the
 * eligibility test rather than the comparison: a daemon's age rises for ever,
 * so against a baseline built while it was young, mysqld up for nine days
 * looks like an enormous anomaly and is not one. See tracksTheClock() for what
 * actually tells the two apart.
 *
 * The ninetieth percentile rather than the maximum is what stops one hang from
 * excusing the next one. A 78-minute hang is a handful of samples in a week of
 * history, which barely moves p90 and would redefine the maximum entirely.
 *
 * @param list<array{at: int, age: float}> $samples chronological
 */
function serviceAgeAnomaly(
    string $name,
    float $current,
    array $samples,
    array $rules,
): ?array {
    if (count($samples) < (int) $rules['min_readings']) {
        return null;
    }

    if (tracksTheClock($samples)) {
        // One process that has simply been up a while. Nothing to say.
        return null;
    }

    if ($current < (float) $rules['age_floor_seconds']) {
        return null;
    }

    $usual = percentileOf(array_column($samples, 'age'), 0.9);
    if ($usual <= 0 || $current < $usual * (float) $rules['age_multiple']) {
        return null;
    }

    return [
        'rule' => 'anomaly:process_age:' . $name,
        'severity' => 'warning',
        'subject' => sprintf(
            '%s has been running for %s',
            $name,
            humanDuration($current),
        ),
        'detail' => sprintf(
            'The oldest %s process has been alive for %s. That job normally finishes well ' .
                'inside %s - the figure is the ninetieth percentile of %d readings over the last ' .
                "%d hours, so nine times in ten it is done by then.\n\n" .
                'This one is not, by a factor of %.1f. A job that starts on a timer and never ' .
                'finishes will keep starting anyway, and the copies accumulate until something ' .
                'runs out.',
            $name,
            humanDuration($current),
            humanDuration($usual),
            count($samples),
            (int) $rules['service_baseline_hours'],
            $current / $usual,
        ),
    ];
}

/**
 * A category that jumped, where jumping is not what it does.
 *
 * Deliberately not the same rule as `category_jump` in alerts.php, which fires
 * on any 100 MB and is right to. This one additionally asks whether 100 MB is
 * remarkable *for this category*, so a backup directory that gains a couple of
 * hundred megabytes every single night stops sending mail about it, while the
 * same growth somewhere that has been flat all month still does.
 */
function categoryGrowthAnomaly(
    string $category,
    int $grew,
    array $history,
    array $rules,
): ?array {
    if ($grew < (int) $rules['category_floor_bytes']) {
        return null;
    }

    if (count($history) < (int) $rules['min_readings']) {
        return null;
    }

    $usual = medianOf($history);

    /*
     * A category that normally does not grow at all has a median of zero, and
     * no multiple of zero is ever exceeded. The floor above is the entire test
     * in that case, which is the right answer: the first 100 MB to land
     * somewhere that has been flat for a month is exactly the interesting one.
     */
    if ($usual > 0 && $grew < $usual * (float) $rules['category_multiple']) {
        return null;
    }

    return [
        'rule' => 'anomaly:category:' . $category,
        'severity' => 'warning',
        'subject' => sprintf(
            '%s grew by %s, which is unusual for it',
            ucfirst($category),
            humanBytes($grew),
        ),
        'detail' => sprintf(
            '%s gained %s since the previous scan. Between scans it usually changes by %s ' .
                '(median of %d recent scans)%s.',
            ucfirst($category),
            humanBytes($grew),
            humanBytes((int) $usual),
            count($history),
            $usual > 0
                ? sprintf(
                    ', so this is %.1f times its normal rate',
                    $grew / $usual,
                )
                : ', so this is the first growth of any size recorded there',
        ),
    ];
}

/**
 * A file growing far faster than that same file grows.
 *
 * Per-file baselines are what make this different from the existing size
 * threshold. A log that turns over 200 MB a day is not news on the day it does
 * it again; the same 200 MB arriving in a file that has never exceeded 5 MB is
 * the runaway error log that filled this disk once already.
 *
 * Rates rather than sizes, because scans are not evenly spaced. A manual "scan
 * now" ten minutes after the scheduled one would otherwise make every file on
 * the disk look as though its growth had collapsed.
 */
function fileGrowthAnomaly(array $file, array $history, array $rules): ?array
{
    $grew = (int) $file['grew'];
    if ($grew < (int) $rules['file_floor_bytes']) {
        return null;
    }

    if (count($history) < (int) $rules['min_readings']) {
        return null;
    }

    $rate = (float) $file['rate'];
    $usual = medianOf($history);

    if ($usual > 0 && $rate < $usual * (float) $rules['file_multiple']) {
        return null;
    }

    return [
        'rule' => 'anomaly:file_growth',
        'path_id' => (int) $file['path_id'],
        'path' => (string) $file['path'],
        'kind' => (string) ($file['kind'] ?? 'file'),
        'severity' => 'warning',
        'subject' => sprintf(
            '%s is growing much faster than it normally does',
            basename((string) $file['path']),
        ),
        /*
         * The path leads, as it does in the threshold alert next door - and
         * for the same reason. presentAlert() swaps it for a mask when nobody
         * is signed in, and a line that leads with the path masks into
         * something readable, while "Full path: file 99C111 (depth 3)" does
         * not.
         */
        'detail' => sprintf(
            "%s\n  grew by %s since the previous scan, a rate of %s an hour.\n  %s",
            (string) $file['path'],
            humanBytes($grew),
            humanBytes((int) $rate),
            $usual > 0
                ? sprintf(
                    'It normally grows at %s an hour (median of %d recent scans), so it is ' .
                        'running %.0f times faster than usual.',
                    humanBytes((int) $usual),
                    count($history),
                    $rate / $usual,
                )
                : sprintf(
                    'It had not grown at all in the previous %d scans.',
                    count($history),
                ),
        ),
    ];
}

/**
 * Load above one job per core, held across consecutive readings.
 *
 * Load is a queue length rather than a percentage: on four cores, four is
 * fully committed and eight means half the work is waiting for a core rather
 * than using one. A single reading over the line is a scan or a backup
 * starting, so the rule wants several in a row.
 *
 * `load1` is already an average over the preceding minute, so consecutive
 * readings really are consecutive evidence rather than one moment sampled
 * repeatedly.
 */
function loadAnomalies(array $readings, array $rules): array
{
    $wanted = (int) $rules['load_readings'];
    if ($wanted < 1 || count($readings) < $wanted) {
        return [];
    }

    $recent = array_slice($readings, 0, $wanted);
    $cores = (int) ($recent[0]['cpu_cores'] ?? 0);
    if ($cores < 1) {
        return [];
    }

    $loads = [];
    foreach ($recent as $row) {
        $load = (float) ($row['load1'] ?? 0);
        if ($load <= $cores) {
            return [];
        }
        $loads[] = $load;
    }

    return [
        [
            'rule' => 'anomaly:load',
            'severity' => 'warning',
            'subject' => sprintf(
                'Load has been above %d for %d readings',
                $cores,
                $wanted,
            ),
            'detail' => sprintf(
                "Load average over the last %d readings: %s. This machine has %d core%s.\n\n" .
                    'Load counts work waiting as well as work running, so anything above %d means ' .
                    'jobs are queuing for a core rather than getting one. Sustained, it is a runaway ' .
                    'process or genuinely too much work, and the process list will say which.',
                $wanted,
                implode(
                    ', ',
                    array_map(
                        fn(float $l): string => number_format($l, 2),
                        $loads,
                    ),
                ),
                $cores,
                $cores === 1 ? '' : 's',
                $cores,
            ),
        ],
    ];
}

/**
 * Swap filling while free memory falls.
 *
 * The pair is the signal, and either half alone is not. Swap in use means
 * nothing on its own - Linux leaves pages there long after the pressure that
 * put them there is gone, and a server can sit at 200 MB of swap for months in
 * perfect health. MemAvailable falling on its own means nothing either; that
 * is the page cache doing its job.
 *
 * Both moving the wrong way at once is the kernel choosing to write memory to
 * disk because it cannot free enough any other way, which is the beginning of
 * the state where everything gets slow before anything gets killed.
 */
function swapAnomalies(array $latest, array $earlier, array $rules): array
{
    $swapGrew =
        (int) ($latest['swap_used_bytes'] ?? 0) -
        (int) ($earlier['swap_used_bytes'] ?? 0);
    $availableFell =
        (int) ($earlier['mem_available_bytes'] ?? 0) -
        (int) ($latest['mem_available_bytes'] ?? 0);

    if ($swapGrew < (int) $rules['swap_floor_bytes'] || $availableFell <= 0) {
        return [];
    }

    return [
        [
            'rule' => 'anomaly:swap',
            'severity' => 'warning',
            'subject' => sprintf(
                'Swap grew by %s while free memory fell',
                humanBytes($swapGrew),
            ),
            'detail' => sprintf(
                'Swap in use rose by %s and available memory dropped by %s over the same period. ' .
                    "Swap now stands at %s, with %s available.\n\n" .
                    'Either figure alone would be unremarkable - swap holds pages long after they ' .
                    'stopped mattering, and available memory moves with the page cache. Both moving ' .
                    'the wrong way together is the kernel paging memory out because it has run out of ' .
                    'cheaper options.',
                humanBytes($swapGrew),
                humanBytes($availableFell),
                humanBytes((int) ($latest['swap_used_bytes'] ?? 0)),
                humanBytes((int) ($latest['mem_available_bytes'] ?? 0)),
            ),
        ],
    ];
}

/**
 * A daemon that was running and is not any more.
 *
 * The one alert on this page that needs no baseline and no statistics: a
 * service either answered or it did not. It is here rather than among the
 * thresholds because the interesting part is still a comparison with the past -
 * "not running" on its own is meaningless for something that was never
 * installed.
 *
 * Which is the entire difficulty. A watch list has ten services on it and a
 * given machine runs four; the other six must never raise anything, ever, or
 * the mail becomes six permanent false alarms and the two real ones arrive in
 * company nobody reads. So this fires only on a *transition*: the service was
 * seen running within living memory, and now is not.
 *
 * @param list<array> $rows from daemonStatus()
 */
function daemonAnomalies(array $rows, array $rules): array
{
    $found = [];

    foreach ($rows as $row) {
        // Not installed here, or no systemd to ask and no process to see.
        // Neither is evidence of anything having gone wrong.
        if (($row['state'] ?? '') !== 'stopped') {
            continue;
        }

        /*
         * Only if we have actually watched it run.
         *
         * since_known false means the service has been stopped for every
         * reading this monitor has ever taken, which is what a service somebody
         * disabled on purpose looks like. Reporting that as an incident on the
         * first night would be the monitor complaining about the machine's
         * configuration rather than about a fault.
         */
        if (!($row['since_known'] ?? false)) {
            continue;
        }

        $since = (int) ($row['since'] ?? 0);
        $label = (string) $row['label'];

        $found[] = [
            'rule' => 'anomaly:daemon_down:' . $row['name'],
            'severity' => 'critical',
            'subject' => sprintf('%s is not running', $label),
            'detail' => sprintf(
                "%s was running and is not any more%s.\n\n%s%s",
                $label,
                $since > 0
                    ? sprintf(
                        ', as of %s',
                        humanDuration(max(0, time() - $since)) . ' ago',
                    )
                    : '',
                $row['unit']
                    ? sprintf(
                        'systemd reports the unit %s as %s (%s). ',
                        $row['unit'],
                        $row['active_state'] ?? 'inactive',
                        $row['sub_state'] ?? 'unknown',
                    )
                    : 'No systemd unit answered for it. ',
                ($row['restarts'] ?? 0) > 0
                    ? sprintf(
                        'It has restarted %d times, so if it comes back on its own it is ' .
                            'crash-looping rather than recovering.',
                        (int) $row['restarts'],
                    )
                    : 'Nothing else on this page can report a service that is absent, ' .
                        'because a process that is not running uses no memory and no CPU.',
            ),
        ];
    }

    return $found;
}

/* ------------------------------------------------------------------ *
 * Gathering
 * ------------------------------------------------------------------ */

/**
 * Everything unusual, from both databases.
 *
 * The host database is read-only here and may not be there at all - the host
 * collector is optional and needs root to install. Its absence is normal and
 * silent; the account-side rules stand on their own.
 */
function detectAnomalies(array $config, PDO $pdo): array
{
    $rules = anomalyRules($config);
    if (!($rules['enabled'] ?? true)) {
        return [];
    }

    $found = accountAnomalies($pdo, $rules);

    try {
        $host = hostDb($config, false);
        if ($host !== null) {
            $found = [...$found, ...hostAnomalies($host, $rules)];
        }
    } catch (Throwable) {
        // A host database that cannot be opened is the host collector not being
        // installed, which is the ordinary case and not a fault to report.
    }

    return $found;
}

/**
 * Rules that run on the account's own scans and readings.
 */
function accountAnomalies(PDO $pdo, array $rules): array
{
    $found = [];

    /*
     * Windows are measured back from the newest reading rather than from now.
     *
     * Every other rule here is anchored to the data - the last two scans, the
     * last few readings - and anchoring this one to the wall clock instead
     * would make it the only rule that goes quiet when the collector falls
     * behind, which is not a distinction worth having.
     */
    $newest = (int) $pdo
        ->query('SELECT MAX(taken_at) FROM vitals')
        ->fetchColumn();
    if ($newest <= 0) {
        return $found;
    }
    $window = $newest - (int) $rules['baseline_hours'] * 3600;

    // Load, over the most recent readings.
    $readings = $pdo->prepare(
        'SELECT load1, cpu_cores FROM vitals
         WHERE load1 IS NOT NULL AND cpu_cores IS NOT NULL
         ORDER BY taken_at DESC LIMIT ?',
    );
    $readings->execute([max(1, (int) $rules['load_readings'])]);
    $found = [
        ...$found,
        ...loadAnomalies($readings->fetchAll(PDO::FETCH_ASSOC), $rules),
    ];

    /*
     * Swap, over the baseline window rather than between neighbouring readings.
     *
     * Consecutive readings are minutes apart and swap moves in kilobytes over
     * that distance, so a reading-to-reading comparison would be measuring
     * noise. The question is whether the machine is worse off than it was some
     * hours ago.
     */
    $swap = $pdo->prepare(
        'SELECT swap_used_bytes, mem_available_bytes, taken_at FROM vitals
         WHERE swap_used_bytes IS NOT NULL AND mem_available_bytes IS NOT NULL
           AND taken_at >= ?
         ORDER BY taken_at DESC',
    );
    $swap->execute([$window]);
    $rows = $swap->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) >= 2) {
        $found = [
            ...$found,
            ...swapAnomalies($rows[0], $rows[count($rows) - 1], $rules),
        ];
    }

    $found = [...$found, ...categoryAnomalies($pdo, $rules)];
    $found = [...$found, ...fileAnomalies($pdo, $rules)];

    return $found;
}

/**
 * Category growth, each category against its own past growth.
 */
function categoryAnomalies(PDO $pdo, array $rules): array
{
    $scans = $pdo
        ->query(
            'SELECT id FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 40',
        )
        ->fetchAll(PDO::FETCH_COLUMN);

    if (count($scans) < 3) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($scans), '?'));
    $rowsStmt = $pdo->prepare(
        "SELECT scan_id, category, bytes FROM scan_totals
         WHERE scan_id IN ($placeholders)",
    );
    $rowsStmt->execute($scans);

    // Newest first, so the walk below reads back through time in order.
    $order = array_flip($scans);
    $series = [];
    foreach ($rowsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $series[(string) $row['category']][$order[(int) $row['scan_id']]] =
            (int) $row['bytes'];
    }

    $found = [];
    foreach ($series as $category => $byAge) {
        ksort($byAge);
        $sizes = array_values($byAge);
        if (count($sizes) < 3) {
            continue;
        }

        /*
         * Growth between each pair, ignoring the shrinkages.
         *
         * A category that halves because somebody emptied it is not evidence
         * about how fast it grows, and letting a negative into the median
         * would drag the baseline below zero and disable the multiple.
         */
        $growths = [];
        for ($i = 1; $i < count($sizes); $i++) {
            $growths[] = max(0, $sizes[$i - 1] - $sizes[$i]);
        }

        $latest = array_shift($growths);
        $anomaly = categoryGrowthAnomaly(
            (string) $category,
            (int) $latest,
            $growths,
            $rules,
        );
        if ($anomaly !== null) {
            $found[] = $anomaly;
        }
    }

    return $found;
}

/**
 * Files growing faster than they normally grow.
 *
 * Only the biggest few movers are examined. A scan holds tens of thousands of
 * paths and building a per-file history for every one of them would cost more
 * than the scan itself, while the ones that matter are by definition the ones
 * that moved most.
 */
function fileAnomalies(PDO $pdo, array $rules): array
{
    $scans = $pdo
        ->query(
            'SELECT id, finished_at FROM scans
         WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 2',
        )
        ->fetchAll(PDO::FETCH_ASSOC);

    if (count($scans) < 2) {
        return [];
    }

    $hours = max(
        0.01,
        ((int) $scans[0]['finished_at'] - (int) $scans[1]['finished_at']) /
            3600,
    );

    $movers = $pdo->prepare(
        'SELECT p.id AS path_id, p.path, p.kind, now.bytes - was.bytes AS grew
         FROM path_sizes now
         JOIN path_sizes was ON was.path_id = now.path_id AND was.scan_id = :was
         JOIN paths p ON p.id = now.path_id
         WHERE now.scan_id = :now AND p.kind = \'file\'
           AND now.bytes - was.bytes >= :floor
         ORDER BY grew DESC LIMIT :limit',
    );
    $movers->bindValue(':was', (int) $scans[1]['id'], PDO::PARAM_INT);
    $movers->bindValue(':now', (int) $scans[0]['id'], PDO::PARAM_INT);
    $movers->bindValue(
        ':floor',
        (int) $rules['file_floor_bytes'],
        PDO::PARAM_INT,
    );
    $movers->bindValue(':limit', (int) $rules['file_limit'], PDO::PARAM_INT);
    $movers->execute();

    $history = $pdo->prepare(
        'SELECT s.finished_at, ps.bytes FROM path_sizes ps
         JOIN scans s ON s.id = ps.scan_id
         WHERE ps.path_id = ? AND s.finished_at IS NOT NULL
         ORDER BY s.finished_at DESC LIMIT 40',
    );

    $found = [];
    foreach ($movers->fetchAll(PDO::FETCH_ASSOC) as $file) {
        $history->execute([(int) $file['path_id']]);
        $past = $history->fetchAll(PDO::FETCH_ASSOC);

        // Rates, so that unevenly spaced scans compare honestly.
        $rates = [];
        for ($i = 1; $i < count($past); $i++) {
            $span =
                ((int) $past[$i - 1]['finished_at'] -
                    (int) $past[$i]['finished_at']) /
                3600;
            if ($span <= 0) {
                continue;
            }
            $rates[] = max(
                0,
                ((int) $past[$i - 1]['bytes'] - (int) $past[$i]['bytes']) /
                    $span,
            );
        }

        // The first entry is the growth being judged; it cannot also be part of
        // the baseline it is being judged against.
        array_shift($rates);

        $anomaly = fileGrowthAnomaly(
            $file + ['rate' => (int) $file['grew'] / $hours],
            $rates,
            $rules,
        );
        if ($anomaly !== null) {
            // One rule name per file, or a cooldown on the first would silence
            // every other file for six hours.
            $anomaly['rule'] .= ':' . $file['path_id'];
            $found[] = $anomaly;
        }
    }

    return $found;
}

/**
 * Rules that need what only the host collector can see.
 */
function hostAnomalies(PDO $host, array $rules): array
{
    /*
     * The daemon check first, and outside the services guard below.
     *
     * A machine with no host_services rows can still have daemon states, and
     * "the database is too new to have memory history" is not a reason to stay
     * quiet about MariaDB being down.
     */
    $found = daemonAnomalies(daemonStatus($host)['rows'], $rules);

    $latest = (int) $host
        ->query('SELECT MAX(taken_at) FROM host_services')
        ->fetchColumn();
    if ($latest <= 0) {
        return $found;
    }

    $since = $latest - (int) $rules['service_baseline_hours'] * 3600;
    $recent = $latest - (int) $rules['baseline_hours'] * 3600;

    $stmt = $host->prepare(
        'SELECT name, taken_at, pss_bytes, procs, oldest_seconds, newest_seconds,
                median_seconds, distinct_parents, state_summary, command_summary
         FROM host_services WHERE taken_at >= ? ORDER BY taken_at ASC',
    );
    $stmt->execute([$since]);

    $series = [];
    $current = [];
    /*
     * Streamed, not fetchAll()'d.
     *
     * At the service baseline's default of seven days this can be tens of
     * thousands of rows, and the loop below only ever needs one of them at a
     * time to build $series and $current - materialising the whole result
     * into a second PHP array first means holding both copies in memory at
     * once for no reason. Iterating the statement directly keeps SQLite
     * handing rows over one at a time, which is the entire difference: same
     * query, same result, a fraction of the peak memory on an install old
     * enough to have a week of this table.
     */
    foreach ($stmt as $row) {
        $name = (string) $row['name'];

        if ((int) $row['taken_at'] === $latest) {
            $current[$name] = $row;
            continue;
        }

        // Memory is judged against the last day, ages and counts against the
        // whole week. See the two-windows note in anomalyRules().
        if ((int) $row['taken_at'] >= $recent) {
            $series[$name]['pss'][] = (float) $row['pss_bytes'];
        }
        $series[$name]['procs'][] = (float) $row['procs'];
        if ($row['oldest_seconds'] !== null) {
            // Timestamped, because the age rule has to ask whether the age
            // moved with the clock, not merely how big it got.
            $series[$name]['age'][] = [
                'at' => (int) $row['taken_at'],
                'age' => (float) $row['oldest_seconds'],
            ];
        }
    }

    /*
     * Load at the moment of the reading, from the host's own sample.
     *
     * The count rule needs it: a pile of processes on an idle machine is a
     * curiosity, and the same pile with load above the core count is the
     * machine failing to keep up. Absent, the rule simply never escalates.
     */
    $context = [];
    try {
        $sample = $host
            ->query(
                'SELECT load1, cpu_cores FROM host_samples ORDER BY id DESC LIMIT 1',
            )
            ->fetch(PDO::FETCH_ASSOC);
        if ($sample) {
            $context = [
                'load' => (float) ($sample['load1'] ?? 0),
                'cores' => (int) ($sample['cpu_cores'] ?? 0),
            ];
        }
    } catch (Throwable) {
        $context = [];
    }

    foreach ($current as $name => $row) {
        $past = $series[$name] ?? [];

        $checks = [
            serviceMemoryAnomaly(
                $name,
                (float) $row['pss_bytes'],
                $past['pss'] ?? [],
                $rules,
            ),
            serviceCountAnomaly(
                $name,
                $row,
                $past['procs'] ?? [],
                $rules,
                $context,
            ),
            $row['oldest_seconds'] === null
                ? null
                : serviceAgeAnomaly(
                    $name,
                    (float) $row['oldest_seconds'],
                    $past['age'] ?? [],
                    $rules,
                ),
        ];

        foreach ($checks as $anomaly) {
            if ($anomaly !== null) {
                $found[] = $anomaly;
            }
        }
    }

    return $found;
}

/**
 * Drops the threshold alert when an anomaly already covers the same event.
 *
 * A log file that runs away trips `path_jump` because it grew by more than the
 * configured size, and `anomaly:file_growth` because it grew far faster than
 * that file ever grows. Both are true and both describe the same runaway, so
 * sending both is one event reported twice - which is the noise this whole
 * file exists to reduce, arriving from the wrong direction.
 *
 * The anomaly wins because it says strictly more: the threshold reports the
 * size, the anomaly reports the size *and* what the size normally is. Where no
 * anomaly fired, the threshold stands untouched - it is still the only thing
 * watching a subject with no history behind it, which is every subject for the
 * first day.
 *
 * @param list<array> $alerts
 * @return list<array>
 */
function supersedeThresholds(array $alerts): array
{
    $covered = [];
    foreach ($alerts as $alert) {
        $rule = (string) $alert['rule'];
        if (str_starts_with($rule, 'anomaly:category:')) {
            $covered[
                'category_jump:' . substr($rule, strlen('anomaly:category:'))
            ] = true;
        }
        if (str_starts_with($rule, 'anomaly:file_growth')) {
            // Keyed by the file, since one runaway file must not silence the
            // threshold for a different one.
            $covered['path_jump/' . ($alert['path_id'] ?? '')] = true;
        }
    }

    return array_values(
        array_filter($alerts, function (array $alert) use ($covered): bool {
            $rule = (string) $alert['rule'];
            if (isset($covered[$rule])) {
                return false;
            }
            if (
                $rule === 'path_jump' &&
                isset($covered['path_jump/' . ($alert['path_id'] ?? '')])
            ) {
                return false;
            }
            return true;
        }),
    );
}
