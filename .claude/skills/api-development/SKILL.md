---
name: api-development
description: "Design and implement RESTful APIs following best practices. Use when creating endpoints, API routes, request/response handling, resource controllers, or API documentation. Covers REST conventions, validation, authentication, pagination, error handling, and versioning (e.g., 'criar endpoint', 'criar API para X', 'implement API route', 'add endpoint for Y')."
dependencies:
  skills: [generate-test, security-analyst]
  docs: [docs/engineering/CODE_STANDARDS.md, docs/architecture/SECURITY.md]
---

# API Development

Design and implement RESTful APIs with consistent patterns.

---

## 1. REST Conventions

### Resource Naming

| Convention         | Example                                       |
| ------------------ | --------------------------------------------- |
| Plural nouns       | `/api/users`, `/api/invoices`                 |
| Nested resources   | `/api/users/{user}/invoices`                  |
| Actions (non-CRUD) | `/api/invoices/{invoice}/approve`             |
| kebab-case         | `/api/user-settings`, not `/api/userSettings` |

### HTTP Methods

| Method   | Purpose        | Response Code    |
| -------- | -------------- | ---------------- |
| `GET`    | List/retrieve  | 200              |
| `POST`   | Create         | 201              |
| `PUT`    | Full update    | 200              |
| `PATCH`  | Partial update | 200              |
| `DELETE` | Remove         | 204 (no content) |

### Status Codes

| Code | When to use                               |
| ---- | ----------------------------------------- |
| 200  | Success with body                         |
| 201  | Created                                   |
| 204  | Success, no body (DELETE)                 |
| 400  | Bad request (malformed syntax)            |
| 401  | Unauthenticated                           |
| 403  | Unauthorized (no permission)              |
| 404  | Not found                                 |
| 409  | Conflict (duplicate, state error)         |
| 422  | Unprocessable entity (Laravel validation) |
| 429  | Rate limited                              |
| 500  | Server error                              |

---

## 2. API Structure (Laravel)

### Route Organization

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('auth/login', [AuthController::class, 'login']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('users.invoices', InvoiceController::class)
            ->scoped();
    });
});
```

### Controller Pattern

```php
class UserController extends Controller
{
    // GET /api/v1/users
    public function index(IndexUserRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->filter($request->validated())
            ->paginate(min($request->integer('per_page', 15), 100));

        return UserResource::collection($users)->response();
    }

    // POST /api/v1/users
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = User::create($request->validated());

        return UserResource::make($user)
            ->response()
            ->setStatusCode(201);
    }
}
```

---

## 3. Request Validation

### Form Requests (MANDATORY)

```php
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization via Policy, not here
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ];
    }
}
```

> ⚠️ **NEVER** use `$request->all()`. Always use `$request->validated()`.

---

## 4. API Resources (Responses)

### Resource Class

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'invoices' => InvoiceResource::collection(
                $this->whenLoaded('invoices')
            ),
        ];
    }
}
```

### Response Envelope

Consistent response format:

```json
{
    "data": { ... },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    },
    "links": {
        "first": "...",
        "last": "...",
        "next": "...",
        "prev": null
    }
}
```

---

## 5. Error Handling

### Consistent Error Format

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Exception Handling

```php
// In Handler or exception class
public function render($request, Throwable $e)
{
    if ($request->expectsJson()) {
        return match (true) {
            $e instanceof ModelNotFoundException => response()->json(
                ['message' => 'Resource not found'], 404
            ),
            $e instanceof AuthorizationException => response()->json(
                ['message' => 'Forbidden'], 403
            ),
            default => parent::render($request, $e),
        };
    }
}
```

---

## 6. Pagination

### Always Paginate List Endpoints

```php
// ✅ Paginated
$users = User::paginate(15);

// ❌ Never return unbounded collections
$users = User::all();
```

### Pagination Parameters

| Parameter  | Default      | Max | Description    |
| ---------- | ------------ | --- | -------------- |
| `page`     | 1            | —   | Current page   |
| `per_page` | 15           | 100 | Items per page |
| `sort`     | `created_at` | —   | Sort field     |
| `order`    | `desc`       | —   | Sort direction |

---

## 7. Authentication & Security

### Sanctum (API Tokens)

```php
// Generate token
$token = $user->createToken('api-token')->plainTextToken;

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

### Security Checklist

- [ ] All endpoints require authentication (except public ones)
- [ ] Authorization via Policies (`$this->authorize()`)
- [ ] Input validated via Form Requests
- [ ] No mass assignment (`$request->validated()` only)
- [ ] Rate limiting applied (`throttle` middleware)
- [ ] Sensitive data excluded from responses
- [ ] CORS configured for allowed origins

---

## 8. Testing API Endpoints

API tests MUST cover all three pillars per `docs/engineering/QUALITY_GATES.md`:

```php
// ✅ Happy Path
test('can list users', function () {
    $users = User::factory(3)->create();

    $this->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

// ❌ Unhappy Path
test('returns 422 for invalid data', function () {
    $this->postJson('/api/v1/users', ['name' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

// 🔒 Security Path
test('cannot access other tenant resources', function () {
    $otherUser = User::factory()->create(['tenant_id' => 999]);

    $this->getJson("/api/v1/users/{$otherUser->id}")
        ->assertForbidden();
});
```

---

## Quality Gates

> Execute standard gates per `docs/engineering/QUALITY_GATES.md`: Lint → Test → i18n → Security
