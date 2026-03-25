# Gate Checklist — Bug Fix

Quality gate validation steps for bug fixes. Same gates as feature development, with bugfix-specific guidance.

---

## Gate 1: Code Quality (Lint)

```bash
composer lint
npm run lint && npm run types
```

### If lint fails

1. Run `composer format` to auto-fix PHP formatting
2. Run `composer rector` to auto-fix deprecated patterns
3. Fix remaining PHPStan errors manually
4. Re-run `composer lint`
5. Commit: `style: fix lint issues`

---

## Gate 2: Tests

### Required Checks

1. **Run full test suite** — fix must not break existing tests:
   ```bash
   composer test
   ```

2. **If bug had a test gap** — write a regression test:
   - Test that reproduces the original bug
   - Test confirms the fix resolves it
   - Commit: `test(scope): add regression test for [bug description]`

3. **Invoke generate-test skill** if needed:
   > Gere um teste de regressão para o bug corrigido

### If tests fail

1. Analyze: is the test wrong or the fix incomplete?
2. Fix appropriately
3. Re-run until passing

---

## Gate 3: Security Analysis

Invoke **security-analyst** skill on modified files:

> Analise a segurança dos arquivos modificados neste bugfix

- **CRITICAL or HIGH** → Must fix before completing
- **MEDIUM/LOW** → Inform user, proceed with their decision

---

## Gate 4: i18n Verification

Scan modified files for hardcoded user-facing strings:

- **PHP files**: must use `__()`
- **TSX/React files**: must use `t()` from `useLaravelReactI18n`
- **Blade files**: must use `__()` or `@lang()`

### If hardcoded strings found

1. Replace with translation helpers
2. Add keys to locale files
3. Commit: `chore(i18n): add missing translations`
4. For full audit, invoke **i18n-manager** skill

---

## Gate Order

```text
1. composer lint              (fast, auto-fixes)
   npm run lint && npm run types
↓
2. composer test              (validates fix + no regressions)
↓
3. security-analyst           (validates security)
↓
4. i18n check                 (validates translations)
↓
✅ All gates pass → Fix can be completed
```

---

## Quick Reference

| Gate | Command/Skill | Blocking Criteria |
|------|---------------|-------------------|
| PHP Lint | `composer lint` | Any error |
| JS/TS Lint | `npm run lint && npm run types` | Any error |
| Tests | `composer test` | Any test failure |
| Regression test | generate-test skill | Required if test gap existed |
| Security | security-analyst skill | CRITICAL or HIGH findings |
| i18n | `grep_search` + manual review | Hardcoded user-facing strings |
