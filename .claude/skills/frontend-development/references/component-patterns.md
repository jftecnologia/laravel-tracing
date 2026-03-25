# Component Patterns Reference

Detailed patterns for creating and composing React components in this project.

---

## Creating a New UI Primitive (Radix UI Wrapper)

Follow this pattern when wrapping a Radix UI component in `components/ui/`:

```tsx
import * as React from 'react'
import * as PrimitiveName from '@radix-ui/react-primitive-name'
import { cn } from '@/lib/utils'

function Root({ className, ...props }: React.ComponentProps<typeof PrimitiveName.Root>) {
  return (
    <PrimitiveName.Root
      data-slot="primitive-name"
      className={cn('base-classes', className)}
      {...props}
    />
  )
}

function Trigger({ className, ...props }: React.ComponentProps<typeof PrimitiveName.Trigger>) {
  return (
    <PrimitiveName.Trigger
      data-slot="primitive-name-trigger"
      className={cn('trigger-classes', className)}
      {...props}
    />
  )
}

function Content({ className, ...props }: React.ComponentProps<typeof PrimitiveName.Content>) {
  return (
    <PrimitiveName.Portal>
      <PrimitiveName.Content
        data-slot="primitive-name-content"
        className={cn('content-classes', className)}
        {...props}
      />
    </PrimitiveName.Portal>
  )
}

export { Root, Trigger, Content }
```

**Key conventions**:
- Use `data-slot` attribute for styling hooks
- Use `cn()` for class merging
- Use `React.ComponentProps<typeof ...>` for prop types
- Export named parts, not default export
- Wrap content in Portal when it renders outside DOM hierarchy

---

## Creating a Variant Component (CVA Pattern)

```tsx
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const componentVariants = cva(
  // Base classes (always applied)
  'inline-flex items-center transition-colors',
  {
    variants: {
      variant: {
        default: 'bg-primary text-primary-foreground',
        secondary: 'bg-secondary text-secondary-foreground',
        outline: 'border border-input bg-background',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
      },
      size: {
        default: 'h-9 px-4 py-2',
        sm: 'h-8 px-3 text-sm',
        lg: 'h-10 px-6',
        icon: 'size-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
    // Optional: compound variants
    compoundVariants: [
      {
        variant: 'outline',
        size: 'sm',
        className: 'border-2',
      },
    ],
  }
)

interface ComponentProps
  extends React.ComponentProps<'div'>,
    VariantProps<typeof componentVariants> {}

function Component({ className, variant, size, ...props }: ComponentProps) {
  return (
    <div
      data-slot="component-name"
      className={cn(componentVariants({ variant, size }), className)}
      {...props}
    />
  )
}

export { Component, componentVariants }
```

---

## Composing Components for Pages

### Page with Data Table

```tsx
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { Link } from '@inertiajs/react'
import { index } from '@/actions/App/Http/Controllers/UserController'

interface Props {
  users: App.Data.PaginatedData<App.Models.User>
}

export default function UsersIndex({ users }: Props) {
  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Users</h1>
        <Button asChild>
          <Link href="/users/create">Add User</Link>
        </Button>
      </div>

      <Card>
        <CardContent className="p-0">
          <table className="w-full">
            <thead>
              <tr className="border-b">
                <th className="p-4 text-left text-sm font-medium">Name</th>
                <th className="p-4 text-left text-sm font-medium">Email</th>
                <th className="p-4 text-right text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.data.map(user => (
                <tr key={user.id} className="border-b last:border-0 hover:bg-muted/50">
                  <td className="p-4">{user.name}</td>
                  <td className="p-4 text-muted-foreground">{user.email}</td>
                  <td className="p-4 text-right">
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/users/${user.id}`}>View</Link>
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </CardContent>
      </Card>
    </div>
  )
}

UsersIndex.layout = (page: React.ReactNode) => (
  <AppLayout breadcrumbs={[{ title: 'Users', href: '/users' }]}>{page}</AppLayout>
)
```

### Page with Form

```tsx
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { InputError } from '@/components/input-error'
import { Form } from '@inertiajs/react'
import { store } from '@/actions/App/Http/Controllers/UserController'

