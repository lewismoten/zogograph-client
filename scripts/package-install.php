#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("This package command is CLI-only.\n"); }
require_once __DIR__ . '/../lib/setupstore.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/package-runtime.php';

function packageInstallUsage(): never
{
    fwrite(STDERR, "Usage:\n"
        . "  php scripts/package-install.php list\n"
        . "  php scripts/package-install.php install /absolute/signed-package-directory\n"
        . "  php scripts/package-install.php enable PACKAGE-ID capability[,capability...]\n"
        . "  php scripts/package-install.php disable PACKAGE-ID\n");
    exit(64);
}

$configFile = __DIR__ . '/../lib/config.php';
if (!is_file($configFile)) { fwrite(STDERR, "Zogograph is not configured.\n"); exit(1); }
$config = loadConfiguredSettings($configFile);
$pdo = db($config);
$command = $argv[1] ?? '';
try {
    if ($command === 'list' && $argc === 2) {
        foreach (packageRuntimePackages($pdo) as $package) {
            echo $package['id'] . ' ' . $package['version'] . ' — ' . $package['state'] . "\n";
        }
        exit(0);
    }
    if ($command === 'install' && $argc === 3) {
        $installed = packageRuntimeInstall($config, $pdo, $argv[2], 'cli administrator');
        logAction($pdo, 'package_runtime_install', $installed['id'], 'ok', 0, 0, $installed['version'] . '; signed package installed');
        echo "Installed {$installed['id']} {$installed['version']}. Review and grant its declared capabilities before enabling it.\n";
        exit(0);
    }
    if ($command === 'enable' && $argc === 4) {
        $grants = preg_split('/[\s,]+/', trim($argv[3])) ?: [];
        packageRuntimeSetEnabled($pdo, $argv[2], $grants, true, 'cli administrator');
        logAction($pdo, 'package_runtime_enable', $argv[2], 'ok', 0, 0, implode(', ', $grants));
        echo "Package registered with its requested grants. Execution remains unavailable until the isolated runtime is installed.\n";
        exit(0);
    }
    if ($command === 'disable' && $argc === 3) {
        packageRuntimeSetEnabled($pdo, $argv[2], [], false, 'cli administrator');
        logAction($pdo, 'package_runtime_disable', $argv[2], 'ok', 0, 0, 'disabled');
        echo "Package disabled.\n";
        exit(0);
    }
    packageInstallUsage();
} catch (Throwable $error) { fwrite(STDERR, 'Package command refused: ' . $error->getMessage() . "\n"); exit(1); }
