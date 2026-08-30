<?php
declare(strict_types=1);

function aiToolSessionTopicPackage(): array
{
    return [
        'set_session_topic' => [
            'group' => 'memory',
            'where' => 'server',
            'permission' => false,
            'summary' => 'give this conversation a title.',
            'description' =>
                "Names the conversation, so that a list of past sessions reads as a list of subjects rather than of timestamps.\n\nUSE THIS ONCE, EARLY, as soon as you know what the conversation is actually about - which is usually after the first tool result rather than after the first message, since what somebody opens with is often not what turns out to be wrong.\n\nWRITE THE SUBJECT, NOT THE ACTIVITY. \"/var/log/exim filling at 2 GB a day\" is a title. \"Disk investigation\" is what every session would be called, and a list of those is no better than a list of dates. Name the thing: the path, the service, the symptom.\n\nIT WILL NOT OVERWRITE A TITLE THE USER SET. If they have named it, yours is ignored and the reply says so - what they called it is what they will look for.\n\nChange it later only if the conversation genuinely turned out to be about something else, not to refine the wording.",
            'parameters' => [
                'topic' => [
                    'type' => 'string',
                    'maxLength' => 80,
                    'required' => true,
                    'description' =>
                        'The subject, in a few words. Specific enough to pick out of a list of thirty.',
                ],
            ],
        ],
    ];
}
