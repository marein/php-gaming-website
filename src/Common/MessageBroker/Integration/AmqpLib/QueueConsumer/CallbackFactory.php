<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use ArrayObject;
use Closure;
use Gaming\Common\MessageBroker\Event\MessageFailed;
use Gaming\Common\MessageBroker\Event\MessageReceived;
use Gaming\Common\MessageBroker\Integration\AmqpLib\AmqpContext;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\EventDispatcher\EventDispatcherInterface;

final class CallbackFactory
{
    /**
     * @param ArrayObject<int, AmqpContext> $pendingMessageToContext
     */
    public function __construct(
        private readonly MessageTranslator $messageTranslator,
        private readonly MessageRouter $messageRouter,
        private readonly ArrayObject $pendingMessageToContext,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageHandler $messageHandler,
        private readonly AMQPChannel $channel
    ) {
    }

    /**
     * @return Closure(AMQPMessage): void
     */
    public function create(string $queueName): Closure
    {
        return function (AMQPMessage $amqpMessage) use ($queueName): void {
            $message = $this->messageTranslator->createMessageFromAmqpMessage($amqpMessage);
            $context = new AmqpContext(
                $this->messageTranslator,
                $this->messageRouter,
                $this->eventDispatcher,
                $this->pendingMessageToContext,
                $this->channel,
                $queueName,
                $message,
                $amqpMessage
            );

            $this->eventDispatcher->dispatch(new MessageReceived($message, ['queue' => $queueName]));

            try {
                $this->messageHandler->handle($message, $context);
            } catch (\Throwable $throwable) {
                $this->eventDispatcher->dispatch(
                    new MessageFailed($message, $throwable, ['queue' => $queueName])
                );
            }

            $context->ackIncomingMessageIfContextIsResolved();
        };
    }
}
