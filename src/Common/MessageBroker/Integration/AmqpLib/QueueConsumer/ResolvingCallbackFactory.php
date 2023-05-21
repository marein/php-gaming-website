<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use ArrayObject;
use Closure;
use Gaming\Common\MessageBroker\Integration\AmqpLib\AmqpContext;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final readonly class ResolvingCallbackFactory implements CallbackFactory
{
    /**
     * @param ArrayObject<int, AmqpContext> $pendingMessageToContext
     */
    public function __construct(
        private MessageRouter $messageRouter,
        private MessageTranslator $messageTranslator,
        private ArrayObject $pendingMessageToContext,
        private AMQPChannel $channel
    ) {
    }

    public function create(string $queueName, MessageHandler $messageHandler): Closure
    {
        return function (AMQPMessage $amqpMessage) use ($queueName, $messageHandler): void {
            $messageHandler->handle(
                $this->messageTranslator->createMessageFromAmqpMessage($amqpMessage),
                $context = new AmqpContext(
                    $this->messageRouter,
                    $this->messageTranslator,
                    $this->pendingMessageToContext,
                    $this->channel,
                    $queueName,
                    $amqpMessage
                )
            );

            $context->ackIncomingMessageIfContextIsResolved();
        };
    }
}
