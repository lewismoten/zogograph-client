<?php
declare(strict_types=1);

/*
 * What gets collected, how often, and who asks for it.
 *
 * Five things that were separate ideas and turn out to be one:
 *
 *   - which scans the host collector runs, and how often
 *   - which the account's collector runs, and how often
 *   - collecting more eagerly while somebody is actually looking
 *   - things worth recording only while somebody is looking
 *   - how hard to throttle anything that leaves this machine
 *
 * They interlock because they are all answers to "when does this work happen".
 * A scan set to hourly that also speeds up while somebody browses, and is also
 * one of three things sharing a per-run budget, cannot be configured on three
 * separate pages without the settings contradicting each other.
 *
 * The costs are real and uneven. A disk walk reads every inode under the
 * account; a capability probe runs a hundred programs; a packet capture holds
 * a raw socket open. Some of these are fine hourly and some would be a
 * self-inflicted outage. So each carries a recommendation, including - for the
 * expensive ones - a recommendation not to schedule it at all.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/actions.php';

/** The intervals on offer, in seconds. Zero means never on a schedule. */
function scanIntervals(): array
{
    return [
        0 => 'only when asked',
        300 => 'every 5 minutes',
        900 => 'every 15 minutes',
        1800 => 'every 30 minutes',
        3600 => 'hourly',
        21600 => 'every 6 hours',
        43200 => 'twice a day',
        86400 => 'daily',
        604800 => 'weekly',
    ];
}

/**
 * Every scan, on both sides, with what it costs and what it is worth.
 *
 * `recommended` is the interval this install would choose. Where that is zero
 * the note says why - "not on a schedule" is a real recommendation for
 * something that reads every inode on the disk, and presenting it as an
 * oversight would get somebody to turn it on.
 *
 * `task` is the host-task name the account writes to ask for one now, or null
 * for things the account collector does itself.
 *
 * @return array<string, array<string, mixed>>
 */
