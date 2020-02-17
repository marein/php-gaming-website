<?php
declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use InvalidArgumentException;

final class FollowEventStoreDispatcher
{
    /**
     * @var EventStorePointer
     */
    private EventStorePointer $eventStorePointer;

    /**
     * @var EventStore
     */
    private EventStore $eventStore;

    /**
     * @var StoredEventPublisher
     */
    private StoredEventPublisher $storedEventPublisher;

    /**
     * FollowEventStoreDispatcher constructor.
     *
     * @param EventStorePointer    $eventStorePointer
     * @param EventStore           $eventStore
     * @param StoredEventPublisher $storedEventPublisher
     */
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
     * Follow the store and publish stored events.
     *
     * @param int $batchSize The number of stored events which gets pulled out of the event store and
     *                       published through the stored event publisher.
     *
     * @throws InvalidArgumentException
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
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
