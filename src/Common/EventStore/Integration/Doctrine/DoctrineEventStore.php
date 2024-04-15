<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\CleanableEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\GapDetection;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventFilters;
use Gaming\Common\Normalizer\Normalizer;
use Psr\Clock\ClockInterface;
use Throwable;

final class DoctrineEventStore implements EventStore, PollableEventStore, CleanableEventStore
{
    private const SELECT = 'e.id, e.event, e.occurredOn';

    private readonly GapDetection $gapDetection;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
        private readonly Normalizer $normalizer,
        private readonly ClockInterface $clock
    ) {
        $this->gapDetection = new DoctrineWaitForUncommittedStoredEventsGapDetection(
            $this->connection,
            $this->table,
            'id'
        );
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

            return StoredEventFilters::untilGapIsFound(
                $this->transformRowsToStoredEvents($rows),
                $id,
                $this->gapDetection
            );
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
