<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelTracing\Storage\RequestStorage;

describe('RequestStorage', function () {
    beforeEach(function () {
        $this->storage = new RequestStorage();
    });

    it('stores and retrieves a value by key', function () {
        $this->storage->set('correlation_id', 'abc-123');

        expect($this->storage->get('correlation_id'))->toBe('abc-123');
    });

    it('returns null for non-existent keys', function () {
        expect($this->storage->get('missing_key'))->toBeNull();
    });

    it('returns true for existing keys via has()', function () {
        $this->storage->set('request_id', 'req-456');

        expect($this->storage->has('request_id'))->toBeTrue();
    });

    it('returns false for missing keys via has()', function () {
        expect($this->storage->has('missing_key'))->toBeFalse();
    });

    it('overwrites existing values on set()', function () {
        $this->storage->set('key', 'first');
        $this->storage->set('key', 'second');

        expect($this->storage->get('key'))->toBe('second');
    });

    it('clears all values on flush()', function () {
        $this->storage->set('key1', 'value1');
        $this->storage->set('key2', 'value2');

        $this->storage->flush();

        expect($this->storage->get('key1'))->toBeNull()
            ->and($this->storage->get('key2'))->toBeNull()
            ->and($this->storage->has('key1'))->toBeFalse()
            ->and($this->storage->has('key2'))->toBeFalse();
    });
});
