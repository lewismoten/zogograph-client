<?php
declare(strict_types=1);

function aiToolChartImagePackage(): array
{
    return [
        'get_chart_image' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'get a picture of one chart.',
            'description' =>
                "Renders one named chart and returns the image to YOU.\n\n" .
                'THIS IS A LAST RESORT. Call get_chart_data first, always. The data is ' .
                'exact, cheap, and answers almost every question a chart can. An image ' .
                'costs roughly fifty times as many tokens and you cannot read a value ' .
                "off it.\n\n" .
                "USE THIS ONLY when the user is describing something about a chart's " .
                'SHAPE that the numbers do not settle: a pattern they can see and you ' .
                'cannot infer, something they say looks wrong, or a disagreement between ' .
                'what they describe and what the data says. In that last case the point ' .
                "is to find out whether you are both looking at the same thing.\n\n" .
                'STILL BETTER THAN take_screenshot. This gives you one chart at a known ' .
                'size with nothing else in the frame; a screenshot gives you the whole ' .
                'window, costs more, and may contain things the user would rather not ' .
                'send. Only fall back to a screenshot when what matters is not a chart ' .
                'at all.',
            'parameters' => [
                'chart' => [
                    'type' => 'string',
                    'enum' => [],
                    'required' => true,
                    'description' => 'Which chart to render.',
                ],
                'hours' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 8760,
                    'required' => true,
                    'description' => 'The period it should cover.',
                ],
                'why' => [
                    'type' => 'string',
                    'maxLength' => 200,
                    'required' => true,
                    'description' =>
                        'What you expect to see and why the numbers from ' .
                        'get_chart_data did not settle it. If you cannot answer that, ' .
                        'call get_chart_data instead.',
                ],
            ],
        ],
    ];
}
