# Product Requirements Document (PRD)

## 1. Overview

### 1.1 Product Name

Laravel Tracing

### 1.2 Problem Statement

In microservice and multi-application architectures, tracking a single user action across multiple systems is difficult. When a request enters Service A, spawns HTTP calls to Services B and C, and dispatches queued jobs, there is no built-in mechanism in Laravel to:

- Correlate all these operations back to the originating request
- Propagate tracing identifiers across HTTP boundaries (incoming and outgoing)
- Maintain session-level correlation across multiple requests from the same user
- Attach tracing headers to outgoing HTTP client calls automatically

While Laravel's native `Context` facade (since v11) provides request-scoped data storage and job propagation, it does **not** handle cross-application boundary concerns: reading tracing IDs from incoming HTTP headers, attaching them to outgoing HTTP responses, or propagating them via the HTTP client to downstream services.

### 1.3 Objectives

- Provide automatic, zero-configuration distributed tracing for Laravel applications
- Propagate correlation IDs and request IDs across HTTP requests, queued jobs, and outgoing HTTP client calls
- Enable end-to-end request tracking across service boundaries
- Support custom tracing sources via a pluggable contract
- Allow granular enable/disable control per tracing source

---

## 2. Stakeholders

- **Package author**: Junior Fontenele (JF Tecnologia)
- **Primary consumers**: Laravel developers building multi-service or multi-application systems
- **Secondary consumers**: DevOps/SRE teams consuming tracing headers for observability

---

## 3. Target Users

- **Laravel application developers** who need to trace requests across multiple services
- **Teams operating multiple Laravel applications** that communicate via HTTP APIs or shared queues
- **Developers integrating with API gateways** or reverse proxies that inject tracing headers (e.g., `X-Correlation-Id`, `X-Request-Id`)

---

## 4. Scope

### 4.1 In Scope

- Resolve tracing values from incoming HTTP request headers
- Generate tracing values (UUID) when no external header is provided
- Persist correlation IDs across requests within the same session
- Attach tracing values as response headers to outgoing HTTP responses
- Propagate tracing values to queued job payloads and restore them during job execution
- Attach tracing headers to outgoing HTTP client requests (per-request macro and global middleware)
- Provide a pluggable `TracingSource` contract for custom tracing values
- Provide a pluggable `TracingStorage` contract for custom storage backends
- Configuration-driven enable/disable at global and per-source levels
- Header name customization via configuration
- Security: sanitize incoming header values to prevent injection

### 4.2 Out of Scope

