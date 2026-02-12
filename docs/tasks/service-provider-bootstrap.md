# Service Provider Bootstrap

**Epic ID**: SPB
**Epic Goal**: Bootstrap the package via the service provider, registering all services and components in Laravel's container.

**Requirements Addressed**:
- FR-01: Service provider auto-discovery (middleware requires manual registration)
- FR-14: Config file publishing

**Architecture Areas**:
- LaravelTracingServiceProvider (package bootstrap)
- Container service registration
- Middleware registration
- Config publishing

---

## Tasks

### SPB-01 Register TracingManager as singleton

**Description**:
Register `TracingManager` as a singleton in Laravel's service container. The manager must be instantiated once per request lifecycle and shared across all components (middleware, facade, HTTP client, job dispatcher). The registration should inject all configured tracing sources and storage dependencies.

The service provider must read the tracing sources from configuration, instantiate them with their dependencies, and inject them into the manager. This is the core bootstrap step that makes the tracing system functional.

**Acceptance Criteria**:
- [ ] AC-1: `TracingManager` is registered as singleton via `app()->singleton()`
- [ ] AC-2: Manager receives array of instantiated `TracingSource` instances from config
- [ ] AC-3: Manager receives `RequestStorage` instance
- [ ] AC-4: Built-in sources (CorrelationIdSource, RequestIdSource) are instantiated with dependencies
- [ ] AC-5: Custom sources from config are instantiated and registered
- [ ] AC-6: Registration happens in `LaravelTracingServiceProvider::register()`
- [ ] AC-7: Manager is resolvable via container: `app(TracingManager::class)`

**Technical Notes**:
- Use `app()->singleton(TracingManager::class, function ($app) { ... })`
- Read tracing sources from `config('laravel-tracing.tracings')`
- Instantiate each source class with its dependencies (IdGenerator, SessionStorage, etc.)
- Filter disabled sources before registering

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (implement register() method)

**Testing**:
- [ ] Unit test: TracingManager is registered as singleton
- [ ] Unit test: manager receives built-in sources
- [ ] Unit test: manager receives custom sources from config
- [ ] Unit test: manager is resolvable from container
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### SPB-02 Register tracing sources from config

**Description**:
Implement the logic to read tracing source configuration and instantiate source classes with their dependencies. The config specifies each tracing's `enabled` status, `header` name, and `source` class. The service provider must parse this config, instantiate the source classes, and register them with the manager.

Support both built-in sources (CorrelationIdSource, RequestIdSource) and custom sources. Each source class must receive its configured dependencies (IdGenerator, SessionStorage, HeaderSanitizer) based on its constructor signature.

**Acceptance Criteria**:
- [ ] AC-1: Read `config('laravel-tracing.tracings')` to get all tracing definitions
- [ ] AC-2: Filter out disabled tracings (where `enabled` = false)
- [ ] AC-3: Instantiate each source class specified in `source` key
- [ ] AC-4: Inject dependencies into source constructors (IdGenerator, SessionStorage, HeaderSanitizer)
- [ ] AC-5: Pass configured `header` name to sources that need it
- [ ] AC-6: Support custom source classes from application namespace (e.g., `App\Tracings\UserIdSource`)
- [ ] AC-7: Handle missing or invalid source classes gracefully (log warning, skip source)

**Technical Notes**:
- Use reflection or `app()->make($sourceClass)` for instantiation
- Built-in dependencies (IdGenerator, SessionStorage) must be registered first
- Custom sources must implement `TracingSource` contract
- Config structure: `tracings.correlation_id.source = CorrelationIdSource::class`

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (implement source loading logic)

**Testing**:
- [ ] Unit test: built-in sources are loaded from config
- [ ] Unit test: custom sources are loaded from config
- [ ] Unit test: disabled sources are not loaded
- [ ] Unit test: invalid source classes are skipped gracefully
- [ ] Feature test: custom tracing source is registered and works
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### SPB-03 Implement package enable/disable check

**Description**:
Implement the global enable/disable check in the service provider. When `config('laravel-tracing.enabled')` is false, the package should skip all registration steps (middleware, job listeners, HTTP macros). This ensures zero overhead when tracing is disabled.

The check should happen early in the `boot()` method. If disabled, return immediately without registering any services. This prevents unnecessary service instantiation and middleware execution.

**Acceptance Criteria**:
- [ ] AC-1: Check `config('laravel-tracing.enabled')` in `boot()` method
- [ ] AC-2: If false, return early without registering middleware, job listeners, or HTTP macros
- [ ] AC-3: If true, proceed with normal registration
- [ ] AC-4: TracingManager is still registered (for facade access, even if disabled)
- [ ] AC-5: Disabled state is respected across all package components
- [ ] AC-6: Environment variable `LARAVEL_TRACING_ENABLED` overrides config

**Technical Notes**:
- Early return pattern: `if (!config('laravel-tracing.enabled')) { return; }`
- Manager is still registered (but will return empty values when disabled)
- Middleware checks enabled status independently (double-check pattern)

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (implement enable check in boot())

**Testing**:
- [ ] Feature test: package disabled (no middleware, no job listeners, no HTTP macros)
- [ ] Feature test: package enabled (all components registered)
- [ ] Feature test: LARAVEL_TRACING_ENABLED=false disables package
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### SPB-04 Write service provider tests

**Description**:
Create comprehensive tests for service provider bootstrap logic. Verify that all services are registered correctly, that the manager receives the correct sources, that config is merged properly, and that the enable/disable toggle works. Test both unit-level (service registration) and integration-level (full bootstrap).

Use Orchestra Testbench to simulate package boot process. Test with different config states (enabled/disabled, custom sources, disabled sources).

**Acceptance Criteria**:
- [ ] AC-1: Test TracingManager is registered as singleton
- [ ] AC-2: Test built-in sources are registered in manager
- [ ] AC-3: Test custom sources from config are registered
- [ ] AC-4: Test disabled sources are not registered
- [ ] AC-5: Test package disabled (no middleware registered)
- [ ] AC-6: Test config is merged from package defaults
- [ ] AC-7: Test config is publishable
- [ ] AC-8: Test Laravel auto-discovery registers service provider (note: middleware requires manual registration in Laravel 12)

**Technical Notes**:
- Use `app()->make(TracingManager::class)` to resolve manager
- Use reflection to inspect registered sources
- Test config publishing with `artisan vendor:publish`

**Files to Create/Modify**:
- `tests/Unit/LaravelTracingServiceProviderTest.php`
- `tests/Feature/ServiceProviderBootstrapTest.php`

**Testing**:
- [ ] All service provider tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 4
**Estimated Complexity**: Medium
**Dependencies**: CTI (TracingManager, sources), CFG (configuration), SP (CorrelationIdSource), RIM (RequestIdSource), MI (middleware)

**Critical Path**:
- SPB-01 depends on CTI-04 (TracingManager must exist)
- SPB-02 depends on CFG-01 (config structure), SP-02 (CorrelationIdSource), RIM-01 (RequestIdSource)
- SPB-03 depends on CFG-03 (enable/disable toggle config)
- SPB-04 depends on SPB-01 through SPB-03 (tests all bootstrap features)
