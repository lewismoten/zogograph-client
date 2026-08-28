# Zogograph completion plan

This is the working implementation plan for the Zogograph product-completion
directive. It is deliberately a living delivery record, not a statement that
the product is complete.

## Operating rules

- Security, authorization, secret handling, and data safety come first.
- Every change must preserve PHP-and-SQLite operation when command execution is
  unavailable.
- New privileged behavior is server-authorized, CSRF-protected, audited, and
  capability-gated before it is exposed in the browser.
- A supported claim needs code and repeatable validation, not only a screen or
  a document.

## Current baseline (2026-08-26)

- [x] PHP/SQLite application with versioned migrations, WAL, busy timeout, and
  request error boundary.
- [x] Local session authentication, CSRF checks for administrative writes,
  group-based access checks, route access checks, and masked unauthenticated
  path data.
- [x] Existing assistant, alerts, email, Telegram, host collector, and
  dashboard features; these need requirement-by-requirement verification rather
  than replacement.
- [x] Verified pre-migration SQLite snapshots and documented restore/recovery
  procedure for primary, host, and assistant databases.
- [x] Reproducible install and degraded-capability tests.
- [x] Reproducible migration-recovery snapshot test for the common SQLite
  snapshot primitive used by account, host, and assistant databases.
- [x] Reproducible SQLite writer-contention test, including recovery after the
  writer lock is released.
- [x] Dependency-free policy checks covering remote-host limits, outbound AI,
  URL-watch private-target controls, and trusted-peer origin pinning.
- [x] Static HTTP policy check covering content policy, anti-framing, browser
  capability restrictions, and direct-file denial rules.
- [ ] Full per-host enrollment, signed/replay-protected remote transport, fleet
  lifecycle, and commercial entitlement verification.
- [ ] Optional at-rest encryption packages, self-managed key custody,
  encrypted fleet replicas, and key-expiry/rotation recovery. The current
  core has opt-in local sealed-copy primitives and setup choices, but the
  executable package runtime, restore/retention workflow, fleet custody, and
  live encrypted SQLite support remain incomplete. See
  `ENCRYPTION_AND_FLEET_PLAN.md` and `ENCRYPTION_REVIEW.md`.
- [ ] Complete browser editor safety/recovery workflow.
- [ ] Authentication recovery, MFA, OIDC verification, commercial entitlement,
  hosted-service boundary, and release/update workflow verification.

## Delivery sequence

1. **Data and access safety** — migration backups/recovery, transactional
   integrity, route authorization, request validation, secret redaction, and
   security headers.
2. **Reliable PHP-only operation** — installer diagnostics, capability states,
   collector failure states, retention/export/restore, and SQLite contention
   coverage.
3. **Operational workflows** — alerts and notification lifecycle, email and
   Telegram delivery, protected AI triage, audit evidence, and browser editing
   recovery.
4. **Fleet and product controls** — remote enrollment/transport, host lifecycle,
   roles, entitlements, OIDC, and hosted-service abstractions.
5. **Release readiness** — accessibility, compatibility, onboarding,
   documentation, SBOM/license/security process, upgrades, and end-to-end tests.

## Active increment

### Setup, language, package-shell, and remote-client roadmap

- [x] Show a deployment-update reload notice in both the dashboard and Setup.
- [x] Core Setup cron editor with per-job profiles, offsets, previews, and
  independently applied managed jobs.
- [x] Inert signed package panels, schema-validated package settings, audit,
  and opaque secret-reference selection in Setup.
- [~] Explain and provision the existing OpenID Connect provider in Setup.
  Provider capability, issuer, and signing-key configuration are visible;
  safe signing-key generation and trusted-client management remain.
- [~] Guided TOTP enrollment: Setup generates a Base32 secret, requires a
  successful verification code before saving, and displays one-time backup
  codes once after acknowledgement. A scannable QR presentation remains.
- [ ] Replace raw dotted generated labels with authored Setup questions.
- [~] Build locale catalogs, locale preference/detection, named formatting,
  RTL support, key-inspection mode, and package language catalog merge rules.
  The dashboard shell and signed package catalogs are covered; feature-panel
  and server-rendered Setup localization remain.
