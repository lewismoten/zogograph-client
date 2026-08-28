<?php
declare(strict_types=1);
require_once __DIR__ . '/package-runtime.php';
require_once __DIR__ . '/openid.php';

/*
 * What a config is made of, described once.
 *
 * Two front ends ask these questions - the browser and the command line - and
 * they used to be two separate lists of prompts. One list, described as data,
 * is the only way they stay the same: a field added here appears in both, with
 * the same label, the same default and the same validation.
 *
 * The defaults all come from lib/config.sample.php, which remains the single
 * description of what an option means.
 */

/**
 * Every category the rules can produce, in a stable order.
 *
 * Derived rather than written out, because it was written out - in the scan, in
 * the API's column list and in the dashboard's filter - and adding a fourth
 * meant finding all three. A category defined in the config and missing from
 * one of those lists is one that gets collected and never shown.
 */
function categoryNames(array $rules): array
{
    $names = [];
    foreach (['category_dirs', 'category_files', 'category_extensions'] as $group) {
        foreach (array_keys($rules[$group] ?? []) as $category) {
            $names[$category] = true;
        }
    }
    return array_keys($names);
}

/**
 * Every question the wizard asks.
 *
 * `path` is where the answer lands in the config array. `type` decides how it
 * is prompted for and how it is parsed. `when` hides a field unless another
 * answer makes it relevant.
 */
