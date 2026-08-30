<?php
declare(strict_types=1);

/* Registry and storage boundary for executable packages. This deliberately
 * does not include package entry points. Loading code comes only after a
 * separately reviewed isolation runtime is implemented. */
require_once __DIR__ . '/package-integrity.php';
require_once __DIR__ . '/setupstore.php';
require_once __DIR__ . '/encryption.php';

const PACKAGE_RUNTIME_CAPABILITIES = [
    'storage_backend',
    'live_database_encryption',
    'package_private_database',
    'key_provider',
    'ciphertext_replica',
    'network_client',
];

/* This is a wire/API compatibility declaration, not evidence that PHP process
 * isolation exists.  A future runner must reject packages newer than this
 * contract before it loads their signed entry point. */
const PACKAGE_RUNTIME_API_VERSION = '1.0';
const PACKAGE_ARCHIVE_MAX_BYTES = 67108864;

/** @return array<string, array{publisher:string,public_key:string}> */
function packageRuntimeTrustedKeys(array $keys): array
{
    $out = [];
    if (count($keys) > 100) {
        throw new InvalidArgumentException(
            'too many trusted package publisher keys',
        );
    }
    if ($keys !== [] && !defined('SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES')) {
        throw new InvalidArgumentException(
            'PHP sodium support is required for trusted package publisher keys',
        );
    }
    foreach ($keys as $keyId => $key) {
        $keyId = trim((string) $keyId);
        $publisher = trim(
            (string) (is_array($key) ? $key['publisher'] ?? '' : ''),
        );
        $public = base64_decode(
            (string) (is_array($key) ? $key['public_key'] ?? '' : ''),
            true,
        );
        if (
            !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $keyId) ||
            $publisher === '' ||
            mb_strlen($publisher) > 120 ||
            $public === false ||
            strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
        ) {
            throw new InvalidArgumentException(
                'a trusted package publisher key is invalid',
            );
        }
        $out[$keyId] = [
            'publisher' => $publisher,
            'public_key' => base64_encode($public),
        ];
    }
    ksort($out, SORT_STRING);
    return $out;
}

/** @return array{id:string,version:string,publisher:string,capabilities:list<string>,dependencies:list<string>,dashboard_pages:list<array>,dashboard_cards:list<array>,runtime:array,settings_schema:?string,setup_panels:list<array>,data:array,compatibility:array,integrity:array,signature:array} */
function packageRuntimeManifest(string $directory, array $trustedKeys): array
{
    $root = realpath($directory);
    if (
        $root === false ||
        is_link($directory) ||
        !is_file($root . '/manifest.json')
    ) {
        throw new InvalidArgumentException('package manifest is unavailable');
    }
    $raw = json_decode(
        (string) file_get_contents($root . '/manifest.json'),
        true,
        128,
        JSON_THROW_ON_ERROR,
    );
    if (!is_array($raw)) {
        throw new InvalidArgumentException('package manifest is invalid');
    }
    $id = trim((string) ($raw['id'] ?? ''));
    $version = trim((string) ($raw['version'] ?? ''));
    $publisher = trim((string) ($raw['publisher'] ?? ''));
    $capabilities = array_values(
        array_unique(
            array_filter((array) ($raw['capabilities'] ?? []), 'is_string'),
        ),
    );
    if (
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id) ||
        !preg_match(
            '/^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][a-z0-9.-]+)?$/i',
            $version,
        ) ||
        $publisher === '' ||
        $capabilities === [] ||
        count($capabilities) > 12 ||
        array_diff($capabilities, PACKAGE_RUNTIME_CAPABILITIES) !== []
    ) {
        throw new InvalidArgumentException(
            'package identity or capabilities are invalid',
        );
    }
    sort($capabilities, SORT_STRING);
    $runtime = (array) ($raw['runtime'] ?? []);
    $runtimeApi = trim((string) ($runtime['minimum_api'] ?? ''));
    $providerClass = trim((string) ($runtime['provider_class'] ?? ''));
    $operations = array_values(
        array_unique((array) ($runtime['operations'] ?? [])),
    );
    $allowedOperations = match ($id) {
        'sqlcipher-encrypted-sqlite' => ['capability', 'sqlcipher_preflight'],
        'zogograph-key-custody' => [
            'capability',
            'custody_endpoint_probe',
            'custody_enrollment_check',
        ],
        default => ['capability'],
    };
    if (
        ($runtime['kind'] ?? '') !== 'zogograph-isolated-php-package' ||
        !preg_match('/^[0-9]+\.[0-9]+$/', $runtimeApi) ||
        !preg_match(
            '/^(?:[A-Za-z_][A-Za-z0-9_]*\\\\)*[A-Za-z_][A-Za-z0-9_]*$/',
            $providerClass,
        ) ||
        $operations !== $allowedOperations ||
        !packageRuntimeRelativePath((string) ($runtime['entrypoint'] ?? '')) ||
        !is_file($root . '/' . $runtime['entrypoint'])
    ) {
        throw new InvalidArgumentException(
            'package runtime declaration is invalid',
        );
    }
    $runtime['minimum_api'] = $runtimeApi;
    $runtime['provider_class'] = $providerClass;
    $runtime['operations'] = $operations;
    foreach (['settings_schema'] as $field) {
        if (
            isset($raw[$field]) &&
            (!packageRuntimeRelativePath((string) $raw[$field]) ||
                !is_file($root . '/' . $raw[$field]))
        ) {
            throw new InvalidArgumentException(
                'package settings declaration is invalid',
            );
        }
    }
    $setupPanels = packageRuntimeSetupPanelDeclarations(
        (array) ($raw['setup_panels'] ?? []),
    );
    $dashboardPages = packageRuntimeDashboardPageDeclarations(
        (array) ($raw['dashboard_pages'] ?? []),
    );
    $dashboardCards = packageRuntimeDashboardCardDeclarations(
        (array) ($raw['dashboard_cards'] ?? []),
    );
    packageRuntimeOwnLocaleKeys(
        $id,
        $setupPanels,
        $dashboardPages,
        $dashboardCards,
    );
    $locales = packageRuntimeLocaleDeclarations(
        (array) ($raw['locales'] ?? []),
        $root,
        (array) ($raw['integrity']['files'] ?? []),
    );
    $dependencies = array_values(
        array_unique((array) ($raw['dependencies'] ?? [])),
    );
    if (count($dependencies) > 20 || in_array($id, $dependencies, true)) {
        throw new InvalidArgumentException('package dependencies are invalid');
    }
    foreach ($dependencies as $dependency) {
        if (
            !is_string($dependency) ||
            !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $dependency)
        ) {
            throw new InvalidArgumentException(
                'package dependencies are invalid',
            );
        }
    }
    sort($dependencies, SORT_STRING);
    $data = (array) ($raw['data'] ?? []);
    $migrations = array_values((array) ($data['migrations'] ?? []));
    if (
        count($migrations) > 100 ||
        count($migrations) !== count(array_unique($migrations))
    ) {
        throw new InvalidArgumentException(
            'package private migrations are invalid',
        );
    }
    foreach ($migrations as $migration) {
        if (
            !is_string($migration) ||
            !preg_match(
                '#^migrations/[0-9]{3,}[-_][a-z0-9._-]+\.sql$#',
                $migration,
            ) ||
            !packageRuntimeRelativePath($migration) ||
            !is_file($root . '/' . $migration) ||
            !isset($raw['integrity']['files'][$migration])
        ) {
            throw new InvalidArgumentException(
                'package private migrations are invalid',
            );
        }
    }
    sort($migrations, SORT_STRING);
    $data['migrations'] = $migrations;
    $compatibility = packageRuntimeManifestCompatibility(
        (array) ($raw['compatibility'] ?? []),
    );
    $integrity = packageVerifyIntegrity($raw, $root);
    if (!$integrity['ok']) {
        throw new InvalidArgumentException($integrity['detail']);
    }
    $signature = packageVerifySignature($raw, $trustedKeys);
    if (
        !$signature['ok'] ||
        (($trustedKeys[$signature['key_id']] ?? [])['publisher'] ?? null) !==
            $publisher
    ) {
        throw new InvalidArgumentException(
            $signature['ok']
                ? 'package publisher does not match its trusted key'
                : $signature['detail'],
        );
    }
    return [
        'id' => $id,
        'version' => $version,
        'publisher' => $publisher,
        'capabilities' => $capabilities,
        'runtime' => $runtime,
        'settings_schema' => isset($raw['settings_schema'])
            ? (string) $raw['settings_schema']
            : null,
        'dependencies' => $dependencies,
        'locales' => $locales,
        'dashboard_pages' => $dashboardPages,
        'dashboard_cards' => $dashboardCards,
        'setup_panels' => $setupPanels,
        'data' => $data,
        'compatibility' => $compatibility,
        'integrity' => (array) $raw['integrity'],
        'signature' => (array) $raw['signature'],
    ];
}

/** Package chrome may translate only its own signed namespace. */
function packageRuntimeOwnLocaleKeys(
    string $packageId,
    array $setupPanels,
    array $dashboardPages,
    array $dashboardCards,
): void {
    $keys = [];
    foreach ($setupPanels as $panel) {
        $keys[] = $panel['title_key'] ?? null;
    }
    foreach ($dashboardPages as $page) {
        $keys[] = $page['title_key'] ?? null;
        $keys[] = $page['summary_key'] ?? null;
    }
    foreach ($dashboardCards as $card) {
        $keys[] = $card['title_key'] ?? null;
        foreach ((array) ($card['facts'] ?? []) as $fact) {
            $keys[] = $fact['label_key'] ?? null;
            $keys[] = $fact['value_key'] ?? null;
        }
    }
    foreach ($keys as $key) {
        if (
            $key !== null &&
            !str_starts_with((string) $key, $packageId . '.')
        ) {
            throw new InvalidArgumentException(
                'package localization keys must use the package namespace',
            );
        }
    }
}

/** Validates metadata for core-rendered package dashboard pages. */
function packageRuntimeDashboardPageDeclarations(array $pages): array
{
    if (count($pages) > 8) {
        throw new InvalidArgumentException(
            'package dashboard pages are invalid',
        );
    }
    $out = [];
    foreach ($pages as $page) {
        if (!is_array($page)) {
            throw new InvalidArgumentException(
                'package dashboard page is invalid',
            );
        }
        $id = trim((string) ($page['id'] ?? ''));
        $title = trim((string) ($page['title'] ?? ''));
        $summary = trim((string) ($page['summary'] ?? ''));
        $parent = isset($page['parent'])
            ? trim((string) $page['parent'])
            : null;
        $titleKey = isset($page['title_key'])
            ? trim((string) $page['title_key'])
            : null;
        $summaryKey = isset($page['summary_key'])
            ? trim((string) $page['summary_key'])
            : null;
        $keywords = array_values(
            array_unique(
                array_filter(
                    (array) ($page['keywords'] ?? []),
                    static fn(mixed $word): bool => is_string($word) &&
                        preg_match('/^[a-z0-9 _-]{1,40}$/i', $word),
                ),
            ),
        );
        if (
            !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $id) ||
            $title === '' ||
            mb_strlen($title) > 80 ||
            $summary === '' ||
            mb_strlen($summary) > 500 ||
            ($parent !== null &&
                !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $parent)) ||
            ($titleKey !== null &&
                !preg_match('/^[a-z][\w.-]{0,119}$/i', $titleKey)) ||
            ($summaryKey !== null &&
                !preg_match('/^[a-z][\w.-]{0,119}$/i', $summaryKey)) ||
            count($keywords) !== count((array) ($page['keywords'] ?? [])) ||
            isset($out[$id])
        ) {
            throw new InvalidArgumentException(
                'package dashboard page is invalid',
            );
        }
        $out[$id] = [
            'id' => $id,
            'title' => $title,
            'summary' => $summary,
            'keywords' => $keywords,
            'parent' => $parent,
            'title_key' => $titleKey,
            'summary_key' => $summaryKey,
        ];
    }
    return array_values($out);
}

/**
 * Signed metadata for a card rendered only by an allowlisted core renderer.
 * A package may name and translate the card, but cannot supply HTML, CSS,
 * JavaScript, a query, or a data source.
 */
