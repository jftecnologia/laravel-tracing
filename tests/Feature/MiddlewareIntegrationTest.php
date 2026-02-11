<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

/**
 * Middleware Integration Tests
 *
 * Tests the complete middleware lifecycle from incoming request resolution
 * to outgoing response header attachment. Verifies middleware execution order,
 * tracing value propagation, and configuration toggles.
 */
describe('Middleware Integration', function () {
    beforeEach(function () {
        // Ensure package is enabled by default
        config(['laravel-tracing.enabled' => true]);

        // Register a test route within web middleware group
        Route::middleware('web')->get('/test', function () {
            return response()->json([
                'correlation_id' => LaravelTracing::correlationId(),
                'request_id' => LaravelTracing::requestId(),
            ]);
        });
    });

    it('executes full request lifecycle with incoming and outgoing middleware', function () {
        $response = $this->get('/test');

        $response->assertOk();
        $response->assertHeader('X-Correlation-Id');
        $response->assertHeader('X-Request-Id');
    });

    it('attaches correlation ID and request ID headers to response', function () {
        $response = $this->get('/test');

        $correlationId = $response->headers->get('X-Correlation-Id');
        $requestId = $response->headers->get('X-Request-Id');

        expect($correlationId)->toBeString()
            ->and($correlationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        expect($requestId)->toBeString()
            ->and($requestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('makes tracings accessible via LaravelTracing facade during request', function () {
        $response = $this->get('/test');

        $data = $response->json();

        expect($data['correlation_id'])->toBeString()
            ->and($data['correlation_id'])->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        expect($data['request_id'])->toBeString()
            ->and($data['request_id'])->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('skips middleware execution when package is globally disabled', function () {
        config(['laravel-tracing.enabled' => false]);

        $response = $this->get('/test');

        $response->assertOk();
        $response->assertHeaderMissing('X-Correlation-Id');
        $response->assertHeaderMissing('X-Request-Id');

        $data = $response->json();
        expect($data['correlation_id'])->toBeNull();
        expect($data['request_id'])->toBeNull();
    });

    it('respects per-tracing disabled toggles', function () {
        config([
            'laravel-tracing.tracings.correlation_id.enabled' => false,
            'laravel-tracing.tracings.request_id.enabled' => true,
        ]);

        $response = $this->get('/test');

        $response->assertOk();
        $response->assertHeaderMissing('X-Correlation-Id');
        $response->assertHeader('X-Request-Id');
    });

    it('executes incoming middleware before controller and outgoing after controller', function () {
        $executionOrder = [];

        Route::middleware('web')->get('/test-order', function () use (&$executionOrder) {
            $executionOrder[] = 'controller';

            // Tracings should already be resolved by incoming middleware
            $correlationId = LaravelTracing::correlationId();
            expect($correlationId)->toBeString();

            return response('ok');
        });

        $response = $this->get('/test-order');

        // If incoming middleware ran first, tracings would be available in controller
        // If outgoing middleware ran last, headers would be attached to response
        $response->assertOk();
        $response->assertHeader('X-Correlation-Id');
        expect($executionOrder)->toBe(['controller']);
    });

    it('generates new correlation ID when none exists', function () {
        $response = $this->get('/test');

        $correlationId = $response->headers->get('X-Correlation-Id');

        expect($correlationId)->toBeString()
            ->and($correlationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('generates unique request ID for each request', function () {
        $response1 = $this->get('/test');
        $response2 = $this->get('/test');

        $requestId1 = $response1->headers->get('X-Request-Id');
        $requestId2 = $response2->headers->get('X-Request-Id');

        expect($requestId1)->toBeString()
            ->and($requestId2)->toBeString()
            ->and($requestId1)->not->toBe($requestId2);
    });

    it('persists correlation ID in session across multiple requests', function () {
        $response1 = $this->get('/test');
        $correlationId1 = $response1->headers->get('X-Correlation-Id');

        $response2 = $this->get('/test');
        $correlationId2 = $response2->headers->get('X-Correlation-Id');

        expect($correlationId1)->toBe($correlationId2);
    });
});
