<?php
declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;

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
