<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class ConfirmingPublisher implements Publisher
{
    private ?AMQPChannel $channel;

    private AMQPMessage $message;

    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly string $exchange
    ) {
        $this->channel = null;
        $this->message = new AMQPMessage(
            '',
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );
    }

    public function publish(Message $message): void
    {
        try {
            $this->channel ??= $this->createConfirmingChannel();

            $this->message->setBody(
                json_encode(
                    [(string)$message->name(), $message->body()],
                    JSON_THROW_ON_ERROR
                )
            );

            $this->channel->basic_publish(
                $this->message,
                $this->exchange,
                $message->name()->domain() . '.' . $message->name()->name()
            );
        } catch (\Throwable $throwable) {
            throw new MessageBrokerException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    public function flush(): void
    {
        try {
            $this->channel ??= $this->createConfirmingChannel();

            $this->channel->wait_for_pending_acks();
        } catch (\Throwable $throwable) {
            throw new MessageBrokerException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }


    /**
     * @throws MessageBrokerException
     */
    private function createConfirmingChannel(): AMQPChannel
    {
        $channel = $this->connectionFactory->create()->channel();

        $channel->confirm_select();

        return $channel;
    }
}
