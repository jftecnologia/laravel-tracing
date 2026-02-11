<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Middleware;

use Closure;
use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that attaches tracing values to outgoing HTTP responses.
 *
 * Executes late in the request lifecycle to attach all resolved tracing values
 * as response headers. Reads all tracings from TracingManager and attaches them
 * using the configured header names from each tracing source.
 *
 * If the package is globally disabled (config('laravel-tracing.enabled') = false),
 * this middleware passes through immediately without attaching any headers.
 */
class OutgoingTracingMiddleware
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        private readonly TracingManager $manager,
    ) {
    }

    /**
     * Handle an outgoing response.
     *
     * Attaches all enabled tracing values as response headers if the package is enabled.
     * Does not modify the request (response-only operation).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Execute next middleware/controller to get the response
        $response = $next($request);

        // Early return if package is globally disabled
        if (! config('laravel-tracing.enabled', true)) {
            return $response;
        }

        // Get all resolved tracing values
        $tracings = $this->manager->all();

        // Attach each tracing as a response header
        foreach ($tracings as $key => $value) {
            if ($value === null) {
                continue;
            }

            // Get the source to retrieve its header name
            $source = $this->manager->getSource($key);

            if (! $source instanceof \JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource) {
                continue;
            }

            $response->header($source->headerName(), $value);
        }

        return $response;
    }
}
