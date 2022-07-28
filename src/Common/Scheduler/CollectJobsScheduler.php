<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

final class CollectJobsScheduler implements Scheduler
{
    public readonly Jobs $jobs;

    public function __construct()
    {
        $this->jobs = new Jobs();
    }

    public function schedule(int $invokeAt, Handler $handler): void
    {
        $this->jobs->add($invokeAt, $handler);
    }
}
