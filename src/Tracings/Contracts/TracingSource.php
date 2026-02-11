<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Tracings\Contracts;

use Illuminate\Http\Request;

/**
 * Contract for tracing sources that resolve and restore tracing values.
 *
 * All tracing sources (built-in and custom) must implement this interface.
 * Each source is responsible for resolving a tracing value from an HTTP request
 * and restoring it when processing queued jobs.
 */
interface TracingSource
{
    /**
     * Resolve the tracing value from the current HTTP request.
     *
     * @param  Request  $request  The current HTTP request instance
     * @return string The resolved tracing value
     */
    public function resolve(Request $request): string;

    /**
     * Get the HTTP header name associated with this tracing source.
     *
     * @return string The header name (e.g., 'X-Correlation-Id')
     */
    public function headerName(): string;

    /**
     * Restore the tracing value from a queued job payload.
     *
     * Allows custom transformation when restoring a tracing value
     * that was serialized into a job payload.
     *
     * @param  string  $value  The serialized tracing value from the job payload
     * @return string The restored tracing value
     */
    public function restoreFromJob(string $value): string;
}
