<?php
declare(strict_types=1);

/* Dependency-free checks for boundaries that must not weaken silently. */

require_once __DIR__ . '/../lib/ai.php';
require_once __DIR__ . '/../lib/urlwatch.php';
require_once __DIR__ . '/../lib/peers.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectRefused(callable $call, string $message): void
{
    try {
        $call();
    } catch (ActionRefused) {
        return;
    }
    throw new RuntimeException($message);
}

$free = peerCapacity(['peers' => ['one' => [], 'two' => [], 'three' => []]]);
expect($free['remote_host_limit'] === 3 && $free['used'] === 3 && $free['remaining'] === 0,
    'the free remote-host limit must include three remote hosts');

$local = aiEndpointPolicy('compatible', 'http://127.0.0.1:11434/v1');
expect($local['local'] && $local['resolve'] === [], 'loopback-compatible AI must remain supported');
$remoteCompatible = aiEndpointPolicy('compatible', 'https://1.1.1.1:8443/v1');
expect(!$remoteCompatible['local'] && $remoteCompatible['resolve'] === ['1.1.1.1:8443:1.1.1.1'],
    'a public HTTPS compatible model on a custom port must remain pinned and keyless');
expectRefused(fn() => aiEndpointPolicy('openai', 'https://127.0.0.1/v1'),
    'hosted AI must reject loopback targets');
expectRefused(fn() => aiEndpointPolicy('compatible', 'http://1.1.1.1:8443/v1'),
    'a remote compatible model must not use plain HTTP');
$providerConnections = aiSettings(['ai' => [
    'provider' => 'anthropic', 'base_url' => 'https://legacy.example/v1', 'api_key' => 'legacy',
    'providers' => [
        'anthropic' => ['base_url' => 'https://anthropic.example/v1', 'api_key' => 'anthropic-secret'],
        'compatible' => ['base_url' => 'https://ollama.example:8443/v1', 'api_key' => ''],
    ],
]]);
expect($providerConnections['base_url'] === 'https://anthropic.example/v1'
    && $providerConnections['api_key'] === 'anthropic-secret'
    && $providerConnections['connections']['compatible']['has_key'] === false,
    'AI provider connections must keep endpoints and credentials separate');

expectRefused(fn() => urlWatchEndpointPolicy('http://127.0.0.1/status'),
    'URL watch must reject unapproved private targets');
$watch = urlWatchEndpointPolicy('http://127.0.0.1/status', ['url_watch' => ['private_hosts' => ['127.0.0.1']]]);
expect($watch['resolve'] === ['127.0.0.1:80:127.0.0.1'], 'approved private URL must be pinned');

expect(peerEndpoint('https://peer.example', 'https://peer.example:443/keys', 'jwks') === 'https://peer.example:443/keys',
    'same-origin peer discovery endpoint must pass');
expectRefused(fn() => peerEndpoint('https://peer.example', 'https://other.example/keys', 'jwks'),
    'peer discovery must not escape its configured origin');

$metadata = peerMetadata(['tags' => 'Production, Web', 'timezone' => 'America/New_York']);
expect($metadata['tags'] === ['production', 'web'] && $metadata['timezone'] === 'America/New_York',
    'peer metadata was not normalized');
expectRefused(fn() => peerMetadata(['timezone' => 'not/a-zone']), 'invalid peer timezone was accepted');

expect(!peerHasOidc([]) && !peerHasOidc(['issuer' => 'https://peer.example']),
    'a partial OIDC peer must not be treated as connected');
expect(peerHasOidc(['base_url' => 'https://peer.example', 'redirect_uri' => 'https://controller.example/peer-connect.php',
    'issuer' => 'https://peer.example', 'client_id' => 'client', 'client_secret' => 'secret']),
    'a complete OIDC peer was not recognized');

echo "security policy checks passed\n";
