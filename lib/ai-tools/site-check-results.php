<?php
declare(strict_types=1);

function aiToolSiteCheckResultsPackage(): array
{
    return [
        'get_site_check_results' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'Whether the watched addresses are answering.',
            'description' =>
                "Returns the configured URL checks with their most recent result, response time, and how often each has failed recently.\n\nUSE THIS WHEN THE USER SAYS THE SITE IS DOWN, SLOW, OR RETURNING ERRORS. These are the only measurements here taken from outside the machine: every internal reading can be perfectly healthy while the site returns 500, and this is the one thing that would notice.\n\nDO NOT use this for problems the user has described as internal - a full disk, a slow database, a cron that stopped.\n\nIf it returns no checks at all, say so: it means nobody has configured one, not that the site is fine.",
            'parameters' => [
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 720,
                    'required' => false,
                    'default' => 24,
                    'description' => 'How far back to summarise failures.',
                ],
            ],
        ],
    ];
}
