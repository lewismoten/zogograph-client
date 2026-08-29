<?php
declare(strict_types=1);

function aiToolTakeScreenshotPackage(): array
{
    return ['take_screenshot' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => true,
        'summary' => 'take a picture of what you can see.',
        'description' => "Captures an image of the page the user currently has open and returns it to you.\n\nUSE THIS SPARINGLY and only when the shape of something matters more than its numbers: a chart the user is describing in words, a layout problem, or a value they can see and you cannot find. Images cost far more tokens than text - a screenshot is worth roughly a thousand words of data you could have fetched precisely.\n\nDO NOT use this to read data that a diagnostic tool returns. If you want the disk figures, call get_current_status; a picture of them is worse in every way.\n\nDO NOT use it as a first step. Find out what you can from the data first, and reach for this when the user's description and the data disagree.\n\nThe user is asked before each capture and may refuse.",
        'parameters' => [
            'why' => ['type' => 'string', 'maxLength' => 200, 'required' => true, 'description' => 'What you expect to see and why the data cannot tell you. This is shown to the user when asking permission, so it has to be a real reason they can judge.'],
        ],
    ]];
}
