<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;
use Gaming\Common\ForkControl\Queue\Queue;

final class Process
{
    public function __construct(
        private readonly int $processId,
        private readonly Queue $queue
    ) {
    }

    public function terminate(): void
    {
        posix_kill($this->processId, SIGTERM);
    }

    /**
     * @throws ForkControlException
     */
    public function send(mixed $data): void
    {
        $this->queue->send($data);
    }

    /**
     * @throws ForkControlException
     */
    public function receive(): mixed
    {
        return $this->queue->receive();
    }
}