function packageRuntimeDashboardCardDeclarations(array $cards): array
{
    if (count($cards) > 16) {
        throw new InvalidArgumentException(
            'package dashboard cards are invalid',
        );
    }
    $out = [];
    foreach ($cards as $card) {
        if (!is_array($card)) {
            throw new InvalidArgumentException(
                'package dashboard card is invalid',
            );
        }
        $id = trim((string) ($card['id'] ?? ''));
        $renderer = trim((string) ($card['renderer'] ?? ''));
        $title = trim((string) ($card['title'] ?? ''));
        $titleKey = isset($card['title_key'])
            ? trim((string) $card['title_key'])
            : null;
        $facts = [];
        foreach ((array) ($card['facts'] ?? []) as $fact) {
            if (!is_array($fact)) {
                throw new InvalidArgumentException(
                    'package dashboard card is invalid',
                );
            }
            $label = trim((string) ($fact['label'] ?? ''));
            $value = trim((string) ($fact['value'] ?? ''));
            $labelKey = isset($fact['label_key'])
                ? trim((string) $fact['label_key'])
                : null;
            $valueKey = isset($fact['value_key'])
                ? trim((string) $fact['value_key'])
                : null;
            if (
                $label === '' ||
                $value === '' ||
                mb_strlen($label) > 80 ||
                mb_strlen($value) > 300 ||
                ($labelKey !== null &&
                    !preg_match('/^[a-z][\w.-]{0,119}$/i', $labelKey)) ||
                ($valueKey !== null &&
                    !preg_match('/^[a-z][\w.-]{0,119}$/i', $valueKey))
            ) {
                throw new InvalidArgumentException(
                    'package dashboard card is invalid',
                );
            }
            $facts[] = [
                'label' => $label,
                'value' => $value,
                'label_key' => $labelKey,
                'value_key' => $valueKey,
            ];
        }
        if (
            !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $id) ||
            !in_array($renderer, ['overview-facts'], true) ||
            $title === '' ||
            mb_strlen($title) > 80 ||
            count($facts) > 6 ||
            ($titleKey !== null &&
                !preg_match('/^[a-z][\w.-]{0,119}$/i', $titleKey)) ||
            isset($out[$id])
        ) {
            throw new InvalidArgumentException(
                'package dashboard card is invalid',
            );
        }
        $out[$id] = [
            'id' => $id,
            'renderer' => $renderer,
            'title' => $title,
            'title_key' => $titleKey,
            'facts' => $facts,
        ];
    }
    return array_values($out);
}

/** Validates signed, inert translation-file declarations. */
function packageRuntimeLocaleDeclarations(
    array $locales,
    string $root,
    array $integrityFiles,
): array {
    if (count($locales) > 20 || array_is_list($locales)) {
        throw new InvalidArgumentException(
            'package locale declarations are invalid',
        );
    }
    $out = [];
    foreach ($locales as $locale => $path) {
        $locale = trim((string) $locale);
        $path = trim((string) $path);
        if (
            !preg_match('/^[a-z]{2,3}(?:-[A-Z]{2})?$/', $locale) ||
            !packageRuntimeRelativePath($path) ||
            !str_ends_with($path, '.json') ||
            !is_file($root . '/' . $path) ||
            !isset($integrityFiles[$path])
        ) {
            throw new InvalidArgumentException(
                'package locale declaration is invalid',
            );
        }
        $out[$locale] = $path;
    }
    ksort($out, SORT_STRING);
    return $out;
}

/** Validates signed, inert Setup navigation metadata. No renderer is accepted. */
function packageRuntimeSetupPanelDeclarations(array $panels): array
{
    if (count($panels) > 8) {
        throw new InvalidArgumentException(
            'package declares too many setup panels',
        );
    }
    $out = [];
    foreach ($panels as $panel) {
        if (!is_array($panel)) {
            throw new InvalidArgumentException(
                'package setup panel is invalid',
            );
        }
        $id = trim((string) ($panel['id'] ?? ''));
        $title = trim((string) ($panel['title'] ?? ''));
        $parent = isset($panel['parent'])
            ? trim((string) $panel['parent'])
            : null;
        $titleKey = isset($panel['title_key'])
            ? trim((string) $panel['title_key'])
            : null;
        $keywords = array_values(
            array_unique(
                array_filter(
                    (array) ($panel['keywords'] ?? []),
                    static fn(mixed $word): bool => is_string($word) &&
                        preg_match('/^[a-z0-9 _-]{1,40}$/i', $word),
                ),
            ),
        );
        if (
            !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $id) ||
            $title === '' ||
            mb_strlen($title) > 80 ||
            ($parent !== null &&
                !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $parent)) ||
            ($titleKey !== null &&
                !preg_match('/^[a-z][\w.-]{0,119}$/i', $titleKey)) ||
            count($keywords) !== count((array) ($panel['keywords'] ?? []))
        ) {
            throw new InvalidArgumentException(
                'package setup panel is invalid',
            );
        }
        if (isset($out[$id])) {
            throw new InvalidArgumentException(
                'package setup panel ids must be unique',
            );
        }
        $out[$id] = [
            'id' => $id,
            'title' => $title,
            'title_key' => $titleKey,
            'keywords' => $keywords,
            'parent' => $parent,
        ];
    }
    return array_values($out);
}

/** Normalizes requirements before a package is installed; this never loads its code. */
function packageRuntimeManifestCompatibility(array $compatibility): array
{
    $minimumPhp = trim((string) ($compatibility['min_php'] ?? ''));
    if (
        $minimumPhp !== '' &&
        !preg_match('/^[0-9]+\.[0-9]+(?:\.[0-9]+)?$/', $minimumPhp)
    ) {
        throw new InvalidArgumentException(
            'package minimum PHP version is invalid',
        );
    }
    $extensions = array_values(
        array_unique((array) ($compatibility['requires_extensions'] ?? [])),
    );
    $runtime = $compatibility['requires_runtime_capability'] ?? [];
    $runtime = is_string($runtime)
        ? [$runtime]
        : array_values(array_unique((array) $runtime));
    if (count($extensions) > 40 || count($runtime) > 20) {
        throw new InvalidArgumentException(
            'package compatibility requirements are too large',
        );
    }
    foreach ([$extensions, $runtime] as $requirements) {
        foreach ($requirements as $requirement) {
            if (
                !is_string($requirement) ||
                !preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $requirement)
            ) {
                throw new InvalidArgumentException(
                    'package compatibility requirement is invalid',
                );
            }
        };
    }
    sort($extensions, SORT_STRING);
    sort($runtime, SORT_STRING);
    return [
        'min_php' => $minimumPhp,
        'requires_extensions' => $extensions,
        'requires_runtime_capability' => $runtime,
    ];
}

/** @return array{status:string,detail:string} Safe host facts; package PHP is never loaded. */
function packageRuntimeCompatibility(array $manifest): array
{
    $runtime = (array) ($manifest['runtime'] ?? []);
    $minimumApi = trim((string) ($runtime['minimum_api'] ?? ''));
    if (!preg_match('/^[0-9]+\.[0-9]+$/', $minimumApi)) {
        return [
            'status' => 'unavailable',
            'detail' => 'package runtime API declaration is invalid',
        ];
    }
    if (version_compare(PACKAGE_RUNTIME_API_VERSION, $minimumApi, '<')) {
        return [
            'status' => 'unavailable',
            'detail' =>
                'requires package runtime API ' . $minimumApi . ' or newer',
        ];
    }
    $requirements = packageRuntimeManifestCompatibility(
        (array) ($manifest['compatibility'] ?? []),
    );
    if (
        $requirements['min_php'] !== '' &&
        version_compare(PHP_VERSION, $requirements['min_php'], '<')
    ) {
        return [
            'status' => 'unavailable',
            'detail' =>
                'requires PHP ' . $requirements['min_php'] . ' or newer',
        ];
    }
    foreach ($requirements['requires_extensions'] as $extension) {
        if (!extension_loaded($extension)) {
            return [
                'status' => 'unavailable',
                'detail' => 'requires PHP extension ' . $extension,
            ];
        }
    }
    foreach ($requirements['requires_runtime_capability'] as $capability) {
        if ($capability === 'isolated_package_runtime') {
            return [
                'status' => 'unavailable',
                'detail' =>
                    'a deployment-attested isolated provider runtime is not installed; the capability worker alone does not activate packages',
            ];
        }
        if ($capability === 'encrypted_sqlite_driver') {
            if (!packageRuntimeHasSqlcipherDriver()) {
                return [
                    'status' => 'unavailable',
                    'detail' =>
                        'a SQLCipher-capable PDO SQLite driver was not detected',
                ];
            }
            continue;
        }
        return [
            'status' => 'unavailable',
            'detail' =>
                'requires unsupported runtime capability ' . $capability,
        ];
    }
    return [
        'status' => 'ready',
        'detail' =>
            'platform requirements are met; package execution remains separately governed',
    ];
}

/** Checks only core-maintained package records; package code is never loaded. */
function packageRuntimeDependencyStatus(PDO $pdo, array $manifest): array
{
    $dependencies = array_values((array) ($manifest['dependencies'] ?? []));
    foreach ($dependencies as $dependency) {
        $stmt = $pdo->prepare(
            'SELECT manifest_json, manifest_sha256, install_path, enabled_at FROM runtime_packages WHERE package_id = ?',
        );
        $stmt->execute([$dependency]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return [
                'ok' => false,
                'detail' =>
                    'requires package ' .
                    $dependency .
                    ' to be installed and enabled',
            ];
        }
        $required = json_decode((string) $row['manifest_json'], true);
        $integrity = is_array($required)
            ? packageRuntimeVerifyInstalledPackage(
                $required,
                (string) $row['install_path'],
                (string) $row['manifest_sha256'],
            )
            : ['ok' => false];
        if (
            $row['enabled_at'] === null ||
            !$integrity['ok'] ||
            packageRuntimeCompatibility((array) $required)['status'] !== 'ready'
        ) {
            return [
                'ok' => false,
                'detail' =>
                    'requires package ' .
                    $dependency .
                    ' to remain enabled and healthy',
            ];
        }
        $revoked = $pdo->prepare(
            'SELECT COUNT(*) FROM runtime_package_revocations WHERE package_id = ?',
        );
        $revoked->execute([$dependency]);
        if ((int) $revoked->fetchColumn() !== 0) {
            return [
                'ok' => false,
                'detail' =>
                    'requires package ' .
                    $dependency .
                    ' to remain enabled and healthy',
            ];
        }
    }
    return [
        'ok' => true,
        'detail' =>
            $dependencies === []
                ? 'no package dependencies'
                : 'package dependencies are enabled and healthy',
    ];
}

/** PDO probing is core-owned and read-only; it is not package code execution. */
function packageRuntimeHasSqlcipherDriver(): bool
{
    if (
        !class_exists(PDO::class) ||
        !in_array('sqlite', PDO::getAvailableDrivers(), true)
    ) {
        return false;
    }
    try {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return trim(
            (string) $pdo->query('PRAGMA cipher_version')->fetchColumn(),
        ) !== '';
    } catch (Throwable) {
        return false;
    }
}

function packageRuntimeRelativePath(string $path): bool
{
    return $path !== '' &&
        !str_starts_with($path, '/') &&
        !str_contains($path, "\0") &&
        !in_array('..', explode('/', str_replace('\\', '/', $path)), true);
}

function packageRuntimeDirectory(array $config): string
{
    $directory = rtrim(
        (string) ($config['package_runtime']['directory'] ?? ''),
        '/',
    );
    if ($directory === '' || is_link($directory)) {
        throw new InvalidArgumentException(
            'package runtime directory is unsafe',
        );
    }
    if (
        !is_dir($directory) &&
        !@mkdir($directory, 0700, true) &&
        !is_dir($directory)
    ) {
        throw new RuntimeException(
            'could not create package runtime directory',
        );
    }
    $real = realpath($directory);
    if ($real === false || !is_writable($real)) {
        throw new RuntimeException('package runtime directory is unavailable');
    }
    @chmod($real, 0700);
    return $real;
}

/** @return array{id:string,version:string,publisher:string,install_path:string} */
function packageRuntimeInstall(
    array $config,
    PDO $pdo,
    string $source,
    string $actor,
): array {
    $trusted = packageRuntimeTrustedKeys(
        (array) ($config['package_runtime']['trusted_keys'] ?? []),
    );
    $manifest = packageRuntimeManifest($source, $trusted);
    $root = realpath($source);
    $base = packageRuntimeDirectory($config);
    $target = $base . '/' . $manifest['id'] . '-' . $manifest['version'];
    if (file_exists($target)) {
        throw new InvalidArgumentException(
            'that package version is already installed',
        );
    }
    if (!mkdir($target, 0700, true)) {
        throw new RuntimeException('could not create package directory');
    }
    try {
        packageRuntimeCopyFile(
            $root . '/manifest.json',
            $target . '/manifest.json',
        );
        foreach (array_keys($manifest['integrity']['files']) as $relative) {
            packageRuntimeCopyFile(
                $root . '/' . $relative,
                $target . '/' . $relative,
            );
        }
    } catch (Throwable $error) {
        packageRuntimeRemoveDirectory($target);
        throw $error;
    }
    $manifestHash = hash_file('sha256', $target . '/manifest.json');
    if (
        !is_string($manifestHash) ||
        !preg_match('/^[a-f0-9]{64}$/', $manifestHash)
    ) {
        packageRuntimeRemoveDirectory($target);
        throw new RuntimeException(
            'could not fingerprint installed package manifest',
        );
    }
    $now = time();
    $pdo->prepare(
        'INSERT INTO runtime_packages (package_id, version, publisher, manifest_json, manifest_sha256, install_path, installed_at, enabled_at, changed_at, changed_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)',
    )->execute([
        $manifest['id'],
        $manifest['version'],
        $manifest['publisher'],
        json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        $manifestHash,
        $target,
        $now,
        $now,
        mb_substr($actor, 0, 120),
    ]);
    return [
        'id' => $manifest['id'],
        'version' => $manifest['version'],
        'publisher' => $manifest['publisher'],
        'install_path' => $target,
    ];
}

