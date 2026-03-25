# Agent Flow — Development Lifecycle Orchestration

Map of the complete AI-assisted development pipeline, skill routing, and quality checkpoints.

---

## 1. Lifecycle Overview

```text
  ┌──────────────┐     ┌─────────────┐     ┌────────────────────┐     ┌─────────────────┐
  │ Brainstorming │────▶│ Generate PRD│────▶│ Generate           │────▶│ Generate UI     │
  │              │     │             │     │ Architecture       │     │ Design          │
  └──────────────┘     └─────────────┘     └────────────────────┘     └─────────────────┘
         │                                                                    │
         │  (clarity sufficient)                                              │
         ▼                                                                    ▼
  ┌──────────────┐                                                   ┌─────────────────┐
  │ Task Planner │◀──────────────────────────────────────────────────│ Generate Task   │
  │ (plan only)  │                                                   │ Breakdown       │
  └──────┬───────┘                                                   └─────────────────┘
         │                                                                    │
         ▼                                                                    ▼
  ┌──────────────┐     ┌─────────────┐     ┌────────────────────┐     ┌─────────────────┐
  │ Implement    │────▶│ Quality     │────▶│ Code Review        │────▶│ Deploy          │
  │ Task         │     │ Gates       │     │ (/code-review)     │     │ (/deploy)       │
  └──────────────┘     └─────────────┘     └────────────────────┘     └─────────────────┘
```

### Entry Points

| Starting Point          | Entry Skill/Workflow             | When                         |
| ----------------------- | -------------------------------- | ---------------------------- |
| Don't know what to do   | `/helpme` workflow               | Universal orchestrator       |
| From zero, vague idea   | `brainstorming`                  | Ideation, exploration        |
| Clear feature in mind   | `generate-prd` or `task-planner` | Requirements are known       |
| PRD already exists      | `generate-architecture`          | Architecture needed          |
| Tasks already exist     | `implement-task`                 | Ready to code                |
| Bug report              | `bug-fixer` or `/debug`          | Issue needs investigation    |
| Quick fix (known cause) | `implement-task`                 | Simple, direct fix           |
| UI looks bad            | `/improve-ui` workflow           | Visual/UX improvement needed |
| Code needs cleanup      | `/refactor` workflow             | Restructuring, tech debt     |

---

## 2. Skill Routing Table

### By User Intent

| User Intent                          | Primary Skill                                    | Supporting Skills                             |
| ------------------------------------ | ------------------------------------------------ | --------------------------------------------- |
| "Quero criar algo novo"              | `brainstorming` → `generate-prd`                 | `generate-architecture`, `generate-ui-design` |
| "Implementar feature X"              | `task-planner`                                   | `frontend-development`, `generate-test`       |
| "Corrigir bug #N"                    | `bug-fixer`                                      | `generate-test`                               |
| "Redesenhar tela Y"                  | `brainstorming` (layout)                         | `generate-ui-design`, `frontend-development`  |
| "Review de segurança"                | `security-analyst`                               | `project-qa-auditor`                          |
| "Auditar antes do PR"                | `/code-review` workflow                          | `security-analyst`, `code-reviewer`           |
| "Qual é o status?"                   | `/status` workflow                               | —                                             |
| "Preciso de testes"                  | `generate-test`                                  | —                                             |
| "Autenticação"                       | `developing-with-fortify`                        | `frontend-development`                        |
| "Traduzir / i18n"                    | `i18n-manager`                                   | —                                             |
| "Criar nova skill"                   | `skill-creator`                                  | `brainstorming` (skill)                       |
| "Não sei por onde começar"           | `/helpme` workflow or `brainstorming` (guidance) | —                                             |
| "Adicionar suporte a Node/Python/Go" | `/add-stack` workflow                            | `init-project`                                |
| "Debug / investigar bug"             | `/debug` workflow                                | `bug-fixer`                                   |
| "Refatorar módulo X"                 | `/refactor` workflow                             | `code-reviewer`, `generate-test`              |
| "Rodar testes"                       | `/test` workflow                                 | `generate-test`                               |
| "Atualizar documentação"             | `/docs` workflow                                 | `generate-architecture`, `generate-prd`       |
| "Melhorar visual / UI feia"          | `/improve-ui` workflow                           | `frontend-development`, `browser-qa-tester`   |

### Chaining Rules

- **Never run `implement-task` on complex tasks without planning first** — route through `task-planner`
- **Always run quality gates after `implement-task`** — lint, test, security
- **Always route auth features through `developing-with-fortify`** — even if user asks `implement-task`
- **Frontend changes should reference `frontend-development`** — patterns compound

---

## 3. Mandatory Gates

Security and i18n are **cross-cutting concerns** enforced at two lifecycle stages: planning (prevent flawed designs) and post-code (catch implementation issues).

### 3.1 Planning Gates

