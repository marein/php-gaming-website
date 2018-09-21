<?php
declare(strict_types=1);

namespace Gambling\Common\EventStore;

use Gambling\Common\Domain\DomainEvent;
use Gambling\Common\EventStore\Exception\EventStoreException;
use Gambling\Common\EventStore\Exception\UnrecoverableException;

interface EventStore
{
    /**
     * Returns the stored events since the given id.
     *
     * @param int $id
     * @param int $limit
     *
     * @return StoredEvent[]
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function storedEventsSince(int $id, int $limit): array;

    /**
     * Returns the stored events from the given aggregate id.
     *
     * @param string $aggregateId
     * @param int    $sinceId
     *
     * @return StoredEvent[]
     * @throws EventStoreException
     */
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array;

    /**
     * Append the given event to the store.
     *
     * @param DomainEvent $domainEvent
     *
     * @return void
     * @throws EventStoreException
     */
    public function append(DomainEvent $domainEvent): void;

    /**
     * Returns true if the Database has an uncommitted event with this id.
     *
     * todo: Move this method in its own interface?
     *
     * @param int $id
     *
     * @return bool
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function hasUncommittedStoredEventId(int $id): bool;
}
