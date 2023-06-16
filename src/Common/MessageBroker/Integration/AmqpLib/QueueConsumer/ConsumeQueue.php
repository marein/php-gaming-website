<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Gaming\Common\MessageBroker\Integration\AmqpLib\Topology\DefinesQueue;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;

final class ConsumeQueue implements QueueConsumer
{
    public function __construct(
        private readonly MessageHandler $messageHandler,
        private readonly DefinesQueue $definesQueue
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        $channel->basic_consume(
            queue: $this->definesQueue->queueName(),
            callback: $callbackFactory->create($this->definesQueue->queueName(), $this->messageHandler)
        );
    }
}
