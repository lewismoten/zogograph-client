<?php
declare(strict_types=1);

/*
 * Backups, as their own subject.
 *
 * On the machine this was written for, /backup holds 76 GiB and the account it
 * is backing up holds 4.5. Two thirds of the disk is copies of one twentieth of
 * it. Anybody looking at the account to work out why the server is filling will
 * come away with completely the wrong answer, and no amount of staring at
 * /home will fix that - which is why this is a section rather than another row
 * in a list of large directories.
 *
 * Four questions, in the order somebody actually asks them:
 *
 *   How much is this costing?      the total, and as a share of the disk
 *   Whose is it?                   per account, against their live data
 *   Is it useful?                  ages, counts, and how fast it grows
 *   What can I do?                 and crucially, what is safe to do
 *
 * The last one is the reason for most of the care in this file. Backup data is
 * the one thing on a server where a confident wrong suggestion is unrecoverable:
 * a monitor that says `rm -rf /backup/...` and is wrong has destroyed the thing
 * that would have fixed it. So every action carries a safety level and a reach,
 * nothing destructive is ever offered as a first suggestion, and where the
 * managed tool should do the work this says so instead of reaching around it.
 *
 * A note on what is known versus guessed. Where JetBackup's own CLI answers,
 * these figures are its metadata - account, date, type, destination. Where it
 * does not, all that exists is a directory called files0000000005, and this
 * says so rather than inventing an interpretation of it.
 */

require_once __DIR__ . '/collect.php';
require_once __DIR__ . '/alerts.php';

/** Where backups usually live, before anything is configured. */
function backupRoots(array $config): array
{
    $configured = $config['host']['backup_roots'] ?? null;
    if (is_array($configured) && $configured !== []) {
        return array_values(array_filter(array_map('strval', $configured)));
    }

    return ['/backup', '/backups', '/var/backup', '/var/backups', '/jetbackup'];
}

/**
 * Names that suggest a backup somebody made by hand.
 *
 * Reported as *probable*, never as fact. A directory called `archive` might be
 * a photo archive, and telling somebody they have a stale backup when they have
 * a live dataset is exactly the mistake that gets a monitor ignored - or worse,
 * obeyed.
 */
function probableBackupPatterns(): array
{
    return [
        'backup', 'backups', 'bak', 'snapshot', 'snapshots', 'dump', 'dumps',
        'archive', 'archives', '.sql.gz', '.sql', '.tar.gz', '.tgz', '.tar',
        '.zip', '.dump', 'old', 'copy',
    ];
}

/** Whether a path looks like something somebody backed up by hand. */
function looksLikeBackup(string $path): ?string
{
    $lower = strtolower($path);

    foreach (probableBackupPatterns() as $pattern) {
        if (str_starts_with($pattern, '.')) {
            if (str_ends_with($lower, $pattern)) {
                return $pattern;
            }
            continue;
        }

        /*
         * Matched as a whole path component, not as a substring.
         *
         * "backup" inside "backupsomething" is coincidence, and more to the
         * point /home/rollback and /var/lib/bakery both contain "bak". A
         * monitor that flags those has taught the reader to skip the section.
         */
        if (preg_match('#(^|/)' . preg_quote($pattern, '#') . '($|/|[-_.])#', $lower)) {
            return $pattern;
        }
    }

    return null;
}

/* ------------------------------------------------------------------ *
 * JetBackup
 * ------------------------------------------------------------------ */

/**
 * The JetBackup CLI, if this machine has one.
 *
 * Three known paths, then whatever the capability sweep found. The known list
 * is not enough on its own: JetBackup's own installer has moved the binary
 * between versions, and inside CageFS the account cannot stat /usr/local/jetapps
 * at all - so a machine with JetBackup installed reported the CLI as missing,
 * which is the wrong answer to the wrong question. The sweep runs as root and
 * searches, so where it has an answer, that answer is better than this list.
 *
 * @param array<string, string> $found name => path, from the capability sweep
 */
