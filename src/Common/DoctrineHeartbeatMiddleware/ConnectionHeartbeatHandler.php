<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

use Gaming\Common\Scheduler\Handler;
use Gaming\Common\Scheduler\Scheduler;
use Psr\Clock\ClockInterface;
use WeakReference;

final class ConnectionHeartbeatHandler implements Handler
{
    /**
     * Without a method like Doctrine\DBAL\Driver\Connection::isConnected(),
     * this handler cannot know if the connection is still open, so the WeakReference is used.
     * If no one has a reference to the connection anymore, the handler can safely terminate.
     * This will not keep the connection open forever.
     *
     * @var WeakReference<TrackActivityConnection>
     */
    private readonly WeakReference $connectionWeakReference;

    public function __construct(
        TrackActivityConnection $connection,
        private readonly string $dummySql,
        private readonly int $interval,
        private readonly ClockInterface $clock,
    ) {
        $this->connectionWeakReference = WeakReference::create($connection);
    }

    public function handle(Scheduler $scheduler): void
    {
        $connection = $this->connectionWeakReference->get();
        if ($connection === null) {
            return;
        }

        if (
            !$connection->isQuerying()
            && ($connection->lastActivity() + $this->interval) < $this->clock->now()->getTimestamp()
        ) {
            $connection->query($this->dummySql);
        }

        $scheduler->schedule(time() + $this->interval, $this);
    }
}
