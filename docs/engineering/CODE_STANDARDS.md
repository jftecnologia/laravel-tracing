# Code Standards & Conventions

**Purpose**: Define code quality standards, conventions, and development philosophy for the Laravel Tracing package.

---

## Development Philosophy

### Core Principles

- **No DDD** — no aggregates, repositories, or domain layers
- **Low bureaucracy, high clarity**
- **Simplicity and explicitness over abstraction**
- **Configuration over hard-coding** — use `config()` instead of magic numbers and inline constants
- **Plug-and-play mindset** — features should be easy to enable/disable

### Architecture Goals

Code must be: **Extensible**, **Pluggable**, **Testable**, **Maintainable**.

SOLID applied pragmatically — improve clarity, not add layers.

---

## Code Organization

### Where Things Go

| What                          | Where                      | Example                                    |
| ----------------------------- | -------------------------- | ------------------------------------------ |
| **Contracts** (interfaces)    | `src/Tracings/Contracts/`  | `TracingSource`, `TracingStorage`           |
| **Sources** (tracing sources) | `src/Tracings/Sources/`    | `CorrelationIdSource`, `RequestIdSource`    |
| **Manager** (core logic)      | `src/Tracings/`            | `TracingManager`                           |
| **Middleware**                 | `src/Middleware/`           | `IncomingTracingMiddleware`                |
| **HTTP integration**          | `src/Http/`                | `HttpClientTracing`                        |
| **Jobs** (queue integration)  | `src/Jobs/`                | `TracingJobDispatcher`                     |
| **Storage**                   | `src/Storage/`             | `RequestStorage`, `SessionStorage`         |
| **Support** (helpers)         | `src/Support/`             | `HeaderSanitizer`, `IdGenerator`           |
| **Facades**                   | `src/Facades/`             | `LaravelTracing`                           |
| **Service Provider**          | `src/`                     | `LaravelTracingServiceProvider`            |
| **Config**                    | `config/`                  | `laravel-tracing.php`                      |

---

## PHP Standards

### Code Style

- **PSR-12** code style (enforced by Pint)
- **PSR-4** autoloading
- Always use curly braces for control structures, even single-line bodies
- Use PHP 8 constructor property promotion

### Type Declarations

Always use explicit types for parameters, return types, and properties:

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    // ...
}
```

### Enums

- Keys in **TitleCase**: `FavoritePerson`, `Monthly`, `Active`
- Use backed enums (`string` or `int`) when persisting to database

### Naming Conventions

| Element   | Style             | Example                        |
| --------- | ----------------- | ------------------------------ |
| Classes   | PascalCase        | `CorrelationIdSource`          |
| Methods   | camelCase         | `resolveFromRequest()`         |
| Variables | camelCase         | `$correlationId`               |
| Booleans  | is/has/can/should | `$isActive`, `hasPermission()` |
| Constants | UPPER_SNAKE       | `MAX_RETRIES`                  |

### PHPDoc

- Prefer PHPDoc blocks over inline comments
- Add array shape type definitions when appropriate
- Never use inline comments unless logic is exceptionally complex

### Fluent APIs

Prefer fluent, expressive interfaces when designing classes:

```php
$manager->extend('custom_id', new CustomIdSource())
    ->resolveAll($request);
```

---

## Laravel Package Conventions

### Service Provider

- Register bindings in `register()`, bootstrapping in `boot()`
- Use singleton bindings for stateful services (TracingManager, Storage)
- Merge config in `register()`, publish config in `boot()`

### Configuration

- **Never use `env()` outside config files** — always `config('key')`
- Use environment variables only in `config/laravel-tracing.php`
- All features should be toggleable via config

### Testing with Testbench

- Use `orchestra/testbench` for testing within a Laravel app context
- Test base class extends `Orchestra\Testbench\TestCase`
- Register the service provider in `getPackageProviders()`
- Workbench app in `workbench/` for manual testing

---

## Quality Tools

### Running Quality Checks

> For the complete list of quality scripts, see [STACK.md](STACK.md#available-scripts).
>
> During AI-assisted development: `vendor/bin/pint --dirty --format agent`

All checks must pass before committing.

### Static Analysis

- Larastan — strict but pragmatic
- Avoid magic methods (`__call`, `__get`) that break analysis
- Use explicit types everywhere
- Prefer dependency injection over service locators

---

## Refactoring Rules

- Refactor only when implementing a related feature, fixing a bug in the area, or when explicitly requested
- Never refactor unrelated code or code that works and is clear
- Refactoring must be scoped and intentional — ask before structural changes

---

## Consistency Rules

Before implementing anything new:

1. Check sibling files for structure, approach, and naming
2. Check for existing components to reuse before writing new ones
3. Reuse existing patterns — prefer consistency over novelty
4. Do not introduce a new pattern if a similar one exists
5. Use descriptive names: `resolveFromRequest()`, not `resolve()`

**Consistency beats cleverness.**

---

## Related Documentation

- **[STACK.md](STACK.md)** — Complete tech stack and dependencies
- **[WORKFLOW.md](WORKFLOW.md)** — Git workflow, commits, PRs
- **[TESTING.md](TESTING.md)** — Testing guidelines
