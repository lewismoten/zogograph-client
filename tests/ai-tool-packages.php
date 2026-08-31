<?php
declare(strict_types=1);

function aiToolPackageExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$catalogue = file_get_contents($root . '/lib/aitools.php');
aiToolPackageExpect(
    $catalogue !== false,
    'central AI tool catalogue is unavailable',
);
aiToolPackageExpect(
    preg_match('/^\s{8}\'[a-z0-9_]+\'\s*=>\s*\[/m', $catalogue) !== 1,
    'central AI tool catalogue still contains an inline tool declaration',
);
aiToolPackageExpect(
    str_contains($catalogue, "\$tool['package'] = 'core-ai-' . \$slug;") &&
        str_contains(
            $catalogue,
            "\$tool['locale_namespace'] = \$tool['package'] . '.';",
        ),
    'core AI tool packages do not expose stable package and locale identities',
);
aiToolPackageExpect(
    str_contains($catalogue, 'function aiToolCoreLocaleCatalogs(): array') &&
        str_contains($catalogue, "str_starts_with(\$key, \$package . '.')"),
    'core AI tool packages do not provide namespace-bound locale catalogs',
);

$modules = glob($root . '/lib/ai-tools/*.php') ?: [];
aiToolPackageExpect($modules !== [], 'no core AI tool modules were found');
$toolPackages = [];
foreach ($modules as $module) {
    $source = file_get_contents($module);
    $name = basename($module);
    aiToolPackageExpect(
        $source !== false,
        "AI tool module $name is unavailable",
    );
    aiToolPackageExpect(
        preg_match_all(
            '/function\s+(aiTool[A-Za-z0-9]+Package)\s*\(\)\s*:\s*array/',
            $source,
            $functions,
        ) === 1,
        "AI tool module $name must declare exactly one package function",
    );
    aiToolPackageExpect(
        preg_match_all('/return\s+\[\s*\'[a-z0-9_]+\'\s*=>\s*\[/s', $source) ===
            1,
        "AI tool module $name must return exactly one tool declaration",
    );
    preg_match(
        '/return\s+\[\s*\'([a-z0-9_]+)\'\s*=>\s*\[/s',
        (string) $source,
        $declaration,
    );
    $toolPackages[] =
        'core-ai-' . str_replace('_', '-', (string) ($declaration[1] ?? ''));
    $function = $functions[1][0];
    aiToolPackageExpect(
        str_contains($catalogue, "require_once __DIR__ . '/ai-tools/$name';") &&
            str_contains($catalogue, "...$function()"),
        "AI tool module $name is not assembled through the central policy catalogue",
    );
}

$english = json_decode(
    (string) file_get_contents($root . '/locales/en.json'),
    true,
);
aiToolPackageExpect(
    is_array($english),
    'shared English catalog is unavailable',
);
$localePackages =
    glob($root . '/lib/ai-tools/locales/core-ai-*', GLOB_ONLYDIR) ?: [];
sort($toolPackages, SORT_STRING);
$actualPackages = array_map('basename', $localePackages);
sort($actualPackages, SORT_STRING);
aiToolPackageExpect(
    $toolPackages === $actualPackages,
    'core AI tool locale directories do not exactly match the one-tool package declarations',
);
foreach ($toolPackages as $package) {
    $directory = $root . '/lib/ai-tools/locales/' . $package;
    aiToolPackageExpect(
        !isset($english[$package . '.summary']),
        "$package still owns its summary in the shared catalog",
    );
    $referenceKeys = null;
    foreach (['en', 'es', 'hi', 'ar', 'zh'] as $locale) {
        $file = $directory . '/' . $locale . '.json';
        $catalog = is_file($file)
            ? json_decode((string) file_get_contents($file), true)
            : null;
        aiToolPackageExpect(
            is_array($catalog) &&
                is_string($catalog[$package . '.summary'] ?? null),
            "$package does not provide its $locale summary in its own package catalog",
        );
        $keys = array_keys($catalog ?? []);
        sort($keys, SORT_STRING);
        aiToolPackageExpect(
            $keys !== [] &&
                array_reduce(
                    $keys,
                    static fn(bool $valid, string $key): bool => $valid &&
                        str_starts_with($key, $package . '.'),
                    true,
                ),
            "$package has a $locale locale key outside its package namespace",
        );
        if ($referenceKeys === null) {
            $referenceKeys = $keys;
        } else {
            aiToolPackageExpect(
                $keys === $referenceKeys,
                "$package does not provide the same locale keys in $locale",
            );
        }
    }
}

echo "AI tool package checks passed\n";
