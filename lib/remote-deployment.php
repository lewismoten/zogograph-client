<?php
declare(strict_types=1);

/* Deployment profiles contain connection metadata and opaque secret references.
 * A profile never carries a password or private key value through a browser. */

const REMOTE_DEPLOYMENT_METHODS = ['rsync_ssh', 'sftp', 'ftps'];
const REMOTE_DEPLOYMENT_UPDATE_MODES = ['manual', 'download', 'automatic'];

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
    $port = filter_var($input['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if ($port === false) $port = $method === 'ftps' ? 21 : 22;
    $username = trim((string)($input['username'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9._-]{1,64}$/', $username)) throw new InvalidArgumentException('deployment username contains unsupported characters');
    $target = remoteDeploymentTargetPath((string)($input['target_path'] ?? ''));
    if ($target === null) throw new InvalidArgumentException('target path must be an absolute path without dot segments');
    $credential = trim((string)($input['credential_reference'] ?? ''));
    if (!preg_match('/^[a-z][a-z0-9_-]{2,62}$/', $credential)) throw new InvalidArgumentException('choose a stored deployment credential reference');
    $known = trim((string)($input['known_host_reference'] ?? ''));
    if ($method !== 'ftps' && !preg_match('/^[a-z][a-z0-9_-]{2,62}$/', $known)) throw new InvalidArgumentException('choose a stored SSH host-key reference');
    if ($method === 'ftps') $known = '';
    $packages = array_values(array_unique(array_filter((array)($input['packages'] ?? []), static fn(mixed $name): bool => is_string($name) && preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $name))));
    if ($packages === []) throw new InvalidArgumentException('choose at least one package group to deploy');
    $controller = trim((string)($input['controller_url'] ?? ''));
    $parts = parse_url($controller);
    if ($parts === false || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])) throw new InvalidArgumentException('controller URL must be a plain HTTPS URL');
    $update = (string)($input['update_mode'] ?? 'manual');
    if (!in_array($update, REMOTE_DEPLOYMENT_UPDATE_MODES, true)) throw new InvalidArgumentException('choose a supported remote update policy');
    return ['method' => $method, 'host' => $host, 'port' => (int)$port, 'username' => $username, 'target_path' => $target,
        'credential_reference' => $credential, 'known_host_reference' => $known === '' ? null : $known, 'packages' => $packages,
        'controller_url' => $controller, 'update_mode' => $update];
}
