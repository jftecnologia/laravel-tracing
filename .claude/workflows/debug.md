---
description: "Structured debugging flow — systematically investigate bugs, reproduce, identify root cause, and fix. Use when a bug needs investigation beyond a simple fix (e.g., 'debug this issue', 'investigate why X happens', 'help me find the root cause')."
---

# Debug

Systematic debugging flow for bugs that need investigation.

## Steps

1. **Gather information**:
   - Read error logs: `last-error` MCP tool (backend), `browser-logs` (frontend)
   - Get stack trace if available
   - Ask user to describe: what happened vs what was expected

2. **Reproduce**:
   - Identify the exact steps to reproduce
   - If web UI: use browser subagent to navigate and reproduce
   - If API: use tinker or curl to reproduce
   - If test: run the failing test in isolation

3. **Narrow the scope**:
   - Identify the file(s) involved
   - Read relevant code with `view_file` or `view_code_item`
   - Check recent changes: `git log --oneline -10 -- <file>`
   - Check database state if relevant: `database-query`

4. **Identify root cause**:
   - Document the hypothesis
   - Verify with code reading or targeted tinker/test
   - If hypothesis fails, try next hypothesis

5. **Present finding to user**:

   ```markdown
   ## 🔍 Root Cause Identified

   **Problema**: [description]
   **Causa**: [root cause]
   **Arquivo(s)**: [file paths]

   ## 🎯 Sua Decisão

   1️⃣ **Corrigir agora** — aplico o fix e rodo os testes
   2️⃣ **Criar issue** — documento o bug e corrijo depois
   3️⃣ **Investigar mais** — ainda não tenho certeza
   ```

6. **Fix** (if approved):
   - Route to `bug-fixer` skill or `implement-task` depending on complexity
   - Ensure tests cover the fix (three pillars)
   - Run quality gates

> ⚠️ **Post-Code Gates**: Before committing fixes, execute Mandatory Gates (see `AGENT_FLOW.md` §3.2)
