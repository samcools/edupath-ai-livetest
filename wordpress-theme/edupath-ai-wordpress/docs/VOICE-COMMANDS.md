# Ayanda Voice Commands

Voice is browser-first for low latency. Where native `SpeechRecognition` is unavailable, authenticated users can fall back to MediaRecorder audio capture and an organisation-controlled server speech-transcription proxy. Text commands remain available at all times.

## Supported voice locale choices

English, Afrikaans, isiZulu, isiXhosa, Sesotho, Setswana, Sepedi, Xitsonga, Tshivenda, siSwati and isiNdebele. Actual speech-recognition and text-to-speech quality depends on the browser/device voice engine unless a server speech service is configured.

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

## Security rule

Speech and agent credentials are never sent to browser JavaScript. The WordPress browser calls WordPress REST; WordPress then calls the configured organisation-controlled proxy. Unauthenticated demo users are not forwarded to configured Microsoft agent or speech services.
