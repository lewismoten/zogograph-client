<?php
declare(strict_types=1);

/* A destructive maintenance helper: never permit a web request to run it. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("no-mac.php runs from the command line only.\n");
}

if (($argv[1] ?? '') === '--help' || ($argv[1] ?? '') === '-h') {
    echo "Usage: php no-mac.php DIRECTORY\n";
    exit(0);
}

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php no-mac.php DIRECTORY\n");
    exit(2);
}

$root = realpath($argv[1]);

function deleteDirectory(string $path): void
{
    $items = scandir($path);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $child = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($child) && !is_link($child)) {
            deleteDirectory($child);
        } else {
            unlink($child);
        }
    }

    rmdir($path);
}

function cleanMacFiles(string $directory): void
{
    $items = scandir($directory);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path) && !is_link($path)) {
            if ($item === '__MACOSX') {
                echo "Deleting directory: {$path}\n";
                deleteDirectory($path);
                continue;
            }

            cleanMacFiles($path);
            continue;
        }

        if (str_starts_with($item, '._')) {
            echo "Deleting file: {$path}\n";
            unlink($path);
        }
    }
}

if ($root === false || !is_dir($root)) {
    fwrite(STDERR, "Directory does not exist or cannot be resolved.\n");
    exit(1);
}
if ($root === DIRECTORY_SEPARATOR) {
    fwrite(STDERR, "Refusing to clean the filesystem root.\n");
    exit(1);
}

echo "Cleaning: {$root}\n";

cleanMacFiles($root);

echo "Done.\n";
