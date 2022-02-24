<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Channel\Channel;

final class Process
{
    public function __construct(
        private readonly int $processId,
        private readonly Channel $channel
    ) {
    }

    public function kill(int $signal): void
    {
        posix_kill($this->processId, $signal);
    }

    public function channel(): Channel
    {
        return $this->channel;
    }
}
