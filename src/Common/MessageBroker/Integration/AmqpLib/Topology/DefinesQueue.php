<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

interface DefinesQueue
{
    public function queueName(): string;
}
