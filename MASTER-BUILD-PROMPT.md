# EduPath AI — Master Build Prompt

## Project identity

Build and maintain **EduPath AI**, a secure, multilingual, AI-enabled education platform for South African learners, parents/guardians, teachers, counsellors, school leaders, education administrators, universities/TVET institutions, employers and platform administrators.

Repository: `samcools/edupath-ai-livetest`

Current baseline: **v3.1.0**

Platform administrator: **Samson Sakoane** (`samson@pyrneo.com`)

Primary objective: **One learner. One evolving education journey. Multiple possible pathways. Continuous AI-supported guidance.**

WordPress is the application host, not the user-facing product experience. EduPath must behave as a standalone education platform.

---

## 1. Architecture

Use a strict two-part WordPress architecture.

### Theme responsibilities

The **EduPath AI Theme** owns presentation only:
- responsive layout;
- light/dark mode;
- branding and visual hierarchy;
- navigation shell;
- login/landing layout;
- dashboard/card/table/report styling;
- mobile/tablet/desktop behaviour;
- no AI, authentication, CRM, report, exam or business logic.

Theme slug: `edupath-ai-theme`

### Plugin responsibilities

The **EduPath AI Platform plugin** owns:
- authentication and OTP;
- roles/permissions;
- learner, parent, teacher and administrator data;
- Ayanda AI, voice and multilingual behaviour;
- OpenAI integration;
- Microsoft Foundry/Azure OpenAI integration;
- Microsoft MCP status/integration;
- Azure Speech;
- local OCR/document extraction;
- exam practice and live proctor simulation;
- CRM;
- notifications;
- PDF reports;
- audit logs;
- REST APIs;
- page creation/self-repair;
- security/privacy/governance.

Plugin slug: `edupath-ai-platform`

Never move plugin functionality back into the theme.

---

## 2. Branding and UI

Use the approved EduPath palette:
- background `#f6f9ff`
- card `#ffffff`
- ink `#26344b`
- muted `#6c7d95`
- border `#dbe6f4`
- purple `#5950d7`
- blue `#53b9ec`
- green `#36cfae`
- pink `#ff85b4`
- yellow `#ffd25e`

Dark mode must use charcoal/navy surfaces while retaining accessible EduPath accent colours.

Use a purple-to-blue sidebar, gradient hero areas, rounded metric cards, clear tables, progress bars, and a floating Ayanda assistant.

### Landing page
- login form on the left;
- modern school-campus image on the right;
- do not use the cropped learner photo on the landing page;
- keep the learner portrait only as Ayanda's avatar;
- collapse cleanly on mobile/tablet.

---

## 3. Standalone portal behaviour

- Hide the WordPress front-end admin toolbar on EduPath portal pages, including for administrators.
- Do not expose WordPress Dashboard links inside EduPath.
- `/wp-admin/` remains available when an administrator deliberately opens it.
- Do not redirect normal users into WordPress Admin.
- Keep all primary navigation inside the EduPath shell.

---

## 4. Authentication

Require **email + password + six-digit email OTP** for every new EduPath portal session.

OTP controls:
- six digits;
- 10-minute expiry;
- one-time use;
- max five verification attempts;
- rate-limit email/IP;
- HTTPS required;
- Secure + HttpOnly + SameSite=Strict portal cookie;
- WordPress authentication alone must not bypass EduPath OTP verification.

### OTP email
Sender must be:
`EduPath AI <admin@pyrneo.com>`

The OTP must appear in:
1. the subject line;
2. a clear plain-text sentence in the body;
3. the visible code section.

Prefer plain-text OTP mail so SMTP/template plugins cannot strip an HTML code block.

Do not send a second generic “OTP issued” email after the OTP email. Log successful issuance internally. Failed attempts, lockouts and serious security events may generate alerts.

Show WhatsApp OTP as a future disabled option until a provider is configured.

### Primary administrator
Ensure:
- Name: Samson Sakoane
- Username: `samson.sakoane`
- Email: `samson@pyrneo.com`
- WordPress role: Administrator
- EduPath role: Platform Administrator
- EduPath access: Full

Never hard-code a password.

---

## 5. Roles and realistic identities

