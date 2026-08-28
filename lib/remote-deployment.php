<?php
declare(strict_types=1);
require_once __DIR__ . '/setupstore.php';

/* Deployment profiles contain connection metadata and opaque secret references.
 * A profile never carries a password or private key value through a browser. */

const REMOTE_DEPLOYMENT_METHODS = ['rsync_ssh', 'sftp', 'ftps'];
const REMOTE_DEPLOYMENT_UPDATE_MODES = ['manual', 'download', 'auto'];

function remoteDeploymentRelativePath(string $path): bool
{
    return $path !== '' && !str_starts_with($path, '/') && !str_contains($path, "\0")
        && !in_array('..', explode('/', str_replace('\\', '/', $path)), true);
}

function remoteDeploymentRemoveDirectory(string $directory): void
{
    if (!is_dir($directory) || is_link($directory)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) $file->isDir() && !$file->isLink() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
    @rmdir($directory);
}

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
    // `automatic` was the original core-only spelling. The remote updater's
    // signed configuration protocol uses `auto`; accept old saved profiles,
    // then normalize them before a profile is reviewed or handed off.
    $update = (string)($input['update_mode'] ?? 'manual');
    if ($update === 'automatic') $update = 'auto';
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
    $exitCode = (int)($status['exitcode'] ?? -1);
    foreach ([1, 2] as $pipe) { stream_get_contents($pipes[$pipe]); fclose($pipes[$pipe]); }
    $closedCode = proc_close($process);
    $code = $exitCode >= 0 ? $exitCode : $closedCode;
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

/** Extract a bounded release ZIP into a private staging directory. */
function remoteDeploymentStageRelease(string $archive): array
{
    if (!class_exists(ZipArchive::class)) throw new RuntimeException('PHP ZipArchive support is required to stage a deployment release');
    if (!is_file($archive) || is_link($archive) || !is_readable($archive)) throw new InvalidArgumentException('choose a readable ZIP release archive');
    $bytes = filesize($archive);
    if (!is_int($bytes) || $bytes < 1 || $bytes > 67108864) throw new InvalidArgumentException('release archive must be between 1 byte and 64 MiB');
    $zip = new ZipArchive();
    if ($zip->open($archive, ZipArchive::RDONLY) !== true) throw new InvalidArgumentException('release archive is not a readable ZIP file');
    $directory = rtrim(sys_get_temp_dir(), '/') . '/zogograph-deploy-release-' . bin2hex(random_bytes(12));
    if (!mkdir($directory, 0700)) { $zip->close(); throw new RuntimeException('could not prepare a private release workspace'); }
    try {
        if ($zip->numFiles < 1 || $zip->numFiles > 1000) throw new InvalidArgumentException('release archive contains an unsupported number of files');
        $entries = []; $total = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if (!is_array($stat)) throw new InvalidArgumentException('release archive entry is invalid');
            $name = str_replace('\\', '/', (string)($stat['name'] ?? ''));
            if ($name === '' || str_ends_with($name, '/')) continue;
            if (!remoteDeploymentRelativePath($name) || isset($entries[$name])) throw new InvalidArgumentException('release archive contains an unsafe path');
            $size = (int)($stat['size'] ?? -1); $compressed = (int)($stat['comp_size'] ?? -1);
            if ($size < 0 || $size > 33554432 || $compressed < 0 || ($size > 1048576 && $compressed === 0) || ($compressed > 0 && $size > $compressed * 200) || ($total += $size) > 67108864) throw new InvalidArgumentException('release archive has an unsafe expanded size');
            $attributes = 0; $system = 0;
            if (!$zip->getExternalAttributesIndex($index, $system, $attributes)) throw new InvalidArgumentException('release archive entry attributes are unavailable');
            $type = ($attributes >> 16) & 0170000;
            if ($type !== 0 && $type !== 0100000) throw new InvalidArgumentException('release archive may contain regular files only');
            $entries[$name] = $index;
        }
        if ($entries === []) throw new InvalidArgumentException('release archive does not contain files');
        $parts = array_map(static fn(string $name): string => explode('/', $name, 2)[0], array_keys($entries));
        $prefix = count(array_unique($parts)) === 1 && str_contains((string)array_key_first($entries), '/') ? $parts[0] . '/' : '';
        $count = 0;
        foreach ($entries as $name => $index) {
            $relative = $prefix === '' ? $name : substr($name, strlen($prefix));
            if ($relative === '' || !remoteDeploymentRelativePath($relative)) throw new InvalidArgumentException('release archive root is invalid');
            $destination = $directory . '/' . $relative; $parent = dirname($destination);
            if (!is_dir($parent) && !mkdir($parent, 0700, true)) throw new RuntimeException('could not create a release path');
            $input = $zip->getStream($name); $output = @fopen($destination, 'xb');
            if (!is_resource($input) || !is_resource($output)) { if (is_resource($input)) fclose($input); if (is_resource($output)) fclose($output); throw new RuntimeException('could not read a release file'); }
            $written = 0;
            while (!feof($input)) { $chunk = fread($input, 1048576); if ($chunk === false || ($written += strlen($chunk)) > $size || fwrite($output, $chunk) !== strlen($chunk)) { fclose($input); fclose($output); throw new RuntimeException('release archive entry exceeds its declared size or could not be staged'); } }
            if ($written !== $size) { fclose($input); fclose($output); throw new RuntimeException('release archive entry does not match its declared size'); }
            fclose($input); fclose($output); chmod($destination, 0600); $count++;
        }
        return ['directory' => $directory, 'files' => $count];
    } catch (Throwable $error) {
        remoteDeploymentRemoveDirectory($directory); throw $error;
    } finally { $zip->close(); }
}

