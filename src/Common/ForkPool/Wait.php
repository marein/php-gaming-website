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
        while (($processId = pcntl_wait($status)) !== -1) {
            $this->processes->remove($processId);
        }

        return $this->forkPool;
    }

    public function any(): ForkPool
    {
        $this->processes->remove(pcntl_wait($status));

        return $this->forkPool;
    }

    public function killAllWhenAnyExits(int $signal): void
    {
        $this->any()->kill($signal)->wait()->all();
    }
}
