# Set up Zogograph

Zogograph is a dependency-free PHP site. You need a PHP-capable web host, a
scheduled-task facility, and access to the account whose home directory you
want to monitor. No Composer, Node.js, or external database server is needed.

## Before you begin

Make sure the host provides:

- PHP 8.1 or newer for both the web server and command line;
- PHP's PDO SQLite extension (`pdo_sqlite`);
- HTTPS for the site;
- a cron editor or shell access for the hosting account; and
- a web-server user that can write `data/` and, during initial setup, `lib/`.

Zogograph stores its measurements and settings in SQLite files under `data/`.
That directory must be private: it must never be served as a downloadable web
directory. The included `.htaccess` files enforce this on Apache when
`AllowOverride` is enabled. For Nginx, start with
[`deploy/nginx.conf.example`](deploy/nginx.conf.example).

## 1. Deploy the application

Create the document root for the Zogograph site, such as
`~/public_html/stats.example.com`, and upload the **complete release** there.
Keep the directory layout intact: in particular, `lib/`, `data/`, and their
included `.htaccess` files must remain in place.

Do not put an existing database or `lib/config.php` from another installation
into a new deployment unless you are deliberately migrating that installation.
Neither belongs in source control or a public download.

If you deploy through SSH, replace the example paths below with yours:

```bash
mkdir -p ~/public_html/stats.example.com
cd /path/to/zogograph-release
cp -R . ~/public_html/stats.example.com/
```

Set ownership and permissions so PHP can create files in `data/` and write the
initial bootstrap in `lib/`. Prefer making the web-server account the owner;
avoid world-writable permissions. After setup, `lib/config.php` is restricted
to its owner automatically.

For Apache, confirm that `.htaccess` is allowed for this document root and that
`mod_rewrite` is enabled so HTTP requests redirect to HTTPS. For Nginx, install
the supplied example's deny rules and configure a separate HTTP virtual host
that redirects to HTTPS.

## 2. Run the setup wizard immediately

Visit:

```
https://stats.example.com/setup.php
```

The page runs a preflight check. Resolve every error before continuing,
especially an unavailable SQLite extension or an unwritable `data/` directory.
Choose the scan root carefully: it defines the only tree the account collector
will examine and the boundary for file actions.

Complete the form, set an administrator password, and save. The wizard writes
the minimal `lib/config.php` bootstrap and stores the remaining settings in
`data/setup.db`.

If you configure a local encryption key, Zogograph also encrypts the complete
settings record in `setup.db` automatically. The bootstrap retains only the
key's absolute path—not the key material—so retain an owner-only offline copy
of that key with your private setup-database backup. Without a configured key,
`setup.db` relies on its owner-only file permissions and is not encrypted at
rest.

After first setup, after changing the local key, and before retiring an old
key, verify the active configuration without disclosing its contents:

```bash
php scripts/verify-setup-config.php --expect-encrypted
```

The command exits nonzero if encrypted configuration cannot be opened. Omit
`--expect-encrypted` only for an intentional no-key installation; it will then
report the permission-only state without treating it as a command failure.

Alternatively, from the deployed directory, run the same wizard in a terminal:

```bash
php setup.php --write
```

The wizard tells you which PHP binary it sees. Use the absolute path reported
by `which php` in cron if your host has more than one PHP version.

The initial setup page is intentionally open while no configuration exists, so
finish it as soon as the files are live. Keep `setup.php` deployed afterwards:
it is the password-protected backend for later administrator configuration
changes. Require HTTPS and protect the administrator password rather than
removing or denying this backend.

## 3. Take the first readings

Run these from the deployed directory as the hosting account:

```bash
php cron.php --vitals
php cron.php --scan
```

Then sign in at the site. The initial overview remains in setup mode until it
has a vital reading and a completed storage scan. If either command fails,
check `data/error.log` and open `health.php`; it is a standalone diagnostic
page that can identify incomplete or inconsistent uploads.

## 4. Schedule collection

Add these jobs in the hosting control panel's cron editor or with `crontab -e`.
Use absolute paths, and replace both placeholders with your own paths:

