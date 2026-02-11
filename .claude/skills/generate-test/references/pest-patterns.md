# PestPHP Test Patterns

Reference for common PestPHP testing patterns in Laravel packages.

---

## Basic Assertions

### Expect API

```php
// Equality
expect($value)->toBe('exact');           // ===
expect($value)->toEqual('loose');        // ==
expect($value)->not->toBe('other');      // negation

// Type checks
expect($value)->toBeString();
expect($value)->toBeInt();
expect($value)->toBeArray();
expect($value)->toBeBool();
expect($value)->toBeNull();
expect($value)->toBeInstanceOf(MyClass::class);

// Truthiness
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeTruthy();
expect($value)->toBeFalsy();
expect($value)->toBeEmpty();
expect($value)->not->toBeEmpty();

// Numeric
expect($value)->toBeGreaterThan(5);
expect($value)->toBeLessThan(10);
expect($value)->toBeBetween(1, 100);

// Strings
expect($value)->toContain('substring');
expect($value)->toStartWith('prefix');
expect($value)->toEndWith('suffix');
expect($value)->toMatch('/regex/');

// Arrays
expect($array)->toHaveKey('key');
expect($array)->toHaveKeys(['a', 'b']);
expect($array)->toContain('item');
expect($array)->toHaveCount(3);
```

---

## Testing Exceptions

```php
// Expect any exception
it('throws on invalid input', function () {
    expect(fn () => $obj->method(null))
        ->toThrow(Exception::class);
});

// Expect specific exception with message
it('throws with specific message', function () {
    expect(fn () => $obj->method('bad'))
        ->toThrow(InvalidArgumentException::class, 'Invalid value');
});

// Using Pest's throws helper
it('throws exception', function () {
    $this->expectException(CustomException::class);
    
    $obj->methodThatThrows();
});
```

---

## Laravel HTTP Testing

```php
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withHeaders;

// Basic request
it('returns ok response', function () {
    get('/')->assertOk();
});

// With headers
it('accepts custom header', function () {
    get('/', ['X-Custom-Header' => 'value'])
        ->assertOk();
});

// Using withHeaders
it('sends with headers', function () {
    withHeaders(['Authorization' => 'Bearer token'])
        ->get('/protected')
        ->assertOk();
});

// Assert response headers
it('includes correlation ID in response', function () {
    get('/')
        ->assertHeader('X-Correlation-Id')
        ->assertHeader('X-Request-Id', 'expected-value');
});

// Assert header missing
it('excludes header when disabled', function () {
    config(['feature.enabled' => false]);
    
    get('/')->assertHeaderMissing('X-Feature-Header');
});

// Assert JSON
it('returns JSON data', function () {
    get('/api/data')
        ->assertJson(['status' => 'ok'])
        ->assertJsonPath('data.id', 123);
});
```

---

## Mocking with Laravel

### HTTP Fake

```php
use Illuminate\Support\Facades\Http;

it('makes external API call', function () {
    Http::fake([
        'api.example.com/*' => Http::response(['data' => 'value'], 200),
    ]);
    
    // Code that makes HTTP request
    $result = $service->fetchData();
    
    expect($result)->toBe('value');
    
    Http::assertSent(fn ($request) => 
        $request->url() === 'https://api.example.com/endpoint'
    );
});
```

### Queue Fake

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessJob;

it('dispatches job with context', function () {
    Queue::fake();
    
    // Code that dispatches job
    ProcessJob::dispatch(['data' => 'value']);
    
    Queue::assertPushed(ProcessJob::class, fn ($job) =>
        $job->data === ['data' => 'value']
    );
});
```

### Event Fake

```php
use Illuminate\Support\Facades\Event;
use App\Events\TracingStarted;

it('fires tracing event', function () {
    Event::fake();
    
    // Code that fires event
    
    Event::assertDispatched(TracingStarted::class);
});
```

### Cache Fake

```php
use Illuminate\Support\Facades\Cache;

it('caches correlation ID', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('correlation_id')
        ->andReturn('cached-id');
    
    $result = $service->getCorrelationId();
    
    expect($result)->toBe('cached-id');
});
```

---

## Setup and Teardown

### beforeEach / afterEach

```php
beforeEach(function () {
    // Runs before each test
    config(['laravel-tracing.enabled' => true]);
    $this->service = new TracingService();
});

