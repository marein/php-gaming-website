<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Event;

use Gaming\Common\MessageBroker\Message;

final class MessageReturned
{
    public function __construct(
        public readonly Message $message,
        public readonly int $replyCode,
        public readonly string $replyText,
        public readonly string $exchange,
        public readonly string $routingKey
    ) {
    }
}
