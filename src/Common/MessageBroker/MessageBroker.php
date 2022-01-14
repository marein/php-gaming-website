<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Message\Message;

interface MessageBroker
{
    public function publish(Message $message): void;

    /**
     * @return never
     */
    public function consume(Consumer $consumer): void;
}
