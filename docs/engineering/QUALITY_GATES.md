# Quality Gates

**Purpose**: Mandatory quality checks before completing any development task. Pragmatic, not bureaucratic — run the checks, fix the issues, move on.

---

## Gate Sequence

```text
Code Change ──▶ Lint ──▶ Test ──▶ i18n ──▶ Security ──▶ Commit
```

Always execute in order. All four gates MUST pass before committing (see [Gate Failure Handling](#gate-failure-handling) for explicit exceptions).

---

## Gate 1: Lint (Code Quality)

Run static analysis and formatting:

| Stack       | Command                             | Notes                   |
| ----------- | ----------------------------------- | ----------------------- |
| PHP/Laravel | `composer lint`                     | Pint + Rector + PHPStan |
| Node.js/TS  | `npm run lint && npm run types`     | ESLint + TypeScript     |
| Python      | `ruff check . && mypy .`            | Ruff + mypy             |
| Go          | `go vet ./... && golangci-lint run` | Vet + linter            |
| Bash        | `shellcheck *.sh`                   | ShellCheck              |

> **Tip**: During AI development, use `vendor/bin/pint --dirty --format agent` for incremental PHP formatting.

**If lint fails:** fix issues → re-run → commit as `style: fix lint issues`.

---

## Gate 2: Tests (Three Pillars)

Every feature/endpoint MUST cover all three pillars:

| Pillar           | What                                   | Example                                 |
| ---------------- | -------------------------------------- | --------------------------------------- |
| ✅ Happy Path    | Expected behavior with valid inputs    | Login succeeds with correct credentials |
| ❌ Unhappy Path  | Failures, validation, edge cases       | Login fails with wrong password         |
| 🔒 Security Path | Scope, permissions, IDOR, data leakage | Cannot access another tenant's data     |

> A test file covering only the happy path is **incomplete**.

### Commands

| Stack       | Run tests       | Run specific                                                  |
| ----------- | --------------- | ------------------------------------------------------------- |
| PHP/Laravel | `composer test` | `php artisan test --compact tests/Feature/Auth/LoginTest.php` |
| Node.js     | `npm test`      | —                                                             |
| Python      | `pytest`        | `pytest tests/test_auth.py`                                   |
| Go          | `go test ./...` | `go test ./pkg/auth/...`                                      |

### When tests are required

- Invoke `generate-test` skill to create tests following the three-pillar approach
- Commit: `test(scope): add tests for [feature]`

### When tests fail

1. Analyze: is the test wrong or the code wrong?
2. **Default**: fix the **code**, not the test
3. **Exception** (requires user approval + justification): test was genuinely incorrect or logic was intentionally changed
4. Re-run until passing

> 🚫 **NEVER** weaken or delete tests to make broken code pass.

---

## Gate 3: i18n Check

All user-facing strings must use translation helpers (`t()`, `__()`).

### Quick check commands

```bash
# PHP: find hardcoded strings in changed files
git diff --name-only --diff-filter=ACMR -- '*.php' | xargs grep -n "['\"]\w" | grep -v "__(" | grep -v "'/\|\"/" | head -20

# TSX: find hardcoded strings in changed files
git diff --name-only --diff-filter=ACMR -- '*.tsx' | xargs grep -n ">\w" | grep -v "{t(" | head -20
```

**If hardcoded strings found:** replace with translation keys → run `i18n-manager` skill if needed.

---

## Gate 4: Security Analysis

Invoke `security-analyst` skill on files created/modified.

| Finding Severity | Action                                                             |
| ---------------- | ------------------------------------------------------------------ |
| 🔴 **CRITICAL**  | Must fix before completing. Commit: `fix(security): [description]` |
| 🟠 **HIGH**      | Must fix before completing. Commit: `fix(security): [description]` |
| 🟡 **MEDIUM**    | Inform user, proceed with their decision                           |
| 🔵 **LOW**       | Inform user, proceed with their decision                           |
| ⚪ **INFO**      | Optional improvement                                               |

### Quick security checklist (for changed files)

- [ ] No raw SQL queries (use Eloquent/Query Builder)
- [ ] No `$request->all()` in mass assignment (use `$request->validated()`)
- [ ] Authorization checks present (`$this->authorize()` or Policy)
- [ ] No unescaped output in Blade (`{!! !!}` without sanitization)
- [ ] No hardcoded secrets or credentials
- [ ] CSRF protection present
- [ ] File uploads validated (type, size)

---

## Gate Failure Handling

If any gate fails:

1. Report the failure clearly
2. Propose a fix
3. Ask how to proceed:
   - 1️⃣ **Fix and retry** — attempt to fix the issue (default)
   - 2️⃣ **Skip with justification** — proceed despite failure (requires explicit user approval; must document reason in commit message)
   - 3️⃣ **Abort** — stop the current task

> **Note**: Option 2 is an escape hatch, not a shortcut. The user MUST confirm and provide a reason.

---

## When Gates Apply

| Phase      | Which gates                | Trigger                                                                                                   |
| ---------- | -------------------------- | --------------------------------------------------------------------------------------------------------- |
| Post-Code  | All 4 gates                | `implement-task`, `bug-fixer`, `/debug`, `/refactor`, `/improve-ui`, `/test`, any code-producing workflow |
| Pre-PR     | All 4 gates (full suite)   | `/code-review` workflow                                                                                   |
| Pre-Deploy | All 4 + build + migrations | `/deploy` workflow                                                                                        |

---

## Related Documentation

- [TESTING.md](TESTING.md) — Testing philosophy, PestPHP patterns, three pillars detail
- [WORKFLOW.md](WORKFLOW.md) — Pre-commit checklist and PR requirements
- [CODE_STANDARDS.md](CODE_STANDARDS.md) — Code quality conventions
- Skill: `generate-test` — Automated test generation
- Skill: `security-analyst` — Security vulnerability analysis
- Skill: `i18n-manager` — Translation audit and sync
