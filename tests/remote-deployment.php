<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/remote-deployment.php';
function remoteDeploymentExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$profile = remoteDeploymentProfile(['method' => 'rsync_ssh', 'host' => 'Remote.Example.com', 'port' => 22, 'username' => 'deploy-user', 'target_path' => '/srv/zogograph-remote', 'credential_reference' => 'deploy-key', 'known_host_reference' => 'remote-host-key', 'packages' => ['remote-core', 'collector'], 'controller_url' => 'https://core.example', 'update_mode' => 'download']);
remoteDeploymentExpect($profile['host'] === 'remote.example.com' && $profile['known_host_reference'] === 'remote-host-key' && $profile['packages'] === ['remote-core', 'collector'], 'valid SSH deployment profile was not normalized');
$ftps = remoteDeploymentProfile(['method' => 'ftps', 'host' => '203.0.113.8', 'username' => 'deploy', 'target_path' => '/httpdocs/remote', 'credential_reference' => 'ftps-credential', 'packages' => ['remote-core'], 'controller_url' => 'https://core.example', 'update_mode' => 'manual']);
remoteDeploymentExpect($ftps['port'] === 21 && $ftps['known_host_reference'] === null, 'FTPS profile defaults are incorrect');
foreach ([['target_path' => '/srv/../bad'], ['host' => 'ssh://bad.example'], ['port' => '0'], ['port' => '70000'], ['credential_reference' => 'actual secret value!'], ['controller_url' => 'http://core.example'], ['controller_url' => 'https://bad_host.example']] as $change) {
    try { remoteDeploymentProfile($change + $profile); throw new RuntimeException('unsafe deployment profile was accepted'); } catch (InvalidArgumentException) { }
}
echo "remote deployment checks passed\n";
