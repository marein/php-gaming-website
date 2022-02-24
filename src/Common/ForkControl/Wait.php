<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

final class Wait
{
    public function __construct(
        private readonly ForkControl $forkControl
    ) {
    }

    public function all(): ForkControl
    {
        while (pcntl_wait($status) !== -1) {
        }

        return $this->forkControl;
    }

    public function any(): ForkControl
    {
        pcntl_wait($status);

        return $this->forkControl;
    }

    public function terminateAllWhenAnyExits(): void
    {
        $this->any()->terminate()->wait()->all();
    }
}
