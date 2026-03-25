#!/usr/bin/env bash
# check-progress.sh — Read docs/progress/ and show summary
# Usage: bash .agents/scripts/check-progress.sh

set -euo pipefail

PROGRESS_DIR="docs/progress"

header() { echo -e "\n\033[1;34m═══ $1 ═══\033[0m"; }

count_status() {
    local status="$1"
    { grep -rh "Status.*${status}" "$PROGRESS_DIR" 2>/dev/null || true; } | wc -l | tr -d ' '
}

header "Project Progress"

if [ ! -d "$PROGRESS_DIR" ]; then
    echo "No progress directory found at $PROGRESS_DIR"
    exit 0
fi

# Read README.md if present
if [ -f "$PROGRESS_DIR/README.md" ]; then
    echo ""
    head -50 "$PROGRESS_DIR/README.md"
    echo ""
fi

# Count statuses across all progress files
TODO=$(count_status "TODO")
IN_PROGRESS=$(count_status "IN_PROGRESS")
DONE=$(count_status "DONE")
BLOCKED=$(count_status "BLOCKED")

TOTAL=$((TODO + IN_PROGRESS + DONE + BLOCKED))

header "Task Summary"
echo "📋 TODO:        $TODO"
echo "🔄 In Progress: $IN_PROGRESS"
echo "✅ Done:        $DONE"
echo "🚫 Blocked:     $BLOCKED"
echo "───────────────────"
echo "📊 Total:       $TOTAL"

if [ "$TOTAL" -gt 0 ]; then
    PCT=$((DONE * 100 / TOTAL))
    echo "📈 Completion:  ${PCT}%"
fi

# Show current git state
header "Git Status"
echo "Branch: $(git branch --show-current 2>/dev/null || echo 'unknown')"
if git rev-parse --is-inside-work-tree > /dev/null 2>&1; then
    CHANGES=$(git status --short 2>/dev/null | wc -l | tr -d ' ')
else
    CHANGES=0
fi
echo "Uncommitted changes: $CHANGES files"
