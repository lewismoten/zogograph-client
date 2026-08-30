<?php
declare(strict_types=1);

/*
 * What this machine can actually do, and what this account can actually reach.
 *
 * On ordinary hosting those are the same question. On CloudLinux they are not,
 * and the gap between them is the reason half of this project's diagnostics
 * report "not available" on a server where the tool is installed and working.
 *
 * CageFS gives every account its own private view of the filesystem, built from
 * a skeleton at /usr/share/cagefs-skeleton. An account sees a small, curated
 * /usr, /opt and /bin - enough to run a website - and nothing else. A program
 * the host has in /usr/sbin is not hidden by permissions; it is simply not
 * present in the filesystem the account is looking at. `which iostat` inside
 * the account is not being refused, it is telling the truth about a world where
 * iostat does not exist.
 *
 * That produces states an ordinary "is it installed?" check cannot express, and
 * the whole point of this file is to keep them apart:
 *
 *   available      on the host and reachable from the account
 *   host_only      installed, working, and invisible in here - exposing it is a
 *                  CageFS configuration change, not an installation
 *   alternate      not on PATH but present under another name or directory,
 *                  which on cPanel is routine: php is ea-php85 in
 *                  /opt/cpanel/ea-php85/root/usr/bin
 *   unusable       found and not executable by this account
 *   missing        not on the host at all - this one needs a package installed
 *
 * Telling somebody to install a package that is already installed, because the
 * check could not see it, wastes an afternoon. Telling them it is missing when
 * it needs a CageFS entry instead wastes the same afternoon differently.
 */

require_once __DIR__ . '/software.php';
require_once __DIR__ . '/collect.php';

/** Anything run through a shell must match this. Nothing else is ever passed. */
const PROGRAM_NAME_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9._+-]{0,63}$/';

/** Where a program may be found when it is not on PATH. */
/*
 * Where the fallback `find` looks for a program that is not on PATH.
 *
 * /usr/local/jetapps is here because JetBackup installs its CLI several levels
 * down inside it and puts nothing on PATH - so "is jetbackup5api installed" was
 * unanswerable by every mechanism this file has, and the backups page reported
 * the CLI as missing on machines that had it.
 */
const CAPABILITY_SEARCH_ROOTS = [
    '/usr/bin',
    '/usr/sbin',
    '/usr/local/bin',
    '/usr/local/sbin',
    '/bin',
    '/sbin',
    '/opt',
    '/usr/local/jetapps',
];

/**
 * The programs worth knowing about, and what each one buys you.
 *
 * The description is deliberately the *capability*, not the program. "iostat"
 * means nothing to somebody deciding whether to ask their host for it; "disk
 * I/O history per device" is a thing they can want.
 *
 * `key` marks the ones that unlock more than they cost. Checking a hundred
 * packages is busywork; these fifteen are the difference between guessing at a
 * problem and measuring it.
 */
