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
require_once $root . '/lib/configure.php';
$schemaFields = configFields(require $root . '/lib/config.sample.php');
i18nExpect($runtime !== false && str_contains($runtime, 'Intl.NumberFormat') && str_contains($runtime, 'I18N_RTL')
    && str_contains($runtime, 'zogograph.locale') && str_contains($runtime, 'data-i18n') && str_contains($runtime, 'registerCatalog')
    && str_contains($runtime, 'i18nNodeValues')
    && str_contains($runtime, 'latin-number') && str_contains($runtime, 'arabic-number') && str_contains($runtime, 'eastern-arabic-number') && str_contains($runtime, 'numberingSystem: "arab"') && str_contains($runtime, 'numberingSystem: "arabext"') && str_contains($runtime, 'compact') && str_contains($runtime, 'percent') && str_contains($runtime, 'unit') && str_contains($runtime, 'date-time') && str_contains($runtime, 'currency')
    && str_contains($runtime, 'i18nPlaceholder') && str_contains($runtime, 'i18nAlt') && str_contains($runtime, 'zogograph-i18n-load') && str_contains($runtime, 'package-i18n'),
    'locale runtime lacks formatting, RTL, preference, or key-mode support');
i18nExpect($runtime !== false && str_contains($runtime, 'Package catalogs are translation extensions')
    && str_contains($runtime, 'if (!(key in catalog)) catalog[key] = value')
    && str_contains($runtime, 'key.startsWith(prefix)') && str_contains($runtime, 'for (const [packageId, catalogs] of Object.entries(data.catalogs || {}))'),
    'package locale catalogs can overwrite core translation text');
i18nExpect($page !== false && str_contains($page, 'id="languagePicker"') && str_contains($page, 'id="languageKeys"')
    && str_contains($page, 'src="i18n.js"'), 'dashboard language controls are unavailable');
i18nExpect($script !== false && str_contains($script, 'new Intl.NumberFormat(document.documentElement.lang || undefined)')
    && str_contains($script, 'toLocaleString(document.documentElement.lang || undefined)') && str_contains($script, 'document.addEventListener("zogograph-i18n-load"')
    && str_contains($script, 'view.manual'), 'dashboard value formatting ignores the selected locale or refreshes manual views');
i18nExpect($script !== false && str_contains($script, 'ZogographI18n.t("image.note_screenshot"')
    && str_contains($script, 'ZogographI18n.t("image.send_screenshot"'),
    'dashboard fixed image descriptions do not use the locale catalog');
i18nExpect($script !== false && str_contains($script, 'ZogographI18n.t("encryption.custody_notice"')
    && str_contains($script, 'ZogographI18n.t("diagnosis.ignore_confirm"')
    && str_contains($script, 'ZogographI18n.t("diagnosis.ignore_success"'),
    'security-sensitive dashboard notices do not use the locale catalog');
i18nExpect($script !== false && str_contains($script, 'function packageText(')
    && str_contains($script, 'package.runtime_intro') && str_contains($script, 'package.installed_for_review')
    && str_contains($script, 'package.toggle_confirmation') && str_contains($script, 'package.remove_confirmation'),
    'package administration does not use localized named messages');
i18nExpect($script !== false && str_contains($script, 'typeof page.parent === "string" && VIEWS[page.parent]')
    && str_contains($script, 'link.dataset.parent = parent'),
    'package dashboard pages cannot select a safe core navigation parent');
i18nExpect($script !== false && str_contains($script, 'page.title_key ? ZogographI18n.t')
    && str_contains($script, 'view.packagePage'),
    'package dashboard pages do not use signed locale keys or refresh after a locale change');
