<?php
declare(strict_types=1);
function i18nExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
function i18nCardSource(string|false $source): string|false
{
    return $source === false ? false : str_replace("'", '"', $source);
}
$cardFiles = glob(dirname(__DIR__) . '/cards/*.js') ?: [];
foreach ($cardFiles as $cardFile) {
    $cardSource = file_get_contents($cardFile);
    preg_match("/id:\\s*'([^']+)'/", (string) $cardSource, $cardId);
    i18nExpect(
        $cardSource !== false &&
            str_contains($cardSource, 'catalogs: Object.freeze({') &&
            str_contains($cardSource, 'render(') &&
            str_contains($cardSource, "'i18n'") &&
            str_contains($cardSource, 'en: Object.freeze({') &&
            str_contains($cardSource, 'es: Object.freeze({') &&
            str_contains($cardSource, 'hi: Object.freeze({') &&
            str_contains($cardSource, 'ar: Object.freeze({') &&
            str_contains($cardSource, 'zh: Object.freeze({'),
        basename($cardFile) .
            ' must provide a render function and catalogs for every supported core locale',
    );
    i18nExpect(
        isset($cardId[1]) && str_contains($cardSource, "'" . $cardId[1] . '.'),
        basename($cardFile) . ' must expose a package-owned locale key',
    );
    i18nExpect(
        isset($cardId[1]) &&
            substr_count($cardSource, "'" . $cardId[1] . '.') >= 6,
        basename($cardFile) .
            ' must include package-owned copy in every locale catalog',
    );
    i18nExpect(
        isset($cardId[1]) &&
            str_contains($cardSource, "locales: ['" . $cardId[1] . ".']"),
        basename($cardFile) . ' must declare its own locale namespace',
    );
    i18nExpect(
        !str_contains($cardSource, 'aiSettingsText'),
        basename($cardFile) .
            ' must not depend on the root AI-settings locale formatter',
    );
    preg_match_all(
        '/(?:helpers\\.)?i18n\\.t\\(\\s*[\'\"]([^\'\"]+)/',
        (string) $cardSource,
        $cardLocaleCalls,
    );
    foreach ($cardLocaleCalls[1] ?? [] as $localeKey) {
        i18nExpect(
            isset($cardId[1]) && str_starts_with($localeKey, $cardId[1] . '.'),
            basename($cardFile) .
                ' must use its own locale namespace instead of the root catalog',
        );
    }
}
$root = dirname(__DIR__);
$runtime = file_get_contents($root . '/i18n.js');
$page = file_get_contents($root . '/index.html');
$script = file_get_contents($root . '/index.js');
foreach ($cardFiles as $cardFile) {
    $cardName = basename($cardFile);
    i18nExpect(
        $page !== false && str_contains($page, 'src="cards/' . $cardName . '"'),
        $cardName . ' is not registered by index.html',
    );
    i18nExpect(
        $page !== false &&
            substr_count($page, 'src="cards/' . $cardName . '"') === 1,
        $cardName . ' must be registered exactly once by index.html',
    );
}
if ($script !== false) {
    // Keep source-level contracts independent from the formatter's line wraps
    // and quote preference while retaining the original source for text checks.
    $script .=
        "\n" .
        preg_replace(
            '/\\(\\s+/',
            '(',
            preg_replace('/\\s+/', ' ', str_replace("'", '"', $script)),
        );
}
$overviewCards = i18nCardSource(
    file_get_contents($root . '/cards/overview-vitals.js'),
);
$scanFactCards = i18nCardSource(
    file_get_contents($root . '/cards/scan-facts.js'),
);
$typeGrowthCard = i18nCardSource(
    file_get_contents($root . '/cards/type-growth.js'),
);
$otherExtensionsCard = i18nCardSource(
    file_get_contents($root . '/cards/other-extensions.js'),
);
$cpuQueueCard = i18nCardSource(
    file_get_contents($root . '/cards/cpu-queue.js'),
);
$cpuGaugesCard = i18nCardSource(
    file_get_contents($root . '/cards/cpu-gauges.js'),
);
$networkGaugesCard = i18nCardSource(
    file_get_contents($root . '/cards/network-gauges.js'),
);
$networkInterfacesCard = i18nCardSource(
    file_get_contents($root . '/cards/network-interfaces.js'),
);
$networkPortsCard = i18nCardSource(
    file_get_contents($root . '/cards/network-ports.js'),
);
$networkCaptureRequestCard = i18nCardSource(
    file_get_contents($root . '/cards/network-capture-request.js'),
);
$networkCaptureHistoryCard = i18nCardSource(
    file_get_contents($root . '/cards/network-capture-history.js'),
);
$networkCaptureCard = i18nCardSource(
    file_get_contents($root . '/cards/network-capture.js'),
);
$networkRdnsCard = i18nCardSource(
    file_get_contents($root . '/cards/network-rdns.js'),
);
$processTrendCard = i18nCardSource(
    file_get_contents($root . '/cards/process-trend.js'),
);
$processListCard = i18nCardSource(
    file_get_contents($root . '/cards/process-list.js'),
);
$capabilitySummaryCard = i18nCardSource(
    file_get_contents($root . '/cards/capability-summary.js'),
);
$hostLogsCard = i18nCardSource(
    file_get_contents($root . '/cards/host-logs.js'),
);
$calendarUpcomingCard = i18nCardSource(
    file_get_contents($root . '/cards/calendar-upcoming.js'),
);
$calendarGridCard = i18nCardSource(
    file_get_contents($root . '/cards/calendar-grid.js'),
);
$calendarEventListCard = i18nCardSource(
    file_get_contents($root . '/cards/calendar-event-list.js'),
);
$reportsStatusCard = i18nCardSource(
    file_get_contents($root . '/cards/reports-status.js'),
);
$reportsEmptyCard = i18nCardSource(
    file_get_contents($root . '/cards/reports-empty.js'),
);
$reportsNotReadyCard = i18nCardSource(
    file_get_contents($root . '/cards/reports-not-ready.js'),
);
$reportsAuthorshipCard = i18nCardSource(
    file_get_contents($root . '/cards/reports-authorship.js'),
);
$reportRowCard = i18nCardSource(
    file_get_contents($root . '/cards/report-row.js'),
);
$fetchHeadersCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-headers.js'),
);
$fetchMetadataCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-metadata.js'),
);
$fetchJsonCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-json.js'),
);
$fetchLinkedDataCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-linked-data.js'),
);
$fetchPreviewCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-preview.js'),
);
$fetchRawCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-raw.js'),
);
$fetchTabsCard = i18nCardSource(
    file_get_contents($root . '/cards/fetch-tabs.js'),
);
$capabilityChangeNoteCard = i18nCardSource(
    file_get_contents($root . '/cards/capability-change-note.js'),
);
$jetBackupCard = i18nCardSource(
    file_get_contents($root . '/cards/jetbackup.js'),
);
$cagefsExplainerCard = i18nCardSource(
    file_get_contents($root . '/cards/cagefs-explainer.js'),
);
$viewGuideCard = i18nCardSource(
    file_get_contents($root . '/cards/view-guide.js'),
);
$notesEmptyCard = i18nCardSource(
    file_get_contents($root . '/cards/notes-empty.js'),
);
$changeHistoryCard = i18nCardSource(
    file_get_contents($root . '/cards/change-history.js'),
);
$installedSoftwareCard = i18nCardSource(
    file_get_contents($root . '/cards/installed-software.js'),
);
$pathHygieneCard = i18nCardSource(
    file_get_contents($root . '/cards/path-hygiene.js'),
);
$alertBannerCard = i18nCardSource(
    file_get_contents($root . '/cards/alert-banner.js'),
);
$broadcastBannerCard = i18nCardSource(
    file_get_contents($root . '/cards/broadcast-banner.js'),
);
$latestReportCard = i18nCardSource(
    file_get_contents($root . '/cards/latest-report.js'),
);
$databaseSnapshotsCard = i18nCardSource(
    file_get_contents($root . '/cards/database-snapshots.js'),
);
$hostDatabaseStorageCard = i18nCardSource(
    file_get_contents($root . '/cards/host-database-storage.js'),
);
$accountDatabaseStorageCard = i18nCardSource(
    file_get_contents($root . '/cards/account-database-storage.js'),
);
$diagnosisResultsCard = i18nCardSource(
    file_get_contents($root . '/cards/diagnosis-results.js'),
);
$pendingWorkCard = i18nCardSource(
    file_get_contents($root . '/cards/pending-work.js'),
);
$updateBannerCard = i18nCardSource(
    file_get_contents($root . '/cards/update-banner.js'),
);
$networkPeersCard = i18nCardSource(
    file_get_contents($root . '/cards/network-peers.js'),
);
$hostMemoryGaugesCard = i18nCardSource(
    file_get_contents($root . '/cards/host-memory-gauges.js'),
);
$overviewGaugesCard = i18nCardSource(
    file_get_contents($root . '/cards/overview-gauges.js'),
);
$actionSummaryCard = i18nCardSource(
    file_get_contents($root . '/cards/action-summary.js'),
);
$cpuFindingsCard = i18nCardSource(
    file_get_contents($root . '/cards/cpu-findings.js'),
);
$cpuCoresCard = i18nCardSource(
    file_get_contents($root . '/cards/cpu-cores.js'),
);
$cpuTimeCard = i18nCardSource(file_get_contents($root . '/cards/cpu-time.js'));
$cpuBusyChartCard = i18nCardSource(
    file_get_contents($root . '/cards/cpu-busy-chart.js'),
);
$hostMemoryCard = i18nCardSource(
    file_get_contents($root . '/cards/host-memory.js'),
);
$hostDisksCard = i18nCardSource(
    file_get_contents($root . '/cards/host-disks.js'),
);
$hostIoProcessesCard = i18nCardSource(
    file_get_contents($root . '/cards/host-io-processes.js'),
);
$terminalActionsCard = i18nCardSource(
    file_get_contents($root . '/cards/terminal-actions.js'),
);
$aiToolsToolbarCard = i18nCardSource(
    file_get_contents($root . '/cards/ai-tools-toolbar.js'),
);
$terminalVerdictSummaryCard = i18nCardSource(
    file_get_contents($root . '/cards/terminal-verdict-summary.js'),
);
$hostFilesCard = i18nCardSource(
    file_get_contents($root . '/cards/host-files.js'),
);
$hostDaemonsCard = i18nCardSource(
    file_get_contents($root . '/cards/host-daemons.js'),
);
$uptimeCard = i18nCardSource(file_get_contents($root . '/cards/uptime.js'));
$onboardingCard = i18nCardSource(
    file_get_contents($root . '/cards/onboarding.js'),
);
$setup = file_get_contents($root . '/setup.php');
$setupRaw = $setup;
if ($setup !== false) {
    $setup .=
        "\n" .
        preg_replace(
            '/\\(\\s+/',
            '(',
            preg_replace('/\\s+/', ' ', str_replace("'", '"', $setup)),
        );
}
$setupPanels = file_get_contents($root . '/lib/setup-panels.php');
$configure = file_get_contents($root . '/lib/configure.php');
$english = json_decode(
    (string) file_get_contents($root . '/locales/en.json'),
    true,
);
i18nExpect(
    !isset($english['storage.no_scan_finished_range']),
    'category chart empty state belongs to its card package',
);
i18nExpect(
    !isset(
        $english['ai.test_connection'],
        $english['ai.test_connection_title'],
        $english['ai.toolset_applied'],
        $english['ai.tool_column'],
        $english['ai.runs_column'],
        $english['ai.may_use_column'],
        $english['ai.ask_first_column'],
        $english['ai.tool_not_in_use'],
        $english['ai.tool_where_server'],
        $english['ai.tool_where_browser'],
        $english['ai.tool_where_acts'],
        $english['ai.tool_where_server_title'],
        $english['ai.tool_where_browser_title'],
        $english['ai.tool_where_acts_title'],
        $english['ai.tool_asks_anyway'],
        $english['ai.tool_asks_anyway_title'],
        $english['ai.recommended'],
        $english['ai.tool_description'],
        $english['ai.tool_description_title'],
        $english['ai.tool_description_help'],
        $english['ai.save_as_toolset'],
        $english['ai.toolset_title'],
        $english['ai.toolset_short_name'],
        $english['ai.toolset_name_hint'],
        $english['ai.toolset_label'],
        $english['ai.toolset_label_hint'],
        $english['ai.toolset_saved'],
        $english['ai.toolset_saved_label'],
        $english['ai.delete_toolset'],
        $english['ai.builtin_toolset'],
        $english['ai.delete_toolset_confirm'],
        $english['ai.delete_toolset_title'],
        $english['ai.toolset_deleted'],
        $english['ai.apply_toolset'],
        $english['ai.tool_subject_intro'],
        $english['ai.tool_group_tab'],
        $english['ai.tool_group_all_enabled'],
        $english['ai.tool_group_some_enabled'],
        $english['ai.tool_subject_all_enabled'],
        $english['ai.enable_subject'],
        $english['ai.subject_enabled_save'],
        $english['ai.tool_subject_available_after_save'],
        $english['ai.tools_title'],
        $english['ai.tools_help'],
        $english['ai.settings.provider_save_unavailable_config'],
        $english['ai.settings.provider_save_unavailable_role'],
        $english['ai.settings.provider_save_ready'],
        $english['ai.save_settings'],
        $english['ai.save_all_pages'],
        $english['ai.settings.config_read_only'],
        $english['ai.settings.role_read_only'],
        $english['ai.settings.not_ready'],
        $english['ai.pricing_empty'],
        $english['ai.pricing_current'],
        $english['ai.pricing_upcoming'],
        $english['ai.pricing_expired'],
        $english['ai.pricing_in_use'],
        $english['ai.pricing_input'],
        $english['ai.pricing_output'],
        $english['ai.pricing_cached'],
        $english['ai.prices_title'],
        $english['ai.save_assistant_pages'],
        $english['ai.clear_portrait'],
        $english['ai.portrait_cleared'],
    ),
    'AI card controls belong to their card packages',
);
i18nExpect(
    !isset(
        $english['storage.not_measured_yet'],
        $english['storage.watched_category'],
        $english['storage.types_categories_help'],
        $english['storage.types_help'],
    ),
    'type composition pie text belongs to its card package',
);
require_once $root . '/lib/configure.php';
require_once $root . '/lib/aitools.php';
$schemaFields = configFields(require $root . '/lib/config.sample.php');
$toolLocaleRoot = $root . '/lib/ai-tools/locales';
$toolLocaleDirectories =
    glob($toolLocaleRoot . '/core-ai-*', GLOB_ONLYDIR) ?: [];
