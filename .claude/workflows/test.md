---
description: "Run existing tests or generate new tests for code. Use when you want to run the test suite, check coverage, generate tests for untested code, or validate that tests pass (e.g., 'run tests', 'create tests for X', 'check test coverage', 'test this feature')."
---

# Test

Run or generate tests for the project.

## Steps

1. **Determine the goal** — ask if unclear:
   - **Run tests**: execute existing test suite
   - **Generate tests**: create new tests for specific code
   - **Coverage check**: run with coverage report
   - **Fix failing tests**: diagnose and fix

2. **Run existing tests** (if goal is run/coverage/fix):
   // turbo
   - **Laravel**: `composer test` or `vendor/bin/pest`
   - **Node.js**: `npm test`
   - **Python**: `pytest`
   - **Go**: `go test ./...`

   For coverage:
   // turbo
   - **Laravel**: `vendor/bin/pest --coverage`
   - **Node.js**: `npm run test:coverage`
   - **Python**: `pytest --cov`
   - **Go**: `go test ./... -cover`

3. **Report results**:

   ```markdown
   ## 🧪 Test Results

   - **Total**: X tests
   - **Passed**: Y ✅
   - **Failed**: Z ❌
   - **Coverage**: N% (if available)

   ### Failing tests (if any)

   - `TestName` — reason
   ```

4. **Generate tests** (if goal is generate):
   - Route to `generate-test` skill with the target files
   - Ensure three pillars: Happy path, Unhappy path, Security path
   - Run generated tests to verify they pass

5. **Fix failing tests** (if goal is fix):
   - Read failing test output
   - Determine cause: code bug or outdated test?
   - If code bug → route to `/debug`
   - If outdated test → update test to match current behavior
   - Re-run to confirm fix

> ⚠️ **Post-Code Gates**: After generating or fixing tests, execute Mandatory Gates (see `AGENT_FLOW.md` §3.2)
