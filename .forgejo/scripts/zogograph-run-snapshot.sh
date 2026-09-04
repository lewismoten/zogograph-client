#!/usr/bin/env bash
set -euo pipefail

mode=${1:-}
case "$mode" in
  site|databases) ;;
  *) echo 'Usage: zogograph-run-snapshot.sh site|databases' >&2; exit 2 ;;
esac

snapshot_id="${GITHUB_RUN_ID}-${GITHUB_RUN_ATTEMPT:-1}"
remote_script=".zogograph-snapshot-${GITHUB_RUN_ID}-${DEPLOY_TARGET}.sh"
ssh_args=(
  -i "$HOME/.ssh/zogograph_deploy_key"
  -p "${DEPLOY_PORT:-22}"
  -o BatchMode=yes
  -o IdentitiesOnly=yes
  -o ConnectTimeout=15
)
remote="${DEPLOY_USER}@${DEPLOY_HOST}"
finished=false

cleanup() {
  if "$finished"; then
    return
  fi
  timeout 1m ssh "${ssh_args[@]}" "$remote" \
    "bash '$remote_script' '$DEPLOY_PATH' '$SNAPSHOT_DIRECTORY' '$snapshot_id' discard; rm -f '$remote_script'" || true
}
trap cleanup EXIT

snapshot_phase() {
  local limit=$1 phase=$2 database=${3:-}
  timeout "$limit" ssh "${ssh_args[@]}" "$remote" \
    "bash '$remote_script' '$DEPLOY_PATH' '$SNAPSHOT_DIRECTORY' '$snapshot_id' '$phase'${database:+ '$database'}"
}

timeout 1m ssh "${ssh_args[@]}" "$remote" "mkdir -p '$SNAPSHOT_DIRECTORY'"
timeout 1m rsync -az \
  -e "ssh -i $HOME/.ssh/zogograph_deploy_key -p ${DEPLOY_PORT:-22} -o BatchMode=yes -o IdentitiesOnly=yes" \
  .forgejo/scripts/zogograph-snapshot.sh "$remote:$remote_script"

if [ "$mode" = site ]; then
  snapshot_phase 1m prepare
  snapshot_phase 1m baseline
  snapshot_phase 1m site
else
  snapshot_phase 1m database data/ai.db
  # The host collector owns a live, larger database. Its online backup may need
  # the full two-minute dedicated step, unlike the smaller application stores.
  snapshot_phase 2m database data/host.db
  snapshot_phase 1m database data/setup.db
  snapshot_phase 1m database data/stats.db
  snapshot_phase 1m databases
  snapshot_phase 1m finalize
  timeout 1m ssh "${ssh_args[@]}" "$remote" "rm -f '$remote_script'"
fi
finished=true
trap - EXIT
printf 'Pre-deploy snapshot: %s\n' "$snapshot_id"
