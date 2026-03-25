---
name: generate-test
description: "Generate PestPHP tests for code. Use when the user asks to create tests, add tests, write tests, or when implement-task requires testing. Covers unit, feature, browser, smoke, and architecture tests. Analyzes test necessity, follows TESTING.md guidelines, generates appropriate test files, and ensures tests pass. Can be invoked standalone or as part of task implementation (e.g., 'crie testes para X', 'adicione testes', 'write tests for Y', 'gere testes unitários')."
---

# Test Generator

Generate PestPHP tests following project testing guidelines. Analyze code, create appropriate tests, and ensure they pass.

---

## Related References

- [references/pest-patterns.md](references/pest-patterns.md) - Common PestPHP test patterns and examples
- `docs/engineering/TESTING.md` - Project testing guidelines (auto-loaded)
- Use `search-docs` with queries like `pest testing`, `pest browser`, `pest architecture` for up-to-date Pest 4 patterns

---

## 1. Test Necessity Analysis

Before generating tests, evaluate WHICH types of test are needed:

| Type | When Needed | Location |
| ---- | ----------- | -------- |
| **Unit** | Business logic in Actions, Services, Rules, custom classes | `tests/Unit/` |
| **Feature** | HTTP endpoints, middleware, policies, Inertia pages | `tests/Feature/` |
| **Browser** | Complex UI interactions, multi-step flows, JS-dependent behavior | `tests/Browser/` |
| **Architecture** | Only when explicitly requested | `tests/` |
| **Smoke** | Only when explicitly requested for bulk page validation | `tests/` |

**Default**: Generate Unit + Feature tests. Only add Browser/Arch/Smoke when explicitly requested or clearly needed.

---

## 2. When to Use This Skill

### Invoked by implement-task

When a task definition includes `Testing: required`:

```markdown
**Testing**:

- [ ] Unit tests for TracingResolver
- [ ] Feature tests for middleware behavior
```

### Invoked Standalone

- "Crie testes para a classe VulnerabilityService"
- "Gere testes para o ProjectController"
- "Write tests for the report generation feature"
- "Adicione testes unitários para o FindingExporter"

---

## 2. Inputs

### Required

- **Target**: Class, method, feature, or files to test

### Auto-loaded

- `docs/engineering/TESTING.md` - Testing philosophy and patterns

### Context (read when needed)

- Source code of class/feature to test
- Related configuration files
- Existing tests for patterns

---

## 3. Analysis Phase

### Step 1: Understand What to Test

Read the target code and identify:

1. **Public methods** - What behaviors are exposed?
2. **Input/Output** - What goes in, what comes out?
3. **Edge cases** - Null, empty, invalid inputs?
4. **Dependencies** - What needs mocking?
5. **Configuration** - Behavior changes with config?

### Step 2: Determine Test Type

| Code Type              | Test Type | Location         |
| ---------------------- | --------- | ---------------- |
| Service/Resolver class | Unit      | `tests/Unit/`    |
| Middleware             | Feature   | `tests/Feature/` |
| Facade method          | Feature   | `tests/Feature/` |
| Helper function        | Unit      | `tests/Unit/`    |
| HTTP endpoint          | Feature   | `tests/Feature/` |
| Job with context       | Feature   | `tests/Feature/` |

### Step 3: Check What NOT to Test

From TESTING.md, do NOT test:

- Laravel framework internals
- Third-party packages
- Trivial getters/setters
- Framework features (assume they work)

---

## 4. Test Generation

### File Naming Convention

```text
tests/Unit/<ClassName>Test.php
tests/Feature/<Feature>Test.php
```

Examples:

- `tests/Unit/Services/VulnerabilityServiceTest.php`
- `tests/Feature/Controllers/ProjectControllerTest.php`
- `tests/Feature/Reports/ReportGenerationTest.php`

### Test Structure Template

