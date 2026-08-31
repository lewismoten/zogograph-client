<?php
declare(strict_types=1);

/* Browser error reports are useful before a person can sign in, but that
 * endpoint must not become a public way to fill the dashboard's own log. */
const ANONYMOUS_CLIENT_PROBLEM_WINDOW = 3600;
const ANONYMOUS_CLIENT_PROBLEM_REPORTER_LIMIT = 256;

function clientProblemReporterHash(array $config): string
{
    $address = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $secret = (string) ($config['mask_secret'] ?? '');
    if ($secret === '') {
        // This is still not the address itself. A configured mask secret makes
        // the value non-reversible; the fallback keeps an incomplete setup
        // from writing a raw address into its database.
        $secret = hash('sha256', (string) ($config['database'] ?? ''));
    }
    return hash_hmac('sha256', $address, $secret);
}

/** Allows one anonymous browser diagnostic per source address per hour. */
function anonymousClientProblemAllowed(
    PDO $pdo,
    array $config,
    ?int $now = null,
): bool {
    $now ??= time();
    $cutoff = $now - ANONYMOUS_CLIENT_PROBLEM_WINDOW;
    $pdo->prepare(
        'DELETE FROM client_problem_rate_limits WHERE reported_at <= ?',
    )->execute([$cutoff]);

    $reporter = clientProblemReporterHash($config);
    $known = $pdo->prepare(
        'SELECT reported_at FROM client_problem_rate_limits WHERE reporter_hash = ?',
    );
    $known->execute([$reporter]);
    if ($known->fetchColumn() !== false) {
        return false;
    }

    // Bound the table as well as the log. A botnet must not turn this modest
    // diagnostic allowance into a growing SQLite table.
    $count = (int) $pdo
        ->query('SELECT COUNT(*) FROM client_problem_rate_limits')
        ->fetchColumn();
    if ($count >= ANONYMOUS_CLIENT_PROBLEM_REPORTER_LIMIT) {
        return false;
    }

    $pdo->prepare(
        'INSERT INTO client_problem_rate_limits (reporter_hash, reported_at) VALUES (?, ?)',
    )->execute([$reporter, $now]);
    return true;
}
