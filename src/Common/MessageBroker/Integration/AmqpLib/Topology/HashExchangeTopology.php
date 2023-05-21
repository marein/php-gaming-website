<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final readonly class HashExchangeTopology implements MultiQueueTopology
{
    /**
     * @param string[] $routingKeys
     */
    public function __construct(
        private string $queueNameTemplate,
        private string $exchangeName,
        private array $routingKeys,
        private int $numberOfShards,
        private string $hashHeaderName
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        $this->declareShards(
            $channel,
            $this->declareHashExchange($channel)
        );
    }

    public function queueNames(): iterable
    {
        for ($shardNumber = 1; $shardNumber <= $this->numberOfShards; $shardNumber++) {
            yield sprintf($this->queueNameTemplate, str_pad((string)$shardNumber, 3, '0', STR_PAD_LEFT));
        }
    }

    private function declareHashExchange(AMQPChannel $channel): string
    {
        $hashExchangeName = sprintf($this->queueNameTemplate, 'HashExchange');

        $channel->exchange_declare(
            exchange:    $hashExchangeName,
            type:        'x-consistent-hash',
            durable:     true,
            auto_delete: false,
            arguments:   new AMQPTable(['hash-header' => $this->hashHeaderName])
        );

        foreach ($this->routingKeys as $routingKey) {
            $channel->exchange_bind(
                $hashExchangeName,
                $this->exchangeName,
                $routingKey
            );
        }

        return $hashExchangeName;
    }

    private function declareShards(AMQPChannel $channel, string $hashExchangeName): void
    {
        foreach ($this->queueNames() as $queueName) {
            $channel->queue_declare(
                queue:       $queueName,
                durable:     true,
                auto_delete: false
            );

            $channel->queue_bind(
                $queueName,
                $hashExchangeName,
                '1'
            );
        }
    }
}
