<?php
declare(strict_types=1);

function aiToolBackupsPackage(): array
{
    return ['get_backups' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'what backups exist and how old each one is.',
        'description' => "Backups found on this machine, with when each was made and how large.\n\nUSE THIS before suggesting anything destructive, and whenever somebody asks whether it is safe to delete something. \"There is a backup from four hours ago\" and \"the newest backup is from March\" lead to completely different advice, and you cannot tell which without asking.\n\nALSO USE IT when the disk is filling: backups are frequently most of what is on a disk, and an old one nobody meant to keep is the easiest space there is to recover.\n\nAbsence of backups here is not proof there are none - this finds the ones in the usual places. Say that rather than telling somebody they have no backups.",
        'parameters' => [],
    ]];
}
