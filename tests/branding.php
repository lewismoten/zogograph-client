<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/branding.php';

function brandingExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

brandingExpect(brandName([]) === 'Zogograph', 'the default product brand changed');
brandingExpect(brandName(['branding' => ['name' => 'Northstar Monitor']]) === 'Northstar Monitor',
    'a configured product brand was not retained');
brandingExpect(brandName(['branding' => ['name' => "bad\nname"]]) === 'Zogograph',
    'a control character was accepted in the product brand');
$setup = file_get_contents(__DIR__ . '/../setup.php');
$openid = file_get_contents(__DIR__ . '/../openid.php');
$peerConnect = file_get_contents(__DIR__ . '/../peer-connect.php');
brandingExpect($setup !== false && str_contains($setup, '$issuer = rawurlencode($brand)'),
    'authenticator issuer does not use the configured product brand');
brandingExpect($setup !== false && str_contains($setup, '$brand = brandName($existing ?? [])')
    && str_contains($setup, '{$brand} — config wizard')
    && str_contains($setup, "say('  ' . \$brand . ' — config wizard')"),
    'command-line setup wizard does not use the configured product brand');
brandingExpect($openid !== false && str_contains($openid, "require_once __DIR__ . '/lib/branding.php';")
    && str_contains($openid, "brandName(\$config) . ' before authorizing a trusted client'"),
    'OpenID authorization does not use the configured product brand');
brandingExpect($peerConnect !== false && str_contains($peerConnect, '$brand = brandName($config);')
    && str_contains($peerConnect, 'htmlspecialchars($brand, ENT_QUOTES)')
    && str_contains($peerConnect, 'Peer connection</title>'),
    'peer-connect page does not use the shared configured brand');

echo "branding checks passed\n";