/** Installs a signed ZIP through a private, bounded extraction directory. */
function packageRuntimeInstallArchive(
    array $config,
    PDO $pdo,
    string $archive,
    string $actor,
): array {
    if (!class_exists(ZipArchive::class)) {
        throw new RuntimeException(
            'PHP ZipArchive support is required to install package archives',
        );
    }
    if (!is_file($archive) || is_link($archive) || !is_readable($archive)) {
        throw new InvalidArgumentException(
            'uploaded package archive is unavailable',
        );
    }
    $bytes = filesize($archive);
    if (!is_int($bytes) || $bytes < 1 || $bytes > PACKAGE_ARCHIVE_MAX_BYTES) {
        throw new InvalidArgumentException(
            'package archive is empty or exceeds 64 MiB',
        );
    }
    $zip = new ZipArchive();
    if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
        throw new InvalidArgumentException(
            'package archive is not a readable ZIP file',
        );
    }
    $zipOpen = true;
    $base = packageRuntimeDirectory($config);
    $temporary = $base . '/.upload-' . bin2hex(random_bytes(16));
    if (!mkdir($temporary, 0700)) {
        $zip->close();
        throw new RuntimeException(
            'could not prepare private package extraction',
        );
    }
    try {
        if ($zip->numFiles < 1 || $zip->numFiles > PACKAGE_MAX_FILES + 1) {
            throw new InvalidArgumentException(
                'package archive contains too many entries',
            );
        }
        $names = [];
        $total = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if (!is_array($stat)) {
                throw new InvalidArgumentException(
                    'package archive entry is invalid',
                );
            }
            $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
            if (
                $name === '' ||
                str_ends_with($name, '/') ||
                !packageRuntimeRelativePath($name) ||
                isset($names[$name])
            ) {
                throw new InvalidArgumentException(
                    'package archive contains an unsafe path',
                );
            }
            $names[$name] = true;
            $size = (int) ($stat['size'] ?? -1);
            $compressed = (int) ($stat['comp_size'] ?? -1);
            if (
                $size < 0 ||
                $size > PACKAGE_MAX_FILE_BYTES ||
                $compressed < 0 ||
                ($size > 1048576 && $compressed === 0) ||
                ($compressed > 0 && $size > $compressed * 200) ||
                ($total += $size) > PACKAGE_ARCHIVE_MAX_BYTES
            ) {
                throw new InvalidArgumentException(
                    'package archive has an unsafe expanded size',
                );
            }
            $attributes = 0;
            $opsys = 0;
            if (
                !$zip->getExternalAttributesIndex($index, $opsys, $attributes)
            ) {
                throw new InvalidArgumentException(
                    'package archive entry attributes are unavailable',
                );
            }
            $type = ($attributes >> 16) & 0170000;
            if ($type !== 0 && $type !== 0100000) {
                throw new InvalidArgumentException(
                    'package archive contains a non-regular file',
                );
            }
            $destination = $temporary . '/' . $name;
            $parent = dirname($destination);
            if (!is_dir($parent) && !mkdir($parent, 0700, true)) {
                throw new RuntimeException(
                    'could not prepare package archive path',
                );
            }
            $input = $zip->getStream((string) $stat['name']);
            $output = @fopen($destination, 'xb');
            if (!is_resource($input) || !is_resource($output)) {
                if (is_resource($input)) {
                    fclose($input);
                }
                if (is_resource($output)) {
                    fclose($output);
                }
                throw new RuntimeException(
                    'could not read package archive entry',
                );
            }
            $written = 0;
            while (!feof($input)) {
                $chunk = fread($input, 1048576);
                if (
                    $chunk === false ||
                    fwrite($output, $chunk) !== strlen($chunk) ||
                    ($written += strlen($chunk)) > $size
                ) {
                    fclose($input);
                    fclose($output);
                    throw new RuntimeException(
                        'could not safely extract package archive entry',
                    );
                }
            }
            fclose($input);
            fclose($output);
            if ($written !== $size) {
                throw new InvalidArgumentException(
                    'package archive entry size does not match its index',
                );
            }
            @chmod($destination, 0640);
        }
        if (!isset($names['manifest.json'])) {
            throw new InvalidArgumentException(
                'package archive is missing manifest.json',
            );
        }
        $zip->close();
        $zipOpen = false;
        return packageRuntimeInstall($config, $pdo, $temporary, $actor);
    } finally {
        if ($zipOpen) {
            $zip->close();
        }
        packageRuntimeRemoveDirectory($temporary);
    }
}

function packageRuntimeCopyFile(string $source, string $destination): void
{
    if (is_link($source) || !is_file($source) || !is_readable($source)) {
        throw new InvalidArgumentException('package source file is unsafe');
    }
    $parent = dirname($destination);
    if (!is_dir($parent) && !mkdir($parent, 0700, true) && !is_dir($parent)) {
        throw new RuntimeException('could not create package file directory');
    }
    if (!copy($source, $destination)) {
        throw new RuntimeException('could not install package file');
    }
    @chmod($destination, 0640);
}