```cron
# Cheap system and account readings, plus alert evaluation.
* * * * * /absolute/path/to/php /absolute/path/to/zogograph/cron.php --vitals --alerts --quiet

# Full storage scan and retention cleanup.
7 * * * * /absolute/path/to/php /absolute/path/to/zogograph/cron.php --scan --alerts --prune --quiet
```

`--quiet` keeps successful cron runs silent and reports failures through cron's
normal error handling. The dashboard's **Schedule** panel can create account
cron entries on hosts that allow it; otherwise it shows the exact line to paste.

## Optional: host-wide collection

The normal collector monitors the account it runs as. To see host-wide process,
disk, and system information, enable **Also collect host-wide readings** in the
setup wizard and add this job to **root's** crontab, not the account crontab:

```cron
*/5 * * * * /absolute/path/to/php /absolute/path/to/zogograph/host-cron.php --collect --prune --quiet
```

Run the printed command once as root to verify it. Skip this step if you do not
administer the machine; Zogograph continues to work with account-level data.

The host collector records its own process tree separately from server totals:
CPU and memory gauges retain the total reading, with the monitor shown as a
darker arc and the non-monitor workload named beside it. Alerts still use the
total. Load average cannot be split exactly by process on Linux, so Zogograph
shows the collector tree's currently runnable tasks beside the total load
instead of presenting an invented "load excluding monitoring" number.

## Finish securely

- Confirm the dashboard is reachable only over HTTPS.
- Confirm a direct request to `data/`, `lib/`, `cron.php`, and `host-cron.php`
  is denied by the web server.
- Keep `setup.php` available for authenticated administrator configuration.
  You may remove `health.php` if you do not want a public diagnostic page.
- Back up `data/` privately. It contains the measurements, settings, and
  sensitive configuration state.

For operational behavior, alerts, upgrades, and recovery, see the detailed
reference in [README.md](README.md).

## Optional: encrypted database copies

Zogograph starts with encryption disabled. In the setup wizard, each
administrator chooses the protection mode, the configuration/database stores to
protect, the key custody method, and a copy schedule and retention preference.
The first built-in mode creates verified encrypted **copies** of selected
SQLite databases and the bootstrap configuration file; it does not alter the
live database or its WAL journal. This is suitable for encrypted local archives
and the later fleet-replica workflow.

### Choose a protection path

Start with the smallest path that meets the installation's recovery needs. The
Protection page only shows optional methods after their package has been
installed and its platform checks have passed.

| situation                                                      | recommended setup choice                                                                                       | important limit                                                                                                |
| -------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| shared host, single VPS, or a first installation               | local encrypted copies; protect configuration, `setup`, and `account`; run daily and retain 30 days            | protects copies and backup media, not the live SQLite files while Zogograph runs                               |
| host/VM/managed platform already encrypts its disks            | record host/VM protection for the selected live stores, then still make encrypted recovery copies              | Zogograph records this as an operator statement; it cannot prove the platform's disk-encryption setting        |
| live SQLite files must be encrypted by Zogograph               | install the SQLCipher package only on a Zogograph-tested PHP/PDO image and run its disposable preflight        | do not activate it on an ordinary PDO SQLite build or treat a sealed copy as live-store encryption             |
| organization needs remote recovery, KMS/HSM, or hosted custody | install and review the compatible custody/replica/provider package, then enroll it through the Protection page | remote custody and replicas remain off until the provider runtime and recovery/availability gates are complete |

The local encrypted-copy path is the initial recommendation because it works
without a commercial account or special database driver. Add `host` and `ai`
only after confirming that their evidence/history needs the same recovery
protection. Keep encryption off if the owner cannot retain and test a recovery
key; encryption without a usable recovery path can permanently lose data.

For the product-owner reasons behind these defaults and the settings that must
remain database-configurable, see [ENCRYPTION_DECISIONS.md](ENCRYPTION_DECISIONS.md).
For the decisions to record before enabling advanced packages, see
[ENCRYPTION_REVIEW.md](ENCRYPTION_REVIEW.md).

