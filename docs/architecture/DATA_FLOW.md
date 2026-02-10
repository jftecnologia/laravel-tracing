# Data Flow Architecture

**Purpose**: Document how tracing data flows through the system across different scenarios.

---

## Overview

This document describes the complete lifecycle of tracing data from request entry to response exit, including propagation to queued jobs and external HTTP calls.

**Key Flows**:
1. **Incoming HTTP Request** → Resolve tracings → Attach to response
2. **Job Dispatch** → Serialize tracings → Restore in job execution
3. **Outgoing HTTP Call** → Attach tracings as headers
4. **Session Persistence** → Correlation ID survives across requests

---

## Flow 1: Incoming HTTP Request (New User Session)

**Scenario**: First request from a user with no existing session.

### Sequence Diagram

```mermaid
sequenceDiagram
    participant Browser
    participant Middleware as IncomingTracingMiddleware
    participant Manager as TracingManager
    participant CorrelationSource as CorrelationIdSource
    participant RequestSource as RequestIdSource
    participant SessionStorage
    participant IdGenerator
    participant RequestStorage
    participant Controller
    participant OutgoingMiddleware as OutgoingTracingMiddleware
    participant Response

    Browser->>Middleware: GET /api/users (no headers)
    Middleware->>Manager: resolveAll(request)

    Manager->>CorrelationSource: resolve(request)
    CorrelationSource->>CorrelationSource: Check external header (accept_external_headers)
    Note over CorrelationSource: Header not present
    CorrelationSource->>SessionStorage: get('correlation_id')
    SessionStorage-->>CorrelationSource: null (no session yet)
    CorrelationSource->>IdGenerator: generate()
    IdGenerator-->>CorrelationSource: "abc-123"
    CorrelationSource->>SessionStorage: set('correlation_id', 'abc-123')
    CorrelationSource-->>Manager: "abc-123"
    Manager->>RequestStorage: set('correlation_id', 'abc-123')

    Manager->>RequestSource: resolve(request)
    RequestSource->>RequestSource: Check external header (accept_external_headers)
    Note over RequestSource: Header not present
    RequestSource->>IdGenerator: generate()
    IdGenerator-->>RequestSource: "xyz-456"
    RequestSource-->>Manager: "xyz-456"
    Manager->>RequestStorage: set('request_id', 'xyz-456')

    Middleware->>Controller: Continue request
    Controller-->>OutgoingMiddleware: Return response
    OutgoingMiddleware->>Manager: all()
    Manager->>RequestStorage: all()
    RequestStorage-->>Manager: {correlation_id: "abc-123", request_id: "xyz-456"}
    Manager-->>OutgoingMiddleware: {correlation_id: "abc-123", request_id: "xyz-456"}
    OutgoingMiddleware->>Response: setHeader('X-Correlation-Id', 'abc-123')
    OutgoingMiddleware->>Response: setHeader('X-Request-Id', 'xyz-456')
    Response->>Browser: 200 OK with headers
```

### Step-by-Step Flow

1. **Request Arrives**: Browser sends GET request with no tracing headers
2. **IncomingTracingMiddleware Executes**: Calls `TracingManager::resolveAll()`
3. **Resolve Correlation ID**:
   - Check if external header present → No
   - Check session storage → No (new session)
   - Generate new UUID → `abc-123`
   - Store in session → Persisted for future requests
   - Store in request storage → Available during this request
4. **Resolve Request ID**:
   - Check if external header present → No
   - Generate new UUID → `xyz-456`
   - Store in request storage → Available during this request
5. **Controller Executes**: Business logic runs (can access tracings via `LaravelTracing::all()`)
6. **OutgoingTracingMiddleware Executes**: Reads all tracings and attaches to response headers
7. **Response Sent**: Browser receives response with `X-Correlation-Id` and `X-Request-Id` headers

**Result**:
- ✅ Correlation ID `abc-123` stored in session (will be reused on next request)
- ✅ Request ID `xyz-456` generated for this request only
- ✅ Both headers attached to response

---

## Flow 2: Subsequent HTTP Request (Existing Session)

**Scenario**: Second request from the same user session.

### Sequence Diagram

