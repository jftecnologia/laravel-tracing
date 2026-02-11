# A Laravel package by Junior Fontenele

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jftecnologia/laravel-tracing.svg?style=flat-square)](https://packagist.org/packages/jftecnologia/laravel-tracing)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jftecnologia/laravel-tracing/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jftecnologia/laravel-tracing/actions?query=workflow%3Atests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jftecnologia/laravel-tracing/fix-php-code-style.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jftecnologia/laravel-tracing/actions?query=workflow%3A"fix-php-code-style-issues"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jftecnologia/laravel-tracing.svg?style=flat-square)](https://packagist.org/packages/jftecnologia/laravel-tracing)
<!--delete-->
---
This repo can be used to scaffold a Laravel package. Follow these steps to get started:

1. Press the "Use this template" button at the top of this repo to create a new repo with the contents of this skeleton.
2. Run "php ./configure.php" to run a script that will replace all placeholders throughout all the files.
3. Have fun creating your package.

<!--/delete-->
This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require jftecnologia/laravel-tracing
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-tracing-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-tracing-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-tracing-views"
```

## Usage

```php
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

// Get correlation ID
$correlationId = LaravelTracing::correlationId();

// Get request ID
$requestId = LaravelTracing::requestId();

// Get all tracing values
$allTracings = LaravelTracing::all();

// Get specific tracing by key
$value = LaravelTracing::get('user_id');

// Check if tracing exists
if (LaravelTracing::has('tenant_id')) {
    // ...
}
```

## Extending the Package

Laravel Tracing is designed to be fully extensible. You can add custom tracing sources to track additional context like user IDs, tenant IDs, or application versions.

### Quick Example: Add User ID Tracing

Register a custom tracing source at runtime in your service provider:

```php
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

app(TracingManager::class)->extend('user_id', new UserIdSource());
```

**For complete documentation on extending the package, see:**
- [Extension Architecture Documentation](docs/architecture/EXTENSIONS.md)

The extension documentation covers:
- Implementing custom tracing sources
- Config-based vs runtime registration
- Replacing built-in sources
- Complete working examples
- Best practices

## Testing

```bash
composer test
```

## Credits

- [Junior Fontenele](https://github.com/jftecnologia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
