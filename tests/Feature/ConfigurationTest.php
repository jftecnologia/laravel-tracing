<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Config;

describe('Configuration', function () {
    describe('default values', function () {
        it('loads default config values without publishing', function () {
            expect(config('laravel-tracing'))->toBeArray()
                ->and(config('laravel-tracing'))->toHaveKeys([
                    'enabled',
                    'accept_external_headers',
                    'tracings',
                    'http_client',
                ]);
        });

        it('has enabled set to true by default', function () {
            expect(config('laravel-tracing.enabled'))->toBeTrue();
        });

        it('has accept_external_headers set to true by default', function () {
            expect(config('laravel-tracing.accept_external_headers'))->toBeTrue();
        });

        it('has correlation_id tracing configured by default', function () {
            $config = config('laravel-tracing.tracings.correlation_id');

            expect($config)->toBeArray()
                ->and($config)->toHaveKeys(['enabled', 'header', 'source'])
                ->and($config['enabled'])->toBeTrue()
                ->and($config['header'])->toBe('X-Correlation-Id')
                ->and($config['source'])->toBe('JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource');
        });

        it('has request_id tracing configured by default', function () {
            $config = config('laravel-tracing.tracings.request_id');

            expect($config)->toBeArray()
                ->and($config)->toHaveKeys(['enabled', 'header', 'source'])
                ->and($config['enabled'])->toBeTrue()
                ->and($config['header'])->toBe('X-Request-Id')
                ->and($config['source'])->toBe('JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource');
        });

        it('has http_client disabled by default', function () {
            expect(config('laravel-tracing.http_client.enabled'))->toBeFalse();
        });
    });

    describe('publishable config', function () {
        it('registers config for publishing with correct tag', function () {
            $publishable = Illuminate\Support\ServiceProvider::$publishes;

            $found = false;

            foreach ($publishable as $paths) {
                foreach ($paths as $source => $destination) {
                    if (str_contains($source, 'config/laravel-tracing.php')) {
                        $found = true;

                        break 2;
                    }
                }
            }

            expect($found)->toBeTrue();
        });
    });

    describe('custom header names via config', function () {
        it('allows custom correlation ID header name', function () {
            Config::set('laravel-tracing.tracings.correlation_id.header', 'X-Custom-Correlation');

            expect(config('laravel-tracing.tracings.correlation_id.header'))
                ->toBe('X-Custom-Correlation');
        });

        it('allows custom request ID header name', function () {
            Config::set('laravel-tracing.tracings.request_id.header', 'X-Custom-Request');

            expect(config('laravel-tracing.tracings.request_id.header'))
                ->toBe('X-Custom-Request');
        });
    });

    describe('external header acceptance toggle', function () {
        it('can be disabled via config', function () {
            Config::set('laravel-tracing.accept_external_headers', false);

            expect(config('laravel-tracing.accept_external_headers'))->toBeFalse();
        });

        it('is enabled by default', function () {
            expect(config('laravel-tracing.accept_external_headers'))->toBeTrue();
        });
    });
});
