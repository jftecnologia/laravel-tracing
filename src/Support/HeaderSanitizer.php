<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Support;

/**
 * Validates and sanitizes external header values to prevent injection attacks.
 */
class HeaderSanitizer
{
    /**
     * Sanitize a header value.
     *
     * Trims whitespace, enforces a 255-character limit, and validates
     * that the value contains only alphanumeric characters, hyphens, and underscores.
     *
     * @param  string|null  $value  The header value to sanitize
     * @return string|null The sanitized value, or null if invalid
     */
    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (strlen($value) > 255) {
            return null;
        }

        if (! preg_match('/^[a-zA-Z0-9\-_]+$/', $value)) {
            return null;
        }

        return $value;
    }
}
