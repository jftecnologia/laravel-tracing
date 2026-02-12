# Laravel Tracing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jftecnologia/laravel-tracing.svg?style=flat-square)](https://packagist.org/packages/jftecnologia/laravel-tracing)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jftecnologia/laravel-tracing/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jftecnologia/laravel-tracing/actions?query=workflow%3Atests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jftecnologia/laravel-tracing/fix-php-code-style.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jftecnologia/laravel-tracing/actions?query=workflow%3A"fix-php-code-style-issues"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jftecnologia/laravel-tracing.svg?style=flat-square)](https://packagist.org/packages/jftecnologia/laravel-tracing)

**Lightweight, plug-and-play request tracing for Laravel applications.**

Laravel Tracing automatically tracks requests across your application using correlation IDs and request IDs. It seamlessly propagates tracing context through queued jobs, HTTP requests, and external API calls — making it easy to correlate logs, debug distributed systems, and trace user sessions end-to-end.

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage Examples](#usage-examples)
- [Custom Tracing Sources](#custom-tracing-sources)
- [Troubleshooting](#troubleshooting)
- [Testing](#testing)
- [Credits](#credits)
- [License](#license)

---

## Overview

In distributed and monolithic Laravel applications, tracking the origin and flow of requests is essential for debugging, monitoring, and log correlation. Without standardized tracing identifiers, it's difficult to:

- Correlate log entries across services
- Debug issues spanning multiple requests or jobs
- Track a user session end-to-end
- Trace requests through queues and external HTTP calls

**Laravel Tracing solves this** by automatically attaching tracing headers (correlation ID, request ID) to every request, propagating them through queued jobs, and forwarding them to external services.

### Why Use Laravel Tracing?

- ✅ **Simple Setup**: One-time middleware registration in `bootstrap/app.php`
- ✅ **Session Persistence**: Correlation IDs survive across multiple requests from the same user
- ✅ **Job Propagation**: Tracing context automatically flows into queued jobs
- ✅ **HTTP Client Integration**: Forward tracing headers to external APIs with `Http::withTracing()`
- ✅ **Fully Extensible**: Add custom tracing sources (user ID, tenant ID, app version) without modifying package code
- ✅ **Environment-Aware**: Toggle features via config file and environment variables
- ✅ **Lightweight**: No external dependencies beyond Laravel core

---

## Features

- **Built-In Tracing**:
  - **Correlation ID** (`X-Correlation-Id`): Tracks user sessions across multiple requests
  - **Request ID** (`X-Request-Id`): Uniquely identifies each HTTP request

- **Session Persistence**: Correlation IDs persist across requests from the same user session (via Laravel session/cookies)

- **Job Propagation**: Tracing values are automatically serialized with job payloads and restored during execution

- **HTTP Client Integration**: Opt-in support for forwarding tracing headers to external services via `Http::withTracing()`

- **Custom Tracing Sources**: Easily add custom tracings (user ID, tenant ID, app version) via config or runtime registration

- **Global Accessor**: Access all tracing values from anywhere in your application via the `LaravelTracing` facade

- **Configurable**: Control header names, enable/disable tracings, and customize behavior via config file and environment variables

- **Fully Tested**: Comprehensive test suite using PestPHP

---

## Requirements

- **PHP**: 8.4 or higher
- **Laravel**: 12 or higher

---

## Installation

### Step 1: Install via Composer

```bash
composer require jftecnologia/laravel-tracing
```

### Step 2: Register Middleware

**Important**: Laravel 12 does not support automatic middleware registration via package discovery. You must manually register the tracing middleware in your `bootstrap/app.php` file:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use JuniorFontenele\LaravelTracing\Middleware\IncomingTracingMiddleware;
use JuniorFontenele\LaravelTracing\Middleware\OutgoingTracingMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register tracing middleware to web group (for session-based correlation ID persistence)
        $middleware->appendToGroup('web', IncomingTracingMiddleware::class);
        $middleware->appendToGroup('web', OutgoingTracingMiddleware::class);
        
        // Optional: Register to api group if you want tracing on API routes
        // Note: API routes won't have session persistence, so correlation ID will be generated per request
        $middleware->appendToGroup('api', IncomingTracingMiddleware::class);
        $middleware->appendToGroup('api', OutgoingTracingMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**Important Notes**:
- The middleware must be registered to the `web` middleware group (or after Laravel's `StartSession` middleware) to ensure session persistence for correlation IDs
- If registered globally or before `StartSession`, correlation IDs will not persist across requests
- Unlike Laravel 11 and earlier versions, Laravel 12 does not support automatic middleware registration through package discovery

### Alternative: Route-Specific Registration

If you prefer to apply tracing only to specific routes:

```php
// In routes/web.php
Route::middleware([
    IncomingTracingMiddleware::class,
    OutgoingTracingMiddleware::class,
])->group(function () {
    Route::get('/traced', function () {
        return response()->json(['status' => 'traced']);
    });
});
```

### Step 3: Publish Configuration (Optional)

To customize tracing behavior (header names, enable/disable features, add custom tracings), publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-tracing-config
```

This creates `config/laravel-tracing.php` where you can customize all package settings.

### Verification

Make a request to your application and check the response headers:

```bash
curl -I http://localhost:8000
```

You should see:

```
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000
X-Request-Id: 123e4567-e89b-12d3-a456-426614174000
```

---

## Quick Start

### Basic Usage

Access tracing values from anywhere in your application:

```php
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// Get correlation ID
$correlationId = LaravelTracing::correlationId();

// Get request ID
$requestId = LaravelTracing::requestId();

// Get all tracing values
$allTracings = LaravelTracing::all();
// Returns: ['correlation_id' => '...', 'request_id' => '...']
```

### Use in Log Context

```php
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

Log::info('User action performed', LaravelTracing::all());
```

**Log output:**
```json
{
  "message": "User action performed",
  "correlation_id": "550e8400-e29b-41d4-a716-446655440000",
  "request_id": "123e4567-e89b-12d3-a456-426614174000"
}
```

### Tracing in Queued Jobs

Tracing values are automatically propagated to queued jobs:

```php
// Dispatch a job from a controller
dispatch(new ProcessOrder($orderId));
```

```php
// Inside the job handler
class ProcessOrder implements ShouldQueue
{
    public function handle(): void
    {
        // Access original correlation ID from the dispatching request
        $correlationId = LaravelTracing::correlationId();

        Log::info('Processing order', [
            'correlation_id' => $correlationId, // Same as dispatching request
            'request_id' => LaravelTracing::requestId(), // Preserved from dispatch
        ]);
    }
}
```

### Forward Tracing to External APIs

```php
use Illuminate\Support\Facades\Http;

// Attach all tracing headers to outgoing HTTP request
$response = Http::withTracing()
    ->get('https://api.example.com/data');
```

**The external service receives:**
```
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000
X-Request-Id: 123e4567-e89b-12d3-a456-426614174000
```

---

## Configuration

### Configuration File Structure

After publishing the config (`php artisan vendor:publish --tag=laravel-tracing-config`), you'll have access to `config/laravel-tracing.php`:

```php
return [
    // Global enable/disable toggle
    'enabled' => env('LARAVEL_TRACING_ENABLED', true),

    // Accept tracing headers from external requests
    'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS', false),

    // Define tracing sources
    'tracings' => [
        'correlation_id' => [
            'enabled' => true,
            'header' => env('LARAVEL_TRACING_CORRELATION_ID_HEADER', 'X-Correlation-Id'),
            'source' => 'JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource',
        ],
        'request_id' => [
            'enabled' => true,
            'header' => env('LARAVEL_TRACING_REQUEST_ID_HEADER', 'X-Request-Id'),
            'source' => 'JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource',
        ],
    ],

    // HTTP client integration
    'http_client' => [
        'enabled' => env('LARAVEL_TRACING_HTTP_CLIENT_ENABLED', false),
    ],
];
```

### Configuration Options

#### Global Settings

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enabled` | bool | `true` | Master switch for the entire package. When `false`, all tracing operations are skipped (zero overhead). |
| `accept_external_headers` | bool | `false` | When `true`, tracing values are read from incoming request headers (forwarded by upstream services). When `false`, values are always generated fresh. |

**Environment Variables:**
- `LARAVEL_TRACING_ENABLED` - Enable/disable package globally
- `LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS` - Accept external headers

#### Per-Tracing Settings

Each entry in the `tracings` array supports:

| Key | Type | Required | Description |
|-----|------|----------|-------------|
| `enabled` | bool | Yes | Whether this tracing source is active |
| `header` | string | Yes | HTTP header name for reading/writing this value |
| `source` | string | Yes | Fully-qualified class name of the `TracingSource` implementation |

**Built-In Tracings:**

- **`correlation_id`**: Session-level identifier (persists across multiple requests from the same user)
  - Default header: `X-Correlation-Id`
  - Environment variable: `LARAVEL_TRACING_CORRELATION_ID_HEADER`

- **`request_id`**: Request-level identifier (unique per HTTP request)
  - Default header: `X-Request-Id`
  - Environment variable: `LARAVEL_TRACING_REQUEST_ID_HEADER`

#### HTTP Client Settings

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `http_client.enabled` | bool | `false` | When `true`, `Http::withTracing()` is available globally. When `false`, it's opt-in per request. |

**Environment Variable:**
- `LARAVEL_TRACING_HTTP_CLIENT_ENABLED`

### Configuration Examples

#### Disable Tracing in Local Environment

```env
# .env.local
LARAVEL_TRACING_ENABLED=false
```

#### Customize Header Names

```env
# .env
LARAVEL_TRACING_CORRELATION_ID_HEADER=X-Trace-Id
LARAVEL_TRACING_REQUEST_ID_HEADER=X-Span-Id
```

#### Disable External Header Acceptance (Security)

If your application is publicly exposed and you don't want to accept correlation IDs from untrusted sources:

```env
# .env.production
LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS=false
```

> **Security Note**: When `accept_external_headers` is `true`, the package will use correlation/request IDs sent by clients. Only enable this if you trust your upstream services (API gateways, load balancers, internal services).

#### Enable HTTP Client Tracing Globally

```env
# .env
LARAVEL_TRACING_HTTP_CLIENT_ENABLED=true
```

When enabled, all HTTP requests automatically include tracing headers without calling `withTracing()`:

```php
// Tracing headers automatically attached
Http::get('https://api.example.com/data');
```

---

## Usage Examples

### Example 1: Accessing Tracing Values in Controllers

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Get all tracing values
        $tracings = LaravelTracing::all();

        // Get specific values
        $correlationId = LaravelTracing::correlationId();
        $requestId = LaravelTracing::requestId();

        // Use in business logic
        $order = Order::create([
            'user_id' => $request->user()->id,
            'correlation_id' => $correlationId, // Store in database for audit
        ]);

        return response()->json(['order_id' => $order->id]);
    }
}
```

### Example 2: Using Tracings in Log Context

```php
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// In any part of your application
Log::info('Processing payment', array_merge(
    ['amount' => 99.99, 'currency' => 'USD'],
    LaravelTracing::all() // Add all tracing values to log context
));
```

**Log output:**
```json
{
  "message": "Processing payment",
  "amount": 99.99,
  "currency": "USD",
  "correlation_id": "550e8400-e29b-41d4-a716-446655440000",
  "request_id": "123e4567-e89b-12d3-a456-426614174000",
  "timestamp": "2026-02-11T10:30:00Z"
}
```

### Example 3: Session Persistence (Same Correlation ID Across Requests)

**Request 1:**
```bash
curl -I http://localhost:8000/api/cart/add
```

**Response:**
```
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000
X-Request-Id: 123e4567-e89b-12d3-a456-426614174000
Set-Cookie: laravel_session=...
```

**Request 2 (same session):**
```bash
curl -I http://localhost:8000/api/cart/checkout \
  -H "Cookie: laravel_session=..."
```

**Response:**
```
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000  ← Same correlation ID!
X-Request-Id: 789e0123-e45b-67c8-d901-234567890abc      ← New request ID
```

The correlation ID persists across requests from the same session, enabling you to trace all actions from a single user.

### Example 4: Tracing in Queued Jobs

```php
// Dispatch job from controller
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create($request->all());

        // Correlation ID and request ID are automatically serialized with the job
        ProcessOrder::dispatch($order);

        return response()->json(['order_id' => $order->id]);
    }
}
```

```php
// Job handler
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        // Tracing values from the original request are restored
        Log::info('Job processing order', [
            'order_id' => $this->order->id,
            'correlation_id' => LaravelTracing::correlationId(), // Same as dispatch request
            'request_id' => LaravelTracing::requestId(),         // Preserved from dispatch
        ]);

        // Process order...
    }
}
```

**Result**: All logs from the job execution include the same correlation ID as the dispatching request, making it easy to trace the entire flow.

### Example 5: Multiple Jobs Share Correlation ID

```php
// Dispatch multiple jobs from the same request
public function bulkProcess(Request $request)
{
    foreach ($request->orders as $order) {
        ProcessOrder::dispatch($order);
        SendInvoice::dispatch($order);
        UpdateInventory::dispatch($order);
    }

    return response()->json(['status' => 'queued']);
}
```

**All dispatched jobs** (`ProcessOrder`, `SendInvoice`, `UpdateInventory`) will share the same correlation ID, allowing you to filter logs by correlation ID to see all related job executions.

### Example 6: Forwarding Tracings to External APIs

```php
use Illuminate\Support\Facades\Http;

// Call external service with tracing headers
public function fetchUserData(int $userId)
{
    $response = Http::withTracing()
        ->get("https://api.example.com/users/{$userId}");

    return $response->json();
}
```

**HTTP request sent to external service:**
```
GET /users/42 HTTP/1.1
Host: api.example.com
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000
X-Request-Id: 123e4567-e89b-12d3-a456-426614174000
```

The external service can now log requests with the same correlation ID, enabling distributed tracing across services.

---

## Custom Tracing Sources

Laravel Tracing is fully extensible. You can add custom tracing sources to track additional context like:

- User ID
- Tenant ID (for multi-tenant applications)
- Application version
- Custom business identifiers

### Quick Example: Add User ID Tracing

#### Step 1: Create Custom Source Class

```php
// app/Tracings/UserIdSource.php
<?php

namespace App\Tracings;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;

class UserIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Return authenticated user ID, or 'guest' if not authenticated
        return (string) ($request->user()?->id ?? 'guest');
    }

    public function headerName(): string
    {
        return config('laravel-tracing.tracings.user_id.header', 'X-User-Id');
    }

    public function restoreFromJob(string $value): string
    {
        return $value; // No transformation needed
    }
}
```

#### Step 2: Register in Configuration

```bash
php artisan vendor:publish --tag=laravel-tracing-config
```

```php
// config/laravel-tracing.php
'tracings' => [
    'correlation_id' => [...],
    'request_id' => [...],

    // Add custom user ID tracing
    'user_id' => [
        'enabled' => env('LARAVEL_TRACING_USER_ID_ENABLED', true),
        'header' => env('LARAVEL_TRACING_USER_ID_HEADER', 'X-User-Id'),
        'source' => \App\Tracings\UserIdSource::class,
    ],
],
```

#### Step 3: Use in Your Application

```php
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// Access user ID from anywhere
$userId = LaravelTracing::get('user_id');

