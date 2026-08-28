# Setup extensibility contract

`setup.php` is the administrator configuration surface, not a dashboard view.
Core and marketplace features may contribute panels only through the Setup
panel registry. A panel declares an id, title, navigation parent, capability
requirements, and a renderer. It never writes files or executes commands
directly from its renderer.

## Panel API

Core registers panels in `lib/setup-panels.php`. A future isolated package
receives a restricted `SetupPanelApi` object rather than the full application:

- `addPanel()` registers metadata and a renderer for one declared panel id.
- `config()` reads only the panel's declared configuration namespace.
- `save()` validates and writes only that namespace after Setup has authorised
  the request.
- `cron()` exposes `lib/schedule.php`'s preset, preview, read, compare, and
  apply operations. It accepts structured cron fields, never arbitrary shell
  commands.

Panels are hidden when their core/plugin feature is disabled. Plugins cannot
replace core panels, add executable shell text, access another plugin's
configuration, or bypass Setup's password/session/CSRF checks.

## Cron editor

The cron editor is a reusable panel component backed by `lib/schedule.php`.
It offers profiles (`none`, every 1, 5, 10, or 30 minutes) and a custom
five-field standard-cron builder: minute, hour, day of month, month, and day
of week. Standard shared-host cron has no seconds field.

Every edit previews the exact managed block before applying it. Applying
replaces only the Zogograph marker block and leaves unrelated account cron jobs
unchanged. The editor records desired structured settings, compares them with
the installed block, and reports in-sync, missing, drifted, or unavailable.

## Delivery order

1. Add the core cron editor as the first registered Setup panel.
2. Persist desired schedule choices and compare them to the installed block.
3. Add searchable, scrollable Setup navigation driven by panel metadata.
4. Add core-panel enablement and plugin capability gating.
5. Expose the restricted registry API to the isolated package runtime only
   after package code execution is independently attested.
