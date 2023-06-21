<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Consumer;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory\ConnectionFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\QueueConsumer;
use Gaming\Common\MessageBroker\MessageHandler;
use Psr\EventDispatcher\EventDispatcherInterface;

final class AmqpConsumerFactory
{
    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly int $prefetchCount,
        private readonly MessageTranslator $messageTranslator,
        private readonly MessageRouter $messageRouter,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(MessageHandler $messageHandler, QueueConsumer $queueConsumer): Consumer
    {
        return new AmqpConsumer(
            $this->connectionFactory,
            $this->prefetchCount,
            $this->messageTranslator,
            $this->messageRouter,
            $this->eventDispatcher,
            $messageHandler,
            $queueConsumer
        );
    }
}
