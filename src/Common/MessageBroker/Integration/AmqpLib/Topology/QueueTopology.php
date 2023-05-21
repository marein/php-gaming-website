<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;

final readonly class QueueTopology implements Topology
{
    /**
     * @param string[] $routingKeys
     */
    public function __construct(
        private string $queueName,
        private string $exchangeName,
        private array $routingKeys
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
}