i18nExpect($setup !== false && str_contains($setup, 'src="i18n.js"') && str_contains($setup, 'id="languagePicker"')
    && str_contains($setup, 'id="languageKeys"') && str_contains($setup, 'data-i18n="setup.installation"')
    && str_contains($setup, "'setup.section.' . \$name") && str_contains($setup, "'setup.section.' . \$activeSection")
    && str_contains($setup, 'aria-current="page"') && str_contains($setup, 'data-setup-nav-results')
    && str_contains($setup, 'ZogographI18n?.t("update.compatibility"') && str_contains($setup, 'ZogographI18n?.t("action.reload"')
    && str_contains($setup, 'zogograph.setup.build.identity') && str_contains($setup, 'sessionStorage.getItem(storageKey)')
    && str_contains($setup, 'document.addEventListener("zogograph-i18n-load"'),
    'Setup language controls or localized shell are unavailable');
i18nExpect($setup !== false && str_contains($setup, 'item.dataset.setupNavTerms + " " + item.textContent'),
    'Setup navigation search ignores translated visible labels');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.document_title"'),
    'Setup document title is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.authorize"')
    && str_contains($setup, 'data-i18n="setup.unlock_to_view"') && str_contains($setup, 'data-i18n="privacy.masked_paths"')
    && str_contains($setup, 'data-i18n="privacy.rotate_mask"'),
    'Setup access and privacy panels are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.not_configured"')
    && str_contains($setup, 'data-i18n="setup.no_password_help"') && str_contains($setup, 'data-i18n="setup.configured_help"'),
    'Setup password-protection notices are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.validation_error"'),
    'Setup validation feedback is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.validation_detail"')
    && str_contains($setup, "json_encode(['detail' => (string)\$error]"),
    'Setup field-level validation details do not use localized named values');
i18nExpect($setup !== false && str_contains($setup, "'auth.password_minimum'")
    && is_array($english) && isset($english['auth.password_mismatch'], $english['auth.password_required_current']),
    'Setup password validation messages are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.error_detail"')
    && str_contains($setup, "json_encode(['detail' => \$detail]"),
    'Setup server-side error feedback does not use localized named values');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.saved_section"'),
    'Setup save confirmation is not localized');
i18nExpect($setup !== false && str_contains($setup, 'function setupFlashNotice')
    && str_contains($setup, 'data-i18n-values="') && str_contains($setup, "'deployment.target_verified'"),
    'Setup action feedback does not use localized flash notices');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.settings_saved"')
    && str_contains($setup, 'data-i18n="setup.first_readings"'),
    'Setup post-save guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.root_step"'),
    'Setup host-collector warning is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.root_install_help"'),
    'Setup host-collector installation guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.root_verify_help"'),
    'Setup host-collector verification guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.root_schedule_help"'),
    'Setup host-collector schedule boundary is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.save"'),
    'Setup generic save action is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.copy_config"')
    && str_contains($setup, 'data-i18n="setup.copy_config_help"'),
    'Setup configuration export guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="setup.new_mask_warning"'),
    'Setup configuration export mask warning is not localized');
i18nExpect($setup !== false && str_contains($setup, 'setupRenderPackageSettingsPanel')
    && str_contains($setup, 'data-package-settings-target') && str_contains($setup, 'save-package-settings:')
    && str_contains($setup, 'data-i18n="package.save_settings"') && str_contains($setup, 'data-i18n="package.no_reference"'),
    'Setup cannot render a signed package settings panel inside a core page');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="package.unavailable"')
    && str_contains($setup, "json_encode(['detail' => \$error->getMessage()]"),
    'Setup package-settings failure does not use localized named values');
i18nExpect($setup !== false && str_contains($setup, 'ZogographI18n?.t("package.fix_settings_json"'),
    'Setup package-settings JSON alert is not localized');
i18nExpect($setup !== false && !str_contains($setup, 'textarea[name=package_settings]')
    && substr_count($setup, 'document.querySelectorAll("[data-package-settings-target]")') === 1,
    'Setup package-secret selectors do not use one panel-specific handler');
