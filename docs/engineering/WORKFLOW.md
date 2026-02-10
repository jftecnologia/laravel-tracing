# Development Workflow

**Purpose**: Define the complete development workflow from starting work to opening pull requests.

---

## Package Development Environment

This is a **Laravel package** (library), not a standalone application.

### Development Testing Environment

Use **Orchestra Testbench Workbench** for development and manual testing:

```bash
composer serve
```

This starts a minimal Laravel application at `http://localhost:8000` with the package loaded.

---

## Pre-Commit Checklist

**Before ANY commit, you MUST:**

1. ✅ Run all quality checks:
   ```bash
   composer lint
   ```

2. ✅ Ensure tests pass (if tests exist):
   ```bash
   composer test
   ```

3. ✅ Verify code follows standards:
   - PSR-12 code style
   - PSR-4 autoloading
   - No PHPStan errors
   - No Rector violations

**No commits are allowed without passing `composer lint`.**

---

## Git Workflow

### Branch Strategy

**Always create a new branch for new work.**

Never commit directly to `master`.

### Branch Naming Convention

Use **semantic branch names**:

```
<type>/<short-description>

Types:
- feat/      → New feature
- fix/       → Bug fix
- refactor/  → Code refactoring (no behavior change)
- chore/     → Tooling, dependencies, config
- docs/      → Documentation only
- test/      → Test additions or fixes
```

**Examples:**

```bash
git checkout -b feat/correlation-id-persistence
git checkout -b fix/queue-context-restore
git checkout -b refactor/tracing-resolver
git checkout -b chore/update-dependencies
git checkout -b docs/add-custom-tracing-guide
```

---

## Commit Guidelines

### Semantic Commits

Use **semantic commit messages**:

```
<type>(<scope>): <short description>

[optional body explaining what and why]

Types:
- feat:     New feature
- fix:      Bug fix
- refactor: Code refactoring
- chore:    Tooling, config, dependencies
- docs:     Documentation
- test:     Test additions or changes
- style:    Code style (formatting, no logic change)
- perf:     Performance improvement
```

**Examples:**

```
feat(tracing): add correlation ID session persistence

Implemented session-level persistence for correlation IDs to maintain
context across multiple requests from the same user session.

Resolves requirement FR-02 from docs/PRD.md
```

```
fix(jobs): preserve original request ID in queued jobs

Previously, a new request ID was generated when jobs executed.
Now the original dispatching request's ID is preserved.
```

```
chore(deps): update Orchestra Testbench to ^10.8
```

### Commit Scope Rules

**Commit by functional activity, not by file.**

✅ **Good:**
```
feat(middleware): add tracing header extraction
```

❌ **Bad:**
```
update TracingMiddleware.php and config file
```

### What Makes a Good Commit

- **Atomic**: One logical change per commit
- **Complete**: The commit doesn't break the application
- **Descriptive**: Clear what was changed and why
- **Tested**: Tests pass (if applicable)

---

## Pull Request Workflow

### Before Opening a PR

**Ask the user first:**

> "Deseja que eu abra um Pull Request?"

**Only open PRs when the user confirms.**

### PR Guidelines

Pull requests must:

1. ✅ **Be created for functional blocks**, not single commits
2. ✅ **Have semantic and detailed titles**
3. ✅ **Include a clear description** explaining:
   - **What** was done
   - **Why** it was done
   - **Technical decisions** made
   - **Requirements** addressed (reference PRD if applicable)

### PR Title Format

```
<type>: <short description>

Examples:
feat: Add correlation ID session persistence
fix: Preserve request ID in queued jobs
refactor: Extract tracing resolution logic
chore: Update testing dependencies
```

### PR Description Template

```markdown
## What

Brief description of the changes.

## Why

Explain the motivation and context.
Reference any issues, requirements, or PRD sections.

## How

Explain technical approach and key decisions.

## Requirements Addressed

- [ ] FR-02: Correlation ID session persistence
- [ ] FR-06: Tracing values in queued jobs

## Checklist

- [ ] `composer lint` passes
- [ ] Tests pass (if applicable)
- [ ] Documentation updated (if needed)
- [ ] No breaking changes (or documented if necessary)
```

---

## Working with Tests

### When to Run Tests

- ✅ Before committing
- ✅ Before opening a PR
- ✅ After making significant changes
- ✅ Before concluding any task

### Running Tests

```bash
composer test
```

**All tests must pass before committing.**

See [TESTING.md](TESTING.md) for detailed testing guidelines.

---

## Code Review Preparation

Before requesting review:

1. ✅ Self-review your changes
2. ✅ Ensure `composer lint` passes
3. ✅ Ensure `composer test` passes
4. ✅ Remove debug code, `dd()`, `dump()`, commented code
5. ✅ Update documentation if needed
6. ✅ Check for:
   - Unused imports
   - Console logs
   - TODOs without tickets
   - Hardcoded values that should be configurable

---

## Available Scripts

Check `composer.json` for all available commands:

```bash
# Quality & Linting
composer format   # Run Pint (code formatting)
composer analyze  # Run PHPStan (static analysis)
composer rector   # Run Rector (automated refactoring)
composer lint     # Run all quality checks (MANDATORY before commits)

# Testing
composer test     # Run PestPHP test suite

# Development
composer serve    # Start Testbench development server
```

---

## Workflow Summary

### Daily Development Cycle

```bash
# 1. Create feature branch
git checkout -b feat/my-feature

# 2. Make changes
# ... edit code ...

# 3. Run quality checks
composer lint

# 4. Run tests
composer test

# 5. Commit changes
git add .
git commit -m "feat(scope): description"

# 6. Push branch
git push origin feat/my-feature

# 7. Ask user before opening PR
# "Deseja que eu abra um Pull Request?"
```

---

## Final Checklist Before Task Completion

Before declaring a task finished:

- [ ] Code follows PSR-12 / PSR-4
- [ ] `composer lint` executed and passes
- [ ] Tests executed and pass (if applicable)
- [ ] No unnecessary abstractions added
- [ ] Code is extensible and readable
- [ ] Documentation updated (if needed)
- [ ] Ready to ask: **"Deseja que eu abra um Pull Request?"**
