<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

/**
 * HTTP Client Integration Tests
 *
 * Tests the integration between tracing and Laravel's HTTP client. Verifies
 * that tracing headers are correctly attached to outgoing HTTP requests in
 * both per-request mode (withTracing()) and global mode (via config).
 */
describe('HTTP Client Integration', function () {
    beforeEach(function () {
        // Ensure package is enabled and HTTP client global mode is disabled
        config([
            'laravel-tracing.enabled' => true,
            'laravel-tracing.http_client.enabled' => false,  // Disable global mode by default
        ]);

        // Setup test route that makes tracings available
        Route::middleware('web')->get('/test-http', function () {
            return response()->json(['ok' => true]);
        });

        // Fake HTTP client to intercept outgoing requests
        Http::fake();
    });

    it('attaches all tracing headers to request using withTracing()', function () {
        // Make a request to populate tracing values
        $this->get('/test-http');

        $correlationId = LaravelTracing::correlationId();
        $requestId = LaravelTracing::requestId();

        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Assert both tracing headers were sent
        Http::assertSent(function ($request) use ($correlationId, $requestId) {
            return $request->hasHeader('X-Correlation-Id', $correlationId)
                && $request->hasHeader('X-Request-Id', $requestId);
        });
    });

    it('does not attach tracing headers without withTracing() when global mode is disabled', function () {
        config(['laravel-tracing.http_client.enabled' => false]);

        // Make a request to populate tracing values
        $this->get('/test-http');

        // Make outgoing request WITHOUT withTracing()
        Http::get('https://api.example.com/data');

        // Assert no tracing headers were sent
        Http::assertSent(function ($request) {
            return ! $request->hasHeader('X-Correlation-Id')
                && ! $request->hasHeader('X-Request-Id');
        });
    });

    it('attaches tracings to all requests when global mode is enabled', function () {
        // Create a new test with global mode enabled from the start
        $this->refreshApplication();

        config([
            'laravel-tracing.enabled' => true,
            'laravel-tracing.http_client.enabled' => true,  // Enable global mode
        ]);

        // Force service provider to re-register with new config
        $this->app->register(JuniorFontenele\LaravelTracing\LaravelTracingServiceProvider::class, force: true);

        // Now fake HTTP after middleware is registered
        Http::fake();

        // Setup test route
        Route::middleware('web')->get('/test-http', function () {
            return response()->json(['ok' => true]);
        });

        // Make a request to populate tracing values
        $this->get('/test-http');

        $correlationId = LaravelTracing::correlationId();
        $requestId = LaravelTracing::requestId();

        // Make outgoing request WITHOUT explicit withTracing() call
        Http::get('https://api.example.com/data');

        // Assert tracing headers were sent automatically
        Http::assertSent(function ($request) use ($correlationId, $requestId) {
            return $request->hasHeader('X-Correlation-Id', $correlationId)
                && $request->hasHeader('X-Request-Id', $requestId);
        });
    });

    it('does not attach disabled tracing sources to outgoing requests', function () {
        config([
            'laravel-tracing.tracings.correlation_id.enabled' => false,
            'laravel-tracing.tracings.request_id.enabled' => true,
        ]);

        // Make a request to populate tracing values
        $this->get('/test-http');

        $requestId = LaravelTracing::requestId();

        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Assert only enabled tracing (request_id) was sent
        Http::assertSent(function ($request) use ($requestId) {
            return ! $request->hasHeader('X-Correlation-Id')  // disabled
                && $request->hasHeader('X-Request-Id', $requestId);  // enabled
        });
    });

    it('does not attach tracings when package is globally disabled', function () {
        config(['laravel-tracing.enabled' => false]);

        // Make a request (tracings won't be resolved)
        $this->get('/test-http');

        // Make outgoing request with withTracing()
        Http::withTracing()->get('https://api.example.com/data');

        // Assert no tracing headers were sent
        Http::assertSent(function ($request) {
            return ! $request->hasHeader('X-Correlation-Id')
                && ! $request->hasHeader('X-Request-Id');
        });
    });

    it('makes withTracing() chainable with other Http methods', function () {
        // Make a request to populate tracing values
        $this->get('/test-http');

        $correlationId = LaravelTracing::correlationId();

        // Chain withTracing() with retry, timeout, and other methods
        Http::withTracing()
            ->retry(3, 100)
            ->timeout(30)
            ->get('https://api.example.com/data');

        // Assert tracing headers were sent despite chaining
        Http::assertSent(function ($request) use ($correlationId) {
            return $request->hasHeader('X-Correlation-Id', $correlationId);
        });
    });

    it('attaches custom tracing sources to outgoing requests', function () {
        // Register a custom tracing source directly on TracingManager
        $manager = app(JuniorFontenele\LaravelTracing\Tracings\TracingManager::class);
        $manager->extend('tenant_id', new class implements JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource
        {
            public function resolve(Illuminate\Http\Request $request): string
            {
                return 'tenant-123';
            }

            public function headerName(): string
            {
                return 'X-Tenant-Id';
            }

            public function restoreFromJob(string $value): string
            {
                return $value;
            }
        });

        // Make a request to resolve tracings (including custom one)
        Route::middleware('web')->get('/test-custom', function () use ($manager) {
            $manager->resolveAll(request());

            return response()->json(['ok' => true]);
        });

        $this->get('/test-custom');

        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Assert custom tracing header was sent
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Tenant-Id', 'tenant-123');
        });
    });

    it('attaches current tracings to multiple outgoing requests', function () {
        // Make a request to populate tracing values
        $this->get('/test-http');

        $correlationId = LaravelTracing::correlationId();
        $requestId = LaravelTracing::requestId();

        // Make multiple outgoing requests
        Http::withTracing()->get('https://api.example.com/endpoint1');
        Http::withTracing()->post('https://api.example.com/endpoint2', ['data' => 'test']);
        Http::withTracing()->put('https://api.example.com/endpoint3', ['data' => 'test']);

        // Assert all requests received tracing headers
        Http::assertSent(function ($request) use ($correlationId, $requestId) {
            return $request->hasHeader('X-Correlation-Id', $correlationId)
                && $request->hasHeader('X-Request-Id', $requestId);
        });

        // Assert exactly 3 requests were made
        Http::assertSentCount(3);
    });

    it('handles null tracing values gracefully', function () {
        // Don't make any request - tracings will be null/empty
        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Assert request was sent without errors (no headers though)
        Http::assertSent(function ($request) {
            return true;  // Request was made successfully
        });
    });

    it('uses correct header names from tracing sources', function () {
        // Change header names via config
        config([
            'laravel-tracing.tracings.correlation_id.header' => 'X-Custom-Correlation',
            'laravel-tracing.tracings.request_id.header' => 'X-Custom-Request',
        ]);

        // Refresh service provider to pick up new config
        $this->app->register(JuniorFontenele\LaravelTracing\LaravelTracingServiceProvider::class, force: true);

        // Make a request to populate tracing values
        $this->get('/test-http');

        // Make outgoing request with tracing
        Http::withTracing()->get('https://api.example.com/data');

        // Assert custom header names were used
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Custom-Correlation')
                && $request->hasHeader('X-Custom-Request');
        });
    });

    it('works in global mode with withTracing() call (both enabled)', function () {
        // Create a new test with global mode enabled
        $this->refreshApplication();

        config([
            'laravel-tracing.enabled' => true,
            'laravel-tracing.http_client.enabled' => true,  // Enable global mode
        ]);

        // Force service provider to re-register with new config
        $this->app->register(JuniorFontenele\LaravelTracing\LaravelTracingServiceProvider::class, force: true);

        // Now fake HTTP after middleware is registered
        Http::fake();

        // Setup test route
        Route::middleware('web')->get('/test-http', function () {
            return response()->json(['ok' => true]);
        });

        // Make a request to populate tracing values
        $this->get('/test-http');

        $correlationId = LaravelTracing::correlationId();

        // Make outgoing request with explicit withTracing() even though global is enabled
        Http::withTracing()->get('https://api.example.com/data');

        // Assert tracing headers were sent
        Http::assertSent(function ($request) use ($correlationId) {
            return $request->hasHeader('X-Correlation-Id', $correlationId);
        });
    });
});
