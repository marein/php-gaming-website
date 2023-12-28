<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\ForkPool;

use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;

final class Publisher implements Task
{
    public function __construct(
        private readonly FollowEventStoreDispatcher $followEventStoreDispatcher
    ) {
    }

    public function execute(Channel $channel): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->followEventStoreDispatcher->stop(...));
        pcntl_signal(SIGTERM, $this->followEventStoreDispatcher->stop(...));

        $this->followEventStoreDispatcher->start();

        return 0;
    }
}
