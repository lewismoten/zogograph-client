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

/**
 * Returns non-executable, secret-free transport arguments for review. Values
 * in double braces are supplied only by a reviewed worker at run time.
 *
 * @return array{method:string,preflight:list<string>,copy:list<string>,packages:list<string>}
 */
function remoteDeploymentCommandPlan(array $input): array
{
    $profile = remoteDeploymentProfile($input);
    $identity = $profile['username'] . '@' . $profile['host'];
    if ($profile['method'] === 'rsync_ssh') {
        $ssh = ['ssh', '-p', (string)$profile['port'], '-o', 'BatchMode=yes', '-o', 'StrictHostKeyChecking=yes', '-o', 'UserKnownHostsFile={{encrypted host-key reference}}', '-i', '{{encrypted credential reference}}'];
        return ['method' => 'rsync_ssh', 'preflight' => [...$ssh, $identity, 'test', '-d', '--', $profile['target_path']], 'copy' => ['rsync', '--archive', '--delete-delay', '--protect-args', '-e', '{{reviewed SSH argument list}}', '{{selected package build}}/', $identity . ':' . $profile['target_path'] . '/'], 'packages' => $profile['packages']];
    }
    if ($profile['method'] === 'sftp') {
        return ['method' => 'sftp', 'preflight' => ['sftp', '-P', (string)$profile['port'], '-oBatchMode=yes', '-oStrictHostKeyChecking=yes', '-oUserKnownHostsFile={{encrypted host-key reference}}', '-i', '{{encrypted credential reference}}', $identity, '{{batch: ls target path}}'], 'copy' => ['sftp', '{{batch: put selected package build to target path}}'], 'packages' => $profile['packages']];
    }
    $url = 'ftps://' . $profile['host'] . ':' . $profile['port'] . $profile['target_path'];
    return ['method' => 'ftps', 'preflight' => ['curl', '--fail', '--silent', '--show-error', '--ftp-ssl-reqd', '--list-only', '--user', $profile['username'] . ':{{encrypted credential reference}}', $url], 'copy' => ['curl', '--fail', '--ftp-ssl-reqd', '--user', $profile['username'] . ':{{encrypted credential reference}}', '--upload-file', '{{selected package build}}', $url . '/{{remote filename}}'], 'packages' => $profile['packages']];
}

/** Runs a non-interactive command without returning its output to a browser. */
function remoteDeploymentRun(array $command): array
{
    $pipes = [];
    $process = @proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
    if (!is_resource($process)) return ['ok' => false, 'detail' => 'the required transfer program could not start'];
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false); stream_set_blocking($pipes[2], false);
    $deadline = microtime(true) + 25;
    do {
        stream_get_contents($pipes[1]); stream_get_contents($pipes[2]);
        $status = proc_get_status($process);
        if (!$status['running']) break;
        usleep(100000);
    } while (microtime(true) < $deadline);
    if (($status['running'] ?? false) === true) {
        proc_terminate($process);
        foreach ([1, 2] as $pipe) { stream_get_contents($pipes[$pipe]); fclose($pipes[$pipe]); }
        proc_close($process);
        return ['ok' => false, 'detail' => 'the target check timed out'];
    }
    foreach ([1, 2] as $pipe) { stream_get_contents($pipes[$pipe]); fclose($pipes[$pipe]); }
    $code = proc_close($process);
    return $code === 0 ? ['ok' => true, 'detail' => 'target directory verified'] : ['ok' => false, 'detail' => 'the target check failed (exit status ' . $code . ')'];
}

/** Verifies only the remote target directory, using owner-only temporary files. */
function remoteDeploymentTest(array $config, array $input, ?callable $runner = null): array
{
    $profile = remoteDeploymentProfile($input);
    $credential = remoteDeploymentSecretValue($config, $profile['credential_reference']);
    $directory = rtrim(sys_get_temp_dir(), '/') . '/zogograph-deploy-test-' . bin2hex(random_bytes(12));
    if (!mkdir($directory, 0700)) throw new RuntimeException('could not prepare a private deployment test workspace');
    $run = $runner ?? 'remoteDeploymentRun';
    try {
        $identity = $profile['username'] . '@' . $profile['host'];
        if ($profile['method'] === 'ftps') {
            $netrc = $directory . '/netrc';
            $contents = 'machine ' . $profile['host'] . "\nlogin " . $profile['username'] . "\npassword " . str_replace(["\r", "\n"], '', $credential) . "\n";
            if (file_put_contents($netrc, $contents) === false) throw new RuntimeException('could not prepare the FTPS credential');
            chmod($netrc, 0600);
            $result = $run(['curl', '--fail', '--silent', '--show-error', '--connect-timeout', '10', '--max-time', '25', '--ftp-ssl-reqd', '--list-only', '--netrc-file', $netrc, '--url', 'ftps://' . $profile['host'] . ':' . $profile['port'] . $profile['target_path']]);
        } else {
            $key = $directory . '/identity'; $knownHosts = $directory . '/known_hosts';
            $known = remoteDeploymentSecretValue($config, (string)$profile['known_host_reference']);
            if (file_put_contents($key, $credential) === false || file_put_contents($knownHosts, $known) === false) throw new RuntimeException('could not prepare the SSH credential');
            chmod($key, 0600); chmod($knownHosts, 0600);
            $options = ['-o', 'BatchMode=yes', '-o', 'ConnectTimeout=10', '-o', 'ConnectionAttempts=1', '-o', 'StrictHostKeyChecking=yes', '-o', 'UserKnownHostsFile=' . $knownHosts, '-i', $key];
            if ($profile['method'] === 'rsync_ssh') {
                $result = $run(['ssh', '-p', (string)$profile['port'], ...$options, $identity, 'test', '-d', '--', $profile['target_path']]);
            } else {
                $batch = $directory . '/sftp.batch';
                if (file_put_contents($batch, 'ls ' . $profile['target_path'] . "\n") === false) throw new RuntimeException('could not prepare the SFTP check');
                chmod($batch, 0600);
                $result = $run(['sftp', '-P', (string)$profile['port'], ...$options, '-b', $batch, $identity]);
            }
        }
        if (!is_array($result) || !isset($result['ok'], $result['detail'])) throw new RuntimeException('the deployment test returned an invalid result');
        return ['ok' => (bool)$result['ok'], 'detail' => mb_substr((string)$result['detail'], 0, 180), 'method' => $profile['method'], 'tested_at' => time()];
    } finally {
        foreach (glob($directory . '/*') ?: [] as $file) @unlink($file);
        @rmdir($directory);
    }
}
