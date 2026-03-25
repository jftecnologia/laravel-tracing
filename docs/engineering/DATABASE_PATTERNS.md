# Database Patterns

**Purpose**: Define conventions for migrations, seeders, factories, and model relationships.

---

## Data Storage Policy

### Migrations = System Data

All data required for the application to function **must** live in migrations:

- Enum values / lookup tables (roles, statuses, priorities, categories)
- Default configuration records
- Permission definitions
- System users or default tenants

```php
// In migration: insert system data
public function up(): void
{
    Schema::create('priorities', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->integer('order');
        $table->timestamps();
    });

    // System data — always present
    DB::table('priorities')->insert([
        ['name' => 'Critical', 'slug' => 'critical', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'High', 'slug' => 'high', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Medium', 'slug' => 'medium', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Low', 'slug' => 'low', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
    ]);
}
```

### Seeders = Demo Data Only

Seeders are **exclusively** for demonstration / factory data:

- Sample users, teams, and content for development
- Test data for UI demonstration
- Never for data the application needs to run

```php
// DatabaseSeeder.php — demo data only
public function run(): void
{
    User::factory(5)
        ->has(Team::factory(3)->has(Post::factory(10)))
        ->create();
}
```

---

## Migrations

### Naming

```bash
php artisan make:migration create_posts_table
php artisan make:migration add_priority_to_posts_table
php artisan make:migration create_team_user_pivot_table
```

### Conventions

- Always use `$table->id()` for primary keys
- Always include `$table->timestamps()`
- Use `$table->softDeletes()` when logical deletion is needed
- Define foreign keys explicitly with `constrained()->cascadeOnDelete()` or `nullOnDelete()`
- Add indexes for frequently queried columns
- Use `$table->foreignId('organization_id')->constrained()->cascadeOnDelete()` for scope-scoped tables (e.g., multi-tenant)

### Multi-Tenant Pattern

Every tenant-scoped table must include:

```php
$table->foreignId('organization_id')->constrained()->cascadeOnDelete();
$table->index('organization_id');
```

---

## Factories

### Factory Naming

Factory class: `{Model}Factory` in `database/factories/`.

### Factory Convention

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'priority' => fake()->randomElement(['critical', 'high', 'medium', 'low']),
            'status' => 'draft',
            'team_id' => Team::factory(),
        ];
    }

    // Named states for common scenarios
    public function highPriority(): static
    {
        return $this->state(['priority' => 'high']);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()]);
    }
}
```

### Rules

- Always define `definition()` with realistic fake data
- Use factory states for common variations (e.g., `->critical()`, `->resolved()`)
- Reference related models via `ModelName::factory()` in foreign keys
- Check existing states before creating new ones

---

## Model Relationships

### Relationship Conventions

- Define relationships in the model, one per method
- Use return type hints: `BelongsTo`, `HasMany`, `BelongsToMany`, etc.
- Name relationships using Laravel conventions (singular for BelongsTo, plural for HasMany)

```php
// In Team model
public function organization(): BelongsTo
{
    return $this->belongsTo(Organization::class);
}

public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}

public function members(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'team_user');
}
```

---

## Related Documentation

- **[CODE_STANDARDS.md](CODE_STANDARDS.md)** — Code quality conventions
- **[TESTING.md](TESTING.md)** — Testing with factories
