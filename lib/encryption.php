<?php
declare(strict_types=1);

/*
 * Sealed-copy primitives.
 *
 * This does not turn PDO SQLite into an encrypted live database. It creates a
 * separately verified encrypted artifact from a consistent source copy, which
 * is the portable building block for encrypted exports and fleet replicas.
 */

const ENCRYPTION_MAGIC = "ZOGOGRAPH-SEAL-2\n";
const LEGACY_ENCRYPTION_MAGIC = "HOSTSCOPE-SEAL-1\n";
const ENCRYPTION_CHUNK_BYTES = 1048576;

function encryptionAvailable(): bool
{
    return extension_loaded('openssl') &&
        in_array(
            'aes-256-gcm',
            array_map('strtolower', openssl_get_cipher_methods()),
            true,
        ) &&
        openssl_cipher_iv_length('aes-256-gcm') === 12;
}

/** Accept the retired marker only when opening an existing sealed artifact. */
function encryptionReadMagic($stream): bool
{
    $magic = encryptionReadExact($stream, strlen(ENCRYPTION_MAGIC));
    return $magic === ENCRYPTION_MAGIC || $magic === LEGACY_ENCRYPTION_MAGIC;
}

/** Creates a 256-bit local KEK outside the web root. */
function encryptionCreateLocalKey(string $path, string $webRoot): void
{
    encryptionKeyPath($path, $webRoot, false);
    $directory = dirname($path);
    if (
        !is_dir($directory) &&
        !@mkdir($directory, 0700, true) &&
        !is_dir($directory)
    ) {
        throw new RuntimeException('could not create the local key directory');
    }
    if (is_link($directory)) {
        throw new RuntimeException(
            'the local key directory must not be a symbolic link',
        );
    }
    $directory = realpath($directory);
    if (
        $directory === false ||
        !is_dir($directory) ||
        !is_writable($directory)
    ) {
        throw new RuntimeException('the local key directory is not writable');
    }
    @chmod($directory, 0700);
    encryptionProtectPrivateKeyDirectory($directory);
    $handle = encryptionWithPrivateUmask(fn() => @fopen($path, 'x'));
    if ($handle === false) {
        throw new RuntimeException('refusing to replace an existing local key');
    }
    try {
        encryptionWriteAll($handle, base64_encode(random_bytes(32)) . "\n");
    } finally {
        fclose($handle);
    }
    @chmod($path, 0600);
    encryptionKeyPath($path, $webRoot, true);
}

/** Adds a denial rule where Apache could otherwise map the key directory. */
function encryptionProtectPrivateKeyDirectory(string $directory): void
{
    $policyPath = $directory . '/.htaccess';
    if (is_link($policyPath)) {
        throw new RuntimeException(
            'the local key directory has an unsafe access policy link',
        );
    }

    $marker = '# Zogograph private-key protection';
    $policy =
        $marker .
        "\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n" .
        "<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n";
    if (file_exists($policyPath)) {
        if (
            !is_file($policyPath) ||
            !is_readable($policyPath) ||
            !is_writable($policyPath)
        ) {
            throw new RuntimeException(
                'the local key directory access policy is unavailable',
            );
        }
        $existing = file_get_contents($policyPath);
        if (!is_string($existing)) {
            throw new RuntimeException(
                'could not read the local key directory access policy',
            );
        }
        if (str_contains($existing, $marker)) {
            return;
        }
        if (
            file_put_contents(
                $policyPath,
                "\n" . $policy,
                FILE_APPEND | LOCK_EX,
            ) === false
        ) {
            throw new RuntimeException(
                'could not update the local key directory access policy',
            );
        }
    } else {
        $handle = encryptionWithPrivateUmask(fn() => @fopen($policyPath, 'x'));
        if ($handle === false) {
            throw new RuntimeException(
                'could not create the local key directory access policy',
            );
        }
        try {
            encryptionWriteAll($handle, $policy);
        } finally {
            fclose($handle);
        }
    }
    @chmod($policyPath, 0644);
}

/** Reads a strictly permissioned, non-web-readable 256-bit local KEK. */
function encryptionReadLocalKey(string $path, string $webRoot): string
{
    $real = encryptionKeyPath($path, $webRoot, true);
    $encoded = @file_get_contents($real);
    $key = is_string($encoded) ? base64_decode(trim($encoded), true) : false;
    if (!is_string($key) || strlen($key) !== 32) {
        throw new RuntimeException(
            'the local key is not a Base64-encoded 256-bit key',
        );
    }
    return $key;
}

/** @return string canonical key path */
function encryptionKeyPath(
    string $path,
    string $webRoot,
    bool $mustExist,
): string {
    if ($path === '' || $path[0] !== '/') {
        throw new InvalidArgumentException('a local key path must be absolute');
    }
    if (is_link($path)) {
        throw new RuntimeException(
            'a local key file must not be a symbolic link',
        );
    }
    if ($mustExist && (!is_file($path) || !is_readable($path))) {
        throw new RuntimeException('the local key file is unavailable');
    }
    $real = $mustExist
        ? realpath($path)
        : encryptionCanonicalPotentialPath($path);
    if (!is_string($real)) {
        throw new RuntimeException('could not resolve the local key path');
    }
    $root = realpath($webRoot);
    if (
        is_string($root) &&
        ($real === $root || str_starts_with($real, rtrim($root, '/') . '/'))
    ) {
        throw new RuntimeException('a local key must be outside the web root');
    }
    if ($mustExist) {
        $stat = stat($real);
        if ($stat === false || ($stat['mode'] & 0170000) !== 0100000) {
            throw new RuntimeException('the local key is not a regular file');
        }
        if (($stat['mode'] & 0077) !== 0) {
            throw new RuntimeException(
                'the local key must not be readable by group or others',
            );
        }
    }
    return $real;
}

/** Resolves a custom encrypted-artifact directory. The built-in data/sealed
 * destination remains the portable default; an explicit override must be a
 * private absolute directory outside the web root. */
function encryptionArtifactDirectory(array $config, string $webRoot): string
{
    $settings = (array) ($config['encryption'] ?? []);
    $configured = (string) ($settings['artifact_directory'] ?? '');
    if ($configured === '') {
        $default = dirname((string) $config['database']) . '/sealed';
        $root = realpath($webRoot);
        $candidate = encryptionCanonicalPotentialPath($default);
        if (
            is_string($root) &&
            is_string($candidate) &&
            ($candidate === $root ||
                str_starts_with($candidate, rtrim($root, '/') . '/'))
        ) {
            throw new RuntimeException(
                'the default encrypted-copy directory must be outside the web root',
            );
        }
        if (
            is_link($default) ||
            (!is_dir($default) &&
                !@mkdir($default, 0700, true) &&
                !is_dir($default))
        ) {
            throw new RuntimeException(
                'the default encrypted-copy directory is unavailable or unsafe',
            );
        }
        $real = realpath($default);
        if (
            $real === false ||
            is_link($default) ||
            !is_dir($real) ||
            !is_writable($real) ||
            (is_string($root) &&
                ($real === $root ||
                    str_starts_with($real, rtrim($root, '/') . '/')))
        ) {
            throw new RuntimeException(
                'the default encrypted-copy directory is unavailable or unsafe',
            );
        }
        @chmod($real, 0700);
        return $real;
    }
    encryptionValidateArtifactDirectory($configured, $webRoot);
    $root = realpath($webRoot);
    if (
        !is_dir($configured) &&
        !@mkdir($configured, 0700, true) &&
        !is_dir($configured)
    ) {
        throw new RuntimeException(
            'could not create the sealed-artifact directory',
        );
    }
    $real = realpath($configured);
    if (
        $real === false ||
        is_link($configured) ||
        !is_dir($real) ||
        !is_writable($real) ||
        (is_string($root) &&
            ($real === $root ||
                str_starts_with($real, rtrim($root, '/') . '/')))
    ) {
        throw new RuntimeException(
            'custom encrypted-copy directory is unavailable or unsafe',
        );
    }
    @chmod($real, 0700);
    return $real;
}

/** Validates an operator-provided directory without creating it. */
function encryptionValidateArtifactDirectory(
    string $directory,
    string $webRoot,
): void {
    if ($directory === '') {
        return;
    }
    if ($directory[0] !== '/' || is_link($directory)) {
        throw new RuntimeException(
            'custom encrypted-copy directory must be an absolute non-symlink path',
        );
    }
    $root = realpath($webRoot);
    $candidate = encryptionCanonicalPotentialPath($directory);
    if (
        is_string($root) &&
        is_string($candidate) &&
        ($candidate === $root ||
            str_starts_with($candidate, rtrim($root, '/') . '/'))
    ) {
        throw new RuntimeException(
            'custom encrypted-copy directory must be outside the web root',
        );
    }
    if (file_exists($directory)) {
        $real = realpath($directory);
        if (
            $real === false ||
            !is_dir($real) ||
            is_link($directory) ||
            (is_string($root) &&
                ($real === $root ||
                    str_starts_with($real, rtrim($root, '/') . '/')))
        ) {
            throw new RuntimeException(
                'custom encrypted-copy directory is unavailable or unsafe',
            );
        }
    }
}

/** Resolves the nearest existing parent so `/var` aliases and a not-yet-created
 * final directory cannot bypass a private-path boundary check. */
function encryptionCanonicalPotentialPath(string $path): ?string
{
    $tail = [];
    $probe = $path;
    while (!file_exists($probe)) {
        $parent = dirname($probe);
        if ($parent === $probe) {
            return null;
        }
        array_unshift($tail, basename($probe));
        $probe = $parent;
    }
    $real = realpath($probe);
    return $real === false
        ? null
        : rtrim($real, '/') . ($tail === [] ? '' : '/' . implode('/', $tail));
}

