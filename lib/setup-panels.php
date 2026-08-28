<?php
declare(strict_types=1);

/* Metadata-only Setup panel registry. Renderers stay core-owned until package
 * execution has an independently attested isolation boundary. */

/** Normalizes inert panel metadata before it reaches Setup navigation. */
function setupPanelDefinition(array $panel): array
{
    $id = setupPanelId((string)($panel['id'] ?? ''));
    $title = trim((string)($panel['title'] ?? ''));
    $kind = (string)($panel['kind'] ?? 'core');
    if ($title === '' || mb_strlen($title) > 80 || !in_array($kind, ['core', 'package'], true)) {
        throw new InvalidArgumentException('setup panel metadata is invalid');
    }
    $keywords = array_values(array_unique(array_filter((array)($panel['keywords'] ?? []), static fn(mixed $word): bool => is_string($word) && preg_match('/^[a-z0-9 _-]{1,40}$/i', $word))));
    return ['id' => $id, 'title' => $title, 'kind' => $kind, 'keywords' => $keywords,
        'feature' => isset($panel['feature']) ? setupPanelId((string)$panel['feature']) : null];
}

/**
 * Core supplies sections derived from fields; an isolated runtime may later
 * supply additional metadata. This function does not execute a package
 * renderer, which keeps a manifest from becoming executable setup code.
 *
 * @return array<string, array{id:string,title:string,kind:string,keywords:list<string>,feature:?string}>
 */
function setupPanels(array $fields, array $registered = []): array
{
    $panels = [
        'basics' => ['id' => 'basics', 'title' => 'Installation', 'kind' => 'core', 'keywords' => ['checks', 'prerequisites'], 'feature' => null],
        'privacy' => ['id' => 'privacy', 'title' => 'Privacy', 'kind' => 'core', 'keywords' => ['mask', 'paths'], 'feature' => null],
        'schedule' => ['id' => 'schedule', 'title' => 'Schedule', 'kind' => 'core', 'keywords' => ['cron', 'vitals', 'scan'], 'feature' => null],
    ];
    foreach ($fields as $field) {
        $id = (string)($field['section'] ?? 'basics');
        $panels[$id] ??= ['id' => $id, 'title' => ucfirst(str_replace('_', ' ', $id)), 'kind' => 'core', 'keywords' => [], 'feature' => null];
    }
    foreach ($registered as $panel) {
        $definition = setupPanelDefinition((array)$panel);
        if (isset($panels[$definition['id']])) throw new InvalidArgumentException('setup panel id is already registered');
        $panels[$definition['id']] = $definition;
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
