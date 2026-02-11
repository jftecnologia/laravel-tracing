<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelTracing\Storage\SessionStorage;

describe('SessionStorage', function () {
    beforeEach(function () {
        // Start session for testing
        if (! session()->isStarted()) {
            session()->start();
        }

        $this->storage = new SessionStorage();
    });

    it('stores and retrieves a value in session with namespace', function () {
        $this->storage->set('correlation_id', 'test-123');

        expect($this->storage->get('correlation_id'))->toBe('test-123')
            ->and(session('laravel_tracing.correlation_id'))->toBe('test-123');
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

    it('stores values under laravel_tracing namespace to avoid conflicts', function () {
        // Set application session value
        session(['my_key' => 'app-value']);

        // Set tracing value with same key name
        $this->storage->set('my_key', 'tracing-value');

        // Both should coexist without conflict
        expect(session('my_key'))->toBe('app-value')
            ->and($this->storage->get('my_key'))->toBe('tracing-value')
            ->and(session('laravel_tracing.my_key'))->toBe('tracing-value');
    });

    it('clears all tracing values on flush()', function () {
        $this->storage->set('correlation_id', 'corr-123');
        $this->storage->set('request_id', 'req-456');

        // Set application session value (should not be affected)
        session(['app_key' => 'app-value']);

        $this->storage->flush();

        expect($this->storage->get('correlation_id'))->toBeNull()
            ->and($this->storage->get('request_id'))->toBeNull()
            ->and($this->storage->has('correlation_id'))->toBeFalse()
            ->and($this->storage->has('request_id'))->toBeFalse()
            ->and(session('app_key'))->toBe('app-value'); // App session data preserved
    });

    it('persists values across multiple accesses in same session', function () {
        $this->storage->set('correlation_id', 'persistent-123');

        // Simulate multiple accesses
        $firstAccess = $this->storage->get('correlation_id');
        $secondAccess = $this->storage->get('correlation_id');
        $thirdAccess = $this->storage->get('correlation_id');

        expect($firstAccess)->toBe('persistent-123')
            ->and($secondAccess)->toBe('persistent-123')
            ->and($thirdAccess)->toBe('persistent-123');
    });
});