function capabilityCatalogue(): array
{
    $g = static fn(string $group, array $rows): array => array_map(
        fn(array $r): array => $r + ['group' => $group],
        $rows,
    );

    return array_merge(
        $g('core', [
            ['name' => 'sh', 'does' => 'Run shell commands at all'],
            ['name' => 'bash', 'does' => 'Shell scripting beyond POSIX sh'],
            ['name' => 'cat', 'does' => 'Read files'],
            ['name' => 'cp', 'does' => 'Copy files'],
            ['name' => 'mv', 'does' => 'Move and rename'],
            ['name' => 'rm', 'does' => 'Delete files'],
            ['name' => 'mkdir', 'does' => 'Create directories'],
            [
                'name' => 'find',
                'does' => 'Search the filesystem by name, size or age',
            ],
            ['name' => 'grep', 'does' => 'Search inside files'],
            ['name' => 'sed', 'does' => 'Edit text in a stream'],
            ['name' => 'awk', 'does' => 'Field-based text processing'],
            ['name' => 'sort', 'does' => 'Order lines'],
            ['name' => 'uniq', 'does' => 'Collapse duplicates'],
            ['name' => 'cut', 'does' => 'Take columns from text'],
            ['name' => 'tr', 'does' => 'Translate characters'],
            ['name' => 'head', 'does' => 'Read the start of a file'],
            ['name' => 'tail', 'does' => 'Follow the end of a log'],
            ['name' => 'wc', 'does' => 'Count lines and bytes'],
            ['name' => 'xargs', 'does' => 'Build command lines from input'],
            ['name' => 'date', 'does' => 'Read and format the clock'],
            ['name' => 'stat', 'does' => 'File size, owner, times and inode'],
            ['name' => 'du', 'does' => 'Directory sizes'],
            ['name' => 'df', 'does' => 'Filesystem space'],
            ['name' => 'ps', 'does' => 'What is running'],
            ['name' => 'kill', 'does' => 'Stop a process'],
            ['name' => 'nice', 'does' => 'Run something at lower priority'],
            ['name' => 'nohup', 'does' => 'Survive a disconnect'],
            ['name' => 'tar', 'does' => 'Archive directories'],
            ['name' => 'gzip', 'does' => 'Compress'],
            ['name' => 'gunzip', 'does' => 'Decompress'],
            ['name' => 'ln', 'does' => 'Hard and symbolic links'],
            ['name' => 'chmod', 'does' => 'Change permissions'],
            ['name' => 'chown', 'does' => 'Change ownership'],
            ['name' => 'touch', 'does' => 'Create a file or set its time'],
            ['name' => 'env', 'does' => 'Inspect the environment'],
            ['name' => 'id', 'does' => 'Which user and groups'],
            ['name' => 'whoami', 'does' => 'Which user'],
            ['name' => 'uname', 'does' => 'Kernel and architecture'],
            ['name' => 'hostname', 'does' => 'Machine name'],
            ['name' => 'mount', 'does' => 'List or attach filesystems'],
            ['name' => 'umount', 'does' => 'Detach filesystems'],
        ]),

        $g('sysadmin', [
            ['name' => 'top', 'does' => 'Live process and CPU view'],
            ['name' => 'free', 'does' => 'Memory in use and available'],
            ['name' => 'uptime', 'does' => 'Load average and time since boot'],
            [
                'name' => 'vmstat',
                'does' => 'Memory, swap and CPU over time',
                'package' => 'procps-ng',
            ],
            [
                'name' => 'lsblk',
                'does' => 'Block devices and partitions',
                'package' => 'util-linux',
            ],
            [
                'name' => 'findmnt',
                'does' => 'Mount tree, including bind mounts',
                'package' => 'util-linux',
            ],
            [
                'name' => 'ss',
                'does' => 'Open sockets and listening ports',
                'package' => 'iproute',
                'key' => true,
            ],
            [
                'name' => 'ip',
                'does' => 'Interfaces, addresses and routes',
                'package' => 'iproute',
            ],
            [
                'name' => 'ping',
                'does' => 'Reachability of a host',
                'package' => 'iputils',
            ],
            [
                'name' => 'traceroute',
                'does' => 'Where along a path traffic stops',
                'package' => 'traceroute',
            ],
            [
                'name' => 'curl',
                'does' => 'HTTP checks against a URL',
                'package' => 'curl',
                'key' => true,
            ],
            [
                'name' => 'wget',
                'does' => 'Fetch over HTTP',
                'package' => 'wget',
            ],
            [
                'name' => 'ssh',
                'does' => 'Connect to another machine',
                'package' => 'openssh-clients',
            ],
            [
                'name' => 'scp',
                'does' => 'Copy files between machines',
                'package' => 'openssh-clients',
            ],
            [
                'name' => 'sftp',
                'does' => 'Interactive file transfer',
                'package' => 'openssh-clients',
            ],
            [
                'name' => 'openssl',
                'does' => 'Certificate and TLS inspection',
                'package' => 'openssl',
            ],
            [
                'name' => 'systemctl',
                'does' => 'Service state and restart counts',
                'package' => 'systemd',
            ],
            [
                'name' => 'journalctl',
                'does' => 'System log, including crashes',
                'package' => 'systemd',
            ],
            [
                'name' => 'crontab',
                'does' => 'Scheduled jobs',
                'package' => 'cronie',
            ],
            [
                'name' => 'logger',
                'does' => 'Write to the system log',
                'package' => 'util-linux',
            ],
            [
                'name' => 'dmesg',
                'does' => 'Kernel messages: OOM kills, disk errors',
                'package' => 'util-linux',
            ],
        ]),

        $g('dev', [
            [
                'name' => 'php',
                'does' => 'Run PHP from the command line',
                'version_arg' => '-v',
            ],
            ['name' => 'perl', 'does' => 'Run Perl', 'version_arg' => '-v'],
            ['name' => 'python3', 'does' => 'Run Python'],
            [
                'name' => 'git',
                'does' => 'Inspect repositories and history',
                'package' => 'git',
                'key' => true,
            ],
            [
                'name' => 'make',
                'does' => 'Build from a Makefile',
                'package' => 'make',
            ],
            ['name' => 'gcc', 'does' => 'Compile C', 'package' => 'gcc'],
            [
                'name' => 'mysql',
                'does' => 'Query MySQL/MariaDB directly',
                'package' => 'mysql',
                'key' => true,
            ],
            [
                'name' => 'mariadb',
                'does' => 'Query MariaDB directly',
                'package' => 'mariadb',
                'key' => true,
            ],
            ['name' => 'mysqldump', 'does' => 'Back up a database'],
            [
                'name' => 'sqlite3',
                'does' => 'Query SQLite files',
                'package' => 'sqlite',
                'key' => true,
            ],
            ['name' => 'composer', 'does' => 'PHP dependency management'],
            ['name' => 'npm', 'does' => 'Node dependency management'],
            [
                'name' => 'node',
                'does' => 'Run JavaScript',
                'version_arg' => '-v',
            ],
            [
                'name' => 'wp',
                'does' => 'WordPress administration from the shell',
            ],
            [
                'name' => 'rsync',
                'does' => 'Efficient copies and backups',
                'package' => 'rsync',
                'key' => true,
            ],
            /*
             * The backup CLIs, which are the difference between a backup page
             * that knows what is in a backup and one that describes directories.
             *
             * Not on PATH on any install seen so far - JetBackup lives several
             * levels inside /usr/local/jetapps - so these are found by the
             * fallback search rather than by `command -v`, and reported as
             * "under another path" rather than missing. `key` because the
             * backups page is unable to say anything useful without one.
             */
            [
                'name' => 'jetbackup5api',
                'does' => 'What JetBackup 5 has backed up, and when',
                'package' => 'jetbackup5',
                'key' => true,
                'version_arg' => '--version',
            ],
            [
                'name' => 'jetbackupapi',
                'does' => 'What JetBackup 4 has backed up, and when',
                'package' => 'jetbackup',
                'version_arg' => '--version',
            ],
        ]),

        $g('diagnostics', [
            [
                'name' => 'htop',
                'does' => 'Interactive process view',
                'package' => 'htop',
            ],
            [
                'name' => 'iotop',
                'does' => 'Which process is using the disk',
                'package' => 'iotop',
            ],
            [
                'name' => 'iostat',
                'does' => 'Disk I/O history per device',
                'package' => 'sysstat',
                'key' => true,
            ],
            [
                'name' => 'sar',
                'does' => 'Historical CPU, memory and I/O',
                'package' => 'sysstat',
                'key' => true,
            ],
            [
                'name' => 'lsof',
                'does' => 'Open files, including deleted ones holding space',
                'package' => 'lsof',
                'key' => true,
                'version_arg' => '-v',
            ],
            [
                'name' => 'strace',
                'does' => 'What a stuck process is waiting on',
                'package' => 'strace',
                'key' => true,
            ],
            [
                'name' => 'tcpdump',
                'does' => 'Packet capture',
                'package' => 'tcpdump',
                'key' => true,
            ],
            ['name' => 'nmap', 'does' => 'Port scanning', 'package' => 'nmap'],
            [
                'name' => 'nc',
                'does' => 'Raw TCP connections',
                'package' => 'nmap-ncat',
            ],
            [
                'name' => 'dig',
                'does' => 'DNS diagnostics',
                'package' => 'bind-utils',
                'key' => true,
            ],
            [
                'name' => 'host',
                'does' => 'Quick DNS lookups',
                'package' => 'bind-utils',
            ],
            [
                'name' => 'whois',
                'does' => 'Domain registration detail',
                'package' => 'whois',
            ],
            [
                'name' => 'jq',
                'does' => 'Parse JSON in scripts',
                'package' => 'jq',
                'key' => true,
            ],
            [
                'name' => 'tree',
                'does' => 'Directory structure at a glance',
                'package' => 'tree',
            ],
            [
                'name' => 'ncdu',
                'does' => 'Interactive disk usage explorer',
                'package' => 'ncdu',
                'key' => true,
            ],
            [
                'name' => 'smartctl',
                'does' => 'SMART disk health and failure prediction',
                'package' => 'smartmontools',
                'key' => true,
            ],
        ]),

        $g('media', [
            [
                'name' => 'ffmpeg',
                'does' => 'Transcode audio and video',
                'package' => 'ffmpeg',
            ],
            [
                'name' => 'ffprobe',
                'does' => 'Inspect media files',
                'package' => 'ffmpeg',
            ],
            [
                'name' => 'convert',
                'does' => 'Resize and convert images',
                'package' => 'ImageMagick',
            ],
            [
                'name' => 'magick',
                'does' => 'ImageMagick 7 entry point',
                'package' => 'ImageMagick',
            ],
            [
                'name' => 'identify',
                'does' => 'Image dimensions and format',
                'package' => 'ImageMagick',
            ],
            [
                'name' => 'exiftool',
                'does' => 'Read and strip image metadata',
                'package' => 'perl-Image-ExifTool',
            ],
        ]),

        $g('archive', [
            [
                'name' => 'zip',
                'does' => 'Create zip archives',
                'package' => 'zip',
            ],
            [
                'name' => 'unzip',
                'does' => 'Extract zip archives',
                'package' => 'unzip',
            ],
            ['name' => '7z', 'does' => '7-zip archives', 'package' => 'p7zip'],
            [
                'name' => 'xz',
                'does' => 'High-ratio compression',
                'package' => 'xz',
            ],
            [
                'name' => 'bzip2',
                'does' => 'bzip2 compression',
                'package' => 'bzip2',
            ],
            [
                'name' => 'zstd',
                'does' => 'Fast modern compression',
                'package' => 'zstd',
            ],
        ]),

        $g('extras', [
            [
                'name' => 'redis-cli',
                'does' => 'Query Redis directly',
                'package' => 'redis',
                'key' => true,
            ],
            [
                'name' => 'mongosh',
                'does' => 'Query MongoDB directly',
                'key' => true,
            ],
            ['name' => 'docker', 'does' => 'Container management'],
            ['name' => 'podman', 'does' => 'Rootless containers'],
            [
                'name' => 'tmux',
                'does' => 'Keep a session alive across disconnects',
                'package' => 'tmux',
            ],
            [
                'name' => 'screen',
                'does' => 'Detachable terminal sessions',
                'package' => 'screen',
            ],
            ['name' => 'supervisord', 'does' => 'Keep a process running'],
            [
                'name' => 'fail2ban-client',
                'does' => 'Ban hosts after failed logins',
                'package' => 'fail2ban',
            ],
            [
                'name' => 'clamdscan',
                'does' => 'Virus scanning',
                'package' => 'clamav',
            ],
            [
                'name' => 'rclone',
                'does' => 'Sync to object storage and cloud drives',
                'package' => 'rclone',
                'key' => true,
            ],
        ]),
    );
}

/**
 * Where to read about a program.
 *
 * man7.org is the default because it is the manual page, it is stable, and it
 * exists for almost everything in the core and sysadmin groups. The exceptions
 * are the tools whose real documentation is not a man page - nobody learns jq
 * or composer from man(1) - and pointing at a thin man page for those would be
 * technically correct and useless.
 */
