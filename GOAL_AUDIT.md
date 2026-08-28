# Setup, shell, and remote-delivery audit

This is a living completion audit for the setup/shell objective. A passing
test proves only the named boundary below; it does not by itself prove the
whole objective is finished.

| Requirement | Current evidence | Remaining proof or work |
| --- | --- | --- |
| Setup update notice | `setup.php` compares build identity, rechecks on focus/visibility return, and persists it across dashboard/setup navigation. `tests/i18n.php` covers the banner source/locales and `tests/release-artifacts.php` requires the deployment manifest and HTTPS verification. | Exercise against a deployed replacement build in a browser using `INTEGRATION_VALIDATION.md`. The supplied test site currently presents a self-signed certificate, so its build manifest cannot be verified by the normal HTTPS path. |
| OpenID Connect provider | `lib/openid.php`, protected Setup client management, discovery, JWKS, PKCE, and `tests/openid.php`. | Run the independent-client browser check in `INTEGRATION_VALIDATION.md`. |
| TOTP and recovery codes | Setup generates a secret, QR URI, verifies a code before save, displays recovery codes once, and hashes/consumes them in `lib/auth.php`; `tests/totp.php` covers code semantics. | Run the display-once/reload browser check in `INTEGRATION_VALIDATION.md`. |
| Locale shell | Core locale runtime has language detection, saved picker, named formatting, RTL, key mode, core fallback protection, and package catalog namespacing. Setup/catalog tests cover the supported catalogs. | Continue replacing any newly introduced visible raw strings; validate with native-language reviewers. |
| Package lifecycle | Signed ZIP intake, integrity, dependencies, explicit grants, enable/disable, revoke/remove, inert dashboard/setup metadata, and package locale catalogs are covered by `tests/package-runtime.php`. A lifecycle change now removes disabled pages and package translation extensions from the live shell. Assistant and Trusted servers are built-in core features: `core_features.assistant` and `core_features.peers` remove dashboard routes and reject their API surface; Trusted servers also closes its telemetry, peer-data, and connection endpoints. `tests/core-features.php` covers both boundaries. | Convert the remaining core pages under the same runtime-enforced feature pattern; do not claim full core-to-plugin conversion until each has an enforced runtime boundary. |
| Setup extensibility | Searchable metadata-driven Setup navigation and signed inert package panels are implemented in `lib/setup-panels.php`. | Package renderers remain intentionally unavailable until independent code isolation is attested; nested secret structures need a similarly constrained bridge. |
| Core-to-remote delivery | Core validates rsync/SFTP/FTPS profiles, encrypted local credential references, target tests, safe ZIP staging/copy, and remote update policy compatibility. It also emits a structured nonsecret handoff; the separately unlocked remote Setup accepts only its controller address and update policy. Core and remote handoff validators have focused tests. | Run the `INTEGRATION_VALIDATION.md` deployment procedure against each transfer method and a real web-server release root. The supplied test HTTPS endpoint currently has a self-signed certificate, which blocks secure release verification. Do not promise one-click credential enrollment: remote passwords, pairing credentials, host keys, and deployment credentials remain deliberately outside the handoff. |
| Remote setup and updates | Restricted remote shell/setup, controller preflight, private config, manual/download/auto policy, signed updater, atomic releases, and idempotent repeated updates have focused tests. | Run an integration deployment against each transfer method and a real web-server release root. |
| Account merge | Review-before-apply, private source clone upgrade, rollback snapshot, overlap consent, and a genuine version-1 collector schema regression live in `lib/account-merge.php` and `tests/account-merge.php`. When supplied, `scratch/old-stats.db` is also inspected and privately prepared without modifying its source. | Keep adding sanitized fixtures when an older production schema is encountered; the supplied account fixture is currently schema 57, so it verifies the real-store path but not an older account migration. |

## Non-negotiable boundaries

- Packages must never turn signed metadata into executable dashboard or Setup
  code without an independently verified isolation boundary.
- Core feature toggles need runtime enforcement, not merely hidden navigation.
- Core deployment must not copy a remote password, pairing secret, or private
  remote config through a public release tree.
- `npm test && npm run build` is required for client changes; the local
  SQLCipher preflight is expected to skip when its PHP driver is unavailable.
