# Analysis Checklists

Detailed checklists for code-reviewer Phases 2–6. Load the relevant section when executing each phase.

---

## Phase 2 — Dead Code Analysis

### 2.1 Unused Classes

- [ ] Search each class in `app/` for imports/references across the codebase
- [ ] Check `config/app.php` providers array for unused service providers
- [ ] Check middleware registrations for unused middleware classes
- [ ] Cross-reference model classes against controller/action usage
- [ ] Identify abstract classes with no concrete implementations

```bash
# Example: find unreferenced classes
grep -rl "ClassName" app/ config/ routes/ --include="*.php" | wc -l
```

### 2.2 Unused Methods

- [ ] For each public method in services/actions, search for call sites
- [ ] Check for public methods only called from within the same class (should be private/protected)
- [ ] Identify interface methods not used by any consumer
- [ ] Check for overridden parent methods that add no behavior

### 2.3 Unused Routes

- [ ] Use `list-routes` MCP tool to get all registered routes
- [ ] Cross-reference route names with Wayfinder usage in frontend (`resources/js/`)
- [ ] Identify routes with no matching controller action
- [ ] Check for resource routes where only some actions are used

### 2.4 Unreachable Code

- [ ] Code after `return`, `throw`, `exit`, or `die` statements
- [ ] Conditions that can never be true (e.g., `if (false)`)
- [ ] Catch blocks for exceptions that can never be thrown
- [ ] Enum cases not handled in switch/match expressions

### 2.5 TODO/FIXME/HACK Inventory

- [ ] Search for `TODO`, `FIXME`, `HACK`, `XXX`, `TEMP`, `WORKAROUND`
- [ ] Record: file, line, marker type, content, age (via `git blame`)
- [ ] Classify: actionable vs. informational vs. stale

```bash
grep -rnI "TODO\|FIXME\|HACK\|XXX\|TEMP\|WORKAROUND" app/ --include="*.php"
```

### 2.6 Orphaned Config Values

- [ ] For each key in `config/*.php`, search for `config('key')` usage
- [ ] Check `.env.example` for variables not referenced in any config file
- [ ] Identify config files never loaded by application code

### 2.7 Unused Database Columns

- [ ] Cross-reference migration columns with model `$fillable`/`$casts`
- [ ] Check if fillable columns are actually used in queries, views, or API responses
- [ ] Identify columns in `$hidden` that are also never used internally
- [ ] Use `database-schema` MCP tool to get current schema

---

## Phase 3 — Code Quality, SOLID & Code Smells

### 3.1 Single Responsibility (S)

- [ ] Classes with more than 300 LOC — likely doing too much
- [ ] Classes with methods spanning multiple domains (e.g., a Service that handles both email and PDF)
- [ ] Controllers with business logic (should be in Actions/Services)
- [ ] Models with non-data methods beyond scopes, relationships, and accessors

### 3.2 Open/Closed (O)

- [ ] Switch/match on type that would need editing to add a new type
- [ ] Methods with growing if/elseif chains for new variants
- [ ] Missing strategy/policy pattern where extension would require modification

### 3.3 Liskov Substitution (L)

- [ ] Subclasses that throw exceptions for parent methods they don't support
- [ ] Overridden methods that change return type semantics
- [ ] Subclasses that tighten preconditions or loosen postconditions

### 3.4 Interface Segregation (I)

- [ ] Interfaces with more than 5 methods
- [ ] Classes implementing interfaces where they `return null` or throw for methods they don't need
- [ ] Single monolithic interface instead of role-based smaller interfaces

### 3.5 Dependency Inversion (D)

- [ ] `new ClassName()` in business logic (should use DI)
- [ ] Static method calls to concrete classes (instead of injected interfaces)
- [ ] Service Locator pattern (`app(ClassName::class)`) in places where constructor injection is possible

### 3.6 Overengineering

- [ ] Abstract classes with only one concrete implementation
- [ ] Interfaces implemented by only one class (unless needed for testing/mocking)
- [ ] Excessive layer depth (Controller → Service → Repository → Model) — project uses Actions, not Repositories
- [ ] Design patterns applied without actual need (e.g., Factory for single variant)

### 3.7 God Classes & Methods

- [ ] Methods with more than 50 LOC
- [ ] Classes with more than 15 public methods
- [ ] Methods with cyclomatic complexity > 10 (many branches/conditions)
- [ ] Methods with more than 5 parameters

### 3.8 Code Duplication

- [ ] Repeated logic blocks across controllers or actions
- [ ] Similar validation rules across multiple FormRequest classes
- [ ] Copy-pasted query builders with slight variations
- [ ] Duplicated frontend logic across React components

