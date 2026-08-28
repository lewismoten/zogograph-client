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

/**
 * Narrow, reusable cron capability for core Setup panels and future isolated
 * packages. Packages supply schedule choices, never executable cron commands.
 */
final class SetupCronApi
{
    public function __construct(private readonly string $scriptDir) {}

    public function profiles(): array { return cronEditorProfiles(); }
    public function preview(array $jobs, string $php): array { return cronEditorBuildLines($jobs, $php, $this->scriptDir); }
    public function status(array $jobs, string $php): array
    {
        return cronEditorStatus($this->preview($jobs, $php), $this->scriptDir);
    }
    public function apply(array $jobs, string $php): array { return cronEditorApply($jobs, $php, $this->scriptDir); }
}
