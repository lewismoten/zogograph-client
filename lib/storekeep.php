<?php
declare(strict_types=1);

/*
 * Looking after the two databases this tool keeps.
 *
 * A monitor that fills the disk it is watching has failed in the most
 * embarrassing way available to it, and the honest way to avoid that is not to
 * hope - it is to show what each thing costs, what it is set to keep, and what
 * the file will settle at if nobody touches it again.
 *
 * Three questions, each of which the retention page could not previously
 * answer:
 *
 *   what is in there    how many rows, and how many bytes those rows occupy.
 *                       Rows alone are misleading: a hundred thousand vitals
 *                       readings are smaller than four hundred incident
 *                       snapshots.
 *   what will it become  every table here grows at a rate that can be measured
 *                       from what is already stored. Given a retention setting,
 *                       the size it converges on is arithmetic, not a guess -
 *                       and "this settles at 40 MB" is a different conversation
 *                       from "this is 12 MB and rising".
 *   what can go safely   which is not the same as "what can be deleted". The
 *                       readings beside an incident are the reason the incident
 *                       record is worth having, and the catalogue says so.
 *
 * Sizes come from `dbstat`, a virtual table SQLite provides only when compiled
 * with SQLITE_ENABLE_DBSTAT_VTAB. It is present in most builds and absent in
 * some, so every size here is marked as measured or unavailable and the page
 * says which. Guessing a size from row counts and calling it a measurement
 * would be worse than not showing one.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/spans.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/retention.php';

/**
 * Everything with a retention setting, and what it costs to keep.
 *
 * `days_key` is the config key under `retention`. `recommended` is what this
 * install would set if nobody had an opinion, and `why` says what the
 * recommendation is protecting - because several of these default to keeping
 * for ever and that needs a reason attached, not just a zero.
 *
 * @return list<array<string, mixed>>
 */
