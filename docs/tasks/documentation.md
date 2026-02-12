# Documentation

**Epic ID**: DOC
**Epic Goal**: Create comprehensive user-facing documentation covering installation, configuration, usage, and troubleshooting.

**Requirements Addressed**:
- FR-18: Comprehensive README with installation, configuration, usage, custom sources, job integration, HTTP client integration

**Architecture Areas**:
- README.md (user-facing documentation)
- Installation and setup guides
- Usage examples and best practices

---

## Tasks

### DOC-01 Write comprehensive README

**Description**:
Create the main README.md file that serves as the entry point for all package documentation. The README should provide a clear overview of the package, its purpose, key features, and quick start guide. It should be well-organized, visually appealing, and cover all major use cases at a high level with links to detailed documentation.

Follow standard Laravel package README structure: badges, installation, basic usage, configuration, advanced features, testing, contributing, license. Include code examples that are copy-pastable and work out of the box.

**Acceptance Criteria**:
- [ ] AC-1: Overview section explaining what the package does and why it's useful
- [ ] AC-2: Features list highlighting key capabilities (session persistence, job propagation, HTTP integration, custom sources)
- [ ] AC-3: Requirements section (Laravel 12, PHP 8.4+)
- [ ] AC-4: Quick start guide with installation and middleware registration example
- [ ] AC-5: Links to detailed sections (installation, configuration, usage, custom sources)
- [ ] AC-6: Badges for build status, coverage, version, license
- [ ] AC-7: Clear table of contents for easy navigation
- [ ] AC-8: License section (MIT or project license)

**Technical Notes**:
- Keep README concise (detailed content goes in separate sections)
- Use markdown formatting for readability
- Include practical examples that developers can run immediately

**Files to Create/Modify**:
- `README.md`

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify all links work
- [ ] Verify code examples are syntactically correct

---

### DOC-02 Add installation guide

**Description**:
Write detailed installation instructions covering all installation scenarios: basic installation via Composer, package auto-discovery, config publishing, and environment variable setup. Include instructions for both new projects and existing projects with custom configurations.

Cover common installation issues and how to verify the package is working correctly. Include Laravel 12-specific setup if needed.

**Acceptance Criteria**:
- [ ] AC-1: Basic installation via `composer require jftecnologia/laravel-tracing`
- [ ] AC-2: Explain middleware registration requirement in `bootstrap/app.php` (required for Laravel 12)
- [ ] AC-3: Config publishing instructions: `php artisan vendor:publish --tag=laravel-tracing-config`
- [ ] AC-4: Environment variable setup (LARAVEL_TRACING_ENABLED, header names, etc.)
- [ ] AC-5: Verification steps (make request, check response headers)
- [ ] AC-6: Common installation issues and troubleshooting
- [ ] AC-7: Upgrading from previous versions (if applicable)

**Technical Notes**:
- Middleware registration is required (not automatic in Laravel 12)
- Config publishing is optional (for customization)
- Clear separation between required and optional steps

**Files to Create/Modify**:
- `README.md` (Installation section)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify installation steps work on fresh Laravel 12 project
- [ ] Verify published config has all expected keys

---

### DOC-03 Add configuration reference

**Description**:
Document all available configuration options with descriptions, default values, and examples. Cover global toggles (enabled, accept_external_headers), per-tracing settings (enabled, header, source), HTTP client settings, and environment variable mappings.

Organize configuration docs by category (global settings, built-in tracings, custom tracings, HTTP client). Include examples for common configuration scenarios (disable tracing in local, custom header names, add custom tracing).

**Acceptance Criteria**:
- [ ] AC-1: Document all config keys in `config/laravel-tracing.php`
- [ ] AC-2: Explain `enabled` global toggle with examples
- [ ] AC-3: Explain `accept_external_headers` toggle with security implications
- [ ] AC-4: Document `tracings` array structure (enabled, header, source)
- [ ] AC-5: Document `http_client.enabled` setting
- [ ] AC-6: List all environment variables with mappings (LARAVEL_TRACING_ENABLED, etc.)
- [ ] AC-7: Provide configuration examples for common scenarios
- [ ] AC-8: Document default values for all settings

**Technical Notes**:
- Configuration reference should be comprehensive (cover all options)
- Include security notes for `accept_external_headers`
- Examples should demonstrate real use cases

**Files to Create/Modify**:
- `README.md` (Configuration section)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify all config keys are documented
- [ ] Verify examples work as described

---

### DOC-04 Add usage examples

