<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

describe('TracingManager', function () {
    beforeEach(function () {
        $this->storage = new RequestStorage();
    });

    it('resolves all sources and stores values on resolveAll()', function () {
        $source1 = Mockery::mock(TracingSource::class);
        $source1->shouldReceive('resolve')->once()->andReturn('id-1');

        $source2 = Mockery::mock(TracingSource::class);
        $source2->shouldReceive('resolve')->once()->andReturn('id-2');

        $manager = new TracingManager(
            ['correlation_id' => $source1, 'request_id' => $source2],
            $this->storage,
        );

        $manager->resolveAll(Request::create('/'));

        expect($manager->get('correlation_id'))->toBe('id-1')
            ->and($manager->get('request_id'))->toBe('id-2');
    });

    it('returns all resolved tracings via all()', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->once()->andReturn('value-1');

        $manager = new TracingManager(
            ['trace_key' => $source],
            $this->storage,
        );

        $manager->resolveAll(Request::create('/'));

        expect($manager->all())->toBe(['trace_key' => 'value-1']);
    });

    it('returns null values via all() before resolveAll() is called', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldNotReceive('resolve');

        $manager = new TracingManager(
            ['trace_key' => $source],
            $this->storage,
        );

        expect($manager->all())->toBe(['trace_key' => null]);
    });

    it('returns specific tracing value via get()', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->andReturn('specific-value');

        $manager = new TracingManager(['key' => $source], $this->storage);
        $manager->resolveAll(Request::create('/'));

        expect($manager->get('key'))->toBe('specific-value');
    });

    it('returns null for non-existent key via get()', function () {
        $manager = new TracingManager([], $this->storage);

        expect($manager->get('missing'))->toBeNull();
    });

    it('returns true for existing key via has()', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->andReturn('value');

        $manager = new TracingManager(['key' => $source], $this->storage);
        $manager->resolveAll(Request::create('/'));

        expect($manager->has('key'))->toBeTrue();
    });

    it('returns false for missing key via has()', function () {
        $manager = new TracingManager([], $this->storage);

        expect($manager->has('missing'))->toBeFalse();
    });

    it('registers runtime tracing source via extend()', function () {
        $manager = new TracingManager([], $this->storage);

        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->andReturn('extended-value');

        $manager->extend('custom', $source);
        $manager->resolveAll(Request::create('/'));

        expect($manager->get('custom'))->toBe('extended-value')
            ->and($manager->all())->toHaveKey('custom');
    });

    it('does not call resolve on sources until resolveAll() is triggered', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldNotReceive('resolve');

        $manager = new TracingManager(['lazy' => $source], $this->storage);

        expect($manager->get('lazy'))->toBeNull();
    });
});
