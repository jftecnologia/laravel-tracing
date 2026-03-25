---
description: "Look at latest committed or changed files and generate a semantic commit message in copy/paste format, optimized for git/github. Use when the user wants to commit, generate a commit message, push changes, or open a PR (e.g., 'commit this', 'generate commit message', 'gerar mensagem de commit', 'push this', 'open a PR')."
---

# Generate Commit Message

Analyze changes, generate semantic commit message, and optionally commit/push/PR.

## Steps

1. **Identify changes**:
   // turbo

   ```bash
   # Check for staged files first, then unstaged
   STAGED=$(git diff --staged --stat)
   if [ -n "$STAGED" ]; then
     echo "=== Staged Changes ==="
     git diff --staged --stat
   else
     echo "=== Unstaged Changes ==="
     git diff --stat
   fi
   ```

2. **Read the diff**:
   // turbo

   ```bash
   # Read the actual diff (staged or unstaged)
   if git diff --staged --quiet 2>/dev/null; then
     git diff
   else
     git diff --staged
   fi
   ```

3. **Generate semantic commit message**:
   Following `docs/engineering/WORKFLOW.md` format:

   ```
   <type>(<scope>): <description>

   [optional body with details]
   ```

   Types: `feat`, `fix`, `refactor`, `style`, `test`, `docs`, `chore`, `ci`, `perf`

   Rules:
   - Lowercase description, no period at end
   - Scope should be the most relevant module/feature
   - Body explains **why**, not **what** (the diff shows what)
   - If multiple logical changes, suggest separate commits

4. **Present to user**:

   ```markdown
   ## 📝 Commit Message
   ```

   <generated message>
   ```

   **Changed files**: X files (Y insertions, Z deletions)

   ## 🎯 Sua Decisão

   1️⃣ **Commit** — Stage all and commit with this message
   2️⃣ **Commit + Push** — Commit and push to remote
   3️⃣ **Commit + Push + PR** — Commit, push, and open a Pull Request

   > Responda o número, edite a mensagem, ou comente livremente.

   ```

   ```

5. **Execute user's choice**:

   **Option 1 — Commit only:**
   // turbo

   ```bash
   git add -A
   git commit -m "<message>"
   ```

   **Option 2 — Commit + Push:**
   // turbo

   ```bash
   git add -A
   git commit -m "<message>"
   git push origin $(git branch --show-current)
   ```

   **Option 3 — Commit + Push + PR:**
   - Commit and push (same as option 2)
   - Generate semantic PR title and description:
     - Title: same format as commit (`<type>(<scope>): <description>`)
     - Body: summary of all changes, acceptance criteria if applicable
   - Open PR using GitHub MCP tools or `gh` CLI

> ⚠️ **Note**: Replace `master` with your default branch if different when opening PRs.
