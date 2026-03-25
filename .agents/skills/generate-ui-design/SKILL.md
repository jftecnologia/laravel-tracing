---
name: generate-ui-design
description: Generate UI/UX design specification documents for the application. Use when the user asks to define the interface design, create UI specs, plan screens/pages, define a design system, establish visual identity, or specify user interactions. This skill creates structured design documentation in docs/design/ based on PRD requirements and architecture. It fits in the pipeline between architecture and task breakdown.
---

# UI Design Specification Generator

Generate comprehensive UI/UX design specifications that define the visual identity, page inventory, component hierarchy, and interaction patterns for the application.

**Pipeline position**: PRD → Architecture → **UI Design** → Task Breakdown → Implementation

---

## 1. Load Context

Read the following files to understand what needs to be designed:

1. **Product Requirements**: `docs/PRD.md`
    - Functional requirements (FR-XX)
    - User flows (high-level)
    - Target users
    - Non-functional requirements (usability, accessibility)

2. **Architecture** (if exists): `docs/architecture/*.md`
    - Component structure
    - Data flows
    - Pages and routes defined

3. **Existing Design** (if exists): `docs/design/*.md`
    - Check what design documentation already exists
    - Update rather than recreate when possible

4. **Existing Frontend Code**: `resources/js/`
    - Check existing components in `resources/js/components/ui/`
    - Check existing layouts in `resources/js/layouts/`
    - Check existing pages in `resources/js/pages/`
    - Understand what UI primitives are already available

---

## 2. Clarify Design Direction

Before generating any documentation, **resolve all ambiguities** with the user. The skill must not produce design specs with unresolved decisions.

### Mandatory Questions

Ask in **Portuguese (pt-BR)**. Group questions logically, ask **up to 6 questions** per round.

#### Round 1: Visual Identity

Ask these unless the user already provided clear direction:

1. **Design style**: "Qual o estilo visual desejado?" — Suggest options:
    - Clean/Minimal (foco em espaço em branco e tipografia)
    - Corporate/Professional (formal, estruturado)
    - Modern/Bold (cores vibrantes, gradientes, sombras)
    - Dashboard/Data-heavy (densidade de informação, tabelas, gráficos)
    - Custom (descreva)

2. **Color scheme**: "Qual a paleta de cores?" — Suggest options:
    - Baseada em uma marca existente (forneça as cores)
    - Tons neutros com accent color (qual cor de destaque?)
    - Dark-first (interface escura como padrão)
    - Light-first (interface clara como padrão)
    - Ambos (dark + light mode)

3. **Reference/Inspiration**: "Existe algum site ou aplicação como referência visual?"

#### Round 2: Structure & Interaction

4. **Layout pattern**: "Qual padrão de layout?" — Suggest based on existing project layouts:
    - Sidebar navigation (já existe: `app-sidebar-layout`)
    - Top header navigation (já existe: `app-header-layout`)
    - Combined (sidebar + header)
    - Custom

5. **Responsive priority**: "Qual a prioridade de responsividade?"
    - Desktop-first (admin/dashboard apps)
    - Mobile-first (consumer-facing apps)
    - Ambos igualmente

6. **Key interaction patterns**: "Quais padrões de interação são importantes?"
    - Formulários complexos (multi-step, validação em tempo real)
    - Tabelas/listas com filtros e paginação
    - Modais e drawers para ações rápidas
    - Real-time updates (notificações, live data)
    - Drag and drop
    - Outros (descreva)

#### Round 3 (if needed): Specific Decisions

Only ask if not clear from previous answers:

- **Typography**: "Alguma preferência de tipografia?" (system fonts, Google Fonts, custom)
- **Icon style**: "Estilo de ícones?" (o projeto já usa Lucide React)
- **Density**: "Qual a densidade da interface?" (compact, comfortable, spacious)
- **Accessibility level**: "Qual o nível de acessibilidade?" (WCAG AA mínimo, AAA desejado)

### When to Suggest Instead of Ask

If the user says "sugira" or "decida por mim":

- Use **Clean/Minimal** style as default
- Use **neutral palette with a single accent color** (indigo/blue)
- Use **existing sidebar layout** from the project
- Use **desktop-first** for admin/internal apps, **mobile-first** for public apps
- Use **WCAG AA** accessibility level
- Use **Lucide React** icons (already in the project)
- Use **comfortable** density

Document all decisions made (whether user-chosen or suggested) in the output.

---

## 3. Design Documentation Structure

Generate design documents in `docs/design/`. See [references/document-templates.md](references/document-templates.md) for complete templates.

### Core Documents (always generate)

