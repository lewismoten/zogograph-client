<?php
declare(strict_types=1);

function aiToolRequestFromHostPackage(): array
{
    return ['request_from_host' => [
        'group' => 'host', 'where' => 'browser', 'permission' => false,
        'summary' => 'ask root\'s collector to do something on its next run.',
        'description' => "Asks the user to approve a job for root's collector, and queues it if they agree.\n\nUSE THIS WHEN A TOOL SAID SOMETHING IS NOT AVAILABLE TO THIS ACCOUNT. That is the whole point: this account is inside a cage and cannot see the process table, run whois, or walk /var - but root's collector can, and it will leave the answer where the next tool call can read it.\n\nIT DOES NOT RETURN THE ANSWER. It queues a job. The collector runs on its own schedule - often every five minutes, sometimes not at all if it was never installed - and the answer appears afterwards. Say that plainly, and use get_pending_host_requests to find out whether it has landed rather than assuming it has. wait_for_new_readings is how you wait for it without ending the conversation.\n\nTHE USER IS ASKED FIRST, EVERY TIME. These jobs run as root on a machine that may be shared, and some of them are expensive - a disk walk on a large filesystem takes minutes and costs I/O the whole time. If they do not answer within the timeout, nothing is queued and this returns queued=false. Treat that as a no and carry on.\n\nCHECK get_pending_host_requests FIRST. Asking for a job that is already waiting does not make it happen sooner, and it does put the same question in front of the user twice.",
        'parameters' => [
            // Fixed collector jobs only: this is never a command-execution path.
            'job' => ['type' => 'string', 'enum' => ['capabilities', 'disk-walk'], 'required' => true,
                'description' => '"capabilities" re-checks which programs exist on the host and their versions, which is what answers "is that installed on the machine even though this account cannot see it". "disk-walk" walks the whole filesystem as root, reaching other accounts and /var - expensive, minutes on a large disk, and the only thing that finds space the account cannot. These two and no others: the process list and the service list are taken on every run already, so asking for them would queue something nothing acts on.'],
            'why' => ['type' => 'string', 'maxLength' => 200, 'required' => true,
                'description' => 'What you are trying to find out, shown to the user when they are asked. It has to be a real reason they can judge - "the account cannot see other users\' processes and the memory does not add up" rather than "to gather information".'],
            'seconds' => ['type' => 'integer', 'minimum' => 15, 'maximum' => 900, 'required' => false, 'default' => 120,
                'description' => 'How long to wait for them to decide before treating it as a no.'],
        ],
    ]];
}
