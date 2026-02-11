<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource;

describe('RequestIdSource', function () {
    it('generates new UUID when no external header', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/');

        $result = $source->resolve($request);

        expect($result)->toBeString()
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/'); // UUID format
    });

    it('uses external header value when accept_external_headers is enabled', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: true,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-request-456',
        ]);

        $result = $source->resolve($request);

        expect($result)->toBe('external-request-456');
    });

    it('ignores external header when accept_external_headers is disabled', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'external-request-456',
        ]);

        $result = $source->resolve($request);

        // Should generate new UUID, not use external header
        expect($result)->not->toBe('external-request-456')
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('generates new UUID when external header is invalid (sanitizer rejects)', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: true,
            headerName: 'X-Request-Id'
        );

        // Invalid header: contains special characters not allowed by HeaderSanitizer
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_REQUEST_ID' => 'invalid!@#$%header',
        ]);

        $result = $source->resolve($request);

        // Should generate new UUID because sanitizer rejected the invalid header
        expect($result)->not->toBe('invalid!@#$%header')
            ->and($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('returns configured header name via headerName()', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Custom-Request-Id'
        );

        expect($source->headerName())->toBe('X-Custom-Request-Id');
    });

    it('returns value unchanged in restoreFromJob()', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $input = 'job-request-123';

        expect($source->restoreFromJob($input))->toBe($input);
    });

    it('generates unique request ID for each call to resolve()', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request1 = Request::create('/');
        $request2 = Request::create('/');
        $request3 = Request::create('/');

        $result1 = $source->resolve($request1);
        $result2 = $source->resolve($request2);
        $result3 = $source->resolve($request3);

        // All three should be unique UUIDs
        expect($result1)->not->toBe($result2)
            ->and($result2)->not->toBe($result3)
            ->and($result1)->not->toBe($result3)
            ->and($result1)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/')
            ->and($result2)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/')
            ->and($result3)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('does not persist request ID in session storage', function () {
        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/');

        $result = $source->resolve($request);

        // Request ID should NOT be stored in session
        expect(session('laravel_tracing.request_id'))->toBeNull()
            ->and($result)->toBeString();
    });

    it('generates new request ID even when called multiple times with same request', function () {
        $source = new RequestIdSource(
            acceptExternalHeaders: false,
            headerName: 'X-Request-Id'
        );

        $request = Request::create('/');

        // Call resolve multiple times with the same request
        $result1 = $source->resolve($request);
        $result2 = $source->resolve($request);
        $result3 = $source->resolve($request);

        // Each call should generate a new UUID (no caching)
        expect($result1)->not->toBe($result2)
            ->and($result2)->not->toBe($result3)
            ->and($result1)->not->toBe($result3);
    });
});
