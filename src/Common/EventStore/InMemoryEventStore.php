<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var StoredEvent[]
     */
    private array $storedEvents = [];

    public function since(int $id, int $limit): array
    {
        return array_slice(
            $this->storedEvents,
            $id,
            $limit
        );
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        return array_filter(
            $this->storedEvents,
            static function (StoredEvent $storedEvent) use ($aggregateId, $sinceId): bool {
                return $storedEvent->domainEvent()->aggregateId() === $aggregateId && $storedEvent->id() > $sinceId;
            }
        );
    }

    public function append(DomainEvent $domainEvent): void
    {
        $this->storedEvents[] = new StoredEvent(
            count($this->storedEvents) + 1,
            Clock::instance()->now(),
            $domainEvent
        );
    }

    public function hasUncommittedStoredEventId(int $id): bool
    {
        return array_key_exists($id - 1, $this->storedEvents);
    }
}
