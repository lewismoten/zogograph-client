<?php
declare(strict_types=1);

/* Core features use the same declarative shape as package contributions, but
 * their renderer remains reviewed application code. A disabled feature must
 * remove its dashboard routes and refuse its API surface; hiding a nav link is
 * never treated as enforcement. */

const CORE_FEATURES = [
    'assistant' => [
        'dashboard_routes' => ['sys-ai', 'sys-ai-past', 'sys-ai-identity', 'sys-ai-model', 'sys-ai-prices',
            'sys-ai-limits', 'sys-ai-images', 'sys-ai-tools', 'sys-ai-try', 'sys-ai-billing', 'sys-reports',
            'sys-memory', 'sys-ai-data'],
        'setup_section' => 'features',
    ],
    'peers' => [
        'dashboard_routes' => ['sys-peers'],
        'setup_section' => 'features',
    ],
];

/** Missing settings deliberately preserve existing installations. */
function coreFeatureEnabled(array $config, string $id): bool
{
    if (!isset(CORE_FEATURES[$id])) return false;
    $configured = $config['core_features'][$id] ?? true;
    return $configured !== false;
}

/** Safe shell metadata only; no configuration, credentials, or package code. */
function coreFeatureStatus(array $config): array
{
    $out = [];
    foreach (CORE_FEATURES as $id => $feature) {
        $out[] = ['id' => $id, 'enabled' => coreFeatureEnabled($config, $id),
            'dashboard_routes' => $feature['dashboard_routes']];
    }
    return $out;
}

/** Feature action boundaries include reads, writes, and connection diagnostics. */
function coreFeatureForAction(string $action): ?string
{
    if ($action === 'ai' || str_starts_with($action, 'ai-')) return 'assistant';
    if ($action === 'peers' || str_starts_with($action, 'peer-')) return 'peers';
    return null;
}

function requireCoreFeatureAccess(array $config, string $action): void
{
    $feature = coreFeatureForAction($action);
    if ($feature !== null && !coreFeatureEnabled($config, $feature)) {
        throw new ActionRefused('this core feature is disabled');
    }
}