$toolPackages = array_values(
    array_filter(
        array_map(
            static fn(array $tool): string => (string) ($tool['package'] ?? ''),
            aiToolCatalogue(),
        ),
        static fn(string $package): bool => $package !== '',
    ),
);
$toolLocalePackages = array_map('basename', $toolLocaleDirectories);
$toolEnglish = [];
$toolCatalogs = array_fill_keys(['en', 'es', 'hi', 'ar', 'zh'], []);
sort($toolPackages);
sort($toolLocalePackages);
i18nExpect(
    count($toolPackages) >= 1 && $toolPackages === $toolLocalePackages,
    'core AI tools and package-local locale directories do not match one-to-one',
);
foreach ($toolLocaleDirectories as $directory) {
    $package = basename($directory);
    $englishCatalog = null;
    foreach (['en', 'es', 'hi', 'ar', 'zh'] as $locale) {
        $file = $directory . '/' . $locale . '.json';
        i18nExpect(
            is_file($file),
            "AI tool package $package lacks its $locale locale catalog",
        );
        $catalog = json_decode((string) file_get_contents($file), true);
        i18nExpect(
            is_array($catalog) &&
                !array_is_list($catalog) &&
                count($catalog) >= 1,
            "AI tool package $package has an invalid $locale locale catalog",
        );
        foreach ($catalog as $key => $value) {
            i18nExpect(
                is_string($key) &&
                    str_starts_with($key, $package . '.') &&
                    is_string($value),
                "AI tool package $package owns an invalid $locale locale key",
            );
        }
        $toolCatalogs[$locale] = array_replace(
            $toolCatalogs[$locale],
            $catalog,
        );
        if ($locale === 'en') {
            $englishCatalog = $catalog;
            $toolEnglish = array_replace($toolEnglish, $catalog);
            continue;
        }
        i18nExpect(
            is_array($englishCatalog) &&
                array_keys($catalog) === array_keys($englishCatalog),
            "AI tool package $package has a $locale catalog that differs from English",
        );
    }
}
i18nExpect(
    isset(
        json_decode(
            (string) file_get_contents(
                $toolLocaleRoot . '/core-ai-get-current-status/en.json',
            ),
            true,
        )['core-ai-get-current-status.activity'],
        json_decode(
            (string) file_get_contents(
                $toolLocaleRoot . '/core-ai-get-measurement-history/en.json',
            ),
            true,
        )['core-ai-get-measurement-history.activity'],
        json_decode(
            (string) file_get_contents(
                $toolLocaleRoot . '/core-ai-open-page/en.json',
            ),
            true,
        )['core-ai-open-page.activity'],
        json_decode(
            (string) file_get_contents(
                $toolLocaleRoot . '/core-ai-request-tool-group/en.json',
            ),
            true,
        )['core-ai-request-tool-group.activity'],
    ),
    'chat tool activity remains owned by core AI tool packages',
);
$pluginManifests = glob($root . '/plugins/*/manifest.json') ?: [];
foreach ($pluginManifests as $manifestFile) {
    $manifest = json_decode((string) file_get_contents($manifestFile), true);
    $package = is_array($manifest) ? (string) ($manifest['id'] ?? '') : '';
    i18nExpect(
        $package !== '',
        "plugin manifest $manifestFile has no package ID",
    );
    $localeDirectory = dirname($manifestFile) . '/locales';
    foreach (['en', 'es', 'hi', 'ar', 'zh'] as $locale) {
        $file = $localeDirectory . '/' . $locale . '.json';
        i18nExpect(
            is_file($file),
            "plugin package $package lacks its $locale locale catalog",
        );
        $catalog = json_decode((string) file_get_contents($file), true);
        i18nExpect(
            is_array($catalog) &&
                !array_is_list($catalog) &&
                count($catalog) >= 1,
            "plugin package $package has an invalid $locale locale catalog",
        );
        foreach ($catalog as $key => $value) {
            i18nExpect(
                is_string($key) &&
                    str_starts_with($key, $package . '.') &&
                    is_string($value),
                "plugin package $package owns an invalid $locale locale key",
            );
        }
    }
}
foreach (glob($root . '/cards/*.js') ?: [] as $cardFile) {
    $card = file_get_contents($cardFile);
    $name = basename($cardFile);
    i18nExpect(
        $card !== false &&
            str_contains((string) $page, 'src="cards/' . $name . '"'),
        "core card package $name is not loaded by the dashboard",
    );
    i18nExpect(
        preg_match(
            '/manifest:\\s*Object\\.freeze\\(\\{\\s*id:\\s*[\\\'\"]core-[a-z0-9-]+[\\\'\"]\\s*,\\s*locales:\\s*\\[\\s*[\\\'\"][a-z][\\w-]*\\./s',
            $card,
        ) === 1 &&
            preg_match(
                '/helpers:\\s*\\[\\s*[\\\'\"][a-zA-Z][\\w]*[\\\'\"]/',
                $card,
            ) === 1 &&
            str_contains($card, 'catalogs: Object.freeze({'),
        "core card package $name lacks an ID, localization namespace, helper contract, or package catalog",
    );
    preg_match(
        '/manifest:\\s*Object\\.freeze\\(\\{\\s*id:\\s*[\\\'\"](core-[a-z0-9-]+)[\\\'\"]/s',
        (string) $card,
        $manifest,
    );
    $package = $manifest[1] ?? '';
    foreach (['en', 'es', 'hi', 'ar', 'zh'] as $locale) {
        i18nExpect(
            preg_match(
                '/\\b' . $locale . ':\\s*Object\\.freeze\\(/',
                (string) $card,
            ) === 1,
            "core card package $name lacks its $locale locale catalog",
        );
        i18nExpect(
            $package !== '' &&
                preg_match(
                    '/[\\\'\"]' . preg_quote($package, '/') . '\\./',
                    (string) $card,
                ) === 1,
            "core card package $name does not own namespaced locale keys",
        );
    }
}
i18nExpect(
    $runtime !== false &&
        str_contains($runtime, 'Intl.NumberFormat') &&
        str_contains($runtime, 'I18N_RTL') &&
        str_contains($runtime, 'zogograph.locale') &&
        str_contains($runtime, 'data-i18n') &&
        str_contains($runtime, 'registerCatalog') &&
        str_contains($runtime, 'i18nNodeValues') &&
        str_contains($runtime, 'latin-number') &&
        str_contains($runtime, 'arabic-number') &&
        str_contains($runtime, 'eastern-arabic-number') &&
        str_contains($runtime, "numberingSystem: 'arab'") &&
        str_contains($runtime, "numberingSystem: 'arabext'") &&
        str_contains($runtime, 'compact') &&
        str_contains($runtime, 'percent') &&
        str_contains($runtime, 'unit') &&
        str_contains($runtime, 'date-time') &&
        str_contains($runtime, 'currency') &&
        str_contains($runtime, 'i18nPlaceholder') &&
        str_contains($runtime, 'i18nAlt') &&
        str_contains($runtime, 'zogograph-i18n-load') &&
        str_contains($runtime, 'package-i18n'),
    'locale runtime lacks formatting, RTL, preference, or key-mode support',
);
i18nExpect(
    $runtime !== false &&
        str_contains($runtime, 'node.dataset.i18nFallback') &&
        str_contains($runtime, 't(key, i18nNodeValues(node), fallback)'),
    'locale runtime can expose untranslated navigation keys instead of server-rendered English',
);
i18nExpect(
    $runtime !== false &&
        str_contains($runtime, 'function i18nSetBrand(name)') &&
        str_contains(
            $runtime,
            'const allValues = { brand: i18nBrand, ...values }',
        ) &&
        str_contains($runtime, 'I18N_DEFAULT_BRAND') &&
        str_contains($runtime, 'I18N_DEFAULT_BRAND,') &&
        str_contains($runtime, "'{{brand}}'") &&
        str_contains($runtime, 'data-brand-template') &&
        str_contains($runtime, 'setBrand: i18nSetBrand') &&
        $script !== false &&
        str_contains($script, 'ZogographI18n.setBrand(overview.brand)') &&
        $setup !== false &&
        str_contains($setup, 'data-brand') &&
        $page !== false &&
        str_contains(
            $page,
            'data-brand-template="{{brand}} — infrastructure observation"',
        ) &&
        is_array($english) &&
        ($english['setup.document_title'] ?? '') === '{{brand}} — Setup',
    'product branding is not supplied consistently to dashboard and setup localization',
);
i18nExpect(
    $runtime !== false &&
        str_contains($runtime, 'Package catalogs are translation extensions') &&
        str_contains($runtime, 'if (!(key in catalog)) catalog[key] = value') &&
        str_contains($runtime, 'key.startsWith(prefix)') &&
        str_contains(
            $runtime,
            'for (const [packageId, catalogs] of Object.entries(data.catalogs || {}))',
        ) &&
        str_contains(
            $runtime,
            'async function i18nLoadPackageCatalogs(force = false)',
        ) &&
        str_contains($runtime, 'if (force) i18n.extensions = {}') &&
        str_contains($runtime, 'function i18nClearPackageCatalogs()') &&
        str_contains(
            $runtime,
            'clearPackageCatalogs: i18nClearPackageCatalogs',
        ),
    'package locale catalogs can overwrite core translation text',
);
i18nExpect(
    $runtime !== false &&
        str_contains(
            $runtime,
            'if (!reload && (locale === I18N_BASE || locale === i18n.locale))',
        ) &&
        str_contains($runtime, 'Object.assign(i18n.catalog, clean);') &&
        $script !== false &&
        str_contains($script, 'if (manifest.catalogs !== undefined)') &&
        str_contains(
            $script,
            'helpers.i18n.registerCatalog(locale, catalog, false, id)',
        ),
    'core card package catalogs are not namespace-registered before their first render',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/update-banner.js"') &&
        $updateBannerCard !== false &&
        str_contains($updateBannerCard, 'window.ZogographUpdateBannerCard') &&
        str_contains($updateBannerCard, 'id: "core-update-banner"') &&
        str_contains($updateBannerCard, 'locales: ["core-update-banner."]') &&
        str_contains($updateBannerCard, 'catalogs: Object.freeze({') &&
        str_contains($updateBannerCard, '"core-update-banner.available"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographUpdateBannerCard, "core-update-banner",',
        ),
    'update banner is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/pending-work.js"') &&
        $pendingWorkCard !== false &&
        str_contains($pendingWorkCard, 'window.ZogographPendingWorkCard') &&
        str_contains($pendingWorkCard, 'id: "core-pending-work"') &&
        str_contains($pendingWorkCard, 'locales: ["core-pending-work."]') &&
        str_contains($pendingWorkCard, 'catalogs: Object.freeze({') &&
        str_contains($pendingWorkCard, '"core-pending-work.waiting"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographPendingWorkCard, "core-pending-work",',
        ),
    'pending work is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/diagnosis-results.js"') &&
        $diagnosisResultsCard !== false &&
        str_contains(
            $diagnosisResultsCard,
            'window.ZogographDiagnosisResultsCard',
        ) &&
        str_contains($diagnosisResultsCard, 'id: "core-diagnosis-results"') &&
        str_contains(
            $diagnosisResultsCard,
            'locales: ["core-diagnosis-results."]',
        ) &&
        str_contains($diagnosisResultsCard, 'catalogs: Object.freeze({') &&
        str_contains($diagnosisResultsCard, '"core-diagnosis-results.none"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographDiagnosisResultsCard, "core-diagnosis-results",',
        ),
    'diagnosis results are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/account-database-storage.js"') &&
        $accountDatabaseStorageCard !== false &&
        str_contains(
            $accountDatabaseStorageCard,
            'window.ZogographAccountDatabaseStorageCard',
        ) &&
        str_contains(
            $accountDatabaseStorageCard,
            'id: "core-account-database-storage"',
        ) &&
        str_contains(
            $accountDatabaseStorageCard,
            'locales: ["core-account-database-storage."]',
        ) &&
        str_contains(
            $accountDatabaseStorageCard,
            'catalogs: Object.freeze({',
        ) &&
        str_contains(
            $accountDatabaseStorageCard,
            '"core-account-database-storage.title"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographAccountDatabaseStorageCard, "core-account-database-storage",',
        ),
    'account database storage is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-database-storage.js"') &&
        $hostDatabaseStorageCard !== false &&
        str_contains(
            $hostDatabaseStorageCard,
            'window.ZogographHostDatabaseStorageCard',
        ) &&
        str_contains(
            $hostDatabaseStorageCard,
            'id: "core-host-database-storage"',
        ) &&
        str_contains(
            $hostDatabaseStorageCard,
            'locales: ["core-host-database-storage."]',
        ) &&
        str_contains($hostDatabaseStorageCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $hostDatabaseStorageCard,
            '"core-host-database-storage.unreadable"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostDatabaseStorageCard, "core-host-database-storage",',
        ),
    'host database storage is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/database-snapshots.js"') &&
        $databaseSnapshotsCard !== false &&
        str_contains(
            $databaseSnapshotsCard,
            'window.ZogographDatabaseSnapshotsCard',
        ) &&
        str_contains($databaseSnapshotsCard, 'id: "core-database-snapshots"') &&
        str_contains(
            $databaseSnapshotsCard,
            'locales: ["core-database-snapshots."]',
        ) &&
        str_contains($databaseSnapshotsCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $databaseSnapshotsCard,
            '"core-database-snapshots.empty"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographDatabaseSnapshotsCard, "core-database-snapshots",',
        ),
    'database snapshots are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/latest-report.js"') &&
        $latestReportCard !== false &&
        str_contains($latestReportCard, 'window.ZogographLatestReportCard') &&
        str_contains($latestReportCard, 'id: "core-latest-report"') &&
        str_contains($latestReportCard, 'locales: ["core-latest-report."]') &&
        str_contains($latestReportCard, 'catalogs: Object.freeze({') &&
        str_contains($latestReportCard, '"core-latest-report.latest"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographLatestReportCard, "core-latest-report",',
        ),
    'latest report is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/broadcast-banner.js"') &&
        $broadcastBannerCard !== false &&
        str_contains(
            $broadcastBannerCard,
            'window.ZogographBroadcastBannerCard',
        ) &&
        str_contains($broadcastBannerCard, 'id: "core-broadcast-banner"') &&
        str_contains(
            $broadcastBannerCard,
            'locales: ["core-broadcast-banner."]',
        ) &&
        str_contains($broadcastBannerCard, 'catalogs: Object.freeze({') &&
        str_contains($broadcastBannerCard, '"core-broadcast-banner.one"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographBroadcastBannerCard, "core-broadcast-banner",',
        ),
    'broadcast banner is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/alert-banner.js"') &&
        $alertBannerCard !== false &&
        str_contains($alertBannerCard, 'window.ZogographAlertBannerCard') &&
        str_contains($alertBannerCard, 'id: "core-alert-banner"') &&
        str_contains($alertBannerCard, 'locales: ["core-alert-banner."]') &&
        str_contains($alertBannerCard, 'catalogs: Object.freeze({') &&
        str_contains($alertBannerCard, '"core-alert-banner.unread"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographAlertBannerCard, "core-alert-banner",',
        ),
    'alert banner is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/path-hygiene.js"') &&
        $pathHygieneCard !== false &&
        str_contains($pathHygieneCard, 'window.ZogographPathHygieneCard') &&
        str_contains($pathHygieneCard, 'id: "core-path-hygiene"') &&
        str_contains($pathHygieneCard, 'locales: ["core-path-hygiene."]') &&
        str_contains($pathHygieneCard, 'catalogs: Object.freeze({') &&
        str_contains($pathHygieneCard, '"core-path-hygiene.enable"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographPathHygieneCard, "core-path-hygiene",',
        ),
    'path hygiene panel is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/installed-software.js"') &&
        $installedSoftwareCard !== false &&
        str_contains(
            $installedSoftwareCard,
            'window.ZogographInstalledSoftwareCard',
        ) &&
        str_contains($installedSoftwareCard, 'id: "core-installed-software"') &&
        str_contains(
            $installedSoftwareCard,
            'locales: ["core-installed-software."]',
        ) &&
        str_contains($installedSoftwareCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $installedSoftwareCard,
            '"core-installed-software.system"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographInstalledSoftwareCard, "core-installed-software",',
        ),
    'installed software is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/change-history.js"') &&
        $changeHistoryCard !== false &&
        str_contains($changeHistoryCard, 'window.ZogographChangeHistoryCard') &&
        str_contains($changeHistoryCard, 'id: "core-change-history"') &&
        str_contains($changeHistoryCard, 'locales: ["core-change-history."]') &&
        str_contains($changeHistoryCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $changeHistoryCard,
            '"core-change-history.empty_checked"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographChangeHistoryCard, "core-change-history",',
        ),
    'change history is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-rdns.js"') &&
        $networkRdnsCard !== false &&
        str_contains($networkRdnsCard, 'window.ZogographNetworkRdnsCard') &&
        str_contains($networkRdnsCard, 'id: "core-network-rdns"') &&
        str_contains($networkRdnsCard, 'locales: ["core-network-rdns."]') &&
        str_contains($networkRdnsCard, 'catalogs: Object.freeze({') &&
        str_contains($networkRdnsCard, '"core-network-rdns.released"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkRdnsCard, "core-network-rdns",',
        ),
    'network reverse-DNS panel is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-capture.js"') &&
        $networkCaptureCard !== false &&
        str_contains(
            $networkCaptureCard,
            'window.ZogographNetworkCaptureCard',
        ) &&
        str_contains($networkCaptureCard, 'id: "core-network-capture"') &&
        str_contains(
            $networkCaptureCard,
            'locales: ["core-network-capture."]',
        ) &&
        str_contains($networkCaptureCard, 'catalogs: Object.freeze({') &&
        str_contains($networkCaptureCard, '"core-network-capture.flags_new"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkCaptureCard, "core-network-capture",',
        ),
    'network capture report is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/cpu-busy-chart.js"') &&
        $cpuBusyChartCard !== false &&
        str_contains($cpuBusyChartCard, 'window.ZogographCpuBusyChartCard') &&
        str_contains($cpuBusyChartCard, 'id: "core-cpu-busy-chart"') &&
        str_contains($cpuBusyChartCard, 'locales: ["core-cpu-busy-chart."]') &&
        str_contains($cpuBusyChartCard, '"core-cpu-busy-chart.empty"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCpuBusyChartCard, "core-cpu-busy-chart",',
        ),
    'CPU busy chart is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/calendar-grid.js"') &&
        $calendarGridCard !== false &&
        str_contains($calendarGridCard, 'window.ZogographCalendarGridCard') &&
        str_contains($calendarGridCard, 'id: "core-calendar-grid"') &&
        str_contains($calendarGridCard, 'locales: ["core-calendar-grid."]') &&
        str_contains($calendarGridCard, 'catalogs: Object.freeze({') &&
        str_contains($calendarGridCard, '"core-calendar-grid.events_on_day"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCalendarGridCard, "core-calendar-grid",',
        ),
    'calendar grid is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/calendar-upcoming.js"') &&
        $calendarUpcomingCard !== false &&
        str_contains(
            $calendarUpcomingCard,
            'window.ZogographCalendarUpcomingCard',
        ) &&
        str_contains($calendarUpcomingCard, 'id: "core-calendar-upcoming"') &&
        str_contains(
            $calendarUpcomingCard,
            'locales: ["core-calendar-upcoming."]',
        ) &&
        str_contains($calendarUpcomingCard, 'catalogs: Object.freeze({') &&
        str_contains($calendarUpcomingCard, '"core-calendar-upcoming.empty"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCalendarUpcomingCard, "core-calendar-upcoming",',
        ),
    'calendar upcoming list is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/calendar-event-list.js"') &&
        $calendarEventListCard !== false &&
        str_contains(
            $calendarEventListCard,
            'window.ZogographCalendarEventListCard',
        ) &&
        str_contains(
            $calendarEventListCard,
            'id: "core-calendar-event-list"',
        ) &&
        str_contains(
            $calendarEventListCard,
            'locales: ["core-calendar-event-list."]',
        ) &&
        str_contains($calendarEventListCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $calendarEventListCard,
            '"core-calendar-event-list.empty"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCalendarEventListCard, "core-calendar-event-list",',
        ),
    'calendar event list is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/reports-status.js"') &&
        str_contains($page, 'src="cards/reports-empty.js"') &&
        str_contains($page, 'src="cards/reports-not-ready.js"') &&
        str_contains($page, 'src="cards/reports-authorship.js"') &&
        str_contains($page, 'src="cards/report-row.js"') &&
        str_contains($reportsStatusCard, 'core-reports-status.on') &&
        str_contains($reportsEmptyCard, 'core-reports-empty.text') &&
        str_contains($reportsNotReadyCard, 'core-reports-not-ready.text') &&
        str_contains($reportsAuthorshipCard, 'core-reports-authorship.text') &&
        str_contains($reportRowCard, 'core-report-row.period_summary') &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographReportsStatusCard',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographReportsEmptyCard',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographReportsNotReadyCard',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographReportsAuthorshipCard',
        ) &&
        str_contains($script, 'ZogographReportRowCard,'),
    'report page cards are not isolated behind core card-module boundaries',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-headers.js"') &&
        str_contains($fetchHeadersCard, 'window.ZogographFetchHeadersCard') &&
        str_contains($fetchHeadersCard, 'id: "core-fetch-headers"') &&
        str_contains($fetchHeadersCard, 'locales: ["core-fetch-headers."]') &&
        str_contains($fetchHeadersCard, 'catalogs: Object.freeze({') &&
        str_contains($fetchHeadersCard, '"core-fetch-headers.help"') &&
        str_contains($script, 'renderCoreCardPackage(') &&
        str_contains($script, 'ZogographFetchHeadersCard,'),
    'fetch headers are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-metadata.js"') &&
        str_contains($fetchMetadataCard, 'window.ZogographFetchMetadataCard') &&
        str_contains($fetchMetadataCard, 'id: "core-fetch-metadata"') &&
        str_contains($fetchMetadataCard, 'locales: ["core-fetch-metadata."]') &&
        str_contains($fetchMetadataCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $fetchMetadataCard,
            '"core-fetch-metadata.what_it_does"',
        ) &&
        str_contains($script, 'ZogographFetchMetadataCard,'),
    'fetch metadata are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-json.js"') &&
        str_contains($fetchJsonCard, 'window.ZogographFetchJsonCard') &&
        str_contains($fetchJsonCard, 'id: "core-fetch-json"') &&
        str_contains($fetchJsonCard, 'locales: ["core-fetch-json."]') &&
        str_contains($fetchJsonCard, 'catalogs: Object.freeze({') &&
        str_contains($fetchJsonCard, '"core-fetch-json.truncated"') &&
        str_contains($script, 'ZogographFetchJsonCard,'),
    'fetch JSON is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-linked-data.js"') &&
        str_contains(
            $fetchLinkedDataCard,
            'window.ZogographFetchLinkedDataCard',
        ) &&
        str_contains($fetchLinkedDataCard, 'id: "core-fetch-linked-data"') &&
        str_contains(
            $fetchLinkedDataCard,
            'locales: ["core-fetch-linked-data."]',
        ) &&
        str_contains($fetchLinkedDataCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $fetchLinkedDataCard,
            '"core-fetch-linked-data.untyped_entity"',
        ) &&
        str_contains($script, 'ZogographFetchLinkedDataCard,'),
    'fetch structured data are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-preview.js"') &&
        str_contains($fetchPreviewCard, 'window.ZogographFetchPreviewCard') &&
        str_contains($fetchPreviewCard, 'id: "core-fetch-preview"') &&
        str_contains($fetchPreviewCard, 'locales: ["core-fetch-preview."]') &&
        str_contains($fetchPreviewCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $fetchPreviewCard,
            '"core-fetch-preview.sandbox_notice"',
        ) &&
        str_contains($script, 'ZogographFetchPreviewCard,'),
    'fetch preview is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-raw.js"') &&
        str_contains($fetchRawCard, 'window.ZogographFetchRawCard') &&
        str_contains($fetchRawCard, 'id: "core-fetch-raw"') &&
        str_contains($fetchRawCard, 'locales: ["core-fetch-raw."]') &&
        str_contains($fetchRawCard, 'catalogs: Object.freeze({') &&
        str_contains($fetchRawCard, '"core-fetch-raw.empty"') &&
        str_contains($script, 'ZogographFetchRawCard,'),
    'fetch raw output is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/fetch-tabs.js"') &&
        str_contains($fetchTabsCard, 'window.ZogographFetchTabsCard') &&
        str_contains($fetchTabsCard, 'id: "core-fetch-tabs"') &&
        str_contains($fetchTabsCard, 'locales: ["core-fetch-tabs."]') &&
        str_contains($fetchTabsCard, 'catalogs: Object.freeze({') &&
        str_contains($fetchTabsCard, '"core-fetch-tabs.visit_title"') &&
        str_contains($script, 'ZogographFetchTabsCard,'),
    'fetch view tabs are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/capability-change-note.js"') &&
        str_contains(
            $capabilityChangeNoteCard,
            'window.ZogographCapabilityChangeNoteCard',
        ) &&
        str_contains(
            $capabilityChangeNoteCard,
            'id: "core-capability-change-note"',
        ) &&
        str_contains(
            $capabilityChangeNoteCard,
            'locales: ["core-capability-change-note."]',
        ) &&
        str_contains($capabilityChangeNoteCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $capabilityChangeNoteCard,
            '"core-capability-change-note.truncated"',
        ) &&
        str_contains($script, 'ZogographCapabilityChangeNoteCard,'),
    'capability change note is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/view-guide.js"') &&
        str_contains($viewGuideCard, 'window.ZogographViewGuideCard') &&
        str_contains($viewGuideCard, 'id: "core-view-guide"') &&
        str_contains($viewGuideCard, 'locales: ["core-view-guide."]') &&
        str_contains($viewGuideCard, 'catalogs: Object.freeze({') &&
        str_contains($viewGuideCard, '"core-view-guide.sign_in_help"') &&
        str_contains($script, 'ZogographViewGuideCard,'),
    'view access guide is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/jetbackup.js"') &&
        str_contains($jetBackupCard, 'window.ZogographJetBackupCard') &&
        str_contains($jetBackupCard, 'id: "core-jetbackup"') &&
        str_contains($jetBackupCard, 'locales: ["core-jetbackup."]') &&
        str_contains($jetBackupCard, 'catalogs: Object.freeze({') &&
        str_contains($jetBackupCard, '"core-jetbackup.known"') &&
        str_contains($script, 'ZogographJetBackupCard,'),
    'JetBackup is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        $script !== false &&
        str_contains($page, 'src="cards/cagefs-explainer.js"') &&
        str_contains(
            $cagefsExplainerCard,
            'window.ZogographCagefsExplainerCard',
        ) &&
        str_contains($cagefsExplainerCard, 'id: "core-cagefs-explainer"') &&
        str_contains(
            $cagefsExplainerCard,
            'locales: ["core-cagefs-explainer."]',
        ) &&
        str_contains($cagefsExplainerCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $cagefsExplainerCard,
            '"core-cagefs-explainer.private_view"',
        ) &&
        str_contains($script, 'ZogographCagefsExplainerCard,'),
    'CageFS explainer is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-logs.js"') &&
        $hostLogsCard !== false &&
        str_contains($hostLogsCard, 'window.ZogographHostLogsCard') &&
        str_contains($hostLogsCard, 'id: "core-host-logs"') &&
        str_contains($hostLogsCard, 'locales: ["core-host-logs."]') &&
        str_contains($hostLogsCard, 'catalogs: Object.freeze({') &&
        str_contains($hostLogsCard, '"core-host-logs.refused_help"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostLogsCard, "core-host-logs",',
        ),
    'host logs are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/capability-summary.js"') &&
        $capabilitySummaryCard !== false &&
        str_contains(
            $capabilitySummaryCard,
            'window.ZogographCapabilitySummaryCard',
        ) &&
        str_contains($capabilitySummaryCard, 'id: "core-capability-summary"') &&
        str_contains(
            $capabilitySummaryCard,
            'locales: ["core-capability-summary."]',
        ) &&
        str_contains($capabilitySummaryCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $capabilitySummaryCard,
            '"core-capability-summary.worth_having"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCapabilitySummaryCard, "core-capability-summary",',
        ),
    'capability summary is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/process-trend.js"') &&
        $processTrendCard !== false &&
        str_contains($processTrendCard, 'window.ZogographProcessTrendCard') &&
        str_contains($processTrendCard, 'id: "core-process-trend"') &&
        str_contains($processTrendCard, 'locales: ["core-process-trend."]') &&
        str_contains($processTrendCard, 'catalogs: Object.freeze({') &&
        str_contains($processTrendCard, '"core-process-trend.summary"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographProcessTrendCard, "core-process-trend",',
        ),
    'process trend is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-capture-history.js"') &&
        $networkCaptureHistoryCard !== false &&
        str_contains(
            $networkCaptureHistoryCard,
            'window.ZogographNetworkCaptureHistoryCard',
        ) &&
        str_contains(
            $networkCaptureHistoryCard,
            'id: "core-network-capture-history"',
        ) &&
        str_contains(
            $networkCaptureHistoryCard,
            'locales: ["core-network-capture-history."]',
        ) &&
        str_contains($networkCaptureHistoryCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $networkCaptureHistoryCard,
            '"core-network-capture-history.empty"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkCaptureHistoryCard, "core-network-capture-history",',
        ),
    'network capture history is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-capture-request.js"') &&
        $networkCaptureRequestCard !== false &&
        str_contains(
            $networkCaptureRequestCard,
            'window.ZogographNetworkCaptureRequestCard',
        ) &&
        str_contains(
            $networkCaptureRequestCard,
            'id: "core-network-capture-request"',
        ) &&
        str_contains(
            $networkCaptureRequestCard,
            'locales: ["core-network-capture-request."]',
        ) &&
        str_contains($networkCaptureRequestCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $networkCaptureRequestCard,
            '"core-network-capture-request.read_asked_warning"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkCaptureRequestCard, "core-network-capture-request",',
        ),
    'network capture request is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-ports.js"') &&
        $networkPortsCard !== false &&
        str_contains($networkPortsCard, 'window.ZogographNetworkPortsCard') &&
        str_contains($networkPortsCard, 'id: "core-network-ports"') &&
        str_contains($networkPortsCard, 'locales: ["core-network-ports."]') &&
        str_contains($networkPortsCard, 'catalogs: Object.freeze({') &&
        str_contains($networkPortsCard, '"core-network-ports.public_empty"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkPortsCard, "core-network-ports",',
        ),
    'network ports are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-interfaces.js"') &&
        $networkInterfacesCard !== false &&
        str_contains(
            $networkInterfacesCard,
            'window.ZogographNetworkInterfacesCard',
        ) &&
        str_contains($networkInterfacesCard, 'id: "core-network-interfaces"') &&
        str_contains(
            $networkInterfacesCard,
            'locales: ["core-network-interfaces."]',
        ) &&
        str_contains($networkInterfacesCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $networkInterfacesCard,
            '"core-network-interfaces.empty"',
        ) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkInterfacesCard, "core-network-interfaces",',
        ),
    'network interfaces are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'id="languagePicker"') &&
        str_contains($page, 'id="languageKeys"') &&
        str_contains($page, 'id="languageHighlight"') &&
        str_contains($page, 'src="i18n.js"'),
    'dashboard language controls are unavailable',
);
i18nExpect(
    $script !== false &&
        str_contains(
            $script,
            'new Intl.NumberFormat(document.documentElement.lang || undefined,',
        ) &&
        str_contains(
            $script,
            "toLocaleString(\n    document.documentElement.lang || undefined,",
        ) &&
        str_contains(
            $script,
            "document.addEventListener('zogograph-i18n-load'",
        ) &&
        str_contains($script, 'view.manual') &&
        str_contains($script, 'function syncViewLabelsFromNavigation()') &&
        str_contains($script, 'syncViewLabelsFromNavigation();') &&
        str_contains($script, 'function renderViewTitles(view)') &&
        str_contains($script, "ZogographI18n.t(\n    'page.document_title'") &&
        str_contains($script, "'page.title'"),
    'dashboard value formatting ignores the selected locale or refreshes manual views',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function humanBytes(value, digits)') &&
        str_contains($script, 'minimumFractionDigits: places') &&
        str_contains($script, 'maximumFractionDigits: places'),
    'human-readable byte values do not honor the selected locale',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'manifest.locales.length === 0') &&
        str_contains($script, 'manifest.locales.every(') &&
        str_contains($script, "typeof prefix === 'string'"),
    'core card packages can omit a declared localization namespace',
);
i18nExpect(
    $script !== false && substr_count($script, 'setHostCard(') === 8,
    'a host summary card bypasses the core card-package boundary',
);
i18nExpect(
    $script !== false && substr_count($script, 'setCard(') === 4,
    'a dashboard summary card bypasses the core card-package boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/overview-vitals.js"') &&
        $overviewCards !== false &&
        str_contains($overviewCards, 'window.ZogographOverviewVitalsCards') &&
        str_contains($overviewCards, 'id: "core-overview-vitals"') &&
        str_contains($overviewCards, 'locales: ["core-overview-vitals."]') &&
        str_contains($overviewCards, 'render(overview, helpers)') &&
        $script !== false &&
        str_contains($script, 'renderCoreCardPackage(') &&
        str_contains($script, 'ZogographOverviewVitalsCards') &&
        str_contains($script, "'core-overview-vitals'") &&
        str_contains($script, 'overview,'),
    'overview vitals cards are not isolated behind their core card-module boundary',
);
i18nExpect(
    $overviewCards !== false &&
        str_contains($overviewCards, 'catalogs: Object.freeze({') &&
        str_contains(
            $overviewCards,
            '"core-overview-vitals.nothing_collected"',
        ),
    'overview vitals card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/scan-facts.js"') &&
        $scanFactCards !== false &&
        str_contains($scanFactCards, 'window.ZogographScanFactCards') &&
        str_contains($scanFactCards, 'id: "core-scan-facts"') &&
        str_contains($scanFactCards, 'locales: ["core-scan-facts."]') &&
        str_contains($scanFactCards, 'render(scans, helpers)') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographScanFactCards, "core-scan-facts", scans,',
        ),
    'storage scan-fact cards are not isolated behind their core card-module boundary',
);
i18nExpect(
    $scanFactCards !== false &&
        str_contains($scanFactCards, 'catalogs: Object.freeze({') &&
        str_contains($scanFactCards, '"core-scan-facts.first_scan_running"') &&
        is_array($english) &&
        !isset($english['storage.first_scan_running']),
    'storage scan-fact card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/type-growth.js"') &&
        $typeGrowthCard !== false &&
        str_contains($typeGrowthCard, 'window.ZogographTypeGrowthCard') &&
        str_contains($typeGrowthCard, 'id: "core-type-growth"') &&
        str_contains($typeGrowthCard, 'locales: ["core-type-growth."]') &&
        str_contains($typeGrowthCard, 'render(scans, helpers)') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographTypeGrowthCard, "core-type-growth", scans,',
        ),
    'storage type-growth chart is not isolated behind its core card-module boundary',
);
i18nExpect(
    $typeGrowthCard !== false &&
        str_contains($typeGrowthCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $typeGrowthCard,
            '"core-type-growth.no_scans_in_range_yet"',
        ) &&
        is_array($english) &&
        !isset($english['storage.no_scans_in_range_yet']),
    'storage type-growth card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/other-extensions.js"') &&
        $otherExtensionsCard !== false &&
        str_contains(
            $otherExtensionsCard,
            'window.ZogographOtherExtensionsCard',
        ) &&
        str_contains($otherExtensionsCard, 'id: "core-other-extensions"') &&
        str_contains(
            $otherExtensionsCard,
            'locales: ["core-other-extensions."]',
        ) &&
        str_contains($otherExtensionsCard, 'render(rows, helpers)') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographOtherExtensionsCard, "core-other-extensions", rows,',
        ),
    'storage other-extensions table is not isolated behind its core card-module boundary',
);
i18nExpect(
    $otherExtensionsCard !== false &&
        str_contains($otherExtensionsCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $otherExtensionsCard,
            '"core-other-extensions.empty_before"',
        ) &&
        is_array($english) &&
        !isset($english['storage.other_empty_before'], $english['chart.other']),
    'storage other-extensions card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/cpu-queue.js"') &&
        $cpuQueueCard !== false &&
        str_contains($cpuQueueCard, 'window.ZogographCpuQueueCard') &&
        str_contains($cpuQueueCard, 'id: "core-cpu-queue"') &&
        str_contains($cpuQueueCard, 'locales: ["core-cpu-queue."]') &&
        str_contains($cpuQueueCard, 'render(input, helpers)') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCpuQueueCard, "core-cpu-queue", { now, sample },',
        ),
    'CPU queue cards are not isolated behind their core card-module boundary',
);
i18nExpect(
    $cpuQueueCard !== false &&
        str_contains($cpuQueueCard, 'catalogs: Object.freeze({') &&
        str_contains($cpuQueueCard, '"core-cpu-queue.wanting_to_run"') &&
        str_contains($cpuQueueCard, '"core-cpu-queue.queue_verdict_storage"') &&
        is_array($english) &&
        !isset(
            $english['cpu.wanting_to_run'],
            $english['cpu.queue_verdict_storage'],
        ),
    'CPU queue card does not own its chart and diagnostic locale catalog',
);
i18nExpect(
    $cpuFindingsCard !== false &&
        str_contains($cpuFindingsCard, 'locales: ["core-cpu-findings."]') &&
        str_contains(
            $cpuFindingsCard,
            '"core-cpu-findings.nothing_to_report"',
        ) &&
        is_array($english) &&
        !isset($english['cpu.nothing_to_report']),
    'CPU findings card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/cpu-gauges.js"') &&
        $cpuGaugesCard !== false &&
        str_contains($cpuGaugesCard, 'window.ZogographCpuGaugesCard') &&
        str_contains($cpuGaugesCard, 'id: "core-cpu-gauges"') &&
        str_contains($cpuGaugesCard, 'locales: ["core-cpu-gauges."]') &&
        str_contains(
            $cpuGaugesCard,
            'helpers: ["target", "buildGauge", "count", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCpuGaugesCard, "core-cpu-gauges",',
        ) &&
        str_contains(
            $script,
            'sample: data.sample, accountVitals: state.overview?.vitals ?? {}',
        ),
    'CPU gauges are not isolated behind their core card-module boundary',
);
i18nExpect(
    $cpuGaugesCard !== false &&
        str_contains($cpuGaugesCard, 'catalogs: Object.freeze({') &&
        str_contains($cpuGaugesCard, '"core-cpu-gauges.host_cpu"') &&
        is_array($english) &&
        !isset($english['cpu.gauge_host_cpu']),
    'CPU gauges do not own their locale catalog exclusively',
);
i18nExpect(
    $cpuTimeCard !== false &&
        str_contains($cpuTimeCard, 'locales: ["core-cpu-time."]') &&
        str_contains($cpuTimeCard, '"core-cpu-time.average_over_range"') &&
        is_array($english) &&
        !isset($english['cpu.average_over_range']),
    'CPU time card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-gauges.js"') &&
        $networkGaugesCard !== false &&
        str_contains($networkGaugesCard, 'window.ZogographNetworkGaugesCard') &&
        str_contains($networkGaugesCard, 'id: "core-network-gauges"') &&
        str_contains($networkGaugesCard, 'locales: ["core-network-gauges."]') &&
        str_contains(
            $networkGaugesCard,
            'helpers: ["target", "buildGauge", "humanBytes", "humanCount", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkGaugesCard, "core-network-gauges", data,',
        ),
    'network gauges are not isolated behind their core card-module boundary',
);
i18nExpect(
    $networkGaugesCard !== false &&
        str_contains($networkGaugesCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $networkGaugesCard,
            '"core-network-gauges.rate_on_interface"',
        ) &&
        is_array($english) &&
        !isset($english['network.rate_on_interface']),
    'network gauges do not own their locale catalog exclusively',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-memory-gauges.js"') &&
        $hostMemoryGaugesCard !== false &&
        str_contains(
            $hostMemoryGaugesCard,
            'window.ZogographHostMemoryGaugesCard',
        ) &&
        str_contains($hostMemoryGaugesCard, 'id: "core-host-memory-gauges"') &&
        str_contains(
            $hostMemoryGaugesCard,
            'locales: ["core-host-memory-gauges."]',
        ) &&
        str_contains(
            $hostMemoryGaugesCard,
            'helpers: ["target", "buildGauge", "humanBytes", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostMemoryGaugesCard, "core-host-memory-gauges",',
        ),
    'host memory gauges are not isolated behind their core card-module boundary',
);
i18nExpect(
    $hostMemoryGaugesCard !== false &&
        str_contains($hostMemoryGaugesCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $hostMemoryGaugesCard,
            '"core-host-memory-gauges.collector_arc"',
        ) &&
        is_array($english) &&
        !isset($english['host.memory_collector_arc']),
    'host memory gauges do not own their locale catalog exclusively',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/overview-gauges.js"') &&
        $overviewGaugesCard !== false &&
        str_contains(
            $overviewGaugesCard,
            'window.ZogographOverviewGaugesCard',
        ) &&
        str_contains($overviewGaugesCard, 'id: "core-overview-gauges"') &&
        str_contains(
            $overviewGaugesCard,
            'locales: ["core-overview-gauges."]',
        ) &&
        str_contains(
            $overviewGaugesCard,
            'helpers: ["target", "buildGauge", "gaugeSeries", "humanBytes", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographOverviewGaugesCard, "core-overview-gauges",',
        ),
    'overview gauges are not isolated behind their core card-module boundary',
);
i18nExpect(
    $overviewGaugesCard !== false &&
        str_contains($overviewGaugesCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $overviewGaugesCard,
            '"core-overview-gauges.nothing_collected"',
        ),
    'overview gauges do not own a package locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/action-summary.js"') &&
        $actionSummaryCard !== false &&
        str_contains($actionSummaryCard, 'window.ZogographActionSummaryCard') &&
        str_contains($actionSummaryCard, 'id: "core-action-summary"') &&
        str_contains($actionSummaryCard, 'locales: ["core-action-summary."]') &&
        str_contains(
            $actionSummaryCard,
            'helpers: ["target", "humanBytes", "humanTime", "count", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographActionSummaryCard, "core-action-summary", rows,',
        ),
    'action summary is not isolated behind its core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/cpu-findings.js"') &&
        $cpuFindingsCard !== false &&
        str_contains($cpuFindingsCard, 'window.ZogographCpuFindingsCard') &&
        str_contains($cpuFindingsCard, 'id: "core-cpu-findings"') &&
        str_contains($cpuFindingsCard, 'locales: ["core-cpu-findings."]') &&
        str_contains(
            $cpuFindingsCard,
            'helpers: ["target", "findingRow", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCpuFindingsCard, "core-cpu-findings", data.findings ?? [],',
        ),
    'CPU findings are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/cpu-cores.js"') &&
        $cpuCoresCard !== false &&
        str_contains($cpuCoresCard, 'window.ZogographCpuCoresCard') &&
        str_contains($cpuCoresCard, 'id: "core-cpu-cores"') &&
        str_contains($cpuCoresCard, 'locales: ["core-cpu-cores."]') &&
        str_contains(
            $cpuCoresCard,
            'helpers: ["target", "buildGauge", "cpuCoreShare", "i18n"]',
        ) &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographCpuCoresCard, "core-cpu-cores", data.cores ?? [],',
        ),
    'CPU core gauges are not isolated behind their core card-module boundary',
);
i18nExpect(
    $cpuCoresCard !== false &&
        str_contains($cpuCoresCard, 'catalogs: Object.freeze({') &&
        str_contains($cpuCoresCard, '"core-cpu-cores.no_per_core_lines"') &&
        is_array($english) &&
        !isset($english['cpu.no_per_core_lines']),
    'CPU core card does not own its locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-memory.js"') &&
        $hostMemoryCard !== false &&
        str_contains($hostMemoryCard, 'window.ZogographHostMemoryCard') &&
        str_contains($hostMemoryCard, 'id: "core-host-memory"') &&
        str_contains($hostMemoryCard, 'locales: ["core-host-memory."]') &&
        str_contains($hostMemoryCard, '"note"') &&
        str_contains($hostMemoryCard, '"humanCount"') &&
        str_contains($hostMemoryCard, '"count"') &&
        str_contains($hostMemoryCard, '"num"') &&
        str_contains($hostMemoryCard, 'i18n.t(`core-host-memory.${key}`') &&
        str_contains($hostMemoryCard, '"core-host-memory.in_use"') &&
        is_array($english) &&
        isset($english['host.memory_card_in_use']) &&
        !isset($english['host.memory_summary_counts']) &&
        str_contains($hostMemoryCard, '"core-host-memory.summary_counts"') &&
        str_contains($hostMemoryCard, 'render(input, helpers)') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostMemoryCard, "core-host-memory", {',
        ),
    'host memory cards are not isolated behind their core card-module boundary',
);
i18nExpect(
    $hostMemoryCard !== false &&
        str_contains($hostMemoryCard, 'catalogs: Object.freeze({') &&
        str_contains(
            $hostMemoryCard,
            '"core-host-memory.monitoring_footprint"',
        ),
    'host memory card does not own a package locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/network-peers.js"') &&
        $networkPeersCard !== false &&
        str_contains($networkPeersCard, 'window.ZogographNetworkPeersCard') &&
        str_contains($networkPeersCard, 'id: "core-network-peers"') &&
        str_contains($networkPeersCard, 'locales: ["core-network-peers."]') &&
        str_contains($networkPeersCard, 'catalogs: Object.freeze({') &&
        str_contains($networkPeersCard, '"core-network-peers.empty"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographNetworkPeersCard, "core-network-peers",',
        ),
    'network peers are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-disks.js"') &&
        $hostDisksCard !== false &&
        str_contains($hostDisksCard, 'window.ZogographHostDisksCard') &&
        str_contains($hostDisksCard, 'id: "core-host-disks"') &&
        str_contains($hostDisksCard, 'locales: ["core-host-disks."]') &&
        str_contains(
            $hostDisksCard,
            'helpers: ["target", "renderTable", "humanBytes", "i18n"]',
        ) &&
        str_contains($hostDisksCard, 'catalogs: Object.freeze({') &&
        str_contains($hostDisksCard, '"core-host-disks.old_collector"') &&
        is_array($english) &&
        !isset($english['host.io_disks_old_collector']) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostDisksCard, "core-host-disks",',
        ),
    'host disks are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-io-processes.js"') &&
        $hostIoProcessesCard !== false &&
        str_contains(
            $hostIoProcessesCard,
            'window.ZogographHostIoProcessesCard',
        ) &&
        str_contains($hostIoProcessesCard, 'id: "core-host-io-processes"') &&
        str_contains(
            $hostIoProcessesCard,
            'locales: ["core-host-io-processes."]',
        ) &&
        str_contains(
            $hostIoProcessesCard,
            'helpers: ["target", "renderTable", "humanBytes", "i18n"]',
        ) &&
        str_contains($hostIoProcessesCard, 'catalogs: Object.freeze({') &&
        str_contains($hostIoProcessesCard, '"core-host-io-processes.empty"') &&
        is_array($english) &&
        !isset($english['host.io_processes_empty']) &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostIoProcessesCard, "core-host-io-processes",',
        ),
    'host I/O processes are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-files.js"') &&
        $hostFilesCard !== false &&
        str_contains($hostFilesCard, 'window.ZogographHostFilesCard') &&
        str_contains($hostFilesCard, 'id: "core-host-files"') &&
        str_contains($hostFilesCard, 'locales: ["core-host-files."]') &&
        str_contains($hostFilesCard, '"core-host-files.linked_total"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostFilesCard, "core-host-files",',
        ),
    'watched files are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/host-daemons.js"') &&
        $hostDaemonsCard !== false &&
        str_contains($hostDaemonsCard, 'window.ZogographHostDaemonsCard') &&
        str_contains($hostDaemonsCard, 'id: "core-host-daemons"') &&
        str_contains($hostDaemonsCard, 'locales: ["core-host-daemons."]') &&
        str_contains($hostDaemonsCard, '"core-host-daemons.empty"') &&
        str_contains($hostDaemonsCard, '"watch"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographHostDaemonsCard, "core-host-daemons",',
        ),
    'host daemons are not isolated behind their core card-module boundary',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'src="cards/uptime.js"') &&
        $uptimeCard !== false &&
        str_contains($uptimeCard, 'id: "core-uptime"') &&
        str_contains($uptimeCard, 'locales: ["core-uptime."]') &&
        str_contains($uptimeCard, 'catalogs: Object.freeze({') &&
        str_contains($uptimeCard, '"core-uptime.no_boot_time"') &&
        str_contains($uptimeCard, '"core-uptime.measured_window_up"') &&
        is_array($english) &&
        !isset($english['uptime.no_boot_time']) &&
        !isset($english['uptime.measured_window_up']) &&
        str_contains($uptimeCard, '"summary"') &&
        str_contains($uptimeCard, '"trimPercent"') &&
        $script !== false &&
        str_contains(
            $script,
            'renderCoreCardPackage(ZogographUptimeCard, "core-uptime",',
        ),
    'uptime dashboard card is not isolated behind its core card-module boundary',
);
i18nExpect(
    $actionSummaryCard !== false &&
        str_contains($actionSummaryCard, 'locales: ["core-action-summary."]') &&
        str_contains($actionSummaryCard, '"core-action-summary.things_done"') &&
        is_array($english) &&
        !isset($english['action_log.things_done']),
    'action summary dashboard card does not own its locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function humanDuration(seconds)') &&
        str_contains($script, '"duration.value"') &&
        is_array($english) &&
        isset($english['duration.value']),
    'duration values do not honor the selected locale',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function humanWhen(seconds, long = false)') &&
        str_contains(
            $script,
            'new Intl.RelativeTimeFormat(document.documentElement.lang || undefined,',
        ),
    'relative time values do not honor the selected locale',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function humanSpan(seconds, long = false)') &&
        str_contains($script, 'function humanSpanUnit(value, unit, long)') &&
        str_contains($script, 'style: "unit"'),
    'time spans do not honor the selected locale',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function whenRange(from, to)') &&
        str_contains(
            $script,
            'fromDate.toDateString() === toDate.toDateString()',
        ) &&
        !str_contains($script, 'if (a.length === b.length)'),
    'date ranges decide their format from translated label length',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("image.note_screenshot"') &&
        str_contains($script, 'ZogographI18n.t("image.send_screenshot"'),
    'dashboard fixed image descriptions do not use the locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("image.portrait_alt"') &&
        str_contains($script, 'ZogographI18n.t("image.note_picture_alt"') &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/storage-inspector-preview.js',
            ),
            'core-storage-inspector-preview.unavailable',
        ) &&
        is_array($english) &&
        isset($english['image.portrait_alt'], $english['image.chart_alt']),
    'dashboard image descriptions or failures do not use package-localized catalog messages',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("table.download_rows"') &&
        str_contains($script, 'ZogographI18n.t("table.sort_by"') &&
        str_contains($script, 'ZogographI18n.t("table.rows_shown"') &&
        str_contains($script, 'ZogographI18n.t("chart.your_notes"') &&
        is_array($english) &&
        isset(
            $english['table.download_rows'],
            $english['table.sort_by'],
            $english['table.rows_total'],
            $english['table.rows_shown'],
            $english['table.show_more'],
            $english['chart.your_notes'],
        ),
    'shared dashboard table controls or chart-note labels do not use the locale catalog',
);
i18nExpect(
    $onboardingCard !== false &&
        str_contains($onboardingCard, 'core-onboarding.intro') &&
        str_contains($onboardingCard, 'core-onboarding.vitals_missing') &&
        str_contains($onboardingCard, 'core-onboarding.scan_ready'),
    'first-run dashboard guidance does not use the locale catalog',
);
i18nExpect(
    is_array($english) &&
        !isset(
            $english['storage.types_not_measured'],
            $english['storage.no_scan_in_range'],
            $english['storage.types_measured'],
            $english['storage.types_total'],
        ),
    'file-type card text must remain package-local rather than returning to shared catalogs',
);
i18nExpect(
    is_array($english) &&
        !isset(
            $english['onboarding.intro'],
            $english['onboarding.vitals_missing'],
            $english['onboarding.open_schedule'],
            $english['onboarding.scan_ready'],
            $english['cpu.limit_unavailable'],
            $english['cpu.limit_host_reading'],
            $english['cpu.limit_not_measured'],
        ),
    'onboarding and limits card text must remain package-local',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("filter.all"') &&
        $page !== false &&
        substr_count($page, 'data-i18n="filter.all"') === 2 &&
        is_array($english) &&
        isset($english['filter.all']),
    'shared filter default does not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="storage.host_logs_title"') &&
        str_contains($page, 'data-i18n="storage.check_again"') &&
        str_contains($page, 'data-i18n="storage.host_logs_help"') &&
        is_array($english) &&
        isset(
            $english['storage.host_logs_title'],
            $english['storage.check_again'],
            $english['storage.host_logs_help'],
        ),
    'Storage host-log guidance does not use the locale catalog',
);
i18nExpect(
    str_contains(
        (string) file_get_contents($root . '/cards/storage-scan-status.js'),
        'core-storage-scan-status.running',
    ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/storage-scan-status.js'),
            'core-storage-scan-status.finished',
        ),
    'Storage scan status does not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="storage.history"') &&
        str_contains($page, 'data-i18n="log.search"') &&
        str_contains($page, 'data-i18n="log.newer"') &&
        $script !== false &&
        str_contains($script, "'core-storage-log'") &&
        is_array($english) &&
        isset(
            $english['storage.history'],
            $english['action.close'],
            $english['log.search'],
            $english['log.lines'],
            $english['log.newer'],
            $english['log.older'],
        ),
    'Storage history and log controls do not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="storage.last_scan_title"') &&
        str_contains($page, 'data-i18n-rich="storage.whole_machine_help"') &&
        str_contains($page, 'data-i18n="storage.type_title"') &&
        str_contains($page, 'data-i18n-rich="storage.type_help"') &&
        str_contains($page, 'data-i18n="storage.path_hygiene_title"') &&
        is_array($english) &&
        isset(
            $english['storage.last_scan_title'],
            $english['storage.whole_machine_help'],
            $english['storage.type_title'],
            $english['storage.type_help'],
            $english['storage.path_hygiene_title'],
        ),
    'Storage scan and analysis guidance does not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="auth.sign_in_help"') &&
        str_contains($page, 'data-i18n="auth.username"') &&
        str_contains($page, 'data-i18n="auth.verification_code"') &&
        is_array($english) &&
        isset(
            $english['auth.sign_in_help'],
            $english['auth.username'],
            $english['auth.password_label'],
            $english['auth.verification_code'],
        ),
    'Sign-in dialog does not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="log.repeat_title"') &&
        str_contains($page, 'data-i18n="log.repeat_help"') &&
        is_array($english) &&
        isset($english['log.repeat_title'], $english['log.repeat_help']),
    'Repeated-log guidance does not use the locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'marketplaceText("discovery_notice"') &&
        str_contains($script, 'marketplaceText("search_label"') &&
        str_contains($script, 'marketplaceText("table_compatibility"') &&
        str_contains($script, 'marketplaceText("toggle_confirmation"') &&
        str_contains($script, 'marketplaceText("restore_confirmation"') &&
        is_array($english) &&
        isset(
            $english['marketplace.discovery_notice'],
            $english['marketplace.ignored_manifests'],
            $english['marketplace.search_label'],
            $english['marketplace.toggle_confirmation'],
            $english['marketplace.restore_confirmation'],
        ),
    'marketplace controls do not use translatable catalog messages',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function peerText(') &&
        str_contains($script, 'peerText("table_transport"') &&
        str_contains($script, 'peerText("overview_title"') &&
        str_contains($script, 'peerText("field_endpoint"') &&
        str_contains($script, 'peerText("field_redirect_help"') &&
        str_contains($script, 'function peerPercent(') &&
        is_array($english) &&
        isset(
            $english['peer.capacity'],
            $english['peer.table_transport'],
            $english['peer.overview_title'],
        ),
    'trusted-server controls do not use locale-aware catalog messages',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("encryption.custody_notice"') &&
        str_contains($script, 'ZogographI18n.t("diagnosis.ignore_confirm"') &&
        str_contains($script, 'ZogographI18n.t("diagnosis.ignore_success"'),
    'security-sensitive dashboard notices do not use the locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains(
            $script,
            'ZogographI18n.t("encryption.create_verified_copies"',
        ) &&
        str_contains(
            $script,
            'ZogographI18n.t("encryption.run_remote_recovery_drill"',
        ),
    'encryption recovery actions do not use the locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function copyButtonLabel(state)') &&
        str_contains($script, 'ZogographI18n.t("store.rollup_result"') &&
        str_contains($script, 'ZogographI18n.t("capabilities.checked_changes"'),
    'shared operational controls do not use the locale catalog',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function packageText(') &&
        str_contains($script, 'package.runtime_intro') &&
        str_contains($script, 'package.installed_for_review') &&
        str_contains($script, 'package.toggle_confirmation') &&
        str_contains($script, 'package.remove_confirmation'),
    'package administration does not use localized named messages',
);
i18nExpect(
    $script !== false &&
        str_contains(
            $script,
            'typeof page.parent === "string" && VIEWS[page.parent]',
        ) &&
        str_contains($script, 'link.dataset.parent = parent'),
    'package dashboard pages cannot select a safe core navigation parent',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'page.title_key ? ZogographI18n.t') &&
        str_contains($script, 'view.packagePage'),
    'package dashboard pages do not use signed locale keys or refresh after a locale change',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'src="i18n.js"') &&
        str_contains($setup, 'id="languagePicker"') &&
        str_contains($setup, 'id="languageKeys"') &&
        str_contains($setup, 'id="languageHighlight"') &&
        str_contains($setup, 'data-i18n="setup.installation"') &&
        str_contains($setup, "'setup.section.' . \$name") &&
        str_contains($setup, "'setup.section.' . \$activeSection") &&
        str_contains($setup, 'aria-current="page"') &&
        str_contains($setup, 'data-setup-nav-results') &&
        str_contains(
            $setup,
            'markSource?.(text, messageKey, messageFallback)',
        ) &&
        str_contains(
            $setup,
            'markSource?.(reload, "action.reload", "Reload now")',
        ) &&
        str_contains($setup, 'zogograph.build.identity') &&
        str_contains($setup, 'zogograph.build.update') &&
        str_contains($setup, 'sessionStorage.getItem(storageKey)') &&
        str_contains(
            $setup,
            'document.addEventListener("zogograph-i18n-load"',
        ) &&
        str_contains($setup, 'document.addEventListener("visibilitychange"') &&
        str_contains($setup, 'window.addEventListener("focus", check)'),
    'Setup language controls or localized shell are unavailable',
);
i18nExpect(
    $setup !== false &&
        str_contains(
            $setup,
            'item.dataset.setupNavTerms + " " + item.textContent',
        ),
    'Setup navigation search ignores translated visible labels',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.document_title"'),
    'Setup document title is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'if ($configured) {') &&
        str_contains(
            $setup,
            "renderField(\$field, \$values, \$errors, false)",
        ) &&
        str_contains($setup, 'data-i18n="auth.create_password"'),
    'first-run Setup does not limit itself to password creation before installation checks',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.authorize"') &&
        str_contains($setup, 'data-i18n="setup.unlock_to_view"') &&
        str_contains($setup, 'data-i18n="privacy.masked_paths"') &&
        str_contains($setup, 'data-i18n="privacy.rotate_mask"'),
    'Setup access and privacy panels are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.not_configured"') &&
        str_contains($setup, 'data-i18n="setup.authorize_help"') &&
        !str_contains($setup, 'data-i18n="setup.no_password_help"'),
    'Setup password-protection notices are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.validation_error"'),
    'Setup validation feedback is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.validation_detail"') &&
        str_contains($setup, "json_encode(['detail' => (string) \$error]") &&
        str_contains($setup, 'JSON_THROW_ON_ERROR'),
    'Setup field-level validation details do not use localized named values',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, "'auth.password_minimum'") &&
        is_array($english) &&
        isset(
            $english['auth.password_mismatch'],
            $english['auth.password_required_current'],
        ),
    'Setup password validation messages are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.error_detail"') &&
        str_contains($setup, "json_encode(['detail' => \$detail]"),
    'Setup server-side error feedback does not use localized named values',
);
i18nExpect(
    $setup !== false && str_contains($setup, 'data-i18n="setup.saved_section"'),
    'Setup save confirmation is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'function setupFlashNotice') &&
        str_contains($setup, 'data-i18n-values="') &&
        str_contains($setup, "'deployment.target_verified'"),
    'Setup action feedback does not use localized flash notices',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.settings_saved"') &&
        str_contains($setup, 'data-i18n="setup.first_readings"'),
    'Setup post-save guidance is not localized',
);
i18nExpect(
    $setup !== false && str_contains($setup, 'data-i18n="setup.root_step"'),
    'Setup host-collector warning is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.root_install_help"'),
    'Setup host-collector installation guidance is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.root_verify_help"'),
    'Setup host-collector verification guidance is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.root_schedule_help"'),
    'Setup host-collector schedule boundary is not localized',
);
i18nExpect(
    $setup !== false && str_contains($setup, 'data-i18n="setup.save"'),
    'Setup generic save action is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.copy_config"') &&
        str_contains($setup, 'data-i18n="setup.copy_config_help"'),
    'Setup configuration export guidance is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.new_mask_warning"'),
    'Setup configuration export mask warning is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'setupRenderPackageSettingsPanel') &&
        str_contains($setup, 'data-package-settings-target') &&
        str_contains($setup, 'save-package-settings:') &&
        str_contains($setup, 'setupRedirect($packagePanelId)') &&
        str_contains($setup, 'data-i18n="package.save_settings"') &&
        str_contains($setup, 'data-i18n="package.no_reference"'),
    'Setup cannot render a signed package settings panel inside a core page',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="package.unavailable"') &&
        str_contains($setup, "'detail' => \$error->getMessage()") &&
        str_contains($setup, 'JSON_THROW_ON_ERROR'),
    'Setup package-settings failure does not use localized named values',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'ZogographI18n?.t("package.fix_settings_json"'),
    'Setup package-settings JSON alert is not localized',
);
i18nExpect(
    $setup !== false &&
        !str_contains($setup, 'textarea[name=package_settings]') &&
        substr_count(
            $setupRaw,
            'document.querySelectorAll("[data-package-settings-target]")',
        ) === 1,
    'Setup package-secret selectors do not use one panel-specific handler',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, "(string) (\$panel['title_key'] ?? '')") &&
        $setupPanels !== false &&
        str_contains($setupPanels, "'title_key' => \$titleKey"),
    'Setup package panels cannot use signed locale catalog titles',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="schedule.intro"') &&
        str_contains($setup, 'data-i18n="schedule.saved_preview"') &&
        str_contains($setup, 'data-i18n="schedule.remove"') &&
        str_contains($setup, 'data-i18n="schedule.no_php"') &&
        str_contains($setup, '$copyKey =') &&
        str_contains($setup, "\$sync === 'in_sync'") &&
        str_contains(
            (string) file_get_contents($root . '/lib/setup-cron-editor.php'),
            'schedule.custom_builder',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/lib/setup-cron-editor.php'),
            'schedule.job.',
        ),
    'Setup schedule controls are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="installation.checks"') &&
        str_contains($setup, 'data-i18n="installation.detail"'),
    'Setup installation checks are not localized',
);
i18nExpect(
    $setup !== false &&
        !str_contains($setup, 'data-i18n="setup.configured"') &&
        str_contains($setup, '$editingLocked && $activeSection !== \'basics\''),
    'Setup shows obsolete configured banners or hides read-only installation checks',
);
i18nExpect(
    $setup !== false &&
        $setupPanels !== false &&
        str_contains($setup, '$hostConfig =') &&
        str_contains($setup, '$current ??') &&
        str_contains($setup, 'readExistingConfig($targetPath)') &&
        str_contains($setup, 'data-i18n="host.status.title"') &&
        str_contains($setupPanels, "['basics', 'schedule', 'host', 'privacy']"),
    'Setup Host status checks or the requested navigation order are unavailable',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'link.href = "./setup.php?section=host"'),
    'dashboard host setup warning has no actionable Setup link',
);
i18nExpect(
    $setup !== false && str_contains($setup, 'data-i18n-alt="auth.qr_alt"'),
    'Setup authenticator QR alternative text is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, "'auth.change_password'") &&
        str_contains($setup, "'auth.create_password'") &&
        str_contains($setup, 'data-i18n="auth.new_password"') &&
        str_contains($setup, 'data-i18n="auth.password_again"'),
    'Setup sign-in password form is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="encryption.private_key_exists"') &&
        str_contains($setup, 'data-i18n="encryption.create_private_key"'),
    'Setup private-key provisioning is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="auth.open_account"') &&
        str_contains($setup, 'data-i18n="auth.provisioning_uri"'),
    'Setup authenticator enrollment guidance is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="openid.provider_intro"') &&
        str_contains($setup, 'data-i18n="openid.secret_copy"') &&
        str_contains($setup, 'data-i18n="openid.actions"'),
    'Setup OpenID security guidance is not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="deployment.copy_release"') &&
        str_contains($setup, 'data-i18n="deployment.review_transport"') &&
        str_contains($setup, 'data-i18n="deployment.handoff"') &&
        str_contains($setup, 'data-i18n="deployment.handoff_help"') &&
        str_contains($setup, 'data-i18n="deployment.method"') &&
        str_contains($setup, 'data-i18n="deployment.update_manual"') &&
        str_contains($setup, 'data-i18n="deployment.store_help"') &&
        str_contains($setup, 'data-i18n="deployment.last_test_result"') &&
        str_contains($setup, 'data-i18n-values='),
    'Setup deployment release controls are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="merge.review_expiry"') &&
        str_contains($setup, 'data-i18n-values=') &&
        str_contains($setup, 'data-i18n="merge.apply_help"') &&
        str_contains($setup, 'data-i18n="merge.last_result"'),
    'Setup account-merge review cannot localize its values and guidance',
);
i18nExpect(
    $configure !== false &&
        str_contains($configure, 'function generatedFieldLabel') &&
        str_contains($configure, "'label' => generatedFieldLabel(\$path)") &&
        !str_contains($configure, "'label' => \$dotted"),
    'generated Setup fields can still expose dotted internal configuration paths',
);
i18nExpect(
    $setup !== false && str_contains($setup, 'data-i18n="form.yes"'),
    'Setup boolean controls are not localized',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.field.') &&
        str_contains($setup, 'h($key)') &&
        is_array($english) &&
        isset(
            $english['setup.field.openid_enabled'],
            $english['setup.field.encryption_key_file'],
            $english['setup.field.host_keep_days'],
        ),
    'schema-driven Setup field labels do not have stable i18n keys and English fallbacks',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.option.') &&
        str_contains($setup, 'h($key)') &&
        is_array($english) &&
        isset(
            $english['setup.option.encryption_mode.none'],
            $english['setup.option.deletable_categories.trash'],
        ),
    'schema-driven Setup option labels do not have stable i18n keys and English fallbacks',
);
i18nExpect(
    $setup !== false &&
        str_contains($setup, 'data-i18n="setup.help.') &&
        str_contains($setup, 'h($key)') &&
        is_array($english) &&
        isset(
            $english['setup.help.openid_enabled'],
            $english['setup.help.encryption_key_file'],
            $english['setup.help.host_keep_days'],
        ),
    'schema-driven Setup help text does not have stable i18n keys and English fallbacks',
);
i18nExpect(
    is_array($english) &&
        isset(
            $english['example.readings'],
            $english['example.compact'],
            $english['example.cost'],
            $english['example.percent'],
            $english['example.storage'],
            $english['example.changed'],
            $english['example.arabic_digits'],
            $english['example.eastern_arabic_digits'],
            $english['update.available'],
            $english['range.10m'],
            $english['action.reload'],
            $english['navigation.storage'],
            $english['navigation.cpu'],
            $english['navigation.deleted_held'],
            $english['navigation.cpu_limits'],
            $english['deployment.test'],
            $english['deployment.copy_release'],
            $english['deployment.method'],
            $english['deployment.update_manual'],
            $english['merge.apply'],
            $english['merge.review_expiry'],
            $english['merge.last_result'],
            $english['installation.checks'],
            $english['auth.qr_alt'],
            $english['auth.open_account'],
            $english['image.generated'],
            $english['package.save_settings'],
            $english['package.runtime_intro'],
            $english['package.installed_for_review'],
            $english['schedule.custom_builder'],
            $english['schedule.job.vitals'],
            $english['schedule.remove'],
            $english['setup.search_results'],
            $english['openid.provider_intro'],
            $english['openid.secret_copy'],
            $english['page.document_title'],
            $english['page.title'],
            $english['terminal.suggestion_expectation'],
            $english['terminal.syntax_preview_title'],
            $english['terminal.recovery_restore_selected'],
            $english['terminal.recovery_restore_title'],
            $english['terminal.recovery_restore_consequence'],
            $english['terminal.recovery_restore_action'],
            $english['terminal.recovery_restored'],
            $english['terminal.recovery_label'],
            $english['terminal.recovery_help'],
            $english['terminal.syntax_preview_dialog_title'],
            $english['terminal.syntax_preview_help'],
            $english['editor.contents_of'],
            $english['editor.shortcuts_editable'],
            $english['editor.shortcuts_read_only'],
            $english['editor.writable_note'],
            $english['editor.read_only_note'],
            $english['editor.unknown'],
            $english['editor.encoding'],
            $english['editor.line_endings'],
            $english['editor.modified'],
            $english['editor.permissions'],
            $english['editor.review_title'],
            $english['editor.review_no_changes'],
            $english['editor.review_dialog_title'],
            $english['editor.review_help'],
            $english['editor.view_dialog_title'],
            $english['editor.view_help'],
            $english['editor.edit_dialog_title'],
            $english['editor.saved_with_recovery'],
            $english['editor.saved'],
            $english['editor.save'],
            $english['editor.parent_directory'],
            $english['editor.symbolic_link_unfollowed'],
            $english['editor.directory_writable'],
            $english['editor.directory_read_only'],
            $english['editor.file_access'],
            $english['editor.writable'],
            $english['editor.read_only'],
            $english['editor.not_regular_file'],
            $english['editor.browser_more'],
            $english['editor.browser_help'],
            $english['terminal.just_started'],
            $english['terminal.running_for_so_far'],
            $english['terminal.running_so_far'],
            $english['url.editing'],
            $english['url.add_check'],
            $english['url.address_placeholder'],
            $english['url.notify_change_help'],
            $english['regex.group'],
            $english['regex.took'],
            $english['regex.group_absence_help'],
            $english['ai.data_page'],
            $english['pending.waiting_on_root'],
            $english['pending.schedule_unknown'],
            $english['pending.collector_overdue'],
            $english['pending.expected_in'],
            $english['pending.due_now'],
            $english['pending.past_due'],
            $english['pending.ready'],
            $english['pending.new'],
            $english['notes.capture_size'],
            $english['notes.pictures_kept'],
            $english['notes.selected'],
            $english['notes.select_to_act'],
            $english['notes.dismiss_selected_help'],
            $english['notes.select_from'],
            $english['notes.table_time'],
            $english['notes.table_icons'],
            $english['notes.broadcast_cleared'],
            $english['notes.broadcast_active'],
            $english['notes.somebody'],
            $english['notes.table_note'],
            $english['notes.table_size'],
            $english['process.ask'],
            $english['process.force'],
            $english['process.ask_stop'],
            $english['process.force_stop'],
            $english['process.ask_confirmation'],
            $english['process.force_confirmation'],
            $english['process.stopped_count'],
            $english['process.still_running_count'],
            $english['process.protected_count'],
            $english['process.failed_count'],
            $english['alerts.sent_open_title'],
            $english['alerts.sent_legacy_title'],
            $english['alerts.muted_title'],
            $english['alerts.notification_held'],
            $english['alerts.delivery'],
            $english['alerts.held_by_operator_mute'],
            $english['alerts.unknown'],
            $english['alerts.no_reason_recorded'],
            $english['alerts.mute_detail_help'],
            $english['alerts.mail_sent'],
            $english['alerts.mail_not_sent'],
            $english['alerts.mail_to'],
            $english['alerts.mail_nobody'],
            $english['alerts.mail_subject'],
            $english['alerts.mail_error'],
            $english['alerts.mail_not_delivered_help'],
            $english['alerts.mail_covered'],
            $english['alerts.mail_message'],
            $english['alerts.mail_headers'],
            $english['alerts.mail_dialog_title'],
            $english['alerts.mail_dialog_help'],
            $english['alert.verdict_timing'],
            $english['alert.verdict_reviewed_at'],
            $english['alert.held_back_title'],
            $english['alert.verdict_dialog_title'],
            $english['alert.verdict_dialog_help'],
            $english['alerts.mute_duration_15m'],
            $english['alerts.mute_duration_1h'],
            $english['alerts.mute_duration_4h'],
            $english['alerts.mute_duration_1d'],
            $english['alerts.mute_duration_7d'],
            $english['process.table_pid'],
            $english['process.table_user'],
            $english['process.table_running_for'],
            $english['process.table_cpu'],
            $english['process.table_command'],
            $english['process.table_process'],
            $english['process.table_how_many'],
            $english['process.table_memory'],
            $english['process.table_of_ram'],
            $english['process.table_biggest_pid'],
            $english['process.table_stop'],
            $english['process.metric_memory'],
            $english['process.trend_summary'],
            $english['process.trend_empty_help'],
            $english['process.trend_empty'],
            $english['process.stop_force'],
            $english['process.stop_ask'],
            $english['process.stop_unavailable'],
            $english['process.stop_force_one_help'],
            $english['process.stop_force_many_help'],
            $english['process.stop_ask_one_help'],
            $english['process.stop_ask_many_help'],
            $english['process.stop_killing'],
            $english['process.stop_asking'],
            $english['process.stop_force_waiting'],
            $english['process.stop_ask_waiting'],
            $english['process.stop_subject_many'],
            $english['process.stop_subject_one'],
            $english['process.stop_force_confirm'],
            $english['process.stop_ask_confirm'],
            $english['process.stop_stopped'],
            $english['process.stop_signalled'],
            $english['process.stop_group_stopped'],
            $english['process.stop_group_remaining'],
            $english['process.stop_group_refused'],
            $english['process.stop_group_failed'],
            $english['process.rank_largest'],
            $english['process.rank_second_largest'],
            $english['process.cpu_cores'],
            $english['process.cpu_cores_worth'],
            $english['process.cpu_help'],
            $english['process.age_with_newest'],
            $english['process.age_started_now'],
            $english['process.age_started_hour'],
            $english['process.age_started_today'],
            $english['process.age_long_running'],
            $english['process.unavailable'],
            $english['process.no_matches'],
            $english['process.matches_summary'],
            $english['process.matches_all_protected'],
            $english['process.matches_some_protected'],
            $english['process.all_matches_protected'],
        ),
    'English fallback catalog is incomplete',
);
preg_match_all(
    '/<(?:input|textarea)\b[^>]*\bplaceholder="[^"]+"[^>]*>/i',
    (string) $page,
    $placeholderNodes,
);
foreach ($placeholderNodes[0] ?? [] as $node) {
    i18nExpect(
        str_contains($node, 'data-i18n-placeholder="'),
        'dashboard static placeholder is not backed by an i18n key',
    );
}
preg_match_all(
    '/<[^>]*\b(?:title|aria-label)="[^"]+"[^>]*>/i',
    (string) $page,
    $accessibleNodes,
);
foreach ($accessibleNodes[0] ?? [] as $node) {
    i18nExpect(
        (!str_contains($node, 'title="') ||
            str_contains($node, 'data-i18n-title="')) &&
            (!str_contains($node, 'aria-label="') ||
                str_contains($node, 'data-i18n-aria-label="')),
        'dashboard static title or accessible label is not backed by an i18n key',
    );
}
preg_match_all(
    '/data-i18n(?:-placeholder|-title|-aria-label|-rich)?="([^"]+)"/',
    (string) $page,
    $pageKeys,
);
foreach (array_unique($pageKeys[1] ?? []) as $key) {
    i18nExpect(
        isset($english[$key]),
        "dashboard i18n attribute key $key has no English fallback",
    );
}
preg_match_all(
    '/ZogographI18n\\.t\\(\\s*"([a-z][\\w.-]+)"/',
    (string) $script,
    $dynamicDashboardKeys,
);
foreach (array_unique($dynamicDashboardKeys[1] ?? []) as $key) {
    i18nExpect(
        isset($english[$key]) ||
            isset($toolEnglish[$key]) ||
            (in_array(
                $key,
                [
                    'core-ai-tool-try.unknown_tool',
                    'core-ai-tool-try.browser_shown',
                    'core-ai-tool-try.arguments',
                    'core-ai-tool-try.browser_result',
                    'core-ai-tool-try.browser_help',
                    'core-ai-tool-try.browser_timing',
                    'core-ai-tool-try.refused',
                    'core-ai-tool-try.server_timing',
                    'core-ai-tool-try.generated_picture',
                    'core-ai-tool-try.picture_note',
                    'core-ai-tool-try.not_reported',
                    'core-ai-tool-try.result',
                    'core-ai-tool-try.result_help',
                    'core-terminal-verdict.stopped',
                    'core-terminal-verdict.timed_out',
                    'core-terminal-verdict.output_ceiling',
                    'core-terminal-verdict.ok',
                    'core-terminal-verdict.killed',
                    'core-terminal-verdict.signal',
                    'core-terminal-verdict.exit',
                ],
                true,
            ) &&
                str_contains(
                    (string) file_get_contents($root . '/cards/ai-tool-try.js'),
                    "'" . $key . "'",
                )),
        "dashboard dynamic i18n key $key has no English fallback",
    );
}
i18nExpect(
    $script !== false &&
        str_contains($script, 'terminal.suggestion_expectation') &&
        str_contains($script, 'terminal.syntax_preview_title') &&
        $terminalActionsCard !== false &&
        str_contains(
            $terminalActionsCard,
            'core-terminal-actions.send_output_to',
        ),
    'terminal editor actions are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'terminal.recovery_restore_selected') &&
        str_contains($script, 'terminal.recovery_restored') &&
        str_contains($script, 'terminal.syntax_preview_dialog_title'),
    'terminal editor recovery and preview dialog are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'editor.contents_of') &&
        str_contains($script, 'editor.shortcuts_editable') &&
        str_contains($script, 'editor.writable_note') &&
        str_contains($script, 'editor.permissions'),
    'terminal editor metadata and accessibility text are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'editor.review_title') &&
        str_contains($script, 'editor.view_dialog_title') &&
        str_contains($script, 'editor.saved_with_recovery'),
    'terminal editor review and save dialogs are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'editor.parent_directory') &&
        str_contains($script, 'editor.file_access') &&
        str_contains($script, 'editor.browser_help'),
    'terminal editor browser is not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'terminal.running_for_so_far') &&
        $terminalVerdictSummaryCard !== false &&
        str_contains(
            $terminalVerdictSummaryCard,
            'core-terminal-verdict-summary.summary',
        ) &&
        $terminalActionsCard !== false &&
        str_contains(
            $terminalActionsCard,
            'core-terminal-actions.send_output_help',
        ),
    'terminal live output and send feedback are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'url.editing') &&
        str_contains($script, 'url.address_placeholder') &&
        str_contains($script, 'url.notify_change_help'),
    'URL check editor retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'regex.group') &&
        str_contains($script, 'regex.took') &&
        str_contains($script, 'regex.group_absence_help'),
    'URL check regex tester retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents($root . '/cards/chat-title.js'),
            'core-chat-title.portrait_title',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/chat-placeholder.js'),
            'core-chat-placeholder.prompt',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/chat-corner-label.js'),
            'core-chat-corner-label.text',
        ),
    'assistant chat identity retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents($root . '/cards/chat-permission.js'),
            'core-chat-permission.wants',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/chat-permission.js'),
            'core-chat-permission.timeout_notice',
        ),
    'assistant permission card retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'pending.waiting_on_root') &&
        str_contains($script, 'pending.collector_overdue') &&
        str_contains($script, 'pending.ready'),
    'host request status retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'notes.capture_size') &&
        str_contains($script, 'notes.pictures_kept'),
    'note attachment summary retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($notesEmptyCard, 'core-notes-empty.message') &&
        str_contains($script, 'notes.selected') &&
        str_contains($script, 'notes.dismiss_selected_help'),
    'note selection controls retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'notes.select_from') &&
        str_contains($script, 'notes.broadcast_cleared') &&
        str_contains($script, 'notes.table_size'),
    'note table retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'process.ask_stop') &&
        str_contains($script, 'process.force_confirmation') &&
        str_contains($script, 'process.still_running_count'),
    'process-stop controls retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'core-ai-tool-try.browser_shown') &&
        str_contains($script, 'core-ai-tool-try.browser_timing') &&
        str_contains($script, 'core-ai-tool-try.server_timing') &&
        str_contains($script, 'core-ai-tool-try.result_help'),
    'AI tool test panel retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alerts.sent_open_title') &&
        str_contains($script, 'alerts.sent_legacy_title') &&
        str_contains($script, 'alerts.muted_title'),
    'alert delivery row retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alerts.notification_held') &&
        str_contains($script, 'alerts.held_by_operator_mute') &&
        str_contains($script, 'alerts.mute_detail_help'),
    'alert mute detail dialog retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alerts.mail_sent') &&
        str_contains($script, 'alerts.mail_error') &&
        str_contains($script, 'alerts.mail_not_delivered_help'),
    'alert email facts retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alerts.mail_covered') &&
        str_contains($script, 'alerts.mail_dialog_title') &&
        str_contains($script, 'alerts.mail_dialog_help'),
    'alert email dialog retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alert.verdict_timing') &&
        str_contains($script, 'alert.held_back_title') &&
        str_contains($script, 'alert.verdict_dialog_help'),
    'alert verdict dialog retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'alerts.mute_duration_15m') &&
        str_contains($script, 'alerts.mute_duration_4h') &&
        str_contains($script, 'alerts.mute_duration_7d'),
    'alert mute duration choices retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'process.table_pid') &&
        str_contains($script, 'process.table_running_for') &&
        str_contains($script, 'process.table_command'),
    'process preview table retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        $processListCard !== false &&
        str_contains($processListCard, '"core-process-list.process"') &&
        str_contains($processListCard, '"core-process-list.biggest_pid"') &&
        str_contains($script, 'process.trend_summary'),
    'live process table and trend retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'process.stop_force_many_help') &&
        str_contains($script, 'process.stop_force_confirm') &&
        str_contains($script, 'process.stop_group_failed'),
    'process stop controls retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'process.cpu_help') &&
        str_contains($script, 'process.age_with_newest') &&
        str_contains($script, 'process.rank_second_largest'),
    'process row explanations retain direct user-visible text',
);
i18nExpect(
    str_contains($page, 'src="cards/busy-moments.js"') &&
        !isset(
            $english['process.busy_empty'],
            $english['process.busy_alert_help'],
            $english['process.busy_table_what'],
            $english['process.busy_level'],
            $english['process.busy_level_with_trigger'],
            $english['process.busy_top_more'],
            $english['process.busy_top_none'],
            $english['process.busy_cpu_explained'],
            $english['process.busy_cpu_burst'],
            $english['process.busy_row_processes'],
        ),
    'busy process history belongs to its card package',
);
i18nExpect(
    ($busyMomentsCard = file_get_contents($root . '/cards/busy-moments.js')) !==
        false &&
        str_contains(
            $busyMomentsCard,
            'core-busy-moments.level_with_trigger',
        ) &&
        str_contains($busyMomentsCard, 'core-busy-moments.cpu_burst') &&
        str_contains($busyMomentsCard, 'core-busy-moments.row_processes'),
    'busy process event details belong to its card package',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'process.unavailable') &&
        str_contains($script, 'process.matches_all_protected') &&
        str_contains($script, 'process.all_matches_protected'),
    'process preview status retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-sweep-empty.js'),
            'core-symlink-sweep-empty.message',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-safety.js',
            ),
            'core-symlink-sweep-safety.message',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-incomplete.js',
            ),
            'core-symlink-sweep-incomplete.message',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-status.js',
            ),
            'core-symlink-sweep-status.preview_skipped',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-failure.js',
            ),
            'core-symlink-sweep-failure.message',
        ),
    'broken-link sweep safety and result text retain direct user-visible text',
);
i18nExpect(
    str_contains(
        (string) file_get_contents($root . '/cards/symlink-actions.js'),
        'core-symlink-actions.repoint_dialog_title',
    ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-actions.js'),
            'core-symlink-actions.make_directory_confirm',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-actions.js'),
            'core-symlink-actions.remove_confirm',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-actions.js'),
            'core-symlink-actions.repointed_missing',
        ),
    'broken-link remediation controls are owned by their card package',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-capped.js',
            ),
            'core-symlink-sweep-capped.message',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-actions.js',
            ),
            'core-symlink-sweep-actions.confirm',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-sweep-table-labels.js',
            ),
            'core-symlink-sweep-table-labels.pointed_at',
        ),
    'broken-link sweep limits and result table retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-table.js'),
            'core-symlink-table.kind_loop',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-filter.js'),
            'core-symlink-filter.empty',
        ) &&
        str_contains(
            (string) file_get_contents($root . '/cards/symlink-table.js'),
            'core-symlink-table.showing_capped',
        ),
    'symlink filters and tables retain package-owned user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-check-summary.js',
            ),
            'core-symlink-check-summary.complete',
        ) &&
        str_contains(
            (string) file_get_contents(
                $root . '/cards/symlink-check-outcome.js',
            ),
            'core-symlink-check-outcome.healthy_partial',
        ),
    'symlink check status retains package-owned user-visible text',
);
i18nExpect(
    str_contains(
        (string) file_get_contents($root . '/cards/ldconfig-offer.js'),
        'core-ldconfig-offer.previous_failed',
    ),
    'library-cache rebuild flow is owned by its localized card package',
);
i18nExpect(
    str_contains($runtime, 'data-i18n-rich') &&
        str_contains($runtime, 'data-i18n-tag') &&
        str_contains($runtime, 'cloneNode(true)'),
    'locale runtime lacks a constrained named-tag renderer for trusted inline markup',
);
$setupLocaleSources =
    (string) $setupRaw .
    "\n" .
    (string) file_get_contents($root . '/lib/setup-cron-editor.php');
