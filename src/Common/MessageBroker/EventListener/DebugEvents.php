<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\EventListener;

use Gaming\Common\MessageBroker\Event\MessageHandled;
use Gaming\Common\MessageBroker\Event\MessageReceived;
use Gaming\Common\MessageBroker\Event\MessageSent;
use Gaming\Common\MessageBroker\Event\MessagesFlushed;
use Gaming\Common\MessageBroker\Event\ReplySent;
use Gaming\Common\MessageBroker\Event\RequestSent;
use Psr\Log\LoggerInterface;

final class DebugEvents
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
                'message' => $event->message->toArray(),
                'metadata' => $event->metadata
            ]
        );
    }

    public function messageHandled(MessageHandled $event): void
    {
        $this->logger->debug(
            'Message handled.',
            [
                'message' => $event->message->toArray(),
                'metadata' => $event->metadata
            ]
        );
    }

    public function messageSent(MessageSent $event): void
    {
        $this->logger->debug(
            'Message sent.',
            [
                'message' => $event->message->toArray(),
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
                'numberOfSentMessages' => $event->numberOfSentMessages,
                'metadata' => $event->metadata
            ]
        );
    }

    public function replySent(ReplySent $event): void
    {
        $this->logger->debug(
            'Reply sent.',
            [
                'message' => $event->message->toArray(),
                'metadata' => $event->metadata
            ]
        );
    }

    public function requestSent(RequestSent $event): void
    {
        $this->logger->debug(
            'Request sent.',
            [
                'message' => $event->message->toArray(),
                'metadata' => $event->metadata
            ]
        );
    }
}
