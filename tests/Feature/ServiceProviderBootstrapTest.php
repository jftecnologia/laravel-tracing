<?php

declare(strict_types = 1);

describe('Service Provider Bootstrap', function () {
    describe('middleware registration', function () {
        it('registers and executes incoming tracing middleware when package is enabled', function () {
            config(['laravel-tracing.enabled' => true]);

            $response = $this->get('/');

            // If middleware is registered and executed, headers will be present
            $response->assertOk();
            $response->assertHeader('X-Correlation-Id');
            $response->assertHeader('X-Request-Id');
        });

        it('registers and executes outgoing tracing middleware when package is enabled', function () {
            config(['laravel-tracing.enabled' => true]);

            $response = $this->get('/');

            // Outgoing middleware attaches headers to response
            $response->assertOk();
            $response->assertHeader('X-Correlation-Id');
            $response->assertHeader('X-Request-Id');
        });

    });

    describe('config publishing', function () {
        it('registers config file for publishing', function () {
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

        it('publishes config to correct destination', function () {
            $publishable = Illuminate\Support\ServiceProvider::$publishes;

            $destination = null;

            foreach ($publishable as $paths) {
                foreach ($paths as $source => $dest) {
                    if (str_contains($source, 'config/laravel-tracing.php')) {
                        $destination = $dest;

                        break 2;
                    }
                }
            }

            expect($destination)->toContain('config/laravel-tracing.php');
        });
    });

    describe('package enable/disable behavior', function () {
        it('attaches tracing headers when package is enabled', function () {
            config(['laravel-tracing.enabled' => true]);

            $response = $this->get('/');

            $response->assertOk();
            $response->assertHeader('X-Correlation-Id');
            $response->assertHeader('X-Request-Id');
        });


        it('respects LARAVEL_TRACING_ENABLED environment variable', function () {
            putenv('LARAVEL_TRACING_ENABLED=false');

            // Refresh application to pick up env change
            $this->refreshApplication();

            $response = $this->get('/');

            $response->assertHeaderMissing('X-Correlation-Id');
            $response->assertHeaderMissing('X-Request-Id');

            // Clean up
            putenv('LARAVEL_TRACING_ENABLED');
        });
    });

    describe('full bootstrap integration', function () {
        it('successfully bootstraps all services and middleware', function () {
            $response = $this->get('/');

            // Package should be fully bootstrapped
            $response->assertOk();
            $response->assertHeader('X-Correlation-Id');
            $response->assertHeader('X-Request-Id');

            // TracingManager should be available
            expect(app('JuniorFontenele\LaravelTracing\Tracings\TracingManager'))
                ->toBeInstanceOf('JuniorFontenele\LaravelTracing\Tracings\TracingManager');
        });

        it('makes LaravelTracing facade functional', function () {
            $this->get('/');

            $facade = app('JuniorFontenele\LaravelTracing\LaravelTracing');

            expect($facade)->toBeInstanceOf('JuniorFontenele\LaravelTracing\LaravelTracing')
                ->and($facade->correlationId())->toBeString()
                ->and($facade->requestId())->toBeString();
        });
    });
});
