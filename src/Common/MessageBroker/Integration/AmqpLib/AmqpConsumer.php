<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use ArrayObject;
use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory\ConnectionFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\Exception\MessageReturnedException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\QueueConsumer;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\ResolvingCallbackFactory;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class AmqpConsumer
{
    private bool $shouldStop;

    /**
     * @var ArrayObject<int, AmqpContext>
     */
    private readonly ArrayObject $pendingMessageToContext;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly int $prefetchCount,
        private readonly MessageRouter $messageRouter,
        private readonly MessageTranslator $messageTranslator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->shouldStop = false;
        $this->pendingMessageToContext = new ArrayObject();
    }

    public function start(QueueConsumer $queueConsumer): void
    {
        $connection = $this->connectionFactory->create();

        try {
            $channel = $connection->channel();
            $channel->confirm_select();
            $channel->basic_qos(0, $this->prefetchCount, false);

            $queueConsumer->register(
                $channel,
                new ResolvingCallbackFactory(
                    $this->messageRouter,
                    $this->messageTranslator,
                    $this->pendingMessageToContext,
                    $channel,
                    $this->eventDispatcher
                )
            );

            $channel->set_ack_handler($this->onPositiveAcknowledgement(...));
            $channel->set_nack_handler($this->onNegativeAcknowledgement(...));
            $channel->set_return_listener($this->onReturn(...));

            while (!$this->shouldStop && $channel->is_consuming()) {
                $channel->wait();
            }

            $channel->wait_for_pending_acks_returns();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    private function onPositiveAcknowledgement(AMQPMessage $pendingMessage): void
    {
        $this->pendingMessageToContext[$pendingMessage->getDeliveryTag()]
            ?->resolvePositiveAcknowledgement($pendingMessage);
    }

    private function onNegativeAcknowledgement(AMQPMessage $pendingMessage): void
    {
        $this->pendingMessageToContext[$pendingMessage->getDeliveryTag()]
            ?->resolveNegativeAcknowledgement($pendingMessage);
    }

    private function onReturn(
        int $replyCode,
        string $replyText,
        string $exchange,
        string $routingKey,
        AMQPMessage $message
    ): void {
        throw new MessageReturnedException(
            $replyCode,
            $replyText,
            $exchange,
            $routingKey
        );
    }
}
