# Setup extensibility contract

`setup.php` is the administrator configuration surface, not a dashboard view.
Core and marketplace features may contribute panels only through the Setup
panel registry. A panel declares an id, title, navigation parent, capability
requirements, and a renderer. It never writes files or executes commands
directly from its renderer.

## Panel API

Core registers panel metadata in `lib/setup-panels.php`. Each definition has a
validated id, title, kind, keywords, and optional feature id, so the Setup
sidebar can remain data-driven and searchable as panels grow. `SetupCronApi` is the
first concrete restricted capability; it is used by the core Schedule panel
today. A future isolated package receives a `SetupPanelApi` object rather than
the full application. The currently concrete `SetupPanelApi` offers:

- `addPanel()` registers validated inert navigation metadata for one declared
  panel id. It cannot pass a renderer, HTML, JavaScript, or a callback.
- `cron()` returns the restricted `SetupCronApi`: `profiles()`, `preview()`,
  `status()`, and `apply()`. It accepts structured choices for named approved
  jobs, never arbitrary shell commands or an arbitrary crontab line.

Panels are hidden when their core/plugin feature is disabled. Plugins cannot
replace core panels, add executable shell text, access another plugin's
configuration, or bypass Setup's password/session/CSRF checks.

No package receives a direct settings object or a renderer callback. Setup's
own generic editor is the only current settings path; it validates a signed
schema, maintains Setup CSRF/unlock checks, and never loads package code.
Audit records and a package-local secret-reference picker remain follow-up
work.

## Signed package panels

An installed package can declare up to eight inert `setup_panels` in its
signed `manifest.json`. Each declaration has only an id, title, and search
keywords—there is no renderer, HTML, JavaScript, command, or callback field.
Setup shows those panels only when package integrity, compatibility, explicit
capability grants, and enabled state all pass. Disabled, revoked, altered, or
unavailable packages contribute nothing to Setup navigation.

The panel uses a generic JSON editor backed by the package's signed
`settings_schema`. Saving is protected by Setup unlock and CSRF checks and
calls the existing schema validator. This creates an immediately useful
configuration surface without loading package code in the web request. Secret
values remain a separate package-secret workflow and are never rendered back
into this editor.

## Cron editor

The cron editor is a reusable panel component: `renderSetupCronEditor()` in
`lib/setup-cron-editor.php` renders one named job's controls, while
`SetupCronApi` backed by `lib/schedule.php` previews, compares, and applies.
It offers profiles (`none`, every 1, 5, 10, or 30 minutes) and a custom
five-field standard-cron builder: minute, hour, day of month, month, and day
of week. Standard shared-host cron has no seconds field.

The saved choice previews the exact managed block before applying it. Applying
replaces only the Zogograph marker block and leaves unrelated account cron jobs
unchanged. The editor records desired structured settings, compares them with
the installed block, and reports in-sync, missing, drifted, or unavailable.

The core editor currently owns two approved jobs: `vitals` and `scan`. Its
persisted value is `schedule.php`, `schedule.vitals`, and `schedule.scan` in
the encrypted setup settings. A plugin cannot invent a cron command by passing
text through this API. Supporting package-defined jobs requires a separate
reviewed capability and command registry, not a broader cron API.

## Delivery order

1. Core cron editor, desired-state persistence, apply, and comparison. (Done.)
2. Searchable, scrollable Setup navigation driven by panel metadata. (Done.)
3. Add core-panel enablement and feature gating to the registry.
4. Surface signed inert package panels through generic schema settings. (Done.)
5. Add settings-save audit records. (Done.)
6. Add a package-local secret reference picker.
7. Bridge renderer callbacks only after package execution is independently
   attested, with a separately reviewed isolation contract.
