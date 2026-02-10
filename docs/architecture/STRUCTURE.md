# Package Structure

**Purpose**: Define the directory organization, namespace mapping, and file placement rules for the Laravel Tracing package.

---

## Overview

The package follows Laravel's standard package structure with clear separation between core functionality, integration points, and extensibility layers. The architecture prioritizes clarity and single responsibility per directory/namespace.

**Key Principles**:
- **No DDD layers** - No repositories, aggregates, or value objects
- **Feature-based organization** - Group by functionality, not by technical layer
- **Clear boundaries** - Each namespace has a single, well-defined purpose
- **Flat hierarchy** - Avoid deep nesting; keep structure simple and navigable

---

## Directory Tree

```
laravel-tracing/
├── src/
│   ├── LaravelTracing.php                    # Main facade implementation (core API)
│   ├── LaravelTracingServiceProvider.php     # Service provider (bootstrap)
│   │
│   ├── Facades/
│   │   └── LaravelTracing.php                # Facade class
│   │
│   ├── Middleware/
│   │   ├── IncomingTracingMiddleware.php     # Handle incoming requests
│   │   └── OutgoingTracingMiddleware.php     # Attach headers to responses
│   │
│   ├── Tracings/
│   │   ├── Contracts/
│   │   │   ├── TracingSource.php             # Interface for all tracing sources
│   │   │   └── TracingStorage.php            # Interface for storage backends
│   │   │
│   │   ├── Sources/
│   │   │   ├── CorrelationIdSource.php       # Built-in: X-Correlation-Id
│   │   │   └── RequestIdSource.php           # Built-in: X-Request-Id
│   │   │
│   │   └── TracingManager.php                # Manage active tracings
│   │
│   ├── Storage/
│   │   ├── RequestStorage.php                # Store tracings in current request
│   │   └── SessionStorage.php                # Persist correlation ID in session
│   │
│   ├── Jobs/
│   │   └── TracingJobDispatcher.php          # Attach tracings to dispatched jobs
│   │
│   ├── Http/
│   │   └── HttpClientTracing.php             # Attach tracings to outgoing HTTP calls
│   │
│   └── Support/
│       ├── IdGenerator.php                   # UUID generation
│       └── HeaderSanitizer.php               # Validate/sanitize header values
│
├── config/
│   └── laravel-tracing.php                   # Package configuration file
│
├── tests/
│   ├── Feature/
│   │   ├── IncomingRequestTracingTest.php
│   │   ├── OutgoingResponseTracingTest.php
│   │   ├── JobPropagationTest.php
│   │   ├── HttpClientIntegrationTest.php
│   │   └── CustomTracingSourceTest.php
│   │
│   └── Unit/
│       ├── TracingManagerTest.php
│       ├── CorrelationIdSourceTest.php
│       ├── RequestIdSourceTest.php
│       ├── SessionStorageTest.php
│       └── HeaderSanitizerTest.php
│
├── docs/
│   ├── PRD.md
│   ├── architecture/
│   │   ├── README.md
│   │   ├── STRUCTURE.md                      # This file
│   │   ├── COMPONENTS.md
│   │   ├── DATA_FLOW.md
│   │   ├── CONFIGURATION.md
│   │   └── EXTENSIONS.md
│   └── engineering/
│       ├── STACK.md
│       ├── CODE_STANDARDS.md
│       ├── WORKFLOW.md
│       └── TESTING.md
│
└── workbench/                                 # Orchestra Testbench workbench (dev only)
```

---

## Namespace Organization

### Root Namespace

```
JuniorFontenele\LaravelTracing
```

All package classes live under this root namespace.

### Namespace Mapping