function jetBackupCli(array $found = []): ?string
{
    foreach ([
        '/usr/local/jetapps/usr/jetbackup5/bin/jetbackup5api',
        '/usr/local/jetapps/usr/jetbackup/bin/jetbackupapi',
        '/usr/bin/jetbackup5api',
    ] as $candidate) {
        if (is_executable($candidate)) {
            return $candidate;
        }
    }

    foreach (['jetbackup5api', 'jetbackupapi'] as $name) {
        $path = trim((string)($found[$name] ?? ''));
        /*
         * This path came from root's capability sweep. Re-checking it with
         * is_executable() as the web account turns an installed, root-only
         * JetBackup into "not found" inside CageFS. Returning it lets the
         * inventory state accurately say that metadata needs root instead.
         */
        if ($path !== '') {
            return $path;
        }
    }

    return null;
}

/**
 * Where the capability sweep found each backup CLI, as far as the host knows.
 *
 * Read from the host database rather than probed here, because probing is the
 * thing this account cannot do: the binary lives somewhere CageFS hides.
 *
 * @return array<string, string>
 */
function backupToolsFromHost(?PDO $host): array
{
    if ($host === null) {
        return [];
    }

    try {
        $rows = $host->query(
            "SELECT name, path, account_path FROM host_capabilities
              WHERE name IN ('jetbackup5api', 'jetbackupapi')"
        )->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $found = [];
    foreach ($rows as $row) {
        /*
         * The account's own path first where there is one.
         *
         * host_capabilities records both what root sees and what the account
         * sees. Running a binary root can see and the account cannot produces
         * "not found" from a shell, which is a confusing way to fail.
         */
        $path = trim((string)($row['account_path'] ?? '')) ?: trim((string)($row['path'] ?? ''));
        if ($path !== '') {
            $found[(string)$row['name']] = $path;
        }
    }

    return $found;
}

/**
 * Asks JetBackup a question and parses the answer.
 *
 * Written against its documented interface and deliberately defensive about
 * every part of the reply, because this has never been run against a live
 * JetBackup here. Anything unexpected produces null, which the caller treats as
 * "no metadata" and falls back to describing directories - a worse answer that
 * is still true, rather than a better-looking one that might not be.
 *
 * @return list<array>|null
 */
function jetBackupQuery(string $cli, string $function, array $arguments = []): ?array
{
    if (!preg_match('/^[A-Za-z]{1,32}$/', $function)) {
        return null;
    }

    $command = escapeshellarg($cli) . ' -F ' . escapeshellarg($function) . ' -O json';
    foreach ($arguments as $key => $value) {
        if (!preg_match('/^[A-Za-z_]{1,32}$/', (string)$key)) {
            continue;
        }
        $command .= ' -D ' . escapeshellarg($key . '=' . $value);
    }

    $raw = shellRead($command . ' 2>/dev/null');
    if ($raw === null) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return null;
    }

    /*
     * The payload has moved between versions, so several shapes are accepted
     * rather than one guessed at. If none of them fit, this returns null and
     * the caller says the metadata could not be read.
     */
    foreach (['data', 'result', 'items', 'backups'] as $key) {
        if (isset($decoded[$key]) && is_array($decoded[$key])) {
            $inner = $decoded[$key];
            foreach (['backups', 'items', 'data'] as $nested) {
                if (isset($inner[$nested]) && is_array($inner[$nested])) {
                    return array_values(array_filter($inner[$nested], 'is_array'));
                }
            }
            return array_values(array_filter($inner, 'is_array'));
        }
    }

    return array_values(array_filter($decoded, 'is_array'));
}

/**
 * JetBackup's own account of what it is storing.
 *
 * @return array{available: bool, reason: ?string, backups: list<array>, destinations: list<array>}
 */
