---
description: Pre-deploy checklist — run quality gates, verify translations, build production, and confirm deployment readiness. Use before deploying to staging or production.
---

# Deploy Checklist

## Steps

1. Detect project stack:
   - `composer.json` → Laravel
   - `package.json` (no `composer.json`) → Node.js
   - `go.mod` → Go
   - `requirements.txt` / `pyproject.toml` → Python

2. Run quality gates for the detected stack:
   // turbo
   - **Laravel**: `composer lint && npm run lint && npm run types`
   - **Node.js**: `npm run lint && npm run build`
   - **Python**: `ruff check . && mypy .`
   - **Go**: `golangci-lint run && go vet ./...`

3. Run test suite:
   // turbo
   - **Laravel**: `composer test`
   - **Node.js**: `npm test`
   - **Python**: `pytest`
   - **Go**: `go test ./...`

4. Check for pending translations (if i18n is configured):
   - **Laravel + React**: verify `lang/` files are in sync
   - Use `i18n-manager` skill if gaps are found

5. Build production assets:
   // turbo
   - **Laravel**: `npm run build`
   - **Node.js**: `npm run build`
   - **Python**: skip (or `python -m build` for packages)
   - **Go**: `go build ./...`

6. Verify pending database changes:
   - **Laravel**: `php artisan migrate --pretend` to preview migrations
   - Report any pending migrations to the user

7. Pre-deploy summary — report to the user:
   - ✅/❌ Quality gates
   - ✅/❌ Tests
   - ✅/❌ Translations
   - ✅/❌ Build
   - ⚠️ Pending migrations (if any)
   - Ask: "Ready to deploy?"
