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

    /** @var array<string, bool> */
    private array $enabledMap;

    /**
     * @param  array<string, TracingSource>  $sources
     * @param  array<string, bool>  $enabledMap  Per-source enabled state (defaults to true if not specified)
     */
    public function __construct(
        array $sources,
        private readonly TracingStorage $storage,
        private readonly bool $enabled = true,
        array $enabledMap = [],
    ) {
        $this->sources = $sources;
        $this->enabledMap = $enabledMap;
    }

    /**
     * Resolve all tracing sources from the current request.
     *
     * Skips resolution entirely when globally disabled.
     * Skips individual sources that are disabled via enabledMap.
     */
    public function resolveAll(Request $request): void
    {
        if (! $this->enabled) {
            return;
        }

        foreach ($this->sources as $key => $source) {
            if (! $this->isSourceEnabled($key)) {
                continue;
            }

            $value = $source->resolve($request);
            $this->storage->set($key, $value);
        }
    }

    /**
     * Get all resolved tracing values.
     *
     * Only returns values for enabled sources.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        $result = [];

        foreach (array_keys($this->sources) as $key) {
            if (! $this->isSourceEnabled($key)) {
                continue;
            }

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

    /**
     * Check whether the global tracing is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get a specific tracing source by key.
     */
    public function getSource(string $key): ?TracingSource
    {
        return $this->sources[$key] ?? null;
    }

    /**
     * Check whether a specific tracing source is enabled.
     */
    private function isSourceEnabled(string $key): bool
    {
        return $this->enabledMap[$key] ?? true;
    }
}
