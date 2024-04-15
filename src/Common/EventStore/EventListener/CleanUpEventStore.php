<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\EventListener;

use Gaming\Common\EventStore\CleanableEventStore;
use Gaming\Common\EventStore\Event\EventsCommitted;

/**
 * If there's only one streaming process, this listener can be used to safely
 * clean up the EventStore directly after the events have been committed.
 */
final class CleanUpEventStore
{
    /**
     * @param int $numberOfEventsToKeep Please keep in mind that the id isn't gapless in some implementations.
     */
    public function __construct(
        private readonly CleanableEventStore $cleanableEventStore,
        private readonly int $numberOfEventsToKeep = 0
    ) {
    }

    public function eventsCommitted(EventsCommitted $event): void
    {
        count($event->storedEvents) && $this->cleanableEventStore->cleanUpTo(
            max($event->storedEvents[count($event->storedEvents) - 1]->id() - $this->numberOfEventsToKeep, 0)
        );
    }
}
