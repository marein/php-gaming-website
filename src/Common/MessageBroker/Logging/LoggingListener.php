<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Logging;

use Gaming\Common\MessageBroker\Event\MessageHandled;
use Gaming\Common\MessageBroker\Event\MessageReceived;
use Gaming\Common\MessageBroker\Event\MessageSent;
use Gaming\Common\MessageBroker\Event\MessagesFlushed;
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

    public function messagesFlushed(MessagesFlushed $event): void
    {
        if ($event->numberOfSentMessages === 0) {
            return;
        }

        $this->logger->debug(
            'Messages flushed.',
            [
                'numberOfSentMessages' => $event->numberOfSentMessages
            ]
        );
    }
}
