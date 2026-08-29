<?php
declare(strict_types=1);

function aiToolNetworkSummaryPackage(): array
{
    return ['get_network_summary' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'Ports, connections and recent traffic.',
        'description' => "Returns which ports are listening, how many established connections there are grouped by the address at the other end, and a summary of the most recent packet capture if one has been taken.\n\nUSE THIS ONLY FOR NETWORK PROBLEMS: unexpected traffic, a suspected flood or scan, a service that should be listening and is not, or a question about who is connecting.\n\nDO NOT use this for a site that is down - that is get_site_check_results. DO NOT use it for slowness unless the user has specifically mentioned bandwidth or connections; a slow site is far more often disk, CPU or the application.\n\nOne address holding hundreds of connections is the shape worth reporting; a long list of addresses with one each is ordinary web traffic.\n\nIF THE LAST CAPTURE WAS TAKEN WITH READING TURNED ON, it also returns what the traffic was asking for: DNS names with their record type and count, and the host names and pages that were requested. That is what answers 'what is all this traffic on port 443'. If it was not, the reply says so - tell the user the tick box exists rather than reporting the address counts as though they were the answer. You cannot turn it on yourself; it is off by default on purpose.",
        'parameters' => [],
    ]];
}
