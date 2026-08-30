#!/usr/bin/env bash

# chmod +x format-diff.sh

set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: npm run format:diff -- path/to/file.php" >&2
  exit 1
fi

file="$1"

if [[ ! -f "$file" ]]; then
  echo "File not found: $file" >&2
  exit 1
fi

formatted_file="$(mktemp "${TMPDIR:-/tmp}/prettier-diff.XXXXXX")"
trap 'rm -f "$formatted_file"' EXIT

npx prettier "$file" > "$formatted_file"

diff_status=0

# No Color
#diff \
#  --unified \
#  --label "$file" \
#  --label "$file (formatted)" \
#  "$file" \
#  "$formatted_file" || diff_status=$?

# Color
git --no-pager diff \
  --no-index \
  --color=always \
  -- "$file" "$formatted_file" || diff_status=$?

# diff returns 1 when differences were found, which is expected.
if [[ "$diff_status" -gt 1 ]]; then
  exit "$diff_status"
fi