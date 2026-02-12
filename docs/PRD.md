# Product Requirements Document (PRD)

## 1. Overview

### 1.1 Product Name

**Laravel Tracing** (`jftecnologia/laravel-tracing`)

### 1.2 Problem Statement

In distributed and monolithic Laravel applications, tracking the origin and flow of requests across HTTP calls, queued jobs, and external API integrations is difficult. Without standardized tracing identifiers, developers cannot correlate log entries, debug cross-service issues, or trace a user session end-to-end.

There is no lightweight, plug-and-play Laravel package that:

- Automatically attaches tracing headers (correlation ID, request ID) to incoming and outgoing requests.
- Propagates tracing context through queued jobs.
- Allows custom tracing sources without requiring deep framework knowledge.
- Works out of the box with minimal configuration.

### 1.3 Objectives

- Provide automatic correlation ID and request ID tracing for every Laravel request.
- Propagate tracing context seamlessly into queued jobs.
- Enable forwarding and receiving tracing headers in external HTTP calls.
- Allow developers to add, replace, or extend tracing sources.
- Minimize required configuration after installation (service provider auto-discovery, manual middleware registration).
- Be fully configurable via config file and environment variables.

---

## 2. Stakeholders

- **Package Author**: Junior Fontenele (JF Tecnologia)
- **Maintainers**: Package contributors and maintainers
- **Consumers**: Laravel developers who install the package

---

## 3. Target Users

- **Laravel developers** building applications that need request tracing, log correlation, or distributed tracing across services.
- **DevOps/SRE teams** that rely on correlation IDs and request IDs to trace issues across services and logs.
- **Teams using microservices or service-oriented architectures** where requests flow through multiple Laravel applications.

---

## 4. Scope

### 4.1 In Scope

- Built-in tracing for correlation ID (`X-Correlation-Id`) and request ID (`X-Request-Id`).
- Correlation ID persistence across multiple requests of the same user session. The correlation ID represents the user session and must allow tracking all requests, jobs, and logs belonging to that session.
- Middleware that reads tracing headers from incoming requests or generates them if absent.
- Middleware that attaches tracing headers to outgoing responses.
- Session-level persistence of the correlation ID (e.g., via cookies or Laravel session) so it survives across multiple HTTP requests from the same user.
- Propagation of tracing context into queued jobs (jobs dispatched from a traced request must carry the tracing data).
- Retrieval of tracing context from within jobs during execution.
- Opt-in integration with Laravel's HTTP client to forward tracing headers on outgoing API calls.
- A global accessor to retrieve all current tracing values from anywhere in the application (e.g., for log context, job payloads).
- Configurable header names for built-in tracings via config file and environment variables.
- Ability to register custom tracing sources (e.g., `X-User-Id`, `X-App-Version`).
- Ability to replace or extend built-in tracing sources with custom implementations.
- Global enable/disable toggle via config and environment variable.
- Per-tracing enable/disable toggle via config and environment variable.
- Configuration file publishable via `php artisan vendor:publish`.
- Laravel 12 package auto-discovery for service provider (middleware requires manual registration in `bootstrap/app.php`).
- Comprehensive README with installation, configuration, and usage documentation.
- Test suite using PestPHP with Orchestra Testbench.

### 4.2 Out of Scope

