<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\Normalizer\Exception\NormalizerException;

interface PollableEventStore
{
    /**
     * @return StoredEvent[]
     * @throws EventStoreException
     * @throws NormalizerException
     */
    public function since(int $id, int $limit): array;
}
