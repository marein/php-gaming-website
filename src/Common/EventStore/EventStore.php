<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\Exception\EventStoreException;

interface EventStore
{
    /**
     * @return StoredEvent[]
     * @throws EventStoreException
     */
    public function byAggregateId(string $aggregateId, int $sinceId = 0): array;

    /**
     * @throws EventStoreException
     */
    public function append(DomainEvent ...$domainEvents): void;
}
