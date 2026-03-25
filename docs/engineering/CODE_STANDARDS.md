# Code Standards & Conventions

**Purpose**: Define code quality standards, conventions, and development philosophy for this Laravel application.

---

## Development Philosophy

### Core Principles

- **No DDD** — no aggregates, repositories, or domain layers
- **Low bureaucracy, high clarity**
- **Simplicity and explicitness over abstraction**
- **Configuration over hard-coding** — use `config()` or tenant/user DB values instead of magic numbers and inline constants. If a value could reasonably vary by environment or tenant, externalize it
- **Plug-and-play mindset** — features should be easy to enable/disable

### Architecture Goals

Code must be: **Extensible**, **Pluggable**, **Testable**, **Maintainable**.

SOLID applied pragmatically — improve clarity, not add layers.

---

## Code Organization

### Where Things Go

| What                                   | Where                           | Example                                                     |
| -------------------------------------- | ------------------------------- | ----------------------------------------------------------- |
| **Actions** (single-purpose)           | `app/Actions/<Domain>/`         | `app/Actions/Fortify/CreateNewUser.php`                     |
| **Services** (multi-step logic)        | `app/Services/`                 | `app/Services/PaymentService.php`                           |
| **Enums**                              | `app/Enums/`                    | `app/Enums/LogLevel.php`                                    |
| **Extensions** (package customization) | `app/Extensions/<Package>/`     | `app/Extensions/ActivityLog/Activity.php`                   |
| **Controllers**                        | `app/Http/Controllers/`         | Grouped by domain in subfolders                             |
| **Form Requests**                      | `app/Http/Requests/`            | `app/Http/Requests/Settings/ProfileUpdateRequest.php`       |
| **Middleware**                         | `app/Http/Middleware/`          | App-level only                                              |
| **Models**                             | `app/Models/`                   | One model per file                                          |
| **Providers**                          | `app/Providers/`                | Service providers                                           |
| **Jobs**                               | `app/Jobs/`                     | Queued jobs                                                 |
| **Events / Listeners**                 | `app/Events/`, `app/Listeners/` | Event-driven features                                       |
| **Notifications**                      | `app/Notifications/`            | Email, SMS, database notifications                          |
| **Policies**                           | `app/Policies/`                 | Authorization policies                                      |
| **Helpers**                            | `app/Support/helpers.php`       | Global helper functions                                     |
| **Exceptions**                         | `app/Exceptions/`               | Custom exception classes (use `php artisan make:exception`) |

### Actions vs Services

- **Action**: single purpose, one public method (`execute`). Use for discrete operations.
- **Service**: coordinates multiple steps or has multiple related methods. Use when logic requires orchestration.
- **Rule**: if it does one thing, it's an Action. If it coordinates, it's a Service.

### Extensions Directory

`app/Extensions/` is for **non-domain customizations** of installed packages:

```text
app/Extensions/
├── ActivityLog/          # Spatie ActivityLog model overrides
├── Fortify/              # Custom Fortify actions and guards
└── System/               # System-level middleware (security headers, locale, etc.)
```

Never put business/domain logic here — only package integration customizations.

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
| Classes   | PascalCase        | `CreateNewUser`                |
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
LaravelTracing::correlation()
    ->fromHeader('X-Correlation-Id')
    ->persistInSession()
    ->attachToResponse();