| Namespace | Purpose | Examples |
|-----------|---------|----------|
| `JuniorFontenele\LaravelTracing` | Core package classes | `LaravelTracing.php`, `LaravelTracingServiceProvider.php` |
| `JuniorFontenele\LaravelTracing\Facades` | Facade implementations | `LaravelTracing.php` |
| `JuniorFontenele\LaravelTracing\Middleware` | HTTP middleware | `IncomingTracingMiddleware.php`, `OutgoingTracingMiddleware.php` |
| `JuniorFontenele\LaravelTracing\Tracings` | Tracing logic and sources | `TracingManager.php` |
| `JuniorFontenele\LaravelTracing\Tracings\Contracts` | Interfaces for extensibility | `TracingSource.php`, `TracingStorage.php` |
| `JuniorFontenele\LaravelTracing\Tracings\Sources` | Built-in and custom tracing sources | `CorrelationIdSource.php`, `RequestIdSource.php` |
| `JuniorFontenele\LaravelTracing\Storage` | Storage backends | `RequestStorage.php`, `SessionStorage.php` |
| `JuniorFontenele\LaravelTracing\Jobs` | Queue integration | `TracingJobDispatcher.php` |
| `JuniorFontenele\LaravelTracing\Http` | HTTP client integration | `HttpClientTracing.php` |
| `JuniorFontenele\LaravelTracing\Support` | Utilities and helpers | `IdGenerator.php`, `HeaderSanitizer.php` |

---

## File Placement Rules

### Where to Add New Features

| Feature Type | Location | Namespace | Notes |
|--------------|----------|-----------|-------|
| **New tracing source** (e.g., `X-User-Id`) | `src/Tracings/Sources/UserIdSource.php` | `JuniorFontenele\LaravelTracing\Tracings\Sources` | Implement `TracingSource` contract |
| **New storage backend** (e.g., cache) | `src/Storage/CacheStorage.php` | `JuniorFontenele\LaravelTracing\Storage` | Implement `TracingStorage` contract |
| **New middleware** | `src/Middleware/CustomMiddleware.php` | `JuniorFontenele\LaravelTracing\Middleware` | Extend or create new middleware |
| **New utility/helper** | `src/Support/HelperClass.php` | `JuniorFontenele\LaravelTracing\Support` | General-purpose utilities |
| **Core API method** | Add to `src/LaravelTracing.php` | `JuniorFontenele\LaravelTracing` | Main facade implementation |

### Feature Test Location

Feature tests live in `tests/Feature/` and test end-to-end flows:

- **Incoming request tracing**: `tests/Feature/IncomingRequestTracingTest.php`
- **Outgoing response tracing**: `tests/Feature/OutgoingResponseTracingTest.php`
- **Job propagation**: `tests/Feature/JobPropagationTest.php`
- **HTTP client integration**: `tests/Feature/HttpClientIntegrationTest.php`
- **Custom tracings**: `tests/Feature/CustomTracingSourceTest.php`

### Unit Test Location

Unit tests live in `tests/Unit/` and test individual components in isolation:

- **Manager logic**: `tests/Unit/TracingManagerTest.php`
- **Tracing sources**: `tests/Unit/CorrelationIdSourceTest.php`, `tests/Unit/RequestIdSourceTest.php`
- **Storage backends**: `tests/Unit/SessionStorageTest.php`, `tests/Unit/RequestStorageTest.php`
- **Utilities**: `tests/Unit/HeaderSanitizerTest.php`, `tests/Unit/IdGeneratorTest.php`

---

## Configuration File Structure

```php
// config/laravel-tracing.php
return [
    // Global enable/disable toggle
    'enabled' => env('LARAVEL_TRACING_ENABLED', true),

    // Accept external tracing headers from incoming requests
    'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL', true),

    // Built-in tracing sources
    'tracings' => [
        'correlation_id' => [
            'enabled' => env('LARAVEL_TRACING_CORRELATION_ENABLED', true),
            'header' => env('LARAVEL_TRACING_CORRELATION_HEADER', 'X-Correlation-Id'),
            'source' => \JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource::class,
        ],
        'request_id' => [
            'enabled' => env('LARAVEL_TRACING_REQUEST_ENABLED', true),
            'header' => env('LARAVEL_TRACING_REQUEST_HEADER', 'X-Request-Id'),
            'source' => \JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource::class,
        ],
    ],

    // HTTP client integration (opt-in)
    'http_client' => [
        'enabled' => env('LARAVEL_TRACING_HTTP_CLIENT_ENABLED', false),
    ],
];
```

