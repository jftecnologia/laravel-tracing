<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Jobs;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueueing;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

/**
 * Propagates tracing values from request context to queued jobs.
 *
 * Listens to Laravel's job lifecycle events and handles tracing propagation:
 * - On JobQueueing: serializes all current tracing values and attaches to job payload
 * - On JobProcessing: restores tracing values from job payload into TracingManager
 *
 * This enables full tracing continuity across asynchronous job execution.
 */
class TracingJobDispatcher
{
    public function __construct(
        private readonly TracingManager $manager
    ) {
    }

    /**
     * Handle job queuing event - serialize tracings to job payload.
     *
     * Reads all current tracing values from the manager and attaches them
     * to the job payload under the 'tracings' key.
     */
    public function handleJobQueueing(JobQueueing $event): void
    {
        if (! $this->manager->isEnabled()) {
            return;
        }

        $tracings = $this->manager->all();

        // Decode existing payload
        $payload = $event->payload();

        // Attach tracings to payload
        $payload['tracings'] = $tracings;

        // Re-encode payload
        $event->payload = json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * Handle job processing event - restore tracings from job payload.
     *
     * Reads tracing values from the job payload and restores them to the manager,
     * preserving the original request ID and all custom tracings.
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        if (! $this->manager->isEnabled()) {
            return;
        }

        // Extract tracings from job payload
        $payload = $this->getJobPayload($event->job);
        $tracings = $payload['tracings'] ?? [];

        if (empty($tracings)) {
            return;
        }

        // Restore tracings to manager
        $this->manager->restore($tracings);
    }

    /**
     * Get the decoded job payload.
     *
     * @return array<string, mixed>
     */
    private function getJobPayload(Job $job): array
    {
        return $job->payload();
    }
}