function jetBackupInventory(?PDO $host = null): array
{
    $found = backupToolsFromHost($host);
    $cli = jetBackupCli($found);

    if ($cli === null) {
        return [
            'available' => false,
            'reason' => 'JetBackup\'s command line tool was not found, so the directories '
                . 'under the backup root can be measured but not explained - a name like '
                . 'files0000000005 says nothing about which account or date it holds.'
                . ($host === null
                    ? ' The host collector\'s capability sweep has not been read, so this '
                      . 'is only what THIS account can see - and inside CageFS it cannot '
                      . 'see /usr/local/jetapps at all.'
                    : ' The host\'s own capability sweep did not find it either, so it is '
                      . 'not installed rather than merely out of reach.'),
            'backups' => [],
            'destinations' => [],
        ];
    }

    $backups = jetBackupQuery($cli, 'listBackups');
    $destinations = jetBackupQuery($cli, 'listDestinations');

    if ($backups === null) {
        return [
            'available' => false,
            'reason' => 'JetBackup is installed at ' . $cli . ' but did not return readable '
                . 'metadata. This usually means it needs running as root.',
            'backups' => [],
            'destinations' => $destinations ?? [],
        ];
    }

    $rows = [];
    foreach ($backups as $entry) {
        // Field names differ between versions; each is looked for under the
        // names it has had, and left null rather than guessed.
        $rows[] = [
            'account' => firstOf($entry, ['account', 'owner', 'username', '_account']),
            'created_at' => jetBackupTimestamp(firstOf($entry, ['created', 'created_on', 'time', 'timestamp', 'date'])),
            'type' => firstOf($entry, ['type', 'backup_type', 'schedule_type']),
            'bytes' => jetBackupBytes(firstOf($entry, ['size', 'total_size', 'used', 'disk_usage'])),
            'destination' => firstOf($entry, ['destination', 'destination_name', 'dest']),
            'path' => firstOf($entry, ['path', 'location', 'directory']),
            'id' => firstOf($entry, ['_id', 'id', 'uniq_id']),
        ];
    }

    return [
        'available' => true,
        'reason' => null,
        'backups' => $rows,
        'destinations' => $destinations ?? [],
    ];
}

function jetBackupTimestamp(mixed $value): ?int
{
    if (is_numeric($value)) {
        $time = (int)$value;
        return $time > 20000000000 ? (int)floor($time / 1000) : $time;
    }
    $time = is_string($value) ? strtotime($value) : false;
    return $time === false ? null : $time;
}

function jetBackupBytes(mixed $value): ?int
{
    if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
        return max(0, (int)$value);
    }
    if (!is_string($value) || !preg_match('/^\s*([0-9]+(?:\.[0-9]+)?)\s*([kmgtpe]?i?b?)?\s*$/i', $value, $m)) {
        return null;
    }
    $unit = strtolower($m[2] ?: 'b');
    $power = array_search($unit, ['b', 'k', 'kb', 'kib', 'm', 'mb', 'mib', 'g', 'gb', 'gib', 't', 'tb', 'tib', 'p', 'pb', 'pib'], true);
    $steps = $power === false ? 0 : (int)floor(($power + 2) / 3);
    return (int)round((float)$m[1] * (1024 ** $steps));
}

/** The first key present, or null. Values are never invented. */
function firstOf(array $row, array $keys): mixed
{
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
    }

    return null;
}

/* ------------------------------------------------------------------ *
 * Putting it together
 * ------------------------------------------------------------------ */

/**
 * Everything the backup section shows, from the last disk walk.
 *
 * Built from host_dirs rather than by walking again: the walk is expensive and
 * has already been done, and this is a different reading of the same numbers.
 */
