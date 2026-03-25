#!/usr/bin/env bash
# verify-quality-gates.sh — Stack-agnostic quality gate runner
# Detects project stack and runs appropriate quality checks.
# Usage: bash .agents/scripts/verify-quality-gates.sh [--strict]

set -euo pipefail

STRICT=${1:-""}
PASS=0
FAIL=0

green() { echo -e "\033[32m✅ $1\033[0m"; }
red() { echo -e "\033[31m❌ $1\033[0m"; }
yellow() { echo -e "\033[33m⚠️  $1\033[0m"; }
header() { echo -e "\n\033[1;34m═══ $1 ═══\033[0m"; }

run_gate() {
    local name="$1"
    local cmd="$2"
    if eval "$cmd" > /dev/null 2>&1; then
        green "$name"
        PASS=$((PASS + 1))
    else
        red "$name"
        FAIL=$((FAIL + 1))
    fi
}

has_npm_script() {
    npm pkg get "scripts.$1" 2>/dev/null | grep -qv "null"
}

header "Detecting Stack"

# Laravel / PHP
if [ -f "composer.json" ]; then
    echo "📦 PHP/Laravel detected"

    header "PHP Quality Gates"
    [ -f "vendor/bin/pint" ] && run_gate "Pint (format)" "vendor/bin/pint --test"
    [ -f "vendor/bin/phpstan" ] && run_gate "PHPStan (analyze)" "vendor/bin/phpstan analyse --no-progress"
    [ -f "vendor/bin/pest" ] && run_gate "Pest (test)" "vendor/bin/pest --no-interaction"
    [ -f "vendor/bin/phpunit" ] && [ ! -f "vendor/bin/pest" ] && run_gate "PHPUnit (test)" "vendor/bin/phpunit --no-interaction"
fi

# Node.js / TypeScript
if [ -f "package.json" ]; then
    echo "📦 Node.js detected"

    header "Node.js Quality Gates"
    if command -v npm > /dev/null 2>&1; then
        if has_npm_script lint; then
            run_gate "ESLint (lint)" "npm run lint"
        else
            yellow "No lint script found"
        fi

        if has_npm_script types; then
            run_gate "TypeScript (types)" "npm run types"
        else
            yellow "No types script found"
        fi

        if has_npm_script test; then
            run_gate "Tests" "npm test"
        else
            yellow "No test script found"
        fi
    fi
fi

# Python
if [ -f "pyproject.toml" ] || [ -f "requirements.txt" ]; then
    echo "📦 Python detected"

    header "Python Quality Gates"
    command -v ruff > /dev/null 2>&1 && run_gate "Ruff (lint)" "ruff check ."
    command -v mypy > /dev/null 2>&1 && run_gate "mypy (types)" "mypy ."
    command -v pytest > /dev/null 2>&1 && run_gate "pytest (test)" "pytest --no-header -q"
fi

# Go
if [ -f "go.mod" ]; then
    echo "📦 Go detected"

    header "Go Quality Gates"
    run_gate "go vet" "go vet ./..."
    command -v golangci-lint > /dev/null 2>&1 && run_gate "golangci-lint" "golangci-lint run"
    run_gate "go test" "go test ./..."
fi

# Summary
header "Summary"
echo "Passed: $PASS"
echo "Failed: $FAIL"

if [ "$FAIL" -gt 0 ]; then
    red "Quality gates FAILED ($FAIL issues)"
    [ "$STRICT" = "--strict" ] && exit 1
else
    green "All quality gates PASSED"
fi
