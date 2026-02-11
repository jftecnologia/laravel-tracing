# Core Tracing Infrastructure

**Epic ID**: CTI
**Epic Goal**: Establish the foundational contracts, utilities, storage, and manager classes for the tracing system.

**Requirements Addressed**:
- FR-05: Global accessor for tracing values
- FR-15: Unique IDs (UUID generation)
- FR-17: Custom tracings follow contract

**Architecture Areas**:
- TracingManager (core coordinator)
- TracingSource contract (extensibility)
- TracingStorage contract (abstraction)
- RequestStorage (request-scoped data)
- Support utilities (IdGenerator, HeaderSanitizer)

---

## Tasks

### CTI-01 Implement core contracts

**Description**:
Create the foundational interfaces that define how tracing sources and storage backends work. The `TracingSource` contract defines how tracings are resolved, while the `TracingStorage` contract defines how values are stored and retrieved. These contracts are the extension points that enable custom tracings and storage backends without modifying package internals.

All tracing sources (built-in and custom) must implement `TracingSource`. All storage backends must implement `TracingStorage`. This ensures consistency and makes the system open for extension.

**Acceptance Criteria**:
- [ ] AC-1: `TracingSource` interface exists with methods: `resolve(Request): string`, `headerName(): string`, `restoreFromJob(string): string`
- [ ] AC-2: `TracingStorage` interface exists with methods: `set(string, string): void`, `get(string): ?string`, `has(string): bool`, `flush(): void`
- [ ] AC-3: All interface methods have proper PHPDoc annotations with parameter and return type descriptions
- [ ] AC-4: Contracts follow SOLID principles and are placed in appropriate namespaces

**Technical Notes**:
- Contracts define behavior, not implementation
- `TracingSource::restoreFromJob()` allows custom transformation when restoring from job payload
- Storage abstraction enables future backends (cache, redis, etc.)

**Files to Create/Modify**:
- `src/Tracings/Contracts/TracingSource.php`
- `src/Tracings/Contracts/TracingStorage.php`

**Testing**:
- [ ] No unit tests needed (interfaces only)
- [ ] `composer lint` passes

---

### CTI-02 Implement support utilities

**Description**:
Create the support utilities that all tracing sources will use. `IdGenerator` provides UUID generation for correlation IDs and request IDs, ensuring uniqueness across all tracings. `HeaderSanitizer` validates and sanitizes external header values to prevent injection attacks. These utilities enforce security and consistency across the package.

The sanitizer must enforce strict validation: max 255 characters, only alphanumeric characters, hyphens, and underscores. Any invalid input returns null, forcing the tracing source to generate a new ID instead.

**Acceptance Criteria**:
- [ ] AC-1: `IdGenerator::generate()` returns UUID v4 string using Laravel's `Str::uuid()`
- [ ] AC-2: `HeaderSanitizer::sanitize(?string): ?string` trims whitespace, enforces 255-character limit, and validates against regex `/^[a-zA-Z0-9\-_]+$/`
- [ ] AC-3: Sanitizer returns null for invalid input (too long, invalid characters, or null input)
- [ ] AC-4: Both utilities are static classes with no dependencies

**Technical Notes**:
- Use Laravel's `Illuminate\Support\Str::uuid()` for ID generation
- Security: Sanitizer prevents header injection and log injection attacks
- Performance: Sanitizer runs on every external header (if enabled)

**Files to Create/Modify**:
- `src/Support/IdGenerator.php`
- `src/Support/HeaderSanitizer.php`

**Testing**:
- [ ] Unit test `IdGenerator::generate()` returns valid UUID format
- [ ] Unit test `HeaderSanitizer::sanitize()` with valid input, invalid characters, length exceeding 255, null input
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTI-03 Implement RequestStorage

**Description**:
Implement the in-memory storage backend for the current request lifecycle. This storage holds all resolved tracing values (correlation ID, request ID, custom tracings) for the duration of a single HTTP request. Values are cleared automatically when the request ends (PHP process terminates or new request starts).

RequestStorage is injected into `TracingManager` and accessed throughout the request lifecycle. It implements the `TracingStorage` contract to allow future alternative backends.

**Acceptance Criteria**:
- [ ] AC-1: Implements `TracingStorage` contract
- [ ] AC-2: Uses in-memory array to store key-value pairs
- [ ] AC-3: `set(key, value)` stores value, `get(key)` retrieves value, `has(key)` checks existence
- [ ] AC-4: `flush()` clears all stored values
- [ ] AC-5: Returns null for non-existent keys

**Technical Notes**:
- Request-scoped: values do not persist beyond the current request
- No serialization or external storage needed
- Simple array-backed implementation

**Files to Create/Modify**:
- `src/Storage/RequestStorage.php`

**Testing**:
- [ ] Unit test: set and get values
- [ ] Unit test: has() returns true for existing keys, false for missing keys
- [ ] Unit test: flush() clears all values
- [ ] Unit test: get() returns null for non-existent keys
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTI-04 Implement TracingManager

**Description**:
Implement the central coordinator for all tracing operations. `TracingManager` loads tracing sources from configuration, resolves values from sources, caches resolved values in storage, and provides global access to all tracings. This is the core component that ties together sources, storage, and the public API.

