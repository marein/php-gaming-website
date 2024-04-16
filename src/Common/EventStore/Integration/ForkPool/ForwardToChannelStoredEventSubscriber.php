<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\ForkPool;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkPool\Channel\Channel;
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

    public function handle(DomainEvent $domainEvent): void
    {
        $shardId = crc32($domainEvent->aggregateId()) % count($this->channels);
        $this->channels[$shardId]->send($domainEvent);
    }

    public function commit(): void
    {
        foreach ($this->channels as $channel) {
            $channel->send('COMMIT');
        }

        foreach ($this->channels as $channel) {
            if ($channel->receive() !== 'ACK') {
                throw new EventStoreException('No ack from channel.');
            }
        }
    }
}
