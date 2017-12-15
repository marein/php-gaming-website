<?php

namespace Gambling\Common\EventStore;

use Gambling\Common\Domain\DomainEvent;

interface EventStore
{
    /**
     * Returns the stored events since the given id.
     *
     * @param int $id
     * @param int $limit
     *
     * @return StoredEvent[]
     */
    public function storedEventsSince(int $id, int $limit): array;

    /**
     * Returns the stored events from the given aggregate id.
     *
     * @param string $aggregateId
     * @param int    $sinceId
     *
     * @return StoredEvent[]
     */
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array;

    /**
     * Append the given event to the store.
     *
     * @param DomainEvent $domainEvent
     *
     * @return void
     */
    public function append(DomainEvent $domainEvent): void;
}