```mermaid
sequenceDiagram
    participant Browser
    participant Middleware as IncomingTracingMiddleware
    participant Manager as TracingManager
    participant CorrelationSource as CorrelationIdSource
    participant RequestSource as RequestIdSource
    participant SessionStorage
    participant IdGenerator
    participant RequestStorage
    participant Response

    Browser->>Middleware: GET /api/posts (with session cookie)
    Middleware->>Manager: resolveAll(request)

    Manager->>CorrelationSource: resolve(request)
    CorrelationSource->>CorrelationSource: Check external header
    Note over CorrelationSource: Header not present
    CorrelationSource->>SessionStorage: get('correlation_id')
    SessionStorage-->>CorrelationSource: "abc-123" (from previous request)
    CorrelationSource-->>Manager: "abc-123" (reused, not regenerated)
    Manager->>RequestStorage: set('correlation_id', 'abc-123')

    Manager->>RequestSource: resolve(request)
    RequestSource->>RequestSource: Check external header
    Note over RequestSource: Header not present
    RequestSource->>IdGenerator: generate()
    IdGenerator-->>RequestSource: "xyz-789" (NEW request ID)
    RequestSource-->>Manager: "xyz-789"
    Manager->>RequestStorage: set('request_id', 'xyz-789')

    Middleware->>Response: Continue and attach headers
    Response->>Browser: Headers: X-Correlation-Id: abc-123, X-Request-Id: xyz-789
```

### Key Behavior

- **Correlation ID**: Reused from session (`abc-123`) → Same across all requests in session
- **Request ID**: Newly generated (`xyz-789`) → Different for each request
- **Session Persistence**: Session storage maintains correlation ID across requests

**Result**:
- ✅ Same correlation ID as first request (`abc-123`)
- ✅ New request ID for this request (`xyz-789`)
- ✅ Session continuity maintained

---

## Flow 3: Accepting External Tracing Headers

**Scenario**: External service forwards a request with existing tracing headers.

### Sequence Diagram

```mermaid
sequenceDiagram
    participant ExternalService as External Service
    participant Middleware as IncomingTracingMiddleware
    participant Manager as TracingManager
    participant CorrelationSource as CorrelationIdSource
    participant RequestSource as RequestIdSource
    participant HeaderSanitizer
    participant SessionStorage
    participant RequestStorage
    participant Response

    ExternalService->>Middleware: POST /api/webhooks<br/>Headers: X-Correlation-Id: external-abc<br/>X-Request-Id: external-xyz

    Middleware->>Manager: resolveAll(request)

    Manager->>CorrelationSource: resolve(request)
    CorrelationSource->>CorrelationSource: Check config: accept_external_headers = true
    CorrelationSource->>HeaderSanitizer: sanitize('external-abc')
    HeaderSanitizer-->>CorrelationSource: 'external-abc' (valid)
    CorrelationSource->>SessionStorage: set('correlation_id', 'external-abc')
    CorrelationSource-->>Manager: "external-abc"
    Manager->>RequestStorage: set('correlation_id', 'external-abc')

    Manager->>RequestSource: resolve(request)
    RequestSource->>RequestSource: Check config: accept_external_headers = true
    RequestSource->>HeaderSanitizer: sanitize('external-xyz')
    HeaderSanitizer-->>RequestSource: 'external-xyz' (valid)
    RequestSource-->>Manager: "external-xyz"
    Manager->>RequestStorage: set('request_id', 'external-xyz')

    Middleware->>Response: Continue and attach headers
    Response->>ExternalService: Headers: X-Correlation-Id: external-abc, X-Request-Id: external-xyz
```

### Key Behavior

- **External Headers Accepted**: When `accept_external_headers = true`
- **Header Sanitization**: All incoming values are sanitized for security
- **Session Overwrite**: External correlation ID replaces any existing session value
- **Propagation**: External values are propagated back in response and to downstream services

**Security Note**: `HeaderSanitizer` validates all external values to prevent injection attacks.

**Result**:
- ✅ Uses external correlation ID (`external-abc`)
- ✅ Uses external request ID (`external-xyz`)
- ✅ Enables cross-service tracing

---

## Flow 4: Rejecting External Headers (Security Mode)

**Scenario**: External headers are disabled for security.

### Configuration

```php
// config/laravel-tracing.php
'accept_external_headers' => false,
```

### Sequence Diagram

