<?php
declare(strict_types=1);

function aiToolTerminalCapabilitiesPackage(): array
{
    return ['what_the_terminal_can_run' => [
        'group' => 'terminal', 'where' => 'server', 'permission' => false,
        'summary' => 'list the commands the operator could actually run.',
        'description' => "Lists which common diagnostic programs exist on this machine and are on the PATH the terminal uses, so a command you suggest can actually run.\n\nUSE THIS BEFORE `recommend_command`, the first time in a conversation that you are about to suggest something. Shared hosts are missing things you would assume are always present - `ss`, `lsof`, `dig` and `strace` are all commonly absent, and CageFS hides most of /usr/sbin from the account entirely. Suggesting a command that is not there wastes somebody's time and makes the next suggestion less trusted.\n\nIt also says whether the terminal is switched on at all. If it is not, do not suggest commands - say that the terminal is off and where to turn it on.\n\nThis checks for the programs on a fixed list. A program not on the list is not thereby absent; it was simply not looked for.",
        'parameters' => [],
    ]];
}
