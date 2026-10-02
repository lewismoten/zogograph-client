<?php
declare(strict_types=1);

namespace Zogograph\Plugin\ReplicaRelay;

/** Signed ciphertext-only transport. It cannot send a key or plaintext store. */
final class ReplicaRelayHttpClient
{
    private readonly string $signingKey;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientToken,
        string $clientSigningKey,
        private readonly ?string $tlsSpkiPin = null,
    ) {
        self::validateEndpoint($baseUrl, $tlsSpkiPin);
        if ($clientToken === '') {
            throw new \InvalidArgumentException(
                'replica relay endpoint or client credential is invalid',
            );
        }
        if (
            !preg_match(
                '~^tok_[a-f0-9]{20}\.[A-Za-z0-9+/_-]+={0,2}$~',
                $clientToken,
            )
        ) {
            throw new \InvalidArgumentException(
                'replica relay client credential is invalid',
            );
        }
        if (
            !function_exists('curl_init') ||
            !function_exists('sodium_crypto_sign_detached')
        ) {
            throw new \RuntimeException(
                'cURL and libsodium are required for replica relay',
            );
        }
        $this->signingKey = base64_decode($clientSigningKey, true);
        if (
            !is_string($this->signingKey) ||
            strlen($this->signingKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
        ) {
            throw new \InvalidArgumentException(
                'replica relay signing credential is invalid',
            );
        }
    }

    /** Streams one already-encrypted artifact without retaining it in memory. */
    public function storeArtifact(
        string $artifactId,
        string $ciphertextPath,
    ): array {
        if (
            !preg_match('/^[A-Za-z0-9._:-]{1,200}$/', $artifactId) ||
            is_link($ciphertextPath) ||
            !is_file($ciphertextPath) ||
            !is_readable($ciphertextPath)
        ) {
            throw new \InvalidArgumentException(
                'replica artifact path or identity is invalid',
            );
        }
        $bytes = filesize($ciphertextPath);
        $digest = hash_file('sha256', $ciphertextPath);
        if (!is_int($bytes) || $bytes < 1 || !is_string($digest)) {
            throw new \RuntimeException('could not inspect replica ciphertext');
        }
        $stream = fopen($ciphertextPath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException('could not read replica ciphertext');
        }
        try {
            return $this->request(
                'PUT',
                '/v1/artifacts/' . rawurlencode($artifactId),
                $stream,
                $bytes,
                $digest,
            );
        } finally {
            fclose($stream);
        }
    }

    /** Tests a configured endpoint before enrollment; it sends no credential or ciphertext. */
    public static function healthEndpoint(
        string $baseUrl,
        ?string $tlsSpkiPin = null,
    ): array {
        self::validateEndpoint($baseUrl, $tlsSpkiPin);
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cURL is required for replica relay');
        }
        $handle = curl_init(rtrim($baseUrl, '/') . '/v1/health');
        if ($handle === false) {
            throw new \RuntimeException(
                'could not initialize replica relay health request',
            );
        }
        try {
            $options = [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ];
            if ($tlsSpkiPin !== null) {
                $options[CURLOPT_PINNEDPUBLICKEY] = $tlsSpkiPin;
            }
            curl_setopt_array($handle, $options);
            $raw = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $response = is_string($raw) ? json_decode($raw, true) : null;
            if (
                $status !== 200 ||
                !is_array($response) ||
                ($response['status'] ?? null) !== 'ok' ||
                ($response['service'] ?? null) !== 'replica-relay' ||
                ($response['api_version'] ?? null) !== 'v1'
            ) {
                throw new \RuntimeException(
                    'replica relay health request was refused',
                );
            }
            return $response;
        } finally {
            curl_close($handle);
        }
    }

    /** Tests only service liveness; it sends no bearer, signature, or ciphertext. */
    public function health(): array
    {
        return self::healthEndpoint($this->baseUrl, $this->tlsSpkiPin);
    }

    /** Reads only this enrolled deployment's ciphertext capacity. It does not
     * grant artifact listing, deletion, download, or billing authority. */
    public function storageEntitlement(): array
    {
        $timestamp = (string) time();
        $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $path = '/v1/client/entitlement';
        $digest = hash('sha256', '', true);
        $payload =
            "GET\n" .
            $path .
            "\n" .
            $timestamp .
            "\n" .
            $nonce .
            "\n" .
            base64_encode($digest);
        $signature = base64_encode(
            sodium_crypto_sign_detached($payload, $this->signingKey),
        );
        $handle = curl_init(rtrim($this->baseUrl, '/') . $path);
        if ($handle === false) {
            throw new \RuntimeException(
                'could not initialize replica relay request',
            );
        }
        try {
            curl_setopt_array(
                $handle,
                $this->transportOptions([
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->clientToken,
                        'X-Zogograph-Timestamp: ' . $timestamp,
                        'X-Zogograph-Nonce: ' . $nonce,
                        'X-Zogograph-Signature: ' . $signature,
                    ],
                ]),
            );
            $raw = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $response = is_string($raw) ? json_decode($raw, true) : null;
            $entitlement = is_array($response)
                ? $response['entitlement'] ?? null
                : null;
            if (
                $status < 200 ||
                $status >= 300 ||
                !is_array($entitlement) ||
                !is_int($entitlement['artifact_quota'] ?? null) ||
                !is_int($entitlement['artifact_used'] ?? null) ||
                !is_int($entitlement['storage_quota_bytes'] ?? null) ||
                !is_int($entitlement['storage_used_bytes'] ?? null) ||
                !is_bool($entitlement['can_store_artifact'] ?? null)
            ) {
                throw new \RuntimeException(
                    'replica relay entitlement request was refused',
                );
            }
            return $entitlement;
        } finally {
            curl_close($handle);
        }
    }

    private function request(
        string $method,
        string $path,
        $stream,
        int $bytes,
        string $digest,
    ): array {
        $timestamp = (string) time();
        $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $digestBytes = hex2bin($digest);
        if (!is_string($digestBytes)) {
            throw new \InvalidArgumentException(
                'replica ciphertext digest is invalid',
            );
        }
        $payload =
            strtoupper($method) .
            "\n" .
            $path .
            "\n" .
            $timestamp .
            "\n" .
            $nonce .
            "\n" .
            base64_encode($digestBytes);
        $signature = base64_encode(
            sodium_crypto_sign_detached($payload, $this->signingKey),
        );
        $handle = curl_init(rtrim($this->baseUrl, '/') . $path);
        if ($handle === false) {
            throw new \RuntimeException(
                'could not initialize replica relay request',
            );
        }
        try {
            curl_setopt_array(
                $handle,
                $this->transportOptions([
                    CURLOPT_UPLOAD => true,
                    CURLOPT_INFILE => $stream,
                    CURLOPT_INFILESIZE => $bytes,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->clientToken,
                        'Content-Type: application/octet-stream',
                        'X-Zogograph-Ciphertext-SHA256: ' . $digest,
                        'X-Zogograph-Timestamp: ' . $timestamp,
                        'X-Zogograph-Nonce: ' . $nonce,
                        'X-Zogograph-Signature: ' . $signature,
                    ],
                ]),
            );
            $raw = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            if (!is_string($raw)) {
                throw new \RuntimeException(
                    'replica relay request failed: ' . curl_error($handle),
                );
            }
            $response = json_decode($raw, true);
            if (!is_array($response) || $status < 200 || $status >= 300) {
                throw new \RuntimeException(
                    'replica relay request was refused',
                );
            }
            return $response;
        } finally {
            curl_close($handle);
        }
    }

    private function transportOptions(array $options): array
    {
        if ($this->tlsSpkiPin !== null) {
            $options[CURLOPT_PINNEDPUBLICKEY] = $this->tlsSpkiPin;
        }
        return $options;
    }

    private static function validateEndpoint(
        string $baseUrl,
        ?string $tlsSpkiPin,
    ): void {
        $url = parse_url($baseUrl);
        if (
            !filter_var($baseUrl, FILTER_VALIDATE_URL) ||
            !is_array($url) ||
            strtolower((string) ($url['scheme'] ?? '')) !== 'https' ||
            !is_string($url['host'] ?? null) ||
            $url['host'] === '' ||
            isset($url['user']) ||
            isset($url['pass']) ||
            isset($url['query']) ||
            isset($url['fragment'])
        ) {
            throw new \InvalidArgumentException(
                'replica relay endpoint is invalid',
            );
        }
        if (
            $tlsSpkiPin !== null &&
            preg_match('~^sha256//[A-Za-z0-9+/]{43}=$~', $tlsSpkiPin) !== 1
        ) {
            throw new \InvalidArgumentException(
                'replica relay TLS public-key pin is invalid',
            );
        }
    }
}
