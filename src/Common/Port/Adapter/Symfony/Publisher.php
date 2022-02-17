<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\EventStore\ThrottlingEventStore;
use Gaming\Common\ForkManager\Process;
use Gaming\Common\ForkManager\Task;
use Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory\EventStorePointerFactory;

final class Publisher implements Task
{
    /**
     * @param Process[] $workers
     */
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory,
        private readonly array $workers,
        private readonly string $eventStorePointerName,
        private readonly int $throttleTimeInMilliseconds,
        private readonly int $batchSize
    ) {
    }

    public function execute(Process $parent): int
    {
        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            $this->workers,
            new InMemoryCacheEventStorePointer(
                $this->eventStorePointerFactory->withName(
                    $this->eventStorePointerName
                )
            ),
            new ThrottlingEventStore(
                new ConsistentOrderEventStore($this->eventStore),
                $this->throttleTimeInMilliseconds
            )
        );

        while (true) {
            $followEventStoreDispatcher->dispatch($this->batchSize);
        }
    }
}