function capabilityHelpUrl(string $name): string
{
    static $specific = [
        'git' => 'https://git-scm.com/docs/git',
        'jq' => 'https://jqlang.github.io/jq/manual/',
        'php' => 'https://www.php.net/manual/en/features.commandline.php',
        'composer' => 'https://getcomposer.org/doc/03-cli.md',
        'npm' => 'https://docs.npmjs.com/cli/commands',
        'node' => 'https://nodejs.org/api/cli.html',
        'wp' => 'https://developer.wordpress.org/cli/commands/',
        'python3' => 'https://docs.python.org/3/using/cmdline.html',
        'mysql' => 'https://mariadb.com/kb/en/mysql-command-line-client/',
        'mariadb' => 'https://mariadb.com/kb/en/mariadb-command-line-client/',
        'mysqldump' => 'https://mariadb.com/kb/en/mariadb-dump/',
        'sqlite3' => 'https://sqlite.org/cli.html',
        'redis-cli' => 'https://redis.io/docs/latest/develop/tools/cli/',
        'mongosh' => 'https://www.mongodb.com/docs/mongodb-shell/',
        'docker' => 'https://docs.docker.com/reference/cli/docker/',
        'podman' => 'https://docs.podman.io/en/latest/Commands.html',
        'rclone' => 'https://rclone.org/commands/',
        'ffmpeg' => 'https://ffmpeg.org/ffmpeg.html',
        'ffprobe' => 'https://ffmpeg.org/ffprobe.html',
        'convert' => 'https://imagemagick.org/script/convert.php',
        'magick' => 'https://imagemagick.org/script/magick.php',
        'identify' => 'https://imagemagick.org/script/identify.php',
        'exiftool' => 'https://exiftool.org/exiftool_pod.html',
        'openssl' => 'https://docs.openssl.org/master/man1/openssl/',
        'systemctl' =>
            'https://www.freedesktop.org/software/systemd/man/systemctl.html',
        'journalctl' =>
            'https://www.freedesktop.org/software/systemd/man/journalctl.html',
        'smartctl' =>
            'https://www.smartmontools.org/browser/trunk/smartmontools/smartctl.8.in',
        'ncdu' => 'https://dev.yorhel.nl/ncdu/man',
        'htop' => 'https://man7.org/linux/man-pages/man1/htop.1.html',
        'supervisord' => 'http://supervisord.org/running.html',
        'fail2ban-client' => 'https://www.fail2ban.org/wiki/index.php/Commands',
        'clamdscan' => 'https://docs.clamav.net/manual/Usage/Scanning.html',
        '7z' => 'https://linux.die.net/man/1/7z',
        'zstd' =>
            'https://github.com/facebook/zstd/blob/dev/programs/zstd.1.md',
        'nc' => 'https://man7.org/linux/man-pages/man1/ncat.1.html',
        'wp-cli' => 'https://developer.wordpress.org/cli/commands/',
    ];

    if (isset($specific[$name])) {
        return $specific[$name];
    }

    // Section 8 for the ones that live in sbin; section 1 for everything else.
    static $sectionEight = [
        'tcpdump',
        'traceroute',
        'iotop',
        'dmesg',
        'lsblk',
        'findmnt',
    ];

    return sprintf(
        'https://man7.org/linux/man-pages/man%d/%s.%d.html',
        in_array($name, $sectionEight, true) ? 8 : 1,
        rawurlencode($name),
        in_array($name, $sectionEight, true) ? 8 : 1,
    );
}

/** Human names for the groups, in the order worth reading them. */
function capabilityGroups(): array
{
    return [
        'core' => 'Core Linux — expected everywhere',
        'sysadmin' => 'System and network tools',
        'dev' => 'Development and hosting',
        'diagnostics' => 'Diagnostics — the ones worth asking for',
        'media' => 'Media and images',
        'archive' => 'Compression and archives',
        'extras' => 'Servers and applications',
    ];
}

/**
 * The catalogue plus anything the config adds.
 *
 * A custom entry is a name and a description of what it buys you. Anything
 * whose name would not survive being handed to a shell is dropped here rather
 * than at the point of use, so there is one place to be sure about it.
 */
function capabilityList(array $config): array
{
    $rows = capabilityCatalogue();

    foreach ((array) ($config['capabilities']['extra'] ?? []) as $entry) {
        if (is_string($entry)) {
            $entry = ['name' => $entry];
        }
        if (!is_array($entry) || !isset($entry['name'])) {
            continue;
        }
        $rows[] = [
            'name' => (string) $entry['name'],
            'does' => (string) ($entry['does'] ?? 'Added to the watch list'),
            'group' => (string) ($entry['group'] ?? 'custom'),
            'package' => $entry['package'] ?? null,
            'version_arg' => $entry['version_arg'] ?? null,
            'key' => (bool) ($entry['key'] ?? false),
        ];
    }

    $seen = [];
    $out = [];
    foreach ($rows as $row) {
        $name = (string) $row['name'];
        if (!preg_match(PROGRAM_NAME_PATTERN, $name) || isset($seen[$name])) {
            continue;
        }
        $seen[$name] = true;
        $out[] = $row + [
            'package' => null,
            'version_arg' => null,
            'key' => false,
            'help' => capabilityHelpUrl($name),
        ];
    }

    return $out;
}

/* ------------------------------------------------------------------ *
 * Probing
 * ------------------------------------------------------------------ */

/**
 * Where each program is, from the point of view of whoever is running this.
 *
 * One shell for the whole list rather than one per program. A hundred and
 * fifteen `command -v` calls is a hundred and fifteen process spawns, which on
 * a loaded shared host is several seconds of doing nothing useful; a single sh
 * loop is one spawn and a few milliseconds.
 *
 * `command -v` rather than `which`: it is a shell builtin required by POSIX,
 * it respects the shell's own idea of PATH, and it does not depend on a
 * /usr/bin/which existing - which, inside a caged account, is exactly the sort
 * of thing that might not.
 *
 * @param list<string> $names
 * @return array<string, string> name => path, only for those found
 */
/**
 * Where to look for a program.
 *
 * The inherited PATH first - it is the truth about this process and may contain
 * something the list below does not - then every place a shared host puts
 * binaries. Duplicates are dropped so the string stays readable when it is
 * shown to somebody, and each entry is kept whether or not it exists: a
 * directory that appears later cannot break a PATH by being absent now.
 */
function searchPath(): string
{
    $extra = [
        '/usr/local/sbin',
        '/usr/local/bin',
        '/usr/sbin',
        '/usr/bin',
        '/sbin',
        '/bin',
        '/opt/cpanel/composer/bin',
        '/usr/local/cpanel/3rdparty/bin',
        '/opt/puppetlabs/bin',
        '/usr/local/mysql/bin',
        '/opt/homebrew/bin',
    ];

    $home = getenv('HOME');
    if (is_string($home) && $home !== '') {
        $extra[] = rtrim($home, '/') . '/bin';
        $extra[] = rtrim($home, '/') . '/.local/bin';
    }

    $inherited = (string) (getenv('PATH') ?: '');
    $parts = array_merge(
        $inherited === '' ? [] : explode(':', $inherited),
        $extra,
    );

    $seen = [];
    foreach ($parts as $part) {
        $part = rtrim(trim($part), '/');
        // An empty element in a PATH means the current directory, which is
        // never what is wanted and is a hazard in its own right.
        if ($part === '' || isset($seen[$part])) {
            continue;
        }
        $seen[$part] = true;
    }

    return implode(':', array_keys($seen));
}

function probePaths(array $names, ?string $asUser = null): array
{
    $safe = array_values(
        array_filter(
            $names,
            fn(string $n): bool => (bool) preg_match(PROGRAM_NAME_PATTERN, $n),
        ),
    );
    if ($safe === []) {
        return [];
    }

    /*
     * An explicit PATH, not whatever the web server happened to inherit.
     *
     * This is the difference between "absent" and "absent from the four
     * directories Apache starts with". A login shell reads /etc/profile and
     * picks up /usr/local/bin, /opt/..., the ea-php directories and the
     * account's own bin; a PHP request inherits none of that, so a program the
     * user runs by name every day was being reported as not installed. Adding
     * the standard locations to whatever PATH is already set makes the answer
     * match what the terminal would say, and the searched PATH is reported
     * alongside the result so the claim can be checked rather than believed.
     */
    $script =
        'PATH=' .
        escapeshellarg(searchPath()) .
        '; export PATH; ' .
        'for p in ' .
        implode(' ', $safe) .
        '; do ' .
        'printf "%s\t%s\n" "$p" "$(command -v "$p" 2>/dev/null || true)"; done';

    if ($asUser !== null) {
        /*
         * Asking the question from inside the cage.
         *
         * cagefs_enter_user runs a command in the account's own namespace, so
         * this is the only way to learn what that account can actually see -
         * as opposed to what root can see, which is the wrong answer to the
         * question being asked.
         */
        if (!preg_match('/^[A-Za-z0-9._-]{1,32}$/', $asUser)) {
            return [];
        }
        $command =
            '/usr/sbin/cagefs_enter_user ' .
            escapeshellarg($asUser) .
            ' /bin/sh -c ' .
            escapeshellarg($script) .
            ' 2>/dev/null';
    } else {
        $command = '/bin/sh -c ' . escapeshellarg($script) . ' 2>/dev/null';
    }

    $out = shellRead($command);
    if ($out === null) {
        return [];
    }

    $found = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
        $parts = explode("\t", $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        [$name, $path] = $parts;
        $path = trim($path);
        if ($path !== '') {
            $found[$name] = $path;
        }
    }

    return $found;
}