### 3.9 Naming & Conventions

- [ ] Inconsistent naming (e.g., some actions use `Execute`, others `Handle`, others `Run`)
- [ ] Controller methods not following Laravel conventions
- [ ] Variable names that don't describe their content
- [ ] Mixed casing styles within the same layer

### 3.10 Type Safety

- [ ] Missing parameter type hints on public methods
- [ ] Missing return type declarations
- [ ] Properties without type declarations (PHP 8.x)
- [ ] Union types that could be narrowed
- [ ] `mixed` type used where a specific type is possible

### 3.11 Code Smells

- [ ] **Feature Envy** — methods that use more data from another class than their own
- [ ] **Long Parameter List** — methods with more than 4 parameters
- [ ] **Shotgun Surgery** — a single logical change requires edits in many different classes
- [ ] **Data Clumps** — groups of data that repeatedly appear together without being encapsulated in a class/DTO
- [ ] **Primitive Obsession** — using primitives (strings, ints) where value objects or enums would be clearer
- [ ] **Inappropriate Intimacy** — classes that access each other's internal details
- [ ] **Message Chains** — long call chains (`$a->b()->c()->d()`)
- [ ] **Dead Stores** — variables assigned but never read
- [ ] **Speculative Generality** — abstractions created "for the future" with no current use

---

## Phase 4 — Configuration & Hardcoded Values

### 4.1 Magic Numbers & Strings

- [ ] Numeric literals in conditions, calculations, or array slicing
- [ ] String literals used as status values, role names, or identifiers
- [ ] Duration values (seconds, minutes) hardcoded in code
- [ ] Pagination limits, retry counts, timeout values

### 4.2 `env()` Outside Config

- [ ] Search for `env(` in `app/`, `routes/`, `resources/` directories
- [ ] All `env()` calls must be in `config/*.php` files only
- [ ] Application code must use `config()` helper

```bash
grep -rn "env(" app/ routes/ --include="*.php"
```

### 4.3 Hardcoded URLs & Paths

- [ ] API endpoint URLs in source code
- [ ] File system paths (should use `storage_path()`, `base_path()`, etc.)
- [ ] External service URLs not in config
- [ ] Frontend URLs not using Wayfinder

### 4.4 Credentials & Secrets

- [ ] API keys or tokens in source code
- [ ] Default passwords in factories or seeders meant for production
- [ ] Connection strings with credentials

### 4.5 Tenant-Configurable Values

- [ ] Values that should vary per tenant but are static in code
- [ ] Colors, labels, or branding hardcoded instead of using tenant settings
- [ ] Feature flags that should be per-tenant

### 4.6 Missing Config Files

- [ ] Behavior controlled by `if/else` in code that should be in config
- [ ] Queue names, connection names hardcoded
- [ ] Cache TTL values in application code

---

## Phase 5 — Spec Compliance & Business Rules

### 5.1 Logic Divergences

- [ ] Compare implemented behavior against `docs/PRD.md` requirements
- [ ] Check controller actions against acceptance criteria in `docs/tasks/`
- [ ] Verify enum values match spec-defined statuses/types

### 5.2 Conflicting Calculations

- [ ] Search for the same metric calculated in multiple places
- [ ] CVSS scores, risk ratings, severity levels — consistent algorithms?
- [ ] Date/time calculations — consistent timezone handling?
- [ ] Statistical aggregations — same formula everywhere?

### 5.3 Inconsistent Business Rules

- [ ] Same validation rules applied differently across FormRequests
- [ ] Permission checks with different conditions for the same action
- [ ] Status transitions allowed in some places but blocked in others

### 5.4 Missing Edge Cases

- [ ] What happens with empty collections?
- [ ] What happens with null foreign keys?
- [ ] Boundary conditions (max values, min values)
- [ ] What happens when tenant has no data?

### 5.5 Status Flow Violations

- [ ] Compare status transitions in code against spec-defined state machine
- [ ] Check for transitions that skip intermediate states
- [ ] Verify status-dependent visibility rules (e.g., clients NEVER see `draft`)

### 5.6 Permission Inconsistencies

- [ ] Cross-reference Policy methods with `docs/RBAC_GUIDE.md`
- [ ] Check for actions without corresponding policy checks
- [ ] Verify middleware role assignments against route definitions
- [ ] Check for hardcoded role checks instead of using Policies

---

## Phase 6 — Test Quality Analysis

### 6.1 Coverage Gaps

