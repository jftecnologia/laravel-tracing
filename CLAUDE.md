# Claude Code – Project Guidelines

This file defines **general directions only**.
All detailed behavior must be loaded from **SKILL** and **docs/** files.

This document is intentionally small.

---

## 1. Source of Truth Hierarchy

When working on this repository, follow this order:

1. Explicit user instructions
2. Active SKILL files under `.claude/skills/**/SKILL.md`
3. Project documentation under `docs/`
4. This `CLAUDE.md`
5. Existing code patterns
6. Framework conventions

Never override a SKILL or documentation rule using assumptions.

---

## 2. Skills System

- Project behaviors are defined through **SKILL files**
- SKILL files live at the `.claude/skills/` folder:
  - `.claude/skills/generate-prd/SKILL.md`
  - `.claude/skills/fix-issue/SKILL.md`
  - etc.

Before performing a task:

- Search for a relevant SKILL
- Follow it strictly
- Do not mix responsibilities between skills

If no SKILL exists, ask before proceeding.

---

## 3. Project Documentation

- All project documentation lives under `docs/`
- Claude Code must:
  - Search `docs/` before asking questions
  - Prefer existing documents over assumptions
  - Write new documentation only inside `docs/`
  - Update and maintain documentation updated after a change, inclusion or exclusion in the requirements or architecture

### Documentation Structure

**Product Requirements:**
- `docs/PRD.md` – Complete product requirements document (features, scope, requirements)
- `docs/use-cases/*.md` – Optional detailed scenarios and integration patterns

**Architecture:**
- `docs/architecture/*.md` – System architecture, component design, data flows, extension points

**Task Breakdown:**
- `docs/tasks/*.md` – Executable development tasks organized by epics with acceptance criteria

**Engineering Documentation:**
- `docs/engineering/STACK.md` – Tech stack, dependencies, available scripts
- `docs/engineering/CODE_STANDARDS.md` – Code quality, development philosophy, tooling
- `docs/engineering/WORKFLOW.md` – Git workflow, commits, PRs, development cycle
- `docs/engineering/TESTING.md` – Testing guidelines and practices

**General:**
- `README.md` – Installation, usage, configuration (user-facing documentation)

---

## 4. Development Philosophy (High-Level)

- No DDD
- No unnecessary abstraction
- Prefer clarity over cleverness
- SOLID, pragmatically applied
- Configuration over hard-coded logic
- Extensible and plugable by default
- Fluent classes

**For complete standards and tooling, see:** `docs/engineering/CODE_STANDARDS.md`

---

## 5. Language Rules

- Code, commits, branches, PRs: **English**
- Conversation with the user: **Portuguese (pt-BR)**

---

## 6. Uncertainty Handling

If information is missing or ambiguous:

- Do not guess
- Do not invent APIs or behavior
- Ask **one clear and objective question**

---

## 7. Execution Boundary

Claude Code must NOT:

- Introduce new architectural layers without asking
- Change existing folder structures without confirmation
- Add new dependencies unless explicitly requested
- Introduce design patterns by default

When in doubt, ask.

---

## 8. Quick Reference

For specific guidance on:

| Topic | See |
|-------|-----|
| **What to build** | `docs/PRD.md` |
| **System architecture** | `docs/architecture/` |
| **Development tasks** | `docs/tasks/` |
| **Tech stack & dependencies** | `docs/engineering/STACK.md` |
| **Code quality & standards** | `docs/engineering/CODE_STANDARDS.md` |
| **Git workflow & commits** | `docs/engineering/WORKFLOW.md` |
| **Testing practices** | `docs/engineering/TESTING.md` |
| **Installation & usage** | `README.md` |

---

This file defines **direction**, not implementation.
