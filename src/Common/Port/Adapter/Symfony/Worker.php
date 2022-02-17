<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEventPublisher;
use Gaming\Common\ForkManager\Process;
use Gaming\Common\ForkManager\Task;

final class Worker implements Task
{
    public function __construct(
        private readonly StoredEventPublisher $storedEventPublisher
    ) {
    }

    public function execute(Process $parent): int
    {
        while ($data = $parent->receive()) {
            match ($data) {
                'SYN' => $parent->send('ACK'),
                default => $this->storedEventPublisher->publish($data)
            };
        }

        return 0;
    }
}