function configFields(array $sample): array
{
    return [
        [
            'key' => 'core_features_assistant',
            'section' => 'features',
            'path' => ['core_features', 'assistant'],
            'type' => 'bool',
            'label' => 'Assistant core feature',
            'help' => 'Disabling this removes the assistant from the dashboard and blocks its API routes. Existing assistant settings and private data are retained for a later re-enable.',
            'default' => (bool)($sample['core_features']['assistant'] ?? true),
        ],
        [
            'key' => 'core_features_peers',
            'section' => 'features',
            'path' => ['core_features', 'peers'],
            'type' => 'bool',
            'label' => 'Trusted servers core feature',
            'help' => 'Disabling this removes trusted servers from the dashboard and refuses its API, telemetry, and connection routes. Existing peer settings and private data are retained for a later re-enable.',
            'default' => (bool)($sample['core_features']['peers'] ?? true),
        ],
        [
            'key' => 'core_features_marketplace',
            'section' => 'features',
            'path' => ['core_features', 'marketplace'],
            'type' => 'bool',
            'label' => 'Marketplace core feature',
            'help' => 'Disabling this removes Marketplace from the dashboard and blocks its API routes. Installed marketplace metadata and revision history are retained for a later re-enable.',
            'default' => (bool)($sample['core_features']['marketplace'] ?? true),
        ],
        [
            'key' => 'core_features_terminal',
            'section' => 'features',
            'path' => ['core_features', 'terminal'],
            'type' => 'bool',
            'label' => 'Terminal core feature',
            'help' => 'Disabling this removes the Terminal and its sessions from the dashboard, blocks its API routes, and prevents assistant tools from reading terminal history. Existing private terminal evidence is retained for a later re-enable.',
            'default' => (bool)($sample['core_features']['terminal'] ?? true),
        ],
        [
            'key' => 'core_features_notes',
            'section' => 'features',
            'path' => ['core_features', 'notes'],
            'type' => 'bool',
            'label' => 'Notes core feature',
            'help' => 'Disabling this removes notes, database copies, chart annotations, and broadcasts from the dashboard and blocks their API routes. Existing notes and captured evidence are retained for a later re-enable.',
            'default' => (bool)($sample['core_features']['notes'] ?? true),
        ],
        [
            'key' => 'openid_enabled',
            'section' => 'openid',
            'path' => ['openid', 'enabled'],
            'type' => 'bool',
            'label' => 'Act as an OpenID Connect provider',
            'help' => 'Lets explicitly registered trusted applications sign users in through this Zogograph installation. It does not make this server a public identity service.',
            'default' => (bool)($sample['openid']['enabled'] ?? false),
        ],
        [
            'key' => 'openid_issuer',
            'section' => 'openid',
            'path' => ['openid', 'issuer'],
            'type' => 'text',
            'label' => 'Public issuer URL',
            'help' => 'The HTTPS URL trusted clients use to discover this provider, for example https://monitor.example.com. Do not include a trailing slash.',
            'default' => (string)($sample['openid']['issuer'] ?? ''),
            'validate' => static fn(string $value): ?string => $value === '' || openIdIssuerUrl($value) !== null
                ? null : 'Enter a complete HTTPS URL without credentials, a query, or a fragment.',
        ],
        [
            'key' => 'openid_signing_private_key',
            'section' => 'openid',
            'path' => ['openid', 'signing_private_key'],
            'type' => 'textarea',
            'label' => 'OpenID signing private key',
            'help' => 'Required before enabling the provider. Use a dedicated RSA private key; never reuse the TLS certificate key. Setup can generate a dedicated key that is kept only in the protected setup session until this section is saved.',
            'default' => (string)($sample['openid']['signing_private_key'] ?? ''),
            'when' => ['openid_enabled' => true],
        ],
        [
            'key' => 'scan_root',
            'section' => 'monitoring',
            'path' => ['scan_root'],
            'type' => 'text',
            'label' => 'Directory to watch',
            'help' => 'Everything is measured under here, and nothing outside it can ever be deleted.',
            'default' => (string)$sample['scan_root'],
            'validate' => function (string $value): ?string {
                if ($value === '') {
                    return 'A directory is required.';
                }
                if (!is_dir($value)) {
                    return "There is no directory at $value.";
                }
                if (!is_readable($value)) {
                    return "$value is not readable by this user.";
                }
                return null;
            },
        ],
        [
            'key' => 'timezone',
            'section' => 'monitoring',
            'path' => ['timezone'],
            'type' => 'text',
            'label' => 'Timezone',
            'help' => 'Used for the timestamps on the dashboard and in alert emails.',
            'default' => (string)$sample['timezone'],
            'validate' => fn(string $v): ?string => in_array($v, timezone_identifiers_list(), true)
                ? null
                : 'Not a timezone PHP knows. Try America/New_York or UTC.',
        ],
        [
            'key' => 'encryption_mode',
            'section' => 'encryption',
            'path' => ['encryption', 'mode'],
            'type' => 'choice',
            'label' => 'Encryption mode',
            'help' => 'Off keeps all current storage unencrypted. Sealed copies make separately encrypted, '
                . 'verified snapshots; they do not alter the live database or its journal. Host-managed '
                . 'volume protection is configured and operated outside Zogograph.',
            'default' => (string)($sample['encryption']['mode'] ?? 'none'),
            'options' => [
                'none' => 'Off — do not encrypt anything',
                'sealed_copies_local' => 'Encrypted copies with a local private key',
                'operator_managed_volume' => 'My host/VM encrypts the selected live stores',
            ],
        ],
        [
            'key' => 'package_runtime_trusted_keys',
            'section' => 'package_runtime',
            'path' => ['package_runtime', 'trusted_keys'],
            'type' => 'json',
            'label' => 'Trusted executable-package publishers',
            'help' => 'Optional. Add only a publisher key you independently trust. Each key id maps to a publisher name and Base64 Ed25519 public_key. A signed package cannot install unless its publisher and key match this list.',
            'default' => (array)($sample['package_runtime']['trusted_keys'] ?? []),
            'validate' => function (array $value): ?string {
                try { packageRuntimeTrustedKeys($value); return null; }
                catch (InvalidArgumentException $error) { return $error->getMessage(); }
            },
        ],
        [
            'key' => 'package_runtime_secret_key_file',
            'section' => 'package_runtime',
            'path' => ['package_runtime', 'secret_key_file'],
            'type' => 'text',
            'label' => 'Package secrets key file (optional)',
            'help' => 'An absolute private key file outside this web directory. Leave blank to reuse the local encryption key when configured. Without either key, package secret references cannot be saved. A separate file gives package credentials separate custody.',
            'default' => (string)($sample['package_runtime']['secret_key_file'] ?? ''),
            'validate' => function (string $value): ?string {
                if ($value === '') return null;
                try { encryptionReadLocalKey($value, dirname(__DIR__)); return null; }
                catch (Throwable $error) { return $error->getMessage(); }
            },
        ],
        [
            'key' => 'encryption_key_custody',
            'section' => 'encryption',
            'path' => ['encryption', 'key_custody'],
            'type' => 'choice',
            'label' => 'Key custody',
            'help' => 'The built-in sealed-copy mode currently uses one private local key file. Fleet, '
                . 'manual-unlock, and marketplace key providers will be selectable only when their secure '
                . 'protocols are installed and tested.',
            'default' => (string)($sample['encryption']['key_custody'] ?? 'local_private_file'),
            'options' => ['local_private_file' => 'Private local key file outside the web directory'],
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_key_file',
            'section' => 'encryption',
            'path' => ['encryption', 'local_key_file'],
            'type' => 'text',
            'label' => 'Private key-file path',
            'help' => 'Create it first with scripts/encryption-key.php. It must be an absolute, non-symlink '
                . 'file outside this web directory and readable only by the Zogograph service account.',
            'default' => (string)($sample['encryption']['local_key_file'] ?? ''),
            'validate' => fn(string $v): ?string => $v !== '' && str_starts_with($v, '/')
                ? null : 'Enter an absolute private key-file path.',
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_artifact_directory',
            'section' => 'encryption',
            'path' => ['encryption', 'artifact_directory'],
            'type' => 'nullable',
            'label' => 'Encrypted-copy directory',
            'help' => 'Leave blank for data/sealed. Use an absolute private path to keep encrypted copies elsewhere.',
            'default' => $sample['encryption']['artifact_directory'] ?? null,
            'validate' => function (?string $value): ?string {
                if ($value === null || $value === '') return null;
                try { encryptionValidateArtifactDirectory($value, dirname(__DIR__)); return null; }
                catch (Throwable $error) { return $error->getMessage(); }
            },
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_stores',
            'section' => 'encryption',
            'path' => ['encryption', 'stores'],
            'type' => 'choices',
            'label' => 'Databases to copy into encrypted artifacts',
            'help' => 'The recommended start protects bootstrap configuration, setup, and account data. Add host or assistant stores when you want their process evidence or conversations protected too; those optional stores wait until they exist.',
            'default' => (array)($sample['encryption']['stores'] ?? []),
            'options' => [
                'config' => 'Bootstrap configuration file', 'setup' => 'Setup and configuration database',
                'account' => 'Account metrics and audit history',
                'host' => 'Host-wide metrics', 'ai' => 'Assistant conversations and memory',
            ],
            'when' => ['encryption_mode' => ['sealed_copies_local', 'operator_managed_volume']],
        ],
        [
            'key' => 'encryption_backup_schedule',
            'section' => 'encryption',
            'path' => ['encryption', 'backup_schedule'],
            'type' => 'choice',
            'label' => 'Encrypted-copy schedule',
            'help' => 'Zogograph records this preference; configure the matching cron or host-scheduler job. '
                . 'Manual creates copies only when cron.php --seal is run.',
            'default' => (string)($sample['encryption']['backup_schedule'] ?? 'daily'),
            'options' => ['manual' => 'Manual only', 'daily' => 'Daily', 'weekly' => 'Weekly'],
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_retention_days',
            'section' => 'encryption',
            'path' => ['encryption', 'retention_days'],
            'type' => 'int',
            'label' => 'Local encrypted-copy retention target (days)',
            'help' => 'Zero means keep every copy. During an explicit sealing run, Zogograph prunes only old verified local artifacts whose ciphertext hash still matches, and never the newest verified copy for a store.',
            'default' => (int)($sample['encryption']['retention_days'] ?? 30),
            'validate' => fn(string $v): ?string => ctype_digit($v) && (int)$v <= 36500
                ? null : 'Enter a whole number from 0 to 36500.',
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_retention_days_by_store',
            'section' => 'encryption',
            'path' => ['encryption', 'retention_days_by_store'],
            'type' => 'json',
            'label' => 'Per-store retention overrides (advanced)',
            'help' => 'Optional JSON object, for example {"setup":365,"account":30}. Zero keeps every copy for that store. Stores not listed use the normal retention target.',
            'default' => (array)($sample['encryption']['retention_days_by_store'] ?? []),
            'validate' => function (array $value): ?string {
                $stores = ['config', 'setup', 'account', 'host', 'ai'];
                foreach ($value as $store => $days) {
                    if (!is_string($store) || !in_array($store, $stores, true)) return 'Use only config, setup, account, host, or ai as store names.';
                    if (!is_int($days) || $days < 0 || $days > 36500) return 'Each retention value must be a whole number from 0 to 36500.';
                }
                return null;
            },
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_recovery_drill_max_age_days',
            'section' => 'encryption',
            'path' => ['encryption', 'recovery_drill_max_age_days'],
            'type' => 'int',
            'label' => 'Recovery-drill review period (days)',
            'help' => 'Zogograph warns when a selected store has not completed a non-destructive recovery drill within this period. Ninety days is the recommended starting point; zero disables periodic reminders after the first required drill.',
            'default' => (int)($sample['encryption']['recovery_drill_max_age_days'] ?? 90),
            'validate' => fn(string $v): ?string => ctype_digit($v) && (int)$v <= 36500
                ? null : 'Enter a whole number from 0 to 36500.',
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'encryption_retention_recovery_tiers',
            'section' => 'encryption',
            'path' => ['encryption', 'retention_recovery_tiers'],
            'type' => 'json',
            'label' => 'Long-term recovery copies (advanced)',
            'help' => 'Optional JSON object such as {"daily":7,"weekly":4,"monthly":12}. It preserves the newest verified copy from each distinct UTC day, week, or month in addition to normal retention. Zero or omitted tiers keep the simple retention target.',
            'default' => (array)($sample['encryption']['retention_recovery_tiers'] ?? ['daily' => 0, 'weekly' => 0, 'monthly' => 0]),
            'validate' => function (array $value): ?string {
                foreach ($value as $tier => $copies) {
                    if (!in_array($tier, ['daily', 'weekly', 'monthly'], true) || !is_int($copies) || $copies < 0 || $copies > 36500) return 'Use daily, weekly, and/or monthly whole-number values from 0 to 36500.';
                }
                return null;
            },
            'when' => ['encryption_mode' => 'sealed_copies_local'],
        ],
        [
            'key' => 'password',
            'section' => 'security',
            'path' => ['password_hash'],
            'type' => 'password',
            'label' => 'Sign-in password',
            'help' => 'Reveals real paths and enables deleting. Leave blank to disable sign-in, '
                . 'which leaves the dashboard readable with every path masked.',
            'default' => '',
        ],
        [
            'key' => 'alerts_enabled',
            'section' => 'alerts',
            'path' => ['alerts', 'enabled'],
            'type' => 'bool',
            'label' => 'Send alert emails',
            'help' => 'The point of the thing: it tells you before the disk fills, not after.',
            'default' => (bool)$sample['alerts']['enabled'],
        ],
        [
            'key' => 'alerts_to',
            'section' => 'alerts',
            'path' => ['alerts', 'to'],
            'type' => 'email',
            'label' => 'Send alerts to',
            'default' => (string)$sample['alerts']['to'],
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => filter_var($v, FILTER_VALIDATE_EMAIL)
                ? null
                : 'That does not look like an email address.',
        ],
        [
            'key' => 'alerts_from',
            'section' => 'alerts',
            'path' => ['alerts', 'from'],
            'type' => 'email',
            'label' => 'Send alerts from',
            'help' => 'Blank uses whatever the server sends as by default.',
            'default' => (string)$sample['alerts']['from'],
            'when' => 'alerts_enabled',
            'optional' => true,
            'validate' => fn(string $v): ?string => $v === '' || filter_var($v, FILTER_VALIDATE_EMAIL)
                ? null
                : 'That does not look like an email address.',
        ],
        [
            'key' => 'disk_free_bytes',
            'section' => 'alerts',
            'path' => ['alerts', 'disk_free_bytes'],
            'type' => 'bytes',
            'label' => 'Warn when free space drops below',
            'help' => 'Write it how you say it: 5G, 500M, or a plain number of bytes.',
            'default' => (int)$sample['alerts']['disk_free_bytes'],
            'when' => 'alerts_enabled',
        ],
        [
            'key' => 'projected_full_hours',
            'section' => 'alerts',
            'path' => ['alerts', 'projected_full_hours'],
            'type' => 'number',
            'label' => 'Warn when the trend says it will be full within (hours)',
            'help' => 'Fitted over the last day of readings. This is the alert that arrives '
                . 'while there is still time to do something.',
            'default' => (float)$sample['alerts']['projected_full_hours'],
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (float)$v > 0
                ? null
                : 'Enter a number of hours.',
        ],
        [
            'key' => 'path_jump_bytes',
            'section' => 'alerts',
            'path' => ['alerts', 'path_jump_bytes'],
            'type' => 'bytes',
            'label' => 'Warn when one file or directory grows in a single scan by',
            'help' => 'The alert that names the culprit rather than the symptom.',
            'default' => (int)$sample['alerts']['path_jump_bytes'],
            'when' => 'alerts_enabled',
        ],
        [
            'key' => 'cpu_percent',
            'section' => 'alerts',
            'path' => ['alerts', 'cpu_percent'],
            'type' => 'number',
            'label' => 'Warn when CPU stays at or above (%)',
            'help' => 'Sustained, not a spike: it has to hold across the whole window '
                . 'below. Zero turns it off. On CloudLinux this is a share of what the '
                . 'account may use, not of the machine.',
            'default' => (float)($sample['alerts']['cpu_percent'] ?? 95),
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (float)$v >= 0 && (float)$v <= 100
                ? null
                : 'Enter a percentage between 0 and 100.',
        ],
        [
            'key' => 'cpu_minutes',
            'section' => 'alerts',
            'path' => ['alerts', 'cpu_minutes'],
            'type' => 'int',
            'label' => 'How long it must stay there (minutes)',
            'help' => 'A burst cannot hold across a long window, which is the whole point '
                . 'of measuring duration rather than height.',
            'default' => (int)($sample['alerts']['cpu_minutes'] ?? 15),
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (int)$v >= 1
                ? null
                : 'Enter a number of minutes, at least 1.',
        ],
        [
            'key' => 'cooldown_seconds',
            'section' => 'alerts',
            'path' => ['alerts', 'cooldown_seconds'],
            'type' => 'seconds',
            'label' => 'Wait at least this long before repeating an alert',
            'help' => 'Written how you say it: 6h, 90m, 2d. A plain number is seconds. '
                . 'An alert that arrives every minute becomes a filter rule within a day.',
            'default' => (int)$sample['alerts']['cooldown_seconds'],
            'when' => 'alerts_enabled',
        ],

        /*
         * The baseline rules.
         *
         * Only the switch and the two numbers worth arguing about are asked
         * here. The rest have sensible defaults that depend on each other, and
         * a wizard page of seventeen tuning knobs is one nobody finishes; they
         * are all documented in lib/config.sample.php for anyone who wants them.
         */
        [
            'key' => 'anomalies_enabled',
            'section' => 'alerts',
            'path' => ['alerts', 'anomalies', 'enabled'],
            'type' => 'bool',
            'label' => 'Also alert on what is unusual, not just what is large',
            'help' => 'Compares each service, category and file against its own history. '
                . 'It catches the things no threshold can - a job that normally takes two '
                . 'minutes still running after an hour - and stays quiet about the nightly '
                . 'backup that always grows. Says nothing until it has history to say it from.',
            'default' => (bool)($sample['alerts']['anomalies']['enabled'] ?? true),
            'when' => 'alerts_enabled',
        ],
        [
            'key' => 'anomalies_min_readings',
            'section' => 'alerts',
            'path' => ['alerts', 'anomalies', 'min_readings'],
            'type' => 'int',
            'label' => 'Readings of history needed before it will judge anything',
            'help' => 'Below this it says nothing at all. Too low and its first day is '
                . 'all false alarms, which is how these get turned off.',
            'default' => (int)($sample['alerts']['anomalies']['min_readings'] ?? 8),
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (int)$v >= 3
                ? null
                : 'Enter a number of readings, at least 3.',
        ],
        [
            'key' => 'anomalies_service_percent',
            'section' => 'alerts',
            'path' => ['alerts', 'anomalies', 'service_pss_percent'],
            'type' => 'number',
            'label' => 'Warn when a service holds more memory than usual by (%)',
            'help' => 'Against its own median over the last day, not against a fixed size. '
                . 'A service that grows and stays grown is leaking; one that grows and comes '
                . 'back was busy.',
            'default' => (float)($sample['alerts']['anomalies']['service_pss_percent'] ?? 25),
            'when' => 'alerts_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (float)$v > 0
                ? null
                : 'Enter a percentage above 0.',
        ],

        [
            'key' => 'deletable_categories',
            'section' => 'maintenance',
            'path' => ['deletable_categories'],
            'type' => 'categories',
            'label' => 'Categories that may be deleted from the dashboard',
            'help' => 'Only ever by a signed-in session, and only inside the watched directory.',
            'default' => (array)$sample['deletable_categories'],
            // Whatever the rules define, so a category added to the config
            // shows up here instead of being quietly undeletable.
            'options' => categoryNames($sample['scan']),
        ],

        /*
         * The host half.
         *
         * Only useful with root on the machine, so it is asked last and can be
         * turned off in one click. Everything under it is hidden until it is on.
         */
        [
            'key' => 'host_enabled',
            'section' => 'host',
            'path' => ['host', 'enabled'],
            'type' => 'bool',
            'label' => 'Also collect host-wide readings (needs root)',
            'help' => 'Adds a second collector, run by root from WHM, that sees what this '
                . 'account cannot: other users\' processes and memory, kernel slab caches, '
                . 'and root-owned logs under /etc. On CloudLinux this is the only way to find '
                . 'out what is using the machine.',
            'default' => (bool)($sample['host']['enabled'] ?? true),
        ],
        [
            'key' => 'host_owner',
            'section' => 'host',
            'path' => ['host', 'owner'],
            'type' => 'text',
            'label' => 'Account the dashboard runs as',
            'help' => 'Root creates the host database, so without this only root can read it '
                . 'and the panel stays empty. Almost always the cPanel username.',
            // Detected rather than guessed: the user PHP is running as right
            // now is the one that will be reading the file later.
            'default' => currentAccountName() ?: (string)($sample['host']['owner'] ?? ''),
            'when' => 'host_enabled',
            'validate' => fn(string $v): ?string => $v === '' || preg_match('/^[a-z_][a-z0-9_-]*$/i', $v)
                ? null
                : 'That does not look like a username.',
            'optional' => true,
        ],
        [
            'key' => 'host_watch',
            'section' => 'host',
            'path' => ['host', 'watch'],
            'type' => 'lines',
            'label' => 'Root-owned files to watch, one pattern per line',
            'help' => 'Apache writes each domain\'s logs under /etc owned by root, where a '
                . 'rotated log can reach several hundred megabytes invisibly. Shell globs.',
            'default' => (array)($sample['host']['watch'] ?? []),
            'when' => 'host_enabled',
        ],
        [
            'key' => 'host_keep_days',
            'section' => 'host',
            'path' => ['host', 'keep_days'],
            'type' => 'int',
            'label' => 'Days of host readings to keep',
            'help' => 'A host reading is much bigger than an account one - it carries every '
                . 'user, the biggest processes and the slab table.',
            'default' => (int)($sample['host']['keep_days'] ?? 14),
            'when' => 'host_enabled',
            'validate' => fn(string $v): ?string => is_numeric($v) && (float)$v >= 1
                ? null
                : 'Enter a number of days, at least 1.',
        ],
    ];
}