i18nExpect($setup !== false && str_contains($setup, "(string)(\$panel['title_key'] ?? '')")
    && str_contains($setup, "(string)(\$panel['title_key'] ?? '')"),
    'Setup package panels cannot use signed locale catalog titles');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="schedule.intro"')
    && str_contains($setup, 'data-i18n="schedule.saved_preview"')
    && str_contains($setup, 'data-i18n="schedule.remove"')
    && str_contains($setup, 'data-i18n="schedule.no_php"') && str_contains($setup, '$copyKey = $sync ===')
    && str_contains((string)file_get_contents($root . '/lib/setup-cron-editor.php'), 'schedule.custom_builder')
    && str_contains((string)file_get_contents($root . '/lib/setup-cron-editor.php'), 'schedule.job.'),
    'Setup schedule controls are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="installation.checks"')
    && str_contains($setup, 'data-i18n="installation.detail"'),
    'Setup installation checks are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n-alt="auth.qr_alt"'),
    'Setup authenticator QR alternative text is not localized');
i18nExpect($setup !== false && str_contains($setup, "'auth.change_password' : 'auth.create_password'")
    && str_contains($setup, 'data-i18n="auth.new_password"') && str_contains($setup, 'data-i18n="auth.password_again"'),
    'Setup sign-in password form is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="encryption.private_key_exists"')
    && str_contains($setup, 'data-i18n="encryption.create_private_key"'),
    'Setup private-key provisioning is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="auth.open_account"')
    && str_contains($setup, 'data-i18n="auth.provisioning_uri"'),
    'Setup authenticator enrollment guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="openid.provider_intro"')
    && str_contains($setup, 'data-i18n="openid.secret_copy"') && str_contains($setup, 'data-i18n="openid.actions"'),
    'Setup OpenID security guidance is not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="deployment.copy_release"')
    && str_contains($setup, 'data-i18n="deployment.review_transport"')
    && str_contains($setup, 'data-i18n="deployment.method"') && str_contains($setup, 'data-i18n="deployment.update_manual"')
    && str_contains($setup, 'data-i18n="deployment.store_help"') && str_contains($setup, 'data-i18n="deployment.last_test_result"')
    && str_contains($setup, 'data-i18n-values='),
    'Setup deployment release controls are not localized');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="merge.review_expiry"')
    && str_contains($setup, 'data-i18n-values=') && str_contains($setup, 'data-i18n="merge.apply_help"')
    && str_contains($setup, 'data-i18n="merge.last_result"'),
    'Setup account-merge review cannot localize its values and guidance');
i18nExpect($configure !== false && str_contains($configure, 'function generatedFieldLabel')
    && str_contains($configure, "'label' => generatedFieldLabel(\$path)")
    && !str_contains($configure, "'label' => \$dotted"),
    'generated Setup fields can still expose dotted internal configuration paths');
i18nExpect($setup !== false && str_contains($setup, 'data-i18n="form.yes"'),
    'Setup boolean controls are not localized');
i18nExpect($setup !== false && str_contains($setup, "data-i18n=\"setup.field.' . h(\$key)")
    && is_array($english) && isset($english['setup.field.openid_enabled'], $english['setup.field.encryption_key_file'], $english['setup.field.host_keep_days']),
    'schema-driven Setup field labels do not have stable i18n keys and English fallbacks');
i18nExpect($setup !== false && str_contains($setup, "data-i18n=\"setup.option.' . h(\$key)")
    && is_array($english) && isset($english['setup.option.encryption_mode.none'], $english['setup.option.deletable_categories.trash']),
    'schema-driven Setup option labels do not have stable i18n keys and English fallbacks');
i18nExpect($setup !== false && str_contains($setup, "data-i18n=\"setup.help.' . h(\$key)")
    && is_array($english) && isset($english['setup.help.openid_enabled'], $english['setup.help.encryption_key_file'], $english['setup.help.host_keep_days']),
    'schema-driven Setup help text does not have stable i18n keys and English fallbacks');
