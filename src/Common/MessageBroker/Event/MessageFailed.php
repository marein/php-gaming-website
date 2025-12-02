<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Event;

use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

final class MessageFailed implements StoppableEventInterface
{
    use Stoppable;

    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly Message $message,
        public readonly Context $context,
        public readonly Throwable $throwable,
        public readonly array $metadata
    ) {
    }
}
