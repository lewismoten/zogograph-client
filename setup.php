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

    /*
     * Where the form starts from.
     *
     * The system's own settings once unlocked, the sample's defaults otherwise.
     * The secrets are never among them: the password box is always empty, and
     * the mask secret is not a form field at all.
     */
    $values = [];
    $current = ($unlocked || !$configured) ? readExistingConfig($targetPath) : null;
    foreach ($fields as $field) {
        $values[$field['key']] = $current !== null
            ? valueAtPath($current, $field['path'], $field['default'])
            : $field['default'];
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
        $root . '/lib/config.sample.php', $activeSection, setupPreflight($root, $targetPath)
    );
}

/** The settings pages, derived from the same field definitions as the CLI. */
function setupSection(array $fields, string $asked): string
{
    if (in_array($asked, ['basics', 'privacy'], true)) return $asked;
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
    array $preflight
): string {
    $out = [];
    $out[] = '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    $out[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $out[] = '<title>Zogograph — setup</title>';
    $out[] = '<link rel="stylesheet" href="index.css">';
    $out[] = '<style>'
        . 'body{max-width:1180px;padding-top:0}'
        . '.setup-header{position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;background:var(--bg)}'
        . '.setup-brand{display:flex;align-items:center;gap:9px;color:var(--text);text-decoration:none}.setup-brand strong{font-size:1.08rem}.setup-brand small{color:var(--muted)}.setup-brand .poll{display:inline-block}.setup-brand .poll-fill,.setup-brand .poll-host-fill{stroke:var(--accent);stroke-dasharray:none;stroke-dashoffset:0}'
        . '.setup-header a:not(.setup-brand){color:var(--muted);font-size:.86rem}'
        . '.setup-layout{display:grid;grid-template-columns:210px minmax(0,1fr);gap:24px;align-items:start}'
        . '.setup-nav{position:sticky;top:16px;border:1px solid var(--line);border-radius:10px;padding:10px}'
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
        . '@media(max-width:700px){.setup-header{padding:11px 0}.setup-layout{display:block}.setup-nav{position:static;display:flex;overflow-x:auto;gap:3px;margin-bottom:16px;white-space:nowrap}.setup-nav a{flex:0 0 auto}}'
        . '</style></head><body class="wiz">';

    $out[] = '<header class="setup-header"><a class="setup-brand" href="?section=basics">'
        . '<span class="poll" aria-hidden="true"><svg viewBox="0 0 36 36"><circle class="poll-track" cx="18" cy="18" r="15"></circle><circle class="poll-fill" cx="18" cy="18" r="15"></circle><circle class="poll-host-track" cx="18" cy="18" r="9"></circle><circle class="poll-host-fill" cx="18" cy="18" r="9"></circle></svg><span class="poll-dot"></span></span>'
        . '<strong>Zogograph</strong><small>Setup</small></a><a href="./">Open dashboard</a></header>';
    $out[] = '<h1>Setup</h1>';

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

    $sections = [];
    foreach ($fields as $field) {
        $section = (string)($field['section'] ?? 'basics');
        if ($section !== 'basics') {
            $sections[$section] = true;
        }
    }
    $sections['privacy'] = true;
    $out[] = '<div class="setup-layout"><aside class="setup-nav" aria-label="Setup sections">'
        . '<a' . ($activeSection === 'basics' ? ' class="current"' : '') . ' href="?section=basics">Basics</a>';
    foreach (array_keys($sections) as $name) {
        $out[] = '<a' . ($activeSection === $name ? ' class="current"' : '')
            . ' href="?section=' . rawurlencode($name) . '">' . h(sectionTitle($name)) . '</a>';
    }
    $out[] = '</aside><main class="setup-main"><form method="post">'
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

    $title = $activeSection === 'basics' ? 'Start here' : sectionTitle($activeSection);
    $suggestedKeyPath = setupSuggestedPrivateKeyPath();
    $suggestedKeyExists = setupPrivateKeyExists($suggestedKeyPath);
    $suggestedPackageSecretKeyPath = setupSuggestedPackageSecretKeyPath();
    $suggestedPackageSecretKeyExists = setupPrivateKeyExists($suggestedPackageSecretKeyPath);
    $out[] = '<section class="setup-section"><h2>' . h($title) . '</h2>';
    if ($editingLocked) {
        $out[] = '<p class="help">Unlock setup to view or change this section.</p>';
    } elseif ($activeSection === 'basics') {
        $out[] = '<p class="help">Create a sign-in password to finish the initial setup. All other settings '
            . 'are available from the navigation when you need them.</p>';
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
    } else {
        $help = sampleHelp($samplePath)['sections'][$activeSection] ?? '';
        if ($help !== '') $out[] = '<p class="help">' . h(firstParagraph($help)) . '</p>';
    }
    if (!$editingLocked) {
        foreach ($fields as $field) {
            if (!fieldIsInSection($field, $activeSection)) {
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
    if ($unlocked && $activeSection === 'basics') {
        $out[] = '<p class="help"><a href="?lock=1">Lock setup again</a></p>';
    }
    $out[] = '</section>';

    if (!$editingLocked) {
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
    $label = h($field['label']);
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
