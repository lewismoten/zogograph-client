<?php
declare(strict_types=1);

function aiToolTerminalOutputPackage(): array
{
    return ['get_terminal_output' => [
        'group' => 'terminal', 'where' => 'server', 'permission' => true,
        'summary' => 'read what a command the user ran printed back.',
        'description' => "Returns what one terminal command printed, or what the most recent one did.\n\nUSE THIS ONLY WHEN THE OUTPUT IS THE QUESTION. \"It said something about permission denied and I do not understand it\", or a command whose exit code says it failed and whose reason is only in what it printed. Call get_terminal_commands first: the command line and the exit code answer a surprising number of these on their own, and they carry far less than the output does.\n\nTHIS IS THE MOST SENSITIVE TOOL YOU HAVE. It returns whatever a program wrote to the screen on a machine you are helping with: `cat` of a config file is a database password, a mail log is customer addresses, an environment dump is every key the account holds. It asks the user before every call for that reason, and the answer may be no - which is a legitimate answer and not an obstacle to work around. Carry on with the command and the exit code and say what you cannot see.\n\nDO NOT QUOTE MORE OF IT BACK THAN THE POINT NEEDS. Repeating a whole config file into the conversation puts it somewhere it was not before. Quote the line that matters.\n\nLong output comes back from the end rather than the beginning: what a command was doing when it stopped is almost always the half worth reading, and an error is usually the last thing said.",
        'parameters' => [
            'command_id' => ['type' => 'string', 'maxLength' => 16, 'required' => false,
                'description' => 'Which command, from get_terminal_commands. Left out, you get the most recent one - which is what the user means by "it" almost every time.'],
            'bytes' => ['type' => 'integer', 'minimum' => 256, 'maximum' => 65536,
                'required' => false, 'default' => 8192,
                'description' => 'How much of it to read, from the end. Ask for what you need rather than the maximum: everything you pull in is something that has left that machine.'],
        ],
    ]];
}
