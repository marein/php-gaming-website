<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkControl\Channel\Channel;
use InvalidArgumentException;

final class ForwardToChannelStoredEventSubscriber implements StoredEventSubscriber
{
    /**
     * @param Channel[] $channels
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $channels
    ) {
        if (count($this->channels) < 1) {
            throw new InvalidArgumentException('no channels given');
        }
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $shardId = crc32($storedEvent->domainEvent()->aggregateId()) % count($this->channels);
        $this->channels[$shardId]->send($storedEvent);
    }
}
