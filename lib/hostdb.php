<?php
declare(strict_types=1);

/*
 * The host's own database.
 *
 * A second SQLite file, deliberately. The account collector runs as the cPanel
 * user once a minute; this one runs as root on whatever schedule WHM allows.
 * Pointing both at one file would mean two writers of different privilege in
 * the same write-ahead log, and the first time root created a WAL the account
 * could not write to, collection would stop with a lock error nobody would
 * think to look for. Separate files means the only thing they share is a
 * directory.
 *
 * The file is created by root and then handed to the account that owns the
 * dashboard, so the web side can read it as an ordinary file it owns. Root
 * stays the only writer.
 */

// hostMigrate leans on columnExists from db.php. Required rather than assumed:
// this file is included by setup.php and api.php on different paths.
require_once __DIR__ . '/db.php';

/**
 * The command that collects a host reading, with real paths filled in.
 *
 * Written out rather than described, because the two things people get wrong
 * are the absolute path to the install and which of the eight PHP binaries on a
 * cPanel box to use - and both are known here. Shared between the setup page
 * and the dashboard so the two can never print different instructions.
 */
function hostCronCommand(?string $root = null): string
{
    $root ??= dirname(__DIR__);

    $php = PHP_BINDIR . '/php';
    foreach (['/usr/local/bin/php', '/opt/cpanel/ea-php85/root/usr/bin/php'] as $candidate) {
        if (is_executable($candidate)) {
            $php = $candidate;
            break;
        }
    }

    return sprintf('%s %s/host-cron.php --collect --prune', $php, $root);
}

/**
 * The same, as a crontab line for root.
 *
 * Every five minutes: a reading walks every process in /proc, which is not free
 * on a busy box, and nothing it watches moves faster than that.
 */
function hostCronLine(?string $root = null): string
{
    return '*/5 * * * * ' . hostCronCommand($root) . ' --quiet';
}

/** Where the host database lives when the config does not say. */
function hostDatabasePath(array $config): string
{
    $configured = (string)($config['host']['database'] ?? '');
    if ($configured !== '') {
        return $configured;
    }
    // Beside the account database, which is already denied over HTTP.
    return dirname((string)$config['database']) . '/host.db';
}

/**
 * Opens the host database, creating and migrating it if needed.
 *
 * $forWriting is the difference between the root collector and the dashboard.
 * The dashboard must never migrate or create: it runs as a user who may not be
 * able to write the file at all, and a half-created database owned by the wrong
 * user is a mess to unpick. It opens what is there and reads it.
 */
function hostDb(array $config, bool $forWriting): ?PDO
{
    static $handles = [];
    $key = $forWriting ? 'rw' : 'ro';
    if (isset($handles[$key])) {
        return $handles[$key];
    }

    $file = hostDatabasePath($config);

    if (!$forWriting) {
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        return $handles[$key] = $pdo;
    }

    $dir = dirname($file);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException("Unable to create $dir");
    }

    $fresh = !file_exists($file);
    $previousUmask = umask(0077);
    try {
    $pdo = new PDO('sqlite:' . $file, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 10000');

    hostMigrate($pdo, $fresh);
    hostHandOver($config, $file);

    return $handles[$key] = $pdo;
    } finally { umask($previousUmask); }
}

/**
 * Gives the database to whoever runs the dashboard.
 *
 * Root creates it, so by default root is the only one who can read it - and the
 * dashboard would show an empty panel with no clue why. The owner comes from
 * the config rather than being guessed, because guessing wrong here means
 * either an unreadable file or a world-readable one, and the second is worse:
 * this database lists every process on the machine with its command line.
 *
 * Group-readable at most. Never world.
 */
function hostHandOver(array $config, string $file): void
{
    @chmod($file, 0640);

    $owner = (string)($config['host']['owner'] ?? '');
    if ($owner === '' || !function_exists('chown')) {
        return;
    }

    // The WAL and shared-memory files are part of the database: an owner who
    // cannot read those cannot read a database in WAL mode at all.
    foreach ([$file, $file . '-wal', $file . '-shm'] as $part) {
        if (file_exists($part)) {
            @chown($part, $owner);
            @chmod($part, 0640);
        }
    }
}

