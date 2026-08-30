<?php
declare(strict_types=1);

function aiToolSymlinkProblemsPackage(): array
{
    return [
        'get_symlink_problems' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' =>
                'symlinks that are broken, looping or leave the account.',
            'description' =>
                "The result of the last symlink walk: links that point at nothing, links that form a loop, links into their own ancestors, and links that leave the watched directory.\n\nUSE THIS when a backup takes forever or never finishes, when `du` and the dashboard disagree about a directory's size, or when something reports an impossible amount of data. A link pointing at one of its own ancestors is an infinitely deep directory to anything that follows links - and backups, du, tar and web crawlers all do - which is how a disk fills with one file copied ten thousand times.\n\nThis reads the STORED result of the last walk rather than walking the tree, which takes tens of seconds. If nothing has been checked it says so; the operator can run one from the Symlinks page.",
            'parameters' => [
                'kind' => [
                    'type' => 'string',
                    'required' => false,
                    'enum' => ['any', 'broken', 'loop', 'ancestor', 'outside'],
                    'default' => 'any',
                    'description' =>
                        'Narrow it to one kind. "loop" and "ancestor" are the two that cause runaway descent; "broken" is untidy rather than dangerous; "outside" makes measured sizes misleading.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'required' => false,
                    'default' => 25,
                    'description' =>
                        'How many to return. The counts are always for all of them, however few are listed.',
                ],
            ],
        ],
    ];
}