/* ------------------------------------------------------------------ *
 * Every other option in the file
 * ------------------------------------------------------------------ */

/**
 * What the sample file says about each option, read out of its own comments.
 *
 * The sample is the single description of what an option is - that is the rule
 * this whole wizard is built on - so the help text comes from there rather than
 * being written twice and drifting apart. Two shapes are picked up:
 *
 *   a comment directly above a key, whether `//` or a block
 *   a line inside a section's block comment of the form
 *       key      what it does
 *     which is how the sample already documents the options in a group
 *
 * Anything it cannot find help for is still offered, without any. A missing
 * sentence is a smaller problem than a missing option.
 *
 * @return array{help: array<string, string>, sections: array<string, string>}
 */
function sampleHelp(string $samplePath): array
{
    $source = is_file($samplePath) ? (string)file_get_contents($samplePath) : '';
    $lines = preg_split('/\R/', $source) ?: [];

    $help = [];
    $sections = [];
    $stack = [];
    $comment = [];
    $inBlock = false;
    // Per-key lines from the block comment that opened the section we are in.
    $keyNotes = [];
    $noteStack = [];

    foreach ($lines as $line) {
        $trim = trim($line);

        /* ------------------------------------------------- comments -- */

        if (!$inBlock && str_starts_with($trim, '/*')) {
            $inBlock = true;
            $comment = [];
            $trim = ltrim(substr($trim, 2), '*');
            if ($trim === '') {
                continue;
            }
        }

        if ($inBlock) {
            $done = str_contains($trim, '*/');
            $text = trim(str_replace(['*/', '*'], '', $trim));
            if ($text !== '') {
                $comment[] = $text;
            }
            if ($done) {
                $inBlock = false;
            }
            continue;
        }

        if (str_starts_with($trim, '//')) {
            $comment[] = trim(substr($trim, 2));
            continue;
        }

        /* ----------------------------------------------- structure -- */

        // A section opening: 'name' => [   with nothing closing it on the line.
        if (preg_match("/^'([^']+)'\s*=>\s*\[\s*$/", $trim, $m)) {
            $stack[] = $m[1];
            $path = implode('.', $stack);
            if ($comment !== []) {
                $sections[$path] = implode(' ', $comment);
            }
            // The notes for the keys inside are the ones this comment listed.
            $noteStack[] = $keyNotes;
            $keyNotes = keyNotesIn($comment);
            $comment = [];
            continue;
        }

        if ($trim === '],' || $trim === ']' || $trim === '];') {
            array_pop($stack);
            $keyNotes = array_pop($noteStack) ?? [];
            $comment = [];
            continue;
        }

        // A leaf: 'name' => something,
        if (preg_match("/^'([^']+)'\s*=>\s*(.+?),?\s*$/", $trim, $m)) {
            $key = $m[1];
            $path = implode('.', array_merge($stack, [$key]));

            $said = $comment !== []
                ? implode(' ', $comment)
                : ($keyNotes[$key] ?? '');

            if ($said !== '') {
                $help[$path] = $said;
            }

            /*
             * The comment is not cleared here, on purpose.
             *
             * The sample documents a run of related keys with one comment above
             * all of them - "all three of these define the rule together" sits
             * above cpu_percent, cpu_minutes and cpu_share. Clearing after the
             * first would leave the other two undocumented while the sentence
             * explaining them is three lines up. A blank line ends the run,
             * which is exactly where the file already puts one.
             */
            continue;
        }

        // A blank line, or anything else, ends a comment's reach.
        $comment = [];
    }

    return ['help' => $help, 'sections' => $sections];
}

