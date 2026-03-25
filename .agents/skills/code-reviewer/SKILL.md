---
name: code-reviewer
description: "Deep codebase review for code health, engineering quality, test quality, and specification compliance. Identifies gaps, incompatibilities, hardcoded configurations, unused classes/methods, overengineering, SOLID violations, possible refactors, divergent/conflicting business rules, test coverage gaps, missing test paths (happy/unhappy/security), and simple security flaws. Generates a structured report with severity ratings, justifications, concrete examples, and actionable next steps linked to appropriate skills. Use when the user asks to review code quality, audit code health, check for dead code, find technical debt, review SOLID compliance, identify refactoring opportunities, audit test quality, check test coverage, or verify code alignment with project specs (e.g., 'revise o código', 'review code quality', 'encontre código morto', 'verifique adesão ao SOLID', 'identifique refatorações', 'audite a saúde do código', 'verifique divergências com as specs', 'revise os testes', 'audit test quality')."
---

# Code Reviewer

Deep codebase review for code health, engineering quality, test quality, and specification compliance. Produce a structured report at `docs/reviews/<SEQ>-<SLUG>.md`.

---

## References

Load these files as needed during execution:

- [references/analysis-checklists.md](references/analysis-checklists.md) — Detailed checklists for Phases 2–6
- [references/report-template.md](references/report-template.md) — Report template for Phase 7
- [references/severity-classification.md](references/severity-classification.md) — Finding severity and priority rules

---

## Constraints

- **Read-only**: Do NOT generate, modify, or delete any application code
- **Output only to**: `docs/reviews/` directory
- **Role**: Reviewer only — report findings, never fix them
- **Language**: Reports in English, conversation in pt-BR
- **Security scope**: Only simple/obvious flaws. For deep OWASP analysis, recommend `security-analyst`
- **Test scope**: Analyze test quality, NOT execute tests. For test execution, recommend `project-qa-auditor`

---

## Scope Determination

Before starting, determine the review scope from user input:

| Trigger | Scope | Phases |
| ------- | ----- | ------ |
| "full review" / "revisão completa" | Entire application | All 7 phases |
| "review module X" / "revise o módulo X" | Specific module/directory | All phases (scoped) |
| "review tests" / "revise os testes" | Test files only | Phases 1, 6, 7 |
| "review interface tests" / "revise testes de interface" | Interface test files | Phases 1, 6, 7 |
| "review code quality" / "revise a qualidade" | Code quality only | Phases 1, 2, 3, 4, 7 |
| "review specs compliance" / "verifique as specs" | Spec alignment only | Phases 1, 5, 7 |
| "pre-refactor review" / "antes de refatorar" | Changed files + related code | All phases (scoped) |

For scoped reviews, ask the user for:

1. The target directories or files (if not obvious)
2. Whether to include test analysis (default: yes)

---

## Execution Flow

```text
Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5 → Phase 6 → Phase 7
Context   Dead Code  Quality    Config    Specs     Tests     Report
```

Execute phases sequentially. Skip phases only if scope excludes them (see table above).

---

## Phase 1 — Scope & Context

1. Determine scope from user input (see Scope Determination above)
2. Load project standards:
   - `docs/engineering/CODE_STANDARDS.md` — development philosophy and patterns
   - `docs/engineering/TESTING.md` — testing guidelines and conventions
   - `docs/PRD.md` — business requirements (for Phase 5)
   - `docs/architecture/*.md` — architecture decisions (as needed)
3. Build file inventory for analysis:
   - Use `find` / `grep` to list files in scope
   - Count files per layer (controllers, actions, models, services, tests)
4. Note the inventory size — adjust depth of analysis accordingly
5. Capture the reviewed commit for traceability:
   - Run `git rev-parse --short HEAD` and `git rev-parse HEAD` to get short and full commit hashes
   - Run `git rev-parse --abbrev-ref HEAD` to get the current branch name
   - Store these values for the report header (Phase 7)

