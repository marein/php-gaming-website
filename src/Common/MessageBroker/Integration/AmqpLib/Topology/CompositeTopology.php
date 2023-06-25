<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;

final class CompositeTopology implements Topology
{
    /**
     * @param iterable<Topology> $topologies
     */
    public function __construct(
        private readonly iterable $topologies
    ) {
    }

    public function declare(AMQPChannel $channel): void
    {
        foreach ($this->topologies as $topology) {
            $topology->declare($channel);
        }
    }
}
