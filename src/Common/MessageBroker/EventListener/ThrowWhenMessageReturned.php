<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\EventListener;

use Gaming\Common\MessageBroker\Event\MessageReturned;
use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class ThrowWhenMessageReturned
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $logLevel = LogLevel::CRITICAL
    ) {
    }

    public function messageReturned(MessageReturned $event): void
    {
        $this->logger->log(
            $this->logLevel,
            'Message returned.',
            [
                'message' => $event->message->toArray(),
                'cause' => $event->cause,
                'metadata' => $event->metadata
            ]
        );

        throw new MessageBrokerException('Message returned.');
    }
}
