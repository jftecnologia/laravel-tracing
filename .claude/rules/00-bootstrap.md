---
trigger: always_on
---

# Project Bootstrap (MANDATORY)

At the start of EVERY task (including follow-ups and refactors):

## Fast Path (Trivial Tasks)

If the user's request is trivial (a question, single-line fix, renaming, or simple clarification):

- Skip skill/doc discovery
- Apply `CLAUDE.md` standards directly
- Still follow language and code standards
- Still declare guidance source in summary

## Standard Path

1. Open and strictly follow `CLAUDE.md` located at the repository root.
   This file is mandatory and authoritative for this workspace.

2. Apply the Source of Truth Hierarchy defined in `CLAUDE.md`.

3. Discover context dynamically:
   - Search `.agents/skills/**/SKILL.md` using broad task-related keywords
   - Search `docs/` using semantic relevance (never rely on hardcoded filenames)
   - Prefer existing project documents and skills over assumptions

4. Skill-first execution rule:
   - Before implementing any task, actively check if a relevant skill exists
   - If a matching skill is found, follow it strictly
   - **Skill composition**: if the skill has `dependencies` in its YAML frontmatter, also load the listed skills/docs
   - Do NOT mix multiple skills unless explicitly required or listed as dependencies
   - If no skill is found, fallback to CLAUDE.md + existing code patterns

5. Framework validation:
   - Use available documentation search or MCP tools (e.g., search-docs)
   - Prefer native framework/stack approaches over custom abstractions

> **Note (API-only projects)**: If the project has no frontend (no `resources/js/`, no React/Vue),
> skip frontend-related rules (design system, visual consistency, component reuse) and
> focus on API patterns, serialization, and backend validation.

---

# Audit Micro-Rule (TRACEABILITY - REQUIRED)

Before modifying, creating, or deleting ANY file, the agent MUST explicitly state in the response summary:

- Which skill was used (file path), OR
- Which documentation source was used (docs path or semantic description), OR
- If none was found:

  "No matching skill or documentation found; proceeding with CLAUDE.md and existing code patterns."

Never perform blind changes without identifying the guidance source.

> See also: `02-transparency.md` for full decision justification and spec conflict detection rules.

---

# Companion Rules

This rule is complemented by:

- **`01-quality-shield.md`** — View impact analysis, visual consistency, documentation maintenance
- **`02-transparency.md`** — Decision justification, spec conflict detection, proactive improvement suggestions

---

# Shared Standards (defined in `CLAUDE.md`)

The following standards are defined once in `CLAUDE.md` and apply globally:

- Source of Truth Hierarchy (§1)
- Quality Gates (§6)
- Language Rules (§8)
- Execution Boundaries (§9)