/**
 * Seals a file with a fresh data key wrapped by `$kek`.
 *
 * @return array{artifact_id: string, plaintext_bytes: int, ciphertext_bytes: int, sha256: string}
 */
function encryptionSealFile(
    string $source,
    string $destination,
    string $kek,
    string $artifactId,
): array {
    encryptionRequire($kek, $artifactId);
    if (!is_file($source) || !is_readable($source)) {
        throw new RuntimeException('the source file is unavailable');
    }
    if (file_exists($destination)) {
        throw new RuntimeException(
            'refusing to replace an existing sealed artifact',
        );
    }
    $in = fopen($source, 'rb');
    $out = encryptionWithPrivateUmask(fn() => @fopen($destination, 'xb'));
    if ($in === false || $out === false) {
        if (is_resource($in)) {
            fclose($in);
        }
        if (is_resource($out)) {
            fclose($out);
        }
        throw new RuntimeException('could not open the sealed-artifact files');
    }
    $plain = 0;
    try {
        $dek = random_bytes(32);
        $wrapNonce = random_bytes(12);
        $wrapped = openssl_encrypt(
            $dek,
            'aes-256-gcm',
            $kek,
            OPENSSL_RAW_DATA,
            $wrapNonce,
            $wrapTag,
            $artifactId,
            16,
        );
        if (!is_string($wrapped) || !is_string($wrapTag)) {
            throw new RuntimeException('could not wrap the data key');
        }
        $header = json_encode(
            [
                'version' => 1,
                'cipher' => 'aes-256-gcm',
                'chunk_bytes' => ENCRYPTION_CHUNK_BYTES,
                'artifact_id' => $artifactId,
                'nonce_prefix' => base64_encode(random_bytes(4)),
                'wrapped_key' => base64_encode($wrapped),
                'wrap_nonce' => base64_encode($wrapNonce),
                'wrap_tag' => base64_encode($wrapTag),
            ],
            JSON_UNESCAPED_SLASHES,
        );
        if (!is_string($header) || strlen($header) > 65535) {
            throw new RuntimeException(
                'could not create the sealed-artifact header',
            );
        }
        $prefix = base64_decode(
            (string) json_decode($header, true)['nonce_prefix'],
            true,
        );
        if (!is_string($prefix) || strlen($prefix) !== 4) {
            throw new RuntimeException(
                'could not write the sealed-artifact header',
            );
        }
        encryptionWriteAll(
            $out,
            ENCRYPTION_MAGIC . pack('N', strlen($header)) . $header,
        );
        $aadPrefix = hash('sha256', $header, true);
        $index = 0;
        while (!feof($in)) {
            $chunk = fread($in, ENCRYPTION_CHUNK_BYTES);
            if ($chunk === false) {
                throw new RuntimeException('could not read the source file');
            }
            if ($chunk === '') {
                continue;
            }
            $ciphertext = openssl_encrypt(
                $chunk,
                'aes-256-gcm',
                $dek,
                OPENSSL_RAW_DATA,
                encryptionChunkNonce($prefix, $index),
                $tag,
                $aadPrefix . encryptionChunkIndex($index),
                16,
            );
            if (!is_string($ciphertext) || !is_string($tag)) {
                throw new RuntimeException('could not write encrypted data');
            }
            encryptionWriteAll(
                $out,
                pack('N', strlen($ciphertext)) . $ciphertext . $tag,
            );
            $plain += strlen($chunk);
            $index++;
        }
        encryptionWriteAll($out, pack('N', 0));
    } catch (Throwable $error) {
        fclose($in);
        fclose($out);
        @unlink($destination);
        throw $error;
    }
    fclose($in);
    fclose($out);
    @chmod($destination, 0600);
    return [
        'artifact_id' => $artifactId,
        'plaintext_bytes' => $plain,
        'ciphertext_bytes' => (int) filesize($destination),
        'sha256' => hash_file('sha256', $destination) ?: '',
    ];
}

/**
 * Makes a WAL-safe SQLite snapshot, seals it, then removes the short-lived
 * plaintext snapshot. The caller must keep the destination directory private.
 *
 * @return array{artifact_id: string, plaintext_bytes: int, ciphertext_bytes: int, sha256: string}
 */
function encryptionSealSqliteSnapshot(
    PDO $pdo,
    string $destination,
    string $kek,
    string $artifactId,
): array {
    $directory = dirname($destination);
    if (!is_dir($directory) || !is_writable($directory)) {
        throw new RuntimeException(
            'the sealed-artifact directory is not writable',
        );
    }
    $snapshot = $destination . '.snapshot-' . bin2hex(random_bytes(8)) . '.db';
    try {
        /* VACUUM INTO includes committed WAL pages; copying the main file does not. */
        encryptionWithPrivateUmask(
            fn() => $pdo->exec('VACUUM INTO ' . $pdo->quote($snapshot)),
        );
        @chmod($snapshot, 0600);
        return encryptionSealFile($snapshot, $destination, $kek, $artifactId);
    } finally {
        @unlink($snapshot);
    }
}

/**
 * Produces one verified sealed snapshot for each explicitly selected local
 * SQLite store. This is intentionally separate from normal collection.
 *
 * @return list<array{store: string, artifact_id: string, plaintext_bytes: int, ciphertext_bytes: int, sha256: string, local_path: string}>
 */
function encryptionSealConfiguredCopies(
    array $config,
    PDO $account,
    string $webRoot,
): array {
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? 'none') === 'none') {
        return [];
    }
    if (($settings['mode'] ?? '') === 'operator_managed_volume') {
        return [];
    }
    if (($settings['mode'] ?? '') !== 'sealed_copies_local') {
        throw new RuntimeException(
            'the selected encryption mode is unavailable in this Zogograph release',
        );
    }
    $key = encryptionReadLocalKey(
        (string) ($settings['local_key_file'] ?? ''),
        $webRoot,
    );
    try {
        $directory = encryptionArtifactDirectory($config, $webRoot);
    } catch (RuntimeException $error) {
        throw new RuntimeException(
            'could not create the sealed-artifact directory',
        );
    }
    @chmod($directory, 0700);
    $stores = encryptionSelectedStores($settings);
    if ($stores === []) {
        throw new RuntimeException(
            'select at least one database for sealed copies',
        );
    }

    $paths = encryptionStorePaths($config, $webRoot);
    $out = [];
    foreach ($stores as $store) {
        if (is_link($paths[$store])) {
            throw new RuntimeException(
                "selected {$store} source must not be a symbolic link",
            );
        }
        if (!is_file($paths[$store])) {
            continue;
        }
        $stamp = gmdate('Ymd\\THis\\Z') . '-' . bin2hex(random_bytes(4));
        $artifact = $store . ':' . $stamp;
        $sealed = $directory . '/' . $store . '.' . $stamp . '.sealed';
        if ($store === 'config') {
            $record =
                ['store' => $store, 'local_path' => $sealed] +
                encryptionSealFile($paths[$store], $sealed, $key, $artifact);
            encryptionVerifySealedStoreArtifact(
                $sealed,
                $key,
                (int) $record['plaintext_bytes'],
                $store,
            );
            $out[] = $record;
            continue;
        }
        $pdo =
            $store === 'account'
                ? $account
                : new PDO('sqlite:' . $paths[$store], null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
        $record =
            ['store' => $store, 'local_path' => $sealed] +
            encryptionSealSqliteSnapshot($pdo, $sealed, $key, $artifact);
        encryptionVerifySealedStoreArtifact(
            $sealed,
            $key,
            (int) $record['plaintext_bytes'],
            $store,
        );
        $out[] = $record;
    }
    return $out;
}

/** Fully authenticates a new artifact before it becomes verified ledger evidence. */
function encryptionVerifySealedArtifact(
    string $source,
    string $kek,
    int $expectedBytes,
): void {
    $temporary = dirname($source) . '/.verify-' . bin2hex(random_bytes(12));
    try {
        encryptionOpenFile($source, $temporary, $kek);
        if ((int) filesize($temporary) !== $expectedBytes) {
            throw new RuntimeException(
                'sealed artifact recovery proof has an unexpected plaintext size',
            );
        }
    } finally {
        @unlink($temporary);
    }
}

/** Authenticates an artifact and checks that its recovered store is usable. */
function encryptionVerifySealedStoreArtifact(
    string $source,
    string $kek,
    int $expectedBytes,
    string $store,
): void {
    $temporary =
        dirname($source) . '/.verify-store-' . bin2hex(random_bytes(12));
    try {
        encryptionOpenFile($source, $temporary, $kek);
        if ((int) filesize($temporary) !== $expectedBytes) {
            throw new RuntimeException(
                'sealed artifact recovery proof has an unexpected plaintext size',
            );
        }
        encryptionVerifyRecoveredStore($temporary, $store);
    } finally {
        @unlink($temporary);
    }
}

/** Checks only a decrypted temporary store; it never executes recovered PHP or
 * changes a recovered SQLite database. */
