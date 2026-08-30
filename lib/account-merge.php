<?php
declare(strict_types=1);

/* Read-only compatibility evidence for a future account-store merge. A merge
 * must never begin by attaching an arbitrary SQLite file to the live store. */

/** @return array{eligible:bool,reason:?string,source_version:?int,current_version:int,tables:list<string>,merge_tables:list<string>} */
function accountMergeInspect(string $sourcePath): array
{
    $currentVersion = max(array_keys(migrations()));
    $empty = [
        'eligible' => false,
        'reason' => null,
        'source_version' => null,
        'current_version' => $currentVersion,
        'tables' => [],
        'merge_tables' => [],
    ];
    if (
        $sourcePath === '' ||
        !str_starts_with($sourcePath, '/') ||
        is_link($sourcePath) ||
        !is_file($sourcePath) ||
        !is_readable($sourcePath)
    ) {
        return array_replace($empty, [
            'reason' =>
                'source must be a readable, non-symlinked absolute SQLite file',
        ]);
    }
    try {
        // VACUUM INTO is the sole statement on this connection. SQLite treats
        // it as a write for query_only purposes even though it never changes
        // the source database, so do not enable query_only here.
        $source = new PDO('sqlite:' . $sourcePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        if (
            (string) $source->query('PRAGMA quick_check(1)')->fetchColumn() !==
            'ok'
        ) {
            return array_replace($empty, [
                'reason' => 'source SQLite integrity check failed',
            ]);
        }
        $names = $source
            ->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name",
            )
            ->fetchAll(PDO::FETCH_COLUMN);
        $tables = array_values(array_filter($names, 'is_string'));
        if (!in_array('schema_version', $tables, true)) {
            return array_replace($empty, [
                'reason' => 'source is not a Zogograph account store',
                'tables' => $tables,
            ]);
        }
        $version = (int) $source
            ->query('SELECT COALESCE(MAX(version), 0) FROM schema_version')
            ->fetchColumn();
        if ($version > $currentVersion) {
            return array_replace($empty, [
                'reason' => 'source was created by a newer Zogograph version',
                'source_version' => $version,
                'tables' => $tables,
            ]);
        }
        $required = ['paths', 'vitals', 'scans', 'scan_totals', 'path_sizes'];
        $mergeTables = array_values(array_intersect($required, $tables));
        if ($mergeTables !== $required) {
            return array_replace($empty, [
                'reason' => 'source lacks the required collector tables',
                'source_version' => $version,
                'tables' => $tables,
                'merge_tables' => $mergeTables,
            ]);
        }
        return [
            'eligible' => true,
            'reason' => null,
            'source_version' => $version,
            'current_version' => $currentVersion,
            'tables' => $tables,
            'merge_tables' => $mergeTables,
        ];
    } catch (Throwable) {
        return array_replace($empty, [
            'reason' => 'source could not be opened as SQLite',
        ]);
    }
}

/** @return array{first:?int,last:?int,rows:int} */
function accountMergeTimeRange(PDO $pdo, string $table, string $column): array
{
    $row = $pdo
        ->query(
            "SELECT COUNT(*) AS rows, MIN($column) AS first_at, MAX($column) AS last_at FROM $table",
        )
        ->fetch();
    return [
        'rows' => (int) ($row['rows'] ?? 0),
        'first' => isset($row['first_at']) ? (int) $row['first_at'] : null,
        'last' => isset($row['last_at']) ? (int) $row['last_at'] : null,
    ];
}

/** @param array{first:?int,last:?int,rows:int} $left @param array{first:?int,last:?int,rows:int} $right */
function accountMergeRangesOverlap(array $left, array $right): bool
{
    return $left['rows'] > 0 &&
        $right['rows'] > 0 &&
        $left['first'] !== null &&
        $left['last'] !== null &&
        $right['first'] !== null &&
        $right['last'] !== null &&
        $left['first'] <= $right['last'] &&
        $right['first'] <= $left['last'];
}

/**
 * Produces evidence for an administrator-facing merge review without attaching
 * the source to the live store or changing either database.
 *
 * @return array{eligible:bool,reason:?string,source_version:?int,current_version:int,tables:list<string>,merge_tables:list<string>,collector:array<string,array{source:array{first:?int,last:?int,rows:int},target:array{first:?int,last:?int,rows:int},overlap:bool}>}
 */
