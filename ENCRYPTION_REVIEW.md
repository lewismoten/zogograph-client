# Encryption decisions to review later

This is the operator/product-owner checklist for encryption. It separates
decisions that Zogograph can safely default from decisions that must remain
changeable in the website and database-backed settings.

The adopted first-release choices and exact future change locations are kept
in [ENCRYPTION_DECISIONS.md](ENCRYPTION_DECISIONS.md).

## Current safe defaults

| decision                | initial value                           | change it in                    | why                                                                                                                   |
| ----------------------- | --------------------------------------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| protection              | off                                     | Setup → Encryption              | Encryption must be explicit.                                                                                          |
| protected stores        | configuration, setup, account           | Setup → Encryption              | These normally hold the most sensitive credentials and history.                                                       |
| archive method          | local sealed copies                     | Encryption package settings     | Works on shared hosting, VPSs, containers, and dedicated hosts.                                                       |
| local custody           | private file outside web root           | Encryption package settings     | Simple, portable, and does not require a third party.                                                                 |
| copy policy             | daily, retain 30 days                   | Encryption package settings     | A useful starting point; it is not an automatic deletion promise until retention automation is installed.             |
| live-store protection   | operator-managed volume where available | Setup → Encryption              | Protects SQLite, WAL, logs, and config together without changing application queries.                                 |
| remote replication      | off                                     | Fleet Encryption → Replicas     | A replica should never be created without a deliberately enrolled peer.                                               |
| commercial services     | off                                     | Marketplace → installed package | A customer deployment must never depend on Zogograph-operated services.                                               |
| remote service identity | normal HTTPS CA and hostname validation | provider package settings       | This is the low-friction portable default; an optional SPKI pin adds a deliberate certificate-key rotation procedure. |

These are seeded settings, not hard-coded policy. The only bootstrap values
that remain in `lib/config.php` are the local setup-store path and enough
information to open the settings service. Once the settings migration is
complete, package policy, provider configuration, replicas, retention, and
service endpoints belong in the setup database and are changed through the
authenticated website/API.

## Decisions requiring later review

| topic                    | review when                                                     | configuration owner          | concern                                                                                                                                                  |
| ------------------------ | --------------------------------------------------------------- | ---------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Default protected stores | before a public release                                         | product settings             | Selecting every optional store can surprise a small deployment; selecting too little leaves secrets exposed.                                             |
| Retention and deletion   | before enabling automatic pruning                               | encryption package           | Delete only after a recovery test and required replica acknowledgements; never remove the last verified copy.                                            |
| Key recovery             | before fleet custody is enabled                                 | custodian package            | Decide who may recover, how many custodians are required, and what happens when one is lost. Do not copy one complete key to every peer.                 |
| Key expiry               | before an expiring service plan                                 | custody provider             | Expiry only prevents a new unwrap. It cannot revoke a copied plaintext database, sealed copy plus key, or a key already cached by a compromised process. |
| SQLCipher support        | per supported PHP/OS build                                      | SQLCipher package            | The package must prove WAL, backup, migration, crash recovery, and performance behavior. It must not fall back to plaintext SQLite.                      |
| MySQL/MariaDB backend    | when multi-node query/write needs outweigh SQLite simplicity    | storage package              | It is a storage backend, not an encryption feature. It adds credentials, migrations, backups, and server operations.                                     |
| Redis                    | when an explicit cache/queue use case exists                    | cache package                | Redis is not durable encrypted metric storage by default; an exposed Redis service is a serious data-disclosure risk.                                    |
| Commercial key hosting   | before pricing or customer onboarding                           | services/key-custody package | Keep key custody and ciphertext replication as separate tenant-isolated roles; establish recovery, deletion, audit, and incident terms.                  |
| TLS public-key pin       | before enabling it or rotating a pinned service certificate key | provider package settings    | A pin supplements CA validation but can block recovery if it is not migrated before the service certificate key changes.                                 |
| Product/domain naming    | before public launch or rebrand                                 | site deployment settings     | Domains and labels must not change encrypted artifact identifiers, peer identities, signing keys, or stored customer data.                               |