- Distributed tracing backends (Jaeger, Zipkin, OpenTelemetry export).
- APM or performance monitoring features.
- Log formatting or log channel configuration (the package provides tracing data; log integration is the consumer's responsibility).
- Automatic log context integration (the package provides the accessor; pushing values to log context is the consumer's responsibility).
- UI or dashboard for viewing traces.
- Support for Laravel versions prior to 12.
- Support for PHP versions prior to 8.4.
- Database storage of tracing data.
- Authentication or authorization features related to tracing.
- Artisan command tracing (CLI-initiated operations). HTTP requests only for v1.

---

## 5. Functional Requirements

- **FR-01**: The package must automatically register its service provider via Laravel's package auto-discovery. Middleware must be manually registered by the developer in `bootstrap/app.php` as Laravel 12 does not support automatic middleware registration through package discovery.

- **FR-02**: On every incoming HTTP request, the package must resolve the correlation ID using the following priority: (1) if accepting external headers is enabled, read from the configured request header (e.g., forwarded by an external service), (2) read from the user's session (persisted from a previous request), (3) generate a new unique correlation ID. The resolved correlation ID must be persisted in the user's session so it remains consistent across all requests within the same session.

- **FR-03**: On every incoming HTTP request, the package must resolve the request ID as follows: (1) if accepting external headers is enabled, read from the configured request header, (2) if the header is not present or external headers are disabled, generate a new unique request ID.

- **FR-04**: The package must attach the correlation ID and request ID (and all active tracing values) as headers on the HTTP response.

- **FR-05**: Tracing values must be accessible from anywhere in the application during the request lifecycle via a global accessor (e.g., facade, helper, or container resolution).

- **FR-06**: When a queued job is dispatched during a traced request, all current tracing values must be serialized and stored with the job payload.

- **FR-07**: When a queued job executes, the tracing values from the dispatching request must be restored and accessible via the same global accessor. The original request ID from the dispatching request must be preserved (not regenerated).

- **FR-08**: The package must provide an opt-in mechanism to attach all current tracing values as headers when making outgoing HTTP requests via Laravel's HTTP client. This integration must be explicitly enabled by the developer (globally via config or per-request). It must not be active by default.

- **FR-09**: The header name for each built-in tracing (correlation ID, request ID) must be configurable via the config file and overridable via environment variables.

- **FR-10**: The package must allow registering additional custom tracing sources (e.g., `X-User-Id`, `X-App-Version`) via the config file.

- **FR-11**: The package must allow replacing any built-in tracing source with a custom implementation via the config file.

- **FR-12**: The package must provide a global enable/disable toggle. When disabled, no tracing headers are read, generated, or attached.

- **FR-13**: The package must provide a per-tracing enable/disable toggle. Individual tracings can be disabled without disabling the entire package.

- **FR-14**: The config file must be publishable via `php artisan vendor:publish --tag=laravel-tracing-config`.

- **FR-15**: Each tracing value generated by the package (correlation ID, request ID) must be unique. UUID or equivalent uniqueness guarantees are expected.

- **FR-16**: When accepting external headers is enabled (see FR-19) and an incoming request provides a tracing header value (e.g., `X-Correlation-Id`), the package must use that value internally rather than generating a new one. This enables cross-service tracing propagation. When accepting external headers is disabled, incoming header values must be ignored.

- **FR-17**: Custom tracing sources must follow the same contract as built-in tracings (readable from request headers, restorable in jobs, accessible globally, attachable to outgoing requests and responses).

- **FR-18**: The package must include a comprehensive `README.md` documenting: installation, configuration, basic usage, custom tracing sources, replacing built-in tracings, job integration, HTTP client integration, and available configuration options.

- **FR-19**: The package must provide a configurable toggle (via config file and environment variable) to enable or disable accepting tracing values from incoming external request headers. When disabled, the package must ignore all externally provided tracing headers and always resolve values internally (from session or by generating new ones). This setting must apply globally to all tracings (built-in and custom). It must be enabled by default to support cross-service propagation out of the box.

---

## 6. Non-Functional Requirements

- **Performance**: The package must add negligible overhead to request processing. Tracing operations (read header, generate ID, store in context) must complete in under 1ms for built-in tracings under normal conditions.

- **Usability**: The package must work immediately after `composer require` with sensible defaults. No mandatory configuration, migrations, or artisan commands required. The README must allow a developer to integrate the package in under 5 minutes for basic usage.

- **Scalability**: The package must support an arbitrary number of custom tracing sources without degrading performance. Adding a new tracing source must not require modifying package internals.

- **Security**: Tracing header values received from external requests must be treated as untrusted input. Values must be sanitized or validated (e.g., length limits, character restrictions) to prevent header injection or log injection attacks.

- **Compatibility**: The package must be compatible with Laravel 12 and PHP 8.4+. It must not conflict with common Laravel packages (Horizon, Telescope, Sanctum, etc.).

---

## 7. User Flows (High-Level)

### Flow 1: Basic Installation and Setup

1. Developer runs `composer require jftecnologia/laravel-tracing`.
2. Laravel auto-discovers the service provider.
3. Developer manually registers the tracing middleware in `bootstrap/app.php` (required for Laravel 12).
4. On the first HTTP request from a user, the package generates a correlation ID and a request ID.
5. The correlation ID is persisted in the user's session.
6. Both IDs are attached to the response headers.
7. Both IDs are available via the global accessor throughout the request lifecycle.
8. On subsequent requests from the same session, the same correlation ID is reused; a new request ID is generated for each request.

### Flow 2: Receiving Tracing Headers from External Service

1. An external service sends a request with `X-Correlation-Id: abc-123` header.
2. The package reads and uses `abc-123` as the correlation ID instead of generating a new one.
3. The package generates a new request ID (since this is a new request).
4. Both values are available via the global accessor and attached to the response.

### Flow 3: Propagating Tracing to a Queued Job

1. During a traced request, the developer dispatches a queued job.
2. The package automatically attaches all current tracing values to the job payload.
3. When the job executes (possibly on a different server/process), the tracing values are restored.
4. Inside the job, the developer accesses tracing values via the global accessor (e.g., to enrich log context).

### Flow 4: Forwarding Tracing Headers to an External API (Opt-in)

1. Developer enables HTTP client tracing integration via the config file or per-request.
2. During a traced request, the developer makes an outgoing HTTP call via Laravel's HTTP client.
3. The package attaches all current tracing headers to the outgoing request.
4. The external service receives the tracing headers and can use them for its own tracing.

### Flow 5: Adding a Custom Tracing Source

1. Developer publishes the config file.
2. Developer registers a custom tracing source (e.g., `X-User-Id`) in the config, specifying the header name and the class responsible for resolving the value.
3. On subsequent requests, the custom tracing value is read from headers (if present), accessible globally, attached to responses, propagated to jobs, and forwarded to outgoing HTTP calls.

### Flow 6: Replacing a Built-in Tracing Source

1. Developer creates a custom class that follows the tracing contract.
2. Developer updates the config to replace the built-in correlation ID tracing with their custom implementation.
3. The package uses the custom class for correlation ID resolution instead of the default.

### Flow 7: Disabling Tracing

1. Developer sets `LARAVEL_TRACING_ENABLED=false` in the `.env` file.
2. The package does not read, generate, or attach any tracing headers.
3. The global accessor returns null/empty values.

---

## 8. Assumptions & Constraints

### 8.1 Assumptions

- The package skeleton (service provider, facade, config, testbench setup) is already in place.
- Laravel 12 package auto-discovery is functional for service providers (registered in `composer.json` `extra.laravel.providers`).
- Middleware registration in Laravel 12 requires manual setup in `bootstrap/app.php`.
- Developers using this package are familiar with Laravel conventions (config files, middleware, facades, queued jobs).
- The host application uses Laravel's built-in HTTP client (`Illuminate\Support\Facades\Http`) for outgoing requests.
- Queued jobs use Laravel's built-in queue system.

### 8.2 Constraints

- Must target Laravel 12 and PHP 8.4+ exclusively.
- Must use PestPHP for all tests.
- Must use Orchestra Testbench for integration testing.
- Must not introduce external dependencies beyond `illuminate/support` and `illuminate/contracts`.
- Must not require database migrations or additional infrastructure.
- Must follow the project's coding standards (Pint, Rector, Larastan as configured).

---

## 9. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Header injection via malicious tracing values | Security vulnerability (log injection, response splitting) | Validate and sanitize all incoming tracing header values; enforce length limits and character restrictions |
| Job serialization fails with custom tracing sources | Jobs fail to dispatch or execute | Ensure tracing data is serialized as simple key-value pairs (strings only); document the contract clearly |
| Middleware ordering conflicts with other packages | Tracing headers missing or incorrect | Document recommended middleware priority; use Laravel's middleware priority system if applicable |
| Performance degradation with many custom tracings | Slower request processing | Lazy-load tracing resolution; benchmark with 10+ custom tracings in test suite |
| Session dependency for correlation ID persistence | Stateless APIs (token-based, no session) won't have session persistence | Fall back to header-based propagation when no session is available; document this behavior clearly |
| Breaking changes in Laravel 12 queue system | Job propagation fails | Pin tests to Laravel 12; monitor Laravel release notes for queue changes |

---

## 10. Success Metrics

- **Simple setup**: After `composer require` and middleware registration in `bootstrap/app.php`, the package attaches `X-Correlation-Id` and `X-Request-Id` headers to responses.
- **Session persistence**: The same correlation ID is returned across multiple requests from the same user session.
- **Test coverage**: All functional requirements (FR-01 through FR-19) have corresponding passing tests.
- **Documentation completeness**: The README covers all user flows described in section 7.
- **Extensibility**: A custom tracing source can be added by editing the config file only (no package code changes).
- **Replaceability**: A built-in tracing source can be replaced by editing the config file only.
- **Job propagation**: Tracing values dispatched in a request context are accessible inside the job handler (with original request ID preserved).

---

## 11. Open Questions

All initial open questions have been resolved:

- **OQ-01** (Resolved): The correlation ID persists across multiple requests of the same user session via session storage. It represents the user session.
- **OQ-02** (Resolved): The package provides only the accessor. Log context integration is the consumer's responsibility.
- **OQ-03** (Resolved): HTTP client integration is opt-in. Not active by default.
- **OQ-04** (Resolved): The original request ID from the dispatching request is preserved in jobs.
- **OQ-05** (Resolved): HTTP-only for v1. Artisan command tracing is out of scope.

No open questions remain at this time.
