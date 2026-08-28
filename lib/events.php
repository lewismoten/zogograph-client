<?php
declare(strict_types=1);

/*
 * Dates that matter, from everything that knows one.
 *
 * Three sources, and the point of the page is that they are on one calendar:
 *
 *   discovered   domain expiries, and the date the disk is projected to fill.
 *                This machine works these out and they change on their own.
 *   kept         renewals, invoices, anything the operator knows and nothing
 *                here could ever find out. These do not change unless somebody
 *                changes them.
 *   happened     alerts that fired, scans that ran, things that were deleted.
 *                The past three months, so "what changed around then" has an
 *                answer.
 *
 * A recurring event is stored once with its rule rather than as rows for every
 * future occurrence: a monthly renewal is one fact, and writing it out twelve
 * times means twelve rows to edit when the date moves.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/domains.php';
require_once __DIR__ . '/hostdb.php';

/** How the calendar behaves: three months back, twelve forward. */
const CALENDAR_BACK_MONTHS = 3;
const CALENDAR_FORWARD_MONTHS = 12;

/** @return array<string, string> */
function eventRepeats(): array
{
    return [
        'once' => 'once',
        'monthly' => 'every month',
        'quarterly' => 'every three months',
        'yearly' => 'every year',
    ];
}

function eventSave(PDO $pdo, array $input): int
{
    $title = trim((string)($input['title'] ?? ''));
    if ($title === '') {
        throw new ActionRefused('an event needs a title');
    }
    if (mb_strlen($title) > 120) {
        throw new ActionRefused('that title is too long');
    }

    $when = trim((string)($input['at'] ?? ''));
    $at = strtotime($when);
    if ($at === false) {
        throw new ActionRefused('that is not a date this understands');
    }

    $repeats = (string)($input['repeats'] ?? 'once');
    if (!isset(eventRepeats()[$repeats])) {
        throw new ActionRefused('that is not one of the repeat options');
    }

    $link = trim((string)($input['link'] ?? ''));
    if ($link !== '') {
        $parts = parse_url($link);
        if ($parts === false || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            throw new ActionRefused('a link has to be an http or https URL');
        }
    }

    $id = (int)($input['id'] ?? 0);
    $fields = [
        $title,
        $at,
        $repeats,
        $link === '' ? null : $link,
        trim((string)($input['note'] ?? '')) ?: null,
        empty($input['priority']) ? 0 : 1,
    ];

    if ($id > 0) {
        $pdo->prepare(
            'UPDATE events SET title = ?, at = ?, repeats = ?, link = ?, note = ?, priority = ?
              WHERE id = ?'
        )->execute([...$fields, $id]);
        return $id;
    }

    $pdo->prepare(
        'INSERT INTO events (title, at, repeats, link, note, priority, added_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([...$fields, time()]);

    return (int)$pdo->lastInsertId();
}

/**
 * Every occurrence of a stored event inside a window.
 *
 * A recurring event is one row and many occurrences; this expands it. Resolving
 * one marks the *series* as dealt with, which is right for a one-off and is why
 * a recurring event's resolution only silences the occurrence it was made for -
 * "I paid this month's invoice" must not silence next month's.
 *
 * @return list<array>
 */
function eventOccurrences(PDO $pdo, int $from, int $to): array
{
    try {
        $rows = $pdo->query('SELECT * FROM events ORDER BY at')->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $out = [];

    foreach ($rows as $row) {
        $at = (int)$row['at'];
        $repeats = (string)$row['repeats'];

        if ($repeats === 'once') {
            if ($at >= $from && $at <= $to) {
                $out[] = eventOccurrence($row, $at);
            }
            continue;
        }

        $months = match ($repeats) {
            'monthly' => 1,
            'quarterly' => 3,
            default => 12,
        };

        /*
         * Each occurrence is counted from the original date, not from the last.
         *
         * Stepping "+1 month" repeatedly is the classic month-arithmetic trap:
         * the 31st of January plus a month is the 3rd of March, because
         * February has no 31st and PHP rolls the excess forward - and from then
         * on the series is stuck on the 3rd for ever. Counting N months from
         * the original and clamping to the end of the target month keeps a
         * monthly event on the day it was set for: 31 Jan, 28 Feb, 31 Mar.
         */
        for ($step = 0; $step < 400; $step++) {
            $when = addMonths($at, $months * $step);
            if ($when > $to) {
                break;
            }
            if ($when >= $from) {
                $out[] = eventOccurrence($row, $when);
            }
        }
    }

    usort($out, fn(array $a, array $b): int => $a['at'] <=> $b['at']);
    return $out;
}

/**
 * A date N months on, clamped to the end of the month rather than overflowing.
 *
 * The 31st of January plus one month is the 28th of February here, and plus two
 * is the 31st of March - which is what somebody who set a reminder for "the
 * 31st" means, and what every calendar application does.
 */
function addMonths(int $at, int $months): int
{
    if ($months === 0) {
        return $at;
    }

    $day = (int)date('j', $at);
    $target = (int)strtotime(sprintf('first day of %+d months', $months), $at);

    $lastDay = (int)date('t', $target);
    $useDay = min($day, $lastDay);

    return (int)mktime(
        (int)date('G', $at),
        (int)date('i', $at),
        (int)date('s', $at),
        (int)date('n', $target),
        $useDay,
        (int)date('Y', $target)
    );
}

function eventOccurrence(array $row, int $at): array
{
    $resolved = (int)($row['resolved_at'] ?? 0);

    return [
        'id' => (int)$row['id'],
        'kind' => 'event',
        'title' => (string)$row['title'],
        'at' => $at,
        'repeats' => (string)$row['repeats'],
        'link' => $row['link'],
        'note' => $row['note'],
        'priority' => (bool)$row['priority'],
        /*
         * A resolution only covers occurrences it could have been about.
         *
         * Marking March's invoice paid must not silence April's. Anything
         * resolved after the occurrence date counts for it; anything before is
         * a resolution of an earlier one.
         */
        'resolved' => $resolved > 0 && $resolved >= $at,
        'resolved_at' => $resolved ?: null,
    ];
}

/**
 * Everything with a date, from every source, over the calendar's window.
 *
 * @return array{from: int, to: int, days: array<string, list<array>>, upcoming: list<array>}
 */
function calendarEntries(array $config, PDO $pdo): array
{
    $now = time();
    $from = (int)strtotime(CALENDAR_BACK_MONTHS . ' months ago midnight');
    $to = (int)strtotime('+' . CALENDAR_FORWARD_MONTHS . ' months last day of this month 23:59:59');

    $entries = [];

    foreach (eventOccurrences($pdo, $from, $to) as $event) {
        $entries[] = $event + [
            'level' => $event['resolved'] ? 'done' : ($event['priority'] ? 'warn' : 'info'),
        ];
    }

    foreach (domainList($pdo) as $domain) {
        // Certificate expiry is independent of registry WHOIS. A domain can
        // have no readable WHOIS expiry (or one far outside this window) while
        // its HTTPS certificate needs renewal this week.
        $certificate = $domain['certificate_expires_at'] ?? null;
        if ($certificate !== null && (int)$certificate >= $from && (int)$certificate <= $to) {
            $left = (int)$certificate - $now;
            $entries[] = [
                'kind' => 'certificate',
                'id' => (int)$domain['id'],
                'title' => $domain['name'] . ' HTTPS certificate expires',
                'at' => (int)$certificate,
                'level' => $left < 0 || $left <= 7 * 86400 ? 'error'
                    : ($left <= 30 * 86400 ? 'warn' : 'info'),
                'note' => 'Read from the HTTPS endpoint on the last domain check.',
                'link' => null,
                'priority' => $left <= 30 * 86400,
                'resolved' => false,
                'view' => 'domains',
            ];
        }

        if ($domain['expires_at'] === null
            || $domain['expires_at'] < $from || $domain['expires_at'] > $to) {
            continue;
        }

        $entries[] = [
            'kind' => 'domain',
            'id' => (int)$domain['id'],
            'title' => $domain['name'] . ' expires',
            'at' => (int)$domain['expires_at'],
            'level' => match ($domain['state']) {
                'expired' => 'error',
                'urgent' => 'error',
                'soon' => 'warn',
                default => 'info',
            },
            'note' => $domain['registrar'] ? 'Registered through ' . $domain['registrar'] : null,
            'link' => null,
            'priority' => in_array($domain['state'], ['expired', 'urgent'], true),
            'resolved' => false,
            'view' => 'domains',
        ];

    }

    /*
     * The date the disk is projected to fill, when there is a real trend.
     *
     * Only when the trend is sustained - a one-off delete produces a slope that
     * projects to a date, and putting that on a calendar would be inventing a
     * deadline out of a rounding error.
     */
    $projection = diskProjection($pdo);
    if ($projection !== null && $projection >= $from && $projection <= $to) {
        $entries[] = [
            'kind' => 'projection',
            'id' => 0,
            'title' => 'Disk projected to be full',
            'at' => $projection,
            'level' => $projection < time() + 14 * 86400 ? 'error' : 'warn',
            'note' => 'From the trend over the last day. It moves as the trend does, and a '
                . 'single large delete will push it out again.',
            'link' => null,
            'priority' => true,
            'resolved' => false,
            'view' => 'storage',
        ];
    }

    /*
     * What already happened, for the months behind us.
     *
     * Only critical alerts: a calendar with every warning on it is a calendar
     * with a mark on every day, which conveys nothing. The alerts page is where
     * the full record lives.
     */
    try {
        $stmt = $pdo->prepare(
            "SELECT id, raised_at, severity, subject FROM alerts
              WHERE raised_at BETWEEN ? AND ? AND severity = 'critical'
              ORDER BY raised_at DESC LIMIT 200"
        );
        $stmt->execute([$from, time()]);
        foreach ($stmt->fetchAll() as $alert) {
            $entries[] = [
                'kind' => 'alert',
                'id' => (int)$alert['id'],
                'title' => (string)$alert['subject'],
                'at' => (int)$alert['raised_at'],
                'level' => 'past',
                'note' => null,
                'link' => null,
                'priority' => false,
                'resolved' => false,
                'view' => 'alerts',
            ];
        }
    } catch (Throwable) {
        // No alerts table yet, which is not a calendar problem.
    }

    /*
     * A reset is a past event with operational meaning: a graph gap, a daemon
     * counter returning to zero, and often the explanation for a short outage.
     * host_boots is keyed by the boot time, so it remains one event even after
     * many collector passes observe the same machine.
     */
    try {
        $host = hostDb($config, false);
        if ($host !== null) {
            $boots = $host->prepare(
                'SELECT booted_at, kernel, ended FROM host_boots
                  WHERE booted_at BETWEEN ? AND ? ORDER BY booted_at DESC LIMIT 100'
            );
            $boots->execute([$from, time()]);
            foreach ($boots->fetchAll() as $boot) {
                $entries[] = [
                    'kind' => 'reset',
                    'id' => (int)$boot['booted_at'],
                    'title' => 'Host restarted',
                    'at' => (int)$boot['booted_at'],
                    'level' => 'past',
                    'note' => trim((string)($boot['ended'] ?? '')) ?: (
                        trim((string)($boot['kernel'] ?? '')) ?: null
                    ),
                    'link' => null,
                    'priority' => false,
                    'resolved' => false,
                    'view' => 'uptime',
                ];
            }
        }
    } catch (Throwable) {
        // Host collection is optional; no boot history is a valid answer.
    }

    $days = [];
    foreach ($entries as $entry) {
        $key = date('Y-m-d', $entry['at']);
        $days[$key][] = $entry;
    }

    $now = time();
    $upcoming = array_values(array_filter(
        $entries,
        fn(array $e): bool => $e['at'] >= $now && $e['level'] !== 'past' && !$e['resolved']
    ));
    usort($upcoming, fn(array $a, array $b): int => $a['at'] <=> $b['at']);

    /*
     * One line per thing, not per occurrence.
     *
     * A monthly renewal has twelve occurrences in this window and they would
     * fill the list, pushing everything else off the bottom - which is the
     * opposite of what a "what is next" list is for. The grid below still shows
     * every occurrence; this shows the next one of each.
     */
    $seen = [];
    $upcoming = array_values(array_filter($upcoming, function (array $e) use (&$seen): bool {
        $key = $e['kind'] . ':' . $e['id'];
        if (isset($seen[$key])) {
            return false;
        }
        $seen[$key] = true;
        return true;
    }));

    return [
        'from' => $from,
        'to' => $to,
        'days' => $days,
        'upcoming' => array_slice($upcoming, 0, 40),
        'back_months' => CALENDAR_BACK_MONTHS,
        'forward_months' => CALENDAR_FORWARD_MONTHS,
    ];
}

/**
 * When the disk is projected to be full, or null.
 *
 * Deliberately conservative: only a sustained trend counts, and only one
 * pointing at a date within the calendar's reach.
 */
function diskProjection(PDO $pdo): ?int
{
    try {
        $rows = $pdo->prepare(
            'SELECT taken_at, disk_free_bytes FROM vitals
              WHERE taken_at >= ? AND disk_free_bytes IS NOT NULL ORDER BY taken_at'
        );
        $rows->execute([time() - 86400]);
        $trend = seriesTrend($rows->fetchAll(), 'disk_free_bytes');
    } catch (Throwable) {
        return null;
    }

    if ($trend === null || empty($trend['sustained']) || ($trend['seconds_left'] ?? null) === null) {
        return null;
    }

    return time() + (int)$trend['seconds_left'];
}

/**
 * High-priority things about to happen, for the dashboard and the emails.
 *
 * The whole reason the priority flag exists: something marked important and due
 * within the window should interrupt, not wait to be found on a calendar page
 * nobody opened.
 *
 * @return list<array>
 */
function pressingEvents(array $config, PDO $pdo, int $withinDays = 14): array
{
    $now = time();
    $limit = $now + $withinDays * 86400;

    $out = [];
    foreach (eventOccurrences($pdo, $now - 86400, $limit) as $event) {
        if (!$event['priority'] || $event['resolved']) {
            continue;
        }
        $out[] = $event;
    }

    foreach (domainsNeedingAttention($pdo) as $domain) {
        if (!in_array($domain['state'], ['expired', 'urgent'], true)) {
            continue;
        }
        $out[] = [
            'kind' => 'domain',
            'title' => $domain['state'] === 'expired'
                ? $domain['name'] . ' has expired'
                : $domain['name'] . ' expires in ' . $domain['days_left'] . ' days',
            'at' => (int)$domain['expires_at'],
            'priority' => true,
            'resolved' => false,
            'note' => $domain['registrar'],
        ];
    }

    usort($out, fn(array $a, array $b): int => $a['at'] <=> $b['at']);
    return $out;
}
