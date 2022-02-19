<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;

final class ForkControl
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
     * @throws ForkControlException
     */
    public function fork(Task $task): Process
    {
        $streamPair = StreamPair::create();

        $parentPid = getmypid();
        if ($parentPid === false) {
            throw new ForkControlException('Unable to get the process id.');
        }

        $forkPid = pcntl_fork();

        return match ($forkPid) {
            -1 => throw new ForkControlException('Unable to fork.'),
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
     * @throws ForkControlException
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
