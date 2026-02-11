<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Middleware;

use Closure;
use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that resolves tracing values from incoming HTTP requests.
 *
 * Executes early in the request lifecycle to resolve all tracing values
 * (correlation ID, request ID, custom tracings) by calling TracingManager::resolveAll().
 * The resolved values are stored in RequestStorage and become available
 * throughout the request lifecycle.
 *
 * If the package is globally disabled (config('laravel-tracing.enabled') = false),
 * this middleware passes through immediately without any tracing logic.
 */
class IncomingTracingMiddleware
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        private readonly TracingManager $manager,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * Resolves all tracing values from the request if the package is enabled.
     * Does not modify the request or response (read-only operation).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Early return if package is globally disabled
        if (! config('laravel-tracing.enabled', true)) {
            return $next($request);
        }

        // Resolve all tracing values and store them for the request lifecycle
        $this->manager->resolveAll($request);

        // Pass request to next middleware without modification
        return $next($request);
    }
}