function packageRuntimeRemoveDirectory(string $directory): void
{
    if (!is_dir($directory) || is_link($directory)) {
        return;
    }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::SKIP_DOTS,
        ),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($items as $item) {
        $path = $item->getPathname();
        if ($item->isLink()) {
            @unlink($path);
        } elseif ($item->isDir()) {
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($directory);
}

/** @return list<array{id:string,version:string,publisher:string,enabled:bool,revoked:bool,integrity:bool,integrity_detail:string,compatibility:array,dependencies:list<string>,dependency_status:array,locales:array,dashboard_pages:list<array>,dashboard_cards:list<array>,capabilities:list<string>,grants:list<string>,setup_panels:list<array>,state:string}> */
function packageRuntimePackages(PDO $pdo): array
{
    $grants = [];
    foreach (
        $pdo
            ->query('SELECT package_id, capability FROM runtime_package_grants')
            ->fetchAll()
        as $row
    ) {
        $grants[$row['package_id']][] = $row['capability'];
    }
    $out = [];
    foreach (
        $pdo
            ->query(
                'SELECT packages.package_id, packages.version, packages.publisher, packages.manifest_json, packages.manifest_sha256, packages.install_path, packages.enabled_at, revocations.reason AS revocation_reason
        FROM runtime_packages packages LEFT JOIN runtime_package_revocations revocations ON revocations.package_id = packages.package_id ORDER BY packages.package_id',
            )
            ->fetchAll()
        as $row
    ) {
        $manifest = json_decode((string) $row['manifest_json'], true) ?: [];
        $required = (array) ($manifest['capabilities'] ?? []);
        $given = $grants[$row['package_id']] ?? [];
        sort($given, SORT_STRING);
        $revoked = $row['revocation_reason'] !== null;
        $integrity = packageRuntimeVerifyInstalledPackage(
            $manifest,
            (string) $row['install_path'],
            (string) ($row['manifest_sha256'] ?? ''),
        );
        $compatibility = packageRuntimeCompatibility($manifest);
        $dependencyStatus = packageRuntimeDependencyStatus($pdo, $manifest);
        $enabled =
            $integrity['ok'] &&
            !$revoked &&
            $compatibility['status'] === 'ready' &&
            $dependencyStatus['ok'] &&
            $row['enabled_at'] !== null &&
            $required === $given;
        $out[] = [
            'id' => $row['package_id'],
            'version' => $row['version'],
            'publisher' => $row['publisher'],
            'enabled' => $enabled,
            'revoked' => $revoked,
            'integrity' => $integrity['ok'],
            'integrity_detail' => $integrity['detail'],
            'capabilities' => $required,
            'grants' => $given,
            'setup_panels' => packageRuntimeSetupPanelDeclarations(
                (array) ($manifest['setup_panels'] ?? []),
            ),
            'compatibility' => $compatibility,
            'dependencies' => (array) ($manifest['dependencies'] ?? []),
            'dependency_status' => $dependencyStatus,
            'locales' => (array) ($manifest['locales'] ?? []),
            'dashboard_pages' => (array) ($manifest['dashboard_pages'] ?? []),
            'dashboard_cards' => (array) ($manifest['dashboard_cards'] ?? []),
            'state' => !$integrity['ok']
                ? 'integrity failed: ' . $integrity['detail']
                : ($revoked
                    ? 'revoked: ' . $row['revocation_reason']
                    : ($compatibility['status'] !== 'ready'
                        ? 'platform unavailable: ' . $compatibility['detail']
                        : (!$dependencyStatus['ok']
                            ? 'dependency unavailable: ' .
                                $dependencyStatus['detail']
                            : ($enabled
                                ? 'registered; ' . $compatibility['detail']
                                : 'needs explicit capability grants')))),
        ];
    }
    return $out;
}

/** @return list<array{route:string,package_id:string,id:string,title:string,summary:string,keywords:list<string>}> */
function packageRuntimeDashboardPages(PDO $pdo): array
{
    $out = [];
    foreach (packageRuntimePackages($pdo) as $package) {
        foreach (
            $package['enabled'] ? $package['dashboard_pages'] : []
            as $page
        ) {
            $out[] =
                [
                    'route' => 'package-' . $package['id'] . '-' . $page['id'],
                    'package_id' => $package['id'],
                ] + $page;
        };
    }
    return $out;
}

/** @return list<array{package_id:string,id:string,renderer:string,title:string,title_key:?string}> */
function packageRuntimeDashboardCards(PDO $pdo): array
{
    $out = [];
    foreach (packageRuntimePackages($pdo) as $package) {
        foreach (
            $package['enabled'] ? $package['dashboard_cards'] : []
            as $card
        ) {
            $out[] = ['package_id' => $package['id']] + $card;
        };
    }
    return $out;
}

/** @return array<string, array<string, array<string, string>>> enabled package id => locale => catalog */
function packageRuntimeLocaleCatalogs(PDO $pdo): array
{
    $out = [];
    $paths = $pdo->prepare(
        'SELECT install_path FROM runtime_packages WHERE package_id = ?',
    );
    foreach (packageRuntimePackages($pdo) as $package) {
        if (!$package['enabled'] || $package['locales'] === []) {
            continue;
        }
        $paths->execute([$package['id']]);
        $path = (string) $paths->fetchColumn();
        $root = realpath($path);
        if ($root === false || is_link($path)) {
            continue;
        }
        foreach ($package['locales'] as $locale => $relative) {
            $file = realpath($root . '/' . $relative);
            if (
                $file === false ||
                is_link($root . '/' . $relative) ||
                !is_file($file) ||
                !str_starts_with($file, rtrim($root, '/') . '/') ||
                filesize($file) > 262144
            ) {
                continue;
            }
            try {
                $raw = json_decode(
                    (string) file_get_contents($file),
                    true,
                    64,
                    JSON_THROW_ON_ERROR,
                );
            } catch (Throwable) {
                continue;
            }
            if (!is_array($raw) || array_is_list($raw) || count($raw) > 1000) {
                continue;
            }
            $catalog = [];
            $prefix = (string) $package['id'] . '.';
            foreach ($raw as $key => $value) {
                if (
                    is_string($key) &&
                    str_starts_with($key, $prefix) &&
                    preg_match('/^[a-z][\w.-]{0,119}$/i', $key) &&
                    is_string($value) &&
                    mb_strlen($value) <= 4000
                ) {
                    $catalog[$key] = $value;
                }
            }
            if ($catalog !== []) {
                $out[$package['id']][(string) $locale] = $catalog;
            }
        }
    }
    return $out;
}

function packageRuntimeSetEnabled(
    PDO $pdo,
    string $id,
    array $grants,
    bool $enabled,
    string $actor,
): void {
    $row = $pdo->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $row->execute([$id]);
    $package = $row->fetch();
    $manifest = is_array($package)
        ? json_decode((string) $package['manifest_json'], true)
        : null;
    if (!is_array($manifest)) {
        throw new InvalidArgumentException('package is not installed');
    }
    if ($enabled) {
        $integrity = packageRuntimeVerifyInstalledPackage(
            $manifest,
            (string) $package['install_path'],
            (string) ($package['manifest_sha256'] ?? ''),
        );
        if (!$integrity['ok']) {
            throw new InvalidArgumentException(
                'installed package integrity failed: ' . $integrity['detail'],
            );
        }
        $revoked = $pdo->prepare(
            'SELECT COUNT(*) FROM runtime_package_revocations WHERE package_id = ?',
        );
        $revoked->execute([$id]);
        if ((int) $revoked->fetchColumn() !== 0) {
            throw new InvalidArgumentException(
                'package is revoked; lift the revocation only after review',
            );
        }
        $compatibility = packageRuntimeCompatibility($manifest);
        if ($compatibility['status'] !== 'ready') {
            throw new InvalidArgumentException(
                'package platform is unavailable: ' . $compatibility['detail'],
            );
        }
        $dependencies = packageRuntimeDependencyStatus($pdo, $manifest);
        if (!$dependencies['ok']) {
            throw new InvalidArgumentException(
                'package dependency is unavailable: ' . $dependencies['detail'],
            );
        }
    }
    $required = (array) ($manifest['capabilities'] ?? []);
    $grants = array_values(
        array_unique(
            array_intersect(
                PACKAGE_RUNTIME_CAPABILITIES,
                array_map('strval', $grants),
            ),
        ),
    );
    sort($required, SORT_STRING);
    sort($grants, SORT_STRING);
    if ($enabled && $required !== $grants) {
        throw new InvalidArgumentException(
            'grant every capability declared by the signed package before enabling it',
        );
    }
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'DELETE FROM runtime_package_grants WHERE package_id = ?',
        )->execute([$id]);
        $insert = $pdo->prepare(
            'INSERT INTO runtime_package_grants (package_id, capability, granted_at, granted_by) VALUES (?, ?, ?, ?)',
        );
        foreach ($grants as $grant) {
            $insert->execute([$id, $grant, time(), mb_substr($actor, 0, 120)]);
        }
        $pdo->prepare(
            'UPDATE runtime_packages SET enabled_at = ?, changed_at = ?, changed_by = ? WHERE package_id = ?',
        )->execute([
            $enabled ? time() : null,
            time(),
            mb_substr($actor, 0, 120),
            $id,
        ]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

/** Removes a disabled package's immutable files and registry record only. */
function packageRuntimeRemove(array $config, PDO $pdo, string $id): void
{
    $id = trim($id);
    $stmt = $pdo->prepare(
        'SELECT enabled_at, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        throw new InvalidArgumentException('package is not installed');
    }
    if ($row['enabled_at'] !== null) {
        throw new InvalidArgumentException(
            'disable the package before removing it',
        );
    }
    $base = packageRuntimeDirectory($config);
    $path = (string) $row['install_path'];
    $real = realpath($path);
    if (
        $real === false ||
        is_link($path) ||
        dirname($real) !== $base ||
        !is_dir($real)
    ) {
        throw new RuntimeException(
            'installed package directory is unavailable or unsafe',
        );
    }
    $detached = $base . '/.removing-' . bin2hex(random_bytes(16));
    if (!rename($real, $detached)) {
        throw new RuntimeException('could not detach the package files');
    }
    try {
        $pdo->beginTransaction();
        $deleted = $pdo->prepare(
            'DELETE FROM runtime_packages WHERE package_id = ?',
        );
        $deleted->execute([$id]);
        if ($deleted->rowCount() !== 1) {
            throw new RuntimeException(
                'package registry changed while removing it',
            );
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        @rename($detached, $real);
        throw $error;
    }
    packageRuntimeRemoveDirectory($detached);
    if (is_dir($detached)) {
        throw new RuntimeException(
            'package was removed from the registry but its detached files need administrator cleanup',
        );
    }
}

/** Records the minimum explicit approval for the credential-only custody
 * diagnostic. It never enables package execution or grants private storage. */
function packageRuntimeApproveCustodyDiagnostic(
    PDO $pdo,
    string $id,
    string $actor,
): void {
    if ($id !== 'zogograph-key-custody') {
        throw new InvalidArgumentException(
            'this diagnostic approval is only available for the custody package',
        );
    }
    $row = $pdo->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $row->execute([$id]);
    $package = $row->fetch();
    $manifest = is_array($package)
        ? json_decode((string) $package['manifest_json'], true)
        : null;
    if (!is_array($manifest)) {
        throw new InvalidArgumentException('package is not installed');
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) $package['manifest_sha256'],
    );
    if (!$integrity['ok']) {
        throw new InvalidArgumentException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $revoked = $pdo->prepare(
        'SELECT COUNT(*) FROM runtime_package_revocations WHERE package_id = ?',
    );
    $revoked->execute([$id]);
    if ((int) $revoked->fetchColumn() !== 0) {
        throw new InvalidArgumentException(
            'package is revoked; review it before approving any diagnostic permission',
        );
    }
    $required = ['key_provider', 'network_client'];
    if (
        array_diff($required, (array) ($manifest['capabilities'] ?? [])) !== []
    ) {
        throw new InvalidArgumentException(
            'signed custody package lacks required diagnostic capabilities',
        );
    }
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            'INSERT OR IGNORE INTO runtime_package_grants (package_id, capability, granted_at, granted_by) VALUES (?, ?, ?, ?)',
        );
        foreach ($required as $capability) {
            $insert->execute([
                $id,
                $capability,
                time(),
                mb_substr($actor, 0, 120),
            ]);
        }
        $pdo->prepare(
            'UPDATE runtime_packages SET changed_at = ?, changed_by = ? WHERE package_id = ?',
        )->execute([time(), mb_substr($actor, 0, 120), $id]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

/** @return array{ok:bool,detail:string} */
function packageRuntimeVerifyInstalledPackage(
    array $manifest,
    string $directory,
    string $manifestHash,
): array {
    $root = realpath($directory);
    if ($root === false || is_link($directory) || !is_dir($root)) {
        return [
            'ok' => false,
            'detail' => 'installed package directory is unavailable',
        ];
    }
    if (!preg_match('/^[a-f0-9]{64}$/', $manifestHash)) {
        return [
            'ok' => false,
            'detail' =>
                'install-time manifest fingerprint is unavailable; reinstall the package',
        ];
    }
    $actual = hash_file('sha256', $root . '/manifest.json');
    if (!is_string($actual) || !hash_equals($manifestHash, $actual)) {
        return [
            'ok' => false,
            'detail' => 'installed package manifest has changed',
        ];
    }
    return packageVerifyIntegrity($manifest, $root);
}

function packageRuntimeRevoke(
    PDO $pdo,
    string $id,
    string $reason,
    string $actor,
): void {
    packageRuntimeInstalled($pdo, $id);
    $reason = trim($reason);
    if ($reason === '' || mb_strlen($reason) > 500) {
        throw new InvalidArgumentException(
            'package revocation reason is required and must be at most 500 characters',
        );
    }
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO runtime_package_revocations (package_id, reason, revoked_at, revoked_by) VALUES (?, ?, ?, ?)
            ON CONFLICT(package_id) DO UPDATE SET reason = excluded.reason, revoked_at = excluded.revoked_at, revoked_by = excluded.revoked_by',
        )->execute([$id, $reason, time(), mb_substr($actor, 0, 120)]);
        $pdo->prepare(
            'UPDATE runtime_packages SET enabled_at = NULL, changed_at = ?, changed_by = ? WHERE package_id = ?',
        )->execute([time(), mb_substr($actor, 0, 120), $id]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

function packageRuntimeLiftRevocation(PDO $pdo, string $id): void
{
    packageRuntimeInstalled($pdo, $id);
    $stmt = $pdo->prepare(
        'DELETE FROM runtime_package_revocations WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException('package is not revoked');
    }
}

function packageRuntimePrivateDatabase(
    PDO $registry,
    array $config,
    string $id,
): PDO {
    packageRuntimeInstalled($registry, $id);
    $grant = $registry->prepare(
        'SELECT enabled_at, manifest_json FROM runtime_packages WHERE package_id = ?',
    );
    $grant->execute([$id]);
    $package = $grant->fetch();
    if (!is_array($package) || $package['enabled_at'] === null) {
        throw new InvalidArgumentException('package is not enabled');
    }
    $manifest = json_decode((string) $package['manifest_json'], true);
    if (!is_array($manifest)) {
        throw new RuntimeException('installed package is unavailable');
    }
    $compatibility = packageRuntimeCompatibility($manifest);
    if ($compatibility['status'] !== 'ready') {
        throw new InvalidArgumentException(
            'package platform is unavailable: ' . $compatibility['detail'],
        );
    }
    $grant = $registry->prepare(
        'SELECT COUNT(*) FROM runtime_package_grants WHERE package_id = ? AND capability = ?',
    );
    $grant->execute([$id, 'package_private_database']);
    if ((int) $grant->fetchColumn() !== 1) {
        throw new InvalidArgumentException(
            'package lacks private database permission',
        );
    }
    $base = packageRuntimeDirectory($config);
    $directory = $base . '/' . $id . '-data';
    if (
        is_link($directory) ||
        (!is_dir($directory) &&
            !mkdir($directory, 0700, true) &&
            !is_dir($directory))
    ) {
        throw new RuntimeException('could not create package data directory');
    }
    $directory = realpath($directory);
    if (
        $directory === false ||
        is_link($directory) ||
        !str_starts_with($directory, rtrim($base, '/') . '/')
    ) {
        throw new RuntimeException(
            'package data directory is unavailable or unsafe',
        );
    }
    @chmod($directory, 0700);
    $path = $directory . '/package.db';
    if (is_link($path)) {
        throw new RuntimeException(
            'package database path must not be a symbolic link',
        );
    }
    $previousUmask = umask(0077);
    try {
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        packageRuntimePrivateSqliteFiles($path);
        packageRuntimeApplyPrivateMigrations($registry, $pdo, $id);
        return $pdo;
    } finally {
        umask($previousUmask);
    }
}

/** A package's database and journals must not become readable by other users. */
function packageRuntimePrivateSqliteFiles(string $path): void
{
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
        if (is_file($file) && !is_link($file)) {
            @chmod($file, 0600);
        }
    }
}

/** Applies only signed declarative SQL to the package's own SQLite file. */
function packageRuntimeApplyPrivateMigrations(
    PDO $registry,
    PDO $private,
    string $id,
): void {
    $row = $registry->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $row->execute([$id]);
    $package = $row->fetch();
    if (!is_array($package)) {
        throw new InvalidArgumentException('package is not installed');
    }
    $manifest = json_decode((string) $package['manifest_json'], true);
    if (!is_array($manifest)) {
        throw new RuntimeException('installed package is unavailable');
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) ($package['manifest_sha256'] ?? ''),
    );
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $root = realpath((string) $package['install_path']);
    if ($root === false) {
        throw new RuntimeException('installed package is unavailable');
    }
    $migrations = (array) (($manifest['data'] ?? [])['migrations'] ?? []);
    $private->exec(
        'CREATE TABLE IF NOT EXISTS package_runtime_migrations (migration TEXT PRIMARY KEY, applied_at INTEGER NOT NULL)',
    );
    $applied = $private
        ->query('SELECT migration FROM package_runtime_migrations')
        ->fetchAll(PDO::FETCH_COLUMN);
    foreach ($migrations as $migration) {
        if (in_array($migration, $applied, true)) {
            continue;
        }
        $path = $root . '/' . $migration;
        $real = realpath($path);
        if (
            $real === false ||
            is_link($path) ||
            !is_file($real) ||
            !str_starts_with($real, rtrim($root, '/') . '/')
        ) {
            throw new RuntimeException('package migration path is unsafe');
        }
        $sql = (string) file_get_contents($real);
        if (
            strlen($sql) === 0 ||
            strlen($sql) > 131072 ||
            str_contains($sql, "\0") ||
            preg_match(
                '/\b(?:attach|detach|vacuum|pragma|load_extension|begin|commit|rollback)\b/i',
                $sql,
            )
        ) {
            throw new RuntimeException(
                'package migration uses an unsupported SQLite operation',
            );
        }
        $private->beginTransaction();
        try {
            $private->exec($sql);
            $private
                ->prepare(
                    'INSERT INTO package_runtime_migrations (migration, applied_at) VALUES (?, ?)',
                )
                ->execute([$migration, time()]);
            $private->commit();
        } catch (Throwable $error) {
            $private->rollBack();
            throw $error;
        }
    }
}

/** Package settings live in setup.db, not the package's metric/database file.
 * Secret values are references to the encrypted local secret vault, never raw keys. */
function packageRuntimeSettings(PDO $pdo, array $config, string $id): array
{
    packageRuntimeInstalled($pdo, $id);
    $store = setupStoreOpen($config, true);
    if ($store === null) {
        throw new RuntimeException('could not open package settings store');
    }
    $store->exec('CREATE TABLE IF NOT EXISTS package_runtime_settings (
        package_id TEXT PRIMARY KEY, value TEXT NOT NULL, updated_at INTEGER NOT NULL, updated_by TEXT NOT NULL
    )');
    $value = $store->prepare(
        'SELECT value FROM package_runtime_settings WHERE package_id = ?',
    );
    $value->execute([$id]);
    $decoded = json_decode((string) $value->fetchColumn(), true);
    return array_replace(
        packageRuntimeSettingsDefaults($pdo, $id),
        is_array($decoded) ? $decoded : [],
    );
}

/** Safe schema defaults reduce setup friction; they never synthesize secrets. */
function packageRuntimeSettingsDefaults(PDO $pdo, string $id): array
{
    $stmt = $pdo->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    $manifest = is_array($package)
        ? json_decode((string) $package['manifest_json'], true)
        : null;
    $relative = is_array($manifest)
        ? (string) ($manifest['settings_schema'] ?? '')
        : '';
    if ($relative === '') {
        return [];
    }
    $root = realpath((string) $package['install_path']);
    $path = $root === false ? false : realpath($root . '/' . $relative);
    if (
        $root === false ||
        $path === false ||
        is_link($root . '/' . $relative) ||
        !is_file($path) ||
        !str_starts_with($path, rtrim($root, '/') . '/')
    ) {
        throw new RuntimeException(
            'installed package settings schema is unavailable',
        );
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) ($package['manifest_sha256'] ?? ''),
    );
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $schema = json_decode(
        (string) file_get_contents($path),
        true,
        64,
        JSON_THROW_ON_ERROR,
    );
    if (!is_array($schema) || ($schema['type'] ?? null) !== 'object') {
        throw new InvalidArgumentException(
            'package settings schema is invalid',
        );
    }
    $defaults = [];
    foreach ((array) ($schema['properties'] ?? []) as $name => $property) {
        if (
            is_string($name) &&
            is_array($property) &&
            array_key_exists('default', $property) &&
            !preg_match('/(?:secret|token|password|private[_-]?key)/i', $name)
        ) {
            packageRuntimeValidateSchemaValue(
                $property['default'],
                $property,
                'settings default.' . $name,
                0,
            );
            $defaults[$name] = $property['default'];
        }
    }
    return $defaults;
}

/**
 * Top-level schema properties that accept a package secret reference.
 * The generic Setup editor can offer a picker for these without reading a
 * secret or guessing about nested package-owned configuration structures.
 *
 * @return list<string>
 */
function packageRuntimeSecretSettingNames(PDO $pdo, string $id): array
{
    $stmt = $pdo->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    $manifest = is_array($package)
        ? json_decode((string) $package['manifest_json'], true)
        : null;
    $relative = is_array($manifest)
        ? (string) ($manifest['settings_schema'] ?? '')
        : '';
    if ($relative === '') {
        return [];
    }
    $root = realpath((string) $package['install_path']);
    $path = $root === false ? false : realpath($root . '/' . $relative);
    if (
        $root === false ||
        $path === false ||
        is_link($root . '/' . $relative) ||
        !is_file($path) ||
        !str_starts_with($path, rtrim($root, '/') . '/')
    ) {
        throw new RuntimeException(
            'installed package settings schema is unavailable',
        );
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) ($package['manifest_sha256'] ?? ''),
    );
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $schema = json_decode(
        (string) file_get_contents($path),
        true,
        64,
        JSON_THROW_ON_ERROR,
    );
    $out = [];
    foreach ((array) ($schema['properties'] ?? []) as $name => $property) {
        if (
            is_string($name) &&
            is_array($property) &&
            preg_match('/(?:secret|token|password|private[_-]?key)/i', $name)
        ) {
            $out[] = $name;
        }
    }
    sort($out, SORT_STRING);
    return $out;
}

function packageRuntimeSaveSettings(
    PDO $pdo,
    array $config,
    string $id,
    array $settings,
    string $actor,
): void {
    packageRuntimeInstalled($pdo, $id);
    $prior = packageRuntimeSettings($pdo, $config, $id);
    packageRuntimeValidateSettings($settings);
    packageRuntimeValidateSettingsSecretReferences($settings, $id);
    packageRuntimeValidateManifestSettings($pdo, $id, $settings);
    $references = packageRuntimeSettingsSecretReferences($settings, $id);
    if ($id === 'zogograph-key-custody' && $references !== []) {
        $probe = packageRuntimeProbeOperationEvidence(
            $config,
            $id,
            'custody_endpoint_probe',
        );
        if (
            $probe === null ||
            $probe['state'] !== 'endpoint-ready' ||
            $probe['probed_at'] < time() - 86400
        ) {
            throw new InvalidArgumentException(
                'run a successful custody endpoint test within the last 24 hours before saving enrollment secret references',
            );
        }
    }
    foreach ($references as $reference) {
        if (!packageRuntimeSecretExists($config, $id, $reference)) {
            throw new InvalidArgumentException(
                'store the referenced package secret before saving these settings',
            );
        }
    }
    $store = setupStoreOpen($config, true);
    if ($store === null) {
        throw new RuntimeException('could not open package settings store');
    }
    $store->exec('CREATE TABLE IF NOT EXISTS package_runtime_settings (
        package_id TEXT PRIMARY KEY, value TEXT NOT NULL, updated_at INTEGER NOT NULL, updated_by TEXT NOT NULL
    )');
    $value = json_encode(
        $settings,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    if (strlen($value) > 65536) {
        throw new InvalidArgumentException('package settings are too large');
    }
    $store
        ->prepare(
            'INSERT INTO package_runtime_settings (package_id, value, updated_at, updated_by) VALUES (?, ?, ?, ?)
        ON CONFLICT(package_id) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at, updated_by = excluded.updated_by',
        )
        ->execute([$id, $value, time(), mb_substr($actor, 0, 120)]);
    $endpointChanged =
        $id !== 'zogograph-key-custody' ||
        ($prior['service_base_url'] ?? null) !==
            ($settings['service_base_url'] ?? null) ||
        ($prior['tls_spki_pin'] ?? null) !==
            ($settings['tls_spki_pin'] ?? null);
    if ($endpointChanged) {
        packageRuntimeClearProbeEvidence($config, $id);
    }
}

/** Enforces a small, predictable JSON Schema subset from the signed package.
 * Unsupported keywords are deliberately ignored until they are implemented. */
function packageRuntimeValidateManifestSettings(
    PDO $pdo,
    string $id,
    array $settings,
): void {
    $stmt = $pdo->prepare(
        'SELECT manifest_json, manifest_sha256, install_path FROM runtime_packages WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    if (!is_array($package)) {
        throw new InvalidArgumentException('package is not installed');
    }
    $manifest = json_decode((string) $package['manifest_json'], true);
    $relative = is_array($manifest)
        ? (string) ($manifest['settings_schema'] ?? '')
        : '';
    if ($relative === '') {
        return;
    }
    $root = realpath((string) $package['install_path']);
    $path = $root === false ? false : realpath($root . '/' . $relative);
    if (
        $root === false ||
        $path === false ||
        is_link($root . '/' . $relative) ||
        !is_file($path) ||
        !str_starts_with($path, rtrim($root, '/') . '/')
    ) {
        throw new RuntimeException(
            'installed package settings schema is unavailable',
        );
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) ($package['manifest_sha256'] ?? ''),
    );
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $schema = json_decode(
        (string) file_get_contents($path),
        true,
        64,
        JSON_THROW_ON_ERROR,
    );
    if (!is_array($schema)) {
        throw new InvalidArgumentException(
            'package settings schema is invalid',
        );
    }
    packageRuntimeValidateSchemaValue($settings, $schema, 'settings', 0);
}

function packageRuntimeValidateSchemaValue(
    mixed $value,
    array $schema,
    string $name,
    int $depth,
): void {
    if ($depth > 12) {
        throw new InvalidArgumentException(
            'package settings schema is nested too deeply',
        );
    }
    $type = $schema['type'] ?? null;
    $valid = match ($type) {
        null => true,
        'object' => is_array($value) && !array_is_list($value),
        'array' => is_array($value) && array_is_list($value),
        'string' => is_string($value),
        'integer' => is_int($value),
        'number' => is_int($value) || is_float($value),
        'boolean' => is_bool($value),
        'null' => $value === null,
        default => throw new InvalidArgumentException(
            'package settings schema has an unsupported type',
        ),
    };
    if (!$valid) {
        throw new InvalidArgumentException($name . ' has the wrong type');
    }
    if (
        isset($schema['enum']) &&
        (!is_array($schema['enum']) || !in_array($value, $schema['enum'], true))
    ) {
        throw new InvalidArgumentException($name . ' is not an allowed value');
    }
    if (is_string($value)) {
        if (
            isset($schema['minLength']) &&
            (!is_int($schema['minLength']) ||
                mb_strlen($value) < $schema['minLength'])
        ) {
            throw new InvalidArgumentException($name . ' is too short');
        }
        if (
            isset($schema['maxLength']) &&
            (!is_int($schema['maxLength']) ||
                mb_strlen($value) > $schema['maxLength'])
        ) {
            throw new InvalidArgumentException($name . ' is too long');
        }
        if (isset($schema['pattern'])) {
            if (!is_string($schema['pattern'])) {
                throw new InvalidArgumentException(
                    'package settings schema pattern is invalid',
                );
            }
            $match = @preg_match(
                '~' . str_replace('~', '\\~', $schema['pattern']) . '~uD',
                $value,
            );
            if ($match === false) {
                throw new InvalidArgumentException(
                    'package settings schema pattern is invalid',
                );
            }
            if ($match !== 1) {
                throw new InvalidArgumentException(
                    $name . ' has an invalid format',
                );
            }
        }
    }
    if (is_int($value) || is_float($value)) {
        if (
            isset($schema['minimum']) &&
            ((!is_int($schema['minimum']) && !is_float($schema['minimum'])) ||
                $value < $schema['minimum'])
        ) {
            throw new InvalidArgumentException(
                $name . ' is below the allowed minimum',
            );
        }
        if (
            isset($schema['maximum']) &&
            ((!is_int($schema['maximum']) && !is_float($schema['maximum'])) ||
                $value > $schema['maximum'])
        ) {
            throw new InvalidArgumentException(
                $name . ' is above the allowed maximum',
            );
        }
    }
    if (is_array($value) && array_is_list($value)) {
        if (
            isset($schema['minItems']) &&
            (!is_int($schema['minItems']) ||
                count($value) < $schema['minItems'])
        ) {
            throw new InvalidArgumentException($name . ' has too few values');
        }
        if (
            isset($schema['maxItems']) &&
            (!is_int($schema['maxItems']) ||
                count($value) > $schema['maxItems'])
        ) {
            throw new InvalidArgumentException($name . ' has too many values');
        }
        if (
            ($schema['uniqueItems'] ?? false) === true &&
            count(
                array_unique(array_map(fn($item) => serialize($item), $value)),
            ) !== count($value)
        ) {
            throw new InvalidArgumentException(
                $name . ' must not repeat values',
            );
        }
        if (isset($schema['items'])) {
            if (!is_array($schema['items'])) {
                throw new InvalidArgumentException(
                    'package settings schema items are invalid',
                );
            }
            foreach ($value as $index => $item) {
                packageRuntimeValidateSchemaValue(
                    $item,
                    $schema['items'],
                    $name . '[' . $index . ']',
                    $depth + 1,
                );
            }
        }
    }
    if (is_array($value) && !array_is_list($value)) {
        $properties = $schema['properties'] ?? [];
        if (!is_array($properties)) {
            throw new InvalidArgumentException(
                'package settings schema properties are invalid',
            );
        }
        foreach ((array) ($schema['required'] ?? []) as $required) {
            if (!is_string($required) || !array_key_exists($required, $value)) {
                throw new InvalidArgumentException(
                    $name . ' is missing ' . $required,
                );
            }
        }
        if (($schema['additionalProperties'] ?? true) === false) {
            foreach (array_keys($value) as $key) {
                if (!array_key_exists($key, $properties)) {
                    throw new InvalidArgumentException(
                        $name . '.' . $key . ' is not allowed',
                    );
                }
            };
        }
        foreach ($value as $key => $child) {
            if (isset($properties[$key])) {
                if (!is_array($properties[$key])) {
                    throw new InvalidArgumentException(
                        'package settings schema property is invalid',
                    );
                }
                packageRuntimeValidateSchemaValue(
                    $child,
                    $properties[$key],
                    $name . '.' . $key,
                    $depth + 1,
                );
            }
        }
    }
}

function packageRuntimeInstalled(PDO $pdo, string $id): void
{
    if (!preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id)) {
        throw new InvalidArgumentException('package id is invalid');
    }
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM runtime_packages WHERE package_id = ?',
    );
    $stmt->execute([$id]);
    if ((int) $stmt->fetchColumn() !== 1) {
        throw new InvalidArgumentException('package is not installed');
    }
}

