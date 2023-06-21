<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;
use Gaming\Common\MessageBroker\Consumer;

final class ConsumerTask implements Task
{
    public function __construct(
        private readonly Consumer $consumer
    ) {
    }

    public function execute(Channel $channel): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->consumer->stop(...));
        pcntl_signal(SIGTERM, $this->consumer->stop(...));

        $this->consumer->start();

        return 0;
    }
}
