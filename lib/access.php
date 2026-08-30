<?php
declare(strict_types=1);

/*
 * The access policy lives in config.php so it can be reviewed and backed up
 * with the rest of the installation. A database table would make changing the
 * one account that can repair a broken installation depend on that very
 * installation being healthy.
 */

function accessDefaults(): array
{
    $groups = [
        'Public' => [
            'pages' => ['overview', 'charts', 'alerts', 'uptime', 'calendar'],
            'charts' => ['*'],
            'tools' => [],
            'notes' => false,
            'live' => false,
            'ai' => false,
            'ips' => false,
            'ranges' => [600, 3600, 21600, 86400, 604800, 2592000, 31536000],
            'write' => false,
        ],
        'Staff' => [
            'pages' => [
                'overview',
                'charts',
                'alerts',
                'uptime',
                'calendar',
                'storage',
                'storage-files',
                'cpu',
                'cpu-time',
                'cpu-cores',
                'cpu-procs',
                'cpu-queue',
                'host',
                'mem-users',
                'mem-goes',
                'mem-services',
                'mem-procs',
                'processes',
                'services',
                'busy',
                'network',
                'net-ports',
                'net-peers',
                'net-urls',
                'backups',
                'backups-other',
                'backups-jetbackup',
            ],
            'charts' => ['*'],
            'tools' => ['diagnostic', 'navigation'],
            'notes' => true,
            'live' => true,
            'ai' => true,
            'ips' => true,
            'ranges' => [600, 3600, 21600, 86400, 604800, 2592000, 31536000],
            'write' => false,
        ],
        'Administrators' => [
            'pages' => ['*'],
            'charts' => ['*'],
            'tools' => ['*'],
            'notes' => true,
            'live' => true,
            'ai' => true,
            'ips' => true,
            'ranges' => ['*'],
            'write' => true,
        ],
    ];

    /*
     * Explicit product roles, plus the original names as compatibility aliases.
     * Viewer sees operational evidence but cannot use live, AI, note, or IP
     * surfaces; Operator has the long-standing Staff capabilities without
     * configuration-write authority; Administrator owns the full installation.
     */
    $groups['Viewer'] = $groups['Staff'];
    $groups['Viewer']['tools'] = [];
    $groups['Viewer']['notes'] = false;
    $groups['Viewer']['live'] = false;
    $groups['Viewer']['ai'] = false;
    $groups['Viewer']['ips'] = false;
    $groups['Operator'] = $groups['Staff'];
    $groups['Administrator'] = $groups['Administrators'];
    return $groups;
}

/** The named policies after local configuration has overridden the defaults. */
function accessGroups(array $config): array
{
    $groups = accessDefaults();
    foreach ($config['access']['groups'] ?? [] as $name => $policy) {
        if (!is_string($name) || !is_array($policy)) {
            continue;
        }
        $groups[$name] = array_replace($groups[$name] ?? [], $policy);
    }
    return $groups;
}

/** The identity stored in the session, including Public for every visitor. */
function accessIdentity(array $config): array
{
    $groups = ['Public'];
    if (isSignedIn()) {
        foreach (
            (array) ($_SESSION['groups'] ?? ['Administrators'])
            as $group
        ) {
            if (is_string($group) && $group !== '') {
                $groups[] = $group;
            }
        }
    }
    return [
        'username' => isSignedIn()
            ? (string) ($_SESSION['username'] ?? 'administrator')
            : null,
        'groups' => array_values(array_unique($groups)),
    ];
}

function accessListAllows(array $values, string|int $wanted): bool
{
    foreach ($values as $value) {
        if ($value === '*' || (string) $value === (string) $wanted) {
            return true;
        }
    }
    return false;
}

/** Whether the active identity may use one protected surface. */
function accessAllows(
    array $config,
    string $surface,
    string|int|null $id = null,
): bool {
    return accessAllowsGroups(
        $config,
        accessIdentity($config)['groups'],
        $surface,
        $id,
    );
}

/** Checks a verified external identity without putting it in the PHP session. */
function accessAllowsGroups(
    array $config,
    array $groups,
    string $surface,
    string|int|null $id = null,
): bool {
    $policies = accessGroups($config);
    foreach ($groups as $group) {
        $policy = $policies[$group] ?? null;
        if (!is_array($policy)) {
            continue;
        }
        if (
            in_array($surface, ['notes', 'live', 'ai', 'ips', 'write'], true) &&
            !empty($policy[$surface])
        ) {
            return true;
        }
        if (
            in_array($surface, ['pages', 'charts', 'tools', 'ranges'], true) &&
            $id !== null &&
            accessListAllows((array) ($policy[$surface] ?? []), $id)
        ) {
            return true;
        }
    }
    return false;
}

/** A safe summary for the browser; never exposes user hashes or group internals. */
function accessView(array $config): array
{
    $identity = accessIdentity($config);
    $policies = accessGroups($config);
    $collect = fn(string $surface): array => array_values(
        array_unique(
            array_merge(
                ...array_map(
                    fn(string $group): array => (array) ($policies[$group][
                        $surface
                    ] ?? []),
                    $identity['groups'],
                ),
            ),
        ),
    );

    return $identity + [
        'pages' => $collect('pages'),
        'charts' => $collect('charts'),
        'tools' => $collect('tools'),
        'notes' => accessAllows($config, 'notes'),
        'live' => accessAllows($config, 'live'),
        'ai' => accessAllows($config, 'ai'),
        'ips' => accessAllows($config, 'ips'),
        'ip_privacy' => !empty($_SESSION['hide_ips']),
        'account_privacy' => !empty($_SESSION['hide_accounts']),
        'ranges' => $collect('ranges'),
        'write' => accessAllows($config, 'write'),
    ];
}
