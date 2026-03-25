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
     * Only returns values for enabled sources that have been resolved.
     * Null (unresolved) values are excluded to prevent serialization
     * of empty tracings into job payloads.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $result = [];

        foreach (array_keys($this->sources) as $key) {
            if (! $this->isSourceEnabled($key)) {
                continue;
            }

            $value = $this->storage->get($key);

            if ($value !== null) {
                $result[$key] = $value;
            }
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
    public function extend(string $key, TracingSource $source): self
    {
        $this->sources[$key] = $source;

        return $this;
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
     * Restore tracing values from job payload.
     *
     * Injects tracing values directly into storage without running resolve().
     * Used by job dispatcher to restore values when processing queued jobs.
     *
     * Non-string values are silently skipped as defense-in-depth against
     * payloads from older versions that may contain null entries.
     *
     * @param  array<string, mixed>  $tracings
     */
    public function restore(array $tracings): void
    {
        if (! $this->enabled) {
            return;
        }

        foreach ($tracings as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (! $this->isSourceEnabled($key)) {
                continue;
            }

            // Allow source to transform value if needed
            $source = $this->getSource($key);
            $restoredValue = $source?->restoreFromJob($value) ?? $value;

            $this->storage->set($key, $restoredValue);
        }
    }

    /**
     * Check whether a specific tracing source is enabled.
     */
    private function isSourceEnabled(string $key): bool
    {
        return $this->enabledMap[$key] ?? true;
    }
}