function scanCatalogue(): array
{
    return [
        /* ------------------------------------------------ the account -- */

        'vitals' => [
            'side' => 'account',
            'label' => 'Readings',
            'what' =>
                'Disk, inodes, CPU, memory, load and uptime. One row per run.',
            'cost' =>
                'Milliseconds. It reads a handful of files in /proc and does some ' .
                'arithmetic.',
            'recommended' => 300,
            'why' =>
                'Every chart here is drawn from these, and the resolution of every ' .
                'question you can ask later is decided by how often they were taken. ' .
                'Five minutes is the usual compromise; a minute is fine if the disk can ' .
                'take the rows.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 60,
        ],
        'scan' => [
            'side' => 'account',
            'label' => 'Disk walk',
            'what' =>
                'Walks the watched directory, recording the size of every path worth ' .
                'recording.',
            'cost' =>
                'Minutes on a large account, and it reads every inode under it. On a ' .
                'shared host this is the single most expensive thing here.',
            'recommended' => 86400,
            'why' =>
                'Daily, at a quiet hour. The numbers it produces move slowly - a ' .
                'directory that grew overnight is still the directory that grew when you ' .
                'look at lunchtime - and running it hourly buys almost nothing for a ' .
                'great deal of disk work.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'urls' => [
            'side' => 'account',
            'label' => 'Site checks',
            'what' =>
                'Fetches each configured address and records what came back.',
            'cost' =>
                'One HTTP request per check that is due. Each check has its own ' .
                'interval; this is only how often the collector looks for due ones.',
            'recommended' => 300,
            'why' =>
                'The check intervals do the real work. This only needs to run often ' .
                'enough not to delay them, so it matches the readings.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'domains' => [
            'side' => 'account',
            'label' => 'Domain expiry',
            'what' => 'Asks a registry when each watched domain expires.',
            'cost' =>
                'One whois lookup, to somebody else\'s server. Throttled to one per ' .
                'run whatever this says.',
            'recommended' => 86400,
            'why' =>
                'An expiry date changes once a year. Asking daily is already far more ' .
                'often than the answer moves, and registries rate-limit anything more ' .
                'eager.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'rdns' => [
            'side' => 'account',
            'label' => 'Address names',
            'what' => 'Resolves one waiting address to a name.',
            'cost' => 'One DNS query, to somebody else\'s resolver.',
            'recommended' => 300,
            'why' =>
                'The queue drains one per run, so this is really "how fast does the ' .
                'queue move". At five minutes a hundred addresses take eight hours, ' .
                'which is fine for something nobody is waiting on.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 60,
        ],
        'processes' => [
            'side' => 'account',
            'label' => 'Process samples',
            'what' =>
                'What is running and what it is using, grouped by program.',
            'cost' =>
                'Walks every process in /proc. Noticeable on a busy machine.',
            'recommended' => 0,
            'why' =>
                'Taken automatically when CPU or memory crosses a threshold, which is ' .
                'when it is worth having. On a schedule it mostly records an idle ' .
                'machine being idle, at the cost of walking /proc to find out.',
            'task' => null,
            'live_only' => true,
            'browse_boost' => 0,
        ],

        /* --------------------------------------------------- the host -- */

        'host-collect' => [
            'side' => 'host',
            'label' => 'Host readings',
            'what' =>
                'The machine as a whole: every filesystem, every tenant, memory, ' .
                'load, and the processes this account cannot see.',
            'cost' => 'Walks /proc as root. Seconds on a busy box.',
            'recommended' => 300,
            'why' =>
                'Everything on this dashboard about the machine rather than the ' .
                'account comes from here. Matching the account readings means the two ' .
                'can be read side by side without interpolating.',
            // Nothing to ask for: the host collector takes a reading every
            // run, so "do it now" is what the next run already is.
            'task' => null,
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'host-disk' => [
            'side' => 'host',
            'label' => 'Host disk walk',
            'what' =>
                'Every filesystem on the machine, the biggest directories on each, ' .
                'and files held open after deletion.',
            'cost' =>
                'Very expensive. It walks filesystems that may hold every account on ' .
                'the server.',
            'recommended' => 0,
            'why' =>
                'Not on a schedule. This is a thing to run when a disk is filling and ' .
                'nobody knows why, not something to do to a shared machine every hour. ' .
                'Ask for it when you need it.',
            'task' => 'request-host-walk',
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'host-capabilities' => [
            'side' => 'host',
            'label' => 'Capability probe',
            'what' =>
                'Which programs exist and what version each is - and, on the same ' .
                'pass, the kernel, PHP, OpenSSL and the rest.',
            'cost' =>
                'Runs a hundred or so programs with --version. Seconds, and a burst ' .
                'of process churn.',
            'recommended' => 86400,
            'why' =>
                'Daily. This is what notices a package update happening underneath ' .
                'you, and a day is fine for that - but every timestamp it records is ' .
                'when a change was noticed rather than when it happened, so a weekly ' .
                'probe turns a Tuesday upgrade into a Sunday event.',
            'task' => 'request-host-task:capabilities',
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'host-capture' => [
            'side' => 'host',
            'label' => 'Packet capture',
            'what' =>
                'Watches the wire for a few seconds and counts what went past.',
            'cost' =>
                'Holds a raw socket open and processes every packet. On a busy ' .
                'nameserver this is real work.',
            'recommended' => 0,
            'why' =>
                'Not on a schedule by default - but this is the one expensive scan ' .
                'where a schedule genuinely helps: two captures a day apart answer "is ' .
                'this getting worse", which one capture cannot. Turn it on while you ' .
                'are investigating something, and off again afterwards.',
            /*
             * Asked for from the capture page, not here.
             *
             * A capture needs an interface and a filter chosen, and a
             * button that started one on whatever the defaults happened to
             * be would produce captures nobody meant.
             */
            'task' => null,
            'goes_to' => 'net-capture',
            'live_only' => false,
            'browse_boost' => 0,
        ],
        'host-rdns' => [
            'side' => 'host',
            'label' => 'Address names (host)',
            'what' =>
                'Resolves one waiting address, for the cases where this account\'s ' .
                'resolver is blocked and the host\'s is not.',
            'cost' => 'One DNS query.',
            'recommended' => 300,
            'why' =>
                'Rides along with the host readings and costs nothing when the queue ' .
                'is empty, which is most of the time.',
            'task' => null,
            'live_only' => false,
            'browse_boost' => 0,
        ],
    ];
}

/**
 * The settings for one scan, with the operator's choices over the defaults.
 *
 * @return array<string, mixed>
 */
function scanSetting(array $config, string $key): array
{
    $catalogue = scanCatalogue();
    $entry = $catalogue[$key] ?? null;

    if ($entry === null) {
        throw new ActionRefused('there is no scan by that name');
    }

    $mine = $config['scans'][$key] ?? [];

    return $entry + [
        'key' => $key,
        'every' => array_key_exists('every', $mine)
            ? max(0, (int) $mine['every'])
            : (int) $entry['recommended'],
        /*
         * Whether to speed up while somebody is browsing.
         *
         * Off unless the catalogue suggests a boost for it: a disk walk that
         * ran faster because somebody opened a page would be actively harmful,
         * so the option is only offered where it makes sense.
         */
        'boost' => array_key_exists('boost', $mine)
            ? (bool) $mine['boost']
            : (int) $entry['browse_boost'] > 0,
        'live_only' => array_key_exists('live_only', $mine)
            ? (bool) $mine['live_only']
            : (bool) $entry['live_only'],
    ];
}

/** All of them, for a page to draw. */
function scanSettings(array $config, string $side = ''): array
{
    $out = [];

    foreach (scanCatalogue() as $key => $entry) {
        if ($side !== '' && $entry['side'] !== $side) {
            continue;
        }
        $out[] = scanSetting($config, $key);
    }

    return $out;
}

/**
 * How long ago somebody was last looking at the dashboard.
 *
 * Written by the shared poll every page makes, so it is a fact about the
 * browser rather than a guess from the web server's access log - which on a
 * host behind a CDN may not be readable and on any host includes crawlers.
 */
function lastSeenBrowsing(PDO $pdo): ?int
{
    try {
        $at = $pdo
            ->query("SELECT value FROM settings WHERE key = 'last_browsed_at'")
            ->fetchColumn();
    } catch (Throwable) {
        return null;
    }

    return $at === false || $at === null ? null : (int) $at;
}

function noteBrowsing(PDO $pdo): void
{
    try {
        $pdo->prepare(
            "INSERT INTO settings (key, value) VALUES ('last_browsed_at', ?)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value",
        )->execute([(string) time()]);
    } catch (Throwable) {
        // A missing settings table is not worth failing a page load over.
    }
}

/** Whether somebody is looking right now, for the purposes of the boost. */
function someoneIsBrowsing(PDO $pdo, int $within = 180): bool
{
    $at = lastSeenBrowsing($pdo);
    return $at !== null && time() - $at <= $within;
}

/**
 * Whether one scan is due.
 *
 * The single decision this whole file exists to make. Four things can stop it:
 * it is switched off, nobody is looking and it only runs when somebody is, it
 * ran recently enough, or - the interesting one - it ran recently enough for
 * its ordinary interval but somebody is browsing, in which case the shorter
 * interval applies instead.
 *
 * @return array{due: bool, why: string, next_at: ?int}
 */
function scanDue(array $config, PDO $pdo, string $key, ?int $lastAt): array
{
    $scan = scanSetting($config, $key);

    if ($scan['every'] <= 0) {
        return [
            'due' => false,
            'why' => 'not on a schedule',
            'next_at' => null,
        ];
    }

    if ($scan['live_only'] && !someoneIsBrowsing($pdo)) {
        return [
            'due' => false,
            'why' => 'only recorded while somebody is looking, and nobody is',
            'next_at' => null,
        ];
    }

    $every = $scan['every'];

    if ($scan['boost'] && someoneIsBrowsing($pdo)) {
        $faster = (int) ($scan['browse_boost'] ?: $every);
        if ($faster > 0 && $faster < $every) {
            $every = $faster;
        }
    }

    if ($lastAt === null) {
        return ['due' => true, 'why' => 'has never run', 'next_at' => null];
    }

    $next = $lastAt + $every;

    if (time() >= $next) {
        return ['due' => true, 'why' => 'due', 'next_at' => $next];
    }

    return [
        'due' => false,
        'why' => 'next in ' . plainSpan($next - time()),
        'next_at' => $next,
    ];
}

/**
 * Checks and normalises what a settings form sent.
 *
 * @return array<string, array<string, mixed>>
 */
function scanSettingsFrom(array $posted): array
{
    $intervals = scanIntervals();
    $out = [];

    foreach (scanCatalogue() as $key => $entry) {
        $field = 'every_' . str_replace('-', '_', $key);
        if (!array_key_exists($field, $posted)) {
            continue;
        }

        $every = (int) $posted[$field];
        if (!isset($intervals[$every])) {
            throw new ActionRefused(
                'that is not one of the intervals on offer',
            );
        }

        $mine = [];

        if ($every !== (int) $entry['recommended']) {
            $mine['every'] = $every;
        }

        $boost = in_array(
            $key,
            array_filter(explode(',', (string) ($posted['boosted'] ?? ''))),
            true,
        );
        if ($boost !== (int) $entry['browse_boost'] > 0) {
            $mine['boost'] = $boost;
        }

        $liveOnly = in_array(
            $key,
            array_filter(explode(',', (string) ($posted['live_only'] ?? ''))),
            true,
        );
        if ($liveOnly !== (bool) $entry['live_only']) {
            $mine['live_only'] = $liveOnly;
        }

        /*
         * Stored only where it differs from the recommendation.
         *
         * Writing every scan out with today's defaults would freeze them into
         * the config file, so a later change to what is recommended would never
         * reach an install that had once pressed Save.
         */
        if ($mine !== []) {
            $out[$key] = $mine;
        }
    }

    return $out;
}

/**
 * When one scan last produced something.
 *
 * Read from what the scan writes rather than from a record of runs: a
 * collector that started and crashed has not collected, and a "last run"
 * column would say it had. Each of these is a fixed query chosen by a key that
 * has already been checked against the catalogue.
 */
function scanLastRunFor(array $config, PDO $pdo, string $key): ?int
{
    $queries = [
        'vitals' => 'SELECT MAX(taken_at) FROM vitals',
        'scan' => 'SELECT MAX(finished_at) FROM scans',
        'urls' => 'SELECT MAX(at) FROM url_results',
        'domains' => 'SELECT MAX(last_checked_at) FROM domains',
        'rdns' => 'SELECT MAX(checked_at) FROM rdns',
        'processes' => 'SELECT MAX(taken_at) FROM process_samples',
    ];

    if (!isset($queries[$key])) {
        return null;
    }

    try {
        $at = $pdo->query($queries[$key])->fetchColumn();
        return $at === false || $at === null ? null : (int) $at;
    } catch (Throwable) {
        return null;
    }
}

/**
 * Whether a host-side scan is due, from the host's own database.
 *
 * Separate from scanDue because the host collector cannot use the account's
 * PDO for anything but reading: it runs as root, and a file it wrote would
 * change owner, after which the account could no longer write its own database.
 * So the last-run times come from the host database and the "is somebody
 * looking" question is answered by reading the account's settings table
 * read-only.
 *
 * @return array{due: bool, why: string}
 */
function hostScanDue(array $config, ?PDO $host, string $key): array
{
    $scan = scanSetting($config, $key);

    if ($scan['every'] <= 0) {
        return ['due' => false, 'why' => 'not on a schedule'];
    }

    if ($host === null) {
        return ['due' => false, 'why' => 'no host database to check against'];
    }

    $queries = [
        'host-collect' => 'SELECT MAX(taken_at) FROM host_samples',
        'host-disk' => 'SELECT MAX(taken_at) FROM host_disks',
        'host-capabilities' => 'SELECT MAX(checked_at) FROM host_capabilities',
        'host-capture' => 'SELECT MAX(taken_at) FROM host_captures',
    ];

    if (!isset($queries[$key])) {
        return ['due' => false, 'why' => 'nothing records this'];
    }

    try {
        $at = $host->query($queries[$key])->fetchColumn();
        $last = $at === false || $at === null ? null : (int) $at;
    } catch (Throwable) {
        return ['due' => false, 'why' => 'could not read when it last ran'];
    }

    if ($last === null) {
        return ['due' => true, 'why' => 'has never run'];
    }

    $every = $scan['every'];

    if ($scan['boost'] && hostSeesBrowsing($config)) {
        $faster = (int) ($scan['browse_boost'] ?: $every);
        if ($faster > 0 && $faster < $every) {
            $every = $faster;
        }
    }

    if ($scan['live_only'] && !hostSeesBrowsing($config)) {
        return [
            'due' => false,
            'why' => 'only while somebody is looking, and nobody is',
        ];
    }

    $next = $last + $every;

    return time() >= $next
        ? ['due' => true, 'why' => 'due']
        : ['due' => false, 'why' => 'next in ' . plainSpan($next - time())];
}

/** The browsing signal, read from the account database without writing to it. */
function hostSeesBrowsing(array $config, int $within = 180): bool
{
    $file = (string) ($config['database'] ?? '');
    if ($file === '' || !is_file($file) || !is_readable($file)) {
        return false;
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('PRAGMA query_only = 1');

        $at = $pdo
            ->query("SELECT value FROM settings WHERE key = 'last_browsed_at'")
            ->fetchColumn();
    } catch (Throwable) {
        return false;
    }

    return $at !== false && $at !== null && time() - (int) $at <= $within;
}
