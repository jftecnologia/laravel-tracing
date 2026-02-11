---
name: generate-test
description: Generate PestPHP tests for code. Use when the user asks to create tests, add tests, write tests, or when implement-task requires testing. This skill analyzes code to test, follows TESTING.md guidelines, generates appropriate test files, and ensures tests pass. Can be invoked standalone or as part of task implementation.
---

# Test Generator

Generate PestPHP tests following project testing guidelines. Analyze code, create appropriate tests, and ensure they pass.

---

## Related References

- [references/pest-patterns.md](references/pest-patterns.md) - Common PestPHP test patterns and examples
- `docs/engineering/TESTING.md` - Project testing guidelines (auto-loaded)

---

## 1. When to Use This Skill

### Invoked by implement-task

When a task definition includes `Testing: required`:

```markdown
**Testing**:

- [ ] Unit tests for TracingResolver
- [ ] Feature tests for middleware behavior
```

### Invoked Standalone

- "Crie testes para a classe TracingService"
- "Gere testes para o middleware"
- "Write tests for the correlation ID feature"
- "Adicione testes unitários para o resolver"

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

```
tests/Unit/<ClassName>Test.php
tests/Feature/<Feature>Test.php
```

Examples:

- `tests/Unit/TracingResolverTest.php`
- `tests/Feature/TracingMiddlewareTest.php`
- `tests/Feature/CorrelationIdPersistenceTest.php`

### Test Structure Template

```php
<?php

declare(strict_types=1);

use JuniorFontenele\LaravelTracing\ClassName;

// Group related tests with describe()
describe('ClassName', function () {

    // Setup if needed
    beforeEach(function () {
        // Common setup
    });

    // Test public behavior
    it('does something when condition', function () {
        // Arrange
        $input = 'test-value';

        // Act
        $result = (new ClassName())->method($input);

        // Assert
        expect($result)->toBe('expected');
    });

    // Edge cases
    it('handles null input gracefully', function () {
        expect(fn () => (new ClassName())->method(null))
            ->toThrow(InvalidArgumentException::class);
    });

});
```

### Naming Convention

Use descriptive names that explain behavior:

```php
// ✅ Good
it('generates unique correlation ID when none exists in session')
it('reuses existing correlation ID from request header')
it('attaches tracing headers to HTTP response')

// ❌ Bad
it('tests correlation ID')
it('works')
it('test 1')
```

---

## 5. Common Test Patterns

### Testing Return Values

```php
it('resolves correlation ID from header', function () {
    $resolver = new CorrelationIdResolver();

    $result = $resolver->resolve('X-Correlation-Id', 'test-123');

    expect($result)->toBe('test-123');
});
```

### Testing Exceptions

```php
it('throws exception for invalid header name', function () {
    $resolver = new CorrelationIdResolver();

    expect(fn () => $resolver->resolve('', 'value'))
        ->toThrow(InvalidArgumentException::class, 'Header name cannot be empty');
});
```

### Testing with Mocks

```php
use Illuminate\Support\Facades\Http;

it('forwards tracing headers on outgoing requests', function () {
    Http::fake();

    // Trigger outgoing request
    Http::get('https://api.example.com');

    Http::assertSent(fn ($request) =>
        $request->hasHeader('X-Correlation-Id')
    );
});
```

### Testing Middleware (Feature)

```php
use function Pest\Laravel\get;

it('attaches correlation ID header to response', function () {
    $response = get('/');

    $response->assertOk();
    $response->assertHeader('X-Correlation-Id');
});
```

### Testing Configuration

```php
it('disables tracing when configured', function () {
    config(['laravel-tracing.enabled' => false]);

    $response = get('/');

    $response->assertHeaderMissing('X-Correlation-Id');
});
```

### Testing with Datasets

```php
it('validates header names', function (string $header, bool $valid) {
    $resolver = new HeaderResolver();

    if ($valid) {
        expect($resolver->isValid($header))->toBeTrue();
    } else {
        expect($resolver->isValid($header))->toBeFalse();
    }
})->with([
    ['X-Correlation-Id', true],
    ['X-Request-Id', true],
    ['', false],
    ['Invalid Header!', false],
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

1. **Analyze failure** - Is test wrong or code wrong?
2. **If test is wrong** - Fix test logic
3. **If code is wrong** - Fix implementation (if invoked from implement-task)
4. **Re-run tests** until passing

### Step 4: Verify Coverage Intent

Check that tests cover:

- [ ] Happy path (valid inputs, expected behavior)
- [ ] Unhappy path (invalid inputs, error handling)
- [ ] Edge cases (null, empty, boundaries)
- [ ] Configuration variations (if applicable)

---

## 7. Integration with implement-task

When invoked from implement-task:

1. **Receive context**: Files created/modified, acceptance criteria
2. **Analyze implementation**: Read the code that was written
3. **Generate tests**: Based on code behavior
4. **Run tests**: Must pass before task completion
5. **Report**: Return test results to implement-task

### Communication Format

```
✅ Tests generated and passing:
- tests/Unit/TracingResolverTest.php (3 tests)
- tests/Feature/MiddlewareTest.php (2 tests)

All 5 tests pass.
```

Or if failing:

```
⚠️ Tests generated but failing:

FAILED  tests/Unit/TracingResolverTest.php
✗ it resolves correlation ID from header

Expected: 'test-123'
Received: null

Suggestion: Check that resolve() method returns the header value.
```

---

## 8. Standalone Usage

When called directly by user:

### Input Examples

- "Crie testes para `src/TracingService.php`"
- "Gere testes unitários para o resolver de correlation ID"
- "Write feature tests for the tracing middleware"
- "Adicione testes para a funcionalidade de session persistence"

### Output

1. Create test file(s)
2. Run tests
3. Report results
4. If failing, suggest fixes

---

## 9. Triggering Phrases

- "Crie testes para..."
- "Gere testes de..."
- "Write tests for..."
- "Add unit tests for..."
- "Add feature tests for..."
- "Teste a classe..."
- "Preciso de testes para..."

---

## 10. Language Rules

- **Test code**: English (function names, assertions, comments)
- **Test descriptions**: English (`it('does something')`)
- **Conversation**: Portuguese (pt-BR)

---

## Completion Criteria

Test generation is complete when:

1. ✅ Test file created with appropriate name/location
2. ✅ Tests follow TESTING.md guidelines
3. ✅ Tests use PestPHP syntax correctly
4. ✅ `composer test` passes
5. ✅ Tests cover intended behavior (not 100% coverage, meaningful coverage)
6. ✅ No debug code left (`dd()`, `dump()`)
