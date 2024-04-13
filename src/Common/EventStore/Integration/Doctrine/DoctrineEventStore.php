<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\CleanableEventStore;
use Gaming\Common\EventStore\Event\EventsCommitted;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\GapDetection;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventFilters;
use Gaming\Common\Normalizer\Normalizer;
use Psr\Clock\ClockInterface;
use Throwable;

final class DoctrineEventStore implements EventStore, PollableEventStore, CleanableEventStore, GapDetection
{
    private const SELECT = 'e.id, e.event, e.occurredOn';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly Normalizer $normalizer,
        private readonly ClockInterface $clock,
        private readonly bool $enableGapDetection = true
    ) {
    }

    public function byAggregateId(string $aggregateId): array
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select(self::SELECT)
                ->from($this->table, 'e')
                ->where('e.aggregateId = :aggregateId')
                ->setParameter('aggregateId', $aggregateId, 'uuid')
                ->executeQuery()
                ->fetchAllAssociative();

            return array_map(
                $this->transformRowToDomainEvent(...),
                $rows
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function append(array $domainEvents): void
    {
        if (count($domainEvents) === 0) {
            return;
        }

        $params = $types = [];
        foreach ($domainEvents as $domainEvent) {
            $params[] = $domainEvent->aggregateId();
            $params[] = $this->normalizer->normalize($domainEvent, DomainEvent::class);
            $params[] = $this->clock->now();
            array_push($types, 'uuid', Types::JSON, Types::DATETIME_IMMUTABLE);
        }

        try {
            $this->connection->executeStatement(
                sprintf(
                    'INSERT INTO %s (aggregateId, event, occurredOn) VALUES %s',
                    $this->table,
                    implode(', ', array_map(static fn(DomainEvent $domainEvent): string => '(?, ?, ?)', $domainEvents))
                ),
                $params,
                $types
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
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

            return $this->enableGapDetection ? StoredEventFilters::untilGapIsFound(
                $this->transformRowsToStoredEvents($rows),
                $id,
                $this
            ) : $this->transformRowsToStoredEvents($rows);
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function cleanUpTo(int $id): void
    {
        try {
            $this->connection->createQueryBuilder()
                ->delete($this->table)
                ->where('id <= :id')
                ->setParameter('id', $id)
                ->executeStatement();
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function shouldWaitForStoredEventWithId(int $id): bool
    {
        $currentIsolationLevel = $this->connection->getTransactionIsolation();

        $this->connection->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

        $hasStoredEvent = $this->connection->createQueryBuilder()
                ->select('COUNT(e.id)')
                ->from($this->table, 'e')
                ->andWhere('e.id = :id')
                ->setParameter('id', $id)
                ->executeQuery()
                ->fetchOne() > 0;

        $this->connection->setTransactionIsolation($currentIsolationLevel);

        return $hasStoredEvent;
    }

    /**
     * This method can be registered as a listener. This is particularly useful
     * if there is only one streaming process, the EventStore is to be cleaned
     * up immediately and no GapDetection is used.
     */
    public function deleteStoredEventsWhenCommitted(EventsCommitted $eventsCommitted): void
    {
        if (count($eventsCommitted->storedEvents) === 0) {
            return;
        }

        try {
            $this->connection->createQueryBuilder()
                ->delete($this->table)
                ->where('id in (:ids)')
                ->setParameter(
                    'ids',
                    array_map(
                        static fn(StoredEvent $storedEvent): int => $storedEvent->id(),
                        $eventsCommitted->storedEvents
                    ),
                    ArrayParameterType::INTEGER
                )
                ->executeStatement();
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return StoredEvent[]
     * @throws Throwable
     */
    private function transformRowsToStoredEvents(array $rows): array
    {
        return array_map(
            fn(array $row): StoredEvent => new StoredEvent(
                (int)$row['id'],
                $this->connection->convertToPHPValue($row['occurredOn'], Types::DATETIME_IMMUTABLE),
                $this->transformRowToDomainEvent($row)
            ),
            $rows
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function transformRowToDomainEvent(array $row): DomainEvent
    {
        return $this->normalizer->denormalize(
            $this->connection->convertToPHPValue($row['event'], Types::JSON),
            DomainEvent::class
        );
    }
}