function accountMergePlan(PDO $target, string $sourcePath): array
{
    $inspection = accountMergeInspect($sourcePath);
    $inspection['collector'] = [];
    if (!$inspection['eligible']) {
        return $inspection;
    }
    try {
        $source = new PDO('sqlite:' . $sourcePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $source->exec('PRAGMA query_only = ON');
        foreach (
            ['vitals' => 'taken_at', 'scans' => 'started_at']
            as $table => $column
        ) {
            $from = accountMergeTimeRange($source, $table, $column);
            $into = accountMergeTimeRange($target, $table, $column);
            $inspection['collector'][$table] = [
                'source' => $from,
                'target' => $into,
                'overlap' => accountMergeRangesOverlap($from, $into),
            ];
        }
        return $inspection;
    } catch (Throwable) {
        return array_replace($inspection, [
            'eligible' => false,
            'reason' => 'could not read source collector ranges',
            'collector' => [],
        ]);
    }
}

/** Returns the backing file for a live SQLite connection, never a memory database. */
function accountMergeDatabasePath(PDO $pdo): string
{
    foreach ($pdo->query('PRAGMA database_list')->fetchAll() as $database) {
        if (
            ($database['name'] ?? '') === 'main' &&
            is_string($database['file'] ?? null) &&
            str_starts_with($database['file'], '/')
        ) {
            return $database['file'];
        }
    }
    throw new RuntimeException(
        'collector merge requires a file-backed account database',
    );
}

/** Creates a consistent private source clone, then upgrades only that clone. */
function accountMergePreparedSource(
    string $sourcePath,
    string $directory,
): string {
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
        throw new RuntimeException('could not create private merge workspace');
    }
    @chmod($directory, 0700);
    $prepared = $directory . '/source-' . bin2hex(random_bytes(8)) . '.db';
    try {
        // VACUUM INTO is the only statement on this connection. SQLite treats
        // it as a write for query_only purposes even though it does not modify
        // the source database, so query_only cannot be enabled here.
        $source = new PDO('sqlite:' . $sourcePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $source->exec('VACUUM INTO ' . $source->quote($prepared));
        @chmod($prepared, 0600);
        $copy = new PDO('sqlite:' . $prepared, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $copy->exec('PRAGMA foreign_keys = ON');
        migrate($copy);
        return $prepared;
    } catch (Throwable $error) {
        @unlink($prepared);
        throw new RuntimeException(
            'could not prepare source database for merge',
            0,
            $error,
        );
    }
}

/** @return list<string> columns shared by the migrated source and destination, excluding row identity. */
function accountMergeColumns(PDO $pdo, string $table): array
{
    $columns = $pdo
        ->query("SELECT name FROM pragma_table_info('$table') ORDER BY cid")
        ->fetchAll(PDO::FETCH_COLUMN);
    return array_values(
        array_filter(
            $columns,
            static fn(mixed $column): bool => is_string($column) &&
                $column !== 'id' &&
                preg_match('/^[a-z][a-z0-9_]*$/', $column),
        ),
    );
}

/**
 * Applies only collector data after a private target snapshot. The caller must
 * explicitly accept time-range overlap; identity, credentials, alerts, actions,
 * packages, and settings are never copied.
 *
 * @return array{snapshot:string,paths:int,vitals:int,scans:int,scan_totals:int,path_sizes:int}
 */
function accountMergeApply(
    PDO $target,
    string $sourcePath,
    bool $allowOverlap = false,
): array {
    $plan = accountMergePlan($target, $sourcePath);
    if (!$plan['eligible']) {
        throw new RuntimeException((string) $plan['reason']);
    }
    if (
        !$allowOverlap &&
        ($plan['collector']['vitals']['overlap'] ||
            $plan['collector']['scans']['overlap'])
    ) {
        throw new RuntimeException(
            'collector time ranges overlap; review the plan and explicitly allow overlap to merge',
        );
    }
    $targetPath = accountMergeDatabasePath($target);
    $workspace = dirname($targetPath) . '/merge-workspace';
    $snapshots = dirname($targetPath) . '/merge-backups';
    if (!is_dir($snapshots) && !mkdir($snapshots, 0700, true)) {
        throw new RuntimeException(
            'could not create private merge backup directory',
        );
    }
    @chmod($snapshots, 0700);
    $snapshot =
        $snapshots .
        '/before-collector-merge-' .
        gmdate('Ymd\\THis\\Z') .
        '-' .
        bin2hex(random_bytes(4)) .
        '.db';
    $target->exec('VACUUM INTO ' . $target->quote($snapshot));
    @chmod($snapshot, 0600);
    $prepared = accountMergePreparedSource($sourcePath, $workspace);
    $attached = false;
    try {
        $target->exec(
            'ATTACH DATABASE ' . $target->quote($prepared) . ' AS merge_source',
        );
        $attached = true;
        $target->beginTransaction();
        $target->exec(
            'UPDATE paths SET first_seen = MIN(first_seen, (SELECT MIN(source.first_seen) FROM merge_source.paths source WHERE source.path = paths.path)), last_seen = MAX(last_seen, (SELECT MAX(source.last_seen) FROM merge_source.paths source WHERE source.path = paths.path)) WHERE EXISTS (SELECT 1 FROM merge_source.paths source WHERE source.path = paths.path)',
        );
        $target->exec(
            'INSERT OR IGNORE INTO paths (path, kind, category, first_seen, last_seen) SELECT path, kind, category, first_seen, last_seen FROM merge_source.paths',
        );
        $paths = $target->query('SELECT changes()')->fetchColumn();
        $vitalColumns = accountMergeColumns($target, 'vitals');
        $vitalList = implode(', ', $vitalColumns);
        $target->exec(
            "INSERT INTO vitals ($vitalList) SELECT $vitalList FROM merge_source.vitals source WHERE source.id = (SELECT MIN(same.id) FROM merge_source.vitals same WHERE same.taken_at = source.taken_at) AND NOT EXISTS (SELECT 1 FROM vitals existing WHERE existing.taken_at = source.taken_at)",
        );
        $vitals = $target->query('SELECT changes()')->fetchColumn();
        $target->exec(
            'INSERT INTO scans (started_at, finished_at, duration_ms, files_seen, dirs_seen, bytes_seen, error) SELECT started_at, finished_at, duration_ms, files_seen, dirs_seen, bytes_seen, error FROM merge_source.scans source WHERE source.id = (SELECT MIN(same.id) FROM merge_source.scans same WHERE same.started_at = source.started_at) AND NOT EXISTS (SELECT 1 FROM scans existing WHERE existing.started_at = source.started_at)',
        );
        $scans = $target->query('SELECT changes()')->fetchColumn();
        $target->exec(
            'CREATE TEMP TABLE account_merge_scan_map AS SELECT source.id AS source_id, (SELECT MIN(target.id) FROM scans target WHERE target.started_at = source.started_at) AS target_id FROM merge_source.scans source',
        );
        $target->exec(
            'CREATE TEMP TABLE account_merge_path_map AS SELECT source.id AS source_id, (SELECT MIN(target.id) FROM paths target WHERE target.path = source.path) AS target_id FROM merge_source.paths source',
        );
        $target->exec(
            'INSERT OR IGNORE INTO scan_totals (scan_id, category, bytes, files) SELECT mapped.target_id, totals.category, totals.bytes, totals.files FROM merge_source.scan_totals totals JOIN account_merge_scan_map mapped ON mapped.source_id = totals.scan_id WHERE mapped.target_id IS NOT NULL',
        );
        $scanTotals = $target->query('SELECT changes()')->fetchColumn();
        $target->exec(
            'INSERT OR IGNORE INTO path_sizes (scan_id, path_id, bytes, files) SELECT scans.target_id, paths.target_id, sizes.bytes, sizes.files FROM merge_source.path_sizes sizes JOIN account_merge_scan_map scans ON scans.source_id = sizes.scan_id JOIN account_merge_path_map paths ON paths.source_id = sizes.path_id WHERE scans.target_id IS NOT NULL AND paths.target_id IS NOT NULL',
        );
        $pathSizes = $target->query('SELECT changes()')->fetchColumn();
        $target->exec('DROP TABLE account_merge_scan_map');
        $target->exec('DROP TABLE account_merge_path_map');
        $target->commit();
        return [
            'snapshot' => $snapshot,
            'paths' => (int) $paths,
            'vitals' => (int) $vitals,
            'scans' => (int) $scans,
            'scan_totals' => (int) $scanTotals,
            'path_sizes' => (int) $pathSizes,
        ];
    } catch (Throwable $error) {
        if ($target->inTransaction()) {
            $target->rollBack();
        }
        throw $error;
    } finally {
        if ($attached) {
            $target->exec('DETACH DATABASE merge_source');
        }
        @unlink($prepared);
    }
}
