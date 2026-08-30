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
$root = dirname(__DIR__);
$runtime = file_get_contents($root . '/i18n.js');
$page = file_get_contents($root . '/index.html');
$script = file_get_contents($root . '/index.js');
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
$processTrendCard = i18nCardSource(
    file_get_contents($root . '/cards/process-trend.js'),
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
        $english['storage.not_measured_yet'],
        $english['storage.watched_category'],
        $english['storage.types_categories_help'],
        $english['storage.types_help'],
    ),
    'type composition pie text belongs to its card package',
);
require_once $root . '/lib/configure.php';
$schemaFields = configFields(require $root . '/lib/config.sample.php');
$toolLocaleRoot = $root . '/lib/ai-tools/locales';
$toolLocaleDirectories =
    glob($toolLocaleRoot . '/core-ai-*', GLOB_ONLYDIR) ?: [];
i18nExpect(
    count($toolLocaleDirectories) >= 1,
    'core AI tools have no package-local locale directories',
);
foreach ($toolLocaleDirectories as $directory) {
    $package = basename($directory);
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
    }
}
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
        is_array($english) &&
        isset($english['cpu.wanting_to_run']),
    'CPU queue card has no package locale catalog or removed a shared chart label',
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
        str_contains($hostMemoryCard, 'i18n.t("host.memory_card_in_use"') &&
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
        str_contains($script, 'ZogographI18n.t("image.preview_unavailable"') &&
        is_array($english) &&
        isset(
            $english['image.portrait_alt'],
            $english['image.chart_alt'],
            $english['image.preview_unavailable'],
        ),
    'dashboard image descriptions or failures do not use localized catalog messages',
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
    $script !== false &&
        str_contains($script, 'ZogographI18n.t("storage.scanning"') &&
        str_contains($script, 'ZogographI18n.t("storage.scan_now"') &&
        is_array($english) &&
        isset($english['storage.scan_now'], $english['storage.scanning']),
    'Storage scan status does not use the locale catalog',
);
i18nExpect(
    $page !== false &&
        str_contains($page, 'data-i18n="storage.history"') &&
        str_contains($page, 'data-i18n="log.search"') &&
        str_contains($page, 'data-i18n="log.newer"') &&
        $script !== false &&
        str_contains($script, 'ZogographI18n.t("storage.log"') &&
        is_array($english) &&
        isset(
            $english['storage.history'],
            $english['storage.log'],
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
            $english['terminal.send_output_to'],
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
            $english['terminal.verdict_timed_out'],
            $english['terminal.verdict_stopped_by'],
            $english['terminal.verdict_finished'],
            $english['terminal.verdict_summary'],
            $english['terminal.send_output_help'],
            $english['url.editing'],
            $english['url.add_check'],
            $english['url.address_placeholder'],
            $english['url.notify_change_help'],
            $english['regex.group'],
            $english['regex.took'],
            $english['regex.group_absence_help'],
            $english['image.portrait_title'],
            $english['ai.ask_assistant'],
            $english['ai.data_page'],
            $english['ai.problem_placeholder'],
            $english['ai.permission_wants'],
            $english['ai.permission_allow'],
            $english['ai.permission_decline'],
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
            $english['notes.empty'],
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
            $english['ai.try_browser_shown'],
            $english['ai.try_arguments'],
            $english['ai.try_browser_result'],
            $english['ai.try_browser_help'],
            $english['ai.try_browser_timing'],
            $english['ai.try_refused'],
            $english['ai.try_server_timing'],
            $english['ai.try_generated_picture'],
            $english['ai.try_picture_note'],
            $english['ai.try_not_reported'],
            $english['ai.try_result'],
            $english['ai.try_result_help'],
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
            $english['storage.link_sweep_no_match'],
            $english['storage.link_sweep_working_safe'],
            $english['storage.link_sweep_incomplete'],
            $english['storage.link_sweep_removing'],
            $english['storage.link_sweep_looking'],
            $english['storage.link_sweep_removed'],
            $english['storage.link_sweep_failed'],
            $english['storage.link_sweep_left_alone'],
            $english['storage.link_sweep_preview'],
            $english['storage.link_sweep_preview_skipped'],
            $english['storage.link_repoint'],
            $english['storage.link_repoint_title'],
            $english['storage.link_repoint_dialog_title'],
            $english['storage.link_repoint_prompt'],
            $english['storage.link_repoint_hint'],
            $english['storage.link_make_directory'],
            $english['storage.link_make_directory_title'],
            $english['storage.link_make_directory_confirm'],
            $english['storage.link_remove'],
            $english['storage.link_remove_title'],
            $english['storage.link_remove_confirm'],
            $english['storage.link_repointed'],
            $english['storage.link_repointed_missing'],
            $english['storage.link_made_directory'],
            $english['storage.link_removed'],
            $english['storage.link_sweep_failed_reason'],
            $english['storage.link_sweep_capped'],
            $english['storage.link_sweep_showing_first'],
            $english['storage.link_sweep_remove_broken'],
            $english['storage.link_sweep_remove_confirm'],
            $english['storage.link_sweep_why_not'],
            $english['storage.link_sweep_pointed_at'],
            $english['storage.symlink_kind_loop'],
            $english['storage.symlink_kind_ancestor'],
            $english['storage.symlink_kind_outside'],
            $english['storage.symlink_kind_broken'],
            $english['storage.symlink_view_all'],
            $english['storage.symlink_view_broken'],
            $english['storage.symlink_view_looped'],
            $english['storage.symlink_view_external'],
            $english['storage.symlink_filter_empty'],
            $english['storage.symlink_link'],
            $english['storage.symlink_new_marker'],
            $english['storage.symlink_points_at'],
            $english['storage.symlink_why_matters'],
            $english['storage.symlink_showing_capped'],
            $english['storage.symlink_walking_tree'],
            $english['storage.symlink_not_checked'],
            $english['storage.symlink_summary_complete'],
            $english['storage.symlink_summary_incomplete'],
            $english['storage.symlink_checked_ago'],
            $english['storage.symlink_stored'],
            $english['storage.symlink_stale'],
            $english['storage.symlink_first_walk'],
            $english['storage.symlink_new_problems'],
            $english['storage.symlink_gone'],
            $english['storage.symlink_unchanged'],
            $english['storage.symlink_healthy'],
            $english['storage.symlink_healthy_partial'],
            $english['storage.ldconfig_intro'],
            $english['storage.ldconfig_ask'],
            $english['storage.ldconfig_confirm'],
            $english['storage.ldconfig_queued'],
            $english['storage.ldconfig_nothing_yet'],
            $english['storage.ldconfig_previous_finished'],
            $english['storage.ldconfig_previous_failed'],
            $english['storage.ldconfig_no_reason'],
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
        isset($english[$key]),
        "dashboard dynamic i18n key $key has no English fallback",
    );
}
i18nExpect(
    $script !== false &&
        str_contains($script, 'terminal.suggestion_expectation') &&
        str_contains($script, 'terminal.syntax_preview_title') &&
        str_contains($script, 'terminal.send_output_to'),
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
        str_contains($script, 'terminal.verdict_summary') &&
        str_contains($script, 'terminal.send_output_help'),
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
        str_contains($script, 'image.portrait_title') &&
        str_contains($script, 'ai.ask_assistant') &&
        str_contains($script, 'ai.problem_placeholder'),
    'assistant chat identity retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'ai.permission_wants') &&
        str_contains($script, 'ai.permission_allow') &&
        str_contains($script, 'ai.permission_decline'),
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
        str_contains($script, 'notes.empty') &&
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
        str_contains($script, 'ai.try_browser_shown') &&
        str_contains($script, 'ai.try_server_timing') &&
        str_contains($script, 'ai.try_result_help'),
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
        str_contains($script, 'process.table_process') &&
        str_contains($script, 'process.table_biggest_pid') &&
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
        str_contains($script, 'storage.link_sweep_no_match') &&
        str_contains($script, 'storage.link_sweep_working_safe') &&
        str_contains($script, 'storage.link_sweep_incomplete') &&
        str_contains($script, 'storage.link_sweep_removing') &&
        str_contains($script, 'storage.link_sweep_preview_skipped') &&
        str_contains($script, 'storage.link_sweep_left_alone'),
    'broken-link sweep safety and result text retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'storage.link_repoint_dialog_title') &&
        str_contains($script, 'storage.link_make_directory_confirm') &&
        str_contains($script, 'storage.link_remove_confirm') &&
        str_contains($script, 'storage.link_repointed_missing'),
    'broken-link remediation controls retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'storage.link_sweep_capped') &&
        str_contains($script, 'storage.link_sweep_remove_confirm') &&
        str_contains($script, 'storage.link_sweep_pointed_at'),
    'broken-link sweep limits and result table retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'storage.symlink_kind_loop') &&
        str_contains($script, 'storage.symlink_filter_empty') &&
        str_contains($script, 'storage.symlink_showing_capped'),
    'symlink filters and tables retain direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'storage.symlink_walking_tree') &&
        str_contains($script, 'storage.symlink_summary_complete') &&
        str_contains($script, 'storage.symlink_healthy_partial'),
    'symlink check status retains direct user-visible text',
);
i18nExpect(
    $script !== false &&
        str_contains($script, 'storage.ldconfig_intro') &&
        str_contains($script, 'storage.ldconfig_confirm') &&
        str_contains($script, 'storage.ldconfig_previous_failed'),
    'library-cache rebuild flow retains direct user-visible text',
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
];
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
                array_unique($dynamicDashboardKeys[1] ?? []),
                array_keys($catalog),
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
        str_contains($script, "'ai.tool_group_tab'") &&
        is_array($english) &&
        isset($english['ai.tool_group.diagnostic']) &&
        isset($english['ai.tool_group_tab']),
    'AI tool-group labels are not localized through their shared helper',
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
echo "i18n checks passed\n";
