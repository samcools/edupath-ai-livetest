# Ayanda Voice Commands

Voice recognition is browser-first for low latency. If speech recognition is not available, every command can be typed into the same Ayanda interface.

## Navigation examples

- “Open Pathways.”
- “Show my applications.”
- “Find bursaries.”
- “Start exam practice.”
- “Open Agent Centre.”
- “Show learners needing intervention.”
- “Open audit logs.”
- “Show Microsoft Agents.”

## Role commands

- “Switch to Teacher.”
- “Change to Parent / Guardian.”
- “Act as Platform Administrator.”

## Action commands

Commands such as “create an intervention”, “update a learner record”, “add a milestone”, “archive a work item” or “add a comment” generate an action preview. Medium/high-risk changes require explicit confirmation before execution. Authenticated server actions are written to the EduPath audit log.

## Design rule

Deterministic commands are resolved locally first. This avoids unnecessary LLM latency for obvious navigation and role-switching instructions. Questions requiring explanation or reasoning fall back to the configured server-side Microsoft agent proxy, or the governed demo response when no external agent is configured.
