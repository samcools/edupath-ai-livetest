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
PLUGIN_OVERLAY="$SCRIPT_ROOT/overlays/v3.1.1/wordpress-plugin/edupath-ai-platform"
THEME_OVERLAY="$SCRIPT_ROOT/overlays/v3.1.1/wordpress-theme/edupath-ai-theme"

if [[ ! -f "$PLUGIN/edupath-ai-platform.php" ]]; then
  echo "EduPath plugin entry file not found: $PLUGIN/edupath-ai-platform.php" >&2
  exit 1
fi

mkdir -p "$PLUGIN/includes" "$THEME/inc"
cp "$PLUGIN_OVERLAY/includes/class-edupath-password-reset.php" "$PLUGIN/includes/class-edupath-password-reset.php"
cp "$THEME_OVERLAY/inc/password-reset-ui.php" "$THEME/inc/password-reset-ui.php"

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

if [[ ! -f "$THEME/functions.php" ]]; then
  echo "EduPath theme functions file not found: $THEME/functions.php" >&2
  exit 1
fi

python3 - "$THEME/functions.php" <<'PY'
from pathlib import Path
import sys
path = Path(sys.argv[1])
text = path.read_text(encoding="utf-8")
loader = "require_once __DIR__ . '/inc/password-reset-ui.php';"
if loader not in text:
    stripped = text.rstrip()
    if stripped.endswith("?>"):
        idx = text.rfind("?>")
        text = text[:idx] + "\n" + loader + "\n" + text[idx:]
    else:
        text = stripped + "\n\n" + loader + "\n"
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
php -l "$THEME/inc/password-reset-ui.php"
php -l "$THEME/functions.php"

grep -q "class-edupath-password-reset.php" "$PLUGIN/edupath-ai-platform.php"
grep -q "EduPath_AI_Password_Reset::boot" "$PLUGIN/includes/class-edupath-password-reset.php"
grep -q "password-reset-ui.php" "$THEME/functions.php"
grep -q "Forgot password?" "$THEME/inc/password-reset-ui.php"

echo "Applied EduPath AI v3.1.1 password-reset overlay."
