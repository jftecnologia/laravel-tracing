# Testing Guidelines

**Purpose**: Define testing philosophy, practices, and execution for this Laravel package.

---

## Testing Philosophy

### Core Principles

- **Tests are created ONLY when explicitly requested by the user**
- **When tests exist, they MUST pass before concluding any task**
- **Test business behavior, not framework internals**
- **Tests must be maintainable and readable**

---

## Testing Framework

### PestPHP

This package uses **PestPHP** (version 4.3+) as the testing framework.

- Modern, expressive PHP testing framework
- Built on top of PHPUnit
- Elegant syntax with less boilerplate
- Better readability and developer experience

### Orchestra Testbench

Tests run in **Orchestra Testbench** (version 10.8+):

- Provides a minimal Laravel environment for package testing
- Simulates a real Laravel application
- Allows testing service providers, facades, middleware, etc.

---

## Test Structure

```
tests/
├── Pest.php           # Pest configuration
├── TestCase.php       # Base test case class
├── Feature/           # Feature/integration tests
│   └── *.php
└── Unit/              # Unit tests
    └── *.php
```

### Feature vs Unit Tests

**Feature Tests** (`tests/Feature/`):
- Test complete features end-to-end
- May involve multiple classes
- Can use Laravel's HTTP testing (`get()`, `post()`, etc.)
- Example: Testing middleware behavior on HTTP requests

**Unit Tests** (`tests/Unit/`):
- Test individual classes/methods in isolation
- No HTTP/database dependencies (use mocks if needed)
- Fast and focused
- Example: Testing a resolver class logic

---

## Running Tests

### Execute All Tests

```bash
composer test
```

This runs the entire PestPHP test suite.

### Run Specific Test File

```bash
./vendor/bin/pest tests/Feature/ExampleTest.php
```

### Run Specific Test

```bash
./vendor/bin/pest --filter="test name"
```

---

## When to Run Tests

**Always run tests:**

- ✅ Before committing code
- ✅ Before opening a pull request
- ✅ After making significant changes
- ✅ Before concluding any task (mandatory rule)

**Even if you didn't write the tests**, you must ensure they still pass.

---

## Writing Tests (When Requested)

### PestPHP Syntax

```php
use function Pest\Laravel\get;

it('attaches correlation ID to response headers', function () {
    $response = get('/');
    
    $response->assertOk();
    $response->assertHeader('X-Correlation-Id');
});

it('persists correlation ID in session', function () {
    $response = get('/');
    
    expect(session()->has('correlation_id'))->toBeTrue();
});
```

### Test Naming

- Use descriptive test names that explain **what** is being tested
- Start with lowercase verb: `it('does something')`
- Be specific about the scenario

✅ **Good:**
```php
it('generates new correlation ID when none exists')
it('reuses correlation ID from session on subsequent requests')
it('attaches tracing headers to outgoing HTTP responses')
```

❌ **Bad:**
```php
it('tests correlation ID')
it('works correctly')
it('test case 1')
```

---

## What to Test

### ✅ DO Test

- **Business logic**: Core package functionality
- **Public APIs**: Facades, helper functions, public methods
- **Configuration behavior**: Features enabled/disabled via config
- **Edge cases**: Null values, missing headers, invalid input
- **Integration points**: Middleware, service providers, HTTP client integration
- **Custom tracing sources**: If extensibility is used

### ❌ DO NOT Test

- **Laravel framework internals**: Don't test Eloquent, routing, sessions, etc.
- **Third-party packages**: Assume dependencies work
- **Framework features**: Assume Laravel's HTTP client, queue system, etc. work
- **Trivial getters/setters**: No value in testing simple property access

---

## Test Organization

### Arrange-Act-Assert Pattern

```php
it('resolves correlation ID from request header', function () {
    // Arrange: Set up test data
    $correlationId = 'test-correlation-123';
    
    // Act: Perform the action
    $response = get('/', ['X-Correlation-Id' => $correlationId]);
    
    // Assert: Verify the result
    expect($response->header('X-Correlation-Id'))->toBe($correlationId);
});
```

### Use Datasets for Multiple Scenarios

```php
it('validates header names', function (string $header) {
    $response = get('/', [$header => 'test-value']);
    
    $response->assertHeader($header);
})->with([
    'X-Correlation-Id',
    'X-Request-Id',
    'X-Custom-Trace',
]);
```

---

## Mocking and Fakes

### When to Mock

