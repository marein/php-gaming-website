<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use InvalidArgumentException;

/**
 * This event store implementation puts the thread to sleep when no events are retrieved.
 */
final class ThrottlingEventStore implements EventStore
{
    private EventStore $eventStore;

    private int $throttleTimeInMicroseconds;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(EventStore $eventStore, int $throttleTimeInMilliseconds)
    {
        if ($throttleTimeInMilliseconds < 1) {
            throw new InvalidArgumentException('throttleTimeInMilliseconds must be greater than 0');
        }

        $this->eventStore = $eventStore;
        $this->throttleTimeInMicroseconds = $throttleTimeInMilliseconds * 1000;
    }

    public function since(int $id, int $limit): array
    {
        $storedEvents = $this->eventStore->since($id, $limit);

        if (count($storedEvents) === 0) {
            usleep($this->throttleTimeInMicroseconds);
        }

        return $storedEvents;
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        $storedEvents = $this->eventStore->byAggregateId($aggregateId, $sinceId);

        if (count($storedEvents) === 0) {
            usleep($this->throttleTimeInMicroseconds);
        }

        return $storedEvents;
    }

    public function append(DomainEvent $domainEvent): void
    {
        $this->eventStore->append($domainEvent);
    }

    public function hasUncommittedStoredEventId(int $id): bool
    {
        return $this->eventStore->hasUncommittedStoredEventId($id);
    }
}