```

---

## Laravel Conventions

### Artisan Commands

- Use `php artisan make:` commands to create files (migrations, controllers, models, etc.)
- For generic PHP classes, use `php artisan make:class`
- Always pass `--no-interaction` and the correct `--options` to artisan commands
- When creating models, also create factories — check available options via `php artisan make:model --help`

### Database & Eloquent

- Use Eloquent models and relationships — avoid `DB::` facade; prefer `Model::query()`
- Prevent N+1 queries with eager loading (`with()`)
- Laravel 12 supports limiting eager loaded records natively: `$query->latest()->limit(10)`
- Use relationship methods with return type hints
- Define casts in a `casts()` method (not `$casts` property) — follow existing model patterns
- Migrations must include all column attributes when modifying (Laravel 12 drops unspecified attributes)
- **System data** (lookup tables, enum values, roles, default configs, required records) must be inserted via **migrations**, not seeders
- **Database seeders** are exclusively for **demo/development data** using factories — never for application-required data
- **Database-agnostic queries only** — Production uses MySQL, tests use SQLite, and future migration to PostgreSQL is planned. Never use database-specific functions (e.g., `IFNULL`, `GROUP_CONCAT`, `JSON_EXTRACT`, `REGEXP`, `DATE_FORMAT`). Use Eloquent methods, query builder expressions, or PHP-level processing instead. If a database-specific feature is unavoidable, document it and ensure test compatibility.

### Controllers & Validation

- Always use Form Request classes for validation — never inline in controllers
- Check sibling Form Requests for array vs string rule format (follow existing convention)
- Group controllers in subfolders by domain: `Http/Controllers/Settings/`

### API Development

- Default to Eloquent API Resources with API versioning
- Follow existing API route conventions if they differ

### Authentication & Authorization

- Fortify handles headless auth (login, register, password reset, 2FA)
- Auth actions live in `app/Actions/Fortify/`
- Use gates and policies for authorization

### Configuration

- **Never use `env()` outside config files** — always `config('key')`
- Use environment variables only in `config/*.php`
- Package configurations go in `config/<package>.php`
- **Choose the right configuration level:**
  - `config/*.php` — application-wide defaults
  - Database — values that may vary per tenant, user, or context
  - User DB — personal preferences (locale, timezone, appearance)
- When reading an overridable value, prefer a fallback chain: DB value → config default

### Queues & Jobs

- Use `ShouldQueue` interface for time-consuming operations

### URL Generation

- Use named routes and `route()` function (backend)
- Frontend: **always** import from `@/actions/` or `@/routes/` (Wayfinder) — applies to `<Link href>`, `router.get()` / `router.visit()`, breadcrumbs, and form actions
- Hard-coded route URLs are only acceptable as a last resort and **must** include a comment justifying why Wayfinder cannot be used

### Internationalization (i18n)

- Use `__()` helper or `trans()` for all user-facing strings
- Translation files in `lang/` directory
- `lucascudo/laravel-pt-br-localization` provides pt-BR translations
- Design with i18n in mind — never hardcode user-facing text

---

## Frontend Standards

### TypeScript

- All frontend code in TypeScript — no plain `.js` files
- Use strict mode (`tsconfig.json`)
- Define shared types in `resources/js/types/`

### React Components

- Functional components only (no class components)
- Use hooks for state and effects
- Page components in `resources/js/pages/`
- Reusable components in `resources/js/components/`
- Layouts in `resources/js/layouts/`

### Inertia.js

- Use `Inertia::render()` for server-side routing (not Blade views)
- Page components live in `resources/js/pages/` (configurable via `vite.config.js`)
- Use deferred props with skeleton/pulsing loading states
- Always use `search-docs` for version-specific Inertia documentation
- See skill: `frontend-development` for Inertia patterns

### Wayfinder (Route Generation)

- **Always prefer Wayfinder** over hard-coded URLs — this is the default for all frontend route references
- Import from `@/actions/` (controllers) or `@/routes/` (named routes)
- Applies to **all** contexts: `<Link href>`, `router.get()` / `router.visit()`, breadcrumbs, and form actions
- Invokable controllers: `import StorePost from '@/actions/.../StorePostController'; StorePost()`
- Parameter binding detects route keys: `show({ slug: "my-post" })` for `{post:slug}`
- Query merging: `show(1, { mergeQuery: { page: 2, sort: null } })` — `null` removes params
- Inertia forms: use `.form()` with `<Form>` component or `form.submit(store())` with `useForm`
- Hard-coded URLs only as a last resort — **must** include a comment explaining why Wayfinder is not viable
- See skill: `frontend-development` for Wayfinder patterns

### Styling

- Tailwind CSS v4 utilities — follow existing patterns before adding new ones
- Use `class-variance-authority` for component variants
- Use `tailwind-merge` + `clsx` for conditional classes
- Always use `search-docs` for version-specific Tailwind CSS documentation
- See skill: `frontend-development` for Tailwind patterns

---

## Quality Tools

### Running Quality Checks

> For the complete list of quality scripts, see [STACK.md](STACK.md#available-scripts).
>
> During AI-assisted development: `vendor/bin/pint --dirty --format agent`

All checks must pass before committing.

### Static Analysis

- Larastan Level 5 — strict but pragmatic
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
5. Use descriptive names: `isRegisteredForDiscounts()`, not `discount()`

**Consistency beats cleverness.**

---

## Related Documentation

- **[STACK.md](STACK.md)** — Complete tech stack and dependencies
- **[WORKFLOW.md](WORKFLOW.md)** — Git workflow, commits, PRs
- **[TESTING.md](TESTING.md)** — Testing guidelines
