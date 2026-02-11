<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

use Illuminate\Contracts\Queue\Job;

/**
 * Mock job implementation for testing.
 *
 * Implements all required methods from Illuminate\Contracts\Queue\Job
 * with minimal functionality for testing purposes.
 */
class MockJob implements Job
{
    public function __construct(
        private array $jobPayload = []
    ) {
    }

    public function payload(): array
    {
        return $this->jobPayload;
    }

    public function getJobId(): ?string
    {
        return null;
    }

    public function getRawBody(): string
    {
        return json_encode($this->jobPayload);
    }

    public function attempts(): int
    {
        return 1;
    }

    public function markAsFailed(): void
    {
    }

    public function delete(): void
    {
    }

    public function isDeleted(): bool
    {
        return false;
    }

    public function isReleased(): bool
    {
        return false;
    }

    public function isDeletedOrReleased(): bool
    {
        return false;
    }

    public function release($delay = 0): void
    {
    }

    public function getConnectionName(): string
    {
        return 'sync';
    }

    public function getQueue(): string
    {
        return 'default';
    }

    public function getName(): string
    {
        return 'TestJob';
    }

    public function resolveName(): string
    {
        return 'TestJob';
    }

    public function uuid(): ?string
    {
        return null;
    }

    public function fire(): void
    {
    }

    public function hasFailed(): bool
    {
        return false;
    }

    public function fail($e = null): void
    {
    }

    public function maxTries(): ?int
    {
        return null;
    }

    public function maxExceptions(): ?int
    {
        return null;
    }

    public function delaySeconds(): ?int
    {
        return null;
    }

    public function retryUntil(): ?int
    {
        return null;
    }

    public function timeout(): ?int
    {
        return null;
    }

    public function failOnTimeout(): bool
    {
        return false;
    }

    public function resolveQueuedJobClass(): array
    {
        return ['TestJob', 'handle'];
    }
}
