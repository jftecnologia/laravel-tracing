<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Tracings\Sources;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Support\HeaderSanitizer;
use JuniorFontenele\LaravelTracing\Support\IdGenerator;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;

/**
 * Session-aware tracing source for correlation IDs.
 *
 * Resolves correlation IDs with the following priority:
 * 1. External header (if accept_external_headers is enabled)
 * 2. Session storage (from previous request in same session)
 * 3. Generate new UUID
 *
 * Once resolved, the correlation ID is persisted in session storage
 * so subsequent requests in the same session reuse the same value.
 */
class CorrelationIdSource implements TracingSource
{
    private const SESSION_KEY = 'correlation_id';

    public function __construct(
        private readonly SessionStorage $sessionStorage,
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
                $this->sessionStorage->set(self::SESSION_KEY, $sanitizedValue);

                return $sanitizedValue;
            }
        }

        // Priority 2: Session storage (from previous request)
        $sessionValue = $this->sessionStorage->get(self::SESSION_KEY);

        if ($sessionValue !== null) {
            return $sessionValue;
        }

        // Priority 3: Generate new UUID
        $generatedValue = IdGenerator::generate();
        $this->sessionStorage->set(self::SESSION_KEY, $generatedValue);

        return $generatedValue;
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