function packageRuntimeValidateSettings(array $settings): void
{
    $walk = function (mixed $value, string $name, int $depth) use (
        &$walk,
    ): void {
        if ($depth > 12) {
            throw new InvalidArgumentException(
                'package settings are nested too deeply',
            );
        }
        if (is_array($value)) {
            if (count($value) > 200) {
                throw new InvalidArgumentException(
                    'package settings contain too many values',
                );
            }
            foreach ($value as $key => $child) {
                $walk($child, (string) $key, $depth + 1);
            }
            return;
        }
        if (
            !is_null($value) &&
            !is_bool($value) &&
            !is_int($value) &&
            !is_float($value) &&
            !is_string($value)
        ) {
            throw new InvalidArgumentException(
                'package setting values are invalid',
            );
        }
        if (is_string($value) && strlen($value) > 4096) {
            throw new InvalidArgumentException('a package setting is too long');
        }
        if (
            preg_match('/(?:secret|token|password|private[_-]?key)/i', $name) &&
            is_string($value) &&
            $value !== '' &&
            !str_starts_with($value, 'secret_ref:')
        ) {
            throw new InvalidArgumentException(
                'package secrets must use a secret_ref: reference, not a raw value',
            );
        }
    };
    $walk($settings, '', 0);
}

function packageRuntimeValidateSettingsSecretReferences(
    array $settings,
    string $id,
): void {
    packageRuntimeSettingsSecretReferences($settings, $id);
}