- [~] Move core pages to a dependency-aware, enable/disable-able plugin-shell
  model. Signed archive upload/install/remove, dependency gating, Setup panels,
  and core-rendered dashboard pages exist; core feature-page conversion remains.
- [~] Give client-remote the same shell, controller enrollment/configuration,
  tested transport deployment choices, updates, and compatible database merge.
  Restricted private setup, controller protocol preflight, pairing storage, and
  update policy configuration exist. Core now validates rsync/SSH, SFTP, and
  explicit-TLS FTP deployment profiles, encrypts deployment credentials as
  opaque credential and host-key references, verifies the remote target before
  every copy, and privately stages a bounded release ZIP for transfer. Signed
  remote update execution and approved telemetry sender remain.
  Compatible account collector stores can now be merged through a
  snapshot-backed, overlap-reviewed transaction with a guided Setup
  upload/review/apply interface that requires stopped-writer and overlap
  acknowledgements.

Audit route-level state changes and secret exposure next. The first finding was
an unprotected sign-out route; it now requires POST plus the current CSRF token.
The remote-peer OIDC flow now pins discovery, token, and key endpoints to the
configured peer origin, validates time-sensitive ID-token claims, and clears a
session token when the peer is removed. Continue the audit across installer and
AI endpoints, then build the signed/replay-protected remote transport rather
than treating the current OIDC peer record as fleet enrollment.

Hosted AI provider URLs now require HTTPS plus public DNS addresses that are
pinned for each cURL request; compatible-model endpoints remain loopback-only
by default. Image generation, reaction search, and provider asset downloads now
share that public-HTTPS/pinned-address policy; asset downloads reject redirects
rather than following them into an unvalidated host. Next, audit installer input
and remaining URL-watch policy before exposing any additional external
integrations.

URL monitoring now pins resolved addresses and permits private targets only when
their exact host is in `url_watch.private_hosts`. Automatic redirect following
is handled one hop at a time, with each target independently policy-checked and
pinned; unsafe private redirect destinations are refused. Without cURL the
feature reports that safe checks are unavailable rather than falling back to an
unpinned stream request.

The web and CLI installer now present the same preflight report for PHP, PDO
SQLite, writable data/configuration storage, HTTPS, timezone, command-execution
capability, and cron setup. Required failures are explicit while optional
command capability remains a warning, preserving a useful PHP-only install.

Host and assistant databases now use the same pre-migration snapshot mechanism
as the primary store. Fresh-store and existing-store paths are both covered by
reproducible PHP checks; the next reliability increment is an operator-facing
integrity/export/restore surface rather than more hidden migration behavior.

Storage pages now run a bounded SQLite `quick_check(1)` and show both the
integrity result and private migration-recovery snapshot count/newest time for
each readable database. Full export/restore actions still need their own
authorization, confirmation, recovery, and large-file test coverage.

Administrators can now run an explicit, audited full account-database integrity
check from Storage. It is CSRF-protected and read-only; the normal page keeps
using the bounded quick check so routine browsing does not become maintenance.

Storage can now produce a one-time consistent account-database export. It is
admin- and CSRF-gated, uses a SQLite snapshot rather than an unsafe live-file
copy, records the event, and removes the temporary server file after streaming.
Host and assistant export/restore workflows still need their own privilege,
ownership, recovery, and large-file handling coverage.

Account restore is now a separate CLI-only workflow. It requires an explicit
writer-stop acknowledgement, rejects an invalid or wrong-kind SQLite file,
captures the current account database before replacement, renames atomically,
and removes the stale WAL/SHM companions. Host and assistant restore remain
separate because their ownership and availability contracts differ.

Remote transport now has an initial signed inbound receiver: a new trusted
server gets a one-time 256-bit transport credential, and `peer-push.php`
requires its HMAC, bounded body, fresh timestamp, and unique nonce before it
persists a remote vital receipt. Sender scheduling, retry/queue behavior,
enrollment expiry/rotation, and fleet views remain to be built on that protocol.

