#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/wordpress-theme/edupath-ai-wordpress"
DIST="$ROOT/dist"
mkdir -p "$DIST"
rm -f "$DIST/edupath-ai-wordpress-theme.zip"
cd "$(dirname "$THEME")"
zip -qr "$DIST/edupath-ai-wordpress-theme.zip" "$(basename "$THEME")"
echo "Built: $DIST/edupath-ai-wordpress-theme.zip"