- OpenTelemetry or OpenTracing protocol compliance
- Span/trace hierarchy management (parent-child relationships between spans)
- Log integration (consumers use Laravel's native `Context` or `Log::withContext` for that)
- Metrics collection or performance monitoring
- UI or dashboard for visualizing traces
- Database storage of trace data
- Distributed tracing across non-Laravel systems (the package targets Laravel-to-Laravel communication)

---

## 5. Functional Requirements

- **FR-01**: The package MUST resolve tracing values from incoming HTTP request headers when the `IncomingTracingMiddleware` is registered.
- **FR-02**: The package MUST generate a UUID v4 for each tracing source when no external header value is provided.
- **FR-03**: The package MUST persist correlation IDs in the session so that subsequent requests from the same session reuse the same correlation ID.
- **FR-04**: The package MUST NOT persist request IDs in the session — each HTTP request receives a fresh request ID.
- **FR-05**: The package MUST attach all enabled tracing values as response headers when the `OutgoingTracingMiddleware` is registered.
- **FR-06**: The package MUST inject tracing values into queued job payloads via `Queue::createPayloadUsing()`.
- **FR-07**: The package MUST restore tracing values from job payloads when the `JobProcessing` event fires.
- **FR-08**: The package MUST provide an `Http::withTracing()` macro to attach tracing headers to individual outgoing HTTP client requests.
- **FR-09**: The package MUST support a global HTTP client middleware mode (opt-in) to attach tracing headers to all outgoing HTTP requests.
- **FR-10**: The package MUST support custom tracing sources via the `TracingSource` contract, registerable via configuration or `TracingManager::extend()` at runtime.
- **FR-11**: The package MUST allow per-source enable/disable via the `tracings.<key>.enabled` config value.
- **FR-12**: The package MUST allow global enable/disable via the `laravel-tracing.enabled` config value. When disabled, all tracing operations are completely skipped (zero overhead).
- **FR-13**: The package MUST sanitize incoming header values to prevent header injection attacks.
- **FR-14**: The package MUST accept external tracing headers only when `accept_external_headers` is explicitly enabled in configuration (default: off).
- **FR-15**: The package MUST allow customization of HTTP header names per tracing source via configuration.

---

## 6. Non-Functional Requirements

- **Performance**: When disabled (`enabled = false`), the package MUST add zero overhead. When enabled, tracing resolution must not measurably impact request latency (< 1ms).
- **Security**: External header values MUST be sanitized before use. The `accept_external_headers` option MUST default to `false` to prevent tracing ID spoofing in untrusted environments.
- **Compatibility**: The package MUST support PHP 8.4+ and Laravel 12+ (illuminate components).
- **Testability**: All core components (TracingManager, Sources, Middleware, JobDispatcher) MUST be testable in isolation using Orchestra Testbench.
- **Extensibility**: New tracing sources MUST be addable via configuration alone (no code changes to the package).

---

## 7. User Flows (High-Level)

### Flow 1: Standard Request Tracing

1. HTTP request arrives at the Laravel application
2. `IncomingTracingMiddleware` executes and calls `TracingManager::resolveAll()`
3. Each registered `TracingSource` resolves its value (from header, session, or generation)
4. Resolved values are stored in `RequestStorage`
5. Application processes the request; tracing values are accessible via `LaravelTracing` facade
6. `OutgoingTracingMiddleware` attaches all resolved values as response headers
7. Response is returned to the client with tracing headers

### Flow 2: Job Propagation

1. During request processing, a queued job is dispatched
2. `Queue::createPayloadUsing()` hook injects current tracing values into the job payload
3. Job is serialized and sent to the queue
4. Worker picks up the job; `JobProcessing` event fires
5. `TracingJobDispatcher` reads tracing values from the payload and calls `TracingManager::restore()`
6. Tracing values are available in the job context via `LaravelTracing` facade

### Flow 3: HTTP Client Propagation

1. Application makes an outgoing HTTP request using Laravel's HTTP client
2. Developer calls `Http::withTracing()->get(...)` (per-request) or has global mode enabled
3. `HttpClientTracing::attachTracings()` reads all current tracing values
4. Tracing values are attached as headers to the outgoing request
5. Downstream service receives the tracing headers

### Flow 4: Cross-Service Correlation

1. Service A receives a request and resolves a correlation ID (generated or from upstream)
2. Service A dispatches an HTTP call to Service B with `Http::withTracing()`
3. Service B's `IncomingTracingMiddleware` reads the correlation ID from the incoming header
4. Service B processes the request with the same correlation ID
5. Both services can correlate logs and operations via the shared correlation ID

---

## 8. Assumptions & Constraints

### 8.1 Assumptions

- Consuming applications use Laravel 12+ with the illuminate/queue component
- HTTP communication between services uses Laravel's HTTP client (`Http` facade)
- Session is available for correlation ID persistence in web requests
- Queue workers are configured to fire standard Laravel queue events

### 8.2 Constraints

- The package depends only on `illuminate/support`, `illuminate/contracts`, and `illuminate/queue` — no additional runtime dependencies
- The package is a Laravel-specific solution — it does not target non-Laravel PHP frameworks
- Session storage for correlation IDs requires a session-capable request (not available in CLI or queue contexts)

---

## 9. Risks & Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Overlap with Laravel Context facade | Users may question value proposition | Document clear differentiation: Context handles internal propagation; this package handles cross-service boundary concerns (headers, HTTP client) |
| Header injection via external tracing values | Security vulnerability | HeaderSanitizer validates and sanitizes all incoming values; `accept_external_headers` defaults to false |
| Session unavailability in queue/CLI contexts | Correlation ID session persistence fails silently | SessionStorage handles missing session gracefully; correlation IDs fall back to generation |
| Performance impact on high-traffic applications | Latency increase | UUID generation is fast (< 0.1ms); package can be fully disabled via config |

---

## 10. Success Metrics

- Package can be installed and configured in under 5 minutes (publish config + register middleware)
- All tracing values propagate correctly across HTTP → Job → HTTP Client boundaries
- Zero overhead when globally disabled
- Custom tracing sources can be added via config alone, without modifying package code
- Test suite passes with 100% coverage of core components

---

## 11. Open Questions (Resolved)

- **OQ-01**: ~~Should the package integrate with Laravel's native `Context` facade as an additional storage backend?~~ → **Deferred to future version.** Not in current scope.
- **OQ-02**: ~~Should the package support OpenTelemetry-compatible header formats?~~ → **No.** Not planned at this time.
- **OQ-03**: ~~Should there be a built-in Artisan command or health check?~~ → **No.** Not planned at this time.
- **OQ-04**: ~~Should the README document patterns for combining with Laravel Context?~~ → **Yes.** README should include guidance on using this package alongside Laravel Context for log enrichment.
