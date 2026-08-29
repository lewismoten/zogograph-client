<?php
declare(strict_types=1);

/*
 * The only thing the dashboard talks to.
 *
 * Read endpoints are open, because the numbers are the useful part and a
 * dashboard you have to log into to glance at is a dashboard you stop glancing
 * at. Everything that names a path goes through presentPath/presentAlert, which
 * removes the real string for a signed-out caller rather than hiding it in the
 * markup. Everything that changes the disk is POST, signed in, and CSRF checked.
 */

/*
 * The error boundary goes on before anything else can fail.
 *
 * Every require below this line, the config file, the database - any of them
 * can blow up, and until this is installed a failure produces a blank 500 with
 * nothing in the body to read.
 */
require_once __DIR__ . '/lib/boot.php';
require_once __DIR__ . '/lib/setupstore.php';
require_once __DIR__ . '/lib/api-json.php';
installErrorBoundary('json');

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    header('Content-Type: application/json');
    apiRespond(['error' => 'Not configured. Copy lib/config.sample.php to lib/config.php.']);
    exit;
}

$config = loadConfiguredSettings($configFile);
date_default_timezone_set($config['timezone'] ?? 'UTC');

// Now that the config is known, problems go beside the database rather than to
// the fallback path guessed before it was read.
define('DISKMON_LOG', dirname((string)$config['database']) . '/error.log');

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/branding.php';
require_once __DIR__ . '/lib/core-features.php';
require_once __DIR__ . '/lib/mask.php';
require_once __DIR__ . '/lib/alerts.php';
require_once __DIR__ . '/lib/incident.php';
require_once __DIR__ . '/lib/daemons.php';
require_once __DIR__ . '/lib/growth.php';
require_once __DIR__ . '/lib/retention.php';
require_once __DIR__ . '/lib/treemap.php';
require_once __DIR__ . '/lib/hostdisk.php';
require_once __DIR__ . '/lib/backups.php';
require_once __DIR__ . '/lib/mountinfo.php';
require_once __DIR__ . '/lib/capabilities.php';
require_once __DIR__ . '/lib/collect.php';
require_once __DIR__ . '/lib/actions.php';
require_once __DIR__ . '/lib/logs.php';
require_once __DIR__ . '/lib/schedule.php';
require_once __DIR__ . '/lib/processes.php';
require_once __DIR__ . '/lib/symlinks.php';
require_once __DIR__ . '/lib/signals.php';
require_once __DIR__ . '/lib/hostdb.php';
// swapPressure and the host collectors live here, not in hostdb.php.
require_once __DIR__ . '/lib/host.php';
// The processor pages: differencing the counters host.php records.
require_once __DIR__ . '/lib/cpu.php';
require_once __DIR__ . '/lib/uptime.php';
require_once __DIR__ . '/lib/limits.php';
require_once __DIR__ . '/lib/inspect.php';
require_once __DIR__ . '/lib/network.php';
require_once __DIR__ . '/lib/logstream.php';
require_once __DIR__ . '/lib/diagnose.php';
require_once __DIR__ . '/lib/labels.php';
require_once __DIR__ . '/lib/whatis.php';
require_once __DIR__ . '/lib/urlwatch.php';
require_once __DIR__ . '/lib/spans.php';
require_once __DIR__ . '/lib/storekeep.php';
require_once __DIR__ . '/lib/notes.php';
require_once __DIR__ . '/lib/software.php';
require_once __DIR__ . '/lib/rdns.php';
require_once __DIR__ . '/lib/scans.php';
require_once __DIR__ . '/lib/aisession.php';
require_once __DIR__ . '/lib/aimemory.php';
require_once __DIR__ . '/lib/terminal.php';
require_once __DIR__ . '/lib/aialerts.php';
require_once __DIR__ . '/lib/headers.php';
require_once __DIR__ . '/lib/pagebits.php';
require_once __DIR__ . '/lib/domains.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/whoami.php';
require_once __DIR__ . '/lib/dnsrecords.php';
require_once __DIR__ . '/lib/external.php';
require_once __DIR__ . '/lib/updates.php';
require_once __DIR__ . '/lib/peers.php';
require_once __DIR__ . '/lib/marketplace.php';
require_once __DIR__ . '/lib/package-runtime.php';
require_once __DIR__ . '/lib/encryption.php';
// renderConfig, so the email page can save through the same writer setup uses.
require_once __DIR__ . '/lib/configure.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-HostScope-API-Version: 1.0');

startSession($config);

$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'overview');
$post = $_SERVER['REQUEST_METHOD'] === 'POST';

try {
    $pdo = db($config);
    apiRespond(route($config, $pdo, $action, $post));
} catch (ActionRefused $e) {
    // An expected refusal, not a fault: the message is written for the person
    // reading it and is safe to show.
    http_response_code(403);
    apiRespond(['error' => $e->getMessage()]);
}
// Anything else falls through to the boundary installed above, which logs it
// with a reference and returns that reference as JSON.

function route(array $config, PDO $pdo, string $action, bool $post): array
{
    $signedIn = isSignedIn();
    requireActionAccess($config, $action);
    requireCoreFeatureAccess($config, $action);

    return match ($action) {
        'overview' => overview($config, $pdo, $signedIn),
        'vitals' => vitalsSeries($pdo),
        'sample' => liveSample($config, $pdo),
        'scans' => scanSeries($config, $pdo),
        'offenders' => offenders($config, $pdo, $signedIn),
        'history' => pathHistory($config, $pdo, $signedIn),
        'alerts' => alertList($config, $pdo, $signedIn),
        'alert-email' => alertEmailView($config, $pdo, $signedIn),
        'actions' => actionLog($config, $pdo, $signedIn),
        'incident' => incidentView($config, $pdo, $signedIn),
        'treemap' => treemapView($config, $signedIn),
        'inspect' => inspectView($config, $pdo, $signedIn),
        'host-logs' => hostLogList($config, $signedIn),
        'host-disk' => hostDiskView($config, $signedIn),
        'backups' => backupsView($config, $signedIn),
        'capabilities' => capabilitiesView($config, $pdo, $signedIn),
        'mounts' => mountsView($config, $signedIn),
        'network' => networkView($config, $pdo, $signedIn),
        'units' => unitsView($config, $signedIn),
        'dns' => dnsView($config, $signedIn),
        'captures' => capturesView($config, $pdo, $signedIn),
        'labels' => labelsView($pdo, $signedIn),
        'descriptions' => descriptionsView($pdo, $signedIn),
        'whoami' => whoAmIView($config, $pdo, $signedIn),
        'dns-records' => dnsRecordsView($config, $pdo, $signedIn),
        'urls' => urlsView($pdo, $signedIn),
        'fetch' => fetchView($config, $pdo, $signedIn),
        'store' => storeView($config, $pdo, $signedIn),
        'ai-store' => aiStoreView($config, $signedIn),
        'terminal' => terminalView($config, $pdo, $signedIn),
        'terminal-run' => doTerminalRun($config, $pdo, $post),
        'terminal-editor-browse' => terminalEditorBrowseView($config, $pdo, $signedIn),
        'terminal-editor' => terminalEditorView($config, $pdo, $signedIn),
        'terminal-editor-save' => doTerminalEditorSave($config, $pdo, $post),
        'terminal-editor-restore' => doTerminalEditorRestore($config, $pdo, $post),
        'totp-recovery-codes' => doTotpRecoveryCodes($config, $pdo, $post),
        'terminal-read' => terminalReadView($config, $pdo, $signedIn),
        'terminal-stop' => doTerminalStop($config, $pdo, $post),
        'terminal-unlock' => doTerminalUnlock($config, $pdo, $post),
        'terminal-lock' => doTerminalLock($config, $pdo, $post),
        'terminal-permit' => doTerminalPermit($config, $pdo, $post),
        'terminal-sessions' => terminalSessionsView($config, $signedIn),
        'terminal-session' => terminalSessionView($config, $signedIn),
        'terminal-name-session' => doTerminalNameSession($config, $pdo, $post),
        'terminal-forget' => doTerminalForget($config, $pdo, $post),
        'ai-memories' => aiMemoriesView($config, $signedIn),
        'ai-memory-save' => doAiMemorySave($config, $pdo, $post),
        'ai-memory-forget' => doAiMemoryForget($config, $pdo, $post),
        'ai-memory-pin' => doAiMemoryPin($config, $pdo, $post),
        'ai-memory-clear' => doAiMemoryClear($config, $pdo, $post),
        'ai-compact' => doAiCompact($config, $pdo, $post),
        'ai-forget' => doAiForget($config, $pdo, $post),
        'store-save' => doStoreSave($config, $pdo, $post),
        'store-purge' => doStorePurge($config, $pdo, $post),
        'store-compact' => doStoreCompact($config, $pdo, $post),
        'store-rollup' => doStoreRollup($config, $pdo, $post),
        'store-integrity' => doStoreIntegrity($config, $pdo, $post),
        'domains' => domainsView($config, $pdo, $signedIn),
        'whois' => whoisView($config, $pdo, $signedIn),
        'calendar' => calendarView($config, $pdo, $signedIn),
        'capability-changes' => capabilityChangesView($config, $pdo, $signedIn),
        'tail' => tailLog($config, $pdo, $signedIn),
        'last-lines' => lastLines($config, $pdo, $signedIn),
        'log-stream' => logStreamView($config, $pdo, $signedIn),
        'schedule' => scheduleRead($config, $pdo, $signedIn),
        'host-setup' => hostSetup($config, $signedIn),
        'email' => emailSettings($config, $pdo, $signedIn),
        'processes' => processList($config, $pdo, $signedIn),
        'busy' => busyMoments($config, $pdo, $signedIn),
        'symlinks' => symlinkCheck($config, $pdo, $signedIn),
        'link-fix' => doLinkFix($config, $pdo, $post),
        'link-sweep' => doLinkSweep($config, $pdo, $post),
        'ldconfig' => doLdconfig($config, $pdo, $post),
        'notes' => notesView($config, $pdo, $signedIn),
        'changed' => changedView($config, $pdo, $signedIn),
        'rdns' => rdnsView($config, $pdo, $signedIn),
        'ai' => aiView($config, $pdo, $signedIn),
        'ai-poll' => aiPollView($config, $pdo, $signedIn),
        'ai-start' => doAiStart($config, $pdo, $post),
        'ai-pin' => doAiPin($config, $pdo, $post),
        'ai-transcript' => aiTranscriptView($config, $signedIn),
        'ai-resume' => doAiResume($config, $pdo, $post),
        'ai-extend' => doAiExtend($config, $pdo, $post),
        'ai-say' => doAiSay($config, $pdo, $post),
        'ai-step' => doAiStep($config, $pdo, $post),
        'ai-permit' => doAiPermit($config, $pdo, $post),
        'ai-tool-result' => doAiToolResult($config, $pdo, $post),
        'host-request' => doHostRequest($config, $pdo, $post),
        'ai-stop' => doAiStop($config, $pdo, $post),
        'ai-interrupt' => doAiInterrupt($config, $pdo, $post),
        'ai-effort' => doAiEffort($config, $pdo, $post),
        'ai-compact-chat' => doAiCompactSession($config, $pdo, $post),
        'ai-asset' => aiAssetView($config, $signedIn),
        'ai-settings' => aiSettingsView($config, $pdo, $signedIn),
        'ai-personality-save' => doAiPersonalitySave($config, $pdo, $post),
        'ai-personality-forget' => doAiPersonalityForget($config, $pdo, $post),
        'ai-portrait-clear' => doAiPortraitClear($config, $pdo, $post),
        'ai-settings-save' => doAiSettingsSave($config, $pdo, $post),
        'ai-toolset-save' => doAiToolsetSave($config, $pdo, $post),
        'ai-toolset-delete' => doAiToolsetDelete($config, $pdo, $post),
        'ai-price-save' => doAiPriceSave($config, $pdo, $post),
        'ai-price-forget' => doAiPriceForget($config, $pdo, $post),
        'client-problem' => doClientProblem($post),
        'ai-try' => doAiTry($config, $pdo, $post),
        'ai-reports' => aiReportsView($config, $pdo, $signedIn),
        'ai-report-now' => doAiReportNow($config, $pdo, $post),
        'ai-report-forget' => doAiReportForget($config, $pdo, $post),
        'ai-test' => doAiTest($config, $pdo, $post),
        'ai-reaction' => doAiReaction($config, $pdo, $post),
        'ai-sessions' => aiSessionsView($config, $signedIn),
        'charts' => chartCatalogueView($config, $signedIn),
        'chart-series' => chartSeriesView($config, $pdo, $signedIn),
        'rdns-ask' => doRdnsAsk($config, $pdo, $post),
        'rdns-cancel' => doRdnsCancel($config, $pdo, $post),
        'rdns-now' => doRdnsNow($config, $pdo, $post),
        'note-marks' => noteMarksView($pdo, $signedIn),
        'note-snapshots' => noteSnapshotsView($config, $pdo, $signedIn),
        'note-save' => doNoteSave($config, $pdo, $post),
        'note-clear' => doNoteClear($pdo, $post),
        'note-delete' => doNoteDelete($pdo, $post),
        'note-batch' => doNoteBatch($config, $pdo, $post),
        'note-snapshot' => doNoteSnapshot($config, $pdo, $signedIn),
        'snapshot-delete' => doSnapshotDelete($config, $pdo, $post),
        'cpu' => cpuView($config, $signedIn, $pdo),
        'host' => hostView($config, $signedIn, $pdo),
        'host-series' => hostSeries($config, $signedIn),
        'host-io' => hostIoSeries($config, $signedIn),
        'uptime' => uptimeView($config, $pdo, $signedIn),
        'health' => healthCheck($config, $pdo, $signedIn),
        'diagnose' => diagnoseView($config, $pdo, $signedIn),
        'path-hygiene' => pathHygieneView($config, $pdo, $signedIn),
        'path-hygiene-rename' => doPathHygieneRename($config, $pdo, $post),
        'self' => selfReport($config, $pdo, $signedIn),
        'problems' => problemLog($config, $signedIn),
        'session' => sessionState($config),
        'signin' => doSignIn($config, $post, $pdo),
        'signout' => doSignOut($pdo, $post),
        'revoke-sessions' => doRevokeSessions($config, $pdo, $post),
        'delete-file' => doDeleteFile($config, $pdo, $post),
        'delete-dir' => doDeleteDir($config, $pdo, $post),
        'empty' => doEmpty($config, $pdo, $post),
        'schedule-save' => doScheduleSave($post, $pdo),
        'updates-save' => doUpdatesSave($config, $pdo, $post),
        'scan-now' => doScanNow($pdo, $post),
        'test-email' => doTestEmail($config, $pdo, $post),
        'test-telegram' => doTestTelegram($config, $pdo, $post),
        'email-save' => doEmailSave($config, $pdo, $post),
        'clear-problems' => doClearProblems($config, $pdo, $post),
        'ip-privacy' => doIpPrivacy($config, $post),
        'account-privacy' => doAccountPrivacy($config, $post),
        'diagnosis-ignore' => doDiagnosisIgnore($config, $pdo, $post),
        'diagnosis-unignore' => doDiagnosisUnignore($config, $pdo, $post),
        'clear-audit' => doClearAudit($pdo, $post),
        'alert-action' => doAlertAction($config, $pdo, $post),
        'alert-mute' => doAlertMute($pdo, $post),
        'alert-unmute' => doAlertUnmute($pdo, $post),
        'daemon-watch' => doDaemonWatch($config, $pdo, $post),
        'request-host-walk' => doRequestHostWalk($config, $pdo, $post),
        'request-capture' => doRequestCapture($config, $pdo, $post),
        'cancel-task' => doCancelTask($pdo, $post),
        'label-save' => doLabelSave($pdo, $post),
        'label-delete' => doLabelDelete($pdo, $post),
        'description-save' => doDescriptionSave($pdo, $post),
        'description-forget' => doDescriptionForget($pdo, $post),
        'url-save' => doUrlSave($config, $pdo, $post),
        'url-delete' => doUrlDelete($pdo, $post),
        'url-run' => doUrlRun($config, $pdo, $post),
        'url-toggle' => doUrlToggle($pdo, $post),
        'regex-try' => doRegexTry($post),
        'domain-save' => doDomainSave($config, $pdo, $post),
        'domain-delete' => doDomainDelete($config, $pdo, $post),
        'domain-check' => doDomainCheck($config, $pdo, $post),
        'dns-sweep' => doDnsSweep($config, $pdo, $post),
        'event-save' => doEventSave($pdo, $post),
        'event-delete' => doEventDelete($pdo, $post),
        'event-resolve' => doEventResolve($pdo, $post),
        'peers' => peersView($config, $pdo, $signedIn),
        'marketplace' => marketplaceView($config, $pdo, $signedIn),
        'marketplace-enable' => doMarketplaceEnable($config, $pdo, $post),
        'marketplace-install' => doMarketplaceInstall($config, $pdo, $post),
        'marketplace-install-path-hygiene' => doMarketplaceInstallPathHygiene($config, $pdo, $post),
        'marketplace-history' => marketplaceHistoryView($config, $post),
        'marketplace-rollback' => doMarketplaceRollback($config, $pdo, $post),
        'package-runtime' => packageRuntimeView($config, $pdo, $signedIn),
        'package-i18n' => packageRuntimeLocaleCatalogsView($pdo, $signedIn),
        'package-pages' => packageRuntimeDashboardPagesView($pdo, $signedIn),
        'package-runtime-install-archive' => doPackageRuntimeInstallArchive($config, $pdo, $post),
        'package-runtime-remove' => doPackageRuntimeRemove($config, $pdo, $post),
        'package-runtime-probe' => doPackageRuntimeProbe($config, $pdo, $post),
        'package-runtime-custody-diagnostic-approve' => doPackageRuntimeCustodyDiagnosticApprove($pdo, $post),
        'package-runtime-custody-enrollment-check' => doPackageRuntimeCustodyEnrollmentCheck($config, $pdo, $post),
        'package-runtime-custody-envelope-store' => doPackageRuntimeCustodyEnvelopeStore($config, $pdo, $post),
        'package-runtime-custody-recovery-drill' => doPackageRuntimeCustodyRecoveryDrill($config, $pdo, $post),
        'package-runtime-enable' => doPackageRuntimeEnable($pdo, $post),
        'package-runtime-revoke' => doPackageRuntimeRevoke($pdo, $post),
        'package-runtime-unrevoke' => doPackageRuntimeLiftRevocation($pdo, $post),
        'package-runtime-settings' => packageRuntimeSettingsView($config, $pdo, $signedIn),
        'package-runtime-settings-save' => doPackageRuntimeSettingsSave($config, $pdo, $post),
        'package-runtime-secrets' => packageRuntimeSecretsView($config, $pdo, $signedIn),
        'package-runtime-secret-create' => doPackageRuntimeSecretCreate($config, $pdo, $post),
        'package-runtime-secret-delete' => doPackageRuntimeSecretDelete($config, $pdo, $post),
        'encryption-status' => encryptionStatusView($config, $pdo, $signedIn),
        'encryption-seal' => doEncryptionSeal($config, $pdo, $post),
        'encryption-recovery-drill' => doEncryptionRecoveryDrill($config, $pdo, $post),
        'peer-save' => doPeerSave($config, $pdo, $post),
        'peer-delete' => doPeerDelete($config, $pdo, $post),
        'peer-transport-rotate' => doPeerTransportRotate($config, $pdo, $post),
        'peer-test' => doPeerTest($config, $pdo, $post),
        'peer-read' => doPeerRead($config, $pdo, $post),
        'check-capabilities' => doCheckCapabilities($config, $pdo, $post),
        'request-host-task' => doRequestHostTask($config, $pdo, $post),
        'match-processes' => doMatchProcesses($config, $post),
        'stop-process' => doStopProcess($config, $pdo, $post),
        'stop-group' => doStopGroup($config, $pdo, $post),
        'stop-matching' => doStopMatching($config, $pdo, $post),
        default => throw new ActionRefused('unknown action'),
    };
}

/** The endpoint has the same policy as the page or tool which uses it. */
function requireActionAccess(array $config, string $action): void
{
    $pages = [
        'vitals' => 'charts', 'scans' => 'storage', 'offenders' => 'storage-files',
        'history' => 'storage-files', 'treemap' => 'storage-map', 'inspect' => 'storage-files',
        'host-logs' => 'storage-files', 'host-disk' => 'host-disk', 'backups' => 'backups',
        'mounts' => 'host-layout', 'processes' => 'processes', 'busy' => 'busy',
        'network' => 'network', 'captures' => 'net-captures', 'dns' => 'net-dns',
        'dns-records' => 'dns-records', 'urls' => 'net-urls', 'fetch' => 'net-fetch',
        'whois' => 'whois', 'whoami' => 'whoami', 'rdns' => 'net-rdns',
        'units' => 'units', 'calendar' => 'calendar', 'alerts' => 'alerts',
        'uptime' => 'uptime', 'cpu' => 'cpu', 'host' => 'host', 'host-series' => 'host',
        'host-io' => 'storage-io', 'capabilities' => 'capabilities', 'schedule' => 'schedule',
        'email' => 'email', 'actions' => 'deletions', 'store' => 'sys-account',
        'ai-store' => 'sys-ai-data', 'terminal' => 'sys-terminal',
        'terminal-read' => 'sys-terminal', 'terminal-sessions' => 'sys-sessions',
        'terminal-session' => 'sys-sessions', 'notes' => 'sys-notes',
        'note-marks' => 'sys-notes', 'note-snapshots' => 'sys-notes',
        'chart-series' => 'charts', 'charts' => 'charts',
        'peers' => 'sys-peers', 'marketplace' => 'sys-marketplace',
        'marketplace-install-path-hygiene' => 'sys-marketplace',
        'package-runtime' => 'sys-packages',
        'package-i18n' => 'sys-packages',
        'package-pages' => 'sys-packages',
        'package-runtime-install-archive' => 'sys-packages',
        'package-runtime-remove' => 'sys-packages',
        'package-runtime-probe' => 'sys-packages',
        'package-runtime-custody-diagnostic-approve' => 'sys-packages',
        'package-runtime-custody-enrollment-check' => 'sys-packages',
        'package-runtime-custody-envelope-store' => 'sys-encryption',
        'package-runtime-custody-recovery-drill' => 'sys-encryption',
        'package-runtime-enable' => 'sys-packages',
        'package-runtime-revoke' => 'sys-packages',
        'package-runtime-unrevoke' => 'sys-packages',
        'package-runtime-settings' => 'sys-packages',
        'package-runtime-settings-save' => 'sys-packages',
        'package-runtime-secrets' => 'sys-packages',
        'package-runtime-secret-create' => 'sys-packages',
        'package-runtime-secret-delete' => 'sys-packages',
        'encryption-status' => 'sys-encryption',
        'encryption-seal' => 'sys-encryption',
        'encryption-recovery-drill' => 'sys-encryption',
        'labels' => 'net-labels', 'descriptions' => 'sys-descriptions',
        'changed' => 'sys-changed', 'capability-changes' => 'sys-changed',
        'installed' => 'sys-installed', 'symlinks' => 'storage-links',
        'tail' => 'log-stream', 'last-lines' => 'log-stream', 'log-stream' => 'log-stream',
        'health' => 'system', 'self' => 'system', 'problems' => 'sys-problems',
        'path-hygiene' => 'storage', 'path-hygiene-rename' => 'storage',
        'host-setup' => 'sys-host-data',
        'ai-memories' => 'sys-memory', 'ai-reports' => 'sys-reports',
        'ai-sessions' => 'sys-ai-billing',
    ];
    if (isset($pages[$action]) && !accessAllows($config, 'pages', $pages[$action])) {
        throw new ActionRefused('your group cannot open this page');
    }

    if ($action === 'chart-series') {
        $chart = (string)($_GET['chart'] ?? $_POST['chart'] ?? '');
        if ($chart === '' || !accessAllows($config, 'charts', $chart)) {
            throw new ActionRefused('your group cannot view that chart');
        }
    }

    $tool = str_starts_with($action, 'terminal') ? 'terminal'
        : (in_array($action, ['fetch', 'whois', 'dns', 'rdns-ask', 'rdns-now'], true) ? 'navigation' : null);
    if ($tool !== null && !accessAllows($config, 'tools', $tool)) {
        throw new ActionRefused('your group cannot use this tool');
    }
    if (str_starts_with($action, 'ai') && !accessAllows($config, 'ai')) {
        throw new ActionRefused('your group cannot use the assistant');
    }
    $range = (int)($_GET['range'] ?? $_POST['range'] ?? 0);
    if ($range > 0 && !accessAllows($config, 'ranges', $range)) {
        throw new ActionRefused('your group cannot use that time range');
    }
}

/* ------------------------------------------------------------------ read -- */

/**
 * The newest host reading, or null when there is not one to be had.
 *
 * Every reason for there not being one - collection turned off, the cron never
 * run, the file unreadable by this account - is the same answer here: no
 * comparison. The host panel is where those are told apart and explained; this
 * one only needs to know whether it can compare.
 */
function latestHostSample(array $config): ?array
{
    if (!($config['host']['enabled'] ?? true)) {
        return null;
    }

    try {
        $pdo = hostDb($config, false);
        if ($pdo === null) {
            return null;
        }
        return $pdo->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

function overview(array $config, PDO $pdo, bool $signedIn): array
{
    /* This endpoint boots every page, so it cannot be refused like an ordinary
     * feature API. When Overview is disabled it returns only shell state and
     * empty presentation values; the browser then routes to another enabled
     * page without receiving dashboard measurements. */
    if (!coreFeatureEnabled($config, 'overview')) {
        return [
            'signed_in' => $signedIn,
            'brand' => brandName($config),
            'core_features' => coreFeatureStatus($config),
            'csrf' => $signedIn ? csrfToken() : null,
            'access' => accessView($config),
            'account' => currentAccountName(),
            'report' => null,
            'broadcasts' => [],
            'who' => $signedIn && coreFeatureEnabled($config, 'assistant') ? (function () use ($config): array {
                $persona = aiPersona($config);
                return ['name' => $persona['name'], 'emoji' => $persona['emoji']];
            })() : null,
            'vitals' => null,
            'scan' => null,
            'scan_running' => false,
            'totals' => [],
            'emptied' => [],
            'open_alerts' => 0,
            'limits' => null,
            'host_clock' => [],
            'deletable' => [],
            'deletable_scope' => 'account',
            'categories' => [],
            'viewable' => [],
            'server_time' => time(),
        ];
    }
    $vitals = $pdo->query('SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1')->fetch() ?: null;

    $scan = $pdo->query(
        'SELECT * FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 1'
    )->fetch() ?: null;



    $totals = [];
    if ($scan) {
        $stmt = $pdo->prepare('SELECT category, bytes, files FROM scan_totals WHERE scan_id = ?');
        $stmt->execute([$scan['id']]);
        foreach ($stmt->fetchAll() as $row) {
            $totals[$row['category']] = ['bytes' => (int)$row['bytes'], 'files' => (int)$row['files']];
        }
    }

    return [
        'signed_in' => $signedIn,
        'brand' => brandName($config),
        'core_features' => coreFeatureStatus($config),
        'csrf' => $signedIn ? csrfToken() : null,
        'access' => accessView($config),
        /*
         * Which account this dashboard is running as.
         *
         * So that the process lists can mark the rows that are this account's
         * own work and the rows that are root's. Reading a hundred-row process
         * table means constantly asking "is that one mine", and the answer is
         * on the page already - it just is not marked.
         *
         * Not a secret: it is in every path the signed-in view already shows,
         * and the masked view masks the paths rather than the account name.
         */
        'account' => currentAccountName(),
        /*
         * The newest summary, for the front page.
         *
         * Signed-in only: it is prose about this machine, written from tool
         * results that name paths and processes, and no amount of redacting a
         * paragraph afterwards makes that safe for the open internet.
         */
        'report' => $signedIn && coreFeatureEnabled($config, 'assistant') ? aiLatestReport($pdo) : null,
        /*
         * Messages one person left for whoever else opens this.
         *
         * Carried on the shared call because they belong on every page - the
         * whole point is that somebody arriving at two in the morning sees
         * "I know about the disk" before they start investigating it again.
         */
        'broadcasts' => $signedIn && coreFeatureEnabled($config, 'notes') && accessAllows($config, 'notes')
            ? noteBroadcasts($pdo, $config) : [],
        /*
         * Two strings, carried on the call every page already makes.
         *
         * The name and emoji appear in the navigation, which is drawn before
         * anybody opens the chat - so fetching them only when the panel opens
         * meant the menu said one thing and the panel said another until you
         * clicked.
         */
        'who' => $signedIn && coreFeatureEnabled($config, 'assistant') ? (function () use ($config): array {
            $persona = aiPersona($config);
            return ['name' => $persona['name'], 'emoji' => $persona['emoji']];
        })() : null,
        /*
         * Recorded here because every page makes this call.
         *
         * The collectors use it to decide whether to collect more eagerly, and
         * a browser polling is a far better signal than the web server's
         * access log - which on a host behind a CDN may not be readable and on
         * any host is full of crawlers.
         */
        'browsing_noted' => $signedIn && coreFeatureEnabled($config, 'assistant') ? aiNoteBrowsing($pdo) : false,
        'vitals' => $vitals,
        'scan' => $scan,
        'scan_running' => scanRunning($pdo),
        'totals' => $totals,
        /*
         * Categories emptied since the scan that measured them.
         *
         * The totals come from the last scan, so after emptying one the figure
         * on its card is stale until the next scan runs - and offering to empty
         * it again, under a size that is no longer there, is the same trap the
         * per-row Delete button had.
         *
         * A partial empty counts here, unlike for individual rows. There the
         * question is "was this exact file removed", and a partial run may have
         * skipped it; here it is "is this total still true", and after any
         * successful removal it is not.
         */
        'emptied' => $scan ? emptiedSince($pdo, (int)$scan['started_at']) : [],
        'open_alerts' => (int)$pdo->query(
            'SELECT COUNT(*) FROM alerts WHERE raised_at >= ' . (time() - 86400)
        )->fetchColumn(),
        /*
         * What the host allows, when there is a host reading to say.
         *
         * Folded into the overview rather than given an endpoint of its own
         * because it changes nothing and is needed on the page that opens: a
         * dashboard that reports 94% CPU without saying that is 94% of two
         * cores out of thirty-two has told you the wrong thing first.
         */
        'limits' => accountLimits($vitals, latestHostSample($config)),
        // The inner countdown ring and the pending-task banners read this.
        'host_clock' => hostClock($config),
        'deletable' => $config['deletable_categories'] ?? [],
        // What the delete buttons are allowed to reach - see resolveDeletable.
        'deletable_scope' => (string)($config['deletable_scope'] ?? 'account'),
        // So the dashboard builds its filters and its chart from the rules that
        // are actually in force, rather than a copy of them that goes stale.
        'categories' => categoryNames($config['scan']),
        'viewable' => $config['viewable_categories'] ?? [],
        'server_time' => time(),
    ];
}

/**
 * When each category was last emptied, if since the given moment.
 *
 * @return array<string, int>
 */
function emptiedSince(PDO $pdo, int $since): array
{
    $stmt = $pdo->prepare(
        "SELECT target, MAX(at) AS at FROM actions
         WHERE action = 'empty_category' AND outcome IN ('ok', 'partial') AND at >= ?
         GROUP BY target"
    );
    $stmt->execute([$since]);

    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[(string)$row['target']] = (int)$row['at'];
    }

    return $out;
}

/** The vitals series for a window, thinned so a long range is not a huge body. */
/**
 * How wide one chart point should be, from a ladder of round durations.
 *
 * The smallest step that brings the range under the point budget. Round
 * numbers because the reader has to hold the bucket in their head while
 * looking at the chart - "each point is five minutes" is a thought, "each
 * point is 43.7 seconds" is an obstacle.
 */
function displayBucket(int $span, int $points): int
{
    if ($span <= 0 || $points <= 0) {
        return 0;
    }

    $ladder = [
        1, 5, 10, 15, 30,
        60, 120, 300, 600, 900, 1800,
        3600, 7200, 10800, 21600, 43200,
        86400, 172800, 604800,
    ];

    foreach ($ladder as $step) {
        if ($span / $step <= $points) {
            return $step;
        }
    }

    return end($ladder);
}

function vitalsSeries(PDO $pdo): array
{
    [$from, $to] = window();
    $limit = clampInt($_GET['limit'] ?? 720, 10, 5000);

    $count = $pdo->prepare('SELECT COUNT(*) FROM vitals WHERE taken_at BETWEEN ? AND ?');
    $count->execute([$from, $to]);
    $total = (int)$count->fetchColumn();

    /*
     * Grouped into time buckets chosen from the range, worst value per bucket.
     *
     * A year holds three million ten-second readings and a canvas has about a
     * thousand pixels, so something has to give. This used to take every nth
     * row, on the reasoning that averaging destroys spikes - which is true,
     * and every-nth destroys them just as thoroughly: it keeps an arbitrary
     * sample and the spike is only kept if it happens to be the arbitrary one.
     *
     * So the bucket carries its worst value, exactly as compaction does in
     * lib/retention.php and using the same classification - MAX for the
     * columns where high is bad, MIN for the three that measure what is left.
     * A point then means "the worst it got in this bucket", which is what a
     * monitoring chart is read for.
     *
     * Buckets come from a ladder of round durations rather than span/points,
     * so the x-axis lands on ten seconds or five minutes or an hour instead of
     * on 43.7 seconds.
     */
    $bucket = displayBucket($to - $from, $limit);

    if ($bucket <= 1) {
        $stmt = $pdo->prepare(
            'SELECT * FROM vitals WHERE taken_at BETWEEN :from AND :to ORDER BY taken_at'
        );
    } else {
        $min = retentionMinColumns();
        $select = [
            'MAX(taken_at) AS taken_at',
            'MAX(id) AS id',
            'SUM(COALESCE(samples, 1)) AS samples',
        ];
        foreach (vitalsColumns($pdo) as $column) {
            if (in_array($column, ['id', 'taken_at', 'samples'], true)) {
                continue;
            }
            $select[] = (in_array($column, $min, true) ? 'MIN' : 'MAX') . "($column) AS $column";
        }

        $stmt = $pdo->prepare(
            'SELECT ' . implode(', ', $select) . '
             FROM vitals WHERE taken_at BETWEEN :from AND :to
             GROUP BY taken_at / :bucket
             ORDER BY taken_at'
        );
        $stmt->bindValue(':bucket', $bucket, PDO::PARAM_INT);
    }

    // Integers, for the reason set out in lib/alerts.php: a bound string
    // compared against a computed expression never matches in SQLite.
    $stmt->bindValue(':from', $from, PDO::PARAM_INT);
    $stmt->bindValue(':to', $to, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();
    $step = $bucket;

    /*
     * Whether this range has been compacted, and how coarsely.
     *
     * Once readings are merged, a point stops meaning "the machine was like
     * this at 04:12" and starts meaning "this was the worst it got in the hour
     * around 04:12". Those are different claims and the second one is only
     * honest if it is stated - a chart that silently changes what its points
     * mean halfway along its own x-axis is a trap.
     */
    $coarse = $pdo->prepare(
        'SELECT MAX(COALESCE(samples, 1)) FROM vitals WHERE taken_at BETWEEN ? AND ?'
    );
    $coarse->execute([$from, $to]);
    $worst = (int)$coarse->fetchColumn();

    /*
     * The projection is computed over a fixed day, not over the rows returned.
     *
     * This used to follow the displayed range, on the reasoning that the card
     * and the chart should describe the same window. That was the wrong
     * trade, for two reasons found by using it.
     *
     * First, nothing on the chart draws the trend, so there was no line for the
     * card to agree with - the coupling bought nothing.
     *
     * Second, an hour cannot support a projection. Disk space does not fall
     * smoothly: it sits flat and then jumps when something flushes, so a
     * one-hour window catches whichever burst it happened to land in. Measured
     * on identical simulated behaviour, the same machine projected anywhere
     * between 80 and 207 days depending on the hour, and occasionally reported
     * a spike instead. Changing the range to look at something made the
     * projection jump, which reads as the machine changing.
     *
     * A day is also exactly the window the alert rules use, so the card and the
     * email that may arrive about it now agree by construction rather than by
     * coincidence.
     */
    $recent = $pdo->prepare(
        'SELECT taken_at, disk_free_bytes, inodes_free FROM vitals
         WHERE taken_at >= ? ORDER BY taken_at'
    );
    $recent->execute([time() - TREND_WINDOW_SECONDS]);
    $recentRows = $recent->fetchAll();

    /*
     * Worked out over exactly the rows being returned, like the trend above.
     *
     * The chart shades the gaps, so the shading and the line have to come from
     * the same set of readings - computed separately they would disagree
     * whenever the series was thinned.
     */
    $uptime = analyseUptime($rows, $from, $to);

    return [
        'from' => $from,
        'to' => $to,
        'total' => $total,
        'step' => $step,
        // How wide each point is. 0 means the readings are shown as collected.
        'bucket_seconds' => $bucket > 1 ? $bucket : 0,
        'rows' => $rows,
        'uptime' => $uptime,
        // 1 means every point is a single reading; more means the older part
        // of this range has been compacted and each point is a worst-of.
        'samples_per_point' => $worst,
        // The window goes inside each trend rather than beside them: the card is
        // handed one trend, and a field it cannot reach is a field it will not
        // print.
        'trend' => [
            'disk' => trendWithWindow($recentRows, 'disk_free_bytes'),
            'inodes' => trendWithWindow($recentRows, 'inodes_free'),
        ],
    ];
}

/** A trend that carries the window it was measured over. */
function trendWithWindow(array $rows, string $field): ?array
{
    $trend = seriesTrend($rows, $field);
    return $trend === null ? null : $trend + ['window_seconds' => TREND_WINDOW_SECONDS];
}

/**
 * Takes a reading right now and returns it.
 *
 * What "live update" polls. The collector's own clock is a minute at best, so
 * without this the ten-second mode would just re-read the same row - and the
 * moment you actually want ten-second resolution is the moment something is
 * going wrong, which is the worst time to be looking at minute-old numbers.
 */
function liveSample(array $config, PDO $pdo): array
{
    if (!accessAllows($config, 'live')) {
        throw new ActionRefused('Live readings are available to Staff and Administrators.');
    }
    return ['vitals' => takeVitals($config, $pdo, 'live')];
}

function scanSeries(array $config, PDO $pdo): array
{
    [$from, $to] = window();

    /*
     * One column per category, built from the config rather than written out.
     *
     * The value is bound; the column *alias* cannot be, so it is filtered to
     * plain identifiers instead. These names come from a config file rather
     * than from a request, but an alias built by string concatenation is an
     * alias built by string concatenation - and a category someone names with a
     * space in it would otherwise produce a syntax error nobody could explain.
     */
    $categories = array_values(array_filter(
        categoryNames($config['scan']),
        fn(string $name): bool => (bool)preg_match('/^[a-z][a-z0-9_]{0,30}$/i', $name)
    ));

    $columns = [];
    $params = [':from' => $from, ':to' => $to];
    foreach ($categories as $i => $category) {
        $columns[] = "MAX(CASE WHEN t.category = :c$i THEN t.bytes END) AS {$category}_bytes";
        $params[":c$i"] = $category;
    }

    $stmt = $pdo->prepare(
        'SELECT s.id, s.started_at, s.finished_at, s.duration_ms, s.files_seen, s.dirs_seen,
                s.bytes_seen' . ($columns ? ', ' . implode(', ', $columns) : '') . '
         FROM scans s LEFT JOIN scan_totals t ON t.scan_id = s.id
         WHERE s.finished_at IS NOT NULL AND s.started_at BETWEEN :from AND :to
         GROUP BY s.id ORDER BY s.started_at'
    );
    $stmt->execute($params);

    /*
     * The type breakdown from the newest scan in the window, not a series.
     *
     * What a disk is made of changes over months, not between two scans an hour
     * apart, so a line per type would be nine flat lines. The interesting form
     * is the composition right now, and one row of it is far cheaper than
     * carrying every scan's copy of it to the browser to draw one pie.
     */
    $rows = $stmt->fetchAll();
    $types = [];
    $extensions = [];
    $typeScan = null;
    $typeHistory = [];

    if ($rows !== []) {
        $latest = (int)end($rows)['id'];

        $byType = $pdo->prepare(
            'SELECT type, bytes, files FROM scan_file_types WHERE scan_id = ? ORDER BY bytes DESC'
        );
        $byType->execute([$latest]);
        $types = $byType->fetchAll();
        $typeScan = $types === [] ? null : $latest;

        /*
         * What fell into `other`, for the newest scan only.
         *
         * A history of these would be a table with forty columns and no
         * question attached to it. The composition now is what turns an
         * unactionable slice into a list of things to go and look at.
         */
        $byExtension = $pdo->prepare(
            'SELECT extension, bytes, files FROM scan_extensions
             WHERE scan_id = ? ORDER BY bytes DESC'
        );
        $byExtension->execute([$latest]);
        $extensions = $byExtension->fetchAll();

        /*
         * One row per scan per type, for the growth chart.
         *
         * Returned long rather than pivoted into a column per type: the set of
         * types is fixed and small, but a scan that saw no `.mp4` has no row at
         * all, and a pivot in SQL would turn that absence into a zero. The
         * difference matters at the left-hand end of the chart, where a type
         * that did not exist yet should start where it started rather than
         * running along the axis from the beginning of time.
         */
        $ids = array_column($rows, 'id');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $history = $pdo->prepare(
            "SELECT f.scan_id, s.started_at, f.type, f.bytes
               FROM scan_file_types f JOIN scans s ON s.id = f.scan_id
              WHERE f.scan_id IN ($marks)
              ORDER BY s.started_at"
        );
        $history->execute($ids);
        $typeHistory = $history->fetchAll();
    }

    return [
        'rows' => $rows,
        'categories' => $categories,
        'file_types' => $types,
        'file_type_history' => $typeHistory,
        'other_extensions' => $extensions,
        // Null when the newest scan predates the tally, so the panel can say
        // "not measured yet" rather than drawing an empty pie as though the
        // account contained nothing.
        'file_types_scan_id' => $typeScan,
        /*
         * Enough for the Scan now button to work from this page.
         *
         * It used to live only beside the offender table and read its state
         * from the overview payload, which the storage view does not fetch -
         * so putting the button here without this would give a control that
         * never knew whether a scan was already running.
         */
        'scan_running' => scanRunning($pdo),
        'last_scan' => $pdo->query(
            'SELECT id, started_at, finished_at, duration_ms, files_seen, dirs_seen, bytes_seen, error
               FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 1'
        )->fetch() ?: null,
    ];
}

/**
 * A scan in flight, or null.
 *
 * An hour's grace, because a scan that died without writing a finish time would
 * otherwise leave the button disabled for ever, and "the button is stuck" is a
 * worse failure than "you can start a second scan an hour later".
 */
function scanRunning(PDO $pdo): ?array
{
    $row = $pdo->query(
        'SELECT id, started_at FROM scans WHERE finished_at IS NULL ORDER BY id DESC LIMIT 1'
    )->fetch();

    return $row && (time() - (int)$row['started_at']) < 3600
        ? ['started_at' => (int)$row['started_at']]
        : null;
}

/**
 * The top offenders, with how much each has grown.
 *
 * Growth is the column worth having. "This is the biggest file" is static
 * knowledge you learn once; "this is the biggest file and it doubled last
 * night" is the thing you act on.
 */
function offenders(array $config, PDO $pdo, bool $signedIn): array
{
    $view = (string)($_GET['view'] ?? $_GET['sort'] ?? 'size');
    if (!in_array($view, GROWTH_VIEWS, true)) {
        // 'bytes' was the old name for the default, and old bookmarks and any
        // half-updated index.js still send it.
        $view = $view === 'bytes' ? 'size' : 'size';
    }

    $filters = ['category' => $_GET['category'] ?? '', 'kind' => $_GET['kind'] ?? ''];
    $limit = clampInt($_GET['limit'] ?? 25, 1, 200);

    $window = growthWindow($pdo, $view);

    if ($window['now'] === null || ($window['before'] === null && $view !== 'size')) {
        return [
            'rows' => [], 'view' => $view, 'scan_id' => $window['now'],
            'note' => growthSpanNote($window, $view), 'window' => $window,
        ];
    }

    $rows = match ($view) {
        'new' => newlyAppeared($pdo, $window, $filters, $limit),
        'deleted' => deletedSince($pdo, $window, $filters, $limit),
        'size' => largestPaths($pdo, $window, $filters, $limit),
        default => grewMost($pdo, $window, $filters, $limit),
    };

    /*
     * Whether the path has been deleted since the scan that found it.
     *
     * The list is drawn from a scan, so a file stays in it until the next one
     * runs - which is right, it is a record of what the scan saw. What is not
     * right is still offering to delete it: the button came back on every
     * refresh and the second press answered "path does not exist", which reads
     * as a fault rather than as "already done".
     *
     * The deleted view has already worked this out for itself, and by asking
     * the filesystem rather than the audit log, so it is left alone.
     */
    if ($view !== 'deleted') {
        $rows = markDeleted($pdo, $rows, (int)$window['now']);
    }

    $viewableCategories = $config['viewable_categories'] ?? [];
    $rows = array_map(
        function (array $row) use ($config, $signedIn, $viewableCategories, $view): array {
            /*
             * Whether each row can actually be read, decided here rather than
             * guessed. The dashboard used to offer View for anything in a
             * viewable category, and a rotated log keeps the word "log" in its
             * name - so git.lewismoten.com-ssl_log-Aug-2026.gz got a button
             * that could only ever answer with binary.
             *
             * A row in the deleted view is never viewable: the file is the one
             * thing it is documented as not being there.
             */
            $row['viewable'] = $signedIn
                && $view !== 'deleted'
                && ($row['kind'] ?? '') === 'file'
                && ($row['deleted_at'] ?? null) === null
                && in_array($row['category'] ?? null, $viewableCategories, true)
                && compressedExtension((string)$row['path']) === null
                && looksLikeText((string)$row['path']);

            return presentPath($config, $row, $signedIn);
        },
        $rows
    );

    return [
        'rows' => $rows,
        'view' => $view,
        'scan_id' => (int)$window['now'],
        'note' => growthSpanNote($window, $view),
        'window' => $window,
        // Kept so an index.js that has not been uploaded yet still works.
        'sort' => $view === 'size' ? 'bytes' : 'growth',
    ];
}

/** The plain "what is biggest" list, which is still the one people open first. */
function largestPaths(PDO $pdo, array $window, array $filters, int $limit): array
{
    $where = ['now.scan_id = :now'];
    $params = [':now' => $window['now'], ':before' => $window['before'] ?? -1, ':limit' => $limit];
    applyFilters($filters, $where, $params);

    return runGrowthQuery(
        $pdo,
        'SELECT p.id AS path_id, p.path, p.kind, p.category,
                now.bytes, now.files, now.links,
                COALESCE(now.bytes - was.bytes, 0) AS growth,
                COALESCE(now.files - was.files, 0) AS growth_files
         FROM path_sizes now
         JOIN paths p ON p.id = now.path_id
         LEFT JOIN path_sizes was ON was.path_id = now.path_id AND was.scan_id = :before
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY now.bytes DESC LIMIT :limit',
        $params
    );
}

/**
 * Marks rows whose file has been deleted through this dashboard since the scan.
 *
 * Only deletes recorded at or after the scan started count. A file removed
 * before the scan cannot be the one the scan found, and a name that has since
 * been recreated is genuinely deletable again.
 *
 * Emptying a category writes one summary row rather than one per file, so that
 * case is matched on the category instead. Less precise, and the alternative is
 * a row per file in an audit log meant to be readable.
 */
function markDeleted(PDO $pdo, array $rows, int $scanId): array
{
    if ($rows === []) {
        return $rows;
    }

    $startedAt = (int)$pdo->query("SELECT started_at FROM scans WHERE id = $scanId")->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT MAX(a.at) FROM actions a
          WHERE a.outcome = \'ok\' AND a.at >= :started
            AND ((a.action = \'delete_file\' AND a.target = :path)
              OR (a.action = \'empty_category\' AND a.target = :category))'
    );

    foreach ($rows as $i => $row) {
        $stmt->execute([
            ':started' => $startedAt,
            ':path' => (string)$row['path'],
            ':category' => (string)($row['category'] ?? ''),
        ]);
        $at = $stmt->fetchColumn();
        $rows[$i]['deleted_at'] = $at === false || $at === null ? null : (int)$at;
    }

    return $rows;
}

/** One path's size over time, which is the chart that shows a leak. */
function pathHistory(array $config, PDO $pdo, bool $signedIn): array
{
    $pathId = clampInt($_GET['path_id'] ?? 0, 1, PHP_INT_MAX);
    [$from, $to] = window();

    $meta = $pdo->prepare('SELECT id, path, kind, category FROM paths WHERE id = ?');
    $meta->execute([$pathId]);
    $path = $meta->fetch();
    if (!$path) {
        throw new ActionRefused('unknown path');
    }

    $stmt = $pdo->prepare(
        'SELECT s.started_at, ps.bytes, ps.files
         FROM path_sizes ps JOIN scans s ON s.id = ps.scan_id
         WHERE ps.path_id = ? AND s.started_at BETWEEN ? AND ?
         ORDER BY s.started_at'
    );
    $stmt->execute([$pathId, $from, $to]);

    return [
        'path' => presentPath($config, $path, $signedIn),
        'rows' => $stmt->fetchAll(),
    ];
}

function alertList(array $config, PDO $pdo, bool $signedIn): array
{
    $limit = clampInt($_GET['limit'] ?? 50, 1, 500);
    $stmt = $pdo->prepare(
        'SELECT a.id, a.raised_at, a.rule, a.severity, a.subject, a.detail,
                a.emailed_at, a.email_error, a.email_id, a.path_id, a.incident_id,
                a.acknowledged_at, a.notification_disposition, a.notification_reason, a.notification_muted_until,
                a.ai_summary, a.ai_verdict, a.ai_reason, a.ai_advice, a.ai_at, a.silenced,
                p.path, p.kind
         FROM alerts a LEFT JOIN paths p ON p.id = a.path_id
         ORDER BY a.raised_at DESC LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'rows' => array_map(
            fn(array $row): array => presentAlert($config, $row, $signedIn),
            $stmt->fetchAll()
        ),
        'mutes' => $signedIn && accessAllows($config, 'write') ? alertMutes($pdo) : [],
        'marketplace_recommendations' => $signedIn && accessAllows($config, 'write')
            ? marketplaceAlertRecommendations($config, $pdo) : [],
    ];
}

/**
 * One alert email, exactly as it was sent.
 *
 * Signed-in only and never masked. The body carries paths, addresses and
 * whatever a log line happened to say - it is the same content the alert page
 * refuses to show a signed-out visitor, and reconstructing it here for one
 * would be the same leak by a different door.
 */
function alertEmailView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'notes')) {
        throw new ActionRefused('sign in to read a sent message');
    }

    $id = clampInt($_GET['id'] ?? 0, 1, PHP_INT_MAX);
    $stmt = $pdo->prepare('SELECT * FROM alert_emails WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row === false) {
        throw new ActionRefused(
            'that message was not kept. Only messages sent since this feature existed '
            . 'are stored; older alerts say they were emailed and cannot show what was '
            . 'said, which is true and is why it says so rather than showing nothing.'
        );
    }

    /*
     * Which alerts went out together in it.
     *
     * The subject of a batched message names a count rather than a cause, so
     * the list is the only thing that says what the message was actually about.
     */
    $ids = array_values(array_filter(array_map(
        intval(...),
        explode(',', (string)$row['alert_ids'])
    )));

    $covered = [];
    if ($ids !== []) {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $others = $pdo->prepare(
            "SELECT id, raised_at, severity, subject FROM alerts WHERE id IN ({$marks})
              ORDER BY raised_at"
        );
        $others->execute($ids);
        $covered = $others->fetchAll();
    }

    return [
        'id' => (int)$row['id'],
        'at' => (int)$row['at'],
        'subject' => (string)$row['subject'],
        'body' => (string)$row['body'],
        'recipients' => (string)$row['recipients'],
        'headers' => (string)($row['headers'] ?? ''),
        'sent' => (bool)$row['sent'],
        'error' => $row['error'],
        'covered' => $covered,
    ];
}

/** The audit trail. Only for people who could have caused an entry in it. */
/**
 * One incident record, in full.
 *
 * Signed-in only, and refused rather than masked. Everywhere else a signed-out
 * visitor sees the shape of a problem with the names taken out, which works
 * because the sizes are the interesting part. It does not work here: this
 * record holds other accounts' command lines, and while redactSecrets() strips
 * the password arguments it recognises, "the ones it recognises" is not a
 * promise worth making to the open internet.
 */
/**
 * One level of the treemap, measured now rather than remembered.
 *
 * Signed-in only, and refused rather than masked. Everywhere else a signed-out
 * visitor sees shapes with the names removed, which works because the sizes are
 * the interesting part. It does not work here: this walks the filesystem on
 * demand at a path the caller chooses, and an anonymous visitor able to map a
 * private directory - or to ask for an expensive walk over and over - is not
 * something masking fixes.
 */
/**
 * The last root-side disk walk: filesystems, directories, and hidden space.
 *
 * Signed-in only. It names every large directory on the machine including other
 * accounts' homes, which is exactly the information the host collector exists
 * to gather and exactly the information not to hand to an anonymous visitor.
 */
/**
 * What this account and this host can actually run.
 *
 * Read-only and cheap: it reports the last probe rather than running one. The
 * probe shells out, and putting that behind a page refresh would spawn a shell
 * every ten seconds to answer a question whose answer changes when somebody
 * installs a package.
 */
function capabilitiesView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see what this account can run');
    }

    $host = null;
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        // No host database is a normal state, handled inside the report.
    }

    $report = capabilityReport($config, $pdo, $host);
    $report['tasks'] = pendingHostTasks($config, $pdo, $host);

    /*
     * When the host collector last ran at all.
     *
     * Without this the page can only say "waiting for the next run", which is
     * true of a collector that runs every five minutes and true of one that has
     * never been installed - and those need completely different sentences.
     * Seventeen hours of "waiting for the next run" is the second case wearing
     * the first one's words.
     */
    $lastRun = 0;
    if ($host !== null) {
        try {
            $lastRun = (int)$host->query('SELECT MAX(taken_at) FROM host_samples')->fetchColumn();
        } catch (Throwable) {
            $lastRun = 0;
        }
    }
    $report['host_last_run'] = $lastRun ?: null;
    $report['host_cron_line'] = hostCronLine();

    return $report;
}

/**
 * The mount table as the kernel describes it, and the CageFS view of it.
 */
/**
 * When programs appeared, vanished or changed version.
 *
 * Its own action rather than a field on `capabilities`: that one is a snapshot
 * the dashboard polls, while this answers to the range selector and would make
 * every poll re-read a year of history to redraw a table showing now.
 */
function capabilityChangesView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see what has changed');
    }

    $host = null;
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        // Absent host database is normal; the account history stands alone.
    }

    [$from, $to] = window();

    return capabilityChanges($config, $pdo, $host, $from, $to);
}

/**
 * What is open, who is connected, and what the last capture found.
 *
 * All of it from the host collector, because none of it is answerable from
 * inside a cage: the account's /proc/net shows its own sockets, `ss` shows what
 * the account may see, and neither is the machine.
 */
function networkView(array $config, PDO $account, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the network');
    }

    $host = null;
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        $host = null;
    }

    if ($host === null) {
        return [
            'available' => false,
            'reason' => 'This needs the host collector. From inside a cage the account sees '
                . 'only its own sockets, which is not the machine.',
            'filters' => captureFilters(),
        ];
    }

    /*
     * Captures are read before anything else, and independently.
     *
     * They survive pruning - they hang off no sample - so a machine whose
     * readings have aged out still has its captures. Returning early here
     * because there is no *sample* hid them completely, which is how a capture
     * somebody had just taken could vanish on a reload.
     */
    // Passing the account's database is what applies your own labels here.
    $capture = latestCapture($host, $account);

    $latest = (int)$host->query('SELECT MAX(id) FROM host_samples')->fetchColumn();
    if ($latest <= 0) {
        return [
            'available' => false,
            'reason' => 'No host readings yet. Anything captured is still below.',
            'filters' => captureFilters(),
            'capture' => $capture,
            'pending' => pendingCaptures($account, $host),
            'max_seconds' => CAPTURE_MAX_SECONDS,
        ];
    }

    $ports = $host->prepare('SELECT * FROM host_ports WHERE sample_id = ? ORDER BY port');
    $ports->execute([$latest]);
    $portRows = array_map(function (array $row): array {
        $row['addresses'] = array_values(array_filter(explode(' ', (string)$row['addresses'])));
        $row['families'] = array_values(array_filter(explode(' ', (string)$row['families'])));
        $row['public'] = (bool)$row['public'];
        $row['service'] = portService((int)$row['port'], (string)$row['protocol']);
        return $row;
    }, $ports->fetchAll());

    /*
     * Interface rates need two readings, like every other counter here.
     *
     * A counter since boot says nothing on its own: a machine up for a year
     * with a terabyte through it and one up for an hour with a terabyte through
     * it are the same number and completely different situations.
     */
    $interfaces = interfaceRates($host, $latest);

    $peers = $host->prepare(
        'SELECT * FROM host_peers WHERE sample_id = ? ORDER BY connections DESC LIMIT 40'
    );
    $peers->execute([$latest]);

    $labels = loadLabels($account);
    $portRows = applyLabels($labels, $portRows, 'port', 'port');

    return [
        'available' => true,
        'reason' => '',
        'labelled' => $labels['count'],
        'taken_at' => (int)$host->query('SELECT taken_at FROM host_samples WHERE id = ' . $latest)->fetchColumn(),
        'ports' => $portRows,
        'interfaces' => $interfaces,
        // The name the internet gives an address, beside the name you gave it.
        'peers' => applyRdns($account, applyLabels($labels, array_map(
            function (array $row): array {
                $row['private'] = (bool)$row['private'];
                return $row;
            },
            $peers->fetchAll()
        ), 'address', 'address')),
        'capture' => $capture,
        'filters' => captureFilters(),
        'pending' => pendingCaptures($account, $host),
        'max_seconds' => CAPTURE_MAX_SECONDS,
    ];
}

/**
 * Bytes and packets per second per interface, between the last two readings.
 *
 * Errors and drops are reported as totals rather than rates, because they are
 * usually zero and a rate of zero says less than a count of zero. A non-zero
 * drop count is the thing worth seeing, whatever its rate.
 *
 * @return list<array>
 */
function interfaceRates(PDO $host, int $latest): array
{
    // The table is keyed on (sample_id, name) and has no id of its own.
    $previous = (int)$host->query(
        'SELECT MAX(sample_id) FROM host_interfaces WHERE sample_id < ' . $latest
    )->fetchColumn();

    $rows = $host->prepare('SELECT * FROM host_interfaces WHERE sample_id = ?');
    $rows->execute([$latest]);
    $now = $rows->fetchAll();

    $before = [];
    $seconds = 0;
    if ($previous > 0) {
        $rows->execute([$previous]);
        foreach ($rows->fetchAll() as $row) {
            $before[$row['name']] = $row;
        }

        $times = $host->prepare('SELECT taken_at FROM host_samples WHERE id IN (?, ?)');
        $times->execute([$previous, $latest]);
        $at = array_map('intval', $times->fetchAll(PDO::FETCH_COLUMN));
        $seconds = count($at) === 2 ? abs($at[1] - $at[0]) : 0;
    }

    $out = [];
    foreach ($now as $row) {
        $was = $before[$row['name']] ?? null;
        $rate = function (string $key) use ($row, $was, $seconds): ?float {
            if ($was === null || $seconds <= 0) {
                return null;
            }
            $delta = (int)$row[$key] - (int)$was[$key];
            // A counter that went backwards is a reboot or a 32-bit wrap, and a
            // negative rate is never a real reading.
            return $delta < 0 ? null : round($delta / $seconds, 1);
        };

        $out[] = [
            'name' => $row['name'],
            'loopback' => $row['name'] === 'lo',
            'up' => $row['up'] === null ? null : (bool)$row['up'],
            'speed_mbit' => $row['speed_mbit'] === null ? null : (int)$row['speed_mbit'],
            'rx_rate' => $rate('rx_bytes'),
            'tx_rate' => $rate('tx_bytes'),
            'rx_packet_rate' => $rate('rx_packets'),
            'tx_packet_rate' => $rate('tx_packets'),
            'rx_bytes' => (int)$row['rx_bytes'],
            'tx_bytes' => (int)$row['tx_bytes'],
            'errors' => (int)$row['rx_errors'] + (int)$row['tx_errors'],
            'dropped' => (int)$row['rx_dropped'] + (int)$row['tx_dropped'],
            'seconds' => $seconds,
        ];
    }

    usort($out, fn(array $a, array $b): int =>
        ($a['loopback'] <=> $b['loopback'])
        ?: (($b['rx_rate'] ?? 0) + ($b['tx_rate'] ?? 0) <=> ($a['rx_rate'] ?? 0) + ($a['tx_rate'] ?? 0)));

    return $out;
}

/** One capture with its summary rows, the newest by default. */
function latestCapture(PDO $host, ?PDO $account = null, ?int $id = null): ?array
{
    try {
        if ($id === null) {
            $capture = $host->query('SELECT * FROM host_captures ORDER BY id DESC LIMIT 1')->fetch();
        } else {
            $stmt = $host->prepare('SELECT * FROM host_captures WHERE id = ?');
            $stmt->execute([$id]);
            $capture = $stmt->fetch();
        }
    } catch (Throwable) {
        return null;
    }
    if (!$capture) {
        return null;
    }

    $rows = $host->prepare(
        'SELECT * FROM host_capture_rows WHERE capture_id = ? ORDER BY packets DESC'
    );
    $rows->execute([(int)$capture['id']]);

    $grouped = [
        'peers' => [], 'ports' => [], 'names' => [], 'flags' => [],
        'asked' => [], 'visited' => [],
    ];
    foreach ($rows->fetchAll() as $row) {
        $kind = (string)$row['kind'];
        if (!isset($grouped[$kind])) {
            continue;
        }

        $entry = [
            'label' => $row['label'],
            'packets' => (int)$row['packets'],
            'in_packets' => (int)($row['in_packets'] ?? 0),
            'out_packets' => (int)($row['out_packets'] ?? 0),
            'in_bytes' => (int)($row['in_bytes'] ?? 0),
            'out_bytes' => (int)($row['out_bytes'] ?? 0),
            'opened' => (int)($row['opened'] ?? 0),
            'bytes' => (int)($row['bytes'] ?? 0),
        ];
        if ($kind === 'peers') {
            $entry['address'] = $row['label'];
            $entry['private'] = (bool)$row['private'];
        }
        if ($kind === 'ports') {
            $entry['port'] = (int)$row['label'];
            $entry['service'] = portService((int)$row['label']);
        }

        /*
         * The rollups carry their sub-kind at the front of the label.
         *
         * "A git.example.com" and "page GET /admin" - split back apart here so
         * the browser gets two fields rather than a string it has to parse.
         */
        if ($kind === 'asked' || $kind === 'visited') {
            $parts = explode(' ', (string)$row['label'], 2);
            $entry['how'] = $parts[0] ?? '';
            $entry['what'] = $parts[1] ?? '';
        }
        $grouped[$kind][] = $entry;
    }

    if ($account !== null) {
        $labels = loadLabels($account);
        $grouped['peers'] = applyRdns($account,
            applyLabels($labels, $grouped['peers'], 'address', 'address'));
        $grouped['ports'] = applyLabels($labels, $grouped['ports'], 'port', 'port');
    }

    $capture['ok'] = (bool)$capture['ok'];
    $capture['hit_limit'] = (bool)$capture['hit_limit'];
    return $capture + $grouped
        + ['filter_label' => captureFilter((string)$capture['filter'])['label'] ?? ''];
}

/**
 * Every capture taken, so two can be compared.
 *
 * A single report answers "what is happening"; two an hour apart answer "is it
 * getting worse", which is usually the actual question. The list carries only
 * the headline numbers - the detail rows are fetched for the one being looked
 * at, because forty captures of forty peers each is a lot of rows to send in
 * order to draw a list of dates.
 */
function capturesView(array $config, PDO $account, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see captures');
    }

    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        $host = null;
    }
    if ($host === null) {
        return ['available' => false, 'reason' => 'This needs the host collector.', 'rows' => []];
    }

    try {
        $rows = $host->query(
            'SELECT id, taken_at, interface, filter, seconds, packets, bytes,
                    in_packets, out_packets, in_bytes, out_bytes,
                    distinct_peers, hit_limit, ok, reason
               FROM host_captures ORDER BY taken_at DESC LIMIT 200'
        )->fetchAll();
    } catch (Throwable) {
        return [
            'available' => false, 'rows' => [],
            'reason' => 'The host database predates packet captures. Upload host-cron.php '
                . 'and lib/, then ask for one.',
        ];
    }

    foreach ($rows as &$row) {
        $row['ok'] = (bool)$row['ok'];
        $row['hit_limit'] = (bool)$row['hit_limit'];
        $row['filter_label'] = captureFilter((string)$row['filter'])['label'] ?? $row['filter'];
        $seconds = (float)$row['seconds'];
        // A rate, because the whole point of a list of captures is comparing
        // them, and two samples of different lengths are not comparable raw.
        $row['packets_per_second'] = $seconds > 0 ? round((int)$row['packets'] / $seconds, 1) : null;
        $row['bytes_per_second'] = $seconds > 0 ? round((int)$row['bytes'] / $seconds) : null;
    }
    unset($row);

    $wanted = isset($_GET['id']) ? clampInt($_GET['id'], 1, PHP_INT_MAX) : null;

    return [
        'available' => true,
        'reason' => '',
        'rows' => $rows,
        'capture' => latestCapture($host, $account, $wanted),
        'filters' => captureFilters(),
    ];
}

/**
 * The URL checks, and the history of whichever one is being looked at.
 *
 * The list is cheap and always sent; the history is only for the selected
 * check, because a month of minute-by-minute results for forty checks is a
 * great deal of data to move in order to draw one chart.
 */
/* ------------------------------------------------------------- domains -- */

function domainsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the watched domains');
    }

    $rows = domainList($pdo);
    $selected = isset($_GET['id']) ? clampInt($_GET['id'], 1, PHP_INT_MAX) : null;

    $storedWhois = null;
    if ($selected !== null) {
        $stmt = $pdo->prepare('SELECT whois FROM domains WHERE id = ?');
        $stmt->execute([$selected]);
        $storedWhois = $stmt->fetchColumn() ?: null;
    }

    $whois = domainWhoisAvailability($config);

    return [
        'rows' => $rows,
        'selected' => $selected,
        'whois' => $storedWhois,
        'notes' => array_combine(
            ['expired', 'urgent', 'soon', 'ok', 'unknown', 'error'],
            array_map('domainStateNote', ['expired', 'urgent', 'soon', 'ok', 'unknown', 'error'])
        ),
        'max' => DOMAIN_MAX,
        'default_warn_days' => DOMAIN_WARN_DAYS,
        'have_whois' => $whois['account'],
        'host_has_whois' => $whois['host'],
        'missing' => !$whois['account'] && !$whois['host'] ? missingCommand($config, 'whois') : null,
        'queue' => domainQueueState($config, $pdo),
    ];
}

/**
 * WHOIS may be deliberately absent from the web account's CageFS view while
 * remaining available to the root collector.  Keep the two answers separate:
 * calling that installation "missing" sends an operator looking for a package
 * that is already installed.
 *
 * @return array{account: bool, host: bool}
 */
function domainWhoisAvailability(array $config): array
{
    $account = whichBinary('whois') !== null;
    $hostHas = false;

    try {
        $host = hostDb($config, false);
        if ($host !== null) {
            $stmt = $host->prepare(
                'SELECT path, executable FROM host_capabilities WHERE name = ? LIMIT 1'
            );
            $stmt->execute(['whois']);
            $row = $stmt->fetch();
            $hostHas = $row !== false && (string)($row['path'] ?? '') !== ''
                && (bool)($row['executable'] ?? false);
        }
    } catch (Throwable) {
        // The host database is optional. Its absence is not evidence that the
        // command is absent, only that the collector has not reported yet.
    }

    return ['account' => $account, 'host' => $hostHas];
}

/**
 * How many domain lookups are waiting, and how fast they drain.
 *
 * Shown because a watch list of twelve on a one-per-run budget takes an hour to
 * work through, and a page that simply showed eleven unchecked domains would
 * look broken rather than patient.
 */
function domainQueueState(array $config, PDO $pdo): array
{
    $now = time();
    $due = 0;
    foreach (domainList($pdo) as $row) {
        if ($row['due_at'] <= $now) {
            $due++;
        }
    }

    return [
        'due' => $due,
        'per_run' => externalBudget($config, 'whois'),
    ];
}

/**
 * A one-off whois lookup for a name that is not being watched.
 *
 * Its own action because the question is different: "is this available" and
 * "when does mine expire" want the same protocol and completely different
 * answers, and running one should not add a domain to the watch list.
 */
/**
 * The host collector's stored answer to a question, if it has one.
 *
 * Returns null when there is no host database at all, which is different from
 * "asked and not answered yet" and is reported differently.
 */
function hostAnswerFor(array $config, string $verb, string $argument): ?array
{
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        return null;
    }
    if ($host === null) {
        return null;
    }

    return hostAnswer($host, $verb, $argument);
}

/**
 * Leaves a request for root's collector, and says what to expect.
 *
 * Deliberately does not block. The collector runs on its own schedule, and a
 * page that waited for it would hang for up to five minutes; the pending
 * banner and its countdown are how the wait is shown.
 */
function askHost(array $config, PDO $pdo, string $verb, string $argument, ?array $previous): array
{
    $at = requestHostTask($pdo, hostAskTask($verb, $argument), currentAccountName());

    return [
        'asked_at' => $at,
        'pending' => true,
        // A previous attempt that failed on the host too is worth repeating,
        // because "the host has not answered yet" and "the host cannot answer"
        // need different responses from the reader.
        'previous_error' => $previous === null ? null : $previous['error'],
    ];
}

function whoisView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to run a whois lookup');
    }

    $name = strtolower(trim((string)($_GET['name'] ?? '')));
    if ($name === '') {
        return [
            'asked' => false,
            'have_whois' => whichBinary('whois') !== null,
            'missing' => whichBinary('whois') === null ? missingCommand($config, 'whois') : null,
            'terms' => whoisTerms(),
        ];
    }

    $answer = whoisLookup($name);

    /*
     * When the account cannot run whois, ask root's collector instead.
     *
     * whois is routinely absent from inside a cage and present on the host, and
     * "not installed here" is a dead end rather than an answer. A stored answer
     * is used if there is one; otherwise a request is left and the page says so.
     */
    $askedFor = null;
    if (!$answer['ok'] && str_contains((string)$answer['error'], 'not installed')) {
        $fromHost = hostAnswerFor($config, 'whois', $name);
        if ($fromHost !== null && $fromHost['ok']) {
            $answer = ['ok' => true, 'text' => (string)$fromHost['text'], 'error' => null];
            $askedFor = ['answered_at' => $fromHost['answered_at']];
        } else {
            $askedFor = askHost($config, $pdo, 'whois', $name, $fromHost);
        }
    }

    $parsed = $answer['ok'] ? parseWhois($answer['text']) : [];

    return [
        'asked' => true,
        'name' => $name,
        'ok' => $answer['ok'],
        'error' => $answer['error'],
        'from_host' => $askedFor,
        // EPP status codes in plain language, one row per meaning; see statusList.
        'status_plain' => statusList((string)($parsed['status'] ?? '')),
        'text' => substr($answer['text'], 0, WHOIS_MAX_BYTES),
        'parsed' => $parsed,
        'have_whois' => whichBinary('whois') !== null,
        // So the page can offer to start watching it without a second lookup.
        'watched' => (bool)$pdo->query(
            'SELECT COUNT(*) FROM domains WHERE name = ' . $pdo->quote($name)
        )->fetchColumn(),
    ];
}

function doDomainSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = domainSave(
        $pdo,
        (string)($_POST['name'] ?? ''),
        (int)($_POST['warn_days'] ?? DOMAIN_WARN_DAYS),
        (int)($_POST['interval_seconds'] ?? 604800)
    );

    logAction($pdo, 'domain_save', (string)($_POST['name'] ?? ''), 'ok', 0, 0, null);

    // Looked up straight away, so the expiry is on screen rather than pending.
    domainCheckDue($pdo, $id, null, $config);

    return ['ok' => true, 'id' => $id] + domainsView($config, $pdo, true);
}

function doDomainDelete(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $stmt = $pdo->prepare('DELETE FROM domains WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such domain');
    }

    logAction($pdo, 'domain_delete', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true] + domainsView($config, $pdo, true);
}

function doDomainCheck(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $result = domainCheckDue($pdo, $id, null, $config);

    if ($result['ran'] === 0) {
        throw new ActionRefused('no such domain');
    }

    return ['ok' => true, 'result' => $result['rows'][0]] + domainsView($config, $pdo, true);
}

/* ------------------------------------------------------------ calendar -- */

function calendarView(array $config, PDO $pdo, bool $signedIn): array
{
    $calendar = calendarEntries($config, $pdo);
    if (!$signedIn) {
        // Public calendar is only machine-derived facts. Personal reminders
        // and their notes remain visible only to signed-in users.
        $calendar['days'] = array_filter(array_map(
            fn(array $rows): array => array_values(array_filter(
                $rows, fn(array $row): bool => ($row['kind'] ?? '') !== 'event'
            )),
            $calendar['days']
        ));
        $calendar['upcoming'] = array_values(array_filter(
            $calendar['upcoming'], fn(array $row): bool => ($row['kind'] ?? '') !== 'event'
        ));
    }

    return $calendar + [
        'events' => $signedIn ? $pdo->query('SELECT * FROM events ORDER BY at')->fetchAll() : [],
        'repeats' => eventRepeats(),
        'now' => time(),
        'writable' => $signedIn && accessAllows($config, 'write'),
    ];
}

function doEventSave(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = eventSave($pdo, [
        'id' => $_POST['id'] ?? 0,
        'title' => $_POST['title'] ?? '',
        'at' => $_POST['at'] ?? '',
        'repeats' => $_POST['repeats'] ?? 'once',
        'link' => $_POST['link'] ?? '',
        'note' => $_POST['note'] ?? '',
        'priority' => ($_POST['priority'] ?? '0') === '1',
    ]);

    logAction($pdo, 'event_save', (string)($_POST['title'] ?? ''), 'ok', 0, 0, null);
    return ['ok' => true, 'id' => $id] + calendarView([], $pdo, true);
}

function doEventDelete(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such event');
    }

    logAction($pdo, 'event_delete', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true] + calendarView([], $pdo, true);
}

/**
 * Marks an event dealt with, or puts it back.
 *
 * Reversible on purpose. "Resolved" is a claim somebody makes and sometimes
 * makes wrongly, and a one-way switch on a reminder about money is a poor idea.
 */
function doEventResolve(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $resolved = ($_POST['resolved'] ?? '1') === '1';

    $stmt = $pdo->prepare('UPDATE events SET resolved_at = ? WHERE id = ?');
    $stmt->execute([$resolved ? time() : null, $id]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such event');
    }

    logAction($pdo, 'event_resolve', (string)$id, 'ok', 0, 0,
        $resolved ? 'marked dealt with' : 'reopened');

    return ['ok' => true] + calendarView([], $pdo, true);
}

/**
 * One request, made now, reported in full.
 *
 * The monitor answers "is it still working"; this answers "what exactly does it
 * send", which is the question while you are setting a check up or arguing with
 * a cache. Nothing is stored - it is a look, not a measurement.
 *
 * The body comes back as text and is rendered by the browser as text. It is
 * never inserted as markup: fetching an arbitrary page and putting its HTML
 * into this document would hand that page the dashboard's session.
 */
function fetchView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to make a request');
    }

    // Typed by a person, so it may well have no scheme on it.
    $url = tidyUrl((string)($_GET['url'] ?? ''));
    if ($url === '') {
        return ['asked' => false];
    }

    $method = strtoupper((string)($_GET['method'] ?? 'GET'));
    $follow = ($_GET['follow'] ?? '1') === '1';

    $started = microtime(true);
    $result = urlWatchFetch($url, ['method' => $method, 'follow_redirects' => $follow], $config);
    $ms = (int)round((microtime(true) - $started) * 1000);

    if ($result['error'] !== null && $result['status'] === 0) {
        return [
            'asked' => true, 'url' => $url, 'ok' => false,
            'error' => $result['error'], 'ms' => $ms,
        ];
    }

    $headers = (string)($result['headers'] ?? '');
    $type = urlWatchHeader($headers, 'content-type') ?? '';
    $body = (string)$result['body'];

    logAction($pdo, 'fetch_url', $url, 'ok', 0, 0, $method . ' ' . $result['status']);

    $isHtml = (bool)preg_match('#^(text/html|application/xhtml)#i', $type);
    $isJson = (bool)preg_match('#json#i', $type);

    $out = [
        'asked' => true,
        'url' => $url,
        'ok' => true,
        'status' => $result['status'],
        'ms' => $ms,
        'method' => $result['method'] ?? $method,
        'hops' => $result['hops'] ?? 0,
        'landed' => $result['landed'] ?? null,
        'headers' => explainHeaderBlock($pdo, $headers),
        'raw_headers' => $headers,
        'body' => $body,
        'bytes' => strlen($body),
        'content_type' => $type,
        /*
         * Whether the browser could reasonably show this as a page.
         *
         * Decided from the declared type rather than by sniffing: the preview
         * is a convenience and guessing wrong turns it into a way to render
         * something unexpected inside a sandboxed frame.
         */
        'previewable' => $isHtml || (bool)preg_match('#^text/plain#i', $type),
        'looks_json' => $isJson,
        'looks_html' => $isHtml,
    ];

    if ($isHtml) {
        $out['meta'] = pageMeta($body);
        $out['linked_data'] = pageLinkedData($body);
    }

    if ($isJson) {
        $out['json'] = formatJson($body);
    }

    /*
     * Parked for the preview frame, which loads it from its own URL.
     *
     * Only for things a frame will actually be offered, so a JSON API response
     * is never written to disk here.
     */
    if ($out['previewable']) {
        $out['preview_token'] = storeFetchBody($pdo, $url, $type, $body);
    }

    return $out;
}

/**
 * Keeps one fetched body for the preview frame to collect.
 *
 * Pruned on the way in rather than by the cron: this table is a scratch buffer
 * of somebody else's pages, and the right number of them to be holding is
 * however many are on screen. An hour is far longer than a preview is looked
 * at and short enough that nothing accumulates.
 */
function storeFetchBody(PDO $pdo, string $url, string $type, string $body): ?string
{
    // Well beyond any page worth previewing, and a limit on what one request
    // can put in the database.
    if (strlen($body) > 4 * 1024 * 1024) {
        return null;
    }

    $pdo->prepare('DELETE FROM fetch_bodies WHERE at < ?')->execute([time() - 3600]);

    $token = bin2hex(random_bytes(16));
    $pdo->prepare(
        'INSERT INTO fetch_bodies (token, url, content_type, body, at) VALUES (?, ?, ?, ?, ?)'
    )->execute([$token, $url, $type, $body, time()]);

    return $token;
}

/**
 * A response head, taken apart header by header.
 *
 * @return list<array<string, mixed>>
 */
function explainHeaderBlock(PDO $pdo, string $block): array
{
    $notes = headerNotes($pdo);
    $out = [];

    foreach (parseHeaderBlock($block) as $header) {
        $out[] = explainHeader(
            (string)($header['name'] ?? ''),
            (string)($header['value'] ?? ''),
            $notes
        );
    }

    return $out;
}

/**
 * A raw header block as a list of pairs, keeping repeats.
 *
 * Set-Cookie and Link legitimately appear several times, and folding them into
 * a map would silently drop all but the last - which on a page about what a
 * server actually sent is the one thing that must not happen.
 *
 * @return list<array{name: string, value: string, response: int}>
 */
function parseHeaderBlock(string $block): array
{
    $out = [];
    $response = 0;

    foreach (preg_split('/\R/', trim($block)) ?: [] as $line) {
        if ($line === '') {
            continue;
        }
        if (preg_match('#^HTTP/#i', $line)) {
            // A redirect chain produces several response heads in one block;
            // numbering them keeps the hops apart.
            $response++;
            $out[] = ['name' => '', 'value' => trim($line), 'response' => $response];
            continue;
        }
        $at = strpos($line, ':');
        if ($at === false) {
            continue;
        }
        $out[] = [
            'name' => trim(substr($line, 0, $at)),
            'value' => trim(substr($line, $at + 1)),
            'response' => max(1, $response),
        ];
    }

    return $out;
}

function urlsView(PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the URL checks');
    }

    $rows = urlWatchList($pdo);
    $selected = isset($_GET['id']) ? clampInt($_GET['id'], 1, PHP_INT_MAX) : null;

    if ($selected === null && $rows !== []) {
        /*
         * Something failing is shown first, without being asked.
         *
         * Opening a monitoring page and being shown a healthy check while
         * another is down is the page working against its own purpose.
         */
        foreach ($rows as $row) {
            if ($row['enabled'] && $row['last_ok'] !== null && (int)$row['last_ok'] === 0) {
                $selected = (int)$row['id'];
                break;
            }
        }
        $selected ??= (int)$rows[0]['id'];
    }

    [$from, $to] = window();

    return [
        'rows' => $rows,
        'selected' => $selected,
        'history' => $selected === null ? null : urlWatchHistory($pdo, $selected, $from, $to),
        // The moments a watched value changed, for marking on the chart. A
        // deploy at 04:12 is the explanation for the response time after it.
        'value_history' => $selected === null
            ? ['first' => null, 'changes' => []]
            : urlWatchChanges($pdo, $selected, $from),
        'intervals' => urlWatchIntervals(),
        'checks' => urlWatchChecks(),
        'max' => URLWATCH_MAX,
        /*
         * Whether the cron can actually deliver the shortest interval.
         *
         * A check set to run every minute on an account whose cron runs every
         * five will run every five, and saying so beats letting somebody
         * conclude the monitoring is broken.
         */
        'collector_interval' => medianGap($pdo->query(
            'SELECT taken_at FROM vitals ORDER BY taken_at DESC LIMIT 20'
        )->fetchAll(PDO::FETCH_COLUMN)),
    ];
}

function doUrlSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = urlWatchSave($pdo, [
        'id' => $_POST['id'] ?? 0,
        'url' => $_POST['url'] ?? '',
        'label' => $_POST['label'] ?? '',
        'interval_seconds' => $_POST['interval_seconds'] ?? 300,
        'expect_status' => $_POST['expect_status'] ?? 200,
        'check_kind' => $_POST['check_kind'] ?? 'none',
        'check_value' => $_POST['check_value'] ?? '',
        'capture_group' => $_POST['capture_group'] ?? 1,
        'notify_change' => ($_POST['notify_change'] ?? '0') === '1',
        'enabled' => ($_POST['enabled'] ?? '1') === '1',
        'follow_redirects' => ($_POST['follow_redirects'] ?? '1') === '1',
        'send_etag' => ($_POST['send_etag'] ?? '0') === '1',
        'method' => $_POST['method'] ?? 'GET',
    ], $config);

    logAction($pdo, 'url_save', (string)($_POST['url'] ?? ''), 'ok', 0, 0, null);

    /*
     * Run once immediately, so the answer is on screen before they look away.
     *
     * A check that says nothing until the next cron tick is a check nobody can
     * tell they configured correctly - and a typo in a selector is best found
     * while the person who typed it is still there.
     */
    $result = urlWatchRunDue($pdo, $id, null, $config);

    return ['ok' => true, 'id' => $id, 'first' => $result['rows'][0] ?? null]
        + urlsView($pdo, true);
}

/**
 * Turns one check on or off without losing it.
 *
 * Distinct from deleting, which takes the history with it. A check paused
 * during a planned outage should come back with its record intact - otherwise
 * the only way to stop the alerts is to destroy the evidence of what the site
 * was doing before.
 */
function doUrlToggle(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $stmt = $pdo->prepare('UPDATE url_checks SET enabled = 1 - enabled WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such check');
    }

    $now = $pdo->prepare('SELECT enabled, label FROM url_checks WHERE id = ?');
    $now->execute([$id]);
    $row = $now->fetch();

    logAction($pdo, 'url_toggle', (string)$row['label'], 'ok', 0, 0,
        $row['enabled'] ? 'switched on' : 'switched off');

    return ['ok' => true, 'enabled' => (bool)$row['enabled']] + urlsView($pdo, true);
}

function doUrlDelete(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $stmt = $pdo->prepare('DELETE FROM url_checks WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such check');
    }

    // The results are cascaded away with it, which is what deleting a check
    // means - keeping orphaned history would be keeping a chart of nothing.
    $pdo->prepare('DELETE FROM url_results WHERE check_id = ?')->execute([$id]);

    logAction($pdo, 'url_delete', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true] + urlsView($pdo, true);
}

function doUrlRun(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $result = urlWatchRunDue($pdo, $id, null, $config);

    if ($result['ran'] === 0) {
        throw new ActionRefused('no such check');
    }

    return ['ok' => true, 'result' => $result['rows'][0]] + urlsView($pdo, true);
}

/**
 * Who is asking and who is answering.
 *
 * Signed in only: it reports the account the process runs as, where its files
 * live and what the server calls itself, which together are a fair sketch of
 * the install for anybody who should not have one.
 */
/**
 * Everything a domain publishes, and what has changed.
 *
 * The domain list comes from the watched domains, because those are the ones
 * somebody has already said they care about - and a zone sweep is expensive
 * enough that offering it for an arbitrary name would invite pointing it at
 * somebody else's.
 */
function dnsRecordsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see DNS records');
    }

    $domains = array_column(domainList($pdo), 'name');
    $wanted = strtolower(trim((string)($_GET['domain'] ?? '')));

    if ($wanted !== '' && !in_array($wanted, $domains, true)) {
        throw new ActionRefused('that domain is not being watched');
    }
    if ($wanted === '' && $domains !== []) {
        $wanted = $domains[0];
    }

    return [
        'domains' => $domains,
        'domain' => $wanted === '' ? null : $wanted,
        'inventory' => $wanted === '' ? null : dnsInventory($pdo, $wanted),
        'changes' => dnsRecentChanges($pdo, $wanted === '' ? null : $wanted, 60),
        'have_dig' => whichBinary('dig') !== null,
        'last_swept' => $wanted === '' ? null : (int)$pdo->query(
            'SELECT MAX(last_seen) FROM dns_records WHERE domain = ' . $pdo->quote($wanted)
        )->fetchColumn(),
    ];
}

/**
 * Sweeps one zone now.
 *
 * Synchronous, unlike most things that shell out here: a sweep is a few dozen
 * short lookups and finishes in seconds, and the answer is only useful while
 * somebody is looking at it. The name limit is what keeps that true on a large
 * zone.
 */
function doDnsSweep(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $domain = strtolower(trim((string)($_POST['domain'] ?? '')));
    $watched = array_column(domainList($pdo), 'name');

    if (!in_array($domain, $watched, true)) {
        throw new ActionRefused('that domain is not being watched');
    }

    $result = dnsSweep($config, $pdo, $domain);

    logAction($pdo, 'dns_sweep', $domain, $result['ok'] ? 'ok' : 'failed', 0, 0,
        $result['ok']
            ? sprintf('%d records across %d names, %d changes',
                $result['records'], $result['names'], count($result['changes']))
            : $result['reason']);

    $_GET['domain'] = $domain;
    return ['ok' => true, 'result' => $result] + dnsRecordsView($config, $pdo, true);
}

function whoAmIView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see who you are');
    }
    return whoAmI($config, $pdo);
}

function labelsView(PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see your labels');
    }

    try {
        $rows = $pdo->query('SELECT * FROM labels ORDER BY kind, match')->fetchAll();
    } catch (Throwable) {
        $rows = [];
    }

    return ['rows' => $rows, 'max' => LABEL_MAX];
}

/**
 * What everything is called and what it does, for the pages that list names.
 *
 * Sent whole rather than a name at a time. The slab page has ninety rows and
 * the process page a hundred, and describing them one request each would be a
 * hundred requests to say something that fits in a few kilobytes.
 *
 * Not gated on signing in: a description of what `dentry` is reveals nothing
 * about this machine, and the pages showing the names are readable without
 * signing in. The operator's own notes are included, since they were written
 * to be read on the page they annotate - if one of them holds something
 * private, that is a reason to reword it rather than to hide the whole set.
 */
function descriptionsView(PDO $pdo, bool $signedIn): array
{
    return [
        'slab' => whatIsAll($pdo, 'slab'),
        'process' => whatIsAll($pdo, 'process'),
        'mine' => $signedIn ? whatIsMine($pdo) : [],
        'max' => WHATIS_MAX,
        'writable' => $signedIn,
    ];
}

/** Writes one of the operator's own descriptions. */
function doDescriptionSave(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $name = (string)($_POST['name'] ?? '');

    whatIsSave(
        $pdo,
        (string)($_POST['kind'] ?? 'process'),
        $name,
        (string)($_POST['what'] ?? ''),
        (string)($_POST['link'] ?? '')
    );

    logAction($pdo, 'description_save', $name, 'ok', 0, 0,
        (string)($_POST['kind'] ?? 'process'));

    return ['ok' => true] + descriptionsView($pdo, true);
}

/** Removes one. */
function doDescriptionForget(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $gone = whatIsForget($pdo, $id);

    logAction($pdo, 'description_forget', (string)$id, $gone ? 'ok' : 'missing', 0, 0, null);

    return ['ok' => $gone] + descriptionsView($pdo, true);
}

function doLabelSave(PDO $pdo, bool $post): array
{
    requireWrite($post);

    saveLabel(
        $pdo,
        (string)($_POST['kind'] ?? 'address'),
        (string)($_POST['match'] ?? ''),
        (string)($_POST['label'] ?? ''),
        (string)($_POST['note'] ?? '')
    );

    logAction($pdo, 'label_save', (string)($_POST['match'] ?? ''), 'ok', 0, 0,
        (string)($_POST['label'] ?? ''));

    return ['ok' => true] + labelsView($pdo, true);
}

function doLabelDelete(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    if (!deleteLabel($pdo, $id)) {
        throw new ActionRefused('no such label');
    }

    logAction($pdo, 'label_delete', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true] + labelsView($pdo, true);
}

/** Captures asked for and not yet taken. */
function pendingCaptures(PDO $account, PDO $host): array
{
    $out = [];
    try {
        $rows = $account->query('SELECT task, requested_at FROM host_tasks')->fetchAll();
    } catch (Throwable) {
        return $out;
    }

    $done = $host->prepare(
        'SELECT MAX(taken_at) FROM host_captures WHERE interface = ? AND filter = ?'
    );

    foreach ($rows as $row) {
        $parsed = parseCaptureTask((string)$row['task']);
        if ($parsed === null) {
            continue;
        }
        $done->execute([$parsed['interface'], $parsed['filter']]);
        if ((int)$done->fetchColumn() >= (int)$row['requested_at']) {
            continue;
        }
        $out[] = $parsed + ['requested_at' => (int)$row['requested_at']];
    }

    return $out;
}

/**
 * Every service unit, from the newest reading that recorded any.
 *
 * "The newest that has some" rather than "the newest", because systemctl can
 * fail on one collection - a container restarting, a transient dbus problem -
 * and an empty list would read as a machine with no services rather than as one
 * reading that did not manage to ask.
 */
function unitsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the service list');
    }

    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        $host = null;
    }

    if ($host === null) {
        return ['available' => false, 'reason' => 'This needs the host collector.', 'rows' => []];
    }

    try {
        $sample = (int)$host->query(
            'SELECT MAX(sample_id) FROM host_units'
        )->fetchColumn();
    } catch (Throwable) {
        return [
            'available' => false, 'rows' => [],
            'reason' => 'The host database predates service listing. Upload host-cron.php '
                . 'and lib/, then let the collector run once.',
        ];
    }

    if ($sample <= 0) {
        return [
            'available' => false, 'rows' => [],
            'reason' => 'No service units recorded. The collector runs systemctl; on a '
                . 'machine without systemd there is nothing to list.',
        ];
    }

    $rows = $host->prepare('SELECT * FROM host_units WHERE sample_id = ?');
    $rows->execute([$sample]);
    $all = $rows->fetchAll();

    $rank = ['failed' => 0, 'masked' => 1, 'running-not-enabled' => 2,
             'enabled-not-running' => 3, 'running' => 4, 'stopped' => 5];
    usort($all, fn(array $a, array $b): int =>
        (($rank[$a['state']] ?? 9) <=> ($rank[$b['state']] ?? 9)) ?: strcmp($a['name'], $b['name']));

    /*
     * What each unit is for, from its name.
     *
     * A list of two hundred units sorted by state answers "what is broken" and
     * nothing else. Grouping them means "is the mail working" is one glance
     * rather than knowing that the answer lives in `dovecot`, `postfix` and
     * `exim` - which is knowledge about this machine that the machine already
     * has and was not sharing.
     *
     * Matched on the name because that is all there is: systemd's own
     * categories are a description field, and units are named by convention
     * far more consistently than they are described.
     */
    foreach ($all as $i => $row) {
        $all[$i]['category'] = serviceCategory((string)$row['name'],
            (string)($row['description'] ?? ''));
    }

    $summary = [];
    $categories = [];
    $enabled = [];
    $says = [];

    foreach ($all as $row) {
        $summary[$row['state']] = ($summary[$row['state']] ?? 0) + 1;
        $categories[$row['category']] = ($categories[$row['category']] ?? 0) + 1;

        $boot = (string)($row['enabled'] ?? '') ?: 'unknown';
        $enabled[$boot] = ($enabled[$boot] ?? 0) + 1;

        $said = (string)($row['active'] ?? '') . '/' . (string)($row['sub'] ?? '');
        $says[$said] = ($says[$said] ?? 0) + 1;
    }

    ksort($categories);
    ksort($enabled);
    arsort($says);

    $notes = [];
    foreach (array_keys($rank) as $state) {
        $notes[$state] = serviceStateNote($state);
    }

    $takenAt = (int)$host->query(
        'SELECT taken_at FROM host_samples WHERE id = ' . $sample
    )->fetchColumn();

    return [
        'available' => true, 'reason' => '', 'rows' => $all,
        'summary' => $summary, 'notes' => $notes, 'taken_at' => $takenAt,
        'categories' => $categories,
        'enabled_values' => $enabled,
        'says_values' => $says,
        'worth_looking' => array_keys(array_filter(
            $notes,
            fn(string $note, string $state): bool => serviceWorthLooking($state),
            ARRAY_FILTER_USE_BOTH
        )),
        'category_notes' => serviceCategoryNotes(),
    ];
}

/**
 * Which states are the ones "worth looking at".
 *
 * Written down rather than left implicit, because the checkbox that used to
 * filter on this said nothing about what it filtered on - and the answer is not
 * obvious. It is by STATE, never by name: no service is more important than
 * another here, only more broken.
 */
function serviceWorthLooking(string $state): bool
{
    return in_array(
        $state,
        ['failed', 'masked', 'running-not-enabled', 'enabled-not-running'],
        true
    );
}

/**
 * A category for one unit, from its name.
 *
 * Ordered longest-match-first in spirit: `php-fpm` must be tested before `php`
 * would match something else, and `cpanel` before `cron` catches `cpanel-cron`.
 * Anything unrecognised is "other", which on a full machine is most of them and
 * is an honest answer rather than a guessed one.
 */
function serviceCategory(string $name, string $description): string
{
    $unit = strtolower($name);
    $said = strtolower($description);

    $groups = [
        'web' => ['httpd', 'apache', 'nginx', 'lshttpd', 'litespeed', 'openlitespeed',
                  'php-fpm', 'php_fpm', 'ea-php', 'lsphp', 'tomcat', 'varnish'],
        'database' => ['mysql', 'mariadb', 'postgres', 'redis', 'mongod', 'memcached',
                       'elasticsearch'],
        'mail' => ['exim', 'postfix', 'dovecot', 'sendmail', 'spamd', 'spamassassin',
                   'opendkim', 'mailman'],
        'dns' => ['named', 'bind', 'pdns', 'unbound', 'nsd', 'systemd-resolved'],
        'ftp and shell' => ['sshd', 'pure-ftpd', 'proftpd', 'vsftpd', 'ssh'],
        'security' => ['firewalld', 'iptables', 'nftables', 'fail2ban', 'lfd', 'csf',
                       'clamd', 'auditd', 'imunify', 'selinux'],
        'control panel' => ['cpanel', 'whm', 'cpsrvd', 'cpdavd', 'cphulk', 'plesk',
                            'directadmin', 'cagefs', 'lvestats', 'lve'],
        'scheduling' => ['cron', 'crond', 'atd', 'anacron', 'systemd-timer'],
        'storage' => ['nfs', 'rpcbind', 'smb', 'nmb', 'iscsi', 'lvm', 'mdmonitor',
                      'multipath', 'quota'],
        'logging' => ['rsyslog', 'syslog', 'journald', 'logrotate', 'auditbeat'],
        'backups' => ['jetbackup', 'r1soft', 'acronis', 'backup', 'restic', 'borg'],
        'the system itself' => ['systemd-', 'dbus', 'polkit', 'chronyd', 'ntpd', 'tuned',
                                'irqbalance', 'kdump', 'udev', 'getty', 'network',
                                'sysstat'],
    ];

    foreach ($groups as $category => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($unit, $needle)) {
                return $category;
            }
        }
    }

    /*
     * The description, as a fallback only.
     *
     * It catches a few - "Apache Web Server" on a unit called something else -
     * and is checked second because a description is prose and prose about a
     * database will mention the web often enough to be misleading.
     */
    foreach (['web server' => 'web', 'database' => 'database', 'mail' => 'mail',
              'firewall' => 'security', 'backup' => 'backups'] as $phrase => $category) {
        if (str_contains($said, $phrase)) {
            return $category;
        }
    }

    return 'other';
}

/** What each category covers, for the filter's tooltip. */
function serviceCategoryNotes(): array
{
    return [
        'web' => 'Whatever serves the site, and the PHP behind it.',
        'database' => 'Anything the site stores things in.',
        'mail' => 'Sending, receiving and filtering.',
        'dns' => 'Answering name lookups.',
        'ftp and shell' => 'Ways in.',
        'security' => 'Firewalls, scanners and intrusion blocking.',
        'control panel' => 'cPanel, Plesk and their many daemons.',
        'scheduling' => 'What runs things on a timer.',
        'storage' => 'Mounts, arrays and quotas.',
        'logging' => 'What writes the logs everything else is read from.',
        'backups' => 'Whatever copies this machine somewhere else.',
        'the system itself' => 'systemd and the machinery underneath everything.',
        'other' => 'Not recognised by name. On a full machine this is most of them.',
    ];
}

/**
 * Name lookups, run now rather than read from a record.
 *
 * A stored DNS result answers "was it working five minutes ago", which is never
 * the question - somebody opening this page is asking whether it works *now*.
 * So this one endpoint does its own work, and it is bounded to a handful of
 * one-shot queries with a three second timeout each.
 */
function dnsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to run name lookups');
    }

    $listening = [];
    try {
        $host = hostDb($config, false);
        if ($host !== null) {
            $sample = (int)$host->query('SELECT MAX(sample_id) FROM host_ports')->fetchColumn();
            if ($sample > 0) {
                $rows = $host->prepare('SELECT port FROM host_ports WHERE sample_id = ?');
                $rows->execute([$sample]);
                foreach ($rows->fetchAll() as $row) {
                    $listening[] = ['port' => (int)$row['port']];
                }
            }
        }
    } catch (Throwable) {
        $listening = [];
    }

    $wanted = (string)($_GET['name'] ?? '');
    $server = (string)($_GET['server'] ?? '');

    if ($wanted !== '') {
        $type = strtoupper((string)($_GET['type'] ?? 'A'));

        /*
         * "Every type" asks for each in turn rather than sending ANY.
         *
         * ANY is not an answer any more: most resolvers return a minimal or
         * refused response to it, and a zone that does answer does so
         * inconsistently. Asking for each type is slower and is the only way to
         * get a complete picture.
         */
        $types = $type === 'ALL' ? dnsLookupTypes() : [$type];

        $queries = [];
        foreach ($types as $one) {
            $queries[] = [
                'name' => $wanted,
                'type' => $one,
                'server' => $server === '' ? null : $server,
                'why' => count($types) > 1 ? '' : 'Asked for from the dashboard.',
            ];
        }
    } else {
        $queries = defaultDnsQueries($config, $listening);
    }

    $result = dnsChecks($queries);

    /*
     * Every answer carries what its type is for, and TXT records are taken
     * apart. A zone read by somebody who does not administer DNS daily is a
     * wall of three-letter names, and the difference between an A and a CNAME
     * is exactly what matters when one of them is wrong.
     */
    $notes = dnsTypeNotes();
    foreach ($result['rows'] as &$row) {
        $row['type_note'] = $notes[$row['type']] ?? '';
        foreach ($row['answers'] as &$answer) {
            $answer['note'] = $notes[$answer['type']] ?? '';
            if ($answer['type'] === 'TXT') {
                $answer['txt'] = parseTxtRecord((string)$answer['value']);
            }

            /*
             * SOA gets the same treatment, in the same shape.
             *
             * Seven unlabelled fields, three of them durations in seconds and
             * one an email address written with a dot where the @ belongs.
             * Reusing the pairs shape means the renderer needs no second path.
             */
            if ($answer['type'] === 'SOA') {
                $soa = parseSoaRecord((string)$answer['value']);
                if ($soa['ok']) {
                    $answer['txt'] = [
                        'kind' => 'SOA',
                        'note' => 'The zone\'s own settings: who runs it, and how long '
                            . 'everyone else may believe what it says.',
                        'pairs' => array_map(
                            fn(array $f): array => [
                                'name' => $f['name'],
                                'value' => $f['value'],
                                'note' => $f['note'],
                            ],
                            $soa['fields']
                        ),
                        'text' => (string)$answer['value'],
                    ];
                }
            }
        }
        unset($answer);
    }
    unset($row);

    return $result + [
        'types' => dnsLookupTypes(),
        'notes' => $notes,
        'serves_dns' => $listening !== [] && in_array(53, array_column($listening, 'port'), true),
        'missing' => whichBinary('dig') === null ? missingCommand($config, 'dig') : null,
    ];
}

/**
 * Asks the host collector for a packet capture on its next run.
 *
 * The dashboard cannot open a raw socket and should not be able to. What it can
 * do is leave a request naming an interface and one of a fixed set of filters,
 * which root's collector picks up - the same protocol as the disk walk, and for
 * the same reason.
 */
/**
 * Withdraws a request before the collector gets to it.
 *
 * Worth having because the wait is minutes: asking for a sixty-second capture
 * and then realising it should have been on a different interface should not
 * mean waiting for the wrong one to run first. Removing the row is the whole
 * of it - the collector only ever acts on what is in the table when it looks.
 */
function doCancelTask(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $task = (string)($_POST['task'] ?? '');
    if ($task === '') {
        throw new ActionRefused('no request named');
    }

    $stmt = $pdo->prepare('DELETE FROM host_tasks WHERE task = ?');
    $stmt->execute([$task]);

    if ($stmt->rowCount() === 0) {
        /*
         * Gone already is not an error.
         *
         * The collector may have answered it between the page being drawn and
         * the button being pressed, which is the ordinary case rather than a
         * fault - and reporting it as one would be confusing about something
         * that worked.
         */
        return ['ok' => true, 'already' => true];
    }

    logAction($pdo, 'cancel_task', $task, 'ok', 0, 0, 'withdrawn before it ran');
    return ['ok' => true, 'already' => false];
}

function doRequestCapture(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $interface = (string)($_POST['interface'] ?? '');
    $filter = (string)($_POST['filter'] ?? 'all');

    if (!preg_match('/^[A-Za-z0-9._-]{1,32}$/', $interface)) {
        throw new ActionRefused('that is not an interface name');
    }
    if (captureFilter($filter) === null) {
        throw new ActionRefused('that is not one of the capture filters on offer');
    }

    $at = requestHostTask(
        $pdo,
        captureTaskName($interface, $filter, ($_POST['read_asked'] ?? '0') === '1'),
        currentAccountName()
    );
    logAction($pdo, 'request_capture', $interface . ' ' . $filter, 'ok', 0, 0,
        'waiting for the host collector');

    return ['ok' => true, 'requested_at' => $at];
}

function mountsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the mount table');
    }

    $host = hostDb($config, false);
    $mounts = [];
    $at = 0;

    if ($host !== null) {
        try {
            $at = (int)$host->query('SELECT MAX(taken_at) FROM host_mountinfo')->fetchColumn();
            if ($at > 0) {
                $stmt = $host->prepare('SELECT * FROM host_mountinfo WHERE taken_at = ?');
                $stmt->execute([$at]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $mounts[] = [
                        'mount_id' => (int)$row['mount_id'],
                        'parent_id' => (int)$row['parent_id'],
                        'device' => (string)$row['device'],
                        'fs_root' => (string)$row['fs_root'],
                        'mount_point' => (string)$row['mount_point'],
                        'fs_type' => (string)$row['fs_type'],
                        'source' => (string)$row['source'],
                        'options' => (string)$row['options'],
                        'propagation' => (string)$row['propagation'],
                        'read_only' => (bool)$row['read_only'],
                        'bind' => (bool)$row['bind'],
                    ];
                }
            }
        } catch (Throwable) {
            $mounts = [];
        }
    }

    /*
     * The account's own mount table, when the host has not recorded one.
     *
     * Inside CageFS this is the *account's* view rather than the machine's,
     * which is a different and still useful answer - it is precisely the small
     * curated filesystem the account lives in.
     */
    $ownView = false;
    if ($mounts === []) {
        $mounts = readMountInfo();
        $ownView = $mounts !== [];
    }

    return [
        'available' => $mounts !== [],
        'own_view' => $ownView,
        'taken_at' => $at,
        'reason' => $mounts === []
            ? 'No mount table could be read. /proc/self/mountinfo is where this comes from.'
            : null,
        'mounts' => $mounts,
        'filesystems' => groupByFilesystem($mounts),
        // Told which view it is looking at, because the same absence of
        // skeleton mounts means opposite things from the two sides.
        'cagefs' => cagefsMounts($mounts, CAGEFS_SKELETON, !$ownView),
    ];
}

/**
 * Backup storage, as its own subject.
 *
 * Signed-in only: it names accounts, their live sizes and their backup sizes,
 * which together describe the shape of somebody's hosting.
 */
function backupsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see backup storage');
    }

    $host = hostDb($config, false);
    if ($host === null) {
        return [
            'available' => false,
            'reason' => 'Backups live outside this account, so this needs the host collector.',
        ];
    }

    try {
        $analysis = analyseBackups($host, $config);
    } catch (Throwable $e) {
        return ['available' => false, 'reason' => 'Could not be read: ' . $e->getMessage()];
    }

    if ($analysis['available']) {
        // The named account, not the unattributed remainder.
        $analysis += backupActions($analysis);
        foreach ($analysis['accounts'] as $i => $row) {
            $analysis['accounts'][$i]['verdict'] =
                backupRatioVerdict($row['ratio'], $row['copies']);
        }
    }

    return $analysis;
}

function hostDiskView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the host disk report');
    }

    $host = hostDb($config, false);
    if ($host === null) {
        return [
            'available' => false,
            'reason' => 'The host collector is not installed, so nothing outside this '
                . 'account can be seen at all.',
            'mounts' => [], 'dirs' => [], 'deleted' => [],
        ];
    }

    $report = hostDiskReport($host);
    $report['unaccounted'] = hostUnaccounted($report);

    $requested = requestedHostWalk($config);
    $report['requested_at'] = $requested;
    $report['pending'] = hostWalkPending($host, $requested);

    return $report;
}

/**
 * Everything knowable about one path.
 *
 * Signed in only. It reports owners, permissions and the contents of
 * directories, which is more than a masked path is willing to say.
 */
function inspectView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to inspect a path');
    }
    return inspectPath($config, $pdo, (string)($_GET['path'] ?? ''));
}

/**
 * The host logs this install offers, and whether each can actually be read.
 *
 * Probed rather than listed, because the list in the config is a statement of
 * intent and the filesystem is the authority. A log that is named but
 * unreadable is worth showing as exactly that - it tells you the feature is
 * configured and the host is what is in the way.
 */
function hostLogList(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read log files');
    }

    $rows = [];
    foreach ($config['host_log_paths'] ?? [] as $path) {
        $real = realpath((string)$path);
        $exists = $real !== false && is_file($real);

        $rows[] = [
            'path' => (string)$path,
            'exists' => $exists,
            'readable' => $exists && is_readable($real),
            'bytes' => $exists ? (int)(@filesize($real) ?: 0) : null,
            'modified_at' => $exists ? (int)(@filemtime($real) ?: 0) : null,
        ];
    }

    return ['rows' => $rows];
}

function treemapView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to explore the disk');
    }

    $path = (string)($_GET['path'] ?? '');

    if (($_GET['source'] ?? 'account') === 'host') {
        if (!($config['host']['enabled'] ?? true)) {
            throw new ActionRefused('host collection is turned off');
        }
        $host = hostDb($config, false);
        if ($host === null) {
            throw new ActionRefused('the host collector is not installed');
        }
        return hostTreemapLevel($host, $path) + ['source' => 'host'];
    }

    return treemapLevel($config, $path) + ['source' => 'account'];
}

function incidentView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the evidence behind an alert');
    }

    $id = clampInt($_GET['id'] ?? 0, 1, PHP_INT_MAX);
    $incident = incidentRead($pdo, $id);
    if ($incident === null) {
        throw new ActionRefused('there is no such incident');
    }

    return $incident;
}

function actionLog(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the action log');
    }

    $rows = $pdo->query('SELECT * FROM actions ORDER BY at DESC LIMIT 500')->fetchAll();
    if (!accessAllows($config, 'ips') || !empty($_SESSION['hide_ips'])) {
        foreach ($rows as &$row) {
            $row['remote_addr'] = null;
        }
        unset($row);
    }

    /*
     * Who did it, as far as anything here can honestly say.
     *
     * There is one password, so there is one identity, and the only thing that
     * distinguishes two uses of it is the address they came from. Calling that
     * "who" would be a lie - an address is a place, not a person - so it is
     * labelled as what it is and the interface says so. When accounts exist
     * this becomes a real name and the column stops needing the caveat.
     */
    $who = [];
    $actors = [];
    foreach ($rows as $row) {
        $address = (string)($row['remote_addr'] ?? '');
        if ($address !== '') {
            $who[$address] = true;
        }
        $actor = (string)($row['actor'] ?? '');
        if ($actor !== '') $actors[$actor] = true;
    }

    $days = (int)(retentionRules($config)['action_days'] ?? 0);

    return [
        'rows' => $rows,
        'kinds' => array_values(array_unique(array_column($rows, 'action'))),
        'who' => array_keys($actors !== [] ? $actors : $who),
        'identity' => $actors !== [] ? 'actor' : 'address',
        // So the page can answer "does this ever get cleared" without anybody
        // having to read the config to find out.
        'kept_days' => $days,
        'total' => (int)$pdo->query('SELECT COUNT(*) FROM actions')->fetchColumn(),
        'oldest_at' => (int)$pdo->query('SELECT MIN(at) FROM actions')->fetchColumn(),
    ];
}

/** Hides addresses for this browser session without changing group policy. */
function doIpPrivacy(array $config, bool $post): array
{
    if (!$post || !isSignedIn() || !csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('sign in with a current form token to change session privacy');
    }
    if (!accessAllows($config, 'ips')) {
        throw new ActionRefused('your group may not view IP addresses');
    }
    $_SESSION['hide_ips'] = ($_POST['hide'] ?? '0') === '1';
    return ['ok' => true, 'access' => accessView($config)];
}

/** Hides the dashboard account label for this browser session. */
function doAccountPrivacy(array $config, bool $post): array
{
    if (!$post || !isSignedIn() || !csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('sign in with a current form token to change session privacy');
    }
    $_SESSION['hide_accounts'] = ($_POST['hide'] ?? '0') === '1';
    return ['ok' => true, 'access' => accessView($config)];
}

/**
 * The end of a log file.
 *
 * Signed in only, and only for the categories the config marks readable. The
 * reader never loads the file - see lib/logs.php - which is the difference
 * between this working on a ten gigabyte error log and taking the site down.
 */
/**
 * The last line of each of several log files, in one request.
 *
 * The single most useful thing about a log at a glance is its most recent line -
 * "is it still throwing the same error" is answered by one line and does not
 * need the viewer opened. Batched because the alternative is one request per
 * row of a table, and a table of forty logs would be forty requests.
 *
 * Each file goes through resolveViewable, so this grants nothing the viewer
 * does not, and a refusal is reported per file rather than failing the batch -
 * one unreadable log should not blank the other nine.
 */
/**
 * The newest lines from every readable log, and what keeps repeating.
 *
 * The file list is assembled rather than configured: everything the scanner has
 * recorded that resolveViewable will open, plus the host logs named in the
 * config that this account can actually read. Assembled because the useful set
 * changes as domains are added, and a list somebody has to maintain is a list
 * that is wrong within a month.
 */
function logStreamView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read log files');
    }

    $want = clampInt($_GET['lines'] ?? 20, 1, 200);

    $files = [];
    $seen = [];

    /*
     * The account's own logs, newest-written first.
     *
     * Ordered by mtime rather than by size: a merged stream wants the files
     * that are being written to now, and the biggest log on the disk is very
     * often one nothing has touched since a rotation months ago.
     */
    $rows = $pdo->query(
        "SELECT p.path FROM paths p
          WHERE p.kind = 'file' AND p.category IN ('logs', 'errors')
          ORDER BY p.last_seen DESC LIMIT 120"
    )->fetchAll(PDO::FETCH_COLUMN);

    foreach ($rows as $path) {
        try {
            $real = resolveViewable($config, (string)$path);
        } catch (ActionRefused) {
            // Rotated, compressed, deleted or binary. Not an error - just not
            // something that can be read as lines.
            continue;
        }
        if (isset($seen[$real])) {
            continue;
        }
        $seen[$real] = true;
        $files[] = ['path' => $real, 'label' => basename($real), 'where' => 'account'];
    }

    foreach ((array)($config['host_log_paths'] ?? []) as $path) {
        $real = realpath((string)$path);
        if ($real === false || isset($seen[$real]) || !is_readable($real)) {
            continue;
        }
        try {
            resolveViewable($config, $real);
        } catch (ActionRefused) {
            continue;
        }
        $seen[$real] = true;
        $files[] = ['path' => $real, 'label' => basename($real), 'where' => 'host'];
    }

    if ($files === []) {
        return [
            'rows' => [], 'files' => [], 'repeats' => ['groups' => [], 'lines' => 0, 'distinct' => 0],
            'ordering' => 'timestamps',
            'note' => 'No readable log files. The scanner records them under the logs and '
                . 'errors categories; a rotated .gz cannot be read as lines, and the host\'s '
                . 'own logs need permission this account may not have.',
        ];
    }

    // Sorted by how recently each was written, so the merge starts with the
    // files that are actually active.
    usort($files, fn(array $a, array $b): int =>
        (int)(@filemtime($b['path']) ?: 0) <=> (int)(@filemtime($a['path']) ?: 0));

    $stream = logStream($files, $want);

    /*
     * Repetition is counted over a wider window than is displayed.
     *
     * Twenty lines cannot show that something is repeating - it either fills
     * all twenty or it does not appear. The counting reads deeper into each
     * file so "41,000 times a minute" is available while the display stays
     * short enough to read.
     */
    $deep = logStream($files, 800);
    $stream['repeats'] = repeatedLines($deep['rows']);
    $stream['repeat_window'] = count($deep['rows']);

    return $stream;
}

function lastLines(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read log files');
    }

    $ids = array_slice(array_filter(array_map(
        'intval',
        explode(',', (string)($_GET['path_ids'] ?? ''))
    )), 0, 60);

    $paths = array_slice(array_filter(
        explode("\n", (string)($_GET['paths'] ?? '')),
        fn(string $p): bool => trim($p) !== ''
    ), 0, 60);

    $wanted = [];

    if ($ids !== []) {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, path FROM paths WHERE id IN ($marks)");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $row) {
            $wanted[] = ['id' => (int)$row['id'], 'path' => (string)$row['path']];
        }
    }
    foreach ($paths as $path) {
        $wanted[] = ['id' => null, 'path' => trim($path)];
    }

    $out = [];
    foreach ($wanted as $entry) {
        try {
            $real = resolveViewable($config, $entry['path']);
            $tail = tailFile($real, 1, 0, '');
            $line = $tail['lines'][0] ?? null;

            $out[] = [
                'path_id' => $entry['id'],
                'path' => $entry['path'],
                'line' => $line['text'] ?? null,
                'line_number' => $line['n'] ?? null,
                'bytes' => $tail['size'],
                'modified_at' => (int)(@filemtime($real) ?: 0),
                'error' => null,
            ];
        } catch (ActionRefused $e) {
            // Reported against the row it belongs to. A log that cannot be read
            // is a fact about that log, not a failure of the request.
            $out[] = [
                'path_id' => $entry['id'],
                'path' => $entry['path'],
                'line' => null,
                'line_number' => null,
                'bytes' => null,
                'modified_at' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    return ['rows' => $out];
}

function tailLog(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read log files');
    }

    /*
     * Named either by id or by path.
     *
     * The id is how the offender table refers to a file it already knows, and
     * carries the category with it. A raw path is how everything else does -
     * the treemap, the host log list - and those files may never have been
     * recorded at all. resolveViewable decides in both cases, so the extra way
     * in grants nothing the other one did not already allow.
     */
    $pathId = clampInt($_GET['path_id'] ?? 0, 0, PHP_INT_MAX);
    $wanted = (string)($_GET['path'] ?? '');

    if ($pathId > 0) {
        $stmt = $pdo->prepare('SELECT id, path, kind, category FROM paths WHERE id = ?');
        $stmt->execute([$pathId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new ActionRefused('unknown path');
        }
    } elseif ($wanted !== '') {
        $row = ['id' => null, 'path' => $wanted, 'kind' => 'file', 'category' => null];
    } else {
        throw new ActionRefused('no file named');
    }

    try {
        $real = resolveViewable($config, (string)$row['path']);
    } catch (ActionRefused $e) {
        /*
         * "Already deleted" beats "no such file" when we did the deleting.
         *
         * A stale tab, or an open log panel refreshed after the file went: the
         * path is genuinely missing and the reason is on record, so there is no
         * need to make it sound like a fault.
         */
        if (!file_exists((string)$row['path']) && alreadyDeleted($pdo, (string)$row['path'])) {
            throw new ActionRefused(
                'that file was deleted. It is still listed because the last scan found '
                . 'it; the next scan will drop it.'
            );
        }
        throw $e;
    }
    $result = tailFile(
        $real,
        clampInt($_GET['lines'] ?? 200, 1, 2000),
        clampInt($_GET['page'] ?? 0, 0, 100000),
        substr((string)($_GET['q'] ?? ''), 0, 200)
    );

    return ['path' => $row['path'], 'category' => $row['category']] + $result;
}

/**
 * What is running now, and what was running when the machine last struggled.
 *
 * Signed in only. A command line is not a size: it carries paths, arguments and
 * sometimes a password somebody put where they should not have, which is also
 * why the arguments are redacted before they get this far.
 */
/**
 * The moments the machine was busy, and what fired at the same time.
 *
 * Its own endpoint rather than a corner of the process list, because that one
 * shells out to `ps` on every call and this needs none of it - opening the page
 * about last night should not start a process listing today.
 *
 * The alerts are matched by clock, and that is all the claim being made. A rule
 * that fired within a couple of minutes of a recorded spike is worth putting a
 * link to; saying it fired *because* of the spike would be asserting a cause
 * nothing here measured.
 */
function busyMoments(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see what the machine was doing');
    }

    $moments = recordedProcesses($pdo, clampInt($_GET['limit'] ?? 25, 1, 100));
    if ($moments === []) {
        return ['moments' => [], 'window_seconds' => BUSY_ALERT_WINDOW];
    }

    $times = array_column($moments, 'taken_at');
    $stmt = $pdo->prepare(
        'SELECT id, raised_at, severity, subject, rule, incident_id
           FROM alerts
          WHERE raised_at BETWEEN ? AND ?
          ORDER BY raised_at'
    );
    $stmt->execute([min($times) - BUSY_ALERT_WINDOW, max($times) + BUSY_ALERT_WINDOW]);
    $alerts = $stmt->fetchAll();

    foreach ($moments as &$moment) {
        $moment['alerts'] = array_values(array_filter(
            $alerts,
            fn(array $a): bool =>
                abs((int)$a['raised_at'] - (int)$moment['taken_at']) <= BUSY_ALERT_WINDOW
        ));
    }
    unset($moment);

    return ['moments' => $moments, 'window_seconds' => BUSY_ALERT_WINDOW];
}

function processList(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the process list');
    }

    $by = ($_GET['by'] ?? 'cpu') === 'memory' ? 'memory' : 'cpu';
    // Ungrouped is how you reach one process of a pool to stop it on its own;
    // grouped is how you read the list. Both, rather than a choice.
    $group = ($_GET['group'] ?? '1') !== '0';
    $live = listProcesses($by, clampInt($_GET['limit'] ?? 15, 1, 100), $group);
    [$from, $to] = window();
    $stopping = signalsAvailable($config);

    return [
        'by' => $by,
        'grouped' => $group,
        'available' => $live['available'],
        'reason' => $live['reason'],
        'rows' => $stopping['available']
            ? annotateEligibility($config, $live['rows'])
            : $live['rows'],
        'trend' => processTrend($pdo, $from, $to, $by),
        // So the Stop buttons are only drawn where they could actually work.
        'can_stop' => $stopping['available'],
        'stop_reason' => $stopping['reason'],
        /*
         * How many cores the percentages are against.
         *
         * `ps` reports each process as a share of ONE core, so a threaded
         * program legitimately reads 400% and a group of them can pass 800% -
         * which sits on the page beside a machine-wide figure that is clamped
         * to 100 and means something entirely different. Two numbers written
         * the same way meaning different things is the whole fault; the core
         * count is what lets the page say which is which.
         */
        'cores' => cpuCores(),
        /*
         * The collectors are in this list, and are not in the recorded one.
         *
         * The stored snapshots drop this tool's own processes, because a record
         * of what was running during a spike should not be mostly the thing
         * that noticed the spike. The live list keeps them: they are really
         * running and really using the CPU, and hiding them would make the
         * column stop adding up.
         */
        'own_processes_shown' => true,
    ];
}

/**
 * Looks for symlinks that will send something into a loop.
 *
 * Run on request rather than on a schedule: it is a whole extra walk of the
 * tree, the answer changes only when somebody creates a link, and nothing about
 * it is urgent enough to pay for every hour.
 *
 * Signed in only, because it reports paths, and bounded by a clock it reports
 * back - a check that quietly gave up and said "nothing wrong" would be worse
 * than not having one.
 */
function symlinkCheck(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to check symlinks');
    }

    /*
     * The stored one, unless a fresh walk was asked for.
     *
     * Opening the page ran a full walk of the tree every time - fifteen seconds
     * of disk before anything appeared - and then forgot it on reload. The
     * button asks for a new one; arriving at the page shows the last one, with
     * its age.
     */
    if (($_GET['fresh'] ?? '0') !== '1') {
        $last = lastSymlinkCheck($pdo);
        if ($last !== null) {
            $total = count($last['problems']);
            $last['problems'] = array_slice($last['problems'], 0, SYMLINK_SHOW_MAX);

            return $last + [
                'total' => $total,
                'shown' => count($last['problems']),
                'stored' => true,
                'new' => [],
                'gone' => 0,
                'first_walk' => false,
                'root' => (string)$config['scan_root'],
                'bulk_max' => SYMLINK_BULK_MAX,
                'host_enabled' => (bool)($config['host']['enabled'] ?? false),
            ];
        }
    }

    $found = findSymlinkProblems($config, (float)clampInt($_GET['budget'] ?? 10, 1, 60));

    /*
     * Compared against the last complete walk before being returned.
     *
     * The count alone says nothing: eleven problems is fine if it has been
     * eleven for a year, and alarming if it was three yesterday. What is new
     * is the answerable question, so it is answered here rather than left for
     * somebody to remember.
     */
    $change = recordSymlinkState($pdo, $found);

    if ($change['added'] !== [] && !$change['first']) {
        raiseSymlinkAlert($pdo, $config, $change['added']);
    }

    /*
     * Sliced here, at the last possible moment, purely so the browser is not
     * handed twenty thousand rows to draw. Everything above this line - the
     * counts, the comparison against last time, the alert - saw all of them.
     */
    $total = count($found['problems']);
    $found['problems'] = array_slice($found['problems'], 0, SYMLINK_SHOW_MAX);

    return $found + [
        'total' => $total,
        'shown' => count($found['problems']),
        'stored' => false,
        'at' => time(),
        'new' => $change['added'],
        'gone' => $change['gone'],
        'first_walk' => $change['first'],
        'root' => (string)$config['scan_root'],
        'bulk_max' => SYMLINK_BULK_MAX,
        // Whether there is a root collector to ask for an ldconfig at all.
        'host_enabled' => (bool)($config['host']['enabled'] ?? false),
    ];
}

/**
 * Raises an alert for links that were not there last time.
 *
 * Only for a walk that completed, and only against a previous walk that
 * existed - the first ever check would otherwise report every link on the
 * machine as newly appeared, which is both useless and the kind of thing that
 * teaches people to ignore the alerts.
 */
function raiseSymlinkAlert(PDO $pdo, array $config, array $added): void
{
    $worst = 'broken';
    foreach ($added as $problem) {
        if (in_array($problem['kind'], ['loop', 'ancestor'], true)) {
            $worst = $problem['kind'];
            break;
        }
    }

    /*
     * A dangling link is untidy; a loop breaks the next backup. Only the
     * second is worth a critical, or the first time one appears everybody
     * learns the alert means nothing.
     */
    $severity = $worst === 'broken' ? 'warning' : 'critical';
    $first = $added[0];

    recordAlert($pdo, $config['alerts'] ?? [], [
        'rule' => 'symlink_new',
        'severity' => $severity,
        'subject' => count($added) === 1
            ? 'A new ' . $first['kind'] . ' symlink appeared'
            : count($added) . ' new symlink problems appeared',
        'detail' => $first['note'] . ' First one: ' . $first['path']
            . ($first['target'] === '' ? '' : ' -> ' . $first['target'])
            . (count($added) > 1 ? ' (and ' . (count($added) - 1) . ' more).' : ''),
        // Linked to the path row where there is one, so the alert can be
        // masked for a signed-out visitor like every other path-bearing alert.
        'path_id' => pathIdIfKnown($pdo, $first['path']),
    ]);
}

/**
 * The id of a path already known to the scanner, or null.
 *
 * Deliberately does not create one. A symlink is not something the scanner
 * counts - it steps over every link it meets - so inserting a row here would
 * put a path into the size tables that has no size and never will.
 */
function pathIdIfKnown(PDO $pdo, string $path): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM paths WHERE path = ?');
    $stmt->execute([$path]);
    $id = $stmt->fetchColumn();

    return $id === false ? null : (int)$id;
}

/**
 * One repair to one link.
 *
 * Three verbs, because there are three things somebody actually wants: point
 * it somewhere else, replace it with a real directory, or remove it. All three
 * refuse anything outside the watched directory and anything that is not a
 * symlink.
 */
function doLinkFix(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!($config['allow_delete'] ?? false)) {
        throw new ActionRefused(
            'this install is set up to look but not touch. Turn on deletions in setup.php '
            . 'to repair links from here.'
        );
    }

    $path = (string)($_POST['path'] ?? '');
    $how = (string)($_POST['how'] ?? '');

    $result = match ($how) {
        'repoint' => repointLink($config, $path, (string)($_POST['target'] ?? '')),
        'directory' => replaceLinkWithDirectory($config, $path),
        'delete' => deleteLink($config, $path),
        default => throw new ActionRefused('that is not something this can do to a link'),
    };

    logAction($pdo, 'symlink_' . $how, $result['path'], 'ok', 0, 0,
        $how === 'repoint'
            ? ($result['was'] . ' -> ' . $result['now']
                . ($result['exists'] ? '' : ' (which is not there yet)'))
            : ($result['was'] ?? $result['target'] ?? ''));

    return ['ok' => true, 'result' => $result, 'how' => $how];
}

/**
 * Deletes every broken link under a pattern.
 *
 * Always previewed first: the browser asks with `confirm=0`, shows exactly what
 * would go, and only then asks again for real. A pattern is easy to write too
 * broadly and this is the one operation here that could take four hundred
 * things at once.
 */
function doLinkSweep(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $confirmed = ($_POST['confirm'] ?? '0') === '1';

    if ($confirmed && !($config['allow_delete'] ?? false)) {
        throw new ActionRefused(
            'this install is set up to look but not touch. Turn on deletions in setup.php '
            . 'to remove links from here.'
        );
    }

    $pattern = (string)($_POST['pattern'] ?? '');
    $result = deleteBrokenMatching($config, $pattern, !$confirmed);

    if ($confirmed) {
        logAction($pdo, 'symlink_sweep', $pattern, 'ok', count($result['deleted']), 0,
            count($result['deleted']) . ' removed, ' . $result['skipped'] . ' left alone');
    }

    return ['ok' => true, 'result' => $result, 'pattern' => $pattern];
}

/**
 * What the host collector saw, which is everything the account cannot.
 *
 * Signed in only, and not negotiable: this lists every process on the machine
 * with its command line, every user's memory, and the paths of root-owned log
 * files. The account dashboard masks its own paths for a signed-out visitor;
 * there is no masked version of this, so there is no signed-out version.
 *
 * Read-only. The root cron is the single writer, and the dashboard never
 * migrates this database - a schema half-created by the web user in a file root
 * expects to own is a mess with no clean way out.
 */
/**
 * Everything the five CPU pages draw, from one reading of the host database.
 *
 * Its own action rather than more fields on `host`, because the two answer to
 * different things: the memory payload is a snapshot of the newest reading and
 * this one is mostly differences across the selected range. Folding them
 * together would make every memory poll re-read a day of tick counters, and
 * every CPU poll re-read the slab table and the watched files.
 *
 * The shape mirrors hostView's when the collector is missing, deliberately: the
 * "no host readings yet, here is the crontab line" case is the same case, and
 * two different explanations of one situation is how somebody ends up
 * following neither.
 */
function cpuView(array $config, bool $signedIn, PDO $account): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see processor readings');
    }

    if (!($config['host']['enabled'] ?? true)) {
        return ['available' => false, 'off' => true, 'sample' => null,
                'reason' => 'Host collection is turned off in the config.'];
    }

    $path = hostDatabasePath($config);
    $pdo = hostDb($config, false);
    if ($pdo === null) {
        return [
            'available' => false,
            'sample' => null,
            'reason' => is_file($path)
                ? 'The host database exists but this account cannot read it. Set '
                    . 'host.owner in the config to the account the dashboard runs as, '
                    . 'then run host-cron.php once as root.'
                : 'No host readings yet. This needs a second collector, run by root.',
            'cron' => hostCronLine(__DIR__),
            'command' => hostCronCommand(__DIR__),
        ];
    }

    try {
        $sample = $pdo->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')->fetch() ?: null;
    } catch (Throwable) {
        return ['available' => false, 'sample' => null,
                'reason' => 'The host database is unreadable or not yet initialised.'];
    }

    if (!$sample) {
        return [
            'available' => false,
            'sample' => null,
            'reason' => 'The host database is set up, but no readings have been recorded yet.',
            'cron' => hostCronLine(__DIR__),
            'command' => hostCronCommand(__DIR__),
        ];
    }

    $withMonitoringWorkload = static function (array $row): array {
        if (($row['mem_total'] ?? null) !== null && ($row['mem_available'] ?? null) !== null) {
            $row['committed_bytes'] = max(0, (int)$row['mem_total'] - (int)$row['mem_available']);
            $row['workload_committed_bytes'] = ($row['monitoring_pss_bytes'] ?? null) === null
                ? null
                : max(0, $row['committed_bytes'] - (int)$row['monitoring_pss_bytes']);
        }
        if (($row['procs_running'] ?? null) !== null && ($row['monitoring_runnable'] ?? null) !== null) {
            $row['workload_runnable'] = max(0,
                (int)$row['procs_running'] - (int)$row['monitoring_runnable']);
        }
        return $row;
    };
    $sample = $withMonitoringWorkload($sample);

    $id = (int)$sample['id'];
    [$from, $to] = window();

    /*
     * Whether root's collector has caught up with this dashboard.
     *
     * The two halves of this install are upgraded separately and there is no
     * order that avoids a window between them: the host database is migrated by
     * host-cron.php running as root, and the dashboard opens it read-only
     * precisely so that it can never be the thing that rewrites root's file. So
     * between uploading these pages and root's cron firing - a minute, or a
     * fortnight if the crontab was never set up - the columns this page is
     * built on genuinely do not exist yet.
     *
     * Asked rather than discovered by catching the failure. The first version
     * let the query throw, which took the whole request down with
     * "no such column: cpu_user_ticks" - a true statement, addressed to nobody,
     * that loses the gauges and the LVE table as well even though both are read
     * from columns that have been there for years.
     */
    $ready = columnExists($pdo, 'host_samples', 'cpu_user_ticks');
    $monitoringReady = columnExists($pdo, 'host_samples', 'monitoring_cpu_percent');
    $notReady = 'The host collector has not recorded the processor breakdown yet. It is '
        . 'written by host-cron.php running as root, which adds these columns the first '
        . 'time it runs after an upgrade — so this fills in on its next collection.';

    /*
     * Only the columns the differencing needs.
     *
     * host_samples is eighty columns wide and a day of it is fifteen hundred
     * rows; selecting * here to save typing would ship a megabyte of memory
     * figures to draw a CPU chart.
     */
    $rows = [];
    if ($ready) {
        $series = $pdo->prepare(
            'SELECT taken_at, cpu_percent, '
            . ($monitoringReady
                ? 'monitoring_cpu_percent, monitoring_processes, monitoring_runnable, '
                : 'NULL AS monitoring_cpu_percent, NULL AS monitoring_processes, NULL AS monitoring_runnable, ')
            . 'cpu_user_ticks, cpu_nice_ticks, cpu_system_ticks,
                    cpu_idle_ticks, cpu_iowait_ticks, cpu_irq_ticks, cpu_softirq_ticks,
                    cpu_steal_ticks, ctxt_switches, interrupts, forks, procs_running,
                    procs_blocked, cpu_pressure_some, load1, cpu_cores
             FROM host_samples WHERE taken_at BETWEEN ? AND ? ORDER BY taken_at'
        );
        $series->execute([$from, $to]);
        $rows = $series->fetchAll();
    }

    /*
     * cpu_idle_ticks is stored as idle plus iowait, because that is what the
     * busy percentage is worked out against. The state breakdown wants them
     * apart, so idle on its own is put back here rather than in the collector -
     * changing what the stored column means would silently rewrite the history
     * every other page reads.
     */
    foreach ($rows as $i => $row) {
        $rows[$i]['cpu_idle_ticks'] = $row['cpu_idle_ticks'] === null
            || $row['cpu_iowait_ticks'] === null
                ? $row['cpu_idle_ticks']
                : (int)$row['cpu_idle_ticks'] - (int)$row['cpu_iowait_ticks'];
    }

    $points = cpuStateSeries($rows);
    $now = $points ? $points[count($points) - 1] : null;

    $of = function (string $sql) use ($pdo, $id): array {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    };

    $cores = $ready ? $of('SELECT * FROM host_cpus WHERE sample_id = ? ORDER BY cpu') : [];

    /*
     * The per-core history summarised rather than returned whole.
     *
     * A day at one reading a minute on a 32-core machine is 46,000 rows, and
     * the question the chart answers - "was it one core or all of them" - is
     * four numbers per reading. The rest is detail nobody plots.
     */
    $spreadRows = [];
    if ($ready) {
        $spreadSeries = $pdo->prepare(
            'SELECT s.taken_at,
                    MAX(c.busy_percent) AS hottest,
                    MIN(c.busy_percent) AS coolest,
                    AVG(c.busy_percent) AS mean,
                    SUM(CASE WHEN c.busy_percent >= 90 THEN 1 ELSE 0 END) AS pinned
             FROM host_cpus c JOIN host_samples s ON s.id = c.sample_id
             WHERE s.taken_at BETWEEN ? AND ? AND c.busy_percent IS NOT NULL
             GROUP BY c.sample_id ORDER BY s.taken_at'
        );
        $spreadSeries->execute([$from, $to]);
        $spreadRows = $spreadSeries->fetchAll();
    }

    /*
     * Ranked on the recent figure, with the ones that only have a lifetime
     * average after them rather than mixed in - a process on its first reading
     * has no recent figure at all, and sorting the two columns together would
     * put a long-dead average above a core being burned right now.
     */
    $processes = $ready ? $of(
        'SELECT * FROM host_processes
         WHERE sample_id = ? AND (cpu_percent IS NOT NULL OR cpu_lifetime_percent IS NOT NULL)
         ORDER BY cpu_percent IS NULL, cpu_percent DESC, cpu_lifetime_percent DESC'
    ) : [];

    $spread = cpuCoreSpread($cores);

    /*
     * The account's own side of the two percentages, for the limits page.
     *
     * Read from the account database rather than inferred from the host one,
     * because that is the only place the cage's view of itself exists - and the
     * whole point of that page is showing the two side by side without
     * pretending either is the other.
     */
    $vitals = $account->query('SELECT * FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1')
        ->fetch() ?: null;

    $lveSeries = $pdo->prepare(
        'SELECT s.taken_at, SUM(l.faults_cpu) AS faults_cpu, SUM(l.faults_mem) AS faults_mem
         FROM host_lve l JOIN host_samples s ON s.id = l.sample_id
         WHERE s.taken_at BETWEEN ? AND ? GROUP BY l.sample_id ORDER BY s.taken_at'
    );
    $lveSeries->execute([$from, $to]);

    return [
        'available' => true,
        'reason' => null,
        'sample' => $sample,
        // What each state means, sent with the data so the legend, the table
        // and the tooltips cannot drift apart from each other.
        'states' => cpuStates(),
        'series' => $points,
        'now' => $now,
        'cores' => $cores,
        'spread' => $spread,
        'spread_series' => $spreadRows,
        'processes' => $processes,
        /*
         * The upgrade window said out loud, above everything else.
         *
         * Without it these pages are five blank charts and a working set of
         * gauges, which reads as broken rather than as waiting - and the thing
         * somebody would go and check first is the one thing that is fine.
         */
        'findings' => $ready
            ? cpuFindings($sample, $now, $spread, $processes)
            // On its own, not alongside the ordinary checks: those would all
            // report "not measured" from the same missing columns, and five
            // restatements of one fact bury the one that says what to do.
            : [finding(
                'unknown',
                'The host collector has not been upgraded yet',
                'This reading has no processor breakdown in it.',
                $notReady . ' The dials above and the account comparison are read from '
                    . 'columns that predate these pages, so those are current; everything '
                    . 'else here fills in on the collector\'s next run.',
                'See when the collectors run',
                'schedule'
            )],
        // Empty string when it is ready, so the pages can print it without
        // having to know which case they are in.
        'schema_reason' => $ready ? '' : $notReady,
        'limits' => accountLimits($vitals, $sample),
        'vitals' => $vitals,
        'lve' => $of('SELECT * FROM host_lve WHERE sample_id = ? ORDER BY faults_cpu DESC, user'),
        'lve_series' => $lveSeries->fetchAll(),
        // So an empty chart can say which kind of empty it is: a collector that
        // has run twice and a collector that has never run look identical
        // otherwise, and only one of them is a problem.
        'sample_count' => (int)$pdo->query('SELECT COUNT(*) FROM host_samples')->fetchColumn(),
        'clock_ticks' => 100,
    ];
}

/**
 * @param PDO $account the account database, which holds the monitoring choices -
 *                     the host database is root's and cannot be written here.
 */
function hostView(array $config, bool $signedIn, PDO $account): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see host readings');
    }

    $watch = daemonWatch($account);

    if (!($config['host']['enabled'] ?? true)) {
        return [
            'available' => false,
            'reason' => 'Host collection is turned off in the config.',
            'off' => true,
            'sample' => null,
        ];
    }

    $path = hostDatabasePath($config);
    $pdo = hostDb($config, false);

    if ($pdo === null) {
        return [
            'available' => false,
            // Told apart, because they need opposite fixes: one means the cron
            // has not run, the other means it ran as root and the dashboard
            // cannot read what it wrote.
            'reason' => is_file($path)
                ? 'The host database exists but this account cannot read it. Set '
                    . 'host.owner in the config to the account the dashboard runs as, '
                    . 'then run host-cron.php once as root.'
                : 'No host readings yet. This needs a second collector, run by root.',
            // The exact line to paste, with this install's real paths in it.
            'cron' => hostCronLine(__DIR__),
            'command' => hostCronCommand(__DIR__),
            'sample' => null,
        ];
    }

    try {
        $sample = $pdo->query('SELECT * FROM host_samples ORDER BY id DESC LIMIT 1')->fetch() ?: null;
    } catch (Throwable) {
        return [
            'available' => false,
            'reason' => 'The host database is unreadable or not yet initialised.',
            'sample' => null,
        ];
    }

    if (!$sample) {
        return [
            'available' => false,
            'reason' => 'The host database is set up, but no readings have been recorded yet.',
            'cron' => hostCronLine(__DIR__),
            'command' => hostCronCommand(__DIR__),
            'sample' => null,
        ];
    }

    $withMonitoringWorkload = static function (array $row): array {
        if (($row['mem_total'] ?? null) !== null && ($row['mem_available'] ?? null) !== null) {
            $row['committed_bytes'] = max(0, (int)$row['mem_total'] - (int)$row['mem_available']);
            $row['workload_committed_bytes'] = ($row['monitoring_pss_bytes'] ?? null) === null
                ? null
                : max(0, $row['committed_bytes'] - (int)$row['monitoring_pss_bytes']);
        }
        if (($row['procs_running'] ?? null) !== null && ($row['monitoring_runnable'] ?? null) !== null) {
            $row['workload_runnable'] = max(0,
                (int)$row['procs_running'] - (int)$row['monitoring_runnable']);
        }
        return $row;
    };
    $sample = $withMonitoringWorkload($sample);

    $id = (int)$sample['id'];
    $of = function (string $sql) use ($pdo, $id): array {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    };

    // Once: this walks a query per service, and the array below wants it twice.
    $services = hostServiceGrowth($pdo, time());

    // Validated against the known list inside hostServiceSeries, so an unknown
    // name falls back to the default rather than reaching the query.
    $metric = (string)($_GET['metric'] ?? 'pss');

    // Throughput per service, folded onto the growth rows so the table can show
    // memory and I/O for the same service side by side.
    $io = hostServiceIoRates($pdo);
    foreach ($services as $i => $service) {
        $services[$i]['read_rate'] = $io[$service['name']]['read'] ?? null;
        $services[$i]['write_rate'] = $io[$service['name']]['write'] ?? null;
    }

    [$from, $to] = window();
    $monitoringReady = columnExists($pdo, 'host_samples', 'monitoring_rss_bytes');
    $series = $pdo->prepare(
        'SELECT taken_at, mem_total, mem_available, used_bytes, cache_bytes, unaccounted,
                slab, shmem, swap_total, swap_free, pss_sum_bytes, proc_count, '
        . ($monitoringReady
            ? 'monitoring_processes, monitoring_runnable, monitoring_cpu_percent, monitoring_rss_bytes, monitoring_pss_bytes '
            : 'NULL AS monitoring_processes, NULL AS monitoring_runnable, NULL AS monitoring_cpu_percent, '
                . 'NULL AS monitoring_rss_bytes, NULL AS monitoring_pss_bytes ')
        . 'FROM host_samples WHERE taken_at BETWEEN ? AND ? ORDER BY taken_at'
    );
    $series->execute([$from, $to]);
    $rows = array_map($withMonitoringWorkload, $series->fetchAll());

    return [
        'available' => true,
        'reason' => null,
        'sample' => $sample,
        // Worked out here, over exactly the rows being returned, so the card and
        // the chart cannot end up describing different windows.
        'swap' => swapPressure($rows),
        'users' => $of('SELECT * FROM host_users WHERE sample_id = ? ORDER BY rss_bytes DESC'),
        'processes' => $of('SELECT * FROM host_processes WHERE sample_id = ? ORDER BY rss_bytes DESC'),
        'slab' => $of('SELECT * FROM host_slab WHERE sample_id = ? ORDER BY cache_bytes DESC'),
        'files' => $of('SELECT * FROM host_files WHERE sample_id = ? ORDER BY bytes DESC'),
        // Now, a day ago, a week ago, and the change - the figures that tell a
        // leak from a large but steady working set.
        'services' => $services,
        'disks' => hostDiskRates($pdo),
        /*
         * Which processes are doing the I/O, as against which devices are
         * carrying it. The device totals say the machine is busy; this says
         * what to go and look at.
         */
        'io_processes' => hostProcessIoRates($pdo),
        /*
         * Whether the services are alive, which no other figure on this page
         * can answer: a process that is not running uses no memory and no CPU
         * and appears in no ranking.
         */
        'daemons' => daemonStatus($pdo, daemonDefinitions($config), $watch),
        // So an empty disk table can tell "not enough readings yet" apart from
        // "read them and found nothing".
        'sample_count' => (int)$pdo->query('SELECT COUNT(*) FROM host_samples')->fetchColumn(),
        // Charted for the biggest few only: twenty lines on one canvas is a
        // legend, not a chart.
        /*
         * The metric the caller asked for, not a fixed one.
         *
         * This used to return PSS regardless while the dashboard chose its axis
         * from the selector, so picking "Writing" drew memory sizes against a
         * bytes-per-second scale - and every background refresh quietly put the
         * memory data back under a label that still said Writing. Echoing the
         * metric back lets the chart take its axis from what the data actually
         * is rather than from what was requested.
         */
        'service_series' => hostServiceSeries(
            $pdo,
            array_column(array_slice($services, 0, 5), 'name'),
            $from,
            $to,
            $metric
        ),
        'service_metric' => $metric,
        'service_charted' => array_column(array_slice($services, 0, 5), 'name'),
        'service_metrics' => array_map(
            fn(array $m): string => $m['label'],
            hostServiceMetrics()
        ),
        // Summed over unique (device, inode), not over paths.
        'file_totals' => hostFileTotals(
            $of('SELECT * FROM host_files WHERE sample_id = ? ORDER BY bytes DESC')
        ),
        'lve' => $of('SELECT * FROM host_lve WHERE sample_id = ? ORDER BY faults_mem DESC, user'),
        'rows' => $rows,
    ];
}

/**
 * One metric of the service series, on its own.
 *
 * A separate action so changing which figure is charted does not refetch the
 * process list, the slab table, the file list and everything else in the host
 * payload to redraw one canvas.
 */
/**
 * Host disk throughput over the selected range.
 *
 * Its own action rather than a field on `host`, because it answers to the range
 * selector while the rest of that panel is a snapshot of the latest reading -
 * bundling them would make every poll re-read a week of device counters to
 * refresh a table that only ever shows now.
 */
function hostIoSeries(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see host readings');
    }

    $pdo = hostDb($config, false);
    if ($pdo === null) {
        return ['rows' => [], 'available' => false,
                'reason' => 'The host collector is not installed.'];
    }

    [$from, $to] = window();

    return [
        'available' => true,
        'reason' => null,
        'rows' => hostDiskSeries($pdo, $from, $to),
        // So an empty chart can say which kind of empty it is.
        'sample_count' => (int)$pdo->query('SELECT COUNT(*) FROM host_samples')->fetchColumn(),
    ];
}

function hostSeries(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see host readings');
    }

    $pdo = hostDb($config, false);
    if ($pdo === null) {
        return ['rows' => [], 'names' => [], 'metric' => 'pss'];
    }

    $metric = (string)($_GET['metric'] ?? 'pss');
    $names = array_values(array_filter(
        array_map('strval', (array)($_GET['names'] ?? [])),
        fn(string $name): bool => $name !== ''
    ));

    [$from, $to] = window();

    return [
        'metric' => $metric,
        'names' => $names,
        'rows' => hostServiceSeries($pdo, array_slice($names, 0, 8), $from, $to, $metric),
    ];
}

/**
 * Uptime: how long it has been up, what it missed, and why it restarted.
 *
 * Open, like the other read endpoints - a boot time and an uptime percentage
 * describe the machine rather than naming anything on it. The boot *history*
 * is the exception: it comes from the host collector, names kernel versions and
 * is only there for somebody signed in.
 */
function uptimeView(array $config, PDO $pdo, bool $signedIn): array
{
    [$from, $to] = window();

    $rows = $pdo->prepare(
        'SELECT taken_at, boot_at FROM vitals WHERE taken_at BETWEEN ? AND ? ORDER BY taken_at'
    );
    $rows->execute([$from, $to]);
    $readings = $rows->fetchAll();

    $latest = $pdo->query(
        'SELECT taken_at, boot_at FROM vitals WHERE boot_at IS NOT NULL
         ORDER BY taken_at DESC, id DESC LIMIT 1'
    )->fetch() ?: null;

    $analysis = analyseUptime($readings, $from, $to);

    /*
     * Uptime measured from the boot clock, not from the readings.
     *
     * The last reading may be a minute old, or an hour if cron is unwell, and
     * "up for 3 days" should not quietly stop advancing because the collector
     * did. now - boot_at is the machine's own answer.
     */
    $bootAt = $latest ? (int)$latest['boot_at'] : null;
    $upFor = $bootAt !== null ? max(0, time() - $bootAt) : null;

    $out = [
        'boot_at' => $bootAt,
        'up_for' => $upFor,
        // A reading older than a few minutes means this figure is being derived
        // from a stale boot time, which is worth flagging rather than hiding.
        'reading_age' => $latest ? max(0, time() - (int)$latest['taken_at']) : null,
        'analysis' => $analysis,
        'window' => ['from' => $from, 'to' => $to],
        /*
         * Two collectors, two tracks.
         *
         * They fail independently and for different reasons - the account's
         * cron is in a crontab this user owns, root's is not - and a single
         * bar merged from both cannot say which one stopped. Which is the only
         * question worth asking when a page goes quiet.
         */
        'tracks' => collectorTracks($config, $pdo, $from, $to),
    ];

    if (!$signedIn) {
        return $out;
    }

    // From the host database, where the collector stored what `last` said.
    $host = hostDb($config, false);
    if ($host === null) {
        return $out + ['history' => ['available' => false,
            'reason' => 'No host readings yet — the boot history comes from the root collector.',
            'boots' => []]];
    }

    try {
        $stmt = $host->query(
            'SELECT booted_at, kernel, ended, shutdown_at, still_running
             FROM host_boots ORDER BY booted_at DESC LIMIT 30'
        );
        $boots = array_map(
            fn(array $row): array => $row + ['reason' => bootReason((string)$row['ended'])],
            $stmt->fetchAll()
        );
        return $out + ['history' => ['available' => true, 'reason' => null, 'boots' => $boots]];
    } catch (Throwable) {
        return $out + ['history' => ['available' => false,
            'reason' => 'The host collector has not recorded a boot history yet.',
            'boots' => []]];
    }
}

/** What is in the crontab. Signed in only: it is a list of what runs as you. */
/**
 * Everything the host side needs, as instructions rather than as a diagnosis.
 *
 * This is the one part of the install the dashboard genuinely cannot do: it
 * runs as the account, and the collector has to run as somebody who can read
 * every process's /proc entry. So the useful output is not "not configured" but
 * the exact lines to paste, with this install's real paths already in them, and
 * an honest account of what each of them grants.
 *
 * Two ways to run it, because two different people install this. A server
 * administrator adds it to root's crontab. Somebody on shared hosting with no
 * root at all can still add it to their own crontab - it collects much less,
 * and saying which parts go missing is more useful than implying it is useless.
 */
function hostSetup(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the setup instructions');
    }

    $root = __DIR__;
    $dataDir = dirname((string)$config['database']);
    $hostDb = hostDatabasePath($config);
    $owner = trim((string)($config['host']['owner'] ?? ''));
    $me = currentAccountName();

    $exists = is_file($hostDb);

    return [
        'cron_line' => hostCronLine($root),
        'command' => hostCronCommand($root),
        'disk_command' => hostCronCommand($root) . ' --disk',
        'database' => $hostDb,
        'database_exists' => $exists,
        'database_readable' => $exists && is_readable($hostDb),
        'owner' => $owner,
        'account' => $me,
        'data_dir' => $dataDir,
        /*
         * Ownership is the step everybody misses.
         *
         * Run as root, the collector creates a database root owns, and then the
         * dashboard - running as the account - cannot open it. The symptom is
         * "the host database exists but this account cannot read it", which
         * says nothing about what to do. host.owner is what fixes it, and it
         * has to be set before the first run or the file has to be chowned
         * afterwards.
         */
        'owner_set' => $owner !== '',
        'needs_owner' => $owner === '' && $exists && !is_readable($hostDb),
        'steps' => [
            [
                'title' => 'Set who the collector writes for',
                'body' => $owner !== ''
                    ? "host.owner is set to \"{$owner}\". The collector chowns what it "
                        . 'writes to that account, so the dashboard can read it.'
                    : 'Set host.owner in lib/config.php to ' . ($me !== '' ? "\"{$me}\"" : 'this account')
                        . ' — the user the dashboard runs as. Running the collector as root '
                        . 'without it creates a database only root can read, and every host '
                        . 'panel stays empty with no obvious reason.',
                'done' => $owner !== '',
            ],
            [
                'title' => 'Run it once by hand, and read what it says',
                'body' => 'As root: ' . hostCronCommand($root)
                    . ' — it prints what it could and could not see. Doing this before '
                    . 'adding the cron line means any permission problem shows up now, '
                    . 'attached to the command that caused it.',
                'done' => $exists,
            ],
            [
                'title' => 'Add it to a crontab',
                'body' => 'Every five minutes. A reading walks every process in /proc, '
                    . 'which is not free on a busy machine, and nothing it watches moves '
                    . 'faster than that.',
                'done' => $exists && (time() - (int)@filemtime($hostDb)) < 3600,
            ],
            [
                'title' => 'Schedule the disk walk separately',
                'body' => 'The walk of the whole filesystem takes tens of seconds, so it is '
                    . 'not part of the five-minute collection. Once a night is plenty: '
                    . '17 3 * * * ' . hostCronCommand($root) . ' --disk --quiet',
                'done' => false,
            ],
        ],
        /*
         * What root buys, stated so the decision can be made on facts.
         *
         * Somebody on shared hosting has no root and never will, and telling
         * them only "run this as root" leaves them with nothing. Running it as
         * the account works and collects a real subset - which is worth having,
         * and worth being honest about the limits of.
         */
        'as_root' => [
            'Memory for every process on the machine, and per-account totals — '
                . 'inside CageFS the account sees only its own.',
            'Device throughput and saturation from /proc/diskstats.',
            'Space held by deleted files still open, from /proc/<pid>/fd.',
            'Directories and files outside the account, including other homes and /backup.',
            'Whether the machine\'s daemons are running, from systemd.',
            'Why it last restarted, from `last`.',
        ],
        'as_account' => [
            'Its own processes and their memory.',
            'Load average and total memory, which are not virtualised.',
            'Anything under the account\'s own home directory.',
        ],
        'account_cron_line' => '*/5 * * * * ' . hostCronCommand($root) . ' --quiet',
    ];
}

function scheduleRead(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the schedule');
    }
    $state = scheduleState(__DIR__);
    try {
        $state['account_runs'] = $pdo->query(
            'SELECT started_at, jobs, seconds, ok, error FROM collector_runs ORDER BY id DESC LIMIT 12'
        )->fetchAll();
    } catch (Throwable) {
        $state['account_runs'] = [];
    }

    try {
        $host = hostDb($config, false);
        $state['host_runs'] = $host === null ? [] : $host->query(
            'SELECT started_at, jobs, seconds, ok, error FROM host_collector_runs ORDER BY id DESC LIMIT 12'
        )->fetchAll();
        $state['updates'] = [
            'modes' => updateStages($config),
            'interval_seconds' => updateInterval($config),
            'manager' => updateManager($config),
            'runs' => [],
        ];
        if ($host !== null) {
            try {
                $state['updates']['runs'] = $host->query(
                    'SELECT started_at, stage, manager, updates, seconds, ok, error, output
                     FROM host_update_runs ORDER BY id DESC LIMIT 16'
                )->fetchAll();
            } catch (Throwable) {
                // A collector which has not yet been upgraded still has useful run data.
            }
        }
    } catch (Throwable) {
        $state['host_runs'] = [];
        $state['updates'] = ['modes' => updateStages($config), 'interval_seconds' => updateInterval($config),
            'manager' => null, 'runs' => []];
    }

    return $state;
}

/** Saves staged OS-update policy; package commands remain root-only. */
function doUpdatesSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so the update policy cannot be saved');
    }
    $modes = $_POST['modes'] ?? [];
    if (!is_array($modes)) $modes = [];
    $modes = array_values(array_intersect(UPDATE_STAGES, array_unique(array_map('strval', $modes))));
    $interval = max(3600, min(31 * 86400, (int)($_POST['interval_seconds'] ?? 86400)));
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $current['updates'] = ['modes' => $modes, 'interval_seconds' => $interval];
    writeConfigFile($current);
    logAction($pdo, 'updates_policy', null, 'ok', 0, 0,
        $modes === [] ? 'disabled' : implode(', ', $modes) . ' every ' . $interval . ' seconds');
    return ['ok' => true, 'updates' => ['modes' => $modes, 'interval_seconds' => $interval,
        'manager' => updateManager($current)]];
}

function doScheduleSave(bool $post, PDO $pdo): array
{
    requireWrite($post);

    $crontab = readCrontab();
    if (!$crontab['available']) {
        throw new ActionRefused($crontab['reason'] ?? 'crontab is not available here');
    }

    // An empty pick for both jobs removes the block entirely, which is how the
    // dashboard deletes a schedule - there is no separate destructive path.
    $lines = buildCronLines(
        [
            'vitals' => (string)($_POST['vitals'] ?? 'off'),
            'scan' => (string)($_POST['scan'] ?? 'off'),
            'minute' => $_POST['minute'] ?? 7,
            'hour' => $_POST['hour'] ?? 2,
        ],
        (string)($_POST['php'] ?? ''),
        __DIR__
    );

    writeCrontab(replaceManagedBlock($crontab['raw'], $lines));

    logAction($pdo, 'schedule_save', 'crontab', 'ok', 0, 0,
        $lines === [] ? 'schedule removed' : count($lines) . ' line(s) written');

    return ['ok' => true, 'lines' => $lines];
}

/**
 * Kicks off a scan by hand.
 *
 * Refused while one is already running, which is not politeness: two walks of
 * the same tree at once double the IO on a machine somebody is already worried
 * about, and produce two scan rows a few seconds apart that make every growth
 * figure meaningless.
 */
function doScanNow(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $running = $pdo->query(
        'SELECT id, started_at FROM scans WHERE finished_at IS NULL
         ORDER BY id DESC LIMIT 1'
    )->fetch();

    if ($running) {
        $age = time() - (int)$running['started_at'];
        // A row left unfinished by a killed process would otherwise block
        // scanning for ever, so an old one does not count as running.
        if ($age < 3600) {
            throw new ActionRefused(
                'A scan started ' . ($age < 60 ? "{$age} seconds" : round($age / 60) . ' minutes')
                . ' ago is still running.'
            );
        }
    }

    $binaries = phpBinaries();
    if (!$binaries) {
        throw new ActionRefused('No PHP binary could be found to run the scan with.');
    }

    $result = startScan(__DIR__, $binaries[0]);
    if (!$result['started']) {
        logAction($pdo, 'scan_now', null, 'failed', 0, 0, $result['reason'] ?? null);
        throw new ActionRefused($result['reason'] ?? 'Could not start the scan.');
    }

    logAction($pdo, 'scan_now', null, 'ok', 0, 0, 'started by hand');
    return ['ok' => true, 'started' => true];
}

/*
 * Every condition that can raise an email, with the settings behind it.
 *
 * Built from the keys lib/alerts.php actually reads, and named here exactly as
 * they are named there. An earlier version of this listed conditions under
 * invented key names - `disk_percent` where the rule reads `disk_free_percent` -
 * so the checkboxes turned things on and off in a corner of the config nothing
 * consulted. The page looked right and did nothing, which is the worst way for
 * a setting to be wrong.
 *
 * A condition is off when its threshold is zero, because that is already how
 * the rules read them. There is no second flag to disagree with the first.
 *
 * `fields` is what the interface renders and what it may write back. Each one
 * carries its own unit so a percentage cannot be saved as bytes, and its own
 * bounds so a typo cannot set a threshold nothing could ever cross.
 */
function emailConditions(array $config): array
{
    $a = $config['alerts'] ?? [];
    $an = $a['anomalies'] ?? [];

    $number = fn(array $from, string $key, float $fallback): float =>
        isset($from[$key]) ? (float)$from[$key] : $fallback;

    $field = fn(string $key, string $label, string $unit, float $value,
                float $min, float $max, string $hint = '', string $group = 'alerts'): array => [
        'key' => $key, 'label' => $label, 'unit' => $unit, 'value' => $value,
        'min' => $min, 'max' => $max, 'hint' => $hint, 'group' => $group,
    ];

    return [
        [
            'key' => 'disk',
            'name' => 'Running out of disk',
            'detail' => 'Free space below either of these. Two tests because a percentage '
                . 'of a small filesystem is a few hundred megabytes, and a percentage of a '
                . 'large one is more room than most accounts ever use.',
            'switch' => 'disk_free_percent',
            'on' => $number($a, 'disk_free_percent', 5.0) > 0
                || $number($a, 'disk_free_bytes', 0) > 0,
            'fields' => [
                $field('disk_free_percent', 'Free space below', 'percent',
                    $number($a, 'disk_free_percent', 5.0), 0, 90),
                $field('disk_free_bytes', 'Or free space below', 'bytes',
                    $number($a, 'disk_free_bytes', 5368709120), 0, 1099511627776),
            ],
        ],
        [
            'key' => 'inodes',
            'name' => 'Running out of inodes',
            'detail' => 'A filesystem with free gigabytes and no inodes still refuses to '
                . 'create a file, and nothing about the disk figures shows it coming.',
            'switch' => 'inodes_free_percent',
            'on' => $number($a, 'inodes_free_percent', 5.0) > 0,
            'fields' => [
                $field('inodes_free_percent', 'Free inodes below', 'percent',
                    $number($a, 'inodes_free_percent', 5.0), 0, 90),
            ],
        ],
        [
            'key' => 'projected',
            'name' => 'On course to fill up',
            'detail' => 'Free space falling fast enough to run out within this many hours, '
                . 'judged from the recent trend rather than from one reading. This is the '
                . 'one that arrives before the disk is full rather than after.',
            'switch' => 'projected_full_hours',
            'on' => $number($a, 'projected_full_hours', 72.0) > 0,
            'fields' => [
                $field('projected_full_hours', 'Warn when full within', 'hours',
                    $number($a, 'projected_full_hours', 72.0), 0, 8760),
            ],
        ],
        [
            'key' => 'cpu',
            'name' => 'CPU held at the ceiling',
            'detail' => 'All three together: at least this share of readings over this many '
                . 'minutes at or above this percentage. A single spike cannot satisfy that '
                . 'however tall it is, which is the point — a backup starting is not an '
                . 'incident. Inside CageFS the percentage is of the account\'s own allowance, '
                . 'not of the machine.',
            'switch' => 'cpu_percent',
            'on' => $number($a, 'cpu_percent', 95.0) > 0,
            'fields' => [
                $field('cpu_percent', 'At or above', 'percent',
                    $number($a, 'cpu_percent', 95.0), 0, 100),
                $field('cpu_minutes', 'Held for', 'minutes',
                    $number($a, 'cpu_minutes', 15), 1, 1440),
                $field('cpu_share', 'For at least this share of readings', 'fraction',
                    $number($a, 'cpu_share', 0.9), 0.1, 1.0),
            ],
        ],
        [
            'key' => 'growth',
            'name' => 'Something grew suddenly',
            'detail' => 'A category or a single path gaining more than this in one scan '
                . 'interval, or a directory gaining this many files — which is the shape a '
                . 'runaway log or a stuck job makes.',
            'switch' => 'category_jump_bytes',
            'on' => $number($a, 'category_jump_bytes', 0) > 0
                || $number($a, 'path_jump_bytes', 0) > 0,
            'fields' => [
                $field('category_jump_bytes', 'A category grew by', 'bytes',
                    $number($a, 'category_jump_bytes', 1073741824), 0, 1099511627776),
                $field('path_jump_bytes', 'One path grew by', 'bytes',
                    $number($a, 'path_jump_bytes', 536870912), 0, 1099511627776),
                $field('path_jump_files', 'A directory gained', 'files',
                    $number($a, 'path_jump_files', 5000), 0, 10000000),
            ],
        ],
        [
            'key' => 'anomalies',
            'name' => 'Unusual against this machine\'s own baseline',
            'detail' => 'Memory, process counts, swap, load and process age compared with '
                . 'what is normal here rather than against a fixed number. Every proportional '
                . 'test is paired with an absolute floor, so a large percentage of something '
                . 'tiny stays out of your inbox.',
            'switch' => 'anomalies.enabled',
            'on' => (bool)($an['enabled'] ?? true),
            'fields' => [
                $field('anomalies.baseline_hours', 'Compare against the last', 'hours',
                    $number($an, 'baseline_hours', 24), 1, 720, '', 'anomalies'),
                $field('anomalies.min_readings', 'Needing at least', 'readings',
                    $number($an, 'min_readings', 8), 3, 1000, '', 'anomalies'),
                $field('anomalies.service_pss_percent', 'A service using more memory by', 'percent',
                    $number($an, 'service_pss_percent', 25.0), 0, 1000, '', 'anomalies'),
                $field('anomalies.service_pss_floor_bytes', 'and at least', 'bytes',
                    $number($an, 'service_pss_floor_bytes', 67108864), 0, 1099511627776, '', 'anomalies'),
                $field('anomalies.process_multiple', 'This many times the usual process count', 'times',
                    $number($an, 'process_multiple', 3.0), 1, 100, '', 'anomalies'),
                $field('anomalies.process_floor', 'and at least this many', 'processes',
                    $number($an, 'process_floor', 4), 1, 10000, '', 'anomalies'),
                $field('anomalies.age_multiple', 'Running this many times its usual span', 'times',
                    $number($an, 'age_multiple', 3.0), 1, 100, '', 'anomalies'),
                $field('anomalies.load_readings', 'Load above one per core for', 'readings',
                    $number($an, 'load_readings', 3), 1, 1000, '', 'anomalies'),
            ],
        ],
    ];
}

/** A number with its trailing zeros trimmed, for putting in a sentence. */
/**
 * Writes a value at a dotted key, creating what is missing on the way.
 *
 * Only one level of nesting is used today (`anomalies.*`), but writing it as a
 * path rather than a special case means adding another group later is a line in
 * emailConditions rather than a branch here.
 */
function assignConfigValue(array &$into, string $key, mixed $value): void
{
    $parts = explode('.', $key);
    $last = array_pop($parts);

    $cursor = &$into;
    foreach ($parts as $part) {
        if (!isset($cursor[$part]) || !is_array($cursor[$part])) {
            $cursor[$part] = [];
        }
        $cursor = &$cursor[$part];
    }
    $cursor[$last] = $value;
}

function trimNumber(float $value): string
{
    return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
}

function emailSettings(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the email settings');
    }

    $a = $config['alerts'] ?? [];

    $last = $pdo->query(
        "SELECT at, target, outcome, detail FROM actions
          WHERE action IN ('test_email', 'alert_email')
          ORDER BY at DESC LIMIT 10"
    )->fetchAll();

    /*
     * Every rule with its severity, its cooldown, and what is recommended.
     *
     * Both settings beside their recommendation, so that a deliberate choice is
     * distinguishable from a default nobody looked at - which on a page about
     * how loudly things shout is the whole difference between a considered
     * setup and one that was never touched.
     */
    $rules = [];
    foreach (alertCatalogue() as $key => $entry) {
        $rules[] = alertRule($config, $key) + [
            'set_severity' => isset($a['rules'][$key]['severity']),
            'set_cooldown' => isset($a['rules'][$key]['cooldown']),
        ];
    }

    return [
        'enabled' => (bool)($a['enabled'] ?? false),
        'to' => (string)($a['to'] ?? ''),
        'from' => (string)($a['from'] ?? ''),
        'site_url' => (string)($a['site_url'] ?? ''),
        'cooldown_seconds' => (int)($a['cooldown_seconds'] ?? 21600),
        'conditions' => emailConditions($config),
        'rules' => $rules,
        'levels' => severityLevels(),
        // Addresses in the field that will never receive anything, so the page
        // can say which rather than silently dropping them.
        'recipients' => alertRecipients((string)($a['to'] ?? '')),
        'bad_recipients' => badRecipients((string)($a['to'] ?? '')),
        // Whether the host can send at all, which is the usual reason nothing
        // arrives and is not something the settings above can fix.
        'mail_available' => function_exists('mail'),
        'recent' => $last,
        'writable' => configuredSettingsWritable(__DIR__ . '/lib/config.php'),
        'can_change' => accessAllows($config, 'write'),
    ];
}

/**
 * Changes only the email settings, through the same renderer setup.php uses.
 *
 * Deliberately narrow. It reads the config that is in force, replaces four
 * values inside `alerts`, and writes the whole file back through renderConfig -
 * so nothing else in the file can be lost by a form that did not know about it,
 * and the result is the same shape setup.php produces.
 */

/**
 * Replaces lib/config.php with a new set of values.
 *
 * Written to a neighbour and renamed, because a half-written config file is a
 * site that will not start - and `rename` within a directory is atomic, so
 * there is no moment at which the file is partial.
 *
 * PHP caches stat() results for the length of a request and beyond, so anything
 * reading the file immediately afterwards gets the old one. Callers are
 * expected to answer with what they just wrote rather than re-reading; the
 * clearstatcache here is for anything that reads it later in the same request.
 */
function writeConfigFile(array $config): void
{
    $file = __DIR__ . '/lib/config.php';

    try {
        saveConfiguredSettings($config, $file, __DIR__ . '/lib');
    } catch (Throwable $e) {
        throw new ActionRefused('could not save setup.db: ' . $e->getMessage());
    }

    clearstatcache(true, $file);
}

function doEmailSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    // Refused before anything is validated, so the form does not walk somebody
    // through a dozen fields to fail on the write.
    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused(
            'lib/config.php is not writable by the web server, so this cannot save. '
            . 'Change it in setup.php, or edit the file directly.'
        );
    }

    /*
     * Several addresses, separated by semicolons.
     *
     * Refused as a whole if any one of them is unusable, rather than quietly
     * keeping the good ones: a typo in the third of four means one person
     * stops being told and nothing anywhere would have mentioned it.
     */
    $to = trim((string)($_POST['to'] ?? ''));
    $bad = badRecipients($to);
    if ($bad !== []) {
        throw new ActionRefused(
            count($bad) === 1
                ? '"' . $bad[0] . '" does not look like an email address'
                : 'these do not look like email addresses: ' . implode(', ', $bad)
        );
    }

    $from = trim((string)($_POST['from'] ?? ''));
    if ($from !== '' && !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        throw new ActionRefused('the from address does not look like an email address');
    }

    $enabled = ($_POST['enabled'] ?? '0') === '1';
    if ($enabled && $to === '') {
        throw new ActionRefused('turning email on needs an address to send to');
    }

    // $file went when writeConfigFile() was extracted; this needs the path too.
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $current['alerts']['enabled'] = $enabled;
    $current['alerts']['to'] = $to;
    $current['alerts']['from'] = $from;

    $cooldown = (int)($_POST['cooldown_seconds'] ?? 0);
    if ($cooldown > 0) {
        $current['alerts']['cooldown_seconds'] = min(604800, max(60, $cooldown));
    }

    /*
     * The thresholds, each validated against the field that offered it.
     *
     * The bounds come from emailConditions, so the interface and the check are
     * reading one definition rather than two that can drift. A value outside
     * its range is refused by name rather than clamped: silently accepting 500%
     * and storing 100 leaves somebody sure they configured something they did
     * not.
     */
    $fields = [];
    foreach (emailConditions($config) as $condition) {
        foreach ($condition['fields'] as $f) {
            $fields[$f['key']] = $f;
        }
    }

    foreach ($fields as $key => $f) {
        $posted = $_POST['field_' . str_replace('.', '__', $key)] ?? null;
        if ($posted === null || $posted === '') {
            continue;
        }

        $value = (float)$posted;
        if (!is_numeric($posted) || $value < $f['min'] || $value > $f['max']) {
            throw new ActionRefused(sprintf(
                '"%s" must be between %s and %s',
                $f['label'],
                trimNumber((float)$f['min']),
                trimNumber((float)$f['max'])
            ));
        }

        // Whole numbers where a fraction would be meaningless - you cannot wait
        // 15.5 readings, and bytes are bytes.
        $whole = in_array($f['unit'], ['bytes', 'minutes', 'readings', 'files', 'processes', 'hours'], true);
        assignConfigValue($current['alerts'], $key, $whole ? (int)round($value) : $value);
    }

    /*
     * A condition is turned off by zeroing the threshold the rule reads.
     *
     * That is already how the alerting code works - `cpu_percent => 0` means
     * the rule does not run - so unticking writes the thing the rules consult
     * rather than adding a second switch that could disagree with the first.
     *
     * Turning one back on restores what it was, not a default, unless it was
     * off before and there is nothing to restore.
     */
    $site = trim((string)($_POST['site_url'] ?? ''));
    if ($site !== '' && !filter_var($site, FILTER_VALIDATE_URL)) {
        throw new ActionRefused('that is not a web address');
    }
    $current['alerts']['site_url'] = $site;

    /*
     * Per-rule severity and cooldown, stored only where they differ.
     *
     * Writing every rule out with its recommendation would freeze today's
     * recommendations into the config file, so a later change to the
     * catalogue would never reach an existing install.
     */
    $perRule = [];
    foreach (alertCatalogue() as $key => $entry) {
        $severity = (string)($_POST['severity_' . $key] ?? '');
        $cooldown = $_POST['cooldown_' . $key] ?? null;
        $mine = [];

        if ($severity !== '' && $severity !== $entry['severity']) {
            if (!isset(severityLevels()[$severity])) {
                throw new ActionRefused('that is not one of the levels on offer');
            }
            $mine['severity'] = $severity;
        }

        if ($cooldown !== null && $cooldown !== '' && (int)$cooldown !== (int)$entry['cooldown']) {
            $seconds = clampInt($cooldown, 0, 30 * 86400);
            $mine['cooldown'] = $seconds;
        }

        if ($mine !== []) {
            $perRule[$key] = $mine;
        }
    }
    $current['alerts']['rules'] = $perRule;

    $wanted = array_filter(explode(',', (string)($_POST['conditions'] ?? '')));

    foreach (emailConditions($config) as $condition) {
        $on = in_array($condition['key'], $wanted, true);

        if ($condition['switch'] === 'anomalies.enabled') {
            $current['alerts']['anomalies']['enabled'] = $on;
            continue;
        }

        foreach ($condition['fields'] as $f) {
            // Only the thresholds that gate the rule; a companion setting like
            // "how many readings to look back over" is not a switch.
            if (!str_ends_with($f['key'], '_percent') && !str_ends_with($f['key'], '_bytes')
                && !str_ends_with($f['key'], '_hours') && !str_ends_with($f['key'], '_files')) {
                continue;
            }
            if (str_starts_with($f['key'], 'anomalies.')) {
                continue;
            }

            if ($on) {
                $existing = (float)($current['alerts'][$f['key']] ?? 0);
                if ($existing <= 0) {
                    $current['alerts'][$f['key']] = $f['value'] > 0 ? $f['value'] : 0;
                }
            } else {
                $current['alerts'][$f['key']] = 0;
            }
        }
    }

    writeConfigFile($current);

    logAction($pdo, 'email_settings', $to !== '' ? $to : null, 'ok', 0, 0,
        ($enabled ? 'email on' : 'email off') . ', ' . count($wanted) . ' condition(s)');

    /*
     * The new settings come back with the write, rather than being fetched.
     *
     * PHP caches the result of stat() for the length of a request and beyond,
     * so a read issued immediately after this returned the *old* file - which
     * looked exactly like the save having silently failed. Answering with what
     * was just written removes the race rather than papering over it.
     */
    return ['ok' => true, 'settings' => emailSettings($current, $pdo, true)];
}

function doTestEmail(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $result = sendTestEmail($config, $pdo);
    logAction(
        $pdo,
        'test_email',
        $result['to'] ?? null,
        ($result['sent'] ?? false) ? 'ok' : 'failed',
        0,
        0,
        $result['error'] ?? null
    );

    return $result;
}

function doTestTelegram(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = sendTestTelegram($config);
    logAction($pdo, 'test_telegram', $result['to'] ?? null,
        ($result['sent'] ?? false) ? 'ok' : 'failed', 0, 0, $result['error'] ?? null);
    return $result;
}

/**
 * What is wrong with this install, in the terms that actually break it.
 *
 * Written for the moment when the dashboard is returning 500s and there is no
 * error log to be found: it answers "can PHP write where it needs to", "is the
 * database reachable", "which PHP is this" without needing a shell. Open to
 * everybody because a broken install has to be diagnosable by whoever can see
 * it, and it deliberately reports states rather than paths.
 */
/**
 * What the monitor is costing and whether it is still running.
 *
 * The one thing a disk monitor should be embarrassed to get wrong is its own
 * disk use, and the one thing it should never do is stop collecting without
 * saying so. Both are invisible from every other page: a database quietly
 * growing to a gigabyte looks exactly like a database that is fine, and a cron
 * job that stopped last Tuesday leaves every chart looking merely quiet.
 *
 * Two collectors on two clocks, reported separately. They fail independently -
 * the account one is a user crontab, the host one is root's - and merging them
 * would let a working half hide a broken one.
 */
/**
 * What is wrong with this machine, in one list.
 *
 * Signed in only: the findings quote paths, process names and open ports, and
 * "which database is exposed" is not something to hand to a passer-by.
 */
function diagnoseView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the diagnosis');
    }
    return diagnose($config, $pdo);
}

function pathHygieneView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to review path names');
    $enabled = pathHygieneEnabled($config, $pdo);
    return ['enabled' => $enabled, 'last' => $enabled ? pathHygieneLast($pdo) : null,
        'can_change' => accessAllows($config, 'write')];
}

function doPathHygieneRename(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    try { $result = pathHygieneRename($config, $pdo, (string)($_POST['path'] ?? ''), (string)($_POST['name'] ?? '')); }
    catch (ActionRefused $error) { throw $error; }
    logAction($pdo, 'path_hygiene_rename', $result['from'], 'ok', 0, 0, 'renamed to ' . $result['to']);
    return ['ok' => true, 'result' => $result, 'last' => pathHygieneLast($pdo)];
}

/** Keeps a deliberate diagnostic exception out of later runs and alerts. */
function doDiagnosisIgnore(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so this exception cannot be saved');
    }

    $key = (string)($_POST['key'] ?? '');
    if (!preg_match('/^[a-f0-9]{40}$/', $key)) {
        throw new ActionRefused('that is not a diagnostic finding');
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $ignored = array_values(array_unique(array_filter(
        $current['diagnostic_ignores'] ?? [],
        fn(mixed $entry): bool => is_string($entry) && preg_match('/^[a-f0-9]{40}$/', $entry)
    )));
    if (!in_array($key, $ignored, true)) {
        $ignored[] = $key;
        $current['diagnostic_ignores'] = $ignored;
        writeConfigFile($current);
    }

    logAction($pdo, 'diagnosis_ignore', $key, 'ok', 0, 0, 'future checks suppressed');
    return ['ok' => true] + diagnose($current, $pdo);
}

/** Restores an intentionally hidden diagnostic to later checks. */
function doDiagnosisUnignore(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so this exception cannot be removed');
    }

    $key = (string)($_POST['key'] ?? '');
    if (!preg_match('/^[a-f0-9]{40}$/', $key)) {
        throw new ActionRefused('that is not a diagnostic finding');
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $current['diagnostic_ignores'] = array_values(array_filter(
        $current['diagnostic_ignores'] ?? [],
        fn(mixed $entry): bool => $entry !== $key
    ));
    writeConfigFile($current);
    logAction($pdo, 'diagnosis_unignore', $key, 'ok', 0, 0, 'future checks restored');

    return ['ok' => true] + diagnose($current, $pdo);
}

function selfReport(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see the monitor\'s own state');
    }

    $now = time();
    $accountDb = (string)$config['database'];
    $hostDbPath = hostDatabasePath($config);

    /*
     * WAL and shared-memory files count.
     *
     * SQLite in WAL mode keeps a `-wal` alongside the database that can be
     * larger than the database itself between checkpoints, and reporting only
     * the main file understates what is actually on the disk - which is exactly
     * the mistake this page exists to catch other people making.
     */
    $sizeOf = function (string $path): array {
        $parts = [];
        $total = 0;
        foreach (['' => 'database', '-wal' => 'write-ahead log', '-shm' => 'shared memory'] as $suffix => $label) {
            $file = $path . $suffix;
            if (!is_file($file)) {
                continue;
            }
            $bytes = (int)(@filesize($file) ?: 0);
            $total += $bytes;
            $parts[] = ['label' => $label, 'bytes' => $bytes];
        }
        return ['total' => $total, 'parts' => $parts, 'exists' => $parts !== []];
    };

    $rows = function (PDO $db, array $tables): array {
        $out = [];
        foreach ($tables as $table) {
            try {
                $out[] = [
                    'table' => $table,
                    'rows' => (int)$db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn(),
                ];
            } catch (Throwable) {
                // A table an older schema does not have. Absent, not zero.
            }
        }
        usort($out, fn(array $a, array $b): int => $b['rows'] <=> $a['rows']);
        return $out;
    };

    $account = [
        'path' => $accountDb,
        'size' => $sizeOf($accountDb),
        'tables' => $rows($pdo, [
            'vitals', 'scans', 'scan_totals', 'scan_file_types', 'scan_extensions',
            'paths', 'path_sizes', 'alerts', 'actions', 'incidents',
            'process_samples', 'capabilities', 'capability_changes',
        ]),
    ];

    $host = ['path' => $hostDbPath, 'size' => $sizeOf($hostDbPath), 'tables' => []];
    $hostPdo = null;
    try {
        $hostPdo = hostDb($config, false);
    } catch (Throwable) {
        $hostPdo = null;
    }
    if ($hostPdo !== null) {
        $host['tables'] = $rows($hostPdo, [
            'host_samples', 'host_users', 'host_processes', 'host_slab', 'host_files',
            'host_services', 'host_disks', 'host_boots', 'host_daemons', 'host_dirs',
            'host_deleted', 'host_mounts',
            // One row per core per reading, which on a large machine is the
            // fastest-growing table here - worth being visible on the page
            // that says what the database is spending its size on.
            'host_cpus',
        ]);
    }

    /*
     * When each collector last wrote, and whether that is late.
     *
     * "Late" needs an interval to be late against, and the only honest source
     * for that is the gap between the readings themselves - a crontab this
     * process may not be able to read is a worse guess than what the data
     * actually shows. Two intervals of grace before it is called late, so one
     * slow run on a busy machine is not an alarm.
     */
    $collector = function (?int $lastAt, ?int $interval) use ($now): array {
        if ($lastAt === null || $lastAt <= 0) {
            return ['last_at' => null, 'age' => null, 'interval' => null, 'state' => 'never'];
        }
        $age = $now - $lastAt;
        $state = 'ok';
        if ($interval !== null && $interval > 0 && $age > $interval * 2.5) {
            $state = $age > $interval * 12 ? 'stopped' : 'late';
        }
        return ['last_at' => $lastAt, 'age' => $age, 'interval' => $interval, 'state' => $state];
    };

    $vitalsAt = (int)$pdo->query('SELECT MAX(taken_at) FROM vitals')->fetchColumn();
    $vitalsGap = medianGap($pdo->query(
        'SELECT taken_at FROM vitals ORDER BY taken_at DESC LIMIT 30'
    )->fetchAll(PDO::FETCH_COLUMN));

    $scanAt = (int)$pdo->query(
        'SELECT MAX(started_at) FROM scans WHERE finished_at IS NOT NULL'
    )->fetchColumn();
    $scanGap = medianGap($pdo->query(
        'SELECT started_at FROM scans WHERE finished_at IS NOT NULL ORDER BY id DESC LIMIT 20'
    )->fetchAll(PDO::FETCH_COLUMN));

    $hostAt = null;
    $hostGap = null;
    if ($hostPdo !== null) {
        try {
            $hostAt = (int)$hostPdo->query('SELECT MAX(taken_at) FROM host_samples')->fetchColumn();
            $hostGap = medianGap($hostPdo->query(
                'SELECT taken_at FROM host_samples ORDER BY id DESC LIMIT 30'
            )->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable) {
            $hostAt = null;
        }
    }

    $walkAt = null;
    if ($hostPdo !== null) {
        try {
            $walkAt = (int)$hostPdo->query('SELECT MAX(taken_at) FROM host_dirs')->fetchColumn() ?: null;
        } catch (Throwable) {
            $walkAt = null;
        }
    }

    return [
        'account_db' => $account,
        'host_db' => $host,
        'host_readable' => $hostPdo !== null,
        'collectors' => [
            [
                'name' => 'Readings',
                'what' => 'Disk, inodes, CPU, memory and load for this account. '
                    . 'php cron.php --vitals',
                'who' => 'this account',
            ] + $collector($vitalsAt ?: null, $vitalsGap),
            [
                'name' => 'Scans',
                'what' => 'Walks the watched directory and records what is growing. '
                    . 'php cron.php --scan',
                'who' => 'this account',
            ] + $collector($scanAt ?: null, $scanGap),
            [
                'name' => 'Host collection',
                'what' => 'Everything past the account: memory by user, devices, daemons. '
                    . 'php host-cron.php --collect',
                'who' => 'root, or whoever runs the host collector',
            ] + $collector($hostAt, $hostGap),
            [
                'name' => 'Host disk walk',
                'what' => 'The whole filesystem, including what the account cannot see. '
                    . 'php host-cron.php --disk',
                'who' => 'root',
                // No expected interval: this one is on its own schedule and is
                // often run by hand, so "late" would be an invention.
            ] + $collector($walkAt, null),
        ],
        'retention' => retentionRules($config),
    ];
}

/**
 * The usual gap between a series of timestamps, newest first.
 *
 * The median rather than the mean, because one long outage would drag an
 * average out to where nothing is ever late again - which is precisely when
 * this needs to still work.
 */
function medianGap(array $times): ?int
{
    $times = array_values(array_map('intval', $times));
    if (count($times) < 3) {
        return null;
    }

    $gaps = [];
    for ($i = 1; $i < count($times); $i++) {
        $gap = $times[$i - 1] - $times[$i];
        if ($gap > 0) {
            $gaps[] = $gap;
        }
    }
    if ($gaps === []) {
        return null;
    }

    sort($gaps);
    return (int)$gaps[intdiv(count($gaps), 2)];
}

function healthCheck(array $config, PDO $pdo, bool $signedIn): array
{
    $dataDir = dirname((string)$config['database']);
    $scanRoot = (string)$config['scan_root'];

    $checks = [
        'php_version' => PHP_VERSION,
        'php_new_enough' => PHP_VERSION_ID >= 80100,
        'sqlite' => in_array('sqlite', PDO::getAvailableDrivers(), true),
        'data_dir_writable' => is_dir($dataDir) && is_writable($dataDir),
        'database_writable' => is_file($config['database'])
            ? is_writable($config['database'])
            : (is_dir($dataDir) && is_writable($dataDir)),
        'scan_root_readable' => is_dir($scanRoot) && is_readable($scanRoot),
        'schema_version' => (int)$pdo->query(
            'SELECT COALESCE(MAX(version), 0) FROM schema_version'
        )->fetchColumn(),
        'mail_available' => function_exists('mail'),
        'shell_available' => function_exists('shell_exec')
            && !in_array('shell_exec', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true),
        'proc_readable' => @is_readable('/proc/stat'),
        'diskstats_readable' => @is_readable('/proc/diskstats'),
        'io_pressure_available' => @is_readable('/proc/pressure/io'),
        /*
         * The dashboard's own markup, checked against its own policy.
         *
         * .htaccess sends default-src 'self' with no style-src, so a single
         * style="" attribute in index.html is blocked outright - the rule does
         * not apply, the console fills with violations, and the only symptom is
         * something looking slightly wrong. Cheap to check, and it fails
         * silently otherwise.
         */
        'no_inline_styles' => !preg_match(
            '/\sstyle\s*=\s*["\']/i',
            (string)@file_get_contents(__DIR__ . '/index.html')
        ),
        // Always available, and the thing people actually want to see when a
        // kernel feature is missing.
        'kernel' => php_uname('r'),
        'problem_log_entries' => count(recentProblems(problemLogPath($config), 200)),
    ];

    // Why, not just whether. A row saying "needs kernel 4.20+ with CONFIG_PSI"
    // leaves the reader to go and find out which of those they are missing,
    // which is the work this page exists to save.
    $checks['io_pressure_reason'] = pressureDiagnosis();
    $checks['diskstats_reason'] = diskstatsDiagnosis();

    // Only somebody signed in gets told where any of it is.
    if ($signedIn) {
        $checks['data_dir'] = $dataDir;
        $checks['problem_log'] = problemLogPath($config);
        $checks['php_error_log'] = ini_get('error_log') ?: '(PHP is not configured with one)';
        $checks['php_binary_guess'] = PHP_BINDIR . '/php';
    }

    return ['checks' => $checks];
}

function problemLogPath(array $config): string
{
    return dirname((string)$config['database']) . '/error.log';
}

/**
 * The dashboard's own error log.
 *
 * Signed in only: the entries name file paths, class names and sometimes SQL,
 * which is the whole reason they are not sent to the browser in the first
 * place.
 */
function problemLog(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read the problem log');
    }

    $grouped = groupedProblems(problemLogPath($config));

    /*
     * The worst level present, for the badge.
     *
     * A count on its own says how many and not how bad, and those are different
     * decisions: forty deprecation notices are worth reading eventually, one
     * fatal is worth reading now.
     */
    $rank = ['critical' => 0, 'error' => 1, 'warning' => 2, 'notice' => 3, 'info' => 4];
    $worst = null;
    $counts = [];

    foreach ($grouped['rows'] as $row) {
        $level = (string)($row['level'] ?? 'notice');
        $counts[$level] = ($counts[$level] ?? 0) + (int)($row['count'] ?? 1);

        if ($worst === null || ($rank[$level] ?? 9) < ($rank[$worst] ?? 9)) {
            $worst = $level;
        }
    }

    return [
        // realpath, so it is not shown once as data/error.log and again as
        // lib/../data/error.log and read as two different files.
        'path' => realpath(problemLogPath($config)) ?: problemLogPath($config),
        'rows' => $grouped['rows'],
        'lines' => $grouped['lines'],
        'total' => count($grouped['rows']),
        'worst' => $worst,
        'by_level' => $counts,
    ];
}

/**
 * Empties the problem log.
 *
 * Destructive, so it goes through the same door as everything else that is:
 * POST, signed in, CSRF checked. Recorded in the audit log, because "the errors
 * disappeared" should have an explanation somewhere that is not the errors.
 */
/**
 * Switches monitoring of one service on or off.
 *
 * Written to the account database, which is the only one the dashboard can
 * write - host.db belongs to root. The collector reads the choice back on its
 * next run, so the effect is not instant and the dashboard says so rather than
 * pretending the row changed.
 */
/**
 * Asks the root collector to walk the disk on its next run.
 *
 * It cannot run anything as root and does not pretend to. The request is left
 * in the account database, the collector reads it within a few minutes, and the
 * answer is that it says so plainly rather than spinning as if something is
 * happening now.
 */
/**
 * Probes what this account can run, now, and stores the answer.
 *
 * Runs on demand rather than on a schedule: the answer changes when somebody
 * installs something, which is not a thing to poll for. Versions are optional
 * because asking forty programs their version means running forty programs.
 */
function doCheckCapabilities(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $withVersions = ($_POST['versions'] ?? '1') !== '0';
    $probe = probeCapabilities($config, null, $withVersions, true);
    $changes = storeCapabilities($pdo, $probe);

    logAction($pdo, 'check_capabilities', 'account', 'ok', 0, 0,
        sprintf('%d programs in %ss', count($probe['rows']), $probe['seconds']));

    return [
        'ok' => true,
        'seconds' => $probe['seconds'],
        'total' => count($probe['rows']),
        'changes' => $changes,
    ];
}

/**
 * Queues something for the root collector: a capability sweep, or exposing one
 * program to this account.
 */
function doRequestHostTask(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $task = (string)($_POST['task'] ?? '');
    $program = trim((string)($_POST['program'] ?? ''));

    if ($task === 'capabilities') {
        $at = requestHostTask($pdo, 'capabilities');
        logAction($pdo, 'request_host_task', 'capabilities', 'ok', 0, 0, 'queued');
        return ['ok' => true, 'task' => 'capabilities', 'requested_at' => $at];
    }

    if ($task === 'expose') {
        /*
         * Only a program the catalogue knows about.
         *
         * This name ends up in a command the root collector runs, so it is
         * checked against a fixed list rather than merely pattern-matched -
         * the pattern is the second line of defence, not the first.
         */
        $known = array_column(capabilityList($config), 'name');
        if (!in_array($program, $known, true)) {
            throw new ActionRefused('not a program this monitor knows about');
        }

        $at = requestHostTask($pdo, 'expose:' . $program);
        logAction($pdo, 'request_host_task', 'expose:' . $program, 'ok', 0, 0, 'queued');
        return ['ok' => true, 'task' => 'expose', 'program' => $program, 'requested_at' => $at];
    }

    throw new ActionRefused('unknown task');
}

function doRequestHostWalk(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!($config['host']['enabled'] ?? true)) {
        throw new ActionRefused('host collection is turned off in the config');
    }

    $at = requestHostWalk($pdo, 'dashboard');
    logAction($pdo, 'request_disk_walk', 'host', 'ok', 0, 0, 'queued for the next host run');

    return ['ok' => true, 'requested_at' => $at];
}

function doDaemonWatch(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $name = (string)($_POST['name'] ?? '');
    $watched = (string)($_POST['watched'] ?? '') === '1';

    /*
     * Only a service that is actually defined.
     *
     * Otherwise this is a free write of arbitrary strings into the database by
     * anyone with a session, and the table would fill with names nothing will
     * ever read.
     */
    $known = array_column(daemonDefinitions($config), 'name');
    if (!in_array($name, $known, true)) {
        throw new ActionRefused('no such service');
    }

    setDaemonWatch($pdo, $name, $watched);
    logAction($pdo, 'daemon_watch', $name, 'ok', 0, 0, $watched ? 'monitored' : 'not monitored');

    return ['ok' => true, 'name' => $name, 'watched' => $watched];
}

/**
 * Marks alerts read or unread, or removes them.
 *
 * Two different things and both are wanted. Acknowledging stops an alert
 * counting towards the badge while leaving it in the history - which is what
 * you want for something you have seen and dealt with. Deleting is for the
 * ones that were noise, and it is per-row rather than a "clear all" because
 * clearing everything is how a real alert gets swept away with the rest.
 */
function doAlertAction(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $what = (string)($_POST['what'] ?? '');
    $ids = array_values(array_filter(
        array_map('intval', explode(',', (string)($_POST['ids'] ?? ''))),
        fn(int $id): bool => $id > 0
    ));

    if ($ids === [] && $what !== 'ack-all') {
        throw new ActionRefused('no alerts named');
    }

    if ($what === 'ack-all') {
        $stmt = $pdo->prepare('UPDATE alerts SET acknowledged_at = ? WHERE acknowledged_at IS NULL');
        $stmt->execute([time()]);
        logAction($pdo, 'ack_alerts', 'all', 'ok', 0, 0, $stmt->rowCount() . ' marked read');
        return ['ok' => true, 'changed' => $stmt->rowCount()];
    }

    if ($what === 'ack') {
        $changed = setAlertsAcknowledged($pdo, $ids, true);
        logAction($pdo, 'ack_alerts', implode(',', $ids), 'ok', 0, 0, 'marked read');
        return ['ok' => true, 'changed' => $changed];
    }

    if ($what === 'unack') {
        $changed = setAlertsAcknowledged($pdo, $ids, false);
        logAction($pdo, 'unack_alerts', implode(',', $ids), 'ok', 0, 0, 'marked unread');
        return ['ok' => true, 'changed' => $changed];
    }

    if ($what === 'delete') {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM alerts WHERE id IN ($marks)");
        $stmt->execute($ids);
        logAction($pdo, 'delete_alerts', implode(',', $ids), 'ok', 0, 0,
            $stmt->rowCount() . ' removed');
        return ['ok' => true, 'changed' => $stmt->rowCount()];
    }

    throw new ActionRefused('unknown alert action');
}

/** Temporarily holds delivery for one rule while preserving every event. */
function doAlertMute(PDO $pdo, bool $post): array
{
    requireWrite($post);
    $rule = trim((string)($_POST['rule'] ?? ''));
    $seconds = clampInt($_POST['seconds'] ?? 3600, 900, 7 * 86400);
    $reason = (string)($_POST['reason'] ?? '');
    $id = alertMute($pdo, $rule, $seconds, $reason, currentAccountName());
    logAction($pdo, 'alert_mute', $rule, 'ok', 0, 0, 'for ' . $seconds . ' seconds: ' . mb_substr(trim($reason), 0, 240));
    return ['ok' => true, 'id' => $id, 'mutes' => alertMutes($pdo)];
}

/** Ends one temporary mute while retaining its record for the audit trail. */
function doAlertUnmute(PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $mute = alertUnmute($pdo, $id);
    if ($mute === null) throw new ActionRefused('that alert mute does not exist');
    logAction($pdo, 'alert_unmute', (string)$mute['rule'], 'ok', 0, 0, (string)$mute['reason']);
    return ['ok' => true, 'mutes' => alertMutes($pdo)];
}

/** The local catalog is administrative metadata; never expose publisher data to public pages. */
function marketplaceView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to view the marketplace');
    $catalog = marketplaceCatalog($config);
    return ['packages' => marketplacePackages($config, $pdo), 'errors' => $catalog['errors'],
        'can_change' => accessAllows($config, 'write')];
}

function doMarketplaceEnable(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? ''));
    $enabled = (string)($_POST['enabled'] ?? '') === '1';
    try { $package = marketplaceSetEnabled($config, $pdo, $id, $enabled, currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, $enabled ? 'marketplace_enable' : 'marketplace_disable', $id, 'ok', 0, 0,
        $package['version'] . '; ' . implode(', ', $package['capabilities']));
    return ['ok' => true, 'package' => $package];
}

function doMarketplaceInstall(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    try { $result = marketplaceInstallManifest($config, $pdo, (string)($_POST['manifest'] ?? ''), currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'marketplace_install', $result['package']['id'], 'ok', 0, 0,
        $result['package']['version'] . '; local manifest; prior=' . ($result['revision'] ?? 'none'));
    return ['ok' => true, 'package' => $result['package']];
}

/** Installs only the reviewed, bundled manifest; it never accepts a path. */
function doMarketplaceInstallPathHygiene(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $json = @file_get_contents(__DIR__ . '/marketplace/zogograph-path-hygiene.json');
    if (!is_string($json)) throw new ActionRefused('the bundled path-name hygiene manifest is unavailable');
    try {
        $result = marketplaceInstallManifest($config, $pdo, $json, currentAccountName());
        if ($result['package']['id'] !== 'zogograph-path-hygiene') throw new RuntimeException('bundled manifest identity did not match');
    } catch (InvalidArgumentException|RuntimeException $error) {
        throw new ActionRefused($error->getMessage());
    }
    logAction($pdo, 'marketplace_install_bundled', 'zogograph-path-hygiene', 'ok', 0, 0,
        $result['package']['version']);
    return ['ok' => true, 'package' => $result['package']];
}

function marketplaceHistoryView(array $config, bool $post): array
{
    requireWrite($post);
    try { return ['history' => marketplaceHistory($config, trim((string)($_POST['id'] ?? '')))]; }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
}

function doMarketplaceRollback(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? ''));
    $revision = trim((string)($_POST['revision'] ?? ''));
    try { $result = marketplaceRollback($config, $pdo, $id, $revision, currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'marketplace_rollback', $id, 'ok', 0, 0, $result['package']['version'] . '; revision=' . $revision);
    return ['ok' => true, 'package' => $result['package']];
}

/** Signed package records are private administrative evidence, never public catalog data. */
function packageRuntimeView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to view installed packages');
    $publishers = [];
    foreach (packageRuntimeTrustedKeys((array)($config['package_runtime']['trusted_keys'] ?? [])) as $id => $key) {
        $publishers[] = ['key_id' => $id, 'publisher' => $key['publisher']];
    }
    $packages = packageRuntimePackages($pdo); $evidence = packageRuntimeProbeEvidence($config);
    $invokers = (array)((array)($config['package_runtime'] ?? [])['probe_invokers'] ?? []);
    foreach ($packages as &$package) {
        $package['probe_configured'] = isset($invokers[$package['id']]);
        $package['probe_evidence'] = $evidence[$package['id']] ?? null;
        $package['provider_readiness'] = packageRuntimeProviderReadiness($pdo, $config, $package['id']);
    }
    unset($package);
    return ['packages' => $packages, 'trusted_publishers' => $publishers,
        'can_change' => accessAllows($config, 'write')];
}

/** Signed package catalog data is available only to signed-in package users. */
function packageRuntimeLocaleCatalogsView(PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to load package translations');
    return ['catalogs' => packageRuntimeLocaleCatalogs($pdo)];
}

function packageRuntimeDashboardPagesView(PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to load package pages');
    return ['pages' => packageRuntimeDashboardPages($pdo), 'cards' => packageRuntimeDashboardCards($pdo)];
}

/** Archive bytes are accepted only through PHP's upload temporary file. */
function doPackageRuntimeInstallArchive(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $file = $_FILES['archive'] ?? null;
    if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
        throw new ActionRefused('choose a package ZIP archive to upload');
    }
    try { $installed = packageRuntimeInstallArchive($config, $pdo, $file['tmp_name'], currentAccountName()); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_install_archive', $installed['id'], 'ok', 0, 0, $installed['version'] . '; signed ZIP archive');
    return ['ok' => true, 'package' => $installed];
}

function doPackageRuntimeRemove(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post); $id = trim((string)($_POST['id'] ?? ''));
    try { packageRuntimeRemove($config, $pdo, $id); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_remove', $id, 'ok', 0, 0, 'immutable files and registry removed; private package data retained');
    return ['ok' => true, 'id' => $id];
}

/** Runs a bounded, no-secret diagnostic only; it does not enable the package. */
function doPackageRuntimeProbe(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post); $id = trim((string)($_POST['id'] ?? ''));
    $operation = trim((string)($_POST['operation'] ?? 'capability'));
    try {
        $input = [];
        if ($operation === 'custody_endpoint_probe') {
            $settings = packageRuntimeSettings($pdo, $config, $id);
            $input = ['service_base_url' => $settings['service_base_url'] ?? null,
                'tls_spki_pin' => $settings['tls_spki_pin'] ?? null];
        }
        $result = packageRuntimeInvokeProbe($pdo, $config, $id, $operation, $input);
        packageRuntimeRecordProbe($config, $id, $operation, $result);
    }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_probe', $id, 'ok', 0, 0, $operation . '; state=' . $result['state']);
    return ['ok' => true, 'id' => $id, 'operation' => $operation, 'result' => $result,
        'warning' => 'This isolated no-secret probe is diagnostic evidence only. It did not enable the package, send an enrollment credential, or activate provider operations.'];
}

function doPackageRuntimeCustodyDiagnosticApprove(PDO $pdo, bool $post): array
{
    requireWrite($post); $id = trim((string)($_POST['id'] ?? ''));
    try { packageRuntimeApproveCustodyDiagnostic($pdo, $id, currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_custody_diagnostic_approve', $id, 'ok', 0, 0, 'key_provider, network_client only; package remains inactive');
    return ['ok' => true, 'packages' => packageRuntimePackages($pdo)];
}

function doPackageRuntimeCustodyEnrollmentCheck(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    try { $result = packageRuntimeInvokeCustodyEnrollmentCheck($pdo, $config); packageRuntimeRecordProbe($config, 'zogograph-key-custody', 'custody_enrollment_check', $result); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_custody_enrollment_check', 'zogograph-key-custody', 'ok', 0, 0, 'credential accepted; no envelope, unwrap, or key operation requested');
    return ['ok' => true, 'result' => $result,
        'warning' => 'The enrolled-client credential was delivered only to the configured one-shot credential-check sandbox. This checked subscription readiness only; it did not create an envelope, unwrap a key, or access a Zogograph database.'];
}

/** Explicitly adds one optional recovery envelope; it never uploads ciphertext
 * or changes the local verified recovery copy. */
function doPackageRuntimeCustodyEnvelopeStore(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if ((string)($_POST['confirm'] ?? '') !== 'store-remote-envelope') throw new ActionRefused('confirm remote envelope storage before sending an artifact data key to custody');
    $artifactId = trim((string)($_POST['artifact_id'] ?? ''));
    try { $result = packageRuntimeStoreCustodyEnvelope($pdo, $config, __DIR__, $artifactId); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_custody_envelope_store', $artifactId, 'ok', 0, 0, 'stored optional remote DEK envelope receipt; local recovery copy retained');
    return ['ok' => true, 'artifact_id' => $artifactId, 'result' => $result,
        'warning' => 'The custody sandbox received only this artifact’s in-memory data key and enrolled-client credentials. It stored a sealed key envelope; no Zogograph database, artifact bytes, plaintext, local recovery key, or billing credential was sent. Local recovery remains required.'];
}

function doPackageRuntimeCustodyRecoveryDrill(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if ((string)($_POST['confirm'] ?? '') !== 'run-remote-recovery-drill') throw new ActionRefused('confirm the remote recovery drill before requesting a temporary custody key release');
    $artifactId = trim((string)($_POST['artifact_id'] ?? ''));
    try { $result = packageRuntimeVerifyCustodyEnvelope($pdo, $config, __DIR__, $artifactId); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_custody_recovery_drill', $artifactId, 'ok', 0, 0, 'remote key release authenticated a sealed artifact; key and temporary plaintext discarded');
    return ['ok' => true, 'artifact_id' => $artifactId, 'result' => $result,
        'warning' => 'The released key was used only in memory to authenticate this sealed artifact. The temporary recovered file was removed, and neither the key nor plaintext was returned to the browser.'];
}

function doPackageRuntimeEnable(PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? ''));
    $enabled = (string)($_POST['enabled'] ?? '') === '1';
    $grants = preg_split('/[\s,]+/', trim((string)($_POST['grants'] ?? ''))) ?: [];
    try { packageRuntimeSetEnabled($pdo, $id, $grants, $enabled, currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, $enabled ? 'package_runtime_enable' : 'package_runtime_disable', $id, 'ok', 0, 0,
        implode(', ', $grants));
    return ['ok' => true, 'packages' => packageRuntimePackages($pdo)];
}

function doPackageRuntimeRevoke(PDO $pdo, bool $post): array
{
    requireWrite($post); $id = trim((string)($_POST['id'] ?? '')); $reason = trim((string)($_POST['reason'] ?? ''));
    try { packageRuntimeRevoke($pdo, $id, $reason, currentAccountName()); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_revoke', $id, 'ok', 0, 0, $reason);
    return ['ok' => true, 'packages' => packageRuntimePackages($pdo)];
}

function doPackageRuntimeLiftRevocation(PDO $pdo, bool $post): array
{
    requireWrite($post); $id = trim((string)($_POST['id'] ?? ''));
    try { packageRuntimeLiftRevocation($pdo, $id); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_unrevoke', $id, 'ok', 0, 0, 'requires explicit re-enable');
    return ['ok' => true, 'packages' => packageRuntimePackages($pdo)];
}

function packageRuntimeSettingsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'write')) throw new ActionRefused('administrator access is required for package settings');
    try { return ['id' => trim((string)($_GET['id'] ?? '')), 'settings' => packageRuntimeSettings($pdo, $config, trim((string)($_GET['id'] ?? '')))]; }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
}

function doPackageRuntimeSettingsSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? ''));
    try {
        $settings = json_decode((string)($_POST['settings'] ?? ''), true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($settings)) throw new InvalidArgumentException('package settings must be a JSON object');
        packageRuntimeSaveSettings($pdo, $config, $id, $settings, currentAccountName());
    } catch (JsonException) { throw new ActionRefused('package settings must be valid JSON'); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_settings_save', $id, 'ok', 0, 0, 'package settings updated');
    return ['ok' => true, 'id' => $id, 'settings' => packageRuntimeSettings($pdo, $config, $id)];
}

/** Secret metadata is administrator-only; values remain unreadable after creation. */
function packageRuntimeSecretsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'write')) throw new ActionRefused('administrator access is required for package secrets');
    $id = trim((string)($_GET['id'] ?? ''));
    try { return ['id' => $id, 'secrets' => packageRuntimeSecrets($pdo, $config, $id)]; }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
}

function doPackageRuntimeSecretCreate(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? '')); $value = (string)($_POST['value'] ?? '');
    try { $reference = packageRuntimeSecretStore($pdo, $config, $id, $value, currentAccountName()); }
    catch (InvalidArgumentException|RuntimeException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_secret_create', $id, 'ok', 0, 0, 'encrypted package secret reference created');
    return ['ok' => true, 'id' => $id, 'reference' => $reference,
        'warning' => 'Store the returned reference in package settings. The secret value is never returned.'];
}

function doPackageRuntimeSecretDelete(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $id = trim((string)($_POST['id'] ?? '')); $reference = trim((string)($_POST['reference'] ?? ''));
    try { packageRuntimeSecretDelete($pdo, $config, $id, $reference); }
    catch (InvalidArgumentException $error) { throw new ActionRefused($error->getMessage()); }
    logAction($pdo, 'package_runtime_secret_delete', $id, 'ok', 0, 0, 'encrypted package secret reference deleted');
    return ['ok' => true, 'id' => $id, 'reference' => $reference];
}

function encryptionStatusView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to view encryption status');
    return encryptionStatus($pdo, $config, __DIR__) + [
        'setup_configuration' => setupStoreConfigurationProtectionForSettings($config, __DIR__),
        'can_change' => accessAllows($config, 'write'),
    ];
}

/** Creates a deliberate first/replacement copy from the signed-in dashboard. */
function doEncryptionSeal(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if (($config['encryption']['mode'] ?? 'none') !== 'sealed_copies_local') {
        throw new ActionRefused('choose verified encrypted copies in setup before creating a sealed artifact');
    }
    try {
        $sealed = encryptionSealConfiguredCopies($config, $pdo, __DIR__);
        if ($sealed === []) throw new RuntimeException('no selected stores are available');
        encryptionRecordArtifacts($pdo, $sealed);
        $pruned = encryptionPruneConfiguredCopies($config, $pdo);
        logAction($pdo, 'encryption_seal', null, 'ok', 0, count($sealed), 'created verified encrypted copies from the Protection page');
        return ['ok' => true, 'created' => count($sealed), 'pruned' => (int)$pruned['removed'],
            'warnings' => $pruned['warnings'], 'status' => encryptionStatus($pdo, $config, __DIR__)];
    } catch (Throwable $error) {
        logAction($pdo, 'encryption_seal', null, 'failed', 0, 0, 'could not create verified encrypted copies');
        throw new ActionRefused('could not create verified encrypted copies; review Protection status and the server problem log');
    }
}

/** Records recovery evidence only after independently reopening every current copy. */
function doEncryptionRecoveryDrill(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    if (($config['encryption']['mode'] ?? 'none') !== 'sealed_copies_local') {
        throw new ActionRefused('choose verified encrypted copies in setup before running a recovery drill');
    }
    try {
        $drill = encryptionRunRecoveryDrill($config, $pdo, __DIR__);
        logAction($pdo, 'encryption_recovery_drill', null, 'ok', 0, (int)$drill['tested'], 'non-destructive encrypted-copy recovery drill completed');
        return ['ok' => true, 'tested' => (int)$drill['tested'], 'warnings' => $drill['warnings'],
            'status' => encryptionStatus($pdo, $config, __DIR__)];
    } catch (Throwable $error) {
        logAction($pdo, 'encryption_recovery_drill', null, 'failed', 0, 0, 'could not complete encrypted-copy recovery drill');
        throw new ActionRefused('could not complete the recovery drill; review Protection status and the server problem log');
    }
}

function doClearProblems(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $path = problemLogPath($config);
    $removed = clearProblems($path);
    logAction($pdo, 'clear_problems', $path, 'ok', 0, $removed, "$removed entries");

    return ['ok' => true, 'removed' => $removed];
}

/** Starts a fresh audit trail, retaining an explicit record of the reset itself. */
function doClearAudit(PDO $pdo, bool $post): array
{
    requireWrite($post);
    $removed = (int)$pdo->query('SELECT COUNT(*) FROM actions')->fetchColumn();
    $pdo->exec('DELETE FROM actions');
    logAction($pdo, 'audit_cleared', null, 'ok', 0, 0,
        $removed . ' earlier entr' . ($removed === 1 ? 'y' : 'ies') . ' removed');
    return ['ok' => true, 'removed' => $removed];
}

/**
 * When the host collector last ran and how often it usually does.
 *
 * Carried on the session response, which every page already fetches, so the
 * countdown ring costs no request of its own. The interval is the median gap
 * between recent readings rather than anything read from a crontab: two
 * readings five minutes apart are better evidence of the schedule than a config
 * file describing a job that may never have been installed.
 */
function hostClock(array $config): array
{
    $out = ['last_at' => null, 'interval' => null, 'pending' => [],
            'account_last_at' => null, 'account_interval' => null];

    /*
     * The account's own collector, measured the same way as the host's.
     *
     * From the readings rather than from the crontab: two readings five minutes
     * apart are better evidence of the schedule than a config file describing a
     * job that may never have been installed.
     */
    try {
        $mine = db($config)->query(
            'SELECT taken_at FROM vitals ORDER BY taken_at DESC LIMIT 20'
        )->fetchAll(PDO::FETCH_COLUMN);

        if ($mine !== []) {
            $out['account_last_at'] = (int)$mine[0];
            $out['account_interval'] = medianGap($mine);
        }
    } catch (Throwable) {
        // No readings yet. Both stay null and the ring stays empty.
    }

    try {
        $host = hostDb($config, false);
        if ($host === null) {
            return $out;
        }

        $times = $host->query(
            'SELECT taken_at FROM host_samples ORDER BY id DESC LIMIT 20'
        )->fetchAll(PDO::FETCH_COLUMN);

        if ($times !== []) {
            $out['last_at'] = (int)$times[0];
            $out['interval'] = medianGap($times);
        }

        $out['pending'] = outstandingHostTasks($config, $host);
    } catch (Throwable) {
        return $out;
    }

    return $out;
}

/**
 * What has been asked of the host collector and not yet come back.
 *
 * Each carries where its result will appear, so a page can be linked to when it
 * lands rather than the reader being told "it is ready" and left to find it.
 *
 * Completion is decided the same way everywhere: something was recorded at or
 * after the moment it was asked for. No write back into the account's database
 * is needed, which matters because the collector runs as root and the account's
 * database is the one thing it deliberately only reads.
 *
 * @return list<array>
 */
function outstandingHostTasks(array $config, PDO $host): array
{
    $out = [];

    try {
        $account = db($config);
        $rows = $account->query('SELECT task, requested_at FROM host_tasks')->fetchAll();
    } catch (Throwable) {
        return $out;
    }

    foreach ($rows as $row) {
        $task = (string)$row['task'];
        $requested = (int)$row['requested_at'];
        $done = 0;
        $what = null;

        $capture = parseCaptureTask($task);
        if ($capture !== null) {
            $stmt = $host->prepare(
                'SELECT MAX(taken_at) FROM host_captures WHERE interface = ? AND filter = ?'
            );
            $stmt->execute([$capture['interface'], $capture['filter']]);
            $done = (int)$stmt->fetchColumn();
            $what = [
                'label' => sprintf('a %s capture on %s',
                    captureFilter($capture['filter'])['label'] ?? $capture['filter'],
                    $capture['interface']),
                'view' => 'net-capture',
            ];
        } elseif ($task === HOSTDISK_TASK) {
            try {
                $done = (int)$host->query('SELECT MAX(taken_at) FROM host_walks')->fetchColumn();
            } catch (Throwable) {
                $done = 0;
            }
            $what = ['label' => 'a walk of the whole disk', 'view' => 'host-disk'];
        } elseif ($task === 'capabilities' || str_starts_with($task, 'expose:')) {
            try {
                $done = (int)$host->query(
                    'SELECT MAX(ran_at) FROM host_capability_runs'
                )->fetchColumn();
            } catch (Throwable) {
                $done = 0;
            }
            $what = [
                'label' => $task === 'capabilities'
                    ? 'a check of which programs are available'
                    : 'exposing ' . substr($task, 7) . ' inside the cage',
                'view' => 'capabilities',
            ];
        }

        if ($what === null || $done >= $requested) {
            continue;
        }

        $out[] = $what + ['task' => $task, 'requested_at' => $requested];
    }

    return $out;
}

function sessionState(array $config): array
{
    return [
        // See hostClock: the inner countdown ring, and the pending-task banners.
        'host_clock' => hostClock($config),
        'signed_in' => isSignedIn(),
        'csrf' => isSignedIn() ? csrfToken() : null,
        'configured' => ($config['password_hash'] ?? '') !== '',
        'access' => accessView($config),
        'can_revoke_sessions' => isSignedIn() && accessAllows($config, 'write'),
    ];
}

/* ----------------------------------------------------------------- write -- */

function doSignIn(array $config, bool $post, PDO $pdo): array
{
    if (!$post) {
        throw new ActionRefused('sign in must be a POST');
    }
    $username = trim((string)($_POST['username'] ?? ''));
    if (signInRateLimited($pdo)) {
        // Match wrong-password timing and wording. A rate limit must not be an
        // oracle for account existence or a way to enumerate client state.
        usleep(random_int(150000, 350000));
        logAction($pdo, 'sign_in_limited', null, 'refused', 0, 0, 'rate limited');
        http_response_code(401);
        return ['error' => 'Incorrect password.'];
    }
    $mfaMethod = null;
    if (!signIn($config, (string)($_POST['password'] ?? ''), $username, (string)($_POST['totp'] ?? ''), $pdo, $mfaMethod)) {
        /*
         * A failed attempt is recorded; the password is not.
         *
         * Repeated failures from one address are the only sign this dashboard
         * can give that somebody is guessing, and they are worth having. What
         * is logged is the fact and the address - never the string tried, which
         * is very often somebody's password for something else.
         */
        logAction($pdo, 'sign_in', null, 'refused', 0, 0, 'wrong password');

        // One message for every failure, so it cannot be used to learn whether
        // a password has been configured at all.
        http_response_code(401);
        return ['error' => 'Incorrect password.'];
    }

    logAction($pdo, 'sign_in', null, 'ok', 0, 0, trim(($username === '' ? '' : $username)
        . ($mfaMethod === null ? '' : '; ' . $mfaMethod)) ?: null);
    return ['signed_in' => true, 'csrf' => csrfToken(), 'access' => accessView($config)];
}

/** Replaces this signed-in account's recovery codes after a fresh TOTP proof. */
function doTotpRecoveryCodes(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $account = (string)($_SESSION['username'] ?? '');
    $named = !empty($_SESSION['auth_named'])
        ? (($config['access']['users'][$account] ?? null)) : null;
    $secret = signInTotpSecret($config, is_array($named) ? $named : null);
    if ($secret === '') throw new ActionRefused('enable an authenticator before creating recovery codes');
    if (!totpValid($secret, (string)($_POST['totp'] ?? ''))) {
        throw new ActionRefused('the current verification code was not accepted');
    }
    $codes = totpRecoveryCodes($pdo, $account);
    logAction($pdo, 'totp_recovery_codes', $account, 'ok', 0, 0, 'replaced eight one-time codes');
    return ['ok' => true, 'codes' => $codes];
}

function doSignOut(PDO $pdo, bool $post): array
{
    if (!$post || !isSignedIn() || !csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('sign in with a current form token to sign out');
    }
    // Only worth a line if there was a session to end.
    logAction($pdo, 'sign_out', null, 'ok', 0, 0, null);
    signOut();
    return ['signed_in' => false];
}

/** Ends every authenticated browser session, including the one requesting it. */
function doRevokeSessions(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    global $configFile;
    $config['auth'] = (array)($config['auth'] ?? []);
    $config['auth']['session_epoch'] = authSessionEpoch($config) + 1;
    try {
        saveConfiguredSettings($config, $configFile, __DIR__ . '/lib');
    } catch (Throwable) {
        throw new ActionRefused('could not end existing sessions; configuration was not changed');
    }
    logAction($pdo, 'session_revoke', 'all signed-in sessions', 'ok', 0, 0, 'authentication generation advanced');
    signOut();
    return ['signed_in' => false, 'revoked' => true];
}

/** Everything destructive shares these checks. */
function requireWrite(bool $post): void
{
    global $config;
    if (!$post) {
        throw new ActionRefused('that must be a POST');
    }
    if (!isSignedIn()) {
        throw new ActionRefused('sign in first');
    }
    if (!accessAllows($config, 'write')) {
        throw new ActionRefused('your group cannot make administrative changes');
    }
    if (!csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('stale form token; reload and try again');
    }
}

/** Records a browser failure in the same log as server failures. */
function doClientProblem(bool $post): array
{
    // This is diagnostic data written to a durable server log. Leaving it open
    // would make an otherwise harmless browser error endpoint a way to fill
    // that log with forged reports, so it has the same protection as every
    // other administrative write.
    requireWrite($post);
    $message = trim((string)($_POST['message'] ?? ''));
    $stack = trim((string)($_POST['stack'] ?? ''));
    if ($message === '') {
        return ['ok' => true];
    }
    logProblem('client-error', substr('Browser: ' . $message . ($stack === '' ? '' : ' | ' . $stack), 0, 8000));
    return ['ok' => true];
}

/** Notes are collaborative content, not administrative configuration. */
function requireNoteWrite(array $config, bool $post): void
{
    if (!$post) {
        throw new ActionRefused('that must be a POST');
    }
    if (!isSignedIn() || !accessAllows($config, 'notes')) {
        throw new ActionRefused('your group cannot write notes');
    }
    if (!csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('stale form token; reload and try again');
    }
}

/** Conversation state belongs to an AI-enabled member, not only an administrator. */
function requireAiUse(array $config, bool $post): void
{
    if (!$post) {
        throw new ActionRefused('that must be a POST');
    }
    if (!isSignedIn() || !accessAllows($config, 'ai')) {
        throw new ActionRefused('your group cannot use the assistant');
    }
    if (!csrfValid($_POST['csrf'] ?? null)) {
        throw new ActionRefused('stale form token; reload and try again');
    }
}

/* ----------------------------------------------------------- peer access -- */

function peersView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'write')) {
        throw new ActionRefused('administrators manage trusted servers');
    }
    $rows = peerDefinitions($config);
    $deliveries = [];
    try {
        $deliveries = peerTransportLatest($pdo);
    } catch (Throwable) {
        // A pre-migration or damaged database still lets an administrator
        // repair peer settings; delivery status is supplementary evidence.
    }
    foreach ($rows as &$row) {
        $token = $_SESSION['peer_tokens'][$row['name']] ?? null;
        $row['connected_as'] = is_array($token) && (int)($token['expires_at'] ?? 0) >= time()
            ? (string)($token['subject'] ?? '') : null;
        $row['remote_vitals'] = $deliveries[$row['name']] ?? null;
        $row['last_received_at'] = $row['remote_vitals']['received_at'] ?? null;
    }
    unset($row);
    return ['rows' => $rows, 'capacity' => peerCapacity($config),
        'writable' => configuredSettingsWritable(__DIR__ . '/lib/config.php')];
}

function peerName(string $name): string
{
    $name = strtolower(trim($name));
    if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,40}$/', $name)) {
        throw new ActionRefused('use a short lowercase server name');
    }
    return $name;
}

function doPeerSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = peerName((string)($_POST['name'] ?? ''));
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $prior = (array)($current['peers'][$name] ?? []);
    if ($prior === [] && peerCapacity($current)['remaining'] === 0) {
        throw new ActionRefused('this edition has reached its remote-host limit');
    }
    $secret = trim((string)($_POST['client_secret'] ?? ''));
    $oidc = [
        'base_url' => trim((string)($_POST['base_url'] ?? '')),
        'redirect_uri' => trim((string)($_POST['redirect_uri'] ?? '')),
        'issuer' => trim((string)($_POST['issuer'] ?? '')),
        'client_id' => mb_substr(trim((string)($_POST['client_id'] ?? '')), 0, 255),
    ];
    $storedSecret = $secret !== '' ? $secret : (string)($prior['client_secret'] ?? '');
    $hasOidcInput = $secret !== '' || array_filter($oidc, fn(string $value): bool => $value !== '') !== [];
    if ($hasOidcInput && (in_array('', $oidc, true) || $storedSecret === '')) {
        throw new ActionRefused('configure every OIDC field and client secret, or leave all OIDC fields blank');
    }
    if ($hasOidcInput) {
        foreach (['base_url', 'redirect_uri', 'issuer'] as $field) $oidc[$field] = peerUrl($oidc[$field]);
    } else {
        $oidc = ['base_url' => '', 'redirect_uri' => '', 'issuer' => '', 'client_id' => ''];
        $storedSecret = '';
    }
    $endpoint = trim((string)($_POST['transport_endpoint'] ?? ''));
    if ($endpoint !== '') $endpoint = peerUrl($endpoint);
    $transport = (string)($prior['transport_secret'] ?? '');
    $newTransport = $transport === '';
    if ($newTransport) {
        $transport = bin2hex(random_bytes(32));
    }
    $peer = [
        'label' => mb_substr(trim((string)($_POST['label'] ?? $name)), 0, 80),
        ...$oidc,
        'client_secret' => $storedSecret,
        'transport_endpoint' => $endpoint,
        'transport_secret' => $transport,
        'transport_pending_until' => $newTransport
            ? time() + PEER_TRANSPORT_PAIR_TTL : (int)($prior['transport_pending_until'] ?? 0),
    ] + peerMetadata($_POST);
    if ($peer['label'] === '') {
        throw new ActionRefused('a peer needs a label');
    }
    $current['peers'][$name] = $peer;
    writeConfigFile($current);
    logAction($pdo, 'peer_save', $name, 'ok', 0, 0, peerHasOidc($peer) ? $peer['issuer'] : 'remote delivery only');
    $out = ['ok' => true] + peersView($current, $pdo, true);
    if ($newTransport) {
        $out['transport_secret'] = $transport;
        $out['transport_endpoint'] = peerTransportEndpoint($peer);
        $out['transport_enrollment'] = peerTransportEnrollment($name, $out['transport_endpoint'], $transport);
    }
    return $out;
}

function doPeerDelete(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = peerName((string)($_POST['name'] ?? ''));
    if (!isset($config['peers'][$name])) {
        throw new ActionRefused('that trusted server does not exist');
    }
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    unset($current['peers'][$name]);
    unset($_SESSION['peer_tokens'][$name]);
    writeConfigFile($current);
    logAction($pdo, 'peer_delete', $name, 'ok', 0, 0, null);
    return ['ok' => true] + peersView($current, $pdo, true);
}

/** Replaces a remote-delivery credential without touching OIDC credentials. */
function doPeerTransportRotate(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = peerName((string)($_POST['name'] ?? ''));
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $peer = $current['peers'][$name] ?? null;
    if (!is_array($peer)) {
        throw new ActionRefused('that trusted server does not exist');
    }
    $credential = bin2hex(random_bytes(32));
    $peer['transport_secret'] = $credential;
    $peer['transport_pending_until'] = time() + PEER_TRANSPORT_PAIR_TTL;
    $current['peers'][$name] = $peer;
    writeConfigFile($current);
    logAction($pdo, 'peer_transport_rotate', $name, 'ok', 0, 0, 'old remote credential revoked');
    $endpoint = peerTransportEndpoint($peer);
    return ['ok' => true, 'transport_secret' => $credential, 'transport_endpoint' => $endpoint,
        'transport_enrollment' => peerTransportEnrollment($name, $endpoint, $credential)]
        + peersView($current, $pdo, true);
}

function doPeerTest(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = peerName((string)($_POST['name'] ?? ''));
    $peer = $config['peers'][$name] ?? null;
    if (!is_array($peer)) {
        throw new ActionRefused('save this trusted server before testing it');
    }
    $result = peerTest($peer);
    logAction($pdo, 'peer_test', $name, $result['ok'] ? 'ok' : 'failed', 0, 0,
        $result['error'] ?? $result['issuer'] ?? null);
    return $result;
}

function doPeerRead(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = peerName((string)($_POST['name'] ?? ''));
    $snapshot = peerRemoteSnapshot($config, $name);
    logAction($pdo, 'peer_read', $name, 'ok', 0, 0, (string)($snapshot['host'] ?? ''));
    return $snapshot;
}

/**
 * Removes a directory and everything under it.
 *
 * Its own action rather than a flag on delete-file, so that nothing can turn a
 * single-file delete into a recursive one by changing one parameter - and so
 * that the confirmation the interface shows is attached to the act it is
 * actually confirming.
 */
function doDeleteDir(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    return ['ok' => true] + deleteDirectory($config, $pdo, (string)($_POST['path'] ?? ''));
}

function doDeleteFile(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = deleteFile($config, $pdo, (string)($_POST['path'] ?? ''));
    return ['ok' => true] + $result;
}

function doEmpty(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = emptyCategory($config, $pdo, (string)($_POST['category'] ?? ''));
    return ['ok' => true] + $result;
}

/**
 * Which processes a command-line pattern would hit.
 *
 * Changes nothing, but goes through the same door as the things that do: a POST
 * so a regular expression does not end up in the access log of every proxy
 * between here and the browser, and signed in because it returns command lines.
 */
function doMatchProcesses(array $config, bool $post): array
{
    requireWrite($post);
    return matchProcesses($config, (string)($_POST['pattern'] ?? ''));
}

function doStopProcess(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    /*
     * The name and age are what the caller had on screen, and they are required.
     *
     * Without them this is "signal whatever holds pid 8214 right now", which is
     * a different and much worse thing - see the top of lib/signals.php.
     */
    $result = stopProcess(
        $config,
        $pdo,
        clampInt($_POST['pid'] ?? 0, 0, PHP_INT_MAX),
        (string)($_POST['name'] ?? ''),
        isset($_POST['elapsed']) && $_POST['elapsed'] !== ''
            ? clampInt($_POST['elapsed'], 0, PHP_INT_MAX)
            : null,
        ($_POST['force'] ?? '') === '1'
    );

    return ['ok' => true] + $result;
}

function doStopGroup(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = stopGroup(
        $config,
        $pdo,
        (string)($_POST['name'] ?? ''),
        ($_POST['force'] ?? '') === '1'
    );
    return ['ok' => true] + $result;
}

function doStopMatching(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = stopMatching(
        $config,
        $pdo,
        (string)($_POST['pattern'] ?? ''),
        ($_POST['force'] ?? '') === '1'
    );
    return ['ok' => true] + $result;
}

/* ----------------------------------------------------------------- utils -- */

/** The requested time window, defaulting to the last day. */
function window(): array
{
    $to = clampInt($_GET['to'] ?? time(), 0, PHP_INT_MAX);
    $from = clampInt($_GET['from'] ?? ($to - 86400), 0, PHP_INT_MAX);
    return $from <= $to ? [$from, $to] : [$to, $from];
}

function clampInt(mixed $value, int $min, int $max): int
{
    $n = is_numeric($value) ? (int)$value : $min;
    return max($min, min($max, $n));
}

/* ----------------------------------------------------------- keeping it -- */

/**
 * What both databases hold, what it costs, and what it will cost.
 *
 * The host database is reported beside the account one where it can be read.
 * It is written by root's collector and this process usually cannot open it,
 * which is not a fault - so it says so rather than showing zeros.
 */
function storeView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $out = [
        'account' => retentionReport($pdo, $config),
        'host' => null,
        'host_why' => null,
        'writable' => configuredSettingsWritable(__DIR__ . '/lib/config.php'),
    ];

    // hostDatabasePath() lives in lib/hostdb.php and already knows the default.
    $hostPath = ($config['host']['enabled'] ?? false) ? hostDatabasePath($config) : null;

    if ($hostPath === null || !is_file($hostPath)) {
        $out['host_why'] = 'The host collector has no database here yet.';
        return $out;
    }

    try {
        $host = new PDO('sqlite:' . $hostPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $store = storeTables($host);
        $out['host'] = [
            'file' => databaseFacts($host, $config, $hostPath),
            'tables' => $store['tables'],
            'measured' => $store['measured'],
            'why' => $store['why'],
            /*
             * Read-only from here, on purpose.
             *
             * The host database belongs to root's collector. This account can
             * usually open it to read and should never be the thing that
             * rewrites it - a VACUUM from here would leave a file owned by the
             * web user that the collector then cannot write.
             */
            'writable' => false,
        ];
    } catch (Throwable $e) {
        $out['host_why'] = 'Present, but this account cannot open it: ' . $e->getMessage();
    }

    return $out;
}

/** What the assistant's own database is holding. */
function aiStoreView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    return aiStoreReport($config) + ['who' => aiPersona($config)];
}

/** Runs the slower full check only when an administrator asks for it. */
function doStoreIntegrity(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $result = databaseFullIntegrity($pdo);
    logAction($pdo, 'database_integrity_check', 'account', $result['ok'] ? 'ok' : 'failed', 0, 0,
        implode("\n", array_slice($result['messages'], 0, 5)));
    return ['ok' => $result['ok'], 'result' => $result] + storeView($config, $pdo, true);
}

/** Reclaims the empty space in the assistant's database. */
function doAiCompact(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is no database here to compact');
    }

    $result = compactNow($ai, $config, aiDatabasePath($config));

    logAction($pdo, 'ai_compact', null, 'ok', 0, 0, sprintf(
        'reclaimed %s',
        humanBytes(max(0, $result['before']['bytes'] - $result['after']['bytes']))
    ));

    return ['ok' => true, 'result' => $result] + aiStoreView($config, true);
}

/** Forgets conversations older than a cut-off, and reclaims what they used. */
function doAiForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $days = clampInt($_POST['days'] ?? 30, 0, 3650);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is no database here to clear out');
    }

    $before = aiDatabaseFacts($config);
    $gone = aiForget($ai, $days);

    /*
     * Compacted immediately, because deleting rows from SQLite does not make
     * the file smaller - it makes the space inside it free for reuse. Somebody
     * who has just deleted six months of transcripts to reclaim disk and finds
     * the file exactly the same size will reasonably conclude nothing happened.
     */
    $reclaimed = 0;
    if ($gone['sessions'] > 0) {
        try {
            $result = compactNow($ai, $config, aiDatabasePath($config));
            $reclaimed = max(0, $result['before']['bytes'] - $result['after']['bytes']);
        } catch (ActionRefused) {
            // Not writable, or no room to write the copy. The rows are still
            // gone; only the file size is unchanged, and the page says so.
            $reclaimed = 0;
        }
    }

    logAction($pdo, 'ai_forget', null, 'ok', $gone['sessions'], 0, sprintf(
        '%d conversations older than %d days, %s reclaimed',
        $gone['sessions'],
        $days,
        humanBytes($reclaimed)
    ));

    return [
        'ok' => true,
        'sessions' => $gone['sessions'],
        'reclaimed' => $reclaimed,
        'was' => $before['bytes'],
    ] + aiStoreView($config, true);
}

/** New retention settings, written to the config file. */
function doStoreSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $wanted = retentionSettings($_POST);
    if ($wanted === []) {
        throw new ActionRefused('nothing was changed');
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    foreach ($wanted as $key => $value) {
        $current['retention'][$key] = $value;
    }

    writeConfigFile($current);

    logAction($pdo, 'retention_settings', null, 'ok', 0, 0, implode(', ', array_map(
        fn(string $k, int $v): string => $k . '=' . ($v === 0 ? 'ever' : $v . 'd'),
        array_keys($wanted),
        $wanted
    )));

    return ['ok' => true] + storeView($current, $pdo, true);
}

/**
 * Clears one kind of record older than a given age.
 *
 * The confirmation lives in the browser; what lives here is the refusal to
 * touch anything not in the catalogue, and the note in the action log - which
 * is itself one of the things that can be cleared, so the entry is written
 * before the deletion rather than after it.
 */
function doStorePurge(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $key = (string)($_POST['key'] ?? '');
    $days = clampInt($_POST['days'] ?? 0, 0, 3650);

    logAction($pdo, 'retention_purge', $key, 'ok', 0, 0,
        $days > 0 ? 'older than ' . $days . ' days' : 'everything');

    $result = purgeEntry($pdo, $config, $key, $days);

    return ['ok' => true, 'result' => $result] + storeView($config, $pdo, true);
}

/** Rewrites the database to hand its empty pages back to the disk. */
function doStoreCompact(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $result = compactNow($pdo, $config);

    logAction($pdo, 'database_compact', null, 'ok', 0, 0,
        'reclaimed ' . humanBytes($result['saved']) . ' in ' . $result['seconds'] . 's');

    return ['ok' => true, 'result' => $result] + storeView($config, $pdo, true);
}

/**
 * Rolls up readings now rather than waiting for the cron.
 *
 * The same compaction the nightly run does. Offered by hand because the reason
 * somebody is on this page is usually that the file is larger than they expected
 * and they would like it smaller today.
 */
function doStoreRollup(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $result = compactVitals($pdo, retentionRules($config));

    /*
     * The space goes back straight away, rather than at the next nightly cron.
     *
     * Somebody pressing "roll up old readings now" is doing it because they
     * want the disk back now. Rolling up sixty thousand rows and reporting a
     * file that is exactly the same size reads as a button that did nothing.
     * reclaimSpace only rewrites when a quarter of the file is free pages, so
     * a rollup that freed very little correctly does not pay for a VACUUM.
     */
    $result['space'] = reclaimSpace($pdo);

    logAction($pdo, 'retention_rollup', null, 'ok', 0, 0, sprintf(
        '%d rows became %d, %d protected, %s',
        $result['removed'] + $result['compacted'],
        $result['compacted'],
        $result['protected'],
        $result['space']
    ));

    return ['ok' => true, 'result' => $result] + storeView($config, $pdo, true);
}

/**
 * Tries a pattern against pasted text and reports exactly what it found.
 *
 * No database, no network, no side effects - this only answers a question. A
 * pattern that looks right and quietly captures the wrong group produces a
 * chart of a constant, and the only way to know beforehand is to have tried it
 * against real text.
 */
function doRegexTry(bool $post): array
{
    requireWrite($post);

    $pattern = (string)($_POST['pattern'] ?? '');
    $text = (string)($_POST['text'] ?? '');

    // Bounded because this runs a regular expression somebody typed against
    // text somebody pasted, and both are ways to spend a lot of CPU.
    if (strlen($text) > 512 * 1024) {
        throw new ActionRefused('that is more text than this will test against');
    }
    if (strlen($pattern) > 2000) {
        throw new ActionRefused('that pattern is too long');
    }

    return urlWatchTryRegex($pattern, $text);
}

/**
 * Asks root's collector to rebuild the shared library cache.
 *
 * The fix when a library symlink has been repaired, or a package installed
 * half-way, and programs start failing with "cannot open shared object file".
 * Nothing this account can do itself - /etc/ld.so.cache is root's - so it goes
 * on the queue like any other host request and comes back on the collector's
 * own schedule.
 *
 * The one verb here that writes rather than asks, which is why it is a separate
 * action with its own confirmation rather than another entry in a list of
 * lookups.
 */
function doLdconfig(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!($config['host']['enabled'] ?? false)) {
        throw new ActionRefused(
            'the host collector is not set up here, so there is nobody to ask'
        );
    }

    // hostAnswer() directly: domainHostAnswer() is hard-wired to whois.
    $answer = null;
    try {
        $host = hostDb($config, false);
        if ($host !== null) {
            $answer = hostAnswer($host, 'ldconfig', '');
        }
    } catch (Throwable) {
        $answer = null;
    }

    /*
     * A previous answer is not a reason to skip.
     *
     * Unlike a whois lookup, the point of this is the effect rather than the
     * text - "we ran it an hour ago" is not an answer to "run it now". So it
     * is always queued, and the previous run is shown for context.
     */
    $at = requestHostTask($pdo, hostAskTask('ldconfig', ''), currentAccountName());

    logAction($pdo, 'ldconfig', null, 'ok', 0, 0, 'queued for the host collector');

    return [
        'ok' => true,
        'asked_at' => $at,
        'previous' => $answer === null ? null : [
            'at' => $answer['answered_at'] ?? null,
            'ok' => $answer['ok'] ?? false,
            'error' => $answer['error'] ?? null,
            // Only the tail: ldconfig -v names every directory it walks, which
            // is hundreds of lines nobody reads and all of them uninteresting
            // unless one of them failed.
            'text' => $answer['text'] === null
                ? null
                : implode("\n", array_slice(explode("\n", (string)$answer['text']), -25)),
        ],
    ];
}

/* ---------------------------------------------------------------- notes -- */

/** Every note, and every database copy on disk. */
function notesView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'notes')) {
        throw new ActionRefused('sign in to see the notes');
    }

    return noteList($pdo, $config) + [
        'views' => array_keys(noteViewLabels()),
        'groups' => array_keys(accessGroups($config)),
        'me' => currentAccountName(),
    ];
}

/**
 * Which actions a whole-site snapshot will call.
 *
 * An allow-list rather than "everything in the switch". Half those actions
 * take a request through to somebody else's server or leave work for root's
 * collector, and a snapshot that quietly fired off forty outbound lookups
 * would be a surprising thing for a button called "capture this".
 *
 * @return array<string, string>
 */
function snapshotActions(): array
{
    return [
        'overview' => 'Overview',
        'vitals' => 'Readings',
        'scans' => 'Disk scans',
        'offenders' => 'Largest and fastest growing',
        'processes' => 'Processes',
        'host' => 'The host',
        'cpu' => 'The processor',
        'host-disk' => 'Filesystems',
        'host-io' => 'Disk I/O',
        'alerts' => 'Alerts',
        'uptime' => 'Uptime',
        'network' => 'Network',
        'urls' => 'URL checks',
        'domains' => 'Domains',
        'dns-records' => 'DNS records',
        'self' => 'The monitor itself',
        'store' => 'What it is keeping',
        'capabilities' => 'Capabilities',
        'diagnose' => 'What is wrong',
        'units' => 'Services',
        'backups' => 'Backups',
        'mounts' => 'Filesystem layout',
        'calendar' => 'Calendar',
        'schedule' => 'Schedule',
        'actions' => 'The action log',
    ];
}

/** Human names for the views a note can be pinned to. */
function noteViewLabels(): array
{
    return snapshotActions();
}

/**
 * Saves a note, and captures whatever it was asked to capture.
 *
 * The note is written first and the snapshot attached afterwards, so a capture
 * that fails leaves the note rather than losing both. What failed is reported
 * beside what was saved - a snapshot silently not happening is the worst
 * outcome here, because nobody finds out until they need it.
 */
function doNoteSave(array $config, PDO $pdo, bool $post): array
{
    requireNoteWrite($config, $post);

    $saved = noteSave($pdo, $config, [
        'body' => $_POST['body'] ?? '',
        'scope' => $_POST['scope'] ?? 'page',
        'view' => $_POST['view'] ?? '',
        'chart' => $_POST['chart'] ?? '',
        'link' => $_POST['link'] ?? '',
        'at' => $_POST['at'] ?? time(),
        'broadcast' => ($_POST['broadcast'] ?? '0') === '1',
        'audience' => array_values(array_filter(explode(',', (string)($_POST['audience'] ?? '')), 'strlen')),
    ], currentAccountName());

    $kind = (string)($_POST['snapshot'] ?? 'none');
    $problems = [];
    $captured = [];

    if ($kind !== 'none' && !isset(snapshotKinds()[$kind])) {
        $problems[] = 'that is not a kind of snapshot this makes';
    } elseif ($kind === 'page') {
        /*
         * The page's own data, sent up by the browser.
         *
         * Which sounds backwards - the server could fetch it again - but it is
         * not: what is wanted is what was on screen, and re-fetching a second
         * later gives a different answer during exactly the incident this is
         * for.
         */
        $payload = (string)($_POST['payload'] ?? '');
        if (trim($payload) === '') {
            $problems[] = 'the page sent nothing to capture';
        } else {
            try {
                noteSnapshotSave($pdo, $saved['id'], 'page',
                    (string)($_POST['view'] ?? 'page'), $payload);
                $captured[] = 'the data behind this page';
            } catch (ActionRefused $e) {
                $problems[] = $e->getMessage();
            }
        }
    } elseif ($kind === 'site') {
        [$captured, $problems] = captureWholeSite($config, $pdo, $saved['id']);
    } elseif ($kind === 'databases') {
        [$captured, $problems] = captureDatabases($config, $pdo, $saved['id']);
    }

    /*
     * Pictures are independent of the snapshot kind.
     *
     * Somebody keeping a picture of a chart is not thereby asking for the whole
     * site's data, and making the two one control would force that choice.
     */
    $images = (string)($_POST['images'] ?? '');
    if (trim($images) !== '') {
        $result = noteImagesSave($pdo, $saved['id'], $images);
        if ($result['kept'] !== []) {
            $captured[] = count($result['kept']) === 1
                ? 'a picture of ' . $result['kept'][0]
                : count($result['kept']) . ' pictures';
        }
        $problems = array_merge($problems, $result['problems']);
    }

    logAction($pdo, 'note_save', null, 'ok', 0, 0,
        ($_POST['scope'] ?? 'page') . ', snapshot: ' . $kind);

    return [
        'ok' => true,
        'id' => $saved['id'],
        'captured' => $captured,
        // Reported rather than thrown: the note is saved either way, and losing
        // the note because the snapshot failed would be the wrong trade.
        'problems' => $problems,
    ] + notesView($config, $pdo, true);
}

/**
 * Calls every read-only view and keeps the answers as one document.
 *
 * A view that throws is recorded as having thrown rather than omitted. "This
 * page could not be read at the time" is itself evidence, and a snapshot that
 * silently skipped it would read as though the page had nothing to say.
 *
 * @return array{0: list<string>, 1: list<string>}
 */
function captureWholeSite(array $config, PDO $pdo, int $noteId): array
{
    $document = ['at' => time(), 'views' => []];
    $problems = [];

    foreach (snapshotActions() as $action => $label) {
        try {
            /*
             * Through route(), so a snapshot holds exactly what a page holds.
             *
             * Calling each view function directly would mean this drifting from
             * what the dashboard actually fetches the first time a signature
             * changes, and a snapshot that differs from the page is worse than
             * no snapshot.
             */
            $document['views'][$action] = route($config, $pdo, $action, false);
        } catch (Throwable $e) {
            $document['views'][$action] = ['error' => $e->getMessage()];
            $problems[] = $label . ': ' . $e->getMessage();
        }
    }

    $json = json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

    try {
        noteSnapshotSave($pdo, $noteId, 'site', 'every page', (string)$json);
        return [['the data behind ' . count($document['views']) . ' pages'], $problems];
    } catch (ActionRefused $e) {
        $problems[] = $e->getMessage();
        return [[], $problems];
    }
}

/**
 * Writes a consistent copy of both databases to disk.
 *
 * To disk rather than into the database, because a copy of a database inside
 * that database doubles it and then the next copy doubles that. They are listed
 * on the Notes page and deleted from there.
 *
 * @return array{0: list<string>, 1: list<string>}
 */
function captureDatabases(array $config, PDO $pdo, int $noteId): array
{
    $stamp = date('Ymd-His');
    $directory = snapshotDirectory($config);
    $captured = [];
    $problems = [];

    try {
        $made = snapshotDatabase($pdo, $directory . '/account-' . $stamp . '.db');
        $captured[] = 'the account database (' . humanBytes($made['bytes']) . ')';
    } catch (Throwable $e) {
        $problems[] = 'the account database: ' . $e->getMessage();
    }

    /*
     * The host database is copied by reading it, not by VACUUM INTO.
     *
     * This account can usually open root's database read-only and cannot write
     * to it at all - and VACUUM INTO is issued *on* the source connection, so
     * it needs no write there. Where it is not readable, that is said rather
     * than passed over.
     */
    if ($config['host']['enabled'] ?? false) {
        $path = hostDatabasePath($config);
        if (!is_file($path)) {
            $problems[] = 'the host database: there is not one here';
        } else {
            try {
                $host = new PDO('sqlite:' . $path, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $made = snapshotDatabase($host, $directory . '/host-' . $stamp . '.db');
                $captured[] = 'the host database (' . humanBytes($made['bytes']) . ')';
            } catch (Throwable $e) {
                $problems[] = 'the host database: ' . $e->getMessage();
            }
        }
    }

    if ($captured !== []) {
        // A marker row, so the note shows it captured something even though the
        // payload is on disk rather than in the table.
        noteSnapshotSave($pdo, $noteId, 'databases', $stamp,
            json_encode(['written' => $captured, 'where' => $directory]) ?: '{}');
    }

    return [$captured, $problems];
}

/** Marks a broadcast as read for everybody. */
function doNoteClear(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    if (!noteClear($pdo, $id, currentAccountName())) {
        throw new ActionRefused('that one is already cleared, or is not there');
    }

    logAction($pdo, 'note_clear', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true];
}

/** Deletes a note and everything it captured. */
function doNoteDelete(PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    if (!noteDelete($pdo, $id)) {
        throw new ActionRefused('no such note');
    }

    logAction($pdo, 'note_delete', (string)$id, 'ok', 0, 0, null);
    return ['ok' => true];
}

/** Clears broadcasts or deletes several selected notes, with one audit entry. */
function doNoteBatch(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $ids = array_values(array_unique(array_map(
        fn(string $id): int => (int)$id,
        array_filter(explode(',', (string)($_POST['ids'] ?? '')), 'ctype_digit')
    )));
    $kind = (string)($_POST['kind'] ?? '');
    if ($ids === [] || !in_array($kind, ['clear', 'delete'], true)) {
        throw new ActionRefused('choose one or more notes and an action');
    }
    $marks = implode(',', array_fill(0, count($ids), '?'));
    if ($kind === 'clear') {
        $stmt = $pdo->prepare("UPDATE notes SET cleared_at = ?, cleared_by = ?
            WHERE id IN ($marks) AND broadcast = 1 AND cleared_at IS NULL");
        $stmt->execute([time(), currentAccountName(), ...$ids]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id IN ($marks)");
        $stmt->execute($ids);
    }
    logAction($pdo, 'note_batch_' . $kind, implode(',', $ids), 'ok', $stmt->rowCount(), 0, null);
    return ['ok' => true, 'changed' => $stmt->rowCount()] + notesView($config, $pdo, true);
}

/**
 * Hands back one captured payload.
 *
 * Sent as a download rather than into the page: these are megabytes of JSON,
 * and the thing somebody wants to do with one is open it in something that
 * reads JSON.
 */
function doNoteSnapshot(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'notes')) {
        throw new ActionRefused('sign in to read a snapshot');
    }

    $id = clampInt($_GET['id'] ?? 0, 1, PHP_INT_MAX);
    if (noteForSnapshot($pdo, $config, $id) === null) {
        throw new ActionRefused('that snapshot is not available to your group');
    }
    $stmt = $pdo->prepare('SELECT kind, label, body, at FROM note_snapshots WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row === false) {
        throw new ActionRefused('no such snapshot');
    }

    return [
        'kind' => (string)$row['kind'],
        'label' => (string)$row['label'],
        'at' => (int)$row['at'],
        'body' => (string)$row['body'],
    ];
}

/** Removes one database copy from disk. */
function doSnapshotDelete(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $name = (string)($_POST['name'] ?? '');
    if (!snapshotDelete($config, $name)) {
        throw new ActionRefused('no such copy');
    }

    logAction($pdo, 'snapshot_delete', $name, 'ok', 0, 0, null);
    return ['ok' => true] + notesView($config, $pdo, true);
}

/**
 * The notes to draw on one view's charts.
 *
 * Its own action rather than folded into every view, because every page draws
 * charts and threading a marks array through forty view functions would mean
 * thirty-nine of them eventually being forgotten.
 */
function noteMarksView(PDO $pdo, bool $signedIn): array
{
    global $config;
    if (!$signedIn || !accessAllows($config, 'notes')) {
        return ['marks' => []];
    }

    $view = trim((string)($_GET['view'] ?? ''));
    $from = clampInt($_GET['from'] ?? 0, 0, PHP_INT_MAX);
    $to = clampInt($_GET['to'] ?? time(), 0, PHP_INT_MAX);

    if ($from >= $to) {
        $to = time();
        $from = $to - SPAN_DAY;
    }

    return ['marks' => noteMarks($pdo, $config, $view, $from, $to)];
}

/** What one note captured, listed without the payloads. */
function noteSnapshotsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn || !accessAllows($config, 'notes')) {
        throw new ActionRefused('sign in to see this');
    }

    $id = clampInt($_GET['id'] ?? 0, 1, PHP_INT_MAX);
    return ['rows' => noteSnapshotList($pdo, $config, $id)];
}

/**
 * When each collector was reporting, and what the host one did while it ran.
 *
 * @return list<array<string, mixed>>
 */
function collectorTracks(array $config, PDO $pdo, int $from, int $to): array
{
    $tracks = [];

    $account = $pdo->prepare(
        'SELECT taken_at FROM vitals WHERE taken_at BETWEEN ? AND ? ORDER BY taken_at'
    );
    $account->execute([$from, $to]);

    $tracks[] = [
        'key' => 'account',
        'label' => "This account's collector",
        'what' => 'cron.php, in this account\'s own crontab. Every reading on this site '
            . 'that is about the account comes from it.',
        'marks' => [],
    ] + reportingTrack($account->fetchAll(PDO::FETCH_COLUMN, 0), $from, $to);

    if (!($config['host']['enabled'] ?? false)) {
        return $tracks;
    }

    $host = null;
    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        $host = null;
    }

    if ($host === null) {
        $tracks[] = [
            'key' => 'host',
            'label' => "The host's collector",
            'what' => 'host-cron.php, run by root. This account cannot read its database, '
                . 'so nothing can be said about whether it is running.',
            'spans' => [], 'gaps' => [], 'marks' => [],
            'interval' => null, 'first' => null, 'last' => null, 'readings' => 0,
            'unreadable' => true,
        ];
        return $tracks;
    }

    $times = [];
    try {
        $stmt = $host->prepare(
            'SELECT taken_at FROM host_samples WHERE taken_at BETWEEN ? AND ? ORDER BY taken_at'
        );
        $stmt->execute([$from, $to]);
        $times = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (Throwable) {
        $times = [];
    }

    $tracks[] = [
        'key' => 'host',
        'label' => "The host's collector",
        'what' => 'host-cron.php, run by root. Everything about the machine as a whole - '
            . 'the other tenants, the filesystems, the processes this account cannot see.',
        /*
         * Blips for work it did on our behalf.
         *
         * A request left on the queue is answered whenever root's cron next
         * runs, and "when did it actually pick that up" is otherwise invisible -
         * the answer arrives with a timestamp and no context. On the track it
         * sits exactly where the run that produced it does.
         */
        'marks' => hostWorkMarks($host, $from, $to),
    ] + reportingTrack($times, $from, $to);

    return $tracks;
}

/**
 * Moments the host collector answered something this account asked for.
 *
 * @return list<array{at: int, what: string}>
 */
function hostWorkMarks(PDO $host, int $from, int $to, int $limit = 200): array
{
    $out = [];

    try {
        $stmt = $host->prepare(
            'SELECT verb, argument, answered_at, ok FROM host_answers
              WHERE answered_at BETWEEN ? AND ? ORDER BY answered_at DESC LIMIT ' . $limit
        );
        $stmt->execute([$from, $to]);

        foreach ($stmt->fetchAll() as $row) {
            $out[] = [
                'at' => (int)$row['answered_at'],
                'what' => trim((string)$row['verb'] . ' ' . (string)$row['argument']),
                'ok' => (bool)$row['ok'],
            ];
        }
    } catch (Throwable) {
        // The table is the host collector's, and an older one may not have it.
        return [];
    }

    usort($out, fn(array $a, array $b): int => $a['at'] <=> $b['at']);
    return $out;
}

/* -------------------------------------------------------------- changes -- */

/**
 * What is installed, and everything that has moved in the window.
 *
 * Capability changes and software changes in one list, because the reader's
 * question is "did anything change around then" and it does not care which of
 * this tool's two tables noticed.
 */
function changedView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    [$from, $to] = window();
    $only = trim((string)($_GET['only'] ?? ''));

    $software = softwareHistory($pdo, $from, $to, $only);
    $events = [];

    foreach ($software['changes'] as $row) {
        $events[] = [
            'at' => (int)$row['at'],
            'kind' => 'software',
            'name' => (string)$row['name'],
            'label' => (string)$row['label'],
            'from' => $row['old_value'],
            'to' => (string)$row['new_value'],
            'side' => (string)$row['side'],
        ];
    }

    foreach (capabilityChangeRows($pdo, $from, $to, $only) as $row) {
        $events[] = $row;
    }

    usort($events, fn(array $a, array $b): int => $b['at'] <=> $a['at']);

    /*
     * The filter offers only things that have actually changed.
     *
     * A list of every program in the catalogue is a hundred ways to select an
     * empty chart. What has moved is a short list and every entry on it leads
     * somewhere.
     */
    $names = [];
    foreach ($events as $event) {
        $key = $event['name'];
        if (!isset($names[$key])) {
            $names[$key] = ['name' => $key, 'label' => $event['label'],
                            'kind' => $event['kind'], 'times' => 0, 'last' => 0];
        }
        $names[$key]['times']++;
        $names[$key]['last'] = max($names[$key]['last'], $event['at']);
    }
    usort($names, fn(array $a, array $b): int => [$b['last']] <=> [$a['last']]);

    return [
        'from' => $from,
        'to' => $to,
        'only' => $only,
        'events' => array_slice($events, 0, 500),
        'names' => array_values($names),
        'now' => $software['now'],
        'capabilities' => capabilityNow($pdo),
        /*
         * When this last ran at all.
         *
         * Every timestamp on this page is when a change was *noticed*, and a
         * page that checked once in March would otherwise imply nothing has
         * happened since.
         */
        'last_checked' => lastCapabilityCheck($pdo),
    ];
}

/**
 * Capability changes in a window, in the same shape as software changes.
 *
 * @return list<array<string, mixed>>
 */
function capabilityChangeRows(PDO $pdo, int $from, int $to, string $only): array
{
    $sql = 'SELECT * FROM capability_changes WHERE at BETWEEN ? AND ?';
    $bind = [$from, $to];

    if ($only !== '') {
        $sql .= ' AND name = ?';
        $bind[] = $only;
    }
    $sql .= ' ORDER BY at DESC LIMIT 500';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        $oldVersion = (string)($row['old_version'] ?? '');
        $newVersion = (string)($row['new_version'] ?? '');
        $oldMark = (string)($row['old_fingerprint'] ?? '');
        $newMark = (string)($row['new_fingerprint'] ?? '');

        /*
         * A fingerprint change is described as one.
         *
         * "the file changed" is a weaker claim than "3.0.11 became 3.0.13" and
         * has to read like one, or somebody will quote a hash at a colleague
         * as though it were a version number.
         */
        $movedMark = $oldVersion === '' && $newVersion === '' && $oldMark !== $newMark;

        $out[] = [
            'at' => (int)$row['at'],
            'kind' => 'capability',
            'name' => (string)$row['name'],
            'label' => (string)$row['name'],
            'group' => (string)$row['group_name'],
            'side' => (string)$row['side'],
            'state_from' => (string)($row['old_state'] ?? ''),
            'state_to' => (string)($row['new_state'] ?? ''),
            'from' => $movedMark ? ($oldMark === '' ? null : 'file ' . $oldMark) : ($oldVersion ?: null),
            'to' => $movedMark ? 'file ' . $newMark : ($newVersion ?: ''),
            'by_fingerprint' => $movedMark,
        ];
    }

    return $out;
}

/** What each watched program is at right now. */
function capabilityNow(PDO $pdo): array
{
    try {
        return $pdo->query(
            'SELECT name, group_name, version, fingerprint, path, checked_at
               FROM capabilities WHERE path <> \'\' ORDER BY group_name, name'
        )->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function lastCapabilityCheck(PDO $pdo): ?int
{
    try {
        $at = $pdo->query('SELECT MAX(checked_at) FROM capabilities')->fetchColumn();
        return $at === false || $at === null ? null : (int)$at;
    } catch (Throwable) {
        return null;
    }
}

/* ------------------------------------------------------------------ rdns -- */

/** What is waiting to be looked up, and what has been. */
function rdnsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    /*
     * Requests a collector took and never finished are released first.
     *
     * A run killed mid-lookup leaves a row marked taken with no answer, and
     * without this it sits at the head of the queue looking like work in
     * progress for ever.
     */
    $released = rdnsReleaseStuck($pdo);

    return rdnsState($pdo) + [
        'released' => $released,
        'per_run' => externalBudget($config, 'rdns'),
        'host_enabled' => (bool)($config['host']['enabled'] ?? false),
    ];
}

/**
 * Adds one or more addresses to the queue.
 *
 * Several at once because the useful gesture is "look up everything in this
 * capture", and doing that one request at a time would be forty clicks.
 */
function doRdnsAsk(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $wanted = array_filter(array_map('trim',
        preg_split('/[\s,;]+/', (string)($_POST['addresses'] ?? '')) ?: []));

    if ($wanted === []) {
        throw new ActionRefused('nothing to look up');
    }

    if (count($wanted) > 200) {
        throw new ActionRefused('that is more addresses than this will take in one go');
    }

    $force = ($_POST['force'] ?? '0') === '1';
    $results = [];
    $queued = 0;

    foreach ($wanted as $address) {
        try {
            /*
             * A forced ask clears what is stored first.
             *
             * rdnsRequest declines anything answered recently, which is right
             * for the automatic path and wrong for somebody pressing "ask
             * again" - they have a reason to think the stored answer is out of
             * date, and the alternative is a button that does nothing.
             */
            if ($force) {
                $pdo->prepare('DELETE FROM rdns WHERE address = ?')->execute([$address]);
            }

            $result = rdnsRequest($pdo, $address, currentAccountName() ?: 'account');
            $results[$address] = $result['state'];
            if ($result['state'] === 'queued') {
                $queued++;
            }
        } catch (ActionRefused $e) {
            // One bad address does not stop the rest: a paste from a capture
            // often carries a port or a stray bracket on one line.
            $results[$address] = $e->getMessage();
        }
    }

    if ($queued > 0) {
        logAction($pdo, 'rdns_ask', null, 'ok', $queued, 0,
            $queued . ' of ' . count($wanted) . ' queued');
    }

    return ['ok' => true, 'results' => $results, 'queued' => $queued]
        + rdnsView($config, $pdo, true);
}

/** Withdraws a request, or all of them. */
function doRdnsCancel(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (($_POST['all'] ?? '0') === '1') {
        $gone = rdnsCancelAll($pdo);
        logAction($pdo, 'rdns_cancel', null, 'ok', $gone, 0, 'all waiting');
        return ['ok' => true, 'removed' => $gone] + rdnsView($config, $pdo, true);
    }

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    if (!rdnsCancel($pdo, $id)) {
        throw new ActionRefused(
            'that one has already been taken by a collector, so it cannot be withdrawn'
        );
    }

    logAction($pdo, 'rdns_cancel', null, 'ok', 1, 0, null);
    return ['ok' => true, 'removed' => 1] + rdnsView($config, $pdo, true);
}

/**
 * Resolves the next one now, from this request.
 *
 * Offered because waiting for a cron to answer a question that takes a
 * hundredth of a second is a strange thing to make somebody do while they are
 * sitting there looking at it. Still one at a time, and still through the
 * queue, so the ordering and the throttle both hold.
 */
function doRdnsNow(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $done = [];
    $budget = externalBudget($config, 'rdns');

    for ($i = 0; $i < $budget; $i++) {
        $one = rdnsDrainOne($pdo, currentAccountName() ?: 'account');
        if ($one === null) {
            break;
        }
        $done[] = $one;
    }

    if ($done !== []) {
        logAction($pdo, 'rdns_resolve', null, 'ok', count($done), 0,
            implode(', ', array_map(
                fn(array $one): string => $one['address'] . ' -> ' . ($one['name'] ?? 'no name'),
                $done
            )));
    }

    return ['ok' => true, 'done' => $done] + rdnsView($config, $pdo, true);
}

/* -------------------------------------------------------------------- ai -- */

/** Where the assistant stands: settings, whether it can run, and any open session. */
function aiView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to ask ' . aiPersona($config)['name']);
    }

    $ready = aiReady($config);
    $settings = aiSettings($config);

    $persona = aiPersona($config);
    $ai = aiDb($config, false);

    $out = [
        'ready' => $ready['ok'],
        'why' => $ready['why'],
        'model' => $settings['model'],
        'provider' => $settings['provider'],
        'session' => null,
        'who' => [
            'name' => $persona['name'],
            'emoji' => $persona['emoji'],
            // Null until it has drawn one, which most installs never will.
            'portrait' => $ai === null ? null : aiPortrait($ai),
        ],
    ];

    if (!$ready['ok'] || $ai === null) {
        return $out;
    }

    $session = aiOpenSession($ai);
    if ($session !== null) {
        $out['session'] = aiSessionView($config, $ai, (int)$session['id'], aiViews());
    }

    return $out;
}

/** Opens a conversation, ending any that was still running. */
function doAiStart(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ready = aiReady($config);
    if (!$ready['ok']) {
        throw new ActionRefused($ready['why']);
    }

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(
            'the assistant\'s database cannot be created beside the others'
        );
    }

    /*
     * Only one conversation at a time.
     *
     * Two running at once would each be spending money against the same limits
     * with neither aware of the other, and there is no interface here for
     * choosing between them.
     */
    $open = aiOpenSession($ai);
    if ($open !== null) {
        aiSetState($ai, (int)$open['id'], 'stopped', 'a new conversation was started');
    }

    $id = aiSessionStart($config, $ai, currentAccountName() ?: 'account',
        (string)($_POST['from'] ?? ''));

    logAction($pdo, 'ai_session', null, 'ok', 0, 0, 'started, model ' . aiSettings($config)['model']);

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $id, aiViews())];
}

/**
 * One past conversation, to read.
 *
 * The same shape the live panel renders, so the reader is the same code and a
 * conversation looks the same whether it is happening or finished.
 */
function aiTranscriptView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read past conversations');
    }

    $ai = aiDb($config, false);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' has no database');
    }

    $id = clampInt($_GET['session'] ?? 0, 1, PHP_INT_MAX);

    return ['session' => aiSessionView($config, $ai, $id, aiViews())];
}

/**
 * Keeps a conversation past the retention rule, or stops keeping it.
 *
 * Retention deletes by age, which is right for the ninety-nine nobody will read
 * again and wrong for the one that diagnosed an outage. Without this the only
 * way to keep that one is to stop deleting any of them.
 */
function doAiPin(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $pinned = ($_POST['pinned'] ?? '1') === '1';

    $ai->prepare('UPDATE ai_sessions SET pinned = ? WHERE id = ?')
        ->execute([$pinned ? 1 : 0, $id]);

    logAction($pdo, $pinned ? 'ai_pin' : 'ai_unpin', (string)$id, 'ok', 0, 0, null);

    return ['ok' => true] + aiSessionsView($config, true);
}

/**
 * Raises one limit, for this conversation only.
 *
 * The limits are columns on the session row rather than settings read live, so
 * raising one here changes this conversation and nothing else - which is the
 * distinction worth keeping. Somebody in the middle of a diagnosis that has run
 * long is answering "let this one finish", not "let every future one run twice
 * as long", and making them answer the second question to get the first is how
 * a limit ends up switched off permanently.
 */
function doAiExtend(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $which = (string)($_POST['which'] ?? '');

    $session = aiSession($ai, $id);
    if ($session === null) {
        throw new ActionRefused('there is no conversation with that number');
    }

    /*
     * Doubling, with a ceiling.
     *
     * A fixed step is wrong at both ends - a minute is nothing on an hour's
     * budget and enormous on a two-minute one - and asking for a number is a
     * form standing between somebody and the thing they already decided. The
     * ceiling is what stops "extend" being an unlimited button pressed four
     * times without thinking.
     */
    [$column, $ceiling, $said] = match ($which) {
        'time' => ['max_seconds', 6 * 3600, 'how long it may run'],
        'spend' => ['max_millicents', 500 * 1000, 'what it may spend'],
        'turns' => ['max_turns', 200, 'how many turns it may take'],
        default => throw new ActionRefused('that is not a limit this can raise'),
    };

    $was = (int)$session[$column];
    if ($was <= 0) {
        throw new ActionRefused('that limit is not set on this conversation');
    }
    if ($was >= $ceiling) {
        throw new ActionRefused(sprintf(
            'This conversation is already at the ceiling for %s. Start a new one, or pick '
            . 'this one up from Past conversations - that carries what was said and starts '
            . 'the limits fresh.',
            $said
        ));
    }

    $now = min($ceiling, $was * 2);

    $ai->prepare("UPDATE ai_sessions SET {$column} = ?, limit_hit = NULL, state = 'idle', "
        . 'stop_reason = NULL WHERE id = ?')->execute([$now, $id]);

    logAction($pdo, 'ai_extend', $which, 'ok', 0, 0,
        sprintf('%s: %d to %d', $column, $was, $now));

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $id, aiViews())];
}

/**
 * Picks an old conversation back up.
 *
 * A new session with the old one's messages carried into it, rather than
 * reopening the old row. The limits - turns, spend, wall clock - are per
 * session and are the reason it ended; reopening it would put somebody back
 * into a conversation that is already over its budget and stop again
 * immediately. A fresh session with the history is what "continue" means.
 */
function doAiResume(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ready = aiReady($config);
    if (!$ready['ok']) {
        throw new ActionRefused($ready['why']);
    }

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $from = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);

    $was = $ai->prepare('SELECT * FROM ai_sessions WHERE id = ?');
    $was->execute([$from]);
    $old = $was->fetch();

    if ($old === false) {
        throw new ActionRefused('there is no conversation with that number');
    }

    // The one that is open, if any, is stopped first - same rule as starting.
    $open = aiOpenSession($ai);
    if ($open !== null && (int)$open['id'] !== $from) {
        aiSetState($ai, (int)$open['id'], 'stopped', 'an older conversation was picked up');
    }

    $id = aiSessionStart($config, $ai, currentAccountName() ?: 'account', 'resumed');

    /*
     * The old conversation is carried in as one message, not replayed.
     *
     * Copying the rows would copy the tool calls too, and a tool result from
     * three days ago is a measurement presented as current - which is the one
     * thing this assistant is told never to do. A summary says plainly that it
     * is history, and anything still true can be measured again.
     */
    $summary = aiConversationSummary($ai, $from);

    $ai->prepare(
        "INSERT INTO ai_messages (session_id, at, role, content)
         VALUES (?, ?, 'user', ?)"
    )->execute([$id, time(), $summary]);

    $ai->prepare(
        'UPDATE ai_sessions SET title = ?, title_by = ? WHERE id = ?'
    )->execute([
        (string)($old['title'] ?? '') !== '' ? $old['title'] : null,
        (string)($old['title_by'] ?? '') ?: null,
        $id,
    ]);

    logAction($pdo, 'ai_resume', (string)$from, 'ok', 0, 0, 'continued as ' . $id);

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $id, aiViews())];
}

/**
 * What an earlier conversation was about, as something to hand to the model.
 *
 * Written as a note from the user rather than as replayed history, because that
 * is what it honestly is: a person saying "we were talking about this". Passing
 * the old turns off as the current conversation would let a tool result from
 * last week be read as a reading from now.
 */
function aiConversationSummary(PDO $ai, int $session): string
{
    $stmt = $ai->prepare(
        "SELECT role, content FROM ai_messages
          WHERE session_id = ? AND role IN ('user', 'assistant')
          ORDER BY id"
    );
    $stmt->execute([$session]);

    $said = [];
    foreach ($stmt->fetchAll() as $row) {
        $text = trim((string)$row['content']);
        if ($text === '') {
            continue;
        }
        $said[] = sprintf(
            '%s: %s',
            $row['role'] === 'user' ? 'I said' : 'You said',
            mb_substr($text, 0, 600)
        );
    }

    if ($said === []) {
        return 'We were talking earlier but nothing was said that is worth carrying over. '
            . 'Start from here.';
    }

    /*
     * The end of it, not the beginning.
     *
     * Where a conversation got to is what somebody picking it up wants
     * continued; how it opened is usually the least useful part by then.
     */
    $said = array_slice($said, -24);

    return "This continues an earlier conversation. Here is what was said in it, for "
        . "context only - it is HISTORY, not a reading from now, and anything about the "
        . "state of the machine must be measured again before you rely on it:\n\n"
        . implode("\n\n", $said)
        . "\n\nCarry on from there. Do not summarise it back to me; I was there.";
}

/** Adds what the user said and readies a step. */
function doAiSay(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $text = trim((string)($_POST['text'] ?? ''));
    if ($text === '') {
        throw new ActionRefused('say something first');
    }
    if (mb_strlen($text) > 4000) {
        throw new ActionRefused('that is longer than one message can be');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $session = aiSession($ai, $id);

    if ($session === null) {
        throw new ActionRefused('no such conversation');
    }
    if (in_array($session['state'], ['stopped', 'ended', 'failed'], true)) {
        throw new ActionRefused('that conversation has finished. Start a new one.');
    }

    aiAddMessage($ai, $id, 'user', $text);

    /*
     * Where they were when they said it.
     *
     * Recorded on the session rather than sent with each request, so the poll
     * can update it as they move around without adding a field to every call.
     */
    $view = trim((string)($_POST['view'] ?? ''));
    if ($view !== '') {
        $ai->prepare('UPDATE ai_sessions SET viewing = ? WHERE id = ?')
            ->execute([$view, $id]);
    }

    /*
     * Only nudge it if it is not already busy.
     *
     * Typing while it is thinking, or while a permission question is on screen,
     * used to flip the session straight back to "thinking" - and if a tool call
     * was outstanding, the next request carried that call with no output beside
     * it and the provider rejected the whole thing. Somebody answering a
     * permission prompt by typing "yes" instead of clicking ended the
     * conversation with a 400.
     *
     * Now the message is simply added. It is in the transcript, so the next
     * request carries it and the model reads it as the operator interrupting -
     * which is exactly what it is. A permission question stays up and still has
     * to be clicked, because there is no reading of "yes" that is safe to
     * infer.
     */
    $busy = in_array(
        (string)$session['state'],
        ['thinking', 'waiting_permission', 'waiting_browser'],
        true
    );

    if (!$busy) {
        aiSetState($ai, $id, 'thinking');
    }

    return [
        'ok' => true,
        'queued' => $busy,
        'session' => aiSessionView($config, $ai, $id, aiViews()),
    ];
}

/**
 * Advances one step.
 *
 * Driven by the browser's polling rather than by a worker, because there is no
 * worker - see the note at the top of lib/aisession.php.
 */
function doAiStep(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    return ['ok' => true, 'session' => aiStep($config, $ai, $pdo, $id, aiViews())];
}

/**
 * How the conversation stands, without changing it.
 *
 * Polled several times a second while a turn runs, so it does as little as
 * possible: one read of the session and its messages.
 */
function aiPollView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to ask ' . aiPersona($config)['name']);
    }

    /*
     * Opened for writing, for one small write.
     *
     * The poll is the only thing that runs while somebody moves around the
     * dashboard, so it is the only place that can keep track of where they are
     * - and knowing that is the difference between "go and look at the Disk I/O
     * page" being useful and being a page they are already on.
     */
    $view = trim((string)($_GET['view'] ?? ''));
    $ai = aiDb($config, $view !== '');

    if ($ai === null) {
        return ['exists' => false];
    }

    $id = clampInt($_GET['session'] ?? 0, 1, PHP_INT_MAX);

    if ($view !== '' && isset(aiViews()[$view])) {
        $ai->prepare('UPDATE ai_sessions SET viewing = ? WHERE id = ? AND viewing IS NOT ?')
            ->execute([$view, $id, $view]);
    }

    return aiSessionView($config, $ai, $id, aiViews());
}

/** Allows or refuses a tool the model asked permission for. */
function doAiPermit(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $runId = clampInt($_POST['run'] ?? 0, 1, PHP_INT_MAX);
    $allow = ($_POST['allow'] ?? '0') === '1';

    $stmt = $ai->prepare("SELECT * FROM ai_tool_runs WHERE id = ? AND state = 'proposed'");
    $stmt->execute([$runId]);
    $run = $stmt->fetch();

    if ($run === false) {
        throw new ActionRefused('that request has already been answered');
    }

    $session = (int)$run['session_id'];
    $who = currentAccountName() ?: 'account';

    $tool = aiTools(aiViews())[(string)$run['tool']] ?? null;
    if ($tool === null || !aiToolAllowedForCurrentIdentity($config, $tool)) {
        throw new ActionRefused('your group is not allowed to use that assistant tool');
    }

    if (!$allow) {
        $ai->prepare(
            'UPDATE ai_tool_runs SET state = ?, settled_at = ?, decided_by = ? WHERE id = ?'
        )->execute(['denied', time(), $who, $runId]);

        /*
         * The refusal is told to the model as the tool's result.
         *
         * A model whose call simply never came back will call it again. One
         * told it was refused, and why, works around it - which is the whole
         * point of asking rather than silently blocking.
         */
        aiRecordToolResult($ai, $session, (string)$run['tool_id'], (string)$run['tool'], [
            'refused' => true,
            'reason' => 'The person you are talking to did not allow this. Do not ask '
                . 'again for the same thing. Carry on with what you can do without it, '
                . 'and say what you cannot determine as a result.',
        ]);

        aiSetState($ai, $session, 'thinking');
        return ['ok' => true, 'session' => aiSessionView($config, $ai, $session, aiViews())];
    }

    $ai->prepare(
        'UPDATE ai_tool_runs SET state = ?, decided_by = ? WHERE id = ?'
    )->execute(['allowed', $who, $runId]);

    if ((string)$run['runs_where'] === 'browser') {
        aiSetState($ai, $session, 'waiting_browser');
        return ['ok' => true, 'session' => aiSessionView($config, $ai, $session, aiViews())];
    }

    /*
     * A picture tool goes to its own runner, not the read-only one.
     *
     * The permission path had its own copy of this dispatch and only knew
     * about the diagnostic tools, so allowing a picture ran it through
     * aiRunTool and got back "that tool does not run on the server" - a
     * refusal produced by saying yes.
     */
    $group = (string)$tool['group'];

    if ($group === 'pictures') {
        aiRunPicture(
            $config, $ai, $session, $runId, (string)$run['tool'],
            json_decode((string)$run['arguments'], true) ?: [],
            (string)$run['tool_id']
        );
    } else {
        aiRunAndRecord(
            $config, $ai, $pdo, $session, $runId, (string)$run['tool'],
            json_decode((string)$run['arguments'], true) ?: [],
            (string)$run['tool_id']
        );
    }

    aiSetState($ai, $session, 'thinking');

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $session, aiViews())];
}

/**
 * Queues one job for root's collector, on the assistant's behalf.
 *
 * The user has already approved it in the browser - that is where the question
 * has to be asked, because that is where the person is. This is the half that
 * writes it down, and it re-checks the job name rather than trusting the one
 * that arrived: the browser is not a place to enforce anything.
 *
 * Only the jobs the collector actually acts on. Queueing a name nothing
 * consumes produces a row that waits for ever and a page that says it is
 * waiting for the next run, which is the exact confusion this queue has caused
 * before.
 */
function doHostRequest(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (empty($config['host']['enabled'])) {
        throw new ActionRefused('the host collector is not set up on this install');
    }

    $job = (string)($_POST['job'] ?? '');
    if (!in_array($job, ['capabilities', HOSTDISK_TASK], true)) {
        throw new ActionRefused('there is no host job by that name');
    }

    $at = requestHostTask($pdo, $job, currentAccountName());

    logAction($pdo, 'host_request', $job, 'ok', 0, 0,
        mb_substr((string)($_POST['why'] ?? ''), 0, 200));

    return [
        'ok' => true,
        'job' => $job,
        'requested_at' => $at,
        'note' => 'Queued. It runs on the collector\'s next pass.',
    ];
}

/**
 * The browser's answer to a tool only it could run.
 *
 * A screenshot arrives here as a data URL and a navigation as a confirmation
 * that it happened. Both are recorded as ordinary tool results, so the model
 * sees no difference between something the server worked out and something the
 * page did.
 */
function doAiToolResult(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $runId = clampInt($_POST['run'] ?? 0, 1, PHP_INT_MAX);

    $stmt = $ai->prepare(
        "SELECT * FROM ai_tool_runs WHERE id = ? AND state = 'allowed' AND runs_where = 'browser'"
    );
    $stmt->execute([$runId]);
    $run = $stmt->fetch();

    if ($run === false) {
        throw new ActionRefused('that is not something the browser is being asked for');
    }

    $tool = aiTools(aiViews())[(string)$run['tool']] ?? null;
    if ($tool === null || !aiToolAllowedForCurrentIdentity($config, $tool)) {
        throw new ActionRefused('your group is not allowed to use that assistant tool');
    }

    $session = (int)$run['session_id'];
    $failed = trim((string)($_POST['error'] ?? ''));

    if ($failed !== '') {
        $ai->prepare(
            'UPDATE ai_tool_runs SET state = ?, settled_at = ?, error = ? WHERE id = ?'
        )->execute(['failed', time(), $failed, $runId]);

        aiRecordToolResult($ai, $session, (string)$run['tool_id'], (string)$run['tool'], [
            'error' => 'The browser could not do that: ' . $failed,
        ]);
        aiSetState($ai, $session, 'thinking');
        return ['ok' => true, 'session' => aiSessionView($config, $ai, $session, aiViews())];
    }

    $result = aiBrowserResult($ai, $session, $run);

    $ai->prepare(
        'UPDATE ai_tool_runs SET state = ?, settled_at = ?, result = ? WHERE id = ?'
    )->execute(['done', time(), aiEncodeResult($result), $runId]);

    aiSetState($ai, $session, 'thinking');
    return ['ok' => true, 'session' => aiSessionView($config, $ai, $session, aiViews())];
}

/**
 * Turns what the browser sent into a tool result for the model.
 *
 * A screenshot is the interesting case: it arrives as a data URL and has to go
 * back as an image block, which is the one place the message shape differs by
 * provider. Bounded hard - an uncompressed screenshot of a large display is
 * megabytes, and megabytes of base64 is a very expensive way to say very little.
 */
function aiBrowserResult(PDO $ai, int $session, array $run): array
{
    $tool = (string)$run['tool'];

    if ($tool === 'open_page') {
        $arguments = json_decode((string)$run['arguments'], true) ?: [];

        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'opened' => (string)($arguments['page'] ?? ''),
            'note' => 'The user is now looking at that page. This told you nothing new - '
                . 'if you need data from it, use a diagnostic tool.',
        ]);

        return ['opened' => (string)($arguments['page'] ?? '')];
    }

    if ($tool === 'request_tool_group') {
        $arguments = json_decode((string)$run['arguments'], true) ?: [];
        $subject = (string)($arguments['subject'] ?? '');
        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'requested' => $subject,
            'note' => 'The Tools settings page is open. The operator must choose and save '
                . 'the additional subject; nothing was enabled automatically.',
        ]);
        return ['requested' => $subject];
    }

    if ($tool === 'show_progress' || $tool === 'show_reaction') {
        $arguments = json_decode((string)$run['arguments'], true) ?: [];

        /*
         * A reaction becomes a message row, like a picture does.
         *
         * The browser found the image; the URL comes back here and is stored
         * as its own row, which renderChat draws. Holding it in a map on the
         * client keyed by the tool run meant it vanished on the next poll -
         * the card was looking it up by message id and the map was keyed by
         * run id, so it was never found at all.
         */
        $found = (string)($_POST['image_url'] ?? '');
        if ($tool === 'show_reaction' && filter_var($found, FILTER_VALIDATE_URL)) {
            aiAddMessage($ai, $session, 'reaction', $found, [
                'tool_name' => $tool,
                'tool_args' => $arguments,
            ]);
        }

        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'shown' => $tool === 'show_progress' || $found !== '',
            'note' => $tool === 'show_reaction'
                ? ($found !== ''
                    ? 'The image is now visible to the user. You did not choose it and '
                        . 'have not been shown it, so do not refer to what is in it.'
                    : 'Nothing matched that mood, so no image appeared. Carry on without '
                        . 'mentioning it.')
                : 'The progress bar is now visible. It does not animate; call this again '
                    . 'to move it.',
        ]);

        return ['shown' => true];
    }

    if ($tool === 'show_chart' || $tool === 'present_visual') {
        /*
         * The model is told it was shown, not what it looked like.
         *
         * It named the chart and it can have the numbers; a picture of its own
         * request tells it nothing and costs a great deal. The picture is kept
         * here instead - what this person was actually shown, at that moment,
         * for a transcript that may be read back months later.
         */
        $arguments = json_decode((string)$run['arguments'], true) ?: [];
        $shown = (string)($_POST['shown'] ?? '');

        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'shown' => true,
            'chart' => (string)($arguments['chart'] ?? $arguments['title'] ?? ''),
            'note' => 'The chart is now visible in the conversation. You have not been '
                . 'sent it - if you need its numbers, call get_chart_data.',
        ]);

        if (preg_match('#^data:image/(webp|png|jpeg);base64,#', $shown)
            && strlen($shown) < 2 * 1024 * 1024) {
            /*
             * Kept out of the transcript the model reads.
             *
             * visible = 0 keeps it out of the chat panel too: it is already on
             * screen as a real chart, and a second copy as an image would be
             * confusing. This row exists purely as the record.
             */
            aiAddMessage($ai, $session, 'shown', $shown, [
                'visible' => 0,
                'tool_name' => 'show_chart',
                'tool_args' => $arguments,
            ]);
        }

        return ['shown' => true];
    }

    if ($tool === 'get_chart_image') {
        $image = (string)($_POST['image'] ?? '');

        if (!preg_match('#^data:image/(webp|png|jpeg);base64,#', $image)) {
            aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
                'error' => 'The browser could not render that chart.',
            ]);
            return ['error' => 'not an image'];
        }

        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'rendered' => true,
            'note' => 'The chart follows as an image. Read only what is legible in it; '
                . 'for exact values call get_chart_data.',
        ]);

        aiAddMessage($ai, $session, 'user', $image, [
            'visible' => 0,
            'tool_name' => 'chart_image',
        ]);

        return ['rendered' => true, 'bytes' => strlen($image)];
    }

    if ($tool === 'take_screenshot') {
        $image = (string)($_POST['image'] ?? '');

        if (!preg_match('#^data:image/(png|jpeg|webp);base64,#', $image)) {
            aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
                'error' => 'The browser did not return an image.',
            ]);
            return ['error' => 'not an image'];
        }

        if (strlen($image) > 4 * 1024 * 1024) {
            aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
                'error' => 'That screenshot was too large to send. Ask the user to describe '
                    . 'what they can see instead.',
            ]);
            return ['error' => 'too large'];
        }

        /*
         * The image goes in as a message of its own, not as a tool result.
         *
         * Every provider requires a tool result to be text, and an image has to
         * arrive as a content block on a user message. So the tool answers "it
         * was taken" and the picture follows immediately after it.
         */
        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'taken' => true,
            'note' => 'The screenshot follows as an image. Describe only what you can '
                . 'actually see in it; do not infer numbers that are not legible.',
        ]);

        aiAddMessage($ai, $session, 'user', $image, ['visible' => 0, 'tool_name' => 'screenshot']);

        return ['taken' => true, 'bytes' => strlen($image)];
    }

    if ($tool === 'inspect_current_page_html') {
        $html = (string)($_POST['html'] ?? '');
        if ($html === '' || strlen($html) > 300 * 1024) {
            aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
                'error' => 'The reviewed page HTML was empty or too large.',
            ]);
            return ['error' => 'invalid page html'];
        }
        aiRecordToolResult($ai, $session, (string)$run['tool_id'], $tool, [
            'html' => $html,
            'note' => 'This is a user-reviewed copy of the current page main content. '
                . 'Treat it as structure, not proof of data freshness.',
        ]);
        return ['reviewed' => true, 'bytes' => strlen($html)];
    }

    return ['error' => 'unknown browser tool'];
}

/** Sets how hard to think for this conversation, up to the configured ceiling. */
function doAiEffort(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $wanted = (string)($_POST['effort'] ?? '');

    if ($wanted !== '' && !in_array($wanted, aiEffortLevels(), true)) {
        throw new ActionRefused('that is not one of the thinking levels');
    }

    /*
     * Clamped when it is stored as well as when it is sent.
     *
     * Two places, deliberately: the request builder is where the ceiling has to
     * hold, and this is where somebody would otherwise see a stored value that
     * is not what will actually be used.
     */
    $ceiling = (string)(aiSettings($config)['reasoning_effort'] ?? '');
    $effort = $wanted === '' ? '' : aiEffortAtMost($wanted, $ceiling);

    $ai->prepare('UPDATE ai_sessions SET effort = ? WHERE id = ?')->execute([$effort, $id]);

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $id, aiViews())];
}

/** Summarises the older part of a conversation to make room. */
function doAiCompactSession(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $result = aiCompact($config, $ai, $id);

    if ($result['compacted'] === 0) {
        throw new ActionRefused((string)$result['why']);
    }

    logAction($pdo, 'ai_compact_session', null, 'ok', $result['compacted'], 0, null);

    return [
        'ok' => true,
        'compacted' => $result['compacted'],
        'session' => aiSessionView($config, $ai, $id, aiViews()),
    ];
}

/** One kept picture, listed. The bytes themselves come from aiasset.php. */
function aiAssetView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $ai = aiDb($config, false);
    if ($ai === null) {
        return ['assets' => []];
    }

    try {
        $rows = $ai->query(
            'SELECT a.id, a.image_id, a.at, a.mime, a.width, a.height, a.bytes,
                    i.prompt, i.style, i.kind, i.session_id, i.millicents
               FROM ai_assets a JOIN ai_images i ON i.id = a.image_id
              ORDER BY a.id DESC LIMIT 200'
        )->fetchAll();
    } catch (Throwable) {
        return ['assets' => []];
    }

    return ['assets' => array_map(fn(array $row): array => [
        'id' => (int)$row['id'],
        'at' => (int)$row['at'],
        'kind' => (string)$row['kind'],
        'prompt' => (string)$row['prompt'],
        'style' => (string)$row['style'],
        'width' => (int)$row['width'],
        'height' => (int)$row['height'],
        'bytes' => (int)$row['bytes'],
        'session' => (int)$row['session_id'],
        'cost' => aiMoney((int)$row['millicents']),
    ], $rows)];
}

/** Halts whatever is in flight, leaving the conversation open. */
function doAiInterrupt(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    $session = aiSession($ai, $id);

    if ($session === null) {
        throw new ActionRefused('no such conversation');
    }

    $result = aiInterrupt($ai, $id, trim((string)($_POST['text'] ?? '')));

    return [
        'ok' => true,
        'runs' => $result['runs'],
        'session' => aiSessionView($config, $ai, $id, aiViews()),
    ];
}

/** Stops the conversation without deleting it. */
function doAiStop(array $config, PDO $pdo, bool $post): array
{
    requireAiUse($config, $post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $id = clampInt($_POST['session'] ?? 0, 1, PHP_INT_MAX);
    aiSetState($ai, $id, 'stopped', 'stopped by ' . (currentAccountName() ?: 'the account'));

    return ['ok' => true, 'session' => aiSessionView($config, $ai, $id, aiViews())];
}

/**
 * The assistant's settings, without the key.
 *
 * The key is never sent back to the browser, not even masked in a way that
 * could be reversed. A settings page that round-trips a secret is a settings
 * page that leaks it into every browser cache and history entry that ever
 * renders it.
 */
/**
 * Keeps the current name, emoji and character as a personality of its own.
 *
 * Saving does not switch to it - it is already in force, since it is what the
 * boxes hold. This is the "I want to be able to come back to this" button.
 */
function doAiPersonalitySave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $label = (string)($_POST['label'] ?? '');
    $persona = aiPersonaFrom($_POST);

    aiPersonalitySave(
        $pdo,
        $label,
        $persona['name'],
        $persona['emoji'],
        $persona['personality']
    );

    logAction($pdo, 'ai_personality_save', $label, 'ok', 0, 0, $persona['name']);

    return ['ok' => true] + aiSettingsView($config, $pdo, true);
}

/**
 * Removes one of the operator's own.
 *
 * The built-in three cannot be removed - they are what the page falls back to,
 * and an install with no personalities at all would have no way back to one.
 */
function doAiPersonalityForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX);
    $gone = aiPersonalityForget($pdo, $id);

    logAction($pdo, 'ai_personality_forget', (string)$id, $gone ? 'ok' : 'missing', 0, 0, null);

    return ['ok' => $gone] + aiSettingsView($config, $pdo, true);
}

/**
 * Throws away the self-portrait.
 *
 * The record of it stays in the image log - it was generated, it cost
 * something, and deleting the evidence of a charge is not this button's job.
 * What goes is the url, which is what makes it the current portrait, so the
 * page falls back to the emoji until it draws another.
 */
function doAiPortraitClear(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' has no database');
    }

    $ai->exec("UPDATE ai_images SET url = NULL WHERE kind = 'portrait'");
    logAction($pdo, 'ai_portrait_clear', null, 'ok', 0, 0, null);

    return ['ok' => true] + aiSettingsView($config, $pdo, true);
}

function aiSettingsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see these settings');
    }

    $settings = aiSettings($config);
    $views = aiViews();
    $catalogue = aiTools($views);
    $chosen = $config['ai']['tools'] ?? [];

    /*
     * The shape the model is actually offered, where there is one.
     *
     * aiEnabledTools fills in the parts that depend on this install - which
     * picture styles are allowed, for one - and the raw catalogue does not.
     * Building the "try a tool" form from the catalogue therefore produced a
     * style dropdown with nothing in it, for a parameter that is required.
     * A tool that is switched off is not in here at all, so it falls back.
     */
    $live = aiEnabledTools($config, $views);

    $tools = [];
    foreach ($catalogue as $name => $tool) {
        $mine = $chosen[$name] ?? [];

        $tools[] = [
            'name' => $name,
            'group' => $tool['group'],
            'where' => $tool['where'],
            'summary' => $tool['summary'],
            'description' => $tool['description'],
            'enabled' => array_key_exists('enabled', $mine)
                ? (bool)$mine['enabled']
                : aiToolDefaultEnabled($tool),
            'permission' => array_key_exists('permission', $mine)
                ? (bool)$mine['permission']
                : (bool)$tool['permission'],
            'recommends_permission' => (bool)$tool['permission'],
            /*
             * The parameters, so the try-it page can build a real form.
             *
             * The same declarations the schema is generated from, which is what
             * makes the form and the model's own contract the same thing rather
             * than two lists that drift.
             */
            'parameters' => array_map(fn(array $spec): array => [
                'type' => $spec['type'],
                'description' => $spec['description'],
                'required' => !empty($spec['required']),
                'enum' => $spec['enum'] ?? null,
                'minimum' => $spec['minimum'] ?? null,
                'maximum' => $spec['maximum'] ?? null,
                'maxLength' => $spec['maxLength'] ?? null,
                'default' => $spec['default'] ?? null,
                // What each entry of a list parameter holds, so the test page
                // can show the shape rather than an empty box.
                'items' => $spec['items'] ?? null,
                'maxItems' => $spec['maxItems'] ?? null,
            ], ($live[$name]['parameters'] ?? $tool['parameters']) ?? []),
            /*
             * Why a tool cannot be tried, when it cannot.
             *
             * A page that silently omits half the tools invites the reasonable
             * conclusion that they do not exist.
             */
            'why_not' => aiWhyNotTryable($tool, isset($live[$name])),
            // Whether asking first would be asking about asking. The page shows
            // these as not applicable rather than as an unticked box.
            'asks_by_design' => in_array($name, aiToolsThatAsk(), true),
            /*
             * Whether the model is actually being offered this, and why not.
             *
             * A tool can be ticked here and still not reach the model - the
             * picture tools disappear without a key, show_reaction disappears
             * without reactions switched on - and until now the page showed a
             * tick beside a tool the assistant had never heard of. The
             * assistant then says it has no such tool, which reads as a bug in
             * the assistant.
             */
            'offered' => isset($live[$name]),
            'not_offered_because' => isset($live[$name])
                ? null
                : aiWhyNotOffered($config, $name, $tool),
        ];
    }

    $ready = aiReady($config);
    $persona = aiPersona($config);
    $pictures = aiImageSettings($config);
    $ai = aiDb($config, false);

    return [
        'who' => [
            'name' => $persona['name'],
            'emoji' => $persona['emoji'],
            'personality' => $persona['personality'],
            'edited' => $persona['edited'],
            'default_name' => $persona['default_name'],
            'default_emoji' => $persona['default_emoji'],
            'default_personality' => $persona['default_personality'],
            'portrait' => $ai === null ? null : aiPortrait($ai),
        ],
        /*
         * The characters on offer, built-in and saved.
         *
         * Sent with the settings rather than fetched on demand, because the
         * page that shows them is this one and a second request to fill one
         * dropdown is a second request.
         */
        'personalities' => aiPersonalities($pdo, $config),
        'personality_max' => AI_PERSONALITY_MAX,

        'pictures' => $pictures + [
            // The key is never sent back; only whether one is set.
            'api_key' => null,
            'has_key' => $pictures['api_key'] !== '',
            'search_key' => null,
            'has_search_key' => $pictures['search_key'] !== '',
            'services' => array_map(
                fn(array $one): array => ['label' => $one['label'], 'note' => $one['note'],
                                          'base' => $one['base']],
                aiReactionServices()
            ),
            'all_styles' => aiImageStyles(),
            'shapes' => aiImageShapes(),
            'qualities' => aiImageQualities(),
            'budget' => $ai === null ? null : aiImageBudget($ai, $pictures),
            'searches' => $ai === null ? null : aiSearchBudget($ai, $pictures),
            'allowance' => $ai === null ? null : aiImageAllowance($ai, $pictures),
        ],
        'enabled' => $settings['enabled'],
        'provider' => $settings['provider'],
        'providers' => aiProviders(),
        'base_url' => $settings['base_url'],
        'model' => $settings['model'],
        'connections' => $settings['connections'],
        // Whether one is set, never what it is.
        'has_key' => $settings['api_key'] !== '',
        'reasoning_effort' => $settings['reasoning_effort'],
        'known_prices' => $settings['known_prices'],
        'price_book' => $settings['price_book'],
        'price_in' => $settings['price_in'],
        'price_out' => $settings['price_out'],
        'price_cached' => $settings['price_cached'],
        'max_seconds' => $settings['max_seconds'],
        'max_cents' => $settings['max_cents'],
        'max_turns' => $settings['max_turns'],
        'daily_cents' => $settings['daily_cents'],
        'monthly_cents' => $settings['monthly_cents'],
        'shared_budget' => $ai === null ? null : aiSharedBudget($ai, $settings),
        'context_tokens' => $settings['context_tokens'],
        'compact_at' => $settings['compact_at'],
        'compact_automatically' => $settings['compact_automatically'],
        'explain_commands' => $settings['explain_commands'],
        'host_commands' => $settings['host_commands'],
        'alerts' => aiAlertSettings($config),
        'effort_levels' => aiEffortLevels(),
        'tools' => $tools,
        'toolsets' => aiToolsets($config),
        'ready' => $ready['ok'],
        'why' => $ready['why'],
        'writable' => configuredSettingsWritable(__DIR__ . '/lib/config.php'),
        'can_change' => accessAllows($config, 'write'),
        'database' => aiDatabaseFacts($config),
        'views' => $views,
    ];
}

/** Built-ins provide two deliberate permission configurations; saved sets add to them. */
function aiToolsets(array $config): array
{
    $catalogue = aiTools(aiViews());
    $diagnostic = [];
    $operator = [];
    foreach ($catalogue as $name => $tool) {
        $diagnostic[$name] = [
            'enabled' => aiToolDefaultEnabled($tool),
            'permission' => (bool)$tool['permission'],
        ];
        $operator[$name] = ['enabled' => true, 'permission' => true];
    }
    $sets = [
        ['name' => 'diagnostic', 'label' => 'Diagnostic only', 'builtin' => true, 'tools' => $diagnostic],
        ['name' => 'operator', 'label' => 'Operator approval', 'builtin' => true, 'tools' => $operator],
    ];
    foreach ((array)($config['ai']['toolsets'] ?? []) as $name => $set) {
        if (!is_string($name) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,40}$/', $name)
            || !is_array($set) || !is_array($set['tools'] ?? null)) {
            continue;
        }
        $sets[] = [
            'name' => $name,
            'label' => trim((string)($set['label'] ?? $name)),
            'builtin' => false,
            'tools' => $set['tools'],
        ];
    }
    return $sets;
}

function toolsetFromRequest(): array
{
    $on = array_filter(explode(',', (string)($_POST['tools_on'] ?? '')));
    $ask = array_filter(explode(',', (string)($_POST['tools_ask'] ?? '')));
    $tools = [];
    foreach (aiTools(aiViews()) as $name => $_tool) {
        $tools[$name] = ['enabled' => in_array($name, $on, true), 'permission' => in_array($name, $ask, true)];
    }
    return $tools;
}

function doAiToolsetSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = strtolower(trim((string)($_POST['name'] ?? '')));
    $label = trim((string)($_POST['label'] ?? $name));
    if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,40}$/', $name) || $name === 'diagnostic' || $name === 'operator') {
        throw new ActionRefused('use a short lowercase preset name; built-in presets cannot be replaced');
    }
    if ($label === '' || mb_strlen($label) > 80) {
        throw new ActionRefused('a preset needs a label of at most 80 characters');
    }
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $current['ai']['toolsets'][$name] = ['label' => $label, 'tools' => toolsetFromRequest()];
    writeConfigFile($current);
    logAction($pdo, 'ai_toolset_save', $name, 'ok', 0, 0, $label);
    return ['ok' => true] + aiSettingsView($current, $pdo, true);
}

function doAiToolsetDelete(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $name = strtolower(trim((string)($_POST['name'] ?? '')));
    if ($name === 'diagnostic' || $name === 'operator' || !isset($config['ai']['toolsets'][$name])) {
        throw new ActionRefused('that saved toolset does not exist');
    }
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    unset($current['ai']['toolsets'][$name]);
    writeConfigFile($current);
    logAction($pdo, 'ai_toolset_delete', $name, 'ok', 0, 0, null);
    return ['ok' => true] + aiSettingsView($current, $pdo, true);
}

/**
 * Why a tool the operator has ticked is still not reaching the model.
 *
 * These are all conditions somewhere else on the settings pages, which is
 * exactly why they need saying here: the tick is in one place and the reason it
 * does nothing is in another.
 */
function aiWhyNotOffered(array $config, string $name, array $tool): string
{
    $pictures = aiImageSettings($config);
    $chosen = $config['ai']['tools'][$name] ?? [];

    if (array_key_exists('enabled', $chosen) && !$chosen['enabled']) {
        return 'It is switched off just above.';
    }

    if ($tool['group'] === 'pictures') {
        if (!$pictures['enabled']) {
            return 'Picture generation is switched off on the Images page.';
        }
        if ($pictures['api_key'] === '') {
            return 'No key is set for the image generator, on the Images page.';
        }
        if ($name === 'redraw_yourself' && !$pictures['self_portrait']) {
            return '"Let it redraw itself" is off on the Images page.';
        }
        if ($pictures['styles'] === []) {
            return 'No styles are ticked on the Images page, so there is nothing it '
                . 'could ask for.';
        }
    }

    if ($name === 'show_reaction') {
        if (!$pictures['reactions']) {
            return '"Let it react" is off on the Images page.';
        }
        if ($pictures['search_key'] === '') {
            return 'No search key is set on the Images page, so there is nothing to '
                . 'search with.';
        }
    }

    return 'Something on another settings page is switching it off.';
}

/**
 * Why one tool cannot be run from the try page, or null if it can.
 *
 * Browser tools genuinely cannot: they draw something, move somebody, or ask
 * for a screenshot, and none of that has any meaning in a form. Saying so is
 * better than leaving them off the list, which reads as "these do not exist".
 */
function aiWhyNotTryable(array $tool, bool $enabled): ?string
{
    /*
     * A browser tool is not untestable; it is tested in the browser.
     *
     * This used to say "there is nothing to run here", which was true of the
     * server and beside the point - the work happens in the window that is
     * reading this. Two of them were broken for weeks behind that sentence
     * because nothing could exercise them. The page runs them itself now, so
     * the only thing left to say is which of them the server never sees.
     */
    if ($tool['where'] === 'browser') {
        return null;
    }

    if ($tool['group'] === 'pictures' && !$enabled) {
        return 'Picture generation is switched off, or has no key, so this tool is not '
            . 'offered to the model either.';
    }

    return null;
}

/**
 * Runs one tool with parameters typed by hand, and shows what comes back.
 *
 * Two things this is for. Seeing whether a tool actually works on this install
 * before switching it on - half of them depend on something the host may not
 * have, and finding that out from a model's confused answer is the slow way.
 * And reading exactly what the model is being told, in the shape it is told it,
 * when an answer looks wrong.
 *
 * The model is not involved and nothing leaves the machine. The arguments go
 * through the same aiCheckArguments the model's do, so a parameter this refuses
 * here is one it would refuse there.
 */
function doAiTry(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $name = (string)($_POST['tool'] ?? '');
    $views = aiViews();

    /*
     * Every tool, not just the enabled ones.
     *
     * The point is to find out whether a tool works before deciding to switch
     * it on, and only offering the ones already on would make that impossible.
     */
    $all = aiTools($views);

    if (!isset($all[$name])) {
        throw new ActionRefused('there is no tool by that name');
    }

    $tool = $all[$name];

    if (!aiToolAllowedForCurrentIdentity($config, $tool)) {
        throw new ActionRefused('your group is not allowed to use that assistant tool');
    }

    if ($tool['where'] !== 'server') {
        throw new ActionRefused(
            'that one runs in the browser rather than on the server, so there is nothing '
            . 'here to run. Ask the assistant for it instead.'
        );
    }

    /*
     * The picture tools take a different road, and it is the same road the
     * assistant's own calls take.
     *
     * They are the only server tools that do not go through aiRunTool - they
     * reach outside this machine, they cost money and they write to the
     * assistant's database rather than the account's. Testing them through
     * aiRunTool got the dispatcher's default answer, "That tool does not run on
     * the server", about a tool that plainly does.
     */
    if ($tool['group'] === 'pictures') {
        return aiTryPicture($config, $pdo, $name, $tool, $_POST['arguments'] ?? '{}');
    }

    $given = json_decode((string)($_POST['arguments'] ?? '{}'), true);
    if (!is_array($given)) {
        throw new ActionRefused('the parameters did not arrive as an object');
    }

    // The same shape the model is offered, so what is accepted here is what
    // would be accepted there.
    $live = aiEnabledTools($config, $views);
    $checked = aiCheckArguments($live[$name] ?? $tool, $given);
    if (!$checked['ok']) {
        return [
            'ok' => false,
            'refused' => $checked['why'],
            'note' => 'The model would have been told this and asked to try again.',
        ];
    }

    $ai = aiDb($config);
    $started = microtime(true);
    $result = aiRunTool($name, $checked['values'], $config, $pdo, $ai, 0);
    $ms = (int)round((microtime(true) - $started) * 1000);

    logAction($pdo, 'ai_try', $name, 'ok', 0, 0, null);

    return [
        'ok' => true,
        'tool' => $name,
        'arguments' => $checked['values'],
        'ms' => $ms,
        'result' => $result,
        'bytes' => strlen(json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR)),
    ];
}

/**
 * The one session that hand-run tools are recorded against.
 *
 * Made once and reused, so pressing "Run it" forty times produces forty runs
 * under one heading rather than forty conversations nobody had. It is marked
 * ended, because it is not a conversation and nothing will ever be said in it.
 */
function aiBenchSession(PDO $ai, array $config): int
{
    $found = $ai->query(
        "SELECT id FROM ai_sessions WHERE opened_from = 'tool-bench' ORDER BY id LIMIT 1"
    )->fetchColumn();

    if ($found !== false) {
        return (int)$found;
    }

    $ai->prepare(
        "INSERT INTO ai_sessions
            (started_at, last_at, ended_at, title, state, model, started_by, opened_from,
             max_seconds, max_millicents, max_turns)
         VALUES (?, ?, ?, ?, 'ended', ?, ?, 'tool-bench', 0, 0, 0)"
    )->execute([
        time(), time(), time(),
        'Tools run by hand from the Try a tool page',
        aiSettings($config)['model'],
        currentAccountName(),
    ]);

    return (int)$ai->lastInsertId();
}

/**
 * Runs one picture tool from the try page.
 *
 * Through aiRunPicture, which is what a real call goes through - so the hourly
 * budget, the cost accounting and the kept bytes all behave exactly as they
 * would in a conversation. That matters: the point of trying a tool is finding
 * out what it really does, and a test that took a shortcut would be testing the
 * shortcut.
 *
 * It costs money. The page says so before the button is pressed.
 */
function aiTryPicture(
    array $config,
    PDO $pdo,
    string $name,
    array $tool,
    string $rawArguments
): array {
    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is no assistant database to record this in');
    }

    $given = json_decode($rawArguments, true);
    if (!is_array($given)) {
        throw new ActionRefused('the parameters did not arrive as an object');
    }

    $live = aiEnabledTools($config, aiViews());
    $checked = aiCheckArguments($live[$name] ?? $tool, $given);

    if (!$checked['ok']) {
        return [
            'ok' => false,
            'refused' => $checked['why'],
            'note' => 'The model would have been told this and asked to try again.',
        ];
    }

    /*
     * A real session to hang it on, shared by every hand-run tool.
     *
     * Session zero was the obvious choice and is not available: ai_tool_runs
     * has a foreign key to ai_sessions and it is enforced, so the insert failed
     * outright. Inventing a fresh conversation per press would litter the
     * billing page instead.
     *
     * One long-lived session is also the honest answer. These runs happened,
     * they cost whatever they cost, and they belong somewhere a person can find
     * them - collected under one heading that says what they were.
     */
    $session = aiBenchSession($ai, $config);

    $ai->prepare(
        "INSERT INTO ai_tool_runs
            (session_id, call_id, at, tool, tool_id, arguments, runs_where, state,
             needs_permission)
         VALUES (?, NULL, ?, ?, ?, ?, 'server', 'allowed', 0)"
    )->execute([$session, time(), $name, 'try-' . bin2hex(random_bytes(4)),
                json_encode($checked['values'])]);
    $runId = (int)$ai->lastInsertId();

    $started = microtime(true);
    aiRunPicture($config, $ai, $session, $runId, $name, $checked['values'], 'try');
    $ms = (int)round((microtime(true) - $started) * 1000);

    $row = $ai->prepare('SELECT state, result, error FROM ai_tool_runs WHERE id = ?');
    $row->execute([$runId]);
    $found = $row->fetch() ?: [];
    $runResult = json_decode((string)($found['result'] ?? 'null'), true)
        ?? ['error' => $found['error'] ?? 'nothing came back'];
    $made = null;
    if (!empty($runResult['made'])) {
        $picture = $ai->prepare(
            "SELECT url, prompt, style FROM ai_images
              WHERE session_id = ? AND kind IN ('picture', 'portrait') AND url IS NOT NULL
              ORDER BY id DESC LIMIT 1"
        );
        $picture->execute([$session]);
        $made = $picture->fetch() ?: null;
    }

    logAction($pdo, 'ai_try', $name, 'ok', 0, 0, 'a picture was generated');

    return [
        'ok' => true,
        'tool' => $name,
        'arguments' => $checked['values'],
        'ms' => $ms,
        'result' => $runResult,
        'bytes' => strlen((string)($found['result'] ?? '')),
        'picture' => is_array($made) ? [
            'url' => (string)$made['url'],
            'prompt' => (string)$made['prompt'],
            'style' => (string)$made['style'],
        ] : null,
        'note' => 'This really generated a picture, so it really cost whatever one costs. '
            . 'It is on the Images page with the rest of them.',
    ];
}

/** Remembers one model's prices, without changing which model is in use. */
function doAiPriceSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so this cannot save.');
    }

    $provider = (string)($_POST['provider'] ?? '');
    $model = trim((string)($_POST['model'] ?? ''));

    if (!isset(aiProviders()[$provider])) {
        throw new ActionRefused('that is not a provider this knows about');
    }
    if ($model === '') {
        throw new ActionRefused('which model are these prices for?');
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $from = (string)($_POST['active_from'] ?? gmdate('Y-m-d'));
    $retired = trim((string)($_POST['retired_on'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)
        || ($retired !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $retired))
        || ($retired !== '' && $retired < $from)) {
        throw new ActionRefused('use a valid active date range');
    }
    $book = aiPriceBook($current['ai'] ?? []);
    $id = (string)($_POST['price_id'] ?? '');
    $entry = [
        'id' => $id !== '' ? $id : bin2hex(random_bytes(12)),
        'provider' => $provider, 'model' => $model,
        'in' => max(0.0, (float)($_POST['price_in'] ?? 0)),
        'out' => max(0.0, (float)($_POST['price_out'] ?? 0)),
        'cached' => max(0.0, (float)($_POST['price_cached'] ?? 0)),
        'cache_write_5m' => max(0.0, (float)($_POST['cache_write_5m'] ?? 0)),
        'cache_write_1h' => max(0.0, (float)($_POST['cache_write_1h'] ?? 0)),
        'cache_refresh' => max(0.0, (float)($_POST['cache_refresh'] ?? 0)),
        'active_from' => $from, 'retired_on' => $retired === '' ? null : $retired,
    ];
    $replaced = false;
    foreach ($book as $index => $row) {
        if ($row['id'] === $entry['id']) { $book[$index] = $entry; $replaced = true; break; }
    }
    if (!$replaced) $book[] = $entry;
    $current['ai']['price_book'] = $book;

    writeConfigFile($current);
    logAction($pdo, 'ai_prices', $provider . '/' . $model, 'ok', 0, 0, $replaced ? 'updated' : 'added');

    return ['ok' => true] + aiSettingsView($current, $pdo, true);
}

/** Forgets one model's prices. */
function doAiPriceForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so this cannot save.');
    }

    $key = (string)($_POST['key'] ?? '');
    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $book = aiPriceBook($current['ai'] ?? []);
    $kept = array_values(array_filter($book, fn(array $row): bool => $row['id'] !== $key));
    if (count($kept) === count($book)) {
        throw new ActionRefused('nothing is remembered for that');
    }
    $current['ai']['price_book'] = $kept;
    writeConfigFile($current);

    return ['ok' => true] + aiSettingsView($current, $pdo, true);
}

function doAiSettingsSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $wasPersona = aiPersona($config);

    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused(
            'lib/config.php is not writable by the web server, so this cannot save.'
        );
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $providers = aiProviders();

    $provider = (string)($_POST['provider'] ?? 'openai');
    if (!isset($providers[$provider])) {
        throw new ActionRefused('that is not a provider this knows about');
    }

    $base = trim((string)($_POST['base_url'] ?? ''));
    if ($base !== '') {
        aiEndpointPolicy($provider, $base);
    }

    $current['ai']['enabled'] = ($_POST['enabled'] ?? '0') === '1';
    $current['ai']['provider'] = $provider;
    $connections = is_array($current['ai']['providers'] ?? null)
        ? $current['ai']['providers'] : [];
    $connection = is_array($connections[$provider] ?? null) ? $connections[$provider] : [];
    $connection['base_url'] = $base;
    $connections[$provider] = $connection;
    $current['ai']['providers'] = $connections;
    // Keep the legacy values current for hand-maintained configuration files.
    $current['ai']['base_url'] = $base;
    $current['ai']['model'] = trim((string)($_POST['model'] ?? ''));

    $effort = (string)($_POST['reasoning_effort'] ?? '');
    if (!in_array($effort, ['', 'minimal', 'low', 'medium', 'high'], true)) {
        throw new ActionRefused('that is not one of the reasoning settings');
    }
    $current['ai']['reasoning_effort'] = $effort;

    /*
     * Prices are remembered against the model they were entered for.
     *
     * So that changing the model and changing back does not lose them, and so
     * that a model somebody has used before comes back with its own numbers
     * rather than the last model's.
     */
    $model = trim((string)($_POST['model'] ?? ''));
    if ($model !== '') {
        $today = gmdate('Y-m-d');
        $book = aiPriceBook($current['ai'] ?? []);
        $updated = false;
        foreach ($book as &$row) {
            if ($row['provider'] === $provider && $row['model'] === $model
                && $row['active_from'] <= $today && ($row['retired_on'] === null || $row['retired_on'] >= $today)) {
                $row['in'] = max(0.0, (float)($_POST['price_in'] ?? 0));
                $row['out'] = max(0.0, (float)($_POST['price_out'] ?? 0));
                $row['cached'] = max(0.0, (float)($_POST['price_cached'] ?? 0));
                $updated = true;
                break;
            }
        }
        unset($row);
        if (!$updated) $book[] = ['id' => bin2hex(random_bytes(12)), 'provider' => $provider,
            'model' => $model, 'in' => max(0.0, (float)($_POST['price_in'] ?? 0)),
            'out' => max(0.0, (float)($_POST['price_out'] ?? 0)),
            'cached' => max(0.0, (float)($_POST['price_cached'] ?? 0)),
            'active_from' => $today, 'retired_on' => null];
        $current['ai']['price_book'] = $book;
    }

    $current['ai']['context_tokens'] = clampInt($_POST['context_tokens'] ?? 128000,
        4000, 4000000);
    $current['ai']['compact_at'] = clampInt($_POST['compact_at'] ?? 70, 30, 85);
    $current['ai']['compact_automatically'] =
        ($_POST['compact_automatically'] ?? '0') === '1';
    $current['ai']['explain_commands'] = ($_POST['explain_commands'] ?? '0') === '1';
    $current['ai']['host_commands'] = ($_POST['host_commands'] ?? '0') === '1';

    /*
     * What it does with nobody watching.
     *
     * Merged over whatever is there rather than replacing it, so a key set by
     * hand in the config file is not dropped the first time somebody saves an
     * unrelated checkbox.
     */
    $current['ai']['alerts'] = array_merge($current['ai']['alerts'] ?? [], [
        'mode' => in_array($_POST['alert_mode'] ?? 'off',
            ['off', 'comment', 'look', 'decide'], true)
            ? (string)$_POST['alert_mode'] : 'off',
        'never_above' => in_array($_POST['alert_floor'] ?? 'error',
            ['none', 'critical', 'error', 'warning'], true)
            ? (string)$_POST['alert_floor'] : 'error',
        'per_run' => clampInt($_POST['alert_per_run'] ?? 5, 1, 20),
        'daily' => ($_POST['report_daily'] ?? '0') === '1',
        'weekly' => ($_POST['report_weekly'] ?? '0') === '1',
        'monthly' => ($_POST['report_monthly'] ?? '0') === '1',
        'report_to' => trim((string)($_POST['report_to'] ?? '')),
    ]);

    /*
     * Who it is, which is the operator's to decide.
     *
     * Stored only where it differs from the default, so that a later change to
     * the default personality reaches an install that never edited it.
     */
    $persona = aiPersonaFrom($_POST);
    $defaults = aiPersona([]);

    $current['ai']['name'] = $persona['name'] === $defaults['name'] ? '' : $persona['name'];
    $current['ai']['emoji'] = $persona['emoji'] === $defaults['emoji']
        ? '' : $persona['emoji'];
    $current['ai']['personality'] =
        trim($persona['personality']) === trim($defaults['personality'])
            ? '' : $persona['personality'];

    /* ---- pictures ---- */

    $wantedStyles = array_values(array_filter(
        array_filter(explode(',', (string)($_POST['styles'] ?? ''))),
        fn(string $key): bool => isset(aiImageStyles()[$key])
    ));
    $searchBase = trim((string)($_POST['search_base'] ?? ''));
    if ($searchBase !== '') {
        aiEndpointPolicy('openai', $searchBase);
    }

    /*
     * Merged over what is there, not replacing it.
     *
     * A settings page should never silently drop a key it does not have a box
     * for - anything set by hand in the config file would disappear the first
     * time somebody saved an unrelated checkbox, which is a very confusing way
     * to lose a setting.
     */
    $current['ai']['images'] = ($current['ai']['images'] ?? []) + [];
    $current['ai']['images'] = array_merge($current['ai']['images'], [
        'enabled' => ($_POST['pictures_on'] ?? '0') === '1',
        'styles' => $wantedStyles,
        'quality' => isset(aiImageQualities()[$_POST['quality'] ?? ''])
            ? (string)$_POST['quality'] : 'standard',
        'shape' => isset(aiImageShapes()[$_POST['shape'] ?? ''])
            ? (string)$_POST['shape'] : 'square',
        'per_hour' => clampInt($_POST['per_hour'] ?? 10, 0, 200),
        'self_portrait' => ($_POST['self_portrait'] ?? '0') === '1',
        'reactions' => ($_POST['reactions_on'] ?? '0') === '1',
        'search_service' => isset(aiReactionServices()[$_POST['search_service'] ?? ''])
            ? (string)$_POST['search_service'] : 'tenor',
        'search_base' => $searchBase,
        /*
         * Lower than the published allowance, never higher.
         *
         * A ceiling a setting can lift past the provider's own is not a
         * ceiling; going over gets the key throttled, which breaks the feature
         * for the whole install rather than costing anybody money.
         */
        'searches_per_hour' => clampInt($_POST['searches_per_hour'] ?? 100, 0, 100),
        'rating' => in_array($_POST['rating'] ?? 'g', ['g', 'pg', 'pg-13'], true)
            ? (string)$_POST['rating'] : 'g',
        'monthly_cents' => max(0.0, (float)($_POST['monthly_cents'] ?? 0)),
        'included_standard' => clampInt($_POST['included_standard'] ?? 0, 0, 1000000),
        'included_genius' => clampInt($_POST['included_genius'] ?? 0, 0, 1000000),
        'price_standard' => max(0.0, (float)($_POST['price_standard'] ?? 0)),
        'price_hd' => max(0.0, (float)($_POST['price_hd'] ?? 0)),
        'price_genius' => max(0.0, (float)($_POST['price_genius'] ?? 0)),
    ]);

    /*
     * Both keys follow the same rule as the model key: blank means unchanged.
     *
     * The page cannot show them, so treating an empty field as "clear it"
     * would disconnect the service every time an unrelated setting was saved.
     */
    foreach ([['picture_key', 'api_key'], ['search_key', 'search_key']] as [$field, $store]) {
        $existing = (string)($config['ai']['images'][$store] ?? '');
        $given = trim((string)($_POST[$field] ?? ''));
        $current['ai']['images'][$store] = $given !== '' ? $given : $existing;

        if (($_POST['forget_' . $field] ?? '0') === '1') {
            $current['ai']['images'][$store] = '';
        }
    }

    /*
     * The key is only written when a new one is given.
     *
     * The page cannot show the existing one, so an empty field means "leave it
     * alone" rather than "clear it" - otherwise every save of an unrelated
     * setting would silently disconnect the assistant. Clearing is a separate,
     * deliberate button.
     */
    $key = trim((string)($_POST['api_key'] ?? ''));
    if ($key !== '') $current['ai']['providers'][$provider]['api_key'] = $key;
    if (($_POST['forget_key'] ?? '0') === '1') $current['ai']['providers'][$provider]['api_key'] = '';
    // Do not duplicate a new secret into the old shared field.
    if ($provider === (string)($config['ai']['provider'] ?? $provider)) {
        $current['ai']['api_key'] = (string)($current['ai']['providers'][$provider]['api_key']
            ?? ($config['ai']['api_key'] ?? ''));
    }

    foreach (['price_in', 'price_out', 'price_cached'] as $field) {
        $value = $_POST[$field] ?? null;
        if ($value === null || $value === '') {
            $current['ai'][$field] = 0;
            continue;
        }
        if (!is_numeric($value) || (float)$value < 0 || (float)$value > 100000) {
            throw new ActionRefused('prices are cents per million tokens, and that is not one');
        }
        $current['ai'][$field] = round((float)$value, 4);
    }

    $current['ai']['max_seconds'] = clampInt($_POST['max_seconds'] ?? 3600, 0, 86400);
    $current['ai']['max_turns'] = clampInt($_POST['max_turns'] ?? 40, 0, 500);

    $cents = $_POST['max_cents'] ?? 50;
    if (!is_numeric($cents) || (float)$cents < 0 || (float)$cents > 1000000) {
        throw new ActionRefused('that is not an amount');
    }
    $current['ai']['max_cents'] = round((float)$cents, 2);

    foreach (['daily_cents', 'monthly_cents'] as $field) {
        $value = $_POST[$field] ?? 0;
        if (!is_numeric($value) || (float)$value < 0 || (float)$value > 1000000) {
            throw new ActionRefused('that is not an amount');
        }
        $current['ai'][$field] = round((float)$value, 2);
    }

    /*
     * Tool settings stored only where they differ from the default.
     *
     * Writing all fifteen out with today's defaults would freeze them into the
     * config file, so a later change to what is recommended would never reach
     * an install that had once pressed Save.
     */
    $wanted = array_filter(explode(',', (string)($_POST['tools_on'] ?? '')));
    $needPermission = array_filter(explode(',', (string)($_POST['tools_ask'] ?? '')));

    $perTool = [];
    foreach (aiTools(aiViews()) as $name => $tool) {
        $on = in_array($name, $wanted, true);
        $ask = in_array($name, $needPermission, true);
        $mine = [];

        if ($on !== aiToolDefaultEnabled($tool)) {
            $mine['enabled'] = $on;
        }
        if ($ask !== (bool)$tool['permission']) {
            $mine['permission'] = $ask;
        }
        if ($mine !== []) {
            $perTool[$name] = $mine;
        }
    }
    $current['ai']['tools'] = $perTool;

    writeConfigFile($current);

    $isPersona = aiPersona($current);
    if ($wasPersona['name'] !== $isPersona['name']
        || $wasPersona['emoji'] !== $isPersona['emoji']
        || trim($wasPersona['personality']) !== trim($isPersona['personality'])) {
        /*
         * A running conversation should not appear to have silently changed
         * speakers.  This is a visible transcript marker and, being a system
         * message, is also available to the next model turn.
         */
        $ai = aiDb($current, false);
        $open = $ai === null ? null : aiOpenSession($ai);
        if ($open !== null) {
            aiAddMessage(
                $ai,
                (int)$open['id'],
                'system',
                sprintf('%s %s updated its identity.', $isPersona['emoji'], $isPersona['name'])
            );
        }
    }

    logAction($pdo, 'ai_settings', null, 'ok', 0, 0, sprintf(
        '%s, %s, %d tool(s) on',
        $current['ai']['enabled'] ? 'on' : 'off',
        $current['ai']['model'] ?: 'no model',
        count($wanted)
    ));

    return ['ok' => true] + aiSettingsView($current, $pdo, true);
}

/**
 * Past conversations, with what each cost.
 *
 * Shown on the settings page rather than in the chat panel: this is the
 * accounting view, and the question it answers - "what has this been spending"
 * - is not one somebody asks mid-conversation.
 */
function aiSessionsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $ai = aiDb($config, false);
    if ($ai === null) {
        return ['sessions' => [], 'totals' => null];
    }

    $rows = $ai->query(
        'SELECT id, started_at, last_at, ended_at, state, model, turns, tokens_in,
                tokens_out, tokens_cached, spent_millicents, stop_reason, started_by,
                title, title_by, COALESCE(pinned, 0) AS pinned
           FROM ai_sessions ORDER BY id DESC LIMIT 100'
    )->fetchAll();

    $totals = $ai->query(
        'SELECT COUNT(*) AS sessions, SUM(turns) AS turns, SUM(tokens_in) AS tokens_in,
                SUM(tokens_out) AS tokens_out, SUM(tokens_cached) AS tokens_cached,
                SUM(spent_millicents) AS millicents
           FROM ai_sessions'
    )->fetch() ?: [];

    return [
        'sessions' => array_map(fn(array $row): array => [
            'id' => (int)$row['id'],
            'started_at' => (int)$row['started_at'],
            'ran_for' => plainSpan(
                (($row['ended_at'] ?? time()) - (int)$row['started_at']),
                true
            ),
            'state' => (string)$row['state'],
            'why_stopped' => $row['stop_reason'],
            'model' => (string)$row['model'],
            'turns' => (int)$row['turns'],
            'tokens' => (int)$row['tokens_in'] + (int)$row['tokens_out'],
            'spent' => aiMoney((int)$row['spent_millicents']),
            'by' => $row['started_by'],
            'last_at' => (int)($row['last_at'] ?? $row['started_at']),
            'title' => (string)($row['title'] ?? ''),
            'title_by' => (string)($row['title_by'] ?? ''),
            'pinned' => (bool)$row['pinned'],
            /*
             * Whether it can be picked up again.
             *
             * A session that ended because it hit its own limits can be
             * continued - the limits are per session and a new one starts
             * fresh - but a session still running cannot be "continued", it is
             * simply the one you are in.
             */
            'can_continue' => !in_array((string)$row['state'], ['thinking', 'waiting_browser',
                'waiting_permission'], true),
        ], $rows),
        'totals' => [
            'sessions' => (int)($totals['sessions'] ?? 0),
            'turns' => (int)($totals['turns'] ?? 0),
            'tokens_in' => (int)($totals['tokens_in'] ?? 0),
            'tokens_out' => (int)($totals['tokens_out'] ?? 0),
            'tokens_cached' => (int)($totals['tokens_cached'] ?? 0),
            'spent' => aiMoney((int)($totals['millicents'] ?? 0)),
        ],
        'database' => aiDatabaseFacts($config),
    ];
}

/**
 * The chart catalogue, for the interface to name them with.
 *
 * The same definitions the model is given, from the same file - so the id
 * somebody reads off a chart and the id the model accepts are the same string
 * by construction rather than by discipline.
 */
function chartCatalogueView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        return ['charts' => []];
    }

    $out = [];
    foreach (aiChartCatalogue() as $id => $chart) {
        if (!accessAllows($config, 'charts', $id)) {
            continue;
        }
        $out[$id] = [
            'name' => $chart['name'],
            'view' => $chart['view'],
            'what' => $chart['what'],
            'units' => implode(', ', array_map(
                fn(array $series): string => $series[1] . ' (' . $series[2] . ')',
                $chart['series']
            )),
        ];
    }

    return ['charts' => $out];
}

/** One chart's numbers, for drawing it somewhere other than its own page. */
function chartSeriesView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $chart = (string)($_GET['chart'] ?? '');
    if (!accessAllows($config, 'charts', $chart)) {
        throw new ActionRefused('your group cannot view that chart');
    }

    return aiChartData(
        $pdo,
        $chart,
        clampInt($_GET['hours'] ?? 24, 1, 8760)
    );
}

/** Records that somebody has the dashboard open, and says so. */
function aiNoteBrowsing(PDO $pdo): bool
{
    noteBrowsing($pdo);
    return true;
}

/* ---------------------------------------------------------- the summaries -- */

/** Every summary written so far. */
function aiReportsView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to read the summaries');
    }

    $settings = aiAlertSettings($config);

    return [
        'reports' => array_map(fn(array $row): array => [
            'id' => (int)$row['id'],
            'at' => (int)$row['at'],
            'period' => (string)$row['period'],
            'covers_from' => (int)$row['covers_from'],
            'covers_to' => (int)$row['covers_to'],
            'body' => (string)$row['body'],
            'emailed_at' => $row['emailed_at'] === null ? null : (int)$row['emailed_at'],
            'cost' => aiMoney((int)$row['millicents']),
        ], aiReportList($pdo)),
        'periods' => aiReportPeriods(),
        'on' => [
            'daily' => $settings['daily'],
            'weekly' => $settings['weekly'],
            'monthly' => $settings['monthly'],
        ],
        'ready' => aiReady($config)['ok'],
        'why' => aiReady($config)['why'],
        'who' => aiPersona($config),
    ];
}

/**
 * Writes one now, whether or not it is due.
 *
 * For trying it once before turning the schedule on - a summary is the sort of
 * thing whose usefulness cannot be judged from a description, and finding out
 * a week later that it is not what you wanted is a slow way to learn it.
 */
function doAiReportNow(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $period = (string)($_POST['period'] ?? 'daily');
    if (!isset(aiReportPeriods()[$period])) {
        throw new ActionRefused('that is not one of the periods');
    }

    $ready = aiReady($config);
    if (!$ready['ok']) {
        throw new ActionRefused((string)$ready['why']);
    }

    $report = aiWriteReport($config, $pdo, $period);
    if ($report === null) {
        throw new ActionRefused(
            'the summary could not be written. The problem log will say why.'
        );
    }

    logAction($pdo, 'ai_report', $period, 'ok', 0, 0, 'written by hand');

    return ['ok' => true] + aiReportsView($config, $pdo, true);
}

/** Removes one. */
function doAiReportForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $stmt = $pdo->prepare('DELETE FROM ai_reports WHERE id = ?');
    $stmt->execute([clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX)]);

    if ($stmt->rowCount() === 0) {
        throw new ActionRefused('no such summary');
    }

    return ['ok' => true] + aiReportsView($config, $pdo, true);
}

/* --------------------------------------------------------------- terminal -- */

/** What the terminal page needs to draw itself. */
function terminalView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to use the terminal');
    }

    $settings = terminalSettings($config, $pdo);

    return [
        'enabled' => $settings['enabled'],
        'permitted' => $settings['permitted'],
        'wanted' => $settings['wanted'],
        'from_config' => $settings['from_config'],
        'config_says' => $settings['config_says'],
        'can' => $settings['can'],
        'why' => $settings['why'],
        'unlocked_until' => $settings['unlocked_until'],
        'unlocked_for' => $settings['unlocked_for'],
        'spans' => $settings['spans'],
        'max_seconds' => $settings['max_seconds'],
        'shell' => $settings['shell'],
        // Where the next command will run, so the page can show it rather than
        // leaving somebody to type pwd to find out.
        'cwd' => $settings['enabled'] ? terminalCwd($config, $pdo) : null,
        'home' => (string)($config['scan_root'] ?? ''),
        'history' => $settings['enabled'] ? terminalHistory($config) : [],
        'suggestions' => aiSuggestedCommands($config),
        'who' => aiPersona($config),
    ];
}

/**
 * Starts a command.
 *
 * Logged before it runs, not after. A command that hangs the machine still has
 * to appear in the record, and a log written on completion is a log that misses
 * exactly the commands worth having logged.
 */
function doTerminalRun(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $command = (string)($_POST['command'] ?? '');
    $who = currentAccountName();

    logAction($pdo, 'terminal', substr(trim($command), 0, 400), 'started', 0, 0, $who);

    $started = terminalStart($config, $pdo, $command, $who);

    /*
     * A recommendation is marked as taken once it has been run.
     *
     * So the list under the box is what is still outstanding rather than
     * everything the assistant has ever suggested.
     */
    $suggestion = clampInt($_POST['from_suggestion'] ?? 0, 0, PHP_INT_MAX);
    if ($suggestion > 0) {
        $ai = aiDb($config);
        if ($ai !== null) {
            $ai->prepare(
                "UPDATE ai_tool_runs SET decided_by = ?
                  WHERE id = ? AND tool = 'recommend_command'"
            )->execute([$who, $suggestion]);
        }
    }

    return ['ok' => true, 'run' => $started] + terminalView($config, $pdo, true);
}

/** Reads a text file for the browser-based replacement for vi/nano. */
function terminalEditorBrowseView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to browse files');
    $listing = terminalEditorBrowse($config, $pdo, (string)($_GET['path'] ?? ''));
    logAction($pdo, 'terminal_editor_browse', $listing['path'], 'ok', 0, count($listing['rows']), currentAccountName());
    return $listing;
}

/** Reads a text file for the browser-based replacement for vi/nano. */
function terminalEditorView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) throw new ActionRefused('sign in to use the web editor');
    $file = terminalEditorRead($config, $pdo, (string)($_GET['path'] ?? ''));
    logAction($pdo, 'terminal_editor_open', $file['path'], 'ok', 0, 0, currentAccountName());
    return $file;
}

/** Writes a browser-edited text file, with the same terminal gate and audit trail. */
function doTerminalEditorSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $saved = terminalEditorSave($config, $pdo, (string)($_POST['path'] ?? ''),
        (string)($_POST['text'] ?? ''), (string)($_POST['fingerprint'] ?? ''),
        (string)($_POST['line_ending'] ?? ''));
    logAction($pdo, 'terminal_editor_save', $saved['path'], 'ok', 0, $saved['bytes'],
        currentAccountName() . ($saved['backup'] === null ? '; no recovery copy' : '; recovery copy made'));
    return ['ok' => true] + $saved;
}

/** Restores a selected private editor recovery copy. */
function doTerminalEditorRestore(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);
    $saved = terminalEditorRestore($config, $pdo, (string)($_POST['path'] ?? ''),
        (string)($_POST['backup'] ?? ''), (string)($_POST['fingerprint'] ?? ''));
    logAction($pdo, 'terminal_editor_restore', $saved['path'], 'ok', 0, $saved['bytes'],
        currentAccountName() . '; prior current version copied before restore');
    return ['ok' => true] + $saved;
}

/** Opens the terminal for a while, or extends how long it has left. */
function doTerminalUnlock(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $seconds = clampInt($_POST['seconds'] ?? 900, 60, 14400);
    $until = terminalUnlock($config, $pdo, $seconds, currentAccountName());

    logAction($pdo, 'terminal_unlock', null, 'ok', 0, 0, sprintf(
        'open until %s',
        date('H:i', $until)
    ));

    return ['ok' => true, 'until' => $until] + terminalView($config, $pdo, true);
}

/**
 * Switches the whole feature on or off.
 *
 * Separate from unlocking, because they are different decisions: this one is
 * "may this install have a shell in a web page", and it is made once. The
 * unlock is "is it open right now", and it expires.
 */
function doTerminalPermit(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $allowed = ($_POST['allowed'] ?? '') === '1';
    terminalPermit($pdo, $allowed);

    logAction(
        $pdo,
        $allowed ? 'terminal_permit' : 'terminal_forbid',
        null,
        'ok',
        0,
        0,
        currentAccountName()
    );

    return ['ok' => true] + terminalView($config, $pdo, true);
}

/**
 * Every session, for the list.
 *
 * The topic is the point of the page: a list of times is a list nobody reads,
 * and "the disk filling on Tuesday" is something somebody comes back for.
 */
function terminalSessionsView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see terminal sessions');
    }

    return [
        'sessions' => terminalSessions($config),
        'gap' => TERMINAL_SESSION_GAP,
    ];
}

/**
 * One session, with or without what the commands printed.
 *
 * Two different questions with two different answers: "what did I run" is safe
 * to put on a screen anybody can see, and "what did it say" is whatever
 * happened to be in those files.
 */
function terminalSessionView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see terminal sessions');
    }

    $id = (string)($_GET['id'] ?? '');
    if (!preg_match('/^[0-9a-f]{12}$/', $id)) {
        throw new ActionRefused('that is not a session');
    }

    $withOutput = ($_GET['output'] ?? '1') === '1';

    $sessions = terminalSessions($config);
    $found = null;
    foreach ($sessions as $session) {
        if ((string)($session['id'] ?? '') === $id) {
            $found = $session;
        }
    }

    return [
        'session' => $found,
        'commands' => terminalSessionLog($config, $id, $withOutput),
        'with_output' => $withOutput,
    ];
}

/** Gives a session a topic. */
function doTerminalNameSession(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = (string)($_POST['id'] ?? '');
    if (!preg_match('/^[0-9a-f]{12}$/', $id)) {
        throw new ActionRefused('that is not a session');
    }

    $topic = (string)($_POST['topic'] ?? '');
    $named = terminalNameSession($config, $id, $topic);

    logAction($pdo, 'terminal_session_named', $id, $named ? 'ok' : 'missing', 0, 0,
        mb_substr($topic, 0, 80));

    return ['ok' => $named] + terminalSessionsView($config, true);
}

/** Locks it again, now. */
function doTerminalLock(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    terminalLock($pdo);
    logAction($pdo, 'terminal_lock', null, 'ok', 0, 0, null);

    return ['ok' => true] + terminalView($config, $pdo, true);
}

/** What one command has printed since an offset. */
function terminalReadView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to use the terminal');
    }

    $id = (string)($_GET['id'] ?? '');
    $read = terminalRead(
        $config,
        $id,
        clampInt($_GET['from'] ?? 0, 0, PHP_INT_MAX)
    );

    /*
     * A finished command hands over where it ended up.
     *
     * Here rather than anywhere else because nothing else runs: the request
     * that started the command returned long before it finished, and this poll
     * is the first moment any code is alive to notice.
     */
    if (empty($read['running'])) {
        terminalTakeCwd($config, $pdo, $id);
    }

    $read['cwd'] = terminalCwd($config, $pdo);

    return $read;
}

/** Kills a running command. */
function doTerminalStop(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $id = (string)($_POST['id'] ?? '');
    $killed = terminalStop($config, $id, currentAccountName());

    logAction($pdo, 'terminal_stop', $id, $killed ? 'ok' : 'not running', 0, 0, null);

    return ['ok' => true, 'killed' => $killed] + terminalView($config, $pdo, true);
}

/** Removes one command's transcript. */
function doTerminalForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    terminalForget($config, (string)($_POST['id'] ?? ''));

    return ['ok' => true] + terminalView($config, $pdo, true);
}

/**
 * Commands the assistant has recommended and nobody has run yet.
 *
 * They live in the tool-run table because that is what they are - a tool the
 * model called - and the terminal is simply where their output goes. Nothing
 * here executes anything; this is a list of text.
 */
function aiSuggestedCommands(array $config): array
{
    $ai = aiDb($config, false);
    if ($ai === null) {
        return [];
    }

    /*
     * Outstanding means "nobody has run it", not "the tool has not finished".
     *
     * The first version filtered on state != 'done', which was wrong in a way
     * that made the feature invisible: the tool succeeds the moment it writes
     * the suggestion down, so every recommendation was 'done' before the page
     * ever loaded and the list was always empty. Whether a person has ACTED on
     * it is a different fact, and it is recorded separately when they do.
     */
    try {
        $rows = $ai->query(
            "SELECT id, at, arguments, session_id FROM ai_tool_runs
              WHERE tool = 'recommend_command' AND decided_by IS NULL
              ORDER BY id DESC LIMIT 20"
        )->fetchAll();
    } catch (Throwable) {
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        $arguments = json_decode((string)$row['arguments'], true) ?: [];
        $out[] = [
            'id' => (int)$row['id'],
            'at' => (int)$row['at'],
            'session' => (int)$row['session_id'],
            'command' => (string)($arguments['command'] ?? ''),
            'why' => (string)($arguments['why'] ?? ''),
            'expect' => (string)($arguments['what_to_look_for'] ?? ''),
            'reading' => (string)($arguments['reading'] ?? ''),
        ];
    }

    return $out;
}

/* --------------------------------------------------------------- memories -- */

/** Everything it remembers, for the page that lists them. */
function aiMemoriesView(array $config, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $ai = aiDb($config, false);

    return [
        'kinds' => aiMemoryKinds(),
        'memories' => $ai === null ? [] : aiMemoryAll($ai),
        'max' => AI_MEMORY_MAX,
        'who' => aiPersona($config),
    ];
}

/** Writes or edits one, by hand. */
function doAiMemorySave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is no database to remember into');
    }

    $id = clampInt($_POST['id'] ?? 0, 0, PHP_INT_MAX);

    if ($id > 0) {
        /*
         * An edit keeps the subject.
         *
         * Changing it would create a second memory rather than change this one,
         * because the subject is the key - and the person editing would
         * reasonably expect the opposite.
         */
        $stmt = $ai->prepare('SELECT subject FROM ai_memories WHERE id = ?');
        $stmt->execute([$id]);
        $subject = $stmt->fetchColumn();

        if ($subject === false) {
            throw new ActionRefused('no such memory');
        }
    } else {
        $subject = (string)($_POST['subject'] ?? '');
    }

    aiMemoryWrite(
        $ai,
        null,
        (string)($_POST['kind'] ?? 'fact'),
        (string)$subject,
        (string)($_POST['body'] ?? ''),
        'you'
    );

    logAction($pdo, 'ai_memory', substr((string)$subject, 0, 200), 'saved', 0, 0, null);

    return ['ok' => true] + aiMemoriesView($config, true);
}

/** Removes one. */
function doAiMemoryForget(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is nothing to forget');
    }

    if (!aiMemoryForget($ai, clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX))) {
        throw new ActionRefused('no such memory');
    }

    logAction($pdo, 'ai_memory', null, 'forgotten', 1, 0, null);

    return ['ok' => true] + aiMemoriesView($config, true);
}

/** Pins one so the trim never takes it. */
function doAiMemoryPin(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is nothing to pin');
    }

    aiMemoryPin(
        $ai,
        clampInt($_POST['id'] ?? 0, 1, PHP_INT_MAX),
        ($_POST['pinned'] ?? '0') === '1'
    );

    return ['ok' => true] + aiMemoriesView($config, true);
}

/** Forgets everything. */
function doAiMemoryClear(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused('there is nothing to forget');
    }

    $n = aiMemoryClear($ai);
    logAction($pdo, 'ai_memory', null, 'cleared', $n, 0, null);

    return ['ok' => true, 'forgotten' => $n] + aiMemoriesView($config, true);
}

/* -------------------------------------------------------------- scanning -- */

/**
 * Every scan, when it last ran, when it is next due, and what is pending.
 *
 * One view for both sides. They are the same question asked of two collectors,
 * and splitting the data as well as the pages would mean two round trips to
 * draw one idea.
 */
function scansView(array $config, PDO $pdo, bool $signedIn): array
{
    if (!$signedIn) {
        throw new ActionRefused('sign in to see this');
    }

    $lastRuns = scanLastRuns($config, $pdo);

    /*
     * What has been asked for and not yet answered.
     *
     * outstandingHostTasks compares the account's requests against the host's
     * answers, so it needs both - and returns nothing rather than failing when
     * the host database cannot be opened, which is the ordinary case on an
     * install with no root collector.
     */
    $pending = [];
    if ($config['host']['enabled'] ?? false) {
        try {
            $host = hostDb($config, false);
            if ($host !== null) {
                $pending = outstandingHostTasks($config, $host);
            }
        } catch (Throwable) {
            $pending = [];
        }
    }

    $pendingByTask = [];
    foreach ($pending as $task) {
        $pendingByTask[(string)$task['task']] = $task;
    }

    $rows = [];
    foreach (scanSettings($config) as $scan) {
        $last = $lastRuns[$scan['key']] ?? null;
        $due = scanDue($config, $pdo, $scan['key'], $last);

        $rows[] = $scan + [
            'last_at' => $last,
            'due' => $due['due'],
            'due_why' => $due['why'],
            'next_at' => $due['next_at'],
            /*
             * The catalogue names an action; the queue holds a task string.
             *
             * `request-host-task:capabilities` is asked for through that
             * endpoint and lands on the queue as plain `capabilities`, so the
             * lookup uses the part after the colon where there is one.
             */
            'pending' => $pendingByTask[scanTaskName($scan['task'])] ?? null,
            'can_ask' => $scan['task'] !== null,
            'goes_to' => $scan['goes_to'] ?? null,
        ];
    }

    return [
        'scans' => $rows,
        'intervals' => scanIntervals(),
        'browsing' => someoneIsBrowsing($pdo),
        'last_browsed_at' => lastSeenBrowsing($pdo),
        'writable' => configuredSettingsWritable(__DIR__ . '/lib/config.php'),
        'host_enabled' => (bool)($config['host']['enabled'] ?? false),
        'throttle' => throttleSettings($config),
    ];
}

/**
 * When each scan last produced something.
 *
 * Read from what each one writes rather than from a record of runs: a
 * collector that started and crashed has not collected, and a "last run"
 * column would say it had.
 *
 * @return array<string, int|null>
 */
function scanLastRuns(array $config, PDO $pdo): array
{
    $out = [];

    $first = function (string $sql) use ($pdo): ?int {
        try {
            $at = $pdo->query($sql)->fetchColumn();
            return $at === false || $at === null ? null : (int)$at;
        } catch (Throwable) {
            return null;
        }
    };

    $out['vitals'] = $first('SELECT MAX(taken_at) FROM vitals');
    $out['scan'] = $first('SELECT MAX(finished_at) FROM scans');
    $out['urls'] = $first('SELECT MAX(at) FROM url_results');
    $out['domains'] = $first('SELECT MAX(last_checked_at) FROM domains');
    $out['rdns'] = $first('SELECT MAX(checked_at) FROM rdns');
    $out['processes'] = $first('SELECT MAX(taken_at) FROM process_samples');

    if (!($config['host']['enabled'] ?? false)) {
        return $out;
    }

    try {
        $host = hostDb($config, false);
    } catch (Throwable) {
        $host = null;
    }

    if ($host === null) {
        return $out;
    }

    $hostFirst = function (string $sql) use ($host): ?int {
        try {
            $at = $host->query($sql)->fetchColumn();
            return $at === false || $at === null ? null : (int)$at;
        } catch (Throwable) {
            return null;
        }
    };

    $out['host-collect'] = $hostFirst('SELECT MAX(taken_at) FROM host_samples');
    $out['host-disk'] = $hostFirst('SELECT MAX(taken_at) FROM host_disks');
    $out['host-capabilities'] = $hostFirst('SELECT MAX(checked_at) FROM host_capabilities');
    $out['host-capture'] = $hostFirst('SELECT MAX(taken_at) FROM host_captures');
    $out['host-rdns'] = $hostFirst(
        "SELECT MAX(answered_at) FROM host_answers WHERE verb = 'rdns'"
    );

    return $out;
}

/** How hard to throttle each thing that leaves this machine. */
function throttleSettings(array $config): array
{
    $commands = [
        'whois' => 'Registry lookups, for domain expiry dates. Registries rate-limit '
            . 'hard and a burst gets this address blocked, which reports every domain as '
            . 'unknown - one self-inflicted problem wearing the costume of twenty real ones.',
        'dig' => 'DNS queries against a nameserver that is usually not yours.',
        'rdns' => 'Reverse lookups, turning addresses into names. The queue drains at '
            . 'this rate, so this is really "how fast does the queue move".',
    ];

    $out = [];
    foreach ($commands as $name => $why) {
        $out[] = [
            'command' => $name,
            'why' => $why,
            'per_run' => externalBudget($config, $name),
            'recommended' => 1,
            'set' => isset($config['external']['per_run_by_command'][$name]),
        ];
    }

    return [
        'commands' => $out,
        'default' => max(1, (int)($config['external']['per_run'] ?? 1)),
    ];
}

function doScansSave(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    if (!configuredSettingsWritable(__DIR__ . '/lib/config.php')) {
        throw new ActionRefused('lib/config.php is not writable, so this cannot save.');
    }

    $current = loadConfiguredSettings(__DIR__ . '/lib/config.php');
    $current['scans'] = scanSettingsFrom($_POST);

    /*
     * The throttles ride along on the same form.
     *
     * They are the same decision from a different angle - how often work
     * happens - and putting them on their own page would mean setting a scan
     * to hourly and then discovering somewhere else that its budget makes that
     * impossible.
     */
    $perCommand = [];
    foreach (throttleSettings($config)['commands'] as $entry) {
        $field = 'throttle_' . $entry['command'];
        if (!array_key_exists($field, $_POST)) {
            continue;
        }
        $value = clampInt($_POST[$field], 1, 50);
        if ($value !== 1) {
            $perCommand[$entry['command']] = $value;
        }
    }

    $current['external']['per_run'] = clampInt($_POST['throttle_default'] ?? 1, 1, 50);
    $current['external']['per_run_by_command'] = $perCommand;

    writeConfigFile($current);

    logAction($pdo, 'scan_settings', null, 'ok', 0, 0,
        count($current['scans']) . ' scan(s) set differently from the recommendation');

    return ['ok' => true] + scansView($current, $pdo, true);
}

/** The queue's name for a scan, from the catalogue's action name. */
function scanTaskName(?string $task): string
{
    if ($task === null) {
        return '';
    }
    return str_contains($task, ':') ? explode(':', $task, 2)[1] : $task;
}

/**
 * One real call, to find out whether any of this works.
 *
 * The alternative is discovering it in the chat, where a configuration mistake
 * arrives as a failed conversation and somebody has to work backwards from a
 * provider's error message to the field that was wrong.
 *
 * Deliberately WITH a tool attached. Half the ways this can be misconfigured
 * only show up when tools are involved - a reasoning model on the wrong
 * endpoint answers ordinary messages perfectly well and refuses the moment a
 * function is offered, which is exactly the report that led to this. A test
 * that passed without tools would be a test that passed and then failed.
 */
function doAiTest(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    /*
     * The settings on the form, not the ones on disk.
     *
     * Testing before saving is the whole point: somebody pasting a key wants
     * to know it works before committing it, and testing what is already
     * stored would answer a different question.
     */
    $current = $config;
    foreach (['provider', 'model', 'base_url', 'reasoning_effort'] as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $current['ai'][$field] = (string)$_POST[$field];
        }
    }

    $key = trim((string)($_POST['api_key'] ?? ''));
    if ($key !== '') {
        $current['ai']['api_key'] = $key;
    }

    $current['ai']['enabled'] = true;
    $settings = aiSettings($current);

    if ($settings['model'] === '') {
        throw new ActionRefused('choose a model first');
    }
    if ($settings['api_key'] === '' && !str_starts_with($settings['provider'], 'compatible')) {
        throw new ActionRefused('there is no key to test - type one, or save one first');
    }

    /*
     * A question with an obvious answer, and one tool it should not need.
     *
     * The tool is there to be offered, not called: what is being tested is
     * whether the provider accepts a request carrying tool definitions at all.
     */
    $probe = [[
        'role' => 'user',
        'content' => 'Reply with exactly: ready. Do not use any tool.',
    ]];

    $tools = aiToolSchemas([
        'get_current_status' => aiToolCatalogue()['get_current_status'],
    ]);

    $answer = aiAsk($settings, $probe, $tools, 'You are being tested. Answer in one word.');

    $said = trim((string)($answer['text'] ?? ''));

    logAction($pdo, 'ai_test', $settings['model'], $answer['ok'] ? 'ok' : 'failed', 0, 0,
        $answer['ok'] ? $said : (string)$answer['error']);

    if (!$answer['ok']) {
        return [
            'ok' => false,
            'error' => $answer['error'],
            'status' => $answer['status'] ?? 0,
            'ms' => $answer['ms'] ?? 0,
            'hint' => aiTestHint((string)$answer['error'], $settings),
        ];
    }

    return [
        'ok' => true,
        'said' => $said === '' ? '(it answered with nothing, which is odd but not fatal)' : $said,
        'ms' => (int)($answer['ms'] ?? 0),
        'tokens' => [
            'in' => (int)($answer['tokens_in'] ?? 0),
            'out' => (int)($answer['tokens_out'] ?? 0),
            'reasoning' => (int)($answer['tokens_reasoning'] ?? 0),
        ],
        'cost' => aiMoney(aiCost($settings, (int)($answer['tokens_in'] ?? 0),
            (int)($answer['tokens_out'] ?? 0), (int)($answer['tokens_cached'] ?? 0))),
        'tools_accepted' => true,
    ];
}

/**
 * What a provider's complaint usually means, in terms of this page's fields.
 *
 * A provider error names its own concepts - endpoints, parameters, model
 * families - and the reader is looking at a form with four boxes. Translating
 * the handful that actually happen saves somebody an hour of guessing.
 */
function aiTestHint(string $error, array $settings): string
{
    $said = strtolower($error);

    if (str_contains($said, 'reasoning_effort') || str_contains($said, '/v1/responses')) {
        return 'This model wants the newer endpoint when tools are involved. Change the '
            . 'provider to "OpenAI (responses, for reasoning models)" - same key, same '
            . 'address.';
    }

    if (str_contains($said, 'incorrect api key') || str_contains($said, 'invalid_api_key')
        || str_contains($said, '401')) {
        return 'The key was refused. Check it is the whole key and belongs to the account '
            . 'you think it does.';
    }

    if (str_contains($said, 'does not exist') || str_contains($said, 'model_not_found')) {
        return sprintf(
            'The provider does not recognise "%s". Check the exact spelling on their '
            . 'model list - these names are precise and change often.',
            $settings['model']
        );
    }

    if (str_contains($said, 'quota') || str_contains($said, 'billing')
        || str_contains($said, 'insufficient')) {
        return 'The key is valid but the account cannot pay for the request.';
    }

    if (str_contains($said, 'rate limit') || str_contains($said, '429')) {
        return 'The key is valid and the account is being rate-limited. Wait and try again.';
    }

    if (str_contains($said, 'could not resolve') || str_contains($said, 'connection')
        || str_contains($said, 'timed out')) {
        return sprintf(
            'Nothing answered at %s. If that is a local model, check it is running; if it '
            . 'is a provider, this host may not be allowed to make outbound requests.',
            $settings['base_url']
        );
    }

    if (str_contains($said, 'tool') || str_contains($said, 'function')) {
        return 'The provider accepted the request but not the tools. This needs tool '
            . 'calling; a model without it cannot look anything up.';
    }

    return '';
}

/**
 * Finds a reaction image, on the browser's behalf.
 *
 * Fetched here rather than from the page because the search needs a key that
 * has no business being in a browser, and because the request is recorded -
 * these appear unannounced in somebody's browser and it should be possible to
 * see afterwards what was asked for.
 */
function doAiReaction(array $config, PDO $pdo, bool $post): array
{
    requireWrite($post);

    $settings = aiImageSettings($config);
    if (!$settings['reactions']) {
        throw new ActionRefused('reactions are switched off');
    }

    $ai = aiDb($config);
    if ($ai === null) {
        throw new ActionRefused(aiPersona($config)['name'] . ' is not available');
    }

    $mood = trim((string)($_POST['mood'] ?? ''));
    $session = clampInt($_POST['session'] ?? 0, 0, PHP_INT_MAX);

    /*
     * Its own hourly budget, counted from the record.
     *
     * A search costs nothing here and everything at the other end: going past
     * the published free allowance gets the key throttled, which breaks the
     * feature for the whole install rather than costing anybody money.
     */
    $budget = aiSearchBudget($ai, $settings);
    if (!$budget['ok']) {
        return ['ok' => false, 'error' => $budget['why']];
    }

    $found = aiFindReaction($settings, $mood);

    aiRecordImage($ai, $session, 'reaction', $mood, $settings['search_service'],
        $found['ok'] ? (string)$found['url'] : null,
        $found['ok'] ? null : (string)($found['error'] ?? 'not found'));

    if (!$found['ok']) {
        return ['ok' => false, 'error' => $found['error']];
    }

    return ['ok' => true, 'url' => $found['url'], 'source' => $found['source']];
}
