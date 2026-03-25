# Tech Stack

**Purpose**: Define the complete technical stack for the Laravel Tracing package.

> [!NOTE]
> These are **reference versions**. On first interaction, verify actual versions via `composer.json`. Update this file if discrepancies exist. See `CLAUDE.md` §7 (Version Freshness).

---

## Quick Reference

| Layer               | Technology         | Version |
| ------------------- | ------------------ | ------- |
| **Language**        | PHP                | ^8.4    |
| **Framework**       | Laravel (illuminate) | ^12   |
| **Testing**         | PestPHP            | ^4.3    |
| **Test Environment**| Orchestra Testbench| ^10.8   |
| **Static Analysis** | Larastan (PHPStan) | ^3.0    |
| **Code Formatting** | Pint               | ^1.27   |
| **Refactoring**     | Rector             | ^2.1    |

---

## Project Type

**Laravel Package (library)** — `composer.json` type: `library`.

Provides distributed tracing for Laravel applications. Depends only on `illuminate/support`, `illuminate/contracts`, and `illuminate/queue`.

---

## Package Dependencies

### Runtime

| Package                | Purpose                           |
| ---------------------- | --------------------------------- |
| `illuminate/support`   | Service provider, facades, config |
| `illuminate/contracts` | Interface contracts               |
| `illuminate/queue`     | Job payload hooks, queue events   |

### Development

| Package                     | Purpose                                |
| --------------------------- | -------------------------------------- |
| `orchestra/testbench`       | Laravel app context for package tests  |
| `pestphp/pest`              | Testing framework                      |
| `larastan/larastan`         | Static analysis (PHPStan for Laravel)  |
| `laravel/pint`              | Code formatting (PSR-12 + Laravel)     |
| `driftingly/rector-laravel` | Automated refactoring                  |
| `laravel/prompts`           | Interactive CLI prompts (dev tools)    |

---

## Quality Tools

| Tool         | Config File    | Purpose                            |
| ------------ | -------------- | ---------------------------------- |
| **Pint**     | `pint.json`    | Code formatting (PSR-12 + Laravel) |
| **Larastan** | `phpstan.neon` | Static analysis                    |
| **Rector**   | `rector.php`   | Automated refactoring              |

---

## Available Scripts

### Composer

```bash
composer format       # Run Pint (code formatter)
composer rector       # Run Rector (automated refactoring)
composer analyze      # Run Larastan/PHPStan (static analysis)
composer lint         # Run format + rector + analyze (all quality checks)
composer test         # Run PestPHP test suite
composer serve        # Build workbench + serve via Testbench
```

### Script Usage

| When                 | Run                                 |
| -------------------- | ----------------------------------- |
| **Before commits**   | `composer lint`                     |
| **Before PRs**       | `composer lint && composer test`    |
| **Testing features** | `composer test`                     |
| **Local dev server** | `composer serve` (Testbench app)    |

---

## Related Documentation

- **[CODE_STANDARDS.md](CODE_STANDARDS.md)** — Code quality and conventions
- **[WORKFLOW.md](WORKFLOW.md)** — Git workflow, commits, PRs
- **[TESTING.md](TESTING.md)** — Testing guidelines
