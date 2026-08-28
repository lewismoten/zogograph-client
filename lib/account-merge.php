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
