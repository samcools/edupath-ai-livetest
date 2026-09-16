#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PARTS="$ROOT/releases/v3.1.0/source-parts"
DEST="${1:-$ROOT/.restored-v3.1.0}"
EXPECTED_B64_SHA="07103689f80b3ca2e73c30e491a63f13eff4904abe6c65e5f49fb7f81306526e"
EXPECTED_TGZ_SHA="6b2610b7f8aae88f86da020f8aa46fbc6f8910a0a71661d9e2d94e1497c28253"

if [[ ! -d "$PARTS" ]]; then
  echo "Missing snapshot parts directory: $PARTS" >&2
  exit 1
fi

mapfile -t files < <(find "$PARTS" -maxdepth 1 -type f -name 'part-*.b64part' | sort)
if [[ ${#files[@]} -ne 19 ]]; then
  echo "Expected 19 source parts; found ${#files[@]}" >&2
  exit 1
fi

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
cat "${files[@]}" > "$TMP/edupath-v3.1.0-source-snapshot.tar.gz.b64"

echo "$EXPECTED_B64_SHA  $TMP/edupath-v3.1.0-source-snapshot.tar.gz.b64" | sha256sum -c -
base64 -d "$TMP/edupath-v3.1.0-source-snapshot.tar.gz.b64" > "$TMP/edupath-v3.1.0-source-snapshot.tar.gz"
echo "$EXPECTED_TGZ_SHA  $TMP/edupath-v3.1.0-source-snapshot.tar.gz" | sha256sum -c -

rm -rf "$DEST"
mkdir -p "$DEST"
tar -xzf "$TMP/edupath-v3.1.0-source-snapshot.tar.gz" -C "$DEST"

echo "EduPath AI v3.1.0 source restored to: $DEST"