/**
 * Looks for a program that is not on PATH.
 *
 * Routine on cPanel rather than exotic: the system php is a stub and the real
 * ones live in /opt/cpanel/ea-php85/root/usr/bin, so "php is not installed" is
 * both what PATH says and completely wrong. Reported as its own state so the
 * answer is "it is here, under another path" rather than "it is missing".
 *
 * Bounded hard. This runs `find` over /usr and /opt, which on a shared host is
 * a lot of inodes, so it is only ever used for the handful of programs that
 * were not found the cheap way.
 *
 * @param list<string> $names
 * @return array<string, string> name => first path found
 */
function probeAlternatePaths(array $names, float $budgetSeconds = 8.0): array
{
    $safe = array_values(
        array_filter(
            $names,
            fn(string $n): bool => (bool) preg_match(PROGRAM_NAME_PATTERN, $n),
        ),
    );
    if ($safe === []) {
        return [];
    }

    $roots = array_values(array_filter(CAPABILITY_SEARCH_ROOTS, 'is_dir'));
    if ($roots === []) {
        return [];
    }

    $expressions = [];
    foreach ($safe as $name) {
        $expressions[] = '-name ' . escapeshellarg($name);
    }

    $command =
        'find ' .
        implode(' ', array_map('escapeshellarg', $roots)) .
        ' -maxdepth 6 -type f -perm -111 \\( ' .
        implode(' -o ', $expressions) .
        ' \\) ' .
        '2>/dev/null';

    // A find over /usr and /opt can run for minutes on a busy host; the whole
    // check is worth a few seconds and no more.
    if (is_executable('/usr/bin/timeout') || is_executable('/bin/timeout')) {
        $command = 'timeout ' . (int) ceil($budgetSeconds) . ' ' . $command;
    }

    $out = shellRead($command);
    if ($out === null) {
        return [];
    }

    $found = [];
    foreach (preg_split('/\R/', trim($out)) ?: [] as $path) {
        $path = trim($path);
        if ($path === '') {
            continue;
        }
        $name = basename($path);
        // The first hit wins: find walks in directory order, and any of them
        // answers "it exists somewhere".
        $found[$name] ??= $path;
    }

    return $found;
}

/**
 * The version string, for programs that are actually there.
 *
 * Deliberately last and deliberately bounded. Asking a program its version
 * means running it, and some of them are slow, some read standard input if
 * given the wrong flag, and a few are interactive. Every one gets a timeout, a
 * closed stdin, and a flag from the catalogue where the usual one is wrong.
 *
 * @param array<string, string> $paths name => path
 * @return array<string, string> name => version line
 */
function probeVersions(array $paths, array $catalogue, int $limit = 140): array
{
    $args = [];
    $keys = [];
    foreach ($catalogue as $row) {
        $args[$row['name']] = $row['version_arg'] ?? '--version';
        $keys[$row['name']] = (bool) ($row['key'] ?? false);
    }

    /*
     * The tools worth knowing about go first.
     *
     * The cap used to be applied in catalogue order, which spends it on cat, cp
     * and mv - programs whose version nobody has ever wanted - and runs out
     * before reaching git, php or curl. If a budget has to be spent somewhere,
     * it should be spent on the answers somebody asked for.
     */
    $order = array_keys($paths);
    usort(
        $order,
        fn(string $a, string $b): int => (int) ($keys[$b] ?? false) <=>
            (int) ($keys[$a] ?? false),
    );

    $hasTimeout =
        is_executable('/usr/bin/timeout') || is_executable('/bin/timeout');
    $versions = [];
    $done = 0;

    foreach ($order as $name) {
        if ($done >= $limit) {
            break;
        }

        $path = $paths[$name];
        if (!preg_match(PROGRAM_NAME_PATTERN, $name) || !is_executable($path)) {
            continue;
        }

        $flag = $args[$name] ?? '--version';
        if (!preg_match('/^-{1,2}[A-Za-z-]{1,16}$/', $flag)) {
            continue;
        }

        // stdin closed so nothing can sit waiting for input it will never get.
        $command = escapeshellarg($path) . ' ' . $flag . ' </dev/null 2>&1';
        if ($hasTimeout) {
            $command = 'timeout 3 ' . $command;
        }

        $out = shellRead($command);
        $done++;
        if ($out === null) {
            continue;
        }

        $version = readVersionLine($out);
        if ($version !== null) {
            $versions[$name] = $version;
        }
    }

    return $versions;
}

/**
 * Pulls a version out of a program's output, or gives up.
 *
 * Necessary because plenty of programs do not support the flag and say so, and
 * the first version of this took the first line whatever it was - so `cat` was
 * reported as having version "/bin/cat: illegal option -- -". That is worse
 * than a blank: a blank says "not known", and an error message dressed as a
 * version says something false with complete confidence.
 *
 * Several lines are considered, not just the first, because a program can be
 * perfectly healthy and still have something else to say first. composer opens
 * with a PHP deprecation notice; the version is on the line after it.
 *
 * Two tests per line, and both are applied to the *shortened* string that would
 * actually be stored. Checking the full line and then truncating was its own
 * bug: a deprecation notice ending in "on line 42" passed the digit test on the
 * strength of a line number that the truncation then cut off.
 */
function readVersionLine(string $output): ?string
{
    $lines = preg_split('/\R/', trim($output)) ?: [];

    foreach (array_slice($lines, 0, 6) as $line) {
        $candidate = mb_substr(trim($line), 0, 120);
        if ($candidate === '' || !looksLikeVersion($candidate)) {
            continue;
        }
        return $candidate;
    }

    return null;
}

/** Whether one line could plausibly be a version rather than a complaint. */
function looksLikeVersion(string $line): bool
{
    $lower = strtolower($line);

    foreach (
        [
            'illegal option',
            'unrecognized option',
            'unrecognised option',
            'invalid option',
            'unknown option',
            'usage:',
            'no such file',
            'command not found',
            'permission denied',
            'try \'',
            'try "',
            // Programs that work fine and clear their throat first.
            'deprecated',
            'warning:',
            'notice:',
            'fatal error',
            'stack trace',
        ]
        as $complaint
    ) {
        if (str_contains($lower, $complaint)) {
            return false;
        }
    }

    // A version without a number in it is not a version.
    return (bool) preg_match('/\d/', $line);
}

/**
 * The number out of a version line, for somewhere there is no room for the rest.
 *
 * "GNU bash, version 3.2.57(1)-release" is the honest answer and far too long
 * for a column; "3.2.57" is what somebody is actually reading it for. The full
 * line is kept and shown alongside, so nothing is lost by shortening it.
 */
function shortVersion(string $line): string
{
    if (preg_match('/(\d+(?:\.\d+){1,3})/', $line, $m)) {
        return $m[1];
    }

    return preg_match('/(\d[\w.\-]*)/', $line, $m) ? $m[1] : '';
}

/**
 * Whether CageFS is installed, and whether this account is inside it.
 *
 * The question that decides how every other answer should be read. On a host
 * with no CageFS, "the account cannot see it" means a permissions or PATH
 * problem. On a host with CageFS and the account enabled, it means the program
 * is not in the skeleton - a completely different fix.
 */
