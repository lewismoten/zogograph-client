# SQLCipher encrypted SQLite package

This is the first executable-package adapter for live SQLite encryption. It is
deliberately **not installable by the current data-only marketplace**. Its
provider positively checks `PRAGMA cipher_version` and refuses ordinary
`pdo_sqlite`; it will never silently substitute a plaintext SQLite database.
The manifest defines the compatibility, isolation, private-data, and settings
contract the marketplace runtime must enforce before it can activate package
code.

The package must fail closed: if it cannot establish that the PHP PDO driver is
backed by the required SQLCipher build, it must leave existing plaintext files
untouched and report the unavailable capability. It must never silently use
normal SQLite after the administrator chose live database encryption.

When the isolated runtime eventually opens a selected database, the adapter
will prove more than driver availability: after applying the caller-owned
256-bit key it reads the SQLite schema and requires SQLCipher's full
`cipher_integrity_check` to return `ok`. A wrong key, a plaintext file, or an
integrity failure is refused before Zogograph enables WAL or performs any
application work.

Before activation the runtime must create and verify a sealed recovery artifact,
convert one selected store at a time, validate schema and application queries,
prove WAL and crash recovery, and retain the original until the administrator
confirms recovery. Each package version requires compatibility tests against
the supported PHP/OS builds. The adapter currently reports a detected driver
as _driver-ready_, not active: isolated execution and the recovery migration
are separate requirements.

## Release integrity and signing

`manifest.json` lists a SHA-256 hash for every immutable package file. The
manifest itself is protected by an Ed25519 detached signature, so it is not
listed in its own hashes. Create a publisher key once and keep the private key
offline:

```bash
php scripts/package-sign.php keygen --key-id zogograph-release --publisher "Zogograph"
```

After reviewing a package release, sign its manifest and write the output back
to `manifest.json` through the release process:

```bash
php scripts/package-sign.php sign --package plugins/sqlcipher-encrypted-sqlite \
  --key-id zogograph-release
```

Any changed, added, removed, or symlinked immutable file makes integrity
verification fail. Runtime package data is deliberately outside that signed
set. A future file-integrity alert package can consume these verification
results and alert without trusting comment headers embedded in source files.

## Customer testing and commercial review

Customers may generate their own publisher identity, sign a private test
package, and verify it locally before the executable package runtime is
available:

```bash
php scripts/package-sign.php keygen --key-id my-team --publisher "My team"
php scripts/package-sign.php sign --package /private/my-package --key-id my-team --write
php scripts/package-sign.php verify --package /private/my-package --public-key BASE64_PUBLIC_KEY
```

To request commercial marketplace review, create a signed, portable review
bundle. It contains the signed manifest and exactly the immutable files named
by it; it excludes runtime data and never needs the private signing key.

```bash
php scripts/package-submit.php prepare --package /private/my-package \
  --public-key BASE64_PUBLIC_KEY --output /private/my-package-review.zip
```

The commercial reviewer verifies this bundle against the customer publisher
key. Publication does not replace the customer signature; marketplace approval
is separate provenance.
