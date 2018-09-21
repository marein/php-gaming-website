<?php
declare(strict_types=1);

namespace Gambling\Common\EventStore;

use Gambling\Common\Domain\DomainEvent;

final class ConsistentOrderEventStore implements EventStore
{
    /**
     * The real event store.
     *
     * @var EventStore
     */
    private $eventStore;

    /**
     * ConsistentOrderEventStore constructor.
     *
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsSince(int $id, int $limit): array
    {
        $storedEvents = $this->eventStore->storedEventsSince($id, $limit);

        $expectedStoredEventId = $id;
        $filteredStoredEvents = [];

        while (count($storedEvents) > 0) {
            $storedEvent = reset($storedEvents);
            $expectedStoredEventId++;
            $isStoredEventIdExpected = $storedEvent->id() === $expectedStoredEventId;

            if (!$isStoredEventIdExpected && $this->hasUncommittedStoredEventId($expectedStoredEventId)) {
                return $filteredStoredEvents;
            }

            if ($isStoredEventIdExpected) {
                $filteredStoredEvents[] = array_shift($storedEvents);
            }
        }

        return $filteredStoredEvents;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        return $this->eventStore->storedEventsByAggregateId($aggregateId, $sinceId);
    }

    /**
     * @inheritdoc
     */
    public function append(DomainEvent $domainEvent): void
    {
        $this->eventStore->append($domainEvent);
    }

    /**
     * @inheritdoc
     */
    public function hasUncommittedStoredEventId(int $id): bool
    {
        return $this->eventStore->hasUncommittedStoredEventId($id);
    }
}
