#!/usr/bin/env bash
set -euo pipefail
umask 077

fail() { echo "Snapshot refused: $*" >&2; exit 1; }

valid_path() {
  local label=$1 value=$2
  [ -n "$value" ] || fail "$label is empty"
  case "$value" in /*) ;; *) fail "$label must be absolute";; esac
  case "$value" in *[!A-Za-z0-9._/-]*) fail "$label has an unsafe character";; esac
}

valid_id() {
  local value=$1
  [[ "$value" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,159}$ ]] || fail 'snapshot ID is unsafe'
  [ "$value" != '.' ] && [ "$value" != '..' ] || fail 'snapshot ID is unsafe'
}

resolve_path() {
  realpath -e -- "$1" 2>/dev/null || realpath "$1"
}

sqlite_argument() {
  local value=$1
  value=${value//\\/\\\\}
  value=${value//\"/\\\"}
  printf '"%s"' "$value"
}

[ "$#" -eq 3 ] || fail 'expected deploy path, snapshot path, and snapshot ID'
valid_path 'deploy path' "$1"
valid_path 'snapshot path' "$2"
valid_id "$3"
test -d "$1" || fail "deploy path does not exist: $1"
test -d "$2" || fail "snapshot path does not exist: $2"
site=$(resolve_path "$1") || fail 'deploy path cannot be resolved'
snapshots=$(resolve_path "$2") || fail 'snapshot path cannot be resolved'
snapshot_id=$3
case "$snapshots" in "$site"|"$site"/*) fail 'snapshot path must be outside the deploy path';; esac
test -r "$site" && test -x "$site" || fail "deploy path is not readable: $site"
test -w "$snapshots" && test -x "$snapshots" || fail "snapshot path is not writable: $snapshots"

if ! source_entry=$(find "$site" -mindepth 1 -maxdepth 1 -print -quit); then
  fail "cannot list deploy path: $site"
fi
test -n "$source_entry" || fail "deploy path is empty: $site"
command -v rsync >/dev/null || fail 'rsync is unavailable on the deployment server'
command -v sha256sum >/dev/null || fail 'sha256sum is unavailable on the deployment server'
database_entry=''
if [ -d "$site/data" ]; then
  if ! database_entry=$(find "$site/data" -type f \( -name '*.db' -o -name '*.sqlite' -o -name '*.sqlite3' \) ! -path "$site/data/migration-backups/*" -print -quit 2>/dev/null); then
    fail 'cannot inspect data directory'
  fi
  [ -z "$database_entry" ] || command -v sqlite3 >/dev/null || fail 'sqlite3 is unavailable on the deployment server'
fi

snapshot="$snapshots/$snapshot_id"
test ! -e "$snapshot" || fail "snapshot already exists: $snapshot"
stage="$snapshots/.zogograph-snapshot-${snapshot_id}-$$"
test ! -e "$stage" || fail "snapshot staging path already exists: $stage"
cleanup() { rm -rf -- "$stage"; }
trap cleanup EXIT
mkdir -p "$stage/site" "$stage/databases"

previous_snapshot=''
previous_mtime=0
while IFS= read -r -d '' candidate; do
  name=${candidate##*/}
  valid_id "$name" || continue
  test ! -L "$candidate" || continue
  resolved=$(resolve_path "$candidate") || continue
  [ "$resolved" = "$candidate" ] || continue
  test -d "$candidate/site" && test -f "$candidate/SNAPSHOT_ID" || continue
  test "$(cat "$candidate/SNAPSHOT_ID")" = "$name" || continue
  candidate_mtime=$(stat -c '%Y' -- "$candidate" 2>/dev/null || stat -f '%m' "$candidate") || continue
  case "$candidate_mtime" in *[!0-9]*|'') continue;; esac
  if [ "$candidate_mtime" -gt "$previous_mtime" ]; then
    previous_snapshot=$candidate
    previous_mtime=$candidate_mtime
  fi
done < <(find "$snapshots" -mindepth 1 -maxdepth 1 -type d ! -name '.*' -print0)

rsync_args=(-a --delete
  --exclude='node_modules/'
  --exclude='*.db' --exclude='*.db-*'
  --exclude='*.sqlite' --exclude='*.sqlite-*'
  --exclude='*.sqlite3' --exclude='*.sqlite3-*')
if [ -n "$previous_snapshot" ]; then
  rsync_args+=("--link-dest=$previous_snapshot/site")
  echo "Snapshot baseline: ${previous_snapshot##*/}"
else
  echo 'Snapshot baseline: none (first full snapshot)'
fi
echo 'Snapshot phase: copying application files'
rsync "${rsync_args[@]}" "$site/" "$stage/site/"
if [ -d "$site/data" ]; then
  echo 'Snapshot phase: backing up SQLite databases'
  find "$site/data" \
    -path "$site/data/migration-backups" -prune -o \
    -type f \( -name '*.db' -o -name '*.sqlite' -o -name '*.sqlite3' \) -print |
  while IFS= read -r database; do
    relative=${database#"$site"/}
    destination="$stage/databases/$relative"
    mkdir -p "$(dirname "$destination")"
    sqlite3 "$database" ".backup $(sqlite_argument "$destination")"
  done
fi

echo 'Snapshot phase: verifying and finalizing files'
if ! captured=$(find "$stage/site" "$stage/databases" -type f -print -quit); then
  fail 'cannot verify staged snapshot'
fi
test -n "$captured" || fail 'staged snapshot has no files'
(cd "$stage/site" && find . -type f -print0 | sort -z | xargs -0 -r sha256sum --) > "$stage/APPLICATION_FILES.sha256"
(cd "$stage/databases" && find . -type f -print0 | sort -z | xargs -0 -r sha256sum --) > "$stage/DATABASE_FILES.sha256"
app_files=$(find "$stage/site" -type f | wc -l | tr -d ' ')
database_files=$(find "$stage/databases" -type f | wc -l | tr -d ' ')
created_at=$(date -u +'%Y-%m-%dT%H:%M:%SZ')
cat > "$stage/MANIFEST.json" <<EOF
{"snapshotId":"$snapshot_id","source":"$site","createdAt":"$created_at","applicationFiles":$app_files,"databaseFiles":$database_files}
EOF
printf '%s\n' "$snapshot_id" > "$stage/SNAPSHOT_ID"

mv -- "$stage" "$snapshot"
trap - EXIT
echo "Snapshot ready: $snapshot ($app_files application files, $database_files database files)"
