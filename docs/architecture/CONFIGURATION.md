# Configuration Architecture

**Purpose**: Define the configuration structure, environment variable mapping, and how to add new configuration options.

---

## Overview

Laravel Tracing uses a configuration file with sensible defaults and full environment variable support. The package provides sane defaults, requiring only middleware registration to get started.

**Design Principles**:
- **Minimal setup required** - Works after `composer require` and middleware registration in `bootstrap/app.php`
- **Environment variable overrides** - All config values support `.env` overrides
- **Explicit over implicit** - Configuration keys are clear and self-documenting
- **Feature toggles** - Global and per-feature enable/disable toggles

---

## Configuration File Structure

### Full Config File

**Location**: `config/laravel-tracing.php`

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Global Enable/Disable Toggle
    |--------------------------------------------------------------------------
    |
    | Master switch for the entire package. When disabled, no tracing headers
    | are read, generated, or attached. Middleware and job listeners are
    | skipped entirely.
    |
    */

    'enabled' => env('LARAVEL_TRACING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Accept External Tracing Headers
    |--------------------------------------------------------------------------
    |
    | When enabled, the package accepts tracing header values from incoming
    | external requests (e.g., from an API gateway or another service).
    | When disabled, all external headers are ignored and values are always
    | generated internally.
    |
    | Security Note: Disable this if you don't trust external tracing headers
    | or want to prevent external services from injecting values.
    |
    */

    'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL', false),

    /*
    |--------------------------------------------------------------------------
    | Tracing Sources
    |--------------------------------------------------------------------------
    |
    | Define all tracing sources (built-in and custom). Each tracing source
    | consists of:
    | - enabled: Toggle this specific tracing on/off
    | - header: The HTTP header name (e.g., 'X-Correlation-Id')
    | - source: The class responsible for resolving the tracing value
    |
    */

    'tracings' => [

        /*
        |----------------------------------------------------------------------
        | Correlation ID (Session-Scoped)
        |----------------------------------------------------------------------
        |
        | The correlation ID represents a user session and persists across
        | multiple requests within the same session. It enables tracking all
        | requests, jobs, and logs belonging to a single user session.
        |
        */

        'correlation_id' => [
            'enabled' => env('LARAVEL_TRACING_CORRELATION_ENABLED', true),
            'header' => env('LARAVEL_TRACING_CORRELATION_HEADER', 'X-Correlation-Id'),
            'source' => \JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource::class,
        ],

        /*
        |----------------------------------------------------------------------
        | Request ID (Request-Scoped)
        |----------------------------------------------------------------------
        |
        | The request ID is unique for each HTTP request. It enables tracking
        | a single request as it flows through the application, including
        | any jobs dispatched by that request.
        |
        */

        'request_id' => [
            'enabled' => env('LARAVEL_TRACING_REQUEST_ENABLED', true),
            'header' => env('LARAVEL_TRACING_REQUEST_HEADER', 'X-Request-Id'),
            'source' => \JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource::class,
        ],

        /*
        |----------------------------------------------------------------------
        | Custom Tracing Sources
        |----------------------------------------------------------------------
        |
        | Add custom tracing sources here. Example:
        |
        | 'user_id' => [
        |     'enabled' => env('LARAVEL_TRACING_USER_ID_ENABLED', true),
        |     'header' => env('LARAVEL_TRACING_USER_ID_HEADER', 'X-User-Id'),
        |     'source' => \App\Tracings\UserIdSource::class,
        | ],
        |
        */

    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Integration
    |--------------------------------------------------------------------------
    |
    | Configure integration with Laravel's HTTP client. When enabled globally,
    | all outgoing HTTP requests will automatically include tracing headers.
    | When disabled, use Http::withTracing() to opt-in per request.
    |
    */

    'http_client' => [
        'enabled' => env('LARAVEL_TRACING_HTTP_CLIENT_ENABLED', false),
    ],

];
```

---

## Environment Variable Mapping

### Global Settings

| Config Key | Environment Variable | Default | Description |
|------------|---------------------|---------|-------------|
| `enabled` | `LARAVEL_TRACING_ENABLED` | `true` | Master enable/disable toggle |
| `accept_external_headers` | `LARAVEL_TRACING_ACCEPT_EXTERNAL` | `false` | Accept tracing headers from incoming requests |

### Built-In Tracing: Correlation ID

| Config Key | Environment Variable | Default | Description |
|------------|---------------------|---------|-------------|
| `tracings.correlation_id.enabled` | `LARAVEL_TRACING_CORRELATION_ENABLED` | `true` | Enable/disable correlation ID tracing |
| `tracings.correlation_id.header` | `LARAVEL_TRACING_CORRELATION_HEADER` | `X-Correlation-Id` | HTTP header name for correlation ID |

### Built-In Tracing: Request ID

| Config Key | Environment Variable | Default | Description |
|------------|---------------------|---------|-------------|
| `tracings.request_id.enabled` | `LARAVEL_TRACING_REQUEST_ENABLED` | `true` | Enable/disable request ID tracing |
| `tracings.request_id.header` | `LARAVEL_TRACING_REQUEST_HEADER` | `X-Request-Id` | HTTP header name for request ID |

### HTTP Client Integration

| Config Key | Environment Variable | Default | Description |
|------------|---------------------|---------|-------------|
| `http_client.enabled` | `LARAVEL_TRACING_HTTP_CLIENT_ENABLED` | `false` | Automatically attach tracings to all outgoing HTTP requests |

---

## Configuration Loading Flow

### How Configuration is Loaded

```mermaid
flowchart TD
    A[Application Boots] --> B[Service Provider register()]
    B --> C[mergeConfigFrom - Load package defaults]
    C --> D{Config Published?}
    D -->|Yes| E[Merge user config with defaults]
    D -->|No| F[Use package defaults only]
    E --> G[Environment Variables Override]
    F --> G
    G --> H[Final Config Available]
