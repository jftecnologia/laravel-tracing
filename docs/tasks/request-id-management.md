# Request ID Management

**Epic ID**: RIM
**Epic Goal**: Implement request-scoped ID generation and propagation for individual request tracking.

**Requirements Addressed**:
- FR-03: Request ID resolution (external header or generate new)
- FR-07: Original request ID preserved in queued jobs
- FR-15: Unique IDs (UUID generation)

**Architecture Areas**:
- RequestIdSource (request-scoped tracing source)
- Request ID generation and propagation

---

## Tasks

### RIM-01 Implement RequestIdSource

**Description**:
Implement the built-in tracing source for request IDs. Unlike correlation IDs which are session-scoped, request IDs are request-scoped and unique to each HTTP request. The source follows a simple resolution priority: (1) external header (if enabled), (2) generate new UUID.

Request IDs are not persisted in session storage. Each request gets a fresh request ID. However, when propagated to queued jobs, the original request ID is preserved (not regenerated in the job context). This allows tracing a request and all jobs it dispatched.

**Acceptance Criteria**:
- [ ] AC-1: Implements `TracingSource` contract
- [ ] AC-2: Constructor accepts `IdGenerator` and `HeaderSanitizer` dependencies
- [ ] AC-3: `resolve(Request)` follows priority: (1) external header (if `accept_external_headers` enabled), (2) generate new UUID
- [ ] AC-4: External header values are sanitized using `HeaderSanitizer`
- [ ] AC-5: Does NOT persist in session storage (request-scoped only)
- [ ] AC-6: `headerName()` returns configured header name (default: 'X-Request-Id')
- [ ] AC-7: `restoreFromJob(string)` returns value unchanged (preserves original request ID)
- [ ] AC-8: Generates new request ID for each HTTP request
- [ ] AC-9: Preserves original request ID when restored from job payload

**Technical Notes**:
- Header name is configurable via `config('laravel-tracing.tracings.request_id.header')`
- External header acceptance is controlled by `config('laravel-tracing.accept_external_headers')`
- Request ID is NOT stored in session (differs from correlation ID)
- Job propagation preserves original request ID (see FR-07)

**Files to Create/Modify**:
- `src/Tracings/Sources/RequestIdSource.php`

**Testing**:
- [ ] Unit test: resolve() with no external header (generates new UUID)
- [ ] Unit test: resolve() with external header enabled (uses external value)
- [ ] Unit test: resolve() with external header disabled (ignores external value, generates new)
- [ ] Unit test: resolve() with invalid external header (sanitizer rejects, generates new)
- [ ] Unit test: headerName() returns configured value
- [ ] Unit test: restoreFromJob() preserves original value
- [ ] Unit test: each request gets unique request ID
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### RIM-02 Write request ID tests

**Description**:
Create comprehensive tests for request ID behavior. Verify that request IDs are unique per request, that external headers are accepted when enabled, that invalid headers are rejected, and that request IDs are preserved in job payloads. Test both unit-level (source behavior) and integration-level (full request lifecycle).

Ensure that request IDs differ across multiple requests (unlike correlation IDs which persist). Test job propagation to verify original request ID is preserved when jobs execute.

**Acceptance Criteria**:
- [ ] AC-1: Test each request generates a unique request ID
- [ ] AC-2: Test external header is used when `accept_external_headers` is enabled
- [ ] AC-3: Test external header is ignored when `accept_external_headers` is false
- [ ] AC-4: Test invalid external header is rejected (sanitizer validation)
- [ ] AC-5: Test request ID is different across multiple requests in same session
- [ ] AC-6: Test request ID is preserved when restored from job payload
- [ ] AC-7: Test correlation ID persists while request ID changes (integration test)

**Technical Notes**:
- Compare request IDs across multiple requests to ensure uniqueness
- Use Testbench to simulate job dispatch and execution
- Assert that job payload contains original request ID

**Files to Create/Modify**:
- `tests/Unit/Tracings/Sources/RequestIdSourceTest.php`
- `tests/Feature/RequestIdBehaviorTest.php`

**Testing**:
- [ ] All request ID tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 2
**Estimated Complexity**: Low
**Dependencies**: CTI (contracts, utilities), CFG (configuration for header names and toggles)

**Critical Path**:
- RIM-01 depends on CTI-01 (implements TracingSource contract) and CTI-02 (uses IdGenerator and HeaderSanitizer)
- RIM-02 depends on RIM-01 (tests RequestIdSource)
