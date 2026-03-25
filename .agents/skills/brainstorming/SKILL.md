---
name: brainstorming
description: "Structured ideation, specification refinement, and skill/workflow discovery. Use when the user wants to brainstorm, think through, explore, define, structure ideas, plan a new project or module, evaluate trade-offs, analyze risks/advantages/disadvantages, define MVPs, compare approaches, plan layout or UI changes, refine incomplete specifications before implementation, or when the user doesn't know which skill, workflow, or tool to use (e.g., 'vamos pensar em como fazer X', 'quero estruturar a ideia de Y', 'brainstorm sobre Z', 'como seria melhor fazer X', 'preciso definir o layout de Y', 'quero repensar a UI de Z', 'não sei por onde começar', 'qual skill devo usar?', 'me ajude a decidir como fazer X')."
---

# Brainstorming

Structured ideation, specification refinement, and intelligent skill/workflow discovery. Transform vague ideas into actionable, AI-consumable specifications — and help users find the best tools for the job.

**Output**: An Implementation Plan artifact for review, then (after approval) a document in `docs/brainstorming/` optimized for use as input to other skills.

---

## Related References

- [references/implementation-plan-template.md](references/implementation-plan-template.md) — Implementation Plan template (intermediate review artifact)
- [references/output-template.md](references/output-template.md) — Final brainstorming document template

---

## 1. Workflow Overview

```text
Phase 0: Understand
  → Phase 1: Context
    → Phase 1.5: Skill & Workflow Discovery
      → Phase 1.7: External Research (conditional)
        → Phase 2: Think
          → Phase 3: Implementation Plan Artifact
            → Phase 4: Review Gate (user approval)
              → Phase 5: Generate Document
                → Phase 6: Next Steps
```

All phases are mandatory. Do NOT skip phases. Phase 4 blocks until user approves.

---

## 2. Phase 0: Understand the Request

Determine the **brainstorming type** from the user's request:

| Type           | Description                             | Key Questions                                                     |
| -------------- | --------------------------------------- | ----------------------------------------------------------------- |
| `new-project`  | New application or product              | Vision, target users, MVP scope, naming                           |
| `feature`      | New feature for existing app            | Value, effort, risks, dependencies                                |
| `architecture` | Architecture change (full or partial)   | Impact, migration path, trade-offs                                |
| `skill`        | New or updated AI skill                 | Necessity, integration, workflow fit                              |
| `refactor`     | Code restructuring                      | Motivation, scope, risk, expected gains                           |
| `bug-analysis` | Complex bug investigation               | Root cause hypotheses, impact, approaches                         |
| `layout`       | UI/UX layout planning or redesign       | User flows, component structure, visual hierarchy, responsiveness |
| `guidance`     | User needs help choosing tools/approach | What they want to achieve, constraints, experience level          |
| `general`      | Any other ideation                      | Adapt dynamically                                                 |

### Clarification Rules

- Ask **at most 5 questions** to clarify scope and intent.
- Questions in **Portuguese (pt-BR)**.
- Never guess — if ambiguous, ask.
- If the request is clear enough, state: "Contexto claro. Iniciando brainstorming."

### Guidance Auto-Detection

If the user's request matches any of these patterns, set type to `guidance`:

- Explicitly asks which skill/workflow/tool to use
- Says "não sei por onde começar" or similar
- Describes a goal without specifying an approach
- Asks "como eu faço X?" without clear direction
- Mentions wanting to "entender as opções" or "explorar possibilidades"

For `guidance` type, the primary goal shifts to **recommending the best skills/workflows** while still providing structured analysis of the problem.

---

## 3. Phase 1: Context Gathering

Research the project to ground the brainstorming in reality.

### Always Check

- `docs/PRD.md` — Business context and requirements
- `docs/architecture/*.md` — Current architecture
- `docs/engineering/*.md` — Tech stack, standards, conventions

### MCP Tools (use as needed)