- [ ] List all controllers — check for matching Feature test files
- [ ] List all Actions — check for matching Unit test files
- [ ] List all Services — check for matching test files
- [ ] List all Policies — check for matching test files
- [ ] List all FormRequests — check for validation tests
- [ ] List all Models with custom methods — check for Unit tests

```bash
# Example: controllers without tests
for f in $(find app/Http/Controllers -name "*.php" -not -name "Controller.php"); do
  base=$(basename "$f" .php)
  grep -rl "$base" tests/ --include="*.php" > /dev/null || echo "MISSING: $base"
done
```

### 6.2 Path Completeness

For each test file, verify coverage of:

**Happy paths:**
- [ ] Core operation succeeds with valid data
- [ ] Correct response status (200, 201, 302)
- [ ] Database state is correct after operation
- [ ] Events/notifications dispatched as expected

**Unhappy paths:**
- [ ] Invalid input returns 422 with validation errors
- [ ] Missing required fields handled
- [ ] Non-existent resource returns 404
- [ ] Duplicate data handled (unique constraints)
- [ ] Business rule violations handled

**Security paths:**
- [ ] Unauthenticated access returns 401/302
- [ ] Unauthorized access returns 403
- [ ] Cross-tenant access blocked (user from Tenant A cannot access Tenant B data)
- [ ] Role-based access enforced (e.g., client cannot access admin routes)
- [ ] Read-only users blocked from write operations

### 6.3 Test Relevance

- [ ] Tests that only assert `assertTrue(true)` or `assertNotNull($var)`
- [ ] Tests that check internal implementation rather than observable behavior
- [ ] Tests tightly coupled to database IDs or timestamps
- [ ] Tests that mock everything — testing only the mock setup
- [ ] Overly long test methods that test multiple unrelated behaviors

### 6.4 Pattern Adherence

Check against `docs/engineering/TESTING.md`:

- [ ] Uses PestPHP syntax (`it()`, `test()`, `describe()`, `expect()`)
- [ ] Tests grouped with `describe()` blocks by feature/class
- [ ] Descriptive test names (`it('returns 403 when user has no access')`)
- [ ] Uses model factories instead of manual creation
- [ ] Uses `Event::fake()`, `Notification::fake()`, `Queue::fake()` appropriately
- [ ] Uses `RefreshDatabase` or `LazilyRefreshDatabase` trait
- [ ] Dataset usage for repetitive validations
- [ ] Uses `actingAs()` for authentication, not manual session setup

### 6.5 Consistency

- [ ] All test files follow the same structure (imports → setup → tests)
- [ ] Consistent use of Pest vs PHPUnit assertions (should be Pest)
- [ ] Consistent factory usage patterns across tests
- [ ] No hardcoded user IDs, emails, or other data (use factories)
- [ ] Consistent approach to testing HTTP responses (`assertOk()` vs `assertStatus(200)`)

### 6.6 Orphaned Tests

- [ ] Tests referencing classes that no longer exist
- [ ] Tests for routes that have been removed
- [ ] Tests for features that have been redesigned
- [ ] Test files in wrong directory structure

### 6.7 Missing Security Test Scenarios

- [ ] Every controller action with policy check should have a corresponding test for unauthorized access
- [ ] Multi-tenant operations should have cross-tenant isolation tests
- [ ] Every public-facing form should have CSRF test
- [ ] Sensitive data endpoints should have tests verifying `$hidden` attributes aren't exposed

### 6.8 Interface Test Coverage

- [ ] List all page components in `resources/js/pages/` — check for corresponding feature test with `assertInertia()`
- [ ] Verify feature tests assert that pages receive the expected props/data (not just render without error)
- [ ] For pages displaying lists: tests verify correct data is present in the response props
- [ ] For pages displaying detail views: tests verify the entity data is correctly passed
- [ ] Form pages have tests for successful submission (happy path) and validation error display
- [ ] Role-protected pages have tests verifying access per role
- [ ] Complex multi-step flows (wizards, multi-page CRUDs) have browser tests
- [ ] Browser tests include `assertNoJavaScriptErrors()`
- [ ] Critical pages (login, dashboard, main listings) have at least smoke-level tests

### 6.9 Business Logic Test Coverage

- [ ] For each calculation in Actions/Services (CVSS, risk scores, statistics), verify unit test with known expected values exists
- [ ] For each state machine (status transitions), verify tests cover valid transitions AND reject invalid ones
- [ ] For each business rule defined in the PRD, verify at least one test exercises it
- [ ] For each business validation rule in FormRequests (beyond generic Laravel rules), verify rejection tests exist
- [ ] Calculation/aggregation tests use datasets with multiple scenarios to ensure correctness across edge cases
