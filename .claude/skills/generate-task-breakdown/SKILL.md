---
name: generate-task-breakdown
description: Break down product requirements and architecture into executable development tasks. Use when the user asks to plan implementation, create a task breakdown, generate development roadmap, or organize work into epics and tasks. This skill reads PRD and architecture documentation to generate structured task lists with acceptance criteria in docs/tasks/.
---

# Task Breakdown Generator

Generate executable development tasks organized by epics with clear acceptance criteria from product requirements and architecture documentation.

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

**Epic Goal**: [One sentence describing the epic's purpose]

**Requirements Addressed**:

- FR-XX: [Requirement description]
- FR-YY: [Requirement description]

**Architecture Areas**:

- [Component or area from architecture docs]
- [Another relevant area]

---

## Tasks

### Task 1: [Action-oriented task name]

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

### Task 2: [Another task name]

[Repeat structure]

---

## Summary

**Total Tasks**: X
**Estimated Complexity**: [Low/Medium/High]
**Dependencies**: [Any external dependencies or blockers]
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

```
docs/tasks/core-tracing-infrastructure.md
docs/tasks/session-persistence.md
docs/tasks/job-propagation.md
docs/tasks/http-client-integration.md
docs/tasks/custom-tracing-sources.md
```

Use **lowercase-with-dashes.md** for task documents.

### Output Location

All task breakdowns go to:

```
docs/tasks/
├── core-tracing-infrastructure.md
├── session-persistence.md
├── job-propagation.md
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

For tasks with dependencies:

```markdown
**Dependencies**:

- ⚠️ Depends on: [Task name] from [epic file]
- ⚠️ Blocks: [Task name] from [epic file]
- ⚠️ Related to: [Task name] (can be done in parallel)
```

Use emoji ⚠️ to make dependencies visible.

---

## 16. Validation Checklist

Before completing, verify:

- [ ] All functional requirements from PRD have corresponding tasks
- [ ] Tasks are granular enough (1-4 hours each)
- [ ] All tasks have specific, testable acceptance criteria
- [ ] Epic organization is functional, not technical
- [ ] Tasks are ordered by dependencies and risk
- [ ] Testing requirements are included
- [ ] Configuration changes are documented
- [ ] Extension points are clearly defined
- [ ] Files to create/modify are listed
- [ ] Cross-references to PRD and architecture exist

---

## 17. Creating an Overview Document

After generating all epic task breakdowns, create:

**`docs/tasks/README.md`** with:

```markdown
# Development Task Breakdown

## Epic Overview

| Epic                                                          | Tasks | Complexity | Status      | Priority |
| ------------------------------------------------------------- | ----- | ---------- | ----------- | -------- |
| [Core Tracing Infrastructure](core-tracing-infrastructure.md) | 5     | Medium     | Not Started | High     |
| [Session Persistence](session-persistence.md)                 | 3     | Low        | Not Started | High     |
| ...                                                           | ...   | ...        | ...         | ...      |

## Development Order

1. [Epic name] - Reason
2. [Epic name] - Reason
3. ...

## Current Progress

- [ ] Epic 1: Core Tracing Infrastructure (0/5 tasks complete)
- [ ] Epic 2: Session Persistence (0/3 tasks complete)
- ...

## Total Effort

- **Total Epics**: X
- **Total Tasks**: Y
- **Overall Complexity**: Medium/High
```

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

1. ✅ All PRD functional requirements are covered by tasks
2. ✅ All architecture components are represented
3. ✅ Tasks are properly sized (1-4 hours each)
4. ✅ Acceptance criteria are specific and testable
5. ✅ Dependencies are documented
6. ✅ Testing requirements are included
7. ✅ Overview/README exists with epic summary
8. ✅ Files are properly named and organized in `docs/tasks/`

---

## Next Steps After Task Breakdown

Once tasks are generated, development can begin:

1. Review epic priority order
2. Select first task from highest priority epic
3. Implement according to acceptance criteria
4. Follow workflow from `docs/engineering/WORKFLOW.md`
5. Mark task complete when all ACs pass
6. Move to next task

Tasks serve as the **implementation contract** between planning and development.
