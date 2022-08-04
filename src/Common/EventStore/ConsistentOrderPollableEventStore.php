<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

final class ConsistentOrderPollableEventStore implements PollableEventStore
{
    public function __construct(
        private readonly PollableEventStore $pollableEventStore
    ) {
    }

    public function since(int $id, int $limit): array
    {
        $storedEvents = $this->pollableEventStore->since($id, $limit);

        $expectedStoredEventId = $id;
        $filteredStoredEvents = [];

        while (count($storedEvents) > 0) {
            $storedEvent = reset($storedEvents);
            $expectedStoredEventId++;
            $isStoredEventIdExpected = $storedEvent->id() === $expectedStoredEventId;

            if (!$isStoredEventIdExpected && $this->hasUncommittedStoredEventId($expectedStoredEventId)) {
                return $filteredStoredEvents;
            }

            if ($isStoredEventIdExpected) {
                $filteredStoredEvents[] = array_shift($storedEvents);
            }
        }

        return $filteredStoredEvents;
    }

    public function hasUncommittedStoredEventId(int $id): bool
    {
        return $this->pollableEventStore->hasUncommittedStoredEventId($id);
    }
}
