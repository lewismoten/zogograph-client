<?php
declare(strict_types=1);

function aiToolAskUserPackage(): array
{
    return ['ask_the_user' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => false,
        'summary' => 'ask the user for something only they know.',
        'description' => "Puts one question to the user with a typed answer box and waits for it.\n\nUSE THIS ONLY FOR WHAT THE TOOLS CANNOT TELL YOU. What changed on Tuesday, which of these sites matters, what the application is supposed to be doing, how much downtime is acceptable. These are facts about intent and history that are not in any database on this machine.\n\nCHECK THE TOOLS FIRST, ALWAYS. Asking the user something you could have looked up is the fastest way to make this tool feel useless - they came here so they would not have to know. If a tool can answer it, call the tool.\n\nONE QUESTION. Not a form, not a survey. If you need three things, ask for the one that changes what you do next and work out the rest.\n\nFour shapes of answer:\n  choice     one of a list. Give options.\n  checklist  any number from a list. Give options.\n  number     a figure, with min and max if they mean something.\n  text       free text, for anything that is not one of the above.\n\nPrefer choice and checklist where the answers are knowable, because picking is faster than typing and the answer comes back in a shape you can rely on.\n\nIF THEY DO NOT ANSWER, this returns after the timeout with answered=false and no value. Carry on without it and say what you assumed - do not ask again.",
        'parameters' => [
            'question' => ['type' => 'string', 'maxLength' => 300, 'required' => true,
                'description' => 'The question, in one line. Say why you are asking if it is not obvious - people answer a question they understand the point of.'],
            'answer' => ['type' => 'string', 'enum' => ['choice', 'checklist', 'number', 'text'], 'required' => true, 'description' => 'What shape the answer takes.'],
            'options' => ['type' => 'array', 'maxItems' => 12, 'required' => false,
                'items' => [
                    'key' => ['type' => 'string', 'maxLength' => 32, 'description' => 'What you get back.'],
                    'label' => ['type' => 'string', 'maxLength' => 80, 'description' => 'What is shown.'],
                ],
                'description' => 'Required for choice and checklist, ignored otherwise. Include an option for "none of these" where that is a real possibility, or you will get a wrong answer rather than no answer.'],
            'minimum' => ['type' => 'integer', 'minimum' => -1000000, 'maximum' => 1000000, 'required' => false, 'description' => 'For answer="number". The lowest sensible value.'],
            'maximum' => ['type' => 'integer', 'minimum' => -1000000, 'maximum' => 1000000, 'required' => false, 'description' => 'For answer="number". The highest sensible value.'],
            'unit' => ['type' => 'string', 'maxLength' => 24, 'required' => false, 'description' => 'For answer="number". What the figure is in, shown beside the box: "GB", "days", "requests a second".'],
            'seconds' => ['type' => 'integer', 'minimum' => 15, 'maximum' => 900, 'required' => false, 'default' => 180, 'description' => 'How long to wait before giving up on an answer.'],
        ],
    ]];
}
