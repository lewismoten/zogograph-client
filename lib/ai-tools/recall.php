<?php
declare(strict_types=1);

function aiToolRecallPackage(): array
{
    return [
        'recall' => [
            'group' => 'memory',
            'where' => 'server',
            'permission' => false,
            'summary' =>
                'look up what was remembered from earlier conversations.',
            'description' =>
                "Searches everything written down by `remember` in past conversations.\n\nThe most useful memories are already put in front of you at the start of a conversation, so use this when you want something SPECIFIC that is not there - you are about to investigate a subsystem and want to know whether it has been looked at before, or the operator refers to something that sounds like it happened previously.\n\nPlain substring matching on the subject and the body. It is not a semantic search: search for a word that would literally appear, and if nothing comes back, nothing was remembered about it - do not treat an empty result as evidence that a thing did not happen.",
            'parameters' => [
                'about' => [
                    'type' => 'string',
                    'required' => false,
                    'maxLength' => 120,
                    'description' =>
                        'A word or phrase to look for. Leave it out to get the most recently useful memories.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                    'required' => false,
                    'default' => 20,
                    'description' => 'How many to return at most.',
                ],
            ],
        ],
    ];
}
