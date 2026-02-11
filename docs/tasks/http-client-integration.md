# HTTP Client Integration

**Epic ID**: HCI
**Epic Goal**: Enable opt-in propagation of tracing headers to outgoing HTTP requests made via Laravel's HTTP client.

**Requirements Addressed**:
- FR-08: Opt-in mechanism to attach tracing headers to outgoing HTTP requests

**Architecture Areas**:
- HttpClientTracing (HTTP macro)
- HTTP client macro registration
- Global vs per-request configuration

---

## Tasks

### HCI-01 Implement HttpClientTracing

**Description**:
Implement the component that attaches tracing headers to outgoing HTTP requests made via Laravel's HTTP client. This component registers a macro on Laravel's `Http` facade, providing a `withTracing()` method that reads all current tracing values and attaches them as headers to the outgoing request.

The integration must be opt-in: developers must explicitly call `Http::withTracing()` or enable global tracing via config. This prevents accidental leakage of tracing headers to external services that don't expect them.

**Acceptance Criteria**:
- [ ] AC-1: Constructor accepts `TracingManager` instance (injected from container)
- [ ] AC-2: `attachTracings(PendingRequest $request)` method reads all tracings from manager
- [ ] AC-3: Each tracing is attached as a header using the source's `headerName()`
- [ ] AC-4: Returns the modified `PendingRequest` instance for chaining
- [ ] AC-5: Only enabled tracings are attached (respects per-tracing toggles)
- [ ] AC-6: Headers are attached using `$request->withHeaders([...])`

**Technical Notes**:
- Works with Laravel's `PendingRequest` class
- Header names come from tracing sources (`headerName()` method)
- Manager returns only enabled tracings (disabled tracings are filtered out)

**Files to Create/Modify**:
- `src/Http/HttpClientTracing.php`

**Testing**:
- [ ] Unit test: attachTracings() reads all tracings from manager
- [ ] Unit test: headers are attached to pending request
- [ ] Unit test: disabled tracings are not attached
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### HCI-02 Register HTTP client macro

**Description**:
Register the `withTracing()` macro on Laravel's `Http` facade. The macro should be registered in the service provider and call the `HttpClientTracing::attachTracings()` method. The macro must be registered globally so it's available throughout the application.

The macro should respect the global enable toggle (skip registration if package is disabled). The registration should happen in the service provider's `boot()` method.

**Acceptance Criteria**:
- [ ] AC-1: Register macro on `Http` facade using `Http::macro('withTracing', ...)`
- [ ] AC-2: Macro resolves `HttpClientTracing` from container
- [ ] AC-3: Macro calls `attachTracings($this)` and returns result
- [ ] AC-4: Macro is registered in `LaravelTracingServiceProvider::boot()`
- [ ] AC-5: Macro is not registered when package is disabled
- [ ] AC-6: Macro is chainable: `Http::withTracing()->get(...)`

**Technical Notes**:
- Macro registration: `Http::macro('withTracing', function () { ... })`
- Resolve `HttpClientTracing` from container inside macro closure
- `$this` inside macro closure refers to `PendingRequest` instance

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (update boot() method)

**Testing**:
- [ ] Feature test: withTracing() macro is available on Http facade
- [ ] Feature test: macro attaches tracing headers to request
- [ ] Feature test: macro is not registered when package is disabled
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### HCI-03 Implement global vs per-request config

**Description**:
Implement support for both global and per-request HTTP client tracing. Global mode (opt-in via config) attaches tracings to all outgoing requests automatically. Per-request mode (default) requires explicit `withTracing()` call for each request.

When global mode is enabled via `config('laravel-tracing.http_client.enabled')`, register a global HTTP middleware that attaches tracings to every outgoing request. When disabled (default), tracings are only attached when `withTracing()` is called.

**Acceptance Criteria**:
- [ ] AC-1: Config key `http_client.enabled` controls global tracing (default: false)
- [ ] AC-2: When enabled, all outgoing requests include tracing headers automatically
- [ ] AC-3: When disabled, tracing headers are only attached via `withTracing()` call
- [ ] AC-4: Global mode uses Laravel's HTTP client global middleware
- [ ] AC-5: Environment variable `LARAVEL_TRACING_HTTP_CLIENT_ENABLED` overrides config
- [ ] AC-6: Per-request mode (withTracing()) works regardless of global setting

**Technical Notes**:
- Global mode: `Http::globalMiddleware()` or similar mechanism
- Global mode attaches tracings to every request (use carefully)
- Per-request mode is safer (opt-in per call)

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (register global middleware if enabled)
- `config/laravel-tracing.php` (ensure http_client.enabled setting exists)

**Testing**:
- [ ] Feature test: global mode enabled (all requests have tracings)
- [ ] Feature test: global mode disabled (only withTracing() requests have tracings)
- [ ] Feature test: LARAVEL_TRACING_HTTP_CLIENT_ENABLED=true enables global mode
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### HCI-04 Write HTTP client integration tests

**Description**:
Create comprehensive tests for HTTP client integration. Test both per-request mode (`withTracing()`) and global mode (automatic attachment). Verify that tracing headers are attached correctly, that disabled tracings are excluded, and that the integration works with Laravel's HTTP client features (retries, middleware, etc.).

Use Laravel's HTTP client testing helpers to fake outgoing requests and assert headers. Test multiple scenarios: single request, multiple requests, chained methods, global mode, per-request mode.

**Acceptance Criteria**:
- [ ] AC-1: Test withTracing() attaches all tracing headers to request
- [ ] AC-2: Test global mode attaches tracings to all requests
- [ ] AC-3: Test disabled tracings are not attached
- [ ] AC-4: Test package disabled (no tracings attached even with withTracing())
- [ ] AC-5: Test withTracing() is chainable with other Http methods
- [ ] AC-6: Test custom tracings are attached to outgoing requests
- [ ] AC-7: Test multiple outgoing requests each receive current tracings

**Technical Notes**:
- Use `Http::fake()` to intercept outgoing requests
- Assert headers using `Http::assertSent()` callback
- Test chaining: `Http::withTracing()->retry(3)->get(...)`

**Files to Create/Modify**:
- `tests/Feature/HttpClientIntegrationTest.php`

**Testing**:
- [ ] All HTTP client integration tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 4
**Estimated Complexity**: Low
**Dependencies**: SPB (service provider with TracingManager registered)

**Critical Path**:
- HCI-01 depends on CTI-04 (uses TracingManager)
- HCI-02 depends on HCI-01 (registers macro that uses HttpClientTracing) and SPB-01 (provider exists)
- HCI-03 depends on HCI-01 (implements global mode using same logic) and CFG-01 (config setting)
- HCI-04 depends on HCI-01 through HCI-03 (tests all HTTP client features)