function retentionCatalogue(): array
{
    return [
        [
            'key' => 'raw_days',
            // Seconds between rows once this tier is settled; null means
            // whatever the collector's own interval turns out to be.
            'cadence' => null,
            'label' => 'Readings at full resolution',
            'table' => 'vitals',
            'column' => 'taken_at',
            'recommended' => 7,
            'never_recommended' => false,
            'what' => 'Every reading exactly as the collector took it.',
            'why' => 'A week is long enough to look back through an incident minute by '
                . 'minute. Older than that, the minute-by-minute detail is almost never '
                . 'what anybody wants and it is by far the largest thing here.',
            'rolls_up' => true,
        ],
        [
            'key' => 'five_minute_days',
            // Seconds between rows once this tier is settled; null means
            // whatever the collector's own interval turns out to be.
            'cadence' => 300,
            'label' => 'Readings at five minutes',
            'table' => 'vitals',
            'column' => 'taken_at',
            'recommended' => 30,
            'never_recommended' => false,
            'what' => 'The same readings, merged into five-minute buckets.',
            'why' => 'A month at five minutes answers "when did this start" for anything '
                . 'that built up over weeks.',
            'rolls_up' => true,
        ],
        [
            'key' => 'hourly_days',
            // Seconds between rows once this tier is settled; null means
            // whatever the collector's own interval turns out to be.
            'cadence' => 3600,
            'label' => 'Readings at an hour',
            'table' => 'vitals',
            'column' => 'taken_at',
            'recommended' => 365,
            'never_recommended' => false,
            'what' => 'Hourly buckets, each holding the worst moment in its hour.',
            'why' => 'A year of hours is what makes "is this normal for August?" a question '
                . 'with an answer.',
            'rolls_up' => true,
        ],
        [
            'key' => 'daily_days',
            // Seconds between rows once this tier is settled; null means
            // whatever the collector's own interval turns out to be.
            'cadence' => 86400,
            'label' => 'Readings at a day',
            'table' => 'vitals',
            'column' => 'taken_at',
            'recommended' => 0,
            'never_recommended' => true,
            'what' => 'One row per day, for ever.',
            'why' => 'A few hundred rows a year. Deleting these saves almost nothing and '
                . 'throws away the only long record of what this machine used to do.',
            'rolls_up' => false,
        ],
        [
            'key' => 'alert_days',
            'label' => 'Alerts raised',
            'table' => 'alerts',
            'column' => 'raised_at',
            'recommended' => 0,
            'never_recommended' => true,
            'what' => 'Each alert, with what it said and whether anybody read it.',
            'why' => 'Small, and the index into the incident evidence. An incident whose '
                . 'alert has been deleted is a folder with no label on it.',
            'rolls_up' => false,
        ],
        [
            'key' => 'incident_days',
            'label' => 'Incident evidence',
            'table' => 'incidents',
            'column' => 'taken_at',
            'recommended' => 0,
            'never_recommended' => true,
            'what' => 'A snapshot of what the machine was doing at the moment each alert '
                . 'fired.',
            'why' => 'One per alerting evaluation, not one per reading. This is the only '
                . 'record of what actually happened, and it is the thing you will want in '
                . 'six months when it happens again.',
            'rolls_up' => false,
        ],
        [
            'key' => 'action_days',
            'label' => 'Action log',
            'table' => 'actions',
            'column' => 'at',
            'recommended' => 0,
            'never_recommended' => false,
            'what' => 'Every action taken through this dashboard: deletions, scans, '
                . 'lookups, sign-ins.',
            'why' => 'This is the audit trail. If something was deleted through here, this '
                . 'is where it says so - which matters most exactly when somebody is asking '
                . 'what happened.',
            'rolls_up' => false,
        ],
        [
            'key' => 'scan_days',
            'label' => 'Disk scans',
            'table' => 'scans',
            'column' => 'started_at',
            'recommended' => 400,
            'never_recommended' => false,
            'what' => 'Each walk of the watched directory and the sizes it found.',
            'why' => 'Just over a year, so a full year of growth is always comparable with '
                . 'the same month last year.',
            'rolls_up' => false,
        ],
        [
            'key' => 'process_days',
            'label' => 'Process samples',
            'table' => 'process_samples',
            'column' => 'taken_at',
            'recommended' => 400,
            'never_recommended' => false,
            'what' => 'Which processes were running and what they were using.',
            'why' => 'Matches the disk scans, so the two can be read side by side.',
            'rolls_up' => false,
        ],
        [
            'key' => 'remote_vital_days',
            'label' => 'Remote vital deliveries',
            'table' => 'remote_peer_vitals',
            'column' => 'taken_at',
            'recommended' => 400,
            'never_recommended' => false,
            'what' => 'Accepted vital payloads sent by paired remote Zogograph installations.',
            'why' => 'Matches local scan history while keeping fleet-delivery evidence bounded.',
            'rolls_up' => false,
        ],
        [
            'key' => 'remote_receipt_days',
            'label' => 'Remote replay receipts',
            'table' => 'remote_peer_receipts',
            'column' => 'received_at',
            'recommended' => 2,
            'never_recommended' => false,
            'what' => 'Recently accepted peer/nonces used to reject replayed deliveries.',
            'why' => 'Two days exceeds the replay window while avoiding a permanent ledger of request nonces.',
            'rolls_up' => false,
        ],
    ];
}

/**
 * How large each table actually is, and how many rows it holds.
 *
 * Indexes are attributed to the table they belong to. An index is a real cost -
 * on url_results it is close to half the total - and hiding it in a separate
 * list makes every table look cheaper than it is.
 *
 * @return array{measured: bool, why: string, tables: array<string, array>, total: int}
 */