```mermaid
sequenceDiagram
    participant ExternalService as External Service
    participant Middleware as IncomingTracingMiddleware
    participant CorrelationSource as CorrelationIdSource
    participant RequestSource as RequestIdSource
    participant IdGenerator

    ExternalService->>Middleware: POST /api/users<br/>Headers: X-Correlation-Id: malicious-value

    Middleware->>CorrelationSource: resolve(request)
    CorrelationSource->>CorrelationSource: Check config: accept_external_headers = false
    Note over CorrelationSource: Ignore external header
    CorrelationSource->>IdGenerator: generate()
    IdGenerator-->>CorrelationSource: "safe-internal-id"
    CorrelationSource-->>Middleware: "safe-internal-id"

    Middleware->>RequestSource: resolve(request)
    RequestSource->>RequestSource: Check config: accept_external_headers = false
    Note over RequestSource: Ignore external header
    RequestSource->>IdGenerator: generate()
    IdGenerator-->>RequestSource: "safe-request-id"
    RequestSource-->>Middleware: "safe-request-id"
```

### Key Behavior

- **External Headers Ignored**: All incoming tracing headers are discarded
- **Internal Generation**: Package always generates its own IDs
- **Security Boundary**: Prevents external services from injecting tracing values

**Use Case**: Internal services that don't trust external tracing headers.

---

## Flow 5: Job Dispatch and Execution

**Scenario**: Queued job is dispatched during a traced request.

### Sequence Diagram

```mermaid
sequenceDiagram
    participant Controller
    participant Job as SendEmailJob
    participant Dispatcher as TracingJobDispatcher
    participant Manager as TracingManager
    participant Queue
    participant Worker
    participant JobHandler

    Controller->>Job: dispatch()
    Job->>Dispatcher: Event: JobQueuing
    Dispatcher->>Manager: all()
    Manager-->>Dispatcher: {correlation_id: "abc-123", request_id: "xyz-456"}
    Dispatcher->>Job: Attach to payload:<br/>job.tracings = {...}
    Job->>Queue: Queue job with tracings

    Note over Queue: Job waits in queue...

    Queue->>Worker: Process job
    Worker->>Dispatcher: Event: JobProcessing
    Dispatcher->>Job: Read job.tracings
    Job-->>Dispatcher: {correlation_id: "abc-123", request_id: "xyz-456"}
    Dispatcher->>Manager: restore(tracings)
    Manager->>Manager: Set correlation_id = "abc-123"
    Manager->>Manager: Set request_id = "xyz-456" (PRESERVED, not regenerated)
    Worker->>JobHandler: handle()
    JobHandler->>Manager: LaravelTracing::correlationId()
    Manager-->>JobHandler: "abc-123"
    JobHandler->>JobHandler: Log with correlation ID context
```

### Step-by-Step Flow

**1. Dispatching (in request context)**:
   - Controller dispatches job: `SendEmailJob::dispatch()`
   - Laravel fires `JobQueuing` event
   - `TracingJobDispatcher` listens to event
   - Dispatcher reads all current tracings from `TracingManager`
   - Dispatcher serializes tracings as key-value array
   - Dispatcher attaches tracings to job payload: `$job->tracings = [...]`
   - Job is queued with tracings embedded

**2. Execution (in worker context)**:
   - Queue worker picks up job
   - Laravel fires `JobProcessing` event (before `handle()`)
   - `TracingJobDispatcher` listens to event
   - Dispatcher reads `$job->tracings` from payload
   - Dispatcher calls `TracingManager::restore($tracings)`
   - Manager injects values into `RequestStorage`
   - Job's `handle()` method executes with tracings available
   - `LaravelTracing::correlationId()` returns original correlation ID

**Key Behavior**:
- **Original Request ID Preserved**: Job executes with the request ID from the dispatching request (not a new one)
- **Correlation ID Maintained**: Same correlation ID propagates through job execution
- **Global Accessor Works**: `LaravelTracing::all()` works inside job handlers

**Job Payload Example**:
```php
[
    'displayName' => 'App\\Jobs\\SendEmailJob',
    'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
    'data' => [
        'commandName' => 'App\\Jobs\\SendEmailJob',
        'command' => '...', // Serialized job
    ],
    'tracings' => [ // Attached by TracingJobDispatcher
        'correlation_id' => 'abc-123',
        'request_id' => 'xyz-456',
    ],
]
```

**Result**:
- ✅ Correlation ID propagated to job (`abc-123`)
- ✅ Original request ID preserved in job (`xyz-456`)
- ✅ Full tracing context available in job handler

---

## Flow 6: Outgoing HTTP Call with Tracing

**Scenario**: Application makes an external API call with tracing headers.

### Sequence Diagram