- `application-info` — PHP/Laravel versions, installed packages
- `database-schema` — Current data model
- `list-routes` — Existing routes and patterns
- `search-docs` — Laravel ecosystem documentation
- `database-query` — Inspect existing data
- `search_web` — General web search (used in Phase 1.7)
- `mcp_docker_mcp_search` — DuckDuckGo search (used in Phase 1.7)
- `mcp_docker_mcp_fetch_content` — Fetch and parse web page content (used in Phase 1.7)

### Type-Specific Context

| Type           | Also Check                                                                           |
| -------------- | ------------------------------------------------------------------------------------ |
| `feature`      | Related models, controllers, existing similar features                               |
| `architecture` | Current folder structure, service patterns, dependency graph                         |
| `skill`        | Existing skills in `.agents/skills/`, `CLAUDE.md` skill table                        |
| `refactor`     | Affected files, test coverage, coupling points                                       |
| `layout`       | `docs/design/*.md`, existing pages/components in `resources/js/`                     |
| `new-project`  | Similar projects if referenced, tech constraints                                     |
| `guidance`     | All available skills (`.agents/skills/*/SKILL.md`), workflows (`.agents/workflows/`) |

---

## 4. Phase 1.5: Skill & Workflow Discovery

Analyze available skills and workflows to identify the best fit for the user's needs. This phase is **always executed** but produces different outputs depending on the type:

- For `guidance` type: this is the **core analysis** — the main value of the brainstorming
- For all other types: this is a **supporting check** — ensures the skill recommends the right next steps

### 4.1 Skill Catalog

Read the actual SKILL.md files to build an up-to-date catalog. The following is a quick-reference index — **always verify against the real files**:

| Skill                       | Purpose                                   | Trigger Phrases                          | Output                    |
| --------------------------- | ----------------------------------------- | ---------------------------------------- | ------------------------- |
| `brainstorming`             | Ideation, spec refinement, tool discovery | "pensar em", "brainstorm", "explorar"    | `docs/brainstorming/*.md` |
| `generate-prd`              | Product requirements                      | "definir requisitos", "PRD"              | `docs/PRD.md`             |
| `generate-architecture`     | System architecture                       | "arquitetura", "estrutura do sistema"    | `docs/architecture/*.md`  |
| `generate-ui-design`        | UI/UX design specs                        | "design", "interface", "layout spec"     | `docs/design/*.md`        |
| `generate-task-breakdown`   | Task breakdown from docs                  | "quebrar em tasks", "planejar épicos"    | `docs/tasks/*.md`         |
| `task-planner`              | Plan + implement from description         | "planejar", "desenvolver", "implementar" | Code + docs               |
| `implement-task`            | Implement task from docs/tasks/           | "implementar task X"                     | Code + tests              |
| `frontend-development`      | React/Inertia/Tailwind/Radix              | "componente", "página", "frontend"       | React components          |
| `generate-test`             | PestPHP tests                             | "criar testes", "testar"                 | Test files                |
| `bug-fixer`                 | Fix bugs from issues/reports              | "corrigir bug", "fix issue #N"           | Bug fix + tests           |
| `security-analyst`          | OWASP security review                     | "segurança", "vulnerabilidades"          | Security report           |
| `project-qa-auditor`        | QA audit before PR/deploy                 | "auditar", "QA", "validar antes de PR"   | QA report                 |
| `developing-with-fortify`   | Auth reference (Fortify)                  | "autenticação", "login", "2FA"           | Auth implementation       |
| `generate-persona`          | Generate persona profiles                 | "criar persona", "gerar persona"         | `docs/personas/*.md`      |
| `generate-persona-feedback` | Simulate persona, generate feedback       | "feedback como X", "testar como persona" | `docs/feedbacks/**/*.md`  |
| `skill-creator`             | Create new skills                         | "criar skill", "nova skill"              | Skill files               |

### 4.2 Workflow Catalog

Scan `.agents/workflows/` for available workflows and document them:

| Workflow                  | Purpose                            | When to Use                            |
| ------------------------- | ---------------------------------- | -------------------------------------- |
| `generate-commit-message` | Semantic commit messages           | After completing code changes          |
| `helpme`                  | Universal orchestrator             | Don't know where to start, any task    |
| `deploy`                  | Pre-deploy checklist               | Before deploying to staging/production |
| `preview`                 | Start dev server + browser preview | Validate UI changes locally            |
| `status`                  | Project progress overview          | Check where the project stands         |
| `full-pipeline`           | Full development lifecycle         | Starting new project or major feature  |
| `code-review`             | Pre-PR review                      | Before opening a pull request          |
| `add-stack`               | Add/activate stack pack            | Adding Node.js, Python, Go support     |
| `debug`                   | Structured debugging               | Bug investigation beyond simple fix    |
| `refactor`                | Refactoring with safety checks     | Code restructuring, cleanup            |
| `test`                    | Run or generate tests              | Test suite, coverage, generate tests   |
| `docs`                    | Generate/update documentation      | Create or update project docs          |
| `improve-ui`              | UI/UX audit and improvement        | Fix ugly UI, align with design system  |
| `i18n`                    | Translation audit, fix, and sync   | Check/fix translations, sync locales   |

> **Note**: Always scan the actual workflow directory for the latest list.

### 4.3 Development Pipeline

Skills fit into a structured development pipeline. Recommend the right entry point:

```text
brainstorming → generate-prd → generate-architecture → generate-ui-design → generate-task-breakdown → implement-task
                                                                                                    ↗
                                                                         task-planner (standalone) ─┘
```

Supporting skills (can be invoked at any point):

- `frontend-development` — During implementation of UI tasks
- `generate-test` — During or after implementation
- `security-analyst` — Before PRs or after significant changes
- `project-qa-auditor` — Before PRs or deploys
- `bug-fixer` — When issues are found
- `developing-with-fortify` — Reference for auth features
- `generate-persona` — Create persona profiles for feedback testing
- `generate-persona-feedback` — Simulate persona behavior and generate actionable feedback

Stack packs (additive, activated by `init-project` or `/add-stack`):

- `stack-node` — Node.js/TypeScript patterns, quality gates, testing
- `stack-python` — Python patterns, quality gates, testing
- `stack-go` — Go patterns, quality gates, testing
- `stack-bash` — Bash/Shell scripting patterns, quality gates, testing

### Gap Detection

During discovery, check if the detected/discussed stack has a matching stack pack. If not:

> ⚠️ Seu projeto usa **[stack]**, mas o pack `stack-[name]` não está ativado.
> Recomendo rodar `/add-stack` para ativar patterns e quality gates específicos.

### 4.4 Matching Rules

When matching user needs to skills:

1. **Analyze the request** — Identify the core intent and desired outcome
2. **Cross-reference capabilities** — Match against the skill catalog
3. **Consider the pipeline** — Determine where the user is in the development lifecycle
4. **Recommend top 1-3** — Prioritize by relevance, not by quantity
5. **Identify pipeline sequences** — When skills chain together (e.g., brainstorming → PRD → architecture)
6. **Flag gaps** — When no skill fully covers the need, state what's missing

### 4.5 Decision Matrix

