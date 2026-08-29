<?php
declare(strict_types=1);

function aiToolListChartsPackage(): array
{
    return ['list_charts' => [
        'group' => 'navigation', 'where' => 'server', 'permission' => false,
        'summary' => 'list every chart and which page it is on.',
        'description' => "Every chart on this dashboard: its id, what it plots, which page it is on, and whether you can fetch its numbers.\n\nUSE THIS BEFORE sending anybody to look at something. There are more charts here than there are tools - pie charts, a treemap of directories, per-service memory, a timeline of alerts - and for most of them the right answer to a question is \"that is the treemap on the Tree map page\" rather than a wall of numbers.\n\n`data_available` says whether get_chart_data can serve that chart. When it is false the chart is still real and still worth sending somebody to; you simply cannot read it yourself.\n\nA chart with a null page is one that can be drawn in the conversation and is not on any page. Do not tell anybody to go and look at it.",
        'parameters' => [],
    ]];
}
