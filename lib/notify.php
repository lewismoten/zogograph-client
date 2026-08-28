<?php
declare(strict_types=1);

/*
 * Who hears about what, how loudly, and what to do about it.
 *
 * Three things were wrong with the alert email before this file existed.
 *
 * It had two volumes - warning and critical - so a domain expiring in a month
 * shouted as loudly as a disk with an hour left. People learn very quickly that
 * an alert channel where everything is urgent contains nothing urgent.
 *
 * The cooldown was one setting for the whole system, so an alert about inodes
 * silenced the next alert about disk space for six hours. Those are different
 * problems that happen to arrive together, and suppressing the second because
 * the first went out is the single easiest way to miss the one that mattered.
 *
 * And the message told you what was wrong and then stopped. The most likely
 * moment for this dashboard to be unreachable is the moment the disk is full,
 * which is exactly when its email arrives - so the message now carries the
 * commands to diagnose the thing by hand, each with a note on what it does and
 * whether it needs root.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/spans.php';

/**
 * The four volumes, and what each is for.
 *
 * The distinction that matters is not how bad the situation is but what the
 * reader should do about it, which is why the notes are about behaviour rather
 * than about severity.
 */
function severityLevels(): array
{
    return [
        'info' => [
            'rank' => 1,
            'label' => 'Info',
            'note' => 'Worth knowing at some point. Nothing needs doing today.',
        ],
        'warning' => [
            'rank' => 2,
            'label' => 'Warning',
            'note' => 'Heading somewhere bad. Worth looking at this week.',
        ],
        'error' => [
            'rank' => 3,
            'label' => 'Error',
            'note' => 'Something is already broken, but the site is still up. Today.',
        ],
        'critical' => [
            'rank' => 4,
            'label' => 'Critical',
            'note' => 'Now. Either something is down or it is about to be.',
        ],
    ];
}

function severityRank(string $severity): int
{
    return severityLevels()[$severity]['rank'] ?? 2;
}

/** The loudest of a set of severities. */
function worstSeverity(array $severities): string
{
    $worst = 'info';
    foreach ($severities as $severity) {
        if (severityRank($severity) > severityRank($worst)) {
            $worst = $severity;
        }
    }
    return $worst;
}

/**
 * Every rule that can raise an alert, and how it should behave.
 *
 * `severity` and `cooldown` are the recommendations. The operator's own
 * settings win; the recommendation is shown beside each one so that a
 * deliberate choice is distinguishable from a default nobody looked at.
 *
 * `view` is where the dashboard shows the evidence, so the email can link to
 * the page that answers the question rather than to the front door.
 *
 * @return array<string, array<string, mixed>>
 */
