# Node.js Testing Patterns

## Framework: Vitest (preferred) or Jest

### Setup (Vitest)

```typescript
// vitest.config.ts
import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    globals: true,
    environment: "node",
    coverage: {
      provider: "v8",
      reporter: ["text", "json", "html"],
    },
  },
});
```

## Test Structure

Follow the **Three Pillar** pattern from `docs/engineering/TESTING.md`:

```text
tests/
├── unit/                    # Pure logic, no I/O
│   ├── services/
│   └── utils/
├── integration/             # With database/external services
│   ├── repositories/
│   └── api/
└── e2e/                     # Full application flow
    └── flows/
```

## Unit Test Pattern

```typescript
import { describe, it, expect, vi } from "vitest";
import { UsersService } from "@/modules/users/users.service";

describe("UsersService", () => {
  // ✅ Happy Path
  it("should create a user with valid data", async () => {
    const repo = {
      create: vi.fn().mockResolvedValue({ id: "1", name: "John" }),
    };
    const service = new UsersService(repo as any);

    const result = await service.create({
      name: "John",
      email: "john@example.com",
    });

    expect(result).toEqual({ id: "1", name: "John" });
    expect(repo.create).toHaveBeenCalledOnce();
  });

  // ❌ Unhappy Path
  it("should throw ValidationError for invalid email", async () => {
    const service = new UsersService({} as any);

    await expect(
      service.create({ name: "John", email: "invalid" }),
    ).rejects.toThrow(ValidationError);
  });

  // 🔒 Security Path
  it("should not expose sensitive fields in response", async () => {
    const repo = {
      create: vi.fn().mockResolvedValue({
        id: "1",
        name: "John",
        passwordHash: "secret",
      }),
    };
    const service = new UsersService(repo as any);

    const result = await service.create({
      name: "John",
      email: "john@example.com",
    });

    expect(result).not.toHaveProperty("passwordHash");
  });
});
```

## Integration Test Pattern

```typescript
import { describe, it, expect, beforeAll, afterAll } from "vitest";
import { createTestApp } from "@/tests/helpers";

describe("POST /api/users", () => {
  let app: TestApp;

  beforeAll(async () => {
    app = await createTestApp();
    await app.db.migrate();
  });

  afterAll(async () => {
    await app.close();
  });

  it("should create a user and return 201", async () => {
    const response = await app.request("POST", "/api/users", {
      body: { name: "John", email: "john@example.com" },
    });

    expect(response.status).toBe(201);
    expect(response.body.data).toHaveProperty("id");
  });

  it("should return 422 for duplicate email", async () => {
    await app.request("POST", "/api/users", {
      body: { name: "John", email: "john@example.com" },
    });

    const response = await app.request("POST", "/api/users", {
      body: { name: "Jane", email: "john@example.com" },
    });

    expect(response.status).toBe(422);
  });
});
```

## Mocking

```typescript
// Mock modules
vi.mock("@/lib/email", () => ({
  sendEmail: vi.fn().mockResolvedValue(true),
}));

// Mock timers
vi.useFakeTimers();
vi.setSystemTime(new Date("2025-01-01"));

// Spy on methods
const spy = vi.spyOn(service, "validate");
```
