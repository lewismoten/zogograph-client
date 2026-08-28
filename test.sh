#!/usr/bin/env sh
set -eu

test -s index.html
test -s api.php
test -s health.php
for test_file in tests/*.php; do
  php "$test_file"
done
