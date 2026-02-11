# Middleware Integration

**Epic ID**: MI
**Epic Goal**: Implement middleware to resolve tracing values from incoming requests and attach them to outgoing responses.

**Requirements Addressed**:
- FR-02: Resolve correlation ID from external header, session, or generate new
- FR-03: Resolve request ID from external header or generate new
- FR-04: Attach tracing headers to HTTP responses
- FR-16: Accept external tracing headers
- FR-19: Toggle external header acceptance

**Architecture Areas**:
- IncomingTracingMiddleware (resolve tracings from request)
- OutgoingTracingMiddleware (attach tracings to response)
- Middleware priority and execution order

---

## Tasks

### MI-01 Implement IncomingTracingMiddleware

**Description**:
Implement the middleware that executes early in the request lifecycle to resolve all tracing values. This middleware is the entry point for the entire tracing system: it triggers `TracingManager::resolveAll()` which delegates to all registered tracing sources (correlation ID, request ID, custom tracings) to resolve their values.

The middleware must check if the package is enabled globally before executing. If disabled, it should pass through immediately without any tracing logic. If enabled, it resolves all tracings and stores them in `RequestStorage` for access throughout the request lifecycle.

**Acceptance Criteria**:
- [ ] AC-1: Implements Laravel's middleware contract (`handle(Request, Closure)`)
- [ ] AC-2: Constructor accepts `TracingManager` instance (injected from container)
- [ ] AC-3: Checks `config('laravel-tracing.enabled')` before execution (early return if disabled)
- [ ] AC-4: Calls `TracingManager::resolveAll($request)` to resolve all tracing values
- [ ] AC-5: Executes before controllers (early in middleware stack)
- [ ] AC-6: Passes request to next middleware after resolution
- [ ] AC-7: Does not modify request or response (read-only operation)

**Technical Notes**:
- Middleware priority: should run early to ensure tracings are available throughout request
- Global enable check prevents unnecessary work when disabled
- Resolution is delegated to manager (middleware is just a trigger)

**Files to Create/Modify**:
- `src/Middleware/IncomingTracingMiddleware.php`

**Testing**:
- [ ] Unit test: middleware calls manager.resolveAll()
- [ ] Unit test: middleware checks global enabled toggle
- [ ] Unit test: middleware passes request to next middleware
- [ ] Feature test: tracings are available after middleware execution
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### MI-02 Implement OutgoingTracingMiddleware

**Description**:
Implement the middleware that executes late in the request lifecycle to attach all tracing values as response headers. This middleware reads all resolved tracings from `TracingManager` and attaches them to the outgoing HTTP response using the configured header names.

The middleware must respect the global enable toggle and per-tracing enable toggles. Only enabled tracings should be attached to the response. The middleware should execute after controllers but before the response is sent.

**Acceptance Criteria**:
- [ ] AC-1: Implements Laravel's middleware contract (`handle(Request, Closure)`)
- [ ] AC-2: Constructor accepts `TracingManager` instance (injected from container)
- [ ] AC-3: Checks `config('laravel-tracing.enabled')` before execution (early return if disabled)
- [ ] AC-4: Calls `TracingManager::all()` to get all resolved tracing values
- [ ] AC-5: Attaches each tracing as a response header using the source's `headerName()`
- [ ] AC-6: Executes after controllers (late in middleware stack)
- [ ] AC-7: Does not modify request (response-only operation)

**Technical Notes**:
- Middleware priority: should run late to ensure tracings are attached just before response
- Header names come from tracing sources (`headerName()` method)
- Manager returns only enabled tracings (disabled tracings are filtered out)

**Files to Create/Modify**:
- `src/Middleware/OutgoingTracingMiddleware.php`

**Testing**:
- [ ] Unit test: middleware calls manager.all()
- [ ] Unit test: middleware checks global enabled toggle
- [ ] Unit test: middleware attaches headers to response
- [ ] Feature test: response contains tracing headers
- [ ] Feature test: disabled tracings are not attached
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### MI-03 Register middleware in service provider

**Description**:
Register both incoming and outgoing middleware in Laravel's HTTP kernel middleware stack. The middleware should be registered automatically via the service provider so developers don't need to manually add them to the kernel.

Use Laravel's middleware priority system to ensure correct execution order: `IncomingTracingMiddleware` should run early, and `OutgoingTracingMiddleware` should run late. The middleware should be registered globally to apply to all HTTP requests.

**Acceptance Criteria**:
- [ ] AC-1: Register `IncomingTracingMiddleware` in global middleware stack
- [ ] AC-2: Register `OutgoingTracingMiddleware` in global middleware stack
- [ ] AC-3: Set middleware priority: IncomingTracingMiddleware runs early (after session middleware)
- [ ] AC-4: Set middleware priority: OutgoingTracingMiddleware runs late (before response is sent)
- [ ] AC-5: Middleware is registered in `LaravelTracingServiceProvider::boot()`
- [ ] AC-6: Middleware registration respects global enable toggle (skip if disabled)

