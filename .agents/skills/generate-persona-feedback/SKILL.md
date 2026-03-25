---
name: generate-persona-feedback
description: "Simulate a persona's behavior to generate realistic feedback on the application. Reads a persona profile from docs/personas/, then interacts with the system (browser, code, database) according to the persona's access level, producing a detailed feedback report with agent triage and next-step recommendations. Use when the user asks to 'test as persona X', 'generate feedback as X', 'simulate persona X', 'run persona feedback', 'feedback como a persona Y', 'testar como o Marcelo', or 'gerar feedback da persona Z'."
---

# Generate Persona Feedback

Simulate a persona's real-world behavior to produce a structured feedback report. The skill reads a persona profile, selects interaction tools (browser, code analysis, database queries) based on the persona's access level, and generates a report with both persona-voice feedback and agent-generated triage.

**Output:** A Markdown report in `docs/feedbacks/<persona-slug>/<SEQ>-<scope>.md`

## References

Load these files as needed during execution:

- [references/feedback-report-template.md](references/feedback-report-template.md) — Template for the final feedback report.
- [references/interaction-strategies.md](references/interaction-strategies.md) — Strategies per access type (Browser, Code, DB).

## Constraints

- **Read-only:** Do NOT modify application code. Only generate the feedback report.
- **Persona fidelity:** Strictly respect the persona's access level. If the persona has no DB access, do NOT query the database.
- **Language:** Report in Portuguese (pt-BR). Conversation in Portuguese.

---

## Execution Workflow

```text
Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5
Load      Strategy   Simulate  Report    Present
Persona   Selection  Interaction         & Suggest
```

All phases are mandatory. Execute sequentially.

---

## Phase 1: Load Persona

1. Receive the persona slug or name from the user. If ambiguous, list available personas in `docs/personas/` and ask.
2. Read `docs/personas/<slug>.md`.
3. Extract key fields:
    - **E-mail** (for browser login)
    - **Método de Interação** (determines tools)
    - **Objetivo Principal** (determines what to test)
    - **Dores / Pain Points** (what to validate)
    - **Paciência e Tolerância** (behavioral constraints)
    - **Atenção aos Detalhes** (depth of exploration)
    - **Proficiência Tecnológica** (language/depth of feedback)
4. Receive scope from the user (optional). If not provided, derive from the persona's "Objetivo Principal".

### Authentication Handling

- **E-mail available:** Use it for browser login. Default password for seeders: `password`.
- **E-mail NOT available:**
    1. Search `database/seeders/` for a user with the persona's role.
    2. If found, suggest the existing seeder user to the user.
    3. If not found, ask the user if they want to create a seeder for this profile. If yes, generate the seeder before continuing.

---

## Phase 2: Strategy Selection

Read [references/interaction-strategies.md](references/interaction-strategies.md).

Select tools based on "Método de Interação":

| Access Level    | Tools                                                  |
| --------------- | ------------------------------------------------------ |
| Apenas Browser  | `browser_subagent`, `browser-logs`, `get-absolute-url` |
| Acesso a Código | `view_file`, `grep_search`, `view_file_outline`        |
| Acesso a Banco  | `database-query`, `database-schema`                    |
| Múltiplos       | Combine the above as specified                         |

**Rule:** If the persona has Browser access, always start with the browser (simulates natural user behavior). Code and DB analysis are complementary.

---

## Phase 3: Execute Simulation

Adopt the persona's point of view throughout. Read [references/interaction-strategies.md](references/interaction-strategies.md) for detailed instructions per access type.

### Browser Interaction

- Log in with the persona's email.
- Navigate flows aligned with the persona's "Objetivo Principal".
- Evaluate whether the persona's documented "Dores" manifest in the UI.
- Respect "Paciência": if low, abandon long flows and record as negative.
- Respect "Atenção aos Detalhes": if low, do not hunt for hidden items in deep submenus.
- Check `browser-logs` for console errors after each page.

### Code Interaction

- Analyze controllers, models, and views relevant to the persona's objectives.
- Verify architecture supports the persona's listed needs.
- Look for inconsistencies, broken patterns, or relevant TODOs.

### Database Interaction

- Query data the persona would need to visualize.
- Verify queries/indexes support the filters the persona expects.
- Check data integrity for the persona's context.
- **DBA-level analysis:** Evaluate query performance (EXPLAIN), check for missing or redundant indexes, analyze modeling best practices, identify data access security risks (e.g., sensitive data without encryption, excessive permissions), and suggest optimizations.

---

## Phase 4: Generate Feedback Report

1. Capture the current git commit: `git rev-parse --short HEAD`.
2. Read [references/feedback-report-template.md](references/feedback-report-template.md).
3. Write sections 1–4 **in the persona's voice** (first person, matching their technical depth and language).
4. Write section 5 (Agent Triage) with technical analysis and skill recommendations.
5. Determine the file path:
    - Check `docs/feedbacks/<persona-slug>/` for the highest existing `<SEQ>` number.
    - Generate filename: `<SEQ>-<scope-slug>.md`
    - Example: `docs/feedbacks/multi-client-single-tenant-marcelo-duarte/0001-dashboard-cliente.md`
6. Save the report using `write_to_file`.

---

## Phase 5: Present & Suggest Action

1. Present the report to the user via `notify_user` with a concise summary of findings.
2. **Suggest the next action directly** based on the triage, e.g.:
    > "Encontrei 2 bugs e 3 melhorias de UX. Quer que eu já corrija o bug mais crítico usando `bug-fixer`? Ou prefere um brainstorming sobre as melhorias de UX?"
3. If the user accepts, invoke the suggested skill in the same session.

---

## Skill Mapping for Triage

Use this mapping when classifying findings and recommending next steps:

| Finding Category | Recommended Skill(s)                       |
| ---------------- | ------------------------------------------ |
| Bug              | `bug-fixer`                                |
| UX Improvement   | `brainstorming` → `task-planner`           |
| New Feature      | `generate-prd` → `generate-task-breakdown` |
| Performance      | `task-planner`                             |
| Security Risk    | `security-analyst`                         |
| Architecture     | `brainstorming` (architecture type)        |
| Missing Tests    | `generate-test`                            |
| i18n Gap         | `i18n-manager`                             |
