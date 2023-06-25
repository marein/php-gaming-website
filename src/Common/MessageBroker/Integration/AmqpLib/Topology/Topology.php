<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

interface Topology
{
    /**
     * @throws Throwable
     */
    public function declare(AMQPChannel $channel): void;
}
