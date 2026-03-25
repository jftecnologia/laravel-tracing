# Claude Code — Project Guidelines

**Laravel Tracing** — A Laravel package for distributed tracing. Propagates correlation IDs and request IDs across HTTP requests, queued jobs, and outgoing HTTP client calls, enabling end-to-end request tracking in microservice and multi-application architectures.
All detailed behavior is loaded from **skills** and **docs/** files.

---

## 1. Source of Truth Hierarchy

1. Explicit user instructions
2. Active skill files under `.agents/skills/**/SKILL.md`
3. Project documentation under `docs/`
4. This `CLAUDE.md`
5. Existing code patterns
6. Framework conventions

Never override a skill or documentation rule using assumptions.

---

## 2. Template Development Workflow

This template supports a structured AI-assisted development flow. For the complete lifecycle, skill routing, and quality gates, see `docs/AGENT_FLOW.md`.

0. **`/init-project`** — First-time setup: detect project context, adapt guidelines
1. **`/brainstorming`** — Structure ideas, explore options, refine specs in `docs/brainstorming/`
2. **`/generate-prd`** — Define product requirements in `docs/PRD.md`
3. **`/generate-architecture`** — Create system architecture in `docs/architecture/`
4. **`/generate-ui-design`** — Define UI/UX design specifications in `docs/design/`
5. **`/generate-task-breakdown`** — Break requirements into tasks in `docs/tasks/`
6. **`/task-planner`** — Plan and optionally implement a specific task/feature (standalone)
7. **`/implement-task`** — Implement individual tasks with quality gates

**Alternative**: **`/task-planner`** — Plan features, tasks, or refactors from natural language descriptions. Routes to `implement-task` for execution after approval.

**Operational workflows** (slash commands for common operations):

| Workflow         | Purpose                                       |
| ---------------- | --------------------------------------------- |
| `/helpme`        | Universal orchestrator — routes to best skill |
| `/full-pipeline` | Guide the full development lifecycle          |
| `/code-review`   | Pre-PR lint, test, security review            |
| `/deploy`        | Pre-deploy checklist                          |
| `/preview`       | Start dev server + browser preview            |
| `/status`        | Project progress overview                     |
| `/add-stack`     | Add/activate stack packs                      |
| `/debug`         | Structured debugging flow                     |
| `/refactor`      | Refactoring with safety checks                |
| `/test`          | Run or generate tests                         |
| `/docs`          | Generate/update documentation                 |
| `/improve-ui`    | UI/UX audit and improvement                   |
| `/i18n`          | Translation audit, fix, and sync              |

**Mandatory gates**: Security and i18n are enforced at both planning and post-code stages. See `docs/AGENT_FLOW.md` §3.

Additional skills for development:

| Skill                       | When to use                                                |
| --------------------------- | ---------------------------------------------------------- |
| `init-project`              | First setup, adapt guidelines to project context           |
| `brainstorming`             | Ideation, spec refinement, planning                        |
| `task-planner`              | Plan + route tasks/features/refactors (no code)            |
| `generate-test`             | Generating PestPHP tests (unit, feature)                   |
| `code-reviewer`             | Code health, quality, test quality, spec compliance review |
| `security-analyst`          | OWASP Top 10 security review                               |
| `project-qa-auditor`        | QA audit before PR/deploy                                  |
| `bug-fixer`                 | Fix bugs needing root cause investigation                  |
| `skill-creator`             | Creating new skills                                        |

<!-- App-only skills (not applicable to this package):
| `frontend-development`      | React + Inertia + Tailwind + Radix UI + Wayfinder          |
| `developing-with-fortify`   | Auth reference (login, 2FA, password reset)                |
| `browser-qa-tester`         | Autonomous UX/UI QA via browser subagent                   |
| `i18n-manager`              | i18n audit, fix, and translation sync                      |
| `generate-persona`          | Generate structured persona profiles                       |
| `generate-persona-feedback` | Simulate persona behavior, generate feedback               |
-->

**Stack Packs** (additive, activated by `init-project` or `/add-stack`):

| Stack Pack     | When to use                                         |
| -------------- | --------------------------------------------------- |
| `stack-node`   | Node.js/TypeScript patterns, quality gates, testing |
| `stack-python` | Python patterns, quality gates, testing             |
| `stack-go`     | Go patterns, quality gates, testing                 |

> Stack packs are **additive layers**. They add patterns and quality gates for the target stack without removing existing skills. Core skills remain available regardless of stack.

Before performing a task: search for a relevant skill, follow it strictly, do not mix responsibilities.

---

## 3. How to Find Information

### Documentation (`docs/`)

| Topic                | Location                                |
| -------------------- | --------------------------------------- |
| Product requirements | `docs/PRD.md`                           |
| Feature PRDs         | `docs/prd/*.md`                         |
| Roles & Permissions  | `docs/RBAC_GUIDE.md`                    |
| System architecture  | `docs/architecture/*.md`                |
| UI/UX design specs   | `docs/design/*.md`                      |
| Development tasks    | `docs/tasks/*.md`                       |
| Progress tracking    | `docs/progress/*.md`                    |
| Brainstorming docs   | `docs/brainstorming/*.md`               |
| Tech stack           | `docs/engineering/STACK.md`             |
| Code standards       | `docs/engineering/CODE_STANDARDS.md`    |
| Git workflow         | `docs/engineering/WORKFLOW.md`          |
| Testing guidelines   | `docs/engineering/TESTING.md`           |
| Quality gates        | `docs/engineering/QUALITY_GATES.md`     |
| Database patterns    | `docs/engineering/DATABASE_PATTERNS.md` |
| Deploy / CI          | `docs/engineering/DEPLOY.md` (TODO)     |
| Persona profiles     | `docs/personas/*.md`                    |
| Persona feedbacks    | `docs/feedbacks/**/*.md`                |
| Agent flow           | `docs/AGENT_FLOW.md`                    |

Search `docs/` before asking questions. Prefer existing documents over assumptions.

### Laravel Boost (MCP Tools)

Always use `search-docs` before making code changes to ensure correct approach. Use multiple broad queries: `['rate limiting', 'routing rate limiting', 'routing']`. Do not add package names to queries.

Other useful tools: `tinker`, `database-schema`, `database-query`, `last-error`, `browser-logs`, `list-routes`, `get-absolute-url`.

---

## 4. Development Philosophy

- No DDD — no unnecessary abstraction
- Prefer clarity over cleverness
- SOLID, pragmatically applied
- Configuration over hard-coded logic
- Extensible and pluggable by default
- Fluent classes
- Design for internationalization (i18n)

**Complete standards**: `docs/engineering/CODE_STANDARDS.md`

---

## 5. Package Structure

```text
src/
├── Facades/          # LaravelTracing facade
├── Http/             # HTTP client tracing integration
├── Jobs/             # Job tracing propagation
├── Middleware/       # Incoming/outgoing tracing middleware
├── Storage/          # Request and session storage
├── Support/          # Helpers (HeaderSanitizer, IdGenerator)
├── Tracings/         # Core tracing logic
│   ├── Contracts/    # TracingSource, TracingStorage interfaces
│   ├── Sources/      # Built-in sources (CorrelationId, RequestId)
│   └── TracingManager.php
├── LaravelTracing.php
└── LaravelTracingServiceProvider.php

