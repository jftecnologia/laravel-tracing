# Custom Tracing Sources

**Epic ID**: CTS
**Epic Goal**: Enable developers to add, replace, and extend tracing sources without modifying package code.

**Requirements Addressed**:
- FR-10: Register additional custom tracing sources
- FR-11: Replace built-in tracing sources with custom implementations
- FR-17: Custom tracings follow the same contract as built-in tracings

**Architecture Areas**:
- Runtime extension via TracingManager
- Custom tracing source registration
- Extension points documentation

---

## Tasks

### CTS-01 Implement runtime extension

**Description**:
Implement the `extend()` method on `TracingManager` that allows programmatic registration of custom tracing sources at runtime. This enables developers to add custom tracings via service providers or other bootstrap code without editing the config file.

The extension method must accept a tracing key and a `TracingSource` instance, and register it with the manager so it's resolved and propagated like built-in tracings. The method should support chaining for multiple extensions.

**Acceptance Criteria**:
- [ ] AC-1: `TracingManager::extend(string $key, TracingSource $source)` method exists
- [ ] AC-2: Method adds the source to the internal sources array
- [ ] AC-3: Extended sources are resolved during `resolveAll()`
- [ ] AC-4: Extended sources are included in `all()`, `get()`, `has()` methods
- [ ] AC-5: Extended sources are propagated to jobs, HTTP responses, and HTTP client
- [ ] AC-6: Method returns `$this` for chaining
- [ ] AC-7: Extended sources can be added in service providers or boot methods

**Technical Notes**:
- Extension happens after initial registration (additive, not replacement)
- Sources are stored in internal array with key as identifier
- Resolution order: config sources first, then extended sources

**Files to Create/Modify**:
- `src/Tracings/TracingManager.php` (add extend() method)

**Testing**:
- [ ] Unit test: extend() adds source to manager
- [ ] Unit test: extended source is resolved during resolveAll()
- [ ] Unit test: extended source is included in all()
- [ ] Unit test: method is chainable
- [ ] Feature test: extended source is propagated to jobs and responses
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTS-02 Document extension points

**Description**:
Create comprehensive documentation for all package extension points. Document the `TracingSource` contract, how to implement custom tracing sources, how to register them via config or runtime extension, and how to replace built-in sources. Provide clear examples and guidelines.

The documentation should be part of the architecture docs (EXTENSIONS.md) and referenced in the README. Include code examples, common use cases, and best practices.

**Acceptance Criteria**:
- [ ] AC-1: Document `TracingSource` contract and all methods
- [ ] AC-2: Document how to create custom tracing sources (implement contract)
- [ ] AC-3: Document how to register custom sources via config
- [ ] AC-4: Document how to register custom sources via runtime extension
- [ ] AC-5: Document how to replace built-in sources (override in config)
- [ ] AC-6: Provide example custom tracing source (e.g., X-User-Id)
- [ ] AC-7: Document all extension points (sources, storage, middleware)
- [ ] AC-8: Add extension guide to README or link to EXTENSIONS.md

**Technical Notes**:
- Examples should be realistic and follow project code standards
- Document constructor dependencies and injection
- Explain session vs request scoping for custom sources

**Files to Create/Modify**:
- `docs/architecture/EXTENSIONS.md` (create or update)
- `README.md` (add extension section or link)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify examples are syntactically correct
- [ ] `composer lint` passes on example code (if included in codebase)

---

### CTS-03 Create example custom tracing source

**Description**:
Create a realistic example custom tracing source that developers can use as a reference. The example should demonstrate best practices for implementing the `TracingSource` contract, handling dependencies, and integrating with the tracing system.

The example should implement a `UserIdSource` that resolves the authenticated user's ID from the request. This is a common use case that demonstrates session-aware tracing and authorization context.

**Acceptance Criteria**:
- [ ] AC-1: Example class `UserIdSource` implements `TracingSource` contract
- [ ] AC-2: `resolve(Request)` returns authenticated user ID or 'guest' if not authenticated
- [ ] AC-3: `headerName()` returns 'X-User-Id'
- [ ] AC-4: `restoreFromJob(string)` returns value unchanged
- [ ] AC-5: Example is documented with PHPDoc comments
- [ ] AC-6: Example is included in docs or tests directory as reference
- [ ] AC-7: Example demonstrates dependency injection (if needed)

**Technical Notes**:
- User resolution: `$request->user()?->id ?? 'guest'`
- Example should be simple and self-contained
- Include in tests/Fixtures or docs/examples directory

**Files to Create/Modify**:
- `tests/Fixtures/UserIdSource.php` (example implementation)
- `docs/architecture/EXTENSIONS.md` (reference the example)

**Testing**:
- [ ] Unit test: example source resolves user ID correctly
- [ ] Unit test: example source returns 'guest' when not authenticated
- [ ] Feature test: example source is registered and works end-to-end
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CTS-04 Write extension tests

**Description**:
Create comprehensive tests for custom tracing source extension. Test config-based registration, runtime extension, replacement of built-in sources, and end-to-end propagation of custom tracings. Verify that custom sources follow the same lifecycle as built-in sources (resolution, storage, response headers, job propagation, HTTP client).

Use the example `UserIdSource` to test custom tracing behavior. Test both happy paths and edge cases (invalid sources, missing dependencies, disabled custom tracings).

**Acceptance Criteria**:
- [ ] AC-1: Test custom source registered via config is loaded and resolved
- [ ] AC-2: Test custom source registered via runtime extension is loaded and resolved
- [ ] AC-3: Test custom source replaces built-in source (override in config)
- [ ] AC-4: Test custom source is included in response headers
- [ ] AC-5: Test custom source is propagated to jobs
- [ ] AC-6: Test custom source is attached to outgoing HTTP requests
- [ ] AC-7: Test disabled custom source is not resolved
- [ ] AC-8: Test invalid custom source class is handled gracefully

**Technical Notes**:
- Use `UserIdSource` fixture for testing
- Test full lifecycle: registration → resolution → storage → propagation
- Test config override: replace CorrelationIdSource with custom implementation

**Files to Create/Modify**:
- `tests/Feature/CustomTracingSourceTest.php`
- `tests/Feature/RuntimeExtensionTest.php`

**Testing**:
- [ ] All extension tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 4
**Estimated Complexity**: Medium
**Dependencies**: SPB (service provider and manager must be fully functional)

**Critical Path**:
- CTS-01 depends on CTI-04 (adds extend() method to TracingManager)
- CTS-02 depends on CTI-01 (documents TracingSource contract)
- CTS-03 depends on CTI-01 (implements TracingSource contract) and CTS-02 (references docs)
- CTS-04 depends on CTS-01 and CTS-03 (tests runtime extension and example source)
