<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Connection;
use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\EventStore\EventStorePointerFactory;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;

final class DoctrineMysqlEventStorePointerFactory implements EventStorePointerFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName
    ) {
    }

    public function withName(string $name): EventStorePointer
    {
        return new InMemoryCacheEventStorePointer(
            new DoctrineMysqlEventStorePointer(
                $this->connection,
                $this->tableName,
                $name
            )
        );
    }
}
