<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\Exception\EventStoreException;

interface EventStore
{
    /**
     * @return DomainEvent[]
     * @throws EventStoreException
     */
    public function byAggregateId(string $aggregateId): array;

    /**
     * @param DomainEvent[] $domainEvents
     *
     * @throws EventStoreException
     */
    public function append(array $domainEvents): void;
}
