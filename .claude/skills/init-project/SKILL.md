---
name: init-project
description: "Interactive onboarding skill that adapts AI guidelines to a specific project or package. Auto-detects project context (type, stack, versions, packages), asks guided questions about tech decisions, and updates guidelines/docs to match. Use when first installing these guidelines in a project, when the project context changes significantly, or when the user asks to 'initialize', 'configure guidelines', 'adapt guidelines', 'init project', 'iniciar projeto', 'configurar guidelines', 'setup guidelines', 'onboarding', '/init-project'."
---

# Init Project

Interactive onboarding that adapts these AI development guidelines to a specific project or package. Runs on first use or when guidelines need updating.

**Output**: Updated `CLAUDE.md`, `docs/engineering/STACK.md`, and other guideline files adapted to the project context.

---

## Related References

- [references/questionnaire.md](references/questionnaire.md) — Guided questionnaire for project configuration

---

## 1. Workflow Overview

```text
Phase 1: Detect → Phase 2: Ask → Phase 3: Update → Phase 4: Verify
```

All phases are mandatory. User interaction happens in Phase 2.

---

## 2. Phase 1: Auto-Detection

Silently gather as much project context as possible before asking the user anything.

### Detection Sources

| Source                         | What to detect                                                            |
| ------------------------------ | ------------------------------------------------------------------------- |
| `composer.json`                | PHP version, Laravel version, all packages, project type (app vs package) |
| `package.json`                 | Node version, frontend frameworks, bundler, UI libraries                  |
| `artisan` file exists          | Confirms Laravel application (not package)                                |
| `config/` directory            | Installed packages by config files, auth strategy                         |
| `database/` directory          | Migration patterns, seeder structure                                      |
| `resources/js/`                | Frontend framework, component library                                     |
| `.env.example`                 | Database driver, cache driver, queue driver                               |
| `phpunit.xml` / `phpstan.neon` | Testing and analysis configuration                                        |

### MCP Tools (when available)

- `application-info` — PHP/Laravel versions, installed packages, Eloquent models
- `database-schema` — Current data model (summary mode)
- `list-routes` — Route patterns and middleware

### Detection Rules

1. **No `artisan` + has `composer.json` with `"type": "library"`** → package
2. **Has `artisan` + `config/app.php`** → Laravel application
3. **Has `resources/js/` + `@inertiajs`** → Inertia.js frontend
4. **Has `tailwind.config.*` or `@tailwindcss`** → Tailwind CSS
5. **Has `resources/views/` + no `@inertiajs`** → Blade/Livewire frontend
6. **Has `config/fortify.php`** → Fortify auth
7. **Has `config/sanctum.php`** → Sanctum auth (API tokens)
8. **Has `package.json` without `composer.json`** → Node.js project
9. **Has `go.mod`** → Go project
10. **Has `requirements.txt` or `pyproject.toml`** → Python project
11. **No primary language manifests (`composer.json`, `package.json`, `go.mod`, `pyproject.toml`, `requirements.txt`) AND has `.sh` scripts as main runtime** → Bash/Shell project
12. **Has `packages/`, `apps/`, or workspace config** (`pnpm-workspace.yaml`, `workspaces` in `package.json`, `composer.json` with `repositories.*.type=path`) → Monorepo

### Output

A structured detection report (internal, not shown to user yet):

```text
Project Type: application | package | monorepo
Project Name: <from composer.json or package.json>
Stack: Laravel | PHP | Node.js | Python | Go | Bash | Multi-stack
Monorepo: yes | no (if yes, list workspace packages)
Locale: <detected locale or 'en' default>
PHP Version: <detected> (if applicable)
Laravel Version: <detected> (if applicable)
Node Version: <detected> (if applicable)
Frontend: React+Inertia | Livewire | Blade | API-only | None
Styling: Tailwind v4 | Tailwind v3 | None
Database: MySQL | PostgreSQL | SQLite | None
Auth: Fortify | Sanctum | None
Key Packages: [list]
Models: [list if available]
```

---

## 2.5. Phase 1.5: Gap Analysis

After detection, cross-reference detected/desired stack with available stack packs and skills:

### Gap Detection Steps

1. List available stack packs: scan `.agents/skills/stack-*/SKILL.md`
2. List available workflows: scan `.agents/workflows/*.md`
3. Compare against detected stack:
   - Is the stack pack for the detected stack present?
   - Are relevant workflows available?
4. Identify gaps (missing packs, inactive skills)

### For Existing Projects (detection mode)

If gaps are found, present them:

```markdown
## 🔍 Gap Analysis

Detectei que seu projeto usa **Node.js + TypeScript**, mas faltam:

1️⃣ **stack-node** — Patterns, quality gates e testing para Node.js
2️⃣ **Workflow /deploy** — Checklist de deploy (não configurado)

> Deseja ativar esses componentes? (ex: "1 e 2", "todos", "nenhum")
```

### For New Projects (inception mode)

If the user is describing a new project and specifies the desired stack:

1. Check if the stack pack exists
2. If missing, suggest using `/add-stack` workflow first
3. If present, suggest activation and continue with init

### Rules

- Do NOT block initialization for missing packs — report and continue
- Always suggest activation, never auto-activate without user consent
- If a Laravel project: all core skills are available, no gaps expected

---

## 3. Phase 2: Guided Questionnaire

Present detected information and ask for confirmation/corrections using the interactive menu format from `02-transparency.md`.