/**
 * The per-key lines out of one block comment.
 *
 * The sample documents a group by listing its keys in a column:
 *
 *     enabled      off until this says otherwise
 *     max_seconds  anything still going after this is killed. The page
 *                  checks on read, because nothing else is running to notice
 *
 * A continuation line is one that starts with spaces and no key, and belongs to
 * whichever key was last named.
 *
 * @param list<string> $comment
 * @return array<string, string>
 */
function keyNotesIn(array $comment): array
{
    $notes = [];
    $last = null;

    foreach ($comment as $line) {
        if (preg_match('/^([a-z][a-z0-9_]{2,})\s{2,}(\S.*)$/', $line, $m)) {
            $last = $m[1];
            $notes[$last] = trim($m[2]);
            continue;
        }
        // Only continue a note while the lines keep coming; a blank line or a
        // sentence of prose ends the list.
        if ($last !== null && $line !== '' && !str_ends_with($line, '.')) {
            $notes[$last] .= ' ' . $line;
            continue;
        }
        $last = null;
    }

    return $notes;
}

/**
 * Every question, hand-written and generated, in one list.
 *
 * The wizard used to ask twenty of the file's hundred and nineteen options, and
 * the other ninety-nine meant opening lib/config.php in an editor. The rest are
 * generated from the sample, so an option added there appears here without
 * anybody remembering to add it - the only way a list this long stays true.
 *
 * The hand-written ones still win where they exist. They carry validation,
 * better wording, and the `when` rules that hide what does not apply.
 *
 * @return list<array>
 */
