<?php
declare(strict_types=1);

/*
 * Product naming is configuration, not an assumption distributed through page
 * templates.  Keep the value deliberately small and plain: it is displayed as
 * text, never interpreted as HTML, and is also returned to the browser for
 * its shell chrome.
 */
const ZOGOGRAPH_DEFAULT_BRAND = 'Zogograph';

function brandName(array $config): string
{
    $name = trim((string)($config['branding']['name'] ?? ZOGOGRAPH_DEFAULT_BRAND));
    if ($name === '' || mb_strlen($name) > 80 || preg_match('/[\x00-\x1F\x7F]/', $name)) {
        return ZOGOGRAPH_DEFAULT_BRAND;
    }
    return $name;
}
