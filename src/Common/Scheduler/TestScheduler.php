<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

/**
 * This class can be used for testing purposes.
 */
final class TestScheduler implements Scheduler
{
    private readonly Jobs $jobs;

    public function __construct()
    {
        $this->jobs = new Jobs();
    }

    public function schedule(int $invokeAt, Handler $handler): void
    {
        $this->jobs->add($invokeAt, $handler);
    }

    public function invokePendingJobs(): void
    {
        $jobs = $this->allJobs();

        foreach ($jobs as $job) {
            $job->handler->handle($this);
        }

        $this->jobs->remove(fn(Job $job) => in_array($job, $jobs, true));
    }

    public function numberOfPendingJobs(): int
    {
        return count($this->allJobs());
    }

    /**
     * @return array<Job>
     */
    private function allJobs(): array
    {
        return $this->jobs->filter(fn (Job $job) => true);
    }
}
