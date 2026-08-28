#!/usr/bin/env php
<?php
declare(strict_types=1);

/* Emergency local recovery; never depends on the application database or SMTP. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This recovery command is CLI-only.\n");
}

$args = array_slice($argv, 1);
if (in_array('--help', $args, true) || in_array('-h', $args, true)) {
    echo "Usage: php scripts/reset-password.php [--user NAME] [--stdin]\n";
    exit(0);
}
$stdin = false;
$username = '';
while ($args !== []) {
    $arg = array_shift($args);
    if ($arg === '--stdin' && !$stdin) {
        $stdin = true;
        continue;
    }
    if ($arg === '--user' && $args !== [] && $username === '') {
        $username = trim((string)array_shift($args));
        if ($username === '') {
            fwrite(STDERR, "A named-user recovery needs a non-empty user name.\n");
            exit(64);
        }
        continue;
    }
    fwrite(STDERR, "Usage: php scripts/reset-password.php [--user NAME] [--stdin]\n");
    exit(64);
}

$configFile = __DIR__ . '/../lib/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "No lib/config.php; configure Zogograph before resetting a password.\n");
    exit(1);
}

require_once __DIR__ . '/../lib/setupstore.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/authrecovery.php';

/** Reads a new secret without echoing it when the current terminal supports stty. */
function recoveryReadSecret(string $prompt): string
{
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    $hidden = function_exists('shell_exec') && !in_array('shell_exec', $disabled, true)
        && stripos(PHP_OS_FAMILY, 'win') !== 0 && stream_isatty(STDIN);
    fwrite(STDERR, $prompt . ($hidden ? '' : ' (input will be visible)'));
    if ($hidden) @shell_exec('stty -echo');
    $line = fgets(STDIN);
    if ($hidden) {
        @shell_exec('stty echo');
        fwrite(STDERR, "\n");
    }
    return rtrim((string)$line, "\r\n");
}

try {
    if ($stdin) {
        $password = rtrim((string)stream_get_contents(STDIN), "\r\n");
    } else {
        $password = recoveryReadSecret('New password: ');
        if (!hash_equals($password, recoveryReadSecret('Repeat password: '))) {
            throw new InvalidArgumentException('the passwords did not match');
        }
    }
    $config = authResetPassword(loadConfiguredSettings($configFile), $password, $username);
    saveConfiguredSettings($config, $configFile, __DIR__ . '/../lib');
    fwrite(STDOUT, 'Password reset for ' . ($username === '' ? 'the legacy administrator' : $username)
        . "; every signed-in browser session has been ended.\n");
} catch (Throwable $e) {
    fwrite(STDERR, 'Password reset refused: ' . $e->getMessage() . "\n");
    exit(1);
}
