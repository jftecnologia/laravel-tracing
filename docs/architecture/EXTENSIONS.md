# Extension Architecture

**Purpose**: Define how developers can extend and customize the Laravel Tracing package.

---

## Overview

Laravel Tracing is designed to be **extensible by default**. Developers can add custom tracing sources, replace built-in behavior, and integrate with custom infrastructure without modifying package code.

**Extension Principles**:
- **Open/Closed Principle** - Extend via contracts, not modification
- **Configuration-based** - Register extensions via config file
- **Contract-driven** - Well-defined interfaces for all extension points
- **No package modification** - All customization happens in application code

---

## Extension Points

### Summary

| Extension Point | Interface/Contract | Location | Use Case |
|----------------|-------------------|----------|----------|
| **Custom Tracing Sources** | `TracingSource` | `src/Tracings/Contracts/TracingSource.php` | Add new tracing values (e.g., `X-User-Id`, `X-App-Version`) |
| **Custom Storage Backends** | `TracingStorage` | `src/Tracings/Contracts/TracingStorage.php` | Store tracings in cache, database, etc. |
| **Custom ID Generators** | Extend `IdGenerator` | `src/Support/IdGenerator.php` | Use ULID, Snowflake IDs, etc. |
| **Custom Middleware** | Extend middleware classes | `src/Middleware/` | Add custom request/response logic |
| **Runtime Extension** | `TracingManager::extend()` | `src/Tracings/TracingManager.php` | Register tracings at runtime (in service providers) |

---

## Extension Point 1: Custom Tracing Sources

### When to Use

Add a custom tracing source when you need to:
- Track a custom header (e.g., `X-User-Id`, `X-Tenant-Id`, `X-App-Version`)
- Resolve tracing values from application state (authenticated user, tenant, etc.)
- Replace built-in tracings with custom logic

### Contract: TracingSource

**Location**: `src/Tracings/Contracts/TracingSource.php`

```php
namespace JuniorFontenele\LaravelTracing\Tracings\Contracts;

use Illuminate\Http\Request;

interface TracingSource
{
    /**
     * Resolve the tracing value from the current request context.
     *
     * This method is called once per request (in IncomingTracingMiddleware)
     * and should return the string value for this tracing.
     */
    public function resolve(Request $request): string;

    /**
     * Get the HTTP header name for this tracing.
     *
     * This header name is used for:
     * - Reading from incoming requests (if accept_external_headers enabled)
     * - Attaching to outgoing responses
     * - Forwarding to external HTTP calls
     */
    public function headerName(): string;

    /**
     * Restore the tracing value from a serialized job payload.
     *
     * This method is called when a queued job is executed, allowing the
     * tracing source to transform the serialized value if needed.
     *
     * Default behavior: return the value as-is.
     */
    public function restoreFromJob(string $value): string;
}
```

### Example: User ID Tracing

**Scenario**: Track the authenticated user's ID across all requests and jobs.

#### Step 1: Create Custom Source Class

```php
// app/Tracings/UserIdSource.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class UserIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Return authenticated user ID, or 'guest' if not authenticated
        return (string) ($request->user()?->id ?? 'guest');
    }

    public function headerName(): string
    {
        // Read header name from config (allows environment override)
        return config('laravel-tracing.tracings.user_id.header', 'X-User-Id');
    }

    public function restoreFromJob(string $value): string
    {
        // No transformation needed - return as-is
        return $value;
    }
}
```

#### Step 2: Register in Configuration

```bash
# Publish config if not already done
php artisan vendor:publish --tag=laravel-tracing-config
```

```php
// config/laravel-tracing.php
'tracings' => [
    'correlation_id' => [...],
    'request_id' => [...],

    // Add custom tracing
    'user_id' => [
        'enabled' => env('LARAVEL_TRACING_USER_ID_ENABLED', true),
        'header' => env('LARAVEL_TRACING_USER_ID_HEADER', 'X-User-Id'),
        'source' => \App\Tracings\UserIdSource::class,
    ],
],
```

#### Step 3: (Optional) Add Environment Variables

```env
# .env
LARAVEL_TRACING_USER_ID_ENABLED=true
LARAVEL_TRACING_USER_ID_HEADER=X-User-Id
```

#### Result

**✅ User ID is now tracked automatically:**
- Attached to all outgoing response headers: `X-User-Id: 42`
- Propagated to queued jobs (original user ID preserved)
- Forwarded to external HTTP calls (if HTTP client enabled)
- Accessible via `LaravelTracing::get('user_id')`

