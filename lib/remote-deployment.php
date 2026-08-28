<?php
declare(strict_types=1);
require_once __DIR__ . '/setupstore.php';

/* Deployment profiles contain connection metadata and opaque secret references.
 * A profile never carries a password or private key value through a browser. */

const REMOTE_DEPLOYMENT_METHODS = ['rsync_ssh', 'sftp', 'ftps'];
const REMOTE_DEPLOYMENT_UPDATE_MODES = ['manual', 'download', 'automatic'];

function remoteDeploymentSecretReference(): string
{
    return 'deploy_ref:' . rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
}

function remoteDeploymentValidateSecretReference(string $reference): void
{
    if (!preg_match('/^deploy_ref:[A-Za-z0-9_-]{16,200}$/', $reference)) {
        throw new InvalidArgumentException('choose a stored deployment credential reference');
    }
}

function remoteDeploymentSecretKey(array $config): string
{
    $encryption = (array)($config['encryption'] ?? []);
    $path = trim((string)($encryption['local_key_file'] ?? ''));
    if ($path === '') throw new RuntimeException('configure a private local encryption key before storing deployment credentials');
    $master = encryptionReadLocalKey($path, dirname(__DIR__));
    $key = hash_hkdf('sha256', $master, 32, 'Zogograph remote deployment vault v1');
    if (!is_string($key) || strlen($key) !== 32) throw new RuntimeException('could not derive deployment credential key');
    return $key;
}

