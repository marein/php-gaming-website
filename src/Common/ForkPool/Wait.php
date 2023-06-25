<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

final class Wait
{
    public function __construct(
        private readonly ForkPool $forkPool,
        private readonly Processes $processes
    ) {
    }

    public function all(): ForkPool
    {
        while (($processId = $this->waitForNextProcess()) !== -1) {
            $this->processes->remove($processId);
        }

        return $this->forkPool;
    }

    public function any(): ForkPool
    {
        $this->processes->remove($this->waitForNextProcess());

        return $this->forkPool;
    }

    public function killAllWhenAnyExits(int $signal): void
    {
        $this->any()->kill($signal)->wait()->all();
    }

    private function waitForNextProcess(): int
    {
        return pcntl_wait($status);
    }
}
