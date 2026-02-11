---
name: implement-task
description: Implement development tasks from task breakdown documents. Use when the user asks to implement, develop, or code a specific task (e.g., "implement CTI-01", "desenvolva a task SP-02"). This skill reads task definitions from docs/tasks/, follows engineering standards, creates semantic branches/commits, runs quality gates (lint, test, security), and updates progress tracking.
---

# Task Implementation

Implement development tasks following engineering standards with quality gates and progress tracking.

---

## Related References

- [references/gate-checklist.md](references/gate-checklist.md) - Detailed gate validation steps

---

## 1. Inputs

### Required

- **Task ID(s)**: One or more task IDs to implement (e.g., `CTI-01`, `SP-02,SP-03`)

### Auto-loaded

These files are automatically read at the start of implementation:

1. `docs/engineering/STACK.md` - Tech stack and available scripts
2. `docs/engineering/CODE_STANDARDS.md` - Development philosophy and quality standards
3. `docs/engineering/WORKFLOW.md` - Git workflow and commit guidelines

### Task Definition

Read the task definition from `docs/tasks/<epic-slug>.md`:

- Description and acceptance criteria
- Files to create/modify
- Technical notes
- Testing requirements

### Optional Context (read when needed)

- `docs/PRD.md` - Product requirements for business context
- `docs/architecture/*.md` - Architecture decisions and patterns
- `docs/use-cases/*.md` - User scenarios and edge cases

---

## 2. Pre-Implementation Setup

### Step 1: Understand the Task

Before writing code:

1. Read the full task definition from `docs/tasks/`
2. Understand **all** acceptance criteria
3. Identify dependencies on other tasks
4. Check current progress in `docs/progress/<epic-slug>.md`

If task has dependencies marked as `TODO` or `IN_PROGRESS`:

> ⚠️ Task [ID] depends on [DEP-ID] which is not complete.
> Do you want to implement the dependency first?

### Step 2: Create Semantic Branch

Create a branch following naming convention:

```bash
# Single task
git checkout -b feat/<epic-slug>-<task-description>

# Examples
git checkout -b feat/cti-tracing-interface
git checkout -b fix/sp-session-restore
git checkout -b refactor/jp-job-middleware
```

Branch types from WORKFLOW.md:

- `feat/` - New feature
- `fix/` - Bug fix
- `refactor/` - Code refactoring
- `chore/` - Tooling, config
- `docs/` - Documentation only
- `test/` - Test additions

### Step 3: Update Progress Status (CRITICAL)

**This step is MANDATORY and must happen BEFORE writing any code.**

Update `docs/progress/<epic-slug>.md` immediately:

```markdown
### [TASK-ID] Task Name

**Status**: `IN_PROGRESS`
**Started**: YYYY-MM-DD
**Notes**: Starting implementation
```

> ⚠️ **DO NOT skip or defer this step.** Progress tracking provides:
>
> - Visibility into current work
> - Recovery point if implementation fails
> - Accurate timestamps for reporting

---

## 3. Implementation Workflow

### Development Loop

For each acceptance criterion:

1. **Implement** - Write code following CODE_STANDARDS.md
2. **Test** - Run tests if applicable
3. **Commit** - Semantic commit for the change

### Semantic Commits

Follow commit format from WORKFLOW.md:

```
<type>(<scope>): <short description>

[optional body]

Types: feat, fix, refactor, chore, docs, test, style, perf
```

**Commit by functional activity, not by file.**

```bash
# ✅ Good
git commit -m "feat(tracing): add correlation ID interface"

# ❌ Bad
git commit -m "update TracingInterface.php"
```

### Code Quality Standards

From CODE_STANDARDS.md:

- **No DDD** - No over-engineering
- **Pragmatic SOLID** - Apply for clarity, not layers
- **Testable** - Business logic is unit-testable
- **Extensible** - Easy to add features

---

## 4. Quality Gates

**All gates MUST pass before completing a task.**

### Gate 1: Code Quality (Lint)

```bash
composer lint
```

This runs Pint (formatting), Rector (refactoring), and PHPStan (analysis).

**If lint fails:**

1. Fix reported issues
2. Re-run `composer lint`
3. Commit fixes: `style: fix lint issues`

### Gate 2: Tests

Check the task definition for testing requirements:

```markdown
**Testing**:

- [ ] Unit tests for ClassName
- [ ] Feature tests for behavior
```

**If tests are required by task:**

1. Invoke **generate-test** skill:

    > Gere testes para os arquivos implementados nesta task

2. generate-test will:
    - Analyze implemented code
    - Create appropriate test files
    - Run tests
    - Report results

3. Tests MUST pass before proceeding

4. Commit tests: `test(scope): add tests for [feature]`

**If tests are NOT required:**

```bash
composer test
```

- Existing tests must still pass
- Consider invoking generate-test for complex logic

**If tests fail:**

1. Analyze failure (test wrong or code wrong?)
2. Fix appropriately
3. Re-run until passing

See [references/gate-checklist.md](references/gate-checklist.md) for detailed test gate steps.

### Gate 3: Security Analysis

Invoke **security-analyst** skill on files created/modified:

> Analise a segurança dos arquivos modificados nesta task

**If CRITICAL or HIGH findings:**

