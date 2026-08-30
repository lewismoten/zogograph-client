<?php
declare(strict_types=1);

/* Signed, replay-protected inbound telemetry from an explicitly paired peer. */

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/outbound.php';

const PEER_TRANSPORT_MAX_BYTES = 262144;
const PEER_TRANSPORT_SKEW = 300;
const PEER_TRANSPORT_PAIR_TTL = 86400;
const PEER_TRANSPORT_PROTOCOL = 1;

function peerTransportSignature(
    string $secret,
    string $peer,
    int $at,
    string $nonce,
    string $body,
): string {
    return hash_hmac(
        'sha256',
        $peer . "\n" . $at . "\n" . $nonce . "\n" . $body,
        $secret,
    );
}

/** @return array{url: string, resolve: list<string>} */
function peerTransportEndpointPolicy(string $endpoint, array $config): array
{
    $endpoint = trim($endpoint);
    $parts = parse_url($endpoint);
    if (
        $endpoint === '' ||
        $parts === false ||
        strtolower((string) ($parts['scheme'] ?? '')) !== 'https' ||
        empty($parts['host']) ||
        isset($parts['user']) ||
        isset($parts['pass']) ||
        isset($parts['query']) ||
        isset($parts['fragment'])
    ) {
        throw new ActionRefused(
            'remote controller endpoint must be a plain HTTPS URL',
        );
    }
    $host = strtolower(trim((string) $parts['host'], '[]'));
    $addresses = outboundHostAddresses($host);
    $allowed = array_map(
        fn(mixed $value): string => strtolower(trim((string) $value)),
        (array) ($config['remote_delivery']['private_controller_hosts'] ?? []),
    );
    if (
        $addresses === [] ||
        (array_filter($addresses, 'outboundPublicAddress') !== $addresses &&
            !in_array($host, $allowed, true))
    ) {
        throw new ActionRefused(
            'remote controller must resolve publicly or be named in remote_delivery.private_controller_hosts',
        );
    }
    $port = (int) ($parts['port'] ?? 443);
    return [
        'url' => $endpoint,
        'resolve' => array_map(
            fn(string $address): string => $host . ':' . $port . ':' . $address,
            $addresses,
        ),
    ];
}

/** Validates the controller's explicit protocol acknowledgement. */
function peerTransportResponse(string $response): array
{
    $decoded = json_decode($response, true);
    $version = is_array($decoded)
        ? (int) ($decoded['protocol_version'] ?? 0)
        : 0;
    if (
        !is_array($decoded) ||
        empty($decoded['ok']) ||
        $version !== PEER_TRANSPORT_PROTOCOL
    ) {
        throw new ActionRefused(
            'remote controller protocol is incompatible; update both Zogograph installations',
        );
    }
    return $decoded;
}

/** Sends one already-encoded body, with a fresh timestamp and nonce each time. */
function peerTransportPost(
    array $policy,
    string $peer,
    string $secret,
    string $body,
): array {
    $at = time();
    $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    $wire = $body;
    $encoding = null;
    if (function_exists('gzencode')) {
        $compressed = gzencode($body, 6);
        if (is_string($compressed) && strlen($compressed) < strlen($body)) {
            $wire = $compressed;
            $encoding = 'gzip';
        }
    }
    $curl = curl_init($policy['url']);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $wire,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_RESOLVE => $policy['resolve'],
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Zogograph-Peer: ' . $peer,
            'X-Zogograph-Timestamp: ' . $at,
            'X-Zogograph-Nonce: ' . $nonce,
            'X-Zogograph-Signature: ' .
            peerTransportSignature($secret, $peer, $at, $nonce, $wire),
            ...$encoding === null ? [] : ['Content-Encoding: ' . $encoding],
        ],
    ]);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($response === false || $status !== 202) {
        throw new ActionRefused(
            $error !== ''
                ? $error
                : 'remote controller returned HTTP ' . $status,
        );
    }
    return peerTransportResponse($response);
}

/** Decodes only the content encoding this transport explicitly supports. */
function peerTransportBody(string $body, string $encoding): string
{
    $encoding = strtolower(trim($encoding));
    if ($encoding === '') {
        return $body;
    }
    if ($encoding !== 'gzip' || !function_exists('gzdecode')) {
        throw new ActionRefused(
            'remote payload content encoding is unsupported',
        );
    }
    $decoded = gzdecode($body, PEER_TRANSPORT_MAX_BYTES);
    if (
        !is_string($decoded) ||
        $decoded === '' ||
        strlen($decoded) > PEER_TRANSPORT_MAX_BYTES
    ) {
        throw new ActionRefused(
            'remote compressed payload is invalid or too large',
        );
    }
    return $decoded;
}