// Include in logs
Log::info('User action', [
    'user_id' => LaravelTracing::get('user_id'),
    'correlation_id' => LaravelTracing::correlationId(),
]);
```

**HTTP response headers now include:**
```
X-Correlation-Id: 550e8400-e29b-41d4-a716-446655440000
X-Request-Id: 123e4567-e89b-12d3-a456-426614174000
X-User-Id: 42
```

### Alternative: Runtime Registration

Register custom tracings programmatically in a service provider:

```php
// app/Providers/AppServiceProvider.php
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;
use App\Tracings\UserIdSource;

public function boot(): void
{
    app(TracingManager::class)->extend('user_id', new UserIdSource());
}
```

### Complete Documentation

For comprehensive documentation on custom tracing sources, including:

- Implementing the `TracingSource` contract
- Replacing built-in sources
- Complete working examples (User ID, Tenant ID, App Version)
- Best practices and testing strategies

**See:** [Extension Architecture Documentation](docs/architecture/EXTENSIONS.md)

---

## Troubleshooting

### Tracing Headers Not Appearing in Response

**Symptoms:**
- Response headers don't include `X-Correlation-Id` or `X-Request-Id`

**Possible Causes & Solutions:**

1. **Package disabled via environment variable**
   ```bash
   # Check .env file
   LARAVEL_TRACING_ENABLED=true  # Make sure this is true (or remove it for default)
   ```

2. **Middleware not registered**
   - Verify that you manually registered the middleware in `bootstrap/app.php` (required for Laravel 12)
   - Ensure the middleware is added to the correct middleware group (`web` for session-based apps)
   - Check that no conflicting middleware is interfering

3. **Response is a redirect or exception**
   - Tracing middleware runs on normal responses. If the response is an exception or early redirect, middleware may not execute.

**Debugging Steps:**
```php
// Add to a controller to verify package is working
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

