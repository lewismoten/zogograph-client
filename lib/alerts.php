<?php
declare(strict_types=1);
require_once __DIR__ . '/setupstore.php';

/*
 * Noticing, and saying so.
 *
 * The reason this project exists is running out of disk without knowing, so the
 * alerting is not an extra: a monitor you have to remember to look at is a
 * monitor that tells you after the fact.
 *
 * Two kinds of rule. Thresholds ("less than five gigabytes left") catch the
 * state you must not be in. Jumps ("this directory gained eight gigabytes since
 * the last scan") catch the thing that is going to put you there, which is the
 * one worth having - a threshold tells you when it is nearly too late, and a
 * jump tells you which log file to go and look at.
 *
 * Every rule is rate limited per rule, and per path where a path is involved.
 * An alert that arrives every minute becomes a filter rule within a day, and
 * then it is not an alert.
 */

require_once __DIR__ . '/notify.php';
require_once __DIR__ . '/aialerts.php';
require_once __DIR__ . '/domains.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/anomalies.php';
require_once __DIR__ . '/incident.php';
require_once __DIR__ . '/logwatch.php';
// logAction lives here: an email that went out is something the monitor did.
require_once __DIR__ . '/actions.php';
// Domains and dated events, which raise alerts of their own.
require_once __DIR__ . '/events.php';
// Update notifications read the root collector's ledger; these are optional
// databases/modules, but loading them here keeps evaluateAlerts self-contained.
require_once __DIR__ . '/hostdb.php';
require_once __DIR__ . '/updates.php';
require_once __DIR__ . '/encryption.php';
require_once __DIR__ . '/package-runtime.php';

/**
 * How far back a projection looks.
 *
 * Shared between the alert rules and the dashboard card on purpose: two
 * projections of the same disk that disagree because they measured different
 * windows is worse than either being absent, and there is no reason for them to
 * differ.
 */
const TREND_WINDOW_SECONDS = 86400;

/**
 * Decides whether a non-urgent notification falls inside configured working
 * hours. This is deliberately pure: queueing and transport must agree on the
 * exact same timezone/DST decision.
 *
 * @return array{disposition:'immediate'|'delayed',reason:?string}
 */
function alertWorkingHoursDecision(
    array $delivery,
    string $severity,
    int $at,
): array {
    if (
        in_array($severity, ['error', 'critical'], true) ||
        empty($delivery['enabled'])
    ) {
        return ['disposition' => 'immediate', 'reason' => null];
    }
    $zone = (string) ($delivery['timezone'] ?? 'UTC');
    try {
        $instant = new DateTimeImmutable('@' . $at);
        $time = $instant->setTimezone(new DateTimeZone($zone));
    } catch (Throwable) {
        return ['disposition' => 'immediate', 'reason' => null];
    }
    $days = array_values(
        array_filter(
            array_map('intval', (array) ($delivery['days'] ?? [1, 2, 3, 4, 5])),
            fn(int $d) => $d >= 1 && $d <= 7,
        ),
    );
    $start = preg_match(
        '/^\d\d:\d\d$/',
        (string) ($delivery['start'] ?? '09:00'),
    )
        ? (string) $delivery['start']
        : '09:00';
    $end = preg_match('/^\d\d:\d\d$/', (string) ($delivery['end'] ?? '17:00'))
        ? (string) $delivery['end']
        : '17:00';
    $clock = $time->format('H:i');
    $inside =
        in_array((int) $time->format('N'), $days, true) &&
        ($start <= $end
            ? $clock >= $start && $clock < $end
            : $clock >= $start || $clock < $end);
    return $inside
        ? ['disposition' => 'immediate', 'reason' => null]
        : [
            'disposition' => 'delayed',
            'reason' =>
                'Outside configured working hours (' .
                $time->format('T') .
                ').',
        ];
}

/** Records one deferred alert idempotently; the original alert remains evidence. */
function queueAlertDelivery(
    PDO $pdo,
    int $alertId,
    int $dueAt,
    string $reason,
    ?int $now = null,
): void {
    $now ??= time();
    $pdo->prepare(
        'INSERT OR IGNORE INTO alert_delivery_queue (alert_id, queued_at, due_at, reason) VALUES (?, ?, ?, ?)',
    )->execute([$alertId, $now, max($now, $dueAt), mb_substr($reason, 0, 240)]);
}

/** @return list<array{alert_id:int,due_at:int,reason:string}> */
function dueAlertDeliveries(PDO $pdo, ?int $now = null, int $limit = 100): array
{
    $stmt = $pdo->prepare(
        'SELECT alert_id, due_at, reason FROM alert_delivery_queue WHERE due_at <= ? ORDER BY due_at, alert_id LIMIT ?',
    );
    $stmt->bindValue(1, $now ?? time(), PDO::PARAM_INT);
    $stmt->bindValue(2, max(1, min(100, $limit)), PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Change the read state of named alerts without touching their evidence.
 *
 * Keeping this separate from the request handler makes the important part of
 * acknowledgement testable: a mistaken click is reversible, while deletion
 * remains the only operation that removes history.
 *
 * @param list<int> $ids
 */
function setAlertsAcknowledged(PDO $pdo, array $ids, bool $acknowledged): int
{
    $ids = array_values(
        array_unique(
            array_filter(
                array_map('intval', $ids),
                fn(int $id): bool => $id > 0,
            ),
        ),
    );
    if ($ids === []) {
        return 0;
    }

    $marks = implode(',', array_fill(0, count($ids), '?'));
    if ($acknowledged) {
        $stmt = $pdo->prepare(
            "UPDATE alerts SET acknowledged_at = ? WHERE acknowledged_at IS NULL AND id IN ($marks)",
        );
        $stmt->execute([time(), ...$ids]);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE alerts SET acknowledged_at = NULL WHERE acknowledged_at IS NOT NULL AND id IN ($marks)",
        );
        $stmt->execute($ids);
    }

    return $stmt->rowCount();
}

/** An expiring operator mute. Error and Critical alerts are never eligible. */
function alertActiveMute(
    PDO $pdo,
    string $rule,
    string $severity,
    ?int $now = null,
): ?array {
    if (in_array($severity, ['error', 'critical'], true)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, rule, expires_at, reason, created_by
        FROM alert_mutes WHERE rule = ? AND expires_at > ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$rule, $now ?? time()]);
    return $stmt->fetch() ?: null;
}

/**
 * Records operator suppression on each event before delivery is selected.
 *
 * @param list<array> $raised
 * @return list<array>
 */