/** Retains at most fifty undelivered readings, one per peer/collection time. */
function peerTransportQueue(
    PDO $pdo,
    string $endpoint,
    string $peer,
    string $body,
    int $takenAt,
): void {
    $pdo->prepare(
        'INSERT INTO remote_delivery_queue (endpoint, peer, taken_at, created_at, body)
        VALUES (?, ?, ?, ?, ?) ON CONFLICT(peer, taken_at) DO NOTHING',
    )->execute([$endpoint, $peer, $takenAt, time(), $body]);
    $pdo->exec('DELETE FROM remote_delivery_queue WHERE id NOT IN (
        SELECT id FROM remote_delivery_queue ORDER BY created_at DESC, id DESC LIMIT 50
    )');
}

/** Defers a failed delivery by 1, 2, 4 … minutes, capped at one hour. */
function peerTransportDefer(PDO $pdo, int $id, string $error): void
{
    $stmt = $pdo->prepare(
        'SELECT attempts FROM remote_delivery_queue WHERE id = ?',
    );
    $stmt->execute([$id]);
    $attempts = (int) $stmt->fetchColumn();
    $seconds = min(3600, 60 * 2 ** min(6, $attempts));
    $pdo->prepare(
        'UPDATE remote_delivery_queue SET attempts = ?, next_attempt_at = ?, last_error = ? WHERE id = ?',
    )->execute([
        $attempts + 1,
        time() + $seconds,
        mb_substr($error, 0, 500),
        $id,
    ]);
}

/** New remote readings are rate-limited independently of queued retries. */
function peerTransportInterval(array $config): int
{
    return max(
        60,
        min(
            86400,
            (int) ($config['remote_delivery']['interval_seconds'] ?? 60),
        ),
    );
}

function peerTransportDue(
    PDO $pdo,
    string $endpoint,
    string $peer,
    array $config,
): bool {
    $stmt = $pdo->prepare(
        'SELECT last_scheduled_at FROM remote_delivery_state WHERE endpoint = ? AND peer = ?',
    );
    $stmt->execute([$endpoint, $peer]);
    $last = (int) $stmt->fetchColumn();
    return $last === 0 || $last + peerTransportInterval($config) <= time();
}

function peerTransportScheduled(PDO $pdo, string $endpoint, string $peer): void
{
    $pdo->prepare(
        'INSERT INTO remote_delivery_state (endpoint, peer, last_scheduled_at) VALUES (?, ?, ?)
        ON CONFLICT(endpoint, peer) DO UPDATE SET last_scheduled_at = excluded.last_scheduled_at',
    )->execute([$endpoint, $peer, time()]);
}

/** @return array<string, array{host: string, taken_at: int, received_at: int, vitals: array<string, int|float|null>}> */
function peerTransportLatest(PDO $pdo): array
{
    $out = [];
    $rows = $pdo
        ->query(
            'SELECT peer, host, taken_at, received_at, body FROM remote_peer_vitals
        WHERE NOT EXISTS (
            SELECT 1 FROM remote_peer_vitals AS newer
            WHERE newer.peer = remote_peer_vitals.peer
              AND (newer.received_at > remote_peer_vitals.received_at
                OR (newer.received_at = remote_peer_vitals.received_at
                    AND newer.taken_at > remote_peer_vitals.taken_at))
        )
        ORDER BY peer',
        )
        ->fetchAll();
    foreach ($rows as $row) {
        $body = json_decode((string) $row['body'], true);
        if (!is_array($body)) {
            continue;
        }
        $vitals = [];
        foreach ($body as $name => $value) {
            if (
                is_string($name) &&
                (is_int($value) || is_float($value) || $value === null)
            ) {
                $vitals[$name] = $value;
            }
        }
        $out[(string) $row['peer']] = [
            'host' => (string) $row['host'],
            'taken_at' => (int) $row['taken_at'],
            'received_at' => (int) $row['received_at'],
            'vitals' => $vitals,
        ];
    }
    return $out;
}

