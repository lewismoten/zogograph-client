# Setup extensibility contract

`setup.php` is the administrator configuration surface, not a dashboard view.
Core and marketplace features may contribute panels only through the Setup
panel registry. A panel declares an id, title, navigation parent, capability
requirements, and a renderer. It never writes files or executes commands
directly from its renderer.

## Panel API

Core registers panel metadata in `lib/setup-panels.php`. `SetupCronApi` is the
first concrete restricted capability; it is used by the core Schedule panel
today. A future isolated package receives a `SetupPanelApi` object rather than
the full application:

- `addPanel()` registers metadata and a renderer for one declared panel id.
- `config()` reads only the panel's declared configuration namespace.
- `save()` validates and writes only that namespace after Setup has authorised
  the request.
- `cron()` returns the restricted `SetupCronApi`: `profiles()`, `preview()`,
  `status()`, and `apply()`. It accepts structured choices for named approved
  jobs, never arbitrary shell commands or an arbitrary crontab line.

Panels are hidden when their core/plugin feature is disabled. Plugins cannot
replace core panels, add executable shell text, access another plugin's
configuration, or bypass Setup's password/session/CSRF checks.

## Cron editor

The cron editor is a reusable panel component backed by `lib/schedule.php`.
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

1. Complete the core cron editor and its saved/install-state comparison. (In progress.)
2. Add searchable, scrollable Setup navigation driven by panel metadata. (In progress.)
3. Add core-panel enablement and plugin capability gating.
4. Expose the restricted registry API to the isolated package runtime only
   after package code execution is independently attested.