Support:
- Learner
- Parent / Guardian
- Teacher
- Counsellor
- School Administrator
- Principal
- Education Department
- Academic Administrator
- University / TVET
- Employer
- Platform Administrator

Every workspace must show the actual signed-in user's name, username, role and relevant profile context.

### Parent rules
A parent has 1–3 linked children only. They share the parent surname but have different ages/grades and grade-appropriate subjects.

Reference family:
- Nomsa Molefe (`nomsa.molefe`)
- Naledi Molefe — age 16 — Grade 10
- Kabelo Molefe — age 13 — Grade 7
- Lesedi Molefe — age 9 — Grade 4

### Teacher rules
A teacher has multiple assigned learners with different surnames.

Reference teacher:
- Lerato Khumalo (`lerato.khumalo`)

Example learners:
- Naledi Molefe
- Thando Mokoena
- Anele Dlamini
- Neo Mokgosi
- Zinhle Khumalo
- Sipho Maseko

---

## 6. Learner progress

Progress must show learner-specific data, never only generic percentages.

For every subject show:
- Term 1;
- Term 2;
- Term 3;
- current mark;
- progress bar;
- trend;
- attendance/support context where relevant.

Grade 10 example subjects:
- English HL
- isiZulu FAL
- Mathematics
- Physical Sciences
- Information Technology
- Life Orientation
- Business Studies

Ayanda must explain progress using authorised learner data.

---

## 7. Required workspaces

All links must work, show relevant role-aware data, and avoid 404s. The plugin must create/repair required WordPress pages after activation/version changes.

### Learner
Dashboard; My Profile; Subjects; Progress; Documents; Content; Exam Practice; Exam & Proctoring; Pathways; Applications; Funding; Work Readiness; Teacher Support; Notifications; Settings.

### Parent / Guardian
Dashboard; My Children; Progress; Attendance; Teacher Feedback; Pathways; Parent Academy; Cases; Teacher Support; Notifications; Settings.

### Teacher
Dashboard; My Classes; Learners; Assessments; Marking; Content; Subject Guide; Interventions; Teacher Academy; Cases; Agent Centre; Notifications; Settings.

### Counsellor
Dashboard; Learner Cases; Pathways; Career Guidance; Interventions; Referrals; Funding; Applications; Agent Centre; Reports; Notifications.

### School Administrator
Dashboard; Learners; Teachers; Classes; Programmes; Attendance; Interventions; Reports; Users & Roles; Agent Centre; Audit Logs.

### Principal
Dashboard; School Analytics; Attendance; Subject Performance; Interventions; Teacher Development; Agent Centre; Reports; Notifications.

### Education Department
Dashboard; District Analytics; School Performance; Education Trends; Skills Pipeline; Digital Inclusion; Agent Centre; Reports; Audit Logs.

### Academic Administrator
Dashboard; Curriculum; Content Studio; Question Banks; Moderation; Academic Analytics; Training; Agent Centre; Guardrails; Audit Logs; Settings.

### University / TVET
Dashboard; Programmes; Entry Requirements; Applications; Scholarships; Prospective Learners; Agent Centre; Reports.

### Employer
Dashboard; Opportunities; Internships; Learnerships; Skills Demand; Talent Pipeline; Agent Centre; Reports.

### Platform Administrator
Dashboard; Agent Centre; Guardrails; Users & Roles; Microsoft integrations; Notification Rules; CRM; Security; Notifications; Audit Logs; System Health; Reports; Settings.

---

## 8. Ayanda AI assistant

Ayanda is a contextual education assistant, not a generic chatbot.

She must answer questions about:
- subjects;
- learner performance;
- progress/trends;
- attendance;
- pathways;
- funding;
- applications;
- work readiness;
- portal/page explanation;
- reports;
- teacher availability/support;
- authorised school/admin data.

Only role-authorised context may be supplied to Ayanda.

### Empathy
When users are confused, worried, stressed or frustrated:
- acknowledge briefly and naturally;
- avoid patronising language;
- move quickly to practical guidance.

### Navigation-first behaviour
Resolve navigation locally before any GPT/agent call.

Examples:
- “Open Pathways”
- “Progress”
- “Show Funding”
- “Open Teacher Support”
- “Go to Documents”
- “Open Reports”
- “Open Security”