The manager uses lazy resolution: tracings are resolved only when `resolveAll(Request)` is called (triggered by middleware). Once resolved, values are cached in `RequestStorage` for fast subsequent access. The manager also supports runtime extension via `extend()` for programmatic registration of custom tracings.

**Acceptance Criteria**:
- [ ] AC-1: Constructor accepts array of `TracingSource` instances and `RequestStorage` instance
- [ ] AC-2: `resolveAll(Request)` iterates all sources, calls `resolve()`, and stores results in storage
- [ ] AC-3: `all()` returns associative array of all resolved tracings (key => value)
- [ ] AC-4: `get(string $key)` returns specific tracing value or null if not found
- [ ] AC-5: `has(string $key)` returns boolean indicating if tracing exists
- [ ] AC-6: `extend(string $key, TracingSource $source)` registers a custom tracing at runtime
- [ ] AC-7: Lazy resolution: sources are not called until `resolveAll()` is triggered

**Technical Notes**:
- Manager does not instantiate sources; they are injected via constructor
- `extend()` allows runtime registration (used for programmatic custom tracings)
- Storage is abstracted via `TracingStorage` contract

**Files to Create/Modify**:
- `src/Tracings/TracingManager.php`

**Testing**:
- [ ] Unit test: resolveAll() calls resolve() on all sources and stores values
- [ ] Unit test: all() returns all resolved tracings
- [ ] Unit test: get() returns specific tracing value
- [ ] Unit test: has() checks existence correctly
- [ ] Unit test: extend() adds runtime tracing source
- [ ] Unit test: lazy resolution (sources not called until resolveAll())
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTI-05 Implement LaravelTracing class

**Description**:
Implement the main API class that developers interact with throughout the application. This class acts as a facade-like accessor to `TracingManager`, providing a fluent, developer-friendly API for retrieving tracing values. It delegates all calls to the manager singleton registered in the service container.

This class is bound in the container and accessed via the `LaravelTracing` facade. It provides convenience methods like `correlationId()` and `requestId()` for common tracings, as well as generic methods like `all()` and `get()` for accessing any tracing.

**Acceptance Criteria**:
- [ ] AC-1: Constructor accepts `TracingManager` instance (injected from container)
- [ ] AC-2: `all(): array` returns all current tracing values
- [ ] AC-3: `get(string $key): ?string` returns specific tracing value
- [ ] AC-4: `has(string $key): bool` checks if tracing exists
- [ ] AC-5: `correlationId(): ?string` returns correlation ID specifically
- [ ] AC-6: `requestId(): ?string` returns request ID specifically
- [ ] AC-7: All methods delegate to `TracingManager`

**Technical Notes**:
- This class is the public API surface for the package
- Injected via container: `app(LaravelTracing::class)`
- Accessed via facade: `LaravelTracing::correlationId()`

**Files to Create/Modify**:
- `src/LaravelTracing.php`
- `src/Facades/LaravelTracing.php` (if not already created)

**Testing**:
- [ ] Unit test: all() delegates to manager
- [ ] Unit test: get() delegates to manager
- [ ] Unit test: has() delegates to manager
- [ ] Unit test: correlationId() returns correlation_id key
- [ ] Unit test: requestId() returns request_id key
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTI-06 Write unit tests for core components

**Description**:
Create comprehensive unit tests for all core components implemented in this epic. Ensure that contracts are properly implemented, utilities work correctly, storage behaves as expected, and the manager coordinates sources correctly. Tests should cover happy paths, edge cases, and error conditions.

Use PestPHP for all tests. Mock dependencies where appropriate (e.g., mock TracingSource when testing manager). Tests should be fast, isolated, and deterministic.

**Acceptance Criteria**:
- [ ] AC-1: Test coverage for `IdGenerator` (UUID format validation)
- [ ] AC-2: Test coverage for `HeaderSanitizer` (valid, invalid, edge cases)
- [ ] AC-3: Test coverage for `RequestStorage` (set, get, has, flush)
- [ ] AC-4: Test coverage for `TracingManager` (resolveAll, all, get, has, extend, lazy resolution)
- [ ] AC-5: Test coverage for `LaravelTracing` (delegation to manager)
- [ ] AC-6: All tests pass with `composer test`
- [ ] AC-7: No test warnings or deprecation notices

**Technical Notes**:
- Use Orchestra Testbench for Laravel integration testing
- Mock `TracingSource` instances for manager tests
- Use data providers for edge case testing (HeaderSanitizer)

**Files to Create/Modify**:
- `tests/Unit/Support/IdGeneratorTest.php`
- `tests/Unit/Support/HeaderSanitizerTest.php`
- `tests/Unit/Storage/RequestStorageTest.php`
- `tests/Unit/Tracings/TracingManagerTest.php`
- `tests/Unit/LaravelTracingTest.php`

**Testing**:
- [ ] All unit tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 6
**Estimated Complexity**: Medium
**Dependencies**: None (foundation epic)

**Critical Path**:
- CTI-01 and CTI-02 are independent and can be done in parallel
- CTI-03 depends on CTI-01 (implements contract)
- CTI-04 depends on CTI-01 and CTI-03
- CTI-05 depends on CTI-04
- CTI-06 depends on all previous tasks
