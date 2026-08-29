<?php
declare(strict_types=1);

function aiToolShowChartPackage(): array
{
    return ['show_chart' => [
        'group' => 'navigation',
        'where' => 'browser',
        'permission' => false,
        'summary' => 'draw a chart inside this conversation.',
        'description' =>
            "Draws a named chart in the chat itself, where the user can see it without "
            . "leaving the page they are on.\n\n"
            . "USE THIS whenever you are talking about something a chart shows and it "
            . "would help the user to see it. It is far better than open_page for this: "
            . "it does not move them away from whatever they were doing, and the chart "
            . "appears right next to the sentence about it.\n\n"
            . "This shows the user a picture. It does NOT show you one - you get "
            . "nothing back but a confirmation. If you need the numbers, call "
            . "get_chart_data; if you genuinely need to see the rendering, call "
            . "get_chart_image.\n\n"
            . "Say what you want them to notice in the same message. A chart with no "
            . "sentence attached is a shrug.",
        'parameters' => [
            'chart' => [
                'type' => 'string', 'enum' => [], 'required' => true,
                'description' => 'Which chart to draw.',
            ],
            'hours' => [
                'type' => 'integer', 'minimum' => 1, 'maximum' => 8760,
                'required' => true,
                'description' => 'The period it should cover.',
            ],
            'why' => [
                'type' => 'string', 'maxLength' => 200, 'required' => true,
                'description' => 'One short line shown as the chart\'s caption, saying '
                    . 'what to look at. Written for the reader: "the step at 09:20 is '
                    . 'where it jumped" rather than "displaying disk usage".',
            ],
        ],
    ]];
}
