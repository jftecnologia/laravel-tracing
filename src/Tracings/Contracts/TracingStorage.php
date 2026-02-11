<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Tracings\Contracts;

/**
 * Contract for tracing storage backends.
 *
 * Defines how tracing values are stored and retrieved during the request lifecycle.
 * Implementations can use in-memory arrays, cache, Redis, or other backends.
 */
interface TracingStorage
{
    /**
     * Store a tracing value by key.
     *
     * @param  string  $key  The tracing key identifier
     * @param  string  $value  The tracing value to store
     */
    public function set(string $key, string $value): void;

    /**
     * Retrieve a tracing value by key.
     *
     * @param  string  $key  The tracing key identifier
     * @return string|null The stored value, or null if not found
     */
    public function get(string $key): ?string;

    /**
     * Check if a tracing value exists for the given key.
     *
     * @param  string  $key  The tracing key identifier
     * @return bool True if the key exists in storage
     */
    public function has(string $key): bool;

    /**
     * Clear all stored tracing values.
     */
    public function flush(): void;
}
