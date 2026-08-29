<?php
declare(strict_types=1);

function aiToolRedrawPortraitPackage(): array
{
    return ['redraw_yourself' => [
        'group' => 'pictures', 'where' => 'server', 'permission' => true,
        'summary' => 'redraw your own portrait.',
        'description' => "Generates a new portrait for yourself, which becomes the picture shown beside your name.\n\nThis is a novelty. It costs money, it shares the hourly picture limit, and it needs permission every time.\n\nUSE IT ALMOST NEVER - when the mood of a long session genuinely calls for it, and not otherwise. Describe yourself however you see fit; the result is squared off and shrunk to a thumbnail, so anything intricate is wasted.\n\nDO NOT do this in place of answering a question, and DO NOT ask twice in a session.",
        'parameters' => [
            'description' => ['type' => 'string', 'maxLength' => 400, 'required' => true, 'description' => 'How you wish to be depicted.'],
            'why' => ['type' => 'string', 'maxLength' => 160, 'required' => true, 'description' => 'What prompted it. Shown when asking permission.'],
        ],
    ]];
}