**Usage in Code:**
```php
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// In controllers, jobs, services
$userId = LaravelTracing::get('user_id');

// In logs
Log::info('User action', [
    'user_id' => LaravelTracing::get('user_id'),
    'correlation_id' => LaravelTracing::correlationId(),
]);
```

---

### Example: Tenant ID Tracing (Multi-Tenancy)

**Scenario**: Track tenant ID for multi-tenant applications.

#### Implementation

```php
// app/Tracings/TenantIdSource.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;
use App\Models\Tenant; // Hypothetical tenant model

class TenantIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Resolve tenant from subdomain, header, or authenticated user
        $tenant = Tenant::current(); // Hypothetical tenant resolution

        return (string) ($tenant?->id ?? 'no-tenant');
    }

    public function headerName(): string
    {
        return config('laravel-tracing.tracings.tenant_id.header', 'X-Tenant-Id');
    }

    public function restoreFromJob(string $value): string
    {
        // Optionally restore tenant context when job executes
        // Tenant::setCurrent($value);

        return $value;
    }
}
```

#### Configuration

```php
'tracings' => [
    'tenant_id' => [
        'enabled' => true,
        'header' => 'X-Tenant-Id',
        'source' => \App\Tracings\TenantIdSource::class,
    ],
],
```

---

### Example: Application Version Tracing

**Scenario**: Track application version in all requests (useful for debugging deployments).

#### Implementation

```php
// app/Tracings/AppVersionSource.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class AppVersionSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Read version from config or .env
        return config('app.version', 'unknown');
    }

    public function headerName(): string
    {
        return 'X-App-Version';
    }

    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
```

#### Configuration

```php
'tracings' => [
    'app_version' => [
        'enabled' => true,
        'header' => 'X-App-Version',
        'source' => \App\Tracings\AppVersionSource::class,
    ],
],
```

**Result**: All responses include `X-App-Version: 1.2.3` header.

---

### Example: Replacing Built-In Correlation ID

**Scenario**: Use tenant ID as correlation ID instead of random UUID.

#### Implementation

```php
// app/Tracings/TenantBasedCorrelationId.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class TenantBasedCorrelationId implements TracingSource
{
    public function resolve(Request $request): string
    {
        $tenantId = Tenant::current()?->id;
        $sessionId = session()->getId();

        // Combine tenant ID and session ID for unique correlation
        return "{$tenantId}-{$sessionId}";
    }

    public function headerName(): string
    {
        return 'X-Correlation-Id';
    }

    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
```

#### Configuration (Replace Built-In)

```php
'tracings' => [
    'correlation_id' => [
        'enabled' => true,
        'header' => 'X-Correlation-Id',
        'source' => \App\Tracings\TenantBasedCorrelationId::class, // Replace default
    ],
],
```

---

## Extension Point 2: Custom Storage Backends

### When to Use

Add a custom storage backend when you need to:
- Store tracings in cache (e.g., Redis) instead of request memory
- Persist tracings in a database for audit logs
- Store tracings in a distributed storage system (e.g., Memcached)

### Contract: TracingStorage

**Location**: `src/Tracings/Contracts/TracingStorage.php`

```php
namespace JuniorFontenele\LaravelTracing\Tracings\Contracts;

interface TracingStorage
{
    /**
     * Store a tracing value.
     */
    public function set(string $key, string $value): void;

    /**
     * Retrieve a tracing value.
     */
    public function get(string $key): ?string;

    /**
     * Check if a tracing value exists.
     */
    public function has(string $key): bool;

    /**
     * Clear all tracing values.
     */
    public function flush(): void;
}
```

### Example: Cache-Based Storage

**Scenario**: Store correlation ID in Redis cache instead of session.

#### Implementation

```php
// app/Tracings/Storage/CacheStorage.php
namespace App\Tracings\Storage;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingStorage;
use Illuminate\Support\Facades\Cache;

class CacheStorage implements TracingStorage
{
    protected string $prefix = 'tracing:';
    protected int $ttl = 3600; // 1 hour

    public function set(string $key, string $value): void
    {
        Cache::put($this->prefix . $key, $value, $this->ttl);
    }

    public function get(string $key): ?string
    {
        return Cache::get($this->prefix . $key);
    }

    public function has(string $key): bool
    {
        return Cache::has($this->prefix . $key);
    }

    public function flush(): void
    {
        // Clear all tracing keys (implementation depends on cache driver)
        // This is a simplified example
        Cache::flush();
    }
}
```

