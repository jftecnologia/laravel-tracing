---
description: Quick pre-PR code review — run lint, tests, security analysis, and code quality review on changed files. Use before opening a pull request.
---

# Pre-PR Code Review

## Steps

1. Identify changed files:
   // turbo

   ```bash
   git diff --name-only master...HEAD
   ```

   If no diff against `master`, use:

   ```bash
   git diff --name-only HEAD~5
   ```

2. Run lint and type checks:
   // turbo
   - **Laravel**: `composer lint && npm run lint && npm run types`
   - **Node.js**: `npm run lint && npm run build`
   - **Python**: `ruff check . && mypy .`
   - **Go**: `golangci-lint run`

3. Run full test suite:
   // turbo
   - **Laravel**: `composer test`
   - **Node.js**: `npm test`
   - **Python**: `pytest`
   - **Go**: `go test ./...`

4. Security analysis:
   - Invoke `security-analyst` skill on the changed files
   - Report any CRITICAL or HIGH findings

5. Code quality review:
   - Invoke `code-reviewer` skill on the changed files
   - Focus on: SOLID, dead code, test coverage, consistency

6. Generate PR summary:
   - List all changed files with brief description
   - Summarize results from steps 2-5
   - Suggest PR title and description following `docs/engineering/WORKFLOW.md` format

7. Present to user:

   ```
   ## 📋 Pre-PR Review Summary

   ### Quality Gates
   - Lint: ✅/❌
   - Tests: ✅/❌ (X passed, Y failed)
   - Security: ✅/❌ (findings count)

   ### Code Review
   - [summary from code-reviewer]

   ### Suggested PR
   - Title: `feat: ...`
   - Files: X changed

   > Ready to open PR?
   ```
