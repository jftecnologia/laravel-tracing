<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Storage;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingStorage;

/**
 * Session-backed storage for persisting tracing values across multiple requests.
 *
 * Uses Laravel's session driver to persist correlation IDs and other tracing values
 * for the duration of a user's session. Values are stored under the `laravel_tracing.*`
 * namespace to avoid conflicts with application session data.
 *
 * IMPORTANT: This storage only works when session is available (started by Laravel's
 * StartSession middleware). If session is not available, operations fail gracefully:
 * - get() returns null
 * - set() does nothing
 * - has() returns false
 *
 * This ensures the package doesn't force session initialization before Laravel's
 * session middleware runs, avoiding potential conflicts with session configuration,
 * CSRF tokens, cookies, and database/redis drivers.
 */
class SessionStorage implements TracingStorage
{
    private const NAMESPACE = 'laravel_tracing';

    public function set(string $key, string $value): void
    {
        // Only store if session is available
        // Avoids forcing session start before Laravel's StartSession middleware
        if (! $this->isSessionAvailable()) {
            return;
        }

        session()->put($this->namespaced($key), $value);
    }

    public function get(string $key): ?string
    {
        // Return null if session is not available yet
        if (! $this->isSessionAvailable()) {
            return null;
        }

        return session()->get($this->namespaced($key));
    }

    public function has(string $key): bool
    {
        // Return false if session is not available yet
        if (! $this->isSessionAvailable()) {
            return false;
        }

        return session()->has($this->namespaced($key));
    }

    public function flush(): void
    {
        // Only flush if session is available
        if (! $this->isSessionAvailable()) {
            return;
        }

        session()->forget(self::NAMESPACE);
    }

    private function namespaced(string $key): string
    {
        return self::NAMESPACE . '.' . $key;
    }

    /**
     * Check if session is available for use.
     *
     * Session must be started by Laravel's StartSession middleware before
     * we can safely use it. This prevents issues with:
     * - Session configuration not being applied
     * - CSRF token validation
     * - Cookie encryption
     * - Database/Redis connection not ready
     *
     * @return bool True if session is started and safe to use
     */
    private function isSessionAvailable(): bool
    {
        return session()->isStarted();
    }
}
