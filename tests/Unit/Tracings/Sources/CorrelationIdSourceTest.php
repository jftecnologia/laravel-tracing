<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;

describe('CorrelationIdSource', function () {
    beforeEach(function () {
        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        $this->sessionStorage = new SessionStorage();
    });

    it('generates new UUID when no external header and no session value', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/');

        $result = $source->resolve($request);

        expect($result)->toBeString()
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/') // UUID format
            ->and($this->sessionStorage->get('correlation_id'))->toBe($result); // Persisted in session
    });

    it('reuses existing session value on subsequent requests', function () {
        // Set existing session value
        $this->sessionStorage->set('correlation_id', 'existing-session-123');

        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/');

        $result = $source->resolve($request);

        expect($result)->toBe('existing-session-123');
    });

    it('uses external header value when accept_external_headers is enabled', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: true,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'external-header-456',
        ]);

        $result = $source->resolve($request);

        expect($result)->toBe('external-header-456')
            ->and($this->sessionStorage->get('correlation_id'))->toBe('external-header-456'); // Persisted in session
    });

    it('ignores external header when accept_external_headers is disabled', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'external-header-456',
        ]);

        $result = $source->resolve($request);

        // Should generate new UUID, not use external header
        expect($result)->not->toBe('external-header-456')
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('generates new UUID when external header is invalid (sanitizer rejects)', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: true,
            headerName: 'X-Correlation-Id'
        );

        // Invalid header: contains special characters not allowed by HeaderSanitizer
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'invalid!@#$%header',
        ]);

        $result = $source->resolve($request);

        // Should generate new UUID because sanitizer rejected the invalid header
        expect($result)->not->toBe('invalid!@#$%header')
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('session value takes priority over generation but not over external header', function () {
        // Set existing session value
        $this->sessionStorage->set('correlation_id', 'session-value-789');

        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: true,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'external-override-999',
        ]);

        $result = $source->resolve($request);

        // External header should override session value
        expect($result)->toBe('external-override-999');
    });

    it('returns configured header name via headerName()', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Custom-Correlation'
        );

        expect($source->headerName())->toBe('X-Custom-Correlation');
    });

    it('returns value unchanged in restoreFromJob()', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: false,
            headerName: 'X-Correlation-Id'
        );

        $input = 'job-correlation-123';

        expect($source->restoreFromJob($input))->toBe($input);
    });

    it('persists resolved value in session using correlation_id key', function () {
        $source = new CorrelationIdSource(
            sessionStorage: $this->sessionStorage,
            acceptExternalHeaders: true,
            headerName: 'X-Correlation-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_CORRELATION_ID' => 'persist-test-123',
        ]);

        $source->resolve($request);

        // Verify persisted with correct key
        expect($this->sessionStorage->get('correlation_id'))->toBe('persist-test-123')
            ->and(session('laravel_tracing.correlation_id'))->toBe('persist-test-123');
    });
});