Create a 256-bit local key outside the document root, then keep an offline
recovery copy. The setup page has **Create a local encryption key** for this
quick-start path: it creates a new file only, never replaces one, requires the
current setup password for an existing installation, and never displays key
material in the browser. Do not store the key in `data/`, `lib/`, the web root,
or alongside a database export. Its suggested path uses the web-service
account's home directory, followed by `/.private/zogograph.key`. The quick-start
also creates that directory when needed and adds an Apache deny policy to it as
an extra precaution. Use the CLI form when the web-service account cannot write
the intended private directory.

```bash
php scripts/encryption-key.php create "$HOME/.private/zogograph.key"
```

In the setup wizard's **Encryption** section, select **Encrypted copies with a
local private key**, enter that absolute key-file path, and choose the stores
to include. The recommended initial selection is `config`, `setup`, and
`account`; add `host` and/or `ai` when their process evidence or assistant
history needs the same recovery protection. Missing optional host and assistant
databases are shown as **waiting** and skipped until they exist; they do not
make an otherwise verified account-only policy fail. The
artifact directory defaults to
the protected `data/sealed/` directory; set a private absolute path if you need
the copies elsewhere. Choose manual, daily, or weekly scheduling and a local
retention period; configure the matching job in your host scheduler. During an
explicit `--seal` run, Zogograph prunes only old verified artifacts whose hash
still matches and never removes the newest verified artifact for a store. Set
retention to zero to retain every local encrypted copy.

The advanced **Per-store retention overrides** field is an optional JSON object
for exceptions to that default, such as `{"setup":365,"account":30}`. Use it
when the setup database or bootstrap configuration needs a longer recovery
window than metrics. A value of `0` retains every verified copy of that one
store; omitted stores continue to use the normal retention target.

For longer-term recovery without keeping every daily artifact, use **Long-term
recovery copies** only when needed. For example,
`{"daily":7,"weekly":4,"monthly":12}` keeps the newest verified copy from
each of the last seven distinct UTC days, four distinct UTC weeks, and twelve
distinct UTC months for every store, even after its normal retention target.
It is off by default so the quick-install 30-day policy stays easy to predict.

If the host, VM, container volume, or managed database storage is already
encrypted at rest, select **My host/VM encrypts the selected live stores** and
choose the protected stores. This records the operator's policy but does not
attempt to verify host-level encryption or create Zogograph copies.

Run a first copy, then schedule future copies separately from normal
collection. An administrator with write access can use **System → Protection**
to create the first verified copies and run the non-destructive drill. Those
browser actions require confirmation, are CSRF-protected and audited, never
reveal the key, and remove temporary decrypted verification files. The CLI
commands remain suitable for scheduled or shell-only deployments:

```bash
php cron.php --seal
```

Immediately record the non-destructive recovery drill before treating the
policy as ready:

```bash
php cron.php --recovery-drill
```

The drill fully decrypts and validates the newest verified copy for every
selected, present store, then removes its temporary plaintext. It never
overwrites a live database. It is the low-risk first proof that the local key
and artifact format can be opened; use the CLI restore instructions below for
an actual replacement recovery exercise.

The default **Recovery-drill review period** is 90 days. Zogograph warns in the
Protection view and through normal encryption-recovery alerts when a store's
last drill becomes older than that period. Choose a shorter interval for more
critical systems, or set it to `0` only if you deliberately want the required
first drill without recurring reminders.

Each new sealed artifact is fully decrypted into a short-lived private temporary
file before Zogograph records it as verified. A sealing run also briefly keeps a
SQLite snapshot and its ciphertext, so plan for about twice the selected source
size plus a small margin at the encrypted-copy destination. The dashboard shows
its conservative estimate and warns when observed free space is lower. A failed
proof leaves the original live data untouched and does not create a verified
ledger record.

