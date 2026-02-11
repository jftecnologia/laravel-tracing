<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;

/**
 * Custom tracing source fixture for testing runtime extension.
 */
class CustomTracingSource implements TracingSource
{
    public function __construct(
        private readonly string $headerName = 'X-Custom-Trace',
        private readonly string $defaultValue = 'custom-default',
    ) {
    }

    public function resolve(Request $request): string
    {
        return $request->header($this->headerName) ?? $this->defaultValue;
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
