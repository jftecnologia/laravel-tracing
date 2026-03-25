---
name: project-qa-auditor
disable-model-invocation: true
description: Technical and functional QA auditor for the application. Validate adherence to PRD specifications, engineering standards, acceptance criteria, security requirements, and architectural constraints. Use when the user asks to run a QA audit, validate before PR, validate before deploy, check compliance, run acceptance criteria validation, verify feature implementation, or audit the application. This skill does NOT generate code — it only audits and reports.
---

# Project QA Auditor

Systematic QA audit organized in 6 deterministic phases. Produce a structured report at `docs/qa/<SEQ>-<SLUG>.md`.

---

## References

Load these files as needed during execution:

- [references/checklists.md](references/checklists.md) — Detailed checklists for Phases 1–4
- [references/browser-flows.md](references/browser-flows.md) — Browser testing flows by role for Phase 5
- [references/report-template.md](references/report-template.md) — Report template for Phase 6

---

## Constraints

- **Read-only**: Do NOT generate, modify, or delete any application code
- **Output only to**: `docs/qa/` directory
- **Role**: Auditor only — report findings, never fix them
- **Language**: Reports in English, conversation in pt-BR

---

## Audit Scope Determination

Before starting, determine the audit scope from user input:

| Trigger                                          | Scope                             | Phases                               |
| ------------------------------------------------ | --------------------------------- | ------------------------------------ |
| "full audit" / "audit completo"                  | Entire application                | All 6 phases                         |
| "validate feature X" / "valide a feature X"      | Specific feature/epic             | Phases 1, 2, 3, 4 (scoped) + 5 if UI |
| "pre-PR" / "antes do PR"                         | Changed files + related tests     | Phases 1, 3, 4                       |
| "pre-deploy"                                     | Full application                  | All 6 phases                         |
| "validate fix" / "valide a correção"             | Fix specification + affected code | Phases 1, 3, 4 (scoped)              |
| "acceptance criteria" / "critérios de aceitação" | Feature spec document             | Phases 1, 4 (AC focused)             |

For scoped audits (feature/fix), ask the user for:

1. The specification document path (if not obvious)
2. The affected files/directories (or derive from git diff)

---

## Execution Flow

```text
Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5 → Phase 6
Static     Arch.     Security   Tests      Browser    Report
```

Execute phases sequentially. Skip phases only if scope excludes them (see table above).

---

## Phase 1 — Static Analysis

Load [references/checklists.md](references/checklists.md) § Phase 1.

1. Run quality gate commands:

   ```bash
   composer lint
   npm run lint && npm run types
   ```

2. Record pass/fail and error counts
3. Validate PRD conformance (checklist §1.2)
4. Validate engineering standards (checklist §1.3)
5. Validate acceptance criteria if spec provided (checklist §1.4)
6. Validate testing standards (checklist §1.5)

Collect findings into: conformities, alerts, non-conformities.

---

## Phase 2 — Architectural Validation

Load [references/checklists.md](references/checklists.md) § Phase 2.

1. Validate multi-tenancy isolation (§2.1)
2. Validate route structure via `list-routes` MCP tool (§2.2)
3. Validate authorization policies (§2.3)
4. Validate queue configuration (§2.4)
5. Validate directory structure (§2.5)

For route validation by role, use `list-routes` with middleware filters to verify:

- Routes have appropriate role middleware
- No routes expose data cross-tenant
- Admin-only routes are properly protected

---

## Phase 3 — Security

Delegate security analysis to the **security-analyst** skill.

1. Invoke **security-analyst** on the files in scope
2. Collect the security report (findings with severity levels)
3. Classify findings using OWASP categories (A01–A10) from the report
4. Include findings in the final QA report under the Security section

The security-analyst skill handles:

- Sensitive data handling ($casts, $hidden, $visible)
- Evidence/file access validation
- Security headers and CORS
- Rate limiting
- CSRF and session config
- Auth and access control

See the security-analyst skill for the full OWASP Top 10 checklist.

---

## Phase 4 — Automated Tests

Load [references/checklists.md](references/checklists.md) § Phase 4.

1. Execute test suite:

   ```bash
   composer test
   ```

2. Record total, passed, failed, errors
3. For each failure, classify as: regression, missing implementation, environment, or flaky
4. Estimate functional coverage (§4.3):
   - Count controller actions vs feature tests
   - Count actions vs unit tests

---

## Phase 5 — Browser Testing

Load [references/browser-flows.md](references/browser-flows.md).

1. Resolve base URL via `get-absolute-url` MCP tool
2. For each role in scope, execute the flow matrix
3. Run cross-cutting validations (tenant isolation, console errors, etc.)
4. Record results in the flow table format
5. Check `browser-logs` for JavaScript errors after each page

Skip this phase if:

- Scope is "pre-PR" with no UI changes
- Application is not running locally

---

## Phase 6 — Report Generation

Load [references/report-template.md](references/report-template.md).

### File Naming

1. Check existing files in `docs/qa/` to determine next sequence number
2. Generate filename: `<SEQ>-<SLUG>.md`
   - `SEQ`: 4-digit zero-padded number (e.g., `0001`)
   - `SLUG`: kebab-case descriptor derived from scope (e.g., `full-audit`, `feature-pm-epic`, `fix-login-redirect`)
3. Example: `docs/qa/0001-full-audit.md`

### Report Assembly

Fill the template with data collected from all phases:

1. **Executive Summary**: 1-2 paragraphs with overall assessment
2. **✅ Conformities**: What passes validation
3. **⚠️ Alerts**: Minor issues, recommendations
4. **❌ Non-Conformities**: Spec violations, missing implementations
5. **🔐 Security Risks**: Classified by OWASP category
6. **🧪 Failed Tests**: With classification
7. **🌐 Browser Issues**: With role and flow context
8. **📊 Functional Coverage**: Metrics table

### Result Classification

| Result                | Criteria                                                                  |
| --------------------- | ------------------------------------------------------------------------- |
| ✅ PASS               | 0 non-conformities, 0 critical/high security risks, 0 test failures       |
| ⚠️ PASS WITH WARNINGS | 0 non-conformities but has alerts or low-severity security items          |
| ❌ FAIL               | Any non-conformity, critical/high security risk, or blocking test failure |

---

## Completion Criteria

Audit is complete when:

1. ✅ All applicable phases executed
2. ✅ Report generated at `docs/qa/<SEQ>-<SLUG>.md`
3. ✅ User informed of overall result (PASS / WARNINGS / FAIL)
4. ✅ Blocking issues clearly identified if FAIL