## Setup configuration at rest

When a valid local Zogograph key file is configured, `lib/setupstore.php`
automatically stores the complete authenticated configuration as an
AES-256-GCM encrypted, versioned record in `setup.db`. It derives a distinct
configuration key from the local key, uses fixed authenticated associated data,
and deletes the old plaintext `config` record in the same SQLite transaction.
This covers bounded settings such as password hashes, TOTP secrets, provider
credentials, package policies, and access settings without making each caller
implement its own field vault.

`lib/config.php` retains only the database/scan/timezone bootstrap and the
absolute locator of the external key; it never contains the key itself. If an
encrypted configuration cannot be opened, Zogograph fails closed instead of
silently falling back to a bootstrap without its authentication policy. Keep a
private backup of both the setup database and the owner-only key file, then
verify a restart/recovery before relying on the change.

An installation with no local key keeps its setup configuration plaintext but
requires private `0600` SQLite permissions. That preserves the low-cost SQLite
quick-start path, but it is not encrypted at rest. Enabling a local key on the
next authenticated setup save migrates the record automatically. Removing the
key path deliberately returns to the private-permissions-only form, so do not
do that until a recovery copy is verified.

This is not live database/volume encryption: `setup.db` is decrypted by the
Zogograph process while in use. Use encrypted storage/volume protection or a
verified future SQLCipher package when an attacker with offline disk access is
in scope.

## Recommended initial product direction

These are deliberate starting policies for the first public release. They
belong in the product-settings database and commercial website administration
interface, not in client source code. Changing one later requires an audited
configuration migration and updated customer-facing terms, not a protocol fork.

| area                  | recommended initial policy                                                                                                                                                                                                 | reason and later change trigger                                                                                                                                                                               |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Commercial review     | Free private signing/testing; manual review for every executable package; no automatic publication.                                                                                                                        | Early review volume should be manageable and security-sensitive packages need human inspection. Add paid expedited review only after measured review time and a published SLA.                                |
| Marketplace price     | $0 to submit/open-source packages; charge only for commercial package sales through a configurable revenue share.                                                                                                          | Avoid charging before the marketplace creates distribution value. Set the percentage, payout threshold, refund window, and tax handling in commercial marketplace settings after legal/accounting review.     |
| SQLCipher support     | Support only tested PHP/OS/container images published by Zogograph; report all other environments as unavailable.                                                                                                          | SQLCipher builds and PDO driver behavior vary. Expand the support matrix only after automated WAL, migration, backup, crash-recovery, and performance results pass for that image.                            |
| Local retention       | Daily sealed copy, retain 30 days; prune only during an explicit sealing run after hash verification, never the newest verified copy per store.                                                                            | This provides a usable baseline without silently deleting the last local recovery data. Recommended future minimum: retain 7 daily, 4 weekly, and 12 monthly verified artifacts, all configurable per policy. |
| Fleet replication     | Off by default; recommend 2 encrypted replicas when at least 3 independently administered hosts are enrolled.                                                                                                              | One remote copy improves availability but is not redundancy; two replicas tolerate one failure. Do not count the owner as a remote replica.                                                                   |
| Fleet custody         | Off at launch; self-managed custody first, with two-of-three threshold recovery only after independent review.                                                                                                             | Custody is harder to recover safely than ciphertext replication. A single remote key service becomes a critical availability and trust dependency.                                                            |
| Zogograph key hosting | Do not launch with key hosting. Launch optional ciphertext replica storage first; reconsider custody only after mature fleet custody, incident response, recovery drills, tenant isolation, and legal/privacy terms exist. | Hosting both encrypted artifacts and unwrap capability increases blast radius and operational obligations. Keep those roles separate even if both services are later sold.                                    |
| MySQL/MariaDB         | Do not ship as an encryption substitute. Offer only as an advanced storage-backend package after the package runtime is stable.                                                                                            | It adds server operations and credentials without automatically solving key custody or at-rest encryption.                                                                                                    |
| Redis                 | Do not offer as metric storage. Offer later only as a bounded cache/queue package with TLS, authentication, eviction policy, and no secrets by default.                                                                    | Redis is easy to expose accidentally and is not a durable replacement for the metric database.                                                                                                                |

