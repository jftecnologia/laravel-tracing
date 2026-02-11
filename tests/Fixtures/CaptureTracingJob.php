<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JuniorFontenele\LaravelTracing\Facades\LaravelTracing;

/**
 * Test job that captures tracing values during execution.
 *
 * Used to verify that tracing values are properly propagated from
 * request context to queued jobs.
 */
class CaptureTracingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public static ?array $capturedTracings = null;

    public function handle(): void
    {
        // Capture all tracing values available in job execution context
        self::$capturedTracings = LaravelTracing::all();
    }

    public static function reset(): void
    {
        self::$capturedTracings = null;
    }
}
