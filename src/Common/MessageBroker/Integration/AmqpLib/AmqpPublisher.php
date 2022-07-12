<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

final class AmqpPublisher implements Publisher
{
    private ?AMQPChannel $channel;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly Topology $topology,
        private readonly ReliablePublishing $reliablePublishing,
        private readonly MessageTranslator $messageTranslator,
        private readonly string $exchangeToPublishTo
    ) {
        $this->channel = null;
    }

    public function publish(Message $message): void
    {
        $this->channel ??= $this->createConfirmingChannelAndTopology();

        try {
            $this->channel->basic_publish(
                $this->messageTranslator->messageToAmqpMessage($message),
                $this->exchangeToPublishTo,
                (string)$message->name()
            );
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }

    public function flush(): void
    {
        $this->channel ??= $this->createConfirmingChannelAndTopology();

        $this->reliablePublishing->flush($this->channel);
    }

    /**
     * @throws MessageBrokerException
     */
    private function createConfirmingChannelAndTopology(): AMQPChannel
    {
        $connection = $this->connectionFactory->create();

        try {
            $channel = $connection->channel();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }

        $this->topology->declare($channel);

        $this->reliablePublishing->prepareChannel($channel);

        return $channel;
    }
}
