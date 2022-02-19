<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkManager\Process;
use InvalidArgumentException;

final class ForwardToProcessStoredEventSubscriber implements StoredEventSubscriber
{
    /**
     * @param Process[] $processes
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $processes
    ) {
        if (count($this->processes) < 1) {
            throw new InvalidArgumentException('no processes given');
        }
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $shardId = crc32($storedEvent->domainEvent()->aggregateId()) % count($this->processes);
        $this->processes[$shardId]->send($storedEvent);
    }
}
