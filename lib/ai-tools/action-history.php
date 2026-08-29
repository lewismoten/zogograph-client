<?php
declare(strict_types=1);

function aiToolActionHistoryPackage(): array
{
    return ['get_action_history' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'What has been done through this dashboard.',
        'description' => "Returns the audit log of actions taken through this dashboard: files and directories deleted, scans started, processes stopped, settings changed, sign-ins.\n\nUSE THIS when something has disappeared, when the user asks 'did I delete that', or when a change needs to be traced to a moment.\n\nDO NOT use this to look for errors - it records deliberate actions, not failures. Failures are in get_recent_log_lines.",
        'parameters' => [
            'hours' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2160, 'required' => false, 'default' => 168, 'description' => 'How far back to look.'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 40, 'required' => false, 'default' => 20, 'description' => 'How many entries, newest first.'],
        ],
    ]];
}
