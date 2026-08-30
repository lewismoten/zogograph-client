<?php
declare(strict_types=1);

function aiToolTimeRangePackage(): array
{
    return [
        'set_time_range' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'change the range the dashboard is showing.',
            'description' =>
                'Changes the time range every chart on the dashboard is drawn over, and ' .
                "optionally turns on live sampling.\n\n" .
                'USE THIS when the detail you need is finer than the range being shown. ' .
                "At a day's range a spike lasting ninety seconds is one pixel and may be " .
                "averaged away entirely; at ten minutes it is the whole chart.\n\n" .
                'LIVE takes a fresh reading every few seconds rather than waiting for the ' .
                "collector's next run. It is the only way to watch something as it " .
                'happens, and it only works at the shortest range. Turn it on when ' .
                'somebody is watching a problem occur; leave it off otherwise, because it ' .
                "writes a reading every few seconds for as long as it is on.\n\n" .
                'This changes what the OPERATOR sees. Say why you are doing it.',
            'parameters' => [
                'range' => [
                    'type' => 'string',
                    'required' => true,
                    'enum' => ['10m', '1h', '6h', '24h', '7d', '30d'],
                    'description' =>
                        'How far back to show. 10m is the finest and is the ' .
                        'only one that can be live.',
                ],
                'live' => [
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false,
                    'description' =>
                        'Take readings continuously while they watch. Only ' .
                        'has an effect at the 10m range.',
                ],
                'why' => [
                    'type' => 'string',
                    'required' => true,
                    'maxLength' => 200,
                    'description' =>
                        'What you expect to become visible at this range, in ' .
                        'one sentence. Shown to them.',
                ],
            ],
        ],
    ];
}