function analyseBackups(PDO $host, array $config): array
{
    $walk = $host->query('SELECT * FROM host_walks ORDER BY taken_at DESC LIMIT 1')
        ->fetch(PDO::FETCH_ASSOC);

    if (!$walk) {
        return [
            'available' => false,
            'reason' => 'No disk walk has been run yet, so there is nothing to analyse. '
                . 'As root: php host-cron.php --disk',
        ];
    }

    $at = (int)$walk['taken_at'];

    $dirs = $host->prepare(
        'SELECT path, bytes, files, COALESCE(measured, 1) AS measured
         FROM host_dirs WHERE taken_at = ?'
    );
    $dirs->execute([$at]);
    $all = $dirs->fetchAll(PDO::FETCH_ASSOC);

    $byPath = [];
    foreach ($all as $row) {
        $byPath[(string)$row['path']] = $row;
    }

    // What the filesystem itself says, which is the honest denominator.
    $mount = $host->prepare(
        'SELECT * FROM host_mounts WHERE taken_at <= ? ORDER BY taken_at DESC, used_bytes DESC LIMIT 1'
    );
    $mount->execute([$at]);
    $filesystem = $mount->fetch(PDO::FETCH_ASSOC) ?: null;

    $roots = [];
    $total = 0;
    foreach (backupRoots($config) as $root) {
        if (!isset($byPath[$root])) {
            continue;
        }
        $bytes = (int)$byPath[$root]['bytes'];
        $roots[] = [
            'path' => $root,
            'bytes' => $bytes,
            'files' => (int)$byPath[$root]['files'],
            'children' => backupChildren($byPath, $root),
        ];
        $total += $bytes;
    }

    // The host database is right here, and it is where the capability sweep
    // recorded the path this account cannot find for itself.
    $jet = jetBackupInventory($host);
    $accounts = backupsByAccount($byPath, $jet, $roots, $total);
    $other = otherProbableBackups($byPath, backupRoots($config));

    /*
     * The share of the disk, taken from df rather than from the walk.
     *
     * Summing the walk's own children would compare a number against itself,
     * and the walk is a lower bound whenever it ran out of time. df knows what
     * is used; that is the figure a percentage should be a percentage of.
     */
    $used = $filesystem ? (int)$filesystem['used_bytes'] : 0;
    $share = $used > 0 ? round($total / $used * 100, 1) : null;

    /*
     * How much of the backup area was actually measured.
     *
     * Without this the total is quietly a lower bound, and a lower bound
     * presented as a total is the whole reason this section exists.
     */
    $unmeasured = 0;
    foreach ($roots as $root) {
        foreach ($root['children'] as $child) {
            if (!$child['measured']) {
                $unmeasured++;
            }
        }
    }

    return [
        'available' => true,
        'reason' => null,
        'taken_at' => $at,
        'complete' => (bool)$walk['complete'],
        'total_bytes' => $total,
        'used_bytes' => $used,
        'share_percent' => $share,
        'mount' => $filesystem ? (string)$filesystem['mount'] : null,
        'roots' => $roots,
        'unmeasured' => $unmeasured,
        'accounts' => $accounts,
        'jetbackup' => $jet,
        'other' => $other,
        'growth' => backupGrowth($host, backupRoots($config)),
    ];
}

/** The immediate children of a backup root, biggest first. */
function backupChildren(array $byPath, string $root): array
{
    $prefix = rtrim($root, '/') . '/';
    $children = [];

    foreach ($byPath as $path => $row) {
        if (!str_starts_with($path, $prefix)) {
            continue;
        }
        if (str_contains(substr($path, strlen($prefix)), '/')) {
            continue;
        }
        $children[] = [
            'path' => $path,
            'name' => basename($path),
            'bytes' => (int)$row['bytes'],
            'files' => (int)$row['files'],
            'measured' => (bool)($row['measured'] ?? 1),
        ];
    }

    /*
     * Unmeasured children sort to the top, not the bottom.
     *
     * They report zero bytes, so sorting purely by size puts the thing nobody
     * looked at last - underneath twenty directories that were measured and
     * are genuinely small. The unknown is the more important row.
     */
    usort($children, function (array $a, array $b): int {
        if ($a['measured'] !== $b['measured']) {
            return $a['measured'] <=> $b['measured'];
        }
        return $b['bytes'] <=> $a['bytes'];
    });

    return array_slice($children, 0, 20);
}

/**
 * Backups per account, against what that account actually holds.
 *
 * The ratio is the point. Sixteen times the live data is not proof of anything
 * - retention of thirty daily incrementals will do that legitimately - but it
 * is the number that decides whether the retention policy is worth reading.
 */
