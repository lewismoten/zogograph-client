<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/peertransport.php';
require_once __DIR__ . '/../lib/peers.php';

$ingress = file_get_contents(__DIR__ . '/../peer-push.php');
if (
    $ingress === false ||
    !str_contains($ingress, "'service' => 'zogograph-peer-transport'") ||
    !str_contains($ingress, "(string)(\$_GET['health'] ?? '') === '1'")
) {
    throw new RuntimeException(
        'peer ingress does not expose the bounded protocol-health response',
    );
}

function transportExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
function transportRefused(callable $call, string $message): void
{
    try {
        $call();
    } catch (ActionRefused) {
        return;
    }
    throw new RuntimeException($message);
}

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec(
    'CREATE TABLE remote_peer_receipts (peer TEXT, nonce TEXT, received_at INTEGER, PRIMARY KEY (peer, nonce))',
);
$pdo->exec(
    'CREATE TABLE remote_peer_vitals (peer TEXT, host TEXT, taken_at INTEGER, received_at INTEGER, body TEXT, PRIMARY KEY (peer, taken_at))',
);
$pdo->exec(
    'CREATE TABLE remote_delivery_queue (id INTEGER PRIMARY KEY, endpoint TEXT, peer TEXT, taken_at INTEGER, created_at INTEGER, body TEXT, attempts INTEGER NOT NULL DEFAULT 0, next_attempt_at INTEGER NOT NULL DEFAULT 0, last_error TEXT, UNIQUE (peer, taken_at))',
);
$pdo->exec(
    'CREATE TABLE remote_delivery_state (endpoint TEXT, peer TEXT, last_scheduled_at INTEGER, PRIMARY KEY (endpoint, peer))',
);
$secret = bin2hex(random_bytes(32));
$enrollment = peerTransportEnrollment(
    'remote-a',
    'https://controller.example/peer-push.php',
    $secret,
);
transportExpect(
    str_contains($enrollment, "'peer' => 'remote-a'") &&
        str_contains($enrollment, "'credential' => '$secret'"),
    'remote pairing fragment did not contain the exact one-time enrollment data',
);
$config = ['peers' => ['remote-a' => ['transport_secret' => $secret]]];
$now = time();
$body = json_encode([
    'version' => 1,
    'host' => 'remote-a.example',
    'taken_at' => $now,
    'vitals' => ['cpu_percent' => 12.5],
]);
$nonce = 'nonce_' . str_repeat('a', 32);
$headers = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $nonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $nonce,
        $body,
    ),
];
$accepted = peerTransportAccept($pdo, $config, $headers, $body);
transportExpect(
    $accepted['peer'] === 'remote-a' &&
        $accepted['vitals']['cpu_percent'] === 12.5,
    'valid signed remote telemetry was not accepted',
);
transportExpect(
    peerTransportResponse(
        json_encode([
            'ok' => true,
            'protocol_version' => PEER_TRANSPORT_PROTOCOL,
        ]),
    )['ok'],
    'matching controller protocol acknowledgement was rejected',
);
transportRefused(
    fn() => peerTransportResponse(
        json_encode(['ok' => true, 'protocol_version' => 2]),
    ),
    'incompatible controller protocol acknowledgement was accepted',
);
$pendingConfig = [
    'peers' => [
        'remote-a' => [
            'transport_secret' => $secret,
            'transport_pending_until' => $now + 60,
        ],
    ],
];
$pendingNonce = 'nonce_' . str_repeat('p', 32);
$pendingHeaders = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $pendingNonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $pendingNonce,
        $body,
    ),
];
transportExpect(
    peerTransportAccept($pdo, $pendingConfig, $pendingHeaders, $body)[
        'activate'
    ],
    'a valid delivery did not activate a pending pairing credential',
);
$expiredConfig = [
    'peers' => [
        'remote-a' => [
            'transport_secret' => $secret,
            'transport_pending_until' => $now - 1,
        ],
    ],
];
$expiredNonce = 'nonce_' . str_repeat('e', 32);
$expiredHeaders = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $expiredNonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $expiredNonce,
        $body,
    ),
];
transportRefused(
    fn() => peerTransportAccept($pdo, $expiredConfig, $expiredHeaders, $body),
    'expired pairing credential was accepted',
);
$latest = peerTransportLatest($pdo);
transportExpect(
    $latest['remote-a']['host'] === 'remote-a.example' &&
        $latest['remote-a']['vitals']['cpu_percent'] === 12.5,
    'latest accepted remote telemetry was not available for fleet summary',
);
$older = json_encode([
    'version' => 1,
    'host' => 'older.example',
    'taken_at' => $now - 1,
    'vitals' => ['cpu_percent' => 1.0],
]);
$olderNonce = 'nonce_' . str_repeat('o', 32);
$olderHeaders = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $olderNonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $olderNonce,
        $older,
    ),
];
peerTransportAccept($pdo, $config, $olderHeaders, $older);
transportExpect(
    peerTransportLatest($pdo)['remote-a']['taken_at'] === $now,
    'fleet summary selected an older remote reading',
);
$altered = json_encode([
    'version' => 1,
    'host' => 'remote-a.example',
    'taken_at' => $now,
    'vitals' => ['cpu_percent' => 99.9],
]);
$alteredNonce = 'nonce_' . str_repeat('c', 32);
$alteredHeaders = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $alteredNonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $alteredNonce,
        $altered,
    ),
];
peerTransportAccept($pdo, $config, $alteredHeaders, $altered);
transportExpect(
    peerTransportLatest($pdo)['remote-a']['vitals']['cpu_percent'] === 12.5,
    'a second signed delivery overwrote the original remote evidence',
);
$failing = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$failing->exec(
    'CREATE TABLE remote_peer_receipts (peer TEXT, nonce TEXT, received_at INTEGER, PRIMARY KEY (peer, nonce))',
);
$failing->exec(
    'CREATE TABLE remote_peer_vitals (peer TEXT, host TEXT, taken_at INTEGER, received_at INTEGER, body TEXT CHECK (0), PRIMARY KEY (peer, taken_at))',
);
$failedNonce = 'nonce_' . str_repeat('d', 32);
$failedHeaders = [
    'peer' => 'remote-a',
    'timestamp' => (string) $now,
    'nonce' => $failedNonce,
    'signature' => peerTransportSignature(
        $secret,
        'remote-a',
        $now,
        $failedNonce,
        $body,
    ),
];
try {
    peerTransportAccept($failing, $config, $failedHeaders, $body);
} catch (PDOException) {
}
transportExpect(
    (int) $failing
        ->query('SELECT COUNT(*) FROM remote_peer_receipts')
        ->fetchColumn() === 0,
    'failed remote vital write left a replay receipt behind',
);
if (function_exists('gzencode')) {
    $compressed = gzencode($body, 6);
    $zipNonce = 'nonce_' . str_repeat('z', 32);
    $zipHeaders = [
        'peer' => 'remote-a',
        'timestamp' => (string) $now,
        'nonce' => $zipNonce,
        'encoding' => 'gzip',
        'signature' => peerTransportSignature(
            $secret,
            'remote-a',
            $now,
            $zipNonce,
            $compressed,
        ),
    ];
    transportExpect(
        peerTransportAccept($pdo, $config, $zipHeaders, $compressed)['peer'] ===
            'remote-a',
        'valid signed compressed remote telemetry was not accepted',
    );
}
transportRefused(
    fn() => peerTransportAccept($pdo, $config, $headers, $body),
    'a replayed nonce was accepted',
);
$bad = $headers;
$bad['nonce'] = 'nonce_' . str_repeat('b', 32);
transportRefused(
    fn() => peerTransportAccept($pdo, $config, $bad, $body),
    'a forged signature was accepted',
);
transportRefused(
    fn() => peerTransportBody($body, 'br'),
    'unsupported remote payload encoding was accepted',
);
transportRefused(
    fn() => peerTransportEndpointPolicy(
        'http://controller.example/peer-push.php',
        [],
    ),
    'plain HTTP controller endpoint was accepted',
);
transportRefused(
    fn() => peerTransportEndpointPolicy('https://127.0.0.1/peer-push.php', []),
    'unapproved private controller endpoint was accepted',
);
$private = peerTransportEndpointPolicy('https://127.0.0.1/peer-push.php', [
    'remote_delivery' => ['private_controller_hosts' => ['127.0.0.1']],
]);
transportExpect(
    $private['resolve'] === ['127.0.0.1:443:127.0.0.1'],
    'approved controller was not pinned',
);
peerTransportQueue(
    $pdo,
    'https://controller.example/peer-push.php',
    'remote-a',
    $body,
    $now,
);
peerTransportQueue(
    $pdo,
    'https://controller.example/peer-push.php',
    'remote-a',
    $body,
    $now,
);
transportExpect(
    (int) $pdo
        ->query('SELECT COUNT(*) FROM remote_delivery_queue')
        ->fetchColumn() === 1,
    'queued remote delivery was not deduplicated',
);
$id = (int) $pdo->query('SELECT id FROM remote_delivery_queue')->fetchColumn();
peerTransportDefer($pdo, $id, 'offline');
$deferred = $pdo
    ->query(
        'SELECT attempts, next_attempt_at, last_error FROM remote_delivery_queue',
    )
    ->fetch();
transportExpect(
    (int) $deferred['attempts'] === 1 &&
        (int) $deferred['next_attempt_at'] > time() &&
        $deferred['last_error'] === 'offline',
    'queued remote delivery did not back off after failure',
);
$endpoint = 'https://controller.example/peer-push.php';
transportExpect(
    peerTransportDue($pdo, $endpoint, 'remote-a', []),
    'an unseen remote delivery was not due',
);
peerTransportScheduled($pdo, $endpoint, 'remote-a');
transportExpect(
    !peerTransportDue($pdo, $endpoint, 'remote-a', [
        'remote_delivery' => ['interval_seconds' => 3600],
    ]),
    'remote delivery interval did not rate-limit a new reading',
);
echo "peer transport checks passed\n";
