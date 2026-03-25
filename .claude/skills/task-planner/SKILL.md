---
name: task-planner
description: "Plan tasks, features, or refactors from natural language descriptions. Does NOT write code — plans and routes to the correct executor. Use when the user asks to plan, analyze, or evaluate something before implementing (e.g., 'planeje a feature X', 'analise como fazer Y', 'refatore Z', 'plan the implementation of X', 'avalie a complexidade de W'). Routes to `implement-task` for execution after planning."
---

# Task Planner

Plan development tasks, features, or refactors from natural language descriptions.
Evaluate complexity, generate an implementation plan, and **delegate execution to `implement-task`**.

> **This skill does NOT implement code.** It plans and routes to the correct executor.

---

## Related References

- [references/plan-template.md](references/plan-template.md) — Output template for implementation plans
- [references/gate-checklist.md](references/gate-checklist.md) — Detailed gate validation steps
- `docs/engineering/QUALITY_GATES.md` — Quality gate definitions
- `docs/engineering/WORKFLOW.md` — Git workflow and commit guidelines

---

## 1. Workflow Overview

```text
Phase 0: Clarification → Phase 1: Context Gathering → Phase 2: Planning → Phase 3: User Approval → Delegate to implement-task
```

This skill **never writes code**. After approval, execution is delegated to `implement-task`.

---

## 2. Phase 0: Clarification (MANDATORY)

**Before ANY research or coding**, evaluate the task description for ambiguity.

### Rules

- **Never guess or assume.** If information is incomplete, ASK.
- Ask **at most 10 questions** — but only the minimum necessary.
- Group related questions into a single message.
- Questions must be specific and actionable.

### What to Clarify

- Scope boundaries (what is IN and OUT of this task)
- User roles involved (who uses this)
- Behavioral decisions (what happens when X?)
- UI expectations (new page? modal? inline? which view?)
- Data model ambiguities (new model? extend existing?)
- Multi-tenant implications (tenant-scoped? global?)
- Integration points (external APIs, queues, events?)
- Priority and urgency (is this blocking something?)

### When to Skip

Skip ONLY when the task description is fully unambiguous:

- Clear scope, roles, behavior, and data model
- Maps directly to existing patterns in the codebase
- No design decisions left open

If skipping, state: "Descrição clara, sem ambiguidades. Prosseguindo para coleta de contexto."

---

## 3. Phase 1: Context Gathering

Research the codebase, documentation, and application state to build full understanding.

### Sources to Check

#### MCP Tools

- `application-info` — PHP/Laravel versions, installed packages
- `database-schema` — Related tables and relations
- `list-routes` — Existing route patterns
- `database-query` — Inspect existing data if needed
- `search-docs` — Laravel ecosystem documentation for correct API usage

#### Project Documentation

- `docs/PRD.md` — Business context and functional requirements
- `docs/architecture/*.md` — Architecture decisions, DB schema, components
- `docs/design/*.md` — UI specs, design system, interactions (if frontend)
- `docs/engineering/*.md` — Code standards, tech stack, workflow, testing guidelines (incl. three-pillar mandate)

#### Existing Code

- Find sibling files for naming, structure, and conventions
- Check existing controllers, actions, form requests for patterns
- Identify reusable components and services

#### Structured Analysis

- Use `sequential-thinking` MCP tool to structure complex analysis when the task involves multiple interacting components or non-obvious design decisions.

---

## 4. Phase 2: Planning

Generate the implementation plan following the template in [references/plan-template.md](references/plan-template.md).

### Requirements

Every plan MUST include:

1. **Objective** — The desired end state
2. **Justification** — Why this task is needed (business value, technical debt, user need)
3. **Summary** — Implementation approach in 2-3 sentences
4. **Scope** — Explicit in/out boundaries
5. **Acceptance Criteria** — Specific, testable, observable (AC-N format)
6. **Files to Create/Modify** — Grouped by layer (backend/frontend/database/tests)
7. **Implementation Steps** — Ordered steps with rationale
8. **Patterns to Follow** — Sibling file references for each file type
9. **Test Plan** — Tests structured by the three mandatory pillars from `docs/engineering/TESTING.md` (Happy Path, Unhappy Path, Security Path)
10. **Risks & Mitigations** — What could go wrong and how to prevent it
11. **Dependencies** — External blockers or prerequisite tasks
12. **Estimated Complexity** — Low / Medium / High

### Acceptance Criteria Rules

Each AC must be:

1. **Specific**: No ambiguous terms like "works well"
2. **Testable**: Can be verified through code, tests, or manual check
3. **Observable**: Produces visible behavior or output
4. **Independent**: Can be checked without depending on other ACs

### Escalation Check

If the task is too complex (3+ new models, 10+ files, architectural decisions needed), suggest the full pipeline:

> ⚠️ Esta tarefa é complexa. Recomendo usar o pipeline completo:
>
> 1. Atualize `docs/PRD.md` com os requisitos
> 2. Execute `/generate-architecture` se necessário
> 3. Execute `/generate-task-breakdown` para quebrar em tasks
> 4. Execute `/implement-task` para cada task

---

## 5. Phase 3: User Approval

Present the plan and offer options.

### Presentation

Show the complete plan inline and ask:

> 📋 Plano de implementação pronto.
> Deseja prosseguir ou tem alterações?

### Next Step Options

Offer these options:

1. ✅ **Aprovar e executar** — Delegate to `implement-task` skill
2. ✏️ **Solicitar alterações** — Iterate on the plan
3. 💾 **Aprovar e salvar** — Save to `docs/plans/<task-name>.md`
4. 🐙 **Criar issue no GitHub** — For future implementation (`gh issue create`)

**Do NOT write code. After approval, delegate to `implement-task`.**

### GitHub Issue Format

If the user chooses to create a GitHub issue, use:

```text
Title: [type]: [short description]
Labels: enhancement (or bug, refactor, as appropriate)
Body: [Full plan content from the template]
```

---

## 6. Delegation

After user approval (option 1), instruct the user to invoke `implement-task`:

> ✅ Plano aprovado. Para implementar, use:
>
> "Implemente o plano de [task-name]"

The `implement-task` skill will:

1. Read the saved plan (or receive it inline)
2. Create semantic branch
3. Implement following CODE_STANDARDS.md
4. Run quality gates (lint, test, security)
5. Ask about Pull Request

### Skill Delegation Reference

| Need | Delegate to |
| ---- | ----------- |
| Implement the plan | `implement-task` |
| Frontend pages/components | `frontend-development` |
| Writing tests | `generate-test` |
| Auth features | `developing-with-fortify` |
| Security review | `security-analyst` |
| Complex bugs | `bug-fixer` |

---

## 7. Error Handling

### If Task Cannot Be Planned

Report clearly what is blocking and suggest alternatives.

### If Task Is Too Simple

If the task is trivial (< 2 files, no design decisions), suggest the user invoke `implement-task` directly:

> 💡 Esta tarefa é simples o suficiente para ser implementada diretamente.
> Use: "Implemente [descrição]" com a skill `implement-task`.

---

## Completion Criteria

Planning is complete when:

1. ✅ Implementation plan generated with all required sections
2. ✅ Escalation check performed (complexity assessment)
3. ✅ User has approved or saved the plan
4. ✅ Next step clearly communicated (delegate to `implement-task` or save)
