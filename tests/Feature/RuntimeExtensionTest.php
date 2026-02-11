<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelTracing\Jobs\TracingJobDispatcher;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;
use Tests\Fixtures\CaptureTracingJob;
use Tests\Fixtures\CustomTracingSource;

/**
 * Runtime Extension Feature Tests
 *
 * Verifies that custom tracing sources registered at runtime via extend()
 * are properly resolved, stored, and propagated across all integration points
 * (HTTP responses, queued jobs, HTTP client).
 */
describe('Runtime Extension', function () {
    beforeEach(function () {
        // Reset captured tracings
        CaptureTracingJob::reset();

        // Use sync queue for synchronous testing
        config(['queue.default' => 'sync']);

        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        // Setup tracing infrastructure
        $this->requestStorage = new RequestStorage();
        $this->sessionStorage = new SessionStorage();

        $this->manager = new TracingManager(
            sources: [
                'correlation_id' => new CorrelationIdSource(
                    sessionStorage: $this->sessionStorage,
                    acceptExternalHeaders: true,
                    headerName: 'X-Correlation-Id'
                ),
            ],
            storage: $this->requestStorage,
            enabled: true
        );

        // Register dispatcher
        $this->dispatcher = new TracingJobDispatcher($this->manager);

        // Bind manager to container for facade access
        $this->app->instance(TracingManager::class, $this->manager);
    });

    it('resolves extended sources from request headers', function () {
        // Extend manager with custom source
        $customSource = new CustomTracingSource('X-Custom-Trace');
        $this->manager->extend('custom_trace', $customSource);

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'test-correlation-123',
            'HTTP_X_CUSTOM_TRACE' => 'test-custom-456',
        ]);
        $this->manager->resolveAll($request);

        // Verify both built-in and extended sources are resolved
        expect($this->manager->get('correlation_id'))->toBe('test-correlation-123')
            ->and($this->manager->get('custom_trace'))->toBe('test-custom-456');
    });

    it('includes extended sources in all()', function () {
        // Extend manager with custom source
        $customSource = new CustomTracingSource('X-Custom-Trace');
        $this->manager->extend('custom_trace', $customSource);

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'test-correlation-123',
            'HTTP_X_CUSTOM_TRACE' => 'test-custom-456',
        ]);
        $this->manager->resolveAll($request);

        $all = $this->manager->all();

        expect($all)->toHaveKey('correlation_id')
            ->and($all)->toHaveKey('custom_trace')
            ->and($all['correlation_id'])->toBe('test-correlation-123')
            ->and($all['custom_trace'])->toBe('test-custom-456');
    });

    it('propagates extended sources to queued jobs', function () {
        // Extend manager with custom source
        $customSource = new CustomTracingSource('X-Custom-Trace');
        $this->manager->extend('custom_trace', $customSource);

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'test-correlation-123',
            'HTTP_X_CUSTOM_TRACE' => 'test-custom-456',
        ]);
        $this->manager->resolveAll($request);

        // Create job queuing event
        $job = new CaptureTracingJob();
        $event = new class ('sync', null, $job, '{"data":"test"}', null) extends Illuminate\Queue\Events\JobQueueing
        {
            public function payload(): array
            {
                return json_decode($this->payload, true);
            }
        };

        // Handle job queuing
        $this->dispatcher->handleJobQueueing($event);

        // Verify custom tracing was added to payload
        $payload = $event->payload();
        expect($payload)->toHaveKey('tracings')
            ->and($payload['tracings'])->toHaveKey('correlation_id')
            ->and($payload['tracings'])->toHaveKey('custom_trace')
            ->and($payload['tracings']['custom_trace'])->toBe('test-custom-456');
    });

    it('attaches extended sources to HTTP responses', function () {
        // Extend manager with custom source
        $customSource = new CustomTracingSource('X-Custom-Trace');
        $this->manager->extend('custom_trace', $customSource);

        // Setup test route
        Route::middleware('web')->get('/test-custom', function () use ($customSource) {
            // Extend within request cycle (simulating service provider boot)
            app(TracingManager::class)->extend('custom_trace', $customSource);

            return response()->json(['ok' => true]);
        });

        // Make request with custom header
        $response = $this->get('/test-custom', [
            'X-Correlation-Id' => 'test-correlation-123',
            'X-Custom-Trace' => 'test-custom-456',
        ]);

        // Verify both headers are in response
        $response->assertHeader('X-Correlation-Id', 'test-correlation-123')
            ->assertHeader('X-Custom-Trace', 'test-custom-456');
    });

    it('attaches extended sources to outgoing HTTP requests with withTracing()', function () {
        // Extend manager with custom source
        $customSource = new CustomTracingSource('X-Custom-Trace');
        $this->manager->extend('custom_trace', $customSource);

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'test-correlation-123',
            'HTTP_X_CUSTOM_TRACE' => 'test-custom-456',
        ]);
        $this->manager->resolveAll($request);

        // Fake HTTP client
        Http::fake();

        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Verify both built-in and extended headers were sent
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Correlation-Id', 'test-correlation-123')
                && $request->hasHeader('X-Custom-Trace', 'test-custom-456');
        });
    });

    it('supports method chaining for multiple extensions', function () {
        // Extend manager with multiple custom sources using chaining
        $customSource1 = new CustomTracingSource('X-Custom-1');
        $customSource2 = new CustomTracingSource('X-Custom-2');

        $result = $this->manager
            ->extend('custom_1', $customSource1)
            ->extend('custom_2', $customSource2);

        // Verify chaining returns manager instance
        expect($result)->toBe($this->manager);

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CUSTOM_1' => 'value-1',
            'HTTP_X_CUSTOM_2' => 'value-2',
        ]);
        $this->manager->resolveAll($request);

        // Verify both custom sources are resolved
        expect($this->manager->get('custom_1'))->toBe('value-1')
            ->and($this->manager->get('custom_2'))->toBe('value-2');
    });

    it('allows extending sources in service provider boot method', function () {
        // Simulate service provider extending the manager
        $this->app->booted(function () {
            $manager = app(TracingManager::class);
            $customSource = new CustomTracingSource('X-User-Id');
            $manager->extend('user_id', $customSource);
        });

        // Trigger boot
        $this->app->boot();

        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_USER_ID' => 'user-123',
        ]);
        $this->manager->resolveAll($request);

        // Verify custom source was registered and resolved
        expect($this->manager->get('user_id'))->toBe('user-123');
    });
});