When asked to open a page, open it immediately. Do not explain what the page can do instead.

---

## 9. Voice

There is one assistant identity: **Ayanda**. Do not offer male voices.

Global speaker ON/OFF:
- ON: read responses aloud;
- OFF: stop current audio and keep future responses text-only;
- microphone/voice commands continue working while speaker output is OFF;
- persist preference.

### Automatic voice by language
- `en-ZA` → Azure `en-ZA-LeahNeural`
- `af-ZA` → Azure `af-ZA-AdriNeural`
- `zu-ZA` → Azure `zu-ZA-ThandoNeural`
- isiXhosa, Sesotho, Setswana, Sepedi, Xitsonga, Tshivenda, siSwati, isiNdebele → configured multilingual OpenAI Ayanda fallback.

Supported portal languages:
English, Afrikaans, isiZulu, isiXhosa, Sesotho, Setswana, Sepedi, Xitsonga, Tshivenda, siSwati, isiNdebele.

Translate the entire portal UI where translations exist, not only menus. Keep English where no reliable translation is available.

Admin settings must include Listen/Preview per language.

Do not send a floating-point `speed` parameter to OpenAI TTS.

If Azure TTS fails, fall back to OpenAI rather than failing the voice experience.

Only one audio stream may play at a time.

---

## 10. OpenAI integration

Platform Administrator only. Store credentials server-side.

Support:
- API key;
- optional Project ID;
- optional Organization ID;
- model selection.

Normalize keys by removing accidental `Bearer ` prefixes, quotes and whitespace.

Connection tests must exercise the same Responses API path used by Ayanda.

Never expose secrets in browser JavaScript, logs, screenshots or reports.

---

## 11. Microsoft Foundry / Azure OpenAI

The active settings UI must match the actual Foundry project credentials.

### Project endpoint
`https://sita-resource.services.ai.azure.com/api/projects/sita`

### Azure OpenAI endpoint
`https://sita-resource.openai.azure.com/openai/v1`

### API key
Entered securely by the administrator; never committed.

### Model deployment
Runtime setting only; not a credential.

### Remove legacy fields from active UI
Do not require:
- Tenant ID
- Client ID
- Client Secret
- integration-mode selector
- agent proxy URL
- proxy bearer key
- default hosted-agent ID
- MCP scope
- MCP project-connection ID
- MCP token/secret

Migrate/remove unused legacy values during upgrade.

### Runtime
Primary:
`POST https://sita-resource.services.ai.azure.com/api/projects/sita/openai/v1/responses`

Fallback:
`POST https://sita-resource.openai.azure.com/openai/v1/responses`

Authentication:
`api-key: <saved Microsoft/Foundry API key>`

Diagnostics must distinguish authentication, endpoint, permission, deployment/model, quota/rate limit, DNS and network timeout errors.

---

## 12. Microsoft MCP

Default endpoint:
`https://mcp.ai.azure.com`

Do not reuse the Foundry API key as an MCP user credential.

Microsoft-hosted MCP uses delegated Microsoft Entra sign-in / Azure RBAC for tool authorization.

Use a protocol-correct Streamable HTTP MCP `initialize` JSON-RPC POST for connectivity testing, not a plain streaming GET.

Report endpoint reachability separately from delegated authorization/tool approval.

---

## 13. Azure Speech

Use one Azure Speech key field.

Resource endpoint:
`https://southafricanorth.api.cognitive.microsoft.com`

Regional TTS host:
`https://southafricanorth.tts.speech.microsoft.com`

TTS route:
`/cognitiveservices/v1`

Voice list:
`/cognitiveservices/voices/list`

Treat `cURL error 28` as network/timeout, not automatically as invalid credentials.

Diagnostics should mention outbound TCP 443/DNS where appropriate for:
- `*.speech.microsoft.com`
- `*.services.ai.azure.com`
- `*.openai.azure.com`
- `mcp.ai.azure.com`

---

## 14. Teacher Support

Learner and Parent/Guardian roles must have Teacher Support.

Show:
- teacher identity;
- username;
- subject(s);
- email where appropriate;
- current availability;
- office hours;
- next available slot;
- status indicator.

