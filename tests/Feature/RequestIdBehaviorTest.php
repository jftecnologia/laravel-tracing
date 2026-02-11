<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;
use JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource;

/**
 * Request ID Behavior Feature Tests
 *
 * These tests verify that request IDs behave differently from correlation IDs:
 * - Request IDs are unique per request (not persisted in session)
 * - Correlation IDs persist across requests in same session
 * - Request IDs are preserved when restored from job payloads
 */
describe('Request ID Behavior', function () {
    beforeEach(function () {
        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        $this->sessionStorage = new SessionStorage();

        $this->requestIdSource = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $this->correlationIdSource = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );
    });

    it('generates unique request ID for each HTTP request', function () {
        $request1 = Request::create('/');
        $request2 = Request::create('/');
        $request3 = Request::create('/');

        $requestId1 = $this->requestIdSource->resolve($request1);
        $requestId2 = $this->requestIdSource->resolve($request2);
        $requestId3 = $this->requestIdSource->resolve($request3);

        // All request IDs should be unique
        expect($requestId1)->not->toBe($requestId2)
            ->and($requestId2)->not->toBe($requestId3)
            ->and($requestId1)->not->toBe($requestId3)
            ->and($requestId1)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/')
            ->and($requestId2)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/')
            ->and($requestId3)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('uses external header when accept_external_headers is enabled', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: true,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-request-123',
        ]);

        $requestId = $source->resolve($request);

        expect($requestId)->toBe('external-request-123');
    });

    it('ignores external header when accept_external_headers is disabled', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-request-456',
        ]);

        $requestId = $source->resolve($request);

        // Should generate new UUID instead of using external header
        expect($requestId)->not->toBe('external-request-456')
            ->and($requestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('rejects invalid external header and generates new UUID', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: true,
            headerName: 'X-Request-Id'
        );

        // Invalid header with special characters
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'invalid!@#$%',
        ]);

        $requestId = $source->resolve($request);

        // Sanitizer should reject invalid header, generate new UUID
        expect($requestId)->not->toBe('invalid!@#$%')
            ->and($requestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('generates different request IDs across multiple requests in same session', function () {
        // Make multiple requests in the same session
        $request1 = Request::create('/');
        $requestId1 = $this->requestIdSource->resolve($request1);

        $request2 = Request::create('/');
        $requestId2 = $this->requestIdSource->resolve($request2);

        $request3 = Request::create('/');
        $requestId3 = $this->requestIdSource->resolve($request3);

        // Request IDs should be unique even in same session
        expect($requestId1)->not->toBe($requestId2)
            ->and($requestId2)->not->toBe($requestId3)
            ->and($requestId1)->not->toBe($requestId3);

        // Verify session was not modified (request IDs not persisted)
        expect(session('laravel_tracing.request_id'))->toBeNull();
    });

    it('preserves request ID when restored from job payload', function () {
        $originalRequestId = 'original-request-789';

        // Simulate restoring request ID from job payload
        $restoredRequestId = $this->requestIdSource->restoreFromJob($originalRequestId);

        // Original request ID should be preserved
        expect($restoredRequestId)->toBe($originalRequestId);
    });

    it('correlation ID persists while request ID changes (integration test)', function () {
        // First request - generates both correlation ID and request ID
        $request1 = Request::create('/');
        $correlationId1 = $this->correlationIdSource->resolve($request1);
        $requestId1 = $this->requestIdSource->resolve($request1);

        expect($correlationId1)->toBeString()
            ->and($requestId1)->toBeString()
            ->and($correlationId1)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/')
            ->and($requestId1)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        // Second request in same session
        $request2 = Request::create('/');
        $correlationId2 = $this->correlationIdSource->resolve($request2);
        $requestId2 = $this->requestIdSource->resolve($request2);

        // Correlation ID should persist (same value)
        expect($correlationId2)->toBe($correlationId1);

        // Request ID should be different (not persisted)
        expect($requestId2)->not->toBe($requestId1)
            ->and($requestId2)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        // Third request in same session
        $request3 = Request::create('/');
        $correlationId3 = $this->correlationIdSource->resolve($request3);
        $requestId3 = $this->requestIdSource->resolve($request3);

        // Correlation ID should still be the same
        expect($correlationId3)->toBe($correlationId1);

        // Request ID should be different from both previous ones
        expect($requestId3)->not->toBe($requestId1)
            ->and($requestId3)->not->toBe($requestId2)
            ->and($requestId3)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('does not persist request ID in session storage', function () {
        // Make request and generate request ID
        $request = Request::create('/');
        $requestId = $this->requestIdSource->resolve($request);

        // Verify request ID was generated
        expect($requestId)->toBeString()
            ->and($requestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        // Verify request ID was NOT persisted in session
        expect($this->sessionStorage->get('request_id'))->toBeNull()
            ->and(session('laravel_tracing.request_id'))->toBeNull();
    });

    it('external header takes priority over generation', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: true,
            headerName: 'X-Request-Id'
        );

        // First request with external header
        $request1 = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-priority-1',
        ]);
        $requestId1 = $source->resolve($request1);

        expect($requestId1)->toBe('external-priority-1');

        // Second request without external header (should generate new)
        $request2 = Request::create('/');
        $requestId2 = $source->resolve($request2);

        expect($requestId2)->not->toBe('external-priority-1')
            ->and($requestId2)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        // Third request with different external header
        $request3 = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-priority-2',
        ]);
        $requestId3 = $source->resolve($request3);

        expect($requestId3)->toBe('external-priority-2');
    });
});
