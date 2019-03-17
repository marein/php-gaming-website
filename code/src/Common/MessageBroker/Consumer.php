<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Message\Message;
use Gaming\Common\MessageBroker\Message\Name;

interface Consumer
{
    /**
     * Handle the message.
     *
     * @param Message $message
     */
    public function handle(Message $message): void;

    /**
     * Routing keys to listen to.
     *
     * @return string[]
     */
    public function routingKeys(): array;

    /**
     * The queue name for this consumer.
     *
     * @return string
     */
    public function queueName(): string;
}
