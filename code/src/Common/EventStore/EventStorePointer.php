<?php

namespace Gambling\Common\EventStore;

use Gambling\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gambling\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;

interface EventStorePointer
{
    /**
     * Track the most recent published stored event id.
     *
     * @param int $id
     *
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     */
    public function trackMostRecentPublishedStoredEventId(int $id): void;

    /**
     * Retrieve the most recent published stored event id.
     *
     * @return int
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     */
    public function retrieveMostRecentPublishedStoredEventId(): int;
}
