---
trigger: always_on
---

# Quality Shield (ENFORCED)

Complementary rule to `00-bootstrap.md`. Enforces impact analysis,
visual consistency, and documentation maintenance on every change.

---

## 1. Engineering Philosophy

> For full standards, see `CLAUDE.md` §4 and `docs/engineering/CODE_STANDARDS.md`.

---

## 2. Impact Analysis

Before implementing any change, ALWAYS identify:

1. **Which views/roles are affected**: identify the user roles impacted by this change
2. **Is it single-view or cross-view?** Cross-view changes require consistency validation
3. **Role-based visibility**: check RBAC policies to ensure data is scoped correctly per role
4. **Data consistency**: if the same data appears in multiple views, ensure consistent formatting and behavior

State the affected views/roles explicitly in the response summary.

> Reference: project architecture docs (`docs/architecture/`) and security docs if available.

---

## 3. Testing Standards

> For full testing standards, see `docs/engineering/TESTING.md`.
> For security testing patterns, see `docs/architecture/SECURITY.md` §1 (if available).

---

## 4. Configuration Hierarchy

> For configuration resolution order and rules, see `docs/architecture/CONFIGURATION.md` (if available).

---

## 5. i18n Enforcement

> For i18n rules, see `docs/engineering/CODE_STANDARDS.md` §i18n (if configured).

---

## 6. Visual Consistency & Theming

> **Note**: This section applies only to projects with a frontend.
> Skip entirely for API-only, CLI, or library projects.

Every UI change MUST support both dark and light modes (if configured):

### Design System Compliance

- Use the project's design system tokens — NEVER ad-hoc colors or spacing
- Reuse existing components before creating new ones
- Follow the project's component variant and styling patterns

### Checklist (for every UI change)

- [ ] Works in light mode
- [ ] Works in dark mode (if supported)
- [ ] Uses design system tokens, not ad-hoc values
- [ ] Reuses existing components where possible

> Reference: project design docs (`docs/design/`) if available.

---

## 7. Documentation Maintenance

Code changes that affect system behavior MUST include documentation updates:

| Change Type                | Update Target                                                   |
| -------------------------- | --------------------------------------------------------------- |
| Architecture / structure   | `docs/architecture/*.md`                                        |
| Roles / permissions        | `docs/architecture/SECURITY.md` (or RBAC docs if present)       |
| New features               | `docs/prd/*.md` (feature specs); keep `docs/PRD.md` as template |
| Configuration additions    | `docs/architecture/CONFIGURATION.md`                            |
| Database schema            | `docs/architecture/DATABASE.md`                                 |
| Task progress              | `docs/tasks/*.md` + `docs/progress/*.md`                        |
| Design system / components | `docs/design/*.md`                                              |
| Data flows                 | `docs/architecture/DATA_FLOW.md`                                |

**Rule**: do NOT deliver code without updating the affected documentation.
If unsure which docs are affected: list candidates and ask the user.

> Reference: `CLAUDE.md` §10

---

## 8. Regression Prevention

> For regression prevention procedures, see `docs/engineering/TESTING.md` and `docs/engineering/QUALITY_GATES.md`.
