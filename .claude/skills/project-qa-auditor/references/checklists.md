# QA Audit Checklists

Detailed checklists for Phases 1–4. Read this file at the start of audit execution.

---

## Phase 1 — Static Analysis

### 1.1 Code Quality Gates

Run the following commands and record results:

```bash
composer lint                    # PHP: pint + rector + phpstan
npm run lint && npm run types    # JS/TS: ESLint + TypeScript
```

Record: pass/fail, error count, warning count.

### 1.2 PRD Conformance

Load `docs/PRD.md` and verify:

- [ ] All entities defined in PRD exist as Eloquent models
- [ ] All user roles from PRD are defined in role enums
- [ ] All CRUD operations specified in PRD have corresponding controller actions
- [ ] All workflows described in PRD have implementing code (actions/jobs)
- [ ] Notification events defined in PRD have corresponding notification classes
- [ ] Dashboard metrics defined in PRD have implementing queries
- [ ] Report types defined in PRD are implemented

### 1.3 Engineering Standards Conformance

Load `docs/engineering/CODE_STANDARDS.md` and verify:

- [ ] Actions are single-purpose (one public method)
- [ ] Services handle orchestration only (no direct DB queries)
- [ ] Form Requests used for all validation (no inline validation)
- [ ] Named routes used consistently
- [ ] Models use `$fillable` (never `$guarded = []`)
- [ ] Enums used instead of magic strings/numbers
- [ ] Database columns follow naming conventions (snake_case)
- [ ] Return types declared on all public methods
- [ ] No `dd()`, `dump()`, `ray()`, or `Log::debug()` left in code

### 1.4 Acceptance Criteria Validation

If a feature/fix specification document is provided:

- [ ] Load the specification document
- [ ] Extract all acceptance criteria
- [ ] For each criterion, identify implementing code
- [ ] Mark criterion as PASS (code exists and matches), PARTIAL (incomplete), or FAIL (missing)
- [ ] Calculate acceptance criteria coverage percentage

### 1.5 Testing Standards Conformance

Load `docs/engineering/TESTING.md` and verify:

- [ ] Feature tests exist for all controllers
- [ ] Unit tests exist for all actions
- [ ] Test factories exist for all models
- [ ] Tests use `RefreshDatabase` trait
- [ ] Tests follow naming convention: `it_does_something` or `test_does_something`
- [ ] No `markTestSkipped()` or `markTestIncomplete()` without justification

---

## Phase 2 — Architectural Validation

### 2.1 Multi-Tenancy

- [ ] All tenant-scoped models use `BelongsToTenant` trait or equivalent scope
- [ ] Tenant middleware applied on all `/admin` and `/app` routes
- [ ] No cross-tenant data leakage in queries (check `where` clauses)
- [ ] Tenant resolution works via subdomain or session
- [ ] Global admin routes are NOT tenant-scoped

### 2.2 Route Structure

Verify route organization:

```bash
# Use laravel-boost list-routes to get all routes
```

- [ ] Routes follow RESTful conventions
- [ ] All routes have names
- [ ] Route groups use appropriate middleware stacks
- [ ] No orphan routes (routes without controller methods)

### 2.3 Authorization (Policies)

- [ ] Every controller action checks authorization (`authorize()` or `$this->authorize()`)
- [ ] Policy classes exist for all models that require access control
- [ ] Policy methods match standard CRUD: `viewAny`, `view`, `create`, `update`, `delete`
- [ ] Policies enforce role-based restrictions per PRD
- [ ] Policies enforce tenant isolation

### 2.4 Queue Configuration

- [ ] Jobs specify a queue name (`$queue` property)
- [ ] Queue driver configured (not `sync` in production config)
- [ ] Failed job handling configured
- [ ] Long-running jobs have timeout settings
- [ ] Notification dispatch uses queue when appropriate

### 2.5 File/Directory Structure

Verify against `CLAUDE.md` structure:

- [ ] `app/Actions/` — single-purpose actions grouped by domain
- [ ] `app/Enums/` — PHP enums
- [ ] `app/Http/Requests/` — Form Requests for validation
- [ ] `app/Policies/` — Authorization policies
- [ ] `app/Extensions/` — Package customizations only
- [ ] No logic placed directly in controllers beyond delegation

---

## Phase 3 — Security

### 3.1 Sensitive Data

- [ ] Passwords hashed (never stored in plaintext)
- [ ] API keys/tokens encrypted at rest (`$casts` with `encrypted`)
- [ ] Sensitive fields excluded from `$visible` or included in `$hidden`
- [ ] Sensitive data not logged (check Log calls)
- [ ] PII fields identified and protected

### 3.2 Evidence/File Access

- [ ] Uploaded files stored in non-public disk (`local` or `s3`, NOT `public`)
- [ ] File download routes require authentication + authorization
- [ ] No direct public URLs to evidence files
- [ ] File upload validates type, size, and extension
- [ ] Filenames sanitized before storage

### 3.3 Security Headers

Check middleware or config for:

- [ ] `X-Content-Type-Options: nosniff`
- [ ] `X-Frame-Options: DENY` or `SAMEORIGIN`
- [ ] `X-XSS-Protection: 1; mode=block`
- [ ] `Strict-Transport-Security` (HSTS) in production
- [ ] `Content-Security-Policy` configured
- [ ] CORS properly restricted (not `*` in production)

### 3.4 Rate Limiting

- [ ] Login routes have rate limiting
- [ ] API routes have rate limiting
- [ ] Password reset routes have rate limiting
- [ ] Rate limit configuration exists in `RouteServiceProvider` or route middleware

### 3.5 CSRF & Session

- [ ] CSRF middleware active on web routes
- [ ] Session driver is not `file` in production config
- [ ] Session cookies marked `httpOnly` and `secure`
- [ ] Session lifetime configured appropriately

### 3.6 Auth & Access Control

- [ ] Two-factor authentication available for sensitive roles
- [ ] Password complexity rules enforced
- [ ] Account lockout after failed attempts
- [ ] Impersonation restricted to GlobalAdmin only
- [ ] Impersonation is logged/tracked

---

## Phase 4 — Automated Tests

### 4.1 Execute Test Suite

```bash
composer test
```

Record: total tests, passed, failed, errors, warnings, time.

### 4.2 Analyze Failures

For each failed test:

- [ ] Record test class and method name
- [ ] Record error message
- [ ] Classify as: regression, missing implementation, environment issue, or flaky test

### 4.3 Coverage Assessment

Estimate functional coverage:

- [ ] Count total controller actions
- [ ] Count controller actions with corresponding feature tests
- [ ] Count total actions (app/Actions)
- [ ] Count actions with corresponding unit tests
- [ ] Calculate coverage percentage: `(tested / total) * 100`
