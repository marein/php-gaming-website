<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;
use Gaming\Common\MessageBroker\Consumer;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template T
 */
final class ConsumerTask implements Task
{
    /**
     * @param Consumer<T> $consumer
     * @param iterable<T> $topicConsumers
     */
    public function __construct(
        private readonly Consumer $consumer,
        private readonly iterable $topicConsumers,
    ) {
    }

    public function execute(Channel $channel): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->consumer->stop(...));
        pcntl_signal(SIGTERM, $this->consumer->stop(...));

        $this->consumer->start($this->topicConsumers);

        return 0;
    }
}
