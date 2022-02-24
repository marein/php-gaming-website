<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;
use Gaming\Common\ForkControl\Queue\QueuePair;
use Gaming\Common\ForkControl\Queue\QueuePairFactory;

final class ForkControl
{
    /**
     * @var Process[]
     */
    private array $forks;

    public function __construct(
        private readonly QueuePairFactory $queuePairFactory
    ) {
        $this->forks = [];
    }

    /**
     * @throws ForkControlException
     */
    public function fork(Task $task): Process
    {
        $queuePair = $this->queuePairFactory->create();

        $parentPid = getmypid();
        if ($parentPid === false) {
            throw new ForkControlException('Unable to get the process id.');
        }

        $forkPid = pcntl_fork();

        return match ($forkPid) {
            -1 => throw new ForkControlException('Unable to fork.'),
            0 => $this->runTaskAndExit($parentPid, $task, $queuePair),
            default => $this->registerFork($forkPid, $queuePair)
        };
    }

    public function signal(): Signal
    {
        return new Signal();
    }

    public function wait(): Wait
    {
        return new Wait($this);
    }

    public function terminate(): ForkControl
    {
        foreach ($this->forks as $fork) {
            $fork->terminate();
        }

        return $this;
    }

    private function registerFork(int $forkPid, QueuePair $queuePair): Process
    {
        $fork = new Process($forkPid, $queuePair->fork());
        $this->forks[] = $fork;

        return $fork;
    }

    /*
     * @throws ForkManagerException
     */
    private function runTaskAndExit(int $parentPid, Task $task, QueuePair $queuePair): never
    {
        exit($task->execute(new Process($parentPid, $queuePair->parent())));
    }
}
