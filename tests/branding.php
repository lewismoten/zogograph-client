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
brandingExpect($setup !== false && str_contains($setup, '$issuer = rawurlencode($brand)'),
    'authenticator issuer does not use the configured product brand');

echo "branding checks passed\n";
