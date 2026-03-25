# Design Document Templates

Templates for each design document. Adapt sections as needed for the specific project.

---

## DESIGN_SYSTEM.md Template

```markdown
# Design System

**Purpose**: Define the visual language and design tokens for the application.

---

## Color Palette

### Primary Colors

| Token                     | Value                  | Usage                  |
|---------------------------|------------------------|------------------------|
| `--color-primary`         | oklch(...)             | Primary actions, links |
| `--color-primary-foreground` | oklch(...)          | Text on primary        |

### Semantic Colors

| Token                     | Value                  | Usage                  |
|---------------------------|------------------------|------------------------|
| `--color-destructive`     | oklch(...)             | Delete, errors         |
| `--color-success`         | oklch(...)             | Success feedback       |
| `--color-warning`         | oklch(...)             | Warnings               |
| `--color-info`            | oklch(...)             | Informational          |

### Neutral Colors

| Token                     | Value                  | Usage                  |
|---------------------------|------------------------|------------------------|
| `--color-background`      | oklch(...)             | Page background        |
| `--color-foreground`      | oklch(...)             | Default text           |
| `--color-muted`           | oklch(...)             | Muted backgrounds      |
| `--color-muted-foreground`| oklch(...)             | Secondary text         |
| `--color-border`          | oklch(...)             | Borders, dividers      |

### Dark Mode Mapping

| Light Token       | Dark Value            |
|-------------------|-----------------------|
| `background`      | oklch(...)            |
| `foreground`      | oklch(...)            |
| ...               | ...                   |

---

## Typography

### Font Family

- **Primary**: [font name] — headings and body
- **Mono**: [font name] — code blocks

### Type Scale

| Role       | Class                      | Size   | Weight    | Line Height |
|------------|----------------------------|--------|-----------|-------------|
| Display    | `text-4xl font-bold`       | 36px   | 700       | 1.1         |
| Heading    | `text-2xl font-semibold`   | 24px   | 600       | 1.2         |
| Subheading | `text-lg font-medium`      | 18px   | 500       | 1.4         |
| Body       | `text-base`                | 16px   | 400       | 1.5         |
| Small      | `text-sm`                  | 14px   | 400       | 1.4         |
| Caption    | `text-xs text-muted-foreground` | 12px | 400    | 1.3         |

---

## Spacing

| Scale  | Value  | Usage                              |
|--------|--------|------------------------------------|
| xs     | 4px    | Tight spacing (icon gaps)          |
| sm     | 8px    | Compact spacing (form fields)      |
| md     | 16px   | Default spacing (section padding)  |
| lg     | 24px   | Comfortable spacing (card gaps)    |
| xl     | 32px   | Section separation                 |
| 2xl    | 48px   | Page section gaps                  |

---

## Border & Radius

| Element        | Radius Class   | Shadow Class     |
|----------------|----------------|------------------|
| Buttons        | `rounded-md`   | `shadow-xs`      |
| Cards          | `rounded-lg`   | `shadow-sm`      |
| Modals/Dialogs | `rounded-xl`   | `shadow-lg`      |
| Inputs         | `rounded-md`   | -                |
| Badges         | `rounded-full` | -                |

---

## Icons

- **Library**: Lucide React
- **Default size**: `size-4` (16px) inline, `size-5` (20px) in buttons
- **Style**: Consistent stroke width, no mixing with other icon sets

---

## Decisions Log

| Decision            | Choice              | Rationale                        |
|---------------------|----------------------|----------------------------------|
| Design style        | [choice]             | [reason]                         |
| Color scheme        | [choice]             | [reason]                         |
| Dark mode           | [yes/no]             | [reason]                         |
| Typography          | [choice]             | [reason]                         |
| Density             | [choice]             | [reason]                         |
```

---

## PAGE_INVENTORY.md Template

