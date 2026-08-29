<?php
declare(strict_types=1);

function aiToolOpenPagePackage(): array
{
    return ['open_page' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => false,
        'summary' => 'move you to a page of this dashboard.',
        'description' => "Opens one of this dashboard's pages in the user's browser. The chat stays open and the conversation continues - this does not interrupt anything, it just changes what is behind the panel.\n\nUSE THIS when you have found something and the user should see it for themselves, or when the page has a control they need - a button to empty a directory, a form to add a check. Say why you are taking them there in the same message.\n\nDO NOT use this to gather information. It shows the user a page; it does not tell YOU anything. The diagnostic tools are how you find things out, and calling this instead wastes a turn and moves the user somewhere for no reason.\n\nDO NOT call it more than once per message. Two navigations in a row means the user never sees the first.",
        'parameters' => [
            'page' => ['type' => 'string', 'enum' => [], 'required' => true, 'description' => 'Which page to open. The list is fixed; anything not in it is refused.'],
            'hours' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 8760, 'required' => false, 'description' => 'Set the page\'s time range to this many hours, so the user arrives looking at the period being discussed rather than the default.'],
            'why' => ['type' => 'string', 'maxLength' => 200, 'required' => true, 'description' => 'One short sentence shown to the user explaining why they are being taken here. Write it for them, not for yourself: "so you can see which directories grew" rather than "navigating to storage".'],
        ],
    ]];
}
