<?php
declare(strict_types=1);

function aiToolOfferChoicesPackage(): array
{
    return [
        'offer_choices' => [
            'group' => 'navigation',
            'where' => 'browser',
            'permission' => false,
            'summary' => 'ask the user which way to go next.',
            'description' =>
                "Shows the user a small set of directions and waits for them to pick one. Returns which they chose.\n\nUSE THIS AT A FORK, not for confirmation. The moment for it is when you have found something and there are two or three genuinely different things to do about it, and which one is right depends on what they want rather than on what the data says: investigate further, leave it alone, explain what it means, fix it. Guessing wastes a turn and sometimes takes the conversation somewhere they did not want to go.\n\nDO NOT use it to ask permission - the tools that need permission ask on their own. DO NOT use it to ask a factual question; that is ask_the_user. DO NOT offer a choice where one option is obviously correct: make the obvious move and say you made it.\n\nEach choice needs a short label and a line saying what happens if it is picked, written so somebody can decide between them without asking you first.\n\nIF THEY DO NOT ANSWER, this returns after the timeout with answered=false and the default you named. Nobody is obliged to answer a question, and a conversation that stops dead because somebody walked away is worse than one that carries on sensibly. Pick a default that is safe to do unattended - which almost always means the one that only looks and never changes anything.",
            'parameters' => [
                'question' => [
                    'type' => 'string',
                    'maxLength' => 200,
                    'required' => true,
                    'description' =>
                        'What you are asking, in one line. "The disk is filling from /var/log - what would you like to do?"',
                ],
                'choices' => [
                    'type' => 'array',
                    'maxItems' => 5,
                    'required' => true,
                    'items' => [
                        'key' => [
                            'type' => 'string',
                            'maxLength' => 32,
                            'description' =>
                                'A short identifier you get back, like "investigate".',
                        ],
                        'label' => [
                            'type' => 'string',
                            'maxLength' => 60,
                            'description' => 'What the button says.',
                        ],
                        'what_happens' => [
                            'type' => 'string',
                            'maxLength' => 160,
                            'description' =>
                                'One line saying what you will do if they pick it. Not a restatement of the label.',
                        ],
                    ],
                    'description' =>
                        'Two to five directions. Fewer is better; five is a menu, and a menu is what you offer when you have not thought about it.',
                ],
                'default_key' => [
                    'type' => 'string',
                    'maxLength' => 32,
                    'required' => true,
                    'description' =>
                        'Which choice to take if nobody answers. Must be one of the keys above, and must be safe to do unattended.',
                ],
                'seconds' => [
                    'type' => 'integer',
                    'minimum' => 15,
                    'maximum' => 900,
                    'required' => false,
                    'default' => 120,
                    'description' =>
                        'How long to wait before taking the default. The user sees the countdown and can always answer during it.',
                ],
            ],
        ],
    ];
}