/** @return list<string> References only; this never resolves secret material. */
function packageRuntimeSettingsSecretReferences(
    array $settings,
    string $id,
): array {
    $references = [];
    $walk = function (mixed $value) use (&$walk, $id, &$references): void {
        if (is_array($value)) {
            foreach ($value as $child) {
                $walk($child);
            }
            return;
        }
        if (is_string($value) && str_starts_with($value, 'secret_ref:')) {
            packageRuntimeValidateSecretReference($id, $value);
            $references[] = $value;
        }
    };
    $walk($settings);
    return array_values(array_unique($references));
}

/** Uses a separately configured key when present, otherwise the local
 * sealed-copy key. The file itself stays outside the web root and setup.db. */
function packageRuntimeSecretKey(array $config): string
{
    $runtime = (array) ($config['package_runtime'] ?? []);
    $encryption = (array) ($config['encryption'] ?? []);
    $path =
        trim((string) ($runtime['secret_key_file'] ?? '')) ?:
        trim((string) ($encryption['local_key_file'] ?? ''));
    if ($path === '') {
        throw new RuntimeException(
            'configure a private package-secret key file or a local encryption key before saving package secrets',
        );
    }
    $master = encryptionReadLocalKey($path, dirname(__DIR__));
    $key = hash_hkdf(
        'sha256',
        $master,
        32,
        'HostScope package secret vault v1',
    );
    if (!is_string($key) || strlen($key) !== 32) {
        throw new RuntimeException('could not derive the package secret key');
    }
    return $key;
}

function packageRuntimeSecretReference(string $id): string
{
    if (!preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id)) {
        throw new InvalidArgumentException('package id is invalid');
    }
    return 'secret_ref:' .
        $id .
        ':' .
        rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
}

function packageRuntimeValidateSecretReference(
    string $id,
    string $reference,
): void {
    if (
        !preg_match(
            '/^secret_ref:' . preg_quote($id, '/') . ':[A-Za-z0-9_-]{16,200}$/',
            $reference,
        )
    ) {
        throw new InvalidArgumentException(
            'package secret reference is invalid or belongs to another package',
        );
    }
}

function packageRuntimeSecretStore(
    PDO $registry,
    array $config,
    string $id,
    string $value,
    string $actor,
): string {
    packageRuntimeInstalled($registry, $id);
    if ($value === '' || strlen($value) > 8192) {
        throw new InvalidArgumentException(
            'package secret must be between 1 and 8192 bytes',
        );
    }
    $store = setupStoreOpen($config, true);
    if ($store === null) {
        throw new RuntimeException('could not open package secrets store');
    }
    $store->exec('CREATE TABLE IF NOT EXISTS package_runtime_secrets (
        reference TEXT PRIMARY KEY, package_id TEXT NOT NULL, nonce BLOB NOT NULL, ciphertext BLOB NOT NULL, tag BLOB NOT NULL,
        created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL, updated_by TEXT NOT NULL
    )');
    $reference = packageRuntimeSecretReference($id);
    $nonce = random_bytes(12);
    $key = packageRuntimeSecretKey($config);
    $ciphertext = openssl_encrypt(
        $value,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        $reference,
        16,
    );
    if (!is_string($ciphertext) || !is_string($tag)) {
        throw new RuntimeException('could not encrypt package secret');
    }
    $store
        ->prepare(
            'INSERT INTO package_runtime_secrets (reference, package_id, nonce, ciphertext, tag, created_at, updated_at, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        )
        ->execute([
            $reference,
            $id,
            $nonce,
            $ciphertext,
            $tag,
            time(),
            time(),
            mb_substr($actor, 0, 120),
        ]);
    return $reference;
}

/** Only the eventual isolated runtime may call this after checking a matching
 * declared secret reference; it is never returned by an HTTP settings API. */
function packageRuntimeSecretValue(
    PDO $registry,
    array $config,
    string $id,
    string $reference,
): string {
    packageRuntimeInstalled($registry, $id);
    packageRuntimeValidateSecretReference($id, $reference);
    $store = setupStoreOpen($config, false);
    if ($store === null) {
        throw new InvalidArgumentException('package secret is unavailable');
    }
    $stmt = $store->prepare(
        'SELECT nonce, ciphertext, tag FROM package_runtime_secrets WHERE reference = ? AND package_id = ?',
    );
    $stmt->execute([$reference, $id]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        throw new InvalidArgumentException('package secret is unavailable');
    }
    $plain = openssl_decrypt(
        $row['ciphertext'],
        'aes-256-gcm',
        packageRuntimeSecretKey($config),
        OPENSSL_RAW_DATA,
        $row['nonce'],
        $row['tag'],
        $reference,
    );
    if (!is_string($plain) || $plain === '') {
        throw new RuntimeException('package secret could not be opened');
    }
    return $plain;
}

/** Checks only package ownership and record presence; it never decrypts a secret. */
function packageRuntimeSecretExists(
    array $config,
    string $id,
    string $reference,
): bool {
    packageRuntimeValidateSecretReference($id, $reference);
    $store = setupStoreOpen($config, false);
    if (
        $store === null ||
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_secrets'",
            )
            ->fetchColumn() !== 1
    ) {
        return false;
    }
    $stmt = $store->prepare(
        'SELECT COUNT(*) FROM package_runtime_secrets WHERE reference = ? AND package_id = ?',
    );
    $stmt->execute([$reference, $id]);
    return (int) $stmt->fetchColumn() === 1;
}

/** @return list<array{reference:string,created_at:int,updated_at:int,referenced:bool}> */
function packageRuntimeSecrets(PDO $registry, array $config, string $id): array
{
    packageRuntimeInstalled($registry, $id);
    $store = setupStoreOpen($config, false);
    if (
        $store === null ||
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_secrets'",
            )
            ->fetchColumn() !== 1
    ) {
        return [];
    }
    $referenced = packageRuntimeSettingsSecretReferences(
        packageRuntimeSettings($registry, $config, $id),
        $id,
    );
    $stmt = $store->prepare(
        'SELECT reference, created_at, updated_at FROM package_runtime_secrets WHERE package_id = ? ORDER BY updated_at DESC',
    );
    $stmt->execute([$id]);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[] = [
            'reference' => (string) $row['reference'],
            'created_at' => (int) $row['created_at'],
            'updated_at' => (int) $row['updated_at'],
            'referenced' => in_array(
                (string) $row['reference'],
                $referenced,
                true,
            ),
        ];
    }
    return $out;
}

function packageRuntimeSecretDelete(
    PDO $registry,
    array $config,
    string $id,
    string $reference,
): void {
    packageRuntimeInstalled($registry, $id);
    packageRuntimeValidateSecretReference($id, $reference);
    $store = setupStoreOpen($config, false);
    if ($store === null) {
        throw new InvalidArgumentException('package secret is unavailable');
    }
    if (
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_settings'",
            )
            ->fetchColumn() === 1
    ) {
        $settings = $store->prepare(
            'SELECT value FROM package_runtime_settings WHERE package_id = ?',
        );
        $settings->execute([$id]);
        $encoded = $settings->fetchColumn();
        $value = is_string($encoded) ? json_decode($encoded, true) : null;
        if (
            is_array($value) &&
            in_array(
                $reference,
                packageRuntimeSettingsSecretReferences($value, $id),
                true,
            )
        ) {
            throw new InvalidArgumentException(
                'remove this secret reference from package settings before deleting it',
            );
        }
    }
    $stmt = $store->prepare(
        'DELETE FROM package_runtime_secrets WHERE reference = ? AND package_id = ?',
    );
    $stmt->execute([$reference, $id]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException('package secret is unavailable');
    }
}

/** Reads a deployment-owned launcher definition. It is intentionally absent
 * from browser/package settings: changing an executable command is host policy. */
function packageRuntimeProbeInvoker(
    array $config,
    string $id,
    string $manifestHash,
): array {
    $entry =
        (array) (((array) ($config['package_runtime'] ?? []))['probe_invokers'][
            $id
        ] ?? []);
    $command = array_values((array) ($entry['command'] ?? []));
    $profile = trim((string) ($entry['isolation_profile'] ?? ''));
    $declaredHash = strtolower(
        trim((string) ($entry['package_manifest_sha256'] ?? '')),
    );
    if (
        count($command) < 1 ||
        count($command) > 24 ||
        !preg_match('/^[a-z0-9][a-z0-9._:-]{2,120}$/i', $profile) ||
        !preg_match('/^[a-f0-9]{64}$/', $declaredHash) ||
        !hash_equals($manifestHash, $declaredHash)
    ) {
        throw new RuntimeException(
            'no reviewed isolated probe launcher is configured for this exact package release',
        );
    }
    foreach ($command as $index => $argument) {
        if (
            !is_string($argument) ||
            $argument === '' ||
            strlen($argument) > 1024 ||
            str_contains($argument, "\0")
        ) {
            throw new RuntimeException(
                'isolated probe launcher command is invalid',
            );
        }
        if (
            $index === 0 &&
            (!str_starts_with($argument, '/') ||
                !is_file($argument) ||
                !is_executable($argument))
        ) {
            throw new RuntimeException(
                'isolated probe launcher executable is unavailable',
            );
        }
    }
    return ['command' => $command, 'isolation_profile' => $profile];
}

/** A credential check needs a different, reviewed egress-restricted sandbox
 * launcher. The configured origin must match the saved provider endpoint. */
function packageRuntimeCustodyCredentialInvoker(
    array $config,
    string $id,
    string $manifestHash,
    string $serviceBaseUrl,
    string $invokerGroup = 'custody_credential_check_invokers',
    string $purpose = 'credential-check',
): array {
    if (
        !in_array(
            $invokerGroup,
            [
                'custody_credential_check_invokers',
                'custody_envelope_invokers',
                'custody_unwrap_invokers',
            ],
            true,
        )
    ) {
        throw new InvalidArgumentException(
            'custody launcher configuration group is invalid',
        );
    }
    $entry =
        (array) ((array) ($config['package_runtime'] ?? [])[$invokerGroup][
            $id
        ] ?? []);
    $command = array_values((array) ($entry['command'] ?? []));
    $profile = trim((string) ($entry['isolation_profile'] ?? ''));
    $hash = strtolower(
        trim((string) ($entry['package_manifest_sha256'] ?? '')),
    );
    $originParts = parse_url((string) ($entry['network_egress_origin'] ?? ''));
    $originScheme = is_array($originParts)
        ? strtolower((string) ($originParts['scheme'] ?? ''))
        : '';
    $originPort =
        is_array($originParts) && isset($originParts['port'])
            ? (int) $originParts['port']
            : 0;
    $origin = is_array($originParts)
        ? $originScheme .
            '://' .
            strtolower((string) ($originParts['host'] ?? '')) .
            ($originPort !== 0 &&
            !($originScheme === 'https' && $originPort === 443)
                ? ':' . $originPort
                : '')
        : '';
    $parts = parse_url($serviceBaseUrl);
    $scheme = is_array($parts)
        ? strtolower((string) ($parts['scheme'] ?? ''))
        : '';
    $port =
        is_array($parts) && isset($parts['port']) ? (int) $parts['port'] : 0;
    $expected = is_array($parts)
        ? $scheme .
            '://' .
            strtolower((string) ($parts['host'] ?? '')) .
            ($port !== 0 && !($scheme === 'https' && $port === 443)
                ? ':' . $port
                : '')
        : '';
    if (
        $id !== 'zogograph-key-custody' ||
        count($command) < 1 ||
        count($command) > 24 ||
        !preg_match('/^[a-z0-9][a-z0-9._:-]{2,120}$/i', $profile) ||
        !preg_match('/^[a-f0-9]{64}$/', $hash) ||
        !hash_equals($manifestHash, $hash) ||
        $expected === '' ||
        !hash_equals($expected, $origin)
    ) {
        throw new RuntimeException(
            "no reviewed endpoint-restricted custody {$purpose} launcher is configured for this exact package and service origin",
        );
    }
    foreach ($command as $index => $argument) {
        if (
            !is_string($argument) ||
            $argument === '' ||
            strlen($argument) > 1024 ||
            str_contains($argument, "\0") ||
            ($index === 0 &&
                (!str_starts_with($argument, '/') ||
                    !is_file($argument) ||
                    !is_executable($argument)))
        ) {
            throw new RuntimeException(
                "custody {$purpose} launcher command is invalid",
            );
        }
    }
    return ['command' => $command, 'isolation_profile' => $profile];
}

/** The sole credential-bearing package operation. It only checks the signed
 * enrolled-client entitlement endpoint and cannot accept caller-supplied data. */