function backupsByAccount(array $byPath, array $jet, array $roots, int $totalBytes = 0): array
{
    $live = [];
    foreach ($byPath as $path => $row) {
        if (preg_match('#^/home/([^/]+)$#', (string)$path, $m)) {
            $live[$m[1]] = (int)$row['bytes'];
        }
    }

    $backedUp = [];

    // JetBackup's own figures where it will say.
    foreach ($jet['backups'] as $backup) {
        $account = $backup['account'];
        if (!is_string($account) || $account === '') {
            continue;
        }
        $backedUp[$account] ??= ['bytes' => 0, 'count' => 0, 'oldest' => null, 'newest' => null];
        $backedUp[$account]['bytes'] += (int)($backup['size'] ?? 0);
        $backedUp[$account]['count']++;

        $created = is_numeric($backup['created'] ?? null) ? (int)$backup['created'] : null;
        if ($created !== null) {
            $backedUp[$account]['oldest'] = $backedUp[$account]['oldest'] === null
                ? $created : min($backedUp[$account]['oldest'], $created);
            $backedUp[$account]['newest'] = $backedUp[$account]['newest'] === null
                ? $created : max($backedUp[$account]['newest'], $created);
        }
    }

    /*
     * Without JetBackup metadata, an account can still be recognised where its
     * name appears as a directory under a backup root - which is how most
     * layouts are arranged. Marked as inferred, because a directory named after
     * an account is evidence and not proof.
     */
    $inferred = false;
    if ($backedUp === [] && $live !== []) {
        /*
         * Look for an account name anywhere under a backup root.
         *
         * Not just as an immediate child: JetBackup's layout is
         * /backup/files0000000005/<account>/... , so a rule that only checked
         * one level down found nothing on the machine this was written for and
         * silently reported no accounts at all.
         *
         * The deepest match wins, because the size recorded against
         * /backup/x/account is that account's and the size against /backup/x
         * is everybody's.
         */
        foreach ($byPath as $path => $row) {
            $path = (string)$path;

            $underRoot = false;
            foreach ($roots as $root) {
                if (str_starts_with($path, rtrim($root['path'], '/') . '/')) {
                    $underRoot = true;
                    break;
                }
            }
            if (!$underRoot) {
                continue;
            }

            $parts = explode('/', trim($path, '/'));
            $account = null;
            foreach ($parts as $part) {
                if (isset($live[$part])) {
                    $account = $part;
                }
            }
            if ($account === null) {
                continue;
            }

            /*
             * Only the shallowest path per account, or a tree of ten nested
             * directories under one account adds that account's data ten times.
             */
            $inferred = true;
            $backedUp[$account] ??= ['bytes' => 0, 'count' => 0, 'oldest' => null,
                                     'newest' => null, 'paths' => []];
            $backedUp[$account]['paths'][$path] = (int)$row['bytes'];
        }

        foreach ($backedUp as $account => $row) {
            $paths = $row['paths'] ?? [];
            $keep = [];
            foreach ($paths as $path => $bytes) {
                $nested = false;
                foreach ($paths as $other => $_) {
                    if ($other !== $path && str_starts_with($path . '/', $other . '/')) {
                        $nested = true;
                        break;
                    }
                }
                if (!$nested) {
                    $keep[$path] = $bytes;
                }
            }
            $backedUp[$account]['bytes'] = array_sum($keep);
            $backedUp[$account]['copies_seen'] = count($keep);
            unset($backedUp[$account]['paths']);
        }
    }

    $out = [];
    foreach ($backedUp as $account => $row) {
        $liveBytes = $live[$account] ?? null;
        $out[] = [
            'account' => $account,
            'backup_bytes' => $row['bytes'],
            'live_bytes' => $liveBytes,
            'ratio' => ($liveBytes !== null && $liveBytes > 0)
                ? round($row['bytes'] / $liveBytes, 1) : null,
            'copies' => $row['count'] ?: null,
            'oldest' => $row['oldest'],
            'newest' => $row['newest'],
            'inferred' => $inferred,
            'unattributed' => false,
        ];
    }

    usort($out, fn(array $a, array $b): int => $b['backup_bytes'] <=> $a['backup_bytes']);

    /*
     * What is left over after everything that could be attributed.
     *
     * On a machine with one account it is tempting to call the whole backup
     * root theirs. That is a guess, and this file exists partly because
     * guessing about backups is how people delete the wrong thing - so the
     * remainder is reported as unattributed and the reader is told what would
     * answer it.
     */
    $attributed = array_sum(array_column($out, 'backup_bytes'));
    $unattributed = max(0, $totalBytes - $attributed);

    if ($unattributed > 0 && ($totalBytes === 0 || $unattributed / max(1, $totalBytes) > 0.02)) {
        $out[] = [
            'account' => null,
            'backup_bytes' => $unattributed,
            'live_bytes' => null,
            'ratio' => null,
            'copies' => null,
            'oldest' => null,
            'newest' => null,
            'inferred' => false,
            'unattributed' => true,
        ];
    }

    return $out;
}

