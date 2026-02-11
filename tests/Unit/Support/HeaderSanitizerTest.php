<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelTracing\Support\HeaderSanitizer;

describe('HeaderSanitizer', function () {
    // Happy path
    it('returns valid alphanumeric value unchanged', function () {
        expect(HeaderSanitizer::sanitize('abc123'))->toBe('abc123');
    });

    it('accepts hyphens and underscores', function () {
        expect(HeaderSanitizer::sanitize('my-trace_id-123'))->toBe('my-trace_id-123');
    });

    it('trims whitespace from valid values', function () {
        expect(HeaderSanitizer::sanitize('  valid-id  '))->toBe('valid-id');
    });

    it('accepts a value at exactly 255 characters', function () {
        $value = str_repeat('a', 255);

        expect(HeaderSanitizer::sanitize($value))->toBe($value);
    });

    // Unhappy path
    it('returns null for null input', function () {
        expect(HeaderSanitizer::sanitize(null))->toBeNull();
    });

    it('returns null for empty string', function () {
        expect(HeaderSanitizer::sanitize(''))->toBeNull();
    });

    it('returns null for whitespace-only string', function () {
        expect(HeaderSanitizer::sanitize('   '))->toBeNull();
    });

    it('returns null for values exceeding 255 characters', function () {
        $value = str_repeat('a', 256);

        expect(HeaderSanitizer::sanitize($value))->toBeNull();
    });

    it('returns null for values with invalid characters', function (string $value) {
        expect(HeaderSanitizer::sanitize($value))->toBeNull();
    })->with([
        'spaces' => ['hello world'],
        'special chars' => ['abc@123'],
        'dots' => ['trace.id.value'],
        'slashes' => ['path/to/value'],
        'html tags' => ['<script>alert(1)</script>'],
        'newlines' => ["value\ninjection"],
        'unicode' => ['válüe-ñ'],
    ]);
});
