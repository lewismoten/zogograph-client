<?php
declare(strict_types=1);

/* Verifies the database primitive shared by account, host, and assistant DBs. */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/storekeep.php';
require_once __DIR__ . '/../lib/hostdb.php';
require_once __DIR__ . '/../lib/aidb.php';

function migrationTestExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$directory =
    sys_get_temp_dir() . '/zogograph-migration-' . bin2hex(random_bytes(8));
migrationTestExpect(
    mkdir($directory, 0700),
    'could not create a temporary test directory',
);
$database = $directory . '/account.db';

try {
    $pdo = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('CREATE TABLE evidence (value TEXT NOT NULL)');
    $pdo->exec("INSERT INTO evidence (value) VALUES ('before migration')");

    $integrity = databaseFullIntegrity($pdo);
    migrationTestExpect(
        $integrity['ok'],
        'full SQLite integrity check did not pass on a valid database',
    );
    $retention = retentionRules([]);
    migrationTestExpect(
        $retention['remote_vital_days'] === 400 &&
            $retention['remote_receipt_days'] === 2,
        'remote transport retention defaults are missing',
    );

    $snapshot = migrationBackup($pdo, 12, 13);
    migrationTestExpect(
        is_file($snapshot),
        'migration snapshot was not created',
    );
    migrationTestExpect(
        (fileperms($snapshot) & 0077) === 0,
        'migration snapshot is not private',
    );

    $copy = new PDO('sqlite:' . $snapshot, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    migrationTestExpect(
        $copy->query('SELECT value FROM evidence')->fetchColumn() ===
            'before migration',
        'migration snapshot did not preserve source data',
    );
    $copy = null;
    $pdo = null;

    /* A real pre-feature alert table: the current migration must add its
     * delivery evidence and mute ledger without requiring a fresh install. */
    $upgrade = $directory . '/upgrade.db';
    $legacy = new PDO('sqlite:' . $upgrade, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $legacy->exec('CREATE TABLE schema_version (version INTEGER NOT NULL)');
    $legacy->exec('INSERT INTO schema_version VALUES (44)');
    $legacy->exec('CREATE TABLE alerts (id INTEGER PRIMARY KEY, raised_at INTEGER NOT NULL, rule TEXT NOT NULL,
        severity TEXT NOT NULL, subject TEXT NOT NULL, detail TEXT NOT NULL)');
    $legacy->exec('CREATE TABLE actions (id INTEGER PRIMARY KEY, at INTEGER NOT NULL, action TEXT NOT NULL,
        target TEXT, outcome TEXT NOT NULL, bytes_freed INTEGER NOT NULL DEFAULT 0,
        files_removed INTEGER NOT NULL DEFAULT 0, detail TEXT, remote_addr TEXT)');
    migrate($legacy);
    $columns = $legacy
        ->query('SELECT name FROM pragma_table_info(\'alerts\')')
        ->fetchAll(PDO::FETCH_COLUMN);
    migrationTestExpect(
        in_array('notification_disposition', $columns, true) &&
            in_array('notification_reason', $columns, true) &&
            in_array('notification_muted_until', $columns, true),
        'alert lifecycle migration did not upgrade the existing alerts table',
    );
    migrationTestExpect(
        (int) $legacy
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'alert_mutes'",
            )
            ->fetchColumn() === 1,
        'alert lifecycle migration did not create the mute ledger',
    );
    $legacy = null;

    /* An interrupted older deployment may have created a table or index before
     * losing its schema-version write. Retrying must repair rather than wedge. */
    $interrupted = $directory . '/interrupted.db';
    $partial = new PDO('sqlite:' . $interrupted, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $partial->exec('CREATE TABLE schema_version (version INTEGER NOT NULL)');
    $partial->exec('INSERT INTO schema_version VALUES (44)');
    $partial->exec('CREATE TABLE alerts (id INTEGER PRIMARY KEY, raised_at INTEGER NOT NULL, rule TEXT NOT NULL,
        severity TEXT NOT NULL, subject TEXT NOT NULL, detail TEXT NOT NULL,
        notification_disposition TEXT NOT NULL DEFAULT \'immediate\', notification_reason TEXT, notification_muted_until INTEGER)');
    $partial->exec('CREATE TABLE actions (id INTEGER PRIMARY KEY, at INTEGER NOT NULL, action TEXT NOT NULL,
        target TEXT, outcome TEXT NOT NULL, bytes_freed INTEGER NOT NULL DEFAULT 0,
        files_removed INTEGER NOT NULL DEFAULT 0, detail TEXT, remote_addr TEXT, actor TEXT)');
    $partial->exec('CREATE TABLE alert_mutes (id INTEGER PRIMARY KEY, rule TEXT NOT NULL, created_at INTEGER NOT NULL,
        expires_at INTEGER NOT NULL, reason TEXT NOT NULL, created_by TEXT)');
    $partial->exec(
        'CREATE INDEX alert_mutes_rule_expiry ON alert_mutes (rule, expires_at DESC)',
    );
    $partial->exec('CREATE INDEX actions_actor_at ON actions (actor, at)');
    migrate($partial);
    migrationTestExpect(
        (int) $partial
            ->query('SELECT MAX(version) FROM schema_version')
            ->fetchColumn() === 57,
        'interrupted migration did not recover to the current schema version',
    );
    migrationTestExpect(
        (int) $partial
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'encryption_artifacts'",
            )
            ->fetchColumn() === 1 &&
            (int) $partial
                ->query(
                    "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'encryption_replicas'",
                )
                ->fetchColumn() === 1 &&
            (int) $partial
                ->query(
                    "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'encryption_remote_envelopes'",
                )
                ->fetchColumn() === 1,
        'encrypted artifact ledger migration did not complete',
    );
    migrationTestExpect(
        in_array(
            'last_verified_at',
            $partial
                ->query(
                    "SELECT name FROM pragma_table_info('encryption_artifacts')",
                )
                ->fetchAll(PDO::FETCH_COLUMN),
            true,
        ),
        'encrypted artifact verification timestamp migration did not complete',
    );
    migrationTestExpect(
        in_array(
            'last_recovery_drill_at',
            $partial
                ->query(
                    "SELECT name FROM pragma_table_info('encryption_artifacts')",
                )
                ->fetchAll(PDO::FETCH_COLUMN),
            true,
        ),
        'encrypted artifact recovery-drill timestamp migration did not complete',
    );
    migrationTestExpect(
        (int) $partial
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'runtime_packages'",
            )
            ->fetchColumn() === 1 &&
            (int) $partial
                ->query(
                    "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'runtime_package_grants'",
                )
                ->fetchColumn() === 1,
        'package runtime registry migration did not complete',
    );
    migrationTestExpect(
        (int) $partial
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'runtime_package_revocations'",
            )
            ->fetchColumn() === 1,
        'package runtime revocation migration did not complete',
    );
    migrationTestExpect(
        in_array(
            'manifest_sha256',
            $partial
                ->query(
                    "SELECT name FROM pragma_table_info('runtime_packages')",
                )
                ->fetchAll(PDO::FETCH_COLUMN),
            true,
        ),
        'package manifest fingerprint migration did not complete',
    );
    migrationTestExpect(
        (int) $partial
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'path_hygiene_scans'",
            )
            ->fetchColumn() === 1,
        'path hygiene scan ledger migration did not complete',
    );
    $partial = null;

    /* Production-like fixtures are intentionally ignored by Git. When the
     * operator supplies them in scratch/, prove each can be copied, opened,
     * and migrated without changing the original. This gives real schema
     * evidence locally while keeping CI and release archives data-free. */
    $fixtureRoot = realpath(__DIR__ . '/../scratch');
    $fixtureMigrations = [
        'old-stats.db' => static fn(PDO $copy) => migrate($copy),
        'old-host.db' => static fn(PDO $copy) => hostMigrate($copy),
        'old-ai.db' => static fn(PDO $copy) => aiMigrate($copy),
        // setup.db has no versioned migration ledger; its integrity and safe
        // clone are still meaningful compatibility evidence.
        'old-setup.db' => static fn(PDO $copy) => null,
    ];
    if ($fixtureRoot !== false) {
        foreach ($fixtureMigrations as $name => $upgradeFixture) {
            $fixture = $fixtureRoot . '/' . $name;
            if (!is_file($fixture) || is_link($fixture)) {
                continue;
            }
            $before = hash_file('sha256', $fixture);
            migrationTestExpect(
                is_string($before),
                "could not fingerprint supplied historical fixture $name",
            );
            $source = new PDO('sqlite:' . $fixture, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            migrationTestExpect(
                (string) $source
                    ->query('PRAGMA quick_check(1)')
                    ->fetchColumn() === 'ok',
                "supplied historical fixture $name failed SQLite integrity",
            );
            $clone = $directory . '/provided-' . $name;
            $source->exec('VACUUM INTO ' . $source->quote($clone));
            $source = null;
            $copy = new PDO('sqlite:' . $clone, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $upgradeFixture($copy);
            migrationTestExpect(
                (string) $copy
                    ->query('PRAGMA quick_check(1)')
                    ->fetchColumn() === 'ok',
                "migration damaged cloned historical fixture $name",
            );
            $copy = null;
            migrationTestExpect(
                hash_file('sha256', $fixture) === $before,
                "migration changed supplied historical fixture $name",
            );
            unlink($clone);
        };
    }

    unlink($snapshot);
    unlink($database);
    foreach (glob($directory . '/migration-backups/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($directory . '/migration-backups');
    unlink($upgrade);
    unlink($interrupted);
    rmdir($directory);
    echo "migration snapshot checks passed\n";
} catch (Throwable $e) {
    foreach (glob($directory . '/migration-backups/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($directory . '/migration-backups');
    @unlink($database);
    @unlink($directory . '/upgrade.db');
    @unlink($directory . '/interrupted.db');
    @rmdir($directory);
    throw $e;
}