function encryptionVerifyRecoveredStore(string $path, string $store): void
{
    if ($store === 'config') {
        $text = file_get_contents($path);
        if (!is_string($text) || !str_starts_with(ltrim($text), '<?php')) {
            throw new RuntimeException(
                'recovered configuration artifact is not PHP bootstrap text',
            );
        }
        try {
            token_get_all($text, TOKEN_PARSE);
        } catch (ParseError $error) {
            throw new RuntimeException(
                'recovered configuration artifact has invalid PHP syntax',
                0,
                $error,
            );
        }
        return;
    }
    if (!in_array($store, ['setup', 'account', 'host', 'ai'], true)) {
        throw new InvalidArgumentException(
            'recovered store identity is invalid',
        );
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    if ($pdo->query('PRAGMA quick_check')->fetchColumn() !== 'ok') {
        throw new RuntimeException(
            'recovered SQLite artifact failed integrity check',
        );
    }
}

/**
 * Restores a sealed bootstrap configuration only after authenticating and
 * parsing it without execution. The existing bootstrap is sealed and backed up
 * before an atomic same-directory replacement.
 *
 * @return string|null sealed prior-configuration backup, if one existed
 */
function encryptionRestoreBootstrapConfiguration(
    string $sealedSource,
    string $keyPath,
    string $target,
    string $webRoot,
    string $recoveryDirectory,
): ?string {
    $source = realpath($sealedSource);
    if (
        $source === false ||
        is_link($sealedSource) ||
        !is_file($source) ||
        is_link($target)
    ) {
        throw new RuntimeException(
            'the sealed source or bootstrap configuration location is unavailable or unsafe',
        );
    }
    $targetDirectory = realpath(dirname($target));
    if (
        $targetDirectory === false ||
        !is_dir($targetDirectory) ||
        !is_writable($targetDirectory)
    ) {
        throw new RuntimeException(
            'the bootstrap configuration directory is unavailable or unsafe',
        );
    }
    if (file_exists($target) && !is_file($target)) {
        throw new RuntimeException(
            'the bootstrap configuration is not a regular file',
        );
    }
    if (
        is_link($recoveryDirectory) ||
        (!is_dir($recoveryDirectory) && !mkdir($recoveryDirectory, 0700, true))
    ) {
        throw new RuntimeException(
            'the private recovery directory is unavailable or unsafe',
        );
    }
    $recovery = realpath($recoveryDirectory);
    if (
        $recovery === false ||
        !is_dir($recovery) ||
        !is_writable($recovery) ||
        is_link($recoveryDirectory)
    ) {
        throw new RuntimeException(
            'the private recovery directory is unavailable or unsafe',
        );
    }
    @chmod($recovery, 0700);
    $plain = $recovery . '/config-restore-' . bin2hex(random_bytes(8)) . '.php';
    $temporary =
        $targetDirectory . '/.config-restore-' . bin2hex(random_bytes(8));
    try {
        $key = encryptionReadLocalKey($keyPath, $webRoot);
        encryptionOpenFile($source, $plain, $key);
        encryptionVerifyRecoveredStore($plain, 'config');
        $backup = null;
        if (is_file($target)) {
            $stamp = gmdate('Ymd\\THis\\Z') . '-' . bin2hex(random_bytes(4));
            $backup =
                $recovery . '/config-before-restore-' . $stamp . '.sealed';
            $record = encryptionSealFile(
                $target,
                $backup,
                $key,
                'config:before-restore:' . $stamp,
            );
            encryptionVerifySealedStoreArtifact(
                $backup,
                $key,
                (int) $record['plaintext_bytes'],
                'config',
            );
        }
        if (!copy($plain, $temporary)) {
            throw new RuntimeException(
                'could not stage recovered bootstrap configuration',
            );
        }
        @chmod($temporary, 0600);
        if (!rename($temporary, $target)) {
            throw new RuntimeException(
                'could not atomically replace bootstrap configuration',
            );
        }
        return $backup;
    } catch (Throwable $error) {
        @unlink($temporary);
        throw $error;
    } finally {
        @unlink($plain);
    }
}

/** Rejects an unusable opt-in policy before setup records it. */
function encryptionValidateSettings(array $config, string $webRoot): void
{
    $settings = (array) ($config['encryption'] ?? []);
    $mode = (string) ($settings['mode'] ?? 'none');
    if ($mode === 'none') {
        return;
    }
    $stores = encryptionSelectedStores($settings);
    if ($stores === []) {
        throw new InvalidArgumentException(
            'select at least one database or configuration artifact to protect',
        );
    }
    if ($mode === 'operator_managed_volume') {
        return;
    }
    if ($mode !== 'sealed_copies_local') {
        throw new InvalidArgumentException(
            'the selected encryption mode is unavailable in this Zogograph release',
        );
    }
    if (
        ($settings['key_custody'] ?? 'local_private_file') !==
        'local_private_file'
    ) {
        throw new InvalidArgumentException(
            'the selected key-custody method is unavailable in this Zogograph release',
        );
    }
    encryptionValidateArtifactDirectory(
        (string) ($settings['artifact_directory'] ?? ''),
        $webRoot,
    );
    encryptionValidateRetentionSettings($settings);
    encryptionReadLocalKey(
        (string) ($settings['local_key_file'] ?? ''),
        $webRoot,
    );
}

/** Refuses malformed hand-edited policy before an explicit sealing run can
 * interpret it as an accidental no-retention or no-reminder setting. */
function encryptionValidateRetentionSettings(array $settings): void
{
    $whole = static function (mixed $value, string $label): void {
        if (
            (!is_int($value) && !(is_string($value) && ctype_digit($value))) ||
            (int) $value < 0 ||
            (int) $value > 36500
        ) {
            throw new InvalidArgumentException(
                "{$label} must be a whole number from 0 to 36500",
            );
        }
    };
    $whole($settings['retention_days'] ?? 0, 'encrypted-copy retention');
    $whole(
        $settings['recovery_drill_max_age_days'] ?? 90,
        'recovery-drill review period',
    );
    $stores = $settings['retention_days_by_store'] ?? [];
    if (!is_array($stores)) {
        throw new InvalidArgumentException(
            'per-store encrypted-copy retention must be an object',
        );
    }
    foreach ($stores as $store => $days) {
        if (
            !is_string($store) ||
            !in_array(
                $store,
                ['config', 'setup', 'account', 'host', 'ai'],
                true,
            )
        ) {
            throw new InvalidArgumentException(
                'per-store encrypted-copy retention has an invalid store',
            );
        }
        $whole($days, 'per-store encrypted-copy retention');
    }
    $tiers = $settings['retention_recovery_tiers'] ?? [];
    if (!is_array($tiers)) {
        throw new InvalidArgumentException(
            'long-term recovery retention must be an object',
        );
    }
    foreach ($tiers as $tier => $copies) {
        if (
            !is_string($tier) ||
            !in_array($tier, ['daily', 'weekly', 'monthly'], true)
        ) {
            throw new InvalidArgumentException(
                'long-term recovery retention has an invalid tier',
            );
        }
        $whole($copies, 'long-term recovery retention');
    }
}

/** Safe operational status for the protected browser/API surface. It reports
 * actual local evidence and never returns key paths, key bytes, or artifacts. */
function encryptionStatus(PDO $pdo, array $config, string $webRoot): array
{
    $settings = (array) ($config['encryption'] ?? []);
    $mode = (string) ($settings['mode'] ?? 'none');
    $stores = encryptionSelectedStores($settings);
    $status = [
        'mode' => $mode,
        'stores' => $stores,
        'policy' => encryptionPolicySummary($settings),
        'waiting_stores' => [],
        'superseded_artifacts' => [],
        'retired_artifacts' => [],
        'remote_envelopes' => [],
        'remote_envelope_candidates' => [],
        'remote_recovery_candidates' => [],
        'ready' => false,
        'evidence' => 'none',
        'warnings' => [],
        'artifacts' => [],
        'storage_estimate' => null,
        'sqlcipher' => encryptionSqlcipherCapability(),
    ];
    if ($mode === 'none') {
        $status['warnings'][] =
            'Encryption is off. Live SQLite files and copies remain unencrypted unless host storage protection is configured separately.';
        return $status;
    }
    if ($mode === 'operator_managed_volume') {
        $status['ready'] = $stores !== [];
        $status['evidence'] = $stores === [] ? 'none' : 'operator_declared';
        $status['warnings'][] =
            'Zogograph cannot verify host or volume encryption. Confirm that the selected databases, WAL files, logs, and backups are covered by your host policy.';
        return $status;
    }
    if ($mode !== 'sealed_copies_local') {
        $status['warnings'][] =
            'The selected encryption mode is not available in this Zogograph release.';
        return $status;
    }
    if (!encryptionAvailable()) {
        $status['warnings'][] = 'OpenSSL AES-256-GCM support is unavailable.';
    }
    try {
        encryptionReadLocalKey(
            (string) ($settings['local_key_file'] ?? ''),
            $webRoot,
        );
    } catch (Throwable $error) {
        $status['warnings'][] =
            'The private local key is not ready: ' . $error->getMessage();
    }
    try {
        encryptionValidateArtifactDirectory(
            (string) ($settings['artifact_directory'] ?? ''),
            $webRoot,
        );
    } catch (Throwable $error) {
        $status['warnings'][] =
            'The custom encrypted-copy directory is unsafe: ' .
            $error->getMessage();
    }
    if ($stores === []) {
        $status['warnings'][] =
            'No databases or configuration artifact have been selected.';
    }
    $estimate = encryptionSealingStorageEstimate($config, $webRoot, $stores);
    $status['storage_estimate'] = $estimate;
    if (
        $estimate['free_bytes'] !== null &&
        $estimate['required_bytes'] > 0 &&
        $estimate['free_bytes'] < $estimate['required_bytes']
    ) {
        $status['warnings'][] =
            'The encrypted-copy destination may not have enough free space for the next sealing run.';
    }
    try {
        $stmt = $pdo->query("SELECT store, MAX(created_at) AS created_at, MAX(last_verified_at) AS last_verified_at,
            MAX(last_recovery_drill_at) AS last_recovery_drill_at, COUNT(*) AS copies FROM encryption_artifacts WHERE state = 'verified' GROUP BY store");
        foreach ($stmt->fetchAll() as $row) {
            $status['artifacts'][] = [
                'store' => $row['store'],
                'created_at' => (int) $row['created_at'],
                'last_verified_at' => (int) $row['last_verified_at'],
                'last_recovery_drill_at' =>
                    (int) $row['last_recovery_drill_at'],
                'copies' => (int) $row['copies'],
            ];
        }
        $superseded = $pdo->query(
            "SELECT store, COUNT(*) AS copies FROM encryption_artifacts WHERE state = 'superseded' GROUP BY store",
        );
        foreach ($superseded->fetchAll() as $row) {
            $status['superseded_artifacts'][] = [
                'store' => $row['store'],
                'copies' => (int) $row['copies'],
            ];
        }
        $retired = $pdo->query(
            "SELECT store, COUNT(*) AS copies FROM encryption_artifacts WHERE state = 'retired' GROUP BY store",
        );
        foreach ($retired->fetchAll() as $row) {
            $status['retired_artifacts'][] = [
                'store' => $row['store'],
                'copies' => (int) $row['copies'],
            ];
        }
        $remote = $pdo->query(
            'SELECT provider_package, state, COUNT(*) AS copies FROM encryption_remote_envelopes GROUP BY provider_package, state ORDER BY provider_package, state',
        );
        foreach ($remote->fetchAll() as $row) {
            $status['remote_envelopes'][] = [
                'provider_package' => (string) $row['provider_package'],
                'state' => (string) $row['state'],
                'copies' => (int) $row['copies'],
            ];
        }
        $candidates = $pdo->query(
            "SELECT artifact_id, store, created_at FROM encryption_artifacts WHERE state = 'verified' ORDER BY created_at DESC LIMIT 100",
        );
        foreach ($candidates->fetchAll() as $row) {
            $status['remote_envelope_candidates'][] = [
                'artifact_id' => (string) $row['artifact_id'],
                'store' => (string) $row['store'],
                'created_at' => (int) $row['created_at'],
            ];
        }
        $recovery = $pdo->query(
            "SELECT a.artifact_id, a.store, a.created_at FROM encryption_artifacts a JOIN encryption_remote_envelopes r ON r.artifact_id = a.artifact_id WHERE a.state = 'verified' AND r.state IN ('stored', 'verified') ORDER BY a.created_at DESC LIMIT 100",
        );
        foreach ($recovery->fetchAll() as $row) {
            $status['remote_recovery_candidates'][] = [
                'artifact_id' => (string) $row['artifact_id'],
                'store' => (string) $row['store'],
                'created_at' => (int) $row['created_at'],
            ];
        }
    } catch (Throwable) {
        $status['warnings'][] =
            'No encrypted artifact ledger is available yet.';
    }
    $paths = encryptionStorePaths($config, $webRoot);
    foreach (encryptionSensitiveSourceWarnings($paths, $stores) as $warning) {
        $status['warnings'][] = $warning;
    }
    $expected = [];
    foreach ($stores as $store) {
        if (
            in_array($store, ['host', 'ai'], true) &&
            !is_file($paths[$store])
        ) {
            $status['waiting_stores'][] = $store;
            $status[
                'warnings'
            ][] = "The optional {$store} database is not present yet, so it is waiting rather than protected.";
            continue;
        }
        $expected[] = $store;
    }
    $covered = array_column($status['artifacts'], 'store');
    $latest = array_column($status['artifacts'], 'created_at', 'store');
    $drilled = array_column(
        $status['artifacts'],
        'last_recovery_drill_at',
        'store',
    );
    try {
        $failed = $pdo
            ->query(
                "SELECT store, COUNT(*) AS copies FROM encryption_artifacts WHERE state = 'verification_failed' GROUP BY store",
            )
            ->fetchAll();
        foreach ($failed as $artifact) {
            $status['warnings'][] =
                'One or more retained encrypted copies for ' .
                $artifact['store'] .
                ' failed their last verification.';
        }
        $failedRemote = $pdo
            ->query(
                "SELECT COUNT(*) FROM encryption_remote_envelopes WHERE state = 'failed'",
            )
            ->fetchColumn();
        if ((int) $failedRemote > 0) {
            $status['warnings'][] =
                'One or more optional remote custody envelope attempts failed; local recovery copies remain the primary evidence.';
        }
    } catch (Throwable) {
        /* Older/partial ledgers already carry a general warning above. */
    }
    foreach ($expected as $store) {
        if (!in_array($store, $covered, true)) {
            $status[
                'warnings'
            ][] = "No verified encrypted copy exists yet for {$store}.";
            continue;
        }
        $maximumAge = encryptionScheduleMaximumAge(
            (string) ($settings['backup_schedule'] ?? 'manual'),
        );
        if (
            $maximumAge !== null &&
            (int) $latest[$store] < time() - $maximumAge
        ) {
            $status[
                'warnings'
            ][] = "The newest verified encrypted copy for {$store} is older than the configured schedule allows.";
        }
        if ((int) ($drilled[$store] ?? 0) <= 0) {
            $status[
                'warnings'
            ][] = "No successful non-destructive recovery drill has been recorded yet for {$store}.";
        } elseif (
            ($maximumDrillAge = encryptionRecoveryDrillMaximumAge(
                $settings,
            )) !== null &&
            (int) $drilled[$store] < time() - $maximumDrillAge
        ) {
            $status[
                'warnings'
            ][] = "The latest non-destructive recovery drill for {$store} is older than the selected review period.";
        }
    }
    $status['ready'] =
        $expected !== [] &&
        encryptionAvailable() &&
        count(
            array_filter(
                $status['warnings'],
                fn(string $warning): bool => !str_contains(
                    $warning,
                    'is not present yet, so it is waiting',
                ) &&
                    !str_starts_with(
                        $warning,
                        'One or more optional remote custody envelope attempts failed',
                    ),
            ),
        ) === 0;
    if ($status['ready']) {
        $status['evidence'] = 'verified_sealed_copies';
    }
    return $status;
}

/** Core-owned read-only probe; detecting a driver never enables live encryption. */
function encryptionSqlcipherCapability(): array
{
    if (
        !class_exists(PDO::class) ||
        !in_array('sqlite', PDO::getAvailableDrivers(), true)
    ) {
        return [
            'state' => 'unavailable',
            'detail' => 'PDO SQLite is unavailable.',
        ];
    }
    try {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $version = trim(
            (string) $pdo->query('PRAGMA cipher_version')->fetchColumn(),
        );
    } catch (Throwable) {
        $version = '';
    }
    return $version === ''
        ? [
            'state' => 'unavailable',
            'detail' => 'No SQLCipher-capable PDO SQLite driver was detected.',
        ]
        : [
            'state' => 'driver-ready',
            'detail' =>
                'SQLCipher PDO driver detected (' .
                $version .
                '). The signed isolated provider and recovery migration are still required.',
        ];
}

/** Does not expose source paths, but calls out aliases and broad local access
 * for selected stores and SQLite companion files. */
function encryptionSensitiveSourceWarnings(array $paths, array $stores): array
{
    $warnings = [];
    foreach ($stores as $store) {
        $path = (string) ($paths[$store] ?? '');
        if ($path === '') {
            continue;
        }
        if (is_link($path)) {
            $warnings[] = "The selected {$store} source is a symbolic link; use an owner-controlled regular file before treating protection as ready.";
            continue;
        }
        if (!file_exists($path)) {
            continue;
        }
        $parts = [$path];
        if ($store !== 'config') {
            $parts = array_merge($parts, [$path . '-wal', $path . '-shm']);
        }
        foreach ($parts as $index => $part) {
            $label =
                $index === 0 ? "{$store} source" : "{$store} SQLite companion";
            if (is_link($part)) {
                $warnings[] = "The selected {$label} is a symbolic link; use an owner-controlled regular file before treating protection as ready.";
                continue;
            }
            if (!is_file($part)) {
                continue;
            }
            $permissions = fileperms($part);
            if (
                $permissions === false ||
                encryptionStorePermissionsPrivate($store, $permissions)
            ) {
                continue;
            }
            $warnings[] =
                $store === 'host'
                    ? "The selected {$label} permits access beyond the documented dashboard group-read handoff; restrict it before treating protection as ready."
                    : (in_array($store, ['config', 'setup'], true)
                        ? "The selected {$label} is readable by group or other users; restrict it to the Zogograph service account before treating protection as ready."
                        : "The selected {$label} is readable or writable by group or other users; restrict it to the Zogograph service account before treating protection as ready.");
        }
    }
    return $warnings;
}

/** Host collection files may be group-readable for the dashboard, never writable or world-accessible. */
function encryptionStorePermissionsPrivate(
    string $store,
    int $permissions,
): bool {
    if ($store === 'host') {
        return ($permissions & 0007) === 0 &&
            in_array($permissions & 0070, [0000, 0040], true);
    }
    return ($permissions & 0077) === 0;
}

/** Conservative next-seal capacity estimate. SQLite snapshots and ciphertext
 * coexist briefly, so one source-size estimate alone is not sufficient. */
function encryptionSealingStorageEstimate(
    array $config,
    string $webRoot,
    array $stores,
): array {
    $base = dirname((string) ($config['database'] ?? ''));
    $settings = (array) ($config['encryption'] ?? []);
    $directory =
        (string) ($settings['artifact_directory'] ?? '') ?: $base . '/sealed';
    $probe = $directory;
    while (!is_dir($probe) && dirname($probe) !== $probe) {
        $probe = dirname($probe);
    }
    $paths = encryptionStorePaths($config, $webRoot);
    $sourceBytes = 0;
    foreach ($stores as $store) {
        if (isset($paths[$store]) && is_file($paths[$store])) {
            $sourceBytes += max(0, (int) filesize($paths[$store]));
        }
    }
    $free = is_dir($probe) ? @disk_free_space($probe) : false;
    return [
        'source_bytes' => $sourceBytes,
        'required_bytes' =>
            $sourceBytes === 0 ? 0 : $sourceBytes * 2 + 10485760,
        'free_bytes' => is_float($free) || is_int($free) ? (int) $free : null,
    ];
}

/** @return array{config:string,setup:string,account:string,host:string,ai:string} */
function encryptionStorePaths(array $config, string $webRoot): array
{
    $base = dirname((string) ($config['database'] ?? ''));
    return [
        'config' => $webRoot . '/lib/config.php',
        'setup' => $base . '/setup.db',
        'account' => (string) ($config['database'] ?? ''),
        'host' =>
            (string) ($config['host']['database'] ?? '') ?: $base . '/host.db',
        'ai' => (string) ($config['ai']['database'] ?? '') ?: $base . '/ai.db',
    ];
}

/** Allows one missed scheduled run before reporting recovery coverage as stale. */
function encryptionScheduleMaximumAge(string $schedule): ?int
{
    return match ($schedule) {
        'daily' => 2 * 86400,
        'weekly' => 9 * 86400,
        default => null,
    };
}

/** Zero deliberately disables periodic reminders after the initial drill. */
function encryptionRecoveryDrillMaximumAge(array $settings): ?int
{
    $days = $settings['recovery_drill_max_age_days'] ?? 90;
    if (!is_int($days) && !(is_string($days) && ctype_digit($days))) {
        return 90 * 86400;
    }
    return (int) $days <= 0 ? null : min(36500, (int) $days) * 86400;
}

/** Safe policy facts for the signed-in Protection view; never paths or keys. */
function encryptionPolicySummary(array $settings): array
{
    $days = $settings['retention_days'] ?? 0;
    $days =
        is_int($days) || (is_string($days) && ctype_digit($days))
            ? min(36500, max(0, (int) $days))
            : 0;
    $schedule = (string) ($settings['backup_schedule'] ?? 'manual');
    $maximumDrillAge = encryptionRecoveryDrillMaximumAge($settings);
    return [
        'backup_schedule' => in_array(
            $schedule,
            ['manual', 'daily', 'weekly'],
            true,
        )
            ? $schedule
            : 'manual',
        'retention_days' => $days,
        'retention_tiers' => encryptionRetentionTiers($settings),
        'recovery_drill_review_days' =>
            $maximumDrillAge === null ? 0 : intdiv($maximumDrillAge, 86400),
    ];
}

/** @return list<string> */
function encryptionSelectedStores(array $settings): array
{
    return array_values(
        array_unique(
            array_filter(
                array_map('strval', (array) ($settings['stores'] ?? [])),
                fn(string $store): bool => in_array(
                    $store,
                    ['config', 'setup', 'account', 'host', 'ai'],
                    true,
                ),
            ),
        ),
    );
}

/** Persists ciphertext-only evidence for local copies and later fleet replicas. */
function encryptionRecordArtifacts(PDO $pdo, array $artifacts): void
{
    $insert = $pdo->prepare('INSERT INTO encryption_artifacts
        (artifact_id, store, created_at, last_verified_at, local_path, ciphertext_bytes, ciphertext_sha256, state)
        VALUES (?, ?, ?, ?, ?, ?, ?, \'verified\')');
    foreach ($artifacts as $artifact) {
        $now = time();
        $insert->execute([
            (string) $artifact['artifact_id'],
            (string) $artifact['store'],
            $now,
            $now,
            (string) $artifact['local_path'],
            (int) $artifact['ciphertext_bytes'],
            (string) $artifact['sha256'],
        ]);
    }
}

/** Records an intended optional custody envelope after local artifact recovery
 * has already been verified. It stores identifiers and hashes, never a DEK or
 * provider envelope body. */
function encryptionRecordRemoteEnvelopePending(
    PDO $pdo,
    string $artifactId,
    string $providerPackage,
    string $providerKeyId,
): void {
    if (
        !preg_match('/^[a-z][a-z0-9-]{1,62}$/', $providerPackage) ||
        !preg_match('/^key_[a-f0-9]{32}$/', $providerKeyId)
    ) {
        throw new InvalidArgumentException(
            'remote custody provider identity is invalid',
        );
    }
    $artifact = $pdo->prepare(
        "SELECT ciphertext_sha256 FROM encryption_artifacts WHERE artifact_id = ? AND state = 'verified'",
    );
    $artifact->execute([$artifactId]);
    $hash = $artifact->fetchColumn();
    if (!is_string($hash) || !preg_match('/^[a-f0-9]{64}$/', $hash)) {
        throw new InvalidArgumentException(
            'a verified local artifact is required before remote custody can be recorded',
        );
    }
    $pdo->prepare(
        'INSERT INTO encryption_remote_envelopes (artifact_id, provider_package, provider_key_id, ciphertext_sha256, envelope_sha256, state, requested_at, stored_at, verified_at, detail)
        VALUES (?, ?, ?, ?, NULL, ?, ?, NULL, NULL, NULL)
        ON CONFLICT(artifact_id, provider_package, provider_key_id) DO UPDATE SET ciphertext_sha256 = excluded.ciphertext_sha256, envelope_sha256 = NULL, state = excluded.state, requested_at = excluded.requested_at, stored_at = NULL, verified_at = NULL, detail = NULL',
    )->execute([
        $artifactId,
        $providerPackage,
        $providerKeyId,
        $hash,
        'pending',
        time(),
    ]);
}

/** Marks a provider receipt only when it matches the original ciphertext fact. */
function encryptionRecordRemoteEnvelopeStored(
    PDO $pdo,
    string $artifactId,
    string $providerPackage,
    string $providerKeyId,
    string $ciphertextSha256,
    string $envelopeSha256,
): void {
    if (
        !preg_match('/^[a-f0-9]{64}$/', $ciphertextSha256) ||
        !preg_match('/^[a-f0-9]{64}$/', $envelopeSha256)
    ) {
        throw new InvalidArgumentException(
            'remote envelope receipt hashes are invalid',
        );
    }
    $stmt = $pdo->prepare("UPDATE encryption_remote_envelopes SET envelope_sha256 = ?, state = 'stored', stored_at = ?, detail = NULL
        WHERE artifact_id = ? AND provider_package = ? AND provider_key_id = ? AND ciphertext_sha256 = ? AND state = 'pending'");
    $stmt->execute([
        $envelopeSha256,
        time(),
        $artifactId,
        $providerPackage,
        $providerKeyId,
        $ciphertextSha256,
    ]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException(
            'remote envelope receipt does not match a pending verified artifact',
        );
    }
}

/** Marks only a matching stored remote envelope as recovery-drill verified. */
function encryptionRecordRemoteEnvelopeVerified(
    PDO $pdo,
    string $artifactId,
    string $providerPackage,
    string $providerKeyId,
): void {
    $stmt = $pdo->prepare(
        "UPDATE encryption_remote_envelopes SET state = 'verified', verified_at = ?, detail = NULL WHERE artifact_id = ? AND provider_package = ? AND provider_key_id = ? AND state IN ('stored', 'verified')",
    );
    $stmt->execute([time(), $artifactId, $providerPackage, $providerKeyId]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException(
            'remote envelope recovery verification does not match a stored envelope',
        );
    }
}

/** Leaves a bounded, non-secret retry explanation; it never changes local
 * artifact evidence or deletes a prior stored receipt. */
function encryptionRecordRemoteEnvelopeFailure(
    PDO $pdo,
    string $artifactId,
    string $providerPackage,
    string $providerKeyId,
    string $detail,
): void {
    $detail = trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $detail) ?? '');
    if ($detail === '' || mb_strlen($detail) > 500) {
        throw new InvalidArgumentException(
            'remote envelope failure detail is invalid',
        );
    }
    $stmt = $pdo->prepare("UPDATE encryption_remote_envelopes SET state = 'failed', detail = ?, stored_at = NULL, verified_at = NULL
        WHERE artifact_id = ? AND provider_package = ? AND provider_key_id = ? AND state = 'pending'");
    $stmt->execute([$detail, $artifactId, $providerPackage, $providerKeyId]);
    if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException(
            'remote envelope failure does not match a pending artifact',
        );
    }
}

/** Re-authenticates retained artifacts without leaving plaintext on disk.
 * It is intentionally an explicit CLI/scheduled operation, not page-load work. */
function encryptionVerifyConfiguredCopies(
    array $config,
    PDO $pdo,
    string $webRoot,
): array {
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? '') !== 'sealed_copies_local') {
        return ['verified' => 0, 'failed' => 0, 'warnings' => []];
    }
    $key = encryptionReadLocalKey(
        (string) ($settings['local_key_file'] ?? ''),
        $webRoot,
    );
    try {
        $directory = encryptionArtifactDirectory($config, $webRoot);
    } catch (RuntimeException $error) {
        return [
            'verified' => 0,
            'failed' => 0,
            'warnings' => [$error->getMessage()],
        ];
    }
    $root = realpath($directory);
    if ($root === false || !is_dir($root) || is_link($directory)) {
        throw new RuntimeException(
            'encrypted artifact directory is unavailable or unsafe',
        );
    }
    $rows = $pdo
        ->query(
            "SELECT artifact_id, store, local_path, ciphertext_sha256 FROM encryption_artifacts WHERE state = 'verified' ORDER BY created_at ASC",
        )
        ->fetchAll();
    $verified = 0;
    $failed = 0;
    $warnings = [];
    $ok = $pdo->prepare(
        "UPDATE encryption_artifacts SET last_verified_at = ? WHERE artifact_id = ? AND state = 'verified'",
    );
    $bad = $pdo->prepare(
        "UPDATE encryption_artifacts SET state = 'verification_failed' WHERE artifact_id = ? AND state = 'verified'",
    );
    foreach ($rows as $artifact) {
        $configured = (string) $artifact['local_path'];
        $path = realpath($configured);
        try {
            if (
                $path === false ||
                is_link($configured) ||
                !is_file($path) ||
                !str_starts_with($path, rtrim($root, '/') . '/') ||
                !str_ends_with($path, '.sealed')
            ) {
                throw new RuntimeException(
                    'artifact is unavailable or outside the configured directory',
                );
            }
            $hash = hash_file('sha256', $path);
            if (
                !is_string($hash) ||
                !hash_equals((string) $artifact['ciphertext_sha256'], $hash)
            ) {
                throw new RuntimeException(
                    'ciphertext hash does not match the recorded artifact',
                );
            }
            $temporary = $root . '/.reverify-' . bin2hex(random_bytes(12));
            try {
                encryptionOpenFile($path, $temporary, $key);
                encryptionVerifyRecoveredStore(
                    $temporary,
                    (string) $artifact['store'],
                );
            } finally {
                @unlink($temporary);
            }
            $ok->execute([time(), $artifact['artifact_id']]);
            $verified++;
        } catch (Throwable $error) {
            $bad->execute([$artifact['artifact_id']]);
            $failed++;
            $warnings[] =
                'encrypted artifact verification failed: ' .
                $error->getMessage();
        }
    }
    return [
        'verified' => $verified,
        'failed' => $failed,
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/** Fully checks current selected-store recovery evidence without changing live
 * data. This is intentionally separate from routine verification so an
 * operator can record the first recovery proof after initial setup. */
function encryptionRunRecoveryDrill(
    array $config,
    PDO $pdo,
    string $webRoot,
): array {
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? '') !== 'sealed_copies_local') {
        return [
            'tested' => 0,
            'warnings' => ['recovery drills require local sealed copies'],
        ];
    }
    $checked = encryptionVerifyConfiguredCopies($config, $pdo, $webRoot);
    if ($checked['failed'] > 0) {
        return ['tested' => 0, 'warnings' => $checked['warnings']];
    }
    $paths = encryptionStorePaths($config, $webRoot);
    $expected = [];
    foreach (encryptionSelectedStores($settings) as $store) {
        if (
            in_array($store, ['host', 'ai'], true) &&
            !is_file($paths[$store])
        ) {
            continue;
        }
        $expected[] = $store;
    }
    $rows = $pdo
        ->query(
            "SELECT artifact_id, store FROM encryption_artifacts WHERE state = 'verified' ORDER BY store, created_at DESC, artifact_id DESC",
        )
        ->fetchAll();
    $latest = [];
    foreach ($rows as $row) {
        if (!isset($latest[$row['store']])) {
            $latest[$row['store']] = (string) $row['artifact_id'];
        }
    }
    $missing = array_values(
        array_filter(
            $expected,
            fn(string $store): bool => !isset($latest[$store]),
        ),
    );
    if ($missing !== []) {
        return [
            'tested' => 0,
            'warnings' => [
                'no verified encrypted copy exists for ' .
                implode(', ', $missing),
            ],
        ];
    }
    $mark = $pdo->prepare(
        "UPDATE encryption_artifacts SET last_recovery_drill_at = ? WHERE artifact_id = ? AND state = 'verified'",
    );
    $now = time();
    foreach ($expected as $store) {
        $mark->execute([$now, $latest[$store]]);
    }
    return ['tested' => count($expected), 'warnings' => []];
}

