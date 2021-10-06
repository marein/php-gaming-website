<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Message\Message;

interface MessageBroker
{
    /**
     * Publish the message.
     *
     * @param Message $message
     */
    public function publish(Message $message): void;

    /**
     * Consume with the given consumer.
     *
     * @param Consumer $consumer
     *
     * @return never
     */
    public function consume(Consumer $consumer): void;
}