function packageRuntimeInvokeCustodyEnrollmentCheck(
    PDO $registry,
    array $config,
): array {
    $id = 'zogograph-key-custody';
    $settings = packageRuntimeSettings($registry, $config, $id);
    $readiness = packageRuntimeProviderReadiness($registry, $config, $id);
    if (($readiness['state'] ?? '') !== 'awaiting-isolated-runtime') {
        throw new InvalidArgumentException(
            $readiness['reason'] ?? 'custody enrollment is not ready',
        );
    }
    $grant = $registry->prepare(
        'SELECT capability FROM runtime_package_grants WHERE package_id = ? AND capability IN (?, ?)',
    );
    $grant->execute([$id, 'key_provider', 'network_client']);
    $granted = array_values(
        array_unique(array_column($grant->fetchAll(), 'capability')),
    );
    sort($granted, SORT_STRING);
    if ($granted !== ['key_provider', 'network_client']) {
        throw new InvalidArgumentException(
            'explicitly approve the custody enrollment-check permissions before sending credentials',
        );
    }
    $input = [
        'service_base_url' => (string) $settings['service_base_url'],
        'tls_spki_pin' => $settings['tls_spki_pin'] ?? null,
        'client_credential' => packageRuntimeSecretValue(
            $registry,
            $config,
            $id,
            (string) $settings['client_credential_secret'],
        ),
        'client_signing_key' => packageRuntimeSecretValue(
            $registry,
            $config,
            $id,
            (string) $settings['client_signing_secret'],
        ),
    ];
    return packageRuntimeInvokeProbe(
        $registry,
        $config,
        $id,
        'custody_enrollment_check',
        $input,
        true,
    );
}

/** Adds one remote envelope only after core has rechecked the local artifact.
 * The DEK is opened in memory and passed once to the separate write sandbox. */
function packageRuntimeStoreCustodyEnvelope(
    PDO $registry,
    array $config,
    string $webRoot,
    string $artifactId,
): array {
    $id = 'zogograph-key-custody';
    if (
        $artifactId === '' ||
        strlen($artifactId) > 200 ||
        preg_match('/[\x00-\x1F\x7F]/', $artifactId)
    ) {
        throw new InvalidArgumentException('artifact identity is invalid');
    }
    $settings = packageRuntimeSettings($registry, $config, $id);
    $readiness = packageRuntimeProviderReadiness($registry, $config, $id);
    if (($readiness['state'] ?? '') !== 'awaiting-isolated-runtime') {
        throw new InvalidArgumentException(
            $readiness['reason'] ?? 'custody enrollment is not ready',
        );
    }
    $enrollment = packageRuntimeProbeOperationEvidence(
        $config,
        $id,
        'custody_enrollment_check',
    );
    if (
        $enrollment === null ||
        $enrollment['state'] !== 'enrollment-ready' ||
        $enrollment['probed_at'] < time() - 86400
    ) {
        throw new InvalidArgumentException(
            'run a successful custody enrollment-readiness check within the last 24 hours before storing an envelope',
        );
    }
    $keyId = (string) ($settings['custody_key_id'] ?? '');
    if (!preg_match('/^key_[a-f0-9]{32}$/', $keyId)) {
        throw new InvalidArgumentException(
            'save the active custody key identifier before storing an envelope',
        );
    }
    $query = $registry->prepare(
        "SELECT local_path, ciphertext_sha256 FROM encryption_artifacts WHERE artifact_id = ? AND state = 'verified'",
    );
    $query->execute([$artifactId]);
    $artifact = $query->fetch();
    if (!is_array($artifact)) {
        throw new InvalidArgumentException(
            'a verified local artifact is required before remote custody can be added',
        );
    }
    $directory = encryptionArtifactDirectory($config, $webRoot);
    $root = realpath($directory);
    $path = realpath((string) $artifact['local_path']);
    if (
        $root === false ||
        $path === false ||
        is_link((string) $artifact['local_path']) ||
        !is_file($path) ||
        !str_starts_with($path, rtrim($root, '/') . '/') ||
        !str_ends_with($path, '.sealed')
    ) {
        throw new RuntimeException(
            'the verified artifact file is unavailable or outside the private artifact directory',
        );
    }
    $ciphertext = hash_file('sha256', $path);
    if (
        !is_string($ciphertext) ||
        !hash_equals((string) $artifact['ciphertext_sha256'], $ciphertext)
    ) {
        throw new RuntimeException(
            'the verified artifact ciphertext no longer matches its ledger',
        );
    }
    $encryption = (array) ($config['encryption'] ?? []);
    if (($encryption['mode'] ?? '') !== 'sealed_copies_local') {
        throw new InvalidArgumentException(
            'local sealed-copy recovery must remain enabled before remote custody can be added',
        );
    }
    $dek = encryptionReadArtifactDataKey(
        $path,
        encryptionReadLocalKey(
            (string) ($encryption['local_key_file'] ?? ''),
            $webRoot,
        ),
        $artifactId,
    );
    encryptionRecordRemoteEnvelopePending($registry, $artifactId, $id, $keyId);
    try {
        $result = packageRuntimeInvokeProbe(
            $registry,
            $config,
            $id,
            'custody_store_envelope',
            [
                'service_base_url' => (string) $settings['service_base_url'],
                'tls_spki_pin' => $settings['tls_spki_pin'] ?? null,
                'client_credential' => packageRuntimeSecretValue(
                    $registry,
                    $config,
                    $id,
                    (string) $settings['client_credential_secret'],
                ),
                'client_signing_key' => packageRuntimeSecretValue(
                    $registry,
                    $config,
                    $id,
                    (string) $settings['client_signing_secret'],
                ),
                'custody_key_id' => $keyId,
                'artifact_id' => $artifactId,
                'data_key' => base64_encode($dek),
            ],
            true,
        );
        $receipt = strtolower((string) ($result['envelope_sha256'] ?? ''));
        if (!preg_match('/^[a-f0-9]{64}$/', $receipt)) {
            throw new RuntimeException(
                'custody envelope-store response omitted its receipt digest',
            );
        }
        encryptionRecordRemoteEnvelopeStored(
            $registry,
            $artifactId,
            $id,
            $keyId,
            $ciphertext,
            $receipt,
        );
        return $result;
    } catch (Throwable $error) {
        try {
            encryptionRecordRemoteEnvelopeFailure(
                $registry,
                $artifactId,
                $id,
                $keyId,
                mb_substr($error->getMessage(), 0, 500),
            );
        } catch (Throwable) {
        }
        throw $error;
    }
}

/** Runs a non-destructive remote-custody recovery drill for one stored envelope. */
function packageRuntimeVerifyCustodyEnvelope(
    PDO $registry,
    array $config,
    string $webRoot,
    string $artifactId,
): array {
    $id = 'zogograph-key-custody';
    $settings = packageRuntimeSettings($registry, $config, $id);
    $readiness = packageRuntimeProviderReadiness($registry, $config, $id);
    if (($readiness['state'] ?? '') !== 'awaiting-isolated-runtime') {
        throw new InvalidArgumentException(
            $readiness['reason'] ?? 'custody enrollment is not ready',
        );
    }
    $enrollment = packageRuntimeProbeOperationEvidence(
        $config,
        $id,
        'custody_enrollment_check',
    );
    if (
        $enrollment === null ||
        $enrollment['state'] !== 'enrollment-ready' ||
        $enrollment['probed_at'] < time() - 86400
    ) {
        throw new InvalidArgumentException(
            'run a successful custody enrollment-readiness check within the last 24 hours before a remote recovery drill',
        );
    }
    $grant = $registry->prepare(
        'SELECT capability FROM runtime_package_grants WHERE package_id = ? AND capability IN (?, ?)',
    );
    $grant->execute([$id, 'key_provider', 'network_client']);
    $granted = array_values(
        array_unique(array_column($grant->fetchAll(), 'capability')),
    );
    sort($granted, SORT_STRING);
    if ($granted !== ['key_provider', 'network_client']) {
        throw new InvalidArgumentException(
            'explicitly approve custody key-provider and network-client permissions before a remote recovery drill',
        );
    }
    $keyId = (string) ($settings['custody_key_id'] ?? '');
    $row = $registry->prepare(
        "SELECT a.store, a.local_path, a.ciphertext_sha256 FROM encryption_artifacts a JOIN encryption_remote_envelopes r ON r.artifact_id = a.artifact_id WHERE a.artifact_id = ? AND a.state = 'verified' AND r.provider_package = ? AND r.provider_key_id = ? AND r.state IN ('stored', 'verified')",
    );
    $row->execute([$artifactId, $id, $keyId]);
    $artifact = $row->fetch();
    if (!is_array($artifact)) {
        throw new InvalidArgumentException(
            'a stored or previously verified remote envelope for a verified local artifact is required',
        );
    }
    $path = realpath((string) $artifact['local_path']);
    $root = realpath(encryptionArtifactDirectory($config, $webRoot));
    if (
        $path === false ||
        $root === false ||
        is_link((string) $artifact['local_path']) ||
        !is_file($path) ||
        !str_starts_with($path, rtrim($root, '/') . '/') ||
        !hash_equals(
            (string) $artifact['ciphertext_sha256'],
            (string) hash_file('sha256', $path),
        )
    ) {
        throw new RuntimeException(
            'the local artifact is unavailable or its ciphertext no longer matches the ledger',
        );
    }
    $result = packageRuntimeInvokeProbe(
        $registry,
        $config,
        $id,
        'custody_unwrap_artifact',
        [
            'service_base_url' => (string) $settings['service_base_url'],
            'tls_spki_pin' => $settings['tls_spki_pin'] ?? null,
            'client_credential' => packageRuntimeSecretValue(
                $registry,
                $config,
                $id,
                (string) $settings['client_credential_secret'],
            ),
            'client_signing_key' => packageRuntimeSecretValue(
                $registry,
                $config,
                $id,
                (string) $settings['client_signing_secret'],
            ),
            'custody_key_id' => $keyId,
            'artifact_id' => $artifactId,
        ],
        true,
    );
    $dek = base64_decode((string) ($result['data_key'] ?? ''), true);
    if (!is_string($dek) || strlen($dek) !== 32) {
        throw new RuntimeException(
            'custody recovery response did not contain a valid transient data key',
        );
    }
    $temporary = $root . '/.custody-drill-' . bin2hex(random_bytes(12));
    try {
        encryptionOpenFileWithDataKey($path, $temporary, $dek, $artifactId);
        encryptionVerifyRecoveredStore($temporary, (string) $artifact['store']);
    } finally {
        @unlink($temporary);
    }
    encryptionRecordRemoteEnvelopeVerified($registry, $artifactId, $id, $keyId);
    unset($result['data_key']);
    return $result + [
        'state' => 'recovery-verified',
        'reason' =>
            'Custody released a transient key that authenticated this sealed artifact during a non-destructive recovery drill.',
    ];
}

/**
 * Launches only a signed no-secret readiness probe through an operator-owned
 * sandbox command. It deliberately does not grant isolated_package_runtime or
 * enable a provider: an attestation record is not proof of live-operation safety.
 * @return array{id:string,state:string,reason:string,version?:string}
 */