```mermaid
sequenceDiagram
    participant Controller
    participant HttpClient as Http::withTracing()
    participant Tracing as HttpClientTracing
    participant Manager as TracingManager
    participant ExternalAPI as External API

    Controller->>HttpClient: get('https://api.example.com/users')
    HttpClient->>Tracing: Attach tracings
    Tracing->>Manager: all()
    Manager-->>Tracing: {correlation_id: "abc-123", request_id: "xyz-456"}
    Tracing->>HttpClient: setHeader('X-Correlation-Id', 'abc-123')
    Tracing->>HttpClient: setHeader('X-Request-Id', 'xyz-456')
    HttpClient->>ExternalAPI: GET /users<br/>Headers: X-Correlation-Id: abc-123<br/>X-Request-Id: xyz-456
    ExternalAPI-->>HttpClient: 200 OK (with data)
    HttpClient-->>Controller: Return response
```

### Usage Examples

**Per-Request (Opt-in)**:
```php
use Illuminate\Support\Facades\Http;

$response = Http::withTracing()
    ->get('https://api.example.com/users');
```

**Global (via Config)**:
```php
// config/laravel-tracing.php
'http_client' => [
    'enabled' => true, // Attach tracings to ALL outgoing requests
],
```

### Key Behavior

- **Opt-In by Default**: Must explicitly call `withTracing()` or enable globally
- **All Tracings Forwarded**: All active tracings (correlation ID, request ID, custom tracings) are attached
- **External Service Receives**: Downstream service can read headers and continue the trace

**Result**:
- ✅ Tracings propagated to external API
- ✅ Enables distributed tracing across services
- ✅ External service can correlate requests

---

## Flow 7: Custom Tracing Source

**Scenario**: Developer adds custom `X-User-Id` tracing.

### Configuration

```php
// config/laravel-tracing.php
'tracings' => [
    'user_id' => [
        'enabled' => true,
        'header' => 'X-User-Id',
        'source' => \App\Tracings\UserIdSource::class,
    ],
],
```

### Custom Source Implementation

```php
namespace App\Tracings;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use Illuminate\Http\Request;

class UserIdSource implements TracingSource
{
    public function resolve(Request $request): string
    {
        // Get authenticated user ID, or 'guest' if not authenticated
        return $request->user()?->id ?? 'guest';
    }

    public function headerName(): string
    {
        return 'X-User-Id';
    }

    public function restoreFromJob(string $value): string
    {
        return $value; // No transformation needed
    }
}
```

### Data Flow

```mermaid
sequenceDiagram
    participant Request
    participant Manager as TracingManager
    participant UserIdSource
    participant Controller
    participant Response

    Request->>Manager: resolveAll(request)
    Manager->>UserIdSource: resolve(request)
    UserIdSource->>Request: user()->id
    Request-->>UserIdSource: 42
    UserIdSource-->>Manager: "42"
    Manager->>Manager: Store: user_id = "42"

    Manager->>Controller: Continue request
    Controller->>Manager: LaravelTracing::get('user_id')
    Manager-->>Controller: "42"

    Controller->>Response: Return
    Response->>Response: Attach header: X-User-Id: 42
```

### Key Behavior

- **Custom Resolution Logic**: `UserIdSource` resolves user ID from authenticated user
- **Automatic Propagation**: User ID is automatically attached to responses, jobs, and HTTP calls
- **No Package Modification**: Registered via config, no changes to package code

**Result**:
- ✅ Custom tracing integrated seamlessly
- ✅ All standard tracing behaviors apply (response headers, job propagation, HTTP forwarding)

---

## Flow 8: Correlation ID Session Persistence

**Scenario**: Track correlation ID across multiple requests in the same user session.

### Multi-Request Flow

```mermaid
sequenceDiagram
    participant Browser
    participant Request1 as Request 1
    participant SessionStorage
    participant Response1
    participant Request2 as Request 2
    participant Response2

    Browser->>Request1: GET /login (new session)
    Request1->>Request1: Generate correlation ID: "abc-123"
    Request1->>SessionStorage: set('correlation_id', 'abc-123')
    Request1->>Response1: X-Correlation-Id: abc-123
    Response1->>Browser: Set session cookie

    Note over Browser: User navigates to another page

    Browser->>Request2: GET /dashboard (with session cookie)
    Request2->>SessionStorage: get('correlation_id')
    SessionStorage-->>Request2: "abc-123" (REUSED)
    Request2->>Response2: X-Correlation-Id: abc-123 (SAME as Request 1)
    Response2->>Browser: Response
```

### Key Behavior

