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
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        // Register middleware for testing
        $router->middleware('web', [
            \Illuminate\Session\Middleware\StartSession::class,
            IncomingTracingMiddleware::class,
            OutgoingTracingMiddleware::class,
        ]);

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
