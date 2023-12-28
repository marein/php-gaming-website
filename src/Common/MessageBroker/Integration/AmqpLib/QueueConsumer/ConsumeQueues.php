<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Gaming\Common\MessageBroker\Integration\AmqpLib\Topology\DefinesQueues;
use PhpAmqpLib\Channel\AMQPChannel;

final class ConsumeQueues implements QueueConsumer
{
    public function __construct(
        private readonly DefinesQueues $definesQueues
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        foreach ($this->definesQueues->queueNames() as $queueName) {
            $channel->basic_consume(
                queue: $queueName,
                callback: $callbackFactory->create($queueName)
            );
        }
    }
}