/** Delivers the latest local vital reading and retries a small queued batch first. */
function peerTransportSend(array $config, PDO $pdo): ?array
{
    $set = (array) ($config['remote_delivery'] ?? []);
    $endpoint = trim((string) ($set['endpoint'] ?? ''));
    $peer = strtolower(trim((string) ($set['peer'] ?? '')));
    $secret = trim((string) ($set['credential'] ?? ''));
    if ($endpoint === '' && $peer === '' && $secret === '') {
        return null;
    }
    if (
        !preg_match('/^[a-z0-9][a-z0-9_-]{0,40}$/', $peer) ||
        strlen($secret) < 32
    ) {
        throw new ActionRefused(
            'remote delivery needs an endpoint, peer name, and pairing credential',
        );
    }
    $policy = peerTransportEndpointPolicy($endpoint, $config);
    $row = $pdo
        ->query(
            'SELECT taken_at, disk_total_bytes, disk_free_bytes, inodes_total, inodes_free,
        cpu_percent, mem_used_percent, load1, cpu_cores FROM vitals ORDER BY taken_at DESC, id DESC LIMIT 1',
        )
        ->fetch();
    if (!is_array($row)) {
        throw new ActionRefused(
            'there is no local vital reading to deliver yet',
        );
    }
    $vitals = [];
    foreach (array_diff(array_keys($row), ['taken_at']) as $key) {
        $vitals[$key] =
            $row[$key] === null
                ? null
                : (is_numeric($row[$key])
                    ? (float) $row[$key]
                    : null);
    }
    $body = json_encode(
        [
            'version' => PEER_TRANSPORT_PROTOCOL,
            'host' => php_uname('n'),
            'taken_at' => (int) $row['taken_at'],
            'vitals' => $vitals,
        ],
        JSON_UNESCAPED_SLASHES,
    );
    if (!is_string($body)) {
        throw new ActionRefused('could not encode remote vital payload');
    }
    $due = peerTransportDue($pdo, $endpoint, $peer, $config);
    if (!function_exists('curl_init')) {
        if ($due) {
            peerTransportQueue(
                $pdo,
                $endpoint,
                $peer,
                $body,
                (int) $row['taken_at'],
            );
            peerTransportScheduled($pdo, $endpoint, $peer);
        }
        return [
            'peer' => $peer,
            'taken_at' => (int) $row['taken_at'],
            'queued' => $due ? 1 : 0,
            'delivered' => 0,
            'skipped' => !$due,
        ];
    }
    $delivered = 0;
    $queued = $pdo->prepare('SELECT id, body FROM remote_delivery_queue WHERE endpoint = ? AND peer = ? AND next_attempt_at <= ?
        ORDER BY created_at, id LIMIT 3');
    $queued->execute([$endpoint, $peer, time()]);
    foreach ($queued as $entry) {
        try {
            peerTransportPost($policy, $peer, $secret, (string) $entry['body']);
            $pdo->prepare(
                'DELETE FROM remote_delivery_queue WHERE id = ?',
            )->execute([(int) $entry['id']]);
            $delivered++;
        } catch (ActionRefused $e) {
            peerTransportDefer($pdo, (int) $entry['id'], $e->getMessage());
            if ($due) {
                peerTransportQueue(
                    $pdo,
                    $endpoint,
                    $peer,
                    $body,
                    (int) $row['taken_at'],
                );
                peerTransportScheduled($pdo, $endpoint, $peer);
            }
            return [
                'peer' => $peer,
                'taken_at' => (int) $row['taken_at'],
                'queued' => $due ? 1 : 0,
                'delivered' => $delivered,
                'skipped' => !$due,
            ];
        }
    }
    if (!$due) {
        return [
            'peer' => $peer,
            'taken_at' => (int) $row['taken_at'],
            'queued' => 0,
            'delivered' => $delivered,
            'skipped' => true,
        ];
    }
    try {
        peerTransportPost($policy, $peer, $secret, $body);
        peerTransportScheduled($pdo, $endpoint, $peer);
        return [
            'peer' => $peer,
            'taken_at' => (int) $row['taken_at'],
            'queued' => 0,
            'delivered' => $delivered + 1,
            'skipped' => false,
        ];
    } catch (ActionRefused) {
        peerTransportQueue(
            $pdo,
            $endpoint,
            $peer,
            $body,
            (int) $row['taken_at'],
        );
        peerTransportScheduled($pdo, $endpoint, $peer);
        $id = $pdo->prepare(
            'SELECT id FROM remote_delivery_queue WHERE peer = ? AND taken_at = ?',
        );
        $id->execute([$peer, (int) $row['taken_at']]);
        peerTransportDefer(
            $pdo,
            (int) $id->fetchColumn(),
            'remote controller unavailable',
        );
        return [
            'peer' => $peer,
            'taken_at' => (int) $row['taken_at'],
            'queued' => 1,
            'delivered' => $delivered,
            'skipped' => false,
        ];
    }
}

/** @return array{peer: string, host: string, taken_at: int, vitals: array<string, int|float|null>, activate: bool} */
function peerTransportAccept(
    PDO $pdo,
    array $config,
    array $headers,
    string $body,
): array {
    if ($body === '' || strlen($body) > PEER_TRANSPORT_MAX_BYTES) {
        throw new ActionRefused('remote payload is empty or too large');
    }
    $peer = strtolower(trim((string) ($headers['peer'] ?? '')));
    $at = filter_var($headers['timestamp'] ?? null, FILTER_VALIDATE_INT);
    $nonce = (string) ($headers['nonce'] ?? '');
    $signature = strtolower(trim((string) ($headers['signature'] ?? '')));
    if (
        !preg_match('/^[a-z0-9][a-z0-9_-]{0,40}$/', $peer) ||
        $at === false ||
        !preg_match('/^[A-Za-z0-9_-]{24,128}$/', $nonce) ||
        !preg_match('/^[a-f0-9]{64}$/', $signature)
    ) {
        throw new ActionRefused('remote request headers are invalid');
    }
    if (abs(time() - $at) > PEER_TRANSPORT_SKEW) {
        throw new ActionRefused(
            'remote request timestamp is outside the replay window',
        );
    }
    $definition = $config['peers'][$peer] ?? null;
    $secret = is_array($definition)
        ? (string) ($definition['transport_secret'] ?? '')
        : '';
    if (
        $secret === '' ||
        !hash_equals(
            peerTransportSignature($secret, $peer, $at, $nonce, $body),
            $signature,
        )
    ) {
        throw new ActionRefused('remote request authentication failed');
    }
    $pendingUntil = (int) ($definition['transport_pending_until'] ?? 0);
    if ($pendingUntil > 0 && $pendingUntil < time()) {
        throw new ActionRefused(
            'remote pairing credential expired; rotate it before connecting',
        );
    }

    $payload = json_decode(
        peerTransportBody($body, (string) ($headers['encoding'] ?? '')),
        true,
    );
    if (
        !is_array($payload) ||
        (int) ($payload['version'] ?? 0) !== PEER_TRANSPORT_PROTOCOL ||
        !is_string($payload['host'] ?? null) ||
        mb_strlen(trim($payload['host'])) > 80 ||
        !is_numeric($payload['taken_at'] ?? null) ||
        !is_array($payload['vitals'] ?? null)
    ) {
        throw new ActionRefused(
            'remote payload shape or protocol version is invalid',
        );
    }
    $takenAt = (int) $payload['taken_at'];
    if ($takenAt < 1 || $takenAt > time() + PEER_TRANSPORT_SKEW) {
        throw new ActionRefused('remote collection time is invalid');
    }
    $vitals = [];
    foreach ((array) $payload['vitals'] as $name => $value) {
        if (
            !is_string($name) ||
            !preg_match('/^[a-z][a-z0-9_]{0,63}$/', $name) ||
            (!is_int($value) && !is_float($value) && $value !== null)
        ) {
            throw new ActionRefused('remote vitals are invalid');
        }
        $vitals[$name] = $value;
    }
    if (count($vitals) > 32) {
        throw new ActionRefused('remote payload contains too many vitals');
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO remote_peer_receipts (peer, nonce, received_at) VALUES (?, ?, ?)',
        )->execute([$peer, $nonce, time()]);
        $pdo->prepare(
            'INSERT INTO remote_peer_vitals (peer, host, taken_at, received_at, body)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT(peer, taken_at) DO NOTHING',
        )->execute([
            $peer,
            trim($payload['host']),
            $takenAt,
            time(),
            json_encode($vitals, JSON_UNESCAPED_SLASHES),
        ]);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (str_contains(strtolower($e->getMessage()), 'unique')) {
            throw new ActionRefused('remote request was already received');
        }
        throw $e;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    return [
        'peer' => $peer,
        'host' => trim($payload['host']),
        'taken_at' => $takenAt,
        'vitals' => $vitals,
        'activate' => $pendingUntil > 0,
    ];
}
