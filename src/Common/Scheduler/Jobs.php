<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

use Closure;

final class Jobs
{
    /**
     * @var Job[]
     */
    private array $jobs;

    public function __construct()
    {
        $this->jobs = [];
    }

    public function add(int $invokeAt, Handler $handler): void
    {
        $this->jobs[] = new Job($invokeAt, $handler);
    }

    /**
     * @param Closure(Job): bool $closure
     */
    public function remove(Closure $closure): void
    {
        $this->jobs = array_filter(
            $this->jobs,
            static fn(Job $job): bool => !$closure($job)
        );
    }

    /**
     * @return array<Job>
     */
    public function filter(Closure $closure): array
    {
        return array_filter($this->jobs, $closure);
    }

    public function merge(Jobs $jobs): void
    {
        $this->jobs = array_merge(
            $this->jobs,
            $jobs->jobs
        );
    }

    public function next(): Job|false
    {
        usort(
            $this->jobs,
            static fn(Job $left, Job $right): int => $left->invokeAt <=> $right->invokeAt
        );

        return reset($this->jobs);
    }
}
