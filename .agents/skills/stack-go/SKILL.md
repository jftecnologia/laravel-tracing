---
name: stack-go
description: "Go development patterns, quality gates, and testing guidelines. Activated as an additive stack pack for projects using Go. Provides idiomatic Go patterns, golangci-lint configuration, and testing with the standard library and testify. Use when implementing Go features, setting up Go projects, or referencing Go best practices."
---

# Stack Pack: Go

Additive stack pack providing Go development patterns, quality gates, and testing guidelines. Supplements the core skills — does NOT replace them.

---

## Related References

- [references/go-patterns.md](references/go-patterns.md) — Idiomatic Go patterns
- [references/go-quality-gates.md](references/go-quality-gates.md) — Quality gate configuration
- [references/go-testing.md](references/go-testing.md) — Testing patterns

---

## 1. When This Pack Applies

This pack is relevant when the project has:

- `go.mod` and `go.sum`
- Go source files (`.go`)
- Go runtime targets (APIs, CLIs, microservices)

---

## 2. Project Structure

```text
cmd/
├── server/              # Main application entry
│   └── main.go
├── cli/                 # CLI tool entry
│   └── main.go
internal/                # Private application code
├── config/
├── handlers/            # HTTP handlers
├── services/            # Business logic
├── repositories/        # Data access
├── models/              # Domain types
└── middleware/
pkg/                     # Public reusable packages
├── logger/
└── httputil/
tests/
├── integration/
└── e2e/
go.mod
go.sum
Makefile
```

---

## 3. Quality Gates

See [references/go-quality-gates.md](references/go-quality-gates.md) for full configuration.

### Quick Reference

```bash
go vet ./...              # Static analysis
golangci-lint run         # Comprehensive linting
go test ./...             # Test
go test ./... -race       # Test with race detector
go build ./...            # Build check
```

---

## 4. Key Principles

1. **Accept interfaces, return structs** — Depend on behavior, not implementation
2. **Errors are values** — Handle explicitly; wrap with `fmt.Errorf("...: %w", err)`
3. **Small interfaces** — Prefer 1-2 method interfaces
4. **Package by feature** — Group by domain, not by layer
5. **context.Context first** — Pass as first parameter for cancellation and deadlines
6. **No globals** — Use dependency injection via constructors
7. **Table-driven tests** — Standard Go testing pattern

---

## 5. Integration with Core Skills

| Core Skill         | What this pack adds                       |
| ------------------ | ----------------------------------------- |
| `implement-task`   | Go patterns, quality gate commands        |
| `generate-test`    | Go testing patterns, table-driven tests   |
| `task-planner`     | Go complexity estimation                  |
| `code-reviewer`    | Go-specific code smells, idiomatic checks |
| `security-analyst` | Go security (govulncheck, gosec)          |
