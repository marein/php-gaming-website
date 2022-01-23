<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\UnrecoverableException;
use Gaming\Common\Normalizer\Exception\NormalizerException;

interface EventStore
{
    /**
     * @return StoredEvent[]
     * @throws UnrecoverableException
     * @throws EventStoreException
     * @throws NormalizerException
     */
    public function since(int $id, int $limit): array;

    /**
     * @return StoredEvent[]
     * @throws EventStoreException
     * @throws NormalizerException
     */
    public function byAggregateId(string $aggregateId, int $sinceId = 0): array;

    /**
     * @throws EventStoreException
     * @throws NormalizerException
     */
    public function append(DomainEvent $domainEvent): void;

    /**
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function hasUncommittedStoredEventId(int $id): bool;
}
