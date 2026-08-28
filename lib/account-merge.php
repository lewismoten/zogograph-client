<?php
declare(strict_types=1);

/* Read-only compatibility evidence for a future account-store merge. A merge
 * must never begin by attaching an arbitrary SQLite file to the live store. */

/** @return array{eligible:bool,reason:?string,source_version:?int,current_version:int,tables:list<string>,merge_tables:list<string>} */
function accountMergeInspect(string $sourcePath): array
{
    $currentVersion = max(array_keys(migrations()));
    $empty = ['eligible' => false, 'reason' => null, 'source_version' => null, 'current_version' => $currentVersion, 'tables' => [], 'merge_tables' => []];
    if ($sourcePath === '' || !str_starts_with($sourcePath, '/') || is_link($sourcePath) || !is_file($sourcePath) || !is_readable($sourcePath)) {
        return array_replace($empty, ['reason' => 'source must be a readable, non-symlinked absolute SQLite file']);
    }
    try {
        $source = new PDO('sqlite:' . $sourcePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $source->exec('PRAGMA query_only = ON');
        if ((string)$source->query('PRAGMA quick_check(1)')->fetchColumn() !== 'ok') {
            return array_replace($empty, ['reason' => 'source SQLite integrity check failed']);
        }
        $names = $source->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        $tables = array_values(array_filter($names, 'is_string'));
        if (!in_array('schema_version', $tables, true)) return array_replace($empty, ['reason' => 'source is not a Zogograph account store', 'tables' => $tables]);
        $version = (int)$source->query('SELECT COALESCE(MAX(version), 0) FROM schema_version')->fetchColumn();
        if ($version > $currentVersion) return array_replace($empty, ['reason' => 'source was created by a newer Zogograph version', 'source_version' => $version, 'tables' => $tables]);
        $required = ['paths', 'vitals', 'scans', 'scan_totals', 'path_sizes'];
        $mergeTables = array_values(array_intersect($required, $tables));
        if ($mergeTables !== $required) return array_replace($empty, ['reason' => 'source lacks the required collector tables', 'source_version' => $version, 'tables' => $tables, 'merge_tables' => $mergeTables]);
        return ['eligible' => true, 'reason' => null, 'source_version' => $version, 'current_version' => $currentVersion, 'tables' => $tables, 'merge_tables' => $mergeTables];
    } catch (Throwable) {
        return array_replace($empty, ['reason' => 'source could not be opened as SQLite']);
    }
}

/** @return array{first:?int,last:?int,rows:int} */
function accountMergeTimeRange(PDO $pdo, string $table, string $column): array
{
    $row = $pdo->query("SELECT COUNT(*) AS rows, MIN($column) AS first_at, MAX($column) AS last_at FROM $table")->fetch();
    return ['rows' => (int)($row['rows'] ?? 0), 'first' => isset($row['first_at']) ? (int)$row['first_at'] : null, 'last' => isset($row['last_at']) ? (int)$row['last_at'] : null];
}

/** @param array{first:?int,last:?int,rows:int} $left @param array{first:?int,last:?int,rows:int} $right */
function accountMergeRangesOverlap(array $left, array $right): bool
{
    return $left['rows'] > 0 && $right['rows'] > 0 && $left['first'] !== null && $left['last'] !== null
        && $right['first'] !== null && $right['last'] !== null && $left['first'] <= $right['last'] && $right['first'] <= $left['last'];
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
    if (!$inspection['eligible']) return $inspection;
    try {
        $source = new PDO('sqlite:' . $sourcePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $source->exec('PRAGMA query_only = ON');
        foreach (['vitals' => 'taken_at', 'scans' => 'started_at'] as $table => $column) {
            $from = accountMergeTimeRange($source, $table, $column);
            $into = accountMergeTimeRange($target, $table, $column);
            $inspection['collector'][$table] = ['source' => $from, 'target' => $into, 'overlap' => accountMergeRangesOverlap($from, $into)];
        }
        return $inspection;
    } catch (Throwable) {
        return array_replace($inspection, ['eligible' => false, 'reason' => 'could not read source collector ranges', 'collector' => []]);
    }
}