An opt-in remote cron sender now delivers its newest local vital reading to the
paired controller over pinned HTTPS. A down controller is reported as delivery
trouble but cannot turn local collection into missing/zero data. Retry queues,
compression, bandwidth/frequency controls, and controller fleet presentation
remain next.

Remote delivery credentials can now be rotated independently of OIDC client
credentials. Rotation is write-authorized, CSRF-protected, audited, returns a
replacement only once, and immediately revokes the prior HMAC key.

Trusted remote-host records now carry normalized operational context
(description, tags, group, environment, timezone, and maintenance note).
These are explicitly organizational until later host/group authorization and
maintenance-window policies enforce behavior from them.

Remote transport data now participates in retention: accepted vital payloads
default to 400 days and replay receipts to two days, with an enforced minimum
day for nonce receipts. This keeps long-running fleet transport bounded.

The trusted-server table now presents a compact fleet summary from accepted
signed delivery records: latest delivery, CPU, memory, and disk-free evidence.
It deliberately distinguishes absent values from zero.

Remote vital evidence is immutable by peer and collection timestamp. Repeated
signed requests still produce replay-protected receipts but cannot overwrite
the first accepted historic measurement.

Remote receipt and vital persistence now commits atomically. A failed vital
write rolls back its nonce receipt, so the sender can retry rather than being
incorrectly treated as a replay after a transient controller write failure.

New and rotated remote delivery credentials are short-lived pairing
credentials: they expire after 24 hours unless the first valid signed delivery
activates them. Existing peer credentials remain compatible.

`SECURITY.md` now documents private vulnerability reporting, safe evidence
handling, and containment expectations. The self-hosted contact is configurable
as `security.contact` rather than inventing a public address.

Local sign-in now has a server-side per-client wrong-password throttle: eight
failures in fifteen minutes receive the same generic response and later limited
attempts do not extend the window. A dependency-free SQLite test covers the
threshold and expiry behavior.

Audit history now retains a responsive 500-row browser view while a separate
signed-in, CSRF-protected CSV download streams all retained entries. The export
records itself and neutralizes spreadsheet-formula-looking text before writing
cells, so operational evidence remains useful outside the dashboard.

Remote delivery now persists a bounded, deduplicated SQLite outbox during a
controller outage and retries a small oldest-first batch on each vital run.
Every retry gets a fresh timestamp and nonce, so replay protection does not
conflict with delayed delivery. Exponential retry backoff now ranges from one
minute to one hour. Payloads are gzip-compressed when that helps and verified
before bounded decompression. New delivery scheduling is now configurable from
one minute to one day while queued retries remain independent.

The Free edition is now represented by one normalized entitlement boundary:
one controller plus three configured remote hosts by default. The peer API
enforces the allowance before it writes configuration, while the dashboard
shows usage and prevents an additional add action. Future offline licenses or
hosted plans can supply the same normalized limit without scattering product
rules through peer handlers. This is a capacity boundary, not yet signed
license verification or remote-host enrollment/transport.

Fleet summaries now query only the newest accepted evidence per remote peer,
supported by a matching migration index, rather than decoding every retained
remote vital on each dashboard refresh.

Remote delivery can now be enrolled without configuring OIDC: peers must either
provide every OIDC client field or none, while the OIDC read/test controls stay
hidden or disabled until that separate connection is complete.

The System page now produces a signed-in, CSRF-protected sanitized diagnostic
snapshot for support. It exports capability states and counts instead of config
or collected evidence, and records each download in the audit trail.

Telegram alert delivery now builds a bounded summary before transport. It orders
by severity, preserves the dashboard link, and explicitly reports omitted
alerts instead of silently losing the end of an oversized batch.

Fully refused alert-email batches now have persisted bounded retry state. The
retry worker only revisits batches no recipient accepted, preserving the exact
original message and avoiding duplicate delivery after a local MTA acceptance.

