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

/** Registry shared by core Setup and the future isolated package bridge. */
final class SetupPanelRegistry
{
    /** @var array<string, array{id:string,title:string,kind:string,keywords:list<string>,feature:?string}> */
    private array $panels = [];

    public function __construct(array $fields)
    {
        foreach ([
            ['id' => 'basics', 'title' => 'Installation', 'kind' => 'core', 'keywords' => ['checks', 'prerequisites']],
            ['id' => 'privacy', 'title' => 'Privacy', 'kind' => 'core', 'keywords' => ['mask', 'paths']],
            ['id' => 'schedule', 'title' => 'Schedule', 'kind' => 'core', 'keywords' => ['cron', 'vitals', 'scan']],
        ] as $panel) $this->add($panel);
        foreach ($fields as $field) {
            $id = (string)($field['section'] ?? 'basics');
            if (!isset($this->panels[$id])) {
                $this->add(['id' => $id, 'title' => ucfirst(str_replace('_', ' ', $id)), 'kind' => 'core']);
            }
        }
    }

    public function add(array $panel): void
    {
        $definition = setupPanelDefinition((array)$panel);
        if (isset($this->panels[$definition['id']])) throw new InvalidArgumentException('setup panel id is already registered');
        $this->panels[$definition['id']] = $definition;
    }

    /** @return array<string, array{id:string,title:string,kind:string,keywords:list<string>,feature:?string}> */
    public function all(): array { return $this->panels; }
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
    $registry = new SetupPanelRegistry($fields);
    foreach ($registered as $panel) $registry->add((array)$panel);
    return $registry->all();
}

/** Future isolated package registration must use this validator. */
function setupPanelId(string $id): string
{
    // Existing core sections include names such as package_runtime. Preserve
    // those stable navigation URLs while applying the same bounded grammar to
    // future package metadata.
    if (!preg_match('/^[a-z][a-z0-9_-]{1,62}$/', $id)) {
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

/**
 * Capability object intended for an independently attested package bridge.
 * It deliberately omits raw config, files, shell access, and HTML renderers.
 */
final class SetupPanelApi
{
    public function __construct(
        private readonly string $packageId,
        private readonly SetupPanelRegistry $registry,
        private readonly SetupCronApi $cron
    ) { setupPanelId($packageId); }

    /** Registers inert package-owned navigation metadata, never a callback. */
    public function addPanel(array $panel): void
    {
        $panel['kind'] = 'package';
        // The caller cannot make its panel appear to belong to a different
        // feature or package. The bridge supplies this object per package.
        $panel['feature'] = $this->packageId;
        $this->registry->add($panel);
    }

    public function cron(): SetupCronApi { return $this->cron; }
}
