<?php
declare(strict_types=1);

function aiToolRecentChangesPackage(): array
{
    return ['get_recent_changes' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'Software that changed underneath.',
        'description' => "Returns changes noticed in installed software - the kernel, PHP, OpenSSL, the C library, and the programs this dashboard watches - with what each was and what it became.\n\nUSE THIS WHENEVER THE USER SAYS SOMETHING WORKED BEFORE AND DOES NOT NOW. It is the single most common cause and the least visible: a package update at three in the morning explains a great deal of otherwise inexplicable behaviour.\n\nThe times are when the change was NOTICED, not when it happened - this is recorded on a probe rather than continuously. Say so rather than asserting a change happened at a particular minute.",
        'parameters' => [
            'hours' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 8760, 'required' => false, 'default' => 336, 'description' => 'How far back to look. Defaults to a fortnight.'],
        ],
    ]];
}
