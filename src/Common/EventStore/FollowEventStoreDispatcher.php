<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Event\EventsCommitted;
use Gaming\Common\EventStore\Event\EventsFetched;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Psr\EventDispatcher\EventDispatcherInterface;

final class FollowEventStoreDispatcher
{
    private bool $shouldStop;

    public function __construct(
        private readonly PollableEventStore $pollableEventStore,
        private EventStorePointer $eventStorePointer,
        private readonly StoredEventSubscriber $storedEventSubscriber,
        private readonly int $batchSize,
        private readonly int $throttleTimeInMilliseconds,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventStorePointer = new InMemoryCacheEventStorePointer($eventStorePointer);
        $this->shouldStop = false;
    }

    /**
     * @throws EventStoreException
     */
    public function start(): void
    {
        while (!$this->shouldStop) {
            $this->handleNextBatch();
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * @throws EventStoreException
     */
    private function handleNextBatch(): void
    {
        $storedEvents = $this->pollableEventStore->since(
            $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId(),
            $this->batchSize
        );
        $this->eventDispatcher->dispatch(new EventsFetched($storedEvents));

        if (count($storedEvents) === 0) {
            $this->eventDispatcher->dispatch(new EventsCommitted([]));
            usleep($this->throttleTimeInMilliseconds * 1000);
            return;
        }

        foreach ($storedEvents as $storedEvent) {
            $this->storedEventSubscriber->handle($storedEvent);
        }

        $this->storedEventSubscriber->commit();
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId(end($storedEvents)->id());
        $this->eventDispatcher->dispatch(new EventsCommitted($storedEvents));
    }
}
