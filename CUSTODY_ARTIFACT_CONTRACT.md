# Remote custody artifact contract

This contract defines the next implementation step after enrolled-client
readiness. It preserves Zogograph’s low-cost local recovery path while allowing
an optional remote custody provider to add a separate recovery envelope.

The first implementation now supports explicit, per-artifact envelope storage
and non-destructive remote recovery drills. Both remain disabled until the
administrator configures and reviews their dedicated launchers; neither makes
hosted custody a default dependency or permits provider-only recovery.

## Recovery model

Every sealed artifact uses a fresh random 256-bit data-encryption key (DEK).
The DEK encrypts the artifact payload. A recovery mechanism holds only a
wrapped form of that DEK; it never receives the database or configuration
plaintext.

The initial remote-custody implementation is additive:

| mechanism               | required                         | role                                                                  |
| ----------------------- | -------------------------------- | --------------------------------------------------------------------- |
| local private-key wrap  | yes for the first remote release | portable primary recovery path and offline fallback                   |
| remote custody envelope | optional, per artifact           | independently authorized recovery path for the same DEK               |
| ciphertext replica      | optional, separate package       | availability copy of ciphertext only; never a DEK or unwrap authority |

Remote custody must not replace the verified local recovery wrap during its
first release. A remote outage, subscription change, account mistake, or
provider failure must not strand a customer who retains their local key. A
later provider-only policy needs separate recovery-quorum and availability
evidence before it can be offered.

## Artifact and ledger requirements

The artifact header will move from one local `wrapped_key` field to a bounded
`key_envelopes` collection. Each entry identifies its recovery method and has
method-specific authenticated parameters. The complete header remains included
in the chunk AAD hash, so a changed envelope or provider identity invalidates
the artifact authentication check.

A remote entry records at least:

- opaque provider package and custody-key identifiers;
- stable artifact identifier and local ciphertext SHA-256;
- the Base64 X25519 sealed DEK envelope returned as accepted by custody;
- provider endpoint identity reference, not a mutable display URL;
- local creation time, remote receipt time, and receipt/digest evidence; and
- state: `pending`, `stored`, `verified`, `failed`, `retired`, or `deleted`.

The local encryption ledger mirrors remote-envelope state without storing the
DEK, the enrolled bearer, the signing key, management credentials, or provider
responses that could contain secrets. A remote envelope becomes recovery
evidence only after the custody service confirms storage and the local ledger
records the matching artifact ID and ciphertext digest.

## Required operation sequence

1. Core creates the consistent local sealed artifact using a fresh DEK and
   verifies its local recovery path.
2. Core writes a `pending` remote-envelope ledger record. It does not delete,
   supersede, or upload plaintext data.
3. A separately attested provider operation reads the custody public wrapping
   key, seals that same DEK locally, and stores the resulting envelope under
   the exact artifact ID.
4. Core validates the bounded receipt, records `stored`, and surfaces that the
   remote copy is an additional recovery path—not a live-at-rest claim.
5. A recovery drill requests an unwrap only through a distinct
   operation, use the DEK in memory to authenticate the artifact, then discard
   it. The drill records outcome evidence but never persists the returned key.
6. Rotation adds and verifies a replacement envelope before any old envelope
   is deleted. Remote-envelope deletion requires local alternate-recovery
   evidence and an explicit administrator confirmation.

Any failure leaves the local verified artifact untouched and the remote ledger
in a visible failed/pending state. Automatic local retention must never delete
the newest verified local copy merely because a remote envelope exists.

## Isolation and permission gates

The implemented `custody_store_envelope` operation requires all of:

- the enrolled-client readiness check has succeeded for the exact endpoint;
- `key_provider` and `network_client` have been explicitly granted;
- a manifest-pinned, one-shot sandbox with egress restricted to that endpoint;
- a request containing only the fresh DEK, public wrapping information,
  artifact ID, and bounded receipt contract; and
- an audit intent and a verified local recovery artifact before launch.

It must not receive local key files, setup/configuration databases, arbitrary
filesystem paths, management/billing credentials, or another package’s state.
`custody_unwrap_artifact` uses a third dedicated launcher and returns a DEK
only through the worker’s private process pipe. Core authenticates one named
artifact into a private temporary file, removes it, discards the DEK, and
records only the verification result. It still requires ongoing review of
rate-limit/retry behavior and no-key logging.

## Customer experience

The Protection page continues to recommend local sealed copies. After a
customer installs a compatible custody package, it explains remote custody as
an **extra recovery path** with the cost, availability, and key-loss tradeoff.
The default remains local-only. A customer may select which stores receive
remote envelopes and can see per-artifact pending/verified/failure evidence.
No remote endpoint, credential, envelope, or key upload happens until they
explicitly choose and confirm that policy.
