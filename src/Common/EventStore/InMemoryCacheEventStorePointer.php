<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class InMemoryCacheEventStorePointer implements EventStorePointer
{
    private ?int $cachedId = null;

    public function __construct(
        private readonly EventStorePointer $eventStorePointer
    ) {
    }

    public function trackMostRecentPublishedStoredEventId(int $id): void
    {
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId($id);

        $this->cachedId = $id;
    }

    public function retrieveMostRecentPublishedStoredEventId(): int
    {
        return $this->cachedId ??= $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();
    }
}
