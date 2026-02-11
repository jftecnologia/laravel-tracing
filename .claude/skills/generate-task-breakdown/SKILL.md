---
name: generate-task-breakdown
description: Break down product requirements and architecture into executable development tasks. Use when the user asks to plan implementation, create a task breakdown, generate development roadmap, or organize work into epics and tasks. This skill reads PRD and architecture documentation to generate structured task lists with acceptance criteria in docs/tasks/ and creates progress tracking in docs/progress/.
---

# Task Breakdown Generator

Generate executable development tasks organized by epics with clear acceptance criteria from product requirements and architecture documentation. Track progress in dedicated progress files.

---

## 1. Load Context

Read the following files in order to understand what needs to be built:

1. **Product Requirements**: `docs/PRD.md`
    - Functional requirements (FR-01, FR-02, etc.)
    - Non-functional requirements
    - Scope (in/out)
    - User flows

2. **Architecture**: `docs/architecture/*.md`
    - Component design
    - Data flows
    - Extension points
    - Technical decisions

3. **Use Cases** (if exist): `docs/use-cases/*.md`
    - Specific user scenarios
    - Edge cases
    - Integration patterns

4. **Workflow Guidelines**: `docs/engineering/WORKFLOW.md`
    - Development process
    - Testing requirements
    - Quality standards

5. **Existing Progress** (if exist): `docs/progress/*.md`
    - Current task status
    - What needs to be resumed
    - Previous blockers or failures

---

## 2. Clarify Missing Information

If requirements or architecture are unclear:

- Identify the specific ambiguity
- Ask **up to five clear questions** in Portuguese (pt-BR)
- Do **not** create tasks for unclear requirements
- Suggest updating PRD or architecture documentation if gaps exist

---

## 3. Task Breakdown Structure

### Epic Definition

An **Epic** is a large feature or capability that:

- Delivers complete user-facing functionality
- Can span multiple development sessions
- Maps to one or more functional requirements
- Has clear business value

### Task Definition

A **Task** is a specific, actionable work item that:

- Can be completed in one development session (1-4 hours)
- Has clear acceptance criteria
- Can be tested independently
- Produces tangible output (code, config, test, doc)

---

## 4. Document Structure

Each task breakdown document follows this structure:

```markdown
# [Epic Name]

**Epic ID**: [SLUG] (e.g., CTI, SP, JP)
**Epic Goal**: [One sentence describing the epic's purpose]

**Requirements Addressed**:

- FR-XX: [Requirement description]
- FR-YY: [Requirement description]

**Architecture Areas**:

- [Component or area from architecture docs]
- [Another relevant area]

---

## Tasks

### [SLUG-01] [Action-oriented task name]

**Description**:
[1-2 paragraphs explaining what needs to be implemented and why]

**Acceptance Criteria**:

- [ ] AC-1: [Specific, testable criterion]
- [ ] AC-2: [Specific, testable criterion]
- [ ] AC-3: [Specific, testable criterion]

**Technical Notes**:

- Implementation detail or constraint
- Reference to architecture component
- Dependencies on other tasks (if any)

**Files to Create/Modify**:

- `src/Path/To/Class.php`
- `config/laravel-tracing.php`
- `tests/Feature/ExampleTest.php`

**Testing**:

- [ ] Unit tests (if requested or complex logic)
- [ ] Feature tests (if applicable)
- [ ] Manual testing steps (if needed)

---

### [SLUG-02] [Another task name]

[Repeat structure]

---

## Summary

**Total Tasks**: X
**Estimated Complexity**: [Low/Medium/High]
**Dependencies**: [Any external dependencies or blockers]
```

---

## 4.1 Task ID System

Every task must have a unique ID for tracking:

### ID Format

```
[EPIC_SLUG]-[NN]

Where:
- EPIC_SLUG: 2-4 uppercase letters derived from epic name
- NN: Two-digit sequential number (01, 02, etc.)
```

### Epic Slug Examples

| Epic Name                   | Slug |
| --------------------------- | ---- |
| Core Tracing Infrastructure | CTI  |
| Session Persistence         | SP   |
| Job Propagation             | JP   |
| HTTP Client Integration     | HCI  |
| Custom Tracing Sources      | CTS  |
| Configuration               | CFG  |
| Documentation               | DOC  |