function allConfigFields(array $sample, ?string $samplePath = null): array
{
    static $path = null;

    /*
     * The path is remembered once.
     *
     * buildConfig is handed a config array and no path, and it must produce the
     * same field list the form was rendered from - a field the form asked about
     * and buildConfig has never heard of is an answer silently dropped.
     */
    if ($samplePath !== null) {
        $path = $samplePath;
    }
    if ($path === null) {
        return configFields($sample);
    }

    return array_merge(configFields($sample), generatedFields($sample, $path));
}

/**
 * A field for every option the hand-written questions do not ask about.
 *
 * @return list<array>
 */
function generatedFields(array $sample, string $samplePath): array
{
    $notes = sampleHelp($samplePath);
    $covered = [];
    foreach (configFields($sample) as $field) {
        $covered[implode('.', $field['path'])] = true;
    }

    $fields = [];

    $walk = function (array $level, array $path) use (
        &$walk, &$fields, $covered, $notes
    ): void {
        foreach ($level as $key => $value) {
            $here = array_merge($path, [(string)$key]);
            $dotted = implode('.', $here);

            /*
             * A few settings are maps whose keys are chosen by the operator:
             * alert-rule overrides, scan schedules, image settings and AI
             * tools. Walking them works only after keys already exist, which
             * makes an empty sample impossible to configure. One validated
             * JSON editor keeps the complete map editable without pretending
             * that a comma-separated line can express nested permissions.
             */
            if (in_array($dotted, structuredConfigPaths(), true)) {
                if (!isset($covered[$dotted])) {
                    $fields[] = generatedStructuredField(
                        $dotted, $here, $value, $notes['help'][$dotted] ?? ''
                    );
                }
                continue;
            }

            /*
             * A map of settings is walked into; a list is one answer.
             *
             * `['a', 'b']` is a value somebody edits as a whole. `['enabled' =>
             * true]` is two questions. The difference is whether the keys are
             * 0,1,2 - which is exactly what array_is_list asks.
             */
            if (is_array($value) && $value !== [] && !array_is_list($value)) {
                $walk($value, $here);
                continue;
            }

            if (isset($covered[$dotted]) || in_array($dotted, hiddenConfigPaths(), true)) {
                continue;
            }

            $fields[] = generatedField($dotted, $here, $value, $notes['help'][$dotted] ?? '');
        }
    };

    $walk($sample, []);

    return $fields;
}

