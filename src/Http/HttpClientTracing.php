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
     * modified request instance for method chaining.
     *
     * Supports both PendingRequest (macro usage) and PSR-7 Request
     * (global middleware usage) objects.
     *
     * @param  PendingRequest|\Illuminate\Http\Client\Factory|\Psr\Http\Message\RequestInterface  $request
     * @return PendingRequest|\Illuminate\Http\Client\Factory|\Psr\Http\Message\RequestInterface
     */
    public function attachTracings($request)
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

        // Handle PendingRequest (macro usage with withTracing())
        if ($request instanceof PendingRequest || $request instanceof \Illuminate\Http\Client\Factory) {
            return $request->withHeaders($headers);
        }

        // Handle PSR-7 Request (global middleware usage)
        if ($request instanceof \Psr\Http\Message\RequestInterface) {
            foreach ($headers as $name => $value) {
                $request = $request->withHeader($name, $value);
            }

            return $request;
        }

        return $request;
    }
}
