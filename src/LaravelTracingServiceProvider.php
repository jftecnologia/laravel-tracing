<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelTracing\Middleware\IncomingTracingMiddleware;
use JuniorFontenele\LaravelTracing\Middleware\OutgoingTracingMiddleware;
use JuniorFontenele\LaravelTracing\Storage\RequestStorage;
use JuniorFontenele\LaravelTracing\Storage\SessionStorage;
use JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource;
use JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

class LaravelTracingServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-tracing.php' => config_path('laravel-tracing.php'),
        ], 'laravel-tracing-config');

        $this->registerMiddleware();
    }

    /**
     * Register tracing middleware in the HTTP kernel.
     *
     * Both incoming and outgoing middleware are registered globally to apply
     * to all HTTP requests. The middleware checks the global enabled toggle
     * internally, so registration is unconditional.
     */
    private function registerMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);

        // Register middlewares globally
        $kernel->pushMiddleware(IncomingTracingMiddleware::class);
        $kernel->pushMiddleware(OutgoingTracingMiddleware::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-tracing.php', 'laravel-tracing');

        $this->registerStorage();
        $this->registerTracingManager();
        $this->registerLaravelTracing();
    }

    /**
     * Register storage classes as singletons.
     */
    private function registerStorage(): void
    {
        $this->app->singleton(function (): \JuniorFontenele\LaravelTracing\Storage\RequestStorage {
            return new RequestStorage();
        });

        $this->app->singleton(function (): \JuniorFontenele\LaravelTracing\Storage\SessionStorage {
            return new SessionStorage();
        });
    }

    /**
     * Register TracingManager as singleton with all tracing sources.
     */
    private function registerTracingManager(): void
    {
        $this->app->singleton(function ($app): \JuniorFontenele\LaravelTracing\Tracings\TracingManager {
            $config = config('laravel-tracing');
            $tracingsConfig = $config['tracings'] ?? [];
            $acceptExternalHeaders = $config['accept_external_headers'] ?? true;
            $enabled = $config['enabled'] ?? true;

            $sources = [];
            $enabledMap = [];

            foreach ($tracingsConfig as $key => $tracingConfig) {
                $isEnabled = $tracingConfig['enabled'] ?? true;
                $enabledMap[$key] = $isEnabled;

                if (! $isEnabled) {
                    continue;
                }

                $sourceClass = $tracingConfig['source'] ?? null;
                $headerName = $tracingConfig['header'] ?? '';

                if (! $sourceClass || ! class_exists($sourceClass)) {
                    continue;
                }

                $sources[$key] = $this->instantiateSource(
                    $sourceClass,
                    $headerName,
                    $acceptExternalHeaders,
                    $app
                );
            }

            return new TracingManager(
                sources: $sources,
                storage: $app->make(RequestStorage::class),
                enabled: $enabled,
                enabledMap: $enabledMap
            );
        });
    }

    /**
     * Instantiate a tracing source with its dependencies.
     */
    private function instantiateSource(
        string $sourceClass,
        string $headerName,
        bool $acceptExternalHeaders,
        $app
    ) {
        // Handle built-in sources with known dependencies
        if ($sourceClass === CorrelationIdSource::class) {
            return new CorrelationIdSource(
                sessionStorage: $app->make(SessionStorage::class),
                acceptExternalHeaders: $acceptExternalHeaders,
                headerName: $headerName
            );
        }

        if ($sourceClass === RequestIdSource::class) {
            return new RequestIdSource(
                acceptExternalHeaders: $acceptExternalHeaders,
                headerName: $headerName
            );
        }

        // For custom sources, attempt to resolve from container
        return $app->make($sourceClass);
    }

    /**
     * Register LaravelTracing facade binding.
     */
    private function registerLaravelTracing(): void
    {
        $this->app->singleton(function ($app): \JuniorFontenele\LaravelTracing\LaravelTracing {
            return new LaravelTracing(
                manager: $app->make(TracingManager::class)
            );
        });
    }
}
