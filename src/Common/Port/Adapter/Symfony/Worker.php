<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkControl\Process;
use Gaming\Common\ForkControl\Task;

final class Worker implements Task
{
    public function __construct(
        private readonly StoredEventSubscriber $storedEventSubscriber
    ) {
    }

    public function execute(Process $parent): int
    {
        while ($data = $parent->receive()) {
            match ($data) {
                'SYN' => $parent->send('ACK'),
                default => $this->storedEventSubscriber->handle($data)
            };
        }

        return 0;
    }
}
