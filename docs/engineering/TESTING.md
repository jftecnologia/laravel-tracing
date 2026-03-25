# Testing Guidelines

**Purpose**: Define testing philosophy, practices, and execution for this Laravel application.

---

## Testing Philosophy

- **Every change must be tested** — write or update tests, then run them
- **Test business behavior**, not framework internals
- **Tests must pass before task completion**
- **Use PestPHP syntax exclusively**
- **Prefer tests over verification scripts** — do not create tinker scripts or verification files when tests cover the functionality
- **Bug fixes follow Red-Green TDD** — write a failing test first (🔴 RED), then fix the code until the test passes (🟢 GREEN), without modifying the test

---

## Testing Framework

- **PestPHP 4** — expressive, modern PHP testing
- **Pest Plugin Laravel** — Laravel-specific assertions and helpers
- **Faker** — test data generation
- **Mockery** — mocking library

---

## Test Structure

```text
tests/
├── Pest.php           # Pest configuration and shared setup
├── TestCase.php       # Base test case class
├── Feature/           # Feature/integration tests (most tests)
└── Unit/              # Isolated unit tests
```

### Feature vs Unit

- **Feature tests** (`tests/Feature/`): test complete features through HTTP, database, queue, etc. Most tests should be feature tests.
- **Unit tests** (`tests/Unit/`): test individual classes/methods in isolation, no Laravel bootstrapping needed.

---

## Running Tests

```bash
# Run all tests
composer test

# Run with compact output
php artisan test --compact

# Run specific file
php artisan test --compact tests/Feature/Auth/LoginTest.php

# Run specific test by name
php artisan test --compact --filter="can login with valid credentials"
```

---

## Creating Tests

Use artisan to create tests:

```bash
# Feature test (default)
php artisan make:test --pest Auth/LoginTest

# Unit test
php artisan make:test --pest --unit Services/PaymentServiceTest
```

Or use the **`/generate-test`** skill for AI-assisted test generation.

---

## Writing Tests

### Naming

Use descriptive `it()` blocks that explain **what** is being tested:

```php
// Good
it('redirects to dashboard after successful login')
it('returns validation error when email is missing')
it('sends notification when order is placed')

// Bad
it('tests login')
it('works correctly')
```

### Arrange-Act-Assert

```php
it('creates a new user with valid data', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    // Act
    $response = post('/register', $data);

    // Assert
    $response->assertRedirect('/dashboard');
    assertDatabaseHas('users', ['email' => 'john@example.com']);
});
```

### Using Factories

Always use factories to create test models — check factory states before manually setting attributes:

```php
it('shows user profile', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Profile')
            ->has('user')
        );
});
```

### Datasets for Multiple Scenarios

```php
it('validates required fields', function (string $field) {
    $data = User::factory()->make()->toArray();
    unset($data[$field]);

    post('/register', $data)
        ->assertSessionHasErrors($field);
})->with(['name', 'email', 'password']);
```

### Faker

Follow existing conventions — use either `$this->faker` or `fake()`:

```php
it('stores a post', function () {
    $user = User::factory()->create();

    actingAs($user)->post('/posts', [
        'title' => fake()->sentence(),
        'body' => fake()->paragraphs(3, true),
    ])->assertRedirect();
});
```

---

## What to Test

### Test

- HTTP endpoints (status codes, redirects, response structure)
- Validation rules (required fields, formats, custom rules)
- Authorization (policies, gates, middleware)
- Business logic in Actions and Services
- Model relationships and scopes
- Queue jobs and their effects
- Events and listeners
- Notifications
- Inertia page rendering and props

### Don't Test

- Laravel framework internals (Eloquent, routing engine, etc.)
- Third-party package behavior
- Trivial getters/setters
- Private methods directly — test through public API

### Mandatory Test Coverage: Three Pillars

Every feature/endpoint MUST cover **all three pillars**:

1. ✅ **Happy Path** — Expected behavior with valid inputs
2. ❌ **Unhappy Path** — Failures, validation, edge cases, invalid states
3. 🔒 **Security Path** — Scope, permissions, IDOR, data leakage

> **Rule**: A test file that only covers the happy path is **incomplete**.

#### Happy Path

Test the expected behavior when everything is valid:

```php
it('logs in with valid credentials', function () {
    $user = User::factory()->create();

    post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');
});
```

#### Unhappy Path

Test failures across these categories:

| Category                 | What to test                                                             |
| ------------------------ | ------------------------------------------------------------------------ |
| **Validation**           | Required fields, invalid formats, custom rules                           |
| **Invalid data**         | Empty strings, null, boundary values, special characters                 |
| **Invalid state**        | Forbidden state transitions (e.g., cannot approve a Draft vulnerability) |
| **Dependency failure**   | External service unavailable, timeout, bad response                      |
| **Authorization denied** | User without permission receives 403/404                                 |

```php
it('fails login with wrong password', function () {
    $user = User::factory()->create();

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors();
});

it('rejects empty email', function () {
    post('/login', [
        'email' => '',
        'password' => 'password',
    ])->assertSessionHasErrors('email');
});
```

#### Security Path

Every endpoint/feature MUST include security tests from the checklist below.

---

### Security Testing Checklist

For each endpoint or feature, evaluate and test the applicable categories:

