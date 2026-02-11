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

    it('makes extend() chainable for multiple extensions', function () {
        $manager = new TracingManager([], $this->storage);

        $source1 = Mockery::mock(TracingSource::class);
        $source1->shouldReceive('resolve')->andReturn('value-1');

        $source2 = Mockery::mock(TracingSource::class);
        $source2->shouldReceive('resolve')->andReturn('value-2');

        $result = $manager
            ->extend('custom1', $source1)
            ->extend('custom2', $source2);

        expect($result)->toBe($manager);

        $manager->resolveAll(Request::create('/'));

        expect($manager->get('custom1'))->toBe('value-1')
            ->and($manager->get('custom2'))->toBe('value-2');
    });

    it('includes extended source in getSource()', function () {
        $manager = new TracingManager([], $this->storage);

        $source = Mockery::mock(TracingSource::class);

        $manager->extend('custom', $source);

        expect($manager->getSource('custom'))->toBe($source);
    });

    it('resolves extended sources during resolveAll()', function () {
        $initialSource = Mockery::mock(TracingSource::class);
        $initialSource->shouldReceive('resolve')->once()->andReturn('initial-value');

        $manager = new TracingManager(['initial' => $initialSource], $this->storage);

        $extendedSource = Mockery::mock(TracingSource::class);
        $extendedSource->shouldReceive('resolve')->once()->andReturn('extended-value');

        $manager->extend('extended', $extendedSource);
        $manager->resolveAll(Request::create('/'));

        expect($manager->get('initial'))->toBe('initial-value')
            ->and($manager->get('extended'))->toBe('extended-value');
    });

    it('includes extended sources in all()', function () {
        $initialSource = Mockery::mock(TracingSource::class);
        $initialSource->shouldReceive('resolve')->andReturn('initial-value');

        $manager = new TracingManager(['initial' => $initialSource], $this->storage);

        $extendedSource = Mockery::mock(TracingSource::class);
        $extendedSource->shouldReceive('resolve')->andReturn('extended-value');

        $manager->extend('extended', $extendedSource);
        $manager->resolveAll(Request::create('/'));

        $all = $manager->all();

        expect($all)->toHaveKey('initial')
            ->and($all)->toHaveKey('extended')
            ->and($all['initial'])->toBe('initial-value')
            ->and($all['extended'])->toBe('extended-value');
    });

    it('allows checking extended sources with has()', function () {
        $manager = new TracingManager([], $this->storage);

        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->andReturn('value');

        $manager->extend('custom', $source);
        $manager->resolveAll(Request::create('/'));

        expect($manager->has('custom'))->toBeTrue();
    });

    it('allows retrieving extended sources with get()', function () {
        $manager = new TracingManager([], $this->storage);

        $source = Mockery::mock(TracingSource::class);
        $source->shouldReceive('resolve')->andReturn('custom-value');

        $manager->extend('custom', $source);
        $manager->resolveAll(Request::create('/'));

        expect($manager->get('custom'))->toBe('custom-value');
    });

    it('does not call resolve on sources until resolveAll() is triggered', function () {
        $source = Mockery::mock(TracingSource::class);
        $source->shouldNotReceive('resolve');

        $manager = new TracingManager(['lazy' => $source], $this->storage);

        expect($manager->get('lazy'))->toBeNull();
    });
});
