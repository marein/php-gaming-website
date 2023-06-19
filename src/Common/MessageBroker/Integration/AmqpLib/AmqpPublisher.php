<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Event\MessageSent;
use Gaming\Common\MessageBroker\Event\MessagesFlushed;
use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory\ConnectionFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ReliablePublishing\ReliablePublishing;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class AmqpPublisher implements Publisher
{
    private ?AMQPChannel $channel;

    private int $numberOfSentMessages;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly ReliablePublishing $reliablePublishing,
        private readonly MessageTranslator $messageTranslator,
        private readonly MessageRouter $messageRouter,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->channel = null;
        $this->numberOfSentMessages = 0;
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

        $this->eventDispatcher->dispatch(
            new MessageSent(
                $message,
                ['exchange' => $route->exchange, 'routingKey' => $route->routingKey]
            )
        );

        $this->numberOfSentMessages++;
    }

    public function flush(): void
    {
        $this->channel ??= $this->createChannel();

        $this->reliablePublishing->flush($this->channel);

        $this->eventDispatcher->dispatch(new MessagesFlushed($this->numberOfSentMessages, []));

        $this->numberOfSentMessages = 0;
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
