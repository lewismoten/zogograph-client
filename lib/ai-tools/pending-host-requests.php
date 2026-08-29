<?php
declare(strict_types=1);

function aiToolPendingHostRequestsPackage(): array
{
    return ['get_pending_host_requests' => [
        'group' => 'host', 'where' => 'server', 'permission' => false,
        'summary' => 'see what the host has been asked for, and whether it ran.',
        'description' => "Everything asked of root's collector: what is still waiting, what has already been answered, when the collector last ran, and when the next run is due.\n\nUSE THIS BEFORE request_from_host, so you do not queue a job that is already waiting or ask again for something answered five minutes ago. USE IT AFTER, to find out whether the answer has landed.\n\nREAD `last_run` BEFORE READING THE QUEUE. Pending jobs mean one thing when the collector ran two minutes ago and something completely different when it says \"never\": in that case nothing in the queue is ever going to happen, because the job was never installed in root's crontab, and no amount of waiting will change it. Say so rather than telling somebody to wait for a run that is not coming.\n\n`collector_has_run_since` on a pending job is the other thing worth reading. True means the collector has been past without answering it, which usually means it cannot do what was asked rather than that it has not got to it yet.\n\nThe wait is an estimate from the collector's recent behaviour, not a promise.",
        'parameters' => [],
    ]];
}
