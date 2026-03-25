---
description: "Generate or update project documentation. Use when you need to create, update, or audit documentation such as README, architecture docs, API docs, or engineering docs (e.g., 'update the docs', 'create documentation', 'document this module', 'update README')."
---

# Documentation

Generate or update project documentation.

## Steps

1. **Determine scope** — ask if unclear:
   - **README**: project overview, setup instructions
   - **Architecture**: system design, component diagrams
   - **API**: endpoint documentation
   - **Engineering**: code standards, workflow, testing
   - **PRD update**: requirements that changed
   - **Task docs**: update task/progress tracking

2. **Audit current state**:
   - Scan `docs/` directory structure
   - Check for outdated content (compare with actual codebase)
   - Identify gaps (undocumented features, missing sections)

3. **Present findings**:

   ```markdown
   ## 📝 Documentation Status

   ### Up to date

   - [file] — last relevant change: [date/commit]

   ### Needs update

   - [file] — reason: [what changed]

   ### Missing

   - [topic] — no documentation exists

   ## 🎯 Sua Decisão

   1️⃣ **Atualizar tudo** — corrigir gaps e outdated
   2️⃣ **Só gaps** — criar documentação faltante
   3️⃣ **Específico** — indicar qual doc atualizar
   ```

4. **Generate/update**:
   - For architecture: route to `generate-architecture` skill
   - For PRD: route to `generate-prd` skill
   - For UI design: route to `generate-ui-design` skill
   - For other docs: create/update directly following patterns in `docs/`

5. **Validation**:
   - Verify no broken internal links
   - Verify consistency with code
   - Update doc maintenance table if applicable (see `.agents/rules/01-quality-shield.md` §7)
