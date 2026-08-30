<?php
declare(strict_types=1);

function aiToolUptimePackage(): array
{
    return [
        'get_uptime' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' =>
                'when the machine restarted and when each collector was running.',
            'description' =>
                "Two records over a period: when the machine was up, and when each collector was actually writing readings.\n\nUSE THIS whenever a chart has a gap in it, and whenever a cluster of alerts arrives together. A gap where the collector stopped and a gap where the machine was off look identical on every chart here and mean opposite things - one is missing data about a working machine, the other is a machine that was not there.\n\nA run of warnings just after a restart is usually a machine coming back rather than a machine in trouble. The same run with no restart under it is something else entirely.",
            'parameters' => [
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 2160,
                    'required' => false,
                    'default' => 168,
                    'description' => 'How far back to look.',
                ],
            ],
        ],
    ];
}
