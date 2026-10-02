<?php
declare(strict_types=1);
require_once __DIR__ .
    '/../plugins/sqlcipher-encrypted-sqlite/src/SqlcipherStorageProvider.php';

use Zogograph\Plugin\SqlcipherEncryptedSqlite\SqlcipherStorageProvider;

function sqlcipherPreflightExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$provider = new SqlcipherStorageProvider();
$capability = $provider->capability();
if (($capability['state'] ?? null) !== 'driver-ready') {
    echo "SQLCipher preflight checks skipped: driver is unavailable\n";
    exit(0);
}
$result = $provider->preflight();
sqlcipherPreflightExpect(
    ($result['id'] ?? null) === 'sqlcipher-encrypted-sqlite' &&
        ($result['state'] ?? null) === 'preflight-ready' &&
        str_contains((string) ($result['reason'] ?? ''), 'wrong-key'),
    'SQLCipher disposable preflight did not prove reopen and wrong-key rejection',
);
echo "SQLCipher preflight checks passed\n";
