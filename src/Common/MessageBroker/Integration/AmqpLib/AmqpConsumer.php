<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use ArrayObject;
use Gaming\Common\MessageBroker\Consumer;
use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory\ConnectionFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\Exception\MessageReturnedException;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter\MessageRouter;
use Gaming\Common\MessageBroker\Integration\AmqpLib\MessageTranslator\MessageTranslator;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\QueueConsumer;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\CallbackFactory;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @template T
 * @implements Consumer<QueueConsumer>
 */
final class AmqpConsumer implements Consumer
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

    public function start(iterable $topicConsumers): void
    {
        $connection = $this->connectionFactory->create();

        try {
            $channel = $connection->channel();
            $channel->basic_qos(0, $this->prefetchCount, false);
            $channel->confirm_select();
            $channel->set_ack_handler($this->onPositiveAcknowledgement(...));
            $channel->set_nack_handler($this->onNegativeAcknowledgement(...));
            $channel->set_return_listener($this->onReturn(...));

            foreach ($topicConsumers as $topicConsumer) {
                $topicConsumer->register(
                    $channel,
                    new CallbackFactory(
                        $this->messageRouter,
                        $this->messageTranslator,
                        $this->pendingMessageToContext,
                        $this->eventDispatcher,
                        $channel
                    )
                );
            }

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
