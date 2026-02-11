<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Tracings;

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingStorage;

/**
 * Central coordinator for all tracing operations.
 *
 * Loads tracing sources, resolves values from HTTP requests, caches
 * resolved values in storage, and provides access to all tracings.
 */
class TracingManager
{
    /** @var array<string, TracingSource> */
    private array $sources;

    public function __construct(
        array $sources,
        private readonly TracingStorage $storage,
    ) {
        $this->sources = $sources;
    }

    /**
     * Resolve all tracing sources from the current request.
     *
     * Iterates all registered sources, calls resolve() on each,
     * and stores the results in storage for fast subsequent access.
     */
    public function resolveAll(Request $request): void
    {
        foreach ($this->sources as $key => $source) {
            $value = $source->resolve($request);
            $this->storage->set($key, $value);
        }
    }

    /**
     * Get all resolved tracing values.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        $result = [];

        foreach (array_keys($this->sources) as $key) {
            $result[$key] = $this->storage->get($key);
        }

        return $result;
    }

    /**
     * Get a specific tracing value by key.
     */
    public function get(string $key): ?string
    {
        return $this->storage->get($key);
    }

    /**
     * Check if a tracing value exists for the given key.
     */
    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }

    /**
     * Register a custom tracing source at runtime.
     */
    public function extend(string $key, TracingSource $source): void
    {
        $this->sources[$key] = $source;
    }
}
