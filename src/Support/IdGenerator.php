<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Support;

use Illuminate\Support\Str;

/**
 * Generates unique identifiers for tracing purposes.
 */
class IdGenerator
{
    /**
     * Generate a UUID v4 string.
     *
     * @return string A UUID v4 formatted string
     */
    public static function generate(): string
    {
        return Str::uuid()->toString();
    }
}
