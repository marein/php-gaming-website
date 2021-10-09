<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;

interface EventStorePointer
{
    /**
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     */
    public function trackMostRecentPublishedStoredEventId(int $id): void;

    /**
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     */
    public function retrieveMostRecentPublishedStoredEventId(): int;
}