## Recommended support matrix

The commercial site should publish a database-backed support matrix with an
expiry/review date for every entry. Start with the following categories, not an
unbounded list of host distributions:

- **Portable:** standard PHP 8.1+ with PDO SQLite; sealed-copy package and
  operator-managed volume protection only.
- **Verified SQLCipher:** Zogograph-published container images and selected VPS
  images where the exact PHP/PDO SQLCipher driver build is continuously tested.
- **Unsupported but detectable:** any other environment; show why the package
  cannot activate and offer sealed copies or host-volume guidance.

This avoids promising that a generic `pdo_sqlite` installation has encrypted
SQLite support when it does not.

## Service and API design decisions

Use one versioned service namespace, independent of a provisional domain:

```
{configured service base}/v1/{service}/{tenant-or-fleet}/{resource}
```

The first service names should be `key-custody`, `replica-relay`, and
`entitlements`. A client stores a configured base URL and pinned enrollment
identity, never a literal deployment URL. On the provisional
VPS, those services can initially live beneath separate paths, for example
`/services/key-custody/`; moving each to its own domain later must preserve the
same versioned API and peer identity.

Fleet API requests need mutual TLS or an equivalent enrolled-client credential,
request timestamp, nonce, scoped role, payload hash, and replay protection.
Transport credentials, package-download credentials, and data-encryption keys
must be distinct. A replica endpoint accepts ciphertext and metadata only. A
custody endpoint receives a wrapped data key and returns a short-lived unwrap
authorization only after policy evaluation; it does not accept metric data.

Normal HTTPS certificate and hostname validation is the recommended default for
small deployments. The custody and replica provider packages also support an
optional `sha256//...` SPKI pin after an administrator has recorded an
endpoint-identity migration procedure. A pin must be updated and tested before
the remote certificate **key** rotates; it is not a replacement for enrolled
client credentials. On the service side, SQLite metadata, key material, and
ciphertext artifacts use absolute, non-symlinked private paths outside the web
root. A reverse-proxy path prefix is routing only, never a storage boundary.

Provider onboarding has two separate stages. First, the setup UI invokes a
credential-free `/v1/health` probe using the supplied HTTPS URL and optional
pin, and verifies the expected service identity and API version. Only after
the administrator has reviewed that result may it accept an enrolled-client
bearer and signing key into encrypted package secret references. A successful
TCP/TLS connection alone is not enrollment evidence; this split limits
accidental credential disclosure to a mistyped or redirected endpoint.

## Package choices without overload

The website should present a single **Protection** page. It first asks what the
administrator wants to protect and then shows only installed compatible
packages. Advanced provider, SQLCipher, storage, and fleet controls stay hidden
until their package is installed and enabled. Each package supplies a short
capability summary, prerequisites, impact, recovery consequence, and a
connection test; it may not silently change another package's settings.

Recommended package families:

- `sealed-copies` — local encrypted artifacts and recovery verification.
- `sqlcipher-encrypted-sqlite` — live encrypted SQLite where a compatible PDO
  driver is installed.
- `fleet-custody` and `fleet-replica` — self-managed peer custody and
  ciphertext replication.
- `kms-*` — optional third-party or Zogograph key-provider adapters.
- `storage-mysql` / `storage-mariadb` — optional transactional storage
  backends.
- `cache-redis` — optional bounded cache/queue integration, never a default
  database replacement.

Every executable package gets a private data directory or database registered
with the package runtime. It cannot read another package's data, the core
configuration, or key material unless the administrator grants that specific
capability. Core retains the enrollment, authorization, audit, package
signature, settings, and secret-reference interfaces; packages retain their
implementation and private schema migrations.