**See [CONFIGURATION.md](CONFIGURATION.md) for detailed configuration architecture.**

---

## Key Architectural Decisions

### 1. Flat Structure Over Deep Nesting

**Decision**: Keep namespace hierarchy shallow (max 3 levels deep).

**Rationale**:
- Easier navigation and file discovery
- Avoids over-engineering typical of DDD architectures
- Aligns with "no unnecessary abstraction" principle

### 2. Feature-Based Organization

**Decision**: Group files by feature/functionality, not by technical layer.

**Rationale**:
- `Tracings/` contains all tracing-related logic (sources, contracts, manager)
- `Storage/` contains all storage backends
- `Jobs/` contains queue integration logic
- Each directory has a clear purpose and single responsibility

### 3. Contracts in Feature Namespace

**Decision**: Place contracts (interfaces) alongside their implementations in `Tracings/Contracts/`.

**Rationale**:
- Contracts are part of the tracing feature, not a separate architectural layer
- Easier to discover and understand in context
- Avoids generic "Contracts" or "Interfaces" root-level directories

### 4. No Repositories or Domain Models

**Decision**: No repository pattern, no domain models, no aggregates.

**Rationale**:
- This package doesn't manage persistent entities
- Tracing data is ephemeral (request-scoped or session-scoped)
- Repository pattern adds unnecessary abstraction for simple key-value storage

### 5. Support Namespace for Utilities

**Decision**: Generic utilities (ID generation, sanitization) live in `Support/`.

**Rationale**:
- Follows Laravel convention (`Illuminate\Support`)
- Clear separation between feature logic and utilities
- Easy to discover and reuse across features

---

## Adding New Components

### Example: Adding a Custom Tracing Source

**Scenario**: Add `X-User-Id` tracing.

**Steps**:
1. Create `src/Tracings/Sources/UserIdSource.php`
2. Implement `TracingSource` contract
3. Register in `config/laravel-tracing.php` under `tracings.user_id`
4. Add test in `tests/Unit/UserIdSourceTest.php`
5. Add integration test in `tests/Feature/CustomTracingSourceTest.php`

**File Location**:
```
src/Tracings/Sources/UserIdSource.php
```

**Namespace**:
```php
namespace JuniorFontenele\LaravelTracing\Tracings\Sources;
```

### Example: Adding a New Storage Backend

**Scenario**: Add cache-based storage for correlation ID.

**Steps**:
1. Create `src/Storage/CacheStorage.php`
2. Implement `TracingStorage` contract
3. Use dependency injection in `CorrelationIdSource` to allow storage swapping
4. Add test in `tests/Unit/CacheStorageTest.php`

**File Location**:
```
src/Storage/CacheStorage.php
```

**Namespace**:
```php
namespace JuniorFontenele\LaravelTracing\Storage;
```

---

## Extension Points

**Where developers can extend the package**:

1. **Custom tracing sources**: Implement `TracingSource` contract in `src/Tracings/Sources/`
2. **Custom storage backends**: Implement `TracingStorage` contract in `src/Storage/`
3. **Custom middleware**: Add to `src/Middleware/` and register in service provider
4. **Custom ID generators**: Extend or replace `IdGenerator` in `src/Support/`

**See [EXTENSIONS.md](EXTENSIONS.md) for detailed extension architecture.**

---

## Related Documentation

- **[COMPONENTS.md](COMPONENTS.md)** → Core components and their responsibilities
- **[DATA_FLOW.md](DATA_FLOW.md)** → Request lifecycle and data flow
- **[CONFIGURATION.md](CONFIGURATION.md)** → Configuration architecture
- **[EXTENSIONS.md](EXTENSIONS.md)** → How to extend and customize
- **[../PRD.md](../PRD.md)** → Product requirements this structure addresses

---

## Maintenance Notes

**When modifying the structure**:

1. ✅ Update this document to reflect changes
2. ✅ Update namespace imports in affected files
3. ✅ Update tests to match new file locations
4. ✅ Run `composer lint` to catch broken imports
5. ✅ Update related architecture docs (COMPONENTS.md, EXTENSIONS.md)

**Keep this document synchronized with the actual codebase.**
