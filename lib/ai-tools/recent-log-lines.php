<?php
declare(strict_types=1);

function aiToolRecentLogLinesPackage(): array
{
    return ['get_recent_log_lines' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'Recent lines from the server and application logs.',
        'description' => "Returns the newest lines across every log file this account can read - PHP error logs, Apache error and access logs, and the dashboard's own log - merged into one time-ordered list. Each line comes back already split into its timestamp, severity, what wrote it, and the message, with any stack trace attached to the error it belongs to.\n\nUSE THIS when the user describes something failing, a page erroring, a 500, a blank page, or anything where software rather than capacity is the suspect.\n\nDO NOT use this to investigate a full disk or high CPU unless the logs are what is filling the disk. Capacity problems are answered by get_current_status and find_largest_paths.\n\nFilter by level when the user is describing a failure: unfiltered, the twenty newest lines on a busy server are usually twenty access-log entries and tell you nothing.",
        'parameters' => [
            'level' => ['type' => 'string', 'enum' => ['any', 'warning', 'error', 'critical'], 'required' => false, 'default' => 'any', 'description' => 'Only lines at this level or worse. "error" is the usual choice when investigating a failure. Lines that announce no level at all - most access-log entries - are excluded by anything other than "any".'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 60, 'required' => false, 'default' => 25, 'description' => 'How many lines to return, newest first.'],
            'contains' => ['type' => 'string', 'maxLength' => 120, 'required' => false, 'description' => 'Plain text that must appear somewhere in the line. This is a literal substring match, NOT a pattern - regular expression syntax is matched as literal characters. Use it to follow up a specific filename, function or error the user has quoted. Leave it out to see everything.'],
        ],
    ]];
}