| User Intent                         | Primary Skill                    | Supporting Skills                             |
| ----------------------------------- | -------------------------------- | --------------------------------------------- |
| "Quero criar algo novo"             | `brainstorming` → `generate-prd` | `generate-architecture`, `generate-ui-design` |
| "Quero implementar feature X"       | `task-planner`                   | `frontend-development`, `generate-test`       |
| "Preciso corrigir um bug"           | `bug-fixer`                      | `generate-test`                               |
| "Quero redesenhar a tela Y"         | `brainstorming` (layout)         | `generate-ui-design`, `frontend-development`  |
| "Preciso fazer review de segurança" | `security-analyst`               | `project-qa-auditor`                          |
| "Quero refatorar o módulo Z"        | `task-planner` (refactor)        | `generate-test`, `security-analyst`           |
| "Preciso definir a arquitetura"     | `generate-architecture`          | `brainstorming` (architecture)                |
| "Quero auditar antes do deploy"     | `project-qa-auditor`             | `security-analyst`, `i18n-manager`            |
| "Preciso criar testes"              | `generate-test`                  | —                                             |
| "Quero implementar autenticação"    | `developing-with-fortify`        | `frontend-development`                        |
| "Quero criar uma nova skill"        | `skill-creator`                  | `brainstorming` (skill)                       |
| "Testar como a persona X"           | `generate-persona-feedback`      | `generate-persona`, `bug-fixer`               |
| "Criar perfil de persona"           | `generate-persona`               | `generate-persona-feedback`                   |
| "Verificar traduções" / "checar i18n" | `i18n-manager`                 | —                                             |
| "Rodar checks antes do PR"          | Workflow `/pre-pr`               | —                                             |
| "Quero revisar o código"            | `code-reviewer`                  | `security-analyst`                            |

---

## 5. Phase 1.7: External Research (Conditional)

Perform web-based research to gather external context about competitors, market references, or similar tools. This phase is **conditional** — only execute when triggered.

### Trigger Conditions

Activate Phase 1.7 when **any** of the following is true:

- User explicitly mentions external products/tools/competitors (e.g., "inspirado no PlexTrac", "como o Vulcan Cyber faz")
- User asks to compare the project with existing solutions
- User requests market research or competitive analysis as part of the brainstorming

> [!NOTE]
> Any brainstorming type can trigger this phase. The trigger is **user intent** (requesting comparison or external reference), not the brainstorming type.

If none of the trigger conditions are met, **skip this phase entirely** and proceed to Phase 2.

### Research Process

1. **Identify research targets** — Extract product names, concepts, or market segments from the user's request
2. **Search** — Use `search_web` or `mcp_docker_mcp_search` to find relevant pages (max **3-5 queries**)
3. **Deep dive** — Use `mcp_docker_mcp_fetch_content` to extract detailed content from the most relevant URLs (max **3 pages**)
4. **Synthesize** — Structure findings into:
   - Key features and capabilities
   - Architecture patterns or technical approaches
   - UX/UI design patterns
   - Pricing models (if relevant)
   - Strengths and weaknesses
5. **Integrate** — Feed research findings into Phase 2 (Structured Thinking) as additional input

### Guardrails

- **Maximum 3-5 search queries** per session — avoid excessive research
- **Maximum 3 pages** for deep content extraction
- **Always cite sources** with URLs in the final document
- **Clearly label** external research findings vs. internal project context
- **If no relevant results are found**, state so and proceed without blocking
- **Do NOT spend time on irrelevant tangents** — focus on what's directly useful for the brainstorming topic

### Output

Research notes to be used as input for Phase 2. These notes are NOT a deliverable — they feed into the structured thinking process and appear in the final document under an "External Research & Market Context" section.

---

## 6. Phase 2: Structured Thinking

Use the **sequential-thinking** MCP tool to analyze the problem systematically.

### Thinking Framework

Structure thoughts around these dimensions (adapt per type):

1. **Problem Definition** — What exactly are we solving?
2. **Current State** — What exists today? What works, what doesn't?
3. **External Context** — What can we learn from similar tools/competitors? (only if Phase 1.7 was executed)
4. **Options** — What are the possible approaches? (minimum 2-3)
5. **Trade-offs** — For each option: pros, cons, effort, risk, impact
6. **Constraints** — Technical, time, resource, business constraints
7. **Dependencies** — What blocks or is blocked by this?
8. **MVP vs Full** — What is the minimum viable version? What can wait?
9. **Recommendation** — Which option is best, and why?

### For `layout` Type — Additional Dimensions

- **User Flows** — Key interactions and navigation paths
- **Component Hierarchy** — Logical grouping and nesting of UI elements
- **Responsive Strategy** — How the layout adapts across breakpoints
- **Accessibility** — Keyboard navigation, screen readers, contrast
- **Visual Hierarchy** — Information priority and emphasis
- **Consistency** — Alignment with existing design patterns

