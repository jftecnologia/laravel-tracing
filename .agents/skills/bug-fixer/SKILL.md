---
name: bug-fixer
description: "Diagnose and fix bugs in the application from GitHub Issues or user-reported descriptions (including images). Use when the bug requires investigation to find the root cause — error logs, stack traces, unexpected behavior, screenshots of errors (e.g., 'corrija o bug #23', 'fix issue #15', 'resolva o erro no dashboard', 'debug the 500 error', 'corrija esse problema'). This skill investigates root causes using MCP tools, plans fixes with user approval, implements corrections with quality gates, and never modifies architecture without explicit authorization. For simple fixes where the cause is already known, prefer `implement-task`."
---

# Bug Fixer

Diagnose and fix bugs following engineering standards with investigation, planning, and quality gates.

> **When to use this vs `implement-task`**: Use `bug-fixer` when the root cause is **unknown** and needs investigation (MCP tools, logs, DB inspection). Use `implement-task` when the fix is **already clear** and just needs implementation.

---

## Related References

- [references/investigation-guide.md](references/investigation-guide.md) — Diagnostic tools and checklist by bug type
- [references/gate-checklist.md](references/gate-checklist.md) — Quality gate validation steps
- [references/fix-plan-template.md](references/fix-plan-template.md) — Fix plan template for user approval
- `.agents/skills/implement-task/references/compliance-checklist.md` — Unified compliance checklist (all gates)

---

## 1. Inputs

### Required

- **Bug report**: One of:
    - GitHub Issue number (e.g., `#23`)
    - Text description from user (may include screenshots/images)

### Auto-loaded

Read these files at the start of every bugfix:

1. `docs/engineering/STACK.md` — Tech stack and available scripts
2. `docs/engineering/CODE_STANDARDS.md` — Development philosophy and quality standards
3. `docs/engineering/WORKFLOW.md` — Git workflow and commit guidelines

### Optional Context (read during Investigation)

- `docs/PRD.md` — Product requirements for expected behavior
- `docs/architecture/*.md` — Architecture decisions, DB schema, components
- `docs/design/*.md` — UI specifications (if frontend bug)
- Existing code — Sibling files for conventions

---

## 2. Phase 0: Input Collection (MANDATORY)

### From GitHub Issue

Fetch issue details:

```bash
gh issue view <NUMBER> --json title,body,labels,state,comments
```

Extract: title, description, reproduction steps, expected vs actual behavior, screenshots.

### From User Report

Evaluate the bug description for completeness. Ask clarifying questions **only when needed**.

### Clarification Rules

- **Never guess or assume.** If information is incomplete, ASK.
- Ask **at most 10 questions** — but only the minimum necessary.
- Group related questions into a single message.
- Questions must be specific and actionable.

### What to clarify (only if not already provided)

- Exact error message or unexpected behavior
- Steps to reproduce
- Expected vs actual behavior
- Which user role / view / page is affected
- When it started occurring (if known)
- Whether it is consistent or intermittent

### When to skip clarification

Skip ONLY when the bug report is fully clear:

- Error message, location, and reproduction steps are explicit
- Root cause is obvious from the report

If skipping, state: "Relatório claro. Prosseguindo para investigação."

---

## 3. Phase 1: Investigation

Research the bug systematically. Load [references/investigation-guide.md](references/investigation-guide.md) for detailed diagnostic steps.

### Step 1: Immediate Diagnostics

Use MCP tools based on bug type:

| Symptom                    | First Tool                     |
| -------------------------- | ------------------------------ |
| Backend error / 500        | `last-error`                   |
| Frontend error / broken UI | `browser-logs`                 |
| Wrong data displayed       | `database-query`               |
| Route not found / 404      | `list-routes`                  |
| Permission denied / 403    | `list-routes` + check policies |
| Validation error / 422     | Read the FormRequest class     |

### Step 2: Code Analysis

1. Read the file(s) at the error location
2. Trace the execution flow (controller → action/service → model)
3. Check sibling files for correct patterns
4. Search for related code: `grep_search` for class/method usage

### Step 3: Context Analysis

1. Check recent git history for the affected file(s):

    ```bash
    git log --oneline -10 -- <file>
    ```

2. Read relevant docs (`docs/architecture/*.md`, `docs/PRD.md`) for expected behavior
3. Use `search-docs` MCP to verify Laravel framework behavior if uncertain

### Step 4: Reproduce (when possible)

Use `tinker` to test hypotheses or `database-query` to verify data state.

---

## 4. Phase 2: Root Cause Analysis

After investigation, document:

1. **What is broken** — The specific failure point
2. **Why it is broken** — The root cause (not just the symptom)
3. **Evidence** — Error messages, stack traces, code references, data queries
4. **Confidence** — Confirmed / Likely / Possible

