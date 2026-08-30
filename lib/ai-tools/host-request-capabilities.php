<?php
declare(strict_types=1);

function aiToolHostRequestCapabilitiesPackage(): array
{
    return [
        'what_the_host_can_be_asked' => [
            'group' => 'host',
            'where' => 'server',
            'permission' => false,
            'summary' => 'list what root\'s collector can be asked to do.',
            'description' =>
                "Lists the jobs this dashboard can ask the host's root collector to run on the account's behalf, and what each one answers.\n\nThis is the route to everything the account cannot see for itself: the whole machine's disk use, other accounts, /var and /backup, the process table as root sees it, what programs exist outside CageFS.\n\nIT IS NOT IMMEDIATE, and this is the part to get right when you talk about it. A request is left in a table; root's cron picks it up on its next run and does the work then. The answer comes back minutes later. The tool tells you how often that cron runs and when it last ran, so you can say how long it will be rather than guessing - and you can draw a progress bar with `show_progress` if the wait is worth showing.\n\nYou cannot make the request. Each job names the page where the operator presses the button; send them there.",
            'parameters' => [],
        ],
    ];
}
