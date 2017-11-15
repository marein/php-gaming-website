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
     * Append the given event to the store.
     *
     * @param DomainEvent $domainEvent
     *
     * @return void
     */
    public function append(DomainEvent $domainEvent): void;
}
