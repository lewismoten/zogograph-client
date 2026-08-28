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

echo "release artifact checks passed\n";
