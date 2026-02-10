# Code Standards & Development Philosophy

**Purpose**: Define code quality standards, architectural principles, and development philosophy for this Laravel package.

---

## Development Philosophy

### Core Principles

- **No DDD** (no aggregates, no repositories, no over-engineering)
- **Low bureaucracy, high clarity**
- **Simplicity and explicitness over abstraction**
- **Configuration over hard-coding**
- **Plug-and-play mindset** (features should be easy to enable/disable)

### Architecture Goals

Code must be:

- ✅ **Extensible** – easy to add new features
- ✅ **Pluggable** – features can be enabled/disabled
- ✅ **Testable** – business logic is unit-testable
- ✅ **Maintainable** – readable and clear for future developers

---

## SOLID Principles (Pragmatic Application)

Follow SOLID, but pragmatically:

- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Extend via interfaces/configuration, not modification
- **Liskov Substitution**: Subtypes must be interchangeable
- **Interface Segregation**: Small, focused interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

**Important**: Apply SOLID to improve clarity and extensibility, not to create unnecessary layers.

---

## Code Style & Quality Tools

### PSR Standards

- **PSR-12**: Code style standard
- **PSR-4**: Autoloading standard

### Quality Tools

**All tools are configured and must pass before commits:**

1. **Pint** (Laravel's opinionated PHP-CS-Fixer)
   - Config: `pint.json`
   - Enforces PSR-12 + Laravel conventions
   - Auto-fixes code style

2. **PHPStan** (via Larastan)
   - Level: **5** (strict but pragmatic)
   - Analyzes code for type safety and potential bugs
   - Config: `phpstan.neon`

3. **Rector** (PHP automated refactoring)
   - Enforces modern PHP patterns
   - Auto-upgrades deprecated code
   - Config: `rector.php`

### Running Quality Checks

**Single command to run all checks:**

```bash
composer lint
```

This runs:
1. `composer format` (Pint)
2. `composer rector` (Rector)
3. `composer analyze` (PHPStan/Larastan)

**All checks must pass before committing.**

---

## Code Organization

### Class Responsibilities

- **Keep classes small and focused**
- **Clear, single responsibility per class**
- **Avoid "god objects" or classes that do too much**

### Fluent APIs

Prefer fluent, expressive APIs:

```php
// ✅ Good: Fluent and readable
LaravelTracing::correlation()
    ->fromHeader('X-Correlation-Id')
    ->persistInSession()
    ->attachToResponse();

// ❌ Bad: Configuration array soup
$tracing->configure([
    'correlation' => [
        'header' => 'X-Correlation-Id',
        'session' => true,
        'response' => true,
    ],
]);
```

### Configuration

- **Never hard-code values** that might change
- **Use config files** (`config/laravel-tracing.php`)
- **Allow environment variable overrides**
- **Provide sensible defaults** (zero-config when possible)

---

## Static Analysis Rules

### Keep Code Analyzable

- Avoid excessive magic methods (`__call`, `__get`, etc.)
- Avoid runtime reflection abuse
- Use explicit types whenever possible
- Prefer dependency injection over service locators

### Type Declarations

```php
// ✅ Good: Explicit types
public function resolve(Request $request): string
{
    return $request->header('X-Correlation-Id') ?? Str::uuid()->toString();
}

// ❌ Bad: No types
public function resolve($request)
{
    return $request->header('X-Correlation-Id') ?? Str::uuid()->toString();
}
```

---

## Naming Conventions

### Classes

- **PascalCase**: `CorrelationIdResolver`
- **Descriptive and specific**: Avoid generic names like `Manager`, `Handler`, `Service`

### Methods

- **camelCase**: `resolveFromRequest()`
- **Verb-first for actions**: `get`, `set`, `resolve`, `attach`, `persist`
- **Boolean methods**: prefix with `is`, `has`, `can`, `should`

### Variables

- **camelCase**: `$correlationId`
- **Descriptive**: Avoid single-letter variables except in loops

---

## Refactoring Rules

### When to Refactor

✅ **Do refactor:**
- When implementing a related feature
- When fixing a bug in the same area
- When explicitly requested

❌ **Don't refactor:**
- Unrelated code while implementing a feature
- Code that works and is clear (refactoring for refactoring's sake)
- Without explicit scope and intention

### Scope Rule

**Refactoring must be scoped and intentional.**  
Always ask before performing structural refactors.

---

## Existing Code First Rule

**Before implementing anything new:**

1. ✅ Inspect existing code
2. ✅ Reuse existing patterns
3. ✅ Prefer consistency over novelty
4. ✅ Do not introduce a new pattern if a similar one already exists

**Consistency beats cleverness.**

---

## Uncertainty Handling

If information is missing or ambiguous:

- ❌ Do not guess
- ❌ Do not invent APIs or behavior
- ✅ Ask **one clear and objective question** before proceeding

---

## Autonomy Boundaries

**Claude Code must NOT:**

- Introduce new architectural layers without asking
- Change existing folder structures without confirmation
- Add new dependencies unless explicitly requested
- Introduce design patterns by default

**When in doubt, ASK before implementing.**

---

## Available Quality Scripts

Check `composer.json` for all available scripts:

```bash
composer format   # Run Pint (code style)
composer analyze  # Run PHPStan/Larastan (static analysis)
composer rector   # Run Rector (automated refactoring)
composer lint     # Run all quality checks (format + rector + analyze)
```

**Mandatory before commits**: `composer lint`