/**
 * Things that look like backups and are not in the backup system.
 *
 * The ones that eventually fill a disk: a copy somebody made before an upgrade
 * two years ago, in a directory nothing manages and no retention policy will
 * ever touch.
 */
function otherProbableBackups(array $byPath, array $roots): array
{
    $out = [];

    foreach ($byPath as $path => $row) {
        $path = (string)$path;

        foreach ($roots as $root) {
            if ($path === $root || str_starts_with($path, rtrim($root, '/') . '/')) {
                continue 2;
            }
        }

        $why = looksLikeBackup($path);
        if ($why === null) {
            continue;
        }

        $out[] = [
            'path' => $path,
            'bytes' => (int)$row['bytes'],
            'files' => (int)$row['files'],
            'matched' => $why,
        ];
    }

    /*
     * The outermost match wins, and its contents are dropped.
     *
     * This had it the wrong way round and kept the deepest, so one manual
     * backup was reported as four rows of its own internals -
     * .../indexers/repos.bleve, .../log, .../data - which is both noise and a
     * total that adds up to more than the thing it describes.
     *
     * The outermost directory is also the unit somebody would act on: nobody
     * moves the log subdirectory of an old backup, they move the backup.
     */
    $out = array_values(array_filter($out, static function (array $row) use ($out): bool {
        foreach ($out as $other) {
            if ($other['path'] !== $row['path']
                && str_starts_with($row['path'] . '/', $other['path'] . '/')) {
                return false;
            }
        }
        return true;
    }));

    usort($out, fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);
    return array_slice($out, 0, 15);
}

/**
 * How backup storage has moved between walks.
 *
 * Reported with the real span between walks rather than as "per day", because
 * the walk is a manual or weekly job and pretending otherwise would turn one
 * measurement into a rate nobody measured.
 */
function backupGrowth(PDO $host, array $roots): array
{
    if ($roots === []) {
        return ['points' => [], 'reason' => 'No backup roots configured.'];
    }

    $placeholders = implode(',', array_fill(0, count($roots), '?'));
    $stmt = $host->prepare(
        "SELECT taken_at, SUM(bytes) AS bytes
         FROM host_dirs WHERE path IN ($placeholders)
         GROUP BY taken_at ORDER BY taken_at DESC LIMIT 60"
    );
    $stmt->execute($roots);
    $points = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

    if (count($points) < 2) {
        return [
            'points' => $points,
            'reason' => 'Only one disk walk has been recorded, so there is nothing to compare '
                . 'against yet. Growth appears once a second walk has run.',
        ];
    }

    $latest = end($points);
    $now = (int)$latest['bytes'];
    $at = (int)$latest['taken_at'];

    $since = static function (int $seconds) use ($points, $at, $now): ?array {
        $target = $at - $seconds;
        $best = null;
        foreach ($points as $point) {
            if ((int)$point['taken_at'] <= $target) {
                $best = $point;
            }
        }
        if ($best === null) {
            return null;
        }

        return [
            'bytes' => (int)$best['bytes'],
            'at' => (int)$best['taken_at'],
            'grew' => $now - (int)$best['bytes'],
            'span' => $at - (int)$best['taken_at'],
        ];
    };

    return [
        'points' => $points,
        'now' => $now,
        'day' => $since(86400),
        'week' => $since(604800),
        'reason' => null,
    ];
}

