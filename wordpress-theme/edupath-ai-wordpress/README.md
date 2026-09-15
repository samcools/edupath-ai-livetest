# EduPath AI Guardian — WordPress Theme

A hackathon-ready WordPress theme that converts the supplied EduPath AI V11 prototype into a role-aware, agentic education pathway platform while preserving the strongest Project Guardian operating patterns.

## What is included

- Role selector for Learner, Parent/Guardian, Teacher, Counsellor, School Administrator, Principal, Education Department, Academic Administrator, University/TVET, Employer and Platform Administrator.
- Learner journey, pathways, applications, funding, work readiness, exam practice, consent-based proctoring demo, interventions, analytics and administration workspaces.
- Agent Centre mapping the eight agents in the supplied ZIP to Microsoft agent technologies.
- Ayanda text + voice assistant.
- Deterministic low-latency voice navigation before agent fallback.
- Voice/text write instructions that open a confirmation preview instead of silently changing consequential data.
- WordPress REST routes for status, agent proxy, authenticated actions and authenticated server transcription fallback.
- Local demo audit trail plus server-side audit capture for authenticated WordPress actions.
- Responsive desktop/tablet/mobile interface.

## Microsoft agent architecture

The theme does **not** place Microsoft or Azure secrets in browser JavaScript. Use **Appearance → EduPath AI Agents** to configure organisation-controlled HTTPS proxies to Microsoft Foundry Agent Service, Copilot Studio / Microsoft 365 Agents SDK orchestration, and an optional speech-transcription service.

See `docs/MICROSOFT-AGENT-MAP.md` and `docs/VOICE-COMMANDS.md`.

## Install

1. Use the installable `edupath-ai-wordpress-theme.zip` package, or zip the `edupath-ai-wordpress` directory.
2. WordPress Admin → Appearance → Themes → Add New → Upload Theme.
3. Activate **EduPath AI Guardian**.
4. Set a static front page if your WordPress configuration does not automatically use `front-page.php`.
5. Optional: configure Microsoft agent and speech proxies in Appearance → EduPath AI Agents.

## Automated validation and packaging

`.github/workflows/build-wordpress-theme.yml` validates PHP and JavaScript syntax, builds the installable theme ZIP, verifies the archive, and uploads it as a GitHub Actions artifact. `scripts/build-theme.sh` provides the same packaging step locally.

## Security boundary

This is a hackathon implementation, not a claim of production deployment. Synthetic data is used. Production use requires Microsoft Entra identity, persistent RBAC/ABAC, POPIA-aligned consent/retention controls, secured integration credentials, database-backed domain records, formal threat modelling, monitoring and testing.

The repository build uses an original abstract Ayanda SVG avatar and references the authentic Pyrneo wordmark URL rather than fabricating a substitute logo.