/** Re-tests the target, then copies a staged release. It never exposes process output. */
function remoteDeploymentCopyRelease(array $config, array $input, string $archive, ?callable $runner = null): array
{
    $profile = remoteDeploymentProfile($input); $run = $runner ?? 'remoteDeploymentRun';
    $tested = remoteDeploymentTest($config, $profile, $run);
    if (!$tested['ok']) return $tested + ['files' => 0];
    $release = remoteDeploymentStageRelease($archive); $workspace = rtrim(sys_get_temp_dir(), '/') . '/zogograph-deploy-copy-' . bin2hex(random_bytes(12));
    if (!mkdir($workspace, 0700)) { remoteDeploymentRemoveDirectory($release['directory']); throw new RuntimeException('could not prepare a private deployment workspace'); }
    try {
        $credential = remoteDeploymentSecretValue($config, $profile['credential_reference']);
        $identity = $profile['username'] . '@' . $profile['host'];
        if ($profile['method'] === 'ftps') {
            $netrc = $workspace . '/netrc';
            $contents = 'machine ' . $profile['host'] . "\nlogin " . $profile['username'] . "\npassword " . str_replace(["\r", "\n"], '', $credential) . "\n";
            if (file_put_contents($netrc, $contents) === false) throw new RuntimeException('could not prepare the FTPS credential');
            chmod($netrc, 0600);
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($release['directory'], FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->isLink()) throw new RuntimeException('staged release contains an unsafe file');
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($release['directory']) + 1));
                $result = $run(['curl', '--fail', '--silent', '--show-error', '--connect-timeout', '10', '--max-time', '25', '--ftp-ssl-reqd', '--ftp-create-dirs', '--netrc-file', $netrc, '--upload-file', $file->getPathname(), '--url', 'ftps://' . $profile['host'] . ':' . $profile['port'] . $profile['target_path'] . '/' . $relative]);
                if (!($result['ok'] ?? false)) return ['ok' => false, 'detail' => 'release copy failed before all files were transferred', 'method' => $profile['method'], 'files' => $release['files']];
            }
        } else {
            $key = $workspace . '/identity'; $knownHosts = $workspace . '/known_hosts';
            $known = remoteDeploymentSecretValue($config, (string)$profile['known_host_reference']);
            if (file_put_contents($key, $credential) === false || file_put_contents($knownHosts, $known) === false) throw new RuntimeException('could not prepare the SSH credential');
            chmod($key, 0600); chmod($knownHosts, 0600);
            $options = ['-o', 'BatchMode=yes', '-o', 'ConnectTimeout=10', '-o', 'ConnectionAttempts=1', '-o', 'StrictHostKeyChecking=yes', '-o', 'UserKnownHostsFile=' . $knownHosts, '-i', $key];
            if ($profile['method'] === 'rsync_ssh') {
                $ssh = array_map('escapeshellarg', ['ssh', '-p', (string)$profile['port'], ...$options]);
                $result = $run(['rsync', '--archive', '--delete-delay', '--protect-args', '-e', implode(' ', $ssh), $release['directory'] . '/', $identity . ':' . $profile['target_path'] . '/']);
            } else {
                $batch = $workspace . '/sftp.batch';
                if (file_put_contents($batch, 'put -r ' . $release['directory'] . '/. ' . $profile['target_path'] . "\n") === false) throw new RuntimeException('could not prepare the SFTP copy');
                chmod($batch, 0600); $result = $run(['sftp', '-P', (string)$profile['port'], ...$options, '-b', $batch, $identity]);
            }
            if (!($result['ok'] ?? false)) return ['ok' => false, 'detail' => 'release copy failed before all files were transferred', 'method' => $profile['method'], 'files' => $release['files']];
        }
        return ['ok' => true, 'detail' => 'release copied after target verification', 'method' => $profile['method'], 'files' => $release['files'], 'copied_at' => time()];
    } finally { remoteDeploymentRemoveDirectory($release['directory']); remoteDeploymentRemoveDirectory($workspace); }
}
