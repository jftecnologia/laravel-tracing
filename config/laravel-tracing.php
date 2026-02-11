<?php

declare(strict_types = 1);

return [

    /*
    |--------------------------------------------------------------------------
    | Global Enable/Disable
    |--------------------------------------------------------------------------
    |
    | Master switch for the entire tracing package. When set to false, all
    | tracing operations are completely skipped, providing zero overhead.
    | Useful for disabling tracing in specific environments.
    |
    */

    'enabled' => env('LARAVEL_TRACING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Accept External Headers
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will read tracing values from incoming HTTP
    | request headers (e.g., from an upstream service or API gateway).
    | When disabled, tracing values are always generated fresh, ignoring
    | any external headers sent with the request.
    |
    */

    'accept_external_headers' => env('LARAVEL_TRACING_ACCEPT_EXTERNAL_HEADERS', true),

    /*
    |--------------------------------------------------------------------------
    | Tracing Sources
    |--------------------------------------------------------------------------
    |
    | Define all tracing sources here. Each entry represents a named tracing
    | value that will be resolved from incoming requests and propagated
    | through the application.
    |
    | Each tracing entry supports the following keys:
    |
    |   - enabled: (bool) Whether this tracing source is active.
    |   - header:  (string) The HTTP header name used to read/write this value.
    |   - source:  (string) The TracingSource class responsible for resolving
    |              the value. Must implement the TracingSource contract.
    |
    | You can add custom tracing sources by adding entries here or by using
    | TracingManager::extend() at runtime.
    |
    */

    'tracings' => [

        'correlation_id' => [
            'enabled' => true,
            'header' => env('LARAVEL_TRACING_CORRELATION_ID_HEADER', 'X-Correlation-Id'),
            'source' => 'JuniorFontenele\LaravelTracing\Tracings\Sources\CorrelationIdSource',
        ],

        'request_id' => [
            'enabled' => true,
            'header' => env('LARAVEL_TRACING_REQUEST_ID_HEADER', 'X-Request-Id'),
            'source' => 'JuniorFontenele\LaravelTracing\Tracings\Sources\RequestIdSource',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Integration
    |--------------------------------------------------------------------------
    |
    | When enabled, tracing headers are automatically attached to outgoing
    | HTTP requests made via Laravel's HTTP client (Http::withTracing()).
    | This is opt-in to avoid unintended header propagation to external
    | services.
    |
    */

    'http_client' => [
        'enabled' => env('LARAVEL_TRACING_HTTP_CLIENT_ENABLED', false),
    ],

];
