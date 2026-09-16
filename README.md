# EduPath AI Live Test

Current baseline: **EduPath AI v3.1.0**

This repository is the canonical source and build record for the EduPath AI WordPress platform created for the SITA hackathon workstream.

## Architecture

EduPath is deliberately split into two WordPress components:

- **Theme** — presentation, responsive shell, branding, navigation, light/dark mode and visual states.
- **Platform plugin** — authentication, OTP, learner/parent/teacher data, Ayanda AI and voice, multilingual support, CRM, reports, exams/proctoring, OCR, OpenAI/Microsoft integrations, notifications, security and audit.

The complete current source is preserved under `releases/v3.1.0/source-parts/` as a chunked base64-encoded `tar.gz` source snapshot. Use `scripts/restore-v3.1.0-source.sh` to reconstruct it into a normal working tree.

The project-wide implementation specification is in [`MASTER-BUILD-PROMPT.md`](MASTER-BUILD-PROMPT.md).

## Current Microsoft configuration baseline

- Foundry project endpoint: `https://sita-resource.services.ai.azure.com/api/projects/sita`
- Azure OpenAI endpoint: `https://sita-resource.openai.azure.com/openai/v1`
- Microsoft MCP: `https://mcp.ai.azure.com`
- Azure Speech resource: `https://southafricanorth.api.cognitive.microsoft.com`

No API keys or other secrets are committed to this repository.

## Authentication baseline

EduPath portal authentication requires:

1. Email address
2. Password
3. Six-digit email OTP

OTP sender: `EduPath AI <admin@pyrneo.com>`

Primary platform administrator: **Samson Sakoane** (`samson@pyrneo.com`).

## Restoring the v3.1.0 source

```bash
bash scripts/restore-v3.1.0-source.sh /tmp/edupath-v3.1.0
```

The restored tree contains:

- `wordpress-theme/edupath-ai-theme/`
- `wordpress-plugin/edupath-ai-platform/`
- project docs and QA files
- the master build prompt
- the v3.1 build workflow

## CI

`.github/workflows/build-edupath.yml` reconstructs the current source snapshot, validates PHP and JavaScript, packages installable theme/plugin ZIPs, checks archive integrity and uploads the packages as workflow artifacts.

## Security

Do not commit real OpenAI, Azure, Microsoft, SMTP, OTP or other private credentials. Credentials belong in the secured WordPress Platform Administrator settings or an appropriate secret store.