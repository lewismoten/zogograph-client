# Zogograph ciphertext replica relay package

This optional package sends only already-sealed artifact ciphertext to the
separate Zogograph replica relay. It is disabled by default and is not a
requirement for local sealed copies, local recovery keys, or key custody.

Settings require an HTTPS relay base URL, the opaque tenant identifier, and
two encrypted package `secret_ref:` values: the enrolled-client bearer and its
Base64 Ed25519 private signing key. Never put a custody credential, management
token, billing token, plaintext database, or encryption key in these settings.
The adapter accepts only the generated `tok_…` bearer syntax, so whitespace or
header-like content is rejected before it can enter an HTTP request.

The transport streams one sealed artifact from disk, calculates SHA-256, signs
the method/path/timestamp/nonce/digest, verifies TLS and hostname, and follows
no redirects. It has no upload method for a live SQLite database or a plaintext
export. The relay may receive ciphertext, but never an unwrap authorization.

For a higher-assurance deployment, `tls_spki_pin` may contain the relay
certificate's Base64 `sha256//...` Subject Public Key Info hash. It supplements
normal CA and hostname checks; leave it unset for the recommended low-friction
configuration. Before rotating the relay certificate key or moving to a new
service identity, test and update the pin through an authenticated, audited
identity migration. A public-key pin is not a secret or an enrollment token.

Before a scheduled upload, the adapter can read its signed, minimal storage
readiness projection: artifact count, ciphertext bytes, configured ceilings,
and whether another artifact can be stored. It has no authority to list,
download, delete, manage, or bill the relay account.

`ReplicaRelayHttpClient::healthEndpoint($url, $pin)` calls the unauthenticated
`/v1/health` probe before enrollment, using the same HTTPS and optional SPKI
pin configuration but sending no bearer, signature, or ciphertext. The
instance `health()` method is the equivalent post-enrollment convenience call.
A package setup screen should use the static probe before accepting a client
credential, then show the verified service identity before scheduling a
ciphertext transfer.

The package remains a design preview until the isolated runtime can enforce its
capabilities and the core replication ledger can prove upload, remote digest
verification, retry, retention, deletion acknowledgement, and recovery.
