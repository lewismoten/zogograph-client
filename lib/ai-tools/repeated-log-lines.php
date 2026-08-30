<?php
declare(strict_types=1);

function aiToolRepeatedLogLinesPackage(): array
{
    return [
        'get_repeated_log_lines' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'Which log messages keep coming back.',
            'description' =>
                "Groups recent log lines by shape - numbers, addresses and paths replaced with placeholders - and returns the most frequent, with a count and an example of each.\n\nUSE THIS when a log is growing quickly, when a disk is filling with logs, or when the user says something is 'flooding' or 'spamming'. Ten thousand lines differing only in a request id are ONE event with a count of ten thousand, and that is the only presentation of it anybody can act on.\n\nDO NOT use this to read a specific error. It deliberately destroys the details that make one line different from another - use get_recent_log_lines for that.",
            'parameters' => [
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 25,
                    'required' => false,
                    'default' => 10,
                    'description' =>
                        'How many distinct shapes to return, most frequent first.',
                ],
            ],
        ],
    ];
}
