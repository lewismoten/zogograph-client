<?php
declare(strict_types=1);

function aiToolInspectPageHtmlPackage(): array
{
    return ['inspect_current_page_html' => [
        'group' => 'navigation', 'where' => 'browser', 'permission' => true,
        'summary' => 'read a reviewed copy of the current page structure.',
        'description' => "Asks the user to send the outer HTML of the current page's main content. This is usually more useful and much cheaper than a screenshot for a layout problem: it preserves labels, structure, and accessible state. The user sees an editable preview first and may redact any elements or text before it leaves the browser. Use this for page/layout questions, not to obtain diagnostic values that a normal tool can read.",
        'parameters' => [
            'why' => ['type' => 'string', 'maxLength' => 200, 'required' => true, 'description' => 'What structural detail you need and why other tools cannot provide it.'],
        ],
    ]];
}
