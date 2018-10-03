<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\UnrecoverableException;
use Gaming\Common\EventStore\StoredEvent;

final class DoctrineEventStore implements EventStore
{
    private const SELECT = 'e.id, e.name, BIN_TO_UUID(e.aggregateId) as aggregateId, e.payload, e.occurredOn';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    /**
     * DoctrineEventStore constructor.
     *
     * @param Connection $connection
     * @param string     $table
     */
    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function storedEventsSince(int $id, int $limit): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.id > :id')
                ->setParameter(':id', $id)
                ->setMaxResults($limit)
                ->execute()
                ->fetchAll();

            return $this->transformRowsToStoredEvents($rows);
        } catch (\Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function storedEventsByAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.aggregateId = :aggregateId')
                ->andWhere('e.id > :id')
                ->setParameter(':aggregateId', $aggregateId, 'uuid_binary')
                ->setParameter(':id', $sinceId)
                ->execute()
                ->fetchAll();

            return $this->transformRowsToStoredEvents($rows);
        } catch (\Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function append(DomainEvent $domainEvent): void
    {
        try {
            $this->connection->insert($this->table, [
                'name'        => $domainEvent->name(),
                'aggregateId' => $domainEvent->aggregateId(),
                'payload'     => $domainEvent->payload(),
                'occurredOn'  => $domainEvent->occurredOn()
            ], [
                'string',
                'uuid_binary',
                'json',
                'datetime_immutable'
            ]);
        } catch (\Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function hasUncommittedStoredEventId(int $id): bool
    {
        try {
            $currentIsolationLevel = $this->connection->getTransactionIsolation();

            $this->connection->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

            $hasStoredEvent = $this->connection->createQueryBuilder()
                    ->select('COUNT(id)')
                    ->from($this->table, 'e')
                    ->andWhere('e.id = :id')
                    ->setParameter(':id', $id)
                    ->execute()
                    ->fetchColumn() > 0;

            $this->connection->setTransactionIsolation($currentIsolationLevel);

            return $hasStoredEvent;
        } catch (\Exception $e) {
            throw new UnrecoverableException(
                'Something unexpected happened which can not rollback. Restart your process.',
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Transform the sql rows to stored event instances.
     *
     * @param array $rows
     *
     * @return StoredEvent[]
     */
    private function transformRowsToStoredEvents(array $rows): array
    {
        return array_map(function ($row) {
            return new StoredEvent(
                (int)$row['id'],
                $row['name'],
                $row['aggregateId'],
                $row['payload'],
                new \DateTimeImmutable($row['occurredOn'])
            );
        }, $rows);
    }
}