function alertCatalogue(): array
{
    return [
        'disk_free_percent' => [
            'label' => 'Running out of disk space',
            'severity' => 'critical',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'storage',
            'why' => 'A full disk stops writes, which stops the database, the logs and '
                . 'usually the site.',
        ],
        'disk_free_bytes' => [
            'label' => 'Disk space below a fixed amount',
            'severity' => 'critical',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'storage',
            'why' => 'The same thing said in bytes, for when a percentage of a large disk '
                . 'is still not much room.',
        ],
        'inodes_free_percent' => [
            'label' => 'Running out of inodes',
            'severity' => 'critical',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'storage',
            'why' => 'A disk with free gigabytes and no inodes still refuses to create a '
                . 'file, and the error message never mentions inodes.',
        ],
        'cpu_sustained' => [
            'label' => 'CPU pinned for a while',
            'severity' => 'warning',
            'cooldown' => 2 * SPAN_HOUR,
            'view' => 'cpu',
            'why' => 'A brief spike is nothing; sustained is a runaway process or a '
                . 'crawler, and on a shared host it is what gets an account throttled.',
        ],
        'memory_percent' => [
            'label' => 'Memory nearly gone',
            'severity' => 'critical',
            'cooldown' => 2 * SPAN_HOUR,
            'view' => 'host',
            'why' => 'What happens next is the OOM killer choosing something, and it will '
                . 'not choose what you would have.',
        ],
        'projected_full' => [
            'label' => 'On course to run out',
            'severity' => 'warning',
            'cooldown' => SPAN_DAY,
            'view' => 'storage-files',
            'why' => 'From the measured growth rate. This is the one that gives you a week '
                . 'of notice rather than an hour.',
        ],
        'path_jump' => [
            'label' => 'Something grew suddenly',
            'severity' => 'warning',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'storage-files',
            'why' => 'Usually a log that started repeating itself.',
        ],
        'category_jump' => [
            'label' => 'A whole category grew suddenly',
            'severity' => 'warning',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'storage',
            'why' => 'Backups, caches or logs as a group, rather than one file.',
        ],
        'domain_expiry' => [
            'label' => 'A domain is expiring',
            'severity' => 'warning',
            'cooldown' => SPAN_DAY,
            'view' => 'domains',
            'why' => 'Nothing else here can see this coming: every server measure stays '
                . 'green until the name stops resolving, and then everything fails at once '
                . 'for a reason that is in no log.',
        ],
        'event_due' => [
            'label' => 'Something on the calendar is due',
            'severity' => 'info',
            'cooldown' => SPAN_DAY,
            'view' => 'calendar',
            'why' => 'A renewal or a deadline somebody wrote down.',
        ],
        'url_value_changed' => [
            'label' => 'A watched value changed',
            'severity' => 'info',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'net-urls',
            'why' => 'Usually an automatic update. Worth knowing when something starts '
                . 'behaving differently an hour later.',
        ],
        'url_down' => [
            'label' => 'A site stopped answering',
            'severity' => 'critical',
            'cooldown' => SPAN_HOUR,
            'view' => 'net-urls',
            'why' => 'The only check here made from outside the machine. Every internal '
                . 'measure can be green while the site returns 500.',
        ],
        'symlink_new' => [
            'label' => 'A new symlink problem appeared',
            'severity' => 'warning',
            'cooldown' => SPAN_DAY,
            'view' => 'storage-links',
            'why' => 'A loop that appeared today is a deploy that went wrong. One that has '
                . 'been there two years is a job for a rainy afternoon.',
        ],
        'anomaly' => [
            'label' => 'A reading is unlike itself',
            'severity' => 'info',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'charts',
            'why' => 'Compared against this machine\'s own history rather than a threshold '
                . 'somebody guessed.',
        ],
        'encryption_recovery' => [
            'label' => 'Encrypted-copy recovery needs attention',
            'severity' => 'warning',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'sys-encryption',
            'why' => 'A sealed copy that is missing, stale, or failed verification is not
                reliable recovery evidence when it is needed.',
        ],
        'setup_configuration_encryption' => [
            'label' => 'Setup configuration encryption needs attention',
            'severity' => 'warning',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'sys-encryption',
            'why' => 'A configured local key should protect the stored account, provider, and package policy settings too.',
        ],
        'package_integrity' => [
            'label' => 'Installed package integrity needs attention',
            'severity' => 'error',
            'cooldown' => 6 * SPAN_HOUR,
            'view' => 'sys-packages',
            'why' => 'A signed installed package has changed, is missing a declared file, '
                . 'or contains an unexpected file. Zogograph does not execute it while the mismatch remains.',
        ],
    ];
}

/**
 * The settings for one rule, with the operator's choices layered over the
 * recommendations.
 *
 * A rule name can carry a suffix - `projected_full:disk_free_bytes` - so that
 * each variant gets its own cooldown. The base name is what the catalogue and
 * the settings are keyed on.
 */
function alertRule(array $config, string $rule): array
{
    $base = explode(':', $rule, 2)[0];
    $catalogue = alertCatalogue();

    $entry = $catalogue[$base] ?? [
        'label' => $base,
        'severity' => 'warning',
        'cooldown' => (int)($config['alerts']['cooldown_seconds'] ?? 6 * SPAN_HOUR),
        'view' => 'alerts',
        'why' => '',
    ];

    $mine = $config['alerts']['rules'][$base] ?? [];

    return [
        'key' => $base,
        'label' => $entry['label'],
        'view' => $entry['view'],
        'why' => $entry['why'],
        'recommended_severity' => $entry['severity'],
        'recommended_cooldown' => $entry['cooldown'],
        'severity' => isset($mine['severity']) && isset(severityLevels()[$mine['severity']])
            ? (string)$mine['severity']
            : $entry['severity'],
        /*
         * Zero is a real answer meaning "every time".
         *
         * Which somebody genuinely wants for a site that stopped answering, so
         * it cannot be treated as "unset, use the default" - the check is for
         * the key being present rather than for the value being truthy.
         */
        'cooldown' => array_key_exists('cooldown', $mine)
            ? max(0, (int)$mine['cooldown'])
            : (int)$entry['cooldown'],
    ];
}