- **Session-Scoped Correlation ID**: Same correlation ID for all requests in the user session
- **Cross-Request Continuity**: Correlation ID survives across page navigation, form submissions, AJAX calls
- **Session Storage**: Laravel's session driver (file, database, redis) stores the value
- **Request ID Still Unique**: Each request gets a new request ID, but correlation ID remains constant

**Use Case**: Track all activity for a single user session across multiple requests.

**Example Log Output**:
```
[2026-02-10 10:00:00] correlation_id=abc-123 request_id=xyz-001 User logged in
[2026-02-10 10:00:05] correlation_id=abc-123 request_id=xyz-002 User viewed dashboard
[2026-02-10 10:00:10] correlation_id=abc-123 request_id=xyz-003 User updated profile
```

All three requests share the same `correlation_id` but have unique `request_id` values.

---

## Flow 9: Package Disabled

**Scenario**: Tracing is globally disabled via configuration.

### Configuration

```php
// config/laravel-tracing.php or .env
'enabled' => false,
// or
LARAVEL_TRACING_ENABLED=false
```

### Data Flow

```mermaid
flowchart TD
    A[Incoming Request] --> B{Package Enabled?}
    B -->|No| C[Skip All Tracing Logic]
    C --> D[Execute Controller]
    D --> E[Return Response - No Headers]
    B -->|Yes| F[Normal Tracing Flow]
```

### Key Behavior

- **Middleware Skips Execution**: Middleware checks `config('laravel-tracing.enabled')` and exits early
- **No Headers Generated**: No tracing headers attached to responses
- **No Job Propagation**: Jobs are queued without tracing data
- **Global Accessor Returns Null**: `LaravelTracing::all()` returns empty array

**Use Case**: Disable tracing in local development or testing environments.

---

## Data Storage Summary

### Where Tracing Data Lives

| Storage Location | Scope | Data | Lifetime |
|------------------|-------|------|----------|
| **RequestStorage** | Current request | All resolved tracings | Request lifecycle |
| **SessionStorage** | User session | Correlation ID only | Session duration |
| **Job Payload** | Queued job | All tracings at dispatch time | Until job executes |
| **Response Headers** | HTTP response | All tracings | One-time (per response) |
| **HTTP Request Headers** | Outgoing API call | All tracings | One-time (per request) |

### Storage Flow Diagram

```mermaid
flowchart LR
    A[Incoming Request] --> B[Resolve Tracings]
    B --> C[RequestStorage - Temporary]
    B --> D[SessionStorage - Correlation ID Only]
    C --> E[Response Headers]
    C --> F[Job Payload]
    C --> G[HTTP Client Headers]
    D --> H[Next Request - Reuse Correlation ID]
```

---

## Performance Considerations

### Lazy Resolution

- **When**: Tracings are resolved only once per request (in `IncomingTracingMiddleware`)
- **Caching**: Resolved values are cached in `RequestStorage` for the request lifecycle
- **No Re-Resolution**: Accessing `LaravelTracing::all()` multiple times doesn't trigger re-resolution

### Minimal Overhead

- **ID Generation**: UUID generation is fast (~0.1ms)
- **Session Read/Write**: Single session read/write per request for correlation ID
- **Header Attachment**: Simple string operations (negligible overhead)

**Expected Overhead**: <1ms per request for built-in tracings.

---

## Error Handling

### Invalid External Headers

```mermaid
flowchart TD
    A[External Header Received] --> B{Sanitization Valid?}
    B -->|Yes| C[Use External Value]
    B -->|No| D[Reject Header]
    D --> E[Generate New ID]
```

**Behavior**: Invalid external headers are rejected and a new ID is generated instead.

### Job Serialization Failure

**Behavior**: If job payload cannot be serialized with tracings, Laravel's queue system will fail the job (standard Laravel behavior).

**Mitigation**: Tracings are serialized as simple strings, which are always serializable.

---

## Related Documentation

- **[COMPONENTS.md](COMPONENTS.md)** → Component responsibilities and relationships
- **[STRUCTURE.md](STRUCTURE.md)** → Directory structure and file organization
- **[CONFIGURATION.md](CONFIGURATION.md)** → Configuration options and architecture
- **[EXTENSIONS.md](EXTENSIONS.md)** → How to extend and customize flows
- **[../PRD.md](../PRD.md)** → Functional requirements these flows implement

---

## Maintenance Notes

**When adding new flows**:

1. ✅ Document the scenario and sequence diagram
2. ✅ Explain key behavior and decision points
3. ✅ Update storage summary if new storage is introduced
4. ✅ Add to "Related Documentation" if relevant

**Keep this document synchronized with actual implementation.**
