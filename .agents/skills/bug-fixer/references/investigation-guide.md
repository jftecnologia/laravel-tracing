# Investigation Guide

Diagnostic checklist organized by bug type. Use MCP tools and code analysis to identify root causes.

---

## MCP Diagnostic Tools

| Tool | When to Use |
|------|-------------|
| `last-error` | First step for any backend error — shows last exception with stack trace |
| `browser-logs` | Frontend bugs, JS errors, console warnings |
| `database-schema` | Schema mismatches, missing columns, wrong types |
| `database-query` | Inspect actual data causing the issue |
| `tinker` | Test hypotheses, reproduce logic in isolation |
| `list-routes` | Routing issues, middleware problems, 404s |
| `get-absolute-url` | Verify URL resolution |
| `read-log-entries` | Application log analysis |
| `search-docs` | Framework behavior verification |

---

## Investigation by Bug Type

### HTTP 500 — Internal Server Error

1. Run `last-error` — check exception class, message, file, line
2. Read the file at the error location
3. Check if error is in a migration/schema issue → `database-schema` with filter
4. Check if error is in a relationship → verify model relations and pivot tables
5. Check recent git changes: `git log --oneline -10 -- <file>`
6. Search for similar patterns: `grep_search` in related files

### HTTP 404 — Not Found

1. Run `list-routes` with path filter
2. Check route definitions in `routes/` files
3. Verify controller exists and method is defined
4. Check middleware that might redirect
5. Check model route binding (`getRouteKeyName()`)

### HTTP 403 — Forbidden

1. Check route middleware (auth, role, tenant)
2. Check Policy methods for the resource
3. Verify user role/permissions via `tinker`
4. Check Gate definitions in `AuthorizationServiceProvider`
5. Check multi-tenant scope (is resource accessible to user's tenant?)

### HTTP 422 — Validation Error

1. Identify the FormRequest class for the route
2. Read validation rules
3. Test input against rules via `tinker`
4. Check if rules reference DB tables that might not exist

### Frontend / JavaScript Errors

1. Run `browser-logs` — check for JS exceptions
2. Check the React component at the error location
3. Verify props being passed from controller
4. Check Inertia shared data
5. Verify Wayfinder route references exist

### Database Errors

1. Run `last-error` — identify QueryException details
2. Run `database-schema` with table filter
3. Compare schema with migration files
4. Check if migration was run: `php artisan migrate:status`
5. Verify model `$fillable`, `$casts`, relationships

### Authentication / Authorization Errors

1. Check auth middleware on the route
2. Verify Fortify configuration
3. Check session/cookie configuration
4. Test auth state via `tinker`
5. Check `config/auth.php` guards and providers

### Multi-Tenancy Errors

1. Verify tenant middleware is applied
2. Check if model uses `BelongsToTenant` trait/scope
3. Test cross-tenant data isolation via `database-query`
4. Check pivot tables for tenant association
5. Verify tenant context is set in the request lifecycle

### Queue / Job Errors

1. Check `failed_jobs` table: `database-query` SELECT from `failed_jobs`
2. Read the job class for logic errors
3. Check queue configuration in `config/queue.php`
4. Verify job dispatching conditions
5. Check retry/timeout settings

---

## Code Analysis Patterns

### Find Related Files

```bash
# Find all files referencing a class
grep_search "ClassName" in project root

# Find route definition for a URL
grep_search "'/path/segment'" in routes/

# Find where a method is called
grep_search "->methodName(" in app/

# Find model usage
grep_search "ModelName::" in app/
```

### Git History Analysis

```bash
# Recent changes to a file
git log --oneline -10 -- path/to/file.php

# What changed in the file recently
git diff HEAD~5 -- path/to/file.php

# Who last modified the file
git log -1 --format="%an %ad" -- path/to/file.php
```