function cagefsStatus(?string $user = null, ?string $binary = null): array
{
    // The path is injectable so the wording can be tested without a real
    // CloudLinux box - the strings here are read by somebody diagnosing a
    // problem, and getting them wrong is how a message ends up describing the
    // wrong account.
    $ctl = null;
    $candidates =
        $binary !== null
            ? [$binary]
            : ['/usr/sbin/cagefsctl', '/sbin/cagefsctl', '/usr/bin/cagefsctl'];

    foreach ($candidates as $candidate) {
        if (is_executable($candidate)) {
            $ctl = $candidate;
            break;
        }
    }

    if ($ctl === null) {
        return [
            'installed' => false,
            'user' => $user,
            'enabled' => null,
            'raw' => null,
            'note' =>
                'CageFS is not installed on this host, so every account sees the same ' .
                'filesystem and "installed" means the same thing everywhere.',
        ];
    }

    if ($user === null || !preg_match('/^[A-Za-z0-9._-]{1,32}$/', $user)) {
        return [
            'installed' => true,
            'user' => $user,
            'enabled' => null,
            'raw' => null,
            'note' =>
                'CageFS is installed. Without an account name its status cannot be checked.',
        ];
    }

    $raw = shellRead(
        $ctl . ' --user-status ' . escapeshellarg($user) . ' 2>&1',
    );
    if ($raw === null) {
        return [
            'installed' => true,
            'user' => $user,
            'enabled' => null,
            'raw' => null,
            'note' =>
                'CageFS is installed but cagefsctl could not be run - this needs root.',
        ];
    }

    $text = trim($raw);
    $enabled = null;
    if (
        preg_match('/\bEnabled\b/i', $text) &&
        !preg_match('/\bNot enabled|Disabled\b/i', $text)
    ) {
        $enabled = true;
    } elseif (preg_match('/\bDisabled|Not enabled\b/i', $text)) {
        $enabled = false;
    }

    return [
        'installed' => true,
        'user' => $user,
        'enabled' => $enabled,
        'raw' => mb_substr($text, 0, 400),
        /*
         * The account is named, never called "this account".
         *
         * This line is printed by a collector running as root on behalf of
         * somebody else, so "this account" reads as whoever is at the terminal
         * - which is exactly the account it is not talking about.
         */
        'note' =>
            $enabled === true
                ? sprintf(
                    '%s is inside CageFS, so that account sees a curated filesystem rather than ' .
                        'the host\'s. A program missing there may be installed here and simply not ' .
                        'exposed.',
                    $user,
                )
                : ($enabled === false
                    ? sprintf(
                        'CageFS is installed but %s is not caged, so that account sees the host ' .
                            'filesystem directly.',
                        $user,
                    )
                    : sprintf(
                        'CageFS is installed; the status of %s could not be read.',
                        $user,
                    )),
    ];
}

/* ------------------------------------------------------------------ *
 * Working out what it all means
 * ------------------------------------------------------------------ */

/**
 * Which package a binary came from, where the host can say.
 *
 * One rpm call for every path rather than one each: rpm -qf accepts a list and
 * answers in order, and on a box with several hundred binaries the difference
 * is a second against a minute.
 *
 * Falls back to reading the path, which is a hint rather than an answer and is
 * labelled as one. /usr/bin is where a package manager puts things and
 * /usr/local is where people put things by hand, but neither is a rule.
 *
 * @param array<string, string> $paths name => path
 * @return array<string, string> name => source
 */
function probeSources(array $paths): array
{
    $out = [];

    $rpm = null;
    foreach (['/usr/bin/rpm', '/bin/rpm'] as $candidate) {
        if (is_executable($candidate)) {
            $rpm = $candidate;
            break;
        }
    }

    if ($rpm !== null && $paths !== []) {
        $names = array_keys($paths);
        $args = implode(' ', array_map('escapeshellarg', array_values($paths)));
        $raw = shellRead(
            $rpm . ' -qf --queryformat "%{NAME}\n" ' . $args . ' 2>&1',
        );

        if ($raw !== null) {
            $lines = preg_split('/\R/', trim($raw)) ?: [];
            foreach ($names as $i => $name) {
                $line = trim($lines[$i] ?? '');
                // "file ... is not owned by any package" is rpm telling us it
                // was installed by hand, which is a real answer.
                $out[$name] =
                    $line === '' || str_contains($line, 'not owned')
                        ? 'custom'
                        : $line;
            }
            return $out;
        }
    }

    foreach ($paths as $name => $path) {
        $out[$name] =
            str_starts_with($path, '/usr/local/') ||
            str_starts_with($path, '/opt/')
                ? 'custom?'
                : 'unknown';
    }

    return $out;
}

/**
 * The state of one program, and why.
 *
 * The reason matters as much as the state. "Not available" sends somebody to
 * install a package; "installed on the host but not exposed through CageFS"
 * sends them to cagefsctl, and only one of those will work.
 */
function capabilityState(array $row): array
{
    $inAccount = ($row['account_path'] ?? '') !== '';
    $executable = (bool) ($row['executable'] ?? false);
    $alternate = ($row['alternate_path'] ?? '') !== '';
    $onHost = (bool) ($row['on_host'] ?? false);
    $caged = (bool) ($row['caged'] ?? false);

    if ($inAccount && $executable) {
        return ['state' => 'available', 'reason' => null];
    }

    if ($inAccount && !$executable) {
        return [
            'state' => 'unusable',
            'reason' =>
                'Found at ' .
                $row['account_path'] .
                ' but this account cannot execute it.',
        ];
    }

    if ($alternate) {
        return [
            'state' => 'alternate',
            'reason' =>
                'Not on PATH, but present at ' .
                $row['alternate_path'] .
                '. Call it by its full path, or ask for it to be linked onto PATH.',
        ];
    }

    if ($onHost) {
        return [
            'state' => 'host_only',
            'reason' => $caged
                ? 'Installed on the host and not exposed through CageFS. This needs a CageFS ' .
                    'configuration change, not an installation.'
                : 'Present on the host but not reachable from this account\'s PATH.',
        ];
    }

    return [
        'state' => 'missing',
        'reason' =>
            'Not found on the host either. This one needs installing' .
            ($row['package'] ?? null ? ' (' . $row['package'] . ').' : '.'),
    ];
}

/**
 * Probes everything this side can see and returns rows ready to store.
 *
 * `$asUser` switches the question from "what can I see" to "what can that
 * account see", which only root can ask and only through CageFS.
 *
 * @return array{rows: list<array>, seconds: float, cagefs: array}
 */
function probeCapabilities(
    array $config,
    ?string $asUser = null,
    bool $withVersions = true,
    bool $withAlternates = true,
): array {
    $started = microtime(true);
    $catalogue = capabilityList($config);
    $names = array_column($catalogue, 'name');

    $cagefs = cagefsStatus($asUser);
    $paths = probePaths($names, $asUser);

    $missing = array_values(array_diff($names, array_keys($paths)));
    $alternates =
        $withAlternates && $asUser === null
            ? probeAlternatePaths($missing)
            : [];

    $versions = $withVersions ? probeVersions($paths, $catalogue) : [];
    $sources = probeSources($paths);

    $rows = [];
    foreach ($catalogue as $entry) {
        $name = $entry['name'];
        $path = $paths[$name] ?? '';

        $rows[] = [
            'name' => $name,
            'group_name' => $entry['group'],
            'does' => $entry['does'],
            'package' => $entry['package'],
            'key' => (bool) $entry['key'],
            'path' => $path,
            /*
             * is_executable() rather than trusting the -x that found it.
             *
             * command -v answers from PATH and the shell's own idea of what is
             * runnable, which is not always this process's idea - a binary can
             * be listed and still refuse to run for the account asking.
             */
            'executable' => $path !== '' && @is_executable($path),
            'alternate_path' => $alternates[$name] ?? '',
            'version' => $versions[$name] ?? '',
            /*
             * A fingerprint, but only where there is no version to compare.
             *
             * Hashing every binary on every probe would be pointless work for
             * the ones that answer --version properly - a version string is a
             * better comparison than a hash in every way. This is for the rest,
             * where "the file is different from Tuesday's" is the only change
             * detection available.
             */
            'fingerprint' =>
                ($versions[$name] ?? '') === '' && $path !== ''
                    ? fileFingerprint($path) ?? ''
                    : '',
            'source' => $sources[$name] ?? '',
        ];
    }

    return [
        'rows' => $rows,
        'cagefs' => $cagefs,
        'seconds' => round(microtime(true) - $started, 2),
    ];
}

