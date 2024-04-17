<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\DuplicateVersionInStreamException;
use Gaming\Common\EventStore\Exception\EventStoreException;

interface EventStore
{
    /**
     * @return DomainEvent[]
     * @throws EventStoreException
     */
    public function byStreamId(string $streamId, int $fromStreamVersion = 0): array;

    /**
     * @throws DuplicateVersionInStreamException
     * @throws EventStoreException
     */
    public function append(DomainEvent ...$domainEvents): void;
}
