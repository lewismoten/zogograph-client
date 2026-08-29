<?php
declare(strict_types=1);

function aiToolGaugesPackage(): array
{
    return ['get_gauges' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'read the five gauges on the front page.',
        'description' => "The five arcs at the top of the Overview page - disk, inodes, CPU, memory and load - with what each reads now and which band it is in.\n\nUSE THIS EARLY, and use it whenever the person says something is or is not a problem. It is the only tool that tells you what THEY are looking at: the same numbers are available raw from get_current_status, but without the bands you cannot tell whether the screen in front of them is green or red, and disagreeing with a screen you cannot see is the fastest way to sound wrong.\n\nThe bands here colour the arcs and are fixed. They are NOT the alert thresholds, which are configurable and are what sends email - so a gauge can be amber with no alert raised, and that is not a fault.\n\nEach gauge names the chart that plots it over time, so \"the disk gauge is amber\" can become \"and here is how it got there\".",
        'parameters' => [],
    ]];
}
