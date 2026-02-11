<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelTracing\LaravelTracing;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

describe('LaravelTracing', function () {
    beforeEach(function () {
        $this->manager = Mockery::mock(TracingManager::class);
        $this->tracing = new LaravelTracing($this->manager);
    });

    it('delegates all() to TracingManager', function () {
        $expected = ['correlation_id' => 'id-1', 'request_id' => 'id-2'];

        $this->manager->shouldReceive('all')->once()->andReturn($expected);

        expect($this->tracing->all())->toBe($expected);
    });

    it('delegates get() to TracingManager', function () {
        $this->manager->shouldReceive('get')
            ->with('correlation_id')
            ->once()
            ->andReturn('id-1');

        expect($this->tracing->get('correlation_id'))->toBe('id-1');
    });

    it('delegates has() to TracingManager', function () {
        $this->manager->shouldReceive('has')
            ->with('request_id')
            ->once()
            ->andReturn(true);

        expect($this->tracing->has('request_id'))->toBeTrue();
    });

    it('returns correlation ID via correlationId()', function () {
        $this->manager->shouldReceive('get')
            ->with('correlation_id')
            ->once()
            ->andReturn('corr-123');

        expect($this->tracing->correlationId())->toBe('corr-123');
    });

    it('returns request ID via requestId()', function () {
        $this->manager->shouldReceive('get')
            ->with('request_id')
            ->once()
            ->andReturn('req-456');

        expect($this->tracing->requestId())->toBe('req-456');
    });

    it('returns null when correlation ID is not set', function () {
        $this->manager->shouldReceive('get')
            ->with('correlation_id')
            ->once()
            ->andReturn(null);

        expect($this->tracing->correlationId())->toBeNull();
    });

    it('returns null when request ID is not set', function () {
        $this->manager->shouldReceive('get')
            ->with('request_id')
            ->once()
            ->andReturn(null);

        expect($this->tracing->requestId())->toBeNull();
    });
});
