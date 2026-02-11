# Development Task Breakdown

This directory contains executable development tasks organized by epics with clear acceptance criteria.

## Purpose

Task breakdowns bridge the gap between **what to build** (PRD) and **how to build it** (implementation):

- **Epics**: Large features grouped by functional capabilities
- **Tasks**: Specific, actionable work items (1-4 hours each)
- **Task IDs**: Unique identifiers for tracking (e.g., CTI-01, SP-02)
- **Acceptance Criteria**: Testable conditions for task completion
- **Technical Notes**: Implementation details and constraints

## Task ID System

Every task has a unique ID for tracking:

```
[EPIC_SLUG]-[NN]

Examples:
- CTI-01: First task in Core Tracing Infrastructure
- SP-02: Second task in Session Persistence
- JP-03: Third task in Job Propagation
```

### Epic Slugs

| Epic | Slug |
|------|------|
| Core Tracing Infrastructure | CTI |
| Session Persistence | SP |
| Job Propagation | JP |
| HTTP Client Integration | HCI |
| Custom Tracing Sources | CTS |
| Configuration | CFG |
| Documentation | DOC |

## Progress Tracking

**Task definitions (this directory)** are separate from **progress tracking**:

- `docs/tasks/` = What to do (immutable after creation)
- `docs/progress/` = Execution status (updated during development)

See `docs/progress/README.md` for current status and how to track progress.

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

Claude will automatically use the `generate-task-breakdown` skill to create:

- Task definitions in `docs/tasks/`
- Progress tracking in `docs/progress/`

Based on:
- `docs/PRD.md` (requirements)
- `docs/architecture/*.md` (technical design)
- `docs/use-cases/*.md` (user scenarios, if exist)
- `docs/engineering/WORKFLOW.md` (development process)

## Task Structure

Each epic document contains:

```markdown
# [Epic Name]

**Epic ID**: CTI
**Epic Goal**: Brief description of epic purpose

**Requirements Addressed**: FR-XX codes from PRD.md

**Architecture Areas**: Components from architecture docs

---

## Tasks

### [CTI-01] [Specific action-oriented name]

**Description**: What needs to be implemented and why

**Acceptance Criteria**:
- [ ] AC-1: Specific, testable criterion
- [ ] AC-2: Another testable criterion

**Technical Notes**: Implementation details

**Dependencies**:
- ⚠️ Depends on: (task IDs this depends on)
- ⚠️ Blocks: (task IDs that depend on this)

**Files to Create/Modify**: Exact file paths

**Testing**: Test requirements

---

[More tasks...]
```

## Using Tasks for Development

### Development Workflow

1. **Check progress** at `docs/progress/README.md` for current state
2. **Select epic** based on priority and dependencies
3. **Update progress**: Set task status to `IN_PROGRESS` in `docs/progress/`
4. **Implement** according to acceptance criteria
5. **Follow workflow**: `docs/engineering/WORKFLOW.md`
6. **Update progress**: Set task to `DONE`, add commit/PR references
7. **Move to next task**

### Task Completion Checklist

Before marking a task complete:

- [ ] All acceptance criteria checked
- [ ] `composer lint` passes
- [ ] `composer test` passes (if tests exist)
- [ ] Files created/modified as specified
- [ ] Technical notes addressed
- [ ] Testing performed
- [ ] Progress file updated with commit/PR references
- [ ] Ready to commit

## Epic Overview Template

When all epics are generated, this README should be updated with:

```markdown
## Epic Overview

| Epic | ID | Tasks | Complexity | Priority |
|------|-----|-------|------------|----------|
| [Core Tracing](core-tracing-infrastructure.md) | CTI | 5 | Medium | High |
| [Session Persistence](session-persistence.md) | SP | 3 | Low | High |
| ... | ... | ... | ... | ... |

## Task Reference

| Task ID | Task Name | Epic | Dependencies |
|---------|-----------|------|--------------|
| CTI-01 | Implement CorrelationIdResolver | CTI | - |
| CTI-02 | Add session persistence | CTI | CTI-01 |
| ... | ... | ... | ... |

## Development Order

1. Core Tracing Infrastructure - Foundation for all features
2. Session Persistence - Required for user session tracking
3. ...
```

**Note**: Progress status is tracked separately in `docs/progress/README.md`

## When to Update

Update task breakdowns when:

- PRD requirements change
- Architecture evolves
- New features are added to scope
- Implementation reveals missing tasks
- Priorities shift

**Never change Task IDs** - they are permanent references.

## Task Tracking

Tasks in this directory define **what** to build.  
Progress tracking is **separate** in `docs/progress/`:

- `docs/tasks/` = Task definitions (immutable)
- `docs/progress/` = Execution status (updated during development)

For current status, see: `docs/progress/README.md`

## Cross-References

Tasks reference:

- **Requirements**: `docs/PRD.md` (FR-XX codes)
- **Architecture**: `docs/architecture/*.md` (components, flows)
- **Progress**: `docs/progress/*.md` (execution status)
- **Workflow**: `docs/engineering/WORKFLOW.md` (process, quality checks)
- **Standards**: `docs/engineering/CODE_STANDARDS.md` (coding guidelines)

## Notes

- Tasks are **implementation contracts** - they define done
- Task IDs are **permanent** - never change them
- Acceptance criteria are **objective** - no ambiguous terms
- Tasks are **independent** - can be completed in any order (unless dependencies noted)
- Tasks are **testable** - success is measurable
- Progress is tracked **separately** - in `docs/progress/`