/**
 * The options the wizard must not offer as a text box.
 *
 * Not secrets - those are asked for properly elsewhere - but values where a
 * form field would be a worse way to set them than the thing that already sets
 * them, and offering both is how two sources of truth start.
 *
 * @return list<string>
 */
function hiddenConfigPaths(): array
{
    return [
        // Proved, hashed and written by the wizard itself.
        'password_hash',
        // Generated. Editing it renames every masked path on the dashboard,
        // and there is a checkbox that does that deliberately.
        'mask_secret',
        'auth.totp_secret',
        // The schedule editor validates and saves this structured value as one
        // operation, then applies its matching marked crontab block.
        'schedule.php',
        'schedule.vitals.profile',
        'schedule.vitals.custom.minute',
        'schedule.vitals.custom.hour',
        'schedule.vitals.custom.day',
        'schedule.vitals.custom.month',
        'schedule.vitals.custom.weekday',
        'schedule.vitals.offset.minute',
        'schedule.vitals.offset.hour',
        'schedule.scan.profile',
        'schedule.scan.custom.minute',
        'schedule.scan.custom.hour',
        'schedule.scan.custom.day',
        'schedule.scan.custom.month',
        'schedule.scan.custom.weekday',
        'schedule.scan.offset.minute',
        'schedule.scan.offset.hour',
    ];
}

/** Maps whose dynamic keys need a structured editor in setup.php. */
function structuredConfigPaths(): array
{
    return ['access.groups', 'access.users', 'alerts.rules', 'ai.images', 'ai.tools', 'ai.toolsets', 'openid.clients', 'peers', 'scans'];
}

/** One generated field, with its type guessed from what the sample holds. */
function generatedField(string $dotted, array $path, mixed $value, string $help): array
{
    $key = (string)end($path);
    $field = [
        'key' => 'cfg_' . str_replace('.', '__', $dotted),
        'path' => $path,
        'label' => generatedFieldLabel($path),
        'help' => $help,
        'section' => count($path) > 1 ? $path[0] : 'general',
        'generated' => true,
    ];

    if (is_bool($value)) {
        return $field + ['type' => 'bool', 'default' => $value];
    }

    if (is_array($value)) {
        return $field + ['type' => 'lines', 'default' => $value];
    }

    if ($value === null) {
        /*
         * Null means "work it out", and blank has to mean that too.
         *
         * Every one of these is a path or a database location that defaults to
         * somewhere sensible beside the others. A field that turned blank into
         * an empty string would break the very thing null is for.
         */
        return $field + ['type' => 'nullable', 'default' => '', 'optional' => true];
    }

    if (is_int($value)) {
        // The suffix says what the number means, and the sample is consistent
        // about it, so the field can accept "5G" or "6h" where those make sense.
        if (str_ends_with($key, '_bytes')) {
            return $field + ['type' => 'bytes', 'default' => $value];
        }
        if (str_ends_with($key, '_seconds') || $key === 'session_lifetime') {
            return $field + ['type' => 'seconds', 'default' => $value];
        }
        return $field + ['type' => 'int', 'default' => $value];
    }

    if (is_float($value)) {
        return $field + ['type' => 'number', 'default' => $value];
    }

    if (str_ends_with($key, '_private_key') || str_ends_with($key, '_certificate')) {
        return $field + ['type' => 'textarea', 'default' => (string)$value, 'optional' => true];
    }

    return $field + ['type' => 'text', 'default' => (string)$value, 'optional' => true];
}

/** A JSON field for a configuration map with operator-defined keys. */
function generatedStructuredField(string $dotted, array $path, mixed $value, string $help): array
{
    return [
        'key' => 'cfg_' . str_replace('.', '__', $dotted),
        'path' => $path,
        'label' => generatedFieldLabel($path),
        'help' => trim($help . ' Enter a JSON object; its keys and nested values are preserved exactly.'),
        'section' => count($path) > 1 ? $path[0] : 'general',
        'generated' => true,
        'type' => 'json',
        'default' => $value,
    ];
}

/**
 * The last-resort field generator must never leak an internal dotted config
 * path into Setup or the CLI. The surrounding panel supplies the broad
 * context; this produces a short operator-facing question for the setting.
 */
function generatedFieldLabel(array $path): string
{
    $leaf = (string)end($path);
    $words = preg_split('/[_-]+/', $leaf) ?: [];
    $acronyms = ['ai' => 'AI', 'api' => 'API', 'cpu' => 'CPU', 'dns' => 'DNS', 'id' => 'ID', 'ip' => 'IP', 'oidc' => 'OpenID Connect', 'ssl' => 'SSL', 'tls' => 'TLS', 'totp' => 'TOTP', 'ttl' => 'TTL', 'url' => 'URL'];
    $label = [];
    foreach ($words as $word) {
        $lower = strtolower($word);
        $label[] = $acronyms[$lower] ?? ucfirst($lower);
    }
    $text = implode(' ', $label);
    return $text !== '' ? $text : 'Additional setting';
}

/**
 * The account this process is running as, for the host database's owner.
 *
 * Detected rather than defaulted to a name in the sample file, because a
 * hardcoded username is right on exactly one machine and silently wrong
 * everywhere else - and being wrong here produces a database the dashboard
 * cannot read, with nothing to say why.
 */
function currentAccountName(): string
{
    if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
        $info = @posix_getpwuid(posix_geteuid());
        if (is_array($info) && !empty($info['name'])) {
            return (string)$info['name'];
        }
    }

    $out = @shell_exec('id -un 2>/dev/null');
    return is_string($out) ? trim($out) : '';
}

/** Whether a field applies, given the answers so far. */
function fieldApplies(array $field, array $values): bool
{
    if (!isset($field['when'])) return true;
    if (is_string($field['when'])) return !empty($values[$field['when']]);
    foreach ((array)$field['when'] as $key => $allowed) {
        if (!in_array($values[$key] ?? null, (array)$allowed, true)) return false;
    }
    return true;
}