```php
<?php

declare(strict_types=1);

use App\Models\Vulnerability;
use App\Models\Project;
use App\Models\User;

// Group related tests with describe()
describe('VulnerabilityService', function () {

    // Setup if needed
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
    });

    // Test public behavior
    it('creates a vulnerability for a project', function () {
        // Arrange
        $data = ['title' => 'SQL Injection', 'severity' => 'critical'];

        // Act
        $vulnerability = app(VulnerabilityService::class)->create($this->project, $data);

        // Assert
        expect($vulnerability)->toBeInstanceOf(Vulnerability::class);
        expect($vulnerability->title)->toBe('SQL Injection');
    });

    // Edge cases
    it('throws exception for invalid severity', function () {
        expect(fn () => app(VulnerabilityService::class)->create($this->project, ['severity' => 'invalid']))
            ->toThrow(InvalidArgumentException::class);
    });

});
```

### Naming Convention

Use descriptive names that explain behavior:

```php
// ✅ Good
it('creates vulnerability with critical severity')
it('returns 403 when user has no access to project')
it('generates PDF report with all findings')

// ❌ Bad
it('tests vulnerability')
it('works')
it('test 1')
```

---

## 5. Common Test Patterns

### Testing Return Values

```php
it('calculates CVSS score for a vulnerability', function () {
    $vulnerability = Vulnerability::factory()->create(['cvss_vector' => 'AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H']);

    $score = app(CvssCalculator::class)->calculate($vulnerability);

    expect($score)->toBe(9.8);
});
```

### Testing Exceptions

```php
it('throws exception when project has no vulnerabilities for export', function () {
    $project = Project::factory()->create();

    expect(fn () => app(FindingExporter::class)->export($project))
        ->toThrow(NoFindingsException::class, 'No findings to export');
});
```

### Testing with Mocks

```php
use Illuminate\Support\Facades\Http;

it('fetches CVE details from external API', function () {
    Http::fake([
        'cve.circl.lu/*' => Http::response(['id' => 'CVE-2024-1234', 'summary' => 'Test CVE']),
    ]);

    $result = app(CveService::class)->fetch('CVE-2024-1234');

    expect($result['id'])->toBe('CVE-2024-1234');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'CVE-2024-1234'));
});
```

### Testing Middleware (Feature)

```php
use function Pest\Laravel\get;

it('returns vulnerability list for authenticated user', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    Vulnerability::factory(3)->for($project)->create();

    $response = actingAs($user)->get(route('projects.vulnerabilities.index', $project));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Vulnerabilities/Index')
        ->has('vulnerabilities.data', 3)
    );
});
```

### Testing Configuration

```php
it('marks vulnerability as resolved when closed', function () {
    config(['pentest.auto_resolve_on_close' => true]);

    $vulnerability = Vulnerability::factory()->create(['status' => 'open']);
    app(VulnerabilityService::class)->close($vulnerability);

    expect($vulnerability->fresh()->status)->toBe('resolved');
});
```

### Testing with Datasets

```php
it('validates severity levels', function (string $severity, bool $valid) {
    $validator = app(SeverityValidator::class);

    if ($valid) {
        expect($validator->isValid($severity))->toBeTrue();
    } else {
        expect($validator->isValid($severity))->toBeFalse();
    }
})->with([
    ['critical', true],
    ['high', true],
    ['medium', true],
    ['', false],
    ['invalid', false],
]);
```

See [references/pest-patterns.md](references/pest-patterns.md) for more patterns.

---

## 6. Execution Workflow

### Step 1: Generate Test File

Create test file with appropriate tests based on analysis.

```bash
# Commit test file
git add tests/
git commit -m "test(scope): add tests for [feature]"
```

### Step 2: Run Tests

```bash
composer test
```

### Step 3: If Tests Fail

1. **Analyze failure** — Is the code wrong or the test wrong?
2. **Default action**: Fix the **code**, not the test
3. **Exceptions** (justification required):
   - The test was genuinely wrong (testing incorrect behavior)
   - The logic was intentionally changed and the old test lost its meaning
4. In exception cases: **raise the issue before modifying the test**
5. **Re-run tests** until passing

> 🚫 **NEVER** weaken or delete existing tests to make broken code pass.

### Step 4: Three Pillar Verification

After tests pass, verify coverage of all three pillars from `docs/engineering/TESTING.md`:

