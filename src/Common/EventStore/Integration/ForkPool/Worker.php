<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\ForkPool;

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
        while ($message = $channel->receive()) {
            match ($message) {
                'PING' => $channel->send('PONG'),
                'COMMIT' => $this->handleCommit($channel),
                default => $this->storedEventSubscriber->handle($message)
            };
        }

        return 0;
    }

    private function handleCommit(Channel $channel): void
    {
        $this->storedEventSubscriber->commit();

        $channel->send('ACK');
    }
}