dd(LaravelTracing::all());
// Should output: ['correlation_id' => '...', 'request_id' => '...']
```

---

### Tracings Not Accessible in Jobs

**Symptoms:**
- `LaravelTracing::all()` returns empty array inside job handler

**Possible Causes & Solutions:**

1. **Queue connection doesn't support serialization**
   - Ensure your queue driver supports serialization (`database`, `redis`, `sqs`)
   - `sync` driver works but processes immediately (no async execution)

2. **Job implements custom serialization**
   - If your job implements `SerializesModels` or custom serialization, ensure you're not interfering with the package's job listeners

3. **Package disabled**
   ```bash
   # Check environment variable
   LARAVEL_TRACING_ENABLED=true
   ```

**Debugging Steps:**
```php
// In job handler
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

Log::info('Job tracings', LaravelTracing::all());
// Check logs to see if tracings are restored
```

---

### Custom Tracing Source Not Loaded

**Symptoms:**
- Custom tracing doesn't appear in `LaravelTracing::all()`
- Custom header not attached to responses

**Possible Causes & Solutions:**

1. **Config not published or cache issue**
   ```bash
   # Clear config cache
   php artisan config:clear

   # Republish config
   php artisan vendor:publish --tag=laravel-tracing-config --force
   ```

2. **Custom source class not found**
   - Ensure the class exists at the path specified in config
   - Check namespace matches config entry
   - Run `composer dump-autoload` to refresh autoloader

3. **Custom tracing disabled in config**
   ```php
   // config/laravel-tracing.php
   'tracings' => [
       'user_id' => [
           'enabled' => true, // Make sure this is true
           'header' => 'X-User-Id',
           'source' => \App\Tracings\UserIdSource::class,
       ],
   ],
   ```

4. **TracingSource contract not implemented correctly**
   - Verify your custom source implements all three methods:
     - `resolve(Request $request): string`
     - `headerName(): string`
     - `restoreFromJob(string $value): string`

**Debugging Steps:**
```php
// Check if tracing is registered
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

