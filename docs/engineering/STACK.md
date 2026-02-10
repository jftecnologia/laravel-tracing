# Tech Stack Definition

**Purpose**: Define the complete technical stack for this Laravel package.

---

## Quick Reference

| Aspect | Technology | Version |
|--------|-----------|---------|
| **Language** | PHP | 8.4+ |
| **Framework** | Laravel | 12 |
| **Package Type** | Library | - |
| **Testing** | PestPHP | ^4.3 |
| **Test Environment** | Orchestra Testbench | ^10.8 |
| **Static Analysis** | Larastan | ^3.0 |
| **Code Formatting** | Pint | ^1.27 |
| **Refactoring** | Rector Laravel | ^2.1 |

---

## Project Type

**Laravel Package (Library)**  
**Package Name**: `jftecnologia/laravel-tracing`  
**License**: MIT

**Important**: This is a **library package**, not a standalone application.  
It integrates into existing Laravel applications.

---

## Runtime Requirements

- **PHP**: 8.4+
- **Laravel**: 12 (via `illuminate/support` and `illuminate/contracts`)

### Core Dependencies

Required in production environments:

- `illuminate/support`: ^12 (Laravel helpers, collections, service container)
- `illuminate/contracts`: ^12 (Laravel interfaces for dependency injection)

**Note**: Check `composer.json` for the complete and up-to-date dependency list.

---

## Development & Testing

### Testing Framework

- **PestPHP**: ^4.3 (test framework)
- **Orchestra Testbench**: ^10.8 (Laravel package testing environment)

### Code Quality Tools

- **Larastan**: ^3.0 (PHPStan for Laravel - static analysis)
- **Pint**: ^1.27 (code style formatting - Laravel's opinionated PHP-CS-Fixer wrapper)
- **Rector Laravel**: ^2.1 (automated refactoring and upgrades)

### Development Utilities

- **Laravel Prompts**: ^0.3.9 (elegant CLI prompts for artisan commands)

---

## Available Scripts

**Always check `composer.json` for the complete and current list of scripts.**

Commands defined in `composer.json`:

```bash
# Quality & Linting
composer format   # Run Pint (code style formatter)
composer analyze  # Run Larastan/PHPStan (static analysis)
composer rector   # Run Rector (automated refactoring)
composer lint     # Run all quality checks (format + rector + analyze)

# Testing
composer test     # Run PestPHP test suite

# Development
composer serve    # Start Orchestra Testbench workbench server
```

### Script Usage

- **Before commits**: Always run `composer lint`
- **Before PRs**: Always run `composer lint && composer test`
- **During development**: Use `composer serve` for manual testing

---

## Development Environment

### Orchestra Testbench Workbench

This package uses **Orchestra Testbench Workbench** for development and manual testing.

**What it provides:**
- A minimal Laravel application with this package loaded
- A development server for testing package features manually
- Application structure in `workbench/` directory

**Start development server:**
```bash
composer serve
```

Access at: `http://localhost:8000`

**Note**: The workbench is for **development only**. It's not part of the distributed package.

---

## Testing Guidelines

- Tests use **PestPHP syntax** exclusively  
- Tests run via **Orchestra Testbench** (provides Laravel environment in isolation)  
- Tests are created **only when explicitly requested by user**  
- **Always run tests before concluding a task** to ensure nothing breaks  
- Test locations: `tests/Feature/` and `tests/Unit/`

**See [TESTING.md](TESTING.md) for complete testing guidelines.**

---

## Code Quality Standards

### Tools & Requirements

- **Static Analysis**: Larastan/PHPStan (Level 5) - All code must pass `composer analyze`
- **Code Formatting**: Pint (PSR-12 + Laravel preset) - All code must pass `composer format`
- **Automated Refactoring**: Rector Laravel - Follow rules when applicable

### Mandatory Before Commits

```bash
composer lint
```

This runs: format → rector → analyze

All checks must pass before committing.

**See [CODE_STANDARDS.md](CODE_STANDARDS.md) for complete code standards and philosophy.**

---

## Package Structure

```
src/                        # Package source code
├── LaravelTracing.php      # Main package class
├── LaravelTracingServiceProvider.php  # Service provider
└── Facades/                # Facades

config/                     # Configuration files
└── laravel-tracing.php     # Package configuration

tests/                      # Test suite
├── Feature/                # Feature tests
└── Unit/                   # Unit tests

docs/                       # Documentation
├── PRD.md                  # Product requirements
└── engineering/            # Engineering documentation
```

---

## Package Auto-Discovery

This package uses Laravel's auto-discovery feature:

- **Service Provider**: `JuniorFontenele\LaravelTracing\LaravelTracingServiceProvider`
- **Facade Alias**: `LaravelTracing` → `JuniorFontenele\LaravelTracing\Facades\LaravelTracing`

No manual registration required after `composer require`.

---

## What This Stack Does NOT Include

Since this is a **library package**, not a full application:

❌ **No database layer**
- No MySQL, PostgreSQL, migrations, or models
- Consuming applications handle their own database

❌ **No frontend stack**
- No React, Vue, Inertia.js
- No Tailwind CSS, Vite, Webpack
- No npm/yarn/pnpm

❌ **No admin panels**
- No FilamentPHP, Laravel Nova
- Package provides logic, not UI

❌ **No application-level infrastructure**
- No queues configuration (package integrates with existing queues)
- No session drivers (uses application's session)
- No cache drivers (uses application's cache)

**Key Principle**: Consuming applications provide their own infrastructure.  
This package integrates seamlessly with existing Laravel applications.

---

## Related Documentation

- **[CODE_STANDARDS.md](CODE_STANDARDS.md)** → Development philosophy and code quality standards
- **[WORKFLOW.md](WORKFLOW.md)** → Git workflow, commits, PRs, and development cycle
- **[TESTING.md](TESTING.md)** → Complete testing guidelines and practices
- **[../PRD.md](../PRD.md)** → Product requirements document

---

## Updating This Document

When the stack changes:

1. ✅ Update this document
2. ✅ Update `composer.json` (if dependencies change)
3. ✅ Run `composer lint` to ensure compatibility
4. ✅ Update related documentation if needed

**Always keep this document synchronized with `composer.json`.**
