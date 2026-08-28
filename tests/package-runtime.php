<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/package-runtime.php';
require_once __DIR__ . '/../lib/alerts.php';
require_once __DIR__ . '/../lib/configure.php';
require_once __DIR__ . '/../lib/setup-panels.php';
function runtimeExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
if (!function_exists('sodium_crypto_sign_keypair')) { echo "package runtime checks skipped: sodium is unavailable\n"; exit(0); }
$root = sys_get_temp_dir() . '/hostscope-runtime-' . bin2hex(random_bytes(4));
$source = $root . '/source';
mkdir($source . '/src', 0700, true);
mkdir($source . '/migrations', 0700, true);
mkdir($root . '/web', 0700, true);
file_put_contents($source . '/src/Provider.php', "<?php declare(strict_types=1); namespace TestRuntime; final class Provider { public function capability(): array { return ['id' => 'test-runtime', 'state' => 'available', 'reason' => 'test']; } }\n");
file_put_contents($source . '/settings.schema.json', json_encode([
    'type' => 'object', 'additionalProperties' => false, 'required' => ['retention_days', 'custody_secret'],
        'properties' => ['retention_days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 90, 'default' => 30],
        'custody_secret' => ['type' => 'string', 'pattern' => '^secret_ref:test-runtime:']],
], JSON_THROW_ON_ERROR));
file_put_contents($source . '/migrations/001-private-evidence.sql', "CREATE TABLE private_evidence (value TEXT NOT NULL);\n");
try {
    $api = file_get_contents(__DIR__ . '/../api.php'); $dashboard = file_get_contents(__DIR__ . '/../index.js'); $page = file_get_contents(__DIR__ . '/../index.html');
    $sandboxTemplate = file_get_contents(__DIR__ . '/../deploy/package-worker.compose.example.yml');
    runtimeExpect($api !== false && str_contains($api, "'package-runtime-enable' => 'sys-packages'")
        && str_contains($api, "'package-runtime-probe' => 'sys-packages'")
        && str_contains($api, "'package-runtime-secret-create' => 'sys-packages'")
        && str_contains($api, "'package-runtime-revoke' => 'sys-packages'"), 'package runtime writes are not page-access gated');
    runtimeExpect($dashboard !== false && str_contains($dashboard, 'function renderPackageRuntime')
        && str_contains($dashboard, 'does not run package code') && str_contains($dashboard, 'Review or remove altered files before enabling.')
        && str_contains($dashboard, 'Platform unavailable') && str_contains($dashboard, 'no-secret capability probe')
        && str_contains($dashboard, 'Run isolated probe') && str_contains($dashboard, 'row.probe_evidence.probed_at')
        && str_contains($dashboard, 'showCustodyPackageSettings') && str_contains($dashboard, 'Never paste a raw bearer token here.'), 'dashboard does not explain the package execution boundary');
    runtimeExpect($page !== false && str_contains($page, 'data-view="sys-packages"')
        && str_contains($page, 'id="packageRuntimeBody"'), 'package runtime dashboard view is unavailable');
    runtimeExpect($sandboxTemplate !== false && str_contains($sandboxTemplate, 'network_mode: none')
        && str_contains($sandboxTemplate, 'read_only: true') && str_contains($sandboxTemplate, 'cap_drop:')
        && str_contains($sandboxTemplate, 'stdin_open: true') && !str_contains($sandboxTemplate, 'config.php'),
        'package worker deployment template no longer preserves its sandbox boundary');
    $pair = sodium_crypto_sign_keypair();
    $trustedKey = ['test-publisher' => ['publisher' => 'Test publisher',
        'public_key' => base64_encode(sodium_crypto_sign_publickey($pair))]];
    runtimeExpect(packageRuntimeTrustedKeys($trustedKey) === $trustedKey, 'trusted publisher key was not normalized');
    runtimeExpect(packageRuntimeCompatibility(['compatibility' => ['requires_runtime_capability' => 'isolated_package_runtime']])['status'] === 'unavailable',
        'package runtime claimed its absent isolation capability was available');
    runtimeExpect(packageRuntimeCompatibility(['compatibility' => ['requires_extensions' => ['hostscope_test_missing_extension']]])['status'] === 'unavailable',
        'missing package PHP extension was presented as available');
    try { packageRuntimeManifestCompatibility(['min_php' => 'not-a-version']); throw new RuntimeException('invalid package PHP compatibility was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'invalid package PHP compatibility was accepted', $error->getMessage()); }
    $fields = array_column(configFields(require __DIR__ . '/../lib/config.sample.php'), null, 'key');
    $field = $fields['package_runtime_trusted_keys'];
    [, $keyProblem] = parseField($field, json_encode($trustedKey, JSON_THROW_ON_ERROR));
    runtimeExpect($keyProblem === null, 'valid trusted publisher key was rejected by setup');
    [, $badKeyProblem] = parseField($field, '{"test-publisher":{"publisher":"Test publisher","public_key":"bad"}}');
    runtimeExpect($badKeyProblem !== null, 'invalid trusted publisher key was accepted by setup');
    [, $secretKeyProblem] = parseField($fields['package_runtime_secret_key_file'], '');
    runtimeExpect($secretKeyProblem === null, 'optional package secret key field rejected an empty value');
    $manifest = [
        'id' => 'test-runtime', 'version' => '1.0.0', 'publisher' => 'Test publisher',
        'capabilities' => ['package_private_database'],
        'runtime' => ['kind' => 'hostscope-isolated-php-package', 'entrypoint' => 'src/Provider.php', 'provider_class' => 'TestRuntime\\Provider', 'operations' => ['capability'], 'minimum_api' => '1.0'],
        'settings_schema' => 'settings.schema.json',
        'locales' => ['es' => 'locales/es.json'],
        'dashboard_pages' => [['id' => 'status', 'title' => 'Package status', 'summary' => 'A core-rendered package status page.', 'parent' => 'storage', 'keywords' => ['package', 'status']]],
        'setup_panels' => [
            ['id' => 'test-runtime-settings', 'title' => 'Test runtime settings', 'keywords' => ['retention']],
            ['id' => 'test-runtime-security', 'title' => 'Test runtime security', 'parent' => 'security', 'keywords' => ['security']],
        ],
        'data' => ['migrations' => ['migrations/001-private-evidence.sql']],
    ];
    mkdir($source . '/locales', 0700, true); file_put_contents($source . '/locales/es.json', json_encode(['test-runtime.greeting' => 'Hola'], JSON_THROW_ON_ERROR));
    $signed = packageSignManifest($manifest, $source, 'test-publisher', sodium_crypto_sign_secretkey($pair));
    file_put_contents($source . '/manifest.json', json_encode($signed, JSON_THROW_ON_ERROR));
    if (function_exists('proc_open')) {
        $pipes = []; $unattested = proc_open([PHP_BINARY, __DIR__ . '/../scripts/package-runtime-worker.php', '--package-root', $source],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, []);
        runtimeExpect(is_resource($unattested), 'could not start unattended package worker');
        fclose($pipes[0]); $unattestedOutput = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        runtimeExpect(proc_close($unattested) === 77 && str_contains($unattestedOutput, 'attested package sandbox'), 'package worker started without sandbox attestation');
        $pipes = []; $worker = proc_open([PHP_BINARY, __DIR__ . '/../scripts/package-runtime-worker.php', '--package-root', $source],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, ['HOSTSCOPE_PACKAGE_SANDBOX' => '1']);
        runtimeExpect(is_resource($worker), 'could not start package sandbox worker');
        fwrite($pipes[0], json_encode(['api' => '1.0', 'invocation_id' => str_repeat('a', 32), 'operation' => 'capability', 'package_id' => 'test-runtime'], JSON_THROW_ON_ERROR)); fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]); $errors = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        runtimeExpect(proc_close($worker) === 0 && str_contains($output, 'test-runtime') && $errors === '', 'package sandbox worker did not return bounded capability facts');
        $pipes = []; $unknown = proc_open([PHP_BINARY, __DIR__ . '/../scripts/package-runtime-worker.php', '--package-root', $source],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, ['HOSTSCOPE_PACKAGE_SANDBOX' => '1']);
        runtimeExpect(is_resource($unknown), 'could not start package worker unknown-field test');
        fwrite($pipes[0], json_encode(['api' => '1.0', 'invocation_id' => str_repeat('a', 32), 'operation' => 'capability', 'package_id' => 'test-runtime', 'unexpected' => true], JSON_THROW_ON_ERROR)); fclose($pipes[0]);
        $unknownOutput = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        runtimeExpect(proc_close($unknown) === 1 && str_contains($unknownOutput, 'Worker request is invalid'), 'package worker accepted an unknown request field');
        $sqlcipherRoot = realpath(__DIR__ . '/../plugins/sqlcipher-encrypted-sqlite');
        $pipes = []; $sqlcipher = proc_open([PHP_BINARY, __DIR__ . '/../scripts/package-runtime-worker.php', '--package-root', $sqlcipherRoot],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, ['HOSTSCOPE_PACKAGE_SANDBOX' => '1']);
        runtimeExpect(is_resource($sqlcipher), 'could not start SQLCipher preflight worker');
        fwrite($pipes[0], json_encode(['api' => '1.0', 'invocation_id' => str_repeat('a', 32), 'operation' => 'sqlcipher_preflight', 'package_id' => 'sqlcipher-encrypted-sqlite'], JSON_THROW_ON_ERROR)); fclose($pipes[0]);
        $preflightOutput = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        runtimeExpect(proc_close($sqlcipher) === 0 && str_contains($preflightOutput, 'sqlcipher_preflight')
            && (str_contains($preflightOutput, 'unavailable') || str_contains($preflightOutput, 'preflight-ready')),
            'SQLCipher disposable preflight did not return a bounded driver-state result');
    }
    $keyPath = $root . '/package-secrets.key'; encryptionCreateLocalKey($keyPath, $root . '/web');
    $config = ['database' => $root . '/stats.db', 'encryption' => ['local_key_file' => $keyPath], 'package_runtime' => ['directory' => $root . '/installed', 'trusted_keys' => [
        'test-publisher' => ['publisher' => 'Test publisher', 'public_key' => base64_encode(sodium_crypto_sign_publickey($pair))],
    ], 'secret_key_file' => '']];
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    migrate($pdo, true);
    $installed = packageRuntimeInstall($config, $pdo, $source, 'administrator');
    runtimeExpect(is_file($installed['install_path'] . '/src/Provider.php'), 'signed package file was not installed');
    if (class_exists(ZipArchive::class)) {
        $archive = $root . '/test-runtime.zip'; $zip = new ZipArchive();
        runtimeExpect($zip->open($archive, ZipArchive::CREATE) === true, 'could not create package archive');
        foreach (['manifest.json', 'src/Provider.php', 'settings.schema.json', 'migrations/001-private-evidence.sql', 'locales/es.json'] as $file) {
            runtimeExpect($zip->addFile($source . '/' . $file, $file), 'could not add package archive entry');
        }
        $zip->close();
        $archiveConfig = $config; $archiveConfig['package_runtime']['directory'] = $root . '/archive-installed';
        $archivePdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); migrate($archivePdo, true);
        $archiveInstalled = packageRuntimeInstallArchive($archiveConfig, $archivePdo, $archive, 'administrator');
        runtimeExpect(is_file($archiveInstalled['install_path'] . '/src/Provider.php'), 'signed package ZIP archive was not installed');
        packageRuntimeRemove($archiveConfig, $archivePdo, 'test-runtime');
        runtimeExpect(packageRuntimePackages($archivePdo) === [] && !is_dir($archiveInstalled['install_path']), 'disabled package archive was not fully removed from its runtime registry');
    }
    runtimeExpect((packageRuntimePackages($pdo)[0]['setup_panels'][0]['id'] ?? '') === 'test-runtime-settings',
        'signed package Setup panel metadata was not retained');
    runtimeExpect((packageRuntimePackages($pdo)[0]['setup_panels'][1]['parent'] ?? null) === 'security',
        'signed package parent Setup panel metadata was not retained');
    runtimeExpect(!isset(setupPanels([], [['id' => 'test-runtime-security', 'title' => 'Test runtime security', 'kind' => 'package', 'feature' => 'test-runtime', 'parent' => 'security']])['test-runtime-security']),
        'a parented package panel was incorrectly added as a standalone Setup navigation item');
    $genericReadiness = packageRuntimeProviderReadiness($pdo, $config, 'test-runtime');
    runtimeExpect($genericReadiness['state'] === 'not-applicable' && !$genericReadiness['endpoint_tested'],
        'non-provider package received an incorrect custody readiness state');
    try { packageRuntimeApproveCustodyDiagnostic($pdo, 'test-runtime', 'administrator'); throw new RuntimeException('non-custody package received custody diagnostic approval'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'non-custody package received custody diagnostic approval', $error->getMessage()); }
    runtimeExpect(packageRuntimeSettings($pdo, $config, 'test-runtime')['retention_days'] === 30,
        'signed package settings defaults were not shown before setup');
    runtimeExpect(packageRuntimeSecretSettingNames($pdo, 'test-runtime') === ['custody_secret'],
        'package secret-reference schema properties were not identified');
    if (function_exists('proc_open')) {
        try { packageRuntimeInvokeProbe($pdo, $config, 'test-runtime'); throw new RuntimeException('unconfigured probe launcher was accepted'); }
        catch (RuntimeException $error) { runtimeExpect($error->getMessage() !== 'unconfigured probe launcher was accepted', $error->getMessage()); }
        $launcher = $root . '/probe-launcher.sh';
        file_put_contents($launcher, "#!/bin/sh\nHOSTSCOPE_PACKAGE_SANDBOX=1 exec " . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../scripts/package-runtime-worker.php') . ' --package-root ' . escapeshellarg($installed['install_path']) . "\n");
        chmod($launcher, 0700);
        $config['package_runtime']['probe_invokers'] = ['test-runtime' => [
            'command' => [$launcher], 'isolation_profile' => 'test-one-shot-sandbox-v1',
            'package_manifest_sha256' => hash_file('sha256', $installed['install_path'] . '/manifest.json'),
        ]];
        $probe = packageRuntimeInvokeProbe($pdo, $config, 'test-runtime');
        runtimeExpect(($probe['id'] ?? null) === 'test-runtime' && ($probe['state'] ?? null) === 'available',
            'attested no-secret package capability probe did not return its signed package result');
        packageRuntimeRecordProbe($config, 'test-runtime', 'capability', $probe);
        runtimeExpect((packageRuntimeProbeEvidence($config)['test-runtime']['state'] ?? null) === 'available',
            'bounded package probe evidence was not retained for the administrator');
        $badInvoker = $config; $badInvoker['package_runtime']['probe_invokers']['test-runtime']['package_manifest_sha256'] = str_repeat('0', 64);
        try { packageRuntimeInvokeProbe($pdo, $badInvoker, 'test-runtime'); throw new RuntimeException('probe launcher accepted another package release'); }
        catch (RuntimeException $error) { runtimeExpect($error->getMessage() !== 'probe launcher accepted another package release', $error->getMessage()); }
    }
    $before = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$before['enabled'] && $before['state'] === 'needs explicit capability grants', 'package activated without a grant');
    $futureApi = $signed; $futureApi['runtime']['minimum_api'] = '999.0';
    runtimeExpect(packageRuntimeCompatibility($futureApi)['status'] === 'unavailable', 'a package requiring a newer runtime API was accepted');
    try { packageRuntimeSetEnabled($pdo, 'test-runtime', [], true, 'administrator'); throw new RuntimeException('missing grant was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'missing grant was accepted', $error->getMessage()); }
    packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator');
    $after = packageRuntimePackages($pdo)[0];
    runtimeExpect($after['enabled'] && str_starts_with($after['state'], 'registered; platform requirements are met'), 'package was not registered after explicit grant');
    runtimeExpect((packageRuntimeLocaleCatalogs($pdo)['test-runtime']['es']['test-runtime.greeting'] ?? '') === 'Hola', 'enabled package locale catalog was not available through the data-only bridge');
    runtimeExpect((packageRuntimeDashboardPages($pdo)[0]['route'] ?? '') === 'package-test-runtime-status'
        && (packageRuntimeDashboardPages($pdo)[0]['parent'] ?? null) === 'storage', 'enabled package dashboard page was not available through the core shell bridge');
    $dependentManifest = $signed; $dependentManifest['dependencies'] = ['missing-runtime'];
    $pdo->prepare('UPDATE runtime_packages SET manifest_json = ? WHERE package_id = ?')->execute([json_encode($dependentManifest, JSON_THROW_ON_ERROR), 'test-runtime']);
    $dependent = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$dependent['enabled'] && str_starts_with($dependent['state'], 'dependency unavailable:'), 'missing package dependency did not disable the dependent package');
    try { packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator'); throw new RuntimeException('missing package dependency was enabled'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'missing package dependency was enabled', $error->getMessage()); }
    $pdo->prepare('UPDATE runtime_packages SET manifest_json = ? WHERE package_id = ?')->execute([json_encode($signed, JSON_THROW_ON_ERROR), 'test-runtime']);
    $incompatibleManifest = $signed; $incompatibleManifest['compatibility'] = ['requires_runtime_capability' => 'isolated_package_runtime'];
    $pdo->prepare('UPDATE runtime_packages SET manifest_json = ? WHERE package_id = ?')->execute([json_encode($incompatibleManifest, JSON_THROW_ON_ERROR), 'test-runtime']);
    $incompatible = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$incompatible['enabled'] && str_starts_with($incompatible['state'], 'platform unavailable:'),
        'unavailable package platform remained enabled');
    try { packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator'); throw new RuntimeException('unavailable package platform was enabled'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'unavailable package platform was enabled', $error->getMessage()); }
    try { packageRuntimePrivateDatabase($pdo, $config, 'test-runtime'); throw new RuntimeException('unavailable package platform opened private storage'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'unavailable package platform opened private storage', $error->getMessage()); }
    $pdo->prepare('UPDATE runtime_packages SET manifest_json = ? WHERE package_id = ?')->execute([json_encode($signed, JSON_THROW_ON_ERROR), 'test-runtime']);
    runtimeExpect(packageRuntimeIntegrityAlerts($pdo) === [], 'verified package raised an integrity alert');
    packageRuntimeRevoke($pdo, 'test-runtime', 'test compromise response', 'administrator');
    $revoked = packageRuntimePackages($pdo)[0];
    runtimeExpect($revoked['revoked'] && !$revoked['enabled'] && str_starts_with($revoked['state'], 'revoked:'), 'package revocation did not disable the runtime record');
    try { packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator'); throw new RuntimeException('revoked package was enabled'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'revoked package was enabled', $error->getMessage()); }
    packageRuntimeLiftRevocation($pdo, 'test-runtime');
    packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator');
    $private = packageRuntimePrivateDatabase($pdo, $config, 'test-runtime');
    foreach ([$config['package_runtime']['directory'] . '/test-runtime-data/package.db', $config['package_runtime']['directory'] . '/test-runtime-data/package.db-wal', $config['package_runtime']['directory'] . '/test-runtime-data/package.db-shm'] as $privateFile) if (is_file($privateFile)) {
        runtimeExpect((fileperms($privateFile) & 0077) === 0, 'package private SQLite file has broad permissions');
    }
    runtimeExpect((int)$private->query("SELECT COUNT(*) FROM sqlite_master WHERE name = 'private_evidence'")->fetchColumn() === 1,
        'signed package private migration was not applied');
    runtimeExpect((int)$private->query('SELECT COUNT(*) FROM package_runtime_migrations')->fetchColumn() === 1,
        'private migration was not recorded');
    packageRuntimePrivateDatabase($pdo, $config, 'test-runtime');
    runtimeExpect((int)$private->query('SELECT COUNT(*) FROM package_runtime_migrations')->fetchColumn() === 1,
        'private migration was applied more than once');
    if (function_exists('symlink')) {
        $unsafeData = $config['package_runtime']['directory'] . '/unsafe-runtime-data'; $outside = $root . '/outside-data';
        mkdir($outside, 0700, true); @symlink($outside, $unsafeData);
        $pdo->prepare("INSERT INTO runtime_packages (package_id, version, publisher, manifest_json, manifest_sha256, install_path, installed_at, enabled_at, changed_at, changed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute(['unsafe-runtime', '1.0.0', 'Test publisher', json_encode($signed, JSON_THROW_ON_ERROR), hash_file('sha256', $installed['install_path'] . '/manifest.json'), $installed['install_path'], time(), time(), time(), 'administrator']);
        $pdo->prepare('INSERT INTO runtime_package_grants (package_id, capability, granted_at, granted_by) VALUES (?, ?, ?, ?)')->execute(['unsafe-runtime', 'package_private_database', time(), 'administrator']);
        try { packageRuntimePrivateDatabase($pdo, $config, 'unsafe-runtime'); throw new RuntimeException('package data directory symlink was accepted'); }
        catch (RuntimeException $error) { runtimeExpect($error->getMessage() !== 'package data directory symlink was accepted', $error->getMessage()); }
        @unlink($unsafeData); @rmdir($outside);
    }
    $private = null;
    $packagePath = $config['package_runtime']['directory'] . '/test-runtime-data/package.db';
    $outsideDb = $root . '/outside-package.db'; copy($packagePath, $outsideDb); @unlink($packagePath);
    if (function_exists('symlink') && @symlink($outsideDb, $packagePath)) {
        try { packageRuntimePrivateDatabase($pdo, $config, 'test-runtime'); throw new RuntimeException('package database symlink was accepted'); }
        catch (RuntimeException $error) { runtimeExpect($error->getMessage() !== 'package database symlink was accepted', $error->getMessage()); }
        @unlink($packagePath); copy($outsideDb, $packagePath);
    }
    @unlink($outsideDb);
    $reference = packageRuntimeSecretStore($pdo, $config, 'test-runtime', 'remote-client-credential', 'administrator');
    runtimeExpect(str_starts_with($reference, 'secret_ref:test-runtime:'), 'package secret reference is not package-scoped');
    runtimeExpect(packageRuntimeSecretValue($pdo, $config, 'test-runtime', $reference) === 'remote-client-credential', 'encrypted package secret could not be recovered');
    $listedSecrets = packageRuntimeSecrets($pdo, $config, 'test-runtime');
    runtimeExpect(count($listedSecrets) === 1 && $listedSecrets[0]['reference'] === $reference && !$listedSecrets[0]['referenced'],
        'package secret inventory exposed incorrect metadata before settings used it');
    packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['retention_days' => 30, 'custody_secret' => $reference], 'administrator');
    $listedSecrets = packageRuntimeSecrets($pdo, $config, 'test-runtime');
    runtimeExpect(count($listedSecrets) === 1 && $listedSecrets[0]['referenced'], 'package secret inventory did not report an active reference');
    runtimeExpect(packageRuntimeSettings($pdo, $config, 'test-runtime')['retention_days'] === 30,
        'package SQLite-backed settings were not retained');
    runtimeExpect(!isset(packageRuntimeProbeEvidence($config)['test-runtime']),
        'changed package settings retained stale diagnostic probe evidence');
    try { packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['retention_days' => 0, 'custody_secret' => $reference], 'administrator'); throw new RuntimeException('invalid schema value was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'invalid schema value was accepted', $error->getMessage()); }
    try { packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['retention_days' => 30, 'custody_secret' => $reference, 'undeclared' => true], 'administrator'); throw new RuntimeException('undeclared schema setting was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'undeclared schema setting was accepted', $error->getMessage()); }
    try { packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['custody_secret' => 'raw-secret'], 'administrator'); throw new RuntimeException('raw package secret was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'raw package secret was accepted', $error->getMessage()); }
    try { packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['custody_secret' => 'secret_ref:another-package:abcdefghijklmnop'], 'administrator'); throw new RuntimeException('another package secret reference was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'another package secret reference was accepted', $error->getMessage()); }
    try { packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['retention_days' => 30, 'custody_secret' => 'secret_ref:test-runtime:abcdefghijklmnop'], 'administrator'); throw new RuntimeException('missing package secret reference was accepted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'missing package secret reference was accepted', $error->getMessage()); }
    try { packageRuntimeSecretValue($pdo, $config, 'wrong-package', $reference); throw new RuntimeException('another package read a secret'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'another package read a secret', $error->getMessage()); }
    try { packageRuntimeSecretDelete($pdo, $config, 'test-runtime', $reference); throw new RuntimeException('referenced package secret was deleted'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'referenced package secret was deleted', $error->getMessage()); }
    $replacementReference = packageRuntimeSecretStore($pdo, $config, 'test-runtime', 'replacement-client-credential', 'administrator');
    packageRuntimeSaveSettings($pdo, $config, 'test-runtime', ['retention_days' => 30, 'custody_secret' => $replacementReference], 'administrator');
    packageRuntimeSecretDelete($pdo, $config, 'test-runtime', $reference);
    try { packageRuntimeSecretValue($pdo, $config, 'test-runtime', $reference); throw new RuntimeException('deleted package secret was recovered'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'deleted package secret was recovered', $error->getMessage()); }
    file_put_contents($installed['install_path'] . '/src/Provider.php', "<?php declare(strict_types=1); /* altered */\n");
    $altered = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$altered['integrity'] && str_starts_with($altered['state'], 'integrity failed:'), 'changed package file was not flagged by runtime status');
    $integrityAlert = packageRuntimeIntegrityAlerts($pdo);
    runtimeExpect(count($integrityAlert) === 1 && $integrityAlert[0]['rule'] === 'package_integrity'
        && $integrityAlert[0]['severity'] === 'error' && str_contains($integrityAlert[0]['detail'], 'test-runtime'),
        'changed package file did not raise a non-executing integrity alert');
    try { packageRuntimeSetEnabled($pdo, 'test-runtime', ['package_private_database'], true, 'administrator'); throw new RuntimeException('altered package was enabled'); }
    catch (InvalidArgumentException $error) { runtimeExpect($error->getMessage() !== 'altered package was enabled', $error->getMessage()); }
    copy($source . '/src/Provider.php', $installed['install_path'] . '/src/Provider.php');
    file_put_contents($installed['install_path'] . '/manifest.json', (string)file_get_contents($source . '/manifest.json') . "\n");
    $manifestAltered = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$manifestAltered['integrity'] && str_contains($manifestAltered['integrity_detail'], 'manifest has changed'),
        'changed installed package manifest was not flagged by runtime status');
    copy($source . '/manifest.json', $installed['install_path'] . '/manifest.json');
    file_put_contents($installed['install_path'] . '/src/unexpected.php', "<?php declare(strict_types=1);\n");
    $extraFile = packageRuntimePackages($pdo)[0];
    runtimeExpect(!$extraFile['integrity'] && str_contains($extraFile['integrity_detail'], 'files differ'),
        'unexpected installed package file was not flagged by runtime status');
    echo "package runtime checks passed\n";
} finally {
    foreach (glob($root . '/*') ?: [] as $path) {
        if (is_dir($path)) packageRuntimeRemoveDirectory($path); else @unlink($path);
    }
    @rmdir($root);
}
