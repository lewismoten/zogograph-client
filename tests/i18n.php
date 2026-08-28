<?php
declare(strict_types=1);
function i18nExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$root = dirname(__DIR__);
$runtime = file_get_contents($root . '/i18n.js');
$page = file_get_contents($root . '/index.html');
$script = file_get_contents($root . '/index.js');
$setup = file_get_contents($root . '/setup.php');
$configure = file_get_contents($root . '/lib/configure.php');
$english = json_decode((string)file_get_contents($root . '/locales/en.json'), true);
i18nExpect($runtime !== false && str_contains($runtime, 'Intl.NumberFormat') && str_contains($runtime, 'I18N_RTL')
    && str_contains($runtime, 'zogograph.locale') && str_contains($runtime, 'data-i18n') && str_contains($runtime, 'registerCatalog')
    && str_contains($runtime, 'latin-number') && str_contains($runtime, 'compact') && str_contains($runtime, 'percent') && str_contains($runtime, 'unit') && str_contains($runtime, 'date-time') && str_contains($runtime, 'currency')
    && str_contains($runtime, 'i18nPlaceholder') && str_contains($runtime, 'zogograph-i18n-load') && str_contains($runtime, 'package-i18n'),
    'locale runtime lacks formatting, RTL, preference, or key-mode support');
i18nExpect($page !== false && str_contains($page, 'id="languagePicker"') && str_contains($page, 'id="languageKeys"')
    && str_contains($page, 'src="i18n.js"'), 'dashboard language controls are unavailable');
i18nExpect($script !== false && str_contains($script, 'new Intl.NumberFormat(document.documentElement.lang || undefined)')
    && str_contains($script, 'toLocaleString(document.documentElement.lang || undefined)') && str_contains($script, 'document.addEventListener("zogograph-i18n-load"')
    && str_contains($script, 'view.manual'), 'dashboard value formatting ignores the selected locale or refreshes manual views');
i18nExpect($script !== false && str_contains($script, 'typeof page.parent === "string" && VIEWS[page.parent]')
    && str_contains($script, 'link.dataset.parent = parent'),
    'package dashboard pages cannot select a safe core navigation parent');
i18nExpect($script !== false && str_contains($script, 'page.title_key ? ZogographI18n.t')
    && str_contains($script, 'view.packagePage'),
    'package dashboard pages do not use signed locale keys or refresh after a locale change');
i18nExpect($setup !== false && str_contains($setup, 'src="i18n.js"') && str_contains($setup, 'id="languagePicker"')
    && str_contains($setup, 'id="languageKeys"') && str_contains($setup, 'data-i18n="setup.installation"')
    && str_contains($setup, "'setup.section.' . \$name"),
    'Setup language controls or localized shell are unavailable');
i18nExpect($setup !== false && str_contains($setup, 'setupRenderPackageSettingsPanel')
    && str_contains($setup, 'data-package-settings-target') && str_contains($setup, 'save-package-settings:'),
    'Setup cannot render a signed package settings panel inside a core page');
i18nExpect($setup !== false && str_contains($setup, "(string)(\$panel['title_key'] ?? '')")
    && str_contains($setup, "(string)(\$panel['title_key'] ?? '')"),
    'Setup package panels cannot use signed locale catalog titles');
i18nExpect($configure !== false && str_contains($configure, 'function generatedFieldLabel')
    && str_contains($configure, "'label' => generatedFieldLabel(\$path)")
    && !str_contains($configure, "'label' => \$dotted"),
    'generated Setup fields can still expose dotted internal configuration paths');
i18nExpect(is_array($english) && isset($english['example.readings'], $english['example.compact'], $english['example.cost'], $english['example.percent'], $english['example.storage'], $english['example.changed'], $english['update.available'], $english['range.10m'], $english['action.reload'], $english['navigation.storage'], $english['navigation.cpu'], $english['navigation.deleted_held'], $english['navigation.cpu_limits'], $english['deployment.test'], $english['merge.apply']),
    'English fallback catalog is incomplete');
preg_match_all('/data-i18n(?:-placeholder|-title|-aria-label)?="([^"]+)"/', (string)$page, $pageKeys);
foreach (array_unique($pageKeys[1] ?? []) as $key) {
    i18nExpect(isset($english[$key]), "dashboard i18n attribute key $key has no English fallback");
}
$translatedNavigation = ['navigation.deleted_held', 'navigation.cpu_limits', 'navigation.memory_by_user', 'navigation.memory_breakdown', 'navigation.memory_services', 'navigation.memory_processes', 'navigation.kernel_slab', 'navigation.services', 'navigation.units', 'navigation.last_busy', 'navigation.url_checks', 'navigation.open_ports', 'navigation.connected_peers', 'navigation.network_capture', 'navigation.network_captures', 'navigation.dns_lookups', 'navigation.domains', 'navigation.whois', 'navigation.fetch_page', 'navigation.dns_records', 'navigation.whoami', 'navigation.reverse_dns', 'navigation.assistant', 'navigation.assistant_identity', 'navigation.past_conversations', 'navigation.summaries', 'navigation.assistant_memory', 'navigation.ai_model', 'navigation.prices', 'navigation.thresholds', 'navigation.images', 'navigation.tools', 'navigation.try_tool', 'navigation.billing', 'navigation.assistant_data', 'navigation.terminal', 'navigation.sessions', 'navigation.host_capabilities', 'navigation.installed_software', 'navigation.changes', 'navigation.system', 'navigation.account_data', 'navigation.protection', 'navigation.host_data', 'navigation.data_retention', 'navigation.trusted_servers', 'navigation.marketplace', 'navigation.package_runtime', 'navigation.forensics', 'navigation.database_copies', 'navigation.recent_problems', 'navigation.email_alerts', 'navigation.account_scans', 'navigation.host_scans', 'navigation.custom_names', 'navigation.audit', 'navigation.descriptions'];
foreach (['en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'] as $locale) {
    $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
    i18nExpect(is_array($catalog), "locale catalog $locale is invalid");
    if (in_array($locale, ['es', 'hi', 'zh', 'ar'], true)) {
        i18nExpect(isset($catalog['navigation.charts'], $catalog['navigation.storage'], $catalog['navigation.backups'], $catalog['navigation.cpu'], $catalog['navigation.memory'], $catalog['navigation.processes'], $catalog['navigation.network'], $catalog['setup.section.deployment']) && array_diff($translatedNavigation, array_keys($catalog)) === [],
            "locale catalog $locale lacks translated primary navigation");
    }
}
foreach (['navigation.charts', 'navigation.storage', 'navigation.backups', 'navigation.cpu', 'navigation.memory', 'navigation.processes', 'navigation.network', 'navigation.deleted_held', 'navigation.cpu_limits'] as $key) {
    i18nExpect($page !== false && str_contains($page, 'data-i18n="' . $key . '"'), "dashboard navigation does not use $key");
}
echo "i18n checks passed\n";
