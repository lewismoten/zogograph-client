<?php
declare(strict_types=1);

function aiToolMakePicturePackage(): array
{
    return [
        'make_picture' => [
            'group' => 'pictures',
            'where' => 'server',
            'permission' => true,
            'summary' => 'have a picture drawn and show it.',
            'description' =>
                "Describes a picture to an image generator and shows the result in the conversation.\n\nThis costs real money per call and takes several seconds. There is an hourly limit, and every call needs the user's permission.\n\nUSE THIS RARELY, and only when a picture does something words cannot: illustrating a shape, a layout, or an idea the user is struggling to describe.\n\nDO NOT use it to illustrate data - that is what the charts are for, and a drawing of a chart is a drawing, not a measurement. DO NOT use it as decoration. DO NOT use it to be funny more than once in a very long while; the joke does not survive repetition and the user is paying for each one.\n\nDescribe the picture in full: the generator sees only your words and nothing of this conversation.",
            'parameters' => [
                'description' => [
                    'type' => 'string',
                    'maxLength' => 600,
                    'required' => true,
                    'description' =>
                        'What the picture should show, described completely. The generator has no other context.',
                ],
                'style' => [
                    'type' => 'string',
                    'enum' => [],
                    'required' => true,
                    'description' =>
                        'Which generator to use. The list is what this install allows.',
                ],
                'why' => [
                    'type' => 'string',
                    'maxLength' => 160,
                    'required' => true,
                    'description' =>
                        'Why a picture rather than a sentence. Shown to the user when asking permission, so it has to be a reason they can judge.',
                ],
            ],
        ],
    ];
}
