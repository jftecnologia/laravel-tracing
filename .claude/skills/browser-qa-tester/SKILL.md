---
name: browser-qa-tester
description: Autonomous UX/UI QA skill that orchestrates the browser subagent to evaluate usability, navigation flows, visual hierarchies, console errors, and multi-tenant security boundaries in the browser. Use when the user asks to "test the UI", "run browser qa", "evaluate usability", or "test this page". This skill produces a structured QA report detailing observations, pros, cons, and actionable proposals.
---

# Browser QA Tester

This skill orchestrates the `browser_subagent` to autonomously navigate and evaluate the application's UI/UX, producing a structured QA report.

**Important:** This skill requires the application to be running locally and accessible via a browser. Note that the subagent records a WebP video of its session automatically.

---

## References

Load these files as needed during execution:

- [references/heuristics.md](references/heuristics.md) — The strict UX/UI criteria the subagent will evaluate.
- [references/report-template.md](references/report-template.md) — The Markdown template for the final output report.
- [references/subagent-instructions.md](references/subagent-instructions.md) — The exact prompt structure needed to trigger the `browser_subagent`.

---

## Constraints

- This skill is **read-only / exploratory** — do NOT generate or modify application code.
- Provide the final report solely in the `docs/qa-browser/` directory.
- This skill focuses purely on the frontend (DOM, console logs, visual rendering, navigation) and does not replace the `project-qa-auditor` backend/static checks.
- If the application is not running or the URL is inaccessible, stop immediately and inform the user.

---

## Execution Workflow

```text
Phase 1 → Phase 2 → Phase 3 → Phase 4
Context   Subagent  Analyze   Report
```

### Phase 1: Context Gathering

1. Receive the target feature, flow, or URL from the user.
2. Determine the persona (Global Admin, Pentester, Client) the test should simulate. If the user is unauthenticated or the persona is unclear, explicitly ask for clarification.
3. Identify if the current test requires explicit Multi-Tenant testing (e.g., trying to access a restricted ID in the URL).
4. Use the `get-absolute-url` MCP tool to confirm the correct local server URL for the test.

### Phase 2: Orchestrate Browser Subagent

1. Read `references/subagent-instructions.md`.
2. Fill out the bracketed fields `[...]` in the prompt template using the context gathered in Phase 1.
3. Read `references/heuristics.md` so you fully understand what the subagent is checking.
4. Call the `browser_subagent` tool using the constructed prompt as the `Task` parameter.
5. Set the `RecordingName` to a descriptive string (e.g., `tenant_settings_qa`).

### Phase 3: Analyze Subagent Return

1. Once the `browser_subagent` returns, review its summary of findings.
2. Cross-reference its observations against the heuristics list. Ensure there is enough data to form meaningful Pros, Cons, and Actionable Proposals.

### Phase 4: Generate Final QA Report

1. Read `references/report-template.md`.
2. Generate the filename: `docs/qa-browser/<SEQ>-<SLUG>.md` (check `docs/qa-browser/` for the highest existing `<SEQ>` number, padding with zeros like `0001`). Create the directory if it doesn't exist.
3. Assemble the final Markdown report filling in the subagent's findings into the template sections.
4. Make sure the Actionable Proposals strictly relate to the Negatives (Cons) and have clear justifications.
5. Output the file using `write_to_file`.
6. Use `notify_user` to present the completed report to the user and suggest next steps (e.g., "Should we implement these changes using the `frontend-development` skill?").
