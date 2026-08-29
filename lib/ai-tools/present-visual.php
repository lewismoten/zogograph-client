<?php
declare(strict_types=1);

function aiToolPresentVisualPackage(): array
{
    return ['present_visual' => [
        'group' => 'navigation',
        'where' => 'browser',
        'permission' => false,
        'summary' => 'present supplied data as a line chart, pie, or gauge.',
        'description' =>
            "Presents data you already have in the conversation as a small interactive "
            . "visual. Unlike show_chart, this is not limited to a chart built into "
            . "Zogograph. Use it to make a trend, composition, or current-versus-limit "
            . "clear. Do not invent measurements: the data must come from the user or "
            . "a tool result. The browser shows it beside the conversation; you only "
            . "receive confirmation that it was shown.\n\n"
            . "data is JSON. For line use {series:[{key,label}],points:[{at,key:value}]}; "
            . "for pie use {slices:[{label,value}]}; for gauge use {value,max,label}.",
        'parameters' => [
            'kind' => [
                'type' => 'string', 'enum' => ['line', 'pie', 'gauge'], 'required' => true,
                'description' => 'The visual type that best fits the supplied data.',
            ],
            'title' => [
                'type' => 'string', 'maxLength' => 100, 'required' => true,
                'description' => 'A concise title for the reader.',
            ],
            'data' => [
                'type' => 'string', 'maxLength' => 12000, 'required' => true,
                'description' => 'Valid compact JSON in the shape described above.',
            ],
            'why' => [
                'type' => 'string', 'maxLength' => 200, 'required' => true,
                'description' => 'One sentence telling the reader what to notice.',
            ],
        ],
    ]];
}
