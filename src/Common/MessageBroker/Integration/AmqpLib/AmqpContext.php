<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use ArrayObject;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class AmqpContext implements Context
{
    private int $numberOfPendingMessages;

    /**
     * @param ArrayObject<int, AmqpContext> $pendingMessageToContext
     */
    public function __construct(
        private readonly MessageRouter $messageRouter,
        private readonly MessageTranslator $messageTranslator,
        private readonly ArrayObject $pendingMessageToContext,
        private readonly AMQPChannel $channel,
        private readonly string $replyToQueueName,
        private readonly AMQPMessage $incomingAmqpMessage
    ) {
        $this->numberOfPendingMessages = 0;
    }

    public function request(Message $message): void
    {
        $amqpMessage = $this->messageTranslator->createAmqpMessageFromMessage($message);
        $amqpMessage->set('reply_to', $this->replyToQueueName);

        $route = $this->messageRouter->route($message);

        $this->publishAmqpMessage(
            $amqpMessage,
            $route->exchange,
            $route->routingKey
        );
    }

    public function reply(Message $message): void
    {
        if (!$this->incomingAmqpMessage->has('reply_to')) {
            return;
        }

        $this->publishAmqpMessage(
            $this->messageTranslator->createAmqpMessageFromMessage($message),
            '',
            $this->incomingAmqpMessage->get('reply_to')
        );
    }

    public function ackIncomingMessageIfContextIsResolved(): void
    {
        if ($this->numberOfPendingMessages === 0) {
            $this->incomingAmqpMessage->ack();
        }
    }

    public function resolvePositiveAcknowledgement(AMQPMessage $amqpMessage): void
    {
        $this->resolveAmqpMessage($amqpMessage);

        $this->ackIncomingMessageIfContextIsResolved();
    }

    public function resolveNegativeAcknowledgement(AMQPMessage $amqpMessage): void
    {
        $this->resolveAmqpMessage($amqpMessage);

        $this->publishAmqpMessage(
            $amqpMessage,
            (string)$amqpMessage->getExchange(),
            (string)$amqpMessage->getRoutingKey()
        );
    }

    private function resolveAmqpMessage(AMQPMessage $amqpMessage): void
    {
        $this->pendingMessageToContext->offsetUnset($amqpMessage->getDeliveryTag());
        $this->numberOfPendingMessages--;
    }

    private function publishAmqpMessage(AMQPMessage $amqpMessage, string $exchange, string $routingKey): void
    {
        $this->channel->basic_publish($amqpMessage, $exchange, $routingKey);

        $this->pendingMessageToContext->offsetSet($amqpMessage->getDeliveryTag(), $this);
        $this->numberOfPendingMessages++;
    }
}