Reference teacher: **Lerato Khumalo**
Office hours: **Tuesday & Thursday, 14:00–16:30**

Statuses:
- Available now
- Available later today
- Offline — next slot ...

Ayanda chat must include **Ask Teacher**.

Support request types:
- subject question;
- assessment feedback;
- tutoring/support;
- call request;
- preferred contact time.

Log and notify as appropriate.

---

## 15. CRM

Provide a fully fledged role-aware CRM with:
- contacts;
- learner/guardian relationships;
- cases;
- support requests;
- notes;
- statuses;
- activity history;
- follow-ups;
- owner;
- timestamps;
- filtering/search;
- role permissions;
- audit events.

---

## 16. Exam Practice and local OCR

Support local upload/extraction for PDF, DOCX, images, TXT, MD, CSV, JSON and RTF.

Original files should stay in the browser where technically feasible.

Local extraction:
- PDF text;
- DOCX text;
- text formats;
- OCR for scans/images;
- best-effort handwriting OCR.

Explain that handwriting OCR may be less accurate.

Do not send extracted text externally until explicit AI-analysis consent is given.

Exam analysis should identify:
- difficult question types;
- command words;
- cognitive demands;
- likely answering mistakes;
- response structure;
- time management;
- revision priorities;
- how to improve answer quality.

During a live proctored exam Ayanda must not reveal answers.

---

## 17. Live exam / proctoring simulation

Before start require:
- proctoring terms;
- camera consent;
- available single-display check;
- declaration additional displays are disconnected;
- declaration unrelated tabs/windows/apps are closed;
- fullscreen where supported.

If another display is detected, block exam start and instruct the learner to disconnect it.

Do not falsely claim a normal browser can enumerate/force-close every native application or browser tab. Use browser visibility/focus/display APIs, learner attestation and neutral event logging.

During exam:
- lock all other EduPath pages;
- disable role switching;
- allow only exam/proctor controls;
- log neutral focus/visibility/display events;
- no automated misconduct decision.

On end/submit/abort/navigation:
- stop all camera tracks immediately;
- clear timers/listeners;
- exit fullscreen where possible;
- restore normal navigation.

---

## 18. Reports and PDFs

Reports must be professional and genuinely different by report type/role.

Each report should include:
- school/organisation name in header;
- report title/date;
- authorised context;
- KPI cards where appropriate;
- relevant tables;
- interpretation/notes;
- confidentiality statement;
- page numbers;
- governance footer where relevant.

Use the **school name**, not the Pyrneo logo, as prominent report identity.

Report types include Academic Progress, Attendance, Learner Support, School Performance, Subject Performance, Interventions, District Performance, Skills Pipeline, Digital Inclusion, Applications, Scholarships/Funding, Security & Authentication, Notification Delivery, Audit/Governance and System Health.

Every report link must point to the correct report type and data scope.

---

## 19. Notifications/security alerts

Default security/important recipient:
`samson@pyrneo.com`

Notify appropriately for:
- failed login attempts;
- OTP lockouts;
- significant security events;
- confirmed high-risk actions;
- important support requests;
- critical integration failures where useful.

Avoid duplicate/noisy emails.

Populate Platform Administrator Security and Notifications workspaces with event history and delivery status.

---

## 20. Security/governance

Design for alignment with:
- POPIA
- EU AI Act
- NIST AI RMF
- NIST CSF
- ISO/IEC 27001
- ISO/IEC 27002

Do not claim certification merely because controls are implemented.

Required controls:
- least privilege;
- role-based access;
- purpose limitation;
- data minimisation;
- consent;
- guardian visibility boundaries;
- human oversight for consequential decisions;
- audit logging;
- server-side secrets;
- prompt-injection defence;
- no autonomous discipline/failure/exclusion decisions;
- explainable recommendations;
- secure REST authorization/nonces;
- proctoring consent;
- AI-sharing consent for locally extracted text;
- secure cookies;
- validation/escaping.

Consequential actions require preview + explicit confirmation.

---

## 21. Agent Centre

Canonical agents:
1. Learning Guide
2. Assessment Coach
3. Pathway Guide
4. Support Navigator
5. Work Readiness Coach
6. Family & Educator Guide
7. Analytics Guide
8. Notification Guide

