<?php
declare(strict_types=1);

function aiToolRecommendCommandPackage(): array
{
    return ['recommend_command' => [
        'group' => 'terminal', 'where' => 'server', 'permission' => false,
        'summary' => 'put a command in the terminal for the operator to run.',
        'description' =>
            "Writes a shell command onto the operator's terminal page, where they can "
            . "read it and choose to run it. YOU DO NOT RUN ANYTHING. There is no tool "
            . "here that executes a command, and there will not be one - the person "
            . "reading it decides.\n\n"
            . "USE THIS when the answer is somewhere your tools cannot reach: the "
            . "process table as root sees it, a log this account cannot open, the "
            . "output of a host utility, the state of something after a change.\n\n"
            . "DO NOT use it for anything a tool here already answers. If there is a "
            . "tool for it, use the tool - it is faster, it is already permitted, and "
            . "asking a person to type something you could have looked up yourself is "
            . "how an assistant becomes a chore.\n\n"
            . "REQUIREMENTS, all of them, every time:\n"
            . "  - the command must be safe to run and must not change anything unless "
            . "the operator has asked for exactly that change. Prefer reading over "
            . "writing. Never suggest something destructive as a diagnostic.\n"
            . "  - `reading` must explain what the output will look like and how to tell "
            . "a good result from a bad one BEFORE they run it. Somebody staring at "
            . "unfamiliar output with no idea what they are looking for is worse off "
            . "than before they ran it.\n"
            . "  - the command runs as the web account, not as root, unless they have "
            . "told you otherwise. Say so if it needs privileges it will not have.\n\n"
            . "After it runs they can send you the output with one button. Wait for "
            . "that rather than guessing what it said.",
        'parameters' => [
            'command' => ['type' => 'string', 'required' => true, 'maxLength' => 2000,
                'description' => 'The command itself, exactly as it should be typed. One command - use a pipeline if you need several steps, not several calls to this tool. No leading prompt character.'],
            'why' => ['type' => 'string', 'required' => true, 'maxLength' => 400,
                'description' => 'Why you want this run, in one or two sentences, in terms of what you are trying to find out.'],
            'what_to_look_for' => ['type' => 'string', 'required' => true, 'maxLength' => 600,
                'description' => 'What the output will look like and what would count as a problem in it. Written so somebody can read the output without you.'],
            'reading' => ['type' => 'string', 'required' => false, 'maxLength' => 400,
                'description' => 'A link to documentation for the main command, if there is an obvious one - a man page or the project site. Leave it out rather than inventing a URL.'],
        ],
    ]];
}