### Task ID Examples

- `CTI-01`: First task in Core Tracing Infrastructure
- `CTI-02`: Second task in Core Tracing Infrastructure
- `SP-01`: First task in Session Persistence
- `JP-03`: Third task in Job Propagation

**IDs are permanent** - once assigned, they don't change even if task order changes.

---

## 4.2 Progress Tracking System

Progress tracking is **separate** from task definitions:

- **`docs/tasks/`** = What to do (planning, immutable after creation)
- **`docs/progress/`** = Execution status (tracking, updated during development)

### Progress File Structure

For each epic in `docs/tasks/`, create a corresponding progress file in `docs/progress/`:

```
docs/tasks/core-tracing-infrastructure.md    → docs/progress/core-tracing-infrastructure.md
docs/tasks/session-persistence.md            → docs/progress/session-persistence.md
docs/tasks/job-propagation.md                → docs/progress/job-propagation.md
```

### Progress Document Template

```markdown
# Progress: [Epic Name]

**Epic ID**: [SLUG]
**Task Definition**: [../tasks/epic-name.md](../tasks/epic-name.md)
**Last Updated**: YYYY-MM-DD HH:MM

---

## Status Summary

| Task ID | Task Name                       | Status | Commit/PR |
| ------- | ------------------------------- | ------ | --------- |
| CTI-01  | Implement CorrelationIdResolver | TODO   | -         |
| CTI-02  | Add session persistence         | TODO   | -         |
| CTI-03  | Create TracingMiddleware        | TODO   | -         |

**Progress**: 0/3 tasks complete

---

## Task Details

### CTI-01: Implement CorrelationIdResolver

**Status**: `TODO`
**Started**: -
**Completed**: -
**Commit**: -
**PR**: -

**Notes**:

- (none)

**Blockers**:

- (none)

---

### CTI-02: Add session persistence

**Status**: `TODO`
**Started**: -
**Completed**: -
**Commit**: -
**PR**: -

**Notes**:

- (none)

**Blockers**:

- (none)

---

[Repeat for all tasks...]

---

## Epic Notes

(General notes about this epic's progress, decisions made, issues encountered)
```

### Task Status Values

| Status        | Description                 | When to Use                               |
| ------------- | --------------------------- | ----------------------------------------- |
| `TODO`        | Not started                 | Task has not been worked on yet           |
| `IN_PROGRESS` | Currently working           | Active development in progress            |
| `DONE`        | Completed successfully      | All ACs pass, code committed              |
| `FAILED`      | Failed, needs retry         | Implementation failed, needs new approach |
| `BLOCKED`     | Waiting for decision/action | Cannot proceed without external input     |

### Status Transitions

```
TODO → IN_PROGRESS → DONE
                  ↘ FAILED → TODO (retry)
                  ↘ BLOCKED → TODO (when unblocked)
```

### Updating Progress

When starting a task:

```markdown
**Status**: `IN_PROGRESS`
**Started**: 2026-02-10 14:30
```

When completing a task:

```markdown
**Status**: `DONE`
**Completed**: 2026-02-10 16:45
**Commit**: abc1234
**PR**: #42
```

When a task fails:

```markdown
**Status**: `FAILED`
**Notes**:

- Attempted approach X but failed because Y
- Need to try approach Z instead
- Related issue: Session not available in middleware
```

When a task is blocked:

```markdown
**Status**: `BLOCKED`
**Blockers**:

- ⚠️ Need decision: Should we use cookies or Laravel session for persistence?
- ⚠️ Waiting for: Architecture clarification on job serialization format
```

---

## 4.3 Progress Overview Document

Create `docs/progress/README.md` with overall tracking:

```markdown
# Development Progress

**Last Updated**: YYYY-MM-DD HH:MM

## Epic Progress

| Epic                                  | Total  | TODO   | In Progress | Done  | Failed | Blocked | Progress |
| ------------------------------------- | ------ | ------ | ----------- | ----- | ------ | ------- | -------- |
| [CTI](core-tracing-infrastructure.md) | 5      | 3      | 1           | 1     | 0      | 0       | 20%      |
| [SP](session-persistence.md)          | 3      | 3      | 0           | 0     | 0      | 0       | 0%       |
| [JP](job-propagation.md)              | 4      | 4      | 0           | 0     | 0      | 0       | 0%       |
| **Total**                             | **12** | **10** | **1**       | **1** | **0**  | **0**   | **8%**   |

## Current Focus

**Active Task**: CTI-02 - Add session persistence
**Epic**: Core Tracing Infrastructure
**Started**: 2026-02-10 14:30

## Blocked Items

| Task ID | Blocker | Since |
| ------- | ------- | ----- |
| (none)  | -       | -     |

## Failed Items (Need Retry)

| Task ID | Reason | Failed At |
| ------- | ------ | --------- |
| (none)  | -      | -         |

## Recent Completions

| Task ID | Task Name                       | Completed  | Commit  |
| ------- | ------------------------------- | ---------- | ------- |
| CTI-01  | Implement CorrelationIdResolver | 2026-02-10 | abc1234 |

## Notes

(Overall progress notes, decisions, blockers affecting multiple tasks)
```

---

## 5. Task Naming Convention

Use action-oriented, specific names:

✅ **Good**:

- "Implement CorrelationIdResolver class"
- "Add session persistence for correlation ID"
- "Create TracingMiddleware with header extraction"
- "Configure HTTP client tracing integration"

❌ **Bad**:

- "Correlation ID feature"
- "Fix middleware"
- "Update code"
- "Implement tracing"

---

## 6. Acceptance Criteria Rules

Each acceptance criterion must be:

1. **Specific**: No ambiguous terms like "works well" or "is good"
2. **Testable**: Can be verified through code, tests, or manual check
3. **Observable**: Produces visible behavior or output
4. **Independent**: Can be checked without depending on other ACs

**Format**: Use checkbox list with AC-X numbering

✅ **Good ACs**:

- [ ] AC-1: CorrelationIdResolver class is created in `src/Resolvers/` namespace
- [ ] AC-2: Class has `resolve(Request $request): string` public method
- [ ] AC-3: Method returns UUID when no correlation ID exists in request
- [ ] AC-4: Method preserves existing correlation ID from session
- [ ] AC-5: `composer lint` passes with no errors

❌ **Bad ACs**:

- [ ] Code is clean
- [ ] Correlation ID works
- [ ] Tests are added
- [ ] Documentation is good

---

## 7. Task Granularity Guidelines

### Too Large (Split into multiple tasks):

- "Implement entire tracing system"
- "Build middleware and all resolvers"
- "Add all configuration options"

### Just Right:

- "Create CorrelationIdResolver class with session persistence"
- "Add TracingMiddleware to register headers on incoming requests"
- "Implement config file with default tracing sources"

### Too Small (Combine with related work):

- "Import Str facade"
- "Add docblock to method"
- "Format code with Pint"

---

## 8. Task Ordering

Order tasks by:

1. **Foundation first**: Core classes, interfaces, service provider
2. **Features second**: Middleware, resolvers, facades
3. **Integration third**: HTTP client, job propagation
4. **Extensions last**: Custom tracing sources, replaceability

Within each category, order by:

- Dependencies (what depends on what)
- Risk (tackle risky/unknown items early)
- Value (high-value features first)

---

## 9. Epic Organization

Group tasks into epics based on:

**Functional grouping** (preferred):

- Epic: Core Tracing Infrastructure
- Epic: Session Persistence
- Epic: Job Propagation
- Epic: HTTP Client Integration
- Epic: Custom Tracing Sources

**NOT by technical layer**:
❌ Epic: Models
❌ Epic: Controllers
❌ Epic: Tests

---

## 10. File Output

### File Naming

Use semantic slugs matching epic name:

**Task definitions** (lowercase-with-dashes.md):

```
docs/tasks/core-tracing-infrastructure.md
docs/tasks/session-persistence.md
docs/tasks/job-propagation.md
docs/tasks/http-client-integration.md
docs/tasks/custom-tracing-sources.md
```

**Progress tracking** (same slugs):

```
docs/progress/core-tracing-infrastructure.md
docs/progress/session-persistence.md
docs/progress/job-propagation.md
docs/progress/http-client-integration.md
docs/progress/custom-tracing-sources.md
```

### Output Location

