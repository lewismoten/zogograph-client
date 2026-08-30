<?php
declare(strict_types=1);

function aiToolChartDataPackage(): array
{
    return [
        'get_chart_data' => [
            'group' => 'diagnostic',
            'where' => 'server',
            'permission' => false,
            'summary' => 'The numbers behind one named chart.',
            'description' =>
                'Returns the measurements a named chart is drawn from, bucketed over a ' .
                "period, with the lowest, highest and average of each bucket.\n\n" .
                'PREFER THIS OVER EVERY OTHER WAY OF LOOKING AT A CHART. A chart is a ' .
                'rendering of data you can have directly; asking for a picture of it ' .
                'instead is asking for a worse copy at roughly fifty times the cost in ' .
                "tokens, and you cannot read exact values off an image.\n\n" .
                'USE THIS when the user names a chart, or describes something a chart ' .
                "shows, or when you want a measurement over time rather than right now.\n\n" .
                'Each bucket carries its highest value as well as its average, because ' .
                'the average of an hour containing a two-minute spike hides the spike - ' .
                'and the spike is usually the thing being looked for.',
            'parameters' => [
                'chart' => [
                    'type' => 'string',
                    'enum' => [],
                    'required' => true,
                    'description' =>
                        'Which chart. The user may name one; every chart on ' .
                        'this dashboard shows its own id when hovered, so they can tell ' .
                        'you exactly which one they mean.',
                ],
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 8760,
                    'required' => true,
                    'description' =>
                        'How far back. 24 for today, 168 for a week.',
                ],
            ],
        ],
    ];
}
