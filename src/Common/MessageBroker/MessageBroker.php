<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Message\Message;

interface MessageBroker
{
    /**
     * @deprecated Use Publisher instead.
     */
    public function publish(Message $message): void;

    /**
     * @param iterable<Consumer> $consumers
     */
    public function consume(iterable $consumers): void;
}
