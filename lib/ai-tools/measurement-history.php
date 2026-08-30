<?php
declare(strict_types=1);

function aiToolMeasurementHistoryPackage(): array
{
    return [
        'get_measurement_history' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'How one measurement has moved over time.',
            'description' =>
                "Returns a series of readings for ONE measurement over a period, summarised as a handful of points with the minimum, maximum and average of each. Use it to answer 'when did this start', 'is it getting worse', or 'was it like this last week'.\n\nDO NOT use this to find out the current value - get_current_status is one call and gives you all of them.\n\nPick the shortest period that answers the question. Asking for 30 days when the user is describing something that started this morning produces a flat line and tells you nothing.",
            'parameters' => [
                'measurement' => [
                    'type' => 'string',
                    'enum' => [
                        'disk_used_percent',
                        'inodes_used_percent',
                        'cpu_percent',
                        'memory_percent',
                        'load_per_core',
                        'disk_free_bytes',
                    ],
                    'required' => true,
                    'description' =>
                        "Which measurement. disk_used_percent and inodes_used_percent are percentages of the filesystem the account writes to. cpu_percent is of this ACCOUNT'S allowance, not of the machine - inside CageFS it can sit at 100 while the machine is idle, which is what being throttled looks like and is not the same as the server being busy. load_per_core is the load average divided by the core count and expressed as a percentage, so 100 means one runnable process per core.",
                ],
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 8760,
                    'required' => true,
                    'description' =>
                        'How far back to look, in hours. 24 for "today", 168 for a week, 720 for a month.',
                ],
            ],
        ],
    ];
}
