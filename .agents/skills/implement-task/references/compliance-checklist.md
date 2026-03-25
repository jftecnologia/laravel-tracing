# Compliance Checklist

Unified post-implementation checklist for all cross-cutting quality concerns.
Referenced by `implement-task` and `bug-fixer` gate checklists.

---

## Gate 1: Code Quality (Lint)

- [ ] `composer lint` passes (Pint + Rector + PHPStan)
- [ ] `npm run lint && npm run types` passes (ESLint + TypeScript)

---

## Gate 2: Tests (3 Pillars)

- [ ] `composer test` passes — no regressions
- [ ] ✅ **Happy Path** covered — expected behavior with valid inputs
- [ ] ❌ **Unhappy Path** covered — validation errors, invalid states, edge cases
- [ ] 🔒 **Security Path** covered — tenant isolation, RBAC, IDOR, data leakage

---

## Gate 3: Security (OWASP)

- [ ] `security-analyst` skill invoked on created/modified files
- [ ] No 🔴 CRITICAL findings
- [ ] No 🟠 HIGH findings
- [ ] 🟡 MEDIUM / 🔵 LOW / ⚪ INFO — informed user, proceeded with their decision

---

## Gate 4: i18n Verification

- [ ] No hardcoded user-facing strings in PHP files (must use `__()`)
- [ ] No hardcoded user-facing strings in TSX/React files (must use `t()` from `useLaravelReactI18n`)
- [ ] No hardcoded user-facing strings in Blade files (must use `__()` or `@lang()`)
- [ ] New translation keys added to primary locale file
- [ ] New translation keys added to secondary locale file (if applicable)

---

## Post-Gate: Documentation

- [ ] Affected `docs/architecture/*.md` updated (if architecture changed)
- [ ] Affected `docs/design/*.md` updated (if UI changed)
- [ ] `docs/RBAC_GUIDE.md` updated (if roles/permissions changed)
- [ ] `docs/architecture/CONFIGURATION.md` updated (if config added)
- [ ] Progress files updated (`docs/progress/*.md`)

---

## Quick Reference

| Gate | Tool/Skill | Blocking Criteria |
|------|-----------|-------------------|
| Lint (PHP) | `composer lint` | Any error |
| Lint (JS/TS) | `npm run lint && npm run types` | Any error |
| Tests | `composer test` + `generate-test` | Any failure, missing pillar |
| Security | `security-analyst` skill | CRITICAL or HIGH findings |
| i18n | `grep_search` + manual review | Hardcoded user-facing strings |
