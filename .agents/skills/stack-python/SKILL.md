---
name: stack-python
description: "Python development patterns, quality gates, and testing guidelines. Activated as an additive stack pack for projects using Python. Provides idiomatic patterns, Ruff/mypy configuration, and testing with pytest. Use when implementing Python features, setting up Python projects, or referencing Python best practices."
---

# Stack Pack: Python

Additive stack pack providing Python development patterns, quality gates, and testing guidelines. Supplements the core skills — does NOT replace them.

---

## Related References

- [references/python-patterns.md](references/python-patterns.md) — Idiomatic Python patterns
- [references/python-quality-gates.md](references/python-quality-gates.md) — Quality gate configuration
- [references/python-testing.md](references/python-testing.md) — Testing patterns with pytest

---

## 1. When This Pack Applies

This pack is relevant when the project has:

- `requirements.txt`, `pyproject.toml`, or `Pipfile`
- Python source files (`.py`)
- Python runtime targets (APIs, CLIs, scripts, data pipelines)

---

## 2. Project Structure

```text
src/
├── app/                 # Application code
│   ├── __init__.py
│   ├── main.py          # Entry point
│   ├── config.py        # Configuration
│   ├── models/          # Data models
│   ├── services/        # Business logic
│   ├── repositories/    # Data access
│   ├── api/             # API routes/controllers
│   └── utils/           # Shared utilities
├── tests/
│   ├── unit/
│   ├── integration/
│   └── conftest.py      # Shared fixtures
├── pyproject.toml       # Project config + dependencies
└── README.md
```

---

## 3. Quality Gates

See [references/python-quality-gates.md](references/python-quality-gates.md) for full configuration.

### Quick Reference

```bash
ruff check .              # Lint
ruff format .             # Format
mypy .                    # Type check
pytest                    # Test
pytest --cov              # Test with coverage
```

---

## 4. Key Principles

1. **Type hints everywhere** — Use `typing` module; prefer `str | None` over `Optional[str]`
2. **Pydantic for validation** — Data validation and settings management
3. **Async where appropriate** — Use `async/await` for I/O-bound operations
4. **Dataclasses or Pydantic models** — Prefer over plain dicts for structured data
5. **Context managers** — Use `with` for resource management
6. **Virtual environments** — Always use `venv` or equivalent

---

## 5. Integration with Core Skills

| Core Skill         | What this pack adds                    |
| ------------------ | -------------------------------------- |
| `implement-task`   | Python patterns, quality gate commands |
| `generate-test`    | pytest patterns, fixture usage         |
| `task-planner`     | Python complexity estimation           |
| `code-reviewer`    | Python-specific code smells            |
| `security-analyst` | Python security (bandit, pip audit)    |
