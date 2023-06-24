<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final class QueueTopology implements Topology, DefinesQueues
{
    /**
     * @param string[] $routingKeys
     * @param array<string, bool|int|float|string> $queueArguments
     */
    public function __construct(
        private readonly string $queueName,
        private readonly string $exchangeName,
        private readonly array $routingKeys,
        private readonly array $queueArguments = []
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        $channel->queue_declare(
            queue: $this->queueName,
            durable: true,
            auto_delete: false,
            arguments: new AMQPTable($this->queueArguments)
        );

        foreach ($this->routingKeys as $routingKey) {
            $channel->queue_bind(
                $this->queueName,
                $this->exchangeName,
                $routingKey
            );
        }
    }

    public function queueNames(): iterable
    {
        yield $this->queueName;
    }
}
