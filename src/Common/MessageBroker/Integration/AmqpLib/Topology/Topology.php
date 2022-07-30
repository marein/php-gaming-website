<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Topology;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Channel\AMQPChannel;

interface Topology
{
    /**
     * @throws MessageBrokerException
     */
    public function declare(AMQPChannel $channel): void;
}
