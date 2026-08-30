<?php
declare(strict_types=1);

function aiToolLargestPathsPackage(): array
{
    return [
        'find_largest_paths' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' =>
                'The largest and fastest-growing files and directories.',
            'description' =>
                'Returns the biggest paths under the watched directory, or the ones that ' .
                "have grown most since the previous scan, with their sizes and rates.\n\n" .
                'USE THIS ONLY FOR DISK SPACE PROBLEMS - a disk filling up, a disk ' .
                'already full, or a question about what is using the space. Sort by ' .
                "'growth' rather than 'size' when the question is 'what changed', " .
                'because the largest directory on a server is usually the largest ' .
                "directory on that server every day and is not news.\n\n" .
                'DO NOT use this for inode exhaustion. Running out of inodes is about ' .
                'the NUMBER of files, not their size, and the directory responsible is ' .
                "often tiny - use find_crowded_directories.\n\n" .
                'DO NOT use this for CPU, memory or network problems. It reads a stored ' .
                "scan and has nothing to say about any of them.\n\n" .
                'The figures come from the last completed scan, which may be hours old. ' .
                'The reply says when it ran; say so to the user rather than presenting ' .
                'stale sizes as current.',
            'parameters' => [
                'sort_by' => [
                    'type' => 'string',
                    'enum' => ['size', 'growth'],
                    'required' => true,
                    'description' =>
                        '"size" for the biggest, "growth" for what has grown ' .
                        'most since the previous scan. Use "growth" for "what changed" ' .
                        'and "size" for "what is using all the space".',
                ],
                'category' => [
                    'type' => 'string',
                    'enum' => [
                        'any',
                        'logs',
                        'backups',
                        'cache',
                        'temp',
                        'trash',
                        'media',
                        'code',
                        'other',
                    ],
                    'required' => false,
                    'default' => 'any',
                    'description' =>
                        'Narrow to one kind of file. Use "logs" when a log is ' .
                        'suspected, "backups" when a backup job is suspected.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 40,
                    'required' => false,
                    'default' => 15,
                    'description' => 'How many paths to return.',
                ],
            ],
        ],
    ];
}