export default function UsersCreate() {
  return (
    <Card className="max-w-lg">
      <CardHeader>
        <CardTitle>Create User</CardTitle>
      </CardHeader>
      <CardContent>
        <Form {...store.form()} resetOnSuccess>
          {({ errors, processing }) => (
            <div className="flex flex-col gap-4">
              <div className="flex flex-col gap-2">
                <Label htmlFor="name">Name</Label>
                <Input id="name" name="name" required />
                <InputError message={errors.name} />
              </div>

              <div className="flex flex-col gap-2">
                <Label htmlFor="email">Email</Label>
                <Input id="email" name="email" type="email" required />
                <InputError message={errors.email} />
              </div>

              <Button type="submit" disabled={processing}>
                {processing ? 'Creating...' : 'Create User'}
              </Button>
            </div>
          )}
        </Form>
      </CardContent>
    </Card>
  )
}

UsersCreate.layout = (page: React.ReactNode) => (
  <AppLayout breadcrumbs={[
    { title: 'Users', href: '/users' },
    { title: 'Create', href: '/users/create' },
  ]}>{page}</AppLayout>
)
```

### Page with Deferred Props

```tsx
import AppLayout from '@/layouts/app-layout'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'

interface Props {
  summary: { total: number; active: number }
  recentActivity?: App.Models.Activity[]  // deferred
}

export default function Dashboard({ summary, recentActivity }: Props) {
  return (
    <div className="flex flex-col gap-6">
      {/* Immediate data */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card>
          <CardHeader><CardTitle>Total Users</CardTitle></CardHeader>
          <CardContent><p className="text-3xl font-bold">{summary.total}</p></CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Active Users</CardTitle></CardHeader>
          <CardContent><p className="text-3xl font-bold">{summary.active}</p></CardContent>
        </Card>
      </div>

      {/* Deferred data with skeleton */}
      <Card>
        <CardHeader><CardTitle>Recent Activity</CardTitle></CardHeader>
        <CardContent>
          {!recentActivity ? (
            <div className="flex flex-col gap-3">
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-3/4" />
              <Skeleton className="h-4 w-1/2" />
            </div>
          ) : recentActivity.length === 0 ? (
            <p className="text-muted-foreground text-sm">No recent activity.</p>
          ) : (
            <ul className="flex flex-col gap-2">
              {recentActivity.map(activity => (
                <li key={activity.id} className="text-sm">{activity.description}</li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
```

---

## Empty State Pattern

```tsx
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/react'
import { FileText } from 'lucide-react'

interface EmptyStateProps {
  icon?: React.ComponentType<{ className?: string }>
  title: string
  description: string
  action?: { label: string; href: string }
}

function EmptyState({ icon: Icon = FileText, title, description, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      <Icon className="size-12 text-muted-foreground/50 mb-4" />
      <h3 className="text-lg font-medium">{title}</h3>
      <p className="text-muted-foreground text-sm mt-1 max-w-sm">{description}</p>
      {action && (
        <Button asChild className="mt-4">
          <Link href={action.href}>{action.label}</Link>
        </Button>
      )}
    </div>
  )
}
```

---

## Confirmation Dialog Pattern

```tsx
import { Button } from '@/components/ui/button'
import {
  Dialog, DialogContent, DialogDescription, DialogFooter,
  DialogHeader, DialogTitle, DialogTrigger
} from '@/components/ui/dialog'
import { useState } from 'react'

interface ConfirmDialogProps {
  trigger: React.ReactNode
  title: string
  description: string
  confirmLabel?: string
  onConfirm: () => void
  destructive?: boolean
}

function ConfirmDialog({
  trigger, title, description,
  confirmLabel = 'Confirm', onConfirm, destructive = false
}: ConfirmDialogProps) {
  const [open, setOpen] = useState(false)

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>{trigger}</DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" onClick={() => setOpen(false)}>Cancel</Button>
          <Button
            variant={destructive ? 'destructive' : 'default'}
            onClick={() => { onConfirm(); setOpen(false) }}
          >
            {confirmLabel}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
```
