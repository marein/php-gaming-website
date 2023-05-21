<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

interface MultiQueueTopology extends Topology
{
    /**
     * @return iterable<string>
     */
    public function queueNames(): iterable;
}
