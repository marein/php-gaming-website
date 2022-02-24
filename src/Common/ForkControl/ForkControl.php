<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;
use Gaming\Common\ForkControl\Channel\ChannelPair;
use Gaming\Common\ForkControl\Channel\ChannelPairFactory;

final class ForkControl
{
    /**
     * @var Process[]
     */
    private array $forks;

    public function __construct(
        private readonly ChannelPairFactory $channelPairFactory
    ) {
        $this->forks = [];
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
            default => $this->registerFork($forkPid, $channelPair)
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

    public function terminate(): ForkControl
    {
        foreach ($this->forks as $fork) {
            $fork->terminate();
        }

        return $this;
    }

    private function registerFork(int $forkPid, ChannelPair $channelPair): Process
    {
        $fork = new Process($forkPid, $channelPair->fork());
        $this->forks[] = $fork;

        return $fork;
    }
}