/**
 * Combines what the account sees with what the host sees.
 *
 * The account's view decides whether something is usable *here*, which is the
 * question. The host's view decides whether the fix is an installation or a
 * CageFS entry, which is what to do about it. Neither alone is enough.
 *
 * @param list<array> $account rows from probeCapabilities() run as the account
 * @param list<array> $host    rows from the host collector, or []
 */
function mergeCapabilities(
    array $account,
    array $host,
    array $cagefs = [],
): array {
    $byName = [];
    foreach ($host as $row) {
        $byName[(string) $row['name']] = $row;
    }

    $caged = (bool) ($cagefs['enabled'] ?? false);
    $out = [];

    foreach ($account as $row) {
        $name = (string) $row['name'];
        $hostRow = $byName[$name] ?? null;

        $merged = [
            'name' => $name,
            'group_name' => (string) $row['group_name'],
            'does' => (string) $row['does'],
            'package' => $row['package'],
            'key' => (bool) $row['key'],
            'account_path' => (string) $row['path'],
            'executable' => (bool) $row['executable'],
            'alternate_path' => (string) ($row['alternate_path'] ?? ''),
            'version' => (string) ($row['version'] ?? ''),
            // The number on its own, for the column; the full line travels with
            // it so nothing is lost to the shortening.
            'version_short' => shortVersion((string) ($row['version'] ?? '')),
            'source' => (string) ($row['source'] ?? ''),
            /*
             * Absent host data is not evidence of absence.
             *
             * Without the host collector the honest answer to "is it on the
             * host" is that nobody asked. Treating unknown as no would report
             * every missing program as needing an installation, when half of
             * them are installed and merely not exposed.
             */
            'host_known' => $hostRow !== null,
            'on_host' => $hostRow !== null && (string) $hostRow['path'] !== '',
            'host_path' => $hostRow === null ? '' : (string) $hostRow['path'],
            'host_version' =>
                $hostRow === null ? '' : (string) ($hostRow['version'] ?? ''),
            'host_version_short' =>
                $hostRow === null
                    ? ''
                    : shortVersion((string) ($hostRow['version'] ?? '')),
            'caged' => $caged,
        ];

        // The host knows the package where the account cannot see the binary.
        if ($merged['source'] === '' && $hostRow !== null) {
            $merged['source'] = (string) ($hostRow['source'] ?? '');
        }

        $merged['help'] = (string) ($row['help'] ?? '');

        $verdict = capabilityState($merged);
        if (!$merged['host_known'] && $verdict['state'] === 'missing') {
            $verdict = [
                'state' => 'unknown_host',
                'reason' =>
                    'Not reachable from this account. Whether the host has it is ' .
                    'unknown - that needs the host collector.',
            ];
        }

        $row = $merged + $verdict;

        /*
         * The commands travel with the row that needs them.
         *
         * Only for host_only, because that is the only state where exposing
         * something is the fix - and building them for all hundred and fifteen
         * would put a wall of shell in a payload the dashboard polls.
         */
        if ($row['state'] === 'host_only' && $row['host_path'] !== '') {
            try {
                $row['expose'] = cagefsExposeSnippet($name, $row['host_path']);
            } catch (Throwable) {
                $row['expose'] = null;
            }
        }

        $out[] = $row;
    }

    return $out;
}

/** A one-line summary of how much of the catalogue is actually usable. */
function capabilitySummary(array $rows): array
{
    $counts = [
        'available' => 0,
        'alternate' => 0,
        'host_only' => 0,
        'unusable' => 0,
        'missing' => 0,
        'unknown_host' => 0,
    ];

    foreach ($rows as $row) {
        $state = (string) $row['state'];
        $counts[$state] = ($counts[$state] ?? 0) + 1;
    }

    $key = array_values(
        array_filter($rows, fn(array $r): bool => (bool) $r['key']),
    );

    return [
        'total' => count($rows),
        'counts' => $counts,
        'key_total' => count($key),
        'key_available' => count(
            array_filter(
                $key,
                fn(array $r): bool => $r['state'] === 'available',
            ),
        ),
    ];
}

/* ------------------------------------------------------------------ *
 * Storing and reading back
 * ------------------------------------------------------------------ */

/** Writes an account-side probe. */
function storeCapabilities(PDO $pdo, array $probe): int
{
    $pdo->beginTransaction();
    try {
        $at = time();

        /*
         * Compared before the old rows are thrown away.
         *
         * The table holds only the current answer, so the previous one has to
         * be read here or it is gone. Everything about the change history
         * depends on doing this in the right order inside the same transaction.
         */
        $before = [];
        foreach (
            $pdo
                ->query('SELECT * FROM capabilities')
                ->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $before[(string) $row['name']] = $row;
        }

        $changes = detectCapabilityChanges(
            $before,
            $probe['rows'],
            'account',
            $before !== [],
        );
        $log = $pdo->prepare(
            'INSERT INTO capability_changes (at, name, group_name, side,
                old_state, new_state, old_version, new_version,
                old_fingerprint, new_fingerprint)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        );
        foreach ($changes as $change) {
            $log->execute([
                $at,
                $change['name'],
                $change['group_name'],
                $change['side'],
                $change['old_state'],
                $change['new_state'],
                $change['old_version'],
                $change['new_version'],
                $change['old_fingerprint'] ?? '',
                $change['new_fingerprint'] ?? '',
            ]);
        }

        /*
         * The kernel and friends are recorded on the same pass.
         *
         * Same question - what is installed here - and putting it on its own
         * schedule would mean two things that must agree being measured at
         * different moments.
         */
        recordSoftware($pdo, criticalSoftware(), 'account');

        $pdo->exec('DELETE FROM capabilities');

        $insert = $pdo->prepare(
            'INSERT INTO capabilities (name, group_name, checked_at, path, executable,
                alternate_path, version, source, fingerprint)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        );
        $found = 0;
        foreach ($probe['rows'] as $row) {
            if (($row['path'] ?? '') !== '') {
                $found++;
            }
            /*
             * Optional fields defaulted rather than assumed.
             *
             * A row can legitimately arrive without an alternate path or a
             * source - the cheaper probe skips both - and reaching for a key
             * that is not there fills the error log with warnings about
             * something working exactly as intended.
             */
            $insert->execute([
                $row['name'],
                $row['group_name'],
                $at,
                $row['path'] ?? '',
                $row['executable'] ?? false ? 1 : 0,
                $row['alternate_path'] ?? '',
                $row['version'] ?? '',
                $row['source'] ?? '',
                $row['fingerprint'] ?? '',
            ]);
        }

        $pdo->prepare(
            'INSERT INTO capability_runs (ran_at, seconds, found, total,
                cagefs_installed, cagefs_enabled, cagefs_note)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $at,
            $probe['seconds'],
            $found,
            count($probe['rows']),
            $probe['cagefs']['installed'] ?? false ? 1 : 0,
            ($probe['cagefs']['enabled'] ?? null) === null
                ? null
                : ($probe['cagefs']['enabled']
                    ? 1
                    : 0),
            $probe['cagefs']['note'] ?? null,
        ]);

        $pdo->commit();

        return count($changes);
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * What moved between two probes.
 *
 * Both halves of a program's answer count as a change: whether it can be run at
 * all, and which version answers when it is. A version going from 8.7.1 to
 * 8.9.0 is a package upgrade; a state going from absent to available is
 * somebody exposing it through CageFS. They are different events and both are
 * worth a mark on the chart.
 *
 * `$hadPrevious` is what stops the first ever probe from reporting a hundred
 * and fifteen changes. Nothing changed - this is simply the first time anyone
 * looked, and recording it as an event would put a spike at the beginning of
 * every history that means nothing at all.
 *
 * @param array<string, array> $before keyed by name
 * @param list<array> $after
 * @return list<array>
 */
