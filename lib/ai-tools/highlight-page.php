<?php
declare(strict_types=1);

function aiToolHighlightPagePackage(): array
{
    return [
        'highlight_on_page' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'point at something on the page.',
            'description' =>
                "Briefly outlines and pulses one thing on the page the user is looking at, and scrolls it into view.\n\nUSE THIS WITH A SENTENCE ABOUT WHAT YOU ARE POINTING AT. \"The row for clamd\" in prose is something the user then has to find in ninety rows; this puts their eye on it. It is at its best straight after open_page, which gets them to the right page but not to the right line.\n\nIt highlights and fades. It does not select, change or open anything, and it never leaves a mark behind.\n\nIF NOTHING MATCHES, that is what comes back - `found: false` - and you should say so rather than describing a highlight that did not happen. The user may be on a different page, or the thing may not be on screen.\n\nDO NOT use it repeatedly. Pointing at six things in a row is pointing at nothing.",
            'parameters' => [
                'find' => [
                    'type' => 'string',
                    'maxLength' => 120,
                    'required' => true,
                    'description' =>
                        'The text to look for on the page - a process name, a path, a setting label, a chart title. Matched inside the page the user currently has open, ignoring case. Use the exact text you saw in a tool result rather than a description of it.',
                ],
                'why' => [
                    'type' => 'string',
                    'maxLength' => 160,
                    'required' => true,
                    'description' =>
                        'What you want them to notice about it. Shown beside the highlight, so it reads as a caption rather than a label.',
                ],
            ],
        ],
    ];
}
