<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

interface QueueConsumer
{
    /**
     * @throws Throwable
     */
    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void;
}
