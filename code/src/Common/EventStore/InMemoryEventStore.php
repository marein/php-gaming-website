<?php

namespace Gambling\Common\EventStore;

use Gambling\Common\Domain\DomainEvent;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var StoredEvent[]
     */
    private $storedEvents = [];

    /**
     * @inheritdoc
     */
    public function storedEventsSince(int $id, int $limit): array
    {
        return array_slice(
            $this->storedEvents,
            $id,
            $limit
        );
    }

    /**
     * @inheritdoc
     */
    public function append(DomainEvent $domainEvent): void
    {
        $this->storedEvents[] = new StoredEvent(
            count($this->storedEvents) + 1,
            $domainEvent->name(),
            json_encode($domainEvent->payload()),
            $domainEvent->occurredOn()
        );
    }
}