---

## Phase 2 — Dead Code Analysis

Load [references/analysis-checklists.md](references/analysis-checklists.md) § Phase 2.

Identify code that exists but serves no purpose:

1. **Unused classes** — not imported or instantiated anywhere
2. **Unused methods** — public methods never called outside their class
3. **Unused routes** — defined routes not referenced by any controller or frontend
4. **Unreachable code** — code after return/throw, impossible conditions
5. **TODO/FIXME/HACK inventory** — catalog all markers with context
6. **Orphaned config values** — config keys never read by application code
7. **Unused database columns** — model `$fillable` columns not used in queries or views

For each finding, record: file, line, what is unused, and confidence level.

---

## Phase 3 — Code Quality, SOLID & Code Smells

Load [references/analysis-checklists.md](references/analysis-checklists.md) § Phase 3.

Analyze adherence to SOLID principles, code quality standards, and identify code smells:

1. **S — Single Responsibility**: classes with multiple unrelated concerns
2. **O — Open/Closed**: classes requiring modification for extension
3. **L — Liskov Substitution**: subclass violations of parent contracts
4. **I — Interface Segregation**: fat interfaces forcing unused implementations
5. **D — Dependency Inversion**: direct instantiation instead of injection
6. **Overengineering**: unnecessary abstractions, premature optimization, excessive layers
7. **God classes/methods**: excessive LOC, too many responsibilities
8. **Code duplication**: repeated logic across files
9. **Naming inconsistencies**: conventions that differ across similar files
10. **Missing type hints**: parameters, return types, properties without types
11. **Code smells**: feature envy, long parameter list, shotgun surgery, data clumps, primitive obsession, inappropriate intimacy, message chains, dead stores, speculative generality

---

## Phase 4 — Configuration & Hardcoded Values

Load [references/analysis-checklists.md](references/analysis-checklists.md) § Phase 4.

Identify values that should be externalized:

1. **Magic numbers/strings** — unexplained literal values in logic
2. **`env()` outside config** — direct `env()` calls in app code (must be in `config/` only)
3. **Hardcoded URLs/paths** — URLs, file paths, API endpoints in code
4. **Hardcoded credentials** — secrets, API keys, tokens in source
5. **Tenant-configurable values** — values that should vary per tenant but are static
6. **Missing config files** — behavior controlled by hardcoded logic instead of config

---

## Phase 5 — Spec Compliance & Business Rules

Load [references/analysis-checklists.md](references/analysis-checklists.md) § Phase 5.

Cross-reference code behavior with project specifications:

1. **Logic divergences** — implementation that contradicts PRD/specs
2. **Conflicting calculations** — same metric/value computed differently in multiple places
3. **Inconsistent business rules** — same rule with different logic across files
4. **Missing edge cases** — spec-defined scenarios not handled in code
5. **Status flow violations** — state transitions that don't match spec definitions
6. **Permission inconsistencies** — RBAC logic that differs from `RBAC_GUIDE.md`

---

## Phase 6 — Test Quality Analysis

Load [references/analysis-checklists.md](references/analysis-checklists.md) § Phase 6.

Analyze existing tests for quality, completeness, and adherence to standards:

1. **Coverage gaps** — controllers/actions/services without corresponding test files
2. **Path completeness**:
   - Happy paths: core functionality tested?
   - Unhappy paths: invalid inputs, 403, 404, edge cases?
   - Security paths: unauthorized access, cross-tenant, RBAC violations?
3. **Test relevance** — tests asserting trivial things, testing implementation details instead of behavior, overly coupled to internal structure
4. **Pattern adherence** — alignment with `TESTING.md`:
   - PestPHP conventions (`it()`, `describe()`, `expect()`)
   - Test naming (descriptive, behavior-oriented)
   - Factory usage and fake setup
   - Proper trait usage (`RefreshDatabase`, etc.)
