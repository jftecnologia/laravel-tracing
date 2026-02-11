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
 */
class SessionStorage implements TracingStorage
{
    private const NAMESPACE = 'laravel_tracing';

    public function set(string $key, string $value): void
    {
        session()->put($this->namespaced($key), $value);
    }

    public function get(string $key): ?string
    {
        return session()->get($this->namespaced($key));
    }

    public function has(string $key): bool
    {
        return session()->has($this->namespaced($key));
    }

    public function flush(): void
    {
        session()->forget(self::NAMESPACE);
    }

    private function namespaced(string $key): string
    {
        return self::NAMESPACE . '.' . $key;
    }
}