Governed execution pattern:
`User request → deterministic intent → authorised agent/tool → preview → permission check → human confirmation if consequential → execution → audit event → UI refresh`

Autonomy levels:
- Observe
- Recommend
- Prepare
- Execute low-risk only

Never autonomously discipline, fail or exclude learners, guarantee admissions/employment, or expose unauthorised data.

---

## 22. Pathway Engine

Support University, TVET, Occupational qualifications, Learnerships, Apprenticeships, Certifications, Work-integrated learning and Entrepreneurship.

Use actual learner subject/profile context.

“Compare with Ayanda” must return structured readable text, never binary/base64/malformed output. Guard against unexpectedly large/binary external responses.

---

## 23. Funding and applications

Demonstrate NSFAS, bursaries, scholarships, SETA opportunities, requirements, deadlines, missing documents, status and next actions.

Never guarantee eligibility; present potential matches subject to provider verification.

---

## 24. Language behaviour

When language changes:
- translate the full UI where translations exist;
- rerender current page;
- select the matching Ayanda voice automatically;
- retain current user/context;
- answer in selected language where supported.

Do not translate only menus.

---

## 25. Connection-status dashboard

Platform Admin status cards:
- OpenAI
- Azure Speech
- Microsoft Foundry / Azure OpenAI
- Microsoft MCP

Statuses may include Connected, Not configured, Authentication failed, Permission/RBAC required, Delegated sign-in required, Endpoint reachable, Network timeout, DNS failure, Model/deployment unavailable, Quota/rate limited.

Never expose secrets or label every failure “wrong credentials”.

---

## 26. Link/page quality

Every visible link/action must:
- open a valid page/dialog/action;
- show relevant data;
- respect role access;
- avoid 404s;
- avoid empty generic placeholders;
- preserve theme/language/context;
- work on desktop/tablet/mobile.

Run coverage checks across every role menu.

---

## 27. QA before every release

1. PHP lint every PHP file.
2. JavaScript syntax checks.
3. ZIP integrity checks.
4. Verify package roots (`style.css` and plugin bootstrap at correct level).
5. Check every role menu/page.
6. Test deterministic voice/text navigation.
7. Test speaker on/off.
8. Test Teacher Support/availability.
9. Test OTP and verify identical code in subject/body.
10. Test login/logout/session handling.
11. Test distinct report types/links.
12. Test camera stop on exam end/navigation.
13. Test active-exam navigation lock.
14. Test OpenAI diagnostics.
15. Test Azure Speech/fallback.
16. Test Microsoft Foundry diagnostics.
17. Test MCP status logic.
18. Test light/dark mode.
19. Test mobile/tablet.
20. Produce updated QA documentation.

Never claim a live external service passed unless the real credentialed call succeeded.

---

## 28. Repository rules

Canonical paths:
- `wordpress-theme/edupath-ai-theme/`
- `wordpress-plugin/edupath-ai-platform/`
- `docs/`
- `MASTER-BUILD-PROMPT.md`

Do not commit real API keys, passwords, OTPs, SMTP credentials or private tokens.

GitHub Actions must validate PHP/JavaScript and package installable theme/plugin ZIPs.

---

## 29. Current configuration baseline

### Microsoft Foundry
Project endpoint:
`https://sita-resource.services.ai.azure.com/api/projects/sita`

Azure OpenAI endpoint:
`https://sita-resource.openai.azure.com/openai/v1`

API key:
`[ENTER SECURELY IN WORDPRESS ADMIN — NEVER COMMIT]`

### Microsoft MCP
`https://mcp.ai.azure.com`

### Azure Speech
`https://southafricanorth.api.cognitive.microsoft.com`

### Email
OTP sender:
`EduPath AI <admin@pyrneo.com>`

Security/important notifications:
`samson@pyrneo.com`

---

## 30. Final delivery standard

Treat EduPath AI as a real education product, not a clickable mock-up.

Every release must be cohesive, secure, role-aware, responsive, multilingual and useful. Do not add a feature unless it is wired into user journey, permissions, data and audit. Do not fabricate success states. Preserve stable functionality and regression-test authentication, navigation, voice, reports, role access, exams/proctoring, Teacher Support and connection diagnostics before packaging.