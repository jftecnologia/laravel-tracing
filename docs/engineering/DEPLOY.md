# Deploy & CI/CD

> This file is a **template placeholder**. Define your deployment strategy after completing architecture docs.
>
> Use `/generate-architecture` to create architecture documentation first, then fill in this template.

---

## 1. Environments

| Environment | URL         | Branch    | Deploy Trigger         |
| ----------- | ----------- | --------- | ---------------------- |
| Development | `localhost` | Any       | Manual (`npm run dev`) |
| Staging     | —           | `staging` | —                      |
| Production  | —           | `master`  | —                      |

<!-- Define your environments above -->

---

## 2. CI Pipeline

<!-- Define CI steps, e.g.:
- Lint (composer lint, npm run lint)
- Tests (composer test)
- Security scan
- Build
-->

---

## 3. Deploy Process

<!-- Define deploy steps, e.g.:
- Pull latest from branch
- Install dependencies
- Run migrations
- Clear caches
- Restart queues
-->

---

## 4. Rollback

<!-- Define rollback procedures, e.g.:
- Revert to previous release
- Rollback migrations
- Restore database backup
-->

---

## 5. Environment Variables

<!-- Define env var management, e.g.:
- Where secrets are stored
- How to add new env vars
- Sync between environments
-->

---

## 6. Database Migrations (Production)

<!-- Define migration strategy, e.g.:
- Migration review process
- Zero-downtime migration patterns
- Data migration procedures
- See `database-migration` skill for patterns
-->

---

## Related Documentation

- [WORKFLOW.md](WORKFLOW.md) — Git workflow and commit conventions
- [QUALITY_GATES.md](QUALITY_GATES.md) — Pre-commit and pre-deploy gates
- [STACK.md](STACK.md) — Tech stack and dependencies
- Skill: `database-migration` — Migration planning and execution patterns
