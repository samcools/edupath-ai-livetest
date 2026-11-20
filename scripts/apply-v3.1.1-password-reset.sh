#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <restored-edupath-root>" >&2
  exit 1
fi

ROOT="$1"
PLUGIN="$ROOT/wordpress-plugin/edupath-ai-platform"
THEME="$ROOT/wordpress-theme/edupath-ai-theme"
SCRIPT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OVERLAY="$SCRIPT_ROOT/overlays/v3.1.1/wordpress-plugin/edupath-ai-platform"

if [[ ! -f "$PLUGIN/edupath-ai-platform.php" ]]; then
  echo "EduPath plugin entry file not found: $PLUGIN/edupath-ai-platform.php" >&2
  exit 1
fi

mkdir -p "$PLUGIN/includes"
cp "$OVERLAY/includes/class-edupath-password-reset.php" "$PLUGIN/includes/class-edupath-password-reset.php"

python3 - "$PLUGIN/edupath-ai-platform.php" <<'PY'
from pathlib import Path
import re
import sys

path = Path(sys.argv[1])
text = path.read_text(encoding="utf-8")
loader = "require_once __DIR__ . '/includes/class-edupath-password-reset.php';"

if loader not in text:
    stripped = text.rstrip()
    if stripped.endswith("?>"):
        idx = text.rfind("?>")
        text = text[:idx] + "\n" + loader + "\n" + text[idx:]
    else:
        text = stripped + "\n\n" + loader + "\n"

text = re.sub(r"(?mi)^(\s*\*?\s*Version:\s*)3\.1\.0\s*$", r"\g<1>3.1.1", text, count=1)
text = text.replace("EDUPATH_AI_PLATFORM_VERSION', '3.1.0", "EDUPATH_AI_PLATFORM_VERSION', '3.1.1")
text = text.replace('EDUPATH_AI_PLATFORM_VERSION", "3.1.0', 'EDUPATH_AI_PLATFORM_VERSION", "3.1.1')
path.write_text(text, encoding="utf-8")
PY

if [[ -f "$THEME/style.css" ]]; then
  python3 - "$THEME/style.css" <<'PY'
from pathlib import Path
import re
import sys
path = Path(sys.argv[1])
text = path.read_text(encoding="utf-8")
text = re.sub(r"(?mi)^(\s*Version:\s*)3\.1\.0\s*$", r"\g<1>3.1.1", text, count=1)
path.write_text(text, encoding="utf-8")
PY
fi

php -l "$PLUGIN/includes/class-edupath-password-reset.php"
php -l "$PLUGIN/edupath-ai-platform.php"

grep -q "class-edupath-password-reset.php" "$PLUGIN/edupath-ai-platform.php"
grep -q "EduPath_AI_Password_Reset::boot" "$PLUGIN/includes/class-edupath-password-reset.php"

echo "Applied EduPath AI v3.1.1 password-reset overlay."