/**
 * Where to send it, as a list.
 *
 * Semicolons, because that is what mail clients use and therefore what people
 * type. Commas are accepted too - they are what RFC 5322 uses, and somebody
 * will type one.
 *
 * @return list<string>
 */
function alertRecipients(string $field): array
{
    $out = [];

    foreach (preg_split('/[;,]/', $field) ?: [] as $one) {
        $one = trim($one);
        if ($one !== '' && filter_var($one, FILTER_VALIDATE_EMAIL)) {
            $out[] = $one;
        }
    }

    return array_values(array_unique($out));
}

/**
 * Addresses in the field that are not usable, so the settings page can say so.
 *
 * Reported rather than silently dropped: a typo in one of four addresses means
 * one person stops being told, and nothing anywhere would have mentioned it.
 *
 * @return list<string>
 */
function badRecipients(string $field): array
{
    $out = [];

    foreach (preg_split('/[;,]/', $field) ?: [] as $one) {
        $one = trim($one);
        if ($one !== '' && !filter_var($one, FILTER_VALIDATE_EMAIL)) {
            $out[] = $one;
        }
    }

    return $out;
}

/**
 * How urgent a domain's expiry is, and whether to say so again today.
 *
 * Four steps rather than a threshold, because the right reaction genuinely
 * changes: a month out is a note to self, a week out is this week's job, two
 * days out is today's, and expired is an outage that has already started and
 * gets more expensive every day it lasts.
 *
 * @return array{severity: string, when: string, repeat_days: int}|null
 */
function domainUrgency(int $daysLeft): ?array
{
    if ($daysLeft < 0) {
        return [
            'severity' => 'critical',
            'when' => sprintf('expired %s ago', plainSpan(abs($daysLeft) * SPAN_DAY)),
            // Every day, for as long as it stays recoverable. A domain in the
            // redemption period can still be saved, and expensively.
            'repeat_days' => 30,
        ];
    }
    if ($daysLeft <= 2) {
        return ['severity' => 'error', 'when' => daysPhrase($daysLeft), 'repeat_days' => 3];
    }
    if ($daysLeft <= 7) {
        return ['severity' => 'warning', 'when' => daysPhrase($daysLeft), 'repeat_days' => 7];
    }
    if ($daysLeft <= 30) {
        return ['severity' => 'info', 'when' => daysPhrase($daysLeft), 'repeat_days' => 30];
    }

    return null;
}

function daysPhrase(int $days): string
{
    if ($days === 0) {
        return 'expires today';
    }
    return $days === 1 ? 'expires tomorrow' : sprintf('expires in %d days', $days);
}

/* ------------------------------------------------- what to do about it -- */

/**
 * Commands to diagnose one alert by hand.
 *
 * The most likely moment for this dashboard to be unreachable is the moment
 * the disk is full - which is exactly when its email arrives. So the message
 * carries the commands to find the same answers from a shell, and each one
 * says what it does and why, because a block of shell nobody understands gets
 * pasted without reading or not at all.
 *
 * Three things every entry carries:
 *
 *   who   `account` or `root`. Half of these need privileges the account does
 *         not have, and finding that out by running them is a waste of the
 *         three minutes somebody has.
 *   why   what it will tell you. Not what it does - `df -h` is self-evident -
 *         but what you are looking for in the output.
 *   when  null for "run this", or a condition for "run this if that".
 *
 * Values are filled in from the alert, so a message about a runaway process
 * carries that process's id rather than a placeholder.
 *
 * @return list<array{who: string, why: string, when: ?string, command: string}>
 */