function applyAlertMutes(PDO $pdo, array $raised): array
{
    $update = $pdo->prepare("UPDATE alerts SET notification_disposition = 'suppressed',
        notification_reason = ?, notification_muted_until = ? WHERE id = ?");
    foreach ($raised as $i => $alert) {
        $mute = alertActiveMute(
            $pdo,
            (string) $alert['rule'],
            (string) $alert['severity'],
        );
        if ($mute === null) {
            continue;
        }
        $reason =
            'Muted until ' .
            gmdate('c', (int) $mute['expires_at']) .
            ': ' .
            (string) $mute['reason'];
        $update->execute([
            $reason,
            (int) $mute['expires_at'],
            (int) $alert['id'],
        ]);
        $raised[$i]['notification_disposition'] = 'suppressed';
        $raised[$i]['notification_reason'] = $reason;
        $raised[$i]['notification_muted_until'] = (int) $mute['expires_at'];
    }
    return $raised;
}

/** @return list<array{id:int,rule:string,created_at:int,expires_at:int,reason:string,created_by:?string}> */
function alertMutes(PDO $pdo, ?int $now = null): array
{
    $stmt = $pdo->prepare('SELECT id, rule, created_at, expires_at, reason, created_by
        FROM alert_mutes WHERE expires_at > ? ORDER BY expires_at, rule');
    $stmt->execute([$now ?? time()]);
    return $stmt->fetchAll();
}

/** Creates a bounded mute only for a configured rule name. */
function alertMute(
    PDO $pdo,
    string $rule,
    int $seconds,
    string $reason,
    string $who,
): int {
    if (!isset(alertCatalogue()[$rule])) {
        throw new ActionRefused('that alert rule is not available');
    }
    $reason = trim(preg_replace('/\s+/', ' ', $reason) ?? '');
    if ($reason === '') {
        throw new ActionRefused('say why this alert is being muted');
    }
    $seconds = max(900, min(7 * 86400, $seconds));
    $pdo->prepare(
        'INSERT INTO alert_mutes (rule, created_at, expires_at, reason, created_by)
        VALUES (?, ?, ?, ?, ?)',
    )->execute([
        $rule,
        time(),
        time() + $seconds,
        mb_substr($reason, 0, 240),
        $who,
    ]);
    return (int) $pdo->lastInsertId();
}

/** Expires a mute rather than deleting its operator evidence. */
function alertUnmute(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT rule, reason FROM alert_mutes WHERE id = ?');
    $stmt->execute([$id]);
    $mute = $stmt->fetch();
    if ($mute === false) {
        return null;
    }
    $pdo->prepare(
        'UPDATE alert_mutes SET expires_at = ? WHERE id = ?',
    )->execute([time(), $id]);
    return $mute;
}

function evaluateAlerts(array $config, PDO $pdo): array
{
    $rules = $config['alerts'] ?? [];
    if (!($rules['enabled'] ?? false)) {
        return [];
    }

    $retries = retryAlertEmails($pdo);
    if ($retries['attempted'] > 0) {
        logAction(
            $pdo,
            'alert_email_retry',
            null,
            $retries['failed'] === 0 ? 'ok' : 'failed',
            0,
            0,
            sprintf(
                '%d retried, %d still refused',
                $retries['sent'],
                $retries['failed'],
            ),
        );
    }

    $raised = [];

    /*
     * Thresholds first, then the baseline rules, then the overlap removed.
     *
     * The two kinds genuinely do describe the same event sometimes, and the
     * anomaly is the better of the two descriptions. See supersedeThresholds().
     */
    $candidates = supersedeThresholds([
        ...diskAlerts($pdo, $rules),
        ...trendAlerts($pdo, $rules),
        ...cpuAlerts($pdo, $rules),
        ...categoryAlerts($pdo, $rules),
        ...pathAlerts($pdo, $rules),
        /*
         * The baseline rules, which need the whole config rather than the
         * alert rules alone - some of them read the host database, and
         * where that lives is configured outside this section.
         */
        ...detectAnomalies($config, $pdo),
        /*
         * The log rules, which know things about logs that no general rule
         * can: that they rotate, that a .bkup beside one is transient, and
         * that two names for one file is one file.
         */
        ...logAlerts($pdo, $config),
        /*
         * Dates, which no threshold can detect.
         *
         * A domain expiring on Friday is invisible to every measurement on this
         * machine right up to the moment it takes the site down, and a calendar
         * page nobody opened is not a warning.
         */
        ...dateAlerts($config, $pdo),
        ...updateAlerts($config),
        ...setupConfigurationEncryptionAlerts($config),
        ...encryptionRecoveryAlerts($config, $pdo),
        ...packageRuntimeIntegrityAlerts($pdo),
    ]);

    foreach ($candidates as $alert) {
        $id = recordAlert($pdo, $rules, $alert);
        if ($id !== null) {
            $raised[] = $alert + ['id' => $id];
        }
    }

    if ($raised) {
        /*
         * The snapshot is taken here, after the cooldown has had its say.
         *
         * Not before: most evaluations raise nothing, and running `ps` on every
         * one to photograph a machine that is behaving would be a cost with no
         * reader. Not later either - by the time the mail is opened the runaway
         * has been reaped and there is nothing left to photograph.
         *
         * One snapshot for all of them, linked back afterwards, because seven
         * rules firing together are seven views of one machine.
         */
        $incident = captureIncident($config, $pdo, $raised);
        if ($incident !== null) {
            $ids = array_map(intval(...), array_column($raised, 'id'));
            $pdo->prepare(
                'UPDATE alerts SET incident_id = ? WHERE id IN (' .
                    implode(',', array_fill(0, count($ids), '?')) .
                    ')',
            )->execute([$incident, ...$ids]);

            $raised = array_map(
                fn(array $alert): array => $alert + [
                    'incident_id' => $incident,
                ],
                $raised,
            );
        }

        /*
         * The assistant's turn, before anything is sent.
         *
         * It may add a note, and - if configured to - conclude that one of
         * these did not warrant waking anybody. A silenced alert is still
         * raised, still unread and still listed; it simply does not go in the
         * message, and its reasoning is stored beside it.
         *
         * Failing here never stops an alert. Every path through aiTriageAlerts
         * that cannot reach a model, cannot parse an answer, or runs out of
         * budget leaves the alert exactly as it was.
         */
        $raised = aiTriageAlerts($config, $pdo, $raised);

        $raised = applyAlertMutes($pdo, $raised);

        $worthSending = array_values(
            array_filter(
                $raised,
                fn(array $alert): bool => empty($alert['silenced']) &&
                    ($alert['notification_disposition'] ?? 'immediate') !==
                        'suppressed',
            ),
        );

        if ($worthSending !== []) {
            sendAlertEmail($config, $pdo, $worthSending);
            sendTelegramAlerts($config, $pdo, $worthSending);
        }
    }

    return $raised;
}

/** A configured local key should also protect the sensitive setup record. */
function setupConfigurationEncryptionAlerts(array $config): array
{
    $encryption = (array) ($config['encryption'] ?? []);
    $key = trim((string) ($encryption['local_key_file'] ?? ''));
    if ($key === '') {
        return [];
    }
    $status = setupStoreConfigurationProtectionForSettings(
        $config,
        dirname(__DIR__),
    );
    if ($status['state'] === 'ok') {
        return [];
    }
    return [
        [
            'rule' => 'setup_configuration_encryption',
            'severity' => $status['state'] === 'error' ? 'error' : 'warning',
            'subject' => 'Setup configuration encryption needs attention',
            'detail' => $status['detail'],
        ],
    ];
}

/** Emits one evidence-based alert for a configured portable encryption policy.
 * It intentionally says nothing for protection modes Zogograph cannot verify. */
function encryptionRecoveryAlerts(array $config, PDO $pdo): array
{
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? 'none') !== 'sealed_copies_local') {
        return [];
    }
    $stores = encryptionSelectedStores($settings);
    if ($stores === []) {
        return [];
    }
    $paths = encryptionStorePaths($config, dirname(__DIR__));
    try {
        $rows = $pdo
            ->query(
                "SELECT store, MAX(created_at) AS created_at, MAX(last_recovery_drill_at) AS last_recovery_drill_at
            FROM encryption_artifacts WHERE state = 'verified' GROUP BY store",
            )
            ->fetchAll();
        $failed = $pdo
            ->query(
                "SELECT store FROM encryption_artifacts WHERE state = 'verification_failed' GROUP BY store",
            )
            ->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable) {
        // An older database has no ledger to assess. Do not turn an upgrade into
        // a false encryption incident; the Protection page still explains it.
        return [];
    }
    $latest = [];
    $drilled = [];
    foreach ($rows as $row) {
        $latest[(string) $row['store']] = (int) $row['created_at'];
        $drilled[(string) $row['store']] = (int) $row['last_recovery_drill_at'];
    }
    $problems = [];
    try {
        encryptionReadLocalKey(
            (string) ($settings['local_key_file'] ?? ''),
            dirname(__DIR__),
        );
    } catch (Throwable) {
        $problems[] = 'The private local key is unavailable to Zogograph.';
    }
    foreach ($failed as $store) {
        $problems[] = "A retained encrypted copy for {$store} failed verification.";
    }
    $maximumAge = encryptionScheduleMaximumAge(
        (string) ($settings['backup_schedule'] ?? 'manual'),
    );
    foreach ($stores as $store) {
        if (
            in_array($store, ['host', 'ai'], true) &&
            !is_file($paths[$store])
        ) {
            continue;
        }
        if (!isset($latest[$store])) {
            $problems[] = "No verified encrypted copy exists for {$store}.";
            continue;
        }
        if (($drilled[$store] ?? 0) <= 0) {
            $problems[] = "No successful non-destructive recovery drill has been recorded yet for {$store}.";
        } elseif (
            ($maximumDrillAge = encryptionRecoveryDrillMaximumAge(
                $settings,
            )) !== null &&
            $drilled[$store] < time() - $maximumDrillAge
        ) {
            $problems[] = "The latest non-destructive recovery drill for {$store} is older than the selected review period.";
        }
        if ($maximumAge !== null && $latest[$store] < time() - $maximumAge) {
            $problems[] = "The newest verified encrypted copy for {$store} is older than the selected schedule.";
        }
    }
    if ($problems === []) {
        return [];
    }
    $critical =
        $failed !== [] ||
        str_starts_with($problems[0], 'The private local key');
    return [
        [
            'rule' => 'encryption_recovery',
            'severity' => $critical ? 'error' : 'warning',
            'subject' => 'Encrypted-copy recovery needs attention',
            'detail' => implode("\n", $problems),
        ],
    ];
}

/** Reports an installed-package hash or manifest mismatch without loading its
 * code. A mismatch can indicate compromise, so this is an error alert. */
function packageRuntimeIntegrityAlerts(PDO $pdo): array
{
    try {
        $packages = packageRuntimePackages($pdo);
    } catch (Throwable) {
        return [];
    } // Package tables may not exist during an upgrade.
    $failed = array_values(
        array_filter(
            $packages,
            fn(array $package): bool => !$package['integrity'],
        ),
    );
    if ($failed === []) {
        return [];
    }
    $detail = array_map(
        fn(array $package): string => $package['id'] .
            ': ' .
            $package['integrity_detail'],
        array_slice($failed, 0, 10),
    );
    if (count($failed) > 10) {
        $detail[] =
            count($failed) -
            10 .
            ' additional installed package(s) failed integrity verification.';
    }
    return [
        [
            'rule' => 'package_integrity',
            'severity' => 'error',
            'subject' => 'Installed package integrity needs attention',
            'detail' =>
                "Zogograph did not execute these packages. Review or remove the altered files before enabling them.\n" .
                implode("\n", $detail),
        ],
    ];
}