The dashboard's **System → Protection** view tracks this evidence. Daily
policies warn after two days without a new verified copy; weekly policies warn
after nine days. Manual policies never infer a missed run. A warning means the
most recent verified ciphertext may still be usable, but it no longer meets the
recovery point objective you selected.

When normal alert delivery is enabled, Zogograph also adds the configurable
**Encrypted-copy recovery needs attention** rule. It warns when a selected
store has no verified copy, has not completed its first non-destructive
recovery drill, has an overdue drill review, or has missed its selected
schedule, and reports an error when a retained copy fails verification or the
configured local key is no longer usable. It deliberately does not alert for
host/VM volume protection: Zogograph cannot verify a host encryption claim.

```cron
# Daily encrypted copies. This does nothing unless encryption.mode is selected.
19 3 * * * /absolute/path/to/php /absolute/path/to/zogograph/cron.php --seal --quiet

# Weekly full ciphertext/authentication re-check. It creates no new copy and
# decrypts each retained artifact only into a short-lived private temporary file.
43 4 * * 0 /absolute/path/to/php /absolute/path/to/zogograph/cron.php --verify-sealed --quiet

# Quarterly non-destructive recovery drill. Keep this at or below the selected
# Recovery-drill review period; the default 90 days makes this a good start.
17 5 1 1,4,7,10 * /absolute/path/to/php /absolute/path/to/zogograph/cron.php --recovery-drill --quiet
```

The key is never kept in Zogograph configuration or its databases. A lost key
cannot be recovered from a sealed copy. See
[ENCRYPTION_AND_FLEET_PLAN.md](ENCRYPTION_AND_FLEET_PLAN.md) for the current
scope, limitations, rotation design, and planned self-managed fleet custody.

Optional remote custody is off by default. The separate key-custody service
provides tenant key, envelope, enrolled-client credential, audit, and
subscription APIs; the separate replica relay accepts only signed,
hash-verified ciphertext. After a credential-free endpoint identity probe and
an enrolled-client readiness check, an administrator may explicitly add a
sealed remote DEK envelope to a verified local artifact, then run a
non-destructive remote recovery drill. Storage and recovery each require their
own reviewed, endpoint-restricted sandbox launcher. The browser explains the
precise data boundary before either action: no database path, artifact bytes,
plaintext, local recovery key, management token, or billing credential is sent.
The remote service receives only a sealed DEK envelope; its released recovery
key is used only in memory to authenticate a temporary file which is removed.
Local recovery remains required. Ciphertext replication remains a separate
future client capability. Management and billing credentials always stay
outside the client entirely.

To inspect a copy during recovery, decrypt it to a new private file and remove
that plaintext file when you finish:

```bash
php scripts/encryption-key.php decrypt "$HOME/.private/zogograph.key" account.sealed restored-account.db
```

To restore an account database from a sealed copy, stop the web server and all
Zogograph cron jobs first. The restore command requires that acknowledgement,
fully authenticates and checks the decrypted SQLite candidate, seals a snapshot
of the current account database, replaces it atomically, and removes stale WAL/SHM
files. The short-lived decrypted candidate is removed even when the restore is
refused. It must be run from the deployed Zogograph directory as the account
that owns the files:

```bash
php scripts/restore-account.php --sealed /secure/account.sealed \
  --key "$HOME/.private/zogograph.key" --writers-stopped
```

Do not restart Zogograph until that command exits successfully. This restores
only the account database; configuration, host, and assistant artifacts are
intentionally separate recovery operations because they have different owners
and availability contracts.

Use the generic restore command for the other sealed SQLite stores. It requires
the same stopped-writer acknowledgement, checks that the decrypted candidate
has the expected store table, seals a pre-restore snapshot beside the target,
atomically replaces it, and removes stale WAL/SHM companions:

```bash
php scripts/restore-store.php --store setup --sealed /secure/setup.sealed \
  --key "$HOME/.private/zogograph.key" --writers-stopped
php scripts/restore-store.php --store host --sealed /secure/host.sealed \
  --key "$HOME/.private/zogograph.key" --writers-stopped
php scripts/restore-store.php --store ai --sealed /secure/ai.sealed \
  --key "$HOME/.private/zogograph.key" --writers-stopped
```

