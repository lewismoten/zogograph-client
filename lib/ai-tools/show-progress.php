<?php
declare(strict_types=1);

function aiToolShowProgressPackage(): array
{
    return ['show_progress' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => false,
        'summary' => 'show progress on something that is running.',
        'description' => "Draws a labelled progress indicator in the conversation.\n\nUSE THIS when something is running that the user is waiting on. Three kinds, and picking the right one matters more than any other choice here, because each one makes a different promise:\n\n  kind=\"time\"    a bar and a clock. Requires percent, and seconds_left is what makes it worth drawing. Use it ONLY when you genuinely know roughly how long is left - a scan whose usual duration you have looked up, a capture with a fixed length.\n\n  kind=\"count\"   a bar and a tally: \"7 of 12 checks done\". Requires done and total. Use it when the work is a known number of steps and you are working through them. This is the honest answer far more often than a clock is, because you usually know how many things are left and not how long each takes.\n\n  kind=\"working\" a moving indicator with no end point, for work whose size you do not know. Takes neither a percent nor a total. Use it rather than inventing either.\n\nNEVER invent a duration, a percentage or a total. A made-up clock is worse than no clock, because people plan around it - and \"working\" is always available and always true. If you do not know how far through something is, that is what kind=\"working\" is for.\n\nDO NOT use any of them decoratively. This is for work the user is waiting on, not for narrating your own thinking.\n\nIt draws once and does not animate its own value. Call it again to move it; each call replaces the last one for the same `what`.",
        'parameters' => [
            'what' => ['type' => 'string', 'maxLength' => 80, 'required' => true, 'description' => 'What is running, in a few words.'],
            'kind' => ['type' => 'string', 'enum' => ['time', 'count', 'working'], 'required' => true, 'description' => 'Which honest progress shape to use.'],
            'percent' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100, 'required' => false, 'description' => 'For kind="time" only.'],
            'seconds_left' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 604800, 'required' => false, 'description' => 'For kind="time".'],
            'done' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 1000000, 'required' => false, 'description' => 'For kind="count".'],
            'total' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000000, 'required' => false, 'description' => 'For kind="count".'],
            'unit' => ['type' => 'string', 'maxLength' => 24, 'required' => false, 'description' => 'For kind="count".'],
        ],
    ]];
}
