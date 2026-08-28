#!/usr/bin/env bash
set -euo pipefail

fail() { echo "Restore refused: $*" >&2; exit 1; }

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
test -r "$site" && test -w "$site" && test -x "$site" || fail "deploy path is not accessible: $site"
test -r "$snapshots" && test -x "$snapshots" || fail "snapshot path is not readable: $snapshots"
command -v rsync >/dev/null || fail 'rsync is unavailable on the deployment server'
command -v sha256sum >/dev/null || fail 'sha256sum is unavailable on the deployment server'

expected_snapshot="$snapshots/$snapshot_id"
test -d "$expected_snapshot" || fail "unknown snapshot: $snapshot_id"
snapshot=$(resolve_path "$expected_snapshot") || fail 'snapshot cannot be resolved'
[ "$snapshot" = "$expected_snapshot" ] || fail 'snapshot directory must not be a symlink'
for required in SNAPSHOT_ID MANIFEST.json APPLICATION_FILES.sha256 DATABASE_FILES.sha256; do
  test -f "$snapshot/$required" || fail "snapshot is missing $required"
done
test "$(cat "$snapshot/SNAPSHOT_ID")" = "$snapshot_id" || fail 'snapshot ID does not match its manifest'
test -d "$snapshot/site" || fail 'snapshot site files are missing'
test -d "$snapshot/databases" || fail 'snapshot database files are missing'

(cd "$snapshot/site" && sha256sum -c ../APPLICATION_FILES.sha256)
(cd "$snapshot/databases" && sha256sum -c ../DATABASE_FILES.sha256)

# Database files are restored only from SQLite's consistent .backup copies.
rsync -a --delete --exclude='data/***' --exclude='.well-known/' "$snapshot/site/" "$site/"
if [ -d "$snapshot/site/data" ]; then
  rsync -a --delete \
    --exclude='*.db' --exclude='*.db-*' \
    --exclude='*.sqlite' --exclude='*.sqlite-*' \
    --exclude='*.sqlite3' --exclude='*.sqlite3-*' \
    "$snapshot/site/data/" "$site/data/"
fi
find "$snapshot/databases" -type f \( -name '*.db' -o -name '*.sqlite' -o -name '*.sqlite3' \) -print |
while IFS= read -r source; do
  relative=${source#"$snapshot/databases/"}
  destination="$site/$relative"
  mkdir -p "$(dirname "$destination")"
  rm -f -- "${destination}-wal" "${destination}-shm" "${destination}-journal"
  rsync -a -- "$source" "$destination"
done
