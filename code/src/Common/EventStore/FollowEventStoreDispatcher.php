<?php

namespace Gambling\Common\EventStore;

use Gambling\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gambling\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;

final class FollowEventStoreDispatcher
{
    /**
     * @var EventStorePointer
     */
    private $publishedStoredEventTracker;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var StoredEventPublisher
     */
    private $storedEventPublisher;

    /**
     * FollowEventStoreDispatcher constructor.
     *
     * @param EventStorePointer    $publishedStoredEventTracker
     * @param EventStore           $eventStore
     * @param StoredEventPublisher $storedEventPublisher
     */
    public function __construct(
        EventStorePointer $publishedStoredEventTracker,
        EventStore $eventStore,
        StoredEventPublisher $storedEventPublisher
    ) {
        $this->publishedStoredEventTracker = $publishedStoredEventTracker;
        $this->eventStore = $eventStore;
        $this->storedEventPublisher = $storedEventPublisher;
    }

    /**
     * Follow the store and publish stored events.
     *
     * @param int $batchSize The number of stored events which gets pulled out of the event store and
     *                       published through the stored event publisher.
     *
     * @throws \InvalidArgumentException
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     */
    public function dispatch(int $batchSize): void
    {
        if ($batchSize < 1) {
            throw new \InvalidArgumentException('batchSize must be greater than 0');
        }

        $lastStoredEventId = $this->publishedStoredEventTracker->retrieveMostRecentPublishedStoredEventId();

        $storedEvents = $this->eventStore->storedEventsSince(
            $lastStoredEventId,
            $batchSize
        );

        if (!empty($storedEvents)) {
            $this->storedEventPublisher->publish($storedEvents);

            $lastStoredEventId = end($storedEvents)->id();
            $this->publishedStoredEventTracker->trackMostRecentPublishedStoredEventId($lastStoredEventId);
        }
    }
}
