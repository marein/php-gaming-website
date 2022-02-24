<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;
use Gaming\Common\ForkControl\Channel\ChannelPair;
use Gaming\Common\ForkControl\Channel\ChannelPairFactory;

final class ForkControl
{
    private Processes $processes;

    public function __construct(
        private readonly ChannelPairFactory $channelPairFactory
    ) {
        $this->processes = new Processes();
    }

    /**
     * @throws ForkControlException
     */
    public function fork(Task $task): Process
    {
        $channelPair = $this->channelPairFactory->create();

        $forkPid = pcntl_fork();

        return match ($forkPid) {
            -1 => throw new ForkControlException('Unable to fork.'),
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
        return new Wait($this);
    }

    public function kill(int $signal): ForkControl
    {
        $this->processes->kill($signal);

        return $this;
    }
}