/** Stores a credential once and returns only a generated opaque reference. */
function remoteDeploymentSecretStore(array $config, string $value, string $actor): string
{
    if ($value === '' || strlen($value) > 16384) throw new InvalidArgumentException('deployment credential must be between 1 and 16384 bytes');
    $store = setupStoreOpen($config, true);
    if ($store === null) throw new RuntimeException('could not open deployment credentials store');
    $store->exec('CREATE TABLE IF NOT EXISTS remote_deployment_secrets (
        reference TEXT PRIMARY KEY, nonce BLOB NOT NULL, ciphertext BLOB NOT NULL, tag BLOB NOT NULL,
        created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL, updated_by TEXT NOT NULL
    )');
    $reference = remoteDeploymentSecretReference(); $nonce = random_bytes(12);
    $ciphertext = openssl_encrypt($value, 'aes-256-gcm', remoteDeploymentSecretKey($config), OPENSSL_RAW_DATA, $nonce, $tag, $reference, 16);
    if (!is_string($ciphertext) || !is_string($tag)) throw new RuntimeException('could not encrypt deployment credential');
    $store->prepare('INSERT INTO remote_deployment_secrets (reference, nonce, ciphertext, tag, created_at, updated_at, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$reference, $nonce, $ciphertext, $tag, time(), time(), mb_substr($actor, 0, 120)]);
    return $reference;
}

/** Intended for the reviewed deployment worker only; never expose through an API. */
function remoteDeploymentSecretValue(array $config, string $reference): string
{
    remoteDeploymentValidateSecretReference($reference);
    $store = setupStoreOpen($config, false);
    if ($store === null || (int)$store->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'remote_deployment_secrets'")->fetchColumn() !== 1) throw new InvalidArgumentException('deployment credential is unavailable');
    $stmt = $store->prepare('SELECT nonce, ciphertext, tag FROM remote_deployment_secrets WHERE reference = ?'); $stmt->execute([$reference]); $row = $stmt->fetch();
    if (!is_array($row)) throw new InvalidArgumentException('deployment credential is unavailable');
    $plain = openssl_decrypt($row['ciphertext'], 'aes-256-gcm', remoteDeploymentSecretKey($config), OPENSSL_RAW_DATA, $row['nonce'], $row['tag'], $reference);
    if (!is_string($plain) || $plain === '') throw new RuntimeException('deployment credential could not be opened');
    return $plain;
}

function remoteDeploymentSecretExists(array $config, string $reference): bool
{
    remoteDeploymentValidateSecretReference($reference);
    $store = setupStoreOpen($config, false);
    if ($store === null || (int)$store->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'remote_deployment_secrets'")->fetchColumn() !== 1) return false;
    $stmt = $store->prepare('SELECT COUNT(*) FROM remote_deployment_secrets WHERE reference = ?'); $stmt->execute([$reference]);
    return (int)$stmt->fetchColumn() === 1;
}

function remoteDeploymentTargetPath(string $path): ?string
{
    $path = trim($path);
    if ($path === '' || !str_starts_with($path, '/') || str_contains($path, "\0") || preg_match('#(?:^|/)\.\.?(/|$)#', $path)) return null;
    return preg_match('#^/[A-Za-z0-9._/@+=,:-]+$#', $path) ? rtrim($path, '/') ?: '/' : null;
}

function remoteDeploymentHost(string $host): ?string
{
    $host = strtolower(trim($host));
    if ($host === '' || strlen($host) > 253 || str_contains($host, "\0")) return null;
    if (filter_var($host, FILTER_VALIDATE_IP)) return $host;
    return preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $host) ? $host : null;
}

/** @return array{method:string,host:string,port:int,username:string,target_path:string,credential_reference:string,known_host_reference:?string,packages:list<string>,controller_url:string,update_mode:string} */
function remoteDeploymentProfile(array $input): array
{
    $method = (string)($input['method'] ?? '');
    if (!in_array($method, REMOTE_DEPLOYMENT_METHODS, true)) throw new InvalidArgumentException('choose rsync over SSH, SFTP, or FTP with explicit TLS');
    $host = remoteDeploymentHost((string)($input['host'] ?? ''));
    if ($host === null) throw new InvalidArgumentException('enter a DNS hostname or IP address without a URL scheme');
    $portText = trim((string)($input['port'] ?? ''));
    if ($portText === '') {
        $port = $method === 'ftps' ? 21 : 22;
    } else {
        $port = filter_var($portText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if ($port === false) throw new InvalidArgumentException('port must be between 1 and 65535');
    }
    $username = trim((string)($input['username'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9._-]{1,64}$/', $username)) throw new InvalidArgumentException('deployment username contains unsupported characters');
    $target = remoteDeploymentTargetPath((string)($input['target_path'] ?? ''));
    if ($target === null) throw new InvalidArgumentException('target path must be an absolute path without dot segments');
    $credential = trim((string)($input['credential_reference'] ?? ''));
    remoteDeploymentValidateSecretReference($credential);
    $known = trim((string)($input['known_host_reference'] ?? ''));
    if ($method !== 'ftps') remoteDeploymentValidateSecretReference($known);
    if ($method === 'ftps') $known = '';
    $packages = array_values(array_unique(array_filter((array)($input['packages'] ?? []), static fn(mixed $name): bool => is_string($name) && preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $name))));
    if ($packages === []) throw new InvalidArgumentException('choose at least one package group to deploy');
    $controller = trim((string)($input['controller_url'] ?? ''));
    $parts = parse_url($controller);
    if ($parts === false || strtolower((string)($parts['scheme'] ?? '')) !== 'https' || empty($parts['host']) || remoteDeploymentHost((string)$parts['host']) === null || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])) throw new InvalidArgumentException('controller URL must be a plain HTTPS URL');
    $update = (string)($input['update_mode'] ?? 'manual');
    if (!in_array($update, REMOTE_DEPLOYMENT_UPDATE_MODES, true)) throw new InvalidArgumentException('choose a supported remote update policy');
    return ['method' => $method, 'host' => $host, 'port' => (int)$port, 'username' => $username, 'target_path' => $target,
        'credential_reference' => $credential, 'known_host_reference' => $known === '' ? null : $known, 'packages' => $packages,
        'controller_url' => $controller, 'update_mode' => $update];
}
