<?php
declare(strict_types=1);

/* Product limits live here, not in individual feature handlers. A local config
 * is the source of truth for self-hosted/offline use; future signed licenses or
 * managed subscriptions can feed this same normalized shape. */

/** @return array{name: string, remote_host_limit: int} */
function hostEntitlement(array $config): array
{
    $set = (array) ($config['edition'] ?? []);
    $name = trim((string) ($set['name'] ?? 'Free')) ?: 'Free';
    $limit = (int) ($set['remote_host_limit'] ?? 3);
    return [
        'name' => mb_substr($name, 0, 80),
        'remote_host_limit' => max(0, min(100000, $limit)),
    ];
}
