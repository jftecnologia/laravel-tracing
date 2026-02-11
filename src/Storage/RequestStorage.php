<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Storage;

use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingStorage;

/**
 * In-memory storage backend for the current request lifecycle.
 *
 * Holds all resolved tracing values for the duration of a single HTTP request.
 * Values are cleared when the request ends or flush() is called.
 */
class RequestStorage implements TracingStorage
{
    /** @var array<string, string> */
    private array $storage = [];

    public function set(string $key, string $value): void
    {
        $this->storage[$key] = $value;
    }

    public function get(string $key): ?string
    {
        return $this->storage[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    public function flush(): void
    {
        $this->storage = [];
    }
}
