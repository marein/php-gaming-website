<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\EventListener;

use Gaming\Common\MessageBroker\Integration\AmqpLib\Event\MessageReturned;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LogMessageReturned
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
                'metadata' => [
                    'replyCode' => $event->replyCode,
                    'replyText' => $event->replyText,
                    'exchange' => $event->exchange,
                    'routingKey' => $event->routingKey
                ],
            ]
        );
    }
}