**Technical Notes**:
- Use `app()->middleware()` or `$kernel->pushMiddleware()` for registration
- Middleware priority ensures correct execution order
- Global registration applies middleware to all HTTP requests

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (update boot() method)

**Testing**:
- [ ] Feature test: middleware is registered and executed on HTTP requests
- [ ] Feature test: middleware execution order is correct (incoming before outgoing)
- [ ] Feature test: middleware is not registered when package is disabled
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### MI-04 Implement external header acceptance

**Description**:
Implement the logic for accepting or rejecting external tracing headers based on the `accept_external_headers` configuration. This setting controls whether the package trusts incoming headers from external services or always generates its own values.

When enabled (default), tracing sources should read external headers and use them if valid (after sanitization). When disabled, tracing sources should ignore all external headers and always generate new values or use session storage.

**Acceptance Criteria**:
- [ ] AC-1: `CorrelationIdSource` checks `config('laravel-tracing.accept_external_headers')` before reading external header
- [ ] AC-2: `RequestIdSource` checks `config('laravel-tracing.accept_external_headers')` before reading external header
- [ ] AC-3: When disabled, external headers are ignored (sources skip to next priority: session or generation)
- [ ] AC-4: When enabled, external headers are sanitized using `HeaderSanitizer` before use
- [ ] AC-5: Invalid external headers are rejected (sanitizer returns null)
- [ ] AC-6: Setting is configurable via `LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS` environment variable

**Technical Notes**:
- Security: external headers are untrusted input and must be sanitized
- Default: enabled (true) to support cross-service tracing out of the box
- When disabled: package always generates its own IDs (isolated from external services)

**Files to Create/Modify**:
- `src/Tracings/Sources/CorrelationIdSource.php` (update resolve() method)
- `src/Tracings/Sources/RequestIdSource.php` (update resolve() method)
- `config/laravel-tracing.php` (ensure accept_external_headers setting exists)

**Testing**:
- [ ] Unit test: CorrelationIdSource with accept_external_headers enabled (uses external header)
- [ ] Unit test: CorrelationIdSource with accept_external_headers disabled (ignores external header)
- [ ] Unit test: RequestIdSource with accept_external_headers enabled (uses external header)
- [ ] Unit test: RequestIdSource with accept_external_headers disabled (ignores external header)
- [ ] Feature test: external headers are accepted when enabled
- [ ] Feature test: external headers are rejected when disabled
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### MI-05 Write middleware integration tests

**Description**:
Create comprehensive integration tests for the entire middleware lifecycle. Test the full flow from incoming request to outgoing response, verifying that tracings are resolved, stored, accessible, and attached correctly. Test both happy paths and edge cases (disabled package, disabled tracings, external headers).

Use Orchestra Testbench to simulate full HTTP requests. Assert that response headers contain expected tracing values. Test middleware execution order and integration with other middleware.

**Acceptance Criteria**:
- [ ] AC-1: Test full request lifecycle: incoming middleware → controller → outgoing middleware
- [ ] AC-2: Test response contains tracing headers (X-Correlation-Id, X-Request-Id)
- [ ] AC-3: Test tracings are accessible via `LaravelTracing` facade during request
- [ ] AC-4: Test package disabled (no middleware execution, no headers)
- [ ] AC-5: Test per-tracing disabled (correlation ID disabled, request ID enabled)
- [ ] AC-6: Test external headers accepted (request with headers → same headers in response)
- [ ] AC-7: Test external headers rejected (accept_external_headers = false)
- [ ] AC-8: Test middleware execution order (incoming before controller, outgoing after)

**Technical Notes**:
- Use Testbench's HTTP testing: `$this->get('/test')`
- Assert response headers: `$response->assertHeader('X-Correlation-Id')`
- Test middleware priority by checking execution order

**Files to Create/Modify**:
- `tests/Feature/MiddlewareIntegrationTest.php`
- `tests/Feature/ExternalHeaderAcceptanceTest.php`

**Testing**:
- [ ] All middleware integration tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 5
**Estimated Complexity**: Medium
**Dependencies**: CTI (TracingManager), CFG (configuration), SP (CorrelationIdSource), RIM (RequestIdSource)

**Critical Path**:
- MI-01 depends on CTI-04 (uses TracingManager)
- MI-02 depends on CTI-04 (uses TracingManager)
- MI-03 depends on MI-01 and MI-02 (registers both middleware)
- MI-04 depends on CTI-02 (HeaderSanitizer), SP-02 (CorrelationIdSource), RIM-01 (RequestIdSource)
- MI-05 depends on MI-01 through MI-04 (tests all middleware features)