The existing constrained browser editor now preserves line endings, detects a
concurrent change with a content fingerprint, writes atomically, and refuses to
overwrite an existing file unless a private pre-save recovery copy was made.
Its recovery copies are now path-bound, listed in the editor, and restored only
after a confirmation plus a fresh copy of the current version. Search/replace,
including keyboard shortcuts, now operates locally in the editor; syntax
highlighting and broader configured editing roots remain before the
editor-workflow item is complete.

The terminal now also exposes a bounded, non-recursive watched-root browser,
so editing no longer requires knowing an editor command and shell path. It
reports links without following them and repeats canonical-path/access checks
when a selected regular file is opened.

The browser editor now renders a synchronized, non-copyable line-number gutter
while retaining native textarea behavior and the existing save and recovery
protections.

Before editing, the browser now identifies supported ASCII/UTF-8 text and shows
its modification time, permissions, and line-ending convention. A bounded,
local change preview makes additions and removals reviewable before the existing
atomic save; legacy 8-bit data remains safely refused rather than risking an
invalid browser JSON response.

Recognized source and configuration files now offer a local read-only syntax
preview using text nodes only. It provides syntax-aware reading without
executing or serializing a file, while the native textarea remains the
accessible editing surface and protected save workflow.

Editor dialogs now ask before a Close or Escape discards changed native-textarea
content. The guard compares the browser-normalized opened value, so preserved
CRLF files do not produce a false unsaved-change warning.

The browser-only display preferences now include a choice of system, compact,
or classic monospace typeface for the editor, diff, and syntax preview. It
uses only local installed fonts and affects neither stored files nor other
users, keeping readability control independent of access or server settings.

Display preferences also provide an explicit high-distinction blue, orange,
and violet status palette for common red/green colour-vision differences.
Status words and symbols remain present in every palette, so colour is never
the only state cue.

The browser editor now states its native keyboard controls beside the editing
surface, including undo, redo, find, replace, and change review. This retains
the browser's reliable textarea history instead of creating a second unsafe
history layer around protected file saves.

A timezone-aware working-hours decision primitive now has deterministic tests
for ordinary weekday delivery, after-hours delay, and Error/Critical bypass.
It is a foundation only: delivery queue persistence, recovery summaries, and
the user/team schedule interface remain required before working-hours delivery
can be marked complete.

The editor now also refuses environment files, private keys, local databases,
and known secret-bearing configuration files on the server, even if they sit
inside the watched root. This keeps credentials out of browser responses and
recovery copies instead of presenting an insufficient client-side warning.

Files reported as unwritable now open in a review-only editor dialog. Local
finding still works, while save, restore, and replace controls are absent rather
than promising an operation the server will refuse.

Alert rules can now be temporarily muted from the Alerts page with a required
reason and bounded expiry. The original event keeps an explicit suppressed
delivery disposition; Error and Critical alerts bypass a mute, and ending a
mute expires rather than deletes its operator record. The overview banner now
honors the same disposition while the unread badge continues to count evidence.

Release artifacts now include a dependency-free CycloneDX SBOM and third-party
inventory, with a machine-checkable test. Commercial source-visible license
text remains deliberately blocked on legal-owner approval; `LICENSE-STATUS.md`
records the exact decision required rather than falsely claiming a license.

New audit records now retain a structured authenticated actor alongside their
source address; collector-created records identify the collector. Historical
records remain visibly unattributed rather than being retroactively guessed.

Local authentication now optionally verifies a standards-based six-digit TOTP
second factor after password verification. Legacy and named-user secrets remain
private configuration, failures are generic and throttle-equivalent, and test
coverage includes the RFC 6238 reference vector. Enrollment/recovery codes and
full authentication-recovery workflow remain before the authentication item is
complete.

Optional TOTP now has an administrator-controlled recovery-code workflow:
generation requires a current authenticator code, atomically replaces prior
codes, displays the new codes once, and retains only password hashes. Each code
is consumed atomically during login and that use is recorded without retaining
the secret itself.

Administrators can now invalidate every signed-in browser session through a
configuration-backed session generation, including on shared hosts that do not
offer controllable session-file storage. The action is write-authorized,
CSRF-protected, audited, and signs out its caller too; new logins receive the
current generation while older or legacy sessions are rejected on their next
request.