- External HTTP calls (use `Http::fake()`)
- Queue jobs (use `Queue::fake()`)
- Time-dependent behavior (use `Carbon::setTestNow()`)

### Example: HTTP Client Mock

```php
use Illuminate\Support\Facades\Http;

it('forwards tracing headers on outgoing requests', function () {
    Http::fake();
    
    // Make outgoing request with tracing
    Http::withHeaders(['X-Correlation-Id' => 'test-123'])
        ->get('https://api.example.com/data');
    
    // Assert header was sent
    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Correlation-Id', 'test-123');
    });
});
```

---

## Test Isolation

### Each Test Must Be Independent

- Tests should not depend on execution order
- Clean up after each test (Pest does this automatically)
- Don't rely on shared state between tests

### Use `beforeEach()` for Setup

```php
beforeEach(function () {
    config(['laravel-tracing.enabled' => true]);
});

it('runs with tracing enabled', function () {
    // Test logic
});
```

---

## Testing Custom Tracing Sources

When users implement custom tracing sources:

```php
it('registers and resolves custom tracing source', function () {
    config([
        'laravel-tracing.sources.custom' => [
            'enabled' => true,
            'header' => 'X-User-Id',
            'resolver' => CustomUserIdResolver::class,
        ],
    ]);
    
    $response = get('/', ['X-User-Id' => 'user-123']);
    
    expect($response->header('X-User-Id'))->toBe('user-123');
});
```

---

## Performance Testing

### Not Required, But Consider

For critical paths, consider basic performance checks:

```php
it('resolves tracing values quickly', function () {
    $start = microtime(true);
    
    get('/');
    
    $duration = microtime(true) - $start;
    
    expect($duration)->toBeLessThan(0.1); // 100ms threshold
});
```

---

## Test Coverage

### Coverage is NOT a Goal

- **Do not aim for 100% coverage**
- **Test meaningful behavior**, not lines of code
- Focus on:
  - Critical business logic
  - Public APIs
  - Edge cases and error handling

---

## Debugging Tests

### Run Tests with Output

```bash
./vendor/bin/pest --verbose
```

### Debug Specific Test

```php
it('debugs correlation ID resolution', function () {
    $response = get('/');
    
    dump($response->headers->all()); // Debug output
    
    $response->assertHeader('X-Correlation-Id');
});
```

### Use `dd()` to Stop Execution

```php
it('investigates tracing context', function () {
    $response = get('/');
    
    dd(
        $response->headers->all(),
        session()->all(),
        config('laravel-tracing')
    );
});
```

---

## Common Testing Patterns

### Testing Middleware

```php
it('attaches tracing headers via middleware', function () {
    $response = get('/');
    
    $response->assertOk();
    $response->assertHeader('X-Correlation-Id');
    $response->assertHeader('X-Request-Id');
});
```

### Testing Facades

```php
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

it('retrieves correlation ID via facade', function () {
    get('/');
    
    $correlationId = LaravelTracing::getCorrelationId();
    
    expect($correlationId)->toBeString();
    expect($correlationId)->not->toBeEmpty();
});
```

### Testing Configuration

```php
it('respects enabled configuration', function () {
    config(['laravel-tracing.enabled' => false]);
    
    $response = get('/');
    
    $response->assertHeaderMissing('X-Correlation-Id');
});
```

### Testing Job Context

```php
use Illuminate\Support\Facades\Queue;

it('propagates tracing context to queued jobs', function () {
    Queue::fake();
    
    get('/dispatch-job');
    
    Queue::assertPushed(ExampleJob::class, function ($job) {
        return $job->tracingContext['correlation_id'] !== null;
    });
});
```

---

## Test Maintenance

### Keep Tests Updated

When changing features:

- ✅ Update affected tests
- ✅ Add tests for new behavior
- ✅ Remove tests for removed features

### Avoid Brittle Tests

- Don't test implementation details
- Test public behavior, not internal structure
- Use abstractions when testing external dependencies

---

## Available Test Scripts

Check `composer.json` for test-related commands:

```bash
composer test     # Run all tests
./vendor/bin/pest # Run Pest directly with options
```

---

## Pre-Commit Test Checklist

Before committing:

- [ ] All tests pass (`composer test`)
- [ ] New tests added (if requested)
- [ ] No `dd()`, `dump()`, or debug code left in tests
- [ ] Test names are descriptive
- [ ] Tests are isolated and repeatable

---

## Remember

**Tests are created ONLY when explicitly requested.**  
**When tests exist, they MUST pass before task completion.**
