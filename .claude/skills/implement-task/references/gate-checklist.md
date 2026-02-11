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

```
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

## Gate 2: Tests

### What It Runs

```bash
composer test
```

Executes PestPHP test suite including:
- Unit tests (`tests/Unit/`)
- Feature tests (`tests/Feature/`)

### Expected Output

```
PASS  Tests\Unit\ExampleTest
✓ it works

PASS  Tests\Feature\ExampleTest  
✓ it works

Tests:    2 passed
Duration: 0.15s
```

### When Tests Are Required

**Always write tests for:**
- New public API methods
- Business logic with conditions
- Data transformation functions
- Integration points

**Optional tests for:**
- Configuration classes
- Simple value objects
- Pass-through methods

### Test Structure

```php
// tests/Unit/TracingTest.php
test('generates unique correlation id', function () {
    $id1 = LaravelTracing::correlationId();
    $id2 = LaravelTracing::correlationId();
    
    expect($id1)->not->toBe($id2);
});
```

### Recovery

```bash
# Run tests with verbose output
composer test -- --verbose

# Run specific test file
./vendor/bin/pest tests/Unit/SpecificTest.php

# Run specific test
./vendor/bin/pest --filter="test name"
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

```
✅ Security Analysis: PASS

No security issues found in the analyzed code.
Task can be marked as DONE.
```

Or with warnings:

```
✅ Security Analysis: PASS WITH WARNINGS

No blocking issues found. The following recommendations are optional:

[SEC-003] 🟡 MEDIUM: Consider adding rate limiting
[SEC-004] 🔵 LOW: Verbose error message in catch block

Task can be marked as DONE. Do you want to address these recommendations first?
```

### Blocking Issues

```
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

## Gate Order

Execute gates in this order:

```
1. composer lint     (fast, auto-fixes available)
↓
2. composer test     (validates behavior)
↓
3. security-analyst  (validates security)
↓
✅ All gates pass → Task can be completed
```

### Rationale

- **Lint first**: Catches syntax/style issues that would affect other gates
- **Tests second**: Validates code actually works
- **Security last**: Analyzes working, clean code

---

## Quick Reference

| Gate | Command | Blocking Criteria |
|------|---------|-------------------|
| Lint | `composer lint` | Any error |
| Tests | `composer test` | Any test failure |
| Security | security-analyst | CRITICAL or HIGH findings |

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
