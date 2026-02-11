<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;

/**
 * Example custom tracing source that tracks authenticated user ID.
 *
 * This source demonstrates how to create a custom tracing source that
 * resolves values based on application state (authentication in this case).
 * Developers can use this as a reference implementation for creating their
 * own custom tracing sources.
 *
 * @see docs/architecture/EXTENSIONS.md for usage guide
 */
class UserIdSource implements TracingSource
{
    /**
     * Resolve the user ID from the authenticated user in the request.
     *
     * Priority:
     * 1. Authenticated user ID (if user is logged in)
     * 2. 'guest' (if no user is authenticated)
     *
     * @param  Request  $request  The current HTTP request
     * @return string The user ID or 'guest'
     */
    public function resolve(Request $request): string
    {
        return (string) ($request->user()?->id ?? 'guest');
    }

    /**
     * Get the HTTP header name for this tracing.
     *
     * @return string The header name ('X-User-Id')
     */
    public function headerName(): string
    {
        return 'X-User-Id';
    }

    /**
     * Restore the user ID from a queued job payload.
     *
     * No transformation needed - return the value as-is.
     * The original user ID from the request is preserved when the job executes.
     *
     * @param  string  $value  The serialized user ID from job payload
     * @return string The restored user ID
     */
    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
