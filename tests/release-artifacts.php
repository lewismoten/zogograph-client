<?php
declare(strict_types=1);

function releaseExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$bom = json_decode((string) file_get_contents($root . '/SBOM.cdx.json'), true);
releaseExpect(
    is_array($bom) &&
        ($bom['bomFormat'] ?? '') === 'CycloneDX' &&
        ($bom['specVersion'] ?? '') === '1.5',
    'SBOM is not valid CycloneDX 1.5 metadata',
);
releaseExpect(
    ($bom['metadata']['component']['name'] ?? '') === 'HostScope' &&
        ($bom['components'] ?? null) === [],
    'SBOM does not describe the dependency-free HostScope release',
);
foreach (
    ['THIRD_PARTY_NOTICES.md', 'LICENSE-STATUS.md', 'SECURITY.md']
    as $file
) {
    releaseExpect(
        is_file($root . '/' . $file),
        "$file is missing from release artifacts",
    );
}
$workflow = file_get_contents($root . '/.forgejo/workflows/deploy.yml');
$restoreWorkflow = file_get_contents($root . '/.forgejo/workflows/restore.yml');
$snapshotRunner = file_get_contents(
    $root . '/.forgejo/scripts/zogograph-run-snapshot.sh',
);
releaseExpect(
    is_string($workflow) &&
        str_contains($workflow, 'Create build manifest') &&
        str_contains($workflow, '"commit": "${GITHUB_SHA}"') &&
        str_contains($workflow, '"runId": "${GITHUB_RUN_ID}"') &&
        str_contains($workflow, '"createdAt": "${created_at}"') &&
        str_contains(
            $workflow,
            'package_version="$(node -e \'process.stdout.write(require("./package.json").version)\')"',
        ) &&
        str_contains($workflow, '"packageVersion": "${package_version}"') &&
        str_contains(
            $workflow,
            '"browserRefreshRequired": ${browser_refresh_required}',
        ) &&
        str_contains($workflow, 'environment: test') &&
        str_contains($workflow, 'environment: client') &&
        str_contains($workflow, 'deploy_path: ${{ vars.TEST_PATH }}') &&
        str_contains($workflow, 'deploy_url: ${{ vars.TEST_URL }}') &&
        str_contains($workflow, 'deploy_path: ${{ vars.DEPLOY_PATH }}') &&
        str_contains($workflow, 'deploy_url: ${{ vars.DEPLOY_URL }}') &&
        str_contains($workflow, 'Verify deployed build') &&
        str_contains($workflow, 'build_url="${DEPLOY_URL%/}/build.json"') &&
        str_contains($workflow, "php: ['8.1', '8.2', '8.3', '8.4', '8.5']") &&
        str_contains($workflow, 'runner-image:php-${{ matrix.php }}') &&
        str_contains($workflow, 'runner-image:php-8.5'),
    'release workflow no longer creates and verifies the public build metadata required for update notices',
);
releaseExpect(
        is_string($workflow) &&
        str_contains($workflow, 'release=zogograph-release') &&
        str_contains($workflow, 'Assemble deployment release') &&
        !str_contains($workflow, 'download-artifact@v3') &&
        str_contains($workflow, "--exclude='node_modules/'") &&
        str_contains($workflow, 'test -s zogograph-release/build.json'),
    'release workflow no longer stages a visible, verified deployment artifact',
);
releaseExpect(
    is_string($workflow) &&
        is_string($restoreWorkflow) &&
        str_contains(
            $workflow,
            'snapshot_repository="${GITHUB_REPOSITORY##*/}"',
        ) &&
        str_contains(
            $restoreWorkflow,
            'snapshot_repository="${GITHUB_REPOSITORY##*/}"',
        ) &&
        str_contains(
            $workflow,
            'command -v rsync >/dev/null || { timeout 1m apt-get update; timeout 1m apt-get install -y rsync; }',
        ) &&
        str_contains(
            $restoreWorkflow,
            'command -v rsync >/dev/null || { timeout 1m apt-get update; timeout 1m apt-get install -y rsync; }',
        ) &&
        str_contains(
            $restoreWorkflow,
            'github.event.inputs.target == matrix.environment',
        ) &&
        str_contains(
            $restoreWorkflow,
            'DEPLOY_PATH: ${{ matrix.deploy_path }}',
        ) &&
        str_contains(
            $workflow,
            'snapshot_site:',
        ) &&
        str_contains($workflow, 'snapshot_databases:') &&
        str_contains($workflow, 'needs: [build, snapshot_databases]') &&
        str_contains(
            $workflow,
            'run: bash .forgejo/scripts/zogograph-run-snapshot.sh site',
        ) &&
        str_contains(
            $workflow,
            'run: bash .forgejo/scripts/zogograph-run-snapshot.sh databases',
        ) &&
        is_string($snapshotRunner) &&
        str_contains(
            $snapshotRunner,
            'remote_script=".zogograph-snapshot-${GITHUB_RUN_ID}-${DEPLOY_TARGET}.sh"',
        ) &&
        str_contains($snapshotRunner, 'site|databases') &&
        str_contains($snapshotRunner, 'snapshot_phase 1m prepare') &&
        str_contains($snapshotRunner, 'snapshot_phase 1m baseline') &&
        str_contains($snapshotRunner, 'snapshot_phase 1m site') &&
        str_contains($snapshotRunner, 'snapshot_phase 2m database data/host.db') &&
        str_contains($snapshotRunner, 'snapshot_phase 1m finalize') &&
        str_contains($snapshotRunner, ' discard; rm -f') &&
        str_contains(
            $restoreWorkflow,
            'remote_script=".zogograph-restore-${GITHUB_RUN_ID}-${DEPLOY_TARGET}.sh"',
        ),
    'deployment workflows no longer provision rsync, isolate snapshots, or target restores safely',
);

echo "release artifact checks passed\n";