function detectCapabilityChanges(
    array $before,
    array $after,
    string $side,
    bool $hadPrevious,
): array {
    if (!$hadPrevious) {
        return [];
    }

    $changes = [];

    foreach ($after as $row) {
        $name = (string) $row['name'];
        $old = $before[$name] ?? null;

        $newState = capabilitySideState($row, $side);
        $newVersion = (string) ($row['version'] ?? '');

        if ($old === null) {
            /*
             * A program added to the catalogue since the last probe.
             *
             * Its state did not change - it was never being watched. Recorded
             * only when it is actually present, so adding twenty names to the
             * config does not produce twenty "changes" on the chart.
             */
            if ($newState === 'absent') {
                continue;
            }
            $changes[] = [
                'name' => $name,
                'group_name' => (string) $row['group_name'],
                'side' => $side,
                'old_state' => 'unwatched',
                'new_state' => $newState,
                'old_version' => '',
                'new_version' => $newVersion,
            ];
            continue;
        }

        $oldState = capabilitySideState(
            [
                'path' => (string) ($old['path'] ?? ''),
                'executable' => (bool) ($old['executable'] ?? false),
            ],
            $side,
        );
        $oldVersion = (string) ($old['version'] ?? '');
        $oldMark = (string) ($old['fingerprint'] ?? '');
        $newMark = (string) ($row['fingerprint'] ?? '');

        /*
         * The fingerprint counts as a change, but only where there is no
         * version on either side.
         *
         * A program that gains a readable version between probes has not
         * changed - the probe got luckier - and reporting that as an upgrade
         * would fill the history with events nobody caused.
         */
        $markMoved =
            $oldVersion === '' &&
            $newVersion === '' &&
            $oldMark !== '' &&
            $newMark !== '' &&
            $oldMark !== $newMark;

        if (
            $oldState === $newState &&
            $oldVersion === $newVersion &&
            !$markMoved
        ) {
            continue;
        }

        /*
         * A version that merely became unknown is not news.
         *
         * Version probing is capped and can be skipped entirely, so a run
         * without it would otherwise report every program as having lost its
         * version - a hundred fabricated events from a cheaper check.
         */
        if (
            $oldState === $newState &&
            $newVersion === '' &&
            $oldVersion !== ''
        ) {
            continue;
        }

        $changes[] = [
            'name' => $name,
            'group_name' => (string) $row['group_name'],
            'side' => $side,
            'old_state' => $oldState,
            'new_state' => $newState,
            'old_version' => $oldVersion,
            'new_version' => $newVersion,
            'old_fingerprint' => $oldMark,
            'new_fingerprint' => $newMark,
        ];
    }

    // Something dropped from the catalogue is not a change on the machine.
    return $changes;
}

/**
 * The simple state one side can actually observe.
 *
 * Deliberately not the merged state from capabilityState(): that one needs both
 * sides and is about what to do next, while this is a record of what a single
 * probe saw. Mixing them would make the history depend on data collected later.
 */
function capabilitySideState(array $row, string $side): string
{
    $path = (string) ($row['path'] ?? '');

    if ($side === 'host') {
        return $path === '' ? 'absent' : 'present';
    }

    if ($path === '') {
        return 'absent';
    }

    return $row['executable'] ?? false ? 'available' : 'unusable';
}

/** Writes a host-side probe. */
function storeHostCapabilities(
    PDO $host,
    array $probe,
    array $inCage,
    ?string $user,
): void {
    $host->beginTransaction();
    try {
        $at = time();

        $before = [];
        foreach (
            $host
                ->query('SELECT * FROM host_capabilities')
                ->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $before[(string) $row['name']] = $row;
        }

        $changes = detectCapabilityChanges(
            $before,
            $probe['rows'],
            'host',
            $before !== [],
        );
        $log = $host->prepare(
            'INSERT INTO host_capability_changes (at, name, group_name, side,
                old_state, new_state, old_version, new_version)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        );
        foreach ($changes as $change) {
            $log->execute([
                $at,
                $change['name'],
                $change['group_name'],
                $change['side'],
                $change['old_state'],
                $change['new_state'],
                $change['old_version'],
                $change['new_version'],
            ]);
        }

        $host->exec('DELETE FROM host_capabilities');

        $insert = $host->prepare(
            'INSERT INTO host_capabilities (name, group_name, checked_at, path, executable,
                version, source, account_path, account_seen)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        );

        $onHost = 0;
        $seen = 0;
        foreach ($probe['rows'] as $row) {
            $accountPath = $inCage[$row['name']] ?? '';
            if ($row['path'] !== '') {
                $onHost++;
            }
            if ($accountPath !== '') {
                $seen++;
            }
            $insert->execute([
                $row['name'],
                $row['group_name'],
                $at,
                $row['path'] ?? '',
                $row['executable'] ?? false ? 1 : 0,
                $row['version'] ?? '',
                $row['source'] ?? '',
                $accountPath,
                $accountPath !== '' ? 1 : 0,
            ]);
        }

        $host
            ->prepare(
                'INSERT INTO host_capability_runs (ran_at, seconds, total, on_host, seen_by_account,
                user, cagefs_installed, cagefs_enabled, cagefs_note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            )
            ->execute([
                $at,
                $probe['seconds'],
                count($probe['rows']),
                $onHost,
                $seen,
                $user,
                $probe['cagefs']['installed'] ?? false ? 1 : 0,
                ($probe['cagefs']['enabled'] ?? null) === null
                    ? null
                    : ($probe['cagefs']['enabled']
                        ? 1
                        : 0),
                $probe['cagefs']['note'] ?? null,
            ]);

        $host->commit();
    } catch (Throwable $e) {
        $host->rollBack();
        throw $e;
    }
}

/**
 * The merged view, from whatever has been recorded.
 *
 * Never probes. The dashboard reads this on every poll and probing there would
 * put a shell spawn behind a page refresh.
 */
function capabilityReport(array $config, PDO $pdo, ?PDO $host): array
{
    $catalogue = [];
    foreach (capabilityList($config) as $row) {
        $catalogue[$row['name']] = $row;
    }

    $stored = $pdo
        ->query('SELECT * FROM capabilities')
        ->fetchAll(PDO::FETCH_ASSOC);
    $run = $pdo
        ->query('SELECT * FROM capability_runs ORDER BY ran_at DESC LIMIT 1')
        ->fetch(PDO::FETCH_ASSOC);

    if (!$stored) {
        return [
            'available' => false,
            'reason' => 'Nothing has been checked yet. Press Check now.',
            'rows' => [],
            'groups' => capabilityGroups(),
            'summary' => null,
            'cagefs' => null,
            'host' => null,
        ];
    }

    $account = [];
    foreach ($stored as $row) {
        $entry = $catalogue[(string) $row['name']] ?? null;
        $account[] = [
            'name' => (string) $row['name'],
            'group_name' => (string) $row['group_name'],
            'does' => (string) ($entry['does'] ?? ''),
            'package' => $entry['package'] ?? null,
            'key' => (bool) ($entry['key'] ?? false),
            'help' => (string) ($entry['help'] ?? ''),
            'path' => (string) ($row['path'] ?? ''),
            'executable' => (bool) $row['executable'],
            'alternate_path' => (string) ($row['alternate_path'] ?? ''),
            'version' => (string) ($row['version'] ?? ''),
            'source' => (string) ($row['source'] ?? ''),
        ];
    }

    $hostRows = [];
    $hostRun = null;
    if ($host !== null) {
        try {
            $hostRows = $host
                ->query('SELECT * FROM host_capabilities')
                ->fetchAll(PDO::FETCH_ASSOC);
            $hostRun = $host
                ->query(
                    'SELECT * FROM host_capability_runs ORDER BY ran_at DESC LIMIT 1',
                )
                ->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            // An older host database has no such table, which simply means the
            // host side has never been asked.
            $hostRows = [];
        }
    }

    $cagefs = [
        'installed' =>
            (bool) ($hostRun['cagefs_installed'] ??
                ($run['cagefs_installed'] ?? false)),
        'enabled' => isset($hostRun['cagefs_enabled'])
            ? (bool) $hostRun['cagefs_enabled']
            : (isset($run['cagefs_enabled'])
                ? (bool) $run['cagefs_enabled']
                : null),
        'note' =>
            (string) ($hostRun['cagefs_note'] ?? ($run['cagefs_note'] ?? '')),
        'user' => $hostRun['user'] ?? null,
    ];

    $rows = mergeCapabilities($account, $hostRows, $cagefs);

    return [
        'available' => true,
        'reason' => null,
        'rows' => $rows,
        'groups' => capabilityGroups(),
        'summary' => capabilitySummary($rows),
        'cagefs' => $cagefs,
        'checked_at' => (int) ($run['ran_at'] ?? 0),
        'seconds' => (float) ($run['seconds'] ?? 0),
        'host_checked_at' => $hostRun ? (int) $hostRun['ran_at'] : null,
        /*
         * Where it looked, so "not installed" can be checked rather than
         * believed. A program on a PATH this did not search is present and
         * reported absent, and without saying where it searched there is no way
         * to tell that apart from the program genuinely not being there.
         */
        'searched_path' => searchPath(),
    ];
}

