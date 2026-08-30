<?php
declare(strict_types=1);

function aiToolRecentAlertsPackage(): array
{
    return [
        'get_recent_alerts' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'Alerts this dashboard has raised.',
            'description' =>
                "Returns alerts raised by the monitor itself, newest first, with when, how severe, and what they said.\n\nUSE THIS EARLY. The monitor may already have noticed and named the problem the user is describing, in which case the alert's own text is a better starting point than anything you would work out from raw readings.\n\nThese are the monitor's own conclusions, NOT log lines from the server or the application. For messages written by PHP, Apache or the application, use get_recent_log_lines.",
            'parameters' => [
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 2160,
                    'required' => false,
                    'default' => 168,
                    'description' =>
                        'How far back to look, in hours. Defaults to a week.',
                ],
                'severity' => [
                    'type' => 'string',
                    'enum' => ['any', 'info', 'warning', 'error', 'critical'],
                    'required' => false,
                    'default' => 'any',
                    'description' =>
                        'Only alerts at this level. Use "any" unless the user has asked about severity specifically.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                    'required' => false,
                    'default' => 20,
                    'description' => 'How many to return, newest first.',
                ],
            ],
        ],
    ];
}
