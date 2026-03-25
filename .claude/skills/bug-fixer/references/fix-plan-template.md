# Fix Plan Template

Present this plan to the user for approval **before writing any code**.
The plan MUST include a Security Assessment (filled during Phase 3.5).

---

## Template

```markdown
## Bug Fix: [Short Description]

**Source**: [GitHub Issue #N / User report]
**Severity**: [Critical / High / Medium / Low]
**Affected Area**: [Module / Feature / Page]

---

### Identified Problems

| #   | Problem         | Evidence                                                      | Confidence                      |
| --- | --------------- | ------------------------------------------------------------- | ------------------------------- |
| 1   | [What is wrong] | [Error message, stack trace, log entry, or observed behavior] | [Confirmed / Likely / Possible] |
| 2   | ...             | ...                                                           | ...                             |

### Root Cause

[1-2 sentences explaining WHY the bug exists]

---

### Actions

| #   | Action       | File               | Type                          |
| --- | ------------ | ------------------ | ----------------------------- |
| 1   | [What to do] | `path/to/file.php` | [Fix / Add / Modify / Remove] |
| 2   | ...          | ...                | ...                           |

### Justification

[Why these specific changes fix the root cause without side effects]

---

### Risks

| Risk                  | Impact        | Mitigation        |
| --------------------- | ------------- | ----------------- |
| [What could go wrong] | [Consequence] | [How to mitigate] |

---

### Security Assessment

| #   | Concern                               | OWASP            | Severity     | Mitigation Applied                          |
| --- | ------------------------------------- | ---------------- | ------------ | ------------------------------------------- |
| 1   | [Security concern identified in plan] | [A01-A10 or N/A] | [🔴🟠🟡🔵⚪] | [How it was addressed in the actions above] |

_If no security concerns were identified: "No security concerns identified in the planned fix."_

_This assessment was performed proactively during plan creation (Phase 3.5).
A full security analysis on the implemented code will be executed in Gate 3 (Phase 5)._

---

### Documentation Updates

| Document      | Change           |
| ------------- | ---------------- |
| [Path if any] | [What to update] |

_If no docs need updating, state: "No documentation changes required."_

---

### Regression Test

[Describe what regression test should verify to prevent this bug from recurring]
```

---

## Presentation

After filling the template, present to the user:

> 📋 Plano de correção pronto.
> Deseja prosseguir ou tem alterações?

**Do NOT write code until the user confirms.**

---

## Architecture Change Detection

If the fix plan requires changes to:

- Directory structure
- New architectural layers or patterns
- New dependencies
- Database schema changes beyond the bug scope

**STOP and escalate:**

> ⚠️ A correção deste bug requer mudanças arquiteturais:
>
> - [List changes]
>
> **Riscos**: [Impact description]
>
> Deseja autorizar essas mudanças? Se sim, a documentação relevante será atualizada.
