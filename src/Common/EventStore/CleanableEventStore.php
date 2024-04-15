<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;

/**
 * Cleaning up the EventStore can be helpful if events aren't needed anymore, e.g. because
 * they've been pushed to their destination and the EventStore is only used as an outbox.
 *
 * This is a separate interface, because not all implementations provide this feature.
 */
interface CleanableEventStore
{
    /**
     * @throws EventStoreException
     */
    public function cleanUpTo(int $id): void;
}
