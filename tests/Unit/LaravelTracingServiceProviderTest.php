<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Log;
use JuniorFontenele\LaravelTracing\LaravelTracing;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

describe('LaravelTracingServiceProvider', function () {
    describe('service registration', function () {
        it('registers TracingManager as singleton', function () {
            $manager1 = app(TracingManager::class);
            $manager2 = app(TracingManager::class);

            expect($manager1)->toBeInstanceOf(TracingManager::class)
                ->and($manager2)->toBe($manager1);
        });

        it('registers RequestStorage as singleton', function () {
            $storage1 = app(RequestStorage::class);
            $storage2 = app(RequestStorage::class);

            expect($storage1)->toBeInstanceOf(RequestStorage::class)
                ->and($storage2)->toBe($storage1);
        });

        it('registers SessionStorage as singleton', function () {
            $storage1 = app(SessionStorage::class);
            $storage2 = app(SessionStorage::class);

            expect($storage1)->toBeInstanceOf(SessionStorage::class)
                ->and($storage2)->toBe($storage1);
        });

        it('registers LaravelTracing as singleton', function () {
            $tracing1 = app(LaravelTracing::class);
            $tracing2 = app(LaravelTracing::class);

            expect($tracing1)->toBeInstanceOf(LaravelTracing::class)
                ->and($tracing2)->toBe($tracing1);
        });

        it('makes TracingManager resolvable from container', function () {
            $manager = app(TracingManager::class);

            expect($manager)->toBeInstanceOf(TracingManager::class)
                ->and($manager->isEnabled())->toBeTrue();
        });
    });

    describe('tracing source registration', function () {
        it('registers built-in correlation_id source from config', function () {
            $manager = app(TracingManager::class);

            // Resolve sources first
            $manager->resolveAll(Illuminate\Http\Request::create('/'));

            // Check that correlation_id is registered
            expect($manager->has('correlation_id'))->toBeTrue();
        });

        it('registers built-in request_id source from config', function () {
            $manager = app(TracingManager::class);

            // Resolve sources first
            $manager->resolveAll(Illuminate\Http\Request::create('/'));

            // Check that request_id is registered
            expect($manager->has('request_id'))->toBeTrue();
        });

        it('does not register disabled sources', function () {
            config(['laravel-tracing.tracings.correlation_id.enabled' => false]);

            // Need to re-register the service provider to pick up config change
            app()->forgetInstance(TracingManager::class);
            $manager = app(TracingManager::class);

            // Source should not be registered
            $allTracings = $manager->all();
            expect($allTracings)->not->toHaveKey('correlation_id');
        });

        it('logs warning when source class is missing', function () {
            Log::shouldReceive('warning')
                ->once()
                ->with("Tracing source 'custom_trace' is missing 'source' class definition");

            config([
                'laravel-tracing.tracings.custom_trace' => [
                    'enabled' => true,
                    'header' => 'X-Custom-Trace',
                    // 'source' is missing
                ],
            ]);

            app()->forgetInstance(TracingManager::class);
            app(TracingManager::class);
        });

        it('logs warning when source class does not exist', function () {
            Log::shouldReceive('warning')
                ->once()
                ->with("Tracing source class 'App\NonExistentSource' for 'custom_trace' does not exist");

            config([
                'laravel-tracing.tracings.custom_trace' => [
                    'enabled' => true,
                    'header' => 'X-Custom-Trace',
                    'source' => 'App\NonExistentSource',
                ],
            ]);

            app()->forgetInstance(TracingManager::class);
            app(TracingManager::class);
        });

        it('skips invalid sources gracefully without breaking registration', function () {
            config([
                'laravel-tracing.tracings.invalid' => [
                    'enabled' => true,
                    'header' => 'X-Invalid',
                    'source' => 'NonExistentClass',
                ],
            ]);

            app()->forgetInstance(TracingManager::class);
            $manager = app(TracingManager::class);

            // Resolve sources
            $manager->resolveAll(Illuminate\Http\Request::create('/'));

            // Manager should still work with valid sources
            expect($manager)->toBeInstanceOf(TracingManager::class)
                ->and($manager->has('correlation_id'))->toBeTrue()
                ->and($manager->has('request_id'))->toBeTrue();
        });
    });

    describe('config merging', function () {
        it('merges default config from package', function () {
            expect(config('laravel-tracing'))->toBeArray()
                ->and(config('laravel-tracing.enabled'))->toBeTrue()
                ->and(config('laravel-tracing.tracings'))->toBeArray();
        });

        it('respects global enabled state from config', function () {
            config(['laravel-tracing.enabled' => false]);

            app()->forgetInstance(TracingManager::class);
            $manager = app(TracingManager::class);

            expect($manager->isEnabled())->toBeFalse();
        });
    });
});