/* ------------------------------------------------------------------ *
 * What can safely be done
 * ------------------------------------------------------------------ */

/**
 * The actions worth offering, each with a safety level and a reach.
 *
 * Two dimensions, and conflating them is how monitors get people into trouble.
 *
 * Safety is about the data. `safe` returns space the backup system itself
 * already considers disposable. `review` changes policy or removes a specific
 * restore point - defensible, and a decision rather than a chore. `dangerous`
 * is anything that reaches around the tool that owns the data, and it is listed
 * only so that it can be named as dangerous. Nothing here ever suggests
 * deleting files under a managed backup root: if that suggestion is wrong, the
 * thing that would have fixed the mistake is what was deleted.
 *
 * Reach is about who can do it. `here` the dashboard could do; `root` needs a
 * shell this account does not have; `provider` needs somebody else entirely,
 * which on managed hosting is most of this list. Saying "run JetBackup cleanup"
 * to somebody with no root is not advice, it is a dead end - so that case gets
 * a message they can send instead.
 */
function backupActions(array $analysis): array
{
    $total = humanBytes((int)($analysis['total_bytes'] ?? 0));

    $account = null;
    foreach ($analysis['accounts'] ?? [] as $row) {
        if (($row['unattributed'] ?? false) === false && is_string($row['account'] ?? null)) {
            $account = $row['account'];
            break;
        }
    }

    $actions = [
        [
            'level' => 'safe',
            'reach' => 'root',
            'title' => 'Run JetBackup\'s own cleanup',
            'detail' => 'Removes snapshots that have already passed their retention policy. '
                . 'This is the tool deleting what it has itself marked as expired, which is '
                . 'the only kind of backup deletion that needs no judgement.',
            'command' => 'jetbackup5api -F cleanup',
        ],
        [
            'level' => 'safe',
            'reach' => 'root',
            'title' => 'Clear finished download archives',
            'detail' => 'JetBackup builds a temporary archive when somebody downloads a '
                . 'restore and does not always remove it. These are copies of copies and '
                . 'nothing restores from them.',
            'command' => null,
        ],
        [
            'level' => 'safe',
            'reach' => 'here',
            'title' => 'Review backups outside the backup system',
            'detail' => 'The "probable backups" list below is manual copies that no retention '
                . 'policy will ever touch. Nothing removes these but a person, and they are '
                . 'usually the oldest thing on the disk.',
            'command' => null,
        ],
        [
            'level' => 'review',
            'reach' => 'root',
            'title' => 'Reduce how many copies are retained',
            'detail' => 'The largest single lever, and a real decision: fewer copies means a '
                . 'shorter window to restore from. Worth doing deliberately rather than '
                . 'because a disk filled up.',
            'command' => null,
        ],
        [
            'level' => 'review',
            'reach' => 'root',
            'title' => 'Remove one specific snapshot',
            'detail' => 'Through JetBackup, so its index stays consistent with what is on '
                . 'disk. Reasonable for a single oversized snapshot; it is still a restore '
                . 'point that will not exist afterwards.',
            'command' => null,
        ],
        [
            'level' => 'review',
            'reach' => 'provider',
            'title' => 'Move the backup destination off the system disk',
            'detail' => 'The structural fix. Backups on the same disk as the data they '
                . 'protect are also the reason the disk fills, and they do not survive the '
                . 'failure they exist for.',
            'command' => null,
        ],
        [
            'level' => 'dangerous',
            'reach' => 'root',
            'title' => 'Deleting files directly under the backup root',
            'detail' => 'Listed so it can be named, not recommended. JetBackup keeps an index '
                . 'of what it believes it holds; removing files behind its back leaves '
                . 'restores that fail at the moment somebody needs them, which is the worst '
                . 'possible time to discover it.',
            'command' => null,
        ],
        [
            'level' => 'dangerous',
            'reach' => 'root',
            'title' => 'Removing JetBackup metadata or a whole destination',
            'detail' => 'This does free the space, and it destroys every restore point on the '
                . 'machine. There is no version of this that a monitoring dashboard should '
                . 'suggest doing quickly.',
            'command' => null,
        ],
    ];

    return [
        'actions' => $actions,
        'message' => providerMessage($analysis, $total, $account),
    ];
}

