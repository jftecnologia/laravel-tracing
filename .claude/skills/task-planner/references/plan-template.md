# Plan Template

Use this template when generating implementation plans. Adapt sections as needed — omit irrelevant sections, expand where complexity demands.

---

```markdown
# Implementation Plan: [Name]

## Objective

[What this task achieves — the desired end state]

## Justification

[Why this task is needed — business value, technical debt, user need, etc.]

## Summary

[2-3 sentences describing the implementation approach]

## Scope

**In scope:**

- [item]

**Out of scope:**

- [item]

---

## Acceptance Criteria

- [ ] AC-1: [Specific, testable criterion]
- [ ] AC-2: [Specific, testable criterion]
- [ ] AC-3: [Specific, testable criterion]

---

## Files to Create/Modify

**Backend:**

- `app/Path/To/File.php` — [purpose]

**Frontend:**

- `resources/js/pages/path/page.tsx` — [purpose]

**Database:**

- `database/migrations/...` — [purpose, if needed]

**Tests:**

- `tests/Feature/...` — [what to test]

---

## Implementation Steps

1. **[Step name]** — [rationale]
2. **[Step name]** — [rationale]
3. **[Step name]** — [rationale]

---

## Patterns to Follow

- [Sibling file reference for each file type, e.g., "Follow `app/Actions/Project/CreateProject.php` pattern"]

---

## Test Plan

> Tests are mandatory for all tasks that create or modify code.
> Follow `docs/engineering/TESTING.md` — all three pillars required.

### ✅ Happy Path

- [ ] [Expected behavior test — valid inputs, correct outputs]

### ❌ Unhappy Path

- [ ] [Validation errors, invalid states, edge cases, dependency failures]

### 🔒 Security Path

- [ ] [Tenant isolation, RBAC, IDOR, data leakage — per TESTING.md checklist]

### Manual Verification

- [ ] [Step to manually verify behavior]

---

## Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| [risk] | [Low/Medium/High] | [mitigation] |

## Dependencies

- [External blockers or prerequisite tasks, if any]

## Estimated Complexity

[Low / Medium / High] — [brief justification]
```
