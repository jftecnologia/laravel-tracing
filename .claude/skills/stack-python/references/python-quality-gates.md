# Python Quality Gates

## Lint & Format — Ruff

```toml
# pyproject.toml
[tool.ruff]
target-version = "py312"
line-length = 100

[tool.ruff.lint]
select = ["E", "F", "I", "N", "W", "UP", "B", "SIM", "RUF"]

[tool.ruff.format]
quote-style = "double"
indent-style = "space"
```

### Commands

```bash
ruff check .              # Lint
ruff check . --fix        # Lint + auto-fix
ruff format .             # Format
ruff format . --check     # Check format only
```

## Type Check — mypy

```toml
# pyproject.toml
[tool.mypy]
python_version = "3.12"
strict = true
warn_return_any = true
warn_unused_configs = true
disallow_untyped_defs = true
```

### Commands

```bash
mypy .                    # Full type check
mypy src/                 # Check specific directory
```

## Security

```bash
pip-audit                 # Check for known vulnerabilities
bandit -r src/            # Static security analysis
```

## Quality Gate Sequence

```bash
# Before commit
ruff check . && ruff format . --check && mypy .

# Before PR
ruff check . && ruff format . --check && mypy . && pytest

# Before deploy
ruff check . && mypy . && pytest --cov && pip-audit
```