#### Usage in Custom Tracing Source

```php
// app/Tracings/CachedCorrelationIdSource.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use App\Tracings\Storage\CacheStorage;
use Illuminate\Http\Request;

class CachedCorrelationIdSource implements TracingSource
{
    public function __construct(
        protected CacheStorage $storage
    ) {}

    public function resolve(Request $request): string
    {
        $key = 'correlation_id:' . session()->getId();

        // Check cache first
        if ($this->storage->has($key)) {
            return $this->storage->get($key);
        }

        // Generate and store
        $correlationId = Str::uuid()->toString();
        $this->storage->set($key, $correlationId);

        return $correlationId;
    }

    public function headerName(): string
    {
        return 'X-Correlation-Id';
    }

    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
```

#### Register in Service Provider

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton(CacheStorage::class);
}
```

---

## Extension Point 3: Custom ID Generators

### When to Use

Replace the default UUID generator when you need:
- ULID (Universally Unique Lexicographically Sortable Identifier)
- Snowflake IDs
- Custom prefixed IDs (e.g., `req_xyz123`)
- Sequential IDs

### Default Implementation

**Location**: `src/Support/IdGenerator.php`

```php
namespace JuniorFontenele\LaravelTracing\Support;

use Illuminate\Support\Str;

class IdGenerator
{
    public static function generate(): string
    {
        return Str::uuid()->toString();
    }
}
```

### Example: ULID Generator

**Scenario**: Use ULIDs instead of UUIDs for better sortability and performance.

#### Implementation

```php
// app/Support/UlidGenerator.php
namespace App\Support;

use Illuminate\Support\Str;

class UlidGenerator
{
    public static function generate(): string
    {
        return (string) Str::ulid(); // Laravel 9+ has built-in ULID support
    }
}
```

#### Usage in Custom Tracing Source

```php
use App\Support\UlidGenerator;

class RequestIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Use ULID instead of UUID
        return UlidGenerator::generate();
    }

    // ... rest of implementation
}
```

### Example: Prefixed ID Generator

**Scenario**: Generate IDs with semantic prefixes (e.g., `req_xyz123`, `corr_abc456`).

#### Implementation

```php
// app/Support/PrefixedIdGenerator.php
namespace App\Support;

use Illuminate\Support\Str;

class PrefixedIdGenerator
{
    public static function generate(string $prefix): string
    {
        $uuid = Str::uuid()->toString();
        $short = substr(str_replace('-', '', $uuid), 0, 12);

        return "{$prefix}_{$short}";
    }
}
```

#### Usage

```php
class RequestIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        return PrefixedIdGenerator::generate('req');
    }
}

class CorrelationIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        return PrefixedIdGenerator::generate('corr');
    }
}
```

**Result**: `X-Request-Id: req_a1b2c3d4e5f6`, `X-Correlation-Id: corr_f6e5d4c3b2a1`

---

## Extension Point 4: Runtime Registration

### When to Use

Register custom tracings at runtime (in service providers) when:
- Tracing configuration is dynamic (e.g., loaded from database)
- Conditionally enable tracings based on application state
- Third-party packages need to register their own tracings

### Method: TracingManager::extend()

**Signature**:
```php
public function extend(string $key, TracingSource $source): void
```

### Example: Register Tracing in Service Provider

**Scenario**: Dynamically register a tracing based on feature flag.

#### Implementation

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;
use App\Tracings\UserIdSource;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register tracing only if feature flag is enabled
        if (config('features.user_id_tracing')) {
            LaravelTracing::extend('user_id', app(UserIdSource::class));
        }
    }
}
```

### Example: Third-Party Package Integration

**Scenario**: A third-party package wants to add its own tracing.

#### Package Service Provider

```php
// vendor/acme/package/src/AcmeServiceProvider.php
namespace Acme\Package;

use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

class AcmeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register custom tracing from third-party package
        LaravelTracing::extend('acme_trace', new AcmeTracingSource());
    }
}
```

---

## Extension Point 5: Custom Middleware

### When to Use

Extend or replace middleware when you need to:
- Add custom logic before/after tracing resolution
- Modify how headers are attached to responses
- Integrate with custom authentication or tenant resolution

### Extending IncomingTracingMiddleware

**Scenario**: Add custom logging before resolving tracings.

