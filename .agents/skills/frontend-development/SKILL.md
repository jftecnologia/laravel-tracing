---
name: frontend-development
description: 'Implements frontend interfaces using React 19, Inertia.js v2, Tailwind CSS v4, Radix UI, and Wayfinder. Activates when creating or modifying React pages, components, layouts, or forms; styling with Tailwind; using Radix UI primitives; referencing backend routes via Wayfinder; implementing deferred props, prefetching, or polling; or when the user mentions frontend, UI, interface, components, pages, forms, navigation, styling, or visual changes.'
---

# Frontend Development

Unified skill for implementing frontend interfaces with the project's full stack: React 19 + TypeScript, Inertia.js v2, Tailwind CSS v4, Radix UI, and Wayfinder.

---

## 1. Before Implementation

1. Check `docs/design/` for UI design specifications (if they exist)
    - If `docs/design/DESIGN_SYSTEM.md` exists, use its tokens for color, typography, and spacing
    - If `docs/design/PAGE_INVENTORY.md` exists, follow its layout and section definitions
    - If `docs/design/COMPONENTS.md` exists, check for component hierarchy and reuse strategy
2. Check existing components in `resources/js/components/ui/` before creating new ones
3. Check sibling pages/components for patterns to follow
4. Use `search-docs` for version-specific documentation on any framework

### Project Structure

```text
resources/js/
├── components/          # Shared components
│   ├── ui/              # UI primitives (Button, Card, Dialog, etc.)
│   └── [domain].tsx     # Domain components (app-sidebar, nav-main, etc.)
├── hooks/               # Custom React hooks
├── layouts/             # Page layouts
│   ├── app/             # App layouts (sidebar, header)
│   ├── auth/            # Auth layouts (simple, split, card)
│   └── settings/        # Settings layout
├── pages/               # Inertia page components
│   ├── auth/            # Auth pages
│   ├── settings/        # Settings pages
│   └── [domain]/        # Domain pages
├── lib/                 # Utilities (cn, toUrl)
├── types/               # TypeScript type definitions
└── actions/             # Wayfinder generated (do not edit)
```

---

## 2. Component Patterns

### Styling Utilities

Always use the `cn()` utility for conditional/merged classes:

```tsx
import { cn } from '@/lib/utils';

<div
    className={cn('base-classes', conditional && 'active-class', className)}
/>;
```

### Component Variants with CVA

Use `class-variance-authority` for components with visual variants:

```tsx
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground',
                secondary: 'bg-secondary text-secondary-foreground',
                destructive: 'bg-destructive text-white',
                outline: 'border border-input text-foreground',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);

function Badge({
    className,
    variant,
    ...props
}: React.ComponentProps<'span'> & VariantProps<typeof badgeVariants>) {
    return (
        <span
            className={cn(badgeVariants({ variant }), className)}
            {...props}
        />
    );
}
```

### Radix UI Primitives

The project uses Radix UI for accessible headless components. Existing primitives in `components/ui/`:

| Primitive     | File                   | Radix Package                   |
| ------------- | ---------------------- | ------------------------------- |
| Dialog/Modal  | `ui/dialog.tsx`        | `@radix-ui/react-dialog`        |
| Dropdown Menu | `ui/dropdown-menu.tsx` | `@radix-ui/react-dropdown-menu` |
| Select        | `ui/select.tsx`        | `@radix-ui/react-select`        |
| Tooltip       | `ui/tooltip.tsx`       | `@radix-ui/react-tooltip`       |
| Avatar        | `ui/avatar.tsx`        | `@radix-ui/react-avatar`        |
| Checkbox      | `ui/checkbox.tsx`      | `@radix-ui/react-checkbox`      |
| Collapsible   | `ui/collapsible.tsx`   | `@radix-ui/react-collapsible`   |
| Toggle        | `ui/toggle.tsx`        | `@radix-ui/react-toggle`        |
| Sidebar       | `ui/sidebar.tsx`       | Custom (uses Sheet internally)  |
| Sheet         | `ui/sheet.tsx`         | `@radix-ui/react-dialog`        |

When needing a new primitive: check if Radix UI has it, create wrapper in `components/ui/`, follow existing patterns (data-slot, cn utility).

For detailed component patterns and Radix integration, see [references/component-patterns.md](references/component-patterns.md).

---

## 3. Inertia.js v2

### Page Components

Pages live in `resources/js/pages/`. Receive props from Laravel controllers via `Inertia::render()`.

```tsx
export default function UsersIndex({ users }: { users: App.Models.User[] }) {
    return (
        <div>
            <h1>Users</h1>
            {users.map((user) => (
                <div key={user.id}>{user.name}</div>
            ))}
        </div>
    );
}
```

### Navigation with Link

Use `<Link>` for client-side navigation (never `<a>`):

```tsx
import { Link } from '@inertiajs/react'

<Link href="/users">Users</Link>
<Link href="/logout" method="post" as="button">Logout</Link>
<Link href="/users" prefetch>Users (prefetched)</Link>
```

### Forms — Form Component (Recommended)

```tsx
import { Form } from '@inertiajs/react';

<Form action="/users" method="post" resetOnSuccess>
    {({ errors, processing, wasSuccessful }) => (
        <>
            <input type="text" name="name" />
            {errors.name && (
                <div className="text-destructive text-sm">{errors.name}</div>
            )}
            <button type="submit" disabled={processing}>
                {processing ? 'Saving...' : 'Save'}
            </button>
            {wasSuccessful && <div>Saved!</div>}
        </>
    )}
</Form>;
```

Render props: `errors`, `hasErrors`, `processing`, `progress`, `wasSuccessful`, `recentlySuccessful`, `clearErrors`, `resetAndClearErrors`, `defaults`, `isDirty`, `reset`, `submit`.

