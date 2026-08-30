<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/authrecovery.php';

function recoveryExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$legacy = authResetPassword(
    [
        'password_hash' => password_hash('before', PASSWORD_DEFAULT),
        'auth' => ['session_epoch' => 2],
    ],
    'new-local-password',
);
recoveryExpect(
    password_verify('new-local-password', $legacy['password_hash']),
    'legacy password was not replaced',
);
recoveryExpect(
    authSessionEpoch($legacy) === 3,
    'legacy reset did not revoke sessions',
);

$named = authResetPassword(
    [
        'password_hash' => password_hash('administrator', PASSWORD_DEFAULT),
        'access' => [
            'users' => [
                'operator' => [
                    'password_hash' => password_hash(
                        'before',
                        PASSWORD_DEFAULT,
                    ),
                ],
            ],
        ],
    ],
    'new-named-password',
    'operator',
);
recoveryExpect(
    password_verify(
        'new-named-password',
        $named['access']['users']['operator']['password_hash'],
    ),
    'named-user password was not replaced',
);
recoveryExpect(
    password_verify('administrator', $named['password_hash']),
    'named reset changed the legacy administrator',
);
recoveryExpect(
    authSessionEpoch($named) === 1,
    'named reset did not revoke sessions',
);

foreach (
    [
        fn() => authResetPassword([], 'short'),
        fn() => authResetPassword([], "valid-password\0"),
        fn() => authResetPassword(
            ['access' => ['users' => []]],
            'valid-password',
            'absent',
        ),
    ]
    as $attempt
) {
    try {
        $attempt();
        throw new RuntimeException(
            'unsafe password recovery input was accepted',
        );
    } catch (InvalidArgumentException) {
        // Expected.
    }
}

echo "authentication recovery checks passed\n";
