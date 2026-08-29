<?php
declare(strict_types=1);

function aiToolShowReactionPackage(): array
{
    return ['show_reaction' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => false,
        'summary' => 'show a small reaction image.',
        'description' => "Finds and shows a short animated image matching a mood.\n\nUSE THIS EXTREMELY SPARINGLY - at most once in a conversation, and only when something genuinely warrants it: a problem solved after a long hunt, or an absurd finding. It is an aside, not a punctuation mark.\n\nDO NOT use it when somebody is having a bad day, when the news is bad, or when a real problem is still unresolved. A cheerful animation over an unfixed outage is the single fastest way to make this whole tool feel like it is not taking the situation seriously.\n\nYou do not choose the image, only the mood. What comes back may not be what you pictured, so never refer to its contents in your reply.",
        'parameters' => [
            'mood' => ['type' => 'string', 'maxLength' => 40, 'required' => true, 'description' => 'Two or three words describing the feeling.'],
            'why' => ['type' => 'string', 'maxLength' => 120, 'required' => true, 'description' => 'A caption shown under it, in your own voice.'],
        ],
    ]];
}
