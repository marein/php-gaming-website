<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class InMemoryCacheEventStorePointer implements EventStorePointer
{
    private EventStorePointer $eventStorePointer;

    private int $cachedId;

    public function __construct(EventStorePointer $eventStorePointer)
    {
        $this->eventStorePointer = $eventStorePointer;

        // Initially retrieve the most recent published stored event id.
        $this->cachedId = $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();
    }

    public function trackMostRecentPublishedStoredEventId(int $id): void
    {
        // Delegate, so this operation becomes persistent.
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId($id);

        $this->cachedId = $id;
    }

    public function retrieveMostRecentPublishedStoredEventId(): int
    {
        return $this->cachedId;
    }
}