**Description**:
Provide practical usage examples covering all major features: accessing tracing values, session persistence, job propagation, HTTP client integration, and custom tracings. Each example should be complete, runnable, and demonstrate a real use case.

Include examples for controllers, jobs, services, middleware, and HTTP client usage. Show how to use the `LaravelTracing` facade and how tracing values integrate with logging and debugging workflows.

**Acceptance Criteria**:
- [ ] AC-1: Example: Accessing tracing values in controllers via `LaravelTracing::all()`
- [ ] AC-2: Example: Using tracings in log context (`Log::info('message', LaravelTracing::all())`)
- [ ] AC-3: Example: Accessing tracings in job handlers
- [ ] AC-4: Example: Forwarding tracings to external APIs via `Http::withTracing()`
- [ ] AC-5: Example: Session persistence (same correlation ID across requests)
- [ ] AC-6: Example: Multiple jobs dispatched from same request share correlation ID
- [ ] AC-7: Code examples are complete and runnable
- [ ] AC-8: Examples follow Laravel best practices

**Technical Notes**:
- Examples should be practical and realistic
- Show integration with Laravel features (logging, queues, HTTP client)
- Include expected output or behavior for each example

**Files to Create/Modify**:
- `README.md` (Usage Examples section)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify examples are syntactically correct
- [ ] Verify examples work in actual Laravel application

---

### DOC-05 Add custom tracing guide

**Description**:
Write a detailed guide on how to create, register, and use custom tracing sources. Cover implementing the `TracingSource` contract, registering via config, registering via runtime extension, and replacing built-in sources. Include complete examples and best practices.

The guide should empower developers to extend the package without modifying its code. Include the `UserIdSource` example and explain when to use session-scoped vs request-scoped custom tracings.

**Acceptance Criteria**:
- [ ] AC-1: Explain `TracingSource` contract and all methods
- [ ] AC-2: Step-by-step guide to implementing a custom tracing source
- [ ] AC-3: Example: Creating a `UserIdSource` tracing
- [ ] AC-4: Registering custom source via config (`tracings.user_id.source`)
- [ ] AC-5: Registering custom source via runtime extension (`TracingManager::extend()`)
- [ ] AC-6: Replacing built-in source (override `correlation_id` with custom implementation)
- [ ] AC-7: Best practices for custom sources (dependencies, scoping, performance)
- [ ] AC-8: Troubleshooting custom source issues

**Technical Notes**:
- Include complete code examples with PHPDoc
- Explain dependency injection for custom sources
- Cover both simple and complex custom tracing scenarios

**Files to Create/Modify**:
- `README.md` (Custom Tracing Sources section)
- `docs/architecture/EXTENSIONS.md` (detailed extension guide)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify examples are syntactically correct
- [ ] Verify custom source examples work end-to-end

---

### DOC-06 Add troubleshooting section

**Description**:
Create a troubleshooting guide covering common issues, error messages, debugging techniques, and frequently asked questions. Address known limitations, edge cases, and gotchas. Provide clear solutions and workarounds for common problems.

Include debugging steps (check response headers, verify config, test with disabled package), common misconfigurations, and integration issues with other packages.

**Acceptance Criteria**:
- [ ] AC-1: Troubleshooting: Tracing headers not appearing in response
- [ ] AC-2: Troubleshooting: Tracings not accessible in jobs
- [ ] AC-3: Troubleshooting: Custom tracing source not loaded
- [ ] AC-4: Troubleshooting: External headers not accepted
- [ ] AC-5: Troubleshooting: Session correlation ID not persisting
- [ ] AC-6: FAQ: Why use correlation ID vs request ID?
- [ ] AC-7: FAQ: Can I use this with stateless APIs?
- [ ] AC-8: FAQ: How do I integrate with logging?
- [ ] AC-9: Debugging steps (verify config, check middleware, test with curl)

**Technical Notes**:
- Organize by symptom (what the user observes)
- Provide step-by-step diagnostic steps
- Link to relevant config and code sections

**Files to Create/Modify**:
- `README.md` (Troubleshooting section)

**Testing**:
- [ ] No tests needed (documentation only)
- [ ] Verify troubleshooting steps solve actual problems
- [ ] Test debugging commands work as described

---

## Summary

**Total Tasks**: 6
**Estimated Complexity**: Low
**Dependencies**: All epics (documents implemented features)

**Critical Path**:
- DOC-01 is the foundation (main README structure)
- DOC-02, DOC-03, DOC-04, DOC-05, DOC-06 can be done in parallel (each documents a specific area)
- All depend on completed implementation (can't document features that don't exist)