### Presentation Format

Load [references/questionnaire.md](references/questionnaire.md) for the full questionnaire structure.

Present the detection results first, then ask only what couldn't be auto-detected or needs confirmation:

```markdown
## 🔍 Detecção Automática

Detectei as seguintes informações do seu projeto:

| Item     | Detectado                |
| -------- | ------------------------ |
| Tipo     | Laravel Application      |
| PHP      | 8.4.2                    |
| Laravel  | 12.1.0                   |
| Frontend | React 19 + Inertia.js v2 |
| Styling  | Tailwind CSS v4          |
| Database | MySQL 9                  |
| Auth     | Fortify                  |

## 🎯 Sua Decisão

As informações acima estão corretas? Preciso confirmar alguns pontos:

1️⃣ **Nome do projeto** — Como devo chamar o projeto na documentação?
2️⃣ **Descrição** — Uma frase descrevendo o projeto
3️⃣ **Arquitetura** — Multi-tenant, single-tenant, SaaS, ou API-only?
4️⃣ **i18n** — Quais idiomas? (padrão: en + pt_BR)
5️⃣ **Tudo correto, seguir com os padrões detectados**

> Responda o número, combine opções (ex: "1 e 3"), ou comente livremente.
```

### Questionnaire Topics

Only ask about items that:

1. Could NOT be auto-detected
2. Have multiple valid interpretations
3. Significantly affect guideline configuration

See [references/questionnaire.md](references/questionnaire.md) for the full list of questions by topic.

### Rules

- Ask questions in **Portuguese (pt-BR)**
- Maximum 2 rounds of questions (avoid questionnaire fatigue)
- If the user says "use defaults" or equivalent, accept all detected values
- Group related questions together

---

## 4. Phase 3: Update Guidelines

Based on detection results and user answers, update the following files:

### Always Update

| File                        | What to update                                   |
| --------------------------- | ------------------------------------------------ |
| `CLAUDE.md` (line 3)        | Project name and description                     |
| `docs/engineering/STACK.md` | Actual versions, detected packages, project type |

### Conditional Updates

| Condition               | Files to update                                                                           |
| ----------------------- | ----------------------------------------------------------------------------------------- |
| No Fortify detected     | Remove `developing-with-fortify` from skill table in `CLAUDE.md`                          |
| No Inertia/React        | Remove `frontend-development` from skill table; update CODE_STANDARDS.md frontend section |
| Package (not app)       | Simplify CLAUDE.md structure section; remove app-specific dirs                            |
| Different DB driver     | Update DATABASE_PATTERNS.md examples                                                      |
| No i18n needed          | Remove `i18n-manager` from skill table                                                    |
| Multi-tenant detected   | Add multi-tenant notes to CLAUDE.md and DATABASE_PATTERNS.md                              |
| Different auth strategy | Update relevant auth references                                                           |

### Update Rules

1. Use `replace_file_content` or `multi_replace_file_content` — never overwrite entire files
2. Preserve all non-project-specific content
3. Only update what's necessary based on detected differences
4. If a version in STACK.md differs from detected → update STACK.md
5. If a skill is irrelevant to the project → comment it in the skill table, don't delete

---

## 5. Phase 4: Verify & Summary

After updates, present a summary to the user:

```markdown
## ✅ Guidelines configuradas!

### O que foi atualizado:

- `CLAUDE.md` — Nome e descrição do projeto
- `docs/engineering/STACK.md` — Versões atuais do PHP 8.4, Laravel 12, etc.
- [other files...]

### Stack detectada:

| Componente | Versão |
| ---------- | ------ |
| PHP        | 8.4.2  |
| Laravel    | 12.1.0 |
| ...        | ...    |

### Próximos passos recomendados:

1️⃣ 📋 Brainstorming — Estruturar ideias (`/brainstorming`)
2️⃣ 📄 Gerar PRD — Definir requisitos (`/generate-prd`)
3️⃣ 🏗️ Gerar Arquitetura — Definir estrutura do sistema (`/generate-architecture`)
4️⃣ 💡 Explorar skills disponíveis — Ver o que a IA pode fazer (`/brainstorming guidance`)
```

---

## 6. Edge Cases

### No Composer/Package JSON Found

- Assume fresh project; ask user about intended stack
- Offer to create initial structure

### Already Initialized

If detection shows guidelines were already configured (e.g., CLAUDE.md has a non-template project name):

```markdown
⚠️ As guidelines já parecem configuradas para o projeto "[Nome]".
Deseja reconfigurá-las? Isso atualizará versões e configurações.

1️⃣ **Sim, reconfigurar** — Atualizar para as versões e configurações atuais
2️⃣ **Apenas versões** — Atualizar só as versões dos pacotes
3️⃣ **Cancelar**
```

### Package Development

For packages (no `artisan`), simplify:

- Remove app-specific directory structure from CLAUDE.md
- Remove controller/route references from CODE_STANDARDS.md
- Keep testing, code standards, and quality gates
- Adjust skill table (remove app-only skills like `browser-qa-tester`)

---

## Completion Criteria

Init is complete when:

1. ✅ `CLAUDE.md` reflects actual project name and description
2. ✅ `docs/engineering/STACK.md` reflects actual versions and packages
3. ✅ Irrelevant skills are marked in CLAUDE.md
4. ✅ User received summary with next steps
5. ✅ Guidelines are ready for immediate use with other skills
