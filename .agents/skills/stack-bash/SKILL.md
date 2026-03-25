---
name: stack-bash
description: "Bash and Shell scripting development patterns, quality gates, and testing guidelines. Activated as an additive stack pack for projects using Bash/Shell scripts. Provides idiomatic patterns, ShellCheck configuration, and testing with BATS or manual assertions. Use when implementing shell scripts, setting up Bash projects, or referencing Bash best practices."
dependencies:
  skills: []
  docs: [docs/engineering/QUALITY_GATES.md]
---

# Stack: Bash / Shell

Additive stack pack for projects using Bash/Shell scripts.

---

## Shell Standards

### Shebang and Safety

Every script MUST start with:

```bash
#!/usr/bin/env bash
set -euo pipefail
```

- `set -e` — Exit on error
- `set -u` — Error on undefined variables
- `set -o pipefail` — Pipeline fails if any command fails

### Quoting

- **Always quote variables**: `"$var"`, never `$var`
- **Use `$()` over backticks**: `result=$(command)` not `` result=`command` ``
- **Use `[[ ]]` over `[ ]`**: `[[ -f "$file" ]]` not `[ -f "$file" ]`

### Naming Conventions

| What            | Convention      | Example             |
| --------------- | --------------- | ------------------- |
| Script files    | `kebab-case.sh` | `check-progress.sh` |
| Functions       | `snake_case`    | `run_gate()`        |
| Constants/Envs  | `UPPER_SNAKE`   | `MAX_RETRIES`       |
| Local variables | `lower_snake`   | `local file_path`   |

---

## Script Organization

```bash
#!/usr/bin/env bash
set -euo pipefail

# ─── Constants ────────────────────────────────────────────────────────────────
readonly SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
readonly VERSION="1.0.0"

# ─── Functions ────────────────────────────────────────────────────────────────
usage() {
    echo "Usage: $(basename "$0") [options]"
    echo ""
    echo "Options:"
    echo "  -h, --help    Show help"
    echo "  -v, --verbose Enable verbose output"
}

main() {
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case "$1" in
            -h|--help) usage; exit 0 ;;
            -v|--verbose) VERBOSE=true; shift ;;
            *) echo "Unknown option: $1"; usage; exit 1 ;;
        esac
    done

    # Main logic here
}

# ─── Entry point ──────────────────────────────────────────────────────────────
main "$@"
```

---

## Error Handling

```bash
# Trap for cleanup
cleanup() {
    rm -rf "${TMP_DIR:-}"
}
trap cleanup EXIT

# Error reporting
die() {
    echo "ERROR: $1" >&2
    exit "${2:-1}"
}

# Usage
[[ -f "$config" ]] || die "Config file not found: $config"
```

---

## Linting

### ShellCheck (MANDATORY)

```bash
# Lint all shell scripts
shellcheck *.sh
shellcheck **/*.sh

# Lint specific file
shellcheck script.sh

# Ignore specific rule
# shellcheck disable=SC2034
UNUSED_VAR="value"
```

Common rules to know:

- **SC2086**: Double quote to prevent globbing and word splitting
- **SC2046**: Quote `$()` to prevent word splitting
- **SC2034**: Variable appears unused
- **SC2155**: Declare and assign separately

---

## Testing

### BATS (Bash Automated Testing System)

```bash
# Install
npm install --save-dev bats

# Test file: tests/test_script.bats
@test "script prints usage with --help" {
    run bash script.sh --help
    [ "$status" -eq 0 ]
    [[ "$output" == *"Usage"* ]]
}

@test "script fails with unknown option" {
    run bash script.sh --invalid
    [ "$status" -eq 1 ]
}

# Run tests
npx bats tests/
```

### Manual Assertion Pattern

```bash
PASS=0; FAIL=0; TOTAL=0

assert() {
    TOTAL=$((TOTAL + 1))
    if eval "$1" > /dev/null 2>&1; then
        PASS=$((PASS + 1)); echo "  ✅ $2"
    else
        FAIL=$((FAIL + 1)); echo "  ❌ $2"
    fi
}

# Usage
assert "test -f 'output.txt'" "Output file created"
assert "grep -q 'expected' output.txt" "Output contains expected text"

echo "Results: $PASS passed, $FAIL failed, $TOTAL total"
[ "$FAIL" -eq 0 ] && exit 0 || exit 1
```

---

## Quality Gates

| Gate       | Command                                 | Blocks? |
| ---------- | --------------------------------------- | ------- |
| ShellCheck | `shellcheck *.sh`                       | ✅ Yes  |
| Tests      | `npx bats tests/` or manual test runner | ✅ Yes  |

---

## Common Patterns

### Logging

```bash
log_info()  { echo "[INFO]  $(date '+%H:%M:%S') $*"; }
log_warn()  { echo "[WARN]  $(date '+%H:%M:%S') $*" >&2; }
log_error() { echo "[ERROR] $(date '+%H:%M:%S') $*" >&2; }
```

### Colored Output

```bash
green()  { echo -e "\033[32m$1\033[0m"; }
red()    { echo -e "\033[31m$1\033[0m"; }
yellow() { echo -e "\033[33m$1\033[0m"; }
bold()   { echo -e "\033[1m$1\033[0m"; }
```

### Temp Files

```bash
TMP_DIR=$(mktemp -d)
TMP_FILE=$(mktemp)
trap 'rm -rf "$TMP_DIR" "$TMP_FILE"' EXIT
```

---

## Anti-Patterns

| ❌ Don't                            | ✅ Do                         |
| ----------------------------------- | ----------------------------- |
| `cd dir && ...` without error check | `cd dir \|\| die "Cannot cd"` |
| Unquoted variables                  | Always quote: `"$var"`        |
| `ls` for iteration                  | `for f in *.txt; do ...`      |
| Backticks for subshells             | `$(command)`                  |
| `[ ]` for conditionals              | `[[ ]]`                       |
| `echo` for error messages           | `echo ... >&2`                |
