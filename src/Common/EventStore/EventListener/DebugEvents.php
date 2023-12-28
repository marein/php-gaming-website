<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\EventListener;

use DateTimeInterface;
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
                    'occurredOn' => $storedEvent->occurredOn()->format(DateTimeInterface::ATOM),
                    'domainEvent' => $storedEvent->domainEvent()::class,
                    'aggregateId' => $storedEvent->domainEvent()->aggregateId()
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
