<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\ForkPool;

use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;
use Gaming\Common\MessageBroker\Consumer;

final class ConsumerTask implements Task
{
    /**
     * @param positive-int $parallelism
     */
    public function __construct(
        private readonly Consumer $consumer,
        private readonly int $parallelism
    ) {
    }

    public function execute(Channel $channel): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->consumer->stop(...));
        pcntl_signal(SIGTERM, $this->consumer->stop(...));

        $this->consumer->start($this->parallelism);

        return 0;
    }
}