function storeTables(PDO $pdo): array
{
    $rows = [];
    foreach ($pdo->query(
        "SELECT name, type, tbl_name FROM sqlite_master
         WHERE type IN ('table','index') AND name NOT LIKE 'sqlite_%'"
    )->fetchAll() as $entry) {
        $rows[(string)$entry['name']] = (string)$entry['tbl_name'];
    }

    $sizes = [];
    $measured = true;
    $why = '';

    try {
        foreach ($pdo->query('SELECT name, SUM(pgsize) AS bytes FROM dbstat GROUP BY name')
                     ->fetchAll() as $entry) {
            $sizes[(string)$entry['name']] = (int)$entry['bytes'];
        }
    } catch (Throwable $e) {
        /*
         * dbstat is optional in SQLite and this build does not have it.
         *
         * Reported rather than worked around: an estimate presented beside real
         * row counts would be read as a measurement, and the decision somebody
         * makes from it - what to stop keeping - deserves better than a guess.
         */
        $measured = false;
        $why = 'This SQLite build has no dbstat table, so per-table sizes cannot be '
            . 'measured. Row counts and the total file size are still exact.';
    }

    $tables = [];
    foreach ($rows as $name => $owner) {
        if (!isset($tables[$owner])) {
            $tables[$owner] = ['rows' => null, 'bytes' => 0, 'index_bytes' => 0];
        }

        $bytes = $sizes[$name] ?? 0;
        if ($name === $owner) {
            $tables[$owner]['bytes'] += $bytes;
        } else {
            $tables[$owner]['index_bytes'] += $bytes;
        }
    }

    foreach (array_keys($tables) as $name) {
        try {
            $tables[$name]['rows'] = (int)$pdo->query(
                'SELECT COUNT(*) FROM ' . quoteName($name)
            )->fetchColumn();
        } catch (Throwable) {
            $tables[$name]['rows'] = null;
        }
        $tables[$name]['total_bytes'] = $tables[$name]['bytes'] + $tables[$name]['index_bytes'];
    }

    uasort($tables, fn(array $a, array $b): int => $b['total_bytes'] <=> $a['total_bytes']);

    return [
        'measured' => $measured,
        'why' => $why,
        'tables' => $tables,
        'total' => array_sum(array_column($tables, 'total_bytes')),
    ];
}

/**
 * An identifier quoted for use in SQL.
 *
 * Table names here come from sqlite_master rather than from a request, so this
 * is belt and braces - but a name is being concatenated into a statement, and
 * the day somebody passes one in from elsewhere the quoting should already be
 * there rather than needing to be remembered.
 */
function quoteName(string $name): string
{
    return '"' . str_replace('"', '""', $name) . '"';
}

/**
 * The state of every retained thing: what it costs now and what it will cost.
 *
 * The projection is the point of this. A table with a 400-day setting that has
 * only been collecting for 30 days is at less than a tenth of its eventual
 * size, and nothing on the page said so - which is how somebody sets a generous
 * retention on a nearly-full disk and finds out four hundred days later.
 *
 * @return array<string, mixed>
 */