/** Removes only old verified local artifacts after a successful sealing run.
 * The newest verified artifact for every store is never eligible for removal. */
function encryptionPruneConfiguredCopies(array $config, PDO $pdo): array
{
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? '') !== 'sealed_copies_local') {
        return ['removed' => 0, 'warnings' => []];
    }
    try {
        $directory = encryptionArtifactDirectory($config, dirname(__DIR__));
    } catch (RuntimeException $error) {
        return ['removed' => 0, 'warnings' => [$error->getMessage()]];
    }
    $root = realpath($directory);
    if ($root === false || !is_dir($root) || is_link($directory)) {
        return [
            'removed' => 0,
            'warnings' => [
                'encrypted artifact directory is unavailable or unsafe',
            ],
        ];
    }
    $latest = [];
    foreach (
        $pdo
            ->query(
                "SELECT store, MAX(created_at) AS created_at FROM encryption_artifacts WHERE state = 'verified' GROUP BY store",
            )
            ->fetchAll()
        as $row
    ) {
        $latest[$row['store']] = (int) $row['created_at'];
    }
    $preserved = encryptionRetentionPreservedArtifactIds(
        $pdo
            ->query(
                "SELECT artifact_id, store, created_at FROM encryption_artifacts WHERE state = 'verified' ORDER BY store, created_at DESC, artifact_id DESC",
            )
            ->fetchAll(),
        $settings,
    );
    $candidates = $pdo->query("SELECT artifact_id, store, created_at, local_path, ciphertext_sha256 FROM encryption_artifacts
        WHERE state = 'verified' ORDER BY created_at ASC");
    $mark = $pdo->prepare(
        "UPDATE encryption_artifacts SET state = 'pruned' WHERE artifact_id = ? AND state = 'verified'",
    );
    $removed = 0;
    $warnings = [];
    foreach ($candidates->fetchAll() as $artifact) {
        $days = encryptionRetentionDays($settings, (string) $artifact['store']);
        if (
            $days <= 0 ||
            (int) $artifact['created_at'] >= time() - $days * 86400
        ) {
            continue;
        }
        if (isset($preserved[(string) $artifact['artifact_id']])) {
            continue;
        }
        if (
            ($latest[$artifact['store']] ?? 0) <=
            (int) $artifact['created_at']
        ) {
            continue;
        }
        $configured = (string) $artifact['local_path'];
        $path = realpath($configured);
        if (
            $path === false ||
            is_link($configured) ||
            !is_file($path) ||
            !str_starts_with($path, rtrim($root, '/') . '/') ||
            !str_ends_with($path, '.sealed')
        ) {
            $warnings[] =
                'refused to prune an artifact outside the configured private directory';
            continue;
        }
        $hash = hash_file('sha256', $path);
        if (
            !is_string($hash) ||
            !hash_equals((string) $artifact['ciphertext_sha256'], $hash)
        ) {
            $warnings[] =
                'refused to prune an artifact whose ciphertext hash no longer matches';
            continue;
        }
        if (!@unlink($path)) {
            $warnings[] = 'could not remove an expired encrypted artifact';
            continue;
        }
        $mark->execute([$artifact['artifact_id']]);
        $removed++;
    }
    return [
        'removed' => $removed,
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/** Returns the active local-copy retention policy for one recognized store. */
function encryptionRetentionDays(array $settings, string $store): int
{
    $days = $settings['retention_days'] ?? 0;
    $overrides = $settings['retention_days_by_store'] ?? [];
    if (is_array($overrides) && array_key_exists($store, $overrides)) {
        $days = $overrides[$store];
    }
    if (!is_int($days) && !(is_string($days) && ctype_digit($days))) {
        return 0;
    }
    return min(36500, max(0, (int) $days));
}

/** Keeps a bounded set of representative older recovery copies per store. */
function encryptionRetentionPreservedArtifactIds(
    array $artifacts,
    array $settings,
): array {
    $limits = encryptionRetentionTiers($settings);
    $seen = [];
    $preserved = [];
    foreach ($artifacts as $artifact) {
        $store = (string) $artifact['store'];
        foreach ($limits as $tier => $limit) {
            if ($limit <= 0) {
                continue;
            }
            $bucket = gmdate(
                match ($tier) {
                    'daily' => 'Y-m-d',
                    'weekly' => 'o-W',
                    default => 'Y-m',
                },
                (int) $artifact['created_at'],
            );
            if (
                isset($seen[$tier][$store][$bucket]) ||
                count($seen[$tier][$store] ?? []) >= $limit
            ) {
                continue;
            }
            $seen[$tier][$store][$bucket] = true;
            $preserved[(string) $artifact['artifact_id']] = true;
        }
    }
    return $preserved;
}

/** Normalizes configured long-term recovery tiers, including old hand-written keys. */
function encryptionRetentionTiers(array $settings): array
{
    $tiers = (array) ($settings['retention_recovery_tiers'] ?? []);
    if ($tiers === []) {
        $tiers = [
            'daily' => $settings['retention_keep_daily'] ?? 0,
            'weekly' => $settings['retention_keep_weekly'] ?? 0,
            'monthly' => $settings['retention_keep_monthly'] ?? 0,
        ];
    }
    $limits = [];
    foreach (['daily', 'weekly', 'monthly'] as $tier) {
        $value = $tiers[$tier] ?? 0;
        $limits[$tier] =
            is_int($value) || (is_string($value) && ctype_digit($value))
                ? min(36500, max(0, (int) $value))
                : 0;
    }
    return $limits;
}

/** Decrypts a sealed artifact into a new destination file after authenticating every chunk. */
function encryptionOpenFile(
    string $source,
    string $destination,
    string $kek,
): void {
    if (!encryptionAvailable() || strlen($kek) !== 32) {
        throw new RuntimeException('encryption support or key is unavailable');
    }
    if (
        !is_file($source) ||
        !is_readable($source) ||
        file_exists($destination)
    ) {
        throw new RuntimeException(
            'the encrypted source or output destination is unavailable',
        );
    }
    $in = fopen($source, 'rb');
    $out = encryptionWithPrivateUmask(fn() => @fopen($destination, 'xb'));
    if ($in === false || $out === false) {
        if (is_resource($in)) {
            fclose($in);
        }
        if (is_resource($out)) {
            fclose($out);
        }
        throw new RuntimeException('could not open the sealed-artifact files');
    }
    try {
        if (!encryptionReadMagic($in)) {
            throw new RuntimeException(
                'the artifact is not a Zogograph sealed copy',
            );
        }
        [$header, $headerText] = encryptionReadArtifactHeader($in);
        $prefix = base64_decode((string) ($header['nonce_prefix'] ?? ''), true);
        $wrapped = base64_decode((string) ($header['wrapped_key'] ?? ''), true);
        $wrapNonce = base64_decode(
            (string) ($header['wrap_nonce'] ?? ''),
            true,
        );
        $wrapTag = base64_decode((string) ($header['wrap_tag'] ?? ''), true);
        if (
            !is_string($prefix) ||
            strlen($prefix) !== 4 ||
            !is_string($wrapped) ||
            !is_string($wrapNonce) ||
            strlen($wrapNonce) !== 12 ||
            !is_string($wrapTag) ||
            strlen($wrapTag) !== 16
        ) {
            throw new RuntimeException('the wrapped artifact key is invalid');
        }
        $dek = openssl_decrypt(
            $wrapped,
            'aes-256-gcm',
            $kek,
            OPENSSL_RAW_DATA,
            $wrapNonce,
            $wrapTag,
            (string) $header['artifact_id'],
        );
        if (!is_string($dek) || strlen($dek) !== 32) {
            throw new RuntimeException('the artifact key cannot be opened');
        }
        $aadPrefix = hash('sha256', $headerText, true);
        for ($index = 0; ; $index++) {
            $bytes = unpack('Nsize', encryptionReadExact($in, 4))['size'];
            if ($bytes === 0) {
                break;
            }
            if ($bytes > ENCRYPTION_CHUNK_BYTES) {
                throw new RuntimeException('an encrypted chunk is too large');
            }
            $ciphertext = encryptionReadExact($in, $bytes);
            $tag = encryptionReadExact($in, 16);
            $plain = openssl_decrypt(
                $ciphertext,
                'aes-256-gcm',
                $dek,
                OPENSSL_RAW_DATA,
                encryptionChunkNonce($prefix, $index),
                $tag,
                $aadPrefix . encryptionChunkIndex($index),
            );
            if (!is_string($plain)) {
                throw new RuntimeException(
                    'the artifact data failed authentication',
                );
            }
            encryptionWriteAll($out, $plain);
        }
        if (fgetc($in) !== false) {
            throw new RuntimeException('the artifact has trailing data');
        }
    } catch (Throwable $error) {
        fclose($in);
        fclose($out);
        @unlink($destination);
        throw $error;
    }
    fclose($in);
    fclose($out);
    @chmod($destination, 0600);
}

/** Opens only the locally wrapped DEK for a verified artifact. Callers must
 * keep it in memory and pass it directly to a narrowly scoped recovery or
 * custody operation; they must never persist or log this value. */
function encryptionReadArtifactDataKey(
    string $source,
    string $kek,
    string $artifactId,
): string {
    if (
        !encryptionAvailable() ||
        strlen($kek) !== 32 ||
        $artifactId === '' ||
        !is_file($source) ||
        !is_readable($source)
    ) {
        throw new RuntimeException(
            'the sealed artifact or local key is unavailable',
        );
    }
    $in = fopen($source, 'rb');
    if ($in === false) {
        throw new RuntimeException('could not open the sealed artifact');
    }
    try {
        if (!encryptionReadMagic($in)) {
            throw new RuntimeException(
                'the artifact is not a Zogograph sealed copy',
            );
        }
        [$header] = encryptionReadArtifactHeader($in);
        if (!hash_equals((string) $header['artifact_id'], $artifactId)) {
            throw new RuntimeException(
                'the sealed artifact identity does not match its ledger',
            );
        }
        $wrapped = base64_decode((string) ($header['wrapped_key'] ?? ''), true);
        $nonce = base64_decode((string) ($header['wrap_nonce'] ?? ''), true);
        $tag = base64_decode((string) ($header['wrap_tag'] ?? ''), true);
        if (
            !is_string($wrapped) ||
            !is_string($nonce) ||
            strlen($nonce) !== 12 ||
            !is_string($tag) ||
            strlen($tag) !== 16
        ) {
            throw new RuntimeException('the wrapped artifact key is invalid');
        }
        $dek = openssl_decrypt(
            $wrapped,
            'aes-256-gcm',
            $kek,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $artifactId,
        );
        if (!is_string($dek) || strlen($dek) !== 32) {
            throw new RuntimeException('the artifact key cannot be opened');
        }
        return $dek;
    } finally {
        fclose($in);
    }
}

/** Authenticates one sealed artifact with a transient provider-released DEK.
 * The caller must remove the output immediately after its recovery check. */
function encryptionOpenFileWithDataKey(
    string $source,
    string $destination,
    string $dek,
    string $artifactId,
): void {
    if (
        !encryptionAvailable() ||
        strlen($dek) !== 32 ||
        !is_file($source) ||
        !is_readable($source) ||
        file_exists($destination)
    ) {
        throw new RuntimeException(
            'the encrypted source, transient key, or output destination is unavailable',
        );
    }
    $in = fopen($source, 'rb');
    $out = encryptionWithPrivateUmask(fn() => @fopen($destination, 'xb'));
    if ($in === false || $out === false) {
        if (is_resource($in)) {
            fclose($in);
        }
        if (is_resource($out)) {
            fclose($out);
        }
        throw new RuntimeException(
            'could not open sealed-artifact recovery files',
        );
    }
    try {
        if (!encryptionReadMagic($in)) {
            throw new RuntimeException(
                'the artifact is not a Zogograph sealed copy',
            );
        }
        [$header, $headerText] = encryptionReadArtifactHeader($in);
        if (!hash_equals((string) $header['artifact_id'], $artifactId)) {
            throw new RuntimeException(
                'the sealed artifact identity does not match its recovery ledger',
            );
        }
        $prefix = base64_decode((string) ($header['nonce_prefix'] ?? ''), true);
        if (!is_string($prefix) || strlen($prefix) !== 4) {
            throw new RuntimeException('the sealed artifact nonce is invalid');
        }
        $aadPrefix = hash('sha256', $headerText, true);
        for ($index = 0; ; $index++) {
            $bytes = unpack('Nsize', encryptionReadExact($in, 4))['size'];
            if ($bytes === 0) {
                break;
            }
            if ($bytes > ENCRYPTION_CHUNK_BYTES) {
                throw new RuntimeException('an encrypted chunk is too large');
            }
            $plain = openssl_decrypt(
                encryptionReadExact($in, $bytes),
                'aes-256-gcm',
                $dek,
                OPENSSL_RAW_DATA,
                encryptionChunkNonce($prefix, $index),
                encryptionReadExact($in, 16),
                $aadPrefix . encryptionChunkIndex($index),
            );
            if (!is_string($plain)) {
                throw new RuntimeException(
                    'the artifact data failed authentication',
                );
            }
            encryptionWriteAll($out, $plain);
        }
        if (fgetc($in) !== false) {
            throw new RuntimeException('the artifact has trailing data');
        }
    } catch (Throwable $error) {
        fclose($in);
        fclose($out);
        @unlink($destination);
        throw $error;
    }
    fclose($in);
    fclose($out);
    @chmod($destination, 0600);
}

/** Reads the bounded authenticated artifact header. Version 1 remains the
 * only accepted format until the additive remote-envelope format is released. */
function encryptionReadArtifactHeader($stream): array
{
    $headerBytes = unpack('Nsize', encryptionReadExact($stream, 4))['size'];
    if ($headerBytes < 2 || $headerBytes > 65535) {
        throw new RuntimeException('the artifact header is invalid');
    }
    $headerText = encryptionReadExact($stream, $headerBytes);
    $header = json_decode($headerText, true);
    if (
        !is_array($header) ||
        ($header['version'] ?? null) !== 1 ||
        ($header['cipher'] ?? null) !== 'aes-256-gcm' ||
        ($header['chunk_bytes'] ?? null) !== ENCRYPTION_CHUNK_BYTES ||
        !is_string($header['artifact_id'] ?? null)
    ) {
        throw new RuntimeException('the artifact header is unsupported');
    }
    return [$header, $headerText];
}

/**
 * Re-encrypts a sealed artifact under a new local key without replacing either
 * input. The temporary plaintext exists only for the duration of this command.
 *
 * @return array{artifact_id: string, plaintext_bytes: int, ciphertext_bytes: int, sha256: string}
 */
function encryptionRotateSealedFile(
    string $source,
    string $destination,
    string $oldKey,
    string $newKey,
    string $artifactId,
): array {
    $temporary = $destination . '.rotate-' . bin2hex(random_bytes(8));
    try {
        encryptionOpenFile($source, $temporary, $oldKey);
        return encryptionSealFile(
            $temporary,
            $destination,
            $newKey,
            $artifactId,
        );
    } finally {
        @unlink($temporary);
    }
}

/** Re-encrypts all verified artifacts before a caller persists a replacement
 * local key path. Superseded ciphertext is retained for a recovery drill.
 * @return list<array{store:string,artifact_id:string,plaintext_bytes:int,ciphertext_bytes:int,sha256:string,local_path:string}> */
function encryptionRotateConfiguredArtifacts(
    array &$config,
    PDO $pdo,
    string $webRoot,
    string $newKeyPath,
    callable $saveConfig,
): array {
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? 'none') !== 'sealed_copies_local') {
        throw new RuntimeException('local sealed copies are not enabled');
    }
    $oldKey = encryptionReadLocalKey(
        (string) ($settings['local_key_file'] ?? ''),
        $webRoot,
    );
    $newKey = encryptionReadLocalKey($newKeyPath, $webRoot);
    if (hash_equals($oldKey, $newKey)) {
        throw new RuntimeException(
            'the replacement key differs from the configured key',
        );
    }
    $directory = encryptionArtifactDirectory($config, $webRoot);
    if ($directory === false || is_link($directory) || !is_dir($directory)) {
        throw new RuntimeException(
            'the sealed-artifact directory is unavailable',
        );
    }
    $rows = $pdo
        ->query(
            "SELECT artifact_id, store, local_path FROM encryption_artifacts WHERE state = 'verified' ORDER BY created_at, artifact_id",
        )
        ->fetchAll();
    if ($rows === []) {
        throw new RuntimeException(
            'no verified sealed artifacts are available to rotate',
        );
    }
    $coverage = [];
    foreach ($rows as $row) {
        $coverage[(string) $row['store']] = true;
    }
    foreach (encryptionSelectedStores($settings) as $store) {
        $path = encryptionStorePaths($config, $webRoot)[$store];
        if (in_array($store, ['host', 'ai'], true) && !is_file($path)) {
            continue;
        }
        if (!isset($coverage[$store])) {
            throw new RuntimeException(
                "no verified artifact is available for selected {$store} store",
            );
        }
    }
    $created = [];
    $superseded = [];
    try {
        foreach ($rows as $row) {
            $source = (string) $row['local_path'];
            $real = realpath($source);
            if (
                $real === false ||
                is_link($source) ||
                !is_file($real) ||
                !str_starts_with($real, rtrim($directory, '/') . '/') ||
                !str_ends_with($real, '.sealed')
            ) {
                throw new RuntimeException(
                    'a verified artifact path is unsafe or unavailable: ' .
                        (string) $row['artifact_id'],
                );
            }
            $stamp = gmdate('Ymd\\THis\\Z') . '-' . bin2hex(random_bytes(4));
            $store = (string) $row['store'];
            $artifact = $store . ':rotation-' . $stamp;
            $destination =
                $directory . '/' . $store . '.rotation-' . $stamp . '.sealed';
            $record =
                ['store' => $store, 'local_path' => $destination] +
                encryptionRotateSealedFile(
                    $real,
                    $destination,
                    $oldKey,
                    $newKey,
                    $artifact,
                );
            encryptionVerifySealedArtifact(
                $destination,
                $newKey,
                (int) $record['plaintext_bytes'],
            );
            $created[] = $record;
            $superseded[] = (string) $row['artifact_id'];
        }
        $pdo->beginTransaction();
        try {
            encryptionRecordArtifacts($pdo, $created);
            $mark = $pdo->prepare(
                "UPDATE encryption_artifacts SET state = 'superseded' WHERE artifact_id = ? AND state = 'verified'",
            );
            foreach ($superseded as $artifact) {
                $mark->execute([$artifact]);
            }
            $config['encryption']['local_key_file'] = $newKeyPath;
            $saveConfig($config);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
        return $created;
    } catch (Throwable $error) {
        foreach ($created as $record) {
            @unlink((string) $record['local_path']);
        }
        throw $error;
    }
}

/** Retires superseded ciphertext only after current-key verification proves a
 * replacement exists for every affected store. @return int retired count */
function encryptionRetireSupersededArtifacts(
    array $config,
    PDO $pdo,
    string $webRoot,
    string $oldKeyPath,
): int {
    $settings = (array) ($config['encryption'] ?? []);
    if (($settings['mode'] ?? 'none') !== 'sealed_copies_local') {
        throw new RuntimeException('local sealed copies are not enabled');
    }
    $oldKey = encryptionReadLocalKey($oldKeyPath, $webRoot);
    $checked = encryptionVerifyConfiguredCopies($config, $pdo, $webRoot);
    if ($checked['failed'] > 0) {
        throw new RuntimeException(
            'current sealed copies must verify before superseded copies can be retired',
        );
    }
    $directory = encryptionArtifactDirectory($config, $webRoot);
    if ($directory === false || is_link($directory) || !is_dir($directory)) {
        throw new RuntimeException(
            'the sealed-artifact directory is unavailable',
        );
    }
    $rows = $pdo
        ->query(
            "SELECT artifact_id, store, local_path FROM encryption_artifacts WHERE state = 'superseded' ORDER BY created_at, artifact_id",
        )
        ->fetchAll();
    if ($rows === []) {
        return 0;
    }
    $current = $pdo
        ->query(
            "SELECT store, COUNT(*) AS copies FROM encryption_artifacts WHERE state = 'verified' GROUP BY store",
        )
        ->fetchAll();
    $coverage = [];
    foreach ($current as $row) {
        $coverage[(string) $row['store']] = (int) $row['copies'];
    }
    $renames = [];
    foreach ($rows as $row) {
        $store = (string) $row['store'];
        $source = (string) $row['local_path'];
        $real = realpath($source);
        if (($coverage[$store] ?? 0) < 1) {
            throw new RuntimeException(
                "no current verified replacement exists for {$store}",
            );
        }
        if (
            $real === false ||
            is_link($source) ||
            !is_file($real) ||
            !str_starts_with($real, rtrim($directory, '/') . '/') ||
            !str_ends_with($real, '.sealed')
        ) {
            throw new RuntimeException(
                'a superseded artifact path is unsafe or unavailable',
            );
        }
        $temporary = $directory . '/.retire-' . bin2hex(random_bytes(12));
        try {
            encryptionOpenFile($real, $temporary . '.verify', $oldKey);
        } finally {
            @unlink($temporary . '.verify');
        }
        $renames[] = [
            'artifact_id' => (string) $row['artifact_id'],
            'source' => $real,
            'temporary' => $temporary,
        ];
    }
    $moved = [];
    try {
        foreach ($renames as $rename) {
            if (!rename($rename['source'], $rename['temporary'])) {
                throw new RuntimeException(
                    'could not stage superseded ciphertext for retirement',
                );
            }
            $moved[] = $rename;
        }
        $pdo->beginTransaction();
        try {
            $mark = $pdo->prepare(
                "UPDATE encryption_artifacts SET state = 'retired' WHERE artifact_id = ? AND state = 'superseded'",
            );
            foreach ($moved as $rename) {
                $mark->execute([$rename['artifact_id']]);
            }
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    } catch (Throwable $error) {
        foreach (array_reverse($moved) as $rename) {
            if (is_file($rename['temporary'])) {
                @rename($rename['temporary'], $rename['source']);
            }
        }
        throw $error;
    }
    foreach ($moved as $rename) {
        if (!@unlink($rename['temporary'])) {
            throw new RuntimeException(
                'retired ciphertext metadata is recorded but a staged file could not be removed',
            );
        }
    }
    return count($moved);
}

function encryptionRequire(string $kek, string $artifactId): void
{
    if (!encryptionAvailable()) {
        throw new RuntimeException(
            'OpenSSL AES-256-GCM support is required for sealed copies',
        );
    }
    if (strlen($kek) !== 32) {
        throw new InvalidArgumentException(
            'a sealed copy needs a 256-bit key-encryption key',
        );
    }
    if ($artifactId === '' || strlen($artifactId) > 200) {
        throw new InvalidArgumentException(
            'an artifact needs a bounded identifier',
        );
    }
}

function encryptionChunkNonce(string $prefix, int $index): string
{
    if ($index < 0) {
        throw new RuntimeException('too many encrypted chunks');
    }
    return $prefix . encryptionChunkIndex($index);
}

function encryptionChunkIndex(int $index): string
{
    return pack('N2', intdiv($index, 4294967296), $index % 4294967296);
}

/** @param resource $stream */
function encryptionReadExact($stream, int $bytes): string
{
    $out = '';
    while (strlen($out) < $bytes && !feof($stream)) {
        $part = fread($stream, $bytes - strlen($out));
        if ($part === false) {
            break;
        }
        $out .= $part;
    }
    if (strlen($out) !== $bytes) {
        throw new RuntimeException('the sealed artifact is truncated');
    }
    return $out;
}

/** @param resource $stream */
function encryptionWriteAll($stream, string $data): void
{
    $offset = 0;
    $length = strlen($data);
    while ($offset < $length) {
        $written = fwrite($stream, substr($data, $offset));
        if (!is_int($written) || $written <= 0) {
            throw new RuntimeException('could not write the sealed artifact');
        }
        $offset += $written;
    }
}

/** Runs a file-creating operation under an owner-only umask and restores it. */
function encryptionWithPrivateUmask(callable $operation): mixed
{
    $previous = umask(0077);
    try {
        return $operation();
    } finally {
        umask($previous);
    }
}
