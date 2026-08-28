<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/remote-deployment.php';
function remoteDeploymentExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$deploymentSource = file_get_contents(__DIR__ . '/../lib/remote-deployment.php');
remoteDeploymentExpect($deploymentSource !== false && str_contains($deploymentSource, '($written += strlen($chunk)) > $size') && str_contains($deploymentSource, '$written !== $size'), 'release ZIP staging does not enforce streamed entry-size bounds');
$profile = remoteDeploymentProfile(['method' => 'rsync_ssh', 'host' => 'Remote.Example.com', 'port' => 22, 'username' => 'deploy-user', 'target_path' => '/srv/zogograph-remote', 'credential_reference' => 'deploy_ref:credential-reference', 'known_host_reference' => 'deploy_ref:host-key-reference', 'packages' => ['remote-core', 'collector'], 'controller_url' => 'https://core.example', 'update_mode' => 'download']);
remoteDeploymentExpect($profile['host'] === 'remote.example.com' && $profile['known_host_reference'] === 'deploy_ref:host-key-reference' && $profile['packages'] === ['remote-core', 'collector'], 'valid SSH deployment profile was not normalized');
$ftps = remoteDeploymentProfile(['method' => 'ftps', 'host' => '203.0.113.8', 'username' => 'deploy', 'target_path' => '/httpdocs/remote', 'credential_reference' => 'deploy_ref:ftps-credential-x', 'packages' => ['remote-core'], 'controller_url' => 'https://core.example', 'update_mode' => 'manual']);
remoteDeploymentExpect($ftps['port'] === 21 && $ftps['known_host_reference'] === null, 'FTPS profile defaults are incorrect');
$plan = remoteDeploymentCommandPlan($profile);
remoteDeploymentExpect($plan['method'] === 'rsync_ssh' && in_array('StrictHostKeyChecking=yes', $plan['preflight'], true)
    && !str_contains(implode(' ', $plan['copy']), 'deploy_ref:'), 'SSH deployment preview exposes a reference or lacks host-key verification');
$ftpsPlan = remoteDeploymentCommandPlan($ftps);
remoteDeploymentExpect($ftpsPlan['method'] === 'ftps' && in_array('--ftp-ssl-reqd', $ftpsPlan['preflight'], true), 'FTPS deployment preview does not require TLS');
$localRun = remoteDeploymentRun([PHP_BINARY, '-r', 'exit(0);']);
remoteDeploymentExpect($localRun['ok'] === true, 'deployment process runner did not retain a successful child exit status');
foreach ([['target_path' => '/srv/../bad'], ['host' => 'ssh://bad.example'], ['port' => '0'], ['port' => '70000'], ['credential_reference' => 'actual secret value!'], ['controller_url' => 'http://core.example'], ['controller_url' => 'https://bad_host.example']] as $change) {
    try { remoteDeploymentProfile($change + $profile); throw new RuntimeException('unsafe deployment profile was accepted'); } catch (InvalidArgumentException) { }
}
$directory = sys_get_temp_dir() . '/zogograph-remote-deployment-' . bin2hex(random_bytes(6));
remoteDeploymentExpect(mkdir($directory, 0700), 'could not create deployment-vault test directory');
try {
    $keyPath = $directory . '/deployment.key';
    encryptionCreateLocalKey($keyPath, dirname(__DIR__));
    $config = ['database' => $directory . '/data/stats.db', 'encryption' => ['local_key_file' => $keyPath]];
    $reference = remoteDeploymentSecretStore($config, 'private-deployment-material', 'test');
    remoteDeploymentExpect(remoteDeploymentSecretExists($config, $reference) && remoteDeploymentSecretValue($config, $reference) === 'private-deployment-material',
        'deployment credential vault did not retain encrypted material behind its opaque reference');
    $knownReference = remoteDeploymentSecretStore($config, 'remote.example.com ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAITest', 'test');
    $testProfile = array_replace($profile, ['credential_reference' => $reference, 'known_host_reference' => $knownReference]);
    $seen = [];
    $result = remoteDeploymentTest($config, $testProfile, static function (array $command) use (&$seen): array { $seen = $command; return ['ok' => true, 'detail' => 'target directory verified']; });
    remoteDeploymentExpect($result['ok'] === true && in_array('StrictHostKeyChecking=yes', $seen, true)
        && !str_contains(implode(' ', $seen), 'private-deployment-material'), 'deployment test lacks SSH verification or exposes credential material');
    $archive = $directory . '/remote-release.zip'; $zip = new ZipArchive();
    remoteDeploymentExpect($zip->open($archive, ZipArchive::CREATE) === true && $zip->addFromString('dist/index.html', '<!doctype html><title>Remote</title>') && $zip->close(), 'could not make a test remote release archive');
    $staged = remoteDeploymentStageRelease($archive);
    remoteDeploymentExpect($staged['files'] === 1 && is_file($staged['directory'] . '/index.html'), 'release archive was not safely staged with its common root removed');
    remoteDeploymentRemoveDirectory($staged['directory']);
    $calls = [];
    $copied = remoteDeploymentCopyRelease($config, $testProfile, $archive, static function (array $command) use (&$calls): array { $calls[] = $command; return ['ok' => true, 'detail' => 'ok']; });
    remoteDeploymentExpect($copied['ok'] === true && count($calls) === 2 && $calls[1][0] === 'rsync' && !str_contains(implode(' ', $calls[1]), 'private-deployment-material'), 'release copy did not retest then use secret-free rsync arguments');
    @unlink($keyPath); @unlink($directory . '/.htaccess'); foreach (glob($directory . '/data/setup.db*') ?: [] as $file) @unlink($file); @rmdir($directory . '/data'); @rmdir($directory);
} catch (Throwable $error) {
    @unlink($keyPath ?? ''); @rmdir($directory); throw $error;
}
echo "remote deployment checks passed\n";
