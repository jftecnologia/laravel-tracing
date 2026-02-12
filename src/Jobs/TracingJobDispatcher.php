<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelTracing\Jobs;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use JuniorFontenele\LaravelTracing\Tracings\TracingManager;

/**
 * Restores tracing values from queued job payloads.
 *
 * Handles the JobProcessing event to restore tracing values from the job
 * payload back into TracingManager during job execution.
 *
 * Tracing values are injected into payloads via Queue::createPayloadUsing()
 * hook registered in LaravelTracingServiceProvider.
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