5. **Consistency** — inconsistent structures, mixed assertion styles, hardcoded IDs/values
6. **Orphaned tests** — tests for classes/features that no longer exist
7. **Missing security test scenarios** — operations without tenant isolation tests, policy actions without matching tests
8. **Interface test coverage** — verify that UI pages are tested:
   - Feature tests for Inertia page rendering (`assertInertia()`)
   - Tests that verify pages contain the expected data/props for the user (not just render without error)
   - Browser tests for complex multi-step UI flows
   - Smoke tests for critical pages (login, dashboard, listings)
   - `assertNoJavaScriptErrors()` in browser tests
9. **Business logic test coverage** — verify that domain rules are tested:
   - Calculations (CVSS, risk ratings, statistics) with unit tests and known expected values
   - Status/state transitions tested for both valid and invalid transitions
   - Business validation rules tested for rejection of invalid data
   - Cross-reference with Phase 5 findings: every identified business rule should have a corresponding test

---

## Phase 7 — Report Generation

Load [references/report-template.md](references/report-template.md).

### File Naming

1. Check existing files in `docs/reviews/` to determine next sequence number
2. Generate filename: `<SEQ>-<SLUG>.md`
   - `SEQ`: 4-digit zero-padded number (e.g., `0001`)
   - `SLUG`: kebab-case descriptor derived from scope (e.g., `full-review`, `module-vulnerabilities`, `test-quality`)
3. Example: `docs/reviews/0001-full-review.md`

### Report Assembly

Fill the template with data collected from all phases. Load [references/severity-classification.md](references/severity-classification.md) for rating criteria.

Include the commit hash and branch captured in Phase 1 in the report header.

Each finding must include:

1. **ID**: `CR-NNN` (sequential per report)
2. **Severity**: 🔴 Critical / 🟠 High / 🟡 Medium / 🔵 Low / ⚪ Info
3. **Category**: which phase found it
4. **Location**: file path and line number(s)
5. **Description**: what is wrong and why it matters
6. **Justification**: reference to the standard, spec, or principle violated
7. **Example**: concrete code snippet showing the issue
8. **Recommended action**: what to do and which skill to use

### Next Steps Section

Group recommendations by suggested workflow:

| Finding Type | Recommended Skill |
| --- | --- |
| Unused code removal | `implement-task` |
| Refactoring opportunity | `task-planner` → `implement-task` |
| Code smells requiring refactor | `task-planner` → `implement-task` |
| Security flaw (deeper analysis) | `security-analyst` |
| Spec divergence (needs clarification) | `brainstorming` or direct user decision |
| Missing tests / coverage gaps | `generate-test` |
| Missing unhappy/security test paths | `generate-test` |
| Missing interface / browser tests | `generate-test` (browser/feature) |
| Missing business logic tests | `generate-test` (unit) |
| Orphaned / irrelevant tests | `implement-task` (cleanup) |
| Test pattern inconsistencies | `implement-task` (refactor) |
| UI/frontend issues | `frontend-development` |
| Configuration externalization | `implement-task` |
| Architectural concern | `generate-architecture` |

---

## Relationship with Other Skills

| Concern | Handled By | Perspective |
| ------- | ---------- | ----------- |
| Code health & quality | **code-reviewer** | "Is it well-written and sustainable?" |
| Functional validation | `project-qa-auditor` | "Does it work correctly?" |
| Security vulnerabilities | `security-analyst` | "Can it be exploited?" |
| Test execution | `project-qa-auditor` | "Do the tests pass?" |
| Test creation | `generate-test` | "What tests are needed?" |
| Test quality analysis | **code-reviewer** | "Are the tests good?" |

---

## Completion Criteria

Review is complete when:

1. ✅ All applicable phases executed
2. ✅ Report generated at `docs/reviews/<SEQ>-<SLUG>.md`
3. ✅ Report includes reviewed commit hash and branch
4. ✅ User informed of finding summary (counts by severity)
5. ✅ Actionable next steps with skill references provided
6. ✅ High-severity items clearly highlighted
