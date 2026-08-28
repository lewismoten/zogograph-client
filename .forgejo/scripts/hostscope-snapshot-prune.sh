#!/usr/bin/env bash
set -euo pipefail
umask 077

fail() { echo "Snapshot retention refused: $*" >&2; exit 1; }

valid_path() {
  local value=$1
  [ -n "$value" ] || fail 'snapshot path is empty'
  case "$value" in /*) ;; *) fail 'snapshot path must be absolute';; esac
  case "$value" in *[!A-Za-z0-9._/-]*) fail 'snapshot path has an unsafe character';; esac
}

valid_id() {
  local value=$1
  [[ "$value" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,159}$ ]] || return 1
  [ "$value" != '.' ] && [ "$value" != '..' ]
}

resolve_path() {
  realpath -e -- "$1" 2>/dev/null || realpath "$1"
}

snapshot_mtime() {
  stat -c '%Y' -- "$1" 2>/dev/null || stat -f '%m' "$1"
}

[ "$#" -eq 2 ] || fail 'expected snapshot path and retention count'
valid_path "$1"
case "$2" in *[!0-9]*|'') fail 'retention count must be a whole number';; esac
[ "$2" -ge 1 ] && [ "$2" -le 50 ] || fail 'retention count must be between 1 and 50'
test -d "$1" || fail "snapshot path does not exist: $1"
snapshots=$(resolve_path "$1") || fail 'snapshot path cannot be resolved'
test -w "$snapshots" && test -x "$snapshots" || fail 'snapshot path is not writable'

complete=()
while IFS= read -r -d '' candidate; do
  name=${candidate##*/}
  valid_id "$name" || continue
  test ! -L "$candidate" || continue
  resolved=$(resolve_path "$candidate") || continue
  [ "$resolved" = "$candidate" ] || continue
  for required in SNAPSHOT_ID MANIFEST.json APPLICATION_FILES.sha256 DATABASE_FILES.sha256; do
    test -f "$candidate/$required" || continue 2
  done
  test "$(cat "$candidate/SNAPSHOT_ID")" = "$name" || continue
  test -d "$candidate/site" && test -d "$candidate/databases" || continue
  complete+=( "$(snapshot_mtime "$candidate")"$'\t'"$candidate" )
done < <(find "$snapshots" -mindepth 1 -maxdepth 1 -type d ! -name '.*' -print0)

newest=()
if [ "${#complete[@]}" -gt 0 ]; then
  mapfile -t newest < <(printf '%s\n' "${complete[@]}" | sort -t $'\t' -k1,1nr -k2,2r)
fi
removed=0
for ((index = $2; index < ${#newest[@]}; index++)); do
  candidate=${newest[index]#*$'\t'}
  name=${candidate##*/}
  valid_id "$name" || fail 'candidate snapshot ID changed during retention'
  resolved=$(resolve_path "$candidate") || fail 'candidate snapshot cannot be resolved during retention'
  [ "$resolved" = "$candidate" ] || fail 'candidate snapshot changed during retention'
  rm -rf --one-file-system -- "$candidate"
  removed=$((removed + 1))
  echo "Removed expired snapshot: $name"
done
echo "Snapshot retention kept ${#newest[@]} complete snapshots before pruning; removed $removed; policy keeps $2."
