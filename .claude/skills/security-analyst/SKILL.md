---
name: security-analyst
description: Analyze code for security vulnerabilities based on OWASP Top 10. Use when the user asks to review security, check for vulnerabilities, analyze code security, or before completing development tasks. This skill identifies security issues, rates their severity, and provides fix recommendations without making automatic changes.
---

# Security Analyst

Analyze code for security vulnerabilities based on OWASP Top 10 and Laravel-specific security concerns. Report findings with severity levels and fix recommendations.

---

## Related References

For detailed vulnerability patterns and code examples, read:

- [references/laravel-vulnerabilities.md](references/laravel-vulnerabilities.md) - Laravel-specific vulnerability patterns with secure/insecure code examples

---

## 1. When to Use This Skill

- Before marking a development task as `DONE`
- When user explicitly requests security review
- After implementing authentication, authorization, or data handling features
- When handling user input, database queries, or external API calls
- Before opening a Pull Request

---

## 2. Analysis Scope

### OWASP Top 10 (2021)

| ID  | Category                  | What to Look For                                         |
| --- | ------------------------- | -------------------------------------------------------- |
| A01 | Broken Access Control     | Missing authorization checks, IDOR, privilege escalation |
| A02 | Cryptographic Failures    | Weak encryption, exposed secrets, insecure random        |
| A03 | Injection                 | SQL injection, XSS, command injection, LDAP injection    |
| A04 | Insecure Design           | Missing security controls, business logic flaws          |
| A05 | Security Misconfiguration | Debug mode, default credentials, verbose errors          |
| A06 | Vulnerable Components     | Outdated dependencies with known CVEs                    |
| A07 | Auth Failures             | Weak passwords, session fixation, brute force            |
| A08 | Integrity Failures        | Insecure deserialization, unsigned updates               |
| A09 | Logging Failures          | Missing audit logs, sensitive data in logs               |
| A10 | SSRF                      | Unvalidated URLs, internal network access                |

### Laravel-Specific Concerns

- Mass assignment vulnerabilities (`$fillable` / `$guarded`)
- Blade XSS (unescaped `{!! !!}` output)
- CSRF token validation
- Authentication middleware usage
- Policy and Gate authorization
- Eloquent query injection (raw queries, `whereRaw`)
- File upload validation
- Session security configuration
- Environment variable exposure

---

## 3. Analysis Process

### Step 1: Identify Changed Files

Determine which files need security review:

```bash
# If working on a specific task, check files listed in task definition
# If general review, check recently modified files
git diff --name-only HEAD~5
```

Focus on files that handle:

- User input (controllers, requests, middleware)
- Database operations (models, repositories, queries)
- Authentication/authorization (guards, policies, gates)
- External communication (HTTP clients, APIs)
- File operations (uploads, downloads, storage)
- Configuration (config files, environment)

### Step 2: Static Analysis

For each file, check for vulnerability patterns documented in [references/laravel-vulnerabilities.md](references/laravel-vulnerabilities.md).

**Key areas to analyze:**

- **Input Handling**: Unvalidated request input
- **SQL Injection**: Raw queries with user input
- **XSS**: Unescaped Blade output with `{!! !!}`
- **Mass Assignment**: Models without `$fillable` or `$guarded`
- **Authorization**: Missing `authorize()` or policy checks
- **Command Injection**: Unescaped shell commands
- **File Uploads**: Missing type/size validation
- **SSRF**: Unvalidated external URLs

### Step 3: Classify Findings

Rate each finding by severity:

| Severity        | Description                                   | Action                            |
| --------------- | --------------------------------------------- | --------------------------------- |
| 🔴 **CRITICAL** | Exploitable vulnerability, immediate risk     | Must fix before task completion   |
| 🟠 **HIGH**     | Significant vulnerability, likely exploitable | Should fix before task completion |
| 🟡 **MEDIUM**   | Potential vulnerability, context-dependent    | Recommend fixing                  |
| 🔵 **LOW**      | Minor issue, defense in depth                 | Consider fixing                   |
| ⚪ **INFO**     | Best practice recommendation                  | Optional improvement              |

---

## 4. Report Format

Generate a security report with this structure:

````markdown
# Security Analysis Report

**Analyzed**: [Date and time]
**Scope**: [Files or task reviewed]
**Result**: [PASS / PASS WITH WARNINGS / FAIL]

---

## Summary

| Severity    | Count |
| ----------- | ----- |
| 🔴 Critical | 0     |
| 🟠 High     | 1     |
| 🟡 Medium   | 2     |
| 🔵 Low      | 0     |
| ⚪ Info     | 1     |

**Blocking Issues**: [Yes/No - Critical or High findings block task completion]

---

## Findings

### [SEC-001] 🟠 HIGH: SQL Injection in UserController

**Location**: `src/Controllers/UserController.php:45`
**Category**: A03 - Injection
**Description**: User input is concatenated directly into SQL query without sanitization.

**Vulnerable Code**:

```php
$users = DB::select("SELECT * FROM users WHERE name LIKE '%" . $request->search . "%'");
```
````

**Recommended Fix**:

```php
$users = DB::select("SELECT * FROM users WHERE name LIKE ?", ['%' . $request->search . '%']);
```

