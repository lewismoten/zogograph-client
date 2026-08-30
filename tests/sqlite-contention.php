<?php
declare(strict_types=1);

/* A second writer must wait for SQLite's configured busy timeout, then recover. */

function contentionExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$directory =
    sys_get_temp_dir() . '/zogograph-contention-' . bin2hex(random_bytes(8));
contentionExpect(
    mkdir($directory, 0700),
    'could not create a temporary test directory',
);
$database = $directory . '/contention.db';

try {
    $writer = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $contender = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $writer->exec('PRAGMA journal_mode = WAL');
    $contender->exec('PRAGMA busy_timeout = 100');
    $writer->exec(
        'CREATE TABLE events (id INTEGER PRIMARY KEY, value TEXT NOT NULL)',
    );
    $writer->beginTransaction();
    $writer->exec("INSERT INTO events (value) VALUES ('held')");

    $started = microtime(true);
    try {
        $contender->exec("INSERT INTO events (value) VALUES ('blocked')");
        throw new RuntimeException(
            'a concurrent writer unexpectedly bypassed the lock',
        );
    } catch (PDOException $e) {
        contentionExpect(
            microtime(true) - $started >= 0.075,
            'the SQLite busy timeout was not honored before reporting contention',
        );
    }

    $writer->commit();
    $contender->exec("INSERT INTO events (value) VALUES ('recovered')");
    contentionExpect(
        (int) $contender
            ->query('SELECT COUNT(*) FROM events')
            ->fetchColumn() === 2,
        'a writer did not recover after the lock was released',
    );
    $writer = null;
    $contender = null;
    unlink($database);
    @unlink($database . '-wal');
    @unlink($database . '-shm');
    rmdir($directory);
    echo "SQLite contention checks passed\n";
} catch (Throwable $e) {
    @unlink($database);
    @unlink($database . '-wal');
    @unlink($database . '-shm');
    @rmdir($directory);
    throw $e;
}
