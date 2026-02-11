<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

/**
 * External Header Acceptance Tests
 *
 * Tests the accept_external_headers configuration toggle that controls
 * whether incoming tracing headers from external services are trusted
 * and used, or ignored in favor of generating fresh values.
 */
describe('External Header Acceptance', function () {
    beforeEach(function () {
        config([
            'laravel-tracing.enabled' => true,
            'laravel-tracing.accept_external_headers' => true,
        ]);

        Route::middleware('web')->get('/test', function () {
            return response()->json([
                'correlation_id' => LaravelTracing::correlationId(),
                'request_id' => LaravelTracing::requestId(),
            ]);
        });
    });

    it('accepts external correlation ID header when enabled', function () {
        $externalCorrelationId = '11111111-2222-3333-4444-555555555555';

        $response = $this->get('/test', [
            'X-Correlation-Id' => $externalCorrelationId,
        ]);

        $response->assertOk();
        $response->assertHeader('X-Correlation-Id', $externalCorrelationId);

        $data = $response->json();
        expect($data['correlation_id'])->toBe($externalCorrelationId);
    });

    it('accepts external request ID header when enabled', function () {
        $externalRequestId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $response = $this->get('/test', [
            'X-Request-Id' => $externalRequestId,
        ]);

        $response->assertOk();
        $response->assertHeader('X-Request-Id', $externalRequestId);

        $data = $response->json();
        expect($data['request_id'])->toBe($externalRequestId);
    });

    it('accepts both external headers when enabled', function () {
        $externalCorrelationId = '11111111-2222-3333-4444-555555555555';
        $externalRequestId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $response = $this->get('/test', [
            'X-Correlation-Id' => $externalCorrelationId,
            'X-Request-Id' => $externalRequestId,
        ]);

        $response->assertOk();
        $response->assertHeader('X-Correlation-Id', $externalCorrelationId);
        $response->assertHeader('X-Request-Id', $externalRequestId);
    });

    it('rejects external headers when accept_external_headers is disabled', function () {
        config(['laravel-tracing.accept_external_headers' => false]);

        $externalCorrelationId = '11111111-2222-3333-4444-555555555555';
        $externalRequestId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $response = $this->get('/test', [
            'X-Correlation-Id' => $externalCorrelationId,
            'X-Request-Id' => $externalRequestId,
        ]);

        $response->assertOk();

        // Headers should NOT match the external values (package generated its own)
        $actualCorrelationId = $response->headers->get('X-Correlation-Id');
        $actualRequestId = $response->headers->get('X-Request-Id');

        expect($actualCorrelationId)->not->toBe($externalCorrelationId);
        expect($actualRequestId)->not->toBe($externalRequestId);

        // Should still be valid UUIDs though
        expect($actualCorrelationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
        expect($actualRequestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('sanitizes and rejects invalid external correlation ID header', function () {
        $invalidCorrelationId = '<script>alert("xss")</script>';

        $response = $this->get('/test', [
            'X-Correlation-Id' => $invalidCorrelationId,
        ]);

        $response->assertOk();

        // Should generate new ID instead of using invalid one
        $actualCorrelationId = $response->headers->get('X-Correlation-Id');
        expect($actualCorrelationId)->not->toBe($invalidCorrelationId);
        expect($actualCorrelationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('sanitizes and rejects invalid external request ID header', function () {
        $invalidRequestId = 'invalid!@#$%^&*()';

        $response = $this->get('/test', [
            'X-Request-Id' => $invalidRequestId,
        ]);

        $response->assertOk();

        // Should generate new ID instead of using invalid one
        $actualRequestId = $response->headers->get('X-Request-Id');
        expect($actualRequestId)->not->toBe($invalidRequestId);
        expect($actualRequestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('persists accepted external correlation ID in session', function () {
        $externalCorrelationId = '11111111-2222-3333-4444-555555555555';

        // First request with external header
        $response1 = $this->get('/test', [
            'X-Correlation-Id' => $externalCorrelationId,
        ]);

        $response1->assertHeader('X-Correlation-Id', $externalCorrelationId);

        // Second request without header - should reuse from session
        $response2 = $this->get('/test');

        $response2->assertHeader('X-Correlation-Id', $externalCorrelationId);
    });

    it('does not persist request ID across requests', function () {
        $externalRequestId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        // First request with external header
        $response1 = $this->get('/test', [
            'X-Request-Id' => $externalRequestId,
        ]);

        $response1->assertHeader('X-Request-Id', $externalRequestId);

        // Second request without header - should generate new request ID
        $response2 = $this->get('/test');

        $actualRequestId = $response2->headers->get('X-Request-Id');
        expect($actualRequestId)->not->toBe($externalRequestId);
        expect($actualRequestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });
});
