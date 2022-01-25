<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Exception;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\Exception\UnrecoverableException;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\Normalizer\Normalizer;

final class DoctrineEventStore implements EventStore
{
    private const SELECT = 'e.id, BIN_TO_UUID(e.aggregateId) as aggregateId, e.event, e.occurredOn';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly Normalizer $normalizer
    ) {
    }

    public function since(int $id, int $limit): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.id > :id')
                ->setParameter('id', $id)
                ->setMaxResults($limit)
                ->executeQuery()
                ->fetchAllAssociative();

            return $this->transformRowsToStoredEvents($rows);
        } catch (Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function byAggregateId(string $aggregateId, int $sinceId = 0): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.aggregateId = :aggregateId')
                ->andWhere('e.id > :id')
                ->setParameter('aggregateId', $aggregateId, 'uuid_binary')
                ->setParameter('id', $sinceId)
                ->executeQuery()
                ->fetchAllAssociative();

            return $this->transformRowsToStoredEvents($rows);
        } catch (Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function append(DomainEvent $domainEvent): void
    {
        try {
            $this->connection->insert(
                $this->table,
                [
                    'aggregateId' => $domainEvent->aggregateId(),
                    'event' => $this->normalizer->normalize($domainEvent, DomainEvent::class),
                    'occurredOn' => Clock::instance()->now()
                ],
                [
                    'uuid_binary',
                    'json',
                    'datetime_immutable'
                ]
            );
        } catch (Exception $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function hasUncommittedStoredEventId(int $id): bool
    {
        try {
            $currentIsolationLevel = $this->connection->getTransactionIsolation();

            $this->connection->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

            $hasStoredEvent = $this->connection->createQueryBuilder()
                    ->select('COUNT(id)')
                    ->from($this->table, 'e')
                    ->andWhere('e.id = :id')
                    ->setParameter('id', $id)
                    ->executeQuery()
                    ->fetchOne() > 0;

            $this->connection->setTransactionIsolation($currentIsolationLevel);

            return $hasStoredEvent;
        } catch (Exception $e) {
            throw new UnrecoverableException(
                'Something unexpected happened which can not rollback. Restart your process.',
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return StoredEvent[]
     */
    private function transformRowsToStoredEvents(array $rows): array
    {
        return array_map(
            fn(array $row): StoredEvent => new StoredEvent(
                (int)$row['id'],
                new DateTimeImmutable($row['occurredOn']),
                $this->normalizer->denormalize(
                    json_decode(
                        $row['event'],
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                    DomainEvent::class
                )
            ),
            $rows
        );
    }
}
