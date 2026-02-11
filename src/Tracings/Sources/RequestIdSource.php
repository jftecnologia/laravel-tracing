<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Tracings\Sources;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Support\HeaderSanitizer;
use JuniorFontenele\LaravelTracing\Support\IdGenerator;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;

/**
 * Request-scoped tracing source for request IDs.
 *
 * Resolves request IDs with the following priority:
 * 1. External header (if accept_external_headers is enabled)
 * 2. Generate new UUID
 *
 * Unlike correlation IDs, request IDs are NOT persisted in session storage.
 * Each HTTP request gets a fresh request ID. However, when propagated to
 * queued jobs, the original request ID is preserved (not regenerated).
 */
class RequestIdSource implements TracingSource
{
    public function __construct(
        private readonly bool $acceptExternalHeaders,
        private readonly string $headerName,
    ) {
    }

    public function resolve(Request $request): string
    {
        // Priority 1: External header (if enabled)
        if ($this->acceptExternalHeaders) {
            $externalValue = $request->header($this->headerName);
            $sanitizedValue = HeaderSanitizer::sanitize($externalValue);

            if ($sanitizedValue !== null) {
                return $sanitizedValue;
            }
        }

        // Priority 2: Generate new UUID
        return IdGenerator::generate();
    }

    public function headerName(): string
    {
        return $this->headerName;
    }

    public function restoreFromJob(string $value): string
    {
        return $value;
    }
}
