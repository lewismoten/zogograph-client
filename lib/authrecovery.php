<?php
declare(strict_types=1);

/* Pure local-password recovery primitive; persistence belongs to its caller. */

/**
 * Replaces one local password and invalidates every authenticated browser
 * session. A filesystem-authorized CLI operator is the recovery authority.
 */
function authResetPassword(array $config, string $password, string $username = ''): array
{
    if (strlen($password) < 8 || str_contains($password, "\0")) {
        throw new InvalidArgumentException('use a password of at least 8 characters');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($hash)) {
        throw new RuntimeException('could not create a password hash');
    }

    if ($username === '') {
        $config['password_hash'] = $hash;
    } else {
        $users = (array)($config['access']['users'] ?? []);
        if (!isset($users[$username]) || !is_array($users[$username])) {
            throw new InvalidArgumentException('that named local user does not exist');
        }
        $users[$username]['password_hash'] = $hash;
        $config['access'] = (array)($config['access'] ?? []);
        $config['access']['users'] = $users;
    }
    $config['auth'] = (array)($config['auth'] ?? []);
    $config['auth']['session_epoch'] = authSessionEpoch($config) + 1;
    return $config;
}
