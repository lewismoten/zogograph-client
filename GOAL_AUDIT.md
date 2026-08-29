# Setup, shell, and remote-delivery audit

This is a living completion audit for the setup/shell objective. A passing
test proves only the named boundary below; it does not by itself prove the
whole objective is finished.

| Requirement | Current evidence | Remaining proof or work |
| --- | --- | --- |
| Setup update notice | `setup.php` compares build identity, rechecks on focus/visibility return, and persists it across dashboard/setup navigation. `tests/i18n.php` covers the banner source/locales and `tests/release-artifacts.php` requires the deployment manifest and HTTPS verification. | Exercise against a deployed replacement build in a browser using `INTEGRATION_VALIDATION.md`. On 2026-08-29, `https://test.zogograph.com/build.json` returned HTTP 404 and its root was an Apache directory listing, so Forgejo's `DEPLOY_URL`/`DEPLOY_PATH` still need to identify the installed app root. |
| OpenID Connect provider | `lib/openid.php`, protected Setup client management, discovery, JWKS, PKCE, and `tests/openid.php`. | Run the independent-client browser check in `INTEGRATION_VALIDATION.md`. |
| TOTP and recovery codes | Setup generates a secret, QR URI, verifies a code before save, displays recovery codes once, and hashes/consumes them in `lib/auth.php`; `tests/totp.php` covers code semantics. | Run the display-once/reload browser check in `INTEGRATION_VALIDATION.md`. |
| Locale shell | Core locale runtime has language detection, saved picker, named formatting, RTL, key mode, core fallback protection, and package catalog namespacing. Dashboard i18n now also uses locale-native byte, duration, span, relative-time, calendar, and range formatting; tests/i18n.php guards these shared formatters and catalog coverage for en, es, hi, zh, and ar. | Continue replacing any newly introduced visible raw strings; validate with native-language reviewers. |
| Package lifecycle | Signed ZIP intake, integrity, dependencies, explicit grants, enable/disable, revoke/remove, inert dashboard/setup metadata, and package locale catalogs are covered by `tests/package-runtime.php`. A lifecycle change now removes disabled pages and package translation extensions from the live shell. Assistant, Trusted servers, Marketplace, Terminal, Notes, Recent problems, Data storage, Audit log, Custom metadata, Email-alert configuration, Backup inspection, Protection, Alerts, Uptime, Package management, Chart history, System health, Network tools, and Host observations are built-in core features with runtime-enforced dashboard and API boundaries. Trusted servers also closes telemetry, peer-data, and connection endpoints; Terminal prevents assistant tools reading terminal evidence; Notes removes shared broadcasts, chart annotations, and the quick-note action; Recent problems hides its badge and log surface while recording continues server-side; Data storage hides its file/history views, inspection/deletion/symlink APIs, and assistant file tools while collectors and automatic retention continue server-side; Audit log prevents assistant action-history reads while accountability recording continues; Custom metadata retains labels and descriptions for re-enable; Email alert configuration leaves existing server-side alert evaluation and delivery unchanged; Backup inspection does not modify provider-managed backups or schedules; Protection leaves configured encrypted-copy schedules and verification running server-side; Alerts clears its shared badge/banner state while evaluation and delivery continue; Uptime does not change collector schedules; Package management also stops package pages and locale catalogs loading; Chart history prevents assistant chart tools from reading or rendering series while current overview collection remains available; System health leaves Setup and collector safety checks running while hiding dashboard findings; Network tools preserves its SSRF, allow-list, and rate-limit checks while hiding dashboard and assistant operation; Host observations leaves root-owned collection and its schedule running while blocking dashboard, assistant, and on-demand task control. `tests/core-features.php` covers every declared core-feature boundary. | Every static dashboard view and navigation route is now required to have exactly one runtime-enforced core-feature owner. Package-defined executable renderers remain deliberately unavailable until independently attested isolation exists. |
| Setup extensibility | Searchable metadata-driven Setup navigation and signed package panels are implemented in `lib/setup-panels.php`. Setup renders the signed settings schema itself—without loading package code—and supports standalone panels plus panels parented under any existing Setup section. The renderer exposes only encrypted secret references, never secret material. | Package-defined executable renderers remain intentionally unavailable until independent code isolation is attested. The present JSON-schema settings bridge is intentionally constrained; nested secret structures need a similarly constrained bridge. |
| Core-to-remote delivery | Core validates rsync/SFTP/FTPS profiles, encrypted local credential references, target tests, safe ZIP staging/copy, and remote update policy compatibility. It also emits a structured nonsecret handoff; the separately unlocked remote Setup accepts only its controller address and update policy. Core and remote handoff validators have focused tests. | Run the `INTEGRATION_VALIDATION.md` deployment procedure against each transfer method and a real web-server release root. The test hostname is reachable but does not currently expose an app at its root, so secure release verification remains pending a matching Forgejo `DEPLOY_URL`/`DEPLOY_PATH`. Do not promise one-click credential enrollment: remote passwords, pairing credentials, host keys, and deployment credentials remain deliberately outside the handoff. |
| Remote setup and updates | Restricted remote shell/setup, controller preflight, private config, manual/download/auto policy, signed updater, atomic releases, and idempotent repeated updates have focused tests. | Run an integration deployment against each transfer method and a real web-server release root. |
| Account merge | Review-before-apply, private source clone upgrade, rollback snapshot, overlap consent, and a genuine version-1 collector schema regression live in `lib/account-merge.php` and `tests/account-merge.php`. When supplied, `scratch/old-stats.db` is also inspected and privately prepared without modifying its source. | Keep adding sanitized fixtures when an older production schema is encountered; the supplied account fixture is currently schema 57, so it verifies the real-store path but not an older account migration. |
| Historical database fixtures | `tests/migration-snapshots.php` makes private copies of the supplied `old-stats.db`, `old-host.db`, `old-ai.db`, and `old-setup.db` fixtures; it checks source integrity, migrates each applicable clone, rechecks clone integrity, and verifies the original SHA-256 is unchanged. All four supplied fixtures passed this path on 2026-08-28. | Add a sanitized fixture whenever a production schema not represented by these files is encountered. |

