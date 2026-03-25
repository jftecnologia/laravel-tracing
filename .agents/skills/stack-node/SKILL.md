---
name: stack-node
description: "Node.js and TypeScript development patterns, quality gates, and testing guidelines. Activated as an additive stack pack for projects using Node.js. Provides idiomatic patterns, ESLint/Prettier/TypeScript configuration, and testing with Vitest or Jest. Use when implementing Node.js features, setting up Node.js projects, or referencing Node.js best practices."
---

# Stack Pack: Node.js / TypeScript

Additive stack pack providing Node.js and TypeScript development patterns, quality gates, and testing guidelines. This pack supplements the core skills — it does NOT replace them.

---

## Related References

- [references/node-patterns.md](references/node-patterns.md) — Idiomatic Node.js/TypeScript patterns
- [references/node-quality-gates.md](references/node-quality-gates.md) — Quality gate configuration
- [references/node-testing.md](references/node-testing.md) — Testing patterns and frameworks

---

## 1. When This Pack Applies

This pack is relevant when the project has:

- `package.json` with Node.js dependencies
- TypeScript configuration (`tsconfig.json`)
- Node.js runtime targets (backend services, CLIs, serverless)

> This pack is NOT for frontend-only React/Inertia (use `frontend-development` skill instead).
> Use this for **Node.js backend**, **CLI tools**, **serverless functions**, **full-stack Node apps**.

---

## 2. Project Structure

```text
src/
├── modules/          # Feature modules
│   ├── users/
│   │   ├── users.controller.ts
│   │   ├── users.service.ts
│   │   ├── users.repository.ts
│   │   └── users.types.ts
│   └── auth/
├── shared/           # Shared utilities
│   ├── errors/
│   ├── middleware/
│   └── utils/
├── config/           # Configuration
├── database/         # Migrations, seeds
└── index.ts          # Entry point

tests/
├── unit/
├── integration/
└── e2e/
```

---

## 3. Quality Gates

See [references/node-quality-gates.md](references/node-quality-gates.md) for full configuration.

### Quick Reference

```bash
# Lint + format
npm run lint          # ESLint
npm run format        # Prettier

# Type check
npm run types         # tsc --noEmit

# Test
npm test              # Vitest or Jest

# Full check
npm run lint && npm run types && npm test
```

---

## 4. Key Principles

1. **TypeScript strict mode** — Always use `strict: true` in tsconfig
2. **ESM first** — Use ES modules (`"type": "module"` in package.json)
3. **Explicit types for public APIs** — Return types, parameter types, exported interfaces
4. **Error handling** — Custom error classes extending `Error`, never throw strings
5. **Async/await** — Prefer over raw Promises; avoid callback patterns
6. **Environment config** — Use `dotenv` or framework config; never hardcode secrets
7. **Dependency injection** — Prefer constructor injection for testability

---

## 5. Integration with Core Skills

This stack pack integrates with core skills by providing stack-specific context:

| Core Skill         | What this pack adds                                     |
| ------------------ | ------------------------------------------------------- |
| `implement-task`   | Node.js patterns, quality gate commands                 |
| `generate-test`    | Vitest/Jest patterns, test structure                    |
| `task-planner`     | Node.js complexity estimation, file layout              |
| `code-reviewer`    | Node.js-specific code smells                            |
| `security-analyst` | Node.js security patterns (npm audit, dependency check) |
