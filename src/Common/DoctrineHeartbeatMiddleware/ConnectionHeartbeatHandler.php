<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Gaming\Common\Scheduler\Handler;
use Gaming\Common\Scheduler\Scheduler;

final class ConnectionHeartbeatHandler implements Handler
{
    public function __construct(
        private readonly TrackActivityConnection $connection,
        private readonly string $dummySql,
        private readonly int $interval,
    ) {
    }

    public function handle(Scheduler $scheduler): void
    {
        if (
            !$this->connection->isWriting()
            && ($this->connection->lastActivity() + $this->interval) < time()
        ) {
            $this->connection->query($this->dummySql);
        }

        $scheduler->schedule(time() + $this->interval, $this);
    }
}
