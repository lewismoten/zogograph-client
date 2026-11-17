<?php
declare(strict_types=1);

/*
 * Copy this to lib/config.php and edit it. config.php is not in git.
 *
 * Generate the password hash with:
 *   php -r 'echo password_hash("your password", PASSWORD_DEFAULT), "\n";'
 *
 * Generate the mask secret with:
 *   php -r 'echo bin2hex(random_bytes(32)), "\n";'
 */

$defaultScanRoot = static function (): string {
    $home = getenv('HOME');
    if (!is_string($home) || $home === '' || $home[0] !== '/') {
        $home = '';
        if (
            function_exists('posix_geteuid') &&
            function_exists('posix_getpwuid')
        ) {
            $account = @posix_getpwuid(posix_geteuid());
            $home = is_array($account) ? (string) ($account['dir'] ?? '') : '';
        }
    }
    if ($home !== '' && $home[0] === '/') {
        return rtrim($home, '/') ?: '/';
    }

    // A constrained PHP environment may disclose neither account home nor HOME.
    // Watching the installed application is safer than assuming another account.
    return realpath(__DIR__ . '/..') ?: dirname(__DIR__);
};
$dataDirectory = dirname(__DIR__) . '/data';

return [
    // Display name used by the dashboard and setup shell. Keep it short: it
    // appears in browser titles and navigation as well as in visible branding.
    'branding' => ['name' => 'Zogograph'],

    // What to walk. Everything the monitor reports lives under here, and every
    // destructive action is refused outside it.
    'scan_root' => $defaultScanRoot(),
    'timezone' => 'America/New_York',

    'database' => $dataDirectory . '/stats.db',

    /* Built-in feature packages. Disabled features remove their dashboard and
     * API surfaces while preserving their existing private settings for a
     * later re-enable. */
    'core_features' => [
        'overview' => true,
        'assistant' => true,
        'peers' => true,
        'marketplace' => true,
        'terminal' => true,
        'notes' => true,
        'problems' => true,
        'storage' => true,
        'audit' => true,
        'metadata' => true,
        'notifications' => true,
        'backups' => true,
        'encryption' => true,
        'alerts' => true,
        'uptime' => true,
        'packages' => true,
        'charts' => true,
        'health' => true,
        'network' => true,
        'host' => true,
    ],

    // The account crontab desired by Setup. The editor writes only Zogograph's
    // marked block, then compares this intent with what cron actually has.
    'schedule' => [
        'php' => '',
        'vitals' => [
            'profile' => 'every-5m',
            'custom' => [
                'minute' => '*',
                'hour' => '*',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
            ],
        ],
        'scan' => [
            'profile' => 'none',
            'custom' => [
                'minute' => '*',
                'hour' => '*',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
            ],
        ],
    ],

    /*
     * Optional encrypted copies. `none` is the default: normal SQLite files
     * remain unchanged until an administrator explicitly chooses a mode.
     *
     * `sealed_copies_local` makes verified encrypted SQLite snapshots when
     * `cron.php --seal` is scheduled. It does not encrypt the live WAL
     * database. The 256-bit key file must be outside the web root, a regular
     * non-symlink file, and mode 0600. `operator_managed_volume` records an
     * operator's choice to protect the selected live stores using their host,
     * VM, or managed-volume encryption; Zogograph cannot verify that claim.
     */
    'encryption' => [
        'mode' => 'none', // none, sealed_copies_local, or operator_managed_volume
        // local_private_file is the only built-in key custody method today.
        // Fleet, manual-unlock, and marketplace providers are future modes.
        'key_custody' => 'local_private_file',
        'local_key_file' => '',
        // Null stores artifacts in data/sealed/; set an absolute private path to move them.
        'artifact_directory' => null,
        // Start with the sensitive bootstrap/account stores; host and AI are opt-in.
        'stores' => ['config', 'setup', 'account'],
        // These make the policy visible to the operator; scheduling remains
        // their cron/host scheduler responsibility.
        'backup_schedule' => 'daily', // manual, daily, or weekly
        'retention_days' => 30, // 0 keeps every local sealed artifact
        // Re-run a non-destructive recovery drill at least this often; 0 disables reminders.
        'recovery_drill_max_age_days' => 90,
        // Optional long-term recovery copies: newest distinct UTC days/weeks/months to preserve.
        'retention_recovery_tiers' => [
            'daily' => 0,
            'weekly' => 0,
            'monthly' => 0,
        ],
        // Optional per-store overrides: config, setup, account, host, ai => 0..36500 days.
        'retention_days_by_store' => [],
    ],

    /* Local-only marketplace catalog. Packages are data manifests; copying a
     * package here never executes PHP, shell, or browser code. A signed
     * manifest is enabled only when its configured publisher key verifies. */
    'marketplace' => [
        'directory' => $dataDirectory . '/marketplace',
        // 'trusted_keys' => [
        //     'example-publisher' => ['publisher' => 'Example publisher',
        //         'public_key' => 'base64 Ed25519 public key'],
        // ],
    ],

    /* Executable packages are separate from the data-only marketplace. The
     * runtime verifies an Ed25519-signed file manifest, installs immutable
     * files here, and records explicit capability grants before a future
     * package entry point can run. Keep publisher keys in setup settings. */
    'package_runtime' => [
        'directory' => $dataDirectory . '/packages',
        'trusted_keys' => [], // key-id => ['publisher' => '', 'public_key' => 'Base64 Ed25519 key']
        // Optional 0600 key outside the web root for encrypted package secrets.
        // Blank reuses encryption.local_key_file when one is configured.
        'secret_key_file' => '',
        /* Optional no-secret probe launchers. This is deployment-owned host
         * policy, deliberately not a website setting: each command must be an
         * absolute argument vector for a reviewed container/service sandbox.
         * Pin it to the installed manifest hash after reviewing an update.
         * It permits only capability/sqlcipher_preflight evidence; it does not
         * enable packages or authorize a live encryption/provider operation.
         *
         * 'probe_invokers' => [
         *   'sqlcipher-encrypted-sqlite' => [
         *     'command' => ['/usr/bin/docker', 'compose', '-f', '/etc/zogograph/sqlcipher-probe.yml', 'run', '--rm', '-T', 'package-worker'],
         *     'isolation_profile' => 'container-no-network-readonly-v1',
         *     'package_manifest_sha256' => 'the installed manifest.json SHA-256',
         *   ],
         * ],
         */
        /* Credential checks are a separate, high-trust launcher policy. It
         * must be a one-shot sandbox that permits egress only to the declared
         * HTTPS origin; never reuse the no-network probe launcher. Core checks
         * that the origin matches saved custody settings and pins the package
         * manifest, but the container/OS profile must enforce the egress rule.
         * This performs only signed GET /v1/client/subscription and receives
         * the enrolled bearer/signing key once via standard input. It is off
         * by default and never enables package/database/key operations.
         *
         * 'custody_credential_check_invokers' => [
         *   'zogograph-key-custody' => [
         *     'command' => ['/usr/bin/docker', 'compose', '-f', '/etc/zogograph/custody-credential-check.yml', 'run', '--rm', '-T', 'package-worker'],
         *     'isolation_profile' => 'container-one-shot-egress-restricted-v1',
         *     'network_egress_origin' => 'https://custody.example',
         *     'package_manifest_sha256' => 'the installed manifest.json SHA-256',
         *   ],
         * ],
         */
        /* An envelope write is a separate authority, even though it uses the
         * same enrolled client and endpoint. Do not point this at the
         * credential-check launcher. Its sandbox must permit only the exact
         * custody origin and receive its input through standard input.
         *
         * 'custody_envelope_invokers' => [
         *   'zogograph-key-custody' => [
         *     'command' => ['/usr/bin/docker', 'compose', '-f', '/etc/zogograph/custody-envelope-store.yml', 'run', '--rm', '-T', 'package-worker'],
         *     'isolation_profile' => 'container-one-shot-egress-restricted-v1',
         *     'network_egress_origin' => 'https://custody.example',
         *     'package_manifest_sha256' => 'the exact installed manifest SHA-256',
         *   ],
         * ],
         */
        /* Recovery unwrap is a third, independent authority. It must use a
         * fresh one-shot sandbox with only the exact custody origin reachable.
         * The returned DEK may travel only over its private process pipe for an
         * immediate artifact-authentication drill; it must never be logged or
         * persisted by the launcher.
         *
         * 'custody_unwrap_invokers' => [
         *   'zogograph-key-custody' => [
         *     'command' => ['/usr/bin/docker', 'compose', '-f', '/etc/zogograph/custody-recovery-unwrap.yml', 'run', '--rm', '-T', 'package-worker'],
         *     'isolation_profile' => 'container-one-shot-egress-restricted-v1',
         *     'network_egress_origin' => 'https://custody.example',
         *     'package_manifest_sha256' => 'the exact installed manifest SHA-256',
         *   ],
         * ],
         */
    ],

    // Sign-in. Without this nobody can unmask paths or delete anything, which
    // is a safe default for a fresh install.
    'password_hash' => '',

    // Optional Base32 TOTP second factor for the legacy administrator. This is
    // checked only after the password and is never returned through the API.
    'auth' => [
        'totp_secret' => '',
        // Advances when an administrator ends every signed-in browser session.
        'session_epoch' => 0,
    ],

    /*
     * People and their permissions. The legacy password above remains an
     * Administrators login. Named users are optional and carry their own
     * password hash and group membership. A named user may also carry its own
     * private Base32 `totp_secret`. Public is always active; the other built-in
     * roles are Viewer, Operator, and Administrator. The older Staff and
     * Administrators aliases remain available for existing installations.
     */
    'access' => [
        'groups' => [],
        'users' => [],
    ],

    /*
     * Salts the pseudonyms shown to signed-out visitors.
     *
     * Without a secret the mask is just a hash of the path, and anybody who
     * suspects a path exists can confirm it by hashing their guess. Must be
     * set, and must not change, or every chart legend renames itself.
     */
    'mask_secret' => '',

    // How long a signed-in session lasts without activity, in seconds.
    'session_lifetime' => 43200,

    'alerts' => [
        'enabled' => false,
        // Optional Telegram delivery. Keep this token in config.php only.
        'telegram' => [
            'enabled' => false,
            'bot_token' => '',
            'chat_id' => '',
        ],
        /*
         * Future delayed-delivery policy. It is off by default: a missing or
         * incomplete setting must never hold an operational alert. When queue
         * delivery is enabled, Information and Warning notifications may wait
         * outside this local schedule; Error and Critical always bypass it.
         */
        'delivery_hours' => [
            'enabled' => false,
            'timezone' => 'America/New_York',
            'days' => [1, 2, 3, 4, 5], // ISO Monday through Friday
            'start' => '09:00',
            'end' => '17:00',
        ],
        /*
         * Where to send it. Several addresses, separated by semicolons.
         *
         * A semicolon because that is what mail clients use and therefore what
         * people type; commas are accepted too. Each address gets its own
         * message rather than one with a joined To - a single send fails as a
         * unit, so one bad mailbox would mean nobody hears, and a shared To
         * shows everybody the whole list.
         */
        'to' => '',
        'from' => '',

        /*
         * The address of this dashboard, for the links in the messages.
         *
         * The cron that sends them has no request to work it out from, so it
         * has to be written down - otherwise the messages say what is wrong and
         * leave the reader to find the page themselves at two in the morning.
         */
        'site_url' => '',

        /*
         * How loudly each rule speaks, and how long before it repeats.
         *
         * Both are per rule, and both are optional: anything absent uses the
         * recommendation in lib/notify.php, which the settings page shows
         * beside your choice so a decision is distinguishable from a default.
         *
         *   'rules' => [
         *       'disk_free_percent' => ['severity' => 'critical', 'cooldown' => 3600],
         *       'url_down'          => ['cooldown' => 0],   // every time
         *       'anomaly'           => ['severity' => 'info'],
         *   ]
         *
         * A cooldown of 0 means every time, which is a real answer for a site
         * that has stopped answering.
         */
        'rules' => [],
        // No more than one message per rule per this many seconds, however bad
        // things get. An alert that arrives every minute is a filter rule.
        /*
         * CPU held at the ceiling, not a moment of it.
         *
         * A single reading at 100% is a backup starting or a page rendering and
         * is over before anybody could read about it. What matters is the CPU
         * *staying* there, which is what a runaway process looks like.
         *
         * So all three of these define the rule together: at least `cpu_share`
         * of the readings across the last `cpu_minutes` must be at or above
         * `cpu_percent`. A spike cannot satisfy that however tall it is.
         *
         * Mind what the percentage means on CloudLinux: inside CageFS
         * /proc/stat is virtualised to the account's own CPU allowance, so 95
         * here is 95% of what this account may use - which is a real thing to
         * know about, and not the same as the server being at 95%.
         *
         * Set cpu_percent to 0 to turn the rule off.
         */
        'cpu_percent' => 95.0,
        'cpu_minutes' => 15,
        'cpu_share' => 0.9,

        // 21600 seconds is six hours.
        'cooldown_seconds' => 21600,

        /*
         * Anomalies: what is unusual, rather than what is large.
         *
         * The settings above are thresholds, and thresholds have two failure
         * modes. They fire when nothing is wrong - a directory that gains
         * 200 MB every night trips the category rule every night, and after a
         * week nobody reads the mail. And they stay silent when something is:
         * eleven webalizer processes sat on this server for 78 minutes without
         * crossing any of them, because each one was small and the disk was
         * fine. What gave it away was that the job normally takes two minutes,
         * and no threshold can encode "normally" - that number is different on
         * every server.
         *
         * These rules compare each thing against its own history instead. They
         * need no tuning to start with and are deliberately quiet: none of them
         * says anything until it has `min_readings` of history to say it from,
         * and every proportional test is paired with an absolute floor so that
         * 25% of something tiny stays out of your inbox.
         *
         * They are gated by 'enabled' above as well as this one - anomalies
         * ride the same email and the same cooldown as everything else.
         */
        'anomalies' => [
            'enabled' => true,

            // Nothing is judged against less history than this.
            'min_readings' => 8,

            /*
             * Two windows, because the two kinds of subject have different
             * rhythms. A daemon appears in every reading, so a day of history
             * is hundreds of samples. A job that runs four times a day appears
             * in four, and nothing can be concluded from four - the rules that
             * watch those look back a week to have anything to compare with.
             */
            'baseline_hours' => 24,
            'service_baseline_hours' => 168,

            // A service holding materially more memory than it usually does.
            // Both have to be exceeded, so a quarter of a small service is not
            // grounds for mail.
            'service_pss_percent' => 25.0,
            'service_pss_floor_bytes' => 67108864, // 64 MiB

            // A category that jumped, where jumping is not what it does. The
            // multiple is what keeps the nightly backup quiet while the same
            // growth somewhere flat still reports.
            'category_floor_bytes' => 104857600, // 100 MB
            'category_multiple' => 3.0,

            // A file growing far faster than that same file grows. Only the
            // biggest few movers in a scan are examined; the rest cost more to
            // check than they could ever be worth.
            'file_floor_bytes' => 52428800, // 50 MiB
            'file_multiple' => 4.0,
            'file_limit' => 5,

            // Load above one job per core, held across this many consecutive
            // readings. Load counts work waiting as well as work running.
            'load_readings' => 3,

            // Swap filling *while* available memory falls. Either alone is
            // meaningless; together they are the kernel running out of options.
            'swap_floor_bytes' => 33554432, // 32 MiB

            // Many more copies of something than there usually are.
            'process_multiple' => 3.0,
            'process_floor' => 4,

            /*
             * Something still running long after that thing normally finishes.
             *
             * This one needs the host collector, and it is the rule that would
             * have caught the webalizer jobs. It never judges daemons: a
             * process that has simply been up for nine days is not late for
             * anything. See tracksTheClock() in lib/anomalies.php for how the
             * two are told apart, which is the whole difficulty of the rule.
             */
            'age_multiple' => 3.0,
            'age_floor_seconds' => 900,
        ],

        // Fire when free space drops below this, in bytes, or below this
        // fraction of the filesystem.
        'disk_free_bytes' => 5 * 1024 * 1024 * 1024,
        'disk_free_percent' => 5.0,
        'inodes_free_percent' => 5.0,

        // Fire when a category or a single path grows by more than this in one
        // scan interval.
        'category_jump_bytes' => 1024 * 1024 * 1024,
        'path_jump_bytes' => 512 * 1024 * 1024,
        // Or when a directory gains this many files in one interval, which is
        // the shape a runaway log or a stuck job makes.
        'path_jump_files' => 5000,

        // Fire when free space is falling fast enough to run out within this
        // many hours, judged from the recent trend.
        'projected_full_hours' => 72.0,
    ],

    'scan' => [
        // How many offenders to keep per category, per scan.
        'top_count' => 25,
        // Directory names matched case-insensitively.
        'category_dirs' => [
            'trash' => ['.trash', 'trash'],
            'logs' => ['log', 'logs'],
            'temp' => ['tmp', 'temp', 'temporary'],
        ],
        /*
         * Whole filenames, matched case-insensitively, wherever they turn up.
         *
         * These win over both the directory and the extension rules, which is
         * the point of them: Apache and cPanel drop `error_log` all over an
         * account, one per directory that has ever thrown a warning, and any
         * one of them can quietly reach ten gigabytes. Counted as logs they
         * disappear into the log total; counted on their own you can see them.
         */
        'category_files' => [
            'errors' => ['error_log', 'php_errorlog', 'error.log'],
        ],

        // Standalone files outside matching directories.
        'category_extensions' => [
            'logs' => ['log'],
            'temp' => ['tmp', 'temp'],
        ],
        'exclude_paths' => [$dataDirectory],
    ],

    /*
     * What may be deleted, and from where.
     *
     * Deleting is the one thing here that cannot be undone, so it is not
     * governed by the same list that decides what gets *counted*. A path has to
     * be inside scan_root, inside one of these categories, not a symlink, and
     * requested by a signed-in session over POST with a matching token.
     */
    /*
     * How far the delete buttons reach.
     *
     *   'account'     anything inside scan_root. What an account owner usually
     *                 means by "delete this" when looking at their own disk.
     *   'categories'  only the categories listed below, which is the cautious
     *                 rule: a mis-click can only remove something already
     *                 declared disposable.
     *
     * Neither mode will touch the scan root itself, anything outside it, a
     * symlink, or this monitor's own database. Those are not policy - they are
     * the reasons this cannot be turned into a way to delete the machine.
     */
    'deletable_scope' => 'account',
    'deletable_categories' => ['trash', 'temp'],

    /*
     * Log files outside the account that may be read, if the host lets you.
     *
     * On cPanel the interesting ones belong to root and live outside the home
     * directory entirely, so nothing in the account-side rules can ever reach
     * them. Listing them here does not grant access: the file still has to be
     * readable by the user the dashboard runs as, and if it is not, that is
     * exactly what gets reported. On most shared hosts these will be refused,
     * and the refusal is the answer to "can I see the server's error log".
     *
     * Exact paths only, and no globs. A pattern here would be a way to read an
     * arbitrary file by naming one that happens to match.
     */
    /*
     * The network, which is the one resource nothing else here can see.
     *
     * capture_seconds bounds a requested packet sample. It is capped in code as
     * well - see CAPTURE_MAX_SECONDS - because this opens a live interface as
     * root and a config typo should not be able to leave tcpdump running.
     */
    /*
     * How politely to talk to other people's servers.
     *
     * whois registries, and DNS at volume, are somebody else's infrastructure.
     * Asking for twenty domains in one burst is how an install gets temporarily
     * blocked - and a blocked install reports every domain as unknown, which
     * looks exactly like twenty problems rather than one.
     *
     * So outbound lookups drain a queue rather than emptying it: a few per run,
     * oldest first. On a five-minute cron that is a hundred a day, which is far
     * more than any real watch list needs and slow enough that no registry
     * notices.
     */
    'external' => [
        // How many of each kind of outbound lookup per collector run.
        'per_run' => 1,

        // Overrides, for anything that deserves a different rate.
        'per_run_by_command' => [
            'whois' => 1,
            'dns' => 4,
        ],

        /*
         * Which package manager this host uses.
         *
         * Only used to print the right install line when something is missing.
         * `auto` picks from what is actually on the machine, which is right
         * nearly always; naming it explicitly is for the case where several are
         * present and only one is the real one.
         */
        'package_manager' => 'auto',
    ],

    /*
     * Root's host collector can check for, download, or install operating
     * system updates. Leave this empty until a server administrator has chosen
     * a policy. Stages may be combined: Download and Auto-Update imply a fresh
     * Detect first. Notify raises an ordinary dashboard alert when updates are
     * found or a stage fails.
     */
    'updates' => [
        'modes' => [], // detect, notify, download, auto-update
        'interval_seconds' => 86400,
    ],

    // Stable keys from “What is wrong” findings intentionally accepted here.
    // The dashboard writes this list when an operator presses Ignore.
    'diagnostic_ignores' => [],

    /* Named, explicitly trusted Zogograph installations. Signed remote vital
     * delivery has its own one-time credential and does not need OIDC. OIDC is
     * an optional interactive read connection and must be configured in full. */
    'peers' => [],

    /* Set these only on a remote Zogograph installation after its controller
     * shows the one-time pairing credential. The remote cron sends its newest
     * local vital reading over HTTPS; private controller names need an exact
     * allowlist entry to avoid accidental internal SSRF. */
    'remote_delivery' => [
        'endpoint' => '',
        'peer' => '',
        'credential' => '',
        'interval_seconds' => 60,
        'private_controller_hosts' => [],
    ],

    /* URL checks may monitor public HTTP(S) targets. To monitor an internal
     * service, list its exact hostname or address here; this prevents a saved
     * check or DNS change from becoming an accidental SSRF request. */
    'url_watch' => [
        'private_hosts' => [],
    ],

    /* The self-hosted Free edition includes this controller plus three remote
     * Zogograph installations. Future offline licenses or hosted plans use the
     * same single entitlement field; no user data is withheld when it expires. */
    'edition' => [
        'name' => 'Free',
        'remote_host_limit' => 3,
    ],

    /* OpenID Connect provider. Registered trusted clients use the
     * authorization-code flow with PKCE and receive a short-lived RS256 ID
     * token containing the local username and groups. */
    'openid' => [
        'enabled' => false,
        'issuer' => '',
        /* Paste a PEM RSA private key. Its public part is published as JWKS;
         * never reuse a TLS certificate key. Generate one with:
         * openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:3072 */
        'signing_private_key' => '',
        'clients' => [],
    ],

    'network' => [
        'capture_seconds' => 20,

        // Extra names to check on the DNS page, beyond this machine's own.
        'dns_names' => [],
    ],

    'host_log_paths' => [
        '/usr/local/apache/logs/error_log',
        '/usr/local/apache/logs/access_log',
        '/usr/local/apache/logs/suexec_log',
        '/var/log/httpd/error_log',
        '/var/log/httpd/access_log',
        '/var/log/nginx/error.log',
        '/var/log/nginx/access.log',
        '/var/log/messages',
        '/var/log/maillog',
    ],

    /*
     * What a signed-in session may read the contents of.
     *
     * Separate from deletable_categories, and deliberately a different set:
     * being allowed to look at a file and being allowed to remove it are
     * different permissions. Reading is for finding out what filled the disk,
     * which means logs; deleting is for trash and scratch space.
     */
    'viewable_categories' => ['logs', 'errors'],

    /*
     * Recording what was running, and when it is worth doing.
     *
     * Only while the machine is actually struggling. Every process every minute
     * would be a table growing faster than the disk it is watching; the three
     * minutes the CPU was pinned is small and is the only part anybody reads.
     *
     * On CloudLinux, mind what the percentage means. Inside CageFS /proc/stat is
     * virtualised to the account's own CPU allowance, so 70 here is 70% of what
     * this account may use - which a single busy PHP request can reach while the
     * server is otherwise idle. If the record fills with moments that were not
     * interesting, raise it; the host collector reports the machine's real load
     * separately.
     */
    'processes' => [
        'record_when_cpu_above' => 70.0,
        'record_when_memory_above' => 85.0,
        'keep' => 8,

        /*
         * Whether a signed-in session may stop processes.
         *
         * Set it to false and the buttons are not drawn and the endpoints
         * refuse - worth doing if the dashboard is reachable from anywhere you
         * would not want a stolen session to have a kill switch.
         */
        'may_stop' => true,

        /*
         * Names that are never signalled, however they are asked for.
         *
         * The account's own plumbing. Killing sshd from a web page locks you
         * out of the machine you are trying to fix; killing the shell you are
         * sitting in is a support ticket. Note that this is matched against the
         * bare executable name, so 'php' here would also protect 'php cron.php'.
         *
         * Omit the key entirely to use the built-in list in lib/signals.php,
         * which is this and a few more. Set it to an empty array to protect
         * nothing by name - pid 1, this dashboard's own ancestry and other
         * users' processes are still refused, because those are not opinions.
         */
        'protect' => [
            'init',
            'systemd',
            'launchd',
            'sshd',
            'bash',
            'sh',
            'zsh',
            'login',
            'tmux',
            'screen',
            'cron',
            'crond',
            'cpsrvd',
            'cpanel',
            'httpd',
            'nginx',
            'apache2',
            'php-fpm',
            'lsphp',
            'litespeed',
            'mysqld',
            'mariadbd',
            'exim',
            'dovecot',
            'named',
        ],

        /*
         * Most processes one stop may signal at once.
         *
         * A brake on the pattern matcher rather than on you. A regular
         * expression that is one character broader than intended is the classic
         * way to empty an account, and it always looks correct until it runs.
         */
        'max_group' => 40,
    ],

    /*
     * The host-side collector, run by root out of WHM's crontab.
     *
     * Only relevant if you have root on the machine. Inside CageFS a cPanel
     * account gets its own /proc holding only its own processes, so the account
     * collector can watch memory vanish and have nothing to blame - the process
     * holding two gigabytes belongs to another user and, from inside the cage,
     * does not exist. Neither do the kernel's slab caches, nor a 763 MB Apache
     * log under /etc that costs exactly as much disk as one in the home
     * directory.
     *
     * host-cron.php fills that gap. It writes to its own database so that two
     * writers of different privilege never share a write-ahead log.
     */
    /*
     * How long the monitor keeps its own history.
     *
     * A reading a minute is half a million rows a year, and a monitor that
     * fills the disk it is watching has failed in the most embarrassing way
     * available to it. So old readings are compacted: many rows become one
     * covering a longer span, and the further back you look the coarser it
     * gets.
     *
     * A compacted row holds the *worst* moment in its bucket, not the average
     * and not an arbitrary sample. That matters more than it sounds: a minute
     * at 100% CPU inside an otherwise idle hour averages to 1.7% and vanishes,
     * and a spike is most of the reason to keep a year of history at all. The
     * cost is that an old point reads as "load reached 8 during this hour"
     * rather than "load was 8" - which is the more useful of the two.
     *
     * Zero anywhere means for ever, never "delete everything".
     */
    /*
     * What the monitor knows about logs specifically.
     *
     * Logs caused the incident this tool was written after, and they have a
     * life cycle that general rules cannot see. A log rotates; a .bkup beside
     * one is meant to be temporary; two names for one file is one file. None of
     * that is visible to a rule that knows only how big something is.
     *
     * Set any threshold to 0 to turn that rule off.
     */
    'logs' => [
        // Any single log above this, whatever it is doing. A log has no
        // natural size the way a database or an archive does.
        'max_bytes' => 1073741824, // 1 GiB

        /*
         * Bytes an hour, against a ceiling rather than against the file's own
         * past. The baseline rule in lib/anomalies.php asks whether a rate is
         * unusual for that file and answers no for a log that has written a
         * gigabyte an hour all week - which is exactly when somebody wants to
         * be told.
         */
        'fast_bytes_per_hour' => 104857600, // 100 MiB/h

        /*
         * A job writes one of these beside the log it is processing and
         * removes it when it finishes. One still there a day later means the
         * job died halfway - the signature of the Webalizer incident, and
         * invisible to everything else because the file is small.
         */
        'backup_suffixes' => ['.bkup', '.bak', '.tmp', '.partial'],
        'backup_max_age_hours' => 24,

        /*
         * Rotation appears in the history as a fall, when the file is renamed
         * or truncated and starts again. A log this big with no fall at all
         * across this many days is one whose rotation is not running.
         */
        'rotate_after_days' => 8,
        'rotate_min_bytes' => 104857600, // 100 MiB

        /*
         * Error-log acceleration: the recent half of the history against the
         * earlier half. A site that has begun to fail writes errors faster and
         * faster, and the useful moment to hear about it is while the curve is
         * bending rather than when it crosses a line. Both a multiple and a
         * floor, because doubling from nothing is still nothing.
         */
        'acceleration_multiple' => 4.0,
        'acceleration_floor_per_hour' => 10485760, // 10 MiB/h
        'error_names' => [
            'error_log',
            'error.log',
            'php_errorlog',
            'php-error.log',
        ],
    ],

    /*
     * The troubleshooting assistant.
     *
     * Off by default and inert until a key is set. When it is on, this tool
     * sends parts of your logs, your disk figures and your process list to
     * whichever service you point it at - which is worth deciding deliberately
     * rather than discovering. A local model needs no key and nothing leaves
     * the machine; see the 'compatible' provider.
     */
    'ai' => [
        'enabled' => false,

        /*
         * Which wire format to speak.
         *
         *   openai            chat completions. Also Groq, Together, DeepSeek,
         *                     OpenRouter and most others - point base_url at
         *                     theirs.
         *   openai-responses  the newer endpoint. Reasoning models refuse
         *                     function tools on chat completions unless
         *                     reasoning is turned off entirely, which is
         *                     throwing away the reason for using them.
         *   anthropic         its own message format, translated to and from.
         *   compatible        Ollama or anything OpenAI-shaped: llama.cpp,
         *                     vLLM, or a gateway of your own. No key needed.
         *                     Loopback HTTP(S) is allowed for a local model;
         *                     a remote model must use a public HTTPS address
         *                     (a custom port such as :8443 is supported).
         */
        'provider' => 'openai',
        'base_url' => '',
        'model' => '',
        'api_key' => '',

        /*
         * How hard a reasoning model should think. Responses endpoint only.
         *
         * '', minimal, low, medium or high. The thinking is billed as output
         * tokens and on a reasoning model is usually most of the bill, so this
         * is the setting that moves the cost most.
         */
        'reasoning_effort' => '',

        /*
         * Prices per million tokens, in cents. Optional.
         *
         * Without them the session reports token counts and no money, which is
         * correct - a wrong price is worse than no price, so nothing is guessed
         * from the model name. Cached input is billed separately by everyone
         * who offers it, usually at a tenth of the rate, and on a conversation
         * that re-sends a long prompt every turn it is most of the bill.
         */
        'price_in' => 0,
        'price_out' => 0,
        'price_cached' => 0,

        /*
         * What one conversation may spend before it stops.
         *
         * Zero means no limit of that kind. All three are checked before each
         * call rather than after, so a limit stops the next request rather
         * than being noticed once the money is gone.
         */
        'max_seconds' => 3600,
        'max_cents' => 50,
        'max_turns' => 40,
        // Shared caps across every conversation and automated alert investigation.
        // Zero leaves that period uncapped; prices must be configured to measure spend.
        'daily_cents' => 0,
        'monthly_cents' => 0,

        /*
         * Which tools the assistant may use, and which need permission first.
         *
         *   'tools' => [
         *       'get_recent_log_lines' => ['enabled' => false],
         *       'take_screenshot'      => ['permission' => true],
         *   ]
         *
         * Anything absent uses the default in lib/aitools.php: diagnostic
         * reading tools on, every other subject off until you enable it. A tool
         * switched off is not described to the model at all - one told about a
         * tool it cannot use keeps trying it and explains its absence to the
         * user as a fault.
         */
        /*
         * Who it is. All three are optional; blank means the default.
         *
         * The default is called Mr. Radar and has a character - sharp,
         * efficiently professional, with a narrow and situational streak of
         * wit. Rewrite `personality` to change it entirely, or blank it to
         * put the default back. Whatever you write cannot override the rules
         * that keep the answers honest: those are added afterwards and say so.
         * Mr. Radar's default recurring-incident aside is "Oh no, not again!";
         * change the personality text in the Assistant Identity page if a
         * different reaction better fits this installation.
         */
        'name' => '',
        'emoji' => '',
        'personality' => '',

        /*
         * How much the model can hold, and when to summarise.
         *
         *   context_tokens         the model's window. Not discoverable from
         *                          the API - it is a fact about the model -
         *                          so it is typed in. Guessing high is the
         *                          worse mistake: the provider rejects the
         *                          request outright
         *   compact_at             per cent full at which the older part of a
         *                          conversation is replaced by a summary.
         *                          Clamped to 85: summarising means sending
         *                          the part being summarised, so a limit that
         *                          fires when the window is already full
         *                          cannot act
         *   compact_automatically  off means the ring in the chat goes amber
         *                          and waits to be clicked
         */
        'context_tokens' => 128000,
        'compact_at' => 70,
        'compact_automatically' => true,

        /*
         * Whether recommended commands come with their explanation.
         *
         * On, every command it suggests carries a comment above each part,
         * what the output should look like, and a documentation link. Somebody
         * pasting a command they have not understood is the thing this is meant
         * to prevent rather than a step on the way to it - but it is a
         * preference, and somebody fluent in shell may find it noise.
         */
        'explain_commands' => true,

        /*
         * Pictures, which are entirely optional and off until keyed.
         *
         *   enabled        the picture tools are not offered at all when off
         *   api_key        DeepAI
         *   styles         which generators it may choose from. Two or three;
         *                  a model offered twelve will use twelve
         *   quality        standard or hd (hd costs more per image)
         *   shape          square, classic, wide or tall
         *   per_hour       generated pictures only, which is the half that
         *                  costs money. This is what stops a loop running up
         *                  a bill
         *   self_portrait  let it regenerate its own avatar. A novelty
         *
         * Searching for an animation is a separate thing from generating a
         * picture, and is limited separately: a search is a free read against
         * somebody else's quota, a generation is a bill.
         *
         *   reactions          short animated images for the occasional aside
         *   search_service     tenor, klipy, or legacy. Three services with
         *                      the same shape and different terms; which one
         *                      an install may use is not something this code
         *                      can know
         *   search_key         for whichever of them is chosen
         *   search_base        the endpoint, if it is not the default. A
         *                      field rather than a constant so a service can
         *                      move without a code change
         *   searches_per_hour  up to 100, which is what these services publish
         *                      as a free allowance. It can be set lower and
         *                      not higher: a ceiling that a setting can lift
         *                      past the provider's own protects nothing
         *   rating             g, pg or pg-13. These appear unannounced in a
         *                      browser, chosen by a language model from a
         *                      search term it invented; g is the sensible
         *                      default
         *
         * What pictures cost, for the running total. The plan these are sold
         * on is a monthly fee with an allowance and then credits, so a
         * per-image price is only true once the allowance is gone. Both are
         * recorded and the arithmetic is shown rather than hidden - inside the
         * allowance a picture adds nothing to a conversation's cost, because
         * that is the truth. The monthly fee is never divided among whatever
         * happened to be generated: that would make the first picture of the
         * month cost five dollars and the hundredth cost nothing.
         *
         *   monthly_cents       what the plan costs each month
         *   included_per_month  how many generations it covers
         *   price_standard      cents each, after the allowance
         *   price_hd            cents each at HD
         *   price_genius        cents each at the top quality
         */
        'images' => [],

        'tools' => [],
        // Named, reusable tool and permission configurations saved from the
        // Tools page. Built-in Diagnostic and Operator approval sets need not
        // be written here.
        'toolsets' => [],

        // Defaults to ai.db beside the other two databases.
        'database' => null,
    ],

    /*
     * Reading what captured traffic was asking for.
     *
     * A capture counts packets and addresses. That answers "there were 748
     * packets on port 443" and nothing about what they wanted, which is almost
     * always the actual question.
     *
     * Turning this on for a capture makes tcpdump print packet contents so that
     * three things which travel in the clear can be read out and counted: the
     * host name in a TLS handshake, and the Host and page of an HTTP request.
     * Nothing decrypts, no payload is stored, and a query string is cut off
     * before counting - a path says which page, and a query string is where
     * tokens and search terms live.
     *
     * It is asked for per capture from the page rather than set here, because
     * it means the process has looked at the beginning of other people's
     * traffic on a machine that may be shared. That is a decision worth making
     * one capture at a time.
     *
     * There is nothing to configure. This note is here because somebody reading
     * the configuration to find out what this tool looks at deserves to find
     * it, and its absence would read as its absence.
     */

    /*
     * A shell, in a web page.
     *
     * Off by default, and the default is the important part. This runs
     * whatever is typed into it, as the user the web server runs as, and
     * returns the output - which is to say it is a web shell. Anybody who gets
     * the dashboard password gets this. That is not an argument against having
     * it, but it is an argument for the decision being deliberate rather than
     * inherited from a sample file somebody copied.
     *
     * What it buys is the half of troubleshooting that PHP running as this
     * account cannot reach: the process table as root sees it, the output of
     * a host utility, whatever a support technician asked for. Without it the
     * answer is "open an SSH session and paste this back to me", which loses
     * the context, the transcript, and usually the person.
     *
     * The assistant cannot run anything. It can put a command on the page for
     * somebody to read and decide about, and there is no tool that executes.
     *
     *   enabled      off until this says otherwise
     *   max_seconds  anything still going after this is killed. The page
     *                checks on read, because nothing else is running to notice
     *   shell        /bin/sh unless the host is unusual
     *
     * Every command is written to the action log before it runs, not after: a
     * command that hangs the machine still has to appear in the record.
     */
    'terminal' => [
        'enabled' => false,
        'max_seconds' => 300,
        'shell' => '/bin/sh',
    ],

    /*
     * How often each scan runs, and who it speeds up for.
     *
     * Only the ones that differ from the recommendation are stored, so a later
     * change to what this tool recommends reaches an existing install rather
     * than being frozen out by a config written today.
     *
     *   'scans' => [
     *       'scan'             => ['every' => 43200],
     *       'host-capture'     => ['every' => 21600],
     *       'processes'        => ['every' => 300, 'live_only' => true],
     *       'vitals'           => ['boost' => false],
     *   ]
     *
     * `every` is seconds, and 0 means only when asked. `boost` collects more
     * eagerly while somebody has the dashboard open. `live_only` records
     * nothing unless somebody is looking, which suits the samples that are
     * only interesting when a person is watching them happen.
     *
     * The recommendations are in lib/scans.php with the reasoning attached,
     * including for the ones recommended NOT to run on a schedule at all.
     */
    'scans' => [],

    'retention' => [
        // Every reading exactly as collected.
        'raw_days' => 7,
        // Then one row per five minutes.
        'five_minute_days' => 30,
        // Then one row per hour.
        'hourly_days' => 365,
        // Then one row per day, kept indefinitely - a few hundred rows a year,
        // and what makes "was this normal last summer?" answerable.
        'daily_days' => 0,

        /*
         * Untouched history either side of an alert.
         *
         * The hour around the thing that went wrong is the whole reason for
         * keeping any of this, and compacting away the readings that sit
         * beside an incident record would be a poor joke. These stay at full
         * resolution however old they get.
         */
        'protect_hours' => 2,

        /*
         * Incident snapshots, kept separately from everything else because
         * they are the evidence rather than the measurements. One per alerting
         * evaluation - not one per reading - at roughly 26 KB, so even a bad
         * year is a few hundred rows and a handful of megabytes.
         */
        'incident_days' => 0,
        'alert_days' => 0,
        'action_days' => 0,

        'scan_days' => 400,
        'process_days' => 400,
        // Accepted remote readings and short-lived replay-receipt evidence.
        'remote_vital_days' => 400,
        'remote_receipt_days' => 2,
    ],

    /* Private destination for vulnerability reports concerning this install.
     * Leave blank only until the installation owner has chosen one; never use
     * this value as a public contact or expose it to signed-out visitors. */
    'security' => [
        'contact' => '',
    ],

    'host' => [
        /*
         * Whether any of this is in play.
         *
         * Off means host-cron.php refuses to run and the dashboard hides the
         * panel entirely - worth setting if you have no root on the machine,
         * since none of it can work without it.
         */
        'enabled' => true,

        // Defaults to host.db beside the account database, which .htaccess
        // already denies over HTTP.
        'database' => null,

        /*
         * Who the dashboard runs as.
         *
         * Root creates the database, so without this only root can read it and
         * the dashboard shows an empty panel with no clue why. Set it to the
         * cPanel username. The wizard fills it in with whatever account it is
         * running as, which is the right answer in every normal case.
         *
         * Blank means no handover: root keeps the file to itself and the
         * dashboard cannot read it. That is the safe direction to fail in -
         * this database lists every process on the machine with its command
         * line, so a wrong guess that made it world-readable would be worse
         * than one that made it unreadable.
         */
        'owner' => '',

        'keep_days' => 14,

        /*
         * How often `--collect` actually takes a reading, in seconds.
         *
         * hostCronLine() recommends a crontab entry of "every five minutes"
         * because a full reading walks every process, slab cache and
         * service unit, which isn't free and doesn't change meaningfully
         * inside a minute. This gate enforces that regardless of what
         * schedule ends up installed - a crontab edited to run more often
         * only multiplies write-write collision risk and every days-based
         * retention table's steady-state size. `--force` bypasses it for a
         * manual run.
         */
        'collect_interval_seconds' => 300,

        /*
         * The disk walk, run by `php host-cron.php --disk`.
         *
         * Its own command and its own schedule, because it takes tens of
         * seconds and answers a question that does not change minute to
         * minute. Weekly in cron is plenty; run it by hand when space has gone
         * missing and you want to know where.
         *
         * The walk never crosses a filesystem boundary - other disks are
         * separate questions with their own rows - and never follows a symlink.
         */
        'walk_root' => '/',
        'walk_seconds' => 45,

        /*
         * Directories to step over.
         *
         * Network mounts and backup volumes turn a one-minute job into an
         * unbounded one. Anything on a different filesystem is skipped
         * automatically; this is for the ones that are not.
         */
        'walk_skip' => ['/proc', '/sys', '/dev'],

        /*
         * Which daemons to watch, and what they are called here.
         *
         * Leave this out entirely and a sensible list is used: Apache, MariaDB,
         * Forgejo, MongoDB, Redis, Icecast, PHP-FPM, Exim, Dovecot and BIND.
         * Each knows several names for itself, because the same service is
         * httpd on Red Hat and apache2 on Debian.
         *
         * Setting it *replaces* that list rather than adding to it. Somebody
         * who writes out the services they care about means those services, and
         * quietly watching ten more would be the monitor arguing with them.
         *
         * A service on the list that this machine does not run is reported as
         * "not installed" and never alerts. That is the point of having a
         * generous default: there is no cost to watching for something that
         * was never here, and a real cost to leaving out the one that dies.
         *
         *   'daemons' => [
         *       ['name' => 'httpd', 'label' => 'Apache',
         *        'units' => ['httpd', 'apache2'],   // systemd, globs allowed
         *        'processes' => ['httpd']],         // fallback without systemd
         *   ],
         */
        'daemons' => null,

        /*
         * How long to keep the per-service memory series.
         *
         * Much longer than the rest, because these rows are a name and five
         * integers - about a megabyte a month - and their entire value is being
         * older than the detail tables. Answering "was Forgejo this big in
         * June?" is the difference between a diagnostic and a record.
         */
        'keep_service_days' => 90,

        /*
         * Files worth watching that the account cannot stat.
         *
         * Apache writes each domain's logs under /etc/apache2 owned by root.
         * Anything smaller than watch_min_bytes is ignored, or this becomes a
         * list of several hundred empty logs.
         */
        'watch' => [
            '/etc/apache2/logs/domlogs/*',
            '/etc/apache2/logs/domlogs/*/*',
            '/usr/local/apache/logs/*log',
            '/var/log/messages*',
        ],
        'watch_min_bytes' => 1048576,
    ],
];
