# Encryption decisions adopted now

This is the short, decision-oriented companion to the implementation plan and
the later-review checklist. It records the recommended direction for the first
customer deployments; it is not a claim that every future package is active.

## Customer experience and defaults

Customers see one **Protection** page. They choose a protection scope first,
then see compatible methods supplied by installed packages. They do not see
uninstalled KMS, SQLCipher, fleet, database-server, or cache choices.

| choice                             | initial recommendation                                                                                         | why                                                                                                         |
| ---------------------------------- | -------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| normal database                    | SQLite                                                                                                         | It is the smallest supported, local-first deployment.                                                       |
| initially selected stores          | bootstrap configuration, `setup.db`, and `stats.db`                                                            | They normally contain the most sensitive credentials, policy, and operational history.                      |
| conditional stores                 | `host.db` and `ai.db` when present and confirmed                                                               | Host/process evidence and assistant history can be sensitive, but should not surprise a minimal deployment. |
| portable protection                | daily verified AES-256-GCM sealed copies, retained for 30 days                                                 | It works without a specialist database driver and gives a recoverable starting point.                       |
| live at-rest protection            | operator-managed host/volume encryption                                                                        | It covers a live SQLite database and its WAL/SHM files without pretending that a backup is live encryption. |
| local key custody                  | a dedicated, owner-readable key file outside the web root, with one separately protected offline recovery copy | This is understandable and independent of a hosted service.                                                 |
| remote replicas and hosted custody | off                                                                                                            | Neither should become an implicit dependency or an accidental upload path.                                  |

The first setup must require a successful recovery test before it calls a
policy ready. It must also show the space estimate for the next sealing run.
No retention job may remove the newest verified artifact for a protected
store.

## Options deferred behind packages

The following are valuable only when their package is installed, configured,
and has passed its specific release gate:

- `sqlcipher-encrypted-sqlite` for live encrypted SQLite. It must detect a
  genuine SQLCipher-capable PDO build and never fall back to plaintext SQLite.
- self-managed fleet custody and ciphertext replicas. Replica endpoints store
  ciphertext only; custody endpoints authorize envelope unwraps and never
  receive customer metric databases.
- third-party KMS/HSM adapters and the optional Zogograph key-custody service.
  Each is a provider package, not a core requirement.
- MySQL/MariaDB storage and Redis cache/queue integrations. Neither is an
  encryption feature, and Redis is not a default durable metric store.

Package code is not allowed to execute in a dashboard, cron, setup, or API PHP
process. Installing a package may expose metadata and settings, but cannot
imply that its proposed protection is active. A deployment-attested sandbox may
run only signed no-secret probes—such as SQLCipher's disposable preflight or
custody's credential-free endpoint identity check—and records bounded evidence.
The sandboxed worker and staged RPC operations are specified in
[PROVIDER_RPC_PLAN.md](PROVIDER_RPC_PLAN.md).

The Package runtime page also performs a core-owned platform check. It reports
missing PHP extensions, an unavailable SQLCipher PDO driver, or the absent
isolation runtime in plain language before an administrator spends time entering
package settings. “Platform ready” and a successful no-secret probe are only
prerequisites; neither is a live-encryption or provider-connection claim.

## Where a product owner changes this later

| decision                                                                    | authoritative future location                                         | do not change it by                                                 |
| --------------------------------------------------------------------------- | --------------------------------------------------------------------- | ------------------------------------------------------------------- |
| defaults, labels, supported packages, policy choices                        | authenticated setup/product settings database and Protection UI       | editing client source or a release manifest                         |
| package-specific schedule, retention, provider endpoint, and replica policy | that package's schema-validated settings and private package database | adding secrets to `lib/config.php`                                  |
| enrollment credentials and provider secrets                                 | encrypted package secret references                                   | browser storage, URLs, logs, or normal configuration values         |
| commercial plans, prices, domains, service endpoints, and support matrix    | site/marketplace administration database                              | embedding a provisional domain in a protocol or artifact identifier |
| custody entitlement                                                         | the separate commercial billing control plane                         | giving billing credentials custody or client-unwrapping permission  |

Until the settings migration is complete, the small bootstrap configuration is
an exception: it contains only the paths required to open Zogograph. Keep it
private and seal it as an artifact; do not add long-lived commercial,
management, or billing credentials there.

## Security boundaries to preserve

- Use different credentials for customer key management, each enrolled client,
  and commercial billing control. A compromise of one must not become access to
  the other roles.
- A hosted key-custody service receives wrapped key envelopes and authorization
  requests, never customer databases. A replica service receives ciphertext,
  never a usable data key.
- An artifact identifier, package identifier, client enrollment identity, and
  service protocol version remain stable across a rebrand or domain migration.
- Loss of the only recovery key means loss of the protected artifacts. Key
  rotation therefore verifies a new recovery path before retiring an old one.
- At-rest encryption does not protect a compromised running process that has
  already received an unwrapped key. Normal host and account hardening remain
  necessary.

## Review triggers

Review this record before enabling automatic deletion, publishing a new
SQLCipher platform image, allowing executable marketplace packages, turning on
remote replication/custody, integrating a payment provider, or changing a
public domain. Record the responsible operator, exact package/image version,
recovery-drill result, and next review date in administration settings.

For detailed rationale and release gates, see
[ENCRYPTION_REVIEW.md](ENCRYPTION_REVIEW.md) and
[ENCRYPTION_AND_FLEET_PLAN.md](ENCRYPTION_AND_FLEET_PLAN.md), plus the
[PROVIDER_RPC_PLAN.md](PROVIDER_RPC_PLAN.md) invocation contract.
