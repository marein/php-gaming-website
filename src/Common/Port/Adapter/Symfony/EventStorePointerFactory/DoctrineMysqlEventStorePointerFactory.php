<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony\EventStorePointerFactory;

use Doctrine\DBAL\Connection;
use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\Port\Adapter\EventStore\DoctrineMysqlEventStorePointer;

final class DoctrineMysqlEventStorePointerFactory implements EventStorePointerFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName
    ) {
    }

    public function withName(string $name): EventStorePointer
    {
        return new DoctrineMysqlEventStorePointer(
            $this->connection,
            $this->tableName,
            $name
        );
    }
}