```

### Step-by-Step

1. **Package Default Config**: `config/laravel-tracing.php` in package
2. **User Published Config** (optional): `config/laravel-tracing.php` in application (published via `php artisan vendor:publish`)
3. **Environment Variables**: `.env` values override config file values
4. **Final Config**: `config('laravel-tracing.enabled')` returns final merged value

### Publishing Config

**Command**:
```bash
php artisan vendor:publish --tag=laravel-tracing-config
```

**Result**: Copies `vendor/jftecnologia/laravel-tracing/config/laravel-tracing.php` to `config/laravel-tracing.php`.

**When to Publish**:
- To customize tracing sources
- To add custom tracings
- To document configuration in the application's codebase
- **Not required for basic usage** (defaults work out of the box after middleware registration)

---

## Default Values Strategy

### Minimal Setup Philosophy

**Goal**: Package works with minimal setup - `composer require` + middleware registration in `bootstrap/app.php`.

**Note**: Laravel 12 does not support automatic middleware registration through package discovery. Manual registration is required.

**Defaults Chosen**:
- ✅ **Enabled by default** (`enabled = true`) - Tracing active out of the box
- ✅ **Accept external headers** (`accept_external_headers = false`) - Must explicitly enable to accept tracing headers from incoming requests
- ✅ **Both built-in tracings enabled** - Correlation ID and Request ID active
- ✅ **Standard header names** - `X-Correlation-Id`, `X-Request-Id` (industry conventions)
- ⚠️ **HTTP client opt-in** (`http_client.enabled = false`) - Must explicitly enable to avoid surprising behavior

### Rationale for Defaults

| Setting | Default | Reasoning |
|---------|---------|-----------|
| `enabled` | `true` | Package should work immediately after install |
| `accept_external_headers` | `false` | Must explicitly enable to accept tracing headers from incoming requests |
| `correlation_id.enabled` | `true` | Core feature, should be active |
| `request_id.enabled` | `true` | Core feature, should be active |
| `http_client.enabled` | `false` | Opt-in to avoid unexpected behavior (could modify external API calls) |

---

## Adding New Configuration Options

### Example: Add Custom Tracing Source

**Scenario**: Add `X-User-Id` tracing.

#### Step 1: Create the Source Class

```php
// app/Tracings/UserIdSource.php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class UserIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        return (string) ($request->user()?->id ?? 'guest');
    }

    public function headerName(): string
    {
        return config('laravel-tracing.tracings.user_id.header', 'X-User-Id');
    }

    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
