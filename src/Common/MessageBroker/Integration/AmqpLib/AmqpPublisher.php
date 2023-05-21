<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory\ConnectionFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ReliablePublishing\ReliablePublishing;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

final class AmqpPublisher implements Publisher
{
    private ?AMQPChannel $channel;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly ReliablePublishing $reliablePublishing,
        private readonly MessageTranslator $messageTranslator,
        private readonly MessageRouter $messageRouter
    ) {
        $this->channel = null;
    }

    public function send(Message $message): void
    {
        $this->channel ??= $this->createChannel();

        $route = $this->messageRouter->route($message);

        try {
            $this->channel->basic_publish(
                $this->messageTranslator->createAmqpMessageFromMessage($message),
                $route->exchange,
                $route->routingKey
            );
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }

    public function flush(): void
    {
        $this->channel ??= $this->createChannel();

        $this->reliablePublishing->flush($this->channel);
    }

    /**
     * @throws MessageBrokerException
     */
    private function createChannel(): AMQPChannel
    {
        $connection = $this->connectionFactory->create();

        try {
            $channel = $connection->channel();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }

        $this->reliablePublishing->prepareChannel($channel);

        return $channel;
    }
}