1. **Design System** (`DESIGN_SYSTEM.md`)
    - Color palette (Tailwind CSS tokens)
    - Typography scale
    - Spacing scale
    - Border radius, shadows
    - Dark mode mapping
    - Icon usage conventions

2. **Page Inventory** (`PAGE_INVENTORY.md`)
    - Complete list of pages/screens
    - Page hierarchy (grouped by section)
    - Layout assignment per page
    - Auth requirements per page
    - Page-level props from backend

3. **Component Hierarchy** (`COMPONENTS.md`)
    - Shared components inventory
    - Component variants and props
    - Composition patterns
    - Which existing `ui/` components to reuse
    - New components to create

4. **Interaction Patterns** (`INTERACTIONS.md`)
    - Form patterns (validation, submission, feedback)
    - Navigation patterns (breadcrumbs, back, tabs)
    - Feedback patterns (toasts, alerts, loading states)
    - Error patterns (inline, page-level, 404/500)
    - Empty states
    - Skeleton/loading patterns

### Optional Documents (create only if relevant)

- **Responsive Strategy** (`RESPONSIVE.md`) — If responsive complexity is high
- **Accessibility Guide** (`ACCESSIBILITY.md`) — If accessibility requirements go beyond standard WCAG AA

---

## 4. Design System Rules

### Color Tokens

Map all colors to Tailwind CSS `@theme` tokens. Use semantic naming:

```css
@theme {
  --color-primary: oklch(0.55 0.15 250);
  --color-primary-foreground: oklch(0.98 0 0);
  --color-secondary: oklch(0.70 0.03 260);
  --color-accent: oklch(0.65 0.18 145);
  --color-destructive: oklch(0.55 0.2 25);
  --color-muted: oklch(0.92 0.01 260);
  --color-muted-foreground: oklch(0.55 0.02 260);
}
```

### Typography

Define a scale that maps to Tailwind classes:

| Role        | Tailwind Class             | Usage            |
|-------------|----------------------------|------------------|
| Display     | `text-4xl font-bold`       | Page titles      |
| Heading     | `text-2xl font-semibold`   | Section headers  |
| Subheading  | `text-lg font-medium`      | Card titles      |
| Body        | `text-base`                | Default text     |
| Small       | `text-sm`                  | Secondary info   |
| Caption     | `text-xs`                  | Labels, metadata |

### Component States

Every interactive component must define:
- Default, Hover, Focus, Active, Disabled states
- Loading state (when applicable)
- Error state (when applicable)

---

## 5. Page Description Format

For each page in the inventory, describe:

```markdown
### [Page Name]

**Route**: `/path` (route name: `route.name`)
**Layout**: [sidebar | header | auth | none]
**Auth**: [required | guest | public]

**Purpose**: [One sentence]

**Sections**:
1. [Section name] — [description, components used]
2. [Section name] — [description, components used]

**Data** (from backend):
- `prop_name`: type — description

**Actions**:
- [Action description] → [what happens]

**States**:
- Loading: [skeleton pattern]
- Empty: [empty state message/illustration]
- Error: [error handling]
```

---

## 6. File Output

Write all design documentation to:

```text
docs/design/
├── DESIGN_SYSTEM.md
├── PAGE_INVENTORY.md
├── COMPONENTS.md
├── INTERACTIONS.md
├── RESPONSIVE.md          (optional)
└── ACCESSIBILITY.md       (optional)
```

Use **UPPERCASE.md** for design documents (consistent with architecture docs).

---

## 7. Validation Checklist

Before completing, verify:

- [ ] All design decisions are resolved (no open questions)
- [ ] Color palette is defined with Tailwind tokens
- [ ] Every page from the PRD user flows has an entry in PAGE_INVENTORY.md
- [ ] Component hierarchy references existing `ui/` components
- [ ] Interaction patterns cover: forms, navigation, feedback, errors, loading, empty states
- [ ] Dark mode strategy is defined (if applicable)
- [ ] Responsive strategy is defined
- [ ] All functional requirements (FR-XX) can be mapped to pages/interactions

---

## 8. Cross-References

After creating design docs:

1. Ensure PRD user flows trace to specific pages
2. Ensure architecture components map to UI components
3. Reference existing `resources/js/components/ui/` components by name
4. Note which Radix UI primitives are needed (already available in project)

---

## Completion Criteria

UI Design specification is complete when:

1. All user questions are answered and decisions documented
2. Design system is defined with Tailwind-compatible tokens
3. Every screen/page is inventoried with layout and sections
4. Component hierarchy identifies reuse and new components needed
5. Interaction patterns are comprehensive
6. Documents can be used by `/generate-task-breakdown` to create frontend tasks
7. Documents can be used by `/frontend-development` skill during implementation
