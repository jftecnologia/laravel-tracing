# Init-Project Questionnaire

Guided questions organized by topic. Only ask questions that couldn't be auto-detected.

---

## 1. Project Identity

**Always ask (cannot be auto-detected):**

### Q1.1: Project Name

```markdown
1️⃣ **Nome do projeto** — Como devo chamar o projeto na documentação?
Ex: "Meu SaaS", "API Financeira", "Package de Pagamentos"
```

### Q1.2: Project Description

```markdown
2️⃣ **Descrição** — Uma frase curta descrevendo o projeto
Ex: "Plataforma de gestão de clientes para escritórios de advocacia"
```

---

## 2. Architecture Decisions

**Ask only if not detectable from code:**

### Q2.1: Architecture Style

```markdown
3️⃣ **Arquitetura** — Qual o modelo da aplicação?
a) Single-tenant (uma instância por cliente)
b) Multi-tenant (vários clientes compartilham a mesma instância)
c) SaaS (multi-tenant com billing)
d) API-only (headless, sem interface)
e) Monolítica tradicional
```

### Q2.2: Auth Strategy (if not detected)

```markdown
4️⃣ **Autenticação** — Qual a estratégia de autenticação?
a) Fortify (headless — login, registro, 2FA)
b) Sanctum (API tokens)
c) Passport (OAuth2)
d) Custom
e) Nenhuma por enquanto
```

---

## 3. Frontend Decisions

**Ask only if not detectable:**

### Q3.1: Frontend Framework (if not detected)

```markdown
5️⃣ **Frontend** — Qual framework frontend?
a) React + Inertia.js (padrão)
b) Livewire
c) Blade puro
d) API-only (sem frontend)
e) Outro
```

### Q3.2: UI Component Library (if not detected)

```markdown
6️⃣ **Componentes UI** — Qual biblioteca de componentes?
a) Radix UI (headless primitives — padrão)
b) Shadcn/UI
c) Headless UI
d) Material UI
e) Nenhuma específica
```

---

## 4. Internationalization

### Q4.1: Languages

```markdown
7️⃣ **Idiomas** — Quais idiomas o projeto suporta?
a) en + pt_BR (padrão)
b) Apenas en
c) Apenas pt_BR
d) Outros (especifique)
```

---

## 5. Database

### Q5.1: Database Driver (if not detected from .env)

```markdown
8️⃣ **Banco de dados** — Qual banco?
a) MySQL (padrão)
b) PostgreSQL
c) SQLite
d) SQL Server
```

---

## 6. Confirmation

### Q6: Quick Confirm

For projects where everything was auto-detected:

```markdown
## 🎯 Sua Decisão

Detectei tudo automaticamente. Preciso apenas de:

1️⃣ **Nome** — Como chamar o projeto?
2️⃣ **Descrição** — Uma frase curta
3️⃣ **Tudo certo, use os padrões detectados** (nome = pasta do projeto)

> Responda o número ou comente livremente.
```

---

## Presentation Rules

1. Group questions by topic — never ask more than 5 at once
2. Always show auto-detected values for confirmation
3. Use the emoji-numbered format from `02-transparency.md`
4. Accept free-form answers ("use React with Inertia") in addition to numbered options
5. If the user says "padrão" or "default", accept all defaults
6. Maximum 2 rounds of questions
7. Store the chosen locale in `.ai-guidelines.json` as `locale` field

---

## 7. Agent Communication

### Q7.1: Agent Locale

```markdown
9️⃣ **Idioma do agente** — Em qual idioma devo me comunicar com você?
a) pt-BR (padrão — respostas e documentação em pt-BR)
b) en (respostas e documentação em inglês)
c) Outro (especifique)
```

This sets the `locale` field in `.ai-guidelines.json` and affects:

- Agent responses and explanations
- Code comments (when not using English convention)
- Documentation generation language

> **Note:** Commit messages, branch names, and PR descriptions are **always in English** regardless of locale setting, following Git/GitHub conventions.

---

## 8. Monorepo (if detected)

### Q8.1: Workspace Scope

```markdown
🔟 **Monorepo** — Detectei um monorepo. Onde devo aplicar as guidelines?
a) Raiz do monorepo (todas as workspaces)
b) Workspace específica (especifique qual)
c) Cada workspace independentemente
```