function hostMigrate(PDO $pdo, bool $fresh = false): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_version (version INTEGER NOT NULL)');
    $at = (int)($pdo->query('SELECT COALESCE(MAX(version), 0) FROM schema_version')->fetchColumn());

    /*
     * By version number, not by the order they happen to be written in.
     *
     * The list below is kept newest-first so that the interesting one is at the
     * top, which is fine for reading and wrong for applying: foreach walks it in
     * insertion order, so a fresh database ran 21 before 19 and hit an ALTER on
     * a table that its own CREATE had not reached yet. Existing installs never
     * saw it, because every step was already recorded by the time the order
     * drifted - which is exactly why it sat there.
     */
    $steps = hostMigrations();
    ksort($steps);

    $pending = array_filter(array_keys($steps), fn(int $version): bool => $version > $at);
    if (!$fresh && $pending !== []) {
        migrationBackup($pdo, $at, min($pending));
    }

    foreach ($steps as $version => $statements) {
        if ($version <= $at) {
            continue;
        }
        $pdo->beginTransaction();
        try {
            foreach ($statements as $sql) {
                if (preg_match('/^ALTER TABLE (\w+) ADD COLUMN (\w+)/i', trim($sql), $m)
                    && columnExists($pdo, $m[1], $m[2])) {
                    continue;
                }
                $pdo->exec($sql);
            }
            $pdo->prepare('INSERT INTO schema_version (version) VALUES (?)')->execute([$version]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

function hostMigrations(): array
{
    return [
        /*
         * The monitor's footprint is a first-class measurement rather than an
         * adjustment made in the browser.  RSS is exact for the selected
         * process tree; PSS is collected separately by hostTakeReading because
         * the normal top-process PSS budget deliberately skips small programs.
         */
        25 => [
            'ALTER TABLE host_samples ADD COLUMN monitoring_processes INTEGER',
            'ALTER TABLE host_samples ADD COLUMN monitoring_runnable INTEGER',
            'ALTER TABLE host_samples ADD COLUMN monitoring_cpu_percent REAL',
            'ALTER TABLE host_samples ADD COLUMN monitoring_rss_bytes INTEGER',
            'ALTER TABLE host_samples ADD COLUMN monitoring_pss_bytes INTEGER',
        ],
        24 => [
            'CREATE TABLE host_update_runs (
                id INTEGER PRIMARY KEY,
                started_at INTEGER NOT NULL,
                stage TEXT NOT NULL,
                manager TEXT NOT NULL,
                updates INTEGER,
                seconds REAL NOT NULL,
                ok INTEGER NOT NULL,
                error TEXT,
                output TEXT
            )',
            'CREATE INDEX host_update_runs_started ON host_update_runs (started_at)',
        ],
        23 => [
            'CREATE TABLE host_collector_runs (
                id INTEGER PRIMARY KEY,
                started_at INTEGER NOT NULL,
                jobs TEXT NOT NULL,
                seconds REAL NOT NULL,
                ok INTEGER NOT NULL,
                error TEXT
            )',
            'CREATE INDEX host_collector_runs_started ON host_collector_runs (started_at)',
        ],
        1 => [
            /*
             * One row per collection: the whole memory picture at an instant.
             *
             * Wide and flat rather than normalised, because every column is
             * read together and none of them repeat - this is a reading, not a
             * catalogue. The derived columns (used, anon, unaccounted) are
             * stored rather than computed on read so that a change to how they
             * are worked out cannot silently rewrite history.
             */
            "CREATE TABLE host_samples (
                id              INTEGER PRIMARY KEY,
                taken_at        INTEGER NOT NULL,
                mem_total       INTEGER,
                mem_free        INTEGER,
                mem_available   INTEGER,
                buffers         INTEGER,
                cached          INTEGER,
                swap_cached     INTEGER,
                shmem           INTEGER,
                anon_pages      INTEGER,
                mapped          INTEGER,
                slab            INTEGER,
                sreclaimable    INTEGER,
                sunreclaim      INTEGER,
                page_tables     INTEGER,
                kernel_stack    INTEGER,
                dirty           INTEGER,
                swap_total      INTEGER,
                swap_free       INTEGER,
                used_bytes      INTEGER,
                cache_bytes     INTEGER,
                unaccounted     INTEGER,
                rss_sum_bytes   INTEGER,
                pss_sum_bytes   INTEGER,
                pss_procs       INTEGER,
                swap_sum_bytes  INTEGER,
                proc_count      INTEGER,
                thread_count    INTEGER,
                shm_segments    INTEGER,
                shm_bytes       INTEGER,
                devshm_total    INTEGER,
                devshm_used     INTEGER,
                load1           REAL,
                load5           REAL,
                load15          REAL,
                cpu_cores       INTEGER,
                source          TEXT NOT NULL DEFAULT 'cron'
            )",
            'CREATE INDEX host_samples_taken_at ON host_samples (taken_at)',

            /*
             * Memory per user, which is the column CageFS makes invisible.
             *
             * The account cannot see that `git` is holding two gigabytes,
             * because from inside the cage those processes do not exist. This
             * table is the whole reason the host collector is worth running.
             */
            "CREATE TABLE host_users (
                sample_id  INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                user       TEXT NOT NULL,
                procs      INTEGER NOT NULL,
                threads    INTEGER,
                rss_bytes  INTEGER,
                pss_bytes  INTEGER,
                swap_bytes INTEGER
            )",
            'CREATE INDEX host_users_sample ON host_users (sample_id)',

            "CREATE TABLE host_processes (
                sample_id       INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                pid             INTEGER NOT NULL,
                user            TEXT,
                name            TEXT,
                rss_bytes       INTEGER,
                pss_bytes       INTEGER,
                swap_bytes      INTEGER,
                vsz_bytes       INTEGER,
                threads         INTEGER,
                elapsed_seconds INTEGER,
                command         TEXT
            )",
            'CREATE INDEX host_processes_sample ON host_processes (sample_id)',

            /*
             * Kernel slab caches, which belong to nobody and are real memory.
             *
             * A quarter of a gigabyte of xfs_inode does not appear in any
             * process, in any account, or in `free`'s idea of used. It is the
             * first thing to check when the numbers do not add up.
             */
            "CREATE TABLE host_slab (
                sample_id    INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                name         TEXT NOT NULL,
                objs         INTEGER,
                obj_bytes    INTEGER,
                cache_bytes  INTEGER
            )",
            'CREATE INDEX host_slab_sample ON host_slab (sample_id)',

            /*
             * Files worth watching that the account cannot see at all.
             *
             * Apache writes a domain's logs under /etc/apache2, owned by root.
             * A 763 MB rotated log there costs the same disk as one in the home
             * directory and is invisible to every tool the account can run.
             */
            /*
             * Device, inode and link count, not just size.
             *
             * Apache publishes the same log twice - once flat and once under a
             * per-account directory - as two names for one inode. They are not
             * two files and they do not cost two allocations, and a collector
             * that reports the sum of their sizes invents disk that does not
             * exist. The identity of a file is (device, inode); the path is
             * only a name for it.
             */
            "CREATE TABLE host_files (
                sample_id INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                path      TEXT NOT NULL,
                bytes     INTEGER,
                owner     TEXT,
                mtime     INTEGER,
                device    INTEGER,
                inode     INTEGER,
                links     INTEGER
            )",
            'CREATE INDEX host_files_sample ON host_files (sample_id)',

            /*
             * CloudLinux limits, when the machine has them.
             *
             * A process killed for hitting a memory limit leaves nothing behind
             * except a fault counter, so a rising count here is often the only
             * evidence of what happened.
             */
            "CREATE TABLE host_lve (
                sample_id     INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                user          TEXT NOT NULL,
                cpu_percent   REAL,
                mem_bytes     INTEGER,
                procs         INTEGER,
                faults_cpu    INTEGER,
                faults_mem    INTEGER,
                faults_procs  INTEGER
            )",
            'CREATE INDEX host_lve_sample ON host_lve (sample_id)',
        ],

        /*
         * Columns added after the first release.
         *
         * These also appear in the CREATE TABLE above, so a fresh install gets
         * them there and skips this step - the ALTER guard in hostMigrate()
         * makes a column that already exists a no-op. They are repeated here
         * because a database created before they were added has already
         * recorded version 1 and will never run that statement again.
         *
         * That is the whole reason a shipped migration is never edited in
         * place: changing step 1 fixes new installs and silently leaves every
         * existing one behind, which surfaces later as "table host_files has no
         * column named device" on the one machine that matters.
         */
        2 => [
            'ALTER TABLE host_files ADD COLUMN device INTEGER',
            'ALTER TABLE host_files ADD COLUMN inode INTEGER',
            'ALTER TABLE host_files ADD COLUMN links INTEGER',
            // Belt and braces: added the same careless way, one release earlier.
            'ALTER TABLE host_samples ADD COLUMN pss_procs INTEGER',
        ],

        /*
         * The long series: one row per service per reading.
         *
         * Deliberately *not* hanging off host_samples with a cascade, unlike
         * every other detail table here. Those carry a process list and a slab
         * table and are pruned at a fortnight to keep the file small; this one
         * is a name and five integers, and its whole value is being older than
         * that. "Forgejo is using 1.8 GB" is a fact; "Forgejo used 700 MB last
         * month, 1.2 GB last week and 1.8 GB now" is a diagnosis, and it needs
         * history the detail tables cannot afford to keep.
         *
         * Keyed by service name rather than pid on purpose. A pid is gone the
         * moment the daemon restarts, and a series that resets to zero every
         * time something is upgraded cannot show a leak.
         */
        3 => [
            "CREATE TABLE host_services (
                taken_at    INTEGER NOT NULL,
                name        TEXT NOT NULL,
                procs       INTEGER NOT NULL,
                threads     INTEGER,
                rss_bytes   INTEGER,
                pss_bytes   INTEGER,
                swap_bytes  INTEGER,
                read_bytes  INTEGER,
                write_bytes INTEGER
            )",
            'CREATE INDEX host_services_at ON host_services (taken_at)',
            // The index that makes one service's history a range scan rather
            // than a walk of every reading ever taken.
            'CREATE INDEX host_services_name ON host_services (name, taken_at)',
        ],

        /*
         * Disk I/O: the devices, the pressure, and who caused it.
         *
         * The per-service columns are repeated from the CREATE TABLE above for
         * the reason written at the top of the README's schema section - a step
         * already recorded is never run again, so a column added only there
         * would reach new installs and silently skip every existing one.
         */
        4 => [
            'ALTER TABLE host_services ADD COLUMN read_bytes INTEGER',
            'ALTER TABLE host_services ADD COLUMN write_bytes INTEGER',

            // Pressure stall for I/O: the share of time tasks were blocked
            // waiting on the disk. `full` is the share when nothing at all
            // could run, which is what a machine grinding to a halt looks like.
            'ALTER TABLE host_samples ADD COLUMN io_pressure_some REAL',
            'ALTER TABLE host_samples ADD COLUMN io_pressure_full REAL',
            'ALTER TABLE host_samples ADD COLUMN io_read_bytes INTEGER',
            'ALTER TABLE host_samples ADD COLUMN io_write_bytes INTEGER',
            'ALTER TABLE host_samples ADD COLUMN io_ms INTEGER',

            /*
             * Per device, per reading.
             *
             * Counters since boot, not rates. A rate is a difference between
             * two readings and storing it alone would make a gap in collection
             * indistinguishable from a quiet hour; keeping the counters means
             * the difference can always be recovered, over whatever interval
             * actually elapsed.
             */
            "CREATE TABLE host_disks (
                sample_id   INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                name        TEXT NOT NULL,
                stacked     INTEGER NOT NULL DEFAULT 0,
                read_bytes  INTEGER,
                write_bytes INTEGER,
                read_ios    INTEGER,
                write_ios   INTEGER,
                io_ms       INTEGER,
                queue_ms    INTEGER,
                in_flight   INTEGER
            )",
            'CREATE INDEX host_disks_sample ON host_disks (sample_id)',
        ],

        /*
         * What the collector could see when it ran.
         *
         * Recorded because the dashboard runs as the account and the collector
         * runs as root, and the two see different machines. Without this the
         * panel can only report the account's view - which on CloudLinux is a
         * filtered /proc that says nothing about the host - and a reader asking
         * "does this machine have PSI?" gets an answer about the cage instead.
         */
        5 => [
            'ALTER TABLE host_samples ADD COLUMN kernel TEXT',
            'ALTER TABLE host_samples ADD COLUMN diskstats_available INTEGER',
            'ALTER TABLE host_samples ADD COLUMN psi_available INTEGER',
        ],

        /*
         * Why pressure stall info is missing, worked out where it can be.
         *
         * The dashboard runs as the account and cannot read /boot/config-*,
         * which is the file that says whether CONFIG_PSI was compiled in. The
         * collector runs as root and can, so the answer is recorded at
         * collection time and simply read back later.
         */
        6 => [
            'ALTER TABLE host_samples ADD COLUMN psi_reason TEXT',
        ],

        // The same boot column as the account side, for the same reason.
        7 => [
            'ALTER TABLE host_samples ADD COLUMN boot_at INTEGER',

            /*
             * Boot sessions and how the one before each ended.
             *
             * Keyed on the boot time, so re-reading `last` on every collection
             * refreshes what is known without duplicating anything - the
             * current session's row gains an end time once it has one.
             *
             * Kept apart from host_samples and not pruned with them: this is
             * the record of what happened to the machine, and it is smaller and
             * longer-lived than any reading.
             */
            "CREATE TABLE host_boots (
                booted_at     INTEGER PRIMARY KEY,
                kernel        TEXT,
                ended         TEXT,
                shutdown_at   INTEGER,
                still_running INTEGER NOT NULL DEFAULT 0,
                seen_at       INTEGER NOT NULL
            )",
        ],

        /*
         * Clear the boot history once.
         *
         * Boots collected before the timezone fix were stored at an epoch four
         * hours out, and the table is keyed on that epoch - so the corrected
         * reading inserts alongside the wrong one instead of replacing it, and
         * every restart appears twice, once in each timezone. Deleting rather
         * than trying to match them up is safe because this table is derived:
         * it is a cache of what `last` says, and the next collection rebuilds
         * it from wtmp in full.
         */
        8 => [
            'DELETE FROM host_boots',
        ],

        /*
         * How long each service's processes have been alive.
         *
         * The oldest and the newest of the group, because together they say
         * what neither says alone: a group whose oldest is hours old and whose
         * newest is seconds old is churning, not merely long-lived.
         *
         * Recorded for the anomaly that spots a batch job which never finished.
         * Eleven webalizer processes still sitting there after 78 minutes look
         * exactly like one running normally unless their age is written down
         * and compared against how long that job usually takes.
         */
        9 => [
            'ALTER TABLE host_services ADD COLUMN oldest_seconds INTEGER',
            'ALTER TABLE host_services ADD COLUMN newest_seconds INTEGER',
        ],

        /*
         * What each process has actually read from and written to a disk.
         *
         * Per service was already recorded and is the right unit for a chart -
         * "MariaDB is writing 40 MB/s" is the sentence somebody wants. It is
         * the wrong unit for finding the culprit: eleven webalizer processes
         * sum into one row, and the backup that runs as a shell script shows up
         * under whatever it last exec'd.
         *
         * Nullable, deliberately. /proc/<pid>/io is readable by root and by the
         * process's owner and by nobody else, so a collector without root gets
         * nothing for other people's processes - and NULL says that, where 0
         * would claim the process sat idle.
         */
        10 => [
            'ALTER TABLE host_processes ADD COLUMN read_bytes INTEGER',
            'ALTER TABLE host_processes ADD COLUMN write_bytes INTEGER',
        ],

        /*
         * Whether each watched daemon was alive at each reading.
         *
         * Its own timestamp rather than a sample_id, like host_services and for
         * the same reason: this is the table somebody reads in three months to
         * ask when MariaDB last fell over, and it must outlive the per-reading
         * detail that gets pruned at a fortnight. A row is a name and a handful
         * of integers, so a year of it costs less than a day of process rows.
         *
         * state is running / stopped / absent / unknown, and the last two are
         * not the same as the second. See lib/daemons.php - a service that is
         * not installed here must never be shown as down, or the panel has a
         * permanent red row and stops being read.
         */
        11 => [
            'CREATE TABLE host_daemons (
                taken_at INTEGER NOT NULL,
                name TEXT NOT NULL,
                label TEXT NOT NULL,
                state TEXT NOT NULL,
                unit TEXT,
                units_total INTEGER NOT NULL DEFAULT 0,
                units_active INTEGER NOT NULL DEFAULT 0,
                active_state TEXT,
                sub_state TEXT,
                restarts INTEGER,
                started_at INTEGER,
                procs INTEGER NOT NULL DEFAULT 0,
                pid INTEGER,
                source TEXT NOT NULL
            )',
            'CREATE INDEX host_daemons_at ON host_daemons (taken_at)',
            'CREATE INDEX host_daemons_name ON host_daemons (name, taken_at)',
        ],

        /*
         * Where the disk actually went, from root's point of view.
         *
         * Three tables because there are three different answers to "72 GB is
         * used and I can only find 4", and they are not variations of each
         * other - see lib/hostdisk.php.
         *
         * All three keep their own timestamp rather than a sample_id: a walk of
         * the disk is expensive and is not run on every collection, so these
         * rows do not line up with the per-minute samples and must not be
         * pruned with them.
         */
        12 => [
            'CREATE TABLE host_mounts (
                taken_at INTEGER NOT NULL,
                device TEXT NOT NULL,
                mount TEXT NOT NULL,
                type TEXT,
                total_bytes INTEGER NOT NULL,
                used_bytes INTEGER NOT NULL,
                free_bytes INTEGER NOT NULL,
                used_percent REAL,
                also_at TEXT
            )',
            'CREATE INDEX host_mounts_at ON host_mounts (taken_at)',

            'CREATE TABLE host_dirs (
                taken_at INTEGER NOT NULL,
                path TEXT NOT NULL,
                depth INTEGER NOT NULL,
                bytes INTEGER NOT NULL,
                files INTEGER NOT NULL
            )',
            'CREATE INDEX host_dirs_at ON host_dirs (taken_at, bytes)',
            'CREATE INDEX host_dirs_path ON host_dirs (path)',

            'CREATE TABLE host_deleted (
                taken_at INTEGER NOT NULL,
                pid INTEGER,
                process TEXT,
                path TEXT NOT NULL,
                bytes INTEGER NOT NULL,
                holders INTEGER NOT NULL DEFAULT 1
            )',
            'CREATE INDEX host_deleted_at ON host_deleted (taken_at)',

            'CREATE TABLE host_walks (
                taken_at INTEGER NOT NULL,
                root TEXT NOT NULL,
                complete INTEGER NOT NULL,
                seconds REAL,
                dirs_scanned INTEGER,
                deleted_bytes INTEGER,
                deleted_readable INTEGER,
                note TEXT
            )',
            'CREATE INDEX host_walks_at ON host_walks (taken_at)',
        ],

        /*
         * The same question asked from the host, plus the mount topology.
         *
         * Separate from the account's answer on purpose: the difference between
         * the two is the entire diagnosis. A program in host_capabilities and
         * not in capabilities is installed and not exposed, which is a CageFS
         * change; one in neither needs a package.
         */
        13 => [
            'CREATE TABLE host_capabilities (
                name TEXT PRIMARY KEY,
                group_name TEXT NOT NULL,
                checked_at INTEGER NOT NULL,
                path TEXT,
                executable INTEGER NOT NULL DEFAULT 0,
                version TEXT,
                source TEXT,
                account_path TEXT,
                account_seen INTEGER
            )',
            'CREATE TABLE host_capability_runs (
                ran_at INTEGER NOT NULL,
                seconds REAL,
                total INTEGER,
                on_host INTEGER,
                seen_by_account INTEGER,
                user TEXT,
                cagefs_installed INTEGER,
                cagefs_enabled INTEGER,
                cagefs_note TEXT
            )',

            'CREATE TABLE host_mountinfo (
                taken_at INTEGER NOT NULL,
                mount_id INTEGER NOT NULL,
                parent_id INTEGER NOT NULL,
                device TEXT NOT NULL,
                fs_root TEXT NOT NULL,
                mount_point TEXT NOT NULL,
                fs_type TEXT,
                source TEXT,
                options TEXT,
                propagation TEXT,
                read_only INTEGER NOT NULL DEFAULT 0,
                bind INTEGER NOT NULL DEFAULT 0
            )',
            'CREATE INDEX host_mountinfo_at ON host_mountinfo (taken_at)',
        ],

        // The same history from the host's side. Separate because the two
        // collectors write to different databases and neither may write to the
        // other's; the dashboard reads both and interleaves them.
        14 => [
            'CREATE TABLE host_capability_changes (
                at INTEGER NOT NULL,
                name TEXT NOT NULL,
                group_name TEXT NOT NULL,
                side TEXT NOT NULL,
                old_state TEXT,
                new_state TEXT,
                old_version TEXT,
                new_version TEXT
            )',
            'CREATE INDEX host_capability_changes_at ON host_capability_changes (at)',
        ],

        /*
         * What a process count cannot say on its own.
         *
         * "18 copies of git" is a number, and the alert built on it asserted a
         * cause it had no evidence for. These columns are the evidence: age
         * spread, scheduler state, how many distinct parents, and what the
         * command lines actually are. Eighteen sleeping processes with eighteen
         * parents and a one-minute median is a busy Git server; eighteen in
         * uninterruptible sleep under one parent, four hours old, is a fault.
         */
        15 => [
            'ALTER TABLE host_services ADD COLUMN median_seconds INTEGER',
            'ALTER TABLE host_services ADD COLUMN distinct_parents INTEGER',
            'ALTER TABLE host_services ADD COLUMN state_summary TEXT',
            'ALTER TABLE host_services ADD COLUMN command_summary TEXT',
            'ALTER TABLE host_processes ADD COLUMN state TEXT',
            'ALTER TABLE host_processes ADD COLUMN ppid INTEGER',
        ],

        /*
         * What JetBackup says it is holding.
         *
         * Recorded during the disk walk because that is when the sizes are
         * measured, and the two are only useful together: a directory called
         * files0000000005 is a number without this, and this is a claim
         * without the directory sizes.
         */
        16 => [
            'CREATE TABLE host_backups (
                taken_at INTEGER NOT NULL,
                account TEXT,
                created_at INTEGER,
                type TEXT,
                bytes INTEGER,
                destination TEXT,
                path TEXT,
                backup_id TEXT
            )',
            'CREATE INDEX host_backups_at ON host_backups (taken_at)',
        ],

        /*
         * Whether a directory's size was actually measured.
         *
         * Zero bytes previously meant two incompatible things: empty, or never
         * opened because the walk ran out of time first. That is how a second
         * JetBackup destination vanished from the panel entirely - it reported
         * nothing, sorted last, and fell off the size cut.
         */
        17 => [
            'ALTER TABLE host_dirs ADD COLUMN measured INTEGER NOT NULL DEFAULT 1',
        ],

        /*
         * The machine's own CPU, which the account cannot measure.
         *
         * Inside CageFS `/proc/stat` is virtualised to the account's CPU
         * allowance, so the percentage on the dashboard answers "how much of
         * what I am allowed am I using" and nothing else. Read by root, the
         * same file answers "how busy is the server" - a different question
         * with a different answer, and the gap between the two is the whole
         * point of showing them side by side.
         */
        18 => [
            'ALTER TABLE host_samples ADD COLUMN cpu_total_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_idle_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_percent REAL',
        ],

        /*
         * The network: what is open, who is connected, and what crossed the wire.
         *
         * Kept on three different clocks, which is why they are three tables.
         * Ports change when somebody installs something and are worth a row per
         * collection. Interfaces carry counters that only make sense as
         * differences, so they ride the sample. A packet capture happens only
         * when asked for and is the only one that costs anything to take.
         */
        /*
         * Captures get direction, bytes and TCP flags.
         *
         * The first version counted packets per address with both ends of every
         * packet counted, which put this machine at the top of its own report
         * with 99.7% and made the shares add past 100. Direction is what fixes
         * that, and bytes are what tell a large clone apart from a scanner
         * sending hundreds of tiny requests - the two look identical in a
         * packet count.
         */
        /*
         * Commands the account cannot run, answered by root's collector.
         *
         * whois and dig are frequently absent from a cage while being perfectly
         * present on the host. Rather than reporting "not installed here" and
         * stopping, the account leaves a request and the collector answers it -
         * the same protocol as the disk walk, for the same reason.
         *
         * The command is never a string from the request. Only a fixed set of
         * verbs exists, and the argument is validated as a domain name on both
         * sides, because this runs as root.
         */
        /*
         * What a single "CPU is at 90%" cannot say.
         *
         * The existing columns hold one busy percentage and three load figures,
         * which between them cannot tell userland work from kernel work from a
         * stalled disk from a hypervisor taking the core away - and those need
         * four different responses. Every column here is a cumulative counter
         * read straight out of /proc/stat, stored raw and differenced on read,
         * for the same reason the disk counters are: a rate computed at write
         * time is a rate against whatever the gap between readings happened to
         * be, and cron missing a minute would silently halve it forever.
         */
        22 => [
            'ALTER TABLE host_samples ADD COLUMN cpu_user_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_nice_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_system_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_iowait_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_irq_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_softirq_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_steal_ticks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN cpu_guest_ticks INTEGER',

            /*
             * The three that explain a machine which is busy while nothing on
             * it looks busy. Context switches and interrupts are counters;
             * running and blocked are instantaneous depths, which is why they
             * are noisy and why a chart of them wants a wide window.
             */
            'ALTER TABLE host_samples ADD COLUMN ctxt_switches INTEGER',
            'ALTER TABLE host_samples ADD COLUMN interrupts INTEGER',
            'ALTER TABLE host_samples ADD COLUMN forks INTEGER',
            'ALTER TABLE host_samples ADD COLUMN procs_running INTEGER',
            'ALTER TABLE host_samples ADD COLUMN procs_blocked INTEGER',

            // Pressure stall info for the processor: the share of time tasks
            // were runnable and waiting for a core. Absent on kernels built
            // without PSI, which psi_available and psi_reason already explain.
            'ALTER TABLE host_samples ADD COLUMN cpu_pressure_some REAL',

            /*
             * One row per core per reading.
             *
             * Eight cores at 12% and one core at 100% beside seven idle ones
             * are the same aggregate number and completely different problems -
             * the second is a single-threaded bottleneck that buying cores will
             * not fix. Nothing in host_samples can express that, because the
             * aggregate has already thrown the distribution away.
             *
             * busy_percent is stored beside the counters rather than only
             * derived, because the difference is against the *previous
             * reading's* row for the same core, and a query that has to join a
             * table to itself on (cpu, previous sample) to draw a bar chart is
             * a query nobody will write twice.
             */
            "CREATE TABLE host_cpus (
                sample_id    INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                cpu          INTEGER NOT NULL,
                total_ticks  INTEGER,
                idle_ticks   INTEGER,
                user_ticks   INTEGER,
                system_ticks INTEGER,
                iowait_ticks INTEGER,
                steal_ticks  INTEGER,
                busy_percent REAL
            )",
            'CREATE INDEX host_cpus_sample ON host_cpus (sample_id)',

            /*
             * What each kept process was doing with the processor.
             *
             * cpu_ticks is utime+stime since it started - a counter, like
             * everything else here. cpu_percent is that counter differenced
             * against the previous reading, which is the figure people mean by
             * "CPU%", and cpu_lifetime_percent is the same total divided by how
             * long the process has been alive.
             *
             * Both are kept because they answer different questions and are
             * routinely confused. A process at 3% recent and 90% lifetime spent
             * its first minutes pinning a core and has since settled; one at
             * 90% recent and 3% lifetime has just started doing something. `ps`
             * shows the lifetime figure and `top` shows the recent one, which
             * is why the two appear to disagree about the same process.
             */
            'ALTER TABLE host_processes ADD COLUMN cpu_ticks INTEGER',
            'ALTER TABLE host_processes ADD COLUMN cpu_percent REAL',
            'ALTER TABLE host_processes ADD COLUMN cpu_lifetime_percent REAL',

            /*
             * The previous reading's tick count for every process, not just the
             * kept ones.
             *
             * A recent-CPU figure is a difference between two readings, so a
             * process can only be ranked by it if last minute's count for it
             * was written down - and only the biggest few processes by memory
             * are kept in host_processes. Ranking by CPU out of that set would
             * find the busiest of the fattest, which is not the question.
             *
             * So this carries one row per live process and is replaced whole
             * every reading. It is a scratchpad rather than history: no
             * sample_id, no growth, nothing to prune. `started` is the process
             * start time from /proc, kept because pids get reused - without it
             * a recycled pid inherits the dead process's counter and reports a
             * wildly wrong delta.
             */
            "CREATE TABLE host_proc_ticks (
                pid     INTEGER PRIMARY KEY,
                started INTEGER,
                ticks   INTEGER NOT NULL,
                at      INTEGER NOT NULL
            )",
        ],

        21 => [
            "CREATE TABLE host_answers (
                id        INTEGER PRIMARY KEY,
                verb      TEXT NOT NULL,
                argument  TEXT NOT NULL,
                asked_at  INTEGER NOT NULL,
                answered_at INTEGER NOT NULL,
                ok        INTEGER NOT NULL DEFAULT 0,
                text      TEXT,
                error     TEXT
            )",
            'CREATE INDEX host_answers_lookup ON host_answers (verb, argument, answered_at)',
        ],

        20 => [
            'ALTER TABLE host_captures ADD COLUMN in_packets INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_captures ADD COLUMN out_packets INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_captures ADD COLUMN in_bytes INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_captures ADD COLUMN out_bytes INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_captures ADD COLUMN inferred_local TEXT',

            'ALTER TABLE host_capture_rows ADD COLUMN in_packets INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_capture_rows ADD COLUMN out_packets INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_capture_rows ADD COLUMN in_bytes INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_capture_rows ADD COLUMN out_bytes INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_capture_rows ADD COLUMN opened INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE host_capture_rows ADD COLUMN bytes INTEGER NOT NULL DEFAULT 0',
        ],

        19 => [
            "CREATE TABLE host_ports (
                sample_id INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                protocol  TEXT NOT NULL,
                port      INTEGER NOT NULL,
                addresses TEXT,
                families  TEXT,
                public    INTEGER NOT NULL DEFAULT 0,
                process   TEXT,
                pid       INTEGER,
                PRIMARY KEY (sample_id, protocol, port)
            )",

            "CREATE TABLE host_interfaces (
                sample_id  INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                name       TEXT NOT NULL,
                rx_bytes   INTEGER, tx_bytes   INTEGER,
                rx_packets INTEGER, tx_packets INTEGER,
                rx_errors  INTEGER, tx_errors  INTEGER,
                rx_dropped INTEGER, tx_dropped INTEGER,
                up         INTEGER,
                speed_mbit INTEGER,
                PRIMARY KEY (sample_id, name)
            )",

            /*
             * Connection counts per peer, which is the shape a flood makes.
             *
             * Only the busiest are kept. A machine under attack has tens of
             * thousands of peers and the answer is in the top few; storing the
             * tail every five minutes would cost more than the finding is worth.
             */
            "CREATE TABLE host_peers (
                sample_id   INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                address     TEXT NOT NULL,
                connections INTEGER NOT NULL,
                private     INTEGER NOT NULL DEFAULT 0,
                PRIMARY KEY (sample_id, address)
            )",

            /*
             * One row per capture, and its summary rows beside it.
             *
             * Counts and endpoints only - see captureTraffic(). Nothing from a
             * packet body is stored, ever.
             */
            "CREATE TABLE host_captures (
                id          INTEGER PRIMARY KEY,
                taken_at    INTEGER NOT NULL,
                interface   TEXT NOT NULL,
                filter      TEXT NOT NULL,
                seconds     REAL,
                packets     INTEGER NOT NULL DEFAULT 0,
                bytes       INTEGER NOT NULL DEFAULT 0,
                unparsed    INTEGER NOT NULL DEFAULT 0,
                distinct_peers INTEGER,
                distinct_names INTEGER,
                hit_limit   INTEGER NOT NULL DEFAULT 0,
                ok          INTEGER NOT NULL DEFAULT 0,
                reason      TEXT
            )",
            'CREATE INDEX host_captures_taken_at ON host_captures (taken_at)',

            "CREATE TABLE host_capture_rows (
                capture_id INTEGER NOT NULL REFERENCES host_captures(id) ON DELETE CASCADE,
                kind       TEXT NOT NULL,
                label      TEXT NOT NULL,
                packets    INTEGER NOT NULL,
                private    INTEGER NOT NULL DEFAULT 0,
                PRIMARY KEY (capture_id, kind, label)
            )",

            // Every service unit, not only the watched ones.
            "CREATE TABLE host_units (
                sample_id   INTEGER NOT NULL REFERENCES host_samples(id) ON DELETE CASCADE,
                name        TEXT NOT NULL,
                state       TEXT NOT NULL,
                active      TEXT,
                sub         TEXT,
                enabled     TEXT,
                description TEXT,
                PRIMARY KEY (sample_id, name)
            )",
        ],
    ];
}