function retentionReport(PDO $pdo, array $config): array
{
    $rules = retentionRules($config);
    $store = storeTables($pdo);
    $now = time();

    /*
     * The four reading tiers share one table, so they cannot each be measured
     * as "the size of vitals" - that was counting the same bytes four times.
     *
     * Instead: measure what one row costs, work out which tier each existing
     * row currently falls in, and project each tier from the cadence it will
     * settle at. The tiers are boundaries in one table, not four tables.
     */
    $perRow = null;
    $vitals = $store['tables']['vitals'] ?? null;
    if ($vitals !== null && ($vitals['rows'] ?? 0) > 0) {
        $perRow = $vitals['total_bytes'] / $vitals['rows'];
    }
    $interval = collectorInterval($pdo);

    $entries = [];
    $projectedTotal = 0;
    $growthPerYear = 0.0;
    $known = true;

    foreach (retentionCatalogue() as $item) {
        $table = $item['table'];
        $facts = $store['tables'][$table] ?? null;

        $span = tableSpan($pdo, $table, $item['column']);
        $days = (int)($rules[$item['key']] ?? 0);
        $isTier = $table === 'vitals';

        $entry = $item + [
            'days' => $days,
            'forever' => $days === 0,
            'oldest_at' => $span['oldest'],
            'newest_at' => $span['newest'],
            'covers' => $span['oldest'] === null ? 0 : $now - $span['oldest'],
            'shares_table' => $isTier,
            'interval' => $isTier ? $interval : null,
        ];

        if ($isTier) {
            $window = tierWindow($rules, $item['key'], $now);
            $entry['rows'] = countBetween($pdo, 'vitals', 'taken_at', $window['from'], $window['to']);
            $entry['bytes'] = $perRow === null ? null : (int)round($entry['rows'] * $perRow);

            /*
             * Rows this tier holds once it has filled.
             *
             * A tier's depth is its own setting minus the one before it: the
             * five-minute tier covers days 7 to 30, not days 0 to 30. Getting
             * that wrong makes every tier look like it holds the whole history.
             */
            $depth = tierDepth($rules, $item['key']);
            $cadence = $item['cadence'] ?? $interval;

            if ($depth === null) {
                // The daily tier never stops, so it has no settled size. What
                // it costs per year is the answerable question.
                $entry['projected'] = null;
                $entry['per_year'] = $perRow === null ? null : (int)round($perRow * 365);
                $known = false;
            } elseif ($cadence !== null && $perRow !== null) {
                $entry['projected'] = (int)round($depth * SPAN_DAY / $cadence * $perRow);
                $entry['at_rest'] = $entry['covers'] >= $days * SPAN_DAY;
            } else {
                $entry['projected'] = null;
            }

            $entry['rate_per_day'] = $cadence === null || $perRow === null
                ? null
                : $perRow * SPAN_DAY / $cadence;
        } else {
            $entry['rows'] = $facts['rows'] ?? null;
            $entry['bytes'] = $facts === null ? null : $facts['total_bytes'];
            $entry['projected'] = null;
            $entry['at_rest'] = false;
            $entry['rate_per_day'] = null;

            /*
             * The projection, and when it cannot honestly be made.
             *
             * Rate needs a span to divide by. Under a day of history means
             * dividing by something close to zero, which produces a confident
             * number that is mostly noise - so it says "not yet" rather than
             * inventing one.
             */
            if ($entry['bytes'] !== null && $entry['covers'] >= SPAN_DAY && $entry['bytes'] > 0) {
                $perDay = $entry['bytes'] / ($entry['covers'] / SPAN_DAY);
                $entry['rate_per_day'] = $perDay;

                if ($days > 0) {
                    $entry['projected'] = (int)round($perDay * $days);
                    // Already full: the projection is the current size, not growth.
                    $entry['at_rest'] = $entry['covers'] >= $days * SPAN_DAY;
                }
            }
        }

        if ($entry['projected'] !== null) {
            $projectedTotal += $entry['projected'];
        } elseif ($entry['bytes'] !== null) {
            /*
             * Unbounded: it does not converge, so the total cannot either.
             *
             * What it can do is say how fast the unbounded part grows. "Settles
             * near 3.4 MB plus about 60 KB a year" is a complete answer;
             * "at least 3.4 MB" leaves somebody wondering whether that means
             * next month or next decade.
             */
            $known = false;
            $projectedTotal += $entry['bytes'];

            $yearly = $entry['per_year']
                ?? ($entry['rate_per_day'] === null ? null : $entry['rate_per_day'] * 365);
            if ($yearly !== null) {
                $growthPerYear += $yearly;
            }
        }

        $entries[] = $entry;
    }

    /*
     * Tables with no retention rule at all.
     *
     * They are usually small and there is nothing to set, but leaving them out
     * meant the parts of the page never added up to the file size - and an
     * accounting that does not add up is one nobody trusts the rest of.
     */
    $accounted = array_column(retentionCatalogue(), 'table');
    $others = [];
    foreach ($store['tables'] as $name => $facts) {
        if (in_array($name, $accounted, true)) {
            continue;
        }
        $others[] = ['table' => $name] + $facts;
    }

    return [
        'entries' => $entries,
        'others' => $others,
        'measured' => $store['measured'],
        'why' => $store['why'],
        'file' => databaseFacts($pdo, $config),
        'projected_total' => $projectedTotal,
        'projected_known' => $known,
        'growth_per_year' => (int)round($growthPerYear),
        'defaults' => array_column(retentionCatalogue(), 'recommended', 'key'),
    ];
}

/**
 * The oldest and newest thing in a table, or nulls if it is empty.
 *
 * @return array{oldest: ?int, newest: ?int}
 */
