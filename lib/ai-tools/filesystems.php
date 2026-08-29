<?php
declare(strict_types=1);

function aiToolFilesystemsPackage(): array
{
    return ['get_filesystems' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'every mounted filesystem and how full it is.',
        'description' => "Every filesystem on the machine, with size, used, free and inode use.\n\nUSE THIS when the disk figures do not add up, which happens more often than it sounds. The account's own reading is for the filesystem its home directory is on; a machine with a separate /var or /tmp can have one of those full while the account's own gauge is comfortable, and the symptom is writes failing for no visible reason.\n\nThis comes from the host collector, so it is as fresh as that collector's last run and is absent entirely where there is no host collector. It says which.",
        'parameters' => [],
    ]];
}
