<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;

interface PollableEventStore
{
    /**
     * @return StoredEvent[]
     * @throws EventStoreException
     */
    public function since(int $id, int $limit): array;
}
