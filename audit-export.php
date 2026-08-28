<?php
declare(strict_types=1);

/* Streams audit evidence without first collecting it in PHP memory. */
require_once __DIR__ . '/lib/boot.php';
installErrorBoundary('html');

$configFile = __DIR__ . '/lib/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Not configured.');
}

require_once __DIR__ . '/lib/setupstore.php';
$config = loadConfiguredSettings($configFile);
define('DISKMON_LOG', dirname((string)$config['database']) . '/error.log');
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/access.php';
require_once __DIR__ . '/lib/actions.php';

startSession($config);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isSignedIn()
    || !accessAllows($config, 'pages', 'deletions') || !csrfValid($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('A current session with audit access and form token are required.');
}

/** Stops untrusted action text from becoming a spreadsheet formula. */
function auditExportCell(mixed $value): string
{
    $text = (string)($value ?? '');
    return preg_match('/^[=+\-@]/', $text) ? "'" . $text : $text;
}

$pdo = db($config);
logAction($pdo, 'audit_export', null, 'ok', 0, 0, 'full CSV audit export');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="hostscope-audit-' . gmdate('Ymd-His') . '.csv"');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$out = fopen('php://output', 'wb');
fputcsv($out, ['at_utc', 'action', 'target', 'outcome', 'bytes_freed', 'files_removed', 'detail', 'actor', 'remote_address']);
$rows = $pdo->query('SELECT at, action, target, outcome, bytes_freed, files_removed, detail, actor, remote_addr FROM actions ORDER BY at ASC');
while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [
        gmdate('c', (int)$row['at']),
        auditExportCell($row['action']), auditExportCell($row['target']), auditExportCell($row['outcome']),
        (int)$row['bytes_freed'], (int)$row['files_removed'], auditExportCell($row['detail']),
        auditExportCell($row['actor']),
        auditExportCell($row['remote_addr']),
    ]);
}
fclose($out);
