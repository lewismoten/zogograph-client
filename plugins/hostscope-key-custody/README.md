# HostScope remote key-custody package

This optional package connects a client deployment to the commercial
key-custody API. It is disabled by default and is not a requirement for local
keys, sealed copies, or self-managed fleet custody.

The package configures an HTTPS service base, tenant identity, and separate
`secret_ref:` values for the enrolled-client bearer credential and its Base64
Ed25519 private signing key. The service retains only the public half of the
signing key. The default unwrap cache is zero seconds; an administrator may
choose up to 15 minutes only after understanding that a cached authorization
can remain usable until it expires.

The signed settings schema accepts only an `https://` service base and the
opaque enrolled `ten_…` tenant identifier. This catches common setup mistakes
before settings are stored; the adapter performs stricter URL checks again when
it connects. It also accepts only the generated `tok_…` enrolled-client token
syntax, never a token containing whitespace or header-like content, and refuses
control characters, overlong identifiers, or oversized envelopes before making
a request.

For `service_base_url`, use either the service root such as
`https://custody.example/services/key-custody` or the copied API root ending in
`/v1`. HostScope normalizes either form to the versioned API exactly once. Do
not add endpoint names such as `/health`, credentials, query text, or fragments.

Save the endpoint, tenant identifier, and zero-second cache preference first,
then run **Test custody endpoint** from Package runtime. That isolated probe
uses only the saved HTTPS address and optional public TLS pin and confirms the
expected `/v1/health` identity. Only after it succeeds should an administrator
create package-scoped secret references for the enrolled-client bearer and
signing key. A successful probe is not enrollment, a package activation, or a
proof that a live database can be recovered.

HostScope requires a successful endpoint result less than 24 hours old before
it accepts either enrollment reference. Save only opaque `secret_ref:` values;
the raw bearer and private signing key never belong in normal package settings.
Changing the service URL or TLS pin clears that endpoint evidence and requires
the test again; rotating only the encrypted references preserves it.

The next staged operation is an explicitly approved, short-lived
`custody_enrollment_check`. It sends the enrolled-client pair only to signed
`GET /v1/client/subscription` request handling and returns whether a future
envelope write is allowed. It cannot create a key or envelope, request an
unwrap, or receive a HostScope database path. Core will expose it only after a
deployment has configured a reviewed network-restricted sandbox launcher and
the administrator has granted both `key_provider` and `network_client`.

It first reads only the custody key's X25519 public wrapping key, generates a
fresh artifact data key locally, and sends that key only as a sealed envelope
with its artifact identifier and authenticated request metadata. It must not
upload metrics, SQLite databases, configuration files, plaintext keys, or
billing credentials. Billing is managed through the commercial account
API/browser flow, separately from enrolled-client credentials.

The adapter can read a signed, minimal envelope-readiness projection: current
envelope usage, quota, renewal timestamp, and whether another envelope may be
stored. This lets a deployment explain an upload refusal before sending
ciphertext, without giving it a billing, management, key-creation, or
account-recovery credential. A passed renewal pauses only new envelope writes;
it never makes an existing artifact unrecoverable.

If the service temporarily reaches its tenant recovery-release throttle, the
adapter preserves its bounded `retry_after_seconds` response as a dedicated
retry exception. A future active client can explain this as a temporary retry
window rather than an enrollment, billing, or permanent recovery failure.

`KeyCustodyHttpClient::healthEndpoint($url, $pin)` calls the unauthenticated
`/v1/health` probe before enrollment, using the same HTTPS and optional SPKI
pin configuration but sending no bearer, signature, or key material. The
instance `health()` method is the equivalent post-enrollment convenience call.
A package setup screen should use the static probe before accepting a client
credential, then show the verified service identity before continuing.

The included client adapter accepts only an unambiguous HTTPS service URL (no
embedded user credentials, query, or fragment), explicitly verifies the server
certificate and hostname, follows no redirects, and uses a five-minute service
timestamp window plus a one-time request nonce. It signs the HTTP method, request path, timestamp, nonce, and
the exact request-body hash with Ed25519, so a stolen bearer alone cannot alter
or create an enrolled-client request. It retains no key cache: an unwrapped
artifact data key is returned only to the immediate caller for in-memory use.

For a higher-assurance deployment, the optional `tls_spki_pin` package setting
pins the service certificate's Base64 `sha256//...` Subject Public Key Info
hash in addition to normal CA and hostname validation. Leave it unset for the
recommended low-friction setup. A pin binds the enrollment to one certificate
key, so an operator must test and update the setting through an authenticated,
audited endpoint-identity migration before the service rotates that key. It is
not a secret and must never be used as a substitute for the enrolled-client
credential or service identity review.
