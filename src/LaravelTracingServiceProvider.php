<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing;

use Illuminate\Support\ServiceProvider;

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