preg_match_all(
    '/<(?:input|textarea)\\b[^>]*\\bplaceholder="[^"]+"[^>]*>/i',
    $setupLocaleSources,
    $setupPlaceholderNodes,
);
foreach ($setupPlaceholderNodes[0] ?? [] as $node) {
    i18nExpect(
        str_contains($node, 'data-i18n-placeholder="') ||
            str_contains($node, 'data-i18n-placeholder-dynamic="server-path"'),
        'Setup static placeholder is not backed by an i18n key',
    );
}
preg_match_all(
    '/<[^>]*\\b(?:title|aria-label)="[^"]+"[^>]*>/i',
    $setupLocaleSources,
    $setupAccessibleNodes,
);
foreach ($setupAccessibleNodes[0] ?? [] as $node) {
    i18nExpect(
        (!str_contains($node, 'title="') ||
            str_contains($node, 'data-i18n-title="')) &&
            (!str_contains($node, 'aria-label="') ||
                str_contains($node, 'data-i18n-aria-label="')),
        'Setup static title or accessible label is not backed by an i18n key',
    );
}
preg_match_all(
    '/data-i18n(?:-placeholder|-title|-aria-label|-alt)?="([a-z][\\w.-]+)"/',
    $setupLocaleSources,
    $setupKeys,
);
foreach (array_unique($setupKeys[1] ?? []) as $key) {
    i18nExpect(
        isset($english[$key]),
        "setup i18n attribute key $key has no English fallback",
    );
}
$translatedNavigation = [
    'navigation.deleted_held',
    'navigation.cpu_limits',
    'navigation.memory_by_user',
    'navigation.memory_breakdown',
    'navigation.memory_services',
    'navigation.memory_processes',
    'navigation.kernel_slab',
    'navigation.services',
    'navigation.units',
    'navigation.last_busy',
    'navigation.url_checks',
    'navigation.open_ports',
    'navigation.connected_peers',
    'navigation.network_capture',
    'navigation.network_captures',
    'navigation.dns_lookups',
    'navigation.domains',
    'navigation.whois',
    'navigation.fetch_page',
    'navigation.dns_records',
    'navigation.whoami',
    'navigation.reverse_dns',
    'navigation.assistant',
    'navigation.assistant_identity',
    'navigation.past_conversations',
    'navigation.summaries',
    'navigation.assistant_memory',
    'navigation.ai_model',
    'navigation.prices',
    'navigation.thresholds',
    'navigation.images',
    'navigation.tools',
    'navigation.try_tool',
    'navigation.billing',
    'navigation.assistant_data',
    'navigation.terminal',
    'navigation.sessions',
    'navigation.host_capabilities',
    'navigation.installed_software',
    'navigation.changes',
    'navigation.system',
    'navigation.account_data',
    'navigation.protection',
    'navigation.host_data',
    'navigation.data_retention',
    'navigation.trusted_servers',
    'navigation.marketplace',
    'navigation.package_runtime',
    'navigation.forensics',
    'navigation.database_copies',
    'navigation.recent_problems',
    'navigation.email_alerts',
    'navigation.account_scans',
    'navigation.host_scans',
    'navigation.custom_names',
    'navigation.audit',
    'navigation.descriptions',
];
$translatedSecurityMessages = [
    'encryption.custody_notice',
    'diagnosis.ignore_action',
    'diagnosis.ignore_title',
    'diagnosis.ignore_confirm',
    'diagnosis.ignore_success',
    'diagnosis.nothing_to_report',
    'diagnosis.critical_attention',
    'diagnosis.warning_attention',
    'diagnosis.nothing_urgent',
    'diagnosis.ignored_title',
    'diagnosis.ignored_help',
    'diagnosis.reading_everything',
    'host.collector_loading',
    'peer.field_secret',
    'peer.pairing_title',
    'peer.oidc_peer_saved',
];
i18nExpect(
    $script !== false &&
        !str_contains($script, "spinner('") &&
        !str_contains($script, 'spinner("'),
    'visible loading messages must use i18n keys rather than literal spinner text',
);
$translatedEncryptionRecoveryActions = [
    'encryption.create_verified_copies',
    'encryption.run_recovery_drill',
    'encryption.add_remote_envelope',
    'encryption.run_remote_recovery_drill',
    'encryption.remote_envelope_dialog',
    'encryption.verified_local_artifact',
    'encryption.store_optional_envelope',
    'encryption.remote_recovery_dialog',
    'encryption.artifact_with_envelope',
    'encryption.remote_envelope_stored',
    'encryption.remote_recovery_verified',
];
$translatedOperationalControls = [
    'action.copy',
    'action.copied',
    'action.select_to_copy',
    'action.copy_value',
    'action.copy_line',
    'action.selected_press_copy',
    'action.checking',
    'action.check_now',
    'action.rolling_up',
    'action.roll_up_old_readings',
    'action.reclaim_empty_space',
    'action.run_full_integrity_check',
    'store.preparing_export',
    'store.rollup_result',
    'store.rollup_protected',
    'store.rollup_space',
    'email.test_sent',
    'delivery.could_not_send',
    'telegram.test_sent',
    'telegram.test_failed',
    'capabilities.checked',
    'capabilities.checked_changes',
    'capabilities.host_requested',
];
$translatedMarketplace = array_keys(
    array_filter(
        $english,
        static fn(mixed $value, string $key): bool => str_starts_with(
            $key,
            'marketplace.',
        ),
        ARRAY_FILTER_USE_BOTH,
    ),
);
$translatedPeers = array_keys(
    array_filter(
        $english,
        static fn(mixed $value, string $key): bool => str_starts_with(
            $key,
            'peer.',
        ),
        ARRAY_FILTER_USE_BOTH,
    ),
);
$translatedPackageCustody = [
    'package.probe_sqlcipher',
    'package.probe_custody',
    'package.probe_isolated',
    'package.probe_input_custody',
    'package.probe_input_generic',
    'package.probe_confirmation',
    'package.probe_result',
    'package.table_operation',
    'package.table_state',
    'package.table_result',
    'package.table_safety',
    'package.table_artifact',
    'package.custody_approve',
    'package.custody_approve_confirmation',
    'package.custody_approved',
    'package.custody_check',
    'package.custody_check_confirmation',
    'package.custody_check_result',
];
foreach (['en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'] as $locale) {
    $catalog = json_decode(
        (string) file_get_contents($root . '/locales/' . $locale . '.json'),
        true,
    );
    i18nExpect(is_array($catalog), "locale catalog $locale is invalid");
    if (in_array($locale, ['en-US', 'en-GB'], true)) {
        i18nExpect(
            $catalog === [] &&
                str_contains(
                    $runtime,
                    'const catalog = { ...i18n.fallback, ...localized };',
                ),
            "locale catalog $locale must remain an explicit English fallback override",
        );
    }
    if (in_array($locale, ['es', 'hi', 'zh', 'ar'], true)) {
        i18nExpect(
            array_diff(
                array_unique($pageKeys[1] ?? []),
                array_keys($catalog),
            ) === [],
            "locale catalog $locale falls back to English for a visible dashboard label",
        );
        i18nExpect(
            array_diff(
                array_filter(
                    array_unique($dynamicDashboardKeys[1] ?? []),
                    static fn(string $key): bool => !in_array(
                        $key,
                        [
                            'core-ai-tool-try.unknown_tool',
                            'core-ai-tool-try.browser_shown',
                            'core-ai-tool-try.arguments',
                            'core-ai-tool-try.browser_result',
                            'core-ai-tool-try.browser_help',
                            'core-ai-tool-try.browser_timing',
                            'core-ai-tool-try.refused',
                            'core-ai-tool-try.server_timing',
                            'core-ai-tool-try.generated_picture',
                            'core-ai-tool-try.picture_note',
                            'core-ai-tool-try.not_reported',
                            'core-ai-tool-try.result',
                            'core-ai-tool-try.result_help',
                            'core-terminal-verdict.stopped',
                            'core-terminal-verdict.timed_out',
                            'core-terminal-verdict.output_ceiling',
                            'core-terminal-verdict.ok',
                            'core-terminal-verdict.killed',
                            'core-terminal-verdict.signal',
                            'core-terminal-verdict.exit',
                        ],
                        true,
                    ),
                ),
                array_keys(
                    array_replace($catalog, $toolCatalogs[$locale] ?? []),
                ),
            ) === [],
            "locale catalog $locale falls back to English for a visible dashboard message",
        );
        i18nExpect(
            isset(
                $catalog['navigation.charts'],
                $catalog['navigation.storage'],
                $catalog['navigation.backups'],
                $catalog['navigation.cpu'],
                $catalog['navigation.memory'],
                $catalog['navigation.processes'],
                $catalog['navigation.network'],
                $catalog['setup.section.deployment'],
                $catalog['setup.authorize'],
                $catalog['privacy.masked_paths'],
                $catalog['auth.app'],
                $catalog['auth.recovery_title'],
                $catalog['auth.verify'],
                $catalog['openid.provider'],
                $catalog['openid.create_client'],
                $catalog['schedule.custom_builder'],
                $catalog['schedule.remove'],
            ) && array_diff($translatedNavigation, array_keys($catalog)) === [],
            "locale catalog $locale lacks translated primary navigation",
        );
        i18nExpect(
            array_diff(
                array_unique($setupKeys[1] ?? []),
                array_keys($catalog),
            ) === [],
            "locale catalog $locale falls back to English for a visible Setup label",
        );
        i18nExpect(
            array_diff($translatedSecurityMessages, array_keys($catalog)) ===
                [],
            "locale catalog $locale lacks translated security-sensitive dashboard text",
        );
        i18nExpect(
            array_diff(
                $translatedEncryptionRecoveryActions,
                array_keys($catalog),
            ) === [],
            "locale catalog $locale falls back to English for encryption recovery actions",
        );
        i18nExpect(
            array_diff($translatedOperationalControls, array_keys($catalog)) ===
                [],
            "locale catalog $locale falls back to English for shared operational controls",
        );
        i18nExpect(
            array_diff($translatedMarketplace, array_keys($catalog)) === [],
            "locale catalog $locale falls back to English for marketplace controls",
        );
        i18nExpect(
            array_diff($translatedPeers, array_keys($catalog)) === [],
            "locale catalog $locale falls back to English for trusted-server controls",
        );
        i18nExpect(
            array_diff($translatedPackageCustody, array_keys($catalog)) === [],
            "locale catalog $locale falls back to English for package custody controls",
        );
    }
}
foreach (
    array_keys(
        array_filter(
            $english,
            static fn(mixed $value, string $key): bool => str_starts_with(
                $key,
                'setup.field.',
            ),
            ARRAY_FILTER_USE_BOTH,
        ),
    )
    as $key
) {
    foreach (
        [
            'es' => 'Spanish',
            'hi' => 'Hindi',
            'zh' => 'Chinese',
            'ar' => 'Arabic',
        ]
        as $locale => $language
    ) {
        $catalog = json_decode(
            (string) file_get_contents($root . '/locales/' . $locale . '.json'),
            true,
        );
        i18nExpect(
            is_array($catalog) && isset($catalog[$key]),
            "$language catalog lacks schema-driven Setup field $key",
        );
    }
}
foreach (
    array_keys(
        array_filter(
            $english,
            static fn(mixed $value, string $key): bool => str_starts_with(
                $key,
                'setup.help.',
            ),
            ARRAY_FILTER_USE_BOTH,
        ),
    )
    as $key
) {
    foreach (
        [
            'es' => 'Spanish',
            'hi' => 'Hindi',
            'zh' => 'Chinese',
            'ar' => 'Arabic',
        ]
        as $locale => $language
    ) {
        $catalog = json_decode(
            (string) file_get_contents($root . '/locales/' . $locale . '.json'),
            true,
        );
        i18nExpect(
            is_array($catalog) && isset($catalog[$key]),
            "$language catalog lacks schema-driven Setup help $key",
        );
    }
}
foreach (
    array_keys(
        array_filter(
            $english,
            static fn(mixed $value, string $key): bool => str_starts_with(
                $key,
                'setup.option.',
            ),
            ARRAY_FILTER_USE_BOTH,
        ),
    )
    as $key
) {
    foreach (
        [
            'es' => 'Spanish',
            'hi' => 'Hindi',
            'zh' => 'Chinese',
            'ar' => 'Arabic',
        ]
        as $locale => $language
    ) {
        $catalog = json_decode(
            (string) file_get_contents($root . '/locales/' . $locale . '.json'),
            true,
        );
        i18nExpect(
            is_array($catalog) && isset($catalog[$key]),
            "$language catalog lacks schema-driven Setup option $key",
        );
    }
}
foreach ($schemaFields as $field) {
    $fieldKey = (string) $field['key'];
    i18nExpect(
        isset($english['setup.field.' . $fieldKey]),
        "English catalog lacks schema-driven Setup field $fieldKey",
    );
    if (isset($field['help'])) {
        i18nExpect(
            isset($english['setup.help.' . $fieldKey]),
            "English catalog lacks schema-driven Setup help $fieldKey",
        );
    }
    foreach ((array) ($field['options'] ?? []) as $value => $text) {
        $option = array_is_list((array) $field['options']) ? $text : $value;
        i18nExpect(
            isset($english['setup.option.' . $fieldKey . '.' . $option]),
            "English catalog lacks schema-driven Setup option $fieldKey.$option",
        );
    }
}
foreach (
    [
        'navigation.charts',
        'navigation.storage',
        'navigation.backups',
        'navigation.cpu',
        'navigation.memory',
        'navigation.processes',
        'navigation.network',
        'navigation.deleted_held',
        'navigation.cpu_limits',
    ]
    as $key
) {
    i18nExpect(
        $page !== false && str_contains($page, 'data-i18n="' . $key . '"'),
        "dashboard navigation does not use $key",
    );
}
i18nExpect(
    $script !== false &&
        str_contains($script, "'host.files_also_at'") &&
        str_contains($script, "'host.files_linked_total'") &&
        is_array($english) &&
        isset($english['host.files_also_at']) &&
        isset($english['host.files_linked_total']),
    'host file hard-link labels and summary are not localized',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function toolGroupLabel') &&
        $aiToolsToolbarCard !== false &&
        str_contains($aiToolsToolbarCard, '"core-ai-tools-toolbar.tab"') &&
        str_contains($aiToolsToolbarCard, 'toolGroupLabel(group)') &&
        is_array($english) &&
        isset($english['ai.tool_group.diagnostic']) &&
        !isset($english['ai.tool_group_tab']),
    'AI tool-group labels and tab formatters are not owned by their proper packages',
);
i18nExpect(
    $hostDisksCard !== false &&
        str_contains($hostDisksCard, '"host.io_rate_per_second"') &&
        $hostIoProcessesCard !== false &&
        str_contains($hostIoProcessesCard, '"host.io_rate_per_second"') &&
        is_array($english) &&
        isset($english['host.io_rate_per_second']),
    'host I/O views do not share a localized per-second rate formatter',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'function capabilityShortState') &&
        str_contains($script, "'capabilities.nothing_checked_yet'") &&
        is_array($english) &&
        isset($english['capabilities.short_alternate']) &&
        isset($english['capabilities.short_host_only']) &&
        isset($english['capabilities.short_unusable']) &&
        isset($english['capabilities.short_missing']) &&
        isset($english['capabilities.short_unknown_host']) &&
        isset($english['capabilities.nothing_checked_yet']),
    'capability states and empty fallback are not localized',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n-rich="host.services_alive_help"') &&
        str_contains($page, 'data-i18n-tag="not_installed"') &&
        str_contains($page, 'data-i18n-tag="stopped"') &&
        is_array($english) &&
        str_contains(
            (string) ($english['host.services_alive_help'] ?? ''),
            '{{not_installed}}',
        ) &&
        str_contains(
            (string) ($english['host.services_alive_help'] ?? ''),
            '{{stopped}}',
        ),
    'service-status guidance does not preserve translated emphasis',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n-rich="host.memory_breakdown_help"') &&
        str_contains($page, 'data-i18n-tag="pss"') &&
        str_contains($page, 'data-i18n-tag="rss"') &&
        is_array($english) &&
        str_contains(
            (string) ($english['host.memory_breakdown_help'] ?? ''),
            '{{pss}}',
        ) &&
        str_contains(
            (string) ($english['host.memory_breakdown_help'] ?? ''),
            '{{rss}}',
        ),
    'service-memory guidance does not preserve translated metric emphasis',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="processes.trend_initial_help"') &&
        is_array($english) &&
        isset($english['processes.trend_initial_help']),
    'process trend initial guidance is not localized',
);
echo "i18n checks passed\n";
