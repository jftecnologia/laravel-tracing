# Interaction Strategies by Access Type

## Browser Interaction

**Tools:** `browser_subagent`, `browser-logs`, `get-absolute-url`

### Preparation

1. Resolve base URL via `get-absolute-url` MCP tool.
2. Log in using the persona's e-mail and default password (`password`).

### Execution Behavior

- Navigate **only** the flows the persona would naturally use (derived from "Objetivo Principal").
- **Paciência Baixa:** Limit navigation to 3-5 pages. If a flow takes more than 3 clicks to reach its objective, record as a pain point and move on.
- **Paciência Média:** Explore up to 8-10 pages. Tolerate moderate flows.
- **Paciência Alta:** Explore thoroughly, including secondary menus and settings.
- **Atenção Baixa:** Only interact with visually prominent elements (large buttons, top cards, main navigation). Skip items in dropdowns, accordions, or secondary tabs.
- **Atenção Alta:** Inspect tooltips, small labels, secondary panels, and edge cases.
- After each page, check `browser-logs` for JavaScript errors or warnings.
- Record screenshots/recordings for key findings using the `browser_subagent` recording capability.

### What to Evaluate

- Does the UI surface what the persona needs immediately?
- Are the persona's documented "Dores" present in the current interface?
- Is navigation intuitive for the persona's proficiency level?
- Multi-tenant/multi-client context: Is it clear which tenant/company the persona is viewing?

---

## Code Interaction

**Tools:** `view_file`, `grep_search`, `view_file_outline`, `view_code_item`

### Execution Behavior

- Focus on files relevant to the persona's objectives (controllers, models, views, services).
- Analyze from the perspective of the persona's role:
    - **Developer:** Code quality, patterns, maintainability, documentation.
    - **Pentester:** Attack surface, input validation, auth checks, data exposure.
    - **Tech Lead:** Architecture decisions, coupling, scalability, technical debt.

### What to Evaluate

- Does the architecture support the persona's listed needs?
- Are there inconsistencies or broken patterns?
- Are there TODOs or FIXMEs relevant to the persona's domain?
- Is error handling adequate for the persona's use cases?

---

## Database Interaction

**Tools:** `database-query`, `database-schema`

### Execution Behavior

- Query data the persona would need to see or manage.
- Use `database-schema` to understand the data model relevant to the persona.
- Use `database-query` with SELECT/EXPLAIN to analyze data and performance.

### DBA-Level Analysis

When the persona has a DBA or data-focused role, perform deeper analysis:

1. **Performance:** Run `EXPLAIN` on queries relevant to the persona's views. Identify full table scans, missing indexes.
2. **Indexes:** Check for missing indexes on frequently filtered/joined columns. Identify redundant or unused indexes.
3. **Modeling:** Evaluate normalization level, foreign key constraints, data types appropriateness.
4. **Security:** Check for sensitive data stored in plaintext (passwords, tokens, PII). Verify column-level access patterns.
5. **Data Integrity:** Verify referential integrity, orphaned records, null constraints.
6. **Optimization Suggestions:** Propose index additions, query rewrites, or schema changes.

### What to Evaluate

- Does the data model support the persona's filtering/sorting needs?
- Is the data the persona would see accurate and complete?
- Are there performance bottlenecks that would affect the persona's experience?
