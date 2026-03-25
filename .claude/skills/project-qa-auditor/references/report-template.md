# QA Audit Report Template

Use this exact structure for the final report at `docs/qa/<SEQ>-<SLUG>.md`.

---

```markdown
# QA Audit Report: <TITLE>

**Date**: <YYYY-MM-DD HH:MM>
**Auditor**: AI QA Auditor (project-qa-auditor skill)
**Scope**: <description of what was audited>
**Trigger**: <pre-PR | pre-deploy | feature validation | fix validation | full audit>
**Result**: <✅ PASS | ⚠️ PASS WITH WARNINGS | ❌ FAIL>

---

## Executive Summary

<1-2 paragraph overview of audit results>

---

## ✅ Conformities

| # | Area | Description |
|---|------|-------------|
| 1 | <area> | <what is conformant> |

---

## ⚠️ Alerts

| # | Area | Severity | Description | Recommendation |
|---|------|----------|-------------|----------------|
| 1 | <area> | LOW/MEDIUM | <issue> | <recommendation> |

---

## ❌ Non-Conformities

| # | Area | Severity | Description | Location | Impact |
|---|------|----------|-------------|----------|--------|
| 1 | <area> | HIGH/CRITICAL | <issue> | <file:line> | <impact> |

---

## 🔐 Security Risks

| # | Category | Severity | Description | Location | OWASP |
|---|----------|----------|-------------|----------|-------|
| 1 | <category> | CRITICAL/HIGH/MEDIUM/LOW | <issue> | <file:line> | <A01-A10> |

---

## 🧪 Failed Tests

| # | Test | Error | Classification |
|---|------|-------|----------------|
| 1 | <TestClass::method> | <error message> | regression/missing/env/flaky |

**Test Summary**: <X> passed, <Y> failed, <Z> errors out of <T> total

---

## 🌐 Browser Issues

| # | Role | Flow | Result | Notes |
|---|------|------|--------|-------|
| 1 | <role> | <flow name> | ❌ FAIL | <details> |

**Browser Summary**: <X> passed, <Y> failed, <Z> skipped out of <T> total

---

## 📊 Functional Coverage

| Metric | Value |
|--------|-------|
| Controller actions total | <N> |
| Controller actions with tests | <N> |
| Actions total | <N> |
| Actions with tests | <N> |
| Feature test coverage | <N>% |
| Unit test coverage | <N>% |
| PRD entities implemented | <N>/<T> |
| Acceptance criteria met | <N>/<T> (<P>%) |
| Browser flows passed | <N>/<T> |

---

## Quality Gates

| Gate | Result |
|------|--------|
| `composer lint` | ✅ PASS / ❌ FAIL (<N> errors) |
| `npm run lint` | ✅ PASS / ❌ FAIL (<N> errors) |
| `npm run types` | ✅ PASS / ❌ FAIL (<N> errors) |
| `composer test` | ✅ PASS / ❌ FAIL (<N> failures) |

---

## Recommendations

1. <prioritized actionable recommendation>
2. <...>

---

## Next Steps

- [ ] Fix all CRITICAL and HIGH non-conformities
- [ ] Address security risks
- [ ] Fix failing tests
- [ ] Re-run audit after fixes
```