### For `new-project` Type — Additional Dimensions

- **Naming** — 3-5 name suggestions with rationale
- **Target Audience** — Who uses this and why
- **Monetization** — Revenue model considerations (if applicable)
- **MVP Scope** — Minimum features for first release
- **Future Roadmap** — Features explicitly deferred

### For `architecture` Type — Additional Dimensions

- **Migration Path** — Step-by-step transition plan
- **Rollback Strategy** — How to revert if things go wrong
- **Performance Impact** — Expected improvements or regressions
- **Team Impact** — Learning curve, workflow changes

### For `guidance` Type — Additional Dimensions

- **Skill/Workflow Fit Analysis** — How well each candidate skill matches the request
- **Pipeline Recommendation** — Optimal sequence when multiple skills chain together
- **Gap Analysis** — What aspects are NOT covered by existing skills/workflows
- **Effort vs. Value** — Which path gives the best return for the user's time

---

## 7. Phase 3: Synthesize into Implementation Plan Artifact

Compile findings into a structured **Implementation Plan** artifact using [references/implementation-plan-template.md](references/implementation-plan-template.md).

This is NOT the final document — it is an **intermediate plan** for user review before generating the final brainstorming document.

### Implementation Plan Content

Use the required Antigravity UI for Implementation Plans. The plan must include:

1. **Objective & Justification** — What is being explicitly solved and why.
2. **Analysis Directions** — Options to be explored in the final document.
3. **Skill/Workflow Recommendations** — Best tools for the next steps.
4. **Scope** — What the final brainstorming document will cover and what will be excluded.
5. **User Review Required** — Decisions that need user input (Open Questions).

### Output Rules

1. Use the standard Antigravity `write_to_file` tool to create the artifact.
2. Set `IsArtifact: true` and `ArtifactType: 'implementation_plan'`.
3. The TargetFile must be exactly `<appDataDir>/brain/<conversation-id>/implementation_plan.md`
4. Write in **English** (document is AI input).
5. Be **specific and actionable** — no vague statements.
6. Clearly separate **what is decided** from **what needs user input**.
7. The plan must be **concise** — focus on structure, not full analysis.
8. **All file references must use project-relative paths** — never absolute `file:///` URIs.

---

## 8. Phase 4: Review Gate

Present the Implementation Plan artifact to the user for review. This is a **blocking gate**.

### Presentation

Use the `notify_user` tool with `BlockedOnUser=true` and `PathsToReview` pointing to the absolute path of the generated artifact (`<appDataDir>/brain/<conversation-id>/implementation_plan.md`).

Message in pt-BR:

> 📋 **Implementation Plan pronto para revisão.**
> Revise o documento e me diga se deseja ajustar algo, ou aprove para eu gerar o documento completo de brainstorming.

### Iteration

If the user requests changes:

1. Update the Implementation Plan using `replace_file_content` or `multi_replace_file_content`.
2. Re-present via `notify_user`.
3. Repeat until approved.

### On Approval

Proceed to Phase 5 (Generate Document).

---

## 9. Phase 5: Generate Final Document

After approval, generate the full brainstorming document using [references/output-template.md](references/output-template.md).

### Document Rules

1. Write in **English** (document is AI input).
2. Be **specific and actionable** — no vague statements.
3. Include **concrete examples** where helpful.
4. Quantify when possible (effort estimates, impact scores).
5. Clearly separate **facts** from **opinions/recommendations**.
6. Output must be **self-contained** — readable without conversation context.
7. Incorporate all feedback from the Review Gate into the final document.
8. **All file references must use project-relative paths** — never absolute `file:///` URIs. Example: `[DEMO_DATA.md](docs/DEMO_DATA.md)`, not `[DEMO_DATA.md](file:///absolute/path/to/docs/DEMO_DATA.md)`.

Save the final document to:

```text
docs/brainstorming/<NNNN>-<semantic-slug>.md
```

