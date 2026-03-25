# Architecture Documentation

This directory contains the system architecture documentation. Generate it using:

```text
/generate-architecture
```

The skill reads your PRD and engineering standards to produce structured architecture docs covering:

- **STRUCTURE.md** — Directory layout and class responsibilities
- **DATABASE.md** — Schema, relationships, and data model
- **SECURITY.md** — Authentication, authorization, and security rules
- **DATA_FLOW.md** — Request lifecycle and data flow
- **CONFIGURATION.md** — Configuration hierarchy and override patterns
- **EXTENSIONS.md** — Package customization patterns

> **Prerequisite**: Complete `docs/PRD.md` first (via `/generate-prd`).
