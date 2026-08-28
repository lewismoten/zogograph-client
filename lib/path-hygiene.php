<?php
declare(strict_types=1);

/* An opt-in, core-owned marketplace capability. It never executes package
 * files: the package only grants this fixed scanner during an existing walk. */

const PATH_HYGIENE_PACKAGE = 'zogograph-path-hygiene';
const PATH_HYGIENE_LIMIT = 50;

function pathHygieneEnabled(array $config, PDO $pdo): bool
{
    if (!function_exists('marketplacePackages')) return false;
    foreach (marketplacePackages($config, $pdo) as $package) {
        if ($package['id'] === PATH_HYGIENE_PACKAGE && $package['enabled']
            && in_array('path_hygiene', $package['capabilities'], true)) return true;
    }
    return false;
}

/** @return array{enabled:bool,findings:list<array>,total:int,truncated:bool} */
function pathHygieneStart(bool $enabled): array
{
    return ['enabled' => $enabled, 'findings' => [], 'total' => 0, 'truncated' => false];
}

/** Records only names that routinely confuse terminals, scripts, or copy/paste. */
function pathHygieneObserve(string $path, string $name, array &$state): void
{
    if (!$state['enabled']) return;
    $reason = pathHygieneReason($name);
    if ($reason === null) return;

    $state['total']++;
    if (count($state['findings']) >= PATH_HYGIENE_LIMIT) {
        $state['truncated'] = true;
        return;
    }
    $state['findings'][] = ['path' => $path, 'reason' => $reason];
}

function pathHygieneReason(string $name): ?string
{
    if (preg_match('/[\x00-\x1f\x7f]/', $name)) return 'contains a control character';
    if (preg_match('/^\s|\s$/u', $name)) return 'starts or ends with whitespace';
    if (str_starts_with($name, '-')) return 'starts with a dash and can be treated as a command option';
    if (strpbrk($name, '?*[]{}~$&;|<>()`\'"!' . '\\') !== false) return 'contains a shell or wildcard character';
    return null;
}

function pathHygieneStore(PDO $pdo, int $scanId, array $state): void
{
    if (!$state['enabled']) return;
    $pdo->prepare('INSERT INTO path_hygiene_scans (scan_id, checked_at, findings_json, findings_total, truncated)
        VALUES (?, ?, ?, ?, ?)')->execute([
        $scanId, time(), json_encode($state['findings'], JSON_UNESCAPED_SLASHES),
        $state['total'], $state['truncated'] ? 1 : 0,
    ]);
}

/** @return ?array{checked_at:int,findings:list<array>,total:int,truncated:bool} */
function pathHygieneLast(PDO $pdo): ?array
{
    try { $row = $pdo->query('SELECT checked_at, findings_json, findings_total, truncated FROM path_hygiene_scans ORDER BY scan_id DESC LIMIT 1')->fetch(); }
    catch (Throwable) { return null; }
    if (!is_array($row)) return null;
    $findings = json_decode((string)$row['findings_json'], true);
    return ['checked_at' => (int)$row['checked_at'], 'findings' => is_array($findings) ? $findings : [],
        'total' => (int)$row['findings_total'], 'truncated' => (bool)$row['truncated']];
}

function pathHygieneRename(array $config, PDO $pdo, string $path, string $name): array
{
    $last = pathHygieneLast($pdo);
    $known = array_column($last['findings'] ?? [], 'path');
    if (!in_array($path, $known, true)) throw new ActionRefused('that path is not in the latest hygiene scan');
    if ($name === '' || $name === '.' || $name === '..' || str_contains($name, '/') || str_contains($name, "\0")) throw new ActionRefused('enter one valid file or directory name');
    if (pathHygieneReason($name) !== null) throw new ActionRefused('the replacement name is still shell-confusing');
    $root = realpath((string)$config['scan_root']);
    $real = realpath($path);
    if ($root === false || $real === false || ($real !== $root && !str_starts_with($real . '/', rtrim($root, '/') . '/'))) throw new ActionRefused('path is no longer inside the scan root');
    if (is_link($path)) throw new ActionRefused('symlinks cannot be renamed here');
    $destination = dirname($path) . '/' . $name;
    if (file_exists($destination) || is_link($destination)) throw new ActionRefused('a path with that name already exists');
    if (!rename($path, $destination)) throw new ActionRefused('the filesystem refused the rename');

    $findings = array_values(array_filter($last['findings'], fn(array $row): bool => ($row['path'] ?? '') !== $path));
    $pdo->prepare('UPDATE path_hygiene_scans SET findings_json = ?, findings_total = ? WHERE scan_id = (SELECT MAX(scan_id) FROM path_hygiene_scans)')
        ->execute([json_encode($findings, JSON_UNESCAPED_SLASHES), max(0, $last['total'] - 1)]);
    return ['from' => $path, 'to' => $destination];
}
