<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use InvalidArgumentException;

final class FollowEventStoreDispatcher
{
    public function __construct(
        private readonly StoredEventSubscriber $storedEventSubscriber,
        private readonly EventStorePointer $eventStorePointer,
        private readonly EventStore $eventStore
    ) {
    }

    /**
     * @throws EventStoreException
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     */
    public function dispatch(int $batchSize): int
    {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('batchSize must be greater than 0');
        }

        $lastStoredEventId = $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();

        $storedEvents = $this->eventStore->since(
            $lastStoredEventId,
            $batchSize
        );

        if (count($storedEvents) === 0) {
            return 0;
        }

        foreach ($storedEvents as $storedEvent) {
            $this->storedEventSubscriber->handle($storedEvent);
        }

        $this->storedEventSubscriber->commit();

        $lastStoredEventId = end($storedEvents)->id();
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId($lastStoredEventId);

        return count($storedEvents);
    }
}
