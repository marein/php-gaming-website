<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Connection;
use Gaming\Common\EventStore\EventStore;

/**
 * Useful for sharding by enabling runtime configuration of an EventStore
 * based on context like tenantId, aggregateId, or region.
 * If the sharding key is the streamId and the EventStore is used directly
 * for event-sourced streams, a sharding-aware EventStore implementation
 * may be more straightforward.
 */
interface DoctrineEventStoreFactory
{
    public function withConnection(Connection $connection): EventStore;
}
