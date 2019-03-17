<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Message\Message;

interface MessageBroker
{
    /**
     * Publish the message body with routing key to the configured exchange.
     *
     * @param Message $message
     */
    public function publish(Message $message): void;

    /**
     * Consume with the given consumer.
     *
     * @param Consumer $consumer
     */
    public function consume(Consumer $consumer): void;
}
