<?php
declare(strict_types=1);

namespace Gambling\Common\EventStore;

use Gambling\Common\Domain\DomainEvent;

/**
 * This event store implementation puts the thread to sleep when no events are retrieved.
 */
final class ThrottlingEventStore implements EventStore
{
    /**
     * The real event store.
     *
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var int
     */
    private $throttleTimeInMicroseconds;

    /**
     * ThrottlingEventStore constructor.
     *
     * @param EventStore $eventStore
     * @param int        $throttleTimeInMilliseconds
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EventStore $eventStore, int $throttleTimeInMilliseconds)
    {
        if ($throttleTimeInMilliseconds < 1) {
            throw new \InvalidArgumentException('throttleTimeInMilliseconds must be greater than 0');
        }

        $this->eventStore = $eventStore;
        $this->throttleTimeInMicroseconds = $throttleTimeInMilliseconds * 1000;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsSince(int $id, int $limit): array
    {
        $storedEvents = $this->eventStore->storedEventsSince($id, $limit);

        if (count($storedEvents) === 0) {
            usleep($this->throttleTimeInMicroseconds);
        }

        return $storedEvents;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        $storedEvents = $this->eventStore->storedEventsByAggregateId($aggregateId, $sinceId);

        if (count($storedEvents) === 0) {
            usleep($this->throttleTimeInMicroseconds);
        }

        return $storedEvents;
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
