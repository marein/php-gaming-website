<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use Gaming\Common\ForkManager\Process;
use InvalidArgumentException;

final class FollowEventStoreDispatcher
{
    /**
     * @param Process[] $workers
     */
    public function __construct(
        private readonly array $workers,
        private readonly EventStorePointer $eventStorePointer,
        private readonly EventStore $eventStore
    ) {
    }

    /**
     * @throws EventStoreException
     * @throws FailedRetrieveMostRecentPublishedStoredEventIdException
     * @throws FailedTrackMostRecentPublishedStoredEventIdException
     */
    public function dispatch(int $batchSize): void
    {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('batchSize must be greater than 0');
        }

        $lastStoredEventId = $this->eventStorePointer->retrieveMostRecentPublishedStoredEventId();

        $storedEvents = $this->eventStore->since(
            $lastStoredEventId,
            $batchSize
        );

        if (count($storedEvents) === 0) {
            $this->synchronize();
            return;
        }

        foreach ($storedEvents as $storedEvent) {
            $workerId = crc32($storedEvent->domainEvent()->aggregateId()) % count($this->workers);
            $this->workers[$workerId]->send($storedEvent);
        }

        $this->synchronize();

        $lastStoredEventId = end($storedEvents)->id();
        $this->eventStorePointer->trackMostRecentPublishedStoredEventId($lastStoredEventId);
    }

    private function synchronize(): void
    {
        foreach ($this->workers as $worker) {
            $worker->send('SYN');
        }

        foreach ($this->workers as $worker) {
            if ($worker->receive() !== 'ACK') {
                throw new EventStoreException('No ack from worker');
            }
        }
    }
}