dd(LaravelTracing::has('user_id')); // Should be true if registered
```

---

### External Headers Not Accepted

**Symptoms:**
- Sending `X-Correlation-Id` header with request, but server generates new ID instead

**Solution:**

Check the `accept_external_headers` configuration:

```php
// config/laravel-tracing.php
'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS', true),
```

```bash
# .env
LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS=true
```

When `false`, the package ignores external headers and always generates fresh values.

---

### Session Correlation ID Not Persisting

**Symptoms:**
- Each request gets a new correlation ID, even from the same session

**Possible Causes & Solutions:**

1. **Session driver not configured**
   - Ensure `SESSION_DRIVER` is set in `.env` (`cookie`, `file`, `redis`, etc.)
   - `array` driver doesn't persist sessions across requests

2. **Session middleware not running**
   - Check `app/Http/Kernel.php` includes `\Illuminate\Session\Middleware\StartSession::class`

3. **Browser not sending cookies**
   - Check `Set-Cookie` header is included in response
   - Ensure browser accepts cookies (not in incognito/privacy mode)

**Debugging Steps:**
```bash
# Make first request
curl -c cookies.txt http://localhost:8000

# Make second request with cookies
curl -b cookies.txt http://localhost:8000

# Both should have the same X-Correlation-Id
```

---

## Frequently Asked Questions

### Why use correlation ID vs request ID?

- **Correlation ID**: Represents a **user session** or **business transaction**. Persists across multiple requests from the same user. Useful for tracing an entire user journey (e.g., browse → add to cart → checkout → payment).

- **Request ID**: Represents a **single HTTP request**. Unique per request, even within the same session. Useful for identifying specific requests in logs.

**Example:**
- User visits `/products` → Correlation ID: `abc123`, Request ID: `req1`
- User visits `/cart` (same session) → Correlation ID: `abc123`, Request ID: `req2`
- User visits `/checkout` (same session) → Correlation ID: `abc123`, Request ID: `req3`

All three requests share the same correlation ID, allowing you to trace the entire session.

### Can I use this with stateless APIs?

**Yes.** For stateless APIs (no session), correlation IDs are still useful when forwarded from upstream services:

1. **Client sends request with correlation ID**:
   ```bash
   curl -H "X-Correlation-Id: client-abc-123" http://localhost:8000/api/data
   ```

2. **Your API accepts and uses it** (if `accept_external_headers` is `true`):
   ```php
   LaravelTracing::correlationId(); // Returns: "client-abc-123"
   ```

3. **Your API forwards it to downstream services**:
   ```php
   Http::withTracing()->get('https://downstream-service.com/data');
   // Sends: X-Correlation-Id: client-abc-123
   ```

This enables end-to-end tracing across multiple stateless services.

### How do I integrate with logging?

Laravel Tracing provides tracing values — you add them to your log context:

```php
use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// Manual integration
Log::info('User login', array_merge(
    ['user_id' => $userId],
    LaravelTracing::all()
));
```

For automatic integration, create a custom log processor:

```php
// config/logging.php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily'],
    'processors' => [\App\Logging\AddTracingContext::class],
],
```

```php
// app/Logging/AddTracingContext.php
namespace App\Logging;

use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

class AddTracingContext
{
    public function __invoke(array $record): array
    {
        $record['extra'] = array_merge(
            $record['extra'] ?? [],
            LaravelTracing::all()
        );

        return $record;
    }
}
```

Now all logs automatically include tracing context.

---

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test -- --coverage
```

---

## Credits

- [Junior Fontenele](https://github.com/jftecnologia)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
