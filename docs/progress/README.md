# Development Progress

**Purpose**: Track execution status of development tasks defined in `docs/tasks/`.

**Last Updated**: -

---

## Epic Progress

| Epic | ID | Total | TODO | In Progress | Done | Failed | Blocked | Progress |
|------|-----|-------|------|-------------|------|--------|---------|----------|
| (no epics yet) | - | - | - | - | - | - | - | - |
| **Total** | - | **0** | **0** | **0** | **0** | **0** | **0** | **0%** |

---

## Current Focus

**Active Task**: (none)
**Epic**: -
**Started**: -

---

## Blocked Items

| Task ID | Blocker | Since |
|---------|---------|-------|
| (none) | - | - |

---

## Failed Items (Need Retry)

| Task ID | Reason | Failed At |
|---------|--------|-----------|
| (none) | - | - |

---

## Recent Completions

| Task ID | Task Name | Completed | Commit |
|---------|-----------|-----------|--------|
| (none) | - | - | - |

---

## Notes

(Overall progress notes, decisions, blockers affecting multiple tasks)

---

## How to Use This Directory

### Structure

```
docs/progress/
├── README.md                            # This file - overall dashboard
├── core-tracing-infrastructure.md       # Progress for CTI epic
├── session-persistence.md               # Progress for SP epic
└── ...                                  # One file per epic
```

### Task Status Values

| Status | Description | Next Action |
|--------|-------------|-------------|
| `TODO` | Not started | Pick task and start working |
| `IN_PROGRESS` | Currently working | Continue implementation |
| `DONE` | Completed successfully | Move to next task |
| `FAILED` | Implementation failed | Review notes, retry with new approach |
| `BLOCKED` | Cannot proceed | Resolve blocker, ask for help |

### Status Transitions

```
TODO → IN_PROGRESS → DONE
                  ↘ FAILED → TODO (retry)
                  ↘ BLOCKED → TODO (when unblocked)
```

### Relationship with docs/tasks/

- **`docs/tasks/`** = What to do (task definitions, acceptance criteria)
- **`docs/progress/`** = How it's going (status, commits, notes)

**Task definitions are immutable** after creation.
**Progress files are updated** throughout development.

### Resuming Work

When returning to development after a break:

1. Check this README for overall status
2. Look for `IN_PROGRESS` tasks (continue or reset)
3. Check `BLOCKED` and `FAILED` items for pending issues
4. Pick next `TODO` task from highest priority epic

### Updating Progress

When starting a task:
```markdown
**Status**: `IN_PROGRESS`
**Started**: YYYY-MM-DD HH:MM
```

When completing a task:
```markdown
**Status**: `DONE`
**Completed**: YYYY-MM-DD HH:MM
**Commit**: abc1234
**PR**: #42 (if applicable)
```

When a task fails:
```markdown
**Status**: `FAILED`
**Notes**:
- What was attempted
- Why it failed
- What to try next
```

When a task is blocked:
```markdown
**Status**: `BLOCKED`
**Blockers**:
- ⚠️ Description of blocker
- ⚠️ What's needed to unblock
```

### Cross-References

- Task definitions: `docs/tasks/`
- Workflow guidelines: `docs/engineering/WORKFLOW.md`
- Architecture decisions: `docs/architecture/`
- Product requirements: `docs/PRD.md`
