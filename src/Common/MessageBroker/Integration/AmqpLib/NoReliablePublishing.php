<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use PhpAmqpLib\Channel\AMQPChannel;

final class NoReliablePublishing implements ReliablePublishing
{
    public function flush(AMQPChannel $channel): void
    {
    }

    public function prepareChannel(AMQPChannel $channel): void
    {
    }
}