- Must fix before completing task
- Commit fixes: `fix(security): [description]`

**If MEDIUM/LOW/INFO findings:**

- Inform user, proceed with their decision

See [references/gate-checklist.md](references/gate-checklist.md) for detailed validation steps.

---

## 5. Task Completion

### Update Progress Tracking

When all acceptance criteria are met and gates pass:

**Step 1: Update Epic Progress File**

Update `docs/progress/<epic-slug>.md`:

```markdown
### [TASK-ID] Task Name

**Status**: `DONE`
**Started**: YYYY-MM-DD
**Completed**: YYYY-MM-DD
**Commits**:

- `abc1234` feat(scope): description
- `def5678` test(scope): add tests
  **Notes**: Implementation details or decisions made
```

**Step 2: Update Progress README (MANDATORY)**

Update `docs/progress/README.md` with:

1. **Epic Progress table**: Update counts (TODO → Done) and percentage
2. **Recent Completions table**: Add completed task at the top
3. **Current Focus**: Update if epic changed or task in progress changed
4. **Total row**: Recalculate totals across all epics
5. **Last Updated date**: Update to current date

> ⚠️ **The README.md is a dashboard derived from epic progress files.**
> It MUST be updated after every task completion to maintain accuracy.

### Update Related Documentation

If implementation affects documentation:

- Update relevant `docs/architecture/*.md`
- Update `README.md` if public API changed
- Update config file comments if new options added

---

## 6. Pull Request

### Ask Before Opening

Always ask the user:

> ✅ Task [ID] concluída com sucesso.
> Deseja que eu abra um Pull Request?

### If User Confirms

Create PR with semantic title and detailed description:

**Title Format:**

```
<type>: <short description>

Examples:
feat: Add correlation ID tracing interface
fix: Preserve context in queued jobs
```

**Description Template:**

```markdown
## What

[Brief description of changes]

## Why

[Motivation and context]
[Reference to task: Implements CTI-01 from docs/tasks/core-tracing-infrastructure.md]

## How

[Technical approach and key decisions]

## Acceptance Criteria

- [x] AC-1: [Description]
- [x] AC-2: [Description]
- [x] AC-3: [Description]

## Quality Gates

- [x] `composer lint` passes
- [x] `composer test` passes
- [x] Security analysis: [PASS / PASS WITH WARNINGS]

## Files Changed

- `src/Path/To/File.php` - [What changed]
- `config/file.php` - [What changed]
```

---

## 7. Error Handling

### If Gate Fails

1. Report the failure clearly
2. Propose a fix
3. Ask user how to proceed:
    - Fix and retry
    - Skip with justification
    - Abort task

### If Task Cannot Be Completed

Update progress with `BLOCKED` or `FAILED`:

```markdown
### [TASK-ID] Task Name

**Status**: `BLOCKED`
**Blocker**: [Description of what's blocking]
**Notes**: [Context and potential solutions]
```

---

## 8. Multi-Task Implementation

When implementing multiple tasks together:

1. **Evaluate dependencies** - Order tasks by dependency
2. **Single branch** - Use branch for the epic: `feat/<epic-slug>`
3. **Separate commits** - One commit per task completion
4. **Single PR** - Batch related tasks in one PR

### Progress Tracking (CRITICAL)

**For each task in the batch, follow this exact sequence:**

1. **BEFORE starting task N:**
    - Update `docs/progress/<epic-slug>.md` with `IN_PROGRESS` status
    - This must happen BEFORE writing any code for that task

2. **AFTER completing task N:**
    - Update progress with `DONE` status and commit hash
    - Update `docs/progress/README.md` table and totals
    - Commit the progress update

**DO NOT batch progress updates.** Each task's progress must be updated individually as you work through them.

Example sequence for tasks CTI-01, CTI-02, CTI-03:

```
1. Update CTI-01 → IN_PROGRESS
2. Implement CTI-01
3. Update CTI-01 → DONE
4. Update CTI-02 → IN_PROGRESS  ← happens immediately after CTI-01 done
5. Implement CTI-02
6. Update CTI-02 → DONE
7. Update CTI-03 → IN_PROGRESS
8. Implement CTI-03
9. Update CTI-03 → DONE
```

This ensures:

- Visibility into which task is being worked on
- Recovery point if implementation fails mid-epic
- Accurate timestamps for start/completion

---

## 9. Triggering Phrases

- "Implemente a task CTI-01"
- "Desenvolva SP-02 e SP-03"
- "Implement task JP-01"
- "Code the authentication task"
- "Comece o desenvolvimento de HCI-01"

---

## 10. Language Rules

- **Code**: English (variables, functions, comments)
- **Commits**: English
- **PR descriptions**: English
- **Conversation with user**: Portuguese (pt-BR)
- **Progress notes**: Portuguese ok, English ok

---

## Completion Criteria

Task implementation is complete when:

1. ✅ All acceptance criteria are met
2. ✅ `composer lint` passes
3. ✅ `composer test` passes (or tests written if required)
4. ✅ Security analysis shows no CRITICAL/HIGH issues
5. ✅ Semantic commits made for each change
6. ✅ Epic progress file updated (`docs/progress/<epic-slug>.md`)
7. ✅ Progress README updated (`docs/progress/README.md`)
8. ✅ User asked about Pull Request
