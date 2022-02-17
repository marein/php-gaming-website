<?php

declare(strict_types=1);

namespace Gaming\Common\ForkManager;

use Gaming\Common\ForkManager\Exception\ForkManagerException;

final class ForkManager
{
    /**
     * @var Process[]
     */
    private array $forks;

    public function __construct()
    {
        $this->forks = [];
    }

    /**
     * @throws ForkManagerException
     */
    public function fork(Task $task): Process
    {
        $streamPair = StreamPair::create();

        $parentPid = getmypid();
        $forkPid = pcntl_fork();

        return match ($forkPid) {
            -1 => throw new ForkManagerException('Unable to fork.'),
            0 => $this->runTaskAndExit($parentPid, $task, $streamPair),
            default => $this->registerFork($forkPid, $streamPair)
        };
    }

    public function wait(): void
    {
        pcntl_async_signals(true);
        $signalHandler = function () {
            foreach ($this->forks as $fork) {
                $fork->terminate();
            }
        };
        pcntl_signal(SIGTERM, $signalHandler, false);
        pcntl_signal(SIGINT, $signalHandler, false);

        while (pcntl_wait($status) !== -1) {
        }
    }

    /**
     * @throws ForkManagerException
     */
    private function registerFork(int $forkPid, StreamPair $streamPair): Process
    {
        $streamPair->parent()->close();

        $fork = new Process($forkPid, $streamPair->fork());
        $this->forks[] = $fork;

        return $fork;
    }

    /*
     * @throws ForkManagerException
     */
    private function runTaskAndExit(int $parentPid, Task $task, StreamPair $streamPair): never
    {
        $streamPair->fork()->close();

        exit($task->execute(new Process($parentPid, $streamPair->parent())));
    }
}
