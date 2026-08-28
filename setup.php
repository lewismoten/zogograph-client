<?php
declare(strict_types=1);

/*
 * The config wizard, in a browser or at a prompt.
 *
 * Both front ends ask the same questions, because both read them from
 * lib/configure.php. Answers are overlaid on lib/config.sample.php, so the
 * sample stays the single description of what an option is and what it
 * defaults to.
 *
 * Who is allowed to write lib/config.php:
 *
 *   the file is missing        -> anybody. This is an install, and there is no
 *                                 password yet to ask anyone for.
 *   the file exists            -> only somebody who can type the password that
 *                                 is already in it.
 *   the file exists, sign-in
 *   was never configured       -> nobody. There is no secret to prove, so there
 *                                 is nothing to authorise with.
 *
 * Anyone who cannot write still gets the finished text to paste, which needs
 * filesystem access and is therefore its own authorisation.
 *
 * The existing config is read for exactly two values - the password hash to
 * check against, and the mask secret so that reconfiguring does not rename
 * every masked path on the dashboard. Neither is ever shown to somebody who has
 * not proved the password.
 *
 * The install window is still a window: between uploading these files and
 * finishing setup, whoever reaches this page first chooses the password. Do it
 * straight away; afterwards the same protected backend supports later changes.
 */

require_once __DIR__ . '/lib/boot.php';
if (PHP_SAPI !== 'cli') {
    installErrorBoundary('html');
}

$root = __DIR__;
$samplePath = $root . '/lib/config.sample.php';
$targetPath = $root . '/lib/config.php';

if (!is_file($samplePath)) {
    $missing = 'Cannot find lib/config.sample.php next to this script.';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $missing . "\n");
        exit(1);
    }
    http_response_code(500);
    exit($missing);
}

require_once $root . '/lib/configure.php';
require_once $root . '/lib/encryption.php';
require_once $root . '/lib/setupstore.php';
require_once $root . '/lib/setup-preflight.php';
require_once $root . '/lib/schedule.php';
require_once $root . '/lib/setup-cron-editor.php';
require_once $root . '/lib/setup-panels.php';
require_once $root . '/lib/package-runtime.php';
require_once $root . '/lib/auth.php';
require_once $root . '/lib/openid.php';
require_once $root . '/lib/remote-deployment.php';
require_once $root . '/lib/db.php';
require_once $root . '/lib/account-merge.php';
// For hostCronLine/hostCronCommand, shown after a successful write.
require_once $root . '/lib/hostdb.php';

/**
 * How long an unlock lasts. Long enough to fill a form in, and no longer.
 *
 * Declared before the dispatch below, not beside the function that uses it:
 * unlike a function, a file-scope `const` is not hoisted, so one written
 * further down the file simply does not exist yet when runWeb() reads it.
 */
const SETUP_UNLOCK_SECONDS = 900;

$sample = require $samplePath;
$fields = allConfigFields($sample, $samplePath);
$existing = readExistingSecrets($targetPath);

if (PHP_SAPI === 'cli') {
    runCli($sample, $fields, $root, $targetPath, $existing, array_slice($argv, 1));
    exit(0);
}

runWeb($sample, $fields, $root, $targetPath, $existing);

/**
 * The two values a reconfigure needs from the config already in place.
 *
 * Deliberately narrow. Everything else about the existing install is none of
 * this page's business, and pulling the whole array into scope is how a value
 * ends up echoed into the form by accident.
 *
 * @return array{password_hash: string, mask_secret: string}|null null if there
 *         is no config yet.
 */
function readExistingSecrets(string $targetPath): ?array
{
    if (!is_file($targetPath)) {
        return null;
    }

    try {
        $current = loadConfiguredSettings($targetPath);
    } catch (Throwable) {
        // A config too broken to load cannot authorise anything, but it is
        // still a file, so it still may not be overwritten.
        return ['password_hash' => '', 'mask_secret' => ''];
    }

    return [
        'password_hash' => is_array($current) ? (string)($current['password_hash'] ?? '') : '',
        'mask_secret' => is_array($current) ? (string)($current['mask_secret'] ?? '') : '',
    ];
}

/**
 * Whether a supplied password authorises replacing the config.
 *
 * An install with no password configured cannot be authorised at all - there is
 * no secret to prove. That is deliberate: the alternative is a system anybody
 * can reconfigure, which is worse than one that makes you use a text editor.
 */
function mayOverwrite(?array $existing, string $supplied): bool
{
    if ($existing === null) {
        return true;
    }
    if ($existing['password_hash'] === '' || $supplied === '') {
        return false;
    }
    return password_verify($supplied, $existing['password_hash']);
}

/** The whole existing config, read only once the password has been proved. */
function readExistingConfig(string $targetPath): ?array
{
    try {
        $current = loadConfiguredSettings($targetPath);
    } catch (Throwable) {
        return null;
    }
    return is_array($current) ? $current : null;
}

/** Reads one value out of a nested config by its path, or a fallback. */
function valueAtPath(array $config, array $path, mixed $fallback): mixed
{
    $cursor = $config;
    foreach ($path as $step) {
        if (!is_array($cursor) || !array_key_exists($step, $cursor)) {
            return $fallback;
        }
        $cursor = $cursor[$step];
    }
    return $cursor;
}

function startSetupSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Strict',
    ]);
    // Its own name: an unlock here must not be reachable from the dashboard's
    // session, nor the other way round.
    session_name('diskmonsetup');
    session_start();
}

/** A message left behind by a redirect, shown once. */
function takeSetupError(): ?string
{
    $message = $_SESSION['setup_error'] ?? null;
    unset($_SESSION['setup_error']);
    return is_string($message) ? $message : null;
}

/** A one-time success notice, deliberately without a key path or key material. */
function takeSetupNotice(): ?string
{
    $message = $_SESSION['setup_notice'] ?? null;
    unset($_SESSION['setup_notice']);
    return is_string($message) ? $message : null;
}

