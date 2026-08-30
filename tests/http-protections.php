<?php
declare(strict_types=1);

/* Apache's optional modules are runtime concerns; the required policy is not. */

$policy = file_get_contents(__DIR__ . '/../.htaccess');
if ($policy === false) {
    throw new RuntimeException('could not read Apache policy');
}

foreach (
    [
        "Content-Security-Policy \"default-src 'self'",
        'X-Content-Type-Options "nosniff"',
        'X-Frame-Options "DENY"',
        'Referrer-Policy "no-referrer"',
        'Permissions-Policy "camera=(), geolocation=(), microphone=(self), payment=(), usb=()"',
        '<FilesMatch "\\.(db|db-journal|db-wal|db-shm|sqlite|sqlite3)$">',
        '<Files "cron.php">',
        '<Files "host-cron.php">',
        '<Files "no-mac.php">',
    ]
    as $required
) {
    if (!str_contains($policy, $required)) {
        throw new RuntimeException(
            'missing required Apache protection: ' . $required,
        );
    }
}

$nginx = file_get_contents(__DIR__ . '/../deploy/nginx.conf.example');
if ($nginx === false) {
    throw new RuntimeException('could not read Nginx deployment policy');
}
foreach (
    [
        'location ~* \\.(?:db|db-journal|db-wal|db-shm|sqlite|sqlite3)$ { deny all; }',
        'location ~ ^/(?:data|lib|scripts)(?:/|$) { deny all; }',
        'location ~ ^/(?:cron|host-cron|no-mac)\\.php$ { deny all; }',
        "frame-ancestors 'none'",
        'X-Content-Type-Options "nosniff"',
        'X-Frame-Options "DENY"',
        'Referrer-Policy "no-referrer"',
        'Cache-Control "no-store"',
    ]
    as $required
) {
    if (!str_contains($nginx, $required)) {
        throw new RuntimeException('missing Nginx protection: ' . $required);
    }
}

$scriptsPolicy = file_get_contents(__DIR__ . '/../scripts/.htaccess');
if (
    $scriptsPolicy === false ||
    !str_contains($scriptsPolicy, 'Require all denied')
) {
    throw new RuntimeException('CLI script directory is not denied by Apache');
}

$macCleanup = file_get_contents(__DIR__ . '/../no-mac.php');
if ($macCleanup === false) {
    throw new RuntimeException('could not read Mac metadata cleanup helper');
}

$passwordReset = file_get_contents(__DIR__ . '/../scripts/reset-password.php');
if ($passwordReset === false) {
    throw new RuntimeException('could not read password recovery command');
}

foreach (
    [
        ['aiasset.php', "accessAllows(\$config, 'ai')"],
        ['preview.php', "accessAllows(\$config, 'pages', 'net-fetch')"],
        ['thumb.php', "accessAllows(\$config, 'pages', 'storage-files')"],
    ]
    as [$file, $required]
) {
    $source = file_get_contents(__DIR__ . '/../' . $file);
    if ($source === false || !str_contains($source, $required)) {
        throw new RuntimeException(
            "missing media authorization safeguard: {$file}",
        );
    }
}
foreach (['peer-data.php', 'peer-connect.php', 'openid.php'] as $file) {
    $source = file_get_contents(__DIR__ . '/../' . $file);
    if (
        $source === false ||
        !str_contains(
            $source,
            "require_once __DIR__ . '/lib/setupstore.php'",
        ) ||
        !str_contains($source, 'loadConfiguredSettings($file)')
    ) {
        throw new RuntimeException(
            'standalone peer/OIDC endpoint bypasses the shared configuration store: ' .
                $file,
        );
    }
}

$dashboard = file_get_contents(__DIR__ . '/../index.js');
if (
    $dashboard === false ||
    !str_contains($dashboard, 'hint.textContent = meta.hint;') ||
    preg_match('/\.innerHTML\s*=/', $dashboard) === 1
) {
    throw new RuntimeException('dashboard rendering must not use an HTML sink');
}
if (
    !str_contains($dashboard, 'function redactReviewedPageHtml(root)') ||
    !str_contains(
        $dashboard,
        '[data-sensitive], [data-secret], [data-private]',
    ) ||
    !str_contains(
        $dashboard,
        'Array.from(node.attributes).forEach(attribute => node.removeAttribute(attribute.name));',
    ) ||
    !str_contains($dashboard, 'data-review-redacted')
) {
    throw new RuntimeException(
        'reviewed assistant page HTML must redact form and marked sensitive content',
    );
}

