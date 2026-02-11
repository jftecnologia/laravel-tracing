<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelTracing\Middleware\IncomingTracingMiddleware;
use JuniorFontenele\LaravelTracing\Middleware\OutgoingTracingMiddleware;

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
        $router = $this->app->make(Router::class);

        // Register incoming middleware (early in stack)
        $router->aliasMiddleware('tracing.incoming', IncomingTracingMiddleware::class);
        $router->prependMiddlewareToGroup('web', 'tracing.incoming');
        $router->prependMiddlewareToGroup('api', 'tracing.incoming');

        // Register outgoing middleware (late in stack)
        $router->aliasMiddleware('tracing.outgoing', OutgoingTracingMiddleware::class);
        $router->pushMiddlewareToGroup('web', 'tracing.outgoing');
        $router->pushMiddlewareToGroup('api', 'tracing.outgoing');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-tracing.php', 'laravel-tracing');
    }
}
