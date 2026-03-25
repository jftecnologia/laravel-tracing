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
