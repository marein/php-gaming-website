<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use InvalidArgumentException;

final class FollowEventStoreDispatcher
{
    private EventStorePointer $eventStorePointer;

    private EventStore $eventStore;

    private StoredEventPublisher $storedEventPublisher;

    public function __construct(
        EventStorePointer $eventStorePointer,
        EventStore $eventStore,
        StoredEventPublisher $storedEventPublisher
    ) {
        $this->eventStorePointer = $eventStorePointer;
        $this->eventStore = $eventStore;
        $this->storedEventPublisher = $storedEventPublisher;
    }

    /**
     * @throws EventStoreException
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     */
    public function dispatch(int $batchSize): void
    {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('batchSize must be greater than 0');
        }

        $lastStoredEventId = $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();

        $storedEvents = $this->eventStore->since(
            $lastStoredEventId,
            $batchSize
        );

        if (!empty($storedEvents)) {
            $this->storedEventPublisher->publish($storedEvents);

            $lastStoredEventId = end($storedEvents)->id();
            $this->eventStorePointer->trackMostRecentPublishedStoredEventId($lastStoredEventId);
        }
    }
}
