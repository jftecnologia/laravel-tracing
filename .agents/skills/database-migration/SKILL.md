---
name: database-migration
description: "Plan and execute database migrations with zero-downtime patterns. Use when creating or modifying database tables, columns, indexes, or relationships. Laravel-focused with database-agnostic concepts. Handles migration creation, review, rollback planning, and production safety checks (e.g., 'criar migration', 'adicionar coluna', 'create migration for X', 'modify table Y', 'add index')."
dependencies:
  skills: [generate-test]
  docs: [docs/engineering/DATABASE_PATTERNS.md, docs/architecture/DATABASE.md]
---

# Database Migration

Plan and execute safe database migrations.

---

## 1. Pre-Migration Analysis

Before creating any migration:

### Step 1: Inspect Current Schema

```text
Use `database-schema` MCP tool with summary mode to understand current state.
```

### Step 2: Identify Impact

- Which tables are affected?
- Are there foreign keys involved?
- Could the change lock large tables?
- Is the change backward-compatible with running code?

### Step 3: Check for Existing Migrations

```bash
# Laravel: list recent migrations
ls -la database/migrations/ | tail -20
```

Ensure no conflicting or duplicate migrations exist.

---

## 2. Migration Planning

### Naming Convention

```text
YYYY_MM_DD_HHMMSS_<action>_<table>_table.php
```

Actions: `create`, `add`, `remove`, `modify`, `rename`, `drop`

Examples:

- `2026_03_04_120000_create_invoices_table.php`
- `2026_03_04_120100_add_status_column_to_invoices_table.php`

### Column Ordering

When adding columns to existing tables, place them logically:

- FKs near the beginning (after ID)
- Timestamps at the end
- Related columns grouped together

---

## 3. Zero-Downtime Patterns

### Safe Operations (no downtime)

| Operation               | Safe? | Notes                                     |
| ----------------------- | ----- | ----------------------------------------- |
| Add nullable column     | ✅    | Always safe                               |
| Add column with default | ✅    | Safe on modern DBs (MySQL 8+, PG 11+)     |
| Add index               | ⚠️    | Use `ALGORITHM=INPLACE` on large tables   |
| Create new table        | ✅    | Always safe                               |
| Drop unused column      | ⚠️    | Remove code references first              |
| Rename column           | ❌    | Break running code — use add+migrate+drop |

### Rename Column (3-step pattern)

```text
Migration 1: Add new column, copy data
Migration 2: Update code to use new column (deploy)
Migration 3: Drop old column
```

### Add Non-Nullable Column (2-step pattern)

```text
Migration 1: Add column as nullable with default
Migration 2: Backfill data, then alter to NOT NULL
```

---

## 4. Migration Implementation (Laravel)

### Create Migration

```bash
php artisan make:migration <name> --table=<table>
# or for new table:
php artisan make:migration create_<table>_table
```

### Best Practices

```php
// ✅ Always define both up() and down()
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('phone');
    });
}
```

### Index Guidelines

```php
// ✅ Index columns used in WHERE, ORDER BY, JOIN
$table->index('status');
$table->index(['tenant_id', 'created_at']); // Composite for common queries

// ✅ Unique constraints for business rules
$table->unique(['tenant_id', 'email']);

// ✅ Foreign keys with cascade
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
```

---

## 5. Database-Agnostic Concepts

These principles apply regardless of framework or database:

| Concept                | Description                                           |
| ---------------------- | ----------------------------------------------------- |
| Idempotency            | Migrations should be safe to run multiple times       |
| Reversibility          | Every migration should have a rollback plan           |
| Atomicity              | One logical change per migration                      |
| Ordering               | Migrations run sequentially — order matters           |
| Data separation        | Schema changes and data migrations should be separate |
| Backward compatibility | New schema must work with old code during deploy      |

---

## 6. Pre-Production Checklist

Before applying migrations to production:

- [ ] `down()` method tested and verified
- [ ] Large table operations use online DDL (`ALGORITHM=INPLACE`)
- [ ] No destructive changes without data backup plan
- [ ] Foreign key constraints don't break existing data
- [ ] Indexes added for new query patterns
- [ ] Code changes are backward-compatible with old schema
- [ ] Migration tested on staging with production-like data volume

---

## 7. Post-Migration

### Update Documentation

If the migration changes the schema significantly:

1. Update `docs/architecture/DATABASE.md` (if it exists)
2. Run `database-schema summary` to verify final state

### Tests

Invoke `generate-test` skill if the migration affects model behavior:

- Test new columns/relationships
- Test constraints (unique, not null)
- Test cascade behavior

---

## Quality Gates

> Execute standard gates per `docs/engineering/QUALITY_GATES.md`: Lint → Test → i18n → Security
