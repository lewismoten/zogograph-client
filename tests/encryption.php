<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/encryption.php';
require_once __DIR__ . '/../lib/configure.php';

function encryptionExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

if (!encryptionAvailable()) {
    echo "encryption checks skipped: OpenSSL AES-256-GCM is unavailable\n";
    exit(0);
}

$root = sys_get_temp_dir() . '/hostscope-encryption-' . bin2hex(random_bytes(4));
$web = $root . '/web';
$keys = $root . '/keys';
mkdir($web, 0700, true);
mkdir($web . '/lib', 0700, true);
mkdir($keys, 0700, true);
$keyPath = $keys . '/primary.key';
$source = $root . '/source.db';
$sealed = $root . '/source.sealed';
$opened = $root . '/opened.db';

try {
    $api = file_get_contents(__DIR__ . '/../api.php');
    $dashboard = file_get_contents(__DIR__ . '/../index.js');
    $page = file_get_contents(__DIR__ . '/../index.html');
    $rotation = file_get_contents(__DIR__ . '/../scripts/rotate-sealed-copies.php');
    $configRestore = file_get_contents(__DIR__ . '/../scripts/restore-config.php');
    $accountRestore = file_get_contents(__DIR__ . '/../scripts/restore-account.php');
    $storeRestore = file_get_contents(__DIR__ . '/../scripts/restore-store.php');
    $setupGuide = file_get_contents(__DIR__ . '/../SETUP.md');
    $setupPage = file_get_contents(__DIR__ . '/../setup.php');
    encryptionExpect($api !== false && str_contains($api, "'encryption-status' => 'sys-encryption'")
        && str_contains($api, "'encryption-seal' => doEncryptionSeal")
        && str_contains($api, "'encryption-recovery-drill' => doEncryptionRecoveryDrill")
        && str_contains($api, "'package-runtime-custody-envelope-store' => doPackageRuntimeCustodyEnvelopeStore")
        && str_contains($api, 'requireWrite($post)') && str_contains($api, "logAction(\$pdo, 'encryption_seal'"),
        'encryption status is not protected by its dashboard page access rule');
    encryptionExpect($api !== false && str_contains($api, "'setup_configuration' => setupStoreConfigurationProtectionForSettings")
        && $dashboard !== false && str_contains($dashboard, 'Setup configuration at rest'),
        'Protection dashboard does not report setup configuration at-rest status');
    encryptionExpect($dashboard !== false && str_contains($dashboard, 'Live SQLCipher option')
        && encryptionSqlcipherCapability()['state'] !== '', 'Protection dashboard does not report SQLCipher driver readiness');
    encryptionExpect($dashboard !== false && str_contains($dashboard, 'function renderEncryptionStatus')
        && str_contains($dashboard, 'Verified encrypted copies protect backup artifacts')
        && str_contains($dashboard, 'Operator-declared host/volume coverage (not verified)')
        && str_contains($dashboard, 'Recovery-drill review')
        && str_contains($dashboard, 'Bootstrap configuration recovery is deliberately CLI-only')
        && str_contains($dashboard, 'Add optional remote custody envelope')
        && str_contains($dashboard, 'Local recovery remains required')
        && str_contains($dashboard, 'Create verified encrypted copies')
        && str_contains($dashboard, 'Run non-destructive recovery drill'),
        'dashboard does not explain encrypted-copy limitations');
    encryptionExpect($page !== false && str_contains($page, 'data-view="sys-encryption"')
        && str_contains($page, 'id="encryptionBody"'), 'dashboard protection view is unavailable');
    encryptionExpect($rotation !== false && str_contains($rotation, 'encryptionRotateConfiguredArtifacts')
        && str_contains($rotation, 'saveConfiguredSettings') && str_contains($rotation, '--confirm'),
        'configured sealed-copy rotation does not preserve recovery evidence before switching keys');
    encryptionExpect($configRestore !== false && str_contains($configRestore, 'encryptionRestoreBootstrapConfiguration')
        && str_contains($configRestore, '--confirm'),
        'sealed bootstrap configuration has no guarded recovery command');
    encryptionExpect($accountRestore !== false && $storeRestore !== false
        && str_contains($accountRestore, 'encryptionSealFile') && str_contains($storeRestore, 'encryptionSealFile')
        && str_contains($accountRestore, 'encryptionVerifySealedStoreArtifact') && str_contains($storeRestore, 'encryptionVerifySealedStoreArtifact')
        && str_contains($accountRestore, 'rollbackPlain') && str_contains($storeRestore, 'rollbackPlain'),
        'sealed database restoration does not retain a verified sealed rollback artifact');
    encryptionExpect($accountRestore !== false && $storeRestore !== false && str_contains($accountRestore, 'is_link($sourceArgument)')
        && str_contains($accountRestore, 'is_link($target)') && str_contains($storeRestore, 'is_link($sourceArgument)')
        && str_contains($storeRestore, 'is_link($target)'), 'database restore commands do not reject symlinked recovery paths');
    encryptionExpect($storeRestore !== false
        && str_contains($storeRestore, "\$config = \$store === 'setup' ? \$bootstrap : loadConfiguredSettings(\$configFile)"),
        'setup-store recovery still opens the damaged setup database before selecting its target');
    encryptionExpect($setupGuide !== false && str_contains($setupGuide, '--recovery-drill --quiet')
        && str_contains($setupGuide, 'Quarterly non-destructive recovery drill'),
        'setup guide does not provide a recurring recovery-drill schedule');
    encryptionExpect($setupPage !== false && str_contains($setupPage, 'create-local-key')
        && str_contains($setupPage, 'setup_key_create_token')
        && str_contains($setupPage, 'encryptionCreateLocalKey')
        && str_contains($setupPage, 'setupRequestedPrivateKeyPath')
        && str_contains($setupPage, 'setupSuggestedPackageSecretKeyPath')
        && str_contains($setupPage, 'create-package-secret-key')
        && str_contains($setupPage, '<legend>Package secrets key</legend>')
        && str_contains($setupPage, 'Suggested location:')
        && str_contains($setupPage, '$field[\'key\'] === \'package_runtime_secret_key_file\'')
        && str_contains($setupPage, '$editingLocked')
        && str_contains($setupPage, 'Unlock setup to view or change this section.')
        && str_contains($setupPage, 'Masked-path privacy')
        && str_contains($setupPage, 'Before rotation') && str_contains($setupPage, 'Never shown')
        && str_contains($setupPage, "['basics', 'privacy', 'schedule']")
        && str_contains($setupPage, 'Account collector schedule')
        && str_contains($setupPage, 'setupPackagePanels($current)')
        && str_contains($setupPage, 'save-package-settings')
        && str_contains($setupPage, 'signed package schema')
        && str_contains($setupPage, 'package_runtime_settings_save')
        && str_contains($setupPage, 'leave blank to use this location')
        && str_contains($setupPage, 'this step is complete')
        && str_contains($setupPage, 'Its contents were not shown or saved in the browser'),
        'setup does not provide a guarded local encryption-key quick start');

    $sample = require __DIR__ . '/../lib/config.sample.php';
    require_once __DIR__ . '/../lib/setup-panels.php';
    require_once __DIR__ . '/../lib/schedule.php';
    require_once __DIR__ . '/../lib/setup-cron-editor.php';
    $sampleText = file_get_contents(__DIR__ . '/../lib/config.sample.php');
    encryptionExpect(is_string($sample['scan_root'] ?? null) && str_starts_with($sample['scan_root'], '/')
        && $sampleText !== false && str_contains($sampleText, 'posix_getpwuid')
        && str_contains($sampleText, "realpath(__DIR__ . '/..')"),
        'scan-root default does not derive from the service account or installation directory');
    $fields = array_column(configFields($sample), null, 'key');
    $bootstrapFields = array_values(array_filter(configFields($sample),
        static fn(array $field): bool => ($field['section'] ?? 'basics') === 'basics'));
    encryptionExpect($bootstrapFields === [] && ($fields['password']['section'] ?? '') === 'security',
        'password management is not isolated to the Security section');
    encryptionExpect(isset(setupPanels(configFields($sample))['schedule'])
        && setupPanelId('plugin-panel') === 'plugin-panel', 'Setup panel registry is unavailable');
    $registeredPanel = setupPanels([], [['id' => 'sample-plugin', 'title' => 'Sample plugin', 'kind' => 'package', 'keywords' => ['sample']]]);
    encryptionExpect(($registeredPanel['sample-plugin']['kind'] ?? '') === 'package'
        && ($registeredPanel['sample-plugin']['keywords'] ?? []) === ['sample'],
        'Setup panel registry does not retain validated package metadata');
    encryptionExpect(cronEditorExpression('every-5m') === '0,5,10,15,20,25,30,35,40,45,50,55 * * * *'
        && cronEditorExpression('custom', ['minute' => '7', 'hour' => '2']) === '7 2 * * *',
        'reusable cron editor profiles are unavailable');
    $customCronRefused = false;
    try { cronEditorExpression('custom', ['minute' => '60']); } catch (ActionRefused) { $customCronRefused = true; }
    encryptionExpect($customCronRefused,
        'reusable cron editor accepts an out-of-range custom field');
    $cronApi = new SetupCronApi(__DIR__ . '/..');
    encryptionExpect(array_key_exists('custom', $cronApi->profiles())
        && method_exists($cronApi, 'preview') && method_exists($cronApi, 'status') && method_exists($cronApi, 'apply'),
        'restricted Setup cron API is unavailable');
    encryptionExpect(str_contains(renderSetupCronEditor('plugin_job', 'Plugin job', ['profile' => 'custom', 'custom' => ['minute' => '7']]), 'plugin_job_minute')
        && str_contains(renderSetupCronEditor('plugin_job', 'Plugin job', ['profile' => 'custom', 'custom' => ['minute' => '7']]), 'Custom cron builder'),
        'reusable Setup cron editor component is unavailable');
    $panelRegistry = new SetupPanelRegistry([]);
    $panelApi = new SetupPanelApi('sample-plugin', $panelRegistry, $cronApi);
    $panelApi->addPanel(['id' => 'sample-plugin-settings', 'title' => 'Sample plugin settings', 'feature' => 'someone-else']);
    encryptionExpect(($panelRegistry->all()['sample-plugin-settings']['kind'] ?? '') === 'package'
        && ($panelRegistry->all()['sample-plugin-settings']['feature'] ?? '') === 'sample-plugin',
        'restricted Setup package panel API is unavailable');
    encryptionExpect(($sample['encryption']['stores'] ?? []) === ['config', 'setup', 'account'],
        'quick-install encryption defaults included optional host or assistant stores');
    encryptionExpect(isset($fields['encryption_retention_recovery_tiers'])
        && !isset($fields['cfg_encryption__retention_keep_daily']),
        'setup exposed duplicate internal long-term retention controls');
    encryptionExpect(isset($fields['encryption_mode']['options']['operator_managed_volume']),
        'operator-managed volume option is missing from setup');
    encryptionExpect(fieldApplies($fields['encryption_key_file'], ['encryption_mode' => 'sealed_copies_local'])
        && !fieldApplies($fields['encryption_key_file'], ['encryption_mode' => 'none']),
        'local key field does not follow the selected encryption mode');
    encryptionExpect(fieldApplies($fields['encryption_stores'], ['encryption_mode' => 'operator_managed_volume']),
        'store choice is missing for operator-managed volume protection');
    $volumeStatus = encryptionStatus(new PDO('sqlite::memory:'), ['encryption' => ['mode' => 'operator_managed_volume', 'stores' => ['account']]], $web);
    encryptionExpect($volumeStatus['ready'] && $volumeStatus['evidence'] === 'operator_declared',
        'operator-managed volume protection was presented as verified encrypted-copy evidence');

    encryptionCreateLocalKey($keyPath, $web);
    $key = encryptionReadLocalKey($keyPath, $web);
    encryptionExpect(strlen($key) === 32, 'local key did not contain 256 bits');
    encryptionExpect((fileperms($keyPath) & 0077) === 0, 'local key permissions are too broad');
    $privateKeyPath = $root . '/.private/zogograph.key';
    encryptionCreateLocalKey($privateKeyPath, $web);
    $privatePolicy = file_get_contents($root . '/.private/.htaccess');
    encryptionExpect(is_string($privatePolicy) && str_contains($privatePolicy, 'Require all denied')
        && str_contains($privatePolicy, 'Deny from all'), 'private key directory lacks an Apache deny policy');
    if (function_exists('symlink') && @symlink($web, $root . '/linked-web')) {
        $linkedKey = $root . '/linked-web/exposed.key';
        try { encryptionCreateLocalKey($linkedKey, $web); throw new RuntimeException('local key creation followed a symlinked parent into the web root'); }
        catch (RuntimeException $error) { encryptionExpect($error->getMessage() !== 'local key creation followed a symlinked parent into the web root', $error->getMessage()); }
        encryptionExpect(!file_exists($web . '/exposed.key'), 'rejected symlinked local key path still wrote key material in the web root');
        @unlink($root . '/linked-web');
    }
    $defaultDatabase = $root . '/default/stats.db'; mkdir(dirname($defaultDatabase), 0700, true);
    $defaultSealed = dirname($defaultDatabase) . '/sealed';
    symlink($web, $defaultSealed);
    try {
        encryptionArtifactDirectory(['database' => $defaultDatabase, 'encryption' => []], $web);
        throw new RuntimeException('symlinked default encrypted-copy directory was accepted');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'symlinked default encrypted-copy directory was accepted', $error->getMessage());
    }
    try {
        encryptionArtifactDirectory(['database' => $web . '/data/stats.db', 'encryption' => []], $web);
        throw new RuntimeException('web-root default encrypted-copy directory was accepted');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'web-root default encrypted-copy directory was accepted', $error->getMessage());
    }
    @unlink($defaultSealed);
    encryptionValidateSettings(['encryption' => ['mode' => 'sealed_copies_local',
        'local_key_file' => $keyPath, 'stores' => ['account']]], $web);
    encryptionValidateSettings(['encryption' => ['mode' => 'operator_managed_volume',
        'stores' => ['setup']]], $web);
    try {
        encryptionValidateSettings(['encryption' => ['mode' => 'sealed_copies_local', 'local_key_file' => $keyPath,
            'artifact_directory' => $web . '/public-sealed', 'stores' => ['account']]], $web);
        throw new RuntimeException('web-root encrypted-copy directory was accepted');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'web-root encrypted-copy directory was accepted', $error->getMessage());
    }
    try {
        encryptionValidateSettings(['encryption' => ['mode' => 'sealed_copies_local',
            'local_key_file' => '', 'stores' => []]], $web);
        throw new RuntimeException('invalid encryption setup policy was accepted');
    } catch (InvalidArgumentException|RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'invalid encryption setup policy was accepted', $error->getMessage());
    }
    try {
        encryptionValidateSettings(['encryption' => ['mode' => 'sealed_copies_local', 'local_key_file' => $keyPath,
            'stores' => ['account'], 'retention_recovery_tiers' => ['monthly' => -1]]], $web);
        throw new RuntimeException('invalid long-term recovery retention was accepted');
    } catch (InvalidArgumentException $error) {
        encryptionExpect($error->getMessage() !== 'invalid long-term recovery retention was accepted', $error->getMessage());
    }
    file_put_contents($source, str_repeat("private sqlite record\n", 100000));
    $sealedInfo = encryptionSealFile($source, $sealed, $key, 'stats:1');
    encryptionExpect(hash_equals(encryptionReadArtifactDataKey($sealed, $key, 'stats:1'), encryptionReadArtifactDataKey($sealed, $key, 'stats:1')),
        'sealed artifact data key could not be reopened for in-memory custody wrapping');
    $providerOpened = $root . '/provider-opened.db';
    encryptionOpenFileWithDataKey($sealed, $providerOpened, encryptionReadArtifactDataKey($sealed, $key, 'stats:1'), 'stats:1');
    encryptionExpect(hash_file('sha256', $providerOpened) === hash_file('sha256', $source), 'transient provider key did not authenticate and recover the sealed artifact');
    try { encryptionReadArtifactDataKey($sealed, $key, 'stats:other'); throw new RuntimeException('sealed artifact data key accepted a mismatched identity'); }
    catch (RuntimeException $error) { encryptionExpect($error->getMessage() !== 'sealed artifact data key accepted a mismatched identity', $error->getMessage()); }
    encryptionExpect($sealedInfo['plaintext_bytes'] === filesize($source), 'sealed byte count is wrong');
    encryptionExpect(!str_contains((string)file_get_contents($sealed), 'private sqlite record'), 'sealed copy contains plaintext');
    encryptionExpect((fileperms($sealed) & 0077) === 0, 'sealed artifact permissions are too broad');
    $secondSealed = $root . '/source-second.sealed';
    encryptionSealFile($source, $secondSealed, $key, 'stats:1');
    $sealedHeader = function (string $path): array {
        $handle = fopen($path, 'rb');
        if ($handle === false) throw new RuntimeException('could not read sealed artifact header');
        try {
            encryptionExpect(fread($handle, strlen(ENCRYPTION_MAGIC)) === ENCRYPTION_MAGIC, 'sealed artifact magic is invalid');
            $size = unpack('Nsize', (string)fread($handle, 4))['size'];
            return json_decode((string)fread($handle, $size), true, 64, JSON_THROW_ON_ERROR);
        } finally { fclose($handle); }
    };
    $firstHeader = $sealedHeader($sealed); $secondHeader = $sealedHeader($secondSealed);
    $firstDek = openssl_decrypt(base64_decode($firstHeader['wrapped_key'], true), 'aes-256-gcm', $key, OPENSSL_RAW_DATA,
        base64_decode($firstHeader['wrap_nonce'], true), base64_decode($firstHeader['wrap_tag'], true), 'stats:1');
    $secondDek = openssl_decrypt(base64_decode($secondHeader['wrapped_key'], true), 'aes-256-gcm', $key, OPENSSL_RAW_DATA,
        base64_decode($secondHeader['wrap_nonce'], true), base64_decode($secondHeader['wrap_tag'], true), 'stats:1');
    encryptionExpect(is_string($firstDek) && is_string($secondDek) && strlen($firstDek) === 32 && !hash_equals($firstDek, $secondDek),
        'sealed artifacts unexpectedly reused a data-encryption key');
    encryptionOpenFile($sealed, $opened, $key);
    encryptionExpect(hash_file('sha256', $source) === hash_file('sha256', $opened), 'opened copy differs from source');
    encryptionExpect((fileperms($opened) & 0077) === 0, 'decrypted recovery output permissions are too broad');
    encryptionVerifySealedArtifact($sealed, $key, (int)filesize($source));
    file_put_contents($root . '/not-a-store', 'not sqlite');
    try { encryptionVerifyRecoveredStore($root . '/not-a-store', 'account'); throw new RuntimeException('invalid recovered SQLite was accepted'); }
    catch (Throwable $error) { encryptionExpect($error->getMessage() !== 'invalid recovered SQLite was accepted', $error->getMessage()); }
    file_put_contents($root . '/invalid-config.php', "<?php return [;\n");
    try { encryptionVerifyRecoveredStore($root . '/invalid-config.php', 'config'); throw new RuntimeException('invalid recovered configuration syntax was accepted'); }
    catch (Throwable $error) { encryptionExpect($error->getMessage() !== 'invalid recovered configuration syntax was accepted', $error->getMessage()); }
    $recovery = $web . '/data/recovery';
    $configTarget = $web . '/lib/config.php';
    $recoveredText = "<?php return ['private' => 'recovered'];\n";
    $priorText = "<?php return ['private' => 'prior'];\n";
    $configPlain = $root . '/recoverable-config.php';
    $configSealed = $root . '/recoverable-config.sealed';
    file_put_contents($configPlain, $recoveredText);
    file_put_contents($configTarget, $priorText);
    encryptionSealFile($configPlain, $configSealed, $key, 'config:recoverable');
    $configBackup = encryptionRestoreBootstrapConfiguration($configSealed, $keyPath, $configTarget, $web, $recovery);
    $backupOpened = $root . '/prior-config-opened.php';
    if (is_string($configBackup)) encryptionOpenFile($configBackup, $backupOpened, $key);
    encryptionExpect(is_string($configBackup) && str_ends_with($configBackup, '.sealed') && !str_contains((string)file_get_contents($configBackup), $priorText)
        && file_get_contents($backupOpened) === $priorText && file_get_contents($configTarget) === $recoveredText
        && !glob($recovery . '/config-restore-*.php'),
        'guarded bootstrap restoration did not atomically replace the configuration and retain a sealed prior copy');
    $invalidSealed = $root . '/invalid-config.sealed';
    encryptionSealFile($root . '/invalid-config.php', $invalidSealed, $key, 'config:invalid');
    try {
        encryptionRestoreBootstrapConfiguration($invalidSealed, $keyPath, $configTarget, $web, $recovery);
        throw new RuntimeException('invalid sealed configuration was restored');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'invalid sealed configuration was restored', $error->getMessage());
    }
    encryptionExpect(file_get_contents($configTarget) === $recoveredText,
        'invalid sealed configuration changed the existing bootstrap');
    $replacementKeyPath = $keys . '/replacement.key';
    encryptionCreateLocalKey($replacementKeyPath, $web);
    $replacement = encryptionReadLocalKey($replacementKeyPath, $web);
    $rotated = $root . '/rotated.sealed';
    encryptionRotateSealedFile($sealed, $rotated, $key, $replacement, 'stats:2');
    encryptionOpenFile($rotated, $root . '/rotated.db', $replacement);
    encryptionExpect(hash_file('sha256', $source) === hash_file('sha256', $root . '/rotated.db'),
        'rotated sealed copy differs from source');

    if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        file_put_contents($web . '/lib/config.php', "<?php return ['private' => true];\n"); chmod($web . '/lib/config.php', 0600);
        $live = new PDO('sqlite:' . $root . '/live.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $live->exec('CREATE TABLE evidence (value TEXT NOT NULL)');
        $live->exec("INSERT INTO evidence VALUES ('committed WAL value')");
        foreach ([$root . '/live.db', $root . '/live.db-wal', $root . '/live.db-shm'] as $privateFile) if (is_file($privateFile)) chmod($privateFile, 0600);
        $snapshot = $root . '/live.sealed';
        encryptionSealSqliteSnapshot($live, $snapshot, $key, 'stats:sqlite-snapshot');
        encryptionOpenFile($snapshot, $root . '/live-opened.db', $key);
        $restored = new PDO('sqlite:' . $root . '/live-opened.db');
        encryptionExpect($restored->query('SELECT value FROM evidence')->fetchColumn() === 'committed WAL value',
            'sealed SQLite snapshot missed committed data');

        $config = ['database' => $root . '/live.db', 'encryption' => [
            'mode' => 'sealed_copies_local', 'local_key_file' => $keyPath,
            'artifact_directory' => $root . '/sealed', 'stores' => ['config', 'account'],
        ]];
        if (function_exists('symlink')) {
            $configTarget = $web . '/lib/config.php'; $configReal = $root . '/real-config.php';
            copy($configTarget, $configReal); @unlink($configTarget);
            if (@symlink($configReal, $configTarget)) {
                try { encryptionSealConfiguredCopies($config, $live, $web); throw new RuntimeException('symlinked selected configuration source was sealed'); }
                catch (RuntimeException $error) { encryptionExpect($error->getMessage() !== 'symlinked selected configuration source was sealed', $error->getMessage()); }
                @unlink($configTarget); copy($configReal, $configTarget); chmod($configTarget, 0600);
                @unlink($configTarget); @symlink($root . '/missing-config.php', $configTarget);
                encryptionExpect(str_contains(implode(' ', encryptionSensitiveSourceWarnings(encryptionStorePaths($config, $web), ['config'])), 'symbolic link'),
                    'protection status did not report a dangling selected configuration symlink');
                try { encryptionSealConfiguredCopies($config, $live, $web); throw new RuntimeException('dangling selected configuration symlink was skipped'); }
                catch (RuntimeException $error) { encryptionExpect($error->getMessage() !== 'dangling selected configuration symlink was skipped', $error->getMessage()); }
                @unlink($configTarget); copy($configReal, $configTarget); chmod($configTarget, 0600);
            } else { copy($configReal, $configTarget); chmod($configTarget, 0600); }
            @unlink($configReal);
        }
        if (function_exists('symlink') && @symlink($root . '/live.db', $root . '/linked-account.db')) {
            $linkedAccount = $config; $linkedAccount['database'] = $root . '/linked-account.db'; $linkedAccount['encryption']['stores'] = ['account'];
            encryptionExpect(str_contains(implode(' ', encryptionSensitiveSourceWarnings(encryptionStorePaths($linkedAccount, $web), ['account'])), 'account source is a symbolic link'),
                'protection status did not report a selected account-database symlink');
            @unlink($root . '/linked-account.db');
        }
        $copies = encryptionSealConfiguredCopies($config, $live, $web);
        encryptionExpect(count($copies) === 2 && $copies[0]['store'] === 'config'
            && $copies[1]['store'] === 'account', 'configured sealed copies were not made');
        encryptionOpenFile((string)$copies[0]['local_path'], $root . '/restored-config.php', $key);
        encryptionExpect(str_contains((string)file_get_contents($root . '/restored-config.php'), "'private' => true"),
            'sealed configuration copy differs from source');
        $live->exec('CREATE TABLE encryption_artifacts (artifact_id TEXT PRIMARY KEY, store TEXT, created_at INTEGER, last_verified_at INTEGER, last_recovery_drill_at INTEGER, local_path TEXT, ciphertext_bytes INTEGER, ciphertext_sha256 TEXT, state TEXT)');
        $live->exec('CREATE TABLE encryption_remote_envelopes (artifact_id TEXT NOT NULL, provider_package TEXT NOT NULL, provider_key_id TEXT NOT NULL, ciphertext_sha256 TEXT NOT NULL, envelope_sha256 TEXT, state TEXT NOT NULL, requested_at INTEGER NOT NULL, stored_at INTEGER, verified_at INTEGER, detail TEXT, PRIMARY KEY (artifact_id, provider_package, provider_key_id))');
        encryptionRecordArtifacts($live, $copies);
        encryptionExpect((int)$live->query('SELECT COUNT(*) FROM encryption_artifacts')->fetchColumn() === 2,
            'sealed artifacts were not recorded for replication');
        $remoteArtifact = (string)$copies[1]['artifact_id']; $remoteCiphertext = (string)$copies[1]['sha256'];
        encryptionRecordRemoteEnvelopePending($live, $remoteArtifact, 'hostscope-key-custody', 'key_' . str_repeat('a', 32));
        encryptionExpect((string)$live->query("SELECT state FROM encryption_remote_envelopes WHERE artifact_id = '" . $remoteArtifact . "'")->fetchColumn() === 'pending',
            'remote custody intent was not recorded against a verified local artifact');
        $envelopeHash = hash('sha256', 'test remote envelope receipt');
        encryptionRecordRemoteEnvelopeStored($live, $remoteArtifact, 'hostscope-key-custody', 'key_' . str_repeat('a', 32), $remoteCiphertext, $envelopeHash);
        encryptionExpect((string)$live->query("SELECT state FROM encryption_remote_envelopes WHERE artifact_id = '" . $remoteArtifact . "'")->fetchColumn() === 'stored',
            'matching remote custody receipt did not become stored evidence');
        encryptionExpect((encryptionStatus($live, $config, $web)['remote_envelopes'][0]['state'] ?? null) === 'stored',
            'protection status did not disclose bounded remote-envelope evidence');
        try { encryptionRecordRemoteEnvelopeStored($live, $remoteArtifact, 'hostscope-key-custody', 'key_' . str_repeat('a', 32), str_repeat('0', 64), $envelopeHash); throw new RuntimeException('mismatched remote custody receipt was accepted'); }
        catch (InvalidArgumentException $error) { encryptionExpect($error->getMessage() !== 'mismatched remote custody receipt was accepted', $error->getMessage()); }
        $failureArtifact = (string)$copies[0]['artifact_id'];
        encryptionRecordRemoteEnvelopePending($live, $failureArtifact, 'hostscope-key-custody', 'key_' . str_repeat('b', 32));
        encryptionRecordRemoteEnvelopeFailure($live, $failureArtifact, 'hostscope-key-custody', 'key_' . str_repeat('b', 32), 'endpoint temporarily unavailable');
        encryptionExpect((string)$live->query("SELECT state FROM encryption_remote_envelopes WHERE artifact_id = '" . $failureArtifact . "'")->fetchColumn() === 'failed'
            && str_contains(implode(' ', encryptionStatus($live, $config, $web)['warnings']), 'remote custody envelope attempts failed'),
            'failed remote custody attempt was not retained as bounded protection evidence');
        $reverified = encryptionVerifyConfiguredCopies($config, $live, $web);
        encryptionExpect($reverified['verified'] === 2 && $reverified['failed'] === 0,
            'retained encrypted artifacts were not re-verified');
        $beforeDrill = encryptionStatus($live, $config, $web);
        encryptionExpect(!$beforeDrill['ready'] && str_contains(implode(' ', $beforeDrill['warnings']), 'No successful non-destructive recovery drill'),
            'sealed-copy status did not require an initial recovery drill');
        $drill = encryptionRunRecoveryDrill($config, $live, $web);
        encryptionExpect($drill['tested'] === 2 && $drill['warnings'] === []
            && (int)$live->query('SELECT COUNT(*) FROM encryption_artifacts WHERE last_recovery_drill_at IS NOT NULL')->fetchColumn() === 2,
            'recovery drill did not record current selected-store recovery evidence');
        $status = encryptionStatus($live, $config, $web);
        encryptionExpect($status['ready'] && count($status['artifacts']) === 2, 'sealed-copy status did not report verified coverage');
        chmod($web . '/lib/config.php', 0644);
        encryptionExpect(str_contains(implode(' ', encryptionStatus($live, $config, $web)['warnings']), 'config source is readable by group or other users'),
            'protection status did not report a broadly readable selected configuration source');
        chmod($web . '/lib/config.php', 0600);
        chmod($root . '/live.db', 0644);
        encryptionExpect(str_contains(implode(' ', encryptionStatus($live, $config, $web)['warnings']), 'account source is readable or writable by group or other users'),
            'protection status did not report a broadly accessible selected account database');
        chmod($root . '/live.db', 0600);
        $policyConfig = $config; $policyConfig['encryption'] += ['backup_schedule' => 'daily', 'retention_days' => 30,
            'retention_recovery_tiers' => ['daily' => 7, 'weekly' => 4, 'monthly' => 12], 'recovery_drill_max_age_days' => 90];
        $policyStatus = encryptionStatus($live, $policyConfig, $web);
        encryptionExpect($policyStatus['policy'] === ['backup_schedule' => 'daily', 'retention_days' => 30,
            'retention_tiers' => ['daily' => 7, 'weekly' => 4, 'monthly' => 12], 'recovery_drill_review_days' => 90],
            'protection status did not expose a safe effective recovery policy summary');
        $overdueDrill = $config; $overdueDrill['encryption']['recovery_drill_max_age_days'] = 1;
        $live->prepare('UPDATE encryption_artifacts SET last_recovery_drill_at = ?')->execute([time() - 2 * 86400]);
        $overdueStatus = encryptionStatus($live, $overdueDrill, $web);
        encryptionExpect(!$overdueStatus['ready'] && str_contains(implode(' ', $overdueStatus['warnings']), 'older than the selected review period'),
            'overdue recovery drill was reported as current protection evidence');
        $live->prepare('UPDATE encryption_artifacts SET last_recovery_drill_at = ?')->execute([time()]);
        encryptionExpect(encryptionRecoveryDrillMaximumAge(['recovery_drill_max_age_days' => 0]) === null,
            'zero recovery-drill review period did not disable periodic reminders');
        encryptionExpect(is_array($status['storage_estimate']) && $status['storage_estimate']['required_bytes'] > $status['storage_estimate']['source_bytes'],
            'sealed-copy status did not estimate next-run storage needs');
        $optional = $config; $optional['encryption']['stores'] = ['account', 'host', 'ai'];
        $optionalStatus = encryptionStatus($live, $optional, $web);
        encryptionExpect($optionalStatus['ready'] && $optionalStatus['waiting_stores'] === ['host', 'ai'],
            'missing optional databases were treated as failed encryption coverage');
        $stale = $config; $stale['encryption']['backup_schedule'] = 'daily';
        $live->prepare("UPDATE encryption_artifacts SET created_at = ? WHERE store = 'account'")->execute([time() - 3 * 86400]);
        $staleStatus = encryptionStatus($live, $stale, $web);
        encryptionExpect(!$staleStatus['ready'] && str_contains(implode(' ', $staleStatus['warnings']), 'older than the configured schedule'),
            'stale encrypted copy was reported as current recovery evidence');
        $live->prepare("UPDATE encryption_artifacts SET created_at = ? WHERE store = 'account'")->execute([time()]);
        $volume = encryptionStatus($live, ['encryption' => ['mode' => 'operator_managed_volume', 'stores' => ['account']]], $web);
        encryptionExpect($volume['ready'] && $volume['warnings'] !== [], 'volume-protection status did not explain its verification limit');
        $unsafeDirectory = $config; $unsafeDirectory['encryption']['artifact_directory'] = $web . '/public-sealed';
        encryptionExpect(str_contains(implode(' ', encryptionStatus($live, $unsafeDirectory, $web)['warnings']), 'custom encrypted-copy directory is unsafe'),
            'protection status did not report an unsafe custom encrypted-copy directory');
        $oldPath = $root . '/sealed/account.old.sealed';
        copy((string)$copies[1]['local_path'], $oldPath);
        $oldHash = hash_file('sha256', $oldPath);
        $live->prepare("INSERT INTO encryption_artifacts (artifact_id, store, created_at, last_verified_at, local_path, ciphertext_bytes, ciphertext_sha256, state)
            VALUES (?, 'account', ?, ?, ?, ?, ?, 'verified')")
            ->execute(['account:old', time() - 31 * 86400, time() - 31 * 86400, $oldPath, filesize($oldPath), $oldHash]);
        $retentionConfig = $config;
        $retentionConfig['encryption']['retention_days'] = 30;
        $pruned = encryptionPruneConfiguredCopies($retentionConfig, $live);
        encryptionExpect($pruned['removed'] === 1 && !is_file($oldPath), 'expired verified sealed artifact was not pruned');
        encryptionExpect(is_file((string)$copies[1]['local_path']), 'newest verified sealed artifact was pruned');
        $afterPrune = encryptionStatus($live, $retentionConfig, $web);
        $accountEvidence = array_values(array_filter($afterPrune['artifacts'], fn(array $artifact): bool => $artifact['store'] === 'account'));
        encryptionExpect(count($accountEvidence) === 1 && $accountEvidence[0]['copies'] === 1,
            'pruned encrypted artifacts were reported as current verified copies');
        encryptionExpect(encryptionRetentionDays(['retention_days' => 30, 'retention_days_by_store' => ['setup' => 365]], 'setup') === 365
            && encryptionRetentionDays(['retention_days' => 30, 'retention_days_by_store' => ['setup' => 365]], 'account') === 30,
            'per-store retention policy did not fall back to the normal retention target');
        $tiered = encryptionRetentionPreservedArtifactIds([
            ['artifact_id' => 'account:new', 'store' => 'account', 'created_at' => strtotime('2026-08-27 12:00:00 UTC')],
            ['artifact_id' => 'account:old', 'store' => 'account', 'created_at' => strtotime('2026-08-26 12:00:00 UTC')],
            ['artifact_id' => 'setup:new', 'store' => 'setup', 'created_at' => strtotime('2026-08-27 12:00:00 UTC')],
        ], ['retention_recovery_tiers' => ['daily' => 1]]);
        encryptionExpect(isset($tiered['account:new']) && !isset($tiered['account:old']) && isset($tiered['setup:new']),
            'tiered recovery retention did not preserve one newest distinct day per store');
        $overridePath = $root . '/sealed/account.override.sealed';
        copy((string)$copies[1]['local_path'], $overridePath);
        $overrideHash = hash_file('sha256', $overridePath);
        $live->prepare("INSERT INTO encryption_artifacts (artifact_id, store, created_at, last_verified_at, local_path, ciphertext_bytes, ciphertext_sha256, state)
            VALUES (?, 'account', ?, ?, ?, ?, ?, 'verified')")
            ->execute(['account:override', time() - 31 * 86400, time() - 31 * 86400, $overridePath, filesize($overridePath), $overrideHash]);
        $overrideConfig = $retentionConfig;
        $overrideConfig['encryption']['retention_days_by_store'] = ['account' => 0];
        encryptionExpect(encryptionPruneConfiguredCopies($overrideConfig, $live)['removed'] === 0 && is_file($overridePath),
            'zero per-store retention override did not preserve the old encrypted artifact');
        $overrideConfig['encryption']['retention_days_by_store'] = ['account' => 1];
        encryptionExpect(encryptionPruneConfiguredCopies($overrideConfig, $live)['removed'] === 1 && !is_file($overridePath),
            'per-store retention override did not prune the old encrypted artifact');
        $tierPath = $root . '/sealed/account.monthly.sealed';
        copy((string)$copies[1]['local_path'], $tierPath);
        $tierHash = hash_file('sha256', $tierPath);
        $live->prepare("INSERT INTO encryption_artifacts (artifact_id, store, created_at, last_verified_at, local_path, ciphertext_bytes, ciphertext_sha256, state)
            VALUES (?, 'account', ?, ?, ?, ?, ?, 'verified')")
            ->execute(['account:monthly', time() - 40 * 86400, time() - 40 * 86400, $tierPath, filesize($tierPath), $tierHash]);
        $tierConfig = $retentionConfig; $tierConfig['encryption']['retention_recovery_tiers'] = ['monthly' => 2];
        encryptionExpect(encryptionPruneConfiguredCopies($tierConfig, $live)['removed'] === 0 && is_file($tierPath),
            'monthly recovery tier did not retain a representative old encrypted artifact');
        $tierConfig['encryption']['retention_recovery_tiers'] = ['monthly' => 1];
        encryptionExpect(encryptionPruneConfiguredCopies($tierConfig, $live)['removed'] === 1 && !is_file($tierPath),
            'reduced monthly recovery tier did not release the old encrypted artifact');
        file_put_contents((string)$copies[0]['local_path'], 'tampered ciphertext');
        $failedVerification = encryptionVerifyConfiguredCopies($retentionConfig, $live, $web);
        encryptionExpect($failedVerification['failed'] === 1 && (int)$live->query("SELECT COUNT(*) FROM encryption_artifacts WHERE state = 'verification_failed'")->fetchColumn() === 1,
            'tampered retained encrypted artifact was not marked as failed evidence');
        $failedStatus = encryptionStatus($live, $retentionConfig, $web);
        encryptionExpect(str_contains(implode(' ', $failedStatus['warnings']), 'failed their last verification'),
            'failed encrypted artifact was not visible in protection status');
        try { encryptionRotateConfiguredArtifacts($config, $live, $web, $replacementKeyPath, fn(array $updated): null => null); throw new RuntimeException('rotation skipped selected store without verified coverage'); }
        catch (RuntimeException $error) { encryptionExpect($error->getMessage() !== 'rotation skipped selected store without verified coverage', $error->getMessage()); }
        $rotationConfig = $config; $rotationConfig['encryption']['stores'] = ['account']; $rotationSaved = false;
        $rotatedConfigured = encryptionRotateConfiguredArtifacts($rotationConfig, $live, $web, $replacementKeyPath,
            function (array $updated) use (&$rotationSaved): void { $rotationSaved = isset($updated['encryption']['local_key_file']); });
        encryptionExpect(count($rotatedConfigured) === 1 && $rotationSaved
            && $rotationConfig['encryption']['local_key_file'] === $replacementKeyPath,
            'configured sealed-copy rotation did not prepare recovery evidence before switching keys');
        encryptionExpect((int)$live->query("SELECT COUNT(*) FROM encryption_artifacts WHERE state = 'superseded'")->fetchColumn() >= 1
            && (int)$live->query("SELECT COUNT(*) FROM encryption_artifacts WHERE state = 'verified'")->fetchColumn() >= 1,
            'configured sealed-copy rotation did not retain old and new recovery evidence separately');
        encryptionOpenFile((string)$rotatedConfigured[0]['local_path'], $root . '/rotated-configured.db', $replacement);
        $rotationStatus = encryptionStatus($live, $rotationConfig, $web);
        encryptionExpect(count($rotationStatus['superseded_artifacts']) >= 1,
            'protection status did not disclose retained superseded recovery evidence');
        $retired = encryptionRetireSupersededArtifacts($rotationConfig, $live, $web, $keyPath);
        encryptionExpect($retired >= 1 && (int)$live->query("SELECT COUNT(*) FROM encryption_artifacts WHERE state = 'retired'")->fetchColumn() >= 1,
            'superseded sealed copies were not retired after current-key recovery verification');
        $retiredStatus = encryptionStatus($live, $rotationConfig, $web);
        encryptionExpect(count($retiredStatus['retired_artifacts']) >= 1,
            'protection status did not retain retired-copy audit evidence');
    }

    $tampered = $root . '/tampered.sealed';
    copy($sealed, $tampered);
    $handle = fopen($tampered, 'r+b');
    fseek($handle, -20, SEEK_END);
    fwrite($handle, "x");
    fclose($handle);
    try {
        encryptionOpenFile($tampered, $root . '/tampered.db', $key);
        throw new RuntimeException('tampered artifact was accepted');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'tampered artifact was accepted', $error->getMessage());
    }

    try {
        encryptionReadLocalKey($keyPath, $keys);
        throw new RuntimeException('web-root key was accepted');
    } catch (RuntimeException $error) {
        encryptionExpect($error->getMessage() !== 'web-root key was accepted', $error->getMessage());
    }
    echo "encryption checks passed\n";
} finally {
    foreach (glob($root . '/*') ?: [] as $path) {
        if (is_dir($path)) foreach (glob($path . '/*') ?: [] as $child) {
            if (is_dir($child)) foreach (glob($child . '/*') ?: [] as $grandchild) @unlink($grandchild);
            @is_dir($child) ? @rmdir($child) : @unlink($child);
        }
        @is_dir($path) ? @rmdir($path) : @unlink($path);
    }
    @rmdir($root);
}