/** Latest failed or available-update result from the root-only collector. */
function updateAlerts(array $config): array
{
    if (!in_array('notify', updateStages($config), true)) {
        return [];
    }
    try {
        $host = hostDb($config, false);
        if ($host === null) {
            return [];
        }
        $last = $host
            ->query(
                'SELECT id, started_at, stage, manager, updates, ok, error
            FROM host_update_runs ORDER BY id DESC LIMIT 1',
            )
            ->fetch();
        if (!$last) {
            return [];
        }
        if (!(bool) $last['ok']) {
            return [
                [
                    'rule' => 'system_updates',
                    'severity' => 'warning',
                    'subject' => 'System update ' . $last['stage'] . ' failed',
                    'detail' =>
                        ($last['manager'] ?: 'package manager') .
                        ': ' .
                        ($last['error'] ?: 'unknown failure'),
                ],
            ];
        }
        // Download and Auto-Update follow Detect, so the latest row is often
        // not the row that says how many packages are waiting.
        $row = $host
            ->query(
                "SELECT started_at, stage, manager, updates
            FROM host_update_runs WHERE stage = 'detect' AND ok = 1 ORDER BY id DESC LIMIT 1",
            )
            ->fetch();
        if ($row && (int) $row['updates'] > 0) {
            return [
                [
                    'rule' => 'system_updates',
                    'severity' => 'info',
                    'subject' =>
                        (int) $row['updates'] . ' system update(s) available',
                    'detail' =>
                        'Detected by ' .
                        $row['manager'] .
                        ' at ' .
                        date('c', (int) $row['started_at']) .
                        '.',
                ],
            ];
        }
    } catch (Throwable) {
        // The host collector is optional and must not make normal alerting fail.
    }
    return [];
}

/**
 * Lets the assistant comment on, and possibly hold back, what was just raised.
 *
 * Returns the same alerts with `ai_summary` and `silenced` filled in where it
 * had something to say. On any failure it returns them untouched: a feature
 * that sits between a machine noticing a problem and a person being told about
 * it has exactly one acceptable failure mode, which is to get out of the way.
 */
function aiTriageAlerts(
    array $config,
    PDO $pdo,
    array $raised,
    ?callable $ask = null,
): array {
    $settings = aiAlertSettings($config);

    if ($settings['mode'] === 'off') {
        return $raised;
    }

    $looked = 0;

    foreach ($raised as $i => $alert) {
        if ($looked >= $settings['per_run']) {
            break;
        }
        if (!aiMayJudge($config, (string) $alert['severity'])) {
            continue;
        }

        $looked++;

        try {
            $verdict = aiJudgeAlert($config, $pdo, $alert, $ask);
        } catch (Throwable $e) {
            logProblem('warning', 'alert triage failed: ' . $e->getMessage());
            continue;
        }

        if ($verdict === null) {
            continue;
        }

        aiSaveVerdict($pdo, (int) $alert['id'], $verdict);

        $raised[$i]['ai_summary'] = $verdict['summary'];
        $raised[$i]['ai_reason'] = $verdict['reason'];
        $raised[$i]['silenced'] = $verdict['verdict'] === 'silence' ? 1 : 0;
    }

    return $raised;
}

/** The latest vitals row, or null if nothing has been collected yet. */
function latestVitals(PDO $pdo): ?array
{
    $row = $pdo
        ->query('SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1')
        ->fetch();
    return $row ?: null;
}

/** Below this many readings, a window is too thin to judge either way. */
const CPU_ALERT_MIN_READINGS = 5;

/**
 * CPU held at the ceiling, rather than a moment of it.
 *
 * A single reading at 100% is not worth waking anyone: a backup starts, a scan
 * runs, a page renders, and it is over. What matters is the CPU *staying* there,
 * because that is the shape of a runaway process or a machine that has stopped
 * coping - and the two look identical for the first minute.
 *
 * So this rule is defined by duration and not by height alone. It needs most of
 * the readings across a whole window to be at or above the threshold, which a
 * spike cannot satisfy however tall it is.
 *
 * A share rather than every reading, because "every" is brittle: one dip to 94%
 * in fifteen minutes would reset a rule that should have fired, and the dip
 * changes nothing about what is happening. A share rather than the median for
 * the opposite reason - a median tolerates a machine that is pegged only half
 * the time, and that is not the same condition.
 *
 * @return list<array>
 */
function cpuAlerts(PDO $pdo, array $rules): array
{
    $threshold = (float) ($rules['cpu_percent'] ?? 0);
    $minutes = (int) ($rules['cpu_minutes'] ?? 15);
    $share = (float) ($rules['cpu_share'] ?? 0.9);

    // Off by default: a percentage that means different things on a dedicated
    // box and inside a CageFS allowance should be set deliberately.
    if ($threshold <= 0 || $minutes <= 0) {
        return [];
    }

    $window = $minutes * 60;

    $stmt = $pdo->prepare(
        'SELECT taken_at, cpu_percent FROM vitals
         WHERE taken_at >= ? AND cpu_percent IS NOT NULL ORDER BY taken_at',
    );
    $stmt->execute([time() - $window]);
    $readings = $stmt->fetchAll();

    $count = count($readings);
    if ($count < CPU_ALERT_MIN_READINGS) {
        return [];
    }

    /*
     * The window has to be covered, not merely touched.
     *
     * Three readings at 100% inside a fifteen-minute window whose other twelve
     * minutes are missing is not evidence of fifteen minutes pegged - it is
     * evidence of a collector that stopped. Left unchecked, an outage would
     * raise a CPU alert, which is the wrong thing to be told.
     */
    $steps = [];
    foreach (range(1, $count - 1) as $i) {
        $step =
            (int) $readings[$i]['taken_at'] -
            (int) $readings[$i - 1]['taken_at'];
        if ($step > 0) {
            $steps[] = $step;
        }
    }
    if (!$steps) {
        return [];
    }
    sort($steps);
    $interval = $steps[intdiv(count($steps), 2)];
    $expected = max(1, (int) floor($window / $interval));

    if ($count < $expected * 0.8) {
        return [];
    }

    $values = array_map(
        fn(array $r): float => (float) $r['cpu_percent'],
        $readings,
    );
    $over = count(
        array_filter($values, fn(float $v): bool => $v >= $threshold),
    );

    if ($over / $count < $share) {
        return [];
    }

    sort($values);
    $median = $values[intdiv(count($values), 2)];
    $peak = $values[count($values) - 1];
    $lowest = $values[0];

    $blame = cpuAlertCulprits($pdo, time() - $window);

    return [
        [
            'rule' => 'cpu_sustained',
            'severity' => 'critical',
            'subject' => sprintf(
                'CPU has been at %.0f%% or above for %s',
                $threshold,
                humanDuration((float) $window),
            ),
            'detail' => sprintf(
                '%d of the last %d readings were at or above %.0f%% (median %.0f%%, peak %.0f%%, ' .
                    'lowest %.0f%%). This is sustained, not a spike: a burst would not hold across ' .
                    'the whole window.%s',
                $over,
                $count,
                $threshold,
                $median,
                $peak,
                $lowest,
                $blame === '' ? '' : ' ' . $blame,
            ),
        ],
    ];
}

/**
 * What was running while the CPU was pegged, if anything was recorded.
 *
 * An alert that says the CPU is busy and nothing else leaves the reader to go
 * and look; naming the processes turns it into something that can be acted on
 * from the message itself. Silent when nothing was captured, which happens when
 * the process threshold is set above the CPU one.
 */
function cpuAlertCulprits(PDO $pdo, int $since): string
{
    $stmt = $pdo->prepare(
        'SELECT name, MAX(cpu_percent) AS cpu FROM process_samples
         WHERE taken_at >= ? AND reason = \'cpu\'
         GROUP BY name ORDER BY cpu DESC LIMIT 3',
    );
    $stmt->execute([$since]);
    $rows = $stmt->fetchAll();

    if (!$rows) {
        return '';
    }

    $named = [];
    foreach ($rows as $row) {
        $named[] = sprintf('%s (%.0f%%)', $row['name'], (float) $row['cpu']);
    }

    return 'Busiest processes recorded in that window: ' .
        implode(', ', $named) .
        '. Those figures are a per-core lifetime average from ps, so they will not ' .
        'add up to the reading.';
}

function diskAlerts(PDO $pdo, array $rules): array
{
    $now = latestVitals($pdo);
    if (!$now) {
        return [];
    }

    $out = [];

    $free = $now['disk_free_bytes'];
    $total = $now['disk_total_bytes'];
    if ($free !== null && $total) {
        $freePercent = ($free / $total) * 100;
        $floorBytes = (int) ($rules['disk_free_bytes'] ?? 0);
        $floorPercent = (float) ($rules['disk_free_percent'] ?? 0);

        if ($floorBytes > 0 && $free < $floorBytes) {
            $out[] = [
                'rule' => 'disk_free_bytes',
                'severity' => 'critical',
                'subject' =>
                    'Disk nearly full: ' . humanBytes((int) $free) . ' left',
                'detail' => sprintf(
                    'Free space is %s, below the %s floor. The filesystem is %.1f%% used.',
                    humanBytes((int) $free),
                    humanBytes($floorBytes),
                    (float) $now['disk_used_percent'],
                ),
            ];
        } elseif ($floorPercent > 0 && $freePercent < $floorPercent) {
            $out[] = [
                'rule' => 'disk_free_percent',
                'severity' => 'critical',
                'subject' => sprintf(
                    'Disk nearly full: %.1f%% free',
                    $freePercent,
                ),
                'detail' => sprintf(
                    'Only %.1f%% of the filesystem is free (%s of %s).',
                    $freePercent,
                    humanBytes((int) $free),
                    humanBytes((int) $total),
                ),
            ];
        }
    }

    $inodesFree = $now['inodes_free'];
    $inodesTotal = $now['inodes_total'];
    if ($inodesFree !== null && $inodesTotal) {
        $percent = ($inodesFree / $inodesTotal) * 100;
        if ($percent < (float) ($rules['inodes_free_percent'] ?? 0)) {
            $out[] = [
                'rule' => 'inodes_free_percent',
                'severity' => 'critical',
                'subject' => sprintf(
                    'Inodes nearly exhausted: %.1f%% free',
                    $percent,
                ),
                'detail' => sprintf(
                    'Only %s inodes free of %s. The disk can have room and still refuse to create a file.',
                    number_format((float) $inodesFree),
                    number_format((float) $inodesTotal),
                ),
            ];
        }
    }

    return $out;
}

