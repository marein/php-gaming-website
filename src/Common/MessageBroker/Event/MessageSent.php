<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

use Gaming\Common\MessageBroker\Message;

final class MessageSent
{
    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly Message $message,
        public readonly array $metadata
    ) {
    }
}
