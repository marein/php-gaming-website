<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\ForkControl\Queue\Queue;
use Gaming\Common\ForkControl\Task;
use Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory\EventStorePointerFactory;
use InvalidArgumentException;

final class Publisher implements Task
{
    /**
     * @param Queue[] $queues
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $queues,
        private readonly EventStore $eventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory,
        private readonly string $eventStorePointerName,
        private readonly int $throttleTimeInMicroseconds,
        private readonly int $batchSize
    ) {
        if ($this->throttleTimeInMicroseconds < 1000) {
            throw new InvalidArgumentException('throttleTimeInMicroseconds must be at least 1000');
        }
    }

    public function execute(Queue $queue): int
    {
        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            new ForwardToQueueStoredEventSubscriber($this->queues),
            new InMemoryCacheEventStorePointer(
                $this->eventStorePointerFactory->withName(
                    $this->eventStorePointerName
                )
            ),
            new ConsistentOrderEventStore($this->eventStore),
            $this->synchronize(...)
        );

        while (true) {
            if ($followEventStoreDispatcher->dispatch($this->batchSize) === 0) {
                $this->synchronize();
                usleep($this->throttleTimeInMicroseconds);
            }
        }
    }

    public function synchronize(): void
    {
        foreach ($this->queues as $queue) {
            $queue->send('SYN');
        }

        foreach ($this->queues as $queue) {
            if ($queue->receive() !== 'ACK') {
                throw new EventStoreException('No ack from worker');
            }
        }
    }
}
