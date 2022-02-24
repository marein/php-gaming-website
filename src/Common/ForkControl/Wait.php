<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

final class Wait
{
    public function __construct(
        private readonly ForkControl $forkControl,
        private readonly Processes $processes
    ) {
    }

    public function all(): ForkControl
    {
        while (($processId = pcntl_wait($status)) !== -1) {
            $this->processes->remove($processId);
        }

        return $this->forkControl;
    }

    public function any(): ForkControl
    {
        $this->processes->remove(pcntl_wait($status));

        return $this->forkControl;
    }

    public function killAllWhenAnyExits(int $signal): void
    {
        $this->any()->kill($signal)->wait()->all();
    }
}
