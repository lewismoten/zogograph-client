<?php
declare(strict_types=1);

function aiToolCollectorHealthPackage(): array
{
    return ['get_collector_health' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'Whether the monitoring itself is working.',
        'description' => "Returns when each collector last ran, how often it usually runs, and whether it is late.\n\nUSE THIS BEFORE CONCLUDING THAT ANYTHING IS FINE. Every chart on this dashboard goes quiet when a collector stops, and quiet looks exactly like healthy. If the user says 'it looks fine but something is wrong', check this first - the answer is often that the readings stopped three days ago.\n\nAlso use it when readings look impossibly flat or a gap appears in a chart.",
        'parameters' => [],
    ]];
}
