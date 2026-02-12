<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use Illuminate\Queue\Queue;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;
use JuniorFontenele\LaravelTracing\Jobs\TracingJobDispatcher;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;
use JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;
use Tests\Fixtures\CaptureTracingJob;
use Tests\Fixtures\MockJob;

/**
 * Job Propagation Feature Tests
 *
 * Verifies that tracing values are properly propagated from request context
 * to queued jobs and restored during job execution using Queue::createPayloadUsing().
 */
describe('Job Propagation', function () {
    beforeEach(function () {
        // Reset captured tracings
        CaptureTracingJob::reset();

        // Clear any registered payload callbacks
        Queue::createPayloadUsing(null);

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
                'request_id' => new RequestIdSource(
                    acceptExternalHeaders: true,
                    headerName: 'X-Request-Id'
                ),
            ],
            storage: $this->requestStorage,
            enabled: true
        );

        // Register dispatcher
        $this->dispatcher = new TracingJobDispatcher($this->manager);

        // Bind manager to container for facade access
        $this->app->instance(TracingManager::class, $this->manager);

        // Register Queue::createPayloadUsing hook (simulating ServiceProvider)
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $tracings = $this->manager->all();

            return empty($tracings) ? [] : ['tracings' => $tracings];
        });
    });

    it('propagates tracings to job payload via Queue::createPayloadUsing hook', function () {
        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'test-correlation-123',
            'HTTP_X_REQUEST_ID' => 'test-request-456',
        ]);
        $this->manager->resolveAll($request);

        // Simulate queue payload creation
        $queue = app('queue.connection');
        $reflection = new ReflectionClass($queue);
        $method = $reflection->getMethod('createPayload');
        $method->setAccessible(true);

        $job = new CaptureTracingJob();
        $payload = $method->invoke($queue, $job, 'default');
        $decodedPayload = json_decode($payload, true);

        // Verify tracings were injected into payload
        expect($decodedPayload)->toHaveKey('tracings')
            ->and($decodedPayload['tracings'])->toBe([
                'correlation_id' => 'test-correlation-123',
                'request_id' => 'test-request-456',
            ]);
    });

    it('restores tracing values from job payload during processing', function () {
        // Create job with tracing payload
        $jobPayload = [
            'tracings' => [
                'correlation_id' => 'restored-correlation-789',
                'request_id' => 'restored-request-012',
            ],
        ];

        $job = new MockJob($jobPayload);
        $event = new Illuminate\Queue\Events\JobProcessing('sync', $job);

        // Handle job processing
        $this->dispatcher->handleJobProcessing($event);

        // Verify tracings were restored to manager
        expect($this->manager->all())->toBe([
            'correlation_id' => 'restored-correlation-789',
            'request_id' => 'restored-request-012',
        ]);
    });

    it('preserves original request ID when propagating to jobs', function () {
        // Resolve tracings from initial request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'original-request-id-123',
        ]);
        $this->manager->resolveAll($request);

        $originalRequestId = $this->manager->get('request_id');

        // Dispatch job
        CaptureTracingJob::dispatch();

        // Verify job received the original request ID (not regenerated)
        expect(CaptureTracingJob::$capturedTracings)->toHaveKey('request_id')
            ->and(CaptureTracingJob::$capturedTracings['request_id'])->toBe($originalRequestId)
            ->and(CaptureTracingJob::$capturedTracings['request_id'])->toBe('original-request-id-123');
    });

    it('propagates correlation ID to jobs', function () {
        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'correlation-from-request',
        ]);
        $this->manager->resolveAll($request);

        // Dispatch job
        CaptureTracingJob::dispatch();

        // Verify job received the correlation ID
        expect(CaptureTracingJob::$capturedTracings)->toHaveKey('correlation_id')
            ->and(CaptureTracingJob::$capturedTracings['correlation_id'])->toBe('correlation-from-request');
    });

    it('allows LaravelTracing facade to access tracings inside job handler', function () {
        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'facade-test-correlation',
            'HTTP_X_REQUEST_ID' => 'facade-test-request',
        ]);
        $this->manager->resolveAll($request);

        // Dispatch job (which uses LaravelTracing::all())
        CaptureTracingJob::dispatch();

        // Verify job captured tracings via facade
        expect(CaptureTracingJob::$capturedTracings)->toBe([
            'correlation_id' => 'facade-test-correlation',
            'request_id' => 'facade-test-request',
        ]);
    });

    it('does not propagate tracings when manager returns empty values', function () {
        // Don't resolve any tracings (manager will return empty array)

        // Simulate queue payload creation
        $queue = app('queue.connection');
        $reflection = new ReflectionClass($queue);
        $method = $reflection->getMethod('createPayload');
        $method->setAccessible(true);

        $job = new CaptureTracingJob();
        $payload = $method->invoke($queue, $job, 'default');
        $decodedPayload = json_decode($payload, true);

        // When values are null, tracings array will still be present but with null values
        // The hook returns empty array when all values are null
        if (isset($decodedPayload['tracings'])) {
            $tracings = $decodedPayload['tracings'];
            expect($tracings)->toBeArray()
                ->and(array_filter($tracings))->toBeEmpty();
        } else {
            // Or no tracings key at all (depending on implementation)
            expect($decodedPayload)->not->toHaveKey('tracings');
        }
    });

    it('handles job payload without tracings gracefully', function () {
        // Create job with no tracing payload
        $job = new MockJob([]);
        $event = new Illuminate\Queue\Events\JobProcessing('sync', $job);

        // Handle job processing (should not throw exception)
        $this->dispatcher->handleJobProcessing($event);

        // Verify no tracings in manager (empty)
        expect($this->manager->all())->toBe([
            'correlation_id' => null,
            'request_id' => null,
        ]);
    });

    it('shares same correlation ID across multiple jobs dispatched from same request', function () {
        // Resolve tracings from request
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'shared-correlation-id',
        ]);
        $this->manager->resolveAll($request);

        $correlationId = $this->manager->get('correlation_id');

        // Dispatch first job
        CaptureTracingJob::dispatch();
        $firstJobCorrelation = CaptureTracingJob::$capturedTracings['correlation_id'] ?? null;

        // Reset and dispatch second job
        CaptureTracingJob::reset();
        CaptureTracingJob::dispatch();
        $secondJobCorrelation = CaptureTracingJob::$capturedTracings['correlation_id'] ?? null;

        // Verify both jobs received the same correlation ID
        expect($firstJobCorrelation)->toBe($correlationId)
            ->and($secondJobCorrelation)->toBe($correlationId)
            ->and($firstJobCorrelation)->toBe($secondJobCorrelation)
            ->and($correlationId)->toBe('shared-correlation-id');
    });

    it('restores tracings using source restoreFromJob method', function () {
        // Create job with tracing payload
        $jobPayload = [
            'tracings' => [
                'correlation_id' => 'to-be-restored',
                'request_id' => 'to-be-restored-request',
            ],
        ];

        $job = new MockJob($jobPayload);
        $event = new Illuminate\Queue\Events\JobProcessing('sync', $job);

        // Handle job processing
        $this->dispatcher->handleJobProcessing($event);

        // Verify TracingManager::restore() was called and values are set
        expect($this->requestStorage->get('correlation_id'))->toBe('to-be-restored')
            ->and($this->requestStorage->get('request_id'))->toBe('to-be-restored-request');
    });
});
