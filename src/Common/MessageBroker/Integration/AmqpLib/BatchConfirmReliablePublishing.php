<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

final class BatchConfirmReliablePublishing implements ReliablePublishing
{
    public function flush(AMQPChannel $channel): void
    {
        try {
            $channel->wait_for_pending_acks();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }

    public function prepareChannel(AMQPChannel $channel): void
    {
        try {
            $channel->confirm_select();
        } catch (Throwable $throwable) {
            throw MessageBrokerException::fromThrowable($throwable);
        }
    }
}
