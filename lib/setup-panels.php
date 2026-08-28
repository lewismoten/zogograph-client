<?php
declare(strict_types=1);

/* Metadata-only Setup panel registry. Renderers stay core-owned until package
 * execution has an independently attested isolation boundary. */

/** @return array<string, array{id:string,title:string,kind:string}> */
function setupPanels(array $fields): array
{
    $panels = [
        'basics' => ['id' => 'basics', 'title' => 'Installation', 'kind' => 'core'],
        'privacy' => ['id' => 'privacy', 'title' => 'Privacy', 'kind' => 'core'],
        'schedule' => ['id' => 'schedule', 'title' => 'Schedule', 'kind' => 'core'],
    ];
    foreach ($fields as $field) {
        $id = (string)($field['section'] ?? 'basics');
        $panels[$id] ??= ['id' => $id, 'title' => ucfirst(str_replace('_', ' ', $id)), 'kind' => 'core'];
    }
    return $panels;
}

/** Future isolated package registration must use this validator. */
function setupPanelId(string $id): string
{
    if (!preg_match('/^[a-z][a-z0-9-]{1,62}$/', $id)) {
        throw new InvalidArgumentException('setup panel id is invalid');
    }
    return $id;
}