```
docs/tasks/                              # Task definitions (what to do)
├── README.md                            # Epic overview
├── core-tracing-infrastructure.md
├── session-persistence.md
└── ...

docs/progress/                           # Progress tracking (execution status)
├── README.md                            # Overall progress dashboard
├── core-tracing-infrastructure.md
├── session-persistence.md
└── ...
```

---

## 11. Cross-Referencing

Each task document must reference:

- **PRD Requirements**: FR-XX codes from `docs/PRD.md`
- **Architecture Components**: Specific files/classes from `docs/architecture/`
- **Related Tasks**: Dependencies between task documents
- **Workflow Standards**: Reference to quality checks from `docs/engineering/WORKFLOW.md`

---

## 12. Testing Requirements (from WORKFLOW.md)

Every task with code changes must include:

```markdown
**Testing**:

- [ ] `composer lint` passes (mandatory)
- [ ] `composer test` passes (if tests exist)
- [ ] Feature tests added (if new user-facing behavior)
- [ ] Unit tests added (if complex logic)
- [ ] Manual testing performed (describe steps)
```

---

## 13. Special Task Types

### Configuration Task

For tasks adding configuration:

```markdown
**Configuration Changes**:

- Config key: `laravel-tracing.sources.correlation_id.enabled`
- Environment variable: `LARAVEL_TRACING_CORRELATION_ID_ENABLED`
- Default value: `true`
- Validation: Must be boolean
```

### Extensibility Task

For tasks adding extension points:

```markdown
**Extension Point**:

- Contract/Interface: `TracingSourceContract`
- Required methods: `resolve(Request $request): string`
- Registration: Via config `laravel-tracing.sources.custom`
- Example: See `docs/architecture/EXTENSIONS.md`
```

### Documentation Task

For tasks adding user documentation:

```markdown
**Documentation Updates**:

- [ ] Update `README.md` with installation steps
- [ ] Add configuration example
- [ ] Document extension mechanism
- [ ] Add troubleshooting section
```

---

## 14. Complexity Estimation

Rate each epic's complexity:

- **Low**: Well-understood, straightforward implementation, few edge cases
- **Medium**: Some complexity, minor unknowns, standard Laravel patterns
- **High**: Complex logic, many edge cases, new patterns, external dependencies

Use this to prioritize and plan development time.

---

## 15. Dependency Tracking

For tasks with dependencies, use Task IDs:

```markdown
**Dependencies**:

- ⚠️ Depends on: CTI-01 (CorrelationIdResolver must exist first)
- ⚠️ Blocks: SP-02, JP-01 (these tasks wait for this one)
- ⚠️ Related to: CTI-03 (can be done in parallel)
```

Use emoji ⚠️ to make dependencies visible.

### Dependency Rules

- Always reference by Task ID (e.g., `CTI-01`), not by task name only
- Document both directions: what this task depends on AND what it blocks
- Mark parallel-safe tasks as "Related to"

---

## 16. Validation Checklist

Before completing, verify:

**Task Definitions:**

- [ ] All functional requirements from PRD have corresponding tasks
- [ ] Tasks are granular enough (1-4 hours each)
- [ ] All tasks have unique IDs (EPIC_SLUG-NN format)
- [ ] All tasks have specific, testable acceptance criteria
- [ ] Epic organization is functional, not technical
- [ ] Tasks are ordered by dependencies and risk
- [ ] Testing requirements are included
- [ ] Configuration changes are documented
- [ ] Extension points are clearly defined
- [ ] Files to create/modify are listed
- [ ] Cross-references to PRD and architecture exist

**Progress Tracking:**

- [ ] Progress file created for each epic
- [ ] All tasks listed in progress status summary table
- [ ] Progress README.md created with overall dashboard
- [ ] All task statuses initialized to `TODO`

---

## 17. Creating Overview Documents

After generating all epic task breakdowns, create two overview documents:

### Task Overview: `docs/tasks/README.md`