/**
 * Turns one raw answer into the value that belongs in the config.
 *
 * Returns [value, error]. The error is what the caller shows; a field that
 * cannot be parsed keeps its default so the rest of the form still renders.
 */
function parseField(array $field, mixed $raw): array
{
    $type = $field['type'];

    if ($type === 'bool') {
        if (is_string($raw)) {
            return [(bool)preg_match('/^(y|yes|1|on|true)$/i', trim($raw)), null];
        }
        return [(bool)$raw, null];
    }

    /*
     * A list, one per line - or comma separated, because the command-line
     * wizard asks its questions on one line and cannot offer a textarea.
     * Accepting both means the same field works in both front ends.
     */
    if ($type === 'lines') {
        $text = is_array($raw) ? implode("\n", $raw) : (string)$raw;
        $parts = preg_split('/[\r\n,]+/', $text) ?: [];
        $clean = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $clean[] = $part;
            }
        }
        return [$clean, null];
    }

    if ($type === 'json') {
        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return [$field['default'], 'Enter a valid JSON object or array.'];
        }
        if (isset($field['validate'])) {
            $problem = ($field['validate'])($decoded);
            if ($problem !== null) return [$field['default'], $problem];
        }
        return [$decoded, null];
    }

    if ($type === 'categories') {
        $chosen = is_array($raw) ? $raw : [];
        $allowed = $field['options'];
        return [array_values(array_intersect($allowed, array_map('strval', $chosen))), null];
    }

    if ($type === 'choice') {
        $value = is_string($raw) ? trim($raw) : '';
        return array_key_exists($value, $field['options'])
            ? [$value, null]
            : [$field['default'], 'Choose one of the listed options.'];
    }

    if ($type === 'choices') {
        $chosen = is_array($raw) ? $raw : preg_split('/[\r\n,]+/', (string)$raw);
        return [array_values(array_intersect(array_keys($field['options']), array_map('strval', $chosen ?: []))), null];
    }

    $value = is_string($raw) ? trim($raw) : '';

    /*
     * Blank means null, and null means "work it out".
     *
     * These are the options that default to somewhere beside the other files -
     * a database path, the daemon list. Writing an empty string into one would
     * turn "decide for me" into "use the file called nothing".
     */
    if ($type === 'nullable') {
        return [$value === '' ? null : $value, null];
    }

    if ($value === '' && ($field['optional'] ?? false)) {
        return ['', null];
    }

    if (isset($field['validate'])) {
        $problem = ($field['validate'])($value);
        if ($problem !== null) {
            return [$field['default'], $problem];
        }
    }

    if ($type === 'bytes') {
        $parsed = parseBytes($value, true);
        return $parsed === null
            ? [$field['default'], 'Enter a size, like 5G or 500M or a plain number of bytes.']
            : [$parsed, null];
    }

    if ($type === 'seconds') {
        $parsed = parseDuration($value);
        return $parsed === null
            ? [$field['default'], 'Enter a time, like 6h or 90m or 2d. A plain number is seconds.']
            : [$parsed, null];
    }

    if ($type === 'number') {
        return [(float)$value, null];
    }

    // Whole numbers stay whole. A day count written as 14.0 in a config file
    // reads like a rounding error somebody should look into.
    if ($type === 'int') {
        return [(int)$value, null];
    }

    return [$value, null];
}

/**
 * Builds the finished config from the sample and the answers.
 *
 * Merged over the sample rather than written from scratch: everything nobody
 * was asked about - the category rules, the excluded paths, the thresholds that
 * are fine as they are - comes from the sample at whatever it says today.
 */
function buildConfig(
    array $sample,
    array $values,
    string $passwordHash,
    string $maskSecret,
    ?string $samplePath = null
): array
{
    $config = $sample;

    foreach (allConfigFields($sample, $samplePath) as $field) {
        if ($field['type'] === 'password' || !fieldApplies($field, $values)) {
            continue;
        }
        if (!array_key_exists($field['key'], $values)) {
            continue;
        }
        assignPath($config, $field['path'], $values[$field['key']]);
    }

    $config['password_hash'] = $passwordHash;
    $config['mask_secret'] = $maskSecret;

    return $config;
}

function assignPath(array &$target, array $path, mixed $value): void
{
    $cursor = &$target;
    foreach ($path as $step) {
        if (!is_array($cursor[$step] ?? null)) {
            $cursor[$step] ??= null;
        }
        $cursor = &$cursor[$step];
    }
    $cursor = $value;
}

/* ------------------------------------------------------------------ sizes -- */

/**
 * A size, accepting the way people actually write sizes.
 *
 * "5G" is what somebody types when asked for a threshold. Making them work out
 * 5368709120 is how a config ends up three orders of magnitude off with an
 * alert that never fires.
 */
function parseBytes(string $value, bool $units = true): ?int
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    if (ctype_digit($value)) {
        return (int)$value;
    }
    if (!$units || !preg_match('/^(\d+(?:\.\d+)?)\s*([kmgt])i?b?$/i', $value, $m)) {
        return null;
    }

    $scale = ['k' => 1024, 'm' => 1024 ** 2, 'g' => 1024 ** 3, 't' => 1024 ** 4];
    return (int)round((float)$m[1] * $scale[strtolower($m[2])]);
}

/**
 * A duration as something a person can read back.
 *
 * The field used to show 21600 and expect the reader to know that is six hours.
 * Nobody knows that, and a number you cannot check is a number you cannot
 * safely change - so it is shown as 6h and typed back the same way.
 *
 * Exact divisors only, matching formatBytes: 5400 seconds becomes 90m rather
 * than 1.5h, because a whole number in a smaller unit is easier to trust than a
 * fraction of a larger one.
 */
function formatDuration(int $seconds): string
{
    foreach ([['d', 86400], ['h', 3600], ['m', 60]] as [$suffix, $scale]) {
        if ($seconds >= $scale && $seconds % $scale === 0) {
            return ($seconds / $scale) . $suffix;
        }
    }
    return $seconds . 's';
}

/**
 * Reads a duration typed in any reasonable way.
 *
 * Bare digits stay seconds, so nothing that worked before stops working. The
 * spelled-out forms are here because somebody typing a timeout is thinking in
 * hours and will write "6 hours" as readily as "6h".
 */
