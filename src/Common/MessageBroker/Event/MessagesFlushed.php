<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

final class MessagesFlushed
{
    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly int $numberOfSentMessages,
        public readonly array $metadata
    ) {
    }
}
