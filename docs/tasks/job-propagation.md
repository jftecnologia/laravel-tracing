# Job Propagation

**Epic ID**: JP
**Epic Goal**: Propagate tracing values from request context to queued jobs and restore them during job execution.

**Requirements Addressed**:
- FR-06: Serialize tracing values and attach to job payload
- FR-07: Restore tracing values in job execution context with original request ID preserved

**Architecture Areas**:
- TracingJobDispatcher (job event listener)
- Job payload serialization
- Job execution restoration
- JobQueuing and JobProcessing events

---

## Tasks

### JP-01 Implement TracingJobDispatcher

**Description**:
Implement the component that listens to Laravel's job lifecycle events and handles tracing propagation. The dispatcher listens to two events: `JobQueuing` (before job is queued) and `JobProcessing` (before job executes). On queuing, it serializes all current tracing values and attaches them to the job payload. On processing, it restores the values and injects them into `TracingManager`.

This component enables full tracing continuity across asynchronous job execution. All tracing values from the dispatching request context (correlation ID, request ID, custom tracings) are preserved and accessible within the job handler.

**Acceptance Criteria**:
- [ ] AC-1: Constructor accepts `TracingManager` instance (injected from container)
- [ ] AC-2: Listens to `Illuminate\Queue\Events\JobQueuing` event
- [ ] AC-3: Listens to `Illuminate\Queue\Events\JobProcessing` event
- [ ] AC-4: `handleJobQueuing()` method reads all tracings from manager and attaches to job payload
- [ ] AC-5: `handleJobProcessing()` method reads tracings from job payload and restores to manager
- [ ] AC-6: Tracing data is stored in job payload under `tracings` key
- [ ] AC-7: Original request ID is preserved (not regenerated) during restoration

**Technical Notes**:
- Events are fired by Laravel's queue system automatically
- Job payload is serialized as simple associative array
- Restoration happens before job's `handle()` method executes
- Manager must support restoration method for injecting values

**Files to Create/Modify**:
- `src/Jobs/TracingJobDispatcher.php`

**Testing**:
- [ ] Unit test: handleJobQueuing() reads tracings and attaches to payload
- [ ] Unit test: handleJobProcessing() restores tracings from payload
- [ ] Unit test: original request ID is preserved
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### JP-02 Register job event listeners

**Description**:
Register the job event listeners in the service provider so the dispatcher can intercept job lifecycle events. The listeners must be registered in the `boot()` method and respect the global enable toggle (skip registration if package is disabled).

Use Laravel's event system to bind the dispatcher methods to `JobQueuing` and `JobProcessing` events. The listeners should be registered globally to apply to all queued jobs.

**Acceptance Criteria**:
- [ ] AC-1: Register `JobQueuing` event listener in `LaravelTracingServiceProvider::boot()`
- [ ] AC-2: Register `JobProcessing` event listener in `LaravelTracingServiceProvider::boot()`
- [ ] AC-3: Listeners are not registered when package is disabled
- [ ] AC-4: Dispatcher is instantiated from container (dependency injection)
- [ ] AC-5: Events are registered using Laravel's `Event::listen()` or `app('events')->listen()`

**Technical Notes**:
- Event registration: `Event::listen(JobQueuing::class, [TracingJobDispatcher::class, 'handleJobQueuing'])`
- Dispatcher is resolved from container for each event
- Listeners apply to all queued jobs globally

**Files to Create/Modify**:
- `src/LaravelTracingServiceProvider.php` (update boot() method)

**Testing**:
- [ ] Feature test: job events are registered and triggered
- [ ] Feature test: listeners are not registered when package is disabled
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### JP-03 Implement job payload serialization

**Description**:
Implement the serialization logic that converts all current tracing values into a simple associative array and attaches it to the job payload. The serialization must be lightweight (strings only) and compatible with all Laravel queue drivers (database, redis, sync, etc.).

Read all tracings from `TracingManager::all()` and store as `job->tracings = [...]`. The payload must be serializable without any object references or closures.

**Acceptance Criteria**:
- [ ] AC-1: Serialization reads all tracings from `TracingManager::all()`
- [ ] AC-2: Tracings are serialized as associative array: `['correlation_id' => 'abc', 'request_id' => 'xyz']`
- [ ] AC-3: Serialized data is attached to job payload under `tracings` key
- [ ] AC-4: Serialization includes all active tracings (correlation ID, request ID, custom tracings)
- [ ] AC-5: Only string values are serialized (no objects, closures, or resources)
- [ ] AC-6: Serialization is compatible with all Laravel queue drivers

