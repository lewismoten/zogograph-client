<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

function sessionExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$_SESSION = ['signed_in' => true, 'auth_epoch' => 4];
sessionExpect(
    authSessionCurrent(['auth' => ['session_epoch' => 4]]),
    'current session generation was rejected',
);
sessionExpect(
    !authSessionCurrent(['auth' => ['session_epoch' => 5]]),
    'revoked session generation was accepted',
);
sessionExpect(
    authSessionEpoch(['auth' => ['session_epoch' => 'not-a-number']]) === 0,
    'invalid configured session epoch was not safely normalized',
);
$_SESSION = ['signed_in' => true];
sessionExpect(
    !authSessionCurrent(['auth' => ['session_epoch' => 0]]),
    'legacy session without a generation was accepted',
);

echo "session revocation checks passed\n";
