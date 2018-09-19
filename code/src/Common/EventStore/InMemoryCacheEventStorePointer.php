<?php
declare(strict_types=1);

namespace Gambling\Common\EventStore;

final class InMemoryCacheEventStorePointer implements EventStorePointer
{
    /**
     * @var EventStorePointer
     */
    private $eventStorePointer;

    /**
     * @var int
     */
    private $cachedId;

    /**
     * InMemoryCacheEventStorePointer constructor.
     *
     * @param EventStorePointer $eventStorePointer The persistent EventStorePointer.
     */
    public function __construct(EventStorePointer $eventStorePointer)
    {
        $this->eventStorePointer = $eventStorePointer;

        // Initially retrieve the most recent published stored event id.
        $this->cachedId = $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();
    }

    /**
     * @inheritdoc
     */
    public function trackMostRecentPublishedStoredEventId(int $id): void
    {
        // Delegate, so this operation becomes persistent.
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId($id);

        $this->cachedId = $id;
    }

    /**
     * @inheritdoc
     */
    public function retrieveMostRecentPublishedStoredEventId(): int
    {
        return $this->cachedId;
    }
}
