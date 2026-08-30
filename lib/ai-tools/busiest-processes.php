<?php
declare(strict_types=1);

function aiToolBusiestProcessesPackage(): array
{
    return [
        'get_busiest_processes' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'What is running and what it is using.',
            'description' =>
                "Returns the processes recorded at the last collection, with their process id, owner, CPU and memory use, how long each has been running, and its command line.\n\nUSE THIS FOR CPU OR MEMORY PROBLEMS ONLY. The two things worth looking for are a process using far more than the others, and a process that has been running far longer than it should have - a request handler alive for six hours is stuck, whatever its CPU says.\n\nDO NOT use this for disk space or network problems.\n\nThis is a snapshot from the last collector run, not a live view, so a process that has since exited may still be listed and one that started since will not be. Say so if the timing matters.",
            'parameters' => [
                'sort_by' => [
                    'type' => 'string',
                    'enum' => ['cpu', 'memory', 'age'],
                    'required' => true,
                    'description' =>
                        'Which column to sort by, largest first. Use "age" to find stuck processes.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 30,
                    'required' => false,
                    'default' => 12,
                    'description' => 'How many processes to return.',
                ],
            ],
        ],
    ];
}
