<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Psr\Clock\ClockInterface;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var StoredEvent[]
     */
    private array $storedEvents = [];

    public function __construct(
        private readonly ClockInterface $clock
    ) {
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        return array_filter(
            $this->storedEvents,
            static function (StoredEvent $storedEvent) use ($aggregateId, $sinceId): bool {
                return $storedEvent->domainEvent()->aggregateId() === $aggregateId && $storedEvent->id() > $sinceId;
            }
        );
    }

    public function append(DomainEvent $domainEvent): void
    {
        $this->storedEvents[] = new StoredEvent(
            count($this->storedEvents) + 1,
            $this->clock->now(),
            $domainEvent
        );
    }
}
