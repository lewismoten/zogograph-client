# Continuous deployment

The Forgejo workflows in `.forgejo/workflows/` deploy this application with
`rsync`, without replacing the live configuration or SQLite stores. They are
safe for the existing installation after the following Forgejo settings exist.

## Forgejo settings

Repository **variables**:

| Variable                   | Value                                                                                                                              |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `DEPLOY_HOST`              | SSH hostname or IP address                                                                                                         |
| `DEPLOY_PORT`              | SSH port; leave empty for `22`                                                                                                     |
| `DEPLOY_USER`              | restricted deployment user                                                                                                         |
| `DEPLOY_PATH`              | absolute web-root path for this Zogograph client install                                                                           |
| `DEPLOY_URL`               | public HTTPS URL for the install, without a trailing slash                                                                         |
| `TEST_PATH`                | absolute web-root path for the disposable develop/test install                                                                     |
| `TEST_URL`                 | public HTTPS URL for the disposable develop/test install, without a trailing slash                                                 |
| `DEPLOY_KNOWN_HOSTS`       | exact `known_hosts` line for the server's SSH host key                                                                             |
| `SNAPSHOT_PATH`            | absolute private snapshot root, outside the web root and deploy path; the workflow creates a repository-name subdirectory below it |
| `SNAPSHOT_RETENTION`       | optional whole number from `1` to `50`; defaults to `3` complete snapshots                                                         |
| `BROWSER_REFRESH_REQUIRED` | legacy compatibility variable: optional `true` when this release requires open browsers to reload; otherwise omit or use `false`   |

Repository **secret**:

| Secret                   | Value                                                                                        |
| ------------------------ | -------------------------------------------------------------------------------------------- |
| `DEPLOY_SSH_PRIVATE_KEY` | private key for the deployment user; its public key belongs in that user’s `authorized_keys` |

The deployment user needs read/write access to both deployment and snapshot
paths, and the server needs `bash`, `rsync`, and `sqlite3`. Do not make either
the deployment path or snapshot path world-readable.

`DEPLOY_PATH`, `TEST_PATH`, and `SNAPSHOT_PATH` must be existing absolute paths using only
letters, digits, `.`, `_`, `-`, and `/`. This intentionally rejects spaces,
quotes, line feeds, and relative paths. In particular, re-enter
`SNAPSHOT_PATH` manually if it was pasted from a source that might have added a
trailing line feed. The paths are resolved before use, and snapshots may not be
inside the deployment path, including through a symlink.

Get the known-host entry from a trusted administrative connection, for example:

```sh
ssh-keyscan -H your-server.example
```

Verify the reported fingerprint by a separate trusted channel before saving it
as a variable. Do not disable host-key checking in the workflow.

## Deploying

`deploy.yml` runs on pushes to the configured development branches and can be
run manually from any selected ref. It always deploys to `TEST_PATH` and
verifies `TEST_URL`; it never uses the production `DEPLOY_PATH` or `DEPLOY_URL`.
The workflow assumes this directory is the repository root, so it has no
monorepo path dependency.

Each run:

1. creates a release staging directory, excluding `data/`, `lib/config.php`,
   tests, deployment examples, and documentation;
2. writes a public `build.json` containing the commit, ref, run ID/number,
   creation time, Git-derived version, `packageVersion` from `package.json`,
   repository, application, and part;
3. takes a remote snapshot before altering the site;
4. uses `rsync --delete` for application files while explicitly preserving
   `data/`, `lib/config.php`, and `.well-known/` on the server; and
5. fetches `build.json` over HTTPS to confirm the deployed commit.

Snapshot directories use `<snapshot-root>/<repository>/<run-id>-<attempt>`,
such as `/private/snapshots/client/1941-1`; the commit remains in the public
`build.json` and each snapshot's manifest. After a deployment has verified over HTTPS, the workflow
removes only older **complete** snapshots beyond
`SNAPSHOT_RETENTION` (three by default). Incomplete directories, symlinks, and
unrecognized directories are left alone for manual review. Deployment and
restore use one lifecycle lock, so retention cannot race a restore.

Open dashboards check `build.json` once a minute. A new ordinary build shows a
non-disruptive **Reload now** message. Set
`BROWSER_REFRESH_REQUIRED=true` (a preserved legacy variable) for a deployment that changes
the browser/server contract; the same prompt then clearly says the reload is
required before continuing. The browser does not silently discard an in-progress
form or other operator work.

SQLite files below `data/` are captured with SQLite’s online `.backup` command,
not copied together with their WAL files. Snapshot IDs are printed in the job
output. Historical files below `data/migration-backups/` are deliberately not
opened as live databases. The workflow only finds live databases below `data/`;
if a configuration points at a database outside the deploy path, back that
store up separately.

Snapshots are first made in a private sibling staging directory and renamed
only after every file copy, database backup, and manifest write succeeds. A
failed run therefore leaves no usable snapshot and can retry the same ID. Each
completed snapshot contains `MANIFEST.json`, `SNAPSHOT_ID`, and separate
SHA-256 manifests for application and database files. Restore verifies those
hashes before it changes the live site; a changed or incomplete snapshot is
refused.

## Restoring a snapshot

Before restoring, pause the account and host collector cron jobs so they do not
write while files are replaced. In Forgejo, run **Restore Zogograph Client
Snapshot**, enter the snapshot ID, and type exactly:

```text
restore-zogograph-snapshot
```

The restore job snapshots the current site first, restores application and
non-database data files, then restores each SQLite database from its consistent
backup and removes its stale WAL/SHM/journal companions. The pre-restore
snapshot ID is printed in the job output, so a mistaken restore is itself
reversible. Re-enable collectors only after the job succeeds and the site
loads normally.
