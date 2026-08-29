<?php
declare(strict_types=1);

/* Core tool package: metadata stays isolated from the catalogue assembler so
 * it can later move to the signed package runtime without changing its schema. */
function aiToolCurrentStatusPackage(): array
{
    return ['get_current_status' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'The current state of the machine in one call.',
        'description' => "Returns the most recent reading of everything this dashboard measures: disk space used and free, inode use, CPU percentage, memory percentage, load average, uptime, and when each collector last ran.\n\nUSE THIS FIRST for almost any problem. It is one cheap call and it usually tells you which direction to look, which saves three or four more specific calls.\n\nDO NOT use this to look at history - it returns a single moment, the most recent one. For 'when did this start' or 'is this getting worse', use get_measurement_history instead.\n\nDO NOT call this more than once in a conversation unless several minutes have passed or the user says something changed. The readings are collected every few minutes and calling it again immediately returns the same numbers.",
        'parameters' => [],
    ]];
}