function diagnosisFor(array $alert, array $config, array $facts = []): array
{
    $rule = explode(':', (string)($alert['rule'] ?? ''), 2)[0];
    $root = (string)($config['scan_root'] ?? '~');
    $out = [];

    $add = function (string $who, string $why, string $command, ?string $when = null) use (&$out): void {
        $out[] = ['who' => $who, 'why' => $why, 'command' => $command, 'when' => $when];
    };

    switch ($rule) {
        case 'disk_free_percent':
        case 'disk_free_bytes':
        case 'projected_full':
            $add('account',
                'How much room is actually left, and on which filesystem. The number in '
                . 'this email is for the one your account writes to; a machine can be '
                . 'fine on one and full on another.',
                'df -h ' . escapeshellarg($root));
            $add('account',
                'The twenty largest directories under your account, worst first. This is '
                . 'usually enough on its own.',
                'du -x -h -d 3 ' . escapeshellarg($root) . ' 2>/dev/null | sort -rh | head -20');
            $add('account',
                'Files over 100 MB, newest first. A single huge log is the most common '
                . 'answer and this finds it in one pass.',
                'find ' . escapeshellarg($root) . ' -xdev -type f -size +100M -printf '
                . '\'%TY-%Tm-%Td %10s %p\n\' 2>/dev/null | sort -r | head -20');
            $add('account',
                'Space held by files that have been deleted but are still open. The disk '
                . 'stays full and nothing on it is large, which is the confusing case.',
                'lsof -nP +L1 2>/dev/null | head -20',
                'df says full but du cannot find it');
            $add('root',
                'The same question for the whole machine, including the parts your account '
                . 'cannot see.',
                'du -x -h -d 2 / 2>/dev/null | sort -rh | head -30');
            break;

        case 'inodes_free_percent':
            $add('account',
                'Inodes free, which is a different limit from bytes free. A disk with '
                . 'gigabytes spare and no inodes still refuses to create a file, and the '
                . 'error never mentions inodes.',
                'df -i ' . escapeshellarg($root));
            $add('account',
                'Which directories hold the most *files*, rather than the most bytes. '
                . 'Nearly always a session, cache or mail queue directory.',
                'find ' . escapeshellarg($root) . ' -xdev -type d -printf \'%p\n\' 2>/dev/null '
                . '| while read -r d; do echo "$(ls -A "$d" 2>/dev/null | wc -l) $d"; done '
                . '| sort -rn | head -20');
            break;

        case 'cpu_sustained':
            $pid = (int)($facts['pid'] ?? 0);
            $add('account',
                'What is running right now, busiest first. Look for something that should '
                . 'have finished.',
                'ps -eo pid,ppid,user,%cpu,%mem,etime,cmd --sort=-%cpu | head -15');

            if ($pid > 0) {
                $add('account',
                    'Everything about the process this alert named, including how long it '
                    . 'has been running and what started it.',
                    sprintf('ps -o pid,ppid,user,%%cpu,%%mem,etime,lstart,cmd -p %d', $pid));
                $add('account',
                    'What that process is actually doing, second by second. Ctrl-C to stop '
                    . 'watching. A tight loop of the same call is a stuck process.',
                    sprintf('strace -p %d -f -e trace=file,network 2>&1 | head -40', $pid),
                    'you need to know what it is stuck on');
                $add('account',
                    'Stop it. Politely first - a process killed with -9 cannot clean up '
                    . 'after itself, and a half-written file is worse than a busy one.',
                    sprintf('kill %d   # then, only if it is still there a minute later: '
                        . 'kill -9 %d', $pid, $pid),
                    'you have decided it should not be running');
            }

            $add('account',
                'Load average against the core count. Above one job per core, work is '
                . 'queueing rather than running.',
                'uptime; nproc');
            $add('root',
                'Whether the account is being throttled rather than merely busy. Inside '
                . 'CageFS an account can sit at its ceiling while the machine is idle.',
                'lveps -o id,ncpu,cpu,mem,io 2>/dev/null | head -20');
            break;

        case 'memory_percent':
            $add('account',
                'Memory in use, and how much is genuinely available rather than merely '
                . 'unused. The "available" column is the one that matters; "free" being '
                . 'small is normal and healthy.',
                'free -h');
            $add('account',
                'The biggest processes by memory. The OOM killer will choose from near the '
                . 'top of this list, and it will not choose what you would have.',
                'ps -eo pid,user,%mem,rss,cmd --sort=-%mem | head -15');
            $add('root',
                'Whether the kernel has already killed something. This is where an '
                . '"it just disappeared" goes.',
                'dmesg -T 2>/dev/null | grep -i -E "out of memory|oom-killer|killed process" '
                . '| tail -20',
                'a process vanished with no error anywhere');
            break;

        case 'path_jump':
        case 'category_jump':
            $path = (string)($facts['path'] ?? '');
            if ($path !== '') {
                $add('account',
                    'How large it is now, and when it last changed.',
                    'ls -lh ' . escapeshellarg($path));
                $add('account',
                    'The last forty lines. If the same line repeats, that is the whole '
                    . 'answer - find what is producing it rather than deleting the file.',
                    'tail -40 ' . escapeshellarg($path));
                $add('account',
                    'The most repeated lines, worst first. A log growing by gigabytes is '
                    . 'almost always one message in a loop.',
                    'tail -50000 ' . escapeshellarg($path) . ' | sed -E '
                    . '\'s/[0-9]{1,4}([-\/:][0-9]{1,4}){1,5}//g; s/[0-9]+/N/g\' '
                    . '| sort | uniq -c | sort -rn | head -10');
                $add('account',
                    'Empty it without deleting it. Truncating keeps the file handle valid, '
                    . 'where deleting leaves the writer appending to a file that no longer '
                    . 'has a name and the space never comes back.',
                    ': > ' . escapeshellarg($path),
                    'you have found the cause and want the space back now');
            }
            break;

        case 'domain_expiry':
            $domain = (string)($facts['domain'] ?? '');
            if ($domain !== '') {
                $add('account',
                    'What the registry says, including the real expiry date and whether the '
                    . 'domain is locked.',
                    'whois ' . escapeshellarg($domain) . ' | grep -i -E '
                    . '"expir|status|registrar"');
                $add('account',
                    'Whether it still resolves. A domain can be expired and still resolving '
                    . 'for a few days, which is the window you have.',
                    'dig +short NS ' . escapeshellarg($domain) . '; dig +short A '
                    . escapeshellarg($domain));
            }
            break;

        case 'url_down':
            $url = (string)($facts['url'] ?? '');
            if ($url !== '') {
                $add('account',
                    'What the server actually returns, headers and all, without a browser '
                    . 'in the way.',
                    'curl -sS -o /dev/null -D - --max-time 20 ' . escapeshellarg($url));
                $add('account',
                    'Where the time goes. A slow DNS lookup and a slow server look the same '
                    . 'from a browser and need completely different fixes.',
                    'curl -sS -o /dev/null -w '
                    . '\'dns %{time_namelookup}s  connect %{time_connect}s  '
                    . 'tls %{time_appconnect}s  first byte %{time_starttransfer}s  '
                    . 'total %{time_total}s\n\' --max-time 20 ' . escapeshellarg($url));
                $add('account',
                    'The web server\'s own account of the same request.',
                    'tail -40 ' . escapeshellarg($root . '/logs/error_log') . ' 2>/dev/null '
                    . '|| tail -40 ~/logs/*error* 2>/dev/null');
            }
            break;

        case 'symlink_new':
            $add('account',
                'Every broken symlink under your account. Harmless individually; a backup '
                . 'that follows them is not.',
                'find ' . escapeshellarg($root) . ' -xdev -xtype l -print 2>/dev/null | head -40');
            $add('account',
                'Links pointing at one of their own ancestors, which is an infinitely deep '
                . 'directory to anything that follows links.',
                'find ' . escapeshellarg($root) . ' -xdev -type l -printf \'%p -> %l\n\' '
                . '2>/dev/null | awk \'$3 == ".." || $3 == "." \'');
            break;

        default:
            $add('account',
                'The current state of the things this dashboard watches, from the shell.',
                'df -h; free -h; uptime');
            break;
    }

    return $out;
}

