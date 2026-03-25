# Gate Checklist Reference

Detailed validation steps for quality gates before task completion.

---

## Gate 1: Code Quality (Lint)

### What It Runs

```bash
composer lint
```

Executes three tools in sequence:

1. **Pint** (`composer format`) - PSR-12 code formatting
2. **Rector** (`composer rector`) - PHP automated refactoring
3. **PHPStan** (`composer analyze`) - Static analysis level 5

### Expected Output

```text
✓ Pint: No issues found (or auto-fixed)
✓ Rector: No issues found (or auto-fixed)  
✓ PHPStan: No errors [OK]
```

### Common Issues

| Issue | Solution |
|-------|----------|
| PSR-12 violations | Run `composer format` (auto-fixes) |
| Deprecated PHP patterns | Run `composer rector` (auto-fixes) |
| Type mismatches | Add/fix type hints |
| Undefined variables | Fix code logic |
| Missing return types | Add return type declarations |

### Recovery

```bash
# Auto-fix formatting issues
composer format

# Auto-fix deprecated patterns
composer rector

# Re-run full lint
composer lint
```

---

## Gate 2: Tests (MANDATORY)

> **Standard**: See `docs/engineering/TESTING.md` for full testing guidelines.

Tests are **always mandatory** when the task creates or modifies code.
The only exception is when the **user explicitly requests** to skip tests.

### Step 1: Regression Baseline

Run the full test suite BEFORE implementation:

```bash
composer test
```

Record the result. This is the baseline — every test that passes now MUST still pass after implementation.

### Step 2: Generate Tests

**Invoke generate-test skill:**

> Gere testes para os arquivos implementados nesta task

Or be specific:

> Crie testes unitários para src/TracingResolver.php

The generate-test skill will:
1. Analyze implemented code
2. Determine test type (Unit vs Feature)
3. Create test file(s) covering all three pillars
4. Run tests
5. Report pass/fail

### Step 3: Three Pillar Verification

After tests pass, verify coverage of all three pillars:

- [ ] ✅ **Happy Path** — expected behavior with valid inputs
- [ ] ❌ **Unhappy Path** — validation errors, invalid states, edge cases
- [ ] 🔒 **Security Path** — tenant isolation, RBAC, IDOR, data leakage

> A test file that only covers the happy path is **incomplete**.

### Step 4: Regression Check

Run the full test suite AFTER implementation:

```bash
composer test
```

Compare with the baseline. If previously passing tests now fail → task is **BLOCKED**.

### Step 5: Test Integrity

If an existing test fails after implementation:

1. **Default**: Fix the **code**, not the test
2. **Exceptions** (justification required, raise BEFORE modifying):
   - The test was genuinely wrong (testing incorrect behavior)
   - The logic was intentionally changed and the old test lost its meaning
3. Document the justification in the commit message

> 🚫 **NEVER** weaken or delete tests to make broken code pass.

### Recovery (Test Failures)

1. **Analyze failure output**:
   ```bash
   composer test -- --verbose
   ```

2. **Determine cause**:
   - Is the code wrong? → Fix implementation (default)
   - Is the test genuinely wrong? → Fix test with justification

3. **Run specific failing test**:
   ```bash
   ./vendor/bin/pest tests/Unit/SpecificTest.php --filter="test name"
   ```

4. **Re-run full suite** when fixed:
   ```bash
   composer test
   ```

### Test Commit

After tests pass:

```bash
git add tests/
git commit -m "test(scope): add tests for [feature]"
```

---

## Gate 3: Security Analysis

### How to Invoke

After implementing code, trigger security-analyst skill:

> Analise a segurança dos arquivos modificados nesta task

Or list specific files:

> Analyze security for src/TracingMiddleware.php, src/TracingService.php

### What It Checks

OWASP Top 10 categories:
- A01: Broken Access Control
- A02: Cryptographic Failures
- A03: Injection (SQL, XSS, Command)
- A04: Insecure Design
- A05: Security Misconfiguration
- A06: Vulnerable Components
- A07: Authentication Failures
- A08: Integrity Failures
- A09: Logging Failures
- A10: SSRF

