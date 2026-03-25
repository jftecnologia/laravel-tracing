# Development Tasks

This directory contains task breakdowns organized by epic. Generate them using:

```text
/generate-task-breakdown
```

The skill reads your PRD and architecture to produce executable task files:

- One file per epic (e.g., `foundation-auth.md`, `entity-user-management.md`)
- Each task has: ID, description, acceptance criteria, dependencies, and effort estimate
- Tasks are ordered by dependency chain

> **Prerequisite**: Complete `docs/PRD.md` and `docs/architecture/` first.