#### Implementation

```php
// app/Http/Middleware/CustomIncomingTracing.php
namespace App\Http\Middleware;

use JuniorFontenele\LaravelTracing\Middleware\IncomingTracingMiddleware;
use Illuminate\Http\Request;
use Closure;

class CustomIncomingTracing extends IncomingTracingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Custom logic before resolving tracings
        Log::debug('Incoming request', [
            'url' => $request->url(),
            'method' => $request->method(),
        ]);

        // Call parent to resolve tracings
        return parent::handle($request, $next);
    }
}
```

#### Register in Kernel

```php
// app/Http/Kernel.php
protected $middleware = [
    // Replace default middleware with custom
    \App\Http\Middleware\CustomIncomingTracing::class,
    // ...
];
```

---

## Extension Best Practices

### 1. Follow the Contract

**✅ Always implement the full contract**:
```php
class MyTracingSource implements TracingSource
{
    public function resolve(Request $request): string { ... }
    public function headerName(): string { ... }
    public function restoreFromJob(string $value): string { ... }
}
```

**❌ Don't partially implement**:
```php
class BadTracingSource implements TracingSource
{
    public function resolve(Request $request): string { ... }
    // Missing headerName() and restoreFromJob()
}
```

### 2. Use Dependency Injection

**✅ Inject dependencies via constructor**:
```php
class UserIdSource implements TracingSource
{
    public function __construct(
        protected AuthManager $auth,
        protected IdGenerator $generator
    ) {}
}
```

**❌ Don't use facades or service locators inside sources**:
```php
class BadUserIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        return Auth::id(); // Hard to test, tightly coupled
    }
}
```

### 3. Return Empty String for Missing Values

**✅ Return empty string when value cannot be resolved**:
```php
public function resolve(Request $request): string
{
    return (string) ($request->user()?->id ?? '');
}
```

**❌ Don't return null (violates contract)**:
```php
public function resolve(Request $request): string
{
    return $request->user()?->id; // Type error if null
}
```

### 4. Read Header Names from Config

**✅ Allow header names to be configured**:
```php
public function headerName(): string
{
    return config('laravel-tracing.tracings.user_id.header', 'X-User-Id');
}
```

**❌ Don't hard-code header names**:
```php
public function headerName(): string
{
    return 'X-User-Id'; // Can't be customized
}
```

### 5. Test Custom Extensions

**✅ Write tests for custom tracing sources**:
```php
// tests/Unit/UserIdSourceTest.php
it('resolves authenticated user id', function () {
    $user = User::factory()->create(['id' => 42]);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $source = new UserIdSource();
    $result = $source->resolve($request);

    expect($result)->toBe('42');
});
```

---

## Extension Examples Summary

| Extension | Implementation Effort | Complexity | Use Case |
|-----------|---------------------|------------|----------|
| **User ID Tracing** | Low | Simple | Track authenticated users |
| **Tenant ID Tracing** | Low | Simple | Multi-tenancy support |
| **App Version Tracing** | Low | Simple | Track deployed versions |
| **Replace Correlation ID** | Medium | Moderate | Custom correlation logic |
| **Cache-Based Storage** | Medium | Moderate | Distributed storage |
| **ULID Generator** | Low | Simple | Use ULIDs instead of UUIDs |
| **Runtime Registration** | Low | Simple | Dynamic tracing registration |
| **Custom Middleware** | Medium | Moderate | Custom request/response logic |

---

## Related Documentation

- **[COMPONENTS.md](COMPONENTS.md)** → Component architecture and contracts
- **[CONFIGURATION.md](CONFIGURATION.md)** → How to register extensions via config
- **[DATA_FLOW.md](DATA_FLOW.md)** → How custom tracings flow through the system
- **[../PRD.md](../PRD.md)** → Extension requirements (FR-10, FR-11, FR-17)

---

## Maintenance Notes

**When creating custom extensions**:

1. ✅ Implement the full contract (don't skip methods)
2. ✅ Use dependency injection for testability
3. ✅ Write unit tests for custom logic
4. ✅ Document custom tracings in application code
5. ✅ Follow package conventions (naming, structure)

**When adding new extension points to the package**:

1. ✅ Define a clear contract (interface)
2. ✅ Document the extension point in this file
3. ✅ Provide example implementations
4. ✅ Add to "Extension Points Summary" table

**Keep extension points simple, well-documented, and contract-driven.**