Applied during: `brainstorming`, `task-planner`, `/helpme`, `/refactor`, `/full-pipeline`

#### 🔒 Security — Threat Analysis

When planning any feature, change, or refactor:

1. **Identify attack surface** — What data, endpoints, or permissions are involved?
2. **Map OWASP risks** — Injection, broken auth, XSS, IDOR, mass assignment, etc.
3. **Define mitigations** — Specific countermeasures for each identified risk
4. **Flag in the plan** — Each mitigation must appear as a requirement, not a suggestion

```markdown
### Security Considerations (MANDATORY in plans)

| Risk                  | OWASP Category           | Mitigation                         | Priority |
| --------------------- | ------------------------ | ---------------------------------- | -------- |
| User input → DB query | A03:2021 Injection       | Use Eloquent/parameterized queries | 🔴       |
| File upload           | A04:2021 Insecure Design | Validate type, size, scan content  | 🔴       |
```

#### 🌐 i18n — Translation Planning

When planning any feature that has user-facing output:

1. **Identify user-facing strings** — Labels, messages, errors, notifications, emails
2. **Define translation keys** — Propose key naming following project conventions
3. **Flag in the plan** — List new translation keys needed

```markdown
### i18n Considerations (MANDATORY in plans)

| Component         | Strings Count | Translation Keys               |
| ----------------- | ------------- | ------------------------------ |
| Form labels       | 5             | `vulnerabilities.form.*`       |
| Flash messages    | 3             | `vulnerabilities.messages.*`   |
| Validation errors | 4             | Use Laravel's `validation.php` |
```

### 3.2 Post-Code Gates

Applied after: `implement-task`, `bug-fixer`, `/debug`, `/refactor`, `/improve-ui`, `/test`, any workflow that produces code.

```text
Code Change ──▶ Lint ──▶ Test (3 pillars) ──▶ i18n Check ──▶ Security Scan ──▶ Commit
```

> **Full specification**: See `docs/engineering/QUALITY_GATES.md` for detailed gate sequence, commands, failure handling, and stack-specific instructions.

### 3.3 Pre-PR

```text
All Commits ──▶ /code-review ──▶ PR
```

- Full lint + test suite
- Code quality review
- Security review on changed files
- i18n sync verification

### 3.4 Pre-Deploy

```text
PR Merged ──▶ /deploy ──▶ Production
```

- Quality gates pass
- Translations sync verified
- Build success
- Migrations reviewed

---

## 4. Cross-Cutting Concerns

### Summary Table

| Concern      | Planning Phase                           | Post-Code Phase                    | Standalone         |
| ------------ | ---------------------------------------- | ---------------------------------- | ------------------ |
| **Security** | Threat analysis + mitigations in plan    | Scan changed files for OWASP risks | `security-analyst` |
| **i18n**     | Identify strings + plan translation keys | Verify `t()`/`__()` usage          | `/i18n` workflow   |
| **Tests**    | Define test scenarios in plan            | Run 3-pillar tests                 | `/test` workflow   |
| **Docs**     | —                                        | Update affected docs               | `/docs` workflow   |
| **Progress** | —                                        | Update progress tracking           | `/status` workflow |

### Workflow Reference

All workflows and skills that produce code MUST reference the Mandatory Gates:

> ⚠️ **Before finalizing**: execute Post-Code Gates (see `AGENT_FLOW.md` §3.2)

All workflows and skills that plan code MUST include planning gates:

> ⚠️ **During planning**: include Security and i18n considerations (see `AGENT_FLOW.md` §3.1)

### Gap Detection

When any skill detects that a required stack pack is missing:

1. Check `.agents/skills/stack-*` for the relevant pack
2. If missing, suggest: `> ⚠️ Stack pack not found. Run /add-stack to activate.`
3. Do NOT block — continue with best-effort using core skills

---

## 5. Skill vs Workflow Disambiguation

Skills are specialized executors. Workflows are orchestrators that may invoke skills. When both exist for a similar domain:

| Domain      | Workflow       | Skill           | Use Workflow when...                            | Use Skill when...                                |
| ----------- | -------------- | --------------- | ----------------------------------------------- | ------------------------------------------------ |
| Debugging   | `/debug`       | `bug-fixer`     | Bug is vague, needs investigation, no ticket    | Clear ticket/issue exists, known root cause area |
| Code Review | `/code-review` | `code-reviewer` | Pre-PR review (lint + test + security + review) | Deep code health audit only (no lint/test run)   |
| i18n        | `/i18n`        | `i18n-manager`  | Quick audit/fix flow                            | Full sync, detailed scan, programmatic use       |
| Refactoring | `/refactor`    | `task-planner`  | Code restructuring with safety checks           | Planning-only before executing                   |

> **Rule**: When in doubt, prefer the **workflow** — it provides the orchestration layer and calls the skill internally.