foreach (
    [
        "PHP_SAPI !== 'cli'",
        "saveConfiguredSettings(\$config, \$configFile",
        'authResetPassword(loadConfiguredSettings($configFile)',
        "'Password reset refused: '",
    ]
    as $required
) {
    if (!str_contains($passwordReset, $required)) {
        throw new RuntimeException(
            'missing password recovery safeguard: ' . $required,
        );
    }
}
foreach (
    [
        "PHP_SAPI !== 'cli'",
        "if (\$argc !== 2)",
        'realpath($argv[1])',
        'Refusing to clean the filesystem root.',
    ]
    as $required
) {
    if (!str_contains($macCleanup, $required)) {
        throw new RuntimeException(
            'missing cleanup helper safeguard: ' . $required,
        );
    }
}

$diagnosticExport = file_get_contents(__DIR__ . '/../diagnostic-export.php');
if ($diagnosticExport === false) {
    throw new RuntimeException('could not read diagnostic export endpoint');
}
foreach (
    [
        "(\$_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'",
        'isSignedIn()',
        "accessAllows(\$config, 'pages', 'system')",
        "csrfValid(\$_POST['csrf'] ?? null)",
        "logAction(\$pdo, 'diagnostic_export'",
        "'format' => 'zogograph-diagnostic-v1'",
        "'no credentials, paths, or hostnames'",
        "'edition' => hostEntitlement(\$config)",
    ]
    as $required
) {
    if (!str_contains($diagnosticExport, $required)) {
        throw new RuntimeException(
            'missing diagnostic export safeguard: ' . $required,
        );
    }
}

$auditExport = file_get_contents(__DIR__ . '/../audit-export.php');
if ($auditExport === false) {
    throw new RuntimeException('could not read audit export endpoint');
}
foreach (
    [
        "(\$_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'",
        'isSignedIn()',
        "accessAllows(\$config, 'pages', 'deletions')",
        "csrfValid(\$_POST['csrf'] ?? null)",
        "logAction(\$pdo, 'audit_export'",
        "preg_match('/^[=+\\-@]/', \$text)",
        "['at_utc', 'action', 'target', 'outcome', 'bytes_freed', 'files_removed', 'detail', 'actor', 'remote_address']",
        'detail, actor, remote_addr FROM actions ORDER BY at ASC',
        'ORDER BY at ASC',
    ]
    as $required
) {
    if (!str_contains($auditExport, $required)) {
        throw new RuntimeException(
            'missing audit export safeguard: ' . $required,
        );
    }
}

$export = file_get_contents(__DIR__ . '/../database-export.php');
if ($export === false) {
    throw new RuntimeException('could not read database export endpoint');
}
foreach (
    [
        '($_SERVER[\'REQUEST_METHOD\'] ?? \'\') !== \'POST\'',
        'isSignedIn()',
        'accessAllows($config, \'write\')',
        'csrfValid($_POST[\'csrf\'] ?? null)',
        'snapshotDatabase($pdo, $temporary)',
        'logAction($pdo, \'database_export\'',
        '@unlink($temporary)',
    ]
    as $required
) {
    if (!str_contains($export, $required)) {
        throw new RuntimeException(
            'missing database export safeguard: ' . $required,
        );
    }
}

$restore = file_get_contents(__DIR__ . '/../scripts/restore-account.php');
if ($restore === false) {
    throw new RuntimeException('could not read account restore command');
}
foreach (
    [
        "PHP_SAPI !== 'cli'",
        "'--writers-stopped'",
        'databaseFullIntegrity($candidate)',
        "name = 'vitals'",
        'snapshotDatabase($live, $recovery)',
        '@rename($temporary, $target)',
        '@unlink($target . \'-wal\')',
    ]
    as $required
) {
    if (!str_contains($restore, $required)) {
        throw new RuntimeException(
            'missing account restore safeguard: ' . $required,
        );
    }
}
foreach (
    [
        '--sealed',
        '--key',
        'encryptionOpenFile',
        'encryptionReadLocalKey',
        'sealed-restore-',
        'if ($decrypted !== null) @unlink($decrypted);',
    ]
    as $required
) {
    if (!str_contains($restore, $required)) {
        throw new RuntimeException(
            'missing sealed account restore safeguard: ' . $required,
        );
    }
}