/**
 * Where the trend is heading.
 *
 * The most useful alert of the lot, because it fires while there is still time
 * to do something. Fitted over the last day of samples rather than the last
 * two, so a single tar file being written does not predict catastrophe.
 */
function trendAlerts(PDO $pdo, array $rules): array
{
    $hours = (float) ($rules['projected_full_hours'] ?? 0);
    if ($hours <= 0) {
        return [];
    }

    $rows = $pdo->prepare(
        'SELECT taken_at, disk_free_bytes, inodes_free FROM vitals
         WHERE taken_at >= ? ORDER BY taken_at',
    );
    $rows->execute([time() - TREND_WINDOW_SECONDS]);
    $readings = $rows->fetchAll();

    $out = [];

    foreach (
        [
            [
                'disk_free_bytes',
                'Disk',
                fn(float $v): string => humanBytes((int) round($v)),
            ],
            [
                'inodes_free',
                'Inodes',
                fn(float $v): string => number_format(round($v)),
            ],
        ]
        as [$field, $label, $format]
    ) {
        $trend = seriesTrend($readings, $field);

        /*
         * Only a sustained fall is worth an email.
         *
         * Without the gate this fired every time somebody uploaded something
         * large, predicted catastrophe from a one-off, and taught the reader to
         * ignore it - which costs more than the alert was ever worth.
         */
        if (
            $trend === null ||
            !$trend['sustained'] ||
            $trend['seconds_left'] === null
        ) {
            continue;
        }
        if ($trend['seconds_left'] / 3600 > $hours) {
            continue;
        }

        $out[] = [
            'rule' => 'projected_full:' . $field,
            'severity' => 'warning',
            'subject' => sprintf(
                '%s will run out in about %s',
                $label,
                humanDuration($trend['seconds_left']),
            ),
            'detail' => sprintf(
                "%s has been falling steadily for %s, not in one drop:\n" .
                    "  losing about %s an hour\n" .
                    "  %s left, which runs out in %s\n" .
                    '  falling in both halves of the window (%s an hour, then %s an hour)',
                $label,
                humanDuration((float) $trend['span_seconds']),
                $format(-$trend['rate'] * 3600),
                $format($trend['latest']),
                humanDuration($trend['seconds_left']),
                $format(-(float) $trend['first_half'] * 3600),
                $format(-(float) $trend['second_half'] * 3600),
            ),
        ];
    }

    return $out;
}

/**
 * How a series is really moving, told apart from a one-off event.
 *
 * A plain least-squares fit was the old answer and it is the wrong one: a
 * single twenty-gigabyte upload drags the line down hard enough to predict a
 * full disk by Thursday, and after a week of those nobody reads the warnings.
 *
 * The distinction that matters is between a *step* and a *slope*. Space that
 * dropped once and stayed down is consumption that already happened; space
 * that keeps dropping is a leak. So the window is cut in half and each half is
 * measured on its own - a spike lands in one of them, and a leak is in both.
 *
 * Three rates come out, and they answer different questions:
 *
 *   overall  where the level actually went, spikes included
 *   robust   the median step between readings, which a spike cannot move
 *   halves   whether it is still going
 *
 * @return array|null null when there is not enough history to say anything.
 */
function seriesTrend(array $rows, string $field): ?array
{
    $points = [];
    foreach ($rows as $row) {
        if (($row[$field] ?? null) === null) {
            continue;
        }
        $points[] = [(int) $row['taken_at'], (float) $row[$field]];
    }

    $count = count($points);
    $span = $count > 1 ? $points[$count - 1][0] - $points[0][0] : 0;

    // Too few readings, or too short a window, and any answer is noise wearing
    // a confident face.
    if ($count < 8 || $span < 1800) {
        return null;
    }

    $overall = ($points[$count - 1][1] - $points[0][1]) / $span;

    $steps = [];
    foreach (range(1, $count - 1) as $i) {
        $dt = $points[$i][0] - $points[$i - 1][0];
        if ($dt > 0) {
            $steps[] = ($points[$i][1] - $points[$i - 1][1]) / $dt;
        }
    }
    if (!$steps) {
        return null;
    }

    $middle = (int) floor($count / 2);
    $first = halfRate(array_slice($points, 0, $middle + 1));
    $second = halfRate(array_slice($points, $middle));

    /*
     * Falling in both halves, or it is not a trend yet.
     *
     * This is the whole spike filter. An upload makes one half plummet and
     * leaves the other flat; a log file filling up makes both of them fall.
     */
    $sustained =
        $first !== null &&
        $second !== null &&
        $first < 0 &&
        $second < 0 &&
        $overall < 0;

    $median = median($steps);
    // The median is the estimate a spike cannot inflate, so it is preferred for
    // projecting; `overall` covers the case of a slow drift too fine to show up
    // as a consistent step between one reading and the next.
    $rate = $median < 0 ? $median : ($overall < 0 ? $overall : 0.0);

    $latest = $points[$count - 1][1];
    $secondsLeft = $rate < 0 ? $latest / -$rate : null;

    return [
        'rate' => $rate,
        'overall' => $overall,
        'median' => $median,
        'first_half' => $first,
        'second_half' => $second,
        'sustained' => $sustained,
        'seconds_left' => $secondsLeft,
        'latest' => $latest,
        'samples' => $count,
        'span_seconds' => $span,
        // Losing space overall but not in both halves: something happened, and
        // it is not happening now.
        'spike' => !$sustained && $overall < 0,
    ];
}

/** The average rate across a slice, or null if it does not span any time. */
function halfRate(array $points): ?float
{
    $n = count($points);
    if ($n < 2) {
        return null;
    }
    $seconds = $points[$n - 1][0] - $points[0][0];
    return $seconds > 0
        ? ($points[$n - 1][1] - $points[0][1]) / $seconds
        : null;
}

function median(array $values): float
{
    sort($values);
    $n = count($values);
    if ($n === 0) {
        return 0.0;
    }
    $mid = intdiv($n, 2);
    return $n % 2 === 1
        ? $values[$mid]
        : ($values[$mid - 1] + $values[$mid]) / 2;
}

/** Bytes per second, by least squares. Null if the points are all at once. */
function leastSquaresSlope(array $rows, string $xKey, string $yKey): ?float
{
    $n = count($rows);
    if ($n < 2) {
        return null;
    }

    $sumX = $sumY = $sumXY = $sumXX = 0.0;
    $x0 = (float) $rows[0][$xKey];
    foreach ($rows as $row) {
        $x = (float) $row[$xKey] - $x0;
        $y = (float) $row[$yKey];
        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumXX += $x * $x;
    }

    $denominator = $n * $sumXX - $sumX * $sumX;
    if (abs($denominator) < 1e-9) {
        return null;
    }

    return ($n * $sumXY - $sumX * $sumY) / $denominator;
}

function categoryAlerts(PDO $pdo, array $rules): array
{
    $jump = (int) ($rules['category_jump_bytes'] ?? 0);
    if ($jump <= 0) {
        return [];
    }

    $scans = $pdo
        ->query(
            'SELECT id FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 2',
        )
        ->fetchAll(PDO::FETCH_COLUMN);
    if (count($scans) < 2) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT category, bytes FROM scan_totals WHERE scan_id = ?',
    );
    $stmt->execute([$scans[0]]);
    $now = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stmt->execute([$scans[1]]);
    $before = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $out = [];
    foreach ($now as $category => $bytes) {
        $grew = (int) $bytes - (int) ($before[$category] ?? 0);
        if ($grew < $jump) {
            continue;
        }
        $out[] = [
            'rule' => 'category_jump:' . $category,
            'severity' => 'warning',
            'subject' => sprintf(
                '%s grew by %s',
                ucfirst((string) $category),
                humanBytes($grew),
            ),
            'detail' => sprintf(
                '%s is now %s, up %s since the previous scan.',
                ucfirst((string) $category),
                humanBytes((int) $bytes),
                humanBytes($grew),
            ),
        ];
    }

    return $out;
}

/**
 * Individual paths that jumped.
 *
 * This is the one that names the culprit. "Logs grew by four gigabytes" sends
 * you looking; "/home/x/logs/error.log grew by four gigabytes" is the answer.
 */
