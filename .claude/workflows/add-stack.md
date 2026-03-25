---
description: Add or activate a stack pack for a specific technology (Node.js, Python, Go). Use when starting a new project with a non-Laravel stack, or when adding a new technology to an existing project. Detects gaps in skills/workflows and suggests activations.
---

# Add Stack Pack

## Steps

1. **Determine mode** — Ask the user or detect context:
   - **Detection mode**: project files already exist → analyze them
   - **Inception mode**: user is describing a new project → ask about desired stack

2. **Detect or collect stack info**:
   - Scan for: `composer.json`, `package.json`, `go.mod`, `requirements.txt`, `pyproject.toml`
   - If inception mode, ask:
     > Which stack are you targeting?
     > 1️⃣ Node.js / TypeScript
     > 2️⃣ Python
     > 3️⃣ Go
     > 4️⃣ Bash / Shell
     > 5️⃣ Laravel (already included in core)

3. **Gap analysis** — For the detected/selected stack, check:
   - Is the stack pack skill present? (`.agents/skills/stack-<name>/`)
   - Are stack-specific quality gates configured?
   - Are relevant workflows available?
   - Missing items → add to gap report

4. **Present gap report**:

   ```markdown
   ## 🔍 Stack Analysis: Node.js

   ### ✅ Available

   - Core skills (brainstorming, task-planner, implement-task, ...)
   - Workflows (deploy, preview, status, ...)

   ### ⚠️ Missing / Inactive

   1️⃣ **stack-node** skill — Node.js patterns, quality gates, testing
   2️⃣ **Node quality gates** — ESLint + Prettier + TypeScript config

   > Deseja ativar os componentes faltantes? (ex: "1 e 2", "todos")
   ```

5. **Activate requested components**:
   - Read the stack pack SKILL.md for activation instructions
   - Update `CLAUDE.md` skill table if needed
   - Update `docs/engineering/STACK.md` with stack-specific info

6. **Suggest next steps**:
   > ✅ Stack pack ativado!
   >
   > 1️⃣ Iniciar projeto com `/full-pipeline`
   > 2️⃣ Configurar guidelines com `/init-project`
   > 3️⃣ Brainstorming sobre o projeto com `/brainstorming`