```markdown
# Development Task Breakdown

## Epic Overview

| Epic                                                          | ID  | Tasks | Complexity | Priority |
| ------------------------------------------------------------- | --- | ----- | ---------- | -------- |
| [Core Tracing Infrastructure](core-tracing-infrastructure.md) | CTI | 5     | Medium     | High     |
| [Session Persistence](session-persistence.md)                 | SP  | 3     | Low        | High     |
| ...                                                           | ... | ...   | ...        | ...      |

## Task Reference

| Task ID | Task Name                       | Epic                | Dependencies |
| ------- | ------------------------------- | ------------------- | ------------ |
| CTI-01  | Implement CorrelationIdResolver | Core Tracing        | -            |
| CTI-02  | Add session persistence         | Core Tracing        | CTI-01       |
| SP-01   | Configure session storage       | Session Persistence | CTI-02       |
| ...     | ...                             | ...                 | ...          |

## Development Order

1. [Epic name] - Reason
2. [Epic name] - Reason
3. ...

## Total Effort

- **Total Epics**: X
- **Total Tasks**: Y
- **Overall Complexity**: Medium/High
```

### Progress Overview: `docs/progress/README.md`

See section 4.3 for template. This document tracks:

- Epic-level progress percentages
- Currently active task
- Blocked and failed items
- Recent completions
- Overall notes

---

## 18. Language Rules

- **Task documents**: English (tasks, ACs, technical notes)
- **Clarifying questions**: Portuguese (pt-BR)

---

## 19. Avoid Common Mistakes

❌ **Don't**:

- Create tasks for reading documentation
- Create tasks for running `composer lint` or `composer test` (these are implicit)
- Write vague ACs like "works correctly" or "is implemented"
- Create tasks for every single class method
- Group unrelated work into one large task
- Forget to reference PRD requirements

✅ **Do**:

- Focus on deliverable functionality
- Make ACs specific and testable
- Keep tasks focused and independent
- Reference architecture decisions
- Include configuration and extensibility
- Document dependencies explicitly

---

## Completion Criteria

Task breakdown is complete when:

**Task Definitions:**

1. ✅ All PRD functional requirements are covered by tasks
2. ✅ All architecture components are represented
3. ✅ Tasks are properly sized (1-4 hours each)
4. ✅ All tasks have unique IDs (EPIC_SLUG-NN format)
5. ✅ Acceptance criteria are specific and testable
6. ✅ Dependencies are documented with Task IDs
7. ✅ Testing requirements are included
8. ✅ Task overview/README exists with epic summary

**Progress Tracking:** 9. ✅ Progress file exists for each epic in `docs/progress/` 10. ✅ Progress README.md exists with overall dashboard 11. ✅ All tasks initialized with `TODO` status

---

## Next Steps After Task Breakdown

Once tasks and progress tracking are generated, development can begin:

### Starting Development

1. Review `docs/progress/README.md` for overall status
2. Select first `TODO` task from highest priority epic
3. Update task status to `IN_PROGRESS` in progress file
4. Implement according to acceptance criteria in task definition
5. Follow workflow from `docs/engineering/WORKFLOW.md`

### Completing a Task

1. Verify all acceptance criteria pass
2. Run `composer lint` and `composer test`
3. Commit with semantic message referencing Task ID
4. Update progress file:
    - Set status to `DONE`
    - Add commit hash
    - Add PR number (if applicable)
    - Update timestamps
5. Update `docs/progress/README.md` percentages
6. Move to next task

### Handling Failures

1. Update task status to `FAILED`
2. Document what went wrong in **Notes**
3. Identify if it's retryable or needs architecture change
4. Reset to `TODO` when ready to retry

### Handling Blockers

1. Update task status to `BLOCKED`
2. Document blocker in **Blockers** section with ⚠️
3. Add to Blocked Items table in `docs/progress/README.md`
4. Ask user for decision/clarification
5. Reset to `TODO` when blocker is resolved

### Resuming Development

When resuming after a break:

1. Check `docs/progress/README.md` for current state
2. Look for any `IN_PROGRESS` tasks (may need to continue or reset)
3. Check `BLOCKED` and `FAILED` items for pending issues
4. Continue with next appropriate task

---

## Progress Tracking Commands

Use these phrases to trigger progress updates:

- "Iniciar task CTI-01" → Sets task to IN_PROGRESS
- "Concluir task CTI-01" → Sets task to DONE, prompts for commit
- "Task CTI-01 falhou" → Sets task to FAILED, prompts for notes
- "Task CTI-01 bloqueada" → Sets task to BLOCKED, prompts for blocker
- "Status do progresso" → Shows current progress overview

---

Tasks serve as the **implementation contract** between planning and development.
Progress tracking provides **visibility and recoverability** for long-running work.
