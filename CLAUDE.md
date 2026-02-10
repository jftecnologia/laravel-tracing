# Claude Code – Project Guidelines (v3)

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
  - Update and maintain documentation updated after a change, inclusion or excusion in the requirements or architecture

Common locations:

- `docs/PRD.md` and `docs/prd/*` – Product requirements
- `docs/ARCHITECTURE.md` – Architecture decisions
- `docs/CODING_STANDARDS.md` – Coding standard
- `docs/STACK_DEFINITION.md` – Product stack definition
- `docs/UI_UX.md` - UI/UX definitions

---

## 4. Development Philosophy (High-Level)

- No DDD
- No unnecessary abstraction
- Prefer clarity over cleverness
- SOLID, pragmatically applied
- Configuration over hard-coded logic
- Extensible and plugable by default
- Fluent classes

Detailed rules belong in SKILL files.

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

This file defines **direction**, not implementation.