/**
 * A valid CageFS config fragment for exposing one program.
 *
 * The format is INI with a section header, which is not obvious and is not what
 * an earlier version of this file suggested. Appending a bare path produces:
 *
 *   configparser.MissingSectionHeaderError: File contains no section headers.
 *
 * and - worse than simply not working - cagefsctl then refuses to run at all
 * until the file is removed, so a bad suggestion here breaks the tool somebody
 * needs in order to recover from it. Hence writing it out in full rather than
 * describing it, and hence the test that parses the result.
 *
 * One file per program rather than appending to a shared one: appending twice
 * gives two sections with the same name, and removing a program again becomes
 * an edit rather than a delete.
 *
 * @return array{file: string, contents: string, commands: list<string>}
 */
function cagefsExposeSnippet(string $program, string $path): array
{
    if (!preg_match(PROGRAM_NAME_PATTERN, $program)) {
        throw new InvalidArgumentException('not a usable program name');
    }

    $file = '/etc/cagefs/conf.d/monitor-' . $program . '.cfg';

    /*
     * `paths` takes a comma-separated list. Only the binary is listed here:
     * CageFS resolves shared libraries itself, and anything that needs its own
     * data directory - ImageMagick's delegates, terminfo for the curses tools -
     * needs those directories adding too, which is a judgement per program
     * rather than something to guess at.
     */
    /*
     * A plain comment, with no brackets in it.
     *
     * cagefsctl parses these with Python's configparser, which is relaxed about
     * punctuation - but a value full of brackets is exactly the sort of thing
     * that is fine until some other reader is pointed at the file. There is
     * nothing to gain from the parentheses.
     */
    $contents = sprintf(
        "[%s]\ncomment=%s - exposed for the disk monitor\npaths=%s\n",
        $program,
        $program,
        $path,
    );

    /*
     * The heredoc body is returned unindented and must be printed that way.
     *
     * Indenting it for tidiness would write those spaces into the file, and a
     * leading space in an INI file is a continuation line - so the pretty
     * version produces a config that parses differently from the one that was
     * tested.
     */
    return [
        'file' => $file,
        'contents' => $contents,
        'commands' => array_merge(
            [sprintf('cat > %s <<\'EOF\'', $file)],
            explode("\n", rtrim($contents, "\n")),
            ['EOF', 'cagefsctl --force-update'],
        ),
    ];
}

/* ------------------------------------------------------------------ *
 * The history of what changed
 * ------------------------------------------------------------------ */

/**
 * How wide one bar should be, given the range being looked at.
 *
 * Four steps rather than the smooth ladder the charts use, because a bar chart
 * of counts is read as "how many that day" - and a bar covering 43 minutes is
 * a quantity nobody can hold in their head. Minute, hour, day, week are units
 * people already think in.
 */
function changeBucketSeconds(int $span): array
{
    if ($span <= 6 * 3600) {
        return [60, 'minute'];
    }
    if ($span <= 8 * 86400) {
        return [3600, 'hour'];
    }
    if ($span <= 120 * 86400) {
        return [86400, 'day'];
    }

    return [604800, 'week'];
}

/**
 * Where the bucket containing this moment begins.
 *
 * Arithmetic on the epoch aligns to UTC, which is fine for minutes and hours
 * and wrong for days and weeks: on a machine set to New York, dividing by 86400
 * puts the day boundary at eight in the evening, so a bar labelled Tuesday
 * holds most of Monday and the dialog opens with a heading five hours adrift
 * from the rows beneath it.
 *
 * Days and weeks are therefore aligned to local midnight, in the configured
 * timezone, which is the only definition of "day" anybody reading this has.
 */
function changeBucketStart(int $at, int $bucket): int
{
    if ($bucket < 86400) {
        return intdiv($at, $bucket) * $bucket;
    }

    $midnight = (int) mktime(
        0,
        0,
        0,
        (int) date('n', $at),
        (int) date('j', $at),
        (int) date('Y', $at),
    );

    if ($bucket < 604800) {
        return $midnight;
    }

    // Weeks begin on Monday: date('N') is 1 for Monday through 7 for Sunday.
    return $midnight - ((int) date('N', $at) - 1) * 86400;
}

/**
 * Every recorded change in a window, from both sides, bucketed for a chart.
 *
 * Account and host histories live in different databases - neither collector
 * may write to the other's - so they are read separately and interleaved here.
 * A single upgrade can legitimately appear twice, once as the host noticing and
 * once as the account noticing, and those are genuinely two observations rather
 * than a duplicate: on a caged box they can be days apart.
 */
function capabilityChanges(
    array $config,
    PDO $pdo,
    ?PDO $host,
    int $from,
    int $to,
    int $limit = 800,
): array {
    $rows = [];

    $read = static function (?PDO $db, string $table) use (
        $from,
        $to,
        &$rows,
    ): void {
        if ($db === null) {
            return;
        }
        try {
            $stmt = $db->prepare(
                "SELECT * FROM $table WHERE at BETWEEN ? AND ? ORDER BY at DESC",
            );
            $stmt->execute([$from, $to]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rows[] = $row;
            }
        } catch (Throwable) {
            // An older database has no such table, which means no history yet.
        }
    };

    $read($pdo, 'capability_changes');
    $read($host, 'host_capability_changes');

    usort(
        $rows,
        fn(array $a, array $b): int => (int) $b['at'] <=> (int) $a['at'],
    );

    $catalogue = [];
    foreach (capabilityList($config) as $entry) {
        $catalogue[$entry['name']] = $entry;
    }

    [$bucket, $unit] = changeBucketSeconds(max(1, $to - $from));

    $buckets = [];
    $groups = [];

    foreach ($rows as $row) {
        $group = (string) $row['group_name'];
        $groups[$group] = true;

        $key = changeBucketStart((int) $row['at'], $bucket);
        $buckets[$key] ??= ['at' => $key, 'total' => 0, 'counts' => []];
        $buckets[$key]['total']++;
        $buckets[$key]['counts'][$group] =
            ($buckets[$key]['counts'][$group] ?? 0) + 1;
    }

    ksort($buckets);

    $detailed = array_map(static function (array $row) use ($catalogue): array {
        $entry = $catalogue[(string) $row['name']] ?? null;
        return [
            'at' => (int) $row['at'],
            'name' => (string) $row['name'],
            'group_name' => (string) $row['group_name'],
            'side' => (string) $row['side'],
            'does' => (string) ($entry['does'] ?? ''),
            'help' => (string) ($entry['help'] ?? ''),
            'old_state' => (string) ($row['old_state'] ?? ''),
            'new_state' => (string) ($row['new_state'] ?? ''),
            'old_version' => (string) ($row['old_version'] ?? ''),
            'new_version' => (string) ($row['new_version'] ?? ''),
            'old_version_short' => shortVersion(
                (string) ($row['old_version'] ?? ''),
            ),
            'new_version_short' => shortVersion(
                (string) ($row['new_version'] ?? ''),
            ),
        ];
    }, array_slice($rows, 0, $limit));

    return [
        'from' => $from,
        'to' => $to,
        'bucket_seconds' => $bucket,
        'bucket_unit' => $unit,
        'groups' => array_keys($groups),
        'group_names' => capabilityGroups(),
        'buckets' => array_values($buckets),
        'changes' => $detailed,
        'total' => count($rows),
        // Said rather than silently cut: a chart whose bars sum to more than
        // the list beneath it needs to explain itself.
        'truncated' => count($rows) > $limit,
    ];
}
