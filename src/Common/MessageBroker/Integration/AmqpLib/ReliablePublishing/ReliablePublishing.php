<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\ReliablePublishing;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Channel\AMQPChannel;

interface ReliablePublishing
{
    /**
     * @throws MessageBrokerException
     */
    public function prepareChannel(AMQPChannel $channel): void;

    /**
     * @throws MessageBrokerException
     */
    public function flush(AMQPChannel $channel): void;
}
