# Configuration System

**Epic ID**: CFG
**Epic Goal**: Implement the configuration file structure and environment variable support for all package settings.

**Requirements Addressed**:
- FR-09: Configurable header names for built-in tracings
- FR-12: Global enable/disable toggle
- FR-13: Per-tracing enable/disable toggle
- FR-14: Publishable config file
- FR-19: Toggle external header acceptance

**Architecture Areas**:
- Configuration file structure
- Environment variable mapping
- Enable/disable toggles

---

## Tasks

### CFG-01 Implement configuration file structure

**Description**:
Create the main configuration file that defines all package settings. The config file must be well-organized, heavily commented, and provide sensible defaults that work out of the box. It must support configuration for built-in tracings (correlation ID, request ID), custom tracings, HTTP client integration, and global toggles.

The config structure should mirror the architecture: a `tracings` array for individual tracing sources, an `http_client` section for HTTP integration, and top-level settings for global behavior. Each tracing entry should support `enabled`, `header`, and `source` keys.

**Acceptance Criteria**:
- [ ] AC-1: Config file exists at `config/laravel-tracing.php`
- [ ] AC-2: Top-level `enabled` key (boolean, default: true) for global enable/disable
- [ ] AC-3: Top-level `accept_external_headers` key (boolean, default: false) for FR-19
- [ ] AC-4: `tracings` array with entries for `correlation_id` and `request_id`
- [ ] AC-5: Each tracing entry has `enabled`, `header`, and `source` keys
- [ ] AC-6: Default headers: `X-Correlation-Id` and `X-Request-Id`
- [ ] AC-7: Default sources: `CorrelationIdSource::class` and `RequestIdSource::class`
- [ ] AC-8: `http_client` section with `enabled` key (default: false for opt-in)
- [ ] AC-9: Comprehensive comments explaining each setting and its purpose

**Technical Notes**:
- Config is published via `php artisan vendor:publish --tag=laravel-tracing-config`
- Defaults work with minimal setup (middleware registration required)
- Config structure supports runtime extension via `TracingManager::extend()`

**Files to Create/Modify**:
- `config/laravel-tracing.php`

**Testing**:
- [ ] Feature test: config file is publishable
- [ ] Feature test: default config values are loaded correctly
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CFG-02 Add environment variable support

**Description**:
Map environment variables to config keys for easy deployment configuration. Developers should be able to override config settings via `.env` without publishing the config file. Common settings (enabled, accept_external_headers, header names) should have corresponding environment variables.

Use Laravel's `env()` helper to read environment variables with fallback to default values. Environment variable names should follow Laravel conventions: `LARAVEL_TRACING_*` prefix, uppercase, underscores for separators.

**Acceptance Criteria**:
- [ ] AC-1: `LARAVEL_TRACING_ENABLED` environment variable maps to `enabled` config key (default: true)
- [ ] AC-2: `LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS` maps to `accept_external_headers` (default: false)
- [ ] AC-3: `LARAVEL_TRACING_CORRELATION_ID_HEADER` maps to `tracings.correlation_id.header` (default: 'X-Correlation-Id')
- [ ] AC-4: `LARAVEL_TRACING_REQUEST_ID_HEADER` maps to `tracings.request_id.header` (default: 'X-Request-Id')
- [ ] AC-5: `LARAVEL_TRACING_HTTP_CLIENT_ENABLED` maps to `http_client.enabled` (default: false)
- [ ] AC-6: Environment variables override config file defaults
- [ ] AC-7: All env() calls have proper default values

**Technical Notes**:
- Use `env('LARAVEL_TRACING_ENABLED', true)` pattern
- Environment variables take precedence over config defaults
- Booleans: use `filter_var(env('...'), FILTER_VALIDATE_BOOLEAN)` or cast

**Files to Create/Modify**:
- `config/laravel-tracing.php` (add env() calls)

**Testing**:
- [ ] Feature test: LARAVEL_TRACING_ENABLED=false disables package
- [ ] Feature test: LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS=false rejects external headers
- [ ] Feature test: custom header names via env variables
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CFG-03 Implement enable/disable toggles

**Description**:
Implement the logic for global and per-tracing enable/disable toggles. The global toggle should completely disable all tracing operations when set to false. Per-tracing toggles should allow individual tracings to be disabled without affecting others.

This logic should be enforced at multiple points: service provider bootstrap (skip registration if disabled), middleware (skip execution if disabled), and tracing manager (skip disabled sources during resolution). The goal is zero overhead when tracing is disabled.

**Acceptance Criteria**:
- [ ] AC-1: When `config('laravel-tracing.enabled')` is false, all tracing logic is skipped
- [ ] AC-2: When `config('laravel-tracing.tracings.correlation_id.enabled')` is false, correlation ID is not resolved
- [ ] AC-3: When `config('laravel-tracing.tracings.request_id.enabled')` is false, request ID is not resolved
- [ ] AC-4: Disabled tracings are excluded from `TracingManager::all()`
- [ ] AC-5: Global disable prevents middleware execution (early return)
- [ ] AC-6: Per-tracing disable is checked in `TracingManager::resolveAll()`

**Technical Notes**:
- Global toggle: check in service provider `boot()` and middleware `handle()`
- Per-tracing toggle: filter sources in manager before resolution
- Disabled tracings should not appear in response headers or job payloads

**Files to Create/Modify**:
- `src/Tracings/TracingManager.php` (filter disabled sources)
- `src/Middleware/IncomingTracingMiddleware.php` (check global toggle)
- `src/Middleware/OutgoingTracingMiddleware.php` (check global toggle)

**Testing**:
- [ ] Feature test: global toggle disabled (no headers, no job propagation)
- [ ] Feature test: correlation ID disabled, request ID enabled
- [ ] Feature test: request ID disabled, correlation ID enabled
- [ ] Unit test: manager filters disabled sources
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### CFG-04 Write configuration tests

**Description**:
Create comprehensive tests for all configuration scenarios. Test default values, environment variable overrides, published config, enable/disable toggles, and invalid configurations. Ensure the configuration system is robust and handles edge cases gracefully.

Use Orchestra Testbench to simulate different config states. Test both unit-level (config values are read correctly) and integration-level (config affects behavior).

**Acceptance Criteria**:
- [ ] AC-1: Test default config values are loaded without publishing
- [ ] AC-2: Test config file is publishable and contains expected keys
- [ ] AC-3: Test environment variables override config defaults
- [ ] AC-4: Test global enable/disable toggle affects package behavior
- [ ] AC-5: Test per-tracing enable/disable toggles affect resolution
- [ ] AC-6: Test custom header names via config
- [ ] AC-7: Test external header acceptance toggle

**Technical Notes**:
- Use `Config::set()` to simulate config changes in tests
- Use `putenv()` to simulate environment variables
- Test both enabled and disabled states for all toggles

**Files to Create/Modify**:
- `tests/Feature/ConfigurationTest.php`
- `tests/Feature/EnableDisableToggleTest.php`

**Testing**:
- [ ] All configuration tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 4
**Estimated Complexity**: Low
**Dependencies**: CTI (core infrastructure must exist to be configured)

**Critical Path**:
- CFG-01 is the foundation (must be done first)
- CFG-02 depends on CFG-01 (adds env mapping to config)
- CFG-03 depends on CFG-01 (implements toggle logic)
- CFG-04 depends on CFG-01 through CFG-03 (tests all config features)
