<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\UnrecoverableException;

interface EventStore
{
    /**
     * @return StoredEvent[]
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function since(int $id, int $limit): array;

    /**
     * @return StoredEvent[]
     * @throws EventStoreException
     */
    public function byAggregateId(string $aggregateId, int $sinceId = 0): array;

    /**
     * @throws EventStoreException
     */
    public function append(DomainEvent $domainEvent): void;

    /**
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function hasUncommittedStoredEventId(int $id): bool;
}