- `<NNNN>` is a 4-digit sequential number (e.g., `0001`, `0002`). Check existing files in `docs/brainstorming/` and use the next available number.
- Use a descriptive slug for the brainstorming output file (e.g., `0008-tenant-billing-redesign.md`).
- Create `docs/brainstorming/` directory if it doesn't exist.

Present the complete document and confirm in pt-BR:

> ✅ Documento de brainstorming gerado com sucesso.

---

## 10. Phase 6: Next Steps

After generating the document, suggest **contextually relevant** next steps based on the brainstorming type and the skills/workflows identified in Phase 1.5.

### Next Step Options by Type

**`new-project`:**

1. 📄 Gerar PRD (`/generate-prd`)
2. 💾 Salvar brainstorming e continuar depois

**`feature`:**

1. 📄 Gerar PRD (`/generate-prd`)
2. 🛠️ Planejar implementação (`/task-planner`)
3. 🏗️ Atualizar arquitetura (`/generate-architecture`)
4. 💾 Salvar brainstorming e continuar depois

**`architecture`:**

1. 🏗️ Gerar arquitetura (`/generate-architecture`)
2. 📋 Quebrar em tasks (`/generate-task-breakdown`)
3. 💾 Salvar brainstorming e continuar depois

**`skill`:**

1. 🔧 Criar a skill (`/skill-creator`)
2. 💾 Salvar brainstorming e continuar depois

**`refactor`:**

1. 🛠️ Planejar refatoração (`/task-planner`)
2. 📋 Quebrar em tasks (`/generate-task-breakdown`)
3. 💾 Salvar brainstorming e continuar depois

**`bug-analysis`:**

1. 🐛 Corrigir bug (`/bug-fixer`)
2. 🐙 Criar issue no GitHub
3. 💾 Salvar brainstorming e continuar depois

**`layout`:**

1. 🎨 Gerar design spec (`/generate-ui-design`)
2. 🛠️ Planejar implementação (`/task-planner`)
3. 📄 Gerar PRD (`/generate-prd`) — se for redesign completo
4. 💾 Salvar brainstorming e continuar depois

**`guidance`:**

1. 🚀 Executar a skill recomendada (indicar qual, com o comando)
2. 🔄 Brainstorming aprofundado sobre o tema (se o usuário quiser explorar mais antes de agir)
3. 📋 Ver pipeline completo recomendado
4. 💾 Salvar brainstorming e continuar depois

**`general`:**

1. 📄 Gerar PRD (`/generate-prd`)
2. 🛠️ Planejar implementação (`/task-planner`)
3. 💾 Salvar brainstorming e continuar depois

Always include "Salvar brainstorming" as the last option.

---

## 11. File Output

### Implementation Plan Artifact (Phase 3)

```text
<appDataDir>/brain/<conversation-id>/implementation_plan.md
```

### Final Document (Phase 5)

```text
docs/brainstorming/<NNNN>-<semantic-slug>.md
```

- `<NNNN>` is a 4-digit sequential number. Check existing files in `docs/brainstorming/` and use the next available number.
- Use a descriptive slug (e.g., `0008-tenant-billing-redesign.md`, `0009-new-reporting-module.md`)
- Create `docs/brainstorming/` directory if it doesn't exist.

---

## 12. Integration with Other Skills

This skill produces output designed as **input for other skills**. Ensure the documents:

1. Contain enough context for the target skill to work autonomously.
2. Use terminology consistent with existing `docs/` documentation.
3. Reference existing project artifacts by path when relevant.
4. Explicitly state what is decided vs. what needs further definition.

### Discovery Layer

This skill also functions as a **discovery layer** for the entire skill/workflow ecosystem:

- When users don't know which tool to use, brainstorming analyzes their needs and recommends the right path.
- When users invoke brainstorming directly, the skill proactively identifies which skills/workflows will be relevant for next steps.
- The skill catalog in Phase 1.5 must be kept in sync with the actual skills directory — always read real SKILL.md files rather than relying solely on the inline catalog.
