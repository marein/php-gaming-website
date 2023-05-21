<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Closure;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

interface CallbackFactory
{
    /**
     * @return Closure(AMQPMessage): void
     * @throws Throwable
     */
    public function create(string $queueName, MessageHandler $messageHandler): Closure;
}