**References**:

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [Laravel Database Security](https://laravel.com/docs/queries#raw-expressions)

---

### [SEC-002] 🟡 MEDIUM: Missing CSRF Protection

[Continue for each finding...]

---

## Recommendations

1. [Prioritized list of actions]
2. [...]

---

## Next Steps

- [ ] Fix all CRITICAL and HIGH issues
- [ ] Review and address MEDIUM issues
- [ ] Consider LOW and INFO recommendations
- [ ] Re-run security analysis after fixes

```

---

## 5. Integration with Development Workflow

### Before Task Completion

When a development task is about to be marked as `DONE`:

1. **Run security analysis** on files created/modified by the task
2. **Check for blocking issues** (Critical or High severity)
3. **Report findings** to user

**If CRITICAL or HIGH findings exist:**
```

⚠️ Security Analysis: BLOCKING ISSUES FOUND

Task cannot be marked as DONE until the following issues are resolved:

[SEC-001] 🔴 CRITICAL: SQL Injection in TracingMiddleware
[SEC-002] 🟠 HIGH: Missing authorization check

Do you want me to show detailed fix recommendations?

```

**If only MEDIUM/LOW/INFO findings:**
```

✅ Security Analysis: PASS WITH WARNINGS

No blocking issues found. The following recommendations are optional:

[SEC-003] 🟡 MEDIUM: Consider adding rate limiting
[SEC-004] 🔵 LOW: Verbose error message in catch block

Task can be marked as DONE. Do you want to address these recommendations first?

```

**If no findings:**
```

✅ Security Analysis: PASS

No security issues found in the analyzed code.
Task can be marked as DONE.

```

### Relationship with Code Quality

| Concern | Handled By | Examples |
|---------|------------|----------|
| Code formatting | `composer format` (Pint) | PSR-12, indentation |
| Static analysis | `composer analyze` (PHPStan) | Type errors, undefined variables |
| Code style | `composer lint` | Combined quality checks |
| **Security** | **security-analyst skill** | OWASP Top 10, Laravel security |

Security analysis is **separate** from code quality but can be run together before task completion.

---

## 6. Action Policy

### What This Skill Does

✅ **Analyzes** code for security vulnerabilities
✅ **Reports** findings with severity and location
✅ **Recommends** specific fixes with code examples
✅ **Blocks** task completion for critical issues
✅ **Educates** by linking to security resources

### What This Skill Does NOT Do

❌ **Does NOT automatically modify code**
❌ **Does NOT make security decisions for the user**
❌ **Does NOT replace manual security review**
❌ **Does NOT guarantee code is 100% secure**

### Why No Automatic Fixes?

1. **Context matters**: A finding might be a false positive in specific contexts
2. **Business logic**: Security fixes may affect intended functionality
3. **User ownership**: Developer should understand and validate fixes
4. **Auditability**: Changes should be intentional and traceable

### After Reporting

The user can:
1. **Ask for fix implementation**: "Implemente o fix para SEC-001"
2. **Dismiss finding**: "Ignore SEC-002, é um false positive porque..."
3. **Request more details**: "Explique melhor o SEC-003"
4. **Proceed anyway**: "Prossiga mesmo com os warnings"

---

## 7. Common Laravel Security Patterns

For detailed secure coding patterns with code examples, see [references/laravel-vulnerabilities.md](references/laravel-vulnerabilities.md).

**Quick Summary:**
- Use FormRequest classes for input validation
- Use Policy classes for authorization
- Use Eloquent or parameterized queries (never concatenate user input)
- Use `{{ }}` for output (never `{!! !!}` with user input)
- Use `@json()` for JavaScript data serialization

---

## 8. Checklist for Manual Review

Quick checklist for security review:

### Input/Output

- [ ] All user input is validated
- [ ] Output is properly escaped
- [ ] File uploads are validated (type, size, name)
- [ ] JSON responses don't leak sensitive data

### Authentication/Authorization

- [ ] Routes have appropriate middleware
- [ ] Actions check user permissions
- [ ] Sensitive operations require re-authentication
- [ ] Session is properly managed

### Database

- [ ] No raw SQL with user input
- [ ] Mass assignment is protected
- [ ] Sensitive data is encrypted
- [ ] Queries are parameterized

### Configuration

- [ ] Debug mode is off in production
- [ ] Error messages don't leak info
- [ ] HTTPS is enforced
- [ ] CORS is properly configured

### Dependencies

- [ ] No known vulnerable packages
- [ ] Dependencies are up to date
- [ ] Unused dependencies removed

---

## 9. Triggering Security Analysis

Use these phrases to trigger analysis:

- "Analise a segurança do código"
- "Faça uma revisão de segurança"
- "Verifique vulnerabilidades"
- "Security review antes de finalizar"
- "Check OWASP Top 10"

---

## 10. Language Rules

- **Security reports**: English (technical terms, code examples)
- **Conversation with user**: Portuguese (pt-BR)
- **Finding IDs**: SEC-XXX format (e.g., SEC-001, SEC-002)

---

## Completion Criteria

Security analysis is complete when:

1. ✅ All relevant files have been reviewed
2. ✅ Findings are classified by severity
3. ✅ Each finding has location, description, and fix recommendation
4. ✅ Report is generated with summary
5. ✅ User is informed of blocking issues (if any)
6. ✅ User can make informed decision about proceeding

---

## References

- [OWASP Top 10 (2021)](https://owasp.org/Top10/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)
```