- [ ] ✅ **Happy Path** — valid inputs, expected behavior
- [ ] ❌ **Unhappy Path** — validation errors, invalid data, invalid states, dependency failures
- [ ] 🔒 **Security Path** — tenant isolation, RBAC, IDOR, data leakage (per TESTING.md checklist)

> ⚠️ A test file that only covers the happy path is **incomplete**.

---

## 7. Integration with implement-task

When invoked from implement-task:

1. **Receive context**: Files created/modified, acceptance criteria
2. **Analyze implementation**: Read the code that was written
3. **Generate tests**: Based on code behavior
4. **Run tests**: Must pass before task completion
5. **Report**: Return test results to implement-task

### Red-Green Mode (Bug Fixes)

When invoked from `bug-fixer` or for a `fix/` branch, generate the test in **Red-Green mode**:

1. **Write the test BEFORE the fix** — the test asserts the expected (correct) behavior
2. The test MUST **fail** when run against the current (buggy) code
3. Commit the failing test: `test(<scope>): add failing test for <bug-description>`
4. After the fix is applied, re-run the test — it MUST **pass** without modification

> The test defines the contract. The fix satisfies it. Never modify the test to accommodate the fix.

### Communication Format

```text
✅ Tests generated and passing:
- tests/Unit/Services/VulnerabilityServiceTest.php (3 tests)
- tests/Feature/Controllers/ProjectControllerTest.php (2 tests)

All 5 tests pass.
```

Or if failing:

```text
⚠️ Tests generated but failing:

FAILED  tests/Unit/Services/VulnerabilityServiceTest.php
✗ it creates vulnerability with critical severity

Expected: 'critical'
Received: null

Suggestion: Check that create() method assigns the severity correctly.
```

---

## 8. Standalone Usage

When called directly by user:

### Input Examples

- "Crie testes para `app/Services/VulnerabilityService.php`"
- "Gere testes unitários para o cálculo de CVSS"
- "Write feature tests for the project controller"
- "Adicione testes para a funcionalidade de geração de relatórios"

### Output

1. Create test file(s)
2. Run tests
3. Report results
4. If failing, suggest fixes

---

---

## Completion Criteria

Test generation is complete when:

1. ✅ Test file created with appropriate name/location
2. ✅ Tests follow TESTING.md guidelines
3. ✅ Tests use PestPHP syntax correctly
4. ✅ Tests cover all three pillars: Happy Path, Unhappy Path, Security Path
5. ✅ `composer test` passes (no regressions)
6. ✅ No existing tests were weakened or deleted without justification
7. ✅ No debug code left (`dd()`, `dump()`)

---

## Advanced Test Types

### Browser Testing

Browser tests run in real browsers for full integration testing.
Only use when complex UI interactions, JS-dependent behavior, or multi-step flows are involved.

- Browser tests live in `tests/Browser/`
- Use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories
- Use `RefreshDatabase` for clean state per test
- Always include `assertNoJavaScriptErrors()` in browser tests

```php
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in');

    $page->assertSee('Sign In')
        ->assertNoJavaScriptErrors()
        ->click('Forgot Password?')
        ->fill('email', 'user@example.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!');

    Notification::assertSent(ResetPassword::class);
});
```

### Smoke Testing

Quickly validate multiple pages have no JavaScript errors:

```php
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
```

### Architecture Testing

Enforce code conventions with Pest's arch testing:

```php
arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtendNothing()
    ->toHaveSuffix('Controller');
```

---

## Assertion Best Practices

Use specific assertions instead of generic status checks:

| Use | Instead of |
| --- | ---------- |
| `assertSuccessful()` | `assertStatus(200)` |
| `assertNotFound()` | `assertStatus(404)` |
| `assertForbidden()` | `assertStatus(403)` |

---

## Common Pitfalls

- Not importing `use function Pest\Laravel\mock;` before using mock
- Using `assertStatus(200)` instead of `assertSuccessful()`
- Forgetting datasets for repetitive validation tests
- Deleting tests without approval
- Forgetting `assertNoJavaScriptErrors()` in browser tests
