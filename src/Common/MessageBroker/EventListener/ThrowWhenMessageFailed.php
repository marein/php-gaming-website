<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\EventListener;

use Gaming\Common\MessageBroker\Event\MessageFailed;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class ThrowWhenMessageFailed
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $logLevel = LogLevel::CRITICAL
    ) {
    }

    public function messageFailed(MessageFailed $event): void
    {
        $this->logger->log(
            $this->logLevel,
            'Message failed.',
            [
                'message' => $event->message->toArray(),
                'throwable' => $event->throwable,
                'metadata' => $event->metadata
            ]
        );

        throw $event->throwable;
    }
}
