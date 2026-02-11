# Session Persistence

**Epic ID**: SP
**Epic Goal**: Implement correlation ID persistence across multiple requests using Laravel's session storage.

**Requirements Addressed**:
- FR-02: Correlation ID persistence across multiple requests of the same user session

**Architecture Areas**:
- SessionStorage (session-backed storage)
- CorrelationIdSource (session-aware tracing source)
- Session-level persistence

---

## Tasks

### SP-01 Implement SessionStorage

**Description**:
Implement the session-backed storage for persisting correlation IDs across multiple HTTP requests. This storage wraps Laravel's session driver and implements the `TracingStorage` contract. Unlike `RequestStorage` which is cleared after each request, `SessionStorage` persists values for the duration of the user's session.

The storage uses Laravel's session facade (`session()->put()`, `session()->get()`) and stores values under a namespaced key (`laravel_tracing.*`) to avoid conflicts with application session data.

**Acceptance Criteria**:
- [ ] AC-1: Implements `TracingStorage` contract
- [ ] AC-2: Uses Laravel's session driver (`Illuminate\Support\Facades\Session` or `session()` helper)
- [ ] AC-3: Stores values under `laravel_tracing.{key}` namespace
- [ ] AC-4: `set(key, value)` persists value in session
- [ ] AC-5: `get(key)` retrieves value from session (returns null if not found)
- [ ] AC-6: `has(key)` checks if value exists in session
- [ ] AC-7: `flush()` removes all tracing values from session
- [ ] AC-8: Values persist across multiple requests in the same session

**Technical Notes**:
- Session key format: `laravel_tracing.correlation_id`
- Uses Laravel's configured session driver (file, database, redis, etc.)
- Session must be started before accessing (middleware ensures this)

**Files to Create/Modify**:
- `src/Storage/SessionStorage.php`

**Testing**:
- [ ] Unit test: set and get values with session namespace
- [ ] Unit test: has() checks session correctly
- [ ] Unit test: flush() removes all tracing values
- [ ] Feature test: values persist across multiple requests
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### SP-02 Implement CorrelationIdSource

**Description**:
Implement the built-in tracing source for correlation IDs. This source is session-aware and follows a strict resolution priority: (1) external header (if enabled), (2) session storage (from previous request), (3) generate new UUID. Once resolved, the correlation ID is persisted in session storage so subsequent requests reuse the same value.

This source is the core of session continuity. It ensures that all requests from the same user session share the same correlation ID, enabling full session tracing across multiple requests, jobs, and services.

**Acceptance Criteria**:
- [ ] AC-1: Implements `TracingSource` contract
- [ ] AC-2: Constructor accepts `SessionStorage`, `IdGenerator`, and `HeaderSanitizer` dependencies
- [ ] AC-3: `resolve(Request)` follows priority: (1) external header (if `accept_external_headers` enabled), (2) session storage, (3) generate new UUID
- [ ] AC-4: External header values are sanitized using `HeaderSanitizer`
- [ ] AC-5: Resolved value is persisted in session storage via `SessionStorage::set()`
- [ ] AC-6: `headerName()` returns configured header name (default: 'X-Correlation-Id')
- [ ] AC-7: `restoreFromJob(string)` returns value unchanged (no transformation)
- [ ] AC-8: Session persistence key: `correlation_id`

**Technical Notes**:
- Header name is configurable via `config('laravel-tracing.tracings.correlation_id.header')`
- External header acceptance is controlled by `config('laravel-tracing.accept_external_headers')`
- Session storage ensures the same correlation ID across all requests in session

**Files to Create/Modify**:
- `src/Tracings/Sources/CorrelationIdSource.php`

**Testing**:
- [ ] Unit test: resolve() with no external header and no session (generates new UUID)
- [ ] Unit test: resolve() with existing session value (reuses session value)
- [ ] Unit test: resolve() with external header enabled (uses external value)
- [ ] Unit test: resolve() with external header disabled (ignores external value)
- [ ] Unit test: resolve() with invalid external header (sanitizer rejects, generates new)
- [ ] Unit test: headerName() returns configured value
- [ ] Feature test: correlation ID persists across multiple requests
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### SP-03 Write session persistence tests

**Description**:
Create comprehensive tests for session persistence behavior. Verify that correlation IDs persist across multiple requests in the same session, that new sessions get new correlation IDs, and that session values override generation. Test both unit-level (source behavior) and integration-level (full request lifecycle).

Use Orchestra Testbench to simulate multiple requests with session state. Test session expiration, session regeneration, and external header override scenarios.

**Acceptance Criteria**:
- [ ] AC-1: Test correlation ID persists across multiple requests in same session
- [ ] AC-2: Test new session generates new correlation ID
- [ ] AC-3: Test session value takes priority over generation
- [ ] AC-4: Test external header overrides session value (when enabled)
- [ ] AC-5: Test external header is ignored when `accept_external_headers` is false
- [ ] AC-6: Test session storage namespace isolation (no conflicts with app session data)
- [ ] AC-7: Test session flush clears tracing values

**Technical Notes**:
- Use Testbench's session testing helpers
- Simulate multiple requests with `$this->get()` calls
- Assert session values between requests

**Files to Create/Modify**:
- `tests/Feature/SessionPersistenceTest.php`
- `tests/Unit/Tracings/Sources/CorrelationIdSourceTest.php`

**Testing**:
- [ ] All session persistence tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 3
**Estimated Complexity**: Low
**Dependencies**: CTI (contracts, utilities), CFG (configuration for header names and toggles)

**Critical Path**:
- SP-01 depends on CTI-01 (implements TracingStorage contract)
- SP-02 depends on CTI-01, CTI-02 (uses contracts and utilities), and SP-01 (uses SessionStorage)
- SP-03 depends on SP-01 and SP-02 (tests both components)
