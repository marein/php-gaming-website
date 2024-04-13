<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;

final class InMemoryEventStore implements EventStore
{
    /**
     * @var array<string, DomainEvent[]>
     */
    private array $domainEvents = [];

    public function byAggregateId(string $aggregateId): array
    {
        return $this->domainEvents[$aggregateId] ?? [];
    }

    public function append(array $domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            $this->domainEvents[$domainEvent->aggregateId()][] = $domainEvent;
        }
    }
}