/** CSRF token limited to the key-creation action; the key is never returned. */
function setupKeyCreateToken(): string
{
    if (!is_string($_SESSION['setup_key_create_token'] ?? null)) {
        $_SESSION['setup_key_create_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['setup_key_create_token'];
}

/** CSRF token scoped to the action that changes the account crontab. */
function setupScheduleToken(): string
{
    if (!is_string($_SESSION['setup_schedule_token'] ?? null)) {
        $_SESSION['setup_schedule_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['setup_schedule_token'];
}

function setupTotpToken(): string
{
    if (!is_string($_SESSION['setup_totp_token'] ?? null)) $_SESSION['setup_totp_token'] = bin2hex(random_bytes(32));
    return $_SESSION['setup_totp_token'];
}

/**
 * Optional local QR rendering for a pending TOTP URI. No URI is sent over the
 * network; hosts without qrencode retain the manual-secret and app-link flow.
 */
function setupTotpQrImage(string $uri): ?string
{
    if ($uri === '' || strlen($uri) > 1024 || !function_exists('proc_open')) return null;
    foreach (['/usr/bin/qrencode', '/usr/local/bin/qrencode', '/opt/homebrew/bin/qrencode'] as $binary) {
        if (!is_file($binary) || !is_executable($binary)) continue;
        $pipes = []; $process = @proc_open([$binary, '-t', 'SVG', '-o', '-'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($process)) continue;
        fwrite($pipes[0], $uri); fclose($pipes[0]);
        stream_set_blocking($pipes[1], false); stream_set_blocking($pipes[2], false);
        $svg = ''; $deadline = microtime(true) + 2; $aborted = false;
        do {
            $chunk = stream_get_contents($pipes[1]); stream_get_contents($pipes[2]);
            if ($chunk !== false) $svg .= $chunk;
            $status = proc_get_status($process);
            if (!$status['running']) break;
            if (strlen($svg) > 1048576 || microtime(true) >= $deadline) { $aborted = true; proc_terminate($process); break; }
            usleep(10000);
        } while (true);
        $exitCode = (int)($status['exitcode'] ?? -1);
        foreach ([1, 2] as $pipe) { stream_get_contents($pipes[$pipe]); fclose($pipes[$pipe]); }
        $closedCode = proc_close($process); $exit = $exitCode >= 0 ? $exitCode : $closedCode;
        if (!$aborted && $exit === 0 && strlen($svg) > 100 && strlen($svg) <= 1048576 && preg_match('/<svg\\b/i', $svg)) {
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }
    return null;
}

/** CSRF protection for generating a provider signing key without saving a form. */
function setupOpenIdKeyToken(): string
{
    if (!is_string($_SESSION['setup_openid_key_token'] ?? null)) $_SESSION['setup_openid_key_token'] = bin2hex(random_bytes(32));
    return $_SESSION['setup_openid_key_token'];
}

/** CSRF token for one-time OpenID client-secret management. */
function setupOpenIdClientToken(): string
{
    if (!is_string($_SESSION['setup_openid_client_token'] ?? null)) $_SESSION['setup_openid_client_token'] = bin2hex(random_bytes(32));
    return $_SESSION['setup_openid_client_token'];
}

/** CSRF token limited to schema-validated package-setting writes. */
function setupPackageSettingsToken(): string
{
    if (!is_string($_SESSION['setup_package_settings_token'] ?? null)) {
        $_SESSION['setup_package_settings_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['setup_package_settings_token'];
}

/** CSRF token for remote deployment profile and credential-reference changes. */
function setupDeploymentToken(): string
{
    if (!is_string($_SESSION['setup_deployment_token'] ?? null)) $_SESSION['setup_deployment_token'] = bin2hex(random_bytes(32));
    return $_SESSION['setup_deployment_token'];
}

/** CSRF token for the explicitly reviewed account collector merge workflow. */
function setupAccountMergeToken(): string
{
    if (!is_string($_SESSION['setup_account_merge_token'] ?? null)) $_SESSION['setup_account_merge_token'] = bin2hex(random_bytes(32));
    return $_SESSION['setup_account_merge_token'];
}

function setupAccountMergeUploadDirectory(array $config): string
{
    $database = trim((string)($config['database'] ?? ''));
    if ($database === '' || !str_starts_with($database, '/')) throw new RuntimeException('the live account database is unavailable');
    $directory = dirname($database) . '/merge-review-inputs';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('could not prepare private merge review storage');
    if (is_link($directory) || !is_writable($directory)) throw new RuntimeException('private merge review storage is unavailable');
    @chmod($directory, 0700);
    $deadline = time() - 3600;
    foreach (glob($directory . '/review-*.db') ?: [] as $file) {
        if (!is_link($file) && is_file($file) && (int)@filemtime($file) < $deadline) @unlink($file);
    }
    return $directory;
}

/** Opens the already-configured local registry without creating or migrating it. */
function setupPackageRegistry(array $config): ?PDO
{
    $database = (string)($config['database'] ?? '');
    if ($database === '' || !str_starts_with($database, '/') || !is_file($database)) return null;
    try {
        return new PDO('sqlite:' . $database, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (Throwable) {
        return null;
    }
}

/** Inert, signed package metadata that is allowed into Setup navigation. */
function setupPackagePanels(?array $config): array
{
    if ($config === null || ($registry = setupPackageRegistry($config)) === null) return [];
    try {
        $out = [];
        foreach (packageRuntimePackages($registry) as $package) {
            if (!$package['enabled']) continue;
            foreach ((array)$package['setup_panels'] as $panel) {
                $out[] = (array)$panel + ['kind' => 'package', 'feature' => (string)$package['id']];
            }
        }
        return $out;
    } catch (Throwable) {
        return [];
    }
}

function setupPackageSettings(?array $config, string $packageId): array
{
    if ($config === null || ($registry = setupPackageRegistry($config)) === null) {
        throw new RuntimeException('the package registry is unavailable');
    }
    return packageRuntimeSettings($registry, $config, $packageId);
}

/** @return array{names:list<string>,references:list<array{reference:string,created_at:int,updated_at:int,referenced:bool}>} */
function setupPackageSecretPicker(?array $config, string $packageId): array
{
    if ($config === null || ($registry = setupPackageRegistry($config)) === null) {
        throw new RuntimeException('the package registry is unavailable');
    }
    return ['names' => packageRuntimeSecretSettingNames($registry, $packageId),
        'references' => packageRuntimeSecrets($registry, $config, $packageId)];
}

/** Core-owned renderer for both standalone and parented signed package panels. */
function setupRenderPackageSettingsPanel(array $panel, ?array $config): string
{
    $packageId = (string)($panel['feature'] ?? ''); $panelId = (string)($panel['id'] ?? '');
    if ($packageId === '' || $panelId === '') throw new RuntimeException('package panel metadata is unavailable');
    $settings = setupPackageSettings($config, $packageId);
    $encoded = json_encode(setupDisplayValue($settings), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $fieldName = 'package_settings_' . $panelId;
    $titleKey = trim((string)($panel['title_key'] ?? ''));
    $out = '<fieldset><legend' . ($titleKey !== '' ? ' data-i18n="' . h($titleKey) . '"' : '') . '>' . h((string)($panel['title'] ?? 'Package settings')) . '</legend><p class="help">These settings are validated against the signed package schema. Setup does not load package code to render this panel.</p><label>Settings (JSON)'
        . '<textarea name="' . h($fieldName) . '" rows="16" spellcheck="false">' . h($encoded) . '</textarea></label>'
        . '<input type="hidden" name="setup_package_settings_token" value="' . h(setupPackageSettingsToken()) . '">'
        . '<button type="submit" name="intent" value="save-package-settings:' . h($panelId) . '">Save package settings</button></fieldset>';
    $picker = setupPackageSecretPicker($config, $packageId);
    if ($picker['names'] === []) return $out;
    $out .= '<fieldset><legend>Protected secret references</legend><p class="help">Select a reference already stored for this package. The secret value is never shown or copied into this page. Create a new secret from the dashboard’s Packages area first if this list is empty.</p>';
    foreach ($picker['names'] as $name) {
        $selected = (string)($settings[$name] ?? '');
        $out .= '<label>' . h($name) . '<select data-package-secret-picker="' . h($name) . '" data-package-settings-target="' . h($fieldName) . '"><option value="">No reference selected</option>';
        foreach ($picker['references'] as $secret) {
            $reference = (string)$secret['reference'];
            $out .= '<option value="' . h($reference) . '"' . ($reference === $selected ? ' selected' : '') . '>'
                . h($reference) . ($secret['referenced'] ? ' (currently used)' : '') . '</option>';
        }
        $out .= '</select></label>';
    }
    return $out . '</fieldset>';
}

function setupSavePackageSettings(?array $config, string $packageId, array $settings): void
{
    if ($config === null || ($registry = setupPackageRegistry($config)) === null) {
        throw new RuntimeException('the package registry is unavailable');
    }
    packageRuntimeSaveSettings($registry, $config, $packageId, $settings, 'setup administrator');
    // Package settings themselves live in protected setup.db. The fact that
    // they changed belongs in the account action trail, without copying any
    // setting values or secret references into it.
    logAction($registry, 'package_runtime_settings_save', $packageId, 'ok', 0, 0,
        'saved from setup.php using signed package schema');
}

/** The stable persisted shape consumed by SetupCronApi and future panels. */
function setupScheduleChoices(array $source): array
{
    $profiles = cronEditorProfiles();
    $choices = [];
    foreach (array_keys(cronJobs()) as $job) {
        $raw = (array)($source[$job] ?? []);
        $profile = (string)($raw['profile'] ?? 'none');
        if (!isset(cronEditorProfiles($job)[$profile])) $profile = 'none';
        $custom = [];
        $rawCustom = (array)($raw['custom'] ?? []);
        foreach (['minute', 'hour', 'day', 'month', 'weekday'] as $field) {
            $custom[$field] = (string)($rawCustom[$field] ?? '*');
        }
        $rawOffset = (array)($raw['offset'] ?? []);
        $choices[$job] = ['profile' => $profile, 'custom' => $custom,
            'offset' => ['minute' => max(0, min(59, (int)($rawOffset['minute'] ?? 0))), 'hour' => max(0, min(23, (int)($rawOffset['hour'] ?? 0)))]];
    }
    return $choices;
}

/** Pulls schedule choices from the explicit form names, not arbitrary config input. */
function setupScheduleRequest(array $source): array
{
    $choices = [];
    foreach (array_keys(cronJobs()) as $job) {
        $custom = [];
        foreach (['minute', 'hour', 'day', 'month', 'weekday'] as $field) {
            $custom[$field] = (string)($source['schedule_' . $job . '_' . $field] ?? '*');
        }
        $choices[$job] = ['profile' => (string)($source['schedule_' . $job . '_profile'] ?? 'none'), 'custom' => $custom,
            'offset' => ['minute' => (int)($source['schedule_' . $job . '_offset_minute'] ?? 0), 'hour' => (int)($source['schedule_' . $job . '_offset_hour'] ?? 0)]];
    }
    return setupScheduleChoices($choices);
}

/**
 * A practical private-key location for the account running this setup backend.
 *
 * The key must stay outside the document root.  Prefer the account record over
 * HOME because web-service environments do not always retain that variable.
 */
function setupSuggestedPrivateKeyPath(): string
{
    $home = '';
    if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
        $account = @posix_getpwuid(posix_geteuid());
        $home = is_array($account) ? (string)($account['dir'] ?? '') : '';
    }
    if ($home === '') {
        $environmentHome = getenv('HOME');
        $home = is_string($environmentHome) ? $environmentHome : '';
    }

    $home = rtrim($home, '/');
    return $home !== '' && str_starts_with($home, '/')
        ? $home . '/.private/zogograph.key'
        : '/home/your-account/.private/zogograph.key';
}

/** Package credentials can use a separate key without needing another directory. */
function setupSuggestedPackageSecretKeyPath(): string
{
    return dirname(setupSuggestedPrivateKeyPath()) . '/zogograph-package-secrets.key';
}

/** An empty quick-start field deliberately means “use the displayed default”. */
function setupRequestedPrivateKeyPath(mixed $submittedPath): string
{
    $path = trim((string)$submittedPath);
    return $path === '' ? setupSuggestedPrivateKeyPath() : $path;
}

/** A link counts as occupied too: key creation must never follow or replace it. */
function setupPrivateKeyExists(string $path): bool
{
    return is_file($path) || is_link($path);
}

/** Groups the package-key setting with the action that provisions its value. */
function packageSecretKeyQuickStart(string $field, string $path, bool $exists, bool $canCreate): string
{
    $out = '<fieldset><legend>Package secrets key</legend>' . $field
        . '<p class="help">Create a separate key for optional separate custody of package credentials. It will be created in the '
        . 'same hidden private directory as the primary encryption key, with owner-only permissions and '
        . 'an Apache deny policy.</p><p class="help">Suggested location: <code>' . h($path) . '</code></p>'
        . '<input type="hidden" name="setup_key_create_token" value="' . h(setupKeyCreateToken()) . '">';
    if ($exists) {
        return $out . '<p class="banner ok" role="status">Package-secrets key already exists at this location; '
            . 'this step is complete.</p><button type="submit" disabled aria-disabled="true">Create package-secrets key file</button></fieldset>';
    }
    if (!$canCreate) {
        return $out . '<p class="help">Unlock setup with the current password before creating this key.</p>'
            . '<button type="submit" disabled aria-disabled="true">Create package-secrets key file</button></fieldset>';
    }
    return $out . '<button type="submit" name="intent" value="create-package-secret-key">Create package-secrets key file</button></fieldset>';
}

/** Inline provisioning for the encryption setting, never on the bootstrap page. */
function primaryKeyQuickStart(string $path, bool $exists, bool $canCreate): string
{
    $out = '<fieldset><legend>Create private encryption key</legend>'
        . '<p class="help">Creates a Base64 256-bit key outside the web directory, with owner-only '
        . 'permissions and an Apache deny policy for its private directory.</p>'
        . '<label>New private key-file path (leave blank to use this location)<input type="text" name="key_path" '
        . 'placeholder="' . h($path) . '" autocomplete="off"' . ($exists ? ' disabled' : '') . '></label>'
        . '<input type="hidden" name="setup_key_create_token" value="' . h(setupKeyCreateToken()) . '">';
    if ($exists) {
        return $out . '<p class="banner ok" role="status">Private key already exists at this location; '
            . 'this step is complete.</p><button type="submit" disabled aria-disabled="true">Create private key file</button></fieldset>';
    }
    if (!$canCreate) {
        return $out . '<p class="help">Unlock setup with the current password before creating this key.</p>'
            . '<button type="submit" disabled aria-disabled="true">Create private key file</button></fieldset>';
    }
    return $out . '<button type="submit" name="intent" value="create-local-key">Create private key file</button></fieldset>';
}

/* ------------------------------------------------------------------- web -- */

function runWeb(
    array $sample,
    array $fields,
    string $root,
    string $targetPath,
    ?array $existing
): void {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    // A page with a password field and a generated secret on it has no business
    // in anybody's frame.
    header('X-Frame-Options: DENY');

    $posted = $_SERVER['REQUEST_METHOD'] === 'POST';
    $configured = $existing !== null && is_file(setupStorePath(require $targetPath));
    $activeSection = $configured
        ? setupSection($fields, (string)($_GET['section'] ?? $_POST['section'] ?? 'basics'))
        : 'basics';

    /*
     * Unlocking, and why it needs a session at all.
     *
     * Without one, a reconfigure starts from the sample's defaults rather than
     * from what the system is actually set to - so anybody adjusting one
     * threshold would silently reset the scan root, the timezone and the alert
     * address back to whatever the sample says. Showing the current values
     * means proving the password first, and proving it once rather than on
     * every keystroke means remembering it, which is a session.
     *
     * It is deliberately short and deliberately its own cookie: nothing the
     * dashboard's own session can do should reach the installer, and nothing
     * granted here should outlive the sitting.
     */
    startSetupSession();
    $unlocked = $configured
        && !empty($_SESSION['setup_unlocked'])
        && (time() - (int)$_SESSION['setup_unlocked']) < SETUP_UNLOCK_SECONDS;

    if (($_GET['lock'] ?? '') !== '') {
        unset($_SESSION['setup_unlocked']);
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?'), true, 303);
        return;
    }

    if ($posted && ($_POST['intent'] ?? '') === 'unlock') {
        if (mayOverwrite($existing, (string)($_POST['current_password'] ?? ''))) {
            $_SESSION['setup_unlocked'] = time();
            // Redirected rather than rendered, so a refresh does not re-post a
            // password and the URL people end up bookmarking is a plain GET.
            header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?')
                . '?section=' . rawurlencode($activeSection), true, 303);
            return;
        }
        $_SESSION['setup_error'] = $existing['password_hash'] === ''
            ? 'This install has no password set, so nothing can authorise replacing its config.'
            : 'That is not the current password.';
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?'), true, 303);
        return;
    }

    if ($posted && ($_POST['intent'] ?? '') === 'generate-openid-signing-key') {
        if ($configured && !$unlocked) {
            $_SESSION['setup_error'] = 'Unlock setup before generating an OpenID signing key.';
        } elseif (!hash_equals(setupOpenIdKeyToken(), (string)($_POST['setup_openid_key_token'] ?? ''))) {
            $_SESSION['setup_error'] = 'The OpenID key form expired. Reload setup and try again.';
        } else {
            try {
                $_SESSION['setup_pending_openid_signing_key'] = openIdGenerateSigningKey();
                $_SESSION['setup_notice'] = 'A dedicated OpenID signing key was generated. Review the OpenID settings and save this section to commit it.';
            } catch (Throwable $error) {
                $_SESSION['setup_error'] = 'Could not generate the OpenID signing key: ' . $error->getMessage();
            }
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=openid', true, 303);
        return;
    }

    if ($posted && in_array($_POST['intent'] ?? '', ['create-openid-client', 'remove-openid-client', 'ack-openid-client-secret'], true)) {
        try {
            if (!$configured || !$unlocked) throw new RuntimeException('unlock setup before managing trusted OpenID clients');
            if (!hash_equals(setupOpenIdClientToken(), (string)($_POST['setup_openid_client_token'] ?? ''))) throw new RuntimeException('the OpenID client form expired; reload Setup and try again');
            $config = readExistingConfig($targetPath);
            if ($config === null) throw new RuntimeException('could not read the current setup settings');
            $intent = (string)$_POST['intent'];
            if ($intent === 'create-openid-client') {
                $client = openIdCreateClient(trim((string)($_POST['openid_client_id'] ?? '')), trim((string)($_POST['openid_redirect_uri'] ?? '')));
                if (isset(openIdClientDefinitions($config)[$client['client_id']])) throw new RuntimeException('a trusted OpenID client already uses that client ID');
                $config['openid']['clients'][$client['name']] = ['client_id' => $client['client_id'], 'client_secret' => $client['client_secret'], 'redirect_uris' => $client['redirect_uris']];
                [$saved, $problem] = writeConfig($targetPath, $config, $root . '/lib'); if (!$saved) throw new RuntimeException((string)$problem);
                $_SESSION['setup_openid_client_secret'] = ['client_id' => $client['client_id'], 'secret' => $client['client_secret']];
                $_SESSION['setup_notice'] = 'Trusted OpenID client saved. Copy its secret now; it will not be shown again.';
            } elseif ($intent === 'remove-openid-client') {
                $id = (string)($_POST['remove_openid_client_id'] ?? ''); $client = openIdClientDefinitions($config)[$id] ?? null;
                if ($client === null) throw new RuntimeException('that trusted OpenID client is unavailable');
                unset($config['openid']['clients'][$client['name']]);
                [$saved, $problem] = writeConfig($targetPath, $config, $root . '/lib'); if (!$saved) throw new RuntimeException((string)$problem);
                $_SESSION['setup_notice'] = 'Trusted OpenID client removed. Its secret and existing sessions are no longer accepted for new token exchanges.';
            } else {
                unset($_SESSION['setup_openid_client_secret']); $_SESSION['setup_notice'] = 'OpenID client secret acknowledgement saved.';
            }
        } catch (Throwable $error) { $_SESSION['setup_error'] = 'Could not manage trusted OpenID clients: ' . $error->getMessage(); }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=openid', true, 303);
        return;
    }

    if ($posted && in_array($_POST['intent'] ?? '', ['create-local-key', 'create-package-secret-key'], true)) {
        $creatingPackageSecretKey = ($_POST['intent'] ?? '') === 'create-package-secret-key';
        $canCreate = $existing === null || $unlocked
            || mayOverwrite($existing, (string)($_POST['current_password'] ?? ''));
        if (!hash_equals(setupKeyCreateToken(), (string)($_POST['setup_key_create_token'] ?? ''))) {
            $_SESSION['setup_error'] = 'The key-creation form expired. Reload setup and try again.';
        } elseif (!$canCreate) {
            $_SESSION['setup_error'] = 'Enter the current setup password before creating a new local key file.';
        } else {
            try {
                $path = $creatingPackageSecretKey
                    ? setupSuggestedPackageSecretKeyPath()
                    : setupRequestedPrivateKeyPath($_POST['key_path'] ?? '');
                encryptionCreateLocalKey($path, $root);
                $_SESSION['setup_notice'] = $creatingPackageSecretKey
                    ? 'A separate private package-secrets key file was created. Its contents were not shown or saved in the browser. Enter its path in Package secrets key file and save that setting before storing package secrets.'
                    : 'A new private local key file was created. Its contents were not shown or saved in the browser. Record an offline recovery copy before enabling encrypted copies.';
            } catch (Throwable $error) {
                $_SESSION['setup_error'] = 'Could not create the local key file: ' . $error->getMessage();
            }
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=basics', true, 303);
        return;
    }

    if ($posted && ($_POST['intent'] ?? '') === 'apply-schedule') {
        if (!$configured || !$unlocked) {
            $_SESSION['setup_error'] = 'Unlock setup before changing the account schedule.';
        } elseif (!hash_equals(setupScheduleToken(), (string)($_POST['setup_schedule_token'] ?? ''))) {
            $_SESSION['setup_error'] = 'The schedule form expired. Reload setup and try again.';
        } else {
            try {
                $config = readExistingConfig($targetPath);
                if ($config === null) throw new RuntimeException('could not read the current setup settings');
                $existingChoices = setupScheduleChoices((array)($config['schedule'] ?? []));
                $requestedChoices = setupScheduleRequest($_POST);
                [$job, $operation] = array_pad(explode(':', (string)($_POST['schedule_action'] ?? ''), 2), 2, '');
                if (!isset(cronJobs()[$job])) throw new RuntimeException('choose the collector job to change');
                $choices = $existingChoices;
                $choices[$job] = $requestedChoices[$job];
                if ($operation === 'remove') $choices[$job]['profile'] = 'none';
                if (!in_array($operation, ['add', 'update', 'remove'], true)) throw new RuntimeException('unknown schedule action');
                $php = (string)($_POST['schedule_php'] ?? '');
                $api = new SetupCronApi($root);
                // Persist the intended schedule before applying it. If the host refuses
                // the crontab, the page can accurately report the saved intent as out
                // of sync instead of pretending no change was requested.
                $config['schedule'] = ['php' => $php, ...$choices];
                [$saved, $problem] = writeConfig($targetPath, $config, $root . '/lib');
                if (!$saved) throw new RuntimeException((string)$problem);
                $api->applyJob($job, $choices[$job], $php);
                $_SESSION['setup_notice'] = ucfirst($job) . ' schedule ' . ($operation === 'remove' ? 'removed.' : 'saved and applied.');
            } catch (Throwable $error) {
                $_SESSION['setup_error'] = 'Could not apply the schedule: ' . $error->getMessage();
            }
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=schedule', true, 303);
        return;
    }

    /*
     * Where the form starts from.
     *
     * The system's own settings once unlocked, the sample's defaults otherwise.
     * The secrets are never among them: the password box is always empty, and
     * the mask secret is not a form field at all.
     */
    $values = [];
    $current = ($unlocked || !$configured) ? readExistingConfig($targetPath) : null;
    $packagePanels = $unlocked ? setupPackagePanels($current) : [];
    $activeSection = $configured
        ? setupSection($fields, (string)($_GET['section'] ?? $_POST['section'] ?? 'basics'), $packagePanels)
        : 'basics';
    foreach ($fields as $field) {
        $values[$field['key']] = $current !== null
            ? valueAtPath($current, $field['path'], $field['default'])
            : $field['default'];
    }
    if (is_string($_SESSION['setup_pending_openid_signing_key'] ?? null)) {
        $values['openid_signing_private_key'] = $_SESSION['setup_pending_openid_signing_key'];
    }

    if ($posted && in_array($_POST['intent'] ?? '', ['store-deployment-secret', 'save-deployment-profile', 'test-deployment-target', 'copy-deployment-release'], true)) {
        try {
            if (!$configured || !$unlocked || $current === null) throw new RuntimeException('unlock setup before changing remote deployment');
            if (!hash_equals(setupDeploymentToken(), (string)($_POST['setup_deployment_token'] ?? ''))) throw new RuntimeException('the deployment form expired; reload Setup and try again');
            if (($_POST['intent'] ?? '') === 'store-deployment-secret') {
                $reference = remoteDeploymentSecretStore($current, (string)($_POST['deployment_secret'] ?? ''), 'setup administrator');
                $_SESSION['setup_deployment_last_reference'] = $reference;
                $_SESSION['setup_notice'] = 'Deployment credential stored with encryption. Its value was not retained in this page.';
            } elseif (($_POST['intent'] ?? '') === 'test-deployment-target') {
                $profile = remoteDeploymentProfile($_POST);
                if (!remoteDeploymentSecretExists($current, $profile['credential_reference'])) throw new RuntimeException('the selected deployment credential reference is unavailable');
                if ($profile['known_host_reference'] !== null && !remoteDeploymentSecretExists($current, $profile['known_host_reference'])) throw new RuntimeException('the selected SSH host-key reference is unavailable');
                $result = remoteDeploymentTest($current, $profile);
                $_SESSION['setup_deployment_test'] = $result;
                $_SESSION['setup_notice'] = $result['ok'] ? 'Remote deployment target verified. You may now review and copy a build.' : 'Remote deployment target was not verified: ' . $result['detail'];
            } elseif (($_POST['intent'] ?? '') === 'copy-deployment-release') {
                $profile = remoteDeploymentProfile($_POST);
                if (!remoteDeploymentSecretExists($current, $profile['credential_reference'])) throw new RuntimeException('the selected deployment credential reference is unavailable');
                if ($profile['known_host_reference'] !== null && !remoteDeploymentSecretExists($current, $profile['known_host_reference'])) throw new RuntimeException('the selected SSH host-key reference is unavailable');
                $release = $_FILES['deployment_release'] ?? null;
                if (!is_array($release) || ($release['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($release['tmp_name'] ?? null) || !is_uploaded_file($release['tmp_name'])) throw new RuntimeException('choose the ZIP release archive to copy');
                $result = remoteDeploymentCopyRelease($current, $profile, $release['tmp_name']);
                $_SESSION['setup_deployment_copy'] = $result;
                $_SESSION['setup_notice'] = $result['ok'] ? 'Remote deployment release copied after a fresh target verification.' : 'Remote deployment release was not copied: ' . $result['detail'];
            } else {
                $profile = remoteDeploymentProfile($_POST);
                if (!remoteDeploymentSecretExists($current, $profile['credential_reference'])) throw new RuntimeException('the selected deployment credential reference is unavailable');
                if ($profile['known_host_reference'] !== null && !remoteDeploymentSecretExists($current, $profile['known_host_reference'])) throw new RuntimeException('the selected SSH host-key reference is unavailable');
                $current['remote_deployment'] = $profile;
                [$saved, $problem] = writeConfig($targetPath, $current, $root . '/lib');
                if (!$saved) throw new RuntimeException((string)$problem);
                $_SESSION['setup_notice'] = 'Remote deployment profile saved. Test the target before copying any files.';
            }
        } catch (Throwable $error) {
            $_SESSION['setup_error'] = 'Could not save remote deployment settings: ' . $error->getMessage();
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=deployment', true, 303);
        return;
    }

    if ($posted && in_array($_POST['intent'] ?? '', ['review-account-merge', 'apply-account-merge'], true)) {
        try {
            if (!$configured || !$unlocked || $current === null) throw new RuntimeException('unlock setup before reviewing an account merge');
            if (!hash_equals(setupAccountMergeToken(), (string)($_POST['setup_account_merge_token'] ?? ''))) throw new RuntimeException('the account merge form expired; reload Setup and try again');
            if (($_POST['intent'] ?? '') === 'review-account-merge') {
                $upload = $_FILES['account_merge_source'] ?? null;
                if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null) || !is_uploaded_file($upload['tmp_name'])) throw new RuntimeException('choose an exported account SQLite file to review');
                $bytes = filesize($upload['tmp_name']);
                if (!is_int($bytes) || $bytes < 1 || $bytes > 1073741824) throw new RuntimeException('the account database export must be between 1 byte and 1 GiB');
                $source = setupAccountMergeUploadDirectory($current) . '/review-' . bin2hex(random_bytes(16)) . '.db';
                if (!move_uploaded_file($upload['tmp_name'], $source) || !chmod($source, 0600)) { @unlink($source); throw new RuntimeException('could not safely retain the uploaded account export for review'); }
                $plan = accountMergePlan(db($current), $source);
                if (!$plan['eligible']) { @unlink($source); throw new RuntimeException((string)$plan['reason']); }
                $_SESSION['setup_account_merge'] = ['source' => $source, 'expires_at' => time() + 900];
                $_SESSION['setup_account_merge_plan'] = $plan;
                $_SESSION['setup_notice'] = 'Account collector merge reviewed. No data has changed; read the ranges and confirm before applying it.';
            } else {
                $review = $_SESSION['setup_account_merge'] ?? null;
                $source = is_array($review) ? (string)($review['source'] ?? '') : '';
                if ($source === '' || (int)($review['expires_at'] ?? 0) < time() || !is_file($source) || is_link($source)) {
                    if ($source !== '' && is_file($source)) @unlink($source);
                    unset($_SESSION['setup_account_merge'], $_SESSION['setup_account_merge_plan']);
                    throw new RuntimeException('the reviewed account export is no longer available; upload it again for a fresh review');
                }
                if (($_POST['writers_stopped'] ?? '') !== '1') throw new RuntimeException('confirm that all dashboard and collector writers are stopped before applying a merge');
                if (($_POST['allow_overlap'] ?? '') !== '1') throw new RuntimeException('confirm that overlapping collector times may be deduplicated before applying a merge');
                $result = accountMergeApply(db($current), $source, true);
                @unlink($source); unset($_SESSION['setup_account_merge'], $_SESSION['setup_account_merge_plan']);
                $_SESSION['setup_account_merge_result'] = $result;
                $_SESSION['setup_notice'] = 'Account collector data merged. A private rollback snapshot was created before the transaction.';
            }
        } catch (Throwable $error) {
            $_SESSION['setup_error'] = 'Could not complete the account merge: ' . $error->getMessage();
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=maintenance', true, 303);
        return;
    }

    if ($posted && str_starts_with((string)($_POST['intent'] ?? ''), 'save-package-settings')) {
        try {
            if (!$unlocked) throw new RuntimeException('unlock setup before changing package settings');
            if (!hash_equals(setupPackageSettingsToken(), (string)($_POST['setup_package_settings_token'] ?? ''))) {
                throw new RuntimeException('the package settings form expired; reload Setup and try again');
            }
            $intent = (string)$_POST['intent']; $panelId = str_starts_with($intent, 'save-package-settings:') ? substr($intent, strlen('save-package-settings:')) : (string)($_POST['package_panel_id'] ?? '');
            $panels = setupPanels($fields, $packagePanels);
            $panel = $panels[$panelId] ?? null;
            if (!is_array($panel) || $panel['kind'] !== 'package') throw new RuntimeException('that package settings panel is unavailable');
            $settings = json_decode((string)($_POST['package_settings_' . $panelId] ?? $_POST['package_settings'] ?? ''), true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($settings) || array_is_list($settings)) throw new RuntimeException('package settings must be a JSON object');
            setupSavePackageSettings($current, (string)$panel['feature'], $settings);
            $_SESSION['setup_notice'] = 'Package settings were saved using the package’s signed schema.';
        } catch (JsonException) {
            $_SESSION['setup_error'] = 'Package settings must be valid JSON.';
        } catch (Throwable $error) {
            $_SESSION['setup_error'] = 'Could not save package settings: ' . $error->getMessage();
        }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=' . rawurlencode((string)($_POST['package_panel_id'] ?? '')), true, 303);
        return;
    }

    if ($posted && in_array($_POST['intent'] ?? '', ['start-totp', 'confirm-totp', 'ack-totp-recovery'], true)) {
        try {
            if (!$configured || !$unlocked) throw new RuntimeException('unlock setup before changing authentication');
            if (!hash_equals(setupTotpToken(), (string)($_POST['setup_totp_token'] ?? ''))) throw new RuntimeException('the authenticator form expired; reload Setup and try again');
            $intent = (string)$_POST['intent'];
            if ($intent === 'start-totp') {
                $_SESSION['setup_pending_totp'] = totpGenerateSecret();
                unset($_SESSION['setup_recovery_codes']);
            } elseif ($intent === 'confirm-totp') {
                $secret = (string)($_SESSION['setup_pending_totp'] ?? '');
                if ($secret === '' || !totpValid($secret, (string)($_POST['totp_code'] ?? ''))) throw new RuntimeException('the authenticator code was not accepted');
                $config = readExistingConfig($targetPath);
                if ($config === null) throw new RuntimeException('could not read the current setup settings');
                $config['auth']['totp_secret'] = $secret;
                [$saved, $problem] = writeConfig($targetPath, $config, $root . '/lib');
                if (!$saved) throw new RuntimeException((string)$problem);
                $_SESSION['setup_recovery_codes'] = totpRecoveryCodes(db($config), 'administrator');
                unset($_SESSION['setup_pending_totp']);
            } else {
                if (empty($_POST['recovery_confirmed'])) throw new RuntimeException('confirm that you stored the recovery codes before continuing');
                unset($_SESSION['setup_recovery_codes']);
                $_SESSION['setup_notice'] = 'Authenticator and recovery codes are set up.';
            }
        } catch (Throwable $error) { $_SESSION['setup_error'] = 'Could not update authentication: ' . $error->getMessage(); }
        header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?') . '?section=auth', true, 303);
        return;
    }

    $errors = [];
    $refusal = null;
    $rendered = null;
    $written = false;
    $writeError = null;
    $authorised = !$configured;
    $rotate = false;

    if ($posted) {
        foreach ($fields as $field) {
            if (!fieldIsInSection($field, $activeSection)) {
                continue;
            }
            $key = $field['key'];
            if ($field['type'] === 'password') {
                continue;
            }
            $blank = $field['type'] === 'bool' || $field['type'] === 'categories' ? [] : '';
            [$parsed, $problem] = parseField($field, $_POST[$key] ?? $blank);
            $values[$key] = $parsed;
            if ($problem !== null && fieldApplies($field, $values)) {
                $errors[$key] = $problem;
            }
        }

        $supplied = (string)($_POST['current_password'] ?? '');
        $authorised = $unlocked || mayOverwrite($existing, $supplied);
        $rotate = !empty($_POST['rotate_mask']);

        /*
         * A wrong password is not a validation failure.
         *
         * It decides *whether the file is written*, and nothing else. Treated
         * as an error it would block the config from being generated at all,
         * which leaves somebody who mistyped with neither a written file nor
         * the text to paste - the two things this page is for.
         */
        if ($configured && $supplied !== '' && !$authorised) {
            $refusal = $existing['password_hash'] === ''
                ? 'This install has no password set, so nothing can authorise replacing its config.'
                : 'That is not the current password. Nothing was written; here is the text to paste.';
        }

        $password = (string)($_POST['password'] ?? '');
        $again = (string)($_POST['password_again'] ?? '');
        $hash = null;

        if ($password !== '' || $again !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = 'Use at least 8 characters.';
            } elseif ($password !== $again) {
                $errors['password'] = 'Those did not match.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
            }
        } elseif ($configured && $authorised) {
            // Blank means "leave it as it is" when there is something to leave.
            $hash = $existing['password_hash'];
        } elseif ($configured) {
            /*
             * Blank, configured, and not authorised.
             *
             * The old hash cannot be carried into text shown to somebody who
             * has not proved the password, and emitting an empty one would hand
             * them a config that silently disables sign-in the moment they
             * paste it. Asking for a password is the only honest option.
             */
            $errors['password'] = 'Choose a password. The current one cannot be carried over '
                . 'into text shown to somebody who has not entered it.';
        } else {
            $errors['password'] = 'Create a password before continuing.';
        }

        if (!$errors) {
            /*
             * Reusing the mask secret matters more than it looks.
             *
             * It is the key the signed-out pseudonyms are derived from, so a
             * new one renames every masked path at once. Kept unless asked to
             * rotate - and only available to reuse at all when the password
             * has been proved, since reusing it means printing it.
             */
            $mask = ($authorised && $configured && !$rotate && $existing['mask_secret'] !== '')
                ? $existing['mask_secret']
                : bin2hex(random_bytes(32));

            $config = buildConfig($sample, $values, (string)$hash, $mask, $samplePath);
            $rendered = renderConfig($config, $root . '/lib');

            if ($authorised) {
                [$written, $writeError] = writeConfig($targetPath, $config, $root . '/lib');
                if ($written) {
                    unset($_SESSION['setup_pending_openid_signing_key']);
                }
                if ($written && $configured) {
                    header('Location: ' . strtok((string)$_SERVER['REQUEST_URI'], '?')
                        . '?section=' . rawurlencode($activeSection) . '&saved=1', true, 303);
                    return;
                }
            }
        }
    }

    $refusal ??= takeSetupError();
    $notice = takeSetupNotice();

    echo webPage(
        $fields, $values, $errors, $rendered, $written, $writeError,
        $configured, $authorised, $posted, $existing, $refusal, $notice, $unlocked,
        $root . '/lib/config.sample.php', $activeSection, setupPreflight($root, $targetPath), $packagePanels, $current
    );
}

/** The settings pages, derived from the same field definitions as the CLI. */
function setupSection(array $fields, string $asked, array $registered = []): string
{
    if (in_array($asked, ['basics', 'privacy', 'schedule'], true)) return $asked;
    if (isset(setupPanels($fields, $registered)[$asked])) return $asked;
    foreach ($fields as $field) {
        if (($field['section'] ?? 'basics') === $asked) return $asked;
    }
    return 'basics';
}

function fieldIsInSection(array $field, string $section): bool
{
    return ($field['section'] ?? 'basics') === $section;
}

/**
 * Puts the config on disk.
 *
 * `x` mode for a first install, so the "does it exist" check and the create are
 * one operation rather than two with a gap between them. A replacement is
 * written through a temporary file and renamed, so a full disk or a killed
 * request cannot leave a half-written config behind - which on this particular
 * file would lock everybody out.
 *
 * @return array{0: bool, 1: ?string} written, and why not
 */
function writeConfig(string $targetPath, array $config, string $libDir): array
{
    try {
        encryptionValidateSettings($config, dirname($libDir));
        saveConfiguredSettings($config, $targetPath, $libDir);
        return [true, null];
    } catch (Throwable $error) {
        return [false, 'Could not save setup.db and the bootstrap config: ' . $error->getMessage()];
    }
}

function webPage(
    array $fields,
    array $values,
    array $errors,
    ?string $rendered,
    bool $written,
    ?string $writeError,
    bool $configured,
    bool $authorised,
    bool $posted,
    ?array $existing,
    ?string $refusal,
    ?string $notice,
    bool $unlocked,
    string $samplePath,
    string $activeSection,
    array $preflight,
    array $packagePanels,
    ?array $current
): string {
    $out = [];
    $out[] = '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    $out[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $out[] = '<title>Zogograph — setup</title>';
    $out[] = '<link rel="stylesheet" href="index.css">';
    $out[] = '<script src="i18n.js"></script>';
    $out[] = '<style>'
        . 'body{max-width:1180px;padding-top:0}'
        . '.setup-header{position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;background:var(--bg)}'
        . '.setup-brand{display:flex;align-items:center;gap:9px;color:var(--text);text-decoration:none}.setup-brand strong{font-size:1.08rem}.setup-brand small{color:var(--muted)}.setup-brand .poll{display:inline-block}.setup-brand .poll-fill,.setup-brand .poll-host-fill{stroke:var(--accent);stroke-dasharray:none;stroke-dashoffset:0}'
        . '.setup-header a:not(.setup-brand){color:var(--muted);font-size:.86rem}'
        . '.setup-controls{display:flex;align-items:center;gap:9px;font-size:.78rem;color:var(--muted)}.setup-controls label{display:flex;align-items:center;gap:4px;margin:0}.setup-controls select{max-width:105px}'
        . '.setup-layout{display:grid;grid-template-columns:210px minmax(0,1fr);gap:24px;align-items:start}'
        . '.setup-nav{position:sticky;top:16px;max-height:calc(100vh - 32px);overflow:auto;border:1px solid var(--line);border-radius:10px;padding:10px}'
        . '.setup-nav-search{display:block;margin:0 0 8px}.setup-nav-search input{width:100%;box-sizing:border-box}'
        . '.setup-nav a{display:block;padding:7px 8px;border-radius:6px;color:var(--muted);text-decoration:none;font-size:.86rem}'
        . '.setup-nav a:hover,.setup-nav a.current{background:var(--panel);color:var(--text)}'
        . '.setup-main{min-width:0}'
        . '.wiz label{display:grid;gap:6px;margin-bottom:16px}'
        . '.wiz input[type=text],.wiz input[type=password],.wiz input[type=email]{width:100%}'
        . '.wiz .help{color:var(--muted);font-size:.82rem}'
        . '.wiz .bad{color:#ffb0b0;font-size:.82rem}'
        . '.wiz fieldset{border:1px solid var(--line);border-radius:10px;padding:12px;margin:0 0 16px}'
        . '.wiz legend{color:var(--muted);font-size:.85rem;padding:0 6px}'
        . '.wiz .row{display:flex;gap:14px;flex-wrap:wrap}'
        . '.wiz .row label{display:flex;align-items:center;gap:6px;margin:0}'
        . 'textarea{width:100%;min-height:340px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;'
        . 'font-size:.8rem;background:#111;color:#dfe6d6;border:1px solid var(--line);'
        . 'border-radius:10px;padding:12px}'
        . '.banner{border-radius:12px;padding:12px 14px;margin-bottom:16px;border:1px solid}'
        . '.banner p:last-child{margin-bottom:0}'
        . '.banner.warn{border-color:#7a5a30;background:#2b2114}'
        . '.banner.ok{border-color:#2f6f3a;background:#152b1c}'
        . '.banner.crit{border-color:#7a3030;background:#2b1414}'
        . '.setup-update{display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px solid var(--accent);border-radius:10px;padding:10px 12px;margin:0 0 16px;background:var(--panel)}'
        . '.preflight{width:100%;border-collapse:collapse;margin:0 0 16px}.preflight th,.preflight td{padding:7px 8px;border-bottom:1px solid var(--line);text-align:left;font-size:.85rem}.preflight th{color:var(--muted);font-weight:500}.preflight .ok{color:#8be0a8}.preflight .warning{color:#f0c27b}.preflight .error{color:#ff9ca3}'
        . 'code{background:#111;padding:1px 5px;border-radius:4px}'
        . 'details{border:1px solid var(--line);border-radius:10px;margin:0 0 12px;padding:0 14px}'
        . 'details[open]{padding-bottom:8px}'
        . 'summary{cursor:pointer;padding:12px 0;color:var(--text);font-size:.92rem}'
        . 'summary strong{color:#ffb0b0;font-weight:600}'
        . '.wiz details label{margin-bottom:14px}'
        . 'pre{overflow-x:auto}'
        // Only the form fields. The rule above is for the big read-only box
        // that holds the config to copy, which has no name and wants its 340px.
        . 'textarea[name]{min-height:0;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;'
        . 'font-size:.85rem;background:#111;color:#eee;border:1px solid var(--line);'
        . 'border-radius:8px;padding:8px}'
        . '@media(max-width:700px){.setup-header{padding:11px 0}.setup-controls{flex-wrap:wrap}.setup-layout{display:block}.setup-nav{position:static;display:flex;max-height:none;overflow-x:auto;gap:3px;margin-bottom:16px;white-space:nowrap}.setup-nav-search{display:none}.setup-nav a{flex:0 0 auto}}'
        . '</style></head><body class="wiz">';

    $out[] = '<header class="setup-header"><a class="setup-brand" href="?section=basics">'
        . '<span class="poll" aria-hidden="true"><svg viewBox="0 0 36 36"><circle class="poll-track" cx="18" cy="18" r="15"></circle><circle class="poll-fill" cx="18" cy="18" r="15"></circle><circle class="poll-host-track" cx="18" cy="18" r="9"></circle><circle class="poll-host-fill" cx="18" cy="18" r="9"></circle></svg><span class="poll-dot"></span></span>'
        . '<strong>Zogograph</strong><small data-i18n="setup.label">Setup</small></a><span class="setup-controls">'
        . '<label><span data-i18n="language.label">Language</span><select id="languagePicker" aria-label="Language"><option value="en">English</option><option value="en-US">English (US)</option><option value="en-GB">English (UK)</option><option value="es">Español</option><option value="hi">हिन्दी</option><option value="zh">中文</option><option value="ar">العربية</option></select></label>'
        . '<label><input id="languageKeys" type="checkbox"><span data-i18n="language.keys">Show translation keys</span></label>'
        . ($unlocked ? '<a href="?lock=1" data-i18n="setup.lock">Lock setup</a> · ' : '')
        . '<a href="./" data-i18n="setup.dashboard">Open dashboard</a></span></header>';
    $out[] = '<h1 data-i18n="setup.title">Setup</h1>';
    $out[] = '<section id="setupUpdateBanner" class="setup-update" hidden aria-live="polite"></section>';

    if (($_GET['saved'] ?? '') === '1') {
        $out[] = '<div class="banner ok"><strong>Saved.</strong> This section is now stored in '
            . '<code>data/setup.db</code>.</div>';
    }

    if ($written) {
        $out[] = '<div class="banner ok"><strong>Settings saved in <code>data/setup.db</code>.</strong>'
            . '<p>Take the first readings, then open the dashboard:</p>'
            . '<pre><code>php cron.php --vitals' . "\n" . 'php cron.php --scan</code></pre>'
            . '<p>Keep <code>setup.php</code> available for later administrator changes. '
            . 'It requires the current password before it can replace your configuration.</p>'
            . '<p><a href="./">Open the dashboard</a></p></div>';

        if (!empty($values['host_enabled'])) {
            $out[] = '<div class="banner warn"><strong>One more step, as root.</strong>'
                . '<p>The host collector cannot be installed from here — it runs as root, and '
                . 'this page runs as your account. Log in to WHM or ssh as root and add this '
                . 'to <em>root\'s</em> crontab (<code>crontab -e</code>):</p>'
                . '<pre><code>' . h(hostCronLine()) . '</code></pre>'
                . '<p>Then run it once by hand to see that it works — without the '
                . 'schedule, and without <code>--quiet</code> so it tells you what it found:</p>'
                . '<pre><code>' . h(hostCronCommand()) . '</code></pre>'
                . '<p>Until it runs, the <em>whole machine</em> panel on the dashboard will say '
                . 'so rather than showing anything. The dashboard\'s own schedule editor cannot '
                . 'manage this job: it edits your account\'s crontab, and this one is root\'s.</p>'
                . '</div>';
        }
        $out[] = '</body></html>';
        return implode("\n", $out);
    }

    if (!$configured) {
        $out[] = '<div class="banner crit"><strong>Nothing is configured yet.</strong>'
            . '<p>Until you finish, anybody who can reach this page can set the password. '
            . 'Do it now; afterwards this remains the password-protected configuration backend.</p></div>';
    } elseif (!$unlocked && $existing['password_hash'] === '') {
        $out[] = '<div class="banner warn"><strong>Already configured, with no password.</strong>'
            . '<p>There is no secret to prove, so nothing can authorise replacing the config '
            . 'from here. Fill this in and paste the result into <code>lib/config.php</code> '
            . 'yourself — having a file to edit is the authorisation.</p></div>';
    } elseif (!$unlocked) {
        $out[] = '<div class="banner warn"><strong>Already configured.</strong>'
            . '<p>Enter the current password below and this will replace '
            . '<code>lib/config.php</code>. Without it you still get the finished text to '
            . 'copy and paste.</p></div>';
    }

    if ($refusal !== null) {
        $out[] = '<div class="banner crit">' . h($refusal) . '</div>';
    }
    if ($notice !== null) {
        $out[] = '<div class="banner ok">' . h($notice) . '</div>';
    }
    if ($writeError !== null) {
        $out[] = '<div class="banner crit">' . h($writeError) . '</div>';
    }
    if ($posted && $errors) {
        $out[] = '<div class="banner crit">Some answers need another look.</div>';
    }

    $panels = setupPanels($fields, $packagePanels);
    $out[] = '<div class="setup-layout"><aside class="setup-nav" aria-label="Setup sections" data-i18n-aria-label="setup.sections">'
        . '<label class="setup-nav-search"><span data-i18n="setup.search">Search settings</span><input type="search" data-setup-nav-search placeholder="Search settings" data-i18n-placeholder="setup.search" autocomplete="off"></label>'
        . '<a data-i18n="setup.installation" data-setup-nav-item data-setup-nav-terms="installation checks prerequisites"' . ($activeSection === 'basics' ? ' class="current"' : '') . ' href="?section=basics">Installation</a>';
    foreach ($panels as $name => $panel) {
        if ($name === 'basics') continue;
        $navigationKey = $panel['kind'] === 'core' ? 'setup.section.' . $name : (string)($panel['title_key'] ?? '');
        $out[] = '<a data-setup-nav-item data-setup-nav-terms="' . h($name . ' ' . $panel['title'] . ' ' . implode(' ', $panel['keywords'])) . '"'
            . ($navigationKey !== '' ? ' data-i18n="' . h($navigationKey) . '"' : '')
            . ($activeSection === $name ? ' class="current"' : '')
            . ' href="?section=' . rawurlencode($name) . '">' . h($panel['title']) . '</a>';
    }
    $out[] = '</aside><main class="setup-main"><form method="post" enctype="multipart/form-data">'
        . '<input type="hidden" name="section" value="' . h($activeSection) . '">';

    $editingLocked = $configured && $existing['password_hash'] !== '' && !$unlocked;
    if ($editingLocked) {
        // Its own form, so pressing enter in the password box unlocks rather
        // than submitting a whole config full of defaults.
        $out[] = '</form><form method="post"><input type="hidden" name="intent" value="unlock">'
            . '<input type="hidden" name="section" value="' . h($activeSection) . '">'
            . '<fieldset><legend>Authorise</legend>'
            . '<span class="help">The password this install already uses. Enter it to edit the '
            . 'current settings and replace the file.</span>'
            . '<label style="margin-top:10px">Current password'
            . '<input type="password" name="current_password" autocomplete="current-password"></label>'
            . '<button type="submit">Unlock</button>'
            . '</fieldset></form><form method="post">';
    }

    $activePanel = $panels[$activeSection] ?? null;
    $title = $activeSection === 'basics' ? 'Installation'
        : (is_array($activePanel) && $activePanel['kind'] === 'package' ? (string)$activePanel['title'] : sectionTitle($activeSection));
    $suggestedKeyPath = setupSuggestedPrivateKeyPath();
    $suggestedKeyExists = setupPrivateKeyExists($suggestedKeyPath);
    $suggestedPackageSecretKeyPath = setupSuggestedPackageSecretKeyPath();
    $suggestedPackageSecretKeyExists = setupPrivateKeyExists($suggestedPackageSecretKeyPath);
    $out[] = '<section class="setup-section"><h2>' . h($title) . '</h2>';
    if ($editingLocked) {
        $out[] = '<p class="help">Unlock setup to view or change this section.</p>';
    } elseif ($activeSection === 'basics') {
        $out[] = '<p class="help">Review these checks, then open Security to create the sign-in password. '
            . 'All other settings are available from the navigation when you need them.</p>';
    } elseif ($activeSection === 'privacy') {
        $out[] = '<fieldset><legend>Masked-path privacy</legend>'
            . '<p class="help">Signed-out visitors see stable anonymous labels instead of real filesystem '
            . 'paths. Rotating this secret changes every one of those labels, breaking any old label-to-path '
            . 'correlation. Leave it unchanged unless you suspect it was exposed.</p>'
            . '<table class="preflight"><thead><tr><th>What a signed-out visitor sees</th><th>Before rotation</th><th>After rotation</th></tr></thead><tbody>'
            . '<tr><th>Same private log file</th><td><code>.log file A1B2C3</code></td><td><code>.log file D4E5F6</code></td></tr>'
            . '<tr><th>Real filesystem path</th><td colspan="2">Never shown</td></tr>'
            . '</tbody></table>'
            . '<label class="row"><input type="checkbox" name="rotate_mask" value="1"> Generate a new mask secret</label>'
            . '</fieldset>';
    } elseif ($activeSection === 'openid') {
        $pendingOpenIdKey = is_string($_SESSION['setup_pending_openid_signing_key'] ?? null);
        $out[] = '<fieldset><legend data-i18n="openid.provider">OpenID Connect provider</legend><p class="help">This installation can already act as an OpenID Connect provider for trusted applications. Enable it only after setting its public HTTPS issuer, a dedicated signing key, and registered client redirect URLs. The provider supports authorization code with PKCE, discovery, JWKS, and signed ID tokens.</p><p class="help">Client registrations are managed below as structured settings; each client needs an exact redirect URL and a private client secret.</p><input type="hidden" name="setup_openid_key_token" value="' . h(setupOpenIdKeyToken()) . '"><button type="submit" name="intent" value="generate-openid-signing-key" data-i18n="openid.generate_key"' . ($editingLocked ? ' disabled aria-disabled="true"' : '') . '>Generate dedicated RSA signing key</button><p class="help">' . ($pendingOpenIdKey ? 'A new key is ready in this protected setup session. Save this section to commit it; it is not included in notices or logs.' : 'Generation creates a new 3072-bit RSA key in this protected setup session. It is not committed until you save this section.') . '</p></fieldset>';
        $clients = openIdClientDefinitions($current ?? []); $shownSecret = $_SESSION['setup_openid_client_secret'] ?? null;
        $out[] = '<fieldset><legend data-i18n="openid.trusted_apps">Trusted applications</legend><p class="help">Each application gets one exact HTTPS callback URL and a generated confidential-client secret. Existing secrets are never shown again; remove and recreate a client to rotate one.</p><input type="hidden" name="setup_openid_client_token" value="' . h(setupOpenIdClientToken()) . '">';
        if (is_array($shownSecret) && is_string($shownSecret['client_id'] ?? null) && is_string($shownSecret['secret'] ?? null)) {
            $out[] = '<div class="banner warn"><strong>Copy this client secret now.</strong><p>Client ID: <code>' . h($shownSecret['client_id']) . '</code></p><pre><code>' . h($shownSecret['secret']) . '</code></pre><p>It will not be shown again after acknowledgement.</p><button type="submit" name="intent" value="ack-openid-client-secret">I saved the client secret</button></div>';
        }
        if ($clients !== []) {
            $out[] = '<table class="preflight"><thead><tr><th>Client ID</th><th>Exact redirect URL</th><th></th></tr></thead><tbody>';
            foreach ($clients as $client) $out[] = '<tr><td><code>' . h($client['client_id']) . '</code></td><td><code>' . h(implode("\n", $client['redirect_uris'])) . '</code></td><td><button formnovalidate type="submit" name="intent" value="remove-openid-client" data-i18n="openid.remove">Remove<input type="hidden" name="remove_openid_client_id" value="' . h($client['client_id']) . '"></button></td></tr>';
            $out[] = '</tbody></table>';
        }
        $out[] = '<label><span data-i18n="openid.client_id">Client ID</span><input required name="openid_client_id" pattern="[A-Za-z][A-Za-z0-9._-]{2,127}" autocomplete="off" placeholder="trusted-app"></label><label><span data-i18n="openid.redirect_url">Exact HTTPS redirect URL</span><input required type="url" name="openid_redirect_uri" placeholder="https://app.example/callback"></label><button type="submit" name="intent" value="create-openid-client" data-i18n="openid.create_client"' . ($editingLocked ? ' disabled aria-disabled="true"' : '') . '>Create trusted client</button></fieldset>';
    } elseif ($activeSection === 'auth') {
        $pendingTotp = (string)($_SESSION['setup_pending_totp'] ?? '');
        $recoveryCodes = (array)($_SESSION['setup_recovery_codes'] ?? []);
        $out[] = '<fieldset><legend data-i18n="auth.app">Authenticator app</legend><p class="help" data-i18n="auth.app_intro">Use an authenticator app such as Google Authenticator, 1Password, or Authy for a six-digit second factor. The secret is not saved until the app proves it can create a correct code.</p>';
        if ($recoveryCodes !== []) {
            $out[] = '<div class="banner warn"><strong data-i18n="auth.recovery_title">Save these recovery codes now.</strong><p data-i18n="auth.recovery_help">They will not be shown again. Each code works once if your authenticator is unavailable.</p><pre><code>' . h(implode("\n", $recoveryCodes)) . '</code></pre><label class="row"><input type="checkbox" name="recovery_confirmed" value="1"> <span data-i18n="auth.recovery_confirm">I saved these recovery codes somewhere safe.</span></label><input type="hidden" name="setup_totp_token" value="' . h(setupTotpToken()) . '"><button type="submit" name="intent" value="ack-totp-recovery" data-i18n="auth.finish_recovery">Finish recovery-code setup</button></div>';
        } elseif ($pendingTotp !== '') {
            $issuer = rawurlencode('Zogograph'); $account = rawurlencode('administrator');
            $uri = 'otpauth://totp/' . $issuer . ':' . $account . '?secret=' . rawurlencode($pendingTotp) . '&issuer=' . $issuer . '&algorithm=SHA1&digits=6&period=30';
            $qr = setupTotpQrImage($uri);
            $out[] = '<p data-i18n="auth.enter_secret">Enter this secret into your authenticator app:</p><pre><code>' . h($pendingTotp) . '</code></pre>'
                . ($qr === null ? '' : '<p><img width="220" height="220" alt="Scan this QR code with your authenticator app" src="' . h($qr) . '"></p>')
                . '<p class="help">On a device with a compatible authenticator installed, <a href="' . h($uri) . '">open this account in the authenticator app</a>. Otherwise add the secret above manually.</p><p class="help">Provisioning URI: <code>' . h($uri) . '</code></p><label><span data-i18n="auth.current_code">Current six-digit code</span><input type="text" name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"></label><input type="hidden" name="setup_totp_token" value="' . h(setupTotpToken()) . '"><button type="submit" name="intent" value="confirm-totp" data-i18n="auth.verify">Verify and enable authenticator</button>';
        } else {
            $out[] = '<input type="hidden" name="setup_totp_token" value="' . h(setupTotpToken()) . '"><button type="submit" name="intent" value="start-totp" data-i18n="auth.start">Create authenticator secret</button>';
        }
        $out[] = '</fieldset>';
    } elseif ($activeSection === 'deployment') {
        $deployment = (array)(($current ?? [])['remote_deployment'] ?? []);
        $lastReference = is_string($_SESSION['setup_deployment_last_reference'] ?? null) ? $_SESSION['setup_deployment_last_reference'] : '';
        $out[] = '<fieldset><legend data-i18n="deployment.profile">Remote deployment profile</legend><p class="help">Save the target and update policy here. Credentials and SSH host keys are encrypted separately and the profile retains only opaque references. Plain FTP is not available; choose explicit TLS FTP where SSH is unavailable.</p>'
            . '<input type="hidden" name="setup_deployment_token" value="' . h(setupDeploymentToken()) . '">'
            . '<label>Transfer method<select name="method"><option value="rsync_ssh"' . (($deployment['method'] ?? 'rsync_ssh') === 'rsync_ssh' ? ' selected' : '') . '>rsync over SSH</option><option value="sftp"' . (($deployment['method'] ?? '') === 'sftp' ? ' selected' : '') . '>SFTP</option><option value="ftps"' . (($deployment['method'] ?? '') === 'ftps' ? ' selected' : '') . '>FTP with explicit TLS</option></select></label>'
            . '<label>Target hostname or IP<input required name="host" value="' . h((string)($deployment['host'] ?? '')) . '" autocomplete="off"></label><label>Port (leave blank for method default)<input name="port" inputmode="numeric" value="' . h((string)($deployment['port'] ?? '')) . '"></label><label>Deployment username<input required name="username" value="' . h((string)($deployment['username'] ?? '')) . '" autocomplete="username"></label><label>Remote target path<input required name="target_path" value="' . h((string)($deployment['target_path'] ?? '')) . '" placeholder="/srv/zogograph-remote"></label>'
            . '<label>Encrypted credential reference<input required name="credential_reference" value="' . h((string)($deployment['credential_reference'] ?? $lastReference)) . '" autocomplete="off"></label><label>SSH host-key reference (not used for FTPS)<input name="known_host_reference" value="' . h((string)($deployment['known_host_reference'] ?? '')) . '" autocomplete="off"></label><label>Core controller URL<input required type="url" name="controller_url" value="' . h((string)($deployment['controller_url'] ?? '')) . '" placeholder="https://core.example"></label><label>Remote update behavior<select name="update_mode"><option value="manual"' . (($deployment['update_mode'] ?? 'manual') === 'manual' ? ' selected' : '') . '>Manual install</option><option value="download"' . (($deployment['update_mode'] ?? '') === 'download' ? ' selected' : '') . '>Download, then wait for approval</option><option value="automatic"' . (($deployment['update_mode'] ?? '') === 'automatic' ? ' selected' : '') . '>Install automatically</option></select></label><label>Package groups (comma-separated)<input required name="packages[]" value="remote-core" readonly></label><button type="submit" name="intent" value="save-deployment-profile" data-i18n="deployment.save">Save deployment profile</button><button type="submit" name="intent" value="test-deployment-target" data-i18n="deployment.test">Test target and credentials</button></fieldset>';
        $out[] = '<fieldset><legend data-i18n="deployment.store">Store deployment credential or host key</legend><p class="help">Paste one password, private key, or known_hosts entry. It is encrypted with the configured local key and shown only as an opaque reference after storage.</p><label>Credential or host-key material<textarea name="deployment_secret" rows="5" autocomplete="off"></textarea></label><button type="submit" name="intent" value="store-deployment-secret" data-i18n="deployment.store_action">Store encrypted credential</button>'
            . ($lastReference !== '' ? '<p class="banner ok">New encrypted reference: <code>' . h($lastReference) . '</code></p>' : '') . '</fieldset>';
        $out[] = '<fieldset><legend>Copy a tested release</legend><p class="help">Upload the ZIP made from the selected remote-core release. Copying always repeats the target and credential check first. The ZIP is unpacked only in a private temporary directory, and its contents are removed after transfer.</p><label>Remote-core ZIP release<input required type="file" name="deployment_release" accept="application/zip,.zip"></label><button type="submit" name="intent" value="copy-deployment-release">Verify again and copy release</button></fieldset>';
        if ($deployment !== []) {
            try {
                $plan = remoteDeploymentCommandPlan($deployment);
                $out[] = '<details><summary>Review planned transport</summary><p class="help">This is a secret-free argument preview only. Braced values are supplied by the reviewed worker at test/copy time; saving or opening this preview makes no network connection.</p><pre><code>' . h(json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)) . '</code></pre></details>';
            } catch (Throwable) {
                $out[] = '<p class="help">Save a complete deployment profile to review its planned transport.</p>';
            }
        }
        $lastTest = $_SESSION['setup_deployment_test'] ?? null;
        if (is_array($lastTest) && isset($lastTest['ok'], $lastTest['detail'], $lastTest['tested_at'])) {
            $out[] = '<p class="banner ' . ($lastTest['ok'] ? 'ok' : 'warn') . '"><strong>Last target test:</strong> ' . h((string)$lastTest['detail']) . ' (' . h(gmdate('Y-m-d H:i:s', (int)$lastTest['tested_at'])) . ' UTC)</p>';
        }
        $lastCopy = $_SESSION['setup_deployment_copy'] ?? null;
        if (is_array($lastCopy) && isset($lastCopy['ok'], $lastCopy['detail'], $lastCopy['files'])) {
            $out[] = '<p class="banner ' . ($lastCopy['ok'] ? 'ok' : 'warn') . '"><strong>Last release copy:</strong> ' . h((string)$lastCopy['detail']) . ' (' . h((string)$lastCopy['files']) . ' files)</p>';
        }
    } elseif ($activeSection === 'schedule') {
        $schedule = scheduleState(__DIR__);
        $savedSchedule = (array)(($current ?? [])['schedule'] ?? []);
        $choices = setupScheduleChoices($savedSchedule);
        $binaries = (array)($schedule['binaries'] ?? []);
        $php = (string)($savedSchedule['php'] ?? '');
        if (!in_array($php, $binaries, true)) $php = (string)($binaries[0] ?? '');
        $out[] = '<p class="help">Choose the account collector cadence and apply it here. Only Zogograph’s marked cron block is changed; every other account cron job stays untouched.</p>';
        if (!$schedule['available']) {
            $out[] = '<div class="banner warn">' . h((string)($schedule['reason'] ?? 'The crontab cannot be read here.')) . '</div>';
        } elseif ($php === '') {
            $out[] = '<div class="banner warn">No usable command-line PHP binary was found, so this host cannot apply a schedule from Setup.</div>';
        } else {
            try {
                $status = (new SetupCronApi(__DIR__))->status($choices, $php);
                $sync = (string)$status['sync'];
                $copy = $sync === 'in_sync' ? 'Saved schedule is installed and in sync.'
                    : ($sync === 'missing' ? 'Saved schedule is not installed yet.' : 'Installed schedule differs from the saved settings.');
                $out[] = '<div class="banner ' . ($sync === 'in_sync' ? 'ok' : 'warn') . '"><strong>' . h($copy) . '</strong></div>';
            } catch (Throwable $error) {
                $out[] = '<div class="banner warn">Could not build the saved schedule: ' . h($error->getMessage()) . '</div>';
            }
        }
        $out[] = '<fieldset><legend>Account collector schedule</legend>'
            . '<p class="help">Vitals are lightweight readings. A scan walks the monitored directory and is normally left off or scheduled much less often.</p>'
            . '<label>PHP command-line binary<select name="schedule_php">';
        foreach ($binaries as $binary) {
            $out[] = '<option value="' . h($binary) . '"' . ($binary === $php ? ' selected' : '') . '>' . h($binary) . '</option>';
        }
        $out[] = '</select></label>';
        $desiredByJob = [];
        try { if ($php !== '') $desiredByJob = cronEditorBuildJobLines($choices, $php, __DIR__); } catch (Throwable) {}
        $installedByJob = cronEditorInstalledJobLines((array)($schedule['lines'] ?? []));
        foreach (['vitals' => 'Vitals collection', 'scan' => 'Directory scan'] as $job => $label) {
            $out[] = renderSetupCronEditor('schedule_' . $job, $label, $choices[$job], $job)
                . '<p><strong>Saved preview</strong></p><pre><code>' . h($desiredByJob[$job] ?? '(none)') . '</code></pre>'
                . '<p><strong>Currently installed</strong></p><pre><code>' . h($installedByJob[$job] ?? '(none)') . '</code></pre>'
                . '<input type="hidden" name="setup_schedule_token" value="' . h(setupScheduleToken()) . '">'
                . '<input type="hidden" name="intent" value="apply-schedule">'
                . '<button type="submit" name="schedule_action" value="' . h($job . ':add') . '"' . ($editingLocked || !$schedule['available'] || $php === '' ? ' disabled aria-disabled="true"' : '') . '>Add</button> '
                . '<button type="submit" name="schedule_action" value="' . h($job . ':update') . '"' . ($editingLocked || !$schedule['available'] || $php === '' ? ' disabled aria-disabled="true"' : '') . '>Update</button> '
                . '<button type="submit" name="schedule_action" value="' . h($job . ':remove') . '"' . ($editingLocked || !$schedule['available'] || $php === '' ? ' disabled aria-disabled="true"' : '') . '>Remove</button>';
        }
        $out[] = '</fieldset>';
    } elseif ($activeSection === 'maintenance') {
        $review = $_SESSION['setup_account_merge'] ?? null;
        $plan = $_SESSION['setup_account_merge_plan'] ?? null;
        $out[] = '<fieldset><legend data-i18n="merge.upload_title">Merge another account’s collector database</legend><p class="help">Upload an exported Zogograph account SQLite database to review it. The review reads only collector paths, vitals, scans, totals, and path sizes; it never copies accounts, passwords, alerts, settings, packages, or audit history. Nothing changes until you explicitly apply the reviewed merge.</p><input type="hidden" name="setup_account_merge_token" value="' . h(setupAccountMergeToken()) . '"><label>Exported account SQLite file<input required type="file" name="account_merge_source" accept="application/vnd.sqlite3,application/octet-stream,.db,.sqlite"></label><button type="submit" name="intent" value="review-account-merge" data-i18n="merge.review">Review collector merge</button></fieldset>';
        if (is_array($review) && is_array($plan) && ($plan['eligible'] ?? false) === true && (int)($review['expires_at'] ?? 0) >= time()) {
            $out[] = '<fieldset><legend data-i18n="merge.reviewed_title">Reviewed collector merge</legend><p class="help">This review expires in ' . h((string)max(0, (int)ceil(((int)$review['expires_at'] - time()) / 60))) . ' minutes. The live database is checked again when you apply it.</p><table class="preflight"><thead><tr><th>Collector data</th><th>Imported rows</th><th>Existing rows</th><th>Ranges overlap</th></tr></thead><tbody>';
            foreach ((array)($plan['collector'] ?? []) as $kind => $evidence) {
                $source = (array)($evidence['source'] ?? []); $target = (array)($evidence['target'] ?? []);
                $range = static fn(array $range): string => ($range['first'] ?? null) === null ? 'none' : gmdate('Y-m-d H:i', (int)$range['first']) . '–' . gmdate('Y-m-d H:i', (int)$range['last']);
                $out[] = '<tr><th>' . h(ucfirst((string)$kind)) . '</th><td>' . h((string)($source['rows'] ?? 0)) . ' · ' . h($range($source)) . '</td><td>' . h((string)($target['rows'] ?? 0)) . ' · ' . h($range($target)) . '</td><td>' . (($evidence['overlap'] ?? false) ? 'Yes — duplicates are skipped by timestamp' : 'No') . '</td></tr>';
            }
            $out[] = '</tbody></table><p class="help">Stop the account collector cron jobs and any dashboard writers first. Applying creates a private rollback snapshot, upgrades a private clone of the import when needed, then merges collector rows in one transaction.</p><label class="row"><input type="checkbox" name="writers_stopped" value="1"> I stopped all dashboard and collector writers.</label><label class="row"><input type="checkbox" name="allow_overlap" value="1"> I understand overlapping vitals and scans are deduplicated by timestamp.</label><input type="hidden" name="setup_account_merge_token" value="' . h(setupAccountMergeToken()) . '"><button type="submit" name="intent" value="apply-account-merge" data-i18n="merge.apply">Apply reviewed collector merge</button></fieldset>';
        }
        $result = $_SESSION['setup_account_merge_result'] ?? null;
        if (is_array($result) && isset($result['vitals'], $result['scans'], $result['paths'])) {
            $out[] = '<p class="banner ok"><strong>Last collector merge:</strong> ' . h((string)$result['paths']) . ' paths, ' . h((string)$result['vitals']) . ' vitals, and ' . h((string)$result['scans']) . ' scans added. A private rollback snapshot was retained.</p>';
        }
    } elseif (is_array($activePanel) && $activePanel['kind'] === 'package') {
        try {
            $out[] = setupRenderPackageSettingsPanel($activePanel, $current);
        } catch (Throwable $error) {
            $out[] = '<div class="banner warn">Package settings are unavailable: ' . h($error->getMessage()) . '</div>';
        }
    } else {
        $help = sampleHelp($samplePath)['sections'][$activeSection] ?? '';
        if ($help !== '') $out[] = '<p class="help">' . h(firstParagraph($help)) . '</p>';
    }
    if (!$editingLocked && (!is_array($activePanel) || $activePanel['kind'] !== 'package')) {
        foreach ($packagePanels as $panel) if (($panel['parent'] ?? null) === $activeSection) {
            try { $out[] = setupRenderPackageSettingsPanel($panel, $current); }
            catch (Throwable $error) { $out[] = '<div class="banner warn">Package settings are unavailable: ' . h($error->getMessage()) . '</div>'; }
        }
    }
    if (!$editingLocked) {
        foreach ($fields as $field) {
            if (!fieldIsInSection($field, $activeSection)) {
                continue;
            }
            if ($activeSection === 'openid' && $field['path'] === ['openid', 'clients']) {
                continue;
            }
            if ($field['key'] === 'package_runtime_secret_key_file') {
                $out[] = packageSecretKeyQuickStart(
                    renderField($field, $values, $errors, $configured),
                    $suggestedPackageSecretKeyPath,
                    $suggestedPackageSecretKeyExists,
                    !$configured || $unlocked
                );
            } elseif ($field['key'] === 'encryption_key_file') {
                $out[] = renderField($field, $values, $errors, $configured)
                    . primaryKeyQuickStart($suggestedKeyPath, $suggestedKeyExists, !$configured || $unlocked);
            } elseif ($field['key'] === 'password') {
                $out[] = renderField($field, $values, $errors, $configured)
                    . '<button type="submit">' . ($configured ? 'Change password' : 'Create password') . '</button>';
            } else {
                $out[] = renderField($field, $values, $errors, $configured);
            }
        }
    }
    if ($activeSection === 'basics') {
        $out[] = '<details open><summary>Installation checks</summary><table class="preflight"><thead><tr><th>Check</th><th>State</th><th>Detail</th></tr></thead><tbody>';
        foreach ($preflight as $check) {
            $out[] = '<tr><th>' . h($check['label']) . '</th><td class="' . h($check['state']) . '">' . h(strtoupper($check['state']))
                . '</td><td>' . h($check['detail']) . '</td></tr>';
        }
        $out[] = '</tbody></table></details>';
    }
    $out[] = '</section>';

    if (!$editingLocked && !in_array($activeSection, ['basics', 'security', 'schedule', 'maintenance'], true)
        && (!is_array($activePanel) || $activePanel['kind'] !== 'package')) {
        $out[] = '<button type="submit">Save ' . h($title) . '</button>';
    }
    $out[] = '</form></main></div>';

    if ($rendered !== null) {
        $out[] = '<h2 style="margin-top:28px">Copy this into <code>lib/config.php</code></h2>';
        $out[] = '<p class="muted small">Replacing the whole file. Keep it out of reach — '
            . '<code>lib/.htaccess</code> already denies the directory.'
            . ($configured && !$authorised
                ? ' This text carries a <strong>new</strong> mask secret, so using it renames '
                  . 'every masked path on the dashboard.'
                : '')
            . '</p>';
        $out[] = '<textarea readonly onclick="this.select()">' . h($rendered) . '</textarea>';
    }

    $out[] = '<script>document.querySelector("[data-setup-nav-search]")?.addEventListener("input",function(){const q=this.value.trim().toLowerCase();document.querySelectorAll("[data-setup-nav-item]").forEach(function(item){item.hidden=q!==""&&!item.dataset.setupNavTerms.toLowerCase().includes(q);});});document.querySelectorAll("[data-package-secret-picker]").forEach(function(picker){picker.addEventListener("change",function(){const box=document.querySelector("textarea[name=package_settings]");if(!box)return;try{const settings=JSON.parse(box.value);settings[picker.dataset.packageSecretPicker]=picker.value;box.value=JSON.stringify(settings,null,2);}catch(error){picker.value="";window.alert("Fix the package settings JSON before choosing a secret reference.");}});});(function(){let current=null,announced=null,checking=false;const identity=build=>[build?.commit,build?.runId,build?.createdAt].filter(Boolean).join(":");async function check(){if(document.hidden||checking)return;checking=true;try{const response=await fetch("./build.json?at="+Date.now(),{cache:"no-store",credentials:"same-origin"});if(!response.ok)return;const build=await response.json(),next=identity(build);if(!next)return;if(current===null){current=next;return;}if(current!==next&&announced!==next){announced=next;const banner=document.querySelector("#setupUpdateBanner");banner.replaceChildren();const text=document.createElement("span");text.textContent=build.browserRefreshRequired===true?"A compatibility update is ready. Reload before changing settings.":"A newer Zogograph update is available. Reload when you are ready.";const reload=document.createElement("button");reload.type="button";reload.textContent="Reload now";reload.addEventListener("click",()=>location.reload());banner.append(text,reload);banner.hidden=false;}}catch(error){}finally{checking=false;}}check();setInterval(check,60000);})();</script>';
    $out[] = '<script>document.querySelectorAll("[data-package-settings-target]").forEach(function(picker){picker.addEventListener("change",function(){const box=document.querySelector("textarea[name="+picker.dataset.packageSettingsTarget+"]");if(!box)return;try{const settings=JSON.parse(box.value);settings[picker.dataset.packageSecretPicker]=picker.value;box.value=JSON.stringify(settings,null,2);}catch(error){picker.value="";window.alert("Fix the package settings JSON before choosing a secret reference.");}});});</script>';
    $out[] = '</body></html>';
    return implode("\n", $out);
}

/** A section's key as a heading. `alerts` is a word; `host_log_paths` is not. */
function sectionTitle(string $name): string
{
    // "Ai" is not a word, and a heading that reads as one undoes the point of
    // making these legible.
    if ($name === 'ai') {
        return 'AI';
    }
    return ucfirst(str_replace('_', ' ', $name));
}

/**
 * The opening paragraph of a comment, for a group heading.
 *
 * The sample's section comments run to twenty lines and earn every one of them
 * when you are reading the file. Above a form they are a wall, and the first
 * paragraph is the one that says what the section is - which is all a heading
 * needs to do. The file is still there for the rest.
 */
function firstParagraph(string $text): string
{
    // Sentences, until there are enough of them. The comment arrives as one
    // run of text because the line breaks in it were formatting, not meaning.
    $parts = preg_split('/(?<=\.)\s+/', trim($text)) ?: [];
    $said = '';

    foreach ($parts as $part) {
        if ($said !== '' && strlen($said) + strlen($part) > 220) {
            break;
        }
        $said = $said === '' ? $part : $said . ' ' . $part;
    }

    return $said === '' ? $text : $said;
}

function renderField(array $field, array $values, array $errors, bool $configured): string
{
    $key = $field['key'];
    $label = h($key === 'password' ? ($configured ? 'Change password' : 'Create sign-in password') : $field['label']);
    $help = isset($field['help']) ? '<span class="help">' . h($field['help']) . '</span>' : '';
    $bad = isset($errors[$key]) ? '<span class="bad">' . h($errors[$key]) . '</span>' : '';

    if ($field['type'] === 'password') {
        $note = $configured
            ? 'Leave blank to keep the current password.'
            : h($field['help']);
        return '<fieldset><legend>' . $label . '</legend>'
            . '<span class="help">' . $note . '</span>'
            . '<label style="margin-top:10px">New password'
            . '<input type="password" name="password" autocomplete="new-password"></label>'
            . '<label>Again<input type="password" name="password_again" autocomplete="new-password"></label>'
            . $bad . '</fieldset>';
    }

    if ($field['type'] === 'bool') {
        $checked = !empty($values[$key]) ? ' checked' : '';
        return '<fieldset><legend>' . $label . '</legend>'
            . '<label class="row" style="margin:0"><input type="checkbox" name="' . h($key)
            . '" value="1"' . $checked . '> Yes</label>'
            . $help . $bad . '</fieldset>';
    }

    if ($field['type'] === 'categories') {
        $chosen = (array)$values[$key];
        $boxes = '';
        foreach ($field['options'] as $option) {
            $checked = in_array($option, $chosen, true) ? ' checked' : '';
            $boxes .= '<label><input type="checkbox" name="' . h($key) . '[]" value="'
                . h($option) . '"' . $checked . '> ' . h($option) . '</label>';
        }
        return '<fieldset><legend>' . $label . '</legend>'
            . '<div class="row">' . $boxes . '</div>' . $help . $bad . '</fieldset>';
    }

    if ($field['type'] === 'choice') {
        $options = '';
        foreach ($field['options'] as $value => $text) {
            $selected = (string)$values[$key] === (string)$value ? ' selected' : '';
            $options .= '<option value="' . h((string)$value) . '"' . $selected . '>'
                . h((string)$text) . '</option>';
        }
        return '<label>' . $label . '<select name="' . h($key) . '">' . $options . '</select>'
            . $help . $bad . '</label>';
    }

    if ($field['type'] === 'choices') {
        $chosen = (array)$values[$key];
        $boxes = '';
        foreach ($field['options'] as $value => $text) {
            $checked = in_array($value, $chosen, true) ? ' checked' : '';
            $boxes .= '<label><input type="checkbox" name="' . h($key) . '[]" value="'
                . h((string)$value) . '"' . $checked . '> ' . h((string)$text) . '</label>';
        }
        return '<fieldset><legend>' . $label . '</legend><div class="row">' . $boxes . '</div>'
            . $help . $bad . '</fieldset>';
    }

    if ($field['type'] === 'lines') {
        return '<label>' . $label
            . '<textarea name="' . h($key) . '" rows="4" spellcheck="false">'
            . h(displayValue($field, $values[$key])) . '</textarea>'
            . $help . $bad . '</label>';
    }

    if ($field['type'] === 'json') {
        return '<label>' . $label
            . '<textarea class="structured" name="' . h($key) . '" rows="12" spellcheck="false">'
            . h(displayValue($field, $values[$key])) . '</textarea>'
            . $help . $bad . '</label>';
    }

    if ($field['type'] === 'textarea') {
        return '<label>' . $label
            . '<textarea class="structured" name="' . h($key) . '" rows="12" spellcheck="false">'
            . h(displayValue($field, $values[$key])) . '</textarea>'
            . $help . $bad . '</label>';
    }

    $type = $field['type'] === 'email' ? 'email' : 'text';

    return '<label>' . $label
        . '<input type="' . $type . '" name="' . h($key) . '" value="'
        . h(displayValue($field, $values[$key])) . '">'
        . $help . $bad . '</label>';
}

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ------------------------------------------------------------------- cli -- */

function runCli(
    array $sample,
    array $fields,
    string $root,
    string $targetPath,
    ?array $existing,
    array $flags
): void {
    $has = fn(string $flag): bool => in_array($flag, $flags, true);
    $configured = $existing !== null && is_file(setupStorePath(require $targetPath));

    if ($has('--help') || $has('-h')) {
        echo <<<TEXT
        Zogograph — config wizard

          php setup.php              ask, then print the config to paste
          php setup.php --write      ask, then write lib/config.php
          php setup.php --defaults   take every default without asking

        Or open setup.php in a browser, which asks the same questions.

        lib/config.php is written freely when it does not exist. Once it does,
        replacing it needs the password already in it - otherwise the wizard
        prints the text and lets you edit the file yourself.

        TEXT;
        return;
    }

    $quiet = $has('--defaults');

    say('');
    say('  Zogograph — config wizard');
    foreach (setupPreflight($root, $targetPath) as $check) {
        say('  [' . strtoupper($check['state']) . '] ' . $check['label'] . ': ' . $check['detail']);
    }
    say($configured
        ? '  lib/config.php exists. Replacing it needs the password it already holds.'
        : '  Press enter to accept the value in brackets.');
    say('');

    $authorised = !$configured;
    if ($configured && $has('--write')) {
        if ($existing['password_hash'] === '') {
            say('  This install has no password set, so nothing can authorise replacing it.');
            say('  The config will be printed instead.');
        } else {
            $current = readSecret('  Current password: ', canHideInput());
            $authorised = mayOverwrite($existing, $current);
            say($authorised
                ? '  Authorised.'
                : '  That is not the current password. The config will be printed instead.');
        }
        say('');
    }

    $values = [];
    foreach ($fields as $field) {
        if (!fieldApplies($field, $values)) {
            // Skipped questions keep their default, so the config stays whole.
            $values[$field['key']] = $field['default'];
            continue;
        }
        $values[$field['key']] = askField($field, $quiet);
    }

    $hash = askPasswordHash($quiet, $configured && $authorised);
    if ($hash === null) {
        $hash = $configured && $authorised ? $existing['password_hash'] : '';
        say('  Password: unchanged.');
    }

    $mask = ($authorised && $configured && $existing['mask_secret'] !== '')
        ? $existing['mask_secret']
        : bin2hex(random_bytes(32));
    say($mask === ($existing['mask_secret'] ?? null)
        ? '  Mask secret: kept, so masked names do not all change.'
        : '  Mask secret: generated (32 random bytes).');

    $config = buildConfig($sample, $values, $hash, $mask, $samplePath);
    $text = renderConfig($config, $root . '/lib');

    say('');

    if ($has('--write') && $authorised) {
        [$written, $problem] = writeConfig($targetPath, $config, $root . '/lib');
        if ($written) {
            say('  Written to lib/config.php (mode 600).');
            say('');
            say('  Now take the first readings:');
            say('    php cron.php --vitals');
            say('    php cron.php --scan');
            say('');
            if (!empty($values['host_enabled'])) {
                say('  One more step, as root. The host collector runs as root and cannot');
                say('  be installed from here. In root\'s crontab (crontab -e):');
                say('');
                say('    ' . hostCronLine());
                say('');
                say('  Then run it once by hand to check it:');
                say('');
                say('    ' . hostCronCommand());
                say('');
            }
            return;
        }
        say('  ' . $problem);
        say('');
    }

    say('  ── Copy everything between the lines into lib/config.php ────────────');
    say('');
    echo $text;
    say('');
    say('  ── end ──────────────────────────────────────────────────────────────');
    say('');
}

function askField(array $field, bool $quiet): mixed
{
    if ($field['type'] === 'categories') {
        $chosen = [];
        foreach ($field['options'] as $option) {
            if (askYesNo("Allow deleting $option", in_array($option, $field['default'], true), $quiet)) {
                $chosen[] = $option;
            }
        }
        return $chosen;
    }

    if ($field['type'] === 'bool') {
        return askYesNo($field['label'], (bool)$field['default'], $quiet);
    }

    while (true) {
        $raw = prompt($field['label'], displayValue($field, $field['default']), $quiet);
        [$value, $problem] = parseField($field, $raw);
        if ($problem === null) {
            return $value;
        }
        complain($problem);
        if ($quiet) {
            return $field['default'];
        }
    }
}

function prompt(string $question, string $default, bool $quiet): string
{
    if ($quiet) {
        return $default;
    }

    fwrite(STDERR, sprintf('  %s [%s]: ', $question, $default));
    $line = fgets(STDIN);
    if ($line === false) {
        // No terminal and nothing piped: take the default rather than spinning
        // forever on an empty stream.
        fwrite(STDERR, "\n");
        return $default;
    }

    $value = trim($line);
    return $value === '' ? $default : $value;
}

function askYesNo(string $question, bool $default, bool $quiet): bool
{
    while (true) {
        $answer = prompt($question . ' (y/n)', $default ? 'y' : 'n', $quiet);
        if (preg_match('/^(y|yes|n|no)$/i', $answer)) {
            return (bool)preg_match('/^y/i', $answer);
        }
        complain('Answer y or n.');
        if ($quiet) {
            return $default;
        }
    }
}

function complain(string $problem): void
{
    // On a terminal the newline comes from the key the user just pressed.
    // Piped, nothing echoes, so the complaint would land on the end of the
    // prompt it is complaining about.
    if (!stream_isatty(STDIN)) {
        fwrite(STDERR, "\n");
    }
    say('    ' . $problem);
}

/**
 * Reads a new password without echoing it, twice, and returns the hash.
 *
 * Returns null for "leave it alone", which is only offered when there is an
 * existing password the caller is allowed to keep. `stty -echo` is the only
 * portable way to hide input from PHP CLI; where it is unavailable the wizard
 * says so plainly rather than quietly echoing a password into somebody's
 * scrollback and shell history.
 */
function askPasswordHash(bool $quiet, bool $mayKeep): ?string
{
    if ($quiet) {
        return $mayKeep ? null : '';
    }

    say('');
    say('  Sign-in password. Reveals real paths and enables deleting.');
    say($mayKeep
        ? '  Leave blank to keep the current one.'
        : '  Leave blank to disable sign-in entirely.');

    $hidden = canHideInput();
    if (!$hidden) {
        say('  (this terminal will echo what you type)');
    }

    while (true) {
        $first = readSecret('  Password: ', $hidden);
        if ($first === '') {
            if ($mayKeep) {
                return null;
            }
            say('  Sign-in disabled. Every path stays masked for everybody.');
            return '';
        }
        if (strlen($first) < 8) {
            complain('Use at least 8 characters.');
            continue;
        }
        if ($first !== readSecret('  Again: ', $hidden)) {
            complain('Those did not match.');
            continue;
        }

        say('  Password: hashed.');
        return password_hash($first, PASSWORD_DEFAULT);
    }
}

function canHideInput(): bool
{
    if (!function_exists('shell_exec') || stripos(PHP_OS_FAMILY, 'win') === 0) {
        return false;
    }
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    return !in_array('shell_exec', $disabled, true) && stream_isatty(STDIN);
}

function readSecret(string $label, bool $hidden): string
{
    fwrite(STDERR, $label);
    if ($hidden) {
        @shell_exec('stty -echo');
    }
    $value = fgets(STDIN);
    if ($hidden) {
        @shell_exec('stty echo');
        fwrite(STDERR, "\n");
    }
    return $value === false ? '' : trim($value);
}

/** Prompts go to stderr, so `php setup.php > out.php` writes only the config. */
function say(string $line): void
{
    fwrite(STDERR, $line . "\n");
}
