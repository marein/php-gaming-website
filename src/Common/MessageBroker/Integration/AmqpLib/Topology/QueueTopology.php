<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;

final class QueueTopology implements Topology, DefinesQueue
{
    /**
     * @param string[] $routingKeys
     */
    public function __construct(
        private readonly string $queueName,
        private readonly string $exchangeName,
        private readonly array $routingKeys
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        $channel->queue_declare(
            $this->queueName,
            false,
            true,
            false,
            false
        );

        foreach ($this->routingKeys as $routingKey) {
            $channel->queue_bind(
                $this->queueName,
                $this->exchangeName,
                $routingKey
            );
        }
    }

    public function queueName(): string
    {
        return $this->queueName;
    }
}
