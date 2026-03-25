---
description: "Structured refactoring with safety checks — analyze code, plan changes, refactor with tests as safety net. Use when code needs restructuring, cleanup, pattern alignment, or technical debt reduction (e.g., 'refactor this module', 'clean up this code', 'align with project patterns')."
---

# Refactor

Structured refactoring with analysis, planning, and safety validation.

## Steps

1. **Identify refactoring scope**:
   - Ask user what to refactor and why (if not clear)
   - Read the target files/modules
   - Identify the current patterns and issues

2. **Analyze current state**:
   // turbo
   - Run `code-reviewer` skill on target files (or manual review)
   - Identify: code smells, SOLID violations, duplication, inconsistencies
   - Check test coverage: do tests exist for the target code?

3. **Plan the refactoring**:
   Present the plan before executing:

   ```markdown
   ## 🔧 Refactoring Plan

   **Alvo**: [file/module]
   **Motivo**: [why refactor]

   ### O que muda

   - [change 1]
   - [change 2]

   ### Riscos

   - [risk 1]: mitigação [mitigation]

   ### Safety net

   - [ ] Tests exist: ✅/❌
   - [ ] Tests pass before refactor: ✅/❌

   ## 🎯 Sua Decisão

   1️⃣ **Aprovado** — executar o refactoring
   2️⃣ **Ajustar plano** — mudar escopo ou abordagem
   3️⃣ **Cancelar**
   ```

4. **Ensure safety net** (before changing code):
   // turbo
   - If tests exist: run them and confirm they pass
   - If tests DON'T exist: generate them first (`generate-test` skill)
   - Tests must pass BEFORE refactoring begins

5. **Execute refactoring**:
   - Make changes following the approved plan
   - Use `implement-task` patterns (small commits, quality gates)
   - Run tests after each significant change

6. **Validate**:
   // turbo
   - Run full test suite
   - Run lint
   - Compare behavior before/after (tests should still pass)
   - Report results to user

> ⚠️ **Planning gate**: Include Security and i18n considerations in the refactoring plan (see `AGENT_FLOW.md` §3.1)
> ⚠️ **Post-code gate**: Before committing, execute Mandatory Gates (see `AGENT_FLOW.md` §3.2)
