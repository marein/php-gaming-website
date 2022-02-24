<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\ForkControl\Process;
use Gaming\Common\ForkControl\Queue\Queue;
use Gaming\Common\ForkControl\Task;
use Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory\EventStorePointerFactory;
use InvalidArgumentException;

final class Publisher implements Task
{
    /**
     * @param Process[] $workers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $workers,
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
            new ForwardToProcessStoredEventSubscriber($this->workers),
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
