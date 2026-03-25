---
description: Show current project development status — progress tracking, active branch, pending tasks, and quality gate state. Use to get a quick overview of where the project stands.
---

# Project Status

Multi-source status check — combines git, progress files, tasks, and code quality.

## Steps

1. **Git state** (always available):
   // turbo

   ```bash
   echo "Branch: $(git branch --show-current)"
   echo "Uncommitted: $(git status --short | wc -l) files"
   git log --oneline -5
   ```

2. **Progress tracking** (if `docs/progress/` exists):
   // turbo
   - Read `docs/progress/README.md` for the dashboard
   - Count tasks by status: `TODO`, `IN_PROGRESS`, `DONE`, `BLOCKED`
   - Calculate completion by epic

3. **Task files** (if `docs/tasks/` exists):
   - Scan for pending tasks
   - List next 3 `TODO` tasks with IDs and descriptions

4. **Branch analysis** (smart fallback):
   - Parse current branch name for context:
     - `feat/X` → working on feature X
     - `fix/X` → fixing bug X
     - `refactor/X` → refactoring X
   - Check recent commit messages for activity summary

5. **Quick quality snapshot**:
   // turbo
   - Detect stack and run fast lint (skip if toolchain not installed):
     - **Laravel**: `vendor/bin/pint --test 2>&1 | tail -3` (if `vendor/bin/pint` exists)
     - **Node.js**: `npm run lint 2>&1 | tail -3` (if `lint` script exists in `package.json`)
   - Report: passing, failing, or skipped (toolchain not found)

6. **Present summary**:

   ```markdown
   ## 📊 Project Status

   ### Git

   - **Branch**: `feat/...`
   - **Recent commits**: [last 5]
   - **Uncommitted**: X files

   ### Progress (if tracking exists)

   | Epic | Done | Total | % |
   | ... | ... | ... | . |

   ### Current Work (inferred)

   - [from branch name or recent commits]

   ### Next Up (if tasks exist)

   1. [TASK-ID] — Description
   2. [TASK-ID] — Description

   ### Quality

   - Lint: ✅/❌
   ```

## Data Sources Priority

1. **Git** — Always available, always accurate
2. **docs/progress/** — Available when `generate-task-breakdown` was used
3. **docs/tasks/** — Available when task planning was done
4. **Branch + commits** — Inferred context, always available

> If no progress files exist, report git-based status only. Do NOT report "no progress found" — always show SOMETHING useful.