Emergency password recovery is now available as a CLI-only, filesystem-owner
workflow for the legacy administrator or an existing named local user. It never
needs SMTP or the account database, stores only a new password hash, and advances
the same session generation so compromised browser sessions cannot survive it.
The whole `scripts/` directory is denied by the Apache and Nginx deployment
policies as a second, independent guard for recovery and restore commands.

The optional Mac metadata cleanup helper now requires a single explicit CLI
directory, resolves that target before work, refuses the filesystem root, and
is denied by both Apache and Nginx example policies. It cannot be triggered by
an HTTP request or silently default to the application directory.

Remote vital transport now requires an explicit controller protocol-version
acknowledgement in addition to the signed payload version. A rolling upgrade
therefore becomes a visible queued compatibility failure rather than a false
successful delivery.

Creating or rotating a remote transport credential now displays a complete,
copy-safe remote `remote_delivery` configuration fragment rather than asking an
operator to transcribe an endpoint, peer name, and secret separately. It is
still a short-lived one-time enrollment artifact and never appears in later
peer reads.

The supported OpenID provider path now loads configuration through the same
store as the rest of the application and audibly records authorization-code
issuance and token exchange. A repeatable OpenSSL-backed check covers discovery,
strict S256 PKCE, one-time code consumption, RS256 token verification, audience,
nonce, and future time-claim rejection. This is evidence for peer identity only, not a claim that external
OIDC sign-in is implemented.

The overview now recognizes an installation with no vital reading or completed
storage scan and shows concise, evidence-driven onboarding links. It is not a
dismissible tour: it clears only after both foundational collectors have
produced real local evidence.

Standalone peer data and peer connection entry points now use the shared
configuration store, matching the API and OIDC provider. A static policy check
guards these externally reachable routes against silently returning to direct
configuration inclusion.

The header now offers local browser-only display preferences for readable text
size and reduced motion. The system preference remains the default; an explicit
reduced-motion choice suppresses decorative transitions and animation across
the interface without changing server configuration or other users' views.

Nginx/PHP-FPM deployment now has a reviewed example policy mirroring the
Apache database/source/collector denials and browser headers, with the FPM
socket and HTTPS redirect intentionally left for the host operator to set.

Installer preflight facts now live in a small reusable PHP-only module rather
than inside the setup page dispatcher. Reproducible tests exercise an old PHP
version, absent SQLite, plain HTTP, shared-host cron guidance, disabled command
execution, and the minimum supported runtime, preserving the distinction
between a blocking prerequisite and an optional executable capability.

Migration retries now recognize a column, table, or index already created by
an interrupted older deployment before its version row was committed. A fixture
starting at schema 44 with the partial alert and audit objects verifies that the
next startup reaches schema 46 rather than wedging on an “already exists” error.

Provider failures now redact credential-shaped headers, fields, and text before
they become a browser result, audit detail, or persisted failed-response record.
The redaction check exercises bearer credentials, named key fields, nested error
objects, and ordinary safe diagnostic text.

The access defaults now expose compatible Viewer, Operator, and Administrator
role names alongside legacy Staff and Administrators groups. Viewer remains
read-only, Operator keeps the existing operational workflow without write
authority, and Administrator remains full access; direct policy tests cover
each boundary and the compatibility aliases.

Direct AI assets, fetched-page previews, and filesystem thumbnail endpoints now
repeat their AI or page authorization server-side. A signed-in identity without
the originating surface permission cannot retrieve retained media by identifier
or URL token.

The sanitized diagnostic snapshot now carries only the configured edition name,
remote-host allowance, and configured-host count alongside existing capability
states. This gives support evidence for the free-edition boundary without
exporting a license secret or general configuration.

AI now has installation-wide daily and UTC-calendar-month spend ceilings in
addition to each conversation's time, turn, and cost limits. Every model step,
including automatic alert investigation, checks shared recorded spend before it
leaves the machine; the settings page shows capped-period usage and the tests
cover day/month boundaries and uncapped operation.
