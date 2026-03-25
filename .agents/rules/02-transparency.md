---
trigger: always_on
---

# Transparency & Proactive Communication (ENFORCED)

Complementary rule to `00-bootstrap.md`. Ensures the agent always justifies decisions,
detects spec conflicts, and proactively suggests improvements.

---

## 1. Decision Justification

Every response that involves code changes MUST include a summary block listing:

### Required Disclosures

- **Rule(s) applied**: which rules files guided the response (`.agents/rules/` or `.claude/rules/`)
- **Skill(s) used**: which `.agents/skills/**/SKILL.md` was followed (if any)
- **Docs consulted**: which `docs/**/*.md` files were referenced for decisions
- **Code patterns followed**: which existing files/classes served as reference
- **Decisions made**: key choices and WHY (not just what)

### Format

Use a concise block at the end of the response:

```text
📋 Decisions & Sources:
- Rule: 01-quality-shield.md (testing standards, doc maintenance)
- Skill: implement-task (SKILL.md)
- Docs: SECURITY.md (authorization), CODE_STANDARDS.md (naming conventions)
- Pattern: followed existing Policy structure
- Decision: used Action instead of Service because it's a single-purpose operation
```

If no matching skill or documentation was found:

```text
📋 Decisions & Sources:
- No matching skill or documentation found; proceeding with CLAUDE.md and existing code patterns.
- Decision: [justification]
```

---

## 2. Spec Conflict Detection

Before implementing a user request, validate against specs **relevant to the change scope**:

### Validation Checklist

**Always validate:**

1. **Architecture compliance**: does it follow `docs/architecture/*.md`?
2. **Engineering standards**: does it follow `docs/engineering/*.md`?

**Validate when the change affects users or permissions:**

3. **PRD compliance**: does the request align with `docs/PRD.md`?
4. **RBAC rules**: does it respect `docs/RBAC_GUIDE.md`?

**Validate when the change affects UI:**

5. **Design system**: does it follow `docs/design/*.md`?

### When a Conflict is Detected

If the user's request conflicts with any specification:

1. **STOP** — do NOT implement silently
2. **WARN** the user clearly:
   - What the spec says
   - What the request asks
   - Why they conflict
3. **SUGGEST** how to proceed:
   - Option A: follow the spec (explain impact)
   - Option B: override the spec (explain what needs updating)
   - Option C: alternative approach that satisfies both

### When a Better Approach Exists

If the agent identifies a better way to achieve the user's goal:

1. **SUGGEST** the alternative with clear justification
2. **COMPARE**: pros/cons vs the original approach
3. **WAIT** for user confirmation before changing approach
4. NEVER silently implement an alternative without asking

---

## 3. Proactive Improvement Reporting

During implementation, the agent SHOULD report opportunities:

### What to Report

- **Code smells**: duplicated logic, oversized methods, missing types, inconsistent patterns
- **Security gaps**: missing authorization checks, unvalidated input, exposed sensitive data
- **Performance risks**: N+1 queries, missing eager loading, unindexed columns in WHERE clauses
- **Consistency issues**: hardcoded values that should be config, missing i18n, ad-hoc colors
- **Missing tests**: code paths without test coverage in the affected area
- **Outdated docs**: documentation that doesn't match current implementation

### How to Report

- Report as a separate section AFTER completing the requested work
- Classify by priority: 🔴 Critical, 🟠 High, 🟡 Medium, 🔵 Low
- Include file paths and specific lines when possible
- NEVER apply improvements without user confirmation — report only

### What NOT to Report

- Trivial style issues already handled by linters
- Framework internals or upstream package behavior
- Issues in unrelated parts of the codebase (unless critical security)

---

## 4. User Interaction Format

When presenting options or asking for user decisions, use a clear visual menu:

### Format

```markdown
## 🎯 Decision Required

1️⃣ **Option A** — Short description
2️⃣ **Option B** — Short description
3️⃣ **Option C** — Short description

> Reply with a number, combine (e.g., "1 and 3"), or comment freely.
```

### Rules

- Always use `## 🎯 Decision Required` as the section header when asking for user input (adapt language per `CLAUDE.md` §8)
- Use numbered emojis (1️⃣ 2️⃣ 3️⃣) for scannable options
- Keep descriptions short — one line per option
- Include a footer inviting free-form comments
- Place this section at the **end** of the response, after all context and analysis
- NEVER bury decisions inside long paragraphs — they must be visually distinct

---

## 5. Destructive Actions — SafeToAutoRun Prohibition

The following actions are **ALWAYS destructive** and MUST use `SafeToAutoRun = false`.
No exceptions, even if the user explicitly requests auto-run.

### Prohibited Commands (SafeToAutoRun = false ALWAYS)

- `git branch -d` / `git branch -D` — delete local branches
- `git push origin --delete` / `git push origin :branch` — delete remote branches
- `rm`, `rm -rf`, `unlink` — delete files or directories
- `DROP TABLE`, `DROP DATABASE`, `DELETE FROM`, `TRUNCATE` — destructive SQL
- `php artisan migrate:rollback`, `migrate:reset`, `migrate:fresh` — destructive migrations
- `composer remove` — remove packages
- `npm uninstall` / `npm remove` — remove packages
- Any command that permanently removes, deletes, or destroys data

### Rule

If a command matches any pattern above, or contains keywords `delete`, `remove`, `drop`,
`truncate`, `destroy`, `purge`, `rm`, or `unlink`:

1. **SafeToAutoRun = false** — requires explicit user approval in the command approval UI
2. If the agent asked a question about the action, **STOP and WAIT** for the user's response
   before proposing the command. NEVER ask a question and execute in the same turn.
3. Asking a question and auto-executing the action in the same response is a **VIOLATION** —
   the question must be genuine, not decorative.

### Separation of Question and Execution

When the agent needs user confirmation for a destructive action:

1. **First response**: ask the question. END the response. Do NOT propose or run any command.
2. **Second response** (after user confirms): propose the command with `SafeToAutoRun = false`.
3. NEVER combine "should I do X?" with the execution of X in a single response.

---

## 6. Bug Fix Commit Protocol

After fixing bugs or resolving reported problems:

1. **Present the fix** — explain what was changed and why
2. **Request user evaluation** — ask the user to verify the fix works as expected
3. **WAIT for user confirmation** — do NOT commit until the user explicitly approves
4. **Only then commit** — using conventional commit format after approval

### Rule

- NEVER auto-commit after a bug fix or problem resolution
- The user MUST evaluate and confirm the fix before any `git add` / `git commit`
- This applies to: bug fixes, error corrections, behavior adjustments, regression fixes
- This does NOT apply to: planned feature implementation where the user has already approved the plan

### Format

After implementing a fix, end with:

```text
A correção foi aplicada. Verifique se o comportamento está correto antes de eu realizar o commit.
```
