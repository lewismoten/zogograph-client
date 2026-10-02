# Zogograph executable packages

This directory holds source packages that require the future isolated Zogograph
package runtime. It is separate from `data/marketplace/`, whose current JSON
catalog is deliberately data-only.

Every executable package must declare its capabilities, settings schema,
compatibility requirements, private-data/migration namespace, and entry point.
The runtime must verify a package signature and compatibility, obtain explicit
administrator grants, create its private storage, and audit enablement before
loading the entry point. A package must not be loaded directly by PHP include
paths or by copying its files into the web tree.

When declared, the signed `settings_schema` is enforced before settings are
stored. Zogograph supports the small JSON Schema subset needed by packages:
object properties/required fields, `additionalProperties`, scalar and array
types, enums, string length/patterns, numeric limits, and array item limits.
Keep package schemas within that subset so an administrator gets a clear
configuration error instead of silently accepting an unsupported rule.

Packages may declare an ordered `data.migrations` list of signed
`migrations/NNN-name.sql` files. Core re-verifies installed hashes before it
applies those files transactionally to that package's private SQLite database
and records each applied migration. The declarative runner rejects attachment,
pragma, vacuum, extension-loading, and transaction-control statements; it is
not a way to run package PHP or reach core/other-package data.

Package settings may refer to credentials only as package-scoped `secret_ref:`
values. Core encrypts the referenced value in `setup.db` using a key file kept
outside the web root; it never returns the plaintext through the settings API.
The same local key as sealed copies is the simple default, while a separate
0600 key file is available for administrators who require separate custody.
Core refuses to save a reference until its encrypted secret record exists, and
refuses to delete a secret that active package settings still reference. Remove
or replace the reference first; this prevents a later provider invocation from
failing because a credential was silently orphaned.
The Package runtime page can list only each opaque reference, its timestamps,
and whether settings use it; it cannot reveal a stored value. It offers deletion
only for an unused reference, so credential rotation is create → save replacement
→ delete old reference.

The package-runtime dashboard rehashes installed immutable files and compares
the installed manifest with its install-time fingerprint before it reports a
package as usable. A changed, missing, extra, or symlinked package file—or a
changed manifest—is shown as an integrity failure, blocks a new enablement, and
raises the **Installed package integrity needs attention** alert during normal
alert evaluation. The alert verifies hashes and metadata only; it never loads
or executes the altered package.
Older installed packages that predate the manifest-fingerprint migration are
also fail-closed; reinstall their reviewed signed release to establish new
evidence. Never treat a manifest comment or a browser status alone as evidence
that altered package code is safe.

## Shell placement metadata

Signed `dashboard_pages` and `setup_panels` may declare an optional `parent`
whose value is an existing core page or Setup section id, such as `storage` or
`security`. The shell uses that metadata only for navigation placement; it
continues to render the signed title, summary, and schema-validated settings
itself. If a dashboard parent is unavailable in the installed shell, the page
safely falls back under **Package runtime**. A package cannot supply HTML,
JavaScript, CSS, route handlers, or a renderer through this field.

## Bundled design-preview providers

- `sqlcipher-encrypted-sqlite` — live encrypted SQLite only on a verified
  SQLCipher/PDO platform; never falls back to plaintext SQLite.
- `zogograph-key-custody` — optional remote wrapped-key custody using an
  enrolled client credential, separate from management and billing tokens.
- `zogograph-replica-relay` — optional signed transfer of already-sealed
  ciphertext to a separate relay; it never sends a plaintext store or key.

These packages are intentionally unavailable until the isolated runtime can
enforce their capabilities, settings/secret boundaries, and audit trail.
The deployment and JSON-RPC boundary for that runtime is specified in
[`Isolated runtime design`](../docs/ISOLATED_RUNTIME.md). It deliberately requires a
real OS/container sandbox; PHP-only restrictions are not considered execution
isolation.

The bundled worker currently supports only an isolated, no-secret capability
probe. A deployment may opt in to launching that probe through a package-
specific, manifest-pinned sandbox command; it is diagnostic only and package
grants do not authorize arbitrary provider operations.

The **Package runtime** page performs a core-owned platform check before
presenting a package as ready. It checks declared PHP version and extension
requirements, detects a genuine SQLCipher PDO driver where one is requested,
and reports that secret-bearing isolated execution is still unavailable. A
deployment may separately run only its signed no-secret diagnostic probe; a
passing platform check or probe never activates a protection claim.
