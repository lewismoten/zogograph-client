# Isolated provider RPC plan

> [Project README](../README.md) · [Documentation](../docs/README.md) · [Notes](README.md)

This is the next implementation boundary after the sandboxed, no-secret
`capability` probe. It is intentionally separate from package installation and
capability grants. A signed package is not allowed to choose an RPC operation
at runtime; the manifest and core both have to recognize it.

## Version 1 operation set

| Operation                  | Signed capability                              | Data the worker may receive                                                                       | Data it must not receive                                                                      |
| -------------------------- | ---------------------------------------------- | ------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| `capability`               | none                                           | package id and an invocation id                                                                   | settings, secrets, database paths, core handles                                               |
| `seal_envelope`            | `key_provider`                                 | fresh per-artifact data key and provider public information                                       | local KEK, management/billing credential, other package data                                  |
| `store_ciphertext`         | `ciphertext_replica` + `network_client`        | already-sealed artifact bytes/digest and scoped client credential                                 | plaintext store, artifact data key, recovery key                                              |
| `sqlcipher_preflight`      | `live_database_encryption` + `storage_backend` | a disposable, core-created test database path in the package mount                                | selected live database, setup database, production key                                        |
| `custody_enrollment_check` | `key_provider` + `network_client`              | enrolled-client bearer/signing key, configured endpoint, and optional public TLS pin              | management/billing credential, artifact ID, any database path, envelope, or data key          |
| `custody_store_envelope`   | `key_provider` + `network_client`              | one 32-byte in-memory artifact DEK, configured custody-key and artifact IDs, enrolled-client pair | local KEK, plaintext, database path, management/billing credential, or returned envelope body |

`capability`, the SQLCipher-only `sqlcipher_preflight`, and the custody
credential-only `custody_enrollment_check`, and the separately launcher-gated
`custody_store_envelope` worker operation are now implemented
in the sandbox worker. The preflight uses a random temporary file/key and
returns only `unavailable` or `preflight-ready`; it does not receive a selected
store or production key. Add each remaining operation only after its request
and response schema, audit event, recovery behavior, and sandbox mount are
implemented and tested. This keeps clients from granting a broad “provider”
permission whose effects are unclear.

## Core-owned invocation rules

Core must create a one-time invocation id, write an audit intent, verify the
installed manifest/fingerprint/revocation/grants immediately before launch,
and impose a small request, response, CPU, memory, and wall-clock budget. It
must reject a response unless the invocation id, package id, operation, and
schema all match the request. The worker never chooses a core file path or a
secret reference to resolve.

Secret references stay in core. A request schema can name a specific signed
settings field as secret-bearing; only then may core resolve that exact
package-scoped `secret_ref:` into the worker's short-lived standard input. The
secret must be absent from audit entries, process arguments, environment,
worker output, package private SQLite, and error text.

## Deployment decision

Use a one-shot container/service process by default. It is simpler for a small
deployment, prevents long-lived secret caches, and makes the mounted package
release and egress policy visible in the deployment definition. A persistent
worker is a later opt-in optimization only after it has per-invocation memory
clearing, authentication, restart, and crash-recovery evidence.

The core process should launch an argument-vector command supplied by a
deployment-owned configuration file, not a browser setting. That command is
security-sensitive host policy, comparable to a service account or container
profile; letting a website form change it would turn a configuration compromise
into arbitrary command execution.

## Provider rollout

1. **SQLCipher preflight:** implemented for a disposable sandbox database.
   It proves driver availability, encrypted reopen, and wrong-key refusal.
   WAL, backup/restore, crash behavior, and all live-store migration evidence
   remain required before a user can select a live store.
2. **Ciphertext relay:** permit only sealed artifact upload and digest/receipt
   validation. It is safe to introduce before remote key custody because it
   never needs plaintext or a data key.
3. **Remote key custody:** enroll only a client credential, fetch public
   wrapping information, and store a sealed data-key envelope. Keep management
   and billing credentials outside the client package entirely.
4. **Live SQLCipher migration:** require a verified sealed recovery artifact,
   a dry-run database, explicit stopped-writer acknowledgement, successful
   encrypted reopen, and an operator recovery drill before replacing a live
   store.

Each operation stays optional. The supported low-cost default remains local
SQLite with owner-only files and verified sealed copies; no remote service or
container runtime is required for that baseline.

### Credential-check deployment gate

The implemented enrollment check is intentionally not a generic provider
runner. It requires all of the following before core sends the enrolled pair:

1. a fresh credential-free endpoint identity probe and two encrypted package
   secret references;
2. explicit administrator approval of only `key_provider` and `network_client`;
3. a separate deployment-owned `custody_credential_check_invokers` command
   pinned to the installed manifest hash; and
4. a declared egress origin exactly matching the saved HTTPS custody origin.

An envelope write needs a second, explicitly configured
`custody_envelope_invokers` launcher. It must not reuse the readiness-check
launcher merely because the URL and client enrollment are the same. Core must
first prove the named local artifact and its ciphertext hash, then create a
pending ledger row; the worker receives the DEK only in memory and returns a
digest-only receipt. A core workflow and recovery-drill release gate remain
required before writes are exposed in the Protection UI.

The sandbox profile must enforce one-shot execution, read-only package files,
no core mounts, no environment/argument secret delivery, and egress restricted
to that origin. Core checks the configuration assertion but cannot make a
general Docker network satisfy it by itself. The operation calls only signed
`GET /v1/client/subscription`, records bounded readiness evidence, and never
requests an envelope or key. Do not reuse the no-network probe compose template
for it; provide a separately reviewed network-restricted deployment definition.
