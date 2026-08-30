<?php
declare(strict_types=1);
require_once __DIR__ . '/package-integrity.php';

/** Creates a portable, review-only package bundle without executing package code.
 * @return array{package_id:string,version:string,files:int,output:string} */
function packageCreateReviewBundle(
    string $directory,
    string $output,
    string $publicKey,
): array {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException(
            'PHP ZipArchive support is required to create a review bundle',
        );
    }
    $root = realpath($directory);
    if (
        $root === false ||
        !is_file($root . '/manifest.json') ||
        file_exists($output)
    ) {
        throw new InvalidArgumentException(
            'package or review-bundle path is unavailable',
        );
    }
    $manifest = json_decode(
        (string) file_get_contents($root . '/manifest.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    if (!is_array($manifest)) {
        throw new InvalidArgumentException('package manifest is invalid');
    }
    $integrity = packageVerifyIntegrity($manifest, $root);
    $keyId = (string) (($manifest['signature'] ?? [])['key_id'] ?? '');
    $signature = packageVerifySignature($manifest, [
        $keyId => ['public_key' => $publicKey],
    ]);
    if (!$integrity['ok'] || !$signature['ok']) {
        throw new RuntimeException(
            $integrity['ok'] ? $signature['detail'] : $integrity['detail'],
        );
    }
    $archive = new ZipArchive();
    if (
        $archive->open($output, ZipArchive::CREATE | ZipArchive::EXCL) !== true
    ) {
        throw new RuntimeException('could not create review bundle');
    }
    try {
        $archive->addFromString(
            'manifest.json',
            json_encode(
                $manifest,
                JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_THROW_ON_ERROR,
            ) . "\n",
        );
        foreach (array_keys($manifest['integrity']['files']) as $relative) {
            $content = file_get_contents($root . '/' . $relative);
            if (
                !is_string($content) ||
                !$archive->addFromString('package/' . $relative, $content)
            ) {
                throw new RuntimeException(
                    'could not add a signed package file',
                );
            }
        }
        $review = [
            'schema' => 'zogograph-package-review-1',
            'package_id' => $manifest['id'] ?? null,
            'version' => $manifest['version'] ?? null,
            'publisher' => $manifest['publisher'] ?? null,
            'key_id' => $keyId,
            'files' => count($manifest['integrity']['files']),
        ];
        if (
            !$archive->addFromString(
                'review.json',
                json_encode(
                    $review,
                    JSON_PRETTY_PRINT |
                        JSON_UNESCAPED_SLASHES |
                        JSON_THROW_ON_ERROR,
                ) . "\n",
            )
        ) {
            throw new RuntimeException('could not add review metadata');
        }
    } finally {
        $archive->close();
    }
    return [
        'package_id' => (string) ($manifest['id'] ?? ''),
        'version' => (string) ($manifest['version'] ?? ''),
        'files' => count($manifest['integrity']['files']),
        'output' => $output,
    ];
}
