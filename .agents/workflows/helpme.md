---
description: "Universal orchestrator — understands what you need, clarifies doubts, routes to the best skill/workflow, and tracks progress. Use when you don't know which skill to use, when you want intelligent routing, or as a general entry point for any task (e.g., 'helpme quero criar X', 'helpme tem um bug', 'helpme não sei por onde começar', 'helpme preciso melhorar a UI')."
---

# HelpMe — Universal Orchestrator

Single entry point for any development task. Understands intent, clarifies, routes to the best skill/workflow, and tracks progress.

## Flow

```text
Listen → Classify → Clarify → Route → Track → Report
```

## Steps

1. **Listen** — Read the user's request carefully. Do NOT jump to a skill immediately.

2. **Classify intent** — Map the request to one of these categories:

   | Category      | Signals                                         | Route To                          |
   | ------------- | ----------------------------------------------- | --------------------------------- |
   | **New idea**  | "quero criar", "pensei em", "como seria"        | `brainstorming` → `generate-prd`  |
   | **Implement** | "implementar", "criar", "adicionar", "fazer"    | `task-planner` → `implement-task` |
   | **Bug**       | "bug", "erro", "não funciona", "quebrou"        | `bug-fixer` or `implement-task`   |
   | **Debug**     | "investigar", "entender por que", "debug"       | `/debug` workflow                 |
   | **Refactor**  | "refatorar", "melhorar código", "limpar"        | `/refactor` workflow              |
   | **Test**      | "testar", "criar testes", "cobertura"           | `/test` workflow                  |
   | **UI/UX**     | "feio", "melhorar visual", "UI", "UX", "design" | `/improve-ui` workflow            |
   | **Deploy**    | "deploy", "produção", "staging"                 | `/deploy` workflow                |
   | **Review**    | "review", "PR", "antes de mergear"              | `/code-review` workflow           |
   | **Docs**      | "documentar", "docs", "README"                  | `/docs` workflow                  |
   | **Status**    | "status", "progresso", "onde parei"             | `/status` workflow                |
   | **Stack**     | "adicionar Node", "Python", "Go"                | `/add-stack` workflow             |
   | **Lost**      | "não sei", "por onde", "me ajude"               | `brainstorming` (guidance mode)   |
   | **Security**  | "segurança", "vulnerabilidade", "OWASP"         | `security-analyst`                |
   | **Translate** | "traduzir", "i18n", "tradução"                  | `i18n-manager`                    |

3. **Clarify** — If intent is ambiguous, ask **at most 3 questions** in pt-BR:

   ```markdown
   Entendi que você quer [interpretação]. Preciso confirmar:

   1️⃣ [Question about scope]
   2️⃣ [Question about priority/approach]
   3️⃣ [Question about constraints]

   > Responda os números ou comente livremente.
   ```

   If intent is clear, say: "Entendido. Vou rotear para [skill/workflow]."

4. **Check for gaps** — Before routing:
   - Is the target skill available?
   - Is a stack pack needed but missing?
   - Are there prerequisite docs missing (PRD, architecture)?
   - Report gaps and suggest solutions.

5. **Route** — Invoke the chosen skill or workflow. State clearly:

   > 🚀 Roteando para `[skill/workflow]` — [brief reason why]

6. **Track progress** — After routing:
   - Note what was done, what skill was used
   - If the task spans multiple skills, track transitions
   - On completion, summarize:

     ```markdown
     ## ✅ Concluído

     - **Solicitação**: [what user asked]
     - **Caminho**: [skill1] → [skill2]
     - **Resultado**: [what was done]
     - **Próximos passos**: [recommendations]
     ```

## Rules

- NEVER guess the user's intent — clarify when ambiguous
- NEVER skip the classification step
- Prefer WORKFLOWS over skills when both exist for the same task
- If the request is very simple (< 1 min), just do it directly without heavy routing
- Always answer in Portuguese (pt-BR)

> ⚠️ **Planning gate**: When routing to planning skills, ensure Security and i18n considerations are included (see `AGENT_FLOW.md` §3.1)
> ⚠️ **Post-code gate**: When routing to code-producing skills, ensure Post-Code Gates are executed (see `AGENT_FLOW.md` §3.2)
