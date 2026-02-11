<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueueing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelTracing\Http\HttpClientTracing;
use JuniorFontenele\LaravelTracing\Jobs\TracingJobDispatcher;
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

        // Early return if package is disabled
        if (! config('laravel-tracing.enabled', true)) {
            return;
        }

        $this->registerMiddleware();
        $this->registerJobEventListeners();
        $this->registerHttpClientMacro();
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
     * Register job event listeners for tracing propagation.
     *
     * Listens to Laravel's job lifecycle events to propagate tracing values
     * from request context to queued jobs and restore them during execution.
     */
    private function registerJobEventListeners(): void
    {
        Event::listen(JobQueueing::class, function (JobQueueing $event) {
            $dispatcher = $this->app->make(TracingJobDispatcher::class);
            $dispatcher->handleJobQueueing($event);
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            $dispatcher = $this->app->make(TracingJobDispatcher::class);
            $dispatcher->handleJobProcessing($event);
        });
    }

    /**
     * Register withTracing() macro on Http facade.
     *
     * Registers a macro that attaches tracing headers to outgoing HTTP
     * requests. The macro resolves HttpClientTracing from the container
     * and calls attachTracings() on the current PendingRequest instance.
     *
     * Additionally, if global HTTP client tracing is enabled via config,
     * registers a global middleware that attaches tracings to all requests.
     */
    private function registerHttpClientMacro(): void
    {
        // Per-request macro (always available)
        Http::macro('withTracing', function () {
            /** @var \Illuminate\Http\Client\PendingRequest $this */
            $tracing = app(HttpClientTracing::class);

            return $tracing->attachTracings($this);
        });

        // Global mode (opt-in via config)
        if (config('laravel-tracing.http_client.enabled', false)) {
            Http::globalRequestMiddleware(function ($request) {
                $tracing = app(HttpClientTracing::class);

                return $tracing->attachTracings($request);
            });
        }
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
        $this->app->singleton(function (): RequestStorage {
            return new RequestStorage();
        });

        $this->app->singleton(function (): SessionStorage {
            return new SessionStorage();
        });
    }

    /**
     * Register TracingManager as singleton with all tracing sources.
     */
    private function registerTracingManager(): void
    {
        $this->app->singleton(function ($app): TracingManager {
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

                if (! $sourceClass) {
                    Log::warning("Tracing source '{$key}' is missing 'source' class definition");

                    continue;
                }

                if (! class_exists($sourceClass)) {
                    Log::warning("Tracing source class '{$sourceClass}' for '{$key}' does not exist");

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
        $this->app->singleton(function ($app): LaravelTracing {
            return new LaravelTracing(
                manager: $app->make(TracingManager::class)
            );
        });
    }
}
