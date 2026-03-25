# Node.js / TypeScript Patterns

## Module Organization

### Controller Pattern (Express/Fastify)

```typescript
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  async list(req: Request, res: Response): Promise<void> {
    const users = await this.usersService.findAll(req.query);
    res.json({ data: users });
  }

  async create(req: Request, res: Response): Promise<void> {
    const user = await this.usersService.create(req.body);
    res.status(201).json({ data: user });
  }
}
```

### Service Pattern

```typescript
export class UsersService {
  constructor(private readonly usersRepository: UsersRepository) {}

  async findAll(filters: UserFilters): Promise<User[]> {
    return this.usersRepository.findMany(filters);
  }

  async create(data: CreateUserDto): Promise<User> {
    // Business logic here
    return this.usersRepository.create(data);
  }
}
```

### Repository Pattern

```typescript
export class UsersRepository {
  constructor(private readonly db: Database) {}

  async findMany(filters: UserFilters): Promise<User[]> {
    return this.db.query(
      "SELECT * FROM users WHERE status = $1 ORDER BY created_at DESC LIMIT $2 OFFSET $3",
      [filters.status, filters.limit ?? 50, filters.offset ?? 0],
    );
  }
}
```

## Error Handling

```typescript
// Custom error classes
export class AppError extends Error {
  constructor(
    message: string,
    public readonly statusCode: number = 500,
    public readonly code: string = "INTERNAL_ERROR",
  ) {
    super(message);
    this.name = this.constructor.name;
  }
}

export class NotFoundError extends AppError {
  constructor(resource: string, id: string) {
    super(`${resource} with id ${id} not found`, 404, "NOT_FOUND");
  }
}

export class ValidationError extends AppError {
  constructor(
    message: string,
    public readonly errors: Record<string, string[]> = {},
  ) {
    super(message, 422, "VALIDATION_ERROR");
  }
}
```

## Configuration

```typescript
// config/index.ts — type-safe configuration
import { z } from "zod";

const envSchema = z.object({
  NODE_ENV: z
    .enum(["development", "production", "test"])
    .default("development"),
  PORT: z.coerce.number().default(3000),
  DATABASE_URL: z.string().url(),
  JWT_SECRET: z.string().min(32),
});

export const config = envSchema.parse(process.env);
export type Config = z.infer<typeof envSchema>;
```

## Async Patterns

```typescript
// Sequential (when order matters)
async function processSequentially(items: Item[]): Promise<Result[]> {
  const results: Result[] = [];
  for (const item of items) {
    results.push(await processItem(item));
  }
  return results;
}

// Parallel (when independent)
async function processInParallel(items: Item[]): Promise<Result[]> {
  return Promise.all(items.map(processItem));
}

// Controlled concurrency (batched)
async function processInBatches(
  items: Item[],
  batchSize = 10,
): Promise<Result[]> {
  const results: Result[] = [];
  for (let i = 0; i < items.length; i += batchSize) {
    const batch = items.slice(i, i + batchSize);
    results.push(...(await Promise.all(batch.map(processItem))));
  }
  return results;
}
```
