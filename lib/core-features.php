<?php
declare(strict_types=1);

/* Core features use the same declarative shape as package contributions, but
 * their renderer remains reviewed application code. A disabled feature must
 * remove its dashboard routes and refuse its API surface; hiding a nav link is
 * never treated as enforcement. */

const CORE_FEATURES = [
    'overview' => [
        'dashboard_routes' => ['overview'],
        'setup_section' => 'features',
    ],
    'assistant' => [
        'dashboard_routes' => ['sys-ai', 'sys-ai-past', 'sys-ai-identity', 'sys-ai-model', 'sys-ai-providers', 'sys-ai-prices',
            'sys-ai-limits', 'sys-ai-images', 'sys-ai-tools', 'sys-ai-try', 'sys-ai-billing', 'sys-reports',
            'sys-memory', 'sys-ai-data'],
        'setup_section' => 'features',
    ],
    'peers' => [
        'dashboard_routes' => ['sys-peers'],
        'setup_section' => 'features',
    ],
    'marketplace' => [
        'dashboard_routes' => ['sys-marketplace'],
        'setup_section' => 'features',
    ],
    'terminal' => [
        'dashboard_routes' => ['sys-terminal', 'sys-sessions'],
        'setup_section' => 'features',
    ],
    'notes' => [
        'dashboard_routes' => ['sys-notes', 'sys-snapshots'],
        'setup_section' => 'features',
    ],
    'problems' => [
        'dashboard_routes' => ['sys-problems'],
        'setup_section' => 'features',
    ],
    'storage' => [
        'dashboard_routes' => ['storage', 'storage-files', 'storage-map', 'storage-links', 'log-stream',
            'sys-account', 'sys-host-data', 'sys-retention'],
        'setup_section' => 'features',
    ],
    'audit' => [
        'dashboard_routes' => ['deletions'],
        'setup_section' => 'features',
    ],
    'metadata' => [
        'dashboard_routes' => ['net-labels', 'sys-descriptions'],
        'setup_section' => 'features',
    ],
    'notifications' => [
        'dashboard_routes' => ['email'],
        'setup_section' => 'features',
    ],
    'backups' => [
        'dashboard_routes' => ['backups', 'backups-actions', 'backups-other', 'backups-jetbackup'],
        'setup_section' => 'features',
    ],
    'encryption' => [
        'dashboard_routes' => ['sys-encryption'],
        'setup_section' => 'features',
    ],
    'alerts' => [
        'dashboard_routes' => ['alerts'],
        'setup_section' => 'features',
    ],
    'uptime' => [
        'dashboard_routes' => ['uptime'],
        'setup_section' => 'features',
    ],
    'packages' => [
        'dashboard_routes' => ['sys-packages'],
        'setup_section' => 'features',
    ],
    'charts' => [
        'dashboard_routes' => ['charts'],
        'setup_section' => 'features',
    ],
    'health' => [
        'dashboard_routes' => ['system'],
        'setup_section' => 'features',
    ],
    'network' => [
        'dashboard_routes' => ['network', 'net-ports', 'net-peers', 'net-capture', 'net-captures', 'net-dns',
            'net-urls', 'net-fetch', 'net-rdns', 'dns-records', 'domains', 'whois', 'whoami', 'calendar'],
        'setup_section' => 'features',
    ],
    'host' => [
        'dashboard_routes' => ['host-disk', 'disk-dirs', 'disk-deleted', 'disk-procs', 'disk-rootfiles',
            'host-layout', 'cagefs', 'storage-io', 'cpu', 'cpu-time', 'cpu-cores', 'cpu-procs', 'cpu-queue',
            'cpu-limits', 'host', 'mem-users', 'mem-goes', 'mem-services', 'mem-procs', 'mem-slab',
            'processes', 'services', 'busy', 'units', 'capabilities', 'sys-installed', 'sys-changed'],
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
    if ($action === 'marketplace' || str_starts_with($action, 'marketplace-')) return 'marketplace';
    if ($action === 'terminal' || str_starts_with($action, 'terminal-')) return 'terminal';
    if ($action === 'notes' || str_starts_with($action, 'note-') || $action === 'snapshot-delete') return 'notes';
    if ($action === 'problems' || $action === 'clear-problems') return 'problems';
    if ($action === 'store' || str_starts_with($action, 'store-')
        || in_array($action, ['scans', 'offenders', 'history', 'treemap', 'inspect', 'host-logs', 'log-stream',
            'symlinks', 'link-fix', 'link-sweep', 'ldconfig', 'path-hygiene', 'path-hygiene-rename',
            'delete-file', 'delete-dir', 'empty'], true)) return 'storage';
    if ($action === 'actions' || $action === 'clear-audit') return 'audit';
    if ($action === 'labels' || str_starts_with($action, 'label-') || $action === 'descriptions'
        || str_starts_with($action, 'description-')) return 'metadata';
    if ($action === 'email' || $action === 'email-save' || $action === 'test-email' || $action === 'test-telegram') return 'notifications';
    if ($action === 'backups') return 'backups';
    if (in_array($action, ['encryption-status', 'encryption-seal', 'encryption-recovery-drill',
        'package-runtime-custody-envelope-store', 'package-runtime-custody-recovery-drill'], true)) return 'encryption';
    if ($action === 'alerts' || str_starts_with($action, 'alert-')) return 'alerts';
    if ($action === 'uptime') return 'uptime';
    if ($action === 'charts' || $action === 'chart-series') return 'charts';
    if (in_array($action, ['health', 'self', 'diagnose', 'diagnosis-ignore', 'diagnosis-unignore'], true)) return 'health';
    if (in_array($action, ['network', 'captures', 'dns', 'dns-records', 'urls', 'fetch', 'domains', 'whois',
        'calendar', 'whoami', 'rdns', 'request-capture', 'dns-sweep'], true) || str_starts_with($action, 'rdns-')
        || str_starts_with($action, 'url-') || str_starts_with($action, 'domain-') || str_starts_with($action, 'event-')) return 'network';
    if (in_array($action, ['host-disk', 'mounts', 'cpu', 'host', 'host-series', 'host-io', 'processes', 'busy',
        'units', 'capabilities', 'changed', 'capability-changes', 'installed', 'check-capabilities',
        'request-host-task', 'request-host-walk', 'match-processes', 'stop-process', 'stop-group',
        'stop-matching', 'daemon-watch'], true)) return 'host';
    if ($action === 'package-i18n' || $action === 'package-pages' || str_starts_with($action, 'package-runtime-')
        || $action === 'package-runtime') return 'packages';
    return null;
}

/** Some package custody operations are deliberately protected by both the
 * package-management and protection features. */
function coreFeatureDependenciesForAction(string $action): array
{
    $features = [];
    $primary = coreFeatureForAction($action);
    if ($primary !== null) $features[] = $primary;
    if (in_array($action, ['package-runtime-custody-envelope-store', 'package-runtime-custody-recovery-drill'], true)) {
        $features[] = 'packages';
        $features[] = 'encryption';
    }
    return array_values(array_unique($features));
}

function requireCoreFeatureAccess(array $config, string $action): void
{
    foreach (coreFeatureDependenciesForAction($action) as $feature) {
        if (!coreFeatureEnabled($config, $feature)) {
            throw new ActionRefused('this core feature is disabled');
        }
    }
}
