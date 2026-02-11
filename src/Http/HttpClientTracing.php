<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Http;

use Illuminate\Http\Client\PendingRequest;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

/**
 * Attaches tracing headers to outgoing HTTP requests.
 *
 * Reads all current tracing values from TracingManager and attaches them
 * as headers to Laravel's HTTP client requests. Only enabled tracings
 * are attached, preventing accidental leakage to external services.
 */
class HttpClientTracing
{
    public function __construct(
        private readonly TracingManager $manager,
    ) {
    }

    /**
     * Attach all tracing headers to the outgoing HTTP request.
     *
     * Reads all enabled tracings from the manager and attaches them as
     * headers using each source's configured header name. Returns the
     * modified PendingRequest instance for method chaining.
     */
    public function attachTracings(PendingRequest $request): PendingRequest
    {
        $headers = [];

        foreach ($this->manager->all() as $key => $value) {
            if ($value === null) {
                continue;
            }

            $source = $this->manager->getSource($key);

            if (! $source instanceof \JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource) {
                continue;
            }

            $headerName = $source->headerName();
            $headers[$headerName] = $value;
        }

        return $request->withHeaders($headers);
    }
}