function parseDuration(string $value): ?int
{
    $value = trim(strtolower($value));
    if ($value === '') {
        return null;
    }
    if (ctype_digit($value)) {
        return (int)$value;
    }

    if (!preg_match('/^(\d+(?:\.\d+)?)\s*([a-z]+)$/', $value, $m)) {
        return null;
    }

    $units = [
        86400 => ['d', 'day', 'days'],
        3600 => ['h', 'hr', 'hrs', 'hour', 'hours'],
        60 => ['m', 'min', 'mins', 'minute', 'minutes'],
        1 => ['s', 'sec', 'secs', 'second', 'seconds'],
    ];

    foreach ($units as $scale => $names) {
        if (in_array($m[2], $names, true)) {
            return (int)round((float)$m[1] * $scale);
        }
    }

    return null;
}

function formatBytes(int $bytes): string
{
    foreach ([['T', 1024 ** 4], ['G', 1024 ** 3], ['M', 1024 ** 2], ['K', 1024]] as [$suffix, $scale]) {
        if ($bytes >= $scale && $bytes % $scale === 0) {
            return ($bytes / $scale) . $suffix;
        }
    }
    return (string)$bytes;
}

/** How a field's current value should appear in a prompt or an input box. */
function displayValue(array $field, mixed $value): string
{
    $shown = match ($field['type']) {
        'bytes' => formatBytes((int)$value),
        'seconds' => formatDuration((int)$value),
        'bool' => $value ? 'y' : 'n',
        'categories' => implode(', ', (array)$value),
        'lines' => implode("\n", (array)$value),
        'json' => json_encode(setupDisplayValue($value), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}',
        // Blank, so that leaving it blank means what it already meant. Showing
        // the word "null" in a text box invites somebody to type it.
        'nullable' => $value === null ? '' : (string)$value,
        default => (string)$value,
    };
    return setupDisplayPath($shown);
}

/**
 * Removes lexical /./ and /../ segments for a path shown in Setup or CLI.
 * This does not call realpath(): resolving links here could change the path
 * an administrator intended to review or save.
 */
function setupDisplayPath(string $value): string
{
    if (str_contains($value, "\n")) {
        return implode("\n", array_map('setupDisplayPath', explode("\n", $value)));
    }
    if (!str_starts_with($value, '/')) return $value;
    $parts = [];
    foreach (explode('/', $value) as $part) {
        if ($part === '' || $part === '.') continue;
        if ($part === '..') { array_pop($parts); continue; }
        $parts[] = $part;
    }
    return '/' . implode('/', $parts);
}

/**
 * Canonicalizes filesystem paths nested inside a value displayed by Setup.
 * Configuration is not modified here: this is only for an unambiguous review
 * in a form or JSON editor.
 */
function setupDisplayValue(mixed $value): mixed
{
    if (is_string($value)) return setupDisplayPath($value);
    if (!is_array($value)) return $value;
    foreach ($value as $key => $item) $value[$key] = setupDisplayValue($item);
    return $value;
}

/* --------------------------------------------------------------- printing -- */

/**
 * The finished config, as PHP source.
 *
 * `var_export` would do the job but produces `array (` and one entry per line
 * with no shape to it, and this is a file somebody has to read and edit later.
 * The header points at the sample, which is where the explanation of every
 * option lives - duplicating those comments here is how the two drift apart.
 */
function renderConfig(array $config, string $libDir): string
{
    $when = date('Y-m-d H:i');
    $lines = [
        '<?php',
        'declare(strict_types=1);',
        '',
        '/*',
        " * Zogograph configuration, written by setup.php on $when.",
        ' *',
        ' * Every option is described in lib/config.sample.php. This file holds the',
        ' * password hash and the mask secret, so it is gitignored and lib/.htaccess',
        ' * denies the directory - keep both of those true.',
        ' *',
        ' * Changing mask_secret renames every masked path on the dashboard. It is',
        ' * safe to do, but the pseudonyms people have got used to will all change.',
        ' */',
        '',
        'return ' . renderValue($config, 0, $libDir) . ';',
        '',
    ];

    return implode("\n", $lines);
}

function renderValue(mixed $value, int $depth, string $libDir = ''): string
{
    $pad = str_repeat('    ', $depth + 1);
    $close = str_repeat('    ', $depth);

    if (is_array($value)) {
        if ($value === []) {
            return '[]';
        }

        $isList = array_is_list($value);
        $parts = [];
        foreach ($value as $key => $item) {
            $rendered = is_string($item)
                ? relativeToLib($item, $libDir)
                : renderValue($item, $depth + 1, $libDir);
            $parts[] = $isList
                ? $pad . $rendered . ','
                : $pad . renderScalar($key) . ' => ' . $rendered . ',';
        }

        return "[\n" . implode("\n", $parts) . "\n" . $close . ']';
    }

    return renderScalar($value);
}

/**
 * Paths under lib/ are written back as __DIR__ expressions.
 *
 * The sample computes the database path and the exclusions from __DIR__.
 * Exporting the result would freeze whatever absolute path the wizard happened
 * to run from, so a config generated on a laptop and pasted onto the server
 * would point at a directory that is not there - and the exclusion that stops
 * the scanner reporting its own database would quietly stop matching.
 */
function relativeToLib(string $value, string $libDir): string
{
    // A legacy value that walks back out of lib starts with lib/ even though
    // it is not actually inside lib. Normalize it before deciding
    // whether it is safe to render as a __DIR__ expression; otherwise every
    // Setup save preserves the confusing lib/../ spelling.
    $displayPath = setupDisplayPath($value);
    if ($libDir !== '' && str_starts_with($displayPath, $libDir . '/')) {
        return '__DIR__ . ' . renderScalar(substr($displayPath, strlen($libDir)));
    }
    return renderScalar($displayPath);
}

function renderScalar(mixed $value): string
{
    if ($value === null) {
        return 'null';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_int($value)) {
        return (string)$value;
    }
    if (is_float($value)) {
        // A float that prints as "5" reads back as an int, which then fails a
        // strict comparison somewhere far from here.
        $printed = (string)$value;
        return str_contains($printed, '.') ? $printed : $printed . '.0';
    }

    return "'" . strtr((string)$value, ["\\" => '\\\\', "'" => "\\'"]) . "'";
}