function pathAlerts(PDO $pdo, array $rules): array
{
    $jumpBytes = (int) ($rules['path_jump_bytes'] ?? 0);
    $jumpFiles = (int) ($rules['path_jump_files'] ?? 0);
    if ($jumpBytes <= 0 && $jumpFiles <= 0) {
        return [];
    }

    $scans = $pdo
        ->query(
            'SELECT id FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 2',
        )
        ->fetchAll(PDO::FETCH_COLUMN);
    if (count($scans) < 2) {
        return [];
    }

    $rows = $pdo->prepare(
        'SELECT p.id, p.path, p.kind, now.bytes AS bytes, now.files AS files,
                before.bytes AS was_bytes, before.files AS was_files
         FROM path_sizes now
         JOIN path_sizes before ON before.path_id = now.path_id AND before.scan_id = :before
         JOIN paths p ON p.id = now.path_id
         WHERE now.scan_id = :now
           AND (now.bytes - before.bytes >= :jumpBytes OR now.files - before.files >= :jumpFiles)
         ORDER BY (now.bytes - before.bytes) DESC
         LIMIT 20',
    );
    /*
     * Bound as integers, deliberately and not for tidiness.
     *
     * PDO's execute([...]) binds every value as text. SQLite applies a column's
     * affinity when comparing against that column, so `scan_id = '2'` is fine -
     * but `now.bytes - before.bytes` is a computed expression with no affinity
     * at all, and there an integer is never greater than a string. The whole
     * comparison was silently false, so the one alert that names the actual
     * culprit could not fire, and nothing anywhere reported a problem.
     */
    $rows->bindValue(':now', (int) $scans[0], PDO::PARAM_INT);
    $rows->bindValue(':before', (int) $scans[1], PDO::PARAM_INT);
    // A disabled rule must never match, so it is set beyond any real value
    // rather than to zero, which every unchanged path would satisfy.
    $rows->bindValue(
        ':jumpBytes',
        $jumpBytes > 0 ? $jumpBytes : PHP_INT_MAX,
        PDO::PARAM_INT,
    );
    $rows->bindValue(
        ':jumpFiles',
        $jumpFiles > 0 ? $jumpFiles : PHP_INT_MAX,
        PDO::PARAM_INT,
    );
    $rows->execute();

    /*
     * Don't blame a directory for its child's growth.
     *
     * A file gaining two gigabytes makes every directory above it gain two
     * gigabytes as well, so one runaway log raised an alert for itself, for its
     * directory, and for the home directory - three messages about one event,
     * of which only the most specific is any use.
     *
     * Decided over the whole candidate set rather than as the rows arrive: they
     * come biggest-growth first, which means a parent is always seen before the
     * child that explains it, and a running check can only ever look backwards.
     */
    $candidates = $rows->fetchAll();
    $growth = [];
    foreach ($candidates as $row) {
        $growth[(string) $row['path']] =
            (int) $row['bytes'] - (int) $row['was_bytes'];
    }

    $out = [];
    foreach ($candidates as $row) {
        $grewBytes = (int) $row['bytes'] - (int) $row['was_bytes'];
        $grewFiles = (int) $row['files'] - (int) $row['was_files'];

        if (explainedByChild((string) $row['path'], $grewBytes, $growth)) {
            continue;
        }

        $how = [];
        if ($grewBytes > 0) {
            $how[] = 'up ' . humanBytes($grewBytes);
        }
        if ($grewFiles > 0) {
            $how[] = 'up ' . number_format($grewFiles) . ' files';
        }

        $out[] = [
            'rule' => 'path_jump',
            'severity' => 'warning',
            'path_id' => (int) $row['id'],
            'subject' => sprintf(
                '%s %s',
                basename((string) $row['path']),
                implode(', ', $how),
            ),
            'detail' => sprintf(
                "%s\n  now %s in %s files, %s since the previous scan.",
                (string) $row['path'],
                humanBytes((int) $row['bytes']),
                number_format((float) $row['files']),
                implode(' and ', $how),
            ),
        ];
    }

    return $out;
}

/**
 * Whether a path's growth is already accounted for by something inside it.
 *
 * "Essentially all" rather than "all": a directory that gained a gigabyte from
 * one log and another hundred megabytes spread over fifty small files is still,
 * for the purpose of telling somebody what happened, the story of the log.
 */
function explainedByChild(string $path, int $grew, array $growth): bool
{
    if ($grew <= 0) {
        return false;
    }

    $prefix = rtrim($path, '/') . '/';
    foreach ($growth as $other => $otherGrew) {
        if ($other === $path || !str_starts_with($other, $prefix)) {
            continue;
        }
        if ($otherGrew >= $grew * 0.9) {
            return true;
        }
    }

    return false;
}

/**
 * Writes an alert, unless one like it went out recently.
 *
 * Returns the new row id, or null if it was suppressed. The cooldown is keyed
 * on the rule and the path together, so a hundred log files filling up at once
 * still names all hundred, while one of them filling up for a week names it
 * once a cooldown.
 */
function recordAlert(PDO $pdo, array $rules, array $alert): ?int
{
    /*
     * The cooldown belongs to the rule, not to the system.
     *
     * One setting for everything meant an alert about inodes silenced the next
     * alert about disk space for six hours. Those are different problems that
     * happen to arrive together, and suppressing the second because the first
     * went out is the easiest possible way to miss the one that mattered.
     *
     * `$rules` is the alerts section of the config, so it is wrapped back up
     * into the shape alertRule() reads.
     */
    $settings = alertRule(['alerts' => $rules], (string) $alert['rule']);
    $cooldown = $settings['cooldown'];

    // Zero means every time, which is a real answer for a site that is down.
    if ($cooldown <= 0) {
        return writeAlert($pdo, $alert, $settings['severity']);
    }

    $since = time() - $cooldown;

    $check = $pdo->prepare(
        'SELECT COUNT(*) FROM alerts
         WHERE rule = ? AND raised_at >= ?
           AND (path_id IS ? OR path_id = ?)',
    );
    $pathId = $alert['path_id'] ?? null;
    $check->execute([$alert['rule'], $since, $pathId, $pathId]);
    if ((int) $check->fetchColumn() > 0) {
        return null;
    }

    return writeAlert($pdo, $alert, $settings['severity']);
}

/**
 * Writes the row, with the severity the operator chose for this rule.
 *
 * The rule decides how loudly it speaks, not the code that raised it: two
 * volumes meant a domain expiring in a month shouted as loudly as a disk with
 * an hour left, and people learn very quickly that a channel where everything
 * is urgent contains nothing urgent.
 *
 * A rule that names its own severity - the four steps of a domain expiry, say -
 * keeps it, because there the urgency is a property of the event rather than of
 * the kind of event.
 */
