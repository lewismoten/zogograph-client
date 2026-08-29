<?php
declare(strict_types=1);

function aiToolRequestToolGroupPackage(): array
{
    return ['request_tool_group' => [
        'group' => 'diagnostic', 'where' => 'browser', 'permission' => true,
        'summary' => 'ask to enable a subject-specific set of tools.',
        'description' => "Asks the operator to enable one additional subject of tools, then opens the Tools settings page where they can inspect and save that choice. It never enables anything by itself.\n\nUSE THIS only after the diagnostic tools have shown that the next question needs a disabled subject, such as host processes, terminal commands, navigation, memory analysis, or pictures. Say what that subject would let you check before calling it.\n\nDO NOT use this as a first step, or to request more than one subject at once. The point of the smaller initial set is to keep the operator in control of what you can see and do.",
        'parameters' => [
            'subject' => ['type' => 'string', 'enum' => ['host', 'memory', 'navigation', 'terminal', 'pictures'], 'required' => true, 'description' => 'The one tool subject that would answer the next question.'],
            'why' => ['type' => 'string', 'maxLength' => 200, 'required' => true, 'description' => 'A concise explanation for the operator of what this subject would let you check.'],
        ],
    ]];
}