```markdown
# Page Inventory

**Purpose**: Catalog all pages/screens with their layout, auth requirements, and content sections.

---

## Page Map

### [Section Name] (e.g., Authentication)

| Page               | Route            | Layout   | Auth     | Priority |
|--------------------|------------------|----------|----------|----------|
| Login              | `/login`         | auth     | guest    | High     |
| Register           | `/register`      | auth     | guest    | High     |
| ...                | ...              | ...      | ...      | ...      |

### [Section Name] (e.g., Dashboard)

| Page               | Route            | Layout   | Auth     | Priority |
|--------------------|------------------|----------|----------|----------|
| Dashboard          | `/dashboard`     | app      | required | High     |
| ...                | ...              | ...      | ...      | ...      |

---

## Page Details

### [Page Name]

**Route**: `/path` (route name: `route.name`)
**Layout**: [sidebar | header | auth | none]
**Auth**: [required | guest | public]
**Requirements**: FR-XX, FR-YY

**Purpose**: [One sentence]

**Sections**:

1. **[Section name]**
   - Description: [what this section shows/does]
   - Components: [list of components used]
   - Data: `propName` (type)

2. **[Section name]**
   - Description: [what]
   - Components: [list]
   - Data: `propName` (type)

**Actions**:
- [User action] → [system response]
- [User action] → [system response]

**States**:
- **Loading**: [skeleton description]
- **Empty**: [empty state description]
- **Error**: [error handling description]

---

[Repeat for all pages...]
```

---

## COMPONENTS.md Template

```markdown
# Component Hierarchy

**Purpose**: Define the component architecture, reuse strategy, and new components needed.

---

## Existing UI Components (from `resources/js/components/ui/`)

| Component       | Used For                  | Status    |
|-----------------|---------------------------|-----------|
| Button          | Primary actions           | Available |
| Card            | Content containers        | Available |
| Dialog          | Modal confirmations       | Available |
| ...             | ...                       | ...       |

## Shared Components (from `resources/js/components/`)

| Component       | Used For                  | Status    |
|-----------------|---------------------------|-----------|
| AppHeader       | Page header               | Available |
| Breadcrumbs     | Navigation context        | Available |
| ...             | ...                       | ...       |

## New Components Needed

### [Component Name]

**Type**: [ui | shared | page-specific]
**Location**: `resources/js/components/[path].tsx`
**Used in**: [list of pages]

**Props**:
| Prop     | Type              | Required | Description         |
|----------|-------------------|----------|---------------------|
| name     | string            | yes      | ...                 |
| variant  | 'default' \| 'sm' | no       | ...                 |

**Variants** (if using CVA):
- `default`: [description]
- `compact`: [description]

---

## Component Composition Patterns

### [Pattern Name] (e.g., Data Table with Filters)

[Description of how components compose together]

```tsx
<PageContainer>
  <PageHeader title="..." actions={<Button>Add</Button>} />
  <FilterBar filters={[...]} />
  <DataTable columns={[...]} data={data} />
  <Pagination meta={meta} />
</PageContainer>
```

---

## Component Decision Rules

- Reuse `ui/` components before creating new ones
- Create shared components when used in 2+ pages
- Page-specific components stay in the page file unless > 50 lines
```text

---

## INTERACTIONS.md Template

```markdown
# Interaction Patterns

**Purpose**: Define consistent interaction patterns across the application.

---

## Form Patterns

### Standard Form
- Use Inertia `<Form>` component (recommended) or `useForm` hook
- Show inline validation errors below each field
- Disable submit button during processing
- Show success feedback via toast or inline message

### Multi-Step Form
- Step indicator at top (numbered or progress bar)
- Validate each step before advancing
- Allow going back without losing data
- Submit only on final step

---

## Navigation Patterns

### Primary Navigation
- [Sidebar | Header] with active state indication
- Collapsible on mobile

### Breadcrumbs
- Show on all pages except top-level
- Use existing `<Breadcrumbs>` component

### Tabs
- Use for switching between views on the same page
- Active tab with visual indicator
- No full page reload (client-side only)

---

## Feedback Patterns

### Success
- Toast notification for background actions
- Inline success message for form submissions
- Auto-dismiss after 5 seconds

### Error
- Inline field errors for validation (below each field)
- Alert banner for server errors (top of form)
- Full error page for 404/500

### Loading
- Skeleton screens for initial page loads (deferred props)
- Spinner for button actions (`processing` state)
- Progress bar for file uploads

### Empty States
- Illustration or icon + message + primary action
- Example: "No users yet. Create your first user."

---

## Modal/Dialog Patterns

### Confirmation
- Use for destructive actions (delete, cancel)
- Clear title, description, and two actions (cancel + confirm)
- Destructive action button in red

### Form Modal
- Use for quick creation/editing without leaving page
- Close on successful submission
- Show errors inline within modal

---

## Table/List Patterns

### Data Table
- Column headers with sort indicators
- Row hover state
- Pagination below table
- Optional: filters above table, bulk actions

### Simple List
- Card-based or row-based items
- Click to navigate to detail page
- Optional: inline actions (edit, delete)
```
