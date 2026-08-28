#!/usr/bin/env php
<?php
declare(strict_types=1);

/* This worker is for a separately deployed OS/container sandbox only. It
 * exposes a deliberately harmless first operation: provider capability facts.
 * It receives no Zogograph config, secrets, core database handles, or private
 * package database path. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI-only worker.\n"); }
if (getenv('HOSTSCOPE_PACKAGE_SANDBOX') !== '1') { fwrite(STDERR, "Refusing to run outside an attested package sandbox.\n"); exit(77); }
if ($argc !== 3 || $argv[1] !== '--package-root') { fwrite(STDERR, "Usage: package-runtime-worker.php --package-root /sandbox/read-only/package\n"); exit(64); }
$root = realpath($argv[2]);
if ($root === false || is_link($argv[2]) || !is_file($root . '/manifest.json')) { fwrite(STDERR, "Package root is unavailable.\n"); exit(1); }
$input = stream_get_contents(STDIN, 65537);
if (!is_string($input) || strlen($input) > 65536) { fwrite(STDERR, "Worker request is too large.\n"); exit(1); }
try {
    $request = json_decode($input, true, 16, JSON_THROW_ON_ERROR);
    $manifest = json_decode((string)file_get_contents($root . '/manifest.json'), true, 64, JSON_THROW_ON_ERROR);
    $expectedFields = in_array($request['operation'] ?? null, ['custody_endpoint_probe', 'custody_enrollment_check', 'custody_store_envelope', 'custody_unwrap_artifact'], true)
        ? ['api', 'invocation_id', 'operation', 'package_id', 'input'] : ['api', 'invocation_id', 'operation', 'package_id'];
    if (!is_array($request) || array_diff(array_keys($request), $expectedFields) !== [] || count($request) !== count($expectedFields)
        || ($request['api'] ?? null) !== '1.0' || !is_string($request['invocation_id'] ?? null)
        || !preg_match('/^[a-f0-9]{32}$/', $request['invocation_id']) || !is_string($request['operation'] ?? null)
        || !is_array($manifest) || ($request['package_id'] ?? null) !== ($manifest['id'] ?? null)) throw new RuntimeException('Worker request is invalid.');
    $runtime = (array)($manifest['runtime'] ?? []); $entrypoint = (string)($runtime['entrypoint'] ?? ''); $class = (string)($runtime['provider_class'] ?? '');
    $operations = array_values((array)($runtime['operations'] ?? []));
    if ($entrypoint === '' || $class === '' || !in_array($request['operation'], $operations, true) || str_contains($entrypoint, '..') || str_starts_with($entrypoint, '/')) throw new RuntimeException('Package runtime declaration is invalid.');
    $entry = realpath($root . '/' . $entrypoint);
    if ($entry === false || is_link($root . '/' . $entrypoint) || !str_starts_with($entry, $root . '/') || !is_file($entry)) throw new RuntimeException('Package entry point is unavailable.');
    require $entry;
    if (!class_exists($class)) throw new RuntimeException('Package provider class is unavailable.');
    $provider = new $class();
    if ($request['operation'] === 'capability') $result = $provider->capability();
    elseif ($request['operation'] === 'custody_endpoint_probe') {
        $input = $request['input'] ?? null;
        if (!is_array($input) || array_diff(array_keys($input), ['service_base_url', 'tls_spki_pin']) !== []
            || !is_string($input['service_base_url'] ?? null) || (($input['tls_spki_pin'] ?? null) !== null && !is_string($input['tls_spki_pin']))) {
            throw new RuntimeException('Custody endpoint probe input is invalid.');
        }
        $result = $provider->preflight($input['service_base_url'], $input['tls_spki_pin'] ?? null);
    } elseif ($request['operation'] === 'custody_enrollment_check') {
        $input = $request['input'] ?? null;
        if (!is_array($input) || array_diff(array_keys($input), ['service_base_url', 'tls_spki_pin', 'client_credential', 'client_signing_key']) !== []
            || !is_string($input['service_base_url'] ?? null) || (($input['tls_spki_pin'] ?? null) !== null && !is_string($input['tls_spki_pin']))
            || !is_string($input['client_credential'] ?? null) || !is_string($input['client_signing_key'] ?? null)) {
            throw new RuntimeException('Custody enrollment input is invalid.');
        }
        $result = $provider->enrollmentCheck($input['service_base_url'], $input['client_credential'], $input['client_signing_key'], $input['tls_spki_pin'] ?? null);
    } elseif ($request['operation'] === 'custody_store_envelope') {
        $input = $request['input'] ?? null;
        if (!is_array($input) || array_diff(array_keys($input), ['service_base_url', 'tls_spki_pin', 'client_credential', 'client_signing_key', 'custody_key_id', 'artifact_id', 'data_key']) !== []
            || !is_string($input['service_base_url'] ?? null) || (($input['tls_spki_pin'] ?? null) !== null && !is_string($input['tls_spki_pin']))
            || !is_string($input['client_credential'] ?? null) || !is_string($input['client_signing_key'] ?? null)
            || !is_string($input['custody_key_id'] ?? null) || !is_string($input['artifact_id'] ?? null) || !is_string($input['data_key'] ?? null)) {
            throw new RuntimeException('Custody envelope storage input is invalid.');
        }
        $dataKey = base64_decode($input['data_key'], true);
        if (!is_string($dataKey) || strlen($dataKey) !== 32) throw new RuntimeException('Custody envelope data key is invalid.');
        $result = $provider->storeEnvelope($input['service_base_url'], $input['client_credential'], $input['client_signing_key'], $input['custody_key_id'], $input['artifact_id'], $dataKey, $input['tls_spki_pin'] ?? null);
    } elseif ($request['operation'] === 'custody_unwrap_artifact') {
        $input = $request['input'] ?? null;
        if (!is_array($input) || array_diff(array_keys($input), ['service_base_url', 'tls_spki_pin', 'client_credential', 'client_signing_key', 'custody_key_id', 'artifact_id']) !== []
            || !is_string($input['service_base_url'] ?? null) || (($input['tls_spki_pin'] ?? null) !== null && !is_string($input['tls_spki_pin']))
            || !is_string($input['client_credential'] ?? null) || !is_string($input['client_signing_key'] ?? null)
            || !is_string($input['custody_key_id'] ?? null) || !is_string($input['artifact_id'] ?? null)) {
            throw new RuntimeException('Custody recovery input is invalid.');
        }
        $result = $provider->unwrapArtifact($input['service_base_url'], $input['client_credential'], $input['client_signing_key'], $input['custody_key_id'], $input['artifact_id'], $input['tls_spki_pin'] ?? null);
    } else $result = $provider->preflight();
    if (!is_array($result) || !is_string($result['id'] ?? null) || !is_string($result['state'] ?? null) || !is_string($result['reason'] ?? null)
        || strlen(json_encode($result, JSON_THROW_ON_ERROR)) > 4096) throw new RuntimeException('Package capability response is invalid.');
    fwrite(STDOUT, json_encode(['api' => '1.0', 'invocation_id' => $request['invocation_id'], 'package_id' => $manifest['id'], 'operation' => $request['operation'], 'result' => $result], JSON_UNESCAPED_SLASHES) . "\n");
} catch (Throwable $error) { fwrite(STDERR, 'Worker refused: ' . $error->getMessage() . "\n"); exit(1); }
