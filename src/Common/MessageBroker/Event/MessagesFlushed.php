<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

final class MessagesFlushed
{
    public function __construct(
        public readonly int $numberOfSentMessages
    ) {
    }
}
