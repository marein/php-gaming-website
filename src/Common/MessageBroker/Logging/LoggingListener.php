<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Logging;

use Gaming\Common\MessageBroker\Event\MessageHandled;
use Gaming\Common\MessageBroker\Event\MessageReceived;
use Gaming\Common\MessageBroker\Event\MessageSent;
use Psr\Log\LoggerInterface;

final class LoggingListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function messageReceived(MessageReceived $event): void
    {
        $this->logger->debug(
            'Received Message.',
            [
                'message' => ['name' => $event->message->name(), 'body' => $event->message->body()],
                'metadata' => $event->metadata
            ]
        );
    }

    public function messageHandled(MessageHandled $event): void
    {
        $this->logger->debug(
            'Message handled.',
            [
                'message' => ['name' => $event->message->name(), 'body' => $event->message->body()],
                'metadata' => $event->metadata
            ]
        );
    }

    public function messageSent(MessageSent $event): void
    {
        $this->logger->debug(
            'Message sent.',
            [
                'message' => ['name' => $event->message->name(), 'body' => $event->message->body()],
                'metadata' => $event->metadata
            ]
        );
    }
}
