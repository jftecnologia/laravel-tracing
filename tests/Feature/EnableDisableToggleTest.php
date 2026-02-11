<?php

declare(strict_types = 1);

use Illuminate\Http\Request;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Tracings\Contracts\TracingSource;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

describe('Enable/Disable Toggles', function () {
    beforeEach(function () {
        $this->storage = new RequestStorage();
    });

    describe('global toggle', function () {
        it('skips all resolution when globally disabled', function () {
            $source = Mockery::mock(TracingSource::class);
            $source->shouldNotReceive('resolve');

            $manager = new TracingManager(
                ['correlation_id' => $source],
                $this->storage,
                enabled: false,
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->get('correlation_id'))->toBeNull();
        });

        it('resolves all sources when globally enabled', function () {
            $source = Mockery::mock(TracingSource::class);
            $source->shouldReceive('resolve')->once()->andReturn('id-1');

            $manager = new TracingManager(
                ['correlation_id' => $source],
                $this->storage,
                enabled: true,
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->get('correlation_id'))->toBe('id-1');
        });

        it('returns empty array from all() when globally disabled and no values resolved', function () {
            $source = Mockery::mock(TracingSource::class);

            $manager = new TracingManager(
                ['correlation_id' => $source],
                $this->storage,
                enabled: false,
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->all())->toBe(['correlation_id' => null]);
        });

        it('reports enabled status via isEnabled()', function () {
            $enabledManager = new TracingManager([], $this->storage, enabled: true);
            $disabledManager = new TracingManager([], $this->storage, enabled: false);

            expect($enabledManager->isEnabled())->toBeTrue()
                ->and($disabledManager->isEnabled())->toBeFalse();
        });
    });

    describe('per-tracing toggle', function () {
        it('skips disabled source during resolveAll()', function () {
            $enabledSource = Mockery::mock(TracingSource::class);
            $enabledSource->shouldReceive('resolve')->once()->andReturn('req-123');

            $disabledSource = Mockery::mock(TracingSource::class);
            $disabledSource->shouldNotReceive('resolve');

            $manager = new TracingManager(
                ['request_id' => $enabledSource, 'correlation_id' => $disabledSource],
                $this->storage,
                enabledMap: ['request_id' => true, 'correlation_id' => false],
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->get('request_id'))->toBe('req-123')
                ->and($manager->get('correlation_id'))->toBeNull();
        });

        it('excludes disabled source from all()', function () {
            $source1 = Mockery::mock(TracingSource::class);
            $source1->shouldReceive('resolve')->once()->andReturn('req-123');

            $source2 = Mockery::mock(TracingSource::class);

            $manager = new TracingManager(
                ['request_id' => $source1, 'correlation_id' => $source2],
                $this->storage,
                enabledMap: ['request_id' => true, 'correlation_id' => false],
            );

            $manager->resolveAll(Request::create('/'));

            $all = $manager->all();

            expect($all)->toHaveKey('request_id')
                ->and($all)->not->toHaveKey('correlation_id');
        });

        it('enables sources not present in enabledMap by default', function () {
            $source = Mockery::mock(TracingSource::class);
            $source->shouldReceive('resolve')->once()->andReturn('value');

            $manager = new TracingManager(
                ['custom_trace' => $source],
                $this->storage,
                enabledMap: [],
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->get('custom_trace'))->toBe('value')
                ->and($manager->all())->toHaveKey('custom_trace');
        });

        it('disables correlation_id while keeping request_id enabled', function () {
            $corrSource = Mockery::mock(TracingSource::class);
            $corrSource->shouldNotReceive('resolve');

            $reqSource = Mockery::mock(TracingSource::class);
            $reqSource->shouldReceive('resolve')->once()->andReturn('req-456');

            $manager = new TracingManager(
                ['correlation_id' => $corrSource, 'request_id' => $reqSource],
                $this->storage,
                enabledMap: ['correlation_id' => false, 'request_id' => true],
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->all())
                ->toHaveKey('request_id')
                ->not->toHaveKey('correlation_id');
        });

        it('disables request_id while keeping correlation_id enabled', function () {
            $corrSource = Mockery::mock(TracingSource::class);
            $corrSource->shouldReceive('resolve')->once()->andReturn('corr-789');

            $reqSource = Mockery::mock(TracingSource::class);
            $reqSource->shouldNotReceive('resolve');

            $manager = new TracingManager(
                ['correlation_id' => $corrSource, 'request_id' => $reqSource],
                $this->storage,
                enabledMap: ['correlation_id' => true, 'request_id' => false],
            );

            $manager->resolveAll(Request::create('/'));

            expect($manager->all())
                ->toHaveKey('correlation_id')
                ->not->toHaveKey('request_id');
        });
    });

    describe('config-driven toggles', function () {
        it('reads global enabled state from config', function () {
            config(['laravel-tracing.enabled' => false]);

            expect(config('laravel-tracing.enabled'))->toBeFalse();
        });

        it('reads per-tracing enabled state from config', function () {
            config(['laravel-tracing.tracings.correlation_id.enabled' => false]);

            expect(config('laravel-tracing.tracings.correlation_id.enabled'))->toBeFalse();
        });

        it('reads per-tracing enabled state defaults to true', function () {
            expect(config('laravel-tracing.tracings.correlation_id.enabled'))->toBeTrue()
                ->and(config('laravel-tracing.tracings.request_id.enabled'))->toBeTrue();
        });
    });
});