When `--sealed` and `--key` are used, the printed pre-restore rollback path
ends in `.sealed`; its plaintext snapshot is removed after verification. The
legacy `--from` form has no key to use and therefore retains its local private
SQLite rollback snapshot as plaintext—move or protect it yourself before using
that compatibility mode for sensitive data.

Restoring `setup` changes the saved website/package policy, credentials, and
encryption settings. To restore the separately sealed bootstrap `lib/config.php`,
use the explicit CLI command below. It decrypts and parses PHP without executing
it, saves the prior bootstrap as a sealed artifact under `data/recovery/`, then
atomically replaces `lib/config.php`; `--confirm` is required because the next
Zogograph request will use the recovered settings. Keep the printed `.sealed`
prior copy with the recovery key until the recovered configuration is tested.

```bash
php scripts/restore-config.php --sealed /secure/config.sealed \
  --key "$HOME/.private/zogograph.key" --confirm
```

The `--store setup` recovery path uses only the minimal bootstrap first, so it
can replace a damaged encrypted `setup.db` without attempting to open that
database beforehand. The bootstrap's external key locator is not secret; the
key itself is still required to decrypt the sealed recovery artifact.

If host or assistant databases use custom paths stored in `setup.db`, restore
the sealed `setup` database first. That lets the recovery command resolve the
same configured target paths rather than guessing from a damaged settings
store.

To rotate the configured local key, create a replacement first, then use the
all-artifact rotation command. It re-encrypts every currently verified retained
artifact, verifies each replacement, marks the old ciphertext as **superseded**
instead of deleting it, and switches the saved encryption setting only after
those steps succeed:

```bash
php scripts/encryption-key.php create "$HOME/.private/zogograph-replacement.key"
php scripts/rotate-sealed-copies.php --new-key "$HOME/.private/zogograph-replacement.key" --confirm
php cron.php --verify-sealed
```

Complete a real restore drill using the replacement key before deleting any
superseded ciphertext or retiring the old key. The low-level
`scripts/encryption-key.php rotate` command remains useful for a single
standalone artifact; it does not update the encrypted-artifact ledger or the
configured key and is not the normal installation rotation workflow.

After that recovery drill, retire superseded ciphertext with the old key. The
command re-verifies current-key artifacts first, refuses retirement unless each
affected store has a current verified replacement, then authenticates each old
artifact before removal:

```bash
php scripts/retire-superseded-sealed.php --old-key "$HOME/.private/zogograph.key" --confirm
```

## Optional: signed executable packages

The normal marketplace remains data-only. Executable packages, including the
future encryption packages, are installed separately so a package cannot run
merely because a JSON manifest was copied into a catalog. A package must have a
trusted publisher key, a valid Ed25519 signature, and matching hashes for every
immutable file.

After adding the publisher public key through the protected package settings,
an administrator can inspect, install, and grant the package's declared
capabilities from the protected package page/API. The equivalent low-complexity
CLI workflow is:

```bash
php scripts/package-install.php install /absolute/path/to/signed-package
php scripts/package-install.php list
php scripts/package-install.php enable package-id capability[,capability...]
```

The capability list must exactly match the package's signed declaration. At
this stage installation and grants only register immutable files and a private
SQLite data directory; executable package code remains unavailable until the
isolated runtime is complete. This is intentional: it lets administrators
validate provenance and configuration without silently running new code.

Package settings may contain opaque `secret_ref:` values, never a raw provider
token or private key. Before creating one, configure **Package secrets key
file** in setup: leave it blank to reuse the local sealed-copy key, or use
**Create a separate package-secrets key** to create
`$HOME/.private/zogograph-package-secrets.key`. It uses the same private
directory and Apache deny policy as the sealed-copy key. Enter that path in
**Package secrets key file** and save it for separate custody. The authenticated
package API can create a reference once; it returns the reference but never the
secret value. Losing that external key makes the referenced provider credential
unrecoverable, so keep an offline recovery copy.
