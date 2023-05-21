<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;

final readonly class SingleQueueConsumer implements QueueConsumer
{
    public function __construct(
        private MessageHandler $messageHandler,
        private string $queueName
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        $channel->basic_consume(
            queue: $this->queueName,
            callback: $callbackFactory->create($this->queueName, $this->messageHandler)
        );
    }
}
