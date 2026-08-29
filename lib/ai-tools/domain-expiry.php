<?php
declare(strict_types=1);

function aiToolDomainExpiryPackage(): array
{
    return ['get_domain_expiry' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'watched domain names and when each expires.',
        'description' => "Every domain being watched, when it expires, and who it is registered with.\n\nUSE THIS when a site has stopped answering and nothing on the machine explains it. An expired domain looks exactly like a network fault from inside the machine, and it is the one cause that no amount of looking at the server will find.\n\nThe dates come from whois, which the host collector runs, so they are as fresh as the last lookup. Each row says when it was checked.",
        'parameters' => [],
    ]];
}
