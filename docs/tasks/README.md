# Development Task Breakdown

This directory contains executable development tasks organized by epics with clear acceptance criteria.

## Purpose

Task breakdowns bridge the gap between **what to build** (PRD) and **how to build it** (implementation):

- **Epics**: Large features grouped by functional capabilities
- **Tasks**: Specific, actionable work items (1-4 hours each)
- **Acceptance Criteria**: Testable conditions for task completion
- **Technical Notes**: Implementation details and constraints

## Document Naming

Task documents use **lowercase-with-dashes.md** naming matching the epic name:

- `core-tracing-infrastructure.md`
- `session-persistence.md`
- `job-propagation.md`
- `http-client-integration.md`
- `custom-tracing-sources.md`

## Creating Task Breakdowns

To generate task breakdowns, ask Claude Code to:

```
"Gere o planejamento de tasks do projeto"
```

or

```
"Crie o breakdown de desenvolvimento baseado no PRD"
```

Claude will automatically use the `generate-task-breakdown` skill to create comprehensive task breakdowns based on:

- `docs/PRD.md` (requirements)
- `docs/architecture/*.md` (technical design)
- `docs/use-cases/*.md` (user scenarios, if exist)
- `docs/engineering/WORKFLOW.md` (development process)

## Task Structure

Each epic document contains:

```markdown
# [Epic Name]

**Epic Goal**: Brief description of epic purpose

**Requirements Addressed**: FR-XX codes from PRD.md

**Architecture Areas**: Components from architecture docs

---

## Tasks

### Task 1: [Specific action-oriented name]

**Description**: What needs to be implemented and why

**Acceptance Criteria**:
- [ ] AC-1: Specific, testable criterion
- [ ] AC-2: Another testable criterion

**Technical Notes**: Implementation details

**Files to Create/Modify**: Exact file paths

**Testing**: Test requirements

---

[More tasks...]
```

## Using Tasks for Development

### Development Workflow

1. **Select epic** based on priority and dependencies
2. **Pick first task** from epic
3. **Implement** according to acceptance criteria
4. **Follow workflow**: `docs/engineering/WORKFLOW.md`
5. **Mark complete** when all ACs pass
6. **Move to next task**

### Task Completion Checklist

Before marking a task complete:

- [ ] All acceptance criteria checked
- [ ] `composer lint` passes
- [ ] `composer test` passes (if tests exist)
- [ ] Files created/modified as specified
- [ ] Technical notes addressed
- [ ] Testing performed
- [ ] Ready to commit

## Epic Overview Template

When all epics are generated, this README should be updated with:

```markdown
## Epic Overview

| Epic | Tasks | Complexity | Status | Priority |
|------|-------|------------|--------|----------|
| [Core Tracing](core-tracing-infrastructure.md) | 5 | Medium | Not Started | High |
| ... | ... | ... | ... | ... |

## Development Order

1. Core Tracing Infrastructure - Foundation for all features
2. Session Persistence - Required for user session tracking
3. ...

## Current Progress

- [ ] Epic 1: Core Tracing Infrastructure (0/5 tasks complete)
- [ ] Epic 2: Session Persistence (0/3 tasks complete)
- ...
```

## When to Update

Update task breakdowns when:

- PRD requirements change
- Architecture evolves
- New features are added to scope
- Implementation reveals missing tasks
- Priorities shift

## Task Tracking

Tasks in this directory define **what** to build.  
Progress tracking happens through:

- Git commits (follow semantic commits from WORKFLOW.md)
- Task completion checkboxes in epic documents
- Epic overview table in this README

## Cross-References

Tasks reference:

- **Requirements**: `docs/PRD.md` (FR-XX codes)
- **Architecture**: `docs/architecture/*.md` (components, flows)
- **Workflow**: `docs/engineering/WORKFLOW.md` (process, quality checks)
- **Standards**: `docs/engineering/CODE_STANDARDS.md` (coding guidelines)

## Notes

- Tasks are **implementation contracts** - they define done
- Acceptance criteria are **objective** - no ambiguous terms
- Tasks are **independent** - can be completed in any order (unless dependencies noted)
- Tasks are **testable** - success is measurable
