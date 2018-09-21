<?php
declare(strict_types=1);

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
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        return array_filter(
            $this->storedEvents,
            function (StoredEvent $storedEvent) use ($aggregateId, $sinceId) {
                return $storedEvent->aggregateId() == $aggregateId && $storedEvent->id() > $sinceId;
            }
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
            $domainEvent->aggregateId(),
            json_encode($domainEvent->payload()),
            $domainEvent->occurredOn()
        );
    }

    /**
     * @inheritdoc
     */
    public function hasUncommittedStoredEventId(int $id): bool
    {
        return array_key_exists($id - 1, $this->storedEvents);
    }
}