function writeAlert(PDO $pdo, array $alert, string $fallback): int
{
    $severity =
        isset($alert['severity']) && isset(severityLevels()[$alert['severity']])
            ? (string) $alert['severity']
            : $fallback;

    $pdo->prepare(
        'INSERT INTO alerts (raised_at, rule, severity, subject, detail, path_id)
         VALUES (?, ?, ?, ?, ?, ?)',
    )->execute([
        time(),
        $alert['rule'],
        $severity,
        $alert['subject'],
        $alert['detail'],
        $alert['path_id'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * One message for everything raised in this run.
 *
 * One rather than one each: a disk filling up trips several rules at once, and
 * five separate emails about the same event is how people learn to ignore them.
 */
function sendAlertEmail(array $config, PDO $pdo, array $raised): void
{
    $rules = $config['alerts'];
    $recipients = alertRecipients((string) ($rules['to'] ?? ''));

    if ($recipients === [] || !function_exists('mail')) {
        return;
    }

    /*
     * Loudest first, and the message takes the volume of its loudest line.
     *
     * A message whose subject says "info" and whose third paragraph says a
     * domain expired yesterday has buried the only part that mattered.
     */
    usort(
        $raised,
        fn(array $a, array $b): int => severityRank((string) $b['severity']) <=>
            severityRank((string) $a['severity']),
    );

    $worst = worstSeverity(array_column($raised, 'severity'));
    $host = php_uname('n');

    $subject = sprintf(
        '[%s] %s: %s',
        strtoupper(severityLevels()[$worst]['label'] ?? $worst),
        $host,
        count($raised) === 1
            ? $raised[0]['subject']
            : count($raised) . ' things need looking at',
    );

    $lines = ['Zogograph on ' . $host, ''];

    $link = dashboardLink($config);
    if ($link !== null) {
        $lines[] = $link;
        $lines[] = '';
    }

    foreach ($raised as $alert) {
        $level = severityLevels()[$alert['severity']] ?? null;
        $lines[] =
            strtoupper($level['label'] ?? (string) $alert['severity']) .
            ' — ' .
            $alert['subject'];
        $lines[] = '  ' . str_replace("\n", "\n  ", (string) $alert['detail']);

        /*
         * A link to the page that answers this particular question.
         *
         * Not to the front door: somebody woken by this has to find the right
         * page, and the tool already knows which one it is.
         */
        $where = alertRule($config, (string) $alert['rule']);
        $page = dashboardLink($config, $where['view'], ['range' => 86400]);
        if ($page !== null) {
            $lines[] = '  ' . $page;
        }

        /*
         * What the assistant made of it, attributed.
         *
         * Attributed because it matters who said it: everything else in this
         * message is a measurement, and this is an opinion formed by a language
         * model. Somebody reading it at three in the morning should not have to
         * work out which is which.
         */
        if (trim((string) ($alert['ai_summary'] ?? '')) !== '') {
            $lines[] = '';
            $lines[] = '  ' . aiPersona($config)['name'] . ' says:';
            $lines[] =
                '  ' .
                str_replace("\n", "\n  ", trim((string) $alert['ai_summary']));
        }

        $lines[] = '';
    }

    /*
     * The evidence, before the routine status block.
     *
     * Whoever opens this wants to know what was happening, and putting the
     * disk summary first buries it under numbers that are the same every time.
     */
    $incidentId = $raised[0]['incident_id'] ?? null;
    if ($incidentId !== null) {
        $incident = incidentRead($pdo, (int) $incidentId);
        if ($incident !== null && $incident['evidence'] !== null) {
            foreach (incidentSummary($incident['evidence']) as $line) {
                $lines[] = $line;
            }
            $lines[] = '';
        }
    }

    foreach (statusReport($pdo) as $line) {
        $lines[] = $line;
    }

    foreach (diagnosisBlock($config, $pdo, $raised) as $line) {
        $lines[] = $line;
    }

    $lines[] = '';
    $lines[] = repeatNote($config, $raised);

    $headers = [
        'Auto-Submitted: auto-generated',
        // 1 is highest. Only the top two levels claim it; a client that sorts
        // by priority should not put a thirty-day domain notice at the top.
        'X-Priority: ' . (severityRank($worst) >= 3 ? '1' : '3'),
    ];
    $from = trim((string) ($rules['from'] ?? ''));
    if ($from !== '') {
        $headers[] = 'From: ' . $from;
    }

    /*
     * One message per recipient rather than a comma-joined To.
     *
     * A single mail() with several addresses fails as a unit - one bad
     * mailbox and nobody hears - and it also shows everybody the whole list,
     * which is not this tool's to share.
     */
    $body = implode("\n", $lines);
    $failed = [];
    $sentAny = false;

    foreach ($recipients as $one) {
        if (@mail($one, $subject, $body, implode("\r\n", $headers))) {
            $sentAny = true;
        } else {
            $failed[] = $one;
        }
    }

    $error =
        $failed === [] ? null : 'mail() refused: ' . implode(', ', $failed);

    /*
     * The message is kept, whether or not it was delivered.
     *
     * Especially when it was not: "the email failed" is a fact about the mail
     * system, and the thing somebody wants next is what it would have said. It
     * is stored before the alerts are updated so that a row pointing at a
     * message always finds one.
     */
    $ids = array_map(intval(...), array_column($raised, 'id'));

    $pdo->prepare(
        'INSERT INTO alert_emails (at, subject, body, recipients, headers, sent, error, alert_ids, attempts, next_attempt_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    )->execute([
        time(),
        $subject,
        $body,
        implode('; ', $recipients),
        implode("\n", $headers),
        $sentAny ? 1 : 0,
        $error,
        implode(',', $ids),
        0,
        $sentAny ? null : alertEmailRetryAt(0),
    ]);
    $emailId = (int) $pdo->lastInsertId();

    $update = $pdo->prepare(
        'UPDATE alerts SET emailed_at = ?, email_error = ?, email_id = ? WHERE id = ?',
    );
    foreach ($raised as $alert) {
        $update->execute([
            $sentAny ? time() : null,
            $error,
            $emailId,
            $alert['id'],
        ]);
    }

    logAction(
        $pdo,
        'alert_email',
        implode('; ', $recipients),
        $sentAny ? 'ok' : 'failed',
        0,
        0,
        sprintf(
            '%s, %d alert(s)%s',
            $worst,
            count($raised),
            $failed === []
                ? ''
                : ', ' . count($failed) . ' address(es) refused',
        ),
    );
}

/** A bounded retry schedule for mail that the local MTA refused outright. */
function alertEmailRetryAt(int $attempts, ?int $now = null): int
{
    $now ??= time();
    return $now + min(3600, 60 * 2 ** min(6, max(0, $attempts)));
}

/**
 * Retries only wholly refused batches. `mail()` accepting even one recipient
 * means delivery has become MTA work; retrying then could duplicate an alert.
 *
 * @return array{attempted: int, sent: int, failed: int}
 */
function retryAlertEmails(
    PDO $pdo,
    int $limit = 3,
    ?callable $send = null,
): array {
    if (!function_exists('mail') && $send === null) {
        return ['attempted' => 0, 'sent' => 0, 'failed' => 0];
    }
    $limit = max(1, min(10, $limit));
    $stmt = $pdo->prepare('SELECT id, subject, body, recipients, headers, attempts
        FROM alert_emails WHERE sent = 0 AND next_attempt_at <= ? ORDER BY next_attempt_at, id LIMIT ?');
    $stmt->execute([time(), $limit]);
    $update = $pdo->prepare(
        'UPDATE alert_emails SET sent = ?, error = ?, attempts = ?, next_attempt_at = ? WHERE id = ?',
    );
    $alerts = $pdo->prepare(
        'UPDATE alerts SET emailed_at = ?, email_error = ? WHERE email_id = ?',
    );
    $result = ['attempted' => 0, 'sent' => 0, 'failed' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $result['attempted']++;
        $acceptedAny = false;
        $refused = [];
        foreach (
            preg_split('/\s*;\s*/', (string) $row['recipients']) ?: []
            as $recipient
        ) {
            if ($recipient === '') {
                continue;
            }
            $accepted =
                $send !== null
                    ? (bool) $send(
                        $recipient,
                        (string) $row['subject'],
                        (string) $row['body'],
                        (string) ($row['headers'] ?? ''),
                    )
                    : @mail(
                        $recipient,
                        (string) $row['subject'],
                        (string) $row['body'],
                        (string) ($row['headers'] ?? ''),
                    );
            if ($accepted) {
                $acceptedAny = true;
            } else {
                $refused[] = $recipient;
            }
        }
        $attempts = (int) $row['attempts'] + 1;
        $error =
            $refused === []
                ? null
                : 'mail() refused retry ' .
                    $attempts .
                    ': ' .
                    implode(', ', $refused);
        $update->execute([
            $acceptedAny ? 1 : 0,
            $error,
            $attempts,
            $acceptedAny ? null : alertEmailRetryAt($attempts),
            (int) $row['id'],
        ]);
        $alerts->execute([
            $acceptedAny ? time() : null,
            $error,
            (int) $row['id'],
        ]);
        $result[$acceptedAny ? 'sent' : 'failed']++;
    }
    return $result;
}

const TELEGRAM_ALERT_TEXT_LIMIT = 3900;

/**
 * Telegram has a hard message limit. Summarize before transport so an operator
 * knows when a batch was shortened and keeps the dashboard link that answers it.
 */
function telegramAlertText(array $config, array $raised): string
{
    usort(
        $raised,
        fn(array $a, array $b): int => severityRank((string) $b['severity']) <=>
            severityRank((string) $a['severity']),
    );
    $worst = worstSeverity(array_column($raised, 'severity'));
    $lines = [
        sprintf('Zogograph %s on %s', strtoupper($worst), php_uname('n')),
    ];
    $shown = 0;
    foreach ($raised as $alert) {
        $subject =
            preg_replace(
                '/\s+/',
                ' ',
                trim((string) ($alert['subject'] ?? '')),
            ) ?:
            'Untitled alert';
        $line =
            strtoupper((string) ($alert['severity'] ?? 'info')) .
            ' — ' .
            $subject;
        // Leave space for the omitted count and the dashboard link.
        if (
            $shown >= 20 ||
            mb_strlen(implode("\n", [...$lines, $line])) >
                TELEGRAM_ALERT_TEXT_LIMIT - 220
        ) {
            break;
        }
        $lines[] = mb_substr($line, 0, 300);
        $shown++;
    }
    $remaining = count($raised) - $shown;
    if ($remaining > 0) {
        $lines[] =
            '… plus ' .
            $remaining .
            ' more alert' .
            ($remaining === 1 ? '' : 's') .
            '.';
    }
    $link = dashboardLink($config);
    if ($link !== null) {
        $lines[] = $link;
    }
    return mb_substr(implode("\n", $lines), 0, TELEGRAM_ALERT_TEXT_LIMIT);
}

/** Sends a compact duplicate of an alert batch to the configured Telegram chat. */
function sendTelegramAlerts(array $config, PDO $pdo, array $raised): void
{
    $telegram = (array) ($config['alerts']['telegram'] ?? []);
    if (empty($telegram['enabled'])) {
        return;
    }

    $result = sendTelegramMessage(
        $telegram,
        telegramAlertText($config, $raised),
    );
    logAction(
        $pdo,
        'alert_telegram',
        (string) ($telegram['chat_id'] ?? ''),
        $result['sent'] ? 'ok' : 'failed',
        0,
        0,
        $result['error'],
    );
}

/** @return array{sent: bool, error: ?string} */
function sendTelegramMessage(array $telegram, string $text): array
{
    $token = trim((string) ($telegram['bot_token'] ?? ''));
    $chat = trim((string) ($telegram['chat_id'] ?? ''));
    if ($token === '' || $chat === '') {
        return [
            'sent' => false,
            'error' => 'Telegram needs both a bot token and chat ID.',
        ];
    }
    if (!function_exists('curl_init')) {
        return [
            'sent' => false,
            'error' => 'PHP cURL is not available on this host.',
        ];
    }

    $curl = curl_init(
        'https://api.telegram.org/bot' . rawurlencode($token) . '/sendMessage',
    );
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'chat_id' => $chat,
            'text' => mb_substr($text, 0, TELEGRAM_ALERT_TEXT_LIMIT),
            'disable_web_page_preview' => 'true',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($body === false || $status < 200 || $status >= 300) {
        return [
            'sent' => false,
            'error' =>
                $error !== ''
                    ? $error
                    : 'Telegram returned HTTP ' . $status . '.',
        ];
    }
    return ['sent' => true, 'error' => null];
}

/** @return array{sent: bool, to: string, error: ?string} */
function sendTestTelegram(array $config): array
{
    $telegram = (array) ($config['alerts']['telegram'] ?? []);
    $result = sendTelegramMessage(
        $telegram,
        'Zogograph test on ' . php_uname('n') . "\nNothing has tripped.",
    );
    return [
        'sent' => $result['sent'],
        'to' => (string) ($telegram['chat_id'] ?? ''),
        'error' => $result['error'],
    ];
}

/**
 * Commands to diagnose this by hand, at the end of the message.
 *
 * The most likely moment for this dashboard to be unreachable is the moment
 * the disk is full - which is exactly when its email arrives. Each command
 * says what it will tell you and whether it needs root, because a block of
 * shell nobody understands gets pasted without reading or not at all.
 *
 * @return list<string>
 */
function diagnosisBlock(array $config, PDO $pdo, array $raised): array
{
    $seen = [];
    $account = [];
    $root = [];

    foreach ($raised as $alert) {
        foreach (
            diagnosisFor($alert, $config, alertFacts($pdo, $alert))
            as $command
        ) {
            // The same command from two alerts is one command.
            if (isset($seen[$command['command']])) {
                continue;
            }
            $seen[$command['command']] = true;

            if ($command['who'] === 'root') {
                $root[] = $command;
            } else {
                $account[] = $command;
            }
        }
    }

    // Appended once, at the end, rather than wherever the first alert put it.
    $self = selfDiagnosis($config);
    if (!isset($seen[$self['command']])) {
        $account[] = $self;
    }

    if ($account === [] && $root === []) {
        return [];
    }

    $lines = [
        '',
        str_repeat('-', 66),
        '',
        'If this dashboard is not reachable - which is ' .
        'likely, if the disk is the problem - these',
        'answer the same questions from a ' . 'shell.',
        '',
    ];

    if ($account !== []) {
        $lines[] =
            'As ' . (currentAccountOwner($config) ?: 'the account') . ':';
        $lines[] = '';
        foreach ($account as $command) {
            foreach (commandLines($command) as $line) {
                $lines[] = $line;
            }
        }
    }

    if ($root !== []) {
        $lines[] = 'As root:';
        $lines[] = '';
        foreach ($root as $command) {
            foreach (commandLines($command) as $line) {
                $lines[] = $line;
            }
        }
    }

    return $lines;
}

/**
 * One command, wrapped as a comment and the line itself.
 *
 * @return list<string>
 */
function commandLines(array $command): array
{
    $lines = [];

    if ($command['when'] !== null) {
        $lines[] = '  # Only if ' . $command['when'] . ':';
    }

    foreach (wrapComment($command['why'], 68) as $line) {
        $lines[] = '  # ' . $line;
    }

    $lines[] = '  ' . $command['command'];
    $lines[] = '';

    return $lines;
}

/** Wraps prose to a width, for a comment in a plain-text email. */
function wrapComment(string $text, int $width): array
{
    $wrapped = wordwrap(trim($text), $width, "\n", false);
    return $wrapped === '' ? [] : explode("\n", $wrapped);
}

/**
 * Values from an alert that a command can be built around.
 *
 * A message about a runaway process should carry that process's id rather than
 * a placeholder somebody has to go and look up - at which point they may as
 * well not have been given the command.
 *
 * @return array<string, mixed>
 */
function alertFacts(PDO $pdo, array $alert): array
{
    $facts = [];

    if (!empty($alert['path_id'])) {
        try {
            $stmt = $pdo->prepare('SELECT path FROM paths WHERE id = ?');
            $stmt->execute([(int) $alert['path_id']]);
            $path = $stmt->fetchColumn();
            if ($path !== false) {
                $facts['path'] = (string) $path;
            }
        } catch (Throwable) {
            // A missing path is not worth failing an email over.
        }
    }

    /*
     * The rest come out of the alert's own text.
     *
     * Which is not elegant, and is much better than the alternative: the rules
     * that raise these do not carry structured detail, and adding a column to
     * every one of them to serve the email would be a larger change than
     * reading back the thing they already wrote down.
     */
    $detail =
        (string) ($alert['subject'] ?? '') .
        ' ' .
        (string) ($alert['detail'] ?? '');

    if (preg_match('/\bpid\s*[:# ]\s*(\d{1,7})\b/i', $detail, $m)) {
        $facts['pid'] = (int) $m[1];
    }
    if (preg_match('#\bhttps?://\S+#i', $detail, $m)) {
        $facts['url'] = rtrim($m[0], '.,);');
    }

    $rule = explode(':', (string) ($alert['rule'] ?? ''), 2);
    if ($rule[0] === 'domain_expiry' && isset($rule[1])) {
        $facts['domain'] = $rule[1];
    }

    return $facts;
}

/** Whose shell the account commands are meant for, if it can be worked out. */
function currentAccountOwner(array $config): string
{
    $owner = trim((string) ($config['host']['owner'] ?? ''));
    if ($owner !== '') {
        return $owner;
    }

    $root = (string) ($config['scan_root'] ?? '');
    return $root === '' ? '' : basename($root);
}

/**
 * When this will speak again, said per rule rather than once.
 *
 * The cooldown is now per rule, so a single sentence about "this message" would
 * be wrong for every message carrying more than one kind of alert.
 */
function repeatNote(array $config, array $raised): string
{
    $spans = [];

    foreach ($raised as $alert) {
        $settings = alertRule($config, (string) $alert['rule']);
        $spans[$settings['label']] = $settings['cooldown'];
    }

    if (count($spans) === 1) {
        $only = (int) reset($spans);
        return $only <= 0
            ? 'This will be sent again the next time it is true.'
            : 'This will not repeat for ' . plainSpan($only, true) . '.';
    }

    $parts = [];
    foreach ($spans as $label => $seconds) {
        $parts[] =
            $label .
            ': ' .
            ((int) $seconds <= 0
                ? 'every time'
                : plainSpan((int) $seconds, true));
    }

    return 'Before repeating — ' . implode('; ', $parts) . '.';
}

/**
 * Where things stand, as lines of plain text.
 *
 * Shared by the alert emails and the test email, so what a test sends is
 * literally the report a real alert would carry - a test that exercises a
 * different code path tells you the wrong thing about the one that matters.
 *
 * @return list<string>
 */
/**
 * Dates that are about to matter, folded into the alerting.
 *
 * The reason the priority flag exists. A domain expiring on Friday and a VPS
 * renewing on Monday are not conditions any threshold can detect, and a
 * calendar page nobody opened is not a warning - so they ride the same email as
 * everything else.
 *
 * Deliberately narrow: only things marked important, only inside the window,
 * and only while unresolved. An email listing every dated thing would be a
 * newsletter.
 *
 * @return list<array{severity: string, subject: string, detail: string, rule: string}>
 */
function dateAlerts(array $config, PDO $pdo): array
{
    $out = [];

    try {
        $pressing = pressingEvents($config, $pdo);
    } catch (Throwable) {
        return $out;
    }

    /*
     * Domains are read directly rather than through pressingEvents().
     *
     * That function answers "what needs attention", and rightly filters to
     * domains already urgent or expired - which is the dashboard's question and
     * the calendar's. It is not this one: the whole point of a thirty-day
     * notice is that it arrives long before anything is urgent, and going
     * through a filter designed to exclude the not-yet-urgent would have made
     * the info and warning steps unreachable.
     */
    foreach (domainAlerts($config, $pdo) as $alert) {
        $out[] = $alert;
    }

    foreach ($pressing as $item) {
        if ($item['kind'] === 'domain') {
            // Already handled above, with four steps instead of two.
            continue;
        }

        $days = (int) floor(($item['at'] - time()) / SPAN_DAY);

        $out[] = [
            'rule' => 'event_due',
            'severity' =>
                $days <= 1 ? 'error' : ($days <= 3 ? 'warning' : 'info'),
            'subject' => sprintf(
                '%s %s',
                $item['title'],
                $days < 0
                    ? 'was due ' . abs($days) . ' days ago'
                    : ($days === 0
                        ? 'is due today'
                        : 'is due in ' . $days . ' days'),
            ),
            'detail' => trim(
                ($item['note'] ?? '') .
                    "\n" .
                    'Marked as important on the calendar. Mark it dealt with there to stop ' .
                    'it appearing here.',
            ),
        ];
    }

    return $out;
}

function statusReport(PDO $pdo): array
{
    $lines = [];
    $vitals = latestVitals($pdo);

    if (!$vitals) {
        return ['Nothing has been collected yet. Run: php cron.php --vitals'];
    }

    $lines[] =
        'At the last reading (' .
        date('Y-m-d H:i', (int) $vitals['taken_at']) .
        '):';
    $lines[] = sprintf(
        '  disk     %s free of %s (%.1f%% used)',
        humanBytes((int) $vitals['disk_free_bytes']),
        humanBytes((int) $vitals['disk_total_bytes']),
        (float) $vitals['disk_used_percent'],
    );

    if ($vitals['inodes_free'] !== null) {
        $lines[] = sprintf(
            '  inodes   %s free of %s (%.1f%% used)',
            number_format((float) $vitals['inodes_free']),
            number_format((float) $vitals['inodes_total']),
            (float) $vitals['inodes_used_percent'],
        );
    }
    if ($vitals['load1'] !== null) {
        $cores = $vitals['cpu_cores']
            ? ' over ' . (int) $vitals['cpu_cores'] . ' cores'
            : '';
        $lines[] = sprintf(
            '  load     %.2f %.2f %.2f%s',
            (float) $vitals['load1'],
            (float) $vitals['load5'],
            (float) $vitals['load15'],
            $cores,
        );
    }
    if ($vitals['cpu_percent'] !== null) {
        $lines[] = sprintf('  cpu      %.0f%%', (float) $vitals['cpu_percent']);
    }
    if ($vitals['mem_used_percent'] !== null) {
        $lines[] = sprintf(
            '  memory   %.0f%% used, %s available',
            (float) $vitals['mem_used_percent'],
            humanBytes((int) $vitals['mem_available_bytes']),
        );
    }
    if ($vitals['net_rx_rate'] !== null) {
        $lines[] = sprintf(
            '  network  %s/s in, %s/s out',
            humanBytes((int) $vitals['net_rx_rate']),
            humanBytes((int) $vitals['net_tx_rate']),
        );
    }

    $scan = $pdo
        ->query(
            'SELECT id, started_at FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 1',
        )
        ->fetch();

    if (!$scan) {
        $lines[] = '';
        $lines[] = 'No scan has finished yet. Run: php cron.php --scan';
        return $lines;
    }

    $lines[] = '';
    $lines[] =
        'Last scan (' . date('Y-m-d H:i', (int) $scan['started_at']) . '):';

    $totals = $pdo->prepare(
        'SELECT category, bytes, files FROM scan_totals WHERE scan_id = ? ORDER BY bytes DESC',
    );
    $totals->execute([$scan['id']]);
    foreach ($totals->fetchAll() as $row) {
        $lines[] = sprintf(
            '  %-8s %10s in %s files',
            $row['category'],
            humanBytes((int) $row['bytes']),
            number_format((float) $row['files']),
        );
    }

    /*
     * The five biggest things, by name.
     *
     * Unmasked, because this goes to the address in the config and nowhere
     * else - the masking exists for people looking at the dashboard without a
     * password, and an email you asked to be sent to yourself is not that.
     */
    $top = $pdo->prepare(
        'SELECT p.path, ps.bytes FROM path_sizes ps JOIN paths p ON p.id = ps.path_id
         WHERE ps.scan_id = ? AND p.kind = \'file\' ORDER BY ps.bytes DESC LIMIT 5',
    );
    $top->execute([$scan['id']]);
    $biggest = $top->fetchAll();

    if ($biggest) {
        $lines[] = '';
        $lines[] = 'Largest files:';
        foreach ($biggest as $row) {
            $lines[] = sprintf(
                '  %10s  %s',
                humanBytes((int) $row['bytes']),
                $row['path'],
            );
        }
    }

    return $lines;
}

/**
 * Sends the status report on demand, from the dashboard.
 *
 * Deliberately goes to the configured recipient rather than to an address in
 * the request: a monitor that will email arbitrary people its file listing on
 * request is an open relay with a disk inventory attached.
 *
 * @return array{sent: bool, to: string, error: ?string}
 */
function sendTestEmail(array $config, PDO $pdo): array
{
    $rules = $config['alerts'] ?? [];
    $to = trim((string) ($rules['to'] ?? ''));

    if ($to === '') {
        return [
            'sent' => false,
            'to' => '',
            'error' => 'No alert address is configured.',
        ];
    }
    if (!function_exists('mail')) {
        return [
            'sent' => false,
            'to' => $to,
            'error' => 'mail() is not available on this host.',
        ];
    }

    $host = php_uname('n');
    $body = array_merge(
        [
            'Zogograph on ' . $host,
            '',
            'This is a test. Nothing has tripped.',
            '',
        ],
        statusReport($pdo),
        [
            '',
            'Alerts are ' .
            ($rules['enabled'] ?? false
                ? 'enabled'
                : 'DISABLED in the config') .
            '.',
        ],
    );

    $headers = ['Auto-Submitted: auto-generated'];
    $from = trim((string) ($rules['from'] ?? ''));
    if ($from !== '') {
        $headers[] = 'From: ' . $from;
    }

    $sent = @mail(
        $to,
        "[test] $host Zogograph",
        implode("\n", $body),
        implode("\r\n", $headers),
    );

    return [
        'sent' => (bool) $sent,
        'to' => $to,
        'error' => $sent
            ? null
            : 'mail() returned false. The host may not accept mail from PHP.',
    ];
}

function humanBytes(?int $bytes): string
{
    if ($bytes === null) {
        return 'unknown';
    }
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
    $value = (float) $bytes;
    $i = 0;
    while (abs($value) >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return sprintf($i === 0 ? '%d %s' : '%.2f %s', $value, $units[$i]);
}

/*
 * A span of time, in the coarsest unit that still says something.
 *
 * Seconds are a real branch rather than being rounded up into "1 minutes".
 * This started life writing projections - "full in 3.5 days" - where nothing
 * under an hour was worth a word. It is now also used to say how long a process
 * has been running, and there the small end is the whole message: a pid that
 * has been alive four seconds when the screen claims eleven days is the
 * evidence that the number was recycled, and rounding it to a minute throws
 * away exactly the fact being reported.
 */
function humanDuration(float $seconds): string
{
    if ($seconds < 60) {
        $whole = max(0, (int) round($seconds));
        return $whole . ($whole === 1 ? ' second' : ' seconds');
    }
    if ($seconds < 3600) {
        $whole = (int) round($seconds / 60);
        return $whole . ($whole === 1 ? ' minute' : ' minutes');
    }
    if ($seconds < 172800) {
        return sprintf('%.1f hours', $seconds / 3600);
    }
    return sprintf('%.1f days', $seconds / 86400);
}

/**
 * One alert per domain that is close enough to expiry to say something about.
 *
 * Four steps, per domain, so one expiring name cannot silence another - the
 * cooldown is keyed on rule and path together and these carry no path, so a
 * shared rule name would mean the first domain to fire suppressed the rest for
 * a day.
 *
 * @return list<array<string, mixed>>
 */
function domainAlerts(array $config, PDO $pdo): array
{
    try {
        $domains = domainList($pdo);
    } catch (Throwable) {
        return [];
    }

    $out = [];

    foreach ($domains as $domain) {
        if ($domain['days_left'] === null) {
            continue;
        }

        $urgency = domainUrgency((int) $domain['days_left']);
        if ($urgency === null) {
            continue;
        }

        /*
         * A domain set to renew itself is not silent, but it is quieter.
         *
         * Auto-renew fails often enough - an expired card, a registrar that
         * changed its terms - that saying nothing would be wrong. Saying it at
         * the same volume as an unprotected domain would be wrong too.
         */
        $severity =
            $domain['auto_renew'] === true &&
            $urgency['severity'] !== 'critical'
                ? 'info'
                : $urgency['severity'];

        $out[] = [
            'rule' => 'domain_expiry:' . $domain['name'],
            'severity' => $severity,
            'subject' => sprintf('%s %s', $domain['name'], $urgency['when']),
            'detail' => implode(
                "\n",
                array_filter([
                    $domain['registrar']
                        ? 'Registered through ' . $domain['registrar'] . '.'
                        : '',
                    $domain['auto_renew'] === true
                        ? 'The registrar says auto-renew is on, which is not the same as it ' .
                            'working - an expired card is the usual reason it does not.'
                        : ($domain['auto_renew'] === false
                            ? 'Auto-renew is off, so this will not renew itself.'
                            : ''),
                    'Nothing else on this dashboard can see this coming: every server measure ' .
                    'stays green until the name stops resolving, and then everything fails at ' .
                    'once for a reason that is in no log.',
                ]),
            ),
            'group' => 'domains',
            'days_left' => (int) $domain['days_left'],
            'domain' => (string) $domain['name'],
        ];
    }

    /*
     * Loudest first, then soonest.
     *
     * The email lists them in this order and takes the highest as its own
     * severity, so the first line of a message about six domains is the one
     * that needs doing today.
     */
    usort(
        $out,
        fn(array $a, array $b): int => [
            severityRank($b['severity']),
            $a['days_left'],
        ] <=> [severityRank($a['severity']), $b['days_left']],
    );

    return $out;
}
