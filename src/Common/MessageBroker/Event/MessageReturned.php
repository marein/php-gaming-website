<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

use Gaming\Common\MessageBroker\Message;

final class MessageReturned
{
    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly Message $message,
        public readonly string $cause,
        public readonly array $metadata
    ) {
    }
}
