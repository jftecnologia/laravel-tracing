# Progress Tracking

This directory tracks implementation progress for each epic defined in `docs/tasks/`.

## Format

Each epic gets a progress file (e.g., `foundation-auth.md`) with:

```markdown
# Epic Name — Progress

| Task ID | Description | Status      | Commit | Notes |
| ------- | ----------- | ----------- | ------ | ----- |
| FA-01   | Task name   | DONE        | abc123 | ...   |
| FA-02   | Task name   | IN_PROGRESS | —      | ...   |
| FA-03   | Task name   | TODO        | —      | ...   |
```

## Status Values

- `TODO` — Not started
- `IN_PROGRESS` — Currently being worked on
- `DONE` — Completed with commit hash
- `BLOCKED` — Blocked by dependency

> Progress files are created and updated by the `implement-task` skill.
