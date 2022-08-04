<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\UnrecoverableException;
use Gaming\Common\Normalizer\Exception\NormalizerException;

interface PollableEventStore
{
    /**
     * @return StoredEvent[]
     * @throws UnrecoverableException
     * @throws EventStoreException
     * @throws NormalizerException
     */
    public function since(int $id, int $limit): array;

    /**
     * @throws UnrecoverableException
     * @throws EventStoreException
     */
    public function hasUncommittedStoredEventId(int $id): bool;
}