afterEach(function () {
    // Runs after each test
    Mockery::close();
});

it('uses setup service', function () {
    expect($this->service)->toBeInstanceOf(TracingService::class);
});
```

### beforeAll / afterAll

```php
beforeAll(function () {
    // Runs once before all tests in file
});

afterAll(function () {
    // Runs once after all tests in file
});
```

---

## Datasets

### Inline Dataset

```php
it('validates headers', function (string $header, bool $expected) {
    expect(isValidHeader($header))->toBe($expected);
})->with([
    ['X-Correlation-Id', true],
    ['X-Request-Id', true],
    ['', false],
    ['Invalid!', false],
]);
```

### Named Dataset

```php
dataset('valid_headers', [
    'correlation' => ['X-Correlation-Id'],
    'request' => ['X-Request-Id'],
    'trace' => ['X-Trace-Id'],
]);

it('accepts valid headers', function (string $header) {
    expect(isValidHeader($header))->toBeTrue();
})->with('valid_headers');
```

### Combined Datasets

```php
it('combines inputs', function (string $header, string $value) {
    // Test with all combinations
})->with([
    ['X-Correlation-Id', 'X-Request-Id'],
])->with([
    ['value-1', 'value-2'],
]);
```

---

## Grouping Tests

### describe()

```php
describe('TracingService', function () {
    
    describe('getCorrelationId()', function () {
        it('returns existing ID from session', function () {
            // ...
        });
        
        it('generates new ID when none exists', function () {
            // ...
        });
    });
    
    describe('setCorrelationId()', function () {
        it('stores ID in session', function () {
            // ...
        });
    });
});
```

---

## Skipping and Focusing

```php
// Skip test
it('skipped test', function () {
    // ...
})->skip();

// Skip with reason
it('skipped with reason', function () {
    // ...
})->skip('Not implemented yet');

// Skip conditionally
it('skipped on CI', function () {
    // ...
})->skipOnCI();

// Focus (only run this test)
it('focused test', function () {
    // ...
})->only();

// Mark as TODO
it('todo test', function () {
    // ...
})->todo();
```

---

## Time Testing

```php
use Illuminate\Support\Carbon;

it('checks time-based logic', function () {
    Carbon::setTestNow('2025-01-01 12:00:00');
    
    $result = $service->getTimestamp();
    
    expect($result)->toBe('2025-01-01 12:00:00');
    
    Carbon::setTestNow(); // Reset
});
```

---

## Testing Traits

```php
// In TestCase.php or Pest.php
uses(RefreshDatabase::class)->in('Feature');
uses(WithFaker::class)->in('Feature', 'Unit');

// In test file
it('uses faker', function () {
    $name = fake()->name();
    
    expect($name)->toBeString();
});
```

---

## Higher Order Tests

```php
// Shorthand for simple tests
test('true is true')->assertTrue(true);

// With expect
test('array has key')
    ->expect(['key' => 'value'])
    ->toHaveKey('key');

// Chained
test('string checks')
    ->expect('hello world')
    ->toContain('hello')
    ->toContain('world');
```

---

## Architecture Tests

```php
// Ensure no dependencies leak
arch('services do not depend on controllers')
    ->expect('App\Services')
    ->not->toUse('App\Http\Controllers');

// Ensure naming conventions
arch('interfaces have Interface suffix')
    ->expect('App\Contracts')
    ->toBeInterfaces()
    ->toHaveSuffix('Interface');
```

---

## Package-Specific Patterns

### Testing Service Provider

```php
it('registers service in container', function () {
    expect(app()->bound(TracingService::class))->toBeTrue();
});

it('registers facade', function () {
    expect(LaravelTracing::getFacadeRoot())
        ->toBeInstanceOf(TracingService::class);
});
```

### Testing Config Publishing

```php
it('publishes config file', function () {
    $this->artisan('vendor:publish', [
        '--tag' => 'laravel-tracing-config',
    ]);
    
    expect(config_path('laravel-tracing.php'))->toBeFile();
});
```

### Testing Middleware Registration

```php
it('registers middleware', function () {
    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
    
    expect($kernel->hasMiddleware(TracingMiddleware::class))->toBeTrue();
});
```
