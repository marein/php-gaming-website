<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use PhpAmqpLib\Channel\AMQPChannel;

final readonly class CompositeQueueConsumer implements QueueConsumer
{
    /**
     * @param QueueConsumer[] $queueConsumers
     */
    public function __construct(
        private array $queueConsumers
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        foreach ($this->queueConsumers as $queueConsumer) {
            $queueConsumer->register($channel, $callbackFactory);
        }
    }
}