### Forms — useForm Hook

For programmatic control:

```tsx
import { useForm } from '@inertiajs/react';

const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    email: '',
});

function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/users', { onSuccess: () => reset() });
}
```

### Deferred Props (v2)

Load data after initial page render with skeleton fallback:

```tsx
export default function Dashboard({ stats }: { stats?: DashboardStats }) {
    if (!stats) return <Skeleton className="h-32 w-full" />;
    return <StatsGrid stats={stats} />;
}
```

### Polling (v2)

```tsx
import { router } from '@inertiajs/react';
import { useEffect } from 'react';

useEffect(() => {
    const interval = setInterval(
        () => router.reload({ only: ['stats'] }),
        5000,
    );
    return () => clearInterval(interval);
}, []);
```

### WhenVisible — Infinite Scroll (v2)

```tsx
import { WhenVisible } from '@inertiajs/react';

<WhenVisible
    data="users"
    params={{ page: users.current_page + 1 }}
    fallback={<div>Loading more...</div>}
/>;
```

### Shared Data & Persistent Layouts

Persistent layouts avoid re-mounting on navigation:

```tsx
import AppLayout from '@/layouts/app-layout';

function Dashboard({ stats }) {
    return <div>...</div>;
}

Dashboard.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;
export default Dashboard;
```

For detailed Inertia patterns, use `search-docs` with queries like `shared data`, `persistent layouts`, `partial reloads`, `merge props`.

---

## 4. Wayfinder — Backend Routes

> **MANDATORY**: Always prefer Wayfinder-generated route functions over hard-coded URLs (`'/client/dashboard'`, `` `/posts/${id}` `` etc.) in frontend code. This applies to **all** contexts: `<Link href>`, `router.get()` / `router.visit()`, breadcrumbs, form actions, and any other URL reference. Hard-coded URLs are only acceptable as a last resort and **must** include a comment justifying why Wayfinder cannot be used.

Import TypeScript route functions generated from Laravel routes:

```tsx
// Named route imports (preferred — matches route naming convention)
import { dashboard } from '@/routes/client';
import {
    index as vulnerabilitiesIndex,
    show as vulnerabilityShow,
} from '@/routes/client/vulnerabilities';

// Controller action imports (alternative — preferred for tree-shaking)
import { show, store } from '@/actions/App/Http/Controllers/PostController';
```

### Usage patterns

```tsx
// URL generation
vulnerabilityShow.url(1)                        // "/client/vulnerabilities/1"
vulnerabilitiesIndex.url()                       // "/client/vulnerabilities"
dashboard.url()                                  // "/client/dashboard"

// With query params
vulnerabilitiesIndex.url({ query: { view: 'pending', risk_level: 'critical' } })

// In Link components
<Link href={vulnerabilityShow.url(vuln.id)}>View</Link>

// In router calls
router.get(vulnerabilitiesIndex.url(), params, { preserveState: true })
router.visit(vulnerabilitiesIndex.url({ query: { view: 'pending' } }))

// In breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: dashboard.url() },
  { title: 'Vulnerabilities', href: vulnerabilitiesIndex.url() },
]

// With Inertia Form
<Form {...store.form()}>...</Form>
```

Regenerate after route changes: `php artisan wayfinder:generate --no-interaction`

---

## 5. Tailwind CSS v4

### Key Differences from v3

- Import: `@import "tailwindcss"` (not `@tailwind` directives)
- Config: CSS `@theme` directive (not `tailwind.config.js`)
- Opacity: `bg-black/50` (not `bg-opacity-50`)
- Shrink/Grow: `shrink-*` / `grow-*` (not `flex-shrink-*` / `flex-grow-*`)

### Common Layout Patterns

```html
<!-- Flex with gap -->
<div class="flex items-center justify-between gap-4">...</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">...</div>

<!-- Stack -->
<div class="flex flex-col gap-4">...</div>
```

### Dark Mode

Use `dark:` variant, match existing patterns:

```html
<div class="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">...</div>
```

### Responsive

Breakpoints: `sm` (640), `md` (768), `lg` (1024), `xl` (1280), `2xl` (1536). Mobile-first.

For detailed Tailwind patterns, use `search-docs` with relevant queries.

---

## 6. TypeScript Conventions

- Define page prop types inline or in `resources/js/types/`
- Use `App.Models.*` namespace for model types
- All components must have typed props
- Use `React.ComponentProps<'element'>` for extending HTML element props

---

## 7. Quality Checklist

Before considering frontend work complete:

- [ ] Uses existing `ui/` components (no reinventing)
- [ ] Follows sibling page/component patterns
- [ ] Loading states for deferred props (skeleton/spinner)
- [ ] Error states for forms (inline errors)
- [ ] Empty states where data can be empty
- [ ] Dark mode support (if project uses it)
- [ ] Responsive at defined breakpoints
- [ ] Accessible (keyboard nav, ARIA labels, focus management)
- [ ] Uses `cn()` for conditional classes
- [ ] Uses Wayfinder for backend route references
- [ ] `npm run lint && npm run types` passes

---

## Common Pitfalls

- Using `<a>` instead of `<Link>` (breaks SPA navigation)
- Using `<form>` without `e.preventDefault()` (use `<Form>` component instead)
- Forgetting loading states for deferred props (`undefined` initially)
- Creating new components that duplicate existing `ui/` primitives
- Hard-coding route URLs instead of using Wayfinder (applies to `<Link href>`, `router.get()`, `router.visit()`, breadcrumbs, and form actions — use `routeAction.url()` everywhere)
- Using deprecated Tailwind v3 utilities (`bg-opacity-*`, `flex-shrink-*`, `@tailwind`)
- Missing `key` prop in `.map()` iterations
- Not handling `processing` state in form submissions
