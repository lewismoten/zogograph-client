#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This publisher command is CLI-only.\n");
}
require_once __DIR__ . '/../lib/package-submission.php';
if (
    ($argv[1] ?? '') !== 'prepare' ||
    $argc !== 8 ||
    $argv[2] !== '--package' ||
    $argv[4] !== '--public-key' ||
    $argv[6] !== '--output'
) {
    fwrite(
        STDERR,
        "Usage: php scripts/package-submit.php prepare --package /secure/package --public-key BASE64 --output /secure/review.zip\n",
    );
    exit(64);
}
try {
    $bundle = packageCreateReviewBundle($argv[3], $argv[7], $argv[5]);
    fwrite(
        STDOUT,
        "Review bundle created: {$bundle['output']} ({$bundle['package_id']} {$bundle['version']}, {$bundle['files']} files).\n",
    );
    fwrite(
        STDOUT,
        "Upload this unchanged bundle through the commercial marketplace review form when available.\n",
    );
} catch (Throwable $error) {
    fwrite(
        STDERR,
        'Review-bundle creation refused: ' . $error->getMessage() . "\n",
    );
    exit(1);
}