**Technical Notes**:
- Job payload access: `$event->job->payload()` or custom property
- Tracings are already strings (from TracingSource::resolve())
- No transformation needed (simple array copy)

**Files to Create/Modify**:
- `src/Jobs/TracingJobDispatcher.php` (implement handleJobQueuing() logic)

**Testing**:
- [ ] Unit test: all tracings are serialized to job payload
- [ ] Unit test: serialized data is simple associative array
- [ ] Feature test: job payload contains tracings after dispatch
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### JP-04 Implement job execution restoration

**Description**:
Implement the restoration logic that reads tracing values from the job payload and injects them back into `TracingManager` before the job executes. The restored values must be accessible via the same global accessor (`LaravelTracing::all()`) within the job handler.

The restoration must preserve the original request ID (not regenerate it). This allows tracing all jobs back to the request that dispatched them. The restoration happens in the `JobProcessing` event, which fires before the job's `handle()` method.

**Acceptance Criteria**:
- [ ] AC-1: Restoration reads tracings from job payload `tracings` key
- [ ] AC-2: Tracings are injected into `TracingManager` (stored in `RequestStorage`)
- [ ] AC-3: Original request ID is preserved (not regenerated)
- [ ] AC-4: All custom tracings are restored
- [ ] AC-5: Restored values are accessible via `LaravelTracing::all()` in job handler
- [ ] AC-6: Restoration happens before job's `handle()` method executes
- [ ] AC-7: If job payload has no tracings, restoration is skipped gracefully

**Technical Notes**:
- Manager needs `restore(array $tracings)` method to inject values
- Restoration overwrites any existing values in `RequestStorage`
- Job context has no HTTP request, so `resolveAll()` is not called
- Sources' `restoreFromJob()` method allows custom transformation

**Files to Create/Modify**:
- `src/Jobs/TracingJobDispatcher.php` (implement handleJobProcessing() logic)
- `src/Tracings/TracingManager.php` (add restore() method if needed)

**Testing**:
- [ ] Unit test: restoration injects tracings into manager
- [ ] Unit test: original request ID is preserved
- [ ] Feature test: tracings are accessible in job handler
- [ ] Feature test: job payload without tracings is handled gracefully
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

### JP-05 Write job propagation tests

**Description**:
Create comprehensive integration tests for the entire job propagation lifecycle. Test the full flow from job dispatch in a request context to job execution in a worker context. Verify that all tracing values are propagated correctly, that the original request ID is preserved, and that custom tracings work as expected.

Use Orchestra Testbench to simulate job dispatch and execution. Create test jobs that access tracing values in their `handle()` methods and assert the values match the dispatching context.

**Acceptance Criteria**:
- [ ] AC-1: Test job dispatched from request context carries all tracings in payload
- [ ] AC-2: Test job execution restores all tracings from payload
- [ ] AC-3: Test original request ID is preserved in job execution
- [ ] AC-4: Test correlation ID is preserved in job execution
- [ ] AC-5: Test custom tracings are propagated to jobs
- [ ] AC-6: Test `LaravelTracing::all()` works inside job handler
- [ ] AC-7: Test multiple jobs dispatched from same request share same correlation ID
- [ ] AC-8: Test job propagation is disabled when package is disabled

**Technical Notes**:
- Use sync queue driver for synchronous testing
- Create test job classes that capture tracing values
- Assert tracing values in job match dispatching context

**Files to Create/Modify**:
- `tests/Feature/JobPropagationTest.php`
- `tests/Fixtures/TestJob.php` (test job class)

**Testing**:
- [ ] All job propagation tests pass
- [ ] `composer lint` passes
- [ ] `composer test` passes

---

## Summary

**Total Tasks**: 5
**Estimated Complexity**: Medium
**Dependencies**: SPB (service provider with TracingManager registered)

**Critical Path**:
- JP-01 depends on CTI-04 (uses TracingManager)
- JP-02 depends on JP-01 (registers dispatcher in provider) and SPB-01 (provider exists)
- JP-03 depends on JP-01 (implements serialization in dispatcher)
- JP-04 depends on JP-01 (implements restoration in dispatcher)
- JP-05 depends on JP-01 through JP-04 (tests all job features)
