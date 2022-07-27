<?php

declare(strict_types=1);

namespace Gaming\Common\Scheduler;

final class PcntlScheduler implements Scheduler
{
    private readonly Jobs $jobs;

    private bool $isInitialized;

    public function __construct()
    {
        $this->jobs = new Jobs();
        $this->isInitialized = false;
    }

    public function schedule(int $invokeAt, Handler $handler): void
    {
        $this->initialize();

        $this->jobs->add($invokeAt, $handler);

        $this->setAlarmClock();
    }

    private function initialize(): void
    {
        if ($this->isInitialized) {
            return;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, $this->onAlarm(...));

        $this->isInitialized = true;
    }

    private function onAlarm(): void
    {
        $collectJobsScheduler = new CollectJobsScheduler();

        $selectedJobs = $this->jobs->filter(
            static fn(Job $job) => $job->invokeAt <= time()
        );

        foreach ($selectedJobs as $job) {
            $job->handler->handle($collectJobsScheduler);
        }

        $this->jobs->remove(
            static fn(Job $job): bool => in_array($job, $selectedJobs)
        );

        $this->jobs->merge($collectJobsScheduler->jobs);

        $this->setAlarmClock();
    }

    private function setAlarmClock(): void
    {
        $nextJob = $this->jobs->next();
        if (!$nextJob) {
            return;
        }

        pcntl_alarm(max(1, $nextJob->invokeAt - time()));
    }
}