/**
 * Something to send to whoever does have root.
 *
 * Written out in full because the alternative - "ask your host to look at
 * backups" - puts the burden of describing the problem on the person who
 * cannot see it. This says what was measured, what it means, and what to do,
 * and asks for nothing destructive.
 */
function providerMessage(array $analysis, string $total, ?string $account): string
{
    $share = $analysis['share_percent'];
    $mount = $analysis['mount'] ?? '/';

    $lines = [];
    $lines[] = sprintf(
        'Local backup storage is using %s%s on %s.',
        $total,
        $share !== null ? sprintf(', about %.0f%% of the used space', $share) : '',
        $mount
    );

    foreach (array_slice($analysis['accounts'], 0, 3) as $row) {
        if (($row['unattributed'] ?? false) || $row['ratio'] === null || $row['account'] === null) {
            continue;
        }
        $lines[] = sprintf(
            'Account %s holds %s of live data and %s of backups - %.1f times its own size%s.',
            $row['account'],
            humanBytes((int)$row['live_bytes']),
            humanBytes((int)$row['backup_bytes']),
            $row['ratio'],
            $row['copies'] ? sprintf(' across %d copies', $row['copies']) : ''
        );
    }

    $growth = $analysis['growth'];
    if (($growth['week'] ?? null) !== null && $growth['week']['grew'] > 0) {
        $lines[] = sprintf(
            'It has grown by %s over the last %s.',
            humanBytes($growth['week']['grew']),
            humanDuration((float)$growth['week']['span'])
        );
    }

    $lines[] = '';
    /*
     * The unattributed row has no account name, so it must not become one.
     *
     * "review the retention policy for " with nothing after it is the sort of
     * sentence that gets a support ticket closed as unclear.
     */
    $lines[] = 'Could you please review the retention policy'
        . (is_string($account) && $account !== '' ? ' for ' . $account : '')
        . ', remove any expired snapshots through JetBackup, and run a destination cleanup '
        . 'and integrity check afterwards?';
    $lines[] = '';
    $lines[] = 'I would rather not have anything deleted directly from the backup directory - '
        . 'JetBackup keeps its own index and removing files behind it breaks restores.';

    return implode("\n", $lines);
}

/**
 * Whether the ratio is worth mentioning at all.
 *
 * A number without a threshold is a number somebody has to interpret, and the
 * honest threshold here is high: incremental schemes with a month of retention
 * legitimately reach ten times the live data. This flags the ones past the
 * point where a policy is worth reading, and says that is what it means.
 */
function backupRatioVerdict(?float $ratio, ?int $copies): ?string
{
    if ($ratio === null) {
        return null;
    }

    if ($ratio < 5) {
        return null;
    }

    if ($ratio < 12) {
        return sprintf(
            '%.1f times the live data%s. Normal for a month of daily incrementals; worth '
            . 'knowing rather than worth acting on.',
            $ratio,
            $copies ? sprintf(' across %d copies', $copies) : ''
        );
    }

    return sprintf(
        '%.1f times the live data%s. High enough that the retention policy is worth reading - '
        . 'it does not prove anything is wrong, but this is where the space went.',
        $ratio,
        $copies ? sprintf(' across %d copies', $copies) : ''
    );
}
