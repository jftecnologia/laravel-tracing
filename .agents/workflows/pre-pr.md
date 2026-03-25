---
description: Run all quality checks before opening a Pull Request (lint, test, security, i18n)
---

# Pre-PR Quality Check

Run all quality gates in sequence before opening a Pull Request.
Invoke with `/pre-pr` or when preparing code for review.

---

## Steps

### 1. PHP Lint

// turbo

```bash
composer lint
```

If issues are found, fix and re-run until clean.

### 2. JS/TS Lint

// turbo

```bash
npm run lint && npm run types
```

If issues are found, fix and re-run until clean.

### 3. Test Suite

// turbo

```bash
composer test
```

All tests must pass. If failures exist, fix before proceeding.

### 4. i18n Verification

Scan modified files for hardcoded user-facing strings:

```bash
git diff --name-only HEAD main | head -50
```

For each modified PHP, TSX, or Blade file, check:

- **PHP**: user-facing strings use `__()`
- **TSX**: user-facing strings use `t()` from `useLaravelReactI18n`
- **Blade**: user-facing strings use `__()` or `@lang()`

If hardcoded strings found:

1. Replace with translation helpers
2. Add keys to primary locale file (and secondary locale file if applicable)
3. Commit: `chore(i18n): add missing translations`

### 5. Security Analysis

Invoke the **security-analyst** skill on modified files:

> Analise a segurança dos arquivos modificados neste PR

- **CRITICAL/HIGH** → Must fix before PR
- **MEDIUM/LOW** → Report to user, proceed with their decision

### 6. Summary Report

After all checks pass, report:

```text
✅ Pre-PR Check: PASS

- Lint (PHP): ✅
- Lint (JS/TS): ✅
- Tests: ✅ (X passed, 0 failed)
- i18n: ✅
- Security: ✅ [PASS / PASS WITH WARNINGS]

Ready to open Pull Request.
```

If any check failed:

```text
⚠️ Pre-PR Check: BLOCKED

- Lint (PHP): ✅
- Tests: ❌ (3 failures)
- i18n: ⚠️ (2 hardcoded strings)

Fix the issues above before opening a Pull Request.
```
