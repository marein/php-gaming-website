<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\EventListener;

use Gaming\Common\EventStore\Event\EventsCommitted;
use Gaming\Common\EventStore\Event\EventsFetched;
use Psr\Log\LoggerInterface;

final class DebugEvents
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function eventsFetched(EventsFetched $event): void
    {
        foreach ($event->storedEvents as $storedEvent) {
            $this->logger->debug(
                'Event fetched.',
                [
                    'id' => $storedEvent->id(),
                    'streamId' => $storedEvent->domainEvent()->streamId,
                    'streamVersion' => $storedEvent->domainEvent()->streamVersion,
                    'content' => $storedEvent->domainEvent()->content::class,
                    'headers' => $storedEvent->domainEvent()->headers
                ]
            );
        }
    }

    public function eventsCommitted(EventsCommitted $event): void
    {
        count($event->storedEvents) && $this->logger->debug(
            'Events committed.',
            [
                'numberOfCommittedEvents' => count($event->storedEvents)
            ]
        );
    }
}
