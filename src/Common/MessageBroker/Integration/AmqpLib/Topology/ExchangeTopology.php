<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

final class ExchangeTopology implements Topology
{
    public function __construct(
        private readonly string $exchangeName,
        private readonly string $exchangeType
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        try {
            $channel->exchange_declare(
                $this->exchangeName,
                $this->exchangeType,
                false,
                true,
                false
            );
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }
}
