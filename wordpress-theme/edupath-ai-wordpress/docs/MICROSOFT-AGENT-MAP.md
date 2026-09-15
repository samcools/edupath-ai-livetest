# EduPath AI → Microsoft Agent Mapping

The eight agents present in the supplied EduPath V11 ZIP remain the canonical demo agents. Their Microsoft implementation mapping is explicit so the WordPress UI does not pretend that a generic chatbot is equivalent to an enterprise agent runtime.

| EduPath agent | Microsoft implementation | Project Guardian control |
|---|---|---|
| Learning Guide | Microsoft Foundry **Prompt Agent** for declarative learner guidance, exposed through Copilot Studio when multichannel delivery is required | Recommendations only; use approved learning sources; consequential academic decisions remain human-owned |
| Assessment Coach | Foundry Prompt Agent for drafting, or **Hosted Agent** for custom assessment orchestration; Copilot Studio **agent flow** for educator approval | Draft/prepare autonomy; teacher/moderator approval before publication |
| Pathway Guide | Foundry **Hosted Agent** with curated tools/custom functions for programme, requirement and pathway logic | Explainable recommendations; never guarantee admission or employment outcomes |
| Support Navigator | Copilot Studio agent + human-in-the-loop agent flow | Creates reviewable support cases; cannot discipline, exclude, fail or label learners autonomously |
| Work Readiness Coach | Microsoft 365 Agents SDK as web/Teams conversation layer + Foundry Prompt Agent for coaching | Advice/recommendations; user remains in control of applications and commitments |
| Family & Educator Guide | Copilot Studio multichannel agent + Microsoft 365 Agents SDK | Role-filtered data; guardian/educator views exclude restricted notes and unauthorised personal information |
| Analytics Guide | Foundry Hosted Agent with governed analytics tools | Use authorised aggregates; preserve evidence links; avoid opaque ranking or automated sanctions |
| Notification Guide | Copilot Studio agent flow + Microsoft 365 / Power Platform connectors | Low-risk reminders may execute automatically; sensitive communications use approval and audit gates |

## Recommended Microsoft layers

1. **WordPress theme** — experience layer, role-aware UI, deterministic command routing, approval previews and REST boundary.
2. **Microsoft 365 Agents SDK** — optional channel/conversation plumbing for web/Teams and stateful multichannel messaging.
3. **Copilot Studio** — multichannel agent experience, connectors, actions and agent flows, including human-in-the-loop steps.
4. **Microsoft Foundry Agent Service** — prompt agents for managed declarative guidance and hosted agents for custom orchestration/tooling.
5. **Microsoft Entra ID + RBAC** — production identity and authorisation boundary.
6. **Application Insights / Foundry observability** — production tracing, evaluation and monitoring.

## Secure integration pattern

`WordPress browser → WordPress REST endpoint → organisation-controlled agent proxy → Microsoft agent endpoint/tools`

Secrets must stay server-side. The browser receives only task results permitted for the current user.

## Microsoft references verified September 2026

- Microsoft Foundry Agent Service: https://learn.microsoft.com/en-us/azure/foundry/agents/overview
- Microsoft 365 Agents SDK: https://learn.microsoft.com/en-us/microsoft-365/agents-sdk/agents-sdk-overview
- Microsoft Copilot Studio agent flows: https://learn.microsoft.com/en-us/microsoft-copilot-studio/flows-overview