config/
└── laravel-tracing.php   # Package configuration

tests/
├── Feature/          # Integration tests
├── Unit/             # Unit tests
└── Fixtures/         # Test fixtures

workbench/            # Testbench workbench app
```

Key conventions:

- **Contracts** for interfaces — `TracingSource`, `TracingStorage`
- **Sources** for pluggable tracing value providers
- **Orchestra Testbench** for testing within a Laravel app context
- **Config file** publishable via `--tag=laravel-tracing-config`

---

## 6. Quality Gates

See `docs/engineering/QUALITY_GATES.md` for the full specification. Gates run in order: Lint → Test → Security.

Quick reference — before commits:

```bash
composer lint                    # PHP: format + rector + analyze
```

Before PRs: also run `composer test`. During AI development: `vendor/bin/pint --dirty --format agent`.

---

## 7. Version Freshness

On the **first interaction** with a project, verify that documentation matches the actual project:

1. Check actual PHP, Laravel, Node, and key package versions (via `application-info` MCP tool or `composer.json`/`package.json`)
2. Compare with versions documented in `docs/engineering/STACK.md`
3. If discrepancies are found, **update the docs** to reflect reality
4. When the project specializes (e.g., adds multi-tenancy, changes auth strategy), update relevant guidelines to match

This ensures guidelines never drift from the actual project state.

---

## 8. Language Rules

- Code, commits, branches, PRs: **English**
- Conversation with the user: **Portuguese (pt-BR)**

---

## 9. Execution Boundaries

Claude Code must NOT:

- Introduce new architectural layers without asking
- Change existing folder structures without confirmation
- Add new dependencies unless explicitly requested
- Introduce design patterns by default
- Create documentation files unless explicitly requested

Communication: be concise — focus on what's important, not obvious details.

If information is missing or ambiguous: do not guess, ask **one clear question**.

> See also: `.agents/rules/03-factual-integrity.md` for anti-hallucination, evidence, and verification rules.
> See also: `.agents/rules/04-professional-conduct.md` for expert posture, no people-pleasing, and evidence-based responses.
> See also: `.agents/rules/02-transparency.md` §5-§6 for destructive action safeguards and bug fix commit protocol.

---

## 10. Documentation Maintenance

After changes that affect requirements or architecture:

- Update relevant docs in `docs/`
- Keep `docs/PRD.md` as a **template** (filled by `/generate-prd` skill)
- Write new documentation only inside `docs/`
