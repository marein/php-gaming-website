<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;

final class Worker implements Task
{
    public function __construct(
        private readonly StoredEventSubscriber $storedEventSubscriber
    ) {
    }

    public function execute(Channel $channel): int
    {
        while ($data = $channel->receive()) {
            match ($data) {
                'SYN' => $channel->send('ACK'),
                default => $this->storedEventSubscriber->handle($data)
            };
        }

        return 0;
    }
}
