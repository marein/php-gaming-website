<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

/**
 * If the EventStore always stays clean, e.g. by listening to EventsCommitted and cleaning up
 * the specific events that has been processed, there's no need to set up infrastructure to keep track
 * of the pointer in the stream. If your EventStore uses GapDetection, it should be disabled.
 */
final class NullEventStorePointerFactory implements EventStorePointerFactory
{
    public function withName(string $name): EventStorePointer
    {
        return new class implements EventStorePointer {
            public function trackMostRecentPublishedStoredEventId(int $id): void
            {
                // Nothing to do.
            }

            public function retrieveMostRecentPublishedStoredEventId(): int
            {
                return 0;
            }
        };
    }
}
