<?php
declare(strict_types=1);

namespace HostScope\Plugin\KeyCustody;

final class KeyCustodyProvider
{
    /** @return array{id:string,state:string,reason:string} */
    public function capability(): array
    {
        return [
            'id' => 'zogograph-key-custody',
            'state' => 'unavailable',
            'reason' =>
                'The isolated HostScope package runtime is not installed.',
        ];
    }

    public function activate(): never
    {
        throw new \RuntimeException($this->capability()['reason']);
    }

    /** Credential-free endpoint identity probe for the isolated setup flow. */
    public function preflight(
        string $serviceBaseUrl,
        ?string $tlsSpkiPin = null,
    ): array {
        KeyCustodyHttpClient::healthEndpoint($serviceBaseUrl, $tlsSpkiPin);
        return [
            'id' => 'zogograph-key-custody',
            'state' => 'endpoint-ready',
            'reason' =>
                'The configured custody endpoint returned the expected service identity and API version. No enrollment credential or key material was sent.',
        ];
    }

    /**
     * Confirms only that one enrolled client can read its minimal envelope
     * entitlement. It never creates a key/envelope or requests key material.
     * Credentials are supplied for this one short-lived sandbox invocation.
     */
    public function enrollmentCheck(
        string $serviceBaseUrl,
        string $clientCredential,
        string $clientSigningKey,
        ?string $tlsSpkiPin = null,
    ): array {
        $entitlement = new KeyCustodyHttpClient(
            $serviceBaseUrl,
            $clientCredential,
            $clientSigningKey,
            $tlsSpkiPin,
        )->envelopeEntitlement();
        return [
            'id' => 'zogograph-key-custody',
            'state' => 'enrollment-ready',
            'reason' => $entitlement['can_store_envelopes']
                ? 'The enrolled client credential is accepted and may store a future wrapped data-key envelope. No envelope or key was requested.'
                : 'The enrolled client credential is accepted, but its current subscription or envelope quota does not permit a new envelope. No envelope or key was requested.',
        ];
    }

    /**
     * Seals exactly one caller-created artifact DEK for the configured custody
     * key and stores only that sealed envelope. The returned receipt deliberately
     * excludes the envelope and data key.
     * @return array{id:string,state:string,reason:string,envelope_sha256:string}
     */
    public function storeEnvelope(
        string $serviceBaseUrl,
        string $clientCredential,
        string $clientSigningKey,
        string $keyId,
        string $artifactId,
        string $dataKey,
        ?string $tlsSpkiPin = null,
    ): array {
        if (
            !preg_match('/^key_[a-f0-9]{32}$/', $keyId) ||
            $artifactId === '' ||
            strlen($artifactId) > 200 ||
            preg_match('/[\x00-\x1F\x7F]/', $artifactId) ||
            strlen($dataKey) !== 32
        ) {
            throw new \InvalidArgumentException(
                'custody envelope storage input is invalid',
            );
        }
        $client = new KeyCustodyHttpClient(
            $serviceBaseUrl,
            $clientCredential,
            $clientSigningKey,
            $tlsSpkiPin,
        );
        $envelope = KeyCustodyHttpClient::sealDataKey(
            $dataKey,
            $client->wrappingPublicKey($keyId),
        );
        $client->storeEnvelope($keyId, $artifactId, $envelope);
        return [
            'id' => 'zogograph-key-custody',
            'state' => 'envelope-stored',
            'reason' =>
                'The custody service accepted one sealed data-key envelope. The data key and envelope were not returned to HostScope core.',
            'envelope_sha256' => hash('sha256', $envelope),
        ];
    }

    /** Returns one named artifact DEK only to the core process through the
     * one-shot worker pipe. Callers must use it immediately and never persist it. */
    public function unwrapArtifact(
        string $serviceBaseUrl,
        string $clientCredential,
        string $clientSigningKey,
        string $keyId,
        string $artifactId,
        ?string $tlsSpkiPin = null,
    ): array {
        if (
            !preg_match('/^key_[a-f0-9]{32}$/', $keyId) ||
            $artifactId === '' ||
            strlen($artifactId) > 200 ||
            preg_match('/[\x00-\x1F\x7F]/', $artifactId)
        ) {
            throw new \InvalidArgumentException(
                'custody recovery input is invalid',
            );
        }
        $key = new KeyCustodyHttpClient(
            $serviceBaseUrl,
            $clientCredential,
            $clientSigningKey,
            $tlsSpkiPin,
        )->unwrap($keyId, $artifactId, 0);
        return [
            'id' => 'zogograph-key-custody',
            'state' => 'unwrap-released',
            'reason' =>
                'The custody service released one named artifact data key for immediate in-memory recovery verification.',
            'data_key' => base64_encode($key),
        ];
    }
}
