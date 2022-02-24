<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkControl\Queue\Queue;
use Gaming\Common\ForkControl\Task;

final class Worker implements Task
{
    public function __construct(
        private readonly StoredEventSubscriber $storedEventSubscriber
    ) {
    }

    public function execute(Queue $queue): int
    {
        while ($data = $queue->receive()) {
            match ($data) {
                'SYN' => $queue->send('ACK'),
                default => $this->storedEventSubscriber->handle($data)
            };
        }

        return 0;
    }
}
