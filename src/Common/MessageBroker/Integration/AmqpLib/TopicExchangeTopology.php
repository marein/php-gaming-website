<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exchange\AMQPExchangeType;

final class TopicExchangeTopology implements Topology
{
    public function __construct(
        private readonly string $exchangeName
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        try {
            $channel->exchange_declare(
                $this->exchangeName,
                AMQPExchangeType::TOPIC,
                false,
                true,
                false
            );
        } catch (Throwable $throwable) {
            throw new MessageBrokerException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