If multiple possible causes exist, list each with confidence level.

---

## 5. Phase 3: Fix Planning (User Approval Required)

Load [references/fix-plan-template.md](references/fix-plan-template.md) and fill the template.

### Plan Contents

The plan MUST include:

1. **Identified problems** (or possible problems) with evidence
2. **Actions** to take with specific files to modify
3. **Justifications** for why these changes fix the root cause
4. **Risks** — What could go wrong, impact, mitigation
5. **Documentation updates** — Which docs (if any) need updating

### Architecture Protection (CRITICAL)

If the fix requires ANY of these, **STOP and escalate to the user**:

- New architectural layers or patterns
- Changes to directory structure
- New external dependencies
- Significant database schema changes beyond the bug scope
- Changes to existing design patterns

Escalation template:

> ⚠️ A correção deste bug requer mudanças arquiteturais:
>
> - [List changes]
>
> **Riscos**: [Detailed impact]
> **Documentação a atualizar**: [List all affected docs]
>
> Deseja autorizar?

### After Creating the Plan

**Do NOT present the plan to the user yet.**
Proceed to Phase 3.5 (Security Impact Analysis) first.

---

## 5.5. Phase 3.5: Security Impact Analysis (Automatic)

After creating the fix plan and BEFORE presenting it to the user, perform a proactive security impact analysis on the planned actions.

This phase ensures the user always sees a plan with security concerns already identified and mitigated.

### What to Analyze

For each action in the fix plan, evaluate against:

1. **OWASP Top 10 patterns** — Reference the vulnerability patterns in
   `security-analyst/references/laravel-vulnerabilities.md`
2. **Multi-tenant isolation** — Does the fix maintain tenant boundaries?
   (scoped queries, tenant middleware, no cross-tenant data leaks)
3. **Authorization gaps** — Does the fix check permissions via Policies/Gates?
4. **Input validation** — Does the fix validate all user input via FormRequest?
5. **Data exposure** — Could the fix leak sensitive data in responses or logs?
6. **Mass assignment** — Are `$fillable`/`$guarded` properly set on affected models?

### How to Apply

1. Review each planned action through a security lens
2. If security concerns are found:
    - **Amend the action** in the plan with the necessary mitigation
    - Add a row to the **Security Assessment** table in the fix plan template
3. If the fix INTRODUCES a security risk that cannot be mitigated in the plan:
    - Flag it as a `> [!WARNING]` in the plan for the user to evaluate
4. If no concerns are found:
    - Fill the Security Assessment table with: "No security concerns identified."

### Security Impact Checklist

| #   | Check            | Question                                                           |
| --- | ---------------- | ------------------------------------------------------------------ |
| 1   | Access Control   | Does the fix maintain proper authorization (policies, middleware)? |
| 2   | Input Handling   | Is all user input validated before use?                            |
| 3   | Query Safety     | Are queries parameterized? No raw SQL with user input?             |
| 4   | Tenant Isolation | Does the fix respect multi-tenant boundaries?                      |
| 5   | Data Exposure    | Could the fix leak sensitive data in responses/logs?               |
| 6   | Mass Assignment  | Are model `$fillable`/`$guarded` correctly set?                    |
| 7   | CSRF/Auth        | Are state-changing routes protected?                               |
| 8   | File Operations  | Are file uploads/downloads validated?                              |

### Present the Final Plan

After completing the security impact analysis, the plan includes the filled
**Security Assessment** section. Present it to the user:

> 📋 Plano de correção pronto (com análise de impacto de segurança aplicada).
> Deseja prosseguir ou tem alterações?

**Do NOT write code until the user confirms.**

### This Phase Does NOT Replace Gate 3

Gate 3 (Phase 5) still runs the `security-analyst` skill on the **actual implemented code**.
Phase 3.5 reviews the **plan**; Gate 3 reviews the **code**.

---

## 6. Phase 4: Implementation

### Create Semantic Branch

```bash
git checkout -b fix/<bug-description>
```

### Red-Green Bug Fix Protocol (MANDATORY)

> **Methodology**: Follow **Red-Green TDD** for all bug fixes. This ensures the bug is properly captured as a failing test before any code is changed, preventing regressions and validating the fix.

For each action in the fix plan, follow this sequence **when it makes sense** (i.e., the bug is reproducible via automated tests):

#### Step 1: 🔴 RED — Write a failing test that reproduces the bug

1. Create a test that simulates the exact conditions that trigger the bug
2. The test MUST assert the **expected (correct)** behavior
3. Run the test — it MUST **fail** (confirming the bug exists)
4. Commit: `test(<scope>): add failing test for <bug-description>`

