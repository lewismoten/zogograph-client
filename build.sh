#!/usr/bin/env sh
set -eu

rm -rf dist
mkdir -p dist
tar --exclude='./.git' --exclude='./data' --exclude='./dist' --exclude='./tests' --exclude='./deploy' --exclude='./.forgejo' --exclude='./lib/config.php' --exclude='./*.md' -cf - . | tar -xf - -C dist
test -s dist/index.html
test -s dist/api.php
test -s dist/health.php