function packageRuntimeInvokeProbe(
    PDO $registry,
    array $config,
    string $id,
    string $operation = 'capability',
    array $input = [],
    bool $credentialOperation = false,
): array {
    if (!function_exists('proc_open')) {
        throw new RuntimeException(
            'this PHP deployment cannot launch an isolated probe',
        );
    }
    $row = $registry->prepare(
        'SELECT manifest_json, manifest_sha256, install_path, enabled_at FROM runtime_packages WHERE package_id = ?',
    );
    $row->execute([$id]);
    $package = $row->fetch();
    $manifest = is_array($package)
        ? json_decode((string) $package['manifest_json'], true)
        : null;
    if (!is_array($manifest)) {
        throw new InvalidArgumentException('package is not installed');
    }
    $integrity = packageRuntimeVerifyInstalledPackage(
        $manifest,
        (string) $package['install_path'],
        (string) $package['manifest_sha256'],
    );
    if (!$integrity['ok']) {
        throw new RuntimeException(
            'installed package integrity failed: ' . $integrity['detail'],
        );
    }
    $revoked = $registry->prepare(
        'SELECT 1 FROM runtime_package_revocations WHERE package_id = ?',
    );
    $revoked->execute([$id]);
    if ($revoked->fetchColumn()) {
        throw new RuntimeException('package is revoked');
    }
    $signedOperations = (array) ($manifest['runtime']['operations'] ?? []);
    $allowed = $credentialOperation
        ? [
            'custody_enrollment_check',
            'custody_store_envelope',
            'custody_unwrap_artifact',
        ]
        : ['capability', 'sqlcipher_preflight', 'custody_endpoint_probe'];
    if (
        !in_array($operation, $allowed, true) ||
        !in_array($operation, $signedOperations, true)
    ) {
        throw new InvalidArgumentException(
            'package operation is not a signed no-secret probe',
        );
    }
    if ($operation === 'custody_endpoint_probe') {
        if (
            array_diff(array_keys($input), [
                'service_base_url',
                'tls_spki_pin',
            ]) !== [] ||
            !is_string($input['service_base_url'] ?? null) ||
            (($input['tls_spki_pin'] ?? null) !== null &&
                !is_string($input['tls_spki_pin']))
        ) {
            throw new InvalidArgumentException(
                'custody endpoint probe input is invalid',
            );
        }
    } elseif ($operation === 'custody_enrollment_check') {
        if (
            !$credentialOperation ||
            array_diff(array_keys($input), [
                'service_base_url',
                'tls_spki_pin',
                'client_credential',
                'client_signing_key',
            ]) !== [] ||
            !is_string($input['service_base_url'] ?? null) ||
            (($input['tls_spki_pin'] ?? null) !== null &&
                !is_string($input['tls_spki_pin'])) ||
            !is_string($input['client_credential'] ?? null) ||
            !is_string($input['client_signing_key'] ?? null)
        ) {
            throw new InvalidArgumentException(
                'custody credential-check input is invalid',
            );
        }
    } elseif ($operation === 'custody_store_envelope') {
        if (
            !$credentialOperation ||
            array_diff(array_keys($input), [
                'service_base_url',
                'tls_spki_pin',
                'client_credential',
                'client_signing_key',
                'custody_key_id',
                'artifact_id',
                'data_key',
            ]) !== [] ||
            !is_string($input['service_base_url'] ?? null) ||
            (($input['tls_spki_pin'] ?? null) !== null &&
                !is_string($input['tls_spki_pin'])) ||
            !is_string($input['client_credential'] ?? null) ||
            !is_string($input['client_signing_key'] ?? null) ||
            !preg_match(
                '/^key_[a-f0-9]{32}$/',
                (string) ($input['custody_key_id'] ?? ''),
            ) ||
            !is_string($input['artifact_id'] ?? null) ||
            !is_string($input['data_key'] ?? null) ||
            !is_string(base64_decode($input['data_key'], true)) ||
            strlen((string) base64_decode($input['data_key'], true)) !== 32
        ) {
            throw new InvalidArgumentException(
                'custody envelope-store input is invalid',
            );
        }
    } elseif ($operation === 'custody_unwrap_artifact') {
        if (
            !$credentialOperation ||
            array_diff(array_keys($input), [
                'service_base_url',
                'tls_spki_pin',
                'client_credential',
                'client_signing_key',
                'custody_key_id',
                'artifact_id',
            ]) !== [] ||
            !is_string($input['service_base_url'] ?? null) ||
            (($input['tls_spki_pin'] ?? null) !== null &&
                !is_string($input['tls_spki_pin'])) ||
            !is_string($input['client_credential'] ?? null) ||
            !is_string($input['client_signing_key'] ?? null) ||
            !preg_match(
                '/^key_[a-f0-9]{32}$/',
                (string) ($input['custody_key_id'] ?? ''),
            ) ||
            !is_string($input['artifact_id'] ?? null)
        ) {
            throw new InvalidArgumentException(
                'custody recovery input is invalid',
            );
        }
    } elseif ($input !== []) {
        throw new InvalidArgumentException('this probe does not accept input');
    }
    $invoker = $credentialOperation
        ? packageRuntimeCustodyCredentialInvoker(
            $config,
            $id,
            (string) $package['manifest_sha256'],
            (string) $input['service_base_url'],
            $operation === 'custody_store_envelope'
                ? 'custody_envelope_invokers'
                : ($operation === 'custody_unwrap_artifact'
                    ? 'custody_unwrap_invokers'
                    : 'custody_credential_check_invokers'),
            $operation === 'custody_store_envelope'
                ? 'envelope-store'
                : ($operation === 'custody_unwrap_artifact'
                    ? 'recovery-unwrap'
                    : 'credential-check'),
        )
        : packageRuntimeProbeInvoker(
            $config,
            $id,
            (string) $package['manifest_sha256'],
        );
    $invocationId = bin2hex(random_bytes(16));
    $requestData = [
        'api' => PACKAGE_RUNTIME_API_VERSION,
        'invocation_id' => $invocationId,
        'operation' => $operation,
        'package_id' => $id,
    ];
    if ($input !== []) {
        $requestData['input'] = $input;
    }
    $request = json_encode($requestData, JSON_THROW_ON_ERROR);
    $pipes = [];
    $process = proc_open(
        $invoker['command'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        null,
        ['PATH' => '/usr/bin:/bin'],
        ['bypass_shell' => true],
    );
    if (!is_resource($process)) {
        throw new RuntimeException('could not start isolated probe launcher');
    }
    try {
        fwrite($pipes[0], $request . "\n");
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $stdout = '';
        $stderr = '';
        $deadline = microtime(true) + 15;
        do {
            $read = [$pipes[1], $pipes[2]];
            $write = null;
            $except = null;
            @stream_select($read, $write, $except, 0, 200000);
            foreach ($read as $stream) {
                $chunk = stream_get_contents($stream);
                if ($stream === $pipes[1]) {
                    $stdout .= $chunk;
                } else {
                    $stderr .= $chunk;
                }
            }
            if (strlen($stdout) > 8192 || strlen($stderr) > 4096) {
                throw new RuntimeException(
                    'isolated probe output exceeded its limit',
                );
            }
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
        } while (microtime(true) < $deadline);
        if ($status['running'] ?? false) {
            proc_terminate($process);
            throw new RuntimeException('isolated probe timed out');
        }
        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        $reportedExit = $status['exitcode'] ?? -1;
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        $process = null;
        if ($exit === -1 && is_int($reportedExit) && $reportedExit >= 0) {
            $exit = $reportedExit;
        }
        if ($exit !== 0) {
            throw new RuntimeException(
                'isolated probe failed' .
                    ($stderr === ''
                        ? ''
                        : ': ' . mb_substr(trim($stderr), 0, 300)),
            );
        }
        $response = json_decode($stdout, true, 16, JSON_THROW_ON_ERROR);
        if (
            !is_array($response) ||
            array_keys($response) !== [
                'api',
                'invocation_id',
                'package_id',
                'operation',
                'result',
            ] ||
            ($response['api'] ?? null) !== PACKAGE_RUNTIME_API_VERSION ||
            ($response['invocation_id'] ?? null) !== $invocationId ||
            ($response['package_id'] ?? null) !== $id ||
            ($response['operation'] ?? null) !== $operation ||
            !is_array($response['result'] ?? null)
        ) {
            throw new RuntimeException('isolated probe response is invalid');
        }
        $result = $response['result'];
        if (
            ($result['id'] ?? null) !== $id ||
            !is_string($result['state'] ?? null) ||
            !is_string($result['reason'] ?? null) ||
            strlen(json_encode($result, JSON_THROW_ON_ERROR)) > 4096
        ) {
            throw new RuntimeException('isolated probe result is invalid');
        }
        return $result;
    } finally {
        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }
        if (is_resource($process)) {
            @proc_terminate($process);
            @proc_close($process);
        }
    }
}

/** Stores bounded diagnostic evidence separately from package settings/secrets. */
function packageRuntimeRecordProbe(
    array $config,
    string $id,
    string $operation,
    array $result,
): void {
    if (
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id) ||
        !in_array(
            $operation,
            [
                'capability',
                'sqlcipher_preflight',
                'custody_endpoint_probe',
                'custody_enrollment_check',
            ],
            true,
        ) ||
        !is_string($result['state'] ?? null) ||
        !is_string($result['reason'] ?? null) ||
        strlen($result['state']) > 120 ||
        strlen($result['reason']) > 1000
    ) {
        throw new InvalidArgumentException('package probe evidence is invalid');
    }
    $store = setupStoreOpen($config, true);
    if ($store === null) {
        throw new RuntimeException(
            'could not open package probe evidence store',
        );
    }
    $store->exec('CREATE TABLE IF NOT EXISTS package_runtime_probe_evidence (
        package_id TEXT NOT NULL, operation TEXT NOT NULL, state TEXT NOT NULL, reason TEXT NOT NULL, probed_at INTEGER NOT NULL,
        PRIMARY KEY (package_id, operation)
    )');
    $store
        ->prepare(
            'INSERT INTO package_runtime_probe_evidence (package_id, operation, state, reason, probed_at) VALUES (?, ?, ?, ?, ?)
        ON CONFLICT(package_id, operation) DO UPDATE SET state = excluded.state, reason = excluded.reason, probed_at = excluded.probed_at',
        )
        ->execute([
            $id,
            $operation,
            $result['state'],
            $result['reason'],
            time(),
        ]);
}

/** A changed endpoint/pin/policy makes earlier diagnostic evidence stale. */
function packageRuntimeClearProbeEvidence(array $config, string $id): void
{
    if (!preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id)) {
        throw new InvalidArgumentException('package id is invalid');
    }
    $store = setupStoreOpen($config, false);
    if (
        $store === null ||
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_probe_evidence'",
            )
            ->fetchColumn() !== 1
    ) {
        return;
    }
    $store
        ->prepare(
            'DELETE FROM package_runtime_probe_evidence WHERE package_id = ?',
        )
        ->execute([$id]);
}

/** @return array<string,array{operation:string,state:string,reason:string,probed_at:int}> */
function packageRuntimeProbeEvidence(array $config): array
{
    $store = setupStoreOpen($config, false);
    if (
        $store === null ||
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_probe_evidence'",
            )
            ->fetchColumn() !== 1
    ) {
        return [];
    }
    $rows = $store
        ->query(
            'SELECT package_id, operation, state, reason, probed_at FROM package_runtime_probe_evidence ORDER BY probed_at DESC',
        )
        ->fetchAll();
    $out = [];
    foreach ($rows as $row) {
        if (!isset($out[$row['package_id']])) {
            $out[$row['package_id']] = [
                'operation' => (string) $row['operation'],
                'state' => (string) $row['state'],
                'reason' => (string) $row['reason'],
                'probed_at' => (int) $row['probed_at'],
            ];
        }
    }
    return $out;
}

/** @return ?array{operation:string,state:string,reason:string,probed_at:int} */
function packageRuntimeProbeOperationEvidence(
    array $config,
    string $id,
    string $operation,
): ?array {
    if (
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id) ||
        !in_array(
            $operation,
            [
                'capability',
                'sqlcipher_preflight',
                'custody_endpoint_probe',
                'custody_enrollment_check',
            ],
            true,
        )
    ) {
        throw new InvalidArgumentException(
            'package probe operation is invalid',
        );
    }
    $store = setupStoreOpen($config, false);
    if (
        $store === null ||
        (int) $store
            ->query(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'package_runtime_probe_evidence'",
            )
            ->fetchColumn() !== 1
    ) {
        return null;
    }
    $stmt = $store->prepare(
        'SELECT operation, state, reason, probed_at FROM package_runtime_probe_evidence WHERE package_id = ? AND operation = ?',
    );
    $stmt->execute([$id, $operation]);
    $row = $stmt->fetch();
    return is_array($row)
        ? [
            'operation' => (string) $row['operation'],
            'state' => (string) $row['state'],
            'reason' => (string) $row['reason'],
            'probed_at' => (int) $row['probed_at'],
        ]
        : null;
}

/** @return array{state:string,reason:string,endpoint_tested:bool,enrollment_references_ready:bool} */
function packageRuntimeProviderReadiness(
    PDO $registry,
    array $config,
    string $id,
): array {
    packageRuntimeInstalled($registry, $id);
    if ($id !== 'zogograph-key-custody') {
        return [
            'state' => 'not-applicable',
            'reason' => 'This package has no remote-provider enrollment flow.',
            'endpoint_tested' => false,
            'enrollment_references_ready' => false,
        ];
    }
    $settings = packageRuntimeSettings($registry, $config, $id);
    $endpointConfigured =
        is_string($settings['service_base_url'] ?? null) &&
        $settings['service_base_url'] !== '' &&
        is_string($settings['tenant_id'] ?? null) &&
        preg_match('/^ten_[a-f0-9]{24}$/', $settings['tenant_id']) === 1;
    if (!$endpointConfigured) {
        return [
            'state' => 'needs-endpoint',
            'reason' =>
                'Save the HTTPS custody service URL and tenant identifier before testing the endpoint.',
            'endpoint_tested' => false,
            'enrollment_references_ready' => false,
        ];
    }
    $probe = packageRuntimeProbeOperationEvidence(
        $config,
        $id,
        'custody_endpoint_probe',
    );
    $endpointTested =
        $probe !== null &&
        $probe['state'] === 'endpoint-ready' &&
        $probe['probed_at'] >= time() - 86400;
    if (!$endpointTested) {
        return [
            'state' => 'needs-endpoint-test',
            'reason' =>
                'Run a successful custody endpoint test after saving this endpoint. Tests older than 24 hours must be repeated before enrollment references are accepted.',
            'endpoint_tested' => false,
            'enrollment_references_ready' => false,
        ];
    }
    $referencesReady = true;
    foreach (['client_credential_secret', 'client_signing_secret'] as $field) {
        $reference = $settings[$field] ?? null;
        try {
            if (
                !is_string($reference) ||
                !packageRuntimeSecretExists($config, $id, $reference)
            ) {
                $referencesReady = false;
            }
        } catch (InvalidArgumentException) {
            $referencesReady = false;
        }
    }
    if (!$referencesReady) {
        return [
            'state' => 'needs-enrollment-references',
            'reason' =>
                'Create and save separate encrypted client-credential and signing-key references after the endpoint test. Never enter raw credentials in settings.',
            'endpoint_tested' => true,
            'enrollment_references_ready' => false,
        ];
    }
    return [
        'state' => 'awaiting-isolated-runtime',
        'reason' =>
            'Endpoint identity and encrypted enrollment references are ready. Remote custody remains inactive until a reviewed isolated runtime is installed and the provider operation release gate is complete.',
        'endpoint_tested' => true,
        'enrollment_references_ready' => true,
    ];
}