```php
// Example: bug where restore() crashes on null values
it('restores tracing values without error when payload contains null', function () {
    $manager = app(TracingManager::class);

    // This should not throw — but it does (RED)
    $manager->restore(['correlation_id' => null, 'request_id' => null]);

    expect(true)->toBeTrue(); // Reaches here only if no exception
});
```

#### Step 2: 🟢 GREEN — Fix the code until the test passes

1. Implement the minimum fix to make the failing test pass
2. Do **NOT** modify the test — the test defines the correct behavior
3. Run the test — it MUST **pass**
4. Run the full test suite — no regressions
5. Commit: `fix(<scope>): <short description>`

#### When to skip Red-Green

Skip this protocol ONLY when:

- The bug cannot be reproduced via automated tests (e.g., visual-only, environment-specific)
- The fix is a configuration change with no testable code path

If skipping, state: "Red-Green não aplicável: [motivo]. Prosseguindo com correção direta."

### Semantic Commits

```text
test(<scope>): add failing test for <bug-description>   # RED
fix(<scope>): <short description>                        # GREEN

[optional body with context]

Fixes #<issue-number>
```

### Consistency Rules

Before modifying any file:

1. Check sibling files for structure, naming, and approach
2. Follow existing patterns — do not introduce new ones
3. Minimize change scope — fix only what is broken

### Skill Delegation

| Need                     | Skill                     |
| ------------------------ | ------------------------- |
| Frontend fix             | `frontend-development`    |
| Auth-related fix         | `developing-with-fortify` |
| Writing regression tests | `generate-test`           |

---

## 7. Phase 5: Quality Gates

**All gates MUST pass.** See `docs/engineering/QUALITY_GATES.md` and [references/gate-checklist.md](references/gate-checklist.md) for details.

### Gate 1: Code Quality (Lint)

```bash
composer lint
npm run lint && npm run types
```

### Gate 2: Tests

1. Run full test suite — fix must not break anything:

    ```bash
    composer test
    ```

2. Write regression test if the bug had no test coverage
3. Invoke **generate-test** skill if needed

### Gate 3: Security Analysis

Invoke **security-analyst** skill on modified files.

- **CRITICAL/HIGH** → Must fix before completing
- **MEDIUM/LOW** → Inform user, proceed with their decision

### Gate 4: i18n Verification

Scan modified files for hardcoded user-facing strings:

- **PHP files**: must use `__()`
- **TSX/React files**: must use `t()` from `useLaravelReactI18n`
- **Blade files**: must use `__()` or `@lang()`

**If hardcoded strings found:**

1. Replace with translation helpers
2. Add keys to locale files
3. Commit: `chore(i18n): add missing translations`

> For a full audit, invoke the **i18n-manager** skill.

---

## 8. Phase 6: Completion

### Update Documentation

If the fix affects existing documentation:

- Update relevant `docs/architecture/*.md`
- Update `README.md` if public API changed
- Update config comments if behavior changed

### Close GitHub Issue (if applicable)

If the bug came from a GitHub issue, the commit message should include `Fixes #<number>`.

### Pull Request

Ask the user:

> ✅ Bug corrigido com sucesso.
> Deseja que eu abra um Pull Request?

### If User Confirms

**Title Format:**

```text
fix: <short description>
```

**Description Template:**

```markdown
## What

[Brief description of the bug fix]

## Root Cause

[What caused the bug and why]

## Fix

[Technical approach and changes made]

## Regression Test

- [x] Test added to prevent recurrence

## Quality Gates

- [x] `composer lint` passes
- [x] `npm run lint && npm run types` passes
- [x] `composer test` passes
- [x] Security analysis: [PASS / PASS WITH WARNINGS]
- [x] i18n verification: [PASS / N/A]

Fixes #<issue-number>
```

---

## 9. Error Handling

### If Gate Fails

1. Report the failure clearly
2. Propose a fix
3. Ask user: Fix and retry / Skip with justification / Abort

### If Bug Cannot Be Fixed

Report what was found and suggest alternatives:

- Workaround
- Escalation to full feature refactor
- Marking as known issue with documentation

---

---

## Completion Criteria

Bug fix is complete when:

1. ✅ Root cause identified and documented
2. ✅ 🔴 Failing test written that reproduces the bug (when applicable)
3. ✅ 🟢 Fix implemented — test passes without test modification
4. ✅ Fix addresses root cause (not just symptom)
5. ✅ `composer lint` passes
6. ✅ `composer test` passes (no regressions)
7. ✅ Security analysis shows no CRITICAL/HIGH issues
8. ✅ Semantic commits made (test commit + fix commit)
9. ✅ Documentation updated (if affected)
10. ✅ User asked about Pull Request
