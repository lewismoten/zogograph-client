<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
if (!preg_match('/^[A-Z2-7]{32}$/', totpGenerateSecret())) throw new RuntimeException('generated TOTP seed is not Base32 160-bit material');

function totpExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // RFC 6238 SHA-1 test key
totpExpect(totpValid($secret, '287082', 59), 'RFC 6238 TOTP vector was rejected');
totpExpect(!totpValid($secret, '287083', 59), 'incorrect TOTP code was accepted');
totpExpect(totpValid($secret, '287082', 89), 'one permitted TOTP clock step was rejected');
totpExpect(!totpValid($secret, '287082', 120), 'stale TOTP code was accepted');
totpExpect(totpBase32Decode('not-a-secret!') === false, 'invalid Base32 secret was accepted');
totpExpect(signInTotpSecret(['auth' => ['totp_secret' => 'legacy']], null) === 'legacy',
    'legacy TOTP secret was not selected');
totpExpect(signInTotpSecret(['auth' => ['totp_secret' => 'legacy']], ['totp_secret' => 'named']) === 'named',
    'named-user TOTP secret was not selected');

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE totp_recovery_codes (id INTEGER PRIMARY KEY, account TEXT NOT NULL, code_hash TEXT NOT NULL, created_at INTEGER NOT NULL)');
$codes = totpRecoveryCodes($pdo, 'administrator', 100);
totpExpect(count($codes) === 8 && count(array_unique($codes)) === 8, 'recovery codes were not distinct');
totpExpect(totpRecoveryConsume($pdo, 'administrator', $codes[0]), 'a fresh recovery code was refused');
totpExpect(!totpRecoveryConsume($pdo, 'administrator', $codes[0]), 'a recovery code was accepted twice');
totpExpect(!totpRecoveryConsume($pdo, 'administrator', 'BAD-CODE'), 'an invalid recovery code was accepted');
totpExpect(count(totpRecoveryCodes($pdo, 'administrator', 101)) === 8
    && !totpRecoveryConsume($pdo, 'administrator', $codes[1]), 'replacing recovery codes did not revoke earlier codes');

echo "TOTP checks passed\n";