## Live-validation prerequisites

The local workspace deliberately has no `TEST_URL`, `TEST_PATH`, or deployment
credentials. Forgejo supplies deployment variables to its workflow. On
2026-08-29, the supplied `https://test.zogograph.com` endpoint responded over
HTTPS, but its root was an Apache directory listing and `/build.json` and
`/setup.php` returned HTTP 404. Set Forgejo `DEPLOY_URL` to the public URL of
the actual app root and `DEPLOY_PATH` to that same site's document root, then
push a build and confirm its commit in `build.json`. Test-only OIDC, TOTP, and
remote-transfer configuration must not be committed or placed in the public
release tree.

## Recent core-boundary evidence

Overview is now a core feature. Its bootstrap endpoint remains reachable only
to supply shell/access state, and returns empty overview measurements when the
feature is disabled; the browser then falls back to another allowed page.
`tests/core-features.php` covers the declared feature status and bootstrap
redaction source.

Legacy dashboard scheduling, including its hidden Schedule and host-setup
panels, was removed. First-run guidance now links to Setup's protected Schedule
section, whose constrained cron editor remains the only scheduling interface.
The same test verifies that static dashboard views and navigation entries have
exactly one declared core-feature owner; this caught and closed the DNS-records
Network boundary.

## Non-negotiable boundaries

- Packages must never turn signed metadata into executable dashboard or Setup
  code without an independently verified isolation boundary.
- Core feature toggles need runtime enforcement, not merely hidden navigation.
- Core deployment must not copy a remote password, pairing secret, or private
  remote config through a public release tree.
- `npm test && npm run build` is required for client changes; the local
  SQLCipher preflight is expected to skip when its PHP driver is unavailable.
