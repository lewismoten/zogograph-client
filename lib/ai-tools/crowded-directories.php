<?php
declare(strict_types=1);

function aiToolCrowdedDirectoriesPackage(): array
{
    return [
        'find_crowded_directories' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'Directories holding the most files.',
            'description' =>
                "Returns directories by how many FILES they contain, not by size.\n\nUSE THIS ONLY FOR INODE PROBLEMS - 'no space left on device' when df shows free space, a failure to create a file on a disk that is not full, or an explicit inode warning. The answer is nearly always a session, cache or mail-queue directory holding hundreds of thousands of tiny files, and it will not appear anywhere in a list sorted by size.\n\nDO NOT use this for ordinary 'the disk is full' questions.",
            'parameters' => [
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 30,
                    'required' => false,
                    'default' => 15,
                    'description' =>
                        'How many directories to return, most files first.',
                ],
            ],
        ],
    ];
}
