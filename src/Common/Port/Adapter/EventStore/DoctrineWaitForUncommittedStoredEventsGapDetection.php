<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Gaming\Common\EventStore\GapDetection;

final class DoctrineWaitForUncommittedStoredEventsGapDetection implements GapDetection
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly string $column
    ) {
    }

    public function shouldWaitForStoredEventWithId(int $id): bool
    {
        $currentIsolationLevel = $this->connection->getTransactionIsolation();

        $this->connection->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

        $hasStoredEvent = $this->connection->createQueryBuilder()
                ->select('COUNT(' . $this->column . ')')
                ->from($this->table, 'e')
                ->andWhere('e.' . $this->column . ' = :id')
                ->setParameter('id', $id)
                ->executeQuery()
                ->fetchOne() > 0;

        $this->connection->setTransactionIsolation($currentIsolationLevel);

        return $hasStoredEvent;
    }
}
