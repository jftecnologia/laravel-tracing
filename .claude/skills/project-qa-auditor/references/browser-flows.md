# Browser Testing Flows

Phase 5 browser validation. Use the `browser_subagent` tool to navigate and verify each flow.
Use `mcp_laravel-boost_get-absolute-url` to resolve route URLs before navigating.

---

## General Instructions

1. Resolve the application base URL via `get-absolute-url`
2. For each role, log in with test credentials or seeded user
3. Navigate each critical flow and verify expected behavior
4. Record screenshots for failures
5. Check browser console for JS errors via `browser-logs`

---

## Flow Matrix by Role

### GlobalAdmin

| # | Flow | Steps | Expected |
|---|------|-------|----------|
| 1 | Login | Navigate to `/login`, enter credentials, submit | Redirect to global admin dashboard |
| 2 | Tenant listing | Navigate to tenant management | See list of all tenants |
| 3 | Tenant creation | Click create, fill form, submit | Tenant created, redirect to list |
| 4 | User management | Navigate to global user list | See users across tenants |
| 5 | Impersonation | Select tenant, impersonate | Switch to tenant context |
| 6 | Billing plans | Navigate to billing section | See plan management interface |

### CustomerAdmin

| # | Flow | Steps | Expected |
|---|------|-------|----------|
| 1 | Login | Navigate to `/login`, enter credentials | Redirect to tenant dashboard |
| 2 | Dashboard | View dashboard | See tenant-scoped metrics |
| 3 | User management | Navigate to tenant users | See only tenant users |
| 4 | Company management | Navigate to companies | CRUD operations work |
| 5 | Project listing | Navigate to projects | See tenant-scoped projects |
| 6 | Settings | Navigate to tenant settings | See branding/config options |

### Pentester

| # | Flow | Steps | Expected |
|---|------|-------|----------|
| 1 | Login | Navigate to `/login`, enter credentials | Redirect to pentester dashboard |
| 2 | Project list | View assigned projects | See only assigned projects |
| 3 | Vulnerability CRUD | Create/edit vulnerability | Form works, saves correctly |
| 4 | Evidence upload | Upload file to vulnerability | File uploads, stored non-publicly |
| 5 | Report generation | Generate project report | PDF/report generated |
| 6 | Validation queue | View pending validations | See items awaiting review |

### Client

| # | Flow | Steps | Expected |
|---|------|-------|----------|
| 1 | Login | Navigate to `/login`, enter credentials | Redirect to client dashboard |
| 2 | Dashboard | View dashboard | See company-scoped metrics |
| 3 | Project view | View assigned projects | See only company projects |
| 4 | Vulnerability view | View vulnerability details | Read-only, no edit controls |
| 5 | Report fix | Submit fix report on vulnerability | Status changes appropriately |
| 6 | Accept risk | Accept risk on vulnerability | Status changes appropriately |

---

## Cross-Cutting Validations (all roles)

During browser testing, verify for EVERY role:

- [ ] Attempting to access another tenant's URL returns 403 or 404
- [ ] Navigation matches the role (no admin links for clients)
- [ ] Logout works and redirects to login
- [ ] Session persists across page navigation
- [ ] No JavaScript console errors on any page
- [ ] Pages load without 500 errors
- [ ] Responsive layout doesn't break (check at 1024px width)
- [ ] Flash messages appear after form submissions

---

## Recording Results

For each flow tested, record:

```markdown
| Role | Flow | Result | Notes |
|------|------|--------|-------|
| GlobalAdmin | Login | ✅ PASS | — |
| GlobalAdmin | Tenant listing | ❌ FAIL | 500 error on /admin/tenants |
```

Classify results as:
- ✅ **PASS**: Flow works as expected
- ⚠️ **PARTIAL**: Flow works but with minor issues (cosmetic, UX)
- ❌ **FAIL**: Flow broken or produces errors
- ⏭️ **SKIP**: Flow not applicable (feature not yet implemented)
