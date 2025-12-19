<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Throwable;

final class StoredEventFilters
{
    /**
     * @param StoredEvent[] $storedEvents
     *
     * @return StoredEvent[]
     * @throws Throwable
     */
    public static function untilGapIsFound(
        array $storedEvents,
        int $lastStoredEventId,
        GapDetection $gapDetection
    ): array {
        $filteredStoredEvents = [];
        $expectedStoredEventId = $lastStoredEventId;

        while (count($storedEvents) > 0) {
            $storedEvent = reset($storedEvents);
            $expectedStoredEventId++;
            $isStoredEventIdExpected = $storedEvent->id() === $expectedStoredEventId;

            if (
                !$isStoredEventIdExpected
                && $gapDetection->shouldWaitForStoredEventWithId($expectedStoredEventId, $storedEvent)
            ) {
                return $filteredStoredEvents;
            }

            if ($isStoredEventIdExpected) {
                $filteredStoredEvents[] = array_shift($storedEvents);
            }
        }

        return $filteredStoredEvents;
    }
}
