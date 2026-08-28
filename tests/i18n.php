<?php
declare(strict_types=1);
function i18nExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$root = dirname(__DIR__);
$runtime = file_get_contents($root . '/i18n.js');
$page = file_get_contents($root . '/index.html');
$setup = file_get_contents($root . '/setup.php');
$english = json_decode((string)file_get_contents($root . '/locales/en.json'), true);
i18nExpect($runtime !== false && str_contains($runtime, 'Intl.NumberFormat') && str_contains($runtime, 'I18N_RTL')
    && str_contains($runtime, 'zogograph.locale') && str_contains($runtime, 'data-i18n') && str_contains($runtime, 'registerCatalog')
    && str_contains($runtime, 'latin-number') && str_contains($runtime, 'compact') && str_contains($runtime, 'currency')
    && str_contains($runtime, 'i18nPlaceholder') && str_contains($runtime, 'zogograph-i18n-load') && str_contains($runtime, 'package-i18n'),
    'locale runtime lacks formatting, RTL, preference, or key-mode support');
i18nExpect($page !== false && str_contains($page, 'id="languagePicker"') && str_contains($page, 'id="languageKeys"')
    && str_contains($page, 'src="i18n.js"'), 'dashboard language controls are unavailable');
i18nExpect($setup !== false && str_contains($setup, 'src="i18n.js"') && str_contains($setup, 'id="languagePicker"')
    && str_contains($setup, 'id="languageKeys"') && str_contains($setup, 'data-i18n="setup.installation"'),
    'Setup language controls or localized shell are unavailable');
i18nExpect(is_array($english) && isset($english['example.readings'], $english['example.compact'], $english['example.cost'], $english['update.available'], $english['range.10m'], $english['action.reload'], $english['navigation.storage'], $english['navigation.cpu']),
    'English fallback catalog is incomplete');
foreach (['en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'] as $locale) {
    $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
    i18nExpect(is_array($catalog), "locale catalog $locale is invalid");
    if (in_array($locale, ['es', 'hi', 'zh', 'ar'], true)) {
        i18nExpect(isset($catalog['navigation.charts'], $catalog['navigation.storage'], $catalog['navigation.backups'], $catalog['navigation.cpu'], $catalog['navigation.memory'], $catalog['navigation.processes'], $catalog['navigation.network']),
            "locale catalog $locale lacks translated primary navigation");
    }
}
foreach (['navigation.charts', 'navigation.storage', 'navigation.backups', 'navigation.cpu', 'navigation.memory', 'navigation.processes', 'navigation.network'] as $key) {
    i18nExpect($page !== false && str_contains($page, 'data-i18n="' . $key . '"'), "dashboard navigation does not use $key");
}
echo "i18n checks passed\n";
