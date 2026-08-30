<?php
declare(strict_types=1);

function aiToolTerminalCommandsPackage(): array
{
    return [
        'get_terminal_commands' => [
            'group' => 'terminal',
            'where' => 'server',
            'permission' => false,
            'summary' => 'see what commands the user has run in the terminal.',
            'description' =>
                "Returns the commands run in the most recent terminal session - what was typed, when, how long each took, and how it ended. NOT what any of them printed.\n\nUSE THIS WHEN THE USER IS ASKING ABOUT SOMETHING THEY JUST DID. \"I ran the thing you said and it did not work\" is unanswerable without knowing what they actually ran, and asking them to paste it back is asking them to do work you can do yourself. It is also how you find out that the command you suggested was typed with a different flag.\n\nTHE EXIT CODE IS THE MOST USEFUL FIELD. 0 means it worked, 1 usually means it ran and refused, 127 means the program does not exist, 126 means it exists and is not executable, and anything over 128 means a signal killed it - 130 is Ctrl-C, 137 is SIGKILL, 143 is SIGTERM. `stopped_by` means the user stopped it on purpose, and `timed_out` means it hit the terminal's own limit.\n\nTHIS DOES NOT SHOW OUTPUT, on purpose. A command line is what somebody chose to type; its output is whatever happened to be in a file, which may be a password, a key or a customer's details. If you need what it printed, that is get_terminal_output, which is a separate tool with its own permission - and the operator may well have allowed one and not the other.\n\nA command line can still hold a secret - somebody types a password into one now and then. Do not repeat one back in full unless the user needs to see it.",
            'parameters' => [
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                    'required' => false,
                    'default' => 20,
                    'description' =>
                        'How many of the most recent commands. The newest are almost always the relevant ones.',
                ],
            ],
        ],
    ];
}
