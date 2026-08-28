# Zogograph marketplace plan

## Scope

Zogograph’s marketplace is a catalog of optional, locally installed extension
packages: integrations, alert-rule packs, dashboards, and guided-operation
packs. It is not an app store that downloads or runs arbitrary code from the
browser.

## Trust model

- Packages are discovered from a configured local directory only.
- Every package declares a stable id, version, publisher, summary, license,
  and requested capabilities.
- Administrators review and explicitly enable each package.
- Package capabilities are allowlisted and shown before enablement.
- Remote discovery, payment, telemetry, automatic updates, and executable
  package hooks are not part of the current local marketplace and remain
  disabled. An isolated executable runtime is planned for encryption, storage,
  and service packages; it must not be enabled merely by accepting a manifest.

## Delivery sequence

1. [x] Manifest schema, strict validation, and bounded local catalog discovery.
2. [x] Database-backed enablement state, audit records, and capability review.
3. [x] Administrator catalog UI with search, package detail, enable/disable, and safe
   unavailable/error states.
4. [x] First data-only package type: alert-rule recommendations.
5. [x] Signed package verification, compatibility checks, install/rollback,
   and release provenance before any remote package source.
6. [ ] Signed isolated executable-package runtime: explicit capability grants,
   package-private settings/database/migrations, secret references, sandboxed
   entry points, compatibility checks, audit, rollback, and revocation.
7. [ ] First executable packages: sealed-copy encryption and SQLCipher live
   SQLite. The SQLCipher scaffold is in `plugins/sqlcipher-encrypted-sqlite/`.
8. [ ] Publisher workflow, commercial terms, moderation, payments, and hosted
   marketplace service only after legal and business-owner decisions.

Executable-package releases use a signed file-integrity manifest: SHA-256 for
every immutable release file and an Ed25519 signature over the complete
manifest. This detects added, removed, altered, and symlinked files. The future
runtime must refuse a package whose integrity or trusted publisher signature
does not verify; a separate integrity-alert package can surface failures as
alerts without granting it authority to repair or load the package.

## Completion evidence

Each delivery item requires a server-side authorization test, malformed
manifest coverage, capability-denial coverage, migration/upgrade coverage, and
documentation that describes actual behavior rather than planned behavior.

### Current non-executing compatibility evidence

Before executable isolation exists, core validates the signed package's
declared minimum PHP version and required extensions, and can detect a real
SQLCipher-capable PDO driver without loading any package entry point. The
package dashboard displays the result so an administrator sees why a package
is unavailable. A passing result does not grant execution, network access, or
live-database encryption; it only avoids asking the operator to guess which
platform prerequisite is missing.

### Implemented local provenance

Manifests may carry an Ed25519 signature. When a signature is present,
Zogograph verifies it against a locally configured publisher key and refuses to
enable a malformed, untrusted, or invalidly signed package. Unsigned packages
remain local-only administrator-reviewed manifests. Compatibility ranges,
including PHP and database-schema bounds, are checked before enablement; a
changed package version requires fresh administrator approval. Package
installation and rollback are local-only, atomic, and leave the restored
revision disabled for review. Each replaced manifest is retained under a
content-derived SHA-256 revision id, and installation, approval, disablement,
and rollback are audit records. Remote sources remain unavailable.

## Current package format

Set `marketplace.directory` in `lib/config.php` (it defaults to the protected
`data/marketplace/` directory), then add one `<package-id>.json` file per package.
Only `alert_recommendations` is currently accepted. A recommendation is shown
for administrator review only; enabling it neither changes an alert rule nor
executes package code. The planned executable package format is intentionally
separate until its isolation and authorization contract is implemented.

## Signatures and revisions

An unsigned manifest is permitted only as a locally reviewed package. To add
publisher provenance, configure the publisher's Base64 Ed25519 public key under
`marketplace.trusted_keys` and add this object to the manifest:

```json
"signature": {
  "key_id": "example-publisher",
  "algorithm": "ed25519",
  "value": "base64 detached signature"
}
```

The signature covers the manifest without `signature`, serialized as compact
JSON with object keys sorted lexically and array order preserved. A package
that supplies a signature but cannot verify is visible for diagnosis but cannot
be enabled. Use **Install local manifest** for an atomic write, and **History**
to restore a retained revision; neither operation enables the package.

Publishers can create a keypair locally with:

```bash
php scripts/marketplace-sign.php keygen --key-id example-publisher --publisher "Example publisher"
```

Keep the printed private key offline. To sign a manifest without exposing that
key in the command line, provide it interactively (or through a protected
standard-input pipeline):

```bash
php scripts/marketplace-sign.php sign --manifest /secure/package.json --key-id example-publisher
```

```json
{
  "id": "uptime-guidance",
  "version": "1.0.0",
  "name": "Uptime guidance",
  "publisher": "Example publisher",
  "summary": "Advice for reviewing unexpected restarts.",
  "license": "License identifier or terms",
  "capabilities": ["alert_recommendations"],
  "compatibility": {"min_php": "8.1.0", "min_schema": 49},
  "recommendations": [
    {
      "rule": "host_uptime",
      "severity": "warning",
      "reason": "Review unexpected restarts."
    }
  ]
}
```
