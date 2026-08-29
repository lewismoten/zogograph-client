<?php
declare(strict_types=1);

function releaseExpect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$bom = json_decode((string)file_get_contents($root . '/SBOM.cdx.json'), true);
releaseExpect(is_array($bom) && ($bom['bomFormat'] ?? '') === 'CycloneDX'
    && ($bom['specVersion'] ?? '') === '1.5', 'SBOM is not valid CycloneDX 1.5 metadata');
releaseExpect(($bom['metadata']['component']['name'] ?? '') === 'HostScope'
    && ($bom['components'] ?? null) === [], 'SBOM does not describe the dependency-free HostScope release');
foreach (['THIRD_PARTY_NOTICES.md', 'LICENSE-STATUS.md', 'SECURITY.md'] as $file) {
    releaseExpect(is_file($root . '/' . $file), "$file is missing from release artifacts");
}
$workflow = file_get_contents($root . '/.forgejo/workflows/deploy.yml');
$restoreWorkflow = file_get_contents($root . '/.forgejo/workflows/restore.yml');
releaseExpect(is_string($workflow) && str_contains($workflow, 'Create build manifest')
    && str_contains($workflow, '"commit": "${GITHUB_SHA}"') && str_contains($workflow, '"runId": "${GITHUB_RUN_ID}"')
    && str_contains($workflow, '"createdAt": "${created_at}"') && str_contains($workflow, '"browserRefreshRequired": ${browser_refresh_required}')
    && str_contains($workflow, 'Verify deployed build') && str_contains($workflow, 'build_url="${DEPLOY_URL%/}/build.json"')
    && str_contains($workflow, 'apt-get install -y php-cli php-sqlite3 php-mbstring')
    && str_contains($workflow, 'extension_loaded("zip")') && str_contains($workflow, 'apt-get install -y php-zip'),
    'release workflow no longer creates and verifies the public build metadata required for update notices');
releaseExpect(is_string($workflow) && is_string($restoreWorkflow)
    && str_contains($workflow, 'snapshot_repository="${GITHUB_REPOSITORY##*/}"')
    && str_contains($restoreWorkflow, 'snapshot_repository="${GITHUB_REPOSITORY##*/}"')
    && str_contains($workflow, 'command -v rsync >/dev/null || { apt-get update; apt-get install -y rsync; }')
    && str_contains($restoreWorkflow, 'command -v rsync >/dev/null || { apt-get update; apt-get install -y rsync; }'),
    'deployment workflows no longer provision rsync or isolate snapshots by repository');

echo "release artifact checks passed\n";