i18nExpect(is_array($english) && isset($english['example.readings'], $english['example.compact'], $english['example.cost'], $english['example.percent'], $english['example.storage'], $english['example.changed'], $english['example.arabic_digits'], $english['example.eastern_arabic_digits'], $english['update.available'], $english['range.10m'], $english['action.reload'], $english['navigation.storage'], $english['navigation.cpu'], $english['navigation.deleted_held'], $english['navigation.cpu_limits'], $english['deployment.test'], $english['deployment.copy_release'], $english['deployment.method'], $english['deployment.update_manual'], $english['merge.apply'], $english['merge.review_expiry'], $english['merge.last_result'], $english['installation.checks'], $english['auth.qr_alt'], $english['auth.open_account'], $english['image.generated'], $english['package.save_settings'], $english['package.runtime_intro'], $english['package.installed_for_review'], $english['schedule.custom_builder'], $english['schedule.job.vitals'], $english['schedule.remove'], $english['setup.search_results'], $english['openid.provider_intro'], $english['openid.secret_copy']),
    'English fallback catalog is incomplete');
preg_match_all('/data-i18n(?:-placeholder|-title|-aria-label)?="([^"]+)"/', (string)$page, $pageKeys);
foreach (array_unique($pageKeys[1] ?? []) as $key) {
    i18nExpect(isset($english[$key]), "dashboard i18n attribute key $key has no English fallback");
}
$setupLocaleSources = (string)$setup . "\n" . (string)file_get_contents($root . '/lib/setup-cron-editor.php');
preg_match_all('/data-i18n(?:-placeholder|-title|-aria-label|-alt)?="([a-z][\\w.-]+)"/', $setupLocaleSources, $setupKeys);
foreach (array_unique($setupKeys[1] ?? []) as $key) {
    i18nExpect(isset($english[$key]), "setup i18n attribute key $key has no English fallback");
}
$translatedNavigation = ['navigation.deleted_held', 'navigation.cpu_limits', 'navigation.memory_by_user', 'navigation.memory_breakdown', 'navigation.memory_services', 'navigation.memory_processes', 'navigation.kernel_slab', 'navigation.services', 'navigation.units', 'navigation.last_busy', 'navigation.url_checks', 'navigation.open_ports', 'navigation.connected_peers', 'navigation.network_capture', 'navigation.network_captures', 'navigation.dns_lookups', 'navigation.domains', 'navigation.whois', 'navigation.fetch_page', 'navigation.dns_records', 'navigation.whoami', 'navigation.reverse_dns', 'navigation.assistant', 'navigation.assistant_identity', 'navigation.past_conversations', 'navigation.summaries', 'navigation.assistant_memory', 'navigation.ai_model', 'navigation.prices', 'navigation.thresholds', 'navigation.images', 'navigation.tools', 'navigation.try_tool', 'navigation.billing', 'navigation.assistant_data', 'navigation.terminal', 'navigation.sessions', 'navigation.host_capabilities', 'navigation.installed_software', 'navigation.changes', 'navigation.system', 'navigation.account_data', 'navigation.protection', 'navigation.host_data', 'navigation.data_retention', 'navigation.trusted_servers', 'navigation.marketplace', 'navigation.package_runtime', 'navigation.forensics', 'navigation.database_copies', 'navigation.recent_problems', 'navigation.email_alerts', 'navigation.account_scans', 'navigation.host_scans', 'navigation.custom_names', 'navigation.audit', 'navigation.descriptions'];
$translatedSecurityMessages = ['encryption.custody_notice', 'diagnosis.ignore_action', 'diagnosis.ignore_title', 'diagnosis.ignore_confirm', 'diagnosis.ignore_success'];
foreach (['en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'] as $locale) {
    $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
    i18nExpect(is_array($catalog), "locale catalog $locale is invalid");
    if (in_array($locale, ['es', 'hi', 'zh', 'ar'], true)) {
        i18nExpect(isset($catalog['navigation.charts'], $catalog['navigation.storage'], $catalog['navigation.backups'], $catalog['navigation.cpu'], $catalog['navigation.memory'], $catalog['navigation.processes'], $catalog['navigation.network'], $catalog['setup.section.deployment'], $catalog['setup.authorize'], $catalog['privacy.masked_paths'], $catalog['auth.app'], $catalog['auth.recovery_title'], $catalog['auth.verify'], $catalog['openid.provider'], $catalog['openid.create_client'], $catalog['schedule.custom_builder'], $catalog['schedule.remove']) && array_diff($translatedNavigation, array_keys($catalog)) === [],
            "locale catalog $locale lacks translated primary navigation");
        i18nExpect(array_diff(array_unique($setupKeys[1] ?? []), array_keys($catalog)) === [],
            "locale catalog $locale falls back to English for a visible Setup label");
        i18nExpect(array_diff($translatedSecurityMessages, array_keys($catalog)) === [],
            "locale catalog $locale lacks translated security-sensitive dashboard text");
    }
}
foreach (array_keys(array_filter($english, static fn(mixed $value, string $key): bool => str_starts_with($key, 'setup.field.'), ARRAY_FILTER_USE_BOTH)) as $key) {
    foreach (['es' => 'Spanish', 'hi' => 'Hindi', 'zh' => 'Chinese', 'ar' => 'Arabic'] as $locale => $language) {
        $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
    i18nExpect(is_array($catalog) && isset($catalog[$key]), "$language catalog lacks schema-driven Setup field $key");
    }
}
foreach (array_keys(array_filter($english, static fn(mixed $value, string $key): bool => str_starts_with($key, 'setup.help.'), ARRAY_FILTER_USE_BOTH)) as $key) {
    foreach (['es' => 'Spanish', 'hi' => 'Hindi', 'zh' => 'Chinese', 'ar' => 'Arabic'] as $locale => $language) {
        $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
        i18nExpect(is_array($catalog) && isset($catalog[$key]), "$language catalog lacks schema-driven Setup help $key");
    }
}
foreach (array_keys(array_filter($english, static fn(mixed $value, string $key): bool => str_starts_with($key, 'setup.option.'), ARRAY_FILTER_USE_BOTH)) as $key) {
    foreach (['es' => 'Spanish', 'hi' => 'Hindi', 'zh' => 'Chinese', 'ar' => 'Arabic'] as $locale => $language) {
        $catalog = json_decode((string)file_get_contents($root . '/locales/' . $locale . '.json'), true);
        i18nExpect(is_array($catalog) && isset($catalog[$key]), "$language catalog lacks schema-driven Setup option $key");
    }
}
foreach ($schemaFields as $field) {
    $fieldKey = (string)$field['key'];
    i18nExpect(isset($english['setup.field.' . $fieldKey]), "English catalog lacks schema-driven Setup field $fieldKey");
    if (isset($field['help'])) i18nExpect(isset($english['setup.help.' . $fieldKey]), "English catalog lacks schema-driven Setup help $fieldKey");
    foreach ((array)($field['options'] ?? []) as $value => $text) {
        $option = array_is_list((array)$field['options']) ? $text : $value;
        i18nExpect(isset($english['setup.option.' . $fieldKey . '.' . $option]), "English catalog lacks schema-driven Setup option $fieldKey.$option");
    }
}
foreach (['navigation.charts', 'navigation.storage', 'navigation.backups', 'navigation.cpu', 'navigation.memory', 'navigation.processes', 'navigation.network', 'navigation.deleted_held', 'navigation.cpu_limits'] as $key) {
    i18nExpect($page !== false && str_contains($page, 'data-i18n="' . $key . '"'), "dashboard navigation does not use $key");
}
echo "i18n checks passed\n";
