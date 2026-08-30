<?php
declare(strict_types=1);

function aiToolWaitForReadingsPackage(): array
{
    return [
        'wait_for_new_readings' => [
            'group' => 'diagnostic',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'wait until fresh measurements arrive.',
            'description' =>
                "Waits for the next collector run to land, then returns. Use it when the answer you need does not exist yet.\n\nUSE THIS AFTER request_from_host, or when a reading is a few minutes old and the question is about right now, or when you have asked somebody to change something and want to see the effect. It is the difference between \"try again in five minutes\" and staying in the conversation until the data arrives.\n\nwhich=\"account\" is this dashboard's own collector, which runs frequently. which=\"host\" is root's, which runs less often and may not be installed at all - check get_collector_health or get_pending_host_requests first, because waiting for a collector that has never run wastes the whole timeout.\n\nRETURNS EITHER WAY. `arrived: true` means new data landed and you should re-read whatever you were waiting on - the tool results you already have are from before it. `arrived: false` means the timeout passed with nothing new, which is itself a finding worth reporting: the collector is not running.\n\nDO NOT use it to pad a conversation, and do not wait twice in a row for the same thing.",
            'parameters' => [
                'which' => [
                    'type' => 'string',
                    'enum' => ['account', 'host'],
                    'required' => true,
                    'description' =>
                        '"account" for this dashboard\'s own collector, "host" for root\'s.',
                ],
                'seconds' => [
                    'type' => 'integer',
                    'minimum' => 30,
                    'maximum' => 1800,
                    'required' => false,
                    'default' => 300,
                    'description' =>
                        'How long to wait before giving up. Pick it from how often that collector actually runs, which get_collection_schedule will tell you.',
                ],
            ],
        ],
    ];
}
