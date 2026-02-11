<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelTracing\Support\IdGenerator;

describe('IdGenerator', function () {
    it('generates a valid UUID v4 format string', function () {
        $uuid = IdGenerator::generate();

        expect($uuid)->toBeString()
            ->and($uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
    });

    it('generates unique values on each call', function () {
        $first = IdGenerator::generate();
        $second = IdGenerator::generate();

        expect($first)->not->toBe($second);
    });
});
