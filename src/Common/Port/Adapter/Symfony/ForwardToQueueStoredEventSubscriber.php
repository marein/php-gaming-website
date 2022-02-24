<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkControl\Queue\Queue;
use InvalidArgumentException;

final class ForwardToQueueStoredEventSubscriber implements StoredEventSubscriber
{
    /**
     * @param Queue[] $queues
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $queues
    ) {
        if (count($this->queues) < 1) {
            throw new InvalidArgumentException('no queues given');
        }
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $shardId = crc32($storedEvent->domainEvent()->aggregateId()) % count($this->queues);
        $this->queues[$shardId]->send($storedEvent);
    }
}