### Expected Output

```text
✅ Security Analysis: PASS

No security issues found in the analyzed code.
Task can be marked as DONE.
```

Or with warnings:

```text
✅ Security Analysis: PASS WITH WARNINGS

No blocking issues found. The following recommendations are optional:

[SEC-003] 🟡 MEDIUM: Consider adding rate limiting
[SEC-004] 🔵 LOW: Verbose error message in catch block

Task can be marked as DONE. Do you want to address these recommendations first?
```

### Blocking Issues

```text
⚠️ Security Analysis: BLOCKING ISSUES FOUND

Task cannot be marked as DONE until the following issues are resolved:

[SEC-001] 🔴 CRITICAL: SQL Injection in UserController
[SEC-002] 🟠 HIGH: Missing authorization check

Do you want me to show detailed fix recommendations?
```

### Recovery

1. Request fix details: "Mostre os fixes recomendados"
2. Implement fixes
3. Commit: `fix(security): [description]`
4. Re-run security analysis

---

## Gate 4: i18n Verification

### What to Check

Scan created/modified files for hardcoded user-facing strings:

| File Type | Required Helper | Example |
|-----------|----------------|---------|
| PHP (controllers, actions) | `__('key')` | `__('projects.created_successfully')` |
| TSX / React | `t('key')` via `useLaravelReactI18n` | `{t('projects.title')}` |
| Blade | `__('key')` or `@lang('key')` | `{{ __('common.save') }}` |

### How to Check

Use `grep_search` on modified files to find patterns like:
- Strings in PHP `return`, `flash()`, `message`, `title`, `label` — must be wrapped in `__()`
- JSX text content and prop values — must use `t()`
- Blade text content — must use `__()` or `@lang()`

Exclude: variable names, log messages, exception messages, comments, route names, config keys.

### Expected Output

```text
✅ i18n Verification: PASS

No hardcoded user-facing strings found.
```

Or with issues:

```text
⚠️ i18n Verification: ISSUES FOUND

Hardcoded strings found in:
- src/Http/Controllers/ProjectController.php:45 — "Project created successfully"
- resources/js/pages/Projects/Index.tsx:12 — "No projects found"

Fix these before proceeding.
```

### Recovery

1. Replace hardcoded strings with translation helpers
2. Add translation keys to primary locale file
3. Add translation keys to secondary locale file (if applicable)
4. Commit: `chore(i18n): add missing translations`
5. For full audit, invoke **i18n-manager** skill

---

## Gate Order

Execute gates in this order:

```text
1. composer lint     (fast, auto-fixes available)
↓
2. composer test     (validates behavior)
↓
3. security-analyst  (validates security)
↓
4. i18n check        (validates translations)
↓
✅ All gates pass → Task can be completed
```

### Rationale

- **Lint first**: Catches syntax/style issues that would affect other gates
- **Tests second**: Validates code actually works (invoke generate-test if required)
- **Security third**: Analyzes working, clean code
- **i18n last**: Non-blocking scan, quick to fix

---

## Quick Reference

| Gate | Command/Skill | Blocking Criteria |
|------|---------------|-------------------|
| Lint | `composer lint` | Any error |
| Tests (required) | generate-test skill | Any test failure |
| Tests (existing) | `composer test` | Any test failure |
| Security | security-analyst skill | CRITICAL or HIGH findings |
| i18n | `grep_search` + manual review | Hardcoded user-facing strings |

---

## Bypassing Gates (Emergency Only)

In exceptional cases (hotfix, urgent production issue):

1. Document the bypass in commit message
2. Create follow-up task to address skipped gate
3. Get explicit user approval

```bash
git commit -m "fix(critical): emergency hotfix

GATES BYPASSED: security-analyst (will address in HOTFIX-01)
Reason: Production outage requiring immediate fix"
```

**Never bypass gates without user approval.**
