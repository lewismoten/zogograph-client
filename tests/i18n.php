<?php
declare(strict_types=1);
function i18nExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$root = dirname(__DIR__);
$runtime = file_get_contents($root . '/i18n.js');
$page = file_get_contents($root . '/index.html');
$english = json_decode((string)file_get_contents($root . '/locales/en.json'), true);
i18nExpect($runtime !== false && str_contains($runtime, 'Intl.NumberFormat') && str_contains($runtime, 'I18N_RTL')
    && str_contains($runtime, 'zogograph.locale') && str_contains($runtime, 'data-i18n') && str_contains($runtime, 'registerCatalog')
    && str_contains($runtime, 'latin-number') && str_contains($runtime, 'compact') && str_contains($runtime, 'currency'),
    'locale runtime lacks formatting, RTL, preference, or key-mode support');
i18nExpect($page !== false && str_contains($page, 'id="languagePicker"') && str_contains($page, 'id="languageKeys"')
    && str_contains($page, 'src="i18n.js"'), 'dashboard language controls are unavailable');
i18nExpect(is_array($english) && isset($english['example.readings'], $english['example.compact'], $english['example.cost'], $english['update.available']),
    'English fallback catalog is incomplete');
foreach (['en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'] as $locale) {
    i18nExpect(is_array(json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true)), "locale catalog $locale is invalid");
}
echo "i18n checks passed\n";
