<?php

declare(strict_types = 1);

namespace Tests;

use JuniorFontenele\LaravelTracing\Middleware\IncomingTracingMiddleware;
use JuniorFontenele\LaravelTracing\Middleware\OutgoingTracingMiddleware;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Get the kernel and add middleware to the web group
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        // Register the middleware in web group
        $kernel->appendMiddlewareToGroup('web', IncomingTracingMiddleware::class);
        $kernel->appendMiddlewareToGroup('web', OutgoingTracingMiddleware::class);
    }

    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        // Root test endpoint
        $router->get('/', function () {
            return response()->json(['status' => 'ok']);
        })->middleware('web');

        // Secondary test endpoint
        $router->get('/test', function () {
            return response()->json(['status' => 'test']);
        })->middleware('web');
    }
}