function tableSpan(PDO $pdo, string $table, string $column): array
{
    try {
        $row = $pdo->query(
            'SELECT MIN(' . quoteName($column) . ') AS oldest, '
            . 'MAX(' . quoteName($column) . ') AS newest FROM ' . quoteName($table)
        )->fetch();
    } catch (Throwable) {
        return ['oldest' => null, 'newest' => null];
    }

    return [
        'oldest' => $row['oldest'] === null ? null : (int)$row['oldest'],
        'newest' => $row['newest'] === null ? null : (int)$row['newest'],
    ];
}

/**
 * The file itself: how large, how much of it is empty, where it lives.
 *
 * The write-ahead log is counted separately and deliberately. It is a real file
 * taking real space, it can be far larger than the database during a long
 * write, and somebody comparing this page against `ls` should not have to
 * wonder where the difference came from.
 *
 * @return array<string, mixed>
 */
function databaseFacts(PDO $pdo, array $config, ?string $path = null): array
{
    $path ??= (string)($config['database'] ?? '');

    $pageSize = (int)$pdo->query('PRAGMA page_size')->fetchColumn();
    $pages = (int)$pdo->query('PRAGMA page_count')->fetchColumn();
    $free = (int)$pdo->query('PRAGMA freelist_count')->fetchColumn();

    $wal = is_file($path . '-wal') ? (int)filesize($path . '-wal') : 0;

    return [
        'path' => $path,
        'name' => basename($path),
        'bytes' => is_file($path) ? (int)filesize($path) : $pages * $pageSize,
        'wal_bytes' => $wal,
        'page_size' => $pageSize,
        'pages' => $pages,
        'free_pages' => $free,
        'free_bytes' => $free * $pageSize,
        'free_share' => $pages > 0 ? $free / $pages : 0.0,
        'journal' => (string)$pdo->query('PRAGMA journal_mode')->fetchColumn(),
        'writable' => is_writable($path) && is_writable(dirname($path)),
        'integrity' => databaseIntegrity($pdo, $path),
    ];
}

/**
 * A bounded integrity check and the recovery snapshots available beside a DB.
 *
 * `quick_check(1)` is intentionally used on an interactive dashboard: it
 * catches corruption without turning a storage page into an unbounded full
 * table scan. The full check remains a maintenance operation for a future
 * recovery surface. Snapshot names never contain credentials and the files
 * remain under data/, which the web server denies.
 */
function databaseIntegrity(PDO $pdo, string $path): array
{
    try {
        $result = $pdo->query('PRAGMA quick_check(1)')->fetchColumn();
        $ok = $result === 'ok';
        $message = $ok ? 'SQLite quick check passed.' : (string)$result;
    } catch (Throwable $e) {
        $ok = false;
        $message = 'SQLite quick check could not run: ' . $e->getMessage();
    }
    $backups = glob(dirname($path) . '/migration-backups/' . basename($path) . '.schema-*.db') ?: [];
    $newest = 0;
    foreach ($backups as $backup) {
        $newest = max($newest, (int)@filemtime($backup));
    }
    return ['ok' => $ok, 'message' => $message, 'checked_at' => time(),
        'migration_backups' => count($backups), 'newest_backup_at' => $newest ?: null];
}

/**
 * The deliberately explicit full SQLite maintenance check.
 *
 * Unlike `quick_check`, this verifies table and index content as well as the
 * b-tree structure. SQLite still stops after fifty findings so a damaged file
 * cannot make an API response unbounded; the operator gets a clear direction
 * to restore the private migration copy or a known-good backup.
 *
 * @return array{ok: bool, messages: list<string>, checked_at: int}
 */