$storeRestore = file_get_contents(__DIR__ . '/../scripts/restore-store.php');
if ($storeRestore === false) {
    throw new RuntimeException('could not read generic store restore command');
}
foreach (
    [
        "PHP_SAPI !== 'cli'",
        "'--writers-stopped'",
        "['setup', 'account', 'host', 'ai']",
        'databaseFullIntegrity($candidate)',
        "'setup' => 'setup_values'",
        "'host' => 'host_samples'",
        "'ai' => 'ai_sessions'",
        'VACUUM INTO',
        '@rename($temporary, $target)',
        '@unlink($target . \'-wal\')',
    ]
    as $required
) {
    if (!str_contains($storeRestore, $required)) {
        throw new RuntimeException(
            'missing generic store restore safeguard: ' . $required,
        );
    }
}

$api = file_get_contents(__DIR__ . '/../api.php');
if (
    $api === false ||
    !str_contains($api, "'peer-transport-rotate' => doPeerTransportRotate")
) {
    throw new RuntimeException('remote delivery rotation route is missing');
}
foreach (
    [
        'function doPeerTransportRotate',
        'requireWrite($post)',
        '$peer[\'transport_secret\'] = $credential;',
        "'peer_transport_rotate'",
    ]
    as $required
) {
    if (!str_contains($api, $required)) {
        throw new RuntimeException(
            'missing peer credential rotation safeguard: ' . $required,
        );
    }
}

$peerData = file_get_contents(__DIR__ . '/../peer-data.php');
if ($peerData === false) {
    throw new RuntimeException('could not read peer status endpoint');
}
foreach (
    [
        "(\$_SERVER['REQUEST_METHOD'] ?? '') !== 'GET'",
        "header('Allow: GET')",
        "header('X-Content-Type-Options: nosniff')",
        'openIdVerifyOwnToken($config, $bearer)',
    ]
    as $required
) {
    if (!str_contains($peerData, $required)) {
        throw new RuntimeException(
            'missing peer status safeguard: ' . $required,
        );
    }
}

$peerPush = file_get_contents(__DIR__ . '/../peer-push.php');
if ($peerPush === false) {
    throw new RuntimeException('could not read peer delivery endpoint');
}
foreach (
    [
        "(\$_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'",
        "header('Allow: GET, POST')",
        "(string)(\$_GET['health'] ?? '') === '1'",
        'PEER_TRANSPORT_MAX_BYTES',
        'http_response_code(413)',
        'peerTransportAccept($pdo, $config',
        "coreFeatureEnabled(\$config, 'peers')",
        'http_response_code(404)',
    ]
    as $required
) {
    if (!str_contains($peerPush, $required)) {
        throw new RuntimeException(
            'missing peer delivery safeguard: ' . $required,
        );
    }
}
foreach (['peer-data.php', 'peer-connect.php'] as $file) {
    $source = file_get_contents(__DIR__ . '/../' . $file);
    if (
        $source === false ||
        !str_contains($source, "coreFeatureEnabled(\$config, 'peers')") ||
        !str_contains($source, 'http_response_code(404)')
    ) {
        throw new RuntimeException(
            'disabled trusted-server feature leaves an external endpoint active: ' .
                $file,
        );
    }
}

$auth = file_get_contents(__DIR__ . '/../lib/auth.php');
$api = file_get_contents(__DIR__ . '/../api.php');
if ($auth === false || $api === false) {
    throw new RuntimeException(
        'could not read session revocation implementation',
    );
}
foreach (
    [
        'function authSessionCurrent',
        "\$_SESSION['auth_epoch'] = authSessionEpoch(\$config)",
        'if (isSignedIn() && !authSessionCurrent($config))',
    ]
    as $required
) {
    if (!str_contains($auth, $required)) {
        throw new RuntimeException(
            'missing session revocation safeguard: ' . $required,
        );
    }
}
foreach (
    [
        "'revoke-sessions' => doRevokeSessions",
        'function doRevokeSessions',
        'requireWrite($post)',
        "'session_revoke'",
        'saveConfiguredSettings($config, $configFile',
    ]
    as $required
) {
    if (!str_contains($api, $required)) {
        throw new RuntimeException(
            'missing session revocation route safeguard: ' . $required,
        );
    }
}

echo "HTTP protection policy checks passed\n";
