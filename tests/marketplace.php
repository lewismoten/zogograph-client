<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/marketplace.php';
function marketplaceExpect(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}
function marketplaceWriteGuard(string $source, string $function): bool
{
    return preg_match(
        '/function\\s+' .
            preg_quote($function, '/') .
            '\\s*\\([^)]*\\)\\s*:\\s*array\\s*\\{.*?requireWrite\\(\\$post\\);/s',
        $source,
    ) === 1;
}
$root =
    sys_get_temp_dir() . '/zogograph-marketplace-' . bin2hex(random_bytes(4));
mkdir($root, 0700);
marketplaceExpect(
    str_ends_with(marketplaceDirectory([]), '/data/marketplace'),
    'legacy configuration did not receive the private marketplace default',
);
file_put_contents(
    $root . '/uptime-pack.json',
    json_encode([
        'id' => 'uptime-pack',
        'version' => '1.0.0',
        'name' => 'Uptime pack',
        'publisher' => 'HostScope',
        'summary' => 'Useful uptime recommendations.',
        'license' => 'Proprietary',
        'capabilities' => ['alert_recommendations'],
        'recommendations' => [
            [
                'rule' => 'host_uptime',
                'severity' => 'warning',
                'reason' => 'Review unexpected restarts.',
            ],
        ],
    ]),
);
file_put_contents($root . '/bad.json', '{');
$catalog = marketplaceCatalog(['marketplace' => ['directory' => $root]]);
marketplaceExpect(
    count($catalog['packages']) === 1 &&
        $catalog['packages'][0]['id'] === 'uptime-pack',
    'valid marketplace manifest was not discovered',
);
marketplaceExpect(
    count($catalog['errors']) === 1,
    'malformed manifest was not reported',
);
marketplaceExpect(
    $catalog['packages'][0]['provenance']['status'] === 'local unsigned',
    'unsigned local manifest was misclassified',
);
$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec(
    'CREATE TABLE marketplace_packages (package_id TEXT PRIMARY KEY, version TEXT, enabled_at INTEGER, changed_at INTEGER, changed_by TEXT)',
);
$enabled = marketplaceSetEnabled(
    ['marketplace' => ['directory' => $root]],
    $pdo,
    'uptime-pack',
    true,
    'administrator',
);
marketplaceExpect(
    $enabled['enabled'] === true &&
        marketplacePackages(['marketplace' => ['directory' => $root]], $pdo)[0][
            'enabled'
        ] === true,
    'catalog package enablement was not persisted',
);
marketplaceExpect(
    marketplaceAlertRecommendations(
        ['marketplace' => ['directory' => $root]],
        $pdo,
    )[0]['rule'] === 'host_uptime',
    'enabled recommendation package was not exposed as data-only guidance',
);
$updatedManifest = json_decode(
    (string) file_get_contents($root . '/uptime-pack.json'),
    true,
);
$updatedManifest['version'] = '1.0.1';
file_put_contents($root . '/uptime-pack.json', json_encode($updatedManifest));
$updated = marketplacePackages(
    ['marketplace' => ['directory' => $root]],
    $pdo,
)[0];
marketplaceExpect(
    !$updated['enabled'] && $updated['requires_reapproval'],
    'changed package version did not require fresh approval',
);
$disabled = marketplaceSetEnabled(
    ['marketplace' => ['directory' => $root]],
    $pdo,
    'uptime-pack',
    false,
    'administrator',
);
marketplaceExpect(
    $disabled['enabled'] === false &&
        marketplaceAlertRecommendations(
            ['marketplace' => ['directory' => $root]],
            $pdo,
        ) === [],
    'disabled recommendation package remained active',
);
try {
    marketplaceManifest([
        'id' => 'Bad',
        'version' => '1',
        'name' => 'x',
        'publisher' => 'x',
        'summary' => 'x',
        'license' => 'x',
        'capabilities' => ['shell'],
    ]);
    throw new RuntimeException('unsafe manifest was accepted');
} catch (InvalidArgumentException) {
}
try {
    marketplaceManifest([
        'id' => 'bad-guidance',
        'version' => '1.0.0',
        'name' => 'x',
        'publisher' => 'x',
        'summary' => 'x',
        'license' => 'x',
        'capabilities' => ['alert_recommendations'],
        'recommendations' => [
            ['rule' => 'same_rule', 'severity' => 'warning', 'reason' => 'x'],
            ['rule' => 'same_rule', 'severity' => 'error', 'reason' => 'y'],
        ],
    ]);
    throw new RuntimeException('duplicate package guidance was accepted');
} catch (InvalidArgumentException) {
}
$incompatible = marketplaceManifest([
    'id' => 'future-runtime',
    'version' => '1.0.0',
    'name' => 'x',
    'publisher' => 'x',
    'summary' => 'x',
    'license' => 'x',
    'capabilities' => ['alert_recommendations'],
    'compatibility' => ['min_php' => '999.0.0'],
]);
marketplaceExpect(
    !marketplaceCompatibility($pdo, $incompatible)['can_enable'],
    'incompatible package can be enabled',
);
$contribution = marketplaceManifest([
    'id' => 'storage-hygiene',
    'version' => '1.0.0',
    'name' => 'x',
    'publisher' => 'x',
    'summary' => 'x',
    'license' => 'x',
    'capabilities' => ['path_hygiene'],
    'view_contributions' => [
        [
            'capability' => 'path_hygiene',
            'view' => 'storage',
            'keywords' => ['path hygiene'],
            'priority' => 'high',
            'before' => ['another-plugin'],
            'after' => [],
        ],
    ],
]);
marketplaceExpect(
    $contribution['view_contributions'][0]['priority'] === 'high',
    'valid view contribution was not retained',
);
try {
    marketplaceManifest([
        'id' => 'bad-view',
        'version' => '1.0.0',
        'name' => 'x',
        'publisher' => 'x',
        'summary' => 'x',
        'license' => 'x',
        'capabilities' => ['path_hygiene'],
        'view_contributions' => [
            ['capability' => 'path_hygiene', 'view' => 'system'],
        ],
    ]);
    throw new RuntimeException('unsupported view contribution was accepted');
} catch (InvalidArgumentException) {
}
$installer = $root . '/installed';
$installConfig = ['marketplace' => ['directory' => $installer]];
$installOne = json_encode([
    'id' => 'installed-pack',
    'version' => '1.0.0',
    'name' => 'Installed pack',
    'publisher' => 'HostScope',
    'summary' => 'First local revision.',
    'license' => 'Proprietary',
    'capabilities' => ['alert_recommendations'],
]);
$installed = marketplaceInstallManifest(
    $installConfig,
    $pdo,
    $installOne,
    'administrator',
);
marketplaceExpect(
    $installed['revision'] === null &&
        is_file($installer . '/installed-pack.json'),
    'local manifest was not installed atomically',
);
$installTwo = json_encode([
    'id' => 'installed-pack',
    'version' => '1.1.0',
    'name' => 'Installed pack',
    'publisher' => 'HostScope',
    'summary' => 'Second local revision.',
    'license' => 'Proprietary',
    'capabilities' => ['alert_recommendations'],
]);
$updatedInstall = marketplaceInstallManifest(
    $installConfig,
    $pdo,
    $installTwo,
    'administrator',
);
marketplaceExpect(
    is_string($updatedInstall['revision']) &&
        count(marketplaceHistory($installConfig, 'installed-pack')) === 1,
    'package update did not preserve a rollback revision',
);
$rolledBack = marketplaceRollback(
    $installConfig,
    $pdo,
    'installed-pack',
    $updatedInstall['revision'],
    'administrator',
);
marketplaceExpect(
    $rolledBack['package']['version'] === '1.0.0' &&
        !marketplacePackages($installConfig, $pdo)[0]['enabled'],
    'rollback did not restore a disabled prior package revision',
);
$signed = marketplaceManifest([
    'id' => 'signed-pack',
    'version' => '1.0.0',
    'name' => 'Signed pack',
    'publisher' => 'Example publisher',
    'summary' => 'Signed guidance.',
    'license' => 'Proprietary',
    'capabilities' => ['alert_recommendations'],
]);
if (function_exists('sodium_crypto_sign_keypair')) {
    $pair = sodium_crypto_sign_keypair();
    $secret = sodium_crypto_sign_secretkey($pair);
    $public = sodium_crypto_sign_publickey($pair);
    $payload = $signed;
    unset($payload['signature']);
    $signed['signature'] = [
        'key_id' => 'example-publisher',
        'algorithm' => 'ed25519',
        'value' => base64_encode(
            sodium_crypto_sign_detached(
                marketplaceCanonicalJson($payload),
                $secret,
            ),
        ),
    ];
    $signed = marketplaceManifest($signed);
    $trustConfig = [
        'marketplace' => [
            'trusted_keys' => [
                'example-publisher' => [
                    'publisher' => 'Example publisher',
                    'public_key' => base64_encode($public),
                ],
            ],
        ],
    ];
    marketplaceExpect(
        marketplaceProvenance($trustConfig, $signed)['status'] === 'verified',
        'valid publisher signature was not verified',
    );
    $signed['summary'] = 'Changed after signing.';
    marketplaceExpect(
        marketplaceProvenance($trustConfig, $signed)['can_enable'] === false,
        'tampered signed manifest can be enabled',
    );
} else {
    $signed['signature'] = [
        'key_id' => 'example-publisher',
        'algorithm' => 'ed25519',
        'value' => base64_encode(str_repeat('x', 64)),
    ];
    $signed = marketplaceManifest($signed);
    $trustConfig = [
        'marketplace' => [
            'trusted_keys' => [
                'example-publisher' => [
                    'publisher' => 'Example publisher',
                    'public_key' => base64_encode(str_repeat('x', 32)),
                ],
            ],
        ],
    ];
    marketplaceExpect(
        marketplaceProvenance($trustConfig, $signed)['status'] ===
            'unavailable',
        'missing sodium was not reported',
    );
}
$api = file_get_contents(__DIR__ . '/../api.php');
$signer = file_get_contents(__DIR__ . '/../scripts/marketplace-sign.php');
marketplaceExpect(
    $api !== false &&
        str_contains($api, "'marketplace' => 'sys-marketplace'") &&
        str_contains($api, "'marketplace-enable' => doMarketplaceEnable") &&
        str_contains($api, "'marketplace-install' => doMarketplaceInstall") &&
        str_contains($api, "'marketplace-rollback' => doMarketplaceRollback") &&
        marketplaceWriteGuard($api, 'doMarketplaceEnable') &&
        marketplaceWriteGuard($api, 'doMarketplaceInstall') &&
        marketplaceWriteGuard($api, 'doMarketplaceInstallPathHygiene') &&
        marketplaceWriteGuard($api, 'marketplaceHistoryView') &&
        marketplaceWriteGuard($api, 'doMarketplaceRollback'),
    'marketplace administration route is missing its write authorization',
);
marketplaceExpect(
    $signer !== false &&
        str_contains($signer, "PHP_SAPI !== 'cli'") &&
        str_contains($signer, 'marketplaceCanonicalJson($payload)') &&
        str_contains($signer, 'sodium_crypto_sign_detached'),
    'publisher signing command is missing its safety boundary',
);
foreach (scandir($installer . '/.history/installed-pack') ?: [] as $entry) {
    if (preg_match('/^[a-f0-9]{64}\.json$/', $entry)) {
        unlink($installer . '/.history/installed-pack/' . $entry);
    }
}
unlink($installer . '/installed-pack.json');
rmdir($installer . '/.history/installed-pack');
rmdir($installer . '/.history');
rmdir($installer);
unlink($root . '/uptime-pack.json');
unlink($root . '/bad.json');
rmdir($root);
echo "marketplace checks passed\n";