/**
 * The address of a page on this install, for linking out of an email.
 *
 * Built from the configured base rather than from $_SERVER, because the cron
 * that sends these has no request to read one from - and a link to
 * "http://localhost/#/storage" helps nobody.
 */
function dashboardLink(array $config, string $view = '', array $params = []): ?string
{
    // Under `alerts` where the rest of the email settings are, with the older
    // top-level spelling still honoured.
    $base = trim((string)($config['alerts']['site_url'] ?? $config['site_url'] ?? ''));
    if ($base === '') {
        return null;
    }

    $base = rtrim($base, '/');
    if ($view === '') {
        return $base;
    }

    $query = http_build_query($params);
    return $base . '/#/' . $view . ($query === '' ? '' : '?' . $query);
}

/**
 * The one command that belongs at the end of every message.
 *
 * If the dashboard itself is the thing that is broken, this is where its own
 * account of the failure is - and it is the file nobody thinks to look at.
 * Appended once by the message rather than by each alert, or it lands in the
 * middle of the list wherever the first alert happened to put it.
 *
 * @return array{who: string, why: string, when: ?string, command: string}
 */
function selfDiagnosis(array $config): array
{
    return [
        'who' => 'account',
        'why' => 'This dashboard\'s own error log, if the site itself is misbehaving.',
        'when' => 'Zogograph itself is not loading',
        'command' => 'tail -40 '
            . escapeshellarg(dirname((string)($config['database'] ?? '')) . '/error.log'),
    ];
}