| Category                     | What to test                                                        | Expected behavior                                                             |
| ---------------------------- | ------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| **Scope / Tenant Isolation** | Access data from another tenant                                     | Returns 0 results or 404 (NEVER 403)                                          |
| **Permissions (RBAC)**       | Each role has correct access; unpermitted roles are blocked         | 403 or 404 depending on context                                               |
| **IDOR**                     | Access a resource by ID that belongs to another user/company/tenant | 404 (not 403, to prevent enumeration)                                         |
| **Data Leakage**             | Response must not contain internal/sensitive fields                 | Pre-validation statuses hidden from clients; no password hashes, tokens, etc. |
| **Returned Data**            | Response contains ONLY the expected fields for that role/view       | Validate Inertia props; ensure no extra fields                                |
| **Mass Assignment**          | POST/PUT with non-fillable fields                                   | Non-fillable fields are ignored (e.g., `tenant_id` in body)                   |

> **Reference**: See `docs/architecture/SECURITY.md` for full security architecture.

---

### Security Testing Patterns

Use these patterns as templates for your security tests.

#### Pattern 1: Scope Isolation (e.g., Multi-Tenant)

```php
it('does not return data from another organization', function () {
    $otherOrg = Organization::factory()->create();
    $otherTeam = Team::factory()->create(['organization_id' => $otherOrg->id]);

    Post::factory()->create([
        'organization_id' => $otherOrg->id,
        'team_id' => $otherTeam->id,
    ]);

    actingAs($this->user)
        ->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('posts.total', 0)
        );
});
```

#### Pattern 2: IDOR Prevention

```php
it('returns 404 when accessing another team resource by ID', function () {
    $otherTeam = Team::factory()->create();
    $otherPost = Post::factory()->create([
        'team_id' => $otherTeam->id,
    ]);

    actingAs($this->user)
        ->get(route('posts.show', $otherPost))
        ->assertNotFound(); // 404, NOT 403
});
```

#### Pattern 3: Permission Boundary

```php
it('denies access to admin routes for member role', function () {
    $member = User::factory()->create();
    $organization->users()->attach($member->id, ['role' => 'member']);

    actingAs($member)
        ->get(route('admin.posts.index'))
        ->assertForbidden(); // or assertNotFound based on route middleware
});
```

#### Pattern 4: Data Leakage Prevention

```php
it('excludes draft statuses from public listing', function () {
    Post::factory()->create([
        'status' => PostStatus::Draft,
        'team_id' => $this->team->id,
    ]);

    $visible = Post::factory()->create([
        'status' => PostStatus::Published,
        'team_id' => $this->team->id,
    ]);

    actingAs($this->user)
        ->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('posts.total', 1)
            ->where('posts.data.0.id', $visible->id)
        );
});
```

---

## Inertia Testing

Test Inertia responses using the `assertInertia` method:

```php
it('renders the settings page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/settings/profile')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Profile')
            ->has('user')
            ->where('user.email', $user->email)
        );
});
```

---

## Mocking and Fakes

Use Laravel's built-in fakes:

```php
// HTTP
Http::fake(['api.example.com/*' => Http::response(['data' => 'test'])]);

// Queue
Queue::fake();
// ... action ...
Queue::assertPushed(SendNotification::class);

// Notifications
Notification::fake();
// ... action ...
Notification::assertSentTo($user, OrderConfirmation::class);

// Time
$this->travel(5)->minutes();
// or
Carbon::setTestNow(now()->addHour());

// Events
Event::fake();
// ... action ...
Event::assertDispatched(OrderPlaced::class);
```

---

## Test Isolation

- Each test must be independent — no shared state between tests
- Use `RefreshDatabase` trait for database tests (configured in `Pest.php`)
- Use `beforeEach()` for common setup within a file

```php
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can update profile', function () {
    actingAs($this->user)
        ->put('/settings/profile', ['name' => 'New Name'])
        ->assertRedirect();
});
```

---

## Test Coverage

- **Do not aim for 100% coverage** — test meaningful behavior
- Focus on: critical paths, public APIs, edge cases, error handling, **security boundaries**
- Every endpoint MUST have at least one security test (scope, permission, or data validation)
- Use the **`generate-test`** skill for advanced patterns

---

## Debugging Tests

```bash
# Verbose output
php artisan test --compact -v

# Stop on first failure
php artisan test --compact --stop-on-failure
```

Within tests, use `dump()` or `dd()` temporarily — **remove before committing**.

---

## Pre-Commit Test Checklist

- [ ] All tests pass (`composer test`)
- [ ] New/updated tests cover the change
- [ ] No `dd()`, `dump()`, or debug code in tests
- [ ] Test names are descriptive
- [ ] Tests are isolated and repeatable
- [ ] Factories used for model creation
- [ ] Happy path AND unhappy path covered
- [ ] Security tests included (scope, permissions, IDOR, data leakage)
- [ ] Cross-tenant isolation verified (if endpoint is tenant-scoped)
- [ ] Returned data validated (no extra fields exposed to unauthorized roles)

---

## Related Documentation

- **[STACK.md](STACK.md)** — Tech stack and dependencies
- **[CODE_STANDARDS.md](CODE_STANDARDS.md)** — Code quality and conventions
- **[WORKFLOW.md](WORKFLOW.md)** — Git workflow, commits, PRs
- Skill: **`generate-test`** — PestPHP test generation and advanced patterns