/**
 * Drops old readings.
 *
 * The detail tables are much larger than the samples they hang off, so they go
 * first and by cascade - deleting a sample takes its processes, its slab rows
 * and its files with it.
 */
function hostPrune(PDO $pdo, int $keepDays): int
{
    $cutoff = time() - max(1, $keepDays) * 86400;
    $stmt = $pdo->prepare('DELETE FROM host_samples WHERE taken_at < ?');
    $stmt->execute([$cutoff]);
    return $stmt->rowCount();
}

/**
 * Drops old service readings, on their own much longer clock.
 *
 * Separate from the samples because the point of this table is to outlive them.
 * A service row is a name and five integers - at a reading every five minutes
 * and forty services, roughly a megabyte a month - so keeping a quarter of a
 * year costs almost nothing and is what makes "and what was it in June?"
 * answerable.
 */
function hostPruneServices(PDO $pdo, int $keepDays): int
{
    $cutoff = time() - max(1, $keepDays) * 86400;
    $stmt = $pdo->prepare('DELETE FROM host_services WHERE taken_at < ?');
    $stmt->execute([$cutoff]);
    $removed = $stmt->rowCount();

    // Daemon states keep the same long clock: both are the tables whose value
    // is being older than everything else.
    $stmt = $pdo->prepare('DELETE FROM host_daemons WHERE taken_at < ?');
    $stmt->execute([$cutoff]);

    return $removed + $stmt->rowCount();
}

/**
 * Operational ledgers are small but unbounded without a clock of their own.
 * Keep enough history to compare a seasonal change or a failed update months
 * later, without retaining every five-minute collector invocation forever.
 */
function hostPruneOperationalRuns(PDO $pdo): int
{
    $removed = 0;
    foreach ([['host_collector_runs', 90], ['host_update_runs', 365]] as [$table, $days]) {
        try {
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE started_at < ?");
            $stmt->execute([time() - $days * 86400]);
            $removed += $stmt->rowCount();
        } catch (Throwable) {
            // Older host databases do not have the ledger tables yet.
        }
    }
    return $removed;
}