```

#### Step 2: Publish Config (if not already done)

```bash
php artisan vendor:publish --tag=laravel-tracing-config
```

#### Step 3: Register in Config

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

#### Step 4: (Optional) Add Environment Variables

```env
# .env
LARAVEL_TRACING_USER_ID_ENABLED=true
LARAVEL_TRACING_USER_ID_HEADER=X-User-Id
```

#### Result

- ✅ `X-User-Id` header attached to all responses
- ✅ User ID propagated to queued jobs
- ✅ User ID forwarded to outgoing HTTP calls (if HTTP client enabled)
- ✅ Accessible via `LaravelTracing::get('user_id')`

---

## Configuration Schema

### Tracing Source Schema

Each entry in `tracings` must follow this schema:

```php
'tracing_key' => [
    'enabled' => bool,           // Required: Enable/disable this tracing
    'header' => string,          // Required: HTTP header name (e.g., 'X-Custom-Header')
    'source' => string,          // Required: Fully-qualified class name implementing TracingSource
],
```

**Validation Rules**:
- `enabled` must be a boolean (or env() returning boolean)
- `header` must be a non-empty string
- `source` must be a class that implements `TracingSource` contract
- `tracing_key` must be unique (used as internal identifier)

### HTTP Client Schema

```php
'http_client' => [
    'enabled' => bool,           // Required: Enable/disable global HTTP client integration
],
```

---

## Configuration Best Practices

### 1. Use Environment Variables for Deployment

**✅ Good**:
```php
'enabled' => env('LARAVEL_TRACING_ENABLED', true),
```

**❌ Bad**:
```php
'enabled' => true, // Hard-coded, can't be toggled per environment
```

**Rationale**: Allows disabling tracing in local development, enabling in production, etc., without code changes.

### 2. Provide Sensible Defaults

**✅ Good**:
```php
'header' => env('LARAVEL_TRACING_CORRELATION_HEADER', 'X-Correlation-Id'),
```

**❌ Bad**:
```php
'header' => env('LARAVEL_TRACING_CORRELATION_HEADER'), // No default, fails if env var missing
```

**Rationale**: Package should work without requiring environment variables.

### 3. Document Configuration Options

**✅ Good**:
```php
/*
|--------------------------------------------------------------------------
| Accept External Tracing Headers
|--------------------------------------------------------------------------
|
| When enabled, the package accepts tracing header values from incoming
| external requests. When disabled, all external headers are ignored.
|
*/
'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL', false),
```

**Rationale**: Developers reading the config file should understand what each option does.

### 4. Use Explicit Feature Toggles

**✅ Good**:
```php
'tracings' => [
    'correlation_id' => [
        'enabled' => env('LARAVEL_TRACING_CORRELATION_ENABLED', true),
        // ...
    ],
],
```

**❌ Bad**:
```php
'tracings' => [
    'correlation_id' => [
        // No enabled toggle - can't disable correlation ID without removing entire config
    ],
],
```

**Rationale**: Allows disabling individual tracings without modifying package code.

---

## Configuration Access in Code

### Reading Config Values

**In Service Provider**:
```php
public function boot()
{
    if (!config('laravel-tracing.enabled')) {
        return; // Skip registration if disabled
    }

    // Register middleware, listeners, etc.
}
```

**In Tracing Sources**:
```php
class CorrelationIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        $acceptExternal = config('laravel-tracing.accept_external_headers', false);
        $header = config('laravel-tracing.tracings.correlation_id.header', 'X-Correlation-Id');

        if ($acceptExternal && $request->hasHeader($header)) {
            return $request->header($header);
        }

        // Fallback to session or generate new
    }
}
```

**In Application Code**:
```php
// Check if package is enabled
if (config('laravel-tracing.enabled')) {
    $correlationId = LaravelTracing::correlationId();
}
```

---

## Environment-Specific Configuration

### Example: Disable in Local, Enable in Production

**.env.local** (development):
```env
LARAVEL_TRACING_ENABLED=false
```

**.env.production**:
```env
LARAVEL_TRACING_ENABLED=true
LARAVEL_TRACING_HTTP_CLIENT_ENABLED=true
```

**Result**:
- **Local**: No tracing overhead during development
- **Production**: Full tracing active, including HTTP client integration

### Example: Custom Header Names per Environment

**.env.staging**:
```env
LARAVEL_TRACING_CORRELATION_HEADER=X-Staging-Correlation-Id
```

**.env.production**:
```env
LARAVEL_TRACING_CORRELATION_HEADER=X-Correlation-Id
```

**Use Case**: Different header names for different environments to prevent conflicts.

---

## Config Caching

### Laravel Config Cache

**Command**:
```bash
php artisan config:cache
```

**Effect**: All `env()` calls are evaluated once and cached.

**Important**: After modifying `.env` or config files, clear cache:
```bash
php artisan config:clear
```

**Production Best Practice**:
- Always run `config:cache` in production
- Never rely on `.env` changes being picked up instantly (cache must be cleared)

---

## Replacing Built-In Tracings

### Example: Custom Correlation ID Source

**Scenario**: Replace default `CorrelationIdSource` with a custom implementation.

#### Step 1: Create Custom Source

```php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class CustomCorrelationIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Custom logic: use tenant ID as correlation ID
        return (string) ($request->header('X-Tenant-Id') ?? 'default-tenant');
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

