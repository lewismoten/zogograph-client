<?php
declare(strict_types=1);

/*
 * Talking to other people's servers, slowly.
 *
 * whois registries and public resolvers are infrastructure somebody else pays
 * for, and they defend themselves. Twenty lookups in a burst gets an address
 * rate-limited, and a rate-limited install reports every domain as unknown -
 * which reads as twenty problems rather than as one self-inflicted one.
 *
 * So outbound work is a queue that drains rather than a batch that runs. A
 * handful per collector run, oldest request first, and the rest wait. On a
 * five-minute cron one per run is a hundred a day, which is far more than any
 * real watch list needs and slow enough that nobody notices.
 *
 * The ordering matters as much as the rate. Draining oldest-first means a
 * request made an hour ago is answered before one made a minute ago, so a
 * single impatient person cannot starve the queue by asking repeatedly.
 */

require_once __DIR__ . '/db.php';

/** How many of one kind of outbound lookup a single run may make. */
function externalBudget(array $config, string $command): int
{
    $set = $config['external'] ?? [];
    $byCommand = $set['per_run_by_command'] ?? [];

    $limit = (int)($byCommand[$command] ?? $set['per_run'] ?? 1);

    // A budget of zero would stop the queue for ever, which is never what
    // somebody means by "slow down" - one is the floor.
    return max(1, min(100, $limit));
}

/**
 * The package manager this machine uses, and the line to install with.
 *
 * Only ever used to print an instruction. Getting it wrong prints a command
 * that does not exist, which is annoying; guessing silently and being wrong in
 * a way somebody pastes into a root shell is worse - so the detection looks for
 * the binary rather than for the distribution.
 *
 * @return array{manager: string, install: string, detected: bool}
 */
function packageManager(array $config): array
{
    $named = (string)($config['external']['package_manager'] ?? 'auto');

    $known = [
        'dnf' => 'dnf install -y %s',
        'yum' => 'yum install -y %s',
        'apt' => 'apt-get install -y %s',
        'apk' => 'apk add %s',
        'zypper' => 'zypper install -y %s',
        'pacman' => 'pacman -S --noconfirm %s',
    ];

    if ($named !== 'auto' && isset($known[$named])) {
        return ['manager' => $named, 'install' => $known[$named], 'detected' => false];
    }

    foreach ($known as $manager => $template) {
        foreach (['/usr/bin/', '/bin/', '/usr/local/bin/', '/sbin/', '/usr/sbin/'] as $dir) {
            if (@is_executable($dir . $manager)) {
                return ['manager' => $manager, 'install' => $template, 'detected' => true];
            }
        }
    }

    return [
        'manager' => 'unknown',
        // Named rather than guessed. A wrong command pasted into a root shell
        // is worse than an honest "install it however this machine installs
        // things".
        'install' => 'install %s using this system\'s package manager',
        'detected' => false,
    ];
}

/**
 * What to tell somebody when a command is missing.
 *
 * The package name is not always the command name - `dig` lives in bind-utils
 * on Red Hat and dnsutils on Debian - so both are carried and the right one is
 * chosen by the manager that was found.
 *
 * @return array{command: string, package: string, install: string, manager: string, note: string}
 */
function missingCommand(array $config, string $command): array
{
    $packages = [
        'whois' => ['dnf' => 'whois', 'yum' => 'whois', 'apt' => 'whois',
                    'apk' => 'whois', 'default' => 'whois'],
        'dig' => ['dnf' => 'bind-utils', 'yum' => 'bind-utils', 'apt' => 'dnsutils',
                  'apk' => 'bind-tools', 'default' => 'bind-utils'],
        'tcpdump' => ['default' => 'tcpdump'],
        'ss' => ['dnf' => 'iproute', 'yum' => 'iproute', 'apt' => 'iproute2',
                 'default' => 'iproute2'],
    ];

    $manager = packageManager($config);
    $set = $packages[$command] ?? ['default' => $command];
    $package = $set[$manager['manager']] ?? $set['default'] ?? $command;

    return [
        'command' => $command,
        'package' => $package,
        'manager' => $manager['manager'],
        'install' => sprintf($manager['install'], $package),
        'note' => $manager['detected']
            ? ''
            : ($manager['manager'] === 'unknown'
                ? 'No package manager was recognised on this machine, so the line above names '
                    . 'the package rather than the command to install it with.'
                : 'Taken from external.package_manager in the config rather than from what is '
                    . 'on this machine.'),
    ];
}
