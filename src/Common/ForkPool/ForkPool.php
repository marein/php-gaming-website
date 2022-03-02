<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Gaming\Common\ForkPool\Exception\ForkPoolException;
use Gaming\Common\ForkPool\Channel\ChannelPairFactory;

final class ForkPool
{
    private Processes $processes;

    public function __construct(
        private readonly ChannelPairFactory $channelPairFactory
    ) {
        $this->processes = new Processes();
    }

    /**
     * @throws ForkPoolException
     */
    public function fork(Task $task): Process
    {
        $channelPair = $this->channelPairFactory->create();

        $forkPid = pcntl_fork();

        return match ($forkPid) {
            -1 => throw new ForkPoolException('Unable to fork.'),
            0 => exit($task->execute($channelPair->parent())),
            default => $this->processes->add($forkPid, $channelPair->fork())
        };
    }

    public function signal(): Signal
    {
        return new Signal($this);
    }

    public function wait(): Wait
    {
        return new Wait($this, $this->processes);
    }

    public function kill(int $signal): ForkPool
    {
        $this->processes->kill($signal);

        return $this;
    }
}
