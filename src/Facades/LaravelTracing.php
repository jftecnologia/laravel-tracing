<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing the application's tracing system.
 *
 * Available methods:
 * @method static array<string, string|null> all()         Get all current tracing values
 * @method static string|null get(string $key)             Get a tracing value by key
 * @method static bool has(string $key)                    Check if a tracing value exists for the key
 * @method static string|null correlationId()              Get the correlation ID value
 * @method static string|null requestId()                  Get the request ID value
 *
 * @see \JuniorFontenele\LaravelTracing\LaravelTracing
 */
class LaravelTracing extends Facade
{
    /**
     * Get the service container binding name for the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JuniorFontenele\LaravelTracing\LaravelTracing::class;
    }
}
