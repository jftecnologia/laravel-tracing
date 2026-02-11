<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing;

use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

/**
 * Main API class for accessing tracing values.
 *
 * Provides a developer-friendly interface that delegates
 * all calls to the underlying TracingManager.
 */
class LaravelTracing
{
    public function __construct(
        private readonly TracingManager $manager,
    ) {
    }

    /**
     * Get all current tracing values.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return $this->manager->all();
    }

    /**
     * Get a specific tracing value by key.
     */
    public function get(string $key): ?string
    {
        return $this->manager->get($key);
    }

    /**
     * Check if a tracing value exists.
     */
    public function has(string $key): bool
    {
        return $this->manager->has($key);
    }

    /**
     * Get the correlation ID value.
     */
    public function correlationId(): ?string
    {
        return $this->manager->get('correlation_id');
    }

    /**
     * Get the request ID value.
     */
    public function requestId(): ?string
    {
        return $this->manager->get('request_id');
    }
}
