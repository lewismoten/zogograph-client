<?php
declare(strict_types=1);

function aiToolServicesPackage(): array
{
    return ['get_services' => [
        'group' => 'diagnostic',
        'where' => 'server',
        'permission' => false,
        'summary' => 'systemd units, with the broken ones first.',
        'description' =>
            "Every service the machine knows about, worst state first: failed, "
            . "masked, running-when-it-should-not-be, and meant-to-be-running-and-is-"
            . "not.\n\n"
            . "USE THIS when something is not answering. A FAILED unit is invisible "
            . "everywhere else on this dashboard - it uses no CPU and no memory, so "
            . "it does not appear in any list of what is busy - and it is one of the "
            . "few faults where the cause and the fix are both a single line.\n\n"
            . "A MASKED unit cannot be started at all until somebody unmasks it, "
            . "which is worth saying out loud because \"start it\" will fail with a "
            . "message people find confusing.\n\n"
            . "This needs the host collector. Without one there is no service list "
            . "and it says so.",
        'parameters' => [
            'state' => [
                'type' => 'string', 'required' => false,
                'enum' => ['problems', 'all', 'failed', 'masked', 'running',
                           'stopped'],
                'default' => 'problems',
                'description' => '"problems" is failed, masked, and anything running '
                    . 'when it should not be or not running when it should. That is '
                    . 'chosen by state and never by name - no service here is more '
                    . 'important than another, only more broken.',
            ],
            'limit' => [
                'type' => 'integer', 'minimum' => 1, 'maximum' => 100,
                'required' => false, 'default' => 40,
                'description' => 'How many to return.',
            ],
        ],
    ]];
}
