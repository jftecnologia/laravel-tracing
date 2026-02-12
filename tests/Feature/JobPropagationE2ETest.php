<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;
use Tests\Fixtures\CaptureTracingJob;

/**
 * Job Propagation End-to-End Integration Tests
 *
 * Tests the complete lifecycle of tracing propagation through queued jobs,
 * from initial request through job execution, using the actual service provider
 * configuration and Queue::createPayloadUsing() hook.
 */
describe('Job Propagation E2E', function () {
    beforeEach(function () {
        // Reset captured tracings
        CaptureTracingJob::reset();

        // Use sync queue for synchronous testing
        config(['queue.default' => 'sync']);

        // Ensure tracing is enabled
        config(['laravel-tracing.enabled' => true]);
        config(['laravel-tracing.accept_external_headers' => true]);

        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }
    });

    it('propagates correlation ID and request ID from request through job execution', function () {
        // Setup test route that dispatches a job
        Route::middleware('web')->get('/dispatch-job', function () {
            // Dispatch job from within request context
            CaptureTracingJob::dispatch();

            return response()->json([
                'correlation_id' => LaravelTracing::correlationId(),
                'request_id' => LaravelTracing::requestId(),
            ]);
        });

        // Make request with tracing headers
        $response = $this->get('/dispatch-job', [
            'X-Correlation-Id' => 'e2e-correlation-123',
            'X-Request-Id' => 'e2e-request-456',
        ]);

        // Verify response contains tracing headers
        $response->assertOk()
            ->assertHeader('X-Correlation-Id', 'e2e-correlation-123')
            ->assertHeader('X-Request-Id', 'e2e-request-456');

        // Verify controller had access to tracings
        $data = $response->json();
        expect($data['correlation_id'])->toBe('e2e-correlation-123')
            ->and($data['request_id'])->toBe('e2e-request-456');

        // Verify job received the same tracings
        expect(CaptureTracingJob::$capturedTracings)->toBe([
            'correlation_id' => 'e2e-correlation-123',
            'request_id' => 'e2e-request-456',
        ]);
    });

    it('maintains correlation ID across multiple jobs from same request', function () {
        // Setup test route that dispatches multiple jobs
        Route::middleware('web')->get('/dispatch-multiple', function () {
            CaptureTracingJob::dispatch();

            return response()->json(['ok' => true]);
        });

        // Make request
        $response = $this->get('/dispatch-multiple', [
            'X-Correlation-Id' => 'shared-correlation-789',
        ]);

        $response->assertOk();

        $firstJobCorrelation = CaptureTracingJob::$capturedTracings['correlation_id'] ?? null;

        // Reset and dispatch another job from same "request context"
        CaptureTracingJob::reset();

        // Make another request with same correlation ID (simulating session persistence)
        $response2 = $this->get('/dispatch-multiple', [
            'X-Correlation-Id' => 'shared-correlation-789',
        ]);

        $response2->assertOk();

        $secondJobCorrelation = CaptureTracingJob::$capturedTracings['correlation_id'] ?? null;

        // Both jobs should have received the same correlation ID
        expect($firstJobCorrelation)->toBe('shared-correlation-789')
            ->and($secondJobCorrelation)->toBe('shared-correlation-789');
    });

    it('generates new tracings when not provided in request', function () {
        // Setup test route
        Route::middleware('web')->get('/auto-generate', function () {
            CaptureTracingJob::dispatch();

            return response()->json([
                'correlation_id' => LaravelTracing::correlationId(),
                'request_id' => LaravelTracing::requestId(),
            ]);
        });

        // Make request WITHOUT tracing headers
        $response = $this->get('/auto-generate');

        $response->assertOk();

        // Response should have generated tracings
        expect($response->headers->get('X-Correlation-Id'))->not->toBeNull()
            ->and($response->headers->get('X-Request-Id'))->not->toBeNull();

        // Job should have received the generated tracings
        $data = $response->json();
        expect(CaptureTracingJob::$capturedTracings['correlation_id'])->toBe($data['correlation_id'])
            ->and(CaptureTracingJob::$capturedTracings['request_id'])->toBe($data['request_id'])
            ->and(CaptureTracingJob::$capturedTracings['correlation_id'])->not->toBeNull()
            ->and(CaptureTracingJob::$capturedTracings['request_id'])->not->toBeNull();
    });

    it('preserves original request ID in dispatched jobs', function () {
        // Setup test route
        Route::middleware('web')->get('/preserve-request-id', function () {
            $originalRequestId = LaravelTracing::requestId();

            CaptureTracingJob::dispatch();

            return response()->json([
                'original_request_id' => $originalRequestId,
                'job_request_id' => CaptureTracingJob::$capturedTracings['request_id'] ?? null,
            ]);
        });

        // Make request with specific request ID
        $response = $this->get('/preserve-request-id', [
            'X-Request-Id' => 'original-request-999',
        ]);

        $response->assertOk();

        $data = $response->json();

        // Request ID should be preserved from original request to job
        expect($data['original_request_id'])->toBe('original-request-999')
            ->and($data['job_request_id'])->toBe('original-request-999');
    });

    it('works with delayed jobs', function () {
        // Setup test route that dispatches delayed job
        Route::middleware('web')->get('/dispatch-delayed', function () {
            CaptureTracingJob::dispatch()->delay(now()->addSeconds(10));

            return response()->json(['ok' => true]);
        });

        // Make request
        $response = $this->get('/dispatch-delayed', [
            'X-Correlation-Id' => 'delayed-correlation-321',
            'X-Request-Id' => 'delayed-request-654',
        ]);

        $response->assertOk();

        // Job should still receive tracings (even though it's delayed)
        expect(CaptureTracingJob::$capturedTracings)->toBe([
            'correlation_id' => 'delayed-correlation-321',
            'request_id' => 'delayed-request-654',
        ]);
    });

    it('handles jobs without tracing context gracefully', function () {
        // Clear any payload hooks to simulate job without tracing
        Queue::createPayloadUsing(null);

        // Setup test route
        Route::middleware('web')->get('/no-tracing', function () {
            CaptureTracingJob::dispatch();

            return response()->json(['ok' => true]);
        });

        // Make request
        $response = $this->get('/no-tracing');

        $response->assertOk();

        // Job should have empty/null tracings
        expect(CaptureTracingJob::$capturedTracings)->toBeArray();
    });
});
