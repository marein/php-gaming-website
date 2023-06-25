<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;

final class ExchangeTopology implements Topology
{
    public function __construct(
        private readonly string $exchangeName,
        private readonly string $exchangeType
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        $channel->exchange_declare(
            $this->exchangeName,
            $this->exchangeType,
            false,
            true,
            false
        );
    }
}