#### Step 2: Update Config

```php
// config/laravel-tracing.php
'tracings' => [
    'correlation_id' => [
        'enabled' => true,
        'header' => 'X-Correlation-Id',
        'source' => \App\Tracings\CustomCorrelationIdSource::class, // Replace built-in
    ],
],
```

#### Result

- ✅ Custom correlation ID logic replaces built-in
- ✅ No package code modification required
- ✅ Follows Open/Closed Principle

---

## Configuration Validation

### Validation in Service Provider

**When to Validate**:
- During service provider `boot()` or `register()`
- Before using configuration values

**Example Validation**:
```php
public function boot()
{
    $this->validateConfiguration();
}

protected function validateConfiguration(): void
{
    $tracings = config('laravel-tracing.tracings', []);

    foreach ($tracings as $key => $config) {
        if (!isset($config['enabled'], $config['header'], $config['source'])) {
            throw new \InvalidArgumentException(
                "Tracing '{$key}' is missing required configuration keys (enabled, header, source)."
            );
        }

        if (!class_exists($config['source'])) {
            throw new \InvalidArgumentException(
                "Tracing source class '{$config['source']}' does not exist."
            );
        }

        if (!in_array(TracingSource::class, class_implements($config['source']))) {
            throw new \InvalidArgumentException(
                "Tracing source '{$config['source']}' must implement TracingSource contract."
            );
        }
    }
}
```

**Rationale**: Fail fast with clear error messages rather than failing silently at runtime.

---

## Related Documentation

- **[COMPONENTS.md](COMPONENTS.md)** → How components read and use configuration
- **[DATA_FLOW.md](DATA_FLOW.md)** → How config affects data flow (e.g., `accept_external_headers`)
- **[EXTENSIONS.md](EXTENSIONS.md)** → How to register custom tracings via config
- **[../PRD.md](../PRD.md)** → Configuration requirements (FR-09, FR-10, FR-11, FR-12, FR-13, FR-14)

---

## Maintenance Notes

**When adding new config options**:

1. ✅ Add to `config/laravel-tracing.php` with clear documentation
2. ✅ Support environment variable override
3. ✅ Provide sensible default value
4. ✅ Update this document with new option
5. ✅ Update environment variable mapping table
6. ✅ Add validation if required

**Keep configuration simple, explicit, and documented.**
