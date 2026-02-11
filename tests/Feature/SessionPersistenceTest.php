<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;

/**
 * Session Persistence Feature Tests
 *
 * These tests verify the session persistence behavior of correlation IDs
 * through multiple simulated requests without requiring full HTTP middleware.
 * Full HTTP integration tests will be added in the Middleware Integration epic.
 */
describe('Session Persistence', function () {
    beforeEach(function () {
        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        $this->sessionStorage = new SessionStorage();
    });

    it('persists correlation ID across multiple simulated requests in same session', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        // First request - generates new correlation ID
        $request1 = Request::create('/');
        $firstCorrelationId = $source->resolve($request1);

        expect($firstCorrelationId)->toBeString()
            ->and($firstCorrelationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');

        // Second request in same session - should reuse correlation ID
        $request2 = Request::create('/');
        $secondCorrelationId = $source->resolve($request2);

        expect($secondCorrelationId)->toBe($firstCorrelationId);

        // Third request - correlation ID should still be the same
        $request3 = Request::create('/');
        $thirdCorrelationId = $source->resolve($request3);

        expect($thirdCorrelationId)->toBe($firstCorrelationId);
    });

    it('generates new correlation ID for new session', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        // First session
        $request1 = Request::create('/');
        $firstCorrelationId = $source->resolve($request1);

        // Clear session (simulate new session)
        $this->sessionStorage->flush();

        // New session - should generate new correlation ID
        $request2 = Request::create('/');
        $newCorrelationId = $source->resolve($request2);

        expect($newCorrelationId)->not->toBe($firstCorrelationId)
            ->and($newCorrelationId)->toBeString()
            ->and($newCorrelationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('session value takes priority over generation', function () {
        // Manually set session correlation ID
        $this->sessionStorage->set('correlation_id', 'manual-session-value');

        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/');
        $correlationId = $source->resolve($request);

        // Should use session value instead of generating new one
        expect($correlationId)->toBe('manual-session-value');
    });

    it('external header overrides session value when accept_external_headers is enabled', function () {
        // Set existing session value
        $this->sessionStorage->set('correlation_id', 'session-value-123');

        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: true,
            headerName: 'X-Correlation-Id'
        );

        // Send request with external header
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'external-override-456',
        ]);

        $correlationId = $source->resolve($request);

        // External header should override session value
        expect($correlationId)->toBe('external-override-456');

        // Session should be updated with new value
        expect($this->sessionStorage->get('correlation_id'))->toBe('external-override-456');
    });

    it('external header is ignored when accept_external_headers is false', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        // Send request with external header
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'external-should-be-ignored',
        ]);

        $correlationId = $source->resolve($request);

        // Should not use external header (should generate new UUID)
        expect($correlationId)->not->toBe('external-should-be-ignored')
            ->and($correlationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('maintains session storage namespace isolation from app session data', function () {
        // Set application session data
        session(['user_id' => '123', 'app_key' => 'app-value']);

        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/');
        $correlationId = $source->resolve($request);

        // Both app session and tracing session should coexist
        expect(session('user_id'))->toBe('123')
            ->and(session('app_key'))->toBe('app-value')
            ->and($this->sessionStorage->get('correlation_id'))->toBe($correlationId)
            ->and(session('laravel_tracing.correlation_id'))->toBe($correlationId);
    });

    it('clears tracing values when session storage is flushed', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        // Make request to set correlation ID in session
        $request1 = Request::create('/');
        $correlationId = $source->resolve($request1);

        expect($this->sessionStorage->get('correlation_id'))->toBe($correlationId);

        // Flush tracing storage
        $this->sessionStorage->flush();

        // Next request should generate new correlation ID
        $request2 = Request::create('/');
        $newCorrelationId = $source->resolve($request2);

        expect($newCorrelationId)->not->toBe($correlationId)
            ->and($this->sessionStorage->get('correlation_id'))->toBe($newCorrelationId);
    });
});