function databaseFullIntegrity(PDO $pdo): array
{
    try {
        $messages = array_map('strval', $pdo->query('PRAGMA integrity_check(50)')->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable $e) {
        return ['ok' => false, 'messages' => ['SQLite integrity check could not run: ' . $e->getMessage()],
            'checked_at' => time()];
    }

    $ok = $messages === ['ok'];
    return ['ok' => $ok, 'messages' => $ok ? ['SQLite integrity check passed.'] : $messages,
        'checked_at' => time()];
}

/**
 * Rewrites the file to give the empty pages back to the disk.
 *
 * Unconditional, unlike reclaimSpace(), which the cron uses and which declines
 * below a threshold. This is somebody pressing a button having been shown the
 * number, and second-guessing that would be rude.
 *
 * A VACUUM needs room for a second copy of the database while it runs. On a
 * tool that exists to watch for full disks, refusing when that room is not
 * there is the whole point rather than an inconvenience.
 *
 * @return array<string, mixed>
 */
function compactNow(PDO $pdo, array $config, ?string $path = null): array
{
    $before = databaseFacts($pdo, $config, $path);

    if (!$before['writable']) {
        throw new ActionRefused('that database is not writable from here');
    }

    $room = @disk_free_space(dirname($before['path']));
    if ($room !== false && $room < $before['bytes'] * 1.1) {
        throw new ActionRefused(sprintf(
            'a rewrite needs room for a second copy - %s free, %s needed',
            humanBytes((int)$room),
            humanBytes((int)($before['bytes'] * 1.1))
        ));
    }

    $started = microtime(true);
    try {
        $pdo->exec('VACUUM');
    } catch (Throwable $e) {
        throw new ActionRefused('the rewrite failed: ' . $e->getMessage());
    }

    clearstatcache(true, $before['path']);
    $after = databaseFacts($pdo, $config, $path);

    return [
        'before' => $before,
        'after' => $after,
        'saved' => max(0, $before['bytes'] - $after['bytes']),
        'seconds' => round(microtime(true) - $started, 2),
    ];
}

/**
 * Deletes one catalogue entry's rows older than a given age.
 *
 * Only tables named in the catalogue, and only by their own date column, so
 * there is no path from a request to an arbitrary DELETE. `$days` of zero means
 * everything, which is a thing somebody may genuinely want and which the page
 * asks about twice before doing.
 *
 * Rows protected by a nearby incident are exempt for the readings, exactly as
 * they are during compaction - the hour around what went wrong is the reason
 * any of this is kept.
 *
 * @return array{removed: int, protected: int, label: string}
 */
function purgeEntry(PDO $pdo, array $config, string $key, int $days): array
{
    $item = null;
    foreach (retentionCatalogue() as $candidate) {
        if ($candidate['key'] === $key) {
            $item = $candidate;
            break;
        }
    }

    if ($item === null) {
        throw new ActionRefused('there is nothing by that name to clear');
    }

    $cutoff = $days > 0 ? time() - $days * SPAN_DAY : time() + 1;

    $where = quoteName($item['column']) . ' < :cutoff';
    $protected = 0;
    $pad = 0;

    if ($item['table'] === 'vitals') {
        $rules = retentionRules($config);
        $pad = max(0, (int)($rules['protect_hours'] ?? 2)) * SPAN_HOUR;
        if ($pad > 0) {
            // protectionClause() already opens with AND, because it is written
            // to be appended to a WHERE that has something in it.
            $where .= protectionClause('vitals');
            $protected = protectedReadings($pdo, $pad);
        }
    }

    $statement = $pdo->prepare('DELETE FROM ' . quoteName($item['table']) . ' WHERE ' . $where);
    $statement->bindValue(':cutoff', $cutoff, PDO::PARAM_INT);
    if ($pad > 0) {
        $statement->bindValue(':pad', $pad, PDO::PARAM_INT);
    }
    $statement->execute();

    return [
        'removed' => $statement->rowCount(),
        'protected' => $protected,
        'label' => $item['label'],
    ];
}

/**
 * Writes new retention settings back to the config file.
 *
 * Bounded at ten years because a number larger than that is somebody meaning
 * "for ever" and typing digits instead, and the zero that means exactly that is
 * right there.
 *
 * @return array<string, int>
 */
function retentionSettings(array $posted): array
{
    $out = [];

    foreach (retentionCatalogue() as $item) {
        $key = $item['key'];
        if (!array_key_exists($key, $posted)) {
            continue;
        }

        $value = (int)$posted[$key];
        if ($value < 0) {
            throw new ActionRefused('a number of days cannot be negative');
        }
        if ($value > 3650) {
            throw new ActionRefused('use 0 for "keep for ever" rather than a very large number');
        }

        $out[$key] = $value;
    }

    /*
     * The tiers have to stay in order.
     *
     * Full resolution for 30 days and five-minute for 7 is not a stricter
     * setting, it is a contradiction: the compactor works outside in and would
     * merge rows into five-minute buckets that the raw rule then says to keep.
     * Caught here rather than producing a database that quietly disagrees with
     * its own settings.
     */
    $ladder = ['raw_days', 'five_minute_days', 'hourly_days'];
    $previous = 0;
    foreach ($ladder as $key) {
        $value = $out[$key] ?? null;
        if ($value === null || $value === 0) {
            continue;
        }
        if ($value < $previous) {
            throw new ActionRefused(
                'each tier has to reach at least as far back as the one before it'
            );
        }
        $previous = $value;
    }

    return $out;
}

/**
 * The date range one reading tier currently covers.
 *
 * Each tier begins where the one before it ends. A setting of zero means the
 * tier runs back for ever, which is only sensible on the last one.
 *
 * @return array{from: ?int, to: ?int}
 */
function tierWindow(array $rules, string $key, int $now): array
{
    $order = ['raw_days', 'five_minute_days', 'hourly_days', 'daily_days'];
    $at = array_search($key, $order, true);
    if ($at === false) {
        return ['from' => null, 'to' => null];
    }

    $before = $at === 0 ? 0 : (int)($rules[$order[$at - 1]] ?? 0);
    $mine = (int)($rules[$key] ?? 0);

    return [
        // Older bound: null when this tier runs back indefinitely.
        'from' => $mine > 0 ? $now - $mine * SPAN_DAY : null,
        'to' => $now - $before * SPAN_DAY,
    ];
}

/**
 * How many days deep one tier is, or null if it never ends.
 *
 * The five-minute tier set to 30 with raw at 7 is twenty-three days deep, not
 * thirty. Reading its own number as its depth is what made every tier look
 * like it held the entire history.
 */
function tierDepth(array $rules, string $key): ?int
{
    $order = ['raw_days', 'five_minute_days', 'hourly_days', 'daily_days'];
    $at = array_search($key, $order, true);
    if ($at === false) {
        return null;
    }

    $mine = (int)($rules[$key] ?? 0);
    if ($mine === 0) {
        return null;
    }

    $before = $at === 0 ? 0 : (int)($rules[$order[$at - 1]] ?? 0);
    return max(0, $mine - $before);
}

/** Rows in a table between two moments, either of which may be open. */
function countBetween(PDO $pdo, string $table, string $column, ?int $from, ?int $to): int
{
    $where = [];
    $bind = [];

    if ($from !== null) {
        $where[] = quoteName($column) . ' >= ?';
        $bind[] = $from;
    }
    if ($to !== null) {
        $where[] = quoteName($column) . ' < ?';
        $bind[] = $to;
    }

    $sql = 'SELECT COUNT(*) FROM ' . quoteName($table)
        . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where));

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

/**
 * How often readings actually arrive, in seconds.
 *
 * Measured as the median gap between the most recent readings rather than read
 * from a crontab this process cannot see. The median rather than the mean
 * because a single overnight outage is one enormous gap, and a mean including
 * it would say the collector runs every four hours when it runs every minute.
 *
 * Only the newest rows, because older ones have been compacted and their gaps
 * describe the compactor's buckets rather than the collector's schedule.
 */
function collectorInterval(PDO $pdo, int $sample = 60): ?int
{
    try {
        $times = $pdo->query(
            'SELECT taken_at FROM vitals ORDER BY taken_at DESC LIMIT ' . max(3, $sample)
        )->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (Throwable) {
        return null;
    }

    if (count($times) < 3) {
        return null;
    }

    $gaps = [];
    for ($i = 1, $n = count($times); $i < $n; $i++) {
        $gap = (int)$times[$i - 1] - (int)$times[$i];
        if ($gap > 0) {
            $gaps[] = $gap;
        }
    }

    if ($gaps === []) {
        return null;
    }

    sort($gaps);
    return (int)$gaps[intdiv(count($gaps), 2)];
}
