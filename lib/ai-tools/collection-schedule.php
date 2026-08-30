<?php
declare(strict_types=1);

function aiToolCollectionSchedulePackage(): array
{
    return [
        'get_collection_schedule' => [
            'group' => 'host',
            'where' => 'server',
            'permission' => false,
            'summary' =>
                'see how often each collector runs and when it last did.',
            'description' =>
                "How often the account's cron and the host's cron actually run, when each last ran, and when each is next due.\n\nUSE THIS whenever a wait is involved: before telling somebody to check back, before asking them to wait for a request, and whenever a reading looks stale. It is the difference between \"in about four minutes\" and \"shortly\".\n\nALSO USE IT when something looks impossibly fine. A collector that has stopped produces charts that go flat and quiet, which looks exactly like a healthy machine - and this is the tool that tells the two apart.\n\nThe intervals are measured from the gaps between actual runs, not read from a crontab, so they are what is really happening rather than what was configured.",
            'parameters' => [],
        ],
    ];
}
